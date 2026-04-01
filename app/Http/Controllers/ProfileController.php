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
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $user = $userParticipant->user->load('wallet');
        $astrologer = $astrologerParticipant->user->astrologer;

        $deduction = 0;
        $elapsedSeconds = 0;

        // Case 1: Deduction provided directly
        if ($request->filled('deduction')) {
            $deduction = (float) $request->input('deduction', 0);
            $ratePerMinute = $astrologer?->charged_text_price ?? 0;
            if ($ratePerMinute > 0) {
                $elapsedSeconds = (int) round(($deduction / $ratePerMinute) * 60);
            }
        }
        // Case 2: Elapsed seconds provided
        elseif ($request->filled('elapsed_seconds')) {
            $elapsedSeconds = (int) $request->input('elapsed_seconds', 0);
            $ratePerMinute = $astrologer?->charged_text_price ?? 0;
            $deduction = ($elapsedSeconds / 60) * $ratePerMinute;
        }

        // Deduct from wallet if positive
        if ($deduction > 0) {
            $currentBalance = $user->wallet->balance;
            $finalDeduction = min($deduction, $currentBalance); // cap deduction at current balance

            if ($finalDeduction > 0) {
                $user->wallet->decrement('balance', $finalDeduction);
            }
        }

        ChatSession::create([
            'chat_id'         => $chat->id,
            'user_id'         => $user->id,
            'astrologer_id'   => $astrologerParticipant->user->id,
            'duration_seconds' => $elapsedSeconds,
            'deduction'       => $deduction,
            'ended_by'        => $request->input('reason'),
            'ended_at'        => now(),
        ]);

        return response()->json([
            'success'   => true,
            'deduction' => $deduction,
        ]);
    }

    public function callEnd(Request $request, $chatId)
    {
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

        $user = $userParticipant->user->load('wallet');
        $astrologer = $astrologerParticipant->user->astrologer;

        $deduction = 0;
        $elapsedSeconds = 0;

        // Case 1: Deduction provided directly
        if ($request->filled('deduction')) {
            $deduction = (float) $request->input('deduction', 0);
            $ratePerMinute = $astrologer?->charged_call_price ?? 0;
            if ($ratePerMinute > 0) {
                $elapsedSeconds = (int) round(($deduction / $ratePerMinute) * 60);
            }
        }
        // Case 2: Elapsed seconds provided (fallback, though we expect deduction)
        elseif ($request->filled('elapsed_seconds')) {
            $elapsedSeconds = (int) $request->input('elapsed_seconds', 0);
            $ratePerMinute = $astrologer?->charged_call_price ?? 0;
            $deduction = ($elapsedSeconds / 60) * $ratePerMinute;
        }

        // Deduct from wallet if positive
        if ($deduction > 0) {
            $currentBalance = $user->wallet->balance;
            $finalDeduction = min($deduction, $currentBalance); // cap deduction at current balance

            if ($finalDeduction > 0) {
                $user->wallet->decrement('balance', $finalDeduction);
            }
        }

        // Find the latest call history record
        $callHistory = CallHistory::where('user_id', $user->id)
            ->where('astrologer_id', $astrologerParticipant->user->id)
            ->whereNull('ended_at')
            ->latest()
            ->first();

        if ($callHistory) {
            $callHistory->update([
                'ended_at' => now(),
                'duration' => $elapsedSeconds,
                'cost' => $deduction,
                'status' => $request->status,
                'notes' => $request->input('reason'),
            ]);
        }

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
            "instanceId" => config('app.VITE_PUSHER_BEAMS_INSTANCE_ID'),
            "secretKey" => config('app.VITE_PUSHER_BEAMS_SECRET_KEY'),
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
