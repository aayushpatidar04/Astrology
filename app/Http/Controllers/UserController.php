<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Astrologer;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Pusher\PushNotifications\PushNotifications;

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
            'user' => Auth::user()?->load(['details', 'wallet']),
            'astrologers' => $astrologers,
        ]);
    }

    public function startChat($astrologerId)
    {
        $user = auth()->user()->load('wallet');
        $astrologer = Astrologer::findOrFail($astrologerId);

        // Calculate minimum required balance (4 minutes)
        $ratePerMinute = $astrologer->charged_text_price;
        $minRequiredBalance = $ratePerMinute * 4;

        if ($user->wallet->balance < $minRequiredBalance) {
            return redirect()->route('user.chat-with-astrologers')
                ->with('error', 'You need at least 4 minutes balance to start a chat.');
        }

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

        $user = auth()->user()->load('wallet');
        $astrologer = $chat->participants
            ->where('user_id', '!=', $user->id)
            ->first()
            ->user
            ->astrologer;

        $ratePerMinute = $astrologer->charged_text_price;
        $minRequiredBalance = $ratePerMinute * 4; // 4 minutes minimum

        if ($user->wallet->balance < $minRequiredBalance) {
            // Redirect to recharge page or show a message
            return redirect()->route('user.chat-with-astrologers')
                ->with('error', 'You need at least 4 minutes balance to start a chat.');
        }

        return Inertia::render('User/ChatWindow', [
            'auth'     => ['user' => $user],
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
        $beamsClient = new PushNotifications([
            "instanceId" => config('app.VITE_PUSHER_BEAMS_INSTANCE_ID'),
            "secretKey" => config('app.VITE_PUSHER_BEAMS_SECRET_KEY'),
        ]);

        $recipientId = $chat->participants()
            ->where('user_id', '!=', Auth::id())
            ->value('user_id');

        if ($recipientId) {
            $beamsClient->publishToUsers(
                [(string) $recipientId],
                [
                    "web" => [
                        "notification" => [
                            "title" => "New Chat Message!",
                            "body" => $request->message,
                            // "icon" => "https://myastrosathi.intouchsoftware.co.in/images/favicon.png",
                            "icon" => asset(Auth::user()->load('details')->details->profile_image),
                            "deep_link" => route('astrologer.chats', ['id' => $chat->id]),
                            "data" => [],
                        ]
                    ]
                ]
            );
        }

        // Return JSON instead of redirect
        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    public function chatSessions()
    {
        $sessions = ChatSession::with(['chat', 'user', 'astrologer'])->where('user_id', auth()->id())->latest()->get();
        return Inertia::render('User/ChatSession', [
            'sessions' => $sessions,
        ]);
    }
}
