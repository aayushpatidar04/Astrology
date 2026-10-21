<?php

namespace App\Http\Controllers;

use App\Events\AstrologerStatusUpdated;
use App\Events\CallSignal;
use App\Events\TypingEvent;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\CallHistory;
use App\Models\Chat;
use App\Models\ChatSession;
use App\Models\User;
use App\Models\UserWallet;
use App\Services\AstrologerWalletService;
use App\Services\ChatSessionSettlementService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Pusher\PushNotifications\PushNotifications;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function typing(Request $request, $chatId)
    {
        $request->validate([
            'typing' => 'required|boolean',
        ]);
        // Ensure the user is part of the chat
        $chat = Chat::where('id', $chatId)
            ->whereHas('participants', fn($q) => $q->where('user_id', Auth::id()))
            ->firstOrFail();

        // Broadcast typing event
        broadcast(new TypingEvent($chat->id, Auth::id(), $request->typing))->toOthers();

        return response()->json(['success' => true]);
    }

    public function signal(Request $request)
    {
        // Carry actual SDP or ICE candidate
        broadcast(new CallSignal(
            (int)$request->roomId,
            $request->type,
            $request->data,
            Auth::id()
        ))->toOthers();

        return response()->json(['status' => 'ok']);
    }

    public function chatEnd(Request $request, $chatId)
    {
        $validated = $request->validate([
            'deduction' => ['nullable', 'numeric', 'min:0'],
            'elapsed_seconds' => ['nullable', 'integer', 'min:0'],
            'session_reference' => ['nullable', 'uuid'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $sessionReference = $validated['session_reference'] ?? null;

        $chat = Chat::with('participants.user.roles')->findOrFail($chatId);

        // Find the paying participant (role: User)
        $userParticipant = $chat->participants
            ->filter(function ($p) {
                return $p->user->hasRole('User');
            })->first();

        $astrologerParticipant = $chat->participants
            ->filter(function ($p) {
                return $p->user->hasRole('Astrologer');
            })->first();

        if (! $userParticipant || ! $astrologerParticipant) {
            return response()->json(['error' => 'Participants missing'], 404);
        }

        abort_unless(
            in_array(Auth::id(), [$userParticipant->user_id, $astrologerParticipant->user_id], true),
            403
        );

        $user = $userParticipant->user->load('wallet');
        $astrologer = $astrologerParticipant->user->astrologer;
        $elapsedSeconds = 0;
        $chargedRate = (float) ($astrologer?->charged_text_price ?? 0);

        if (isset($validated['deduction']) && $chargedRate > 0) {
            $elapsedSeconds = (int) round(($validated['deduction'] / $chargedRate) * 60);
        } elseif (isset($validated['elapsed_seconds'])) {
            $elapsedSeconds = $validated['elapsed_seconds'];
        }

        $deduction = app(ChatSessionSettlementService::class)->settle(
            $chat,
            $user,
            $astrologer,
            $astrologerParticipant->user->id,
            $elapsedSeconds,
            $sessionReference,
            $validated['reason'] ?? null
        );

        return response()->json([
            'success'   => true,
            'deduction' => $deduction,
        ]);
    }

    public function callEnd(Request $request, $chatId)
    {
        $validated = $request->validate([
            'elapsed_seconds' => ['nullable', 'integer', 'min:0'],
            'deduction' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:answered,ended,missed,rejected,failed'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $chat = Chat::with('participants.user.roles')->findOrFail($chatId);

        // Find the paying participant (role: User)
        $userParticipant = $chat->participants
            ->filter(function ($p) {
                return $p->user->hasRole('User');
            })->first();

        $astrologerParticipant = $chat->participants
            ->filter(function ($p) {
                return $p->user->hasRole('Astrologer');
            })->first();

        if (! $userParticipant || ! $astrologerParticipant) {
            return response()->json(['error' => 'Participants missing'], 404);
        }

        abort_unless(
            in_array(Auth::id(), [$userParticipant->user_id, $astrologerParticipant->user_id], true),
            403
        );

        $user = $userParticipant->user->load('wallet');
        $astrologer = $astrologerParticipant->user->astrologer;
        $chargedRate = (float) ($astrologer?->charged_call_price ?? 0);
        $elapsedSeconds = (int) ($validated['elapsed_seconds'] ?? 0);
        if (isset($validated['deduction']) && ! isset($validated['elapsed_seconds']) && $chargedRate > 0) {
            $elapsedSeconds = (int) round(($validated['deduction'] / $chargedRate) * 60);
        }

        $deduction = DB::transaction(function () use (
            $user,
            $astrologer,
            $astrologerParticipant,
            $chargedRate,
            $elapsedSeconds,
            $validated
        ): float {
            $callHistory = CallHistory::where('user_id', $user->id)
                ->where('astrologer_id', $astrologerParticipant->user->id)
                ->whereNull('ended_at')
                ->latest()
                ->lockForUpdate()
                ->first();

            if (! $callHistory) {
                return 0;
            }

            $user->ensureWallet();
            $userWallet = UserWallet::where('user_id', $user->id)->lockForUpdate()->firstOrFail();
            $requestedDeduction = round(($elapsedSeconds / 60) * $chargedRate, 2);
            $deduction = min($requestedDeduction, (float) $userWallet->balance);

            if ($deduction > 0) {
                $userWallet->decrement('balance', $deduction);
            }

            $callHistory->update([
                'ended_at' => now(),
                'duration' => $elapsedSeconds,
                'cost' => $deduction,
                'status' => $validated['status'],
                'notes' => $validated['reason'] ?? null,
            ]);

            if ($astrologer && $validated['status'] === 'answered') {
                $earning = min(
                    $deduction,
                    round(($elapsedSeconds / 60) * (float) $astrologer->asked_call_price, 2)
                );
                app(AstrologerWalletService::class)->recordEarning(
                    $astrologer,
                    'call_history',
                    $callHistory->id,
                    'Call session earnings',
                    $earning
                );
            }

            return $deduction;
        });

        return response()->json([
            'success' => true,
            'deduction' => $deduction,
        ]);
    }

    public function updateCallStatus(Request $request)
    {
        $chat = Chat::with('participants.user.roles')->findOrFail($request->chatId);

        $userParticipant = $chat->participants
            ->filter(function ($p) {
                return $p->user->hasRole('User');
            })->first();

        $astrologerParticipant = $chat->participants
            ->filter(function ($p) {
                return $p->user->hasRole('Astrologer');
            })->first();

        $callHistory = CallHistory::where('user_id', $userParticipant->user->id)
            ->where('astrologer_id', $astrologerParticipant->user->id)
            ->latest()
            ->first();

        if ($callHistory) {
            $updateData = ['status' => $request->status];

            if (in_array($request->status, ['ended', 'failed'])) {
                $updateData['ended_at'] = now();
                $updateData['duration'] = 0;
                $updateData['cost'] = 0;
            }

            $callHistory->update($updateData);
            $callHistory->refresh();
        }

        return response()->json([
            'success' => true,
            'call_history' => $callHistory ? $callHistory->toArray() : null,
        ]);
    }

    public function beamAuth(Request $request)
    {
        $beamsClient = new PushNotifications([
            'instanceId' => config('services.pusher_beams.instance_id'),
            'secretKey' => config('services.pusher_beams.secret_key'),
        ]);

        return response()->json(
            $beamsClient->generateToken((string) $request->user()->id)
        );
    }

    public function setBusy(Request $request, $id)
    {
        $astrologer = User::findOrFail($id);
        $astrologer->is_busy = $request->busy ? 1 : 0;
        $astrologer->save();

        broadcast(new AstrologerStatusUpdated($astrologer->astrologer->id, $astrologer->is_busy));

        return response()->json(['is_busy' => $astrologer->is_busy]);
    }
}
