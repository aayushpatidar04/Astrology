<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Astrologer;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UserController extends Controller
{
    protected array $middleware = [
        'auth',
        'role:User',
    ];

    public function chatWithAstrologers()
    {
        $astrologers = Astrologer::with('user')->where('online', 1)->paginate(20);
        return Inertia::render('Astrologers/Index', [
            'user' => Auth::user()->load('details'),
            'astrologers' => $astrologers,
        ]);
    }

    public function startChat($astrologerId)
    {
        $user = auth()->user();
        $astrologer = Astrologer::findOrFail($astrologerId);

        // Check if chat already exists between user and astrologer
        $chat = Chat::whereHas('participants', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->whereHas('participants', function ($q) use ($astrologer) {
            $q->where('user_id', $astrologer->user_id);
        })->first();

        if (! $chat) {
            $chat = Chat::create([
                'name' => 'chat_' . $user->id . '_' . $astrologer->user_id,
            ]);

            $chat->participants()->createMany([
                ['user_id' => $user->id],
                ['user_id' => $astrologer->user_id],
            ]);
        }

        // Redirect to chat window (user side)
        return redirect()->route('user.chat.show', $chat->id);
    }

    public function showChat($id)
    {
        $chat = Chat::with(['participants.user.astrologer', 'messages.user'])
            ->findOrFail($id);

        return Inertia::render('User/ChatWindow', [
            'auth'     => ['user' => auth()->user()],
            'chat'     => $chat,
            'messages' => $chat->messages,
        ]);
    }

    public function storeMessage(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $chat = Chat::findOrFail($id);

        $message = ChatMessage::create([
            'chat_id' => $chat->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
        ]);

        broadcast(new MessageSent($message))->toOthers();

        // Return JSON instead of redirect
        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }
}
