<?php

namespace App\Http\Controllers;

use App\Events\CallSignal;
use App\Events\TypingEvent;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Chat;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

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

    public function start(Request $request)
    {
        broadcast(new CallSignal(
            (int)$request->roomId,
            'call_started',
            null,
            auth()->id()
        ))->toOthers();

        return response()->json(['status' => 'ok']);
    }

    public function signal(Request $request)
{
    // Carry actual SDP or ICE candidate
    broadcast(new CallSignal(
        (int)$request->roomId,
        $request->type,
        $request->data,
        auth()->id()
    ))->toOthers();

    return response()->json(['status' => 'ok']);
}

}
