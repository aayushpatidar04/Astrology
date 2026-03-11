<?php

namespace App\Http\Controllers;

use App\Events\AstrologerStatusChanged;
use App\Events\MessageSent;
use App\Models\Chat;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AstrologerController extends Controller
{
    protected array $middleware = [
        'auth',
        'role:Astrologer',
    ];

    public function dashboard()
    {
        return Inertia::render('Dashboard/Astrologer/Dashboard', [
            'user' => Auth::user()->load('astrologer'),
        ]);
    }

    public function status(Request $request)
    {
        $request->validate([
            'online' => 'required|boolean'
        ]);
        try {
            $astrologer = Auth::user()->astrologer;

            if (! $astrologer) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Astrologer profile not found',
                ], 404);
            }

            $astrologer->update([
                'online' => $request->online,
            ]);

            broadcast(new AstrologerStatusChanged($astrologer->user_id, $request->online))->toOthers();

            return response()->json([
                'status' => 'success',
                'online' => $astrologer->online,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function chats($id = null)
    {
        $user = Auth::user();

        $chats = $user->chats()
            ->whereHas('messages') // only chats that have at least one message
            ->with([
                'participants.user',
                'messages' => function ($q) {
                    $q->latest()->limit(1);
                }
            ])
            ->withMax('messages', 'created_at')
            ->orderByDesc('messages_max_created_at')
            ->get();

        $chat = null;
        $messages = [];

        if ($id) {
            $chat = Chat::with(['participants.user', 'messages.user'])->findOrFail($id);
            $messages = $chat->messages;
        }

        return Inertia::render('Dashboard/Astrologer/Chats', [
            'user'  => $user->load('astrologer'),
            'chats' => $chats,
            'chat'     => $chat,
            'messages' => $messages,
        ]);
    }

    public function storeMessage(Request $request, $id){
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $chat = Chat::findOrFail($id);

        // Create the new message
        $message = ChatMessage::create([
            'chat_id'   => $chat->id,
            'user_id' => Auth::id(),
            'message'   => $request->message,
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }
}
