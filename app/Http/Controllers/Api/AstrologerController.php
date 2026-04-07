<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AstrologerController extends Controller
{
    public function chats()
    {
        $userId = Auth::id();

        // Fetch chats where user is a participant
        $chats = Chat::with(['participants.user.details', 'participants.user.astrologer', 'lastMessage'])
            ->whereHas('participants', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->latest('updated_at')
            ->get()
            ->map(function ($chat) {
                return [
                    'id' => $chat->id,
                    'title' => $chat->title ?? 'Chat',
                    'participants' => $chat->participants->map(function ($p) {
                        $profileImage = null;

                        if ($p->user->hasRole('User')) {
                            $profileImage = $p->user->details->profile_image ?? null;
                        } elseif ($p->user->hasRole('Astrologer')) {
                            $profileImage = $p->user->astrologer->profile_image ?? null;
                        }
                        return [
                            'id' => $p->user->id,
                            'name' => $p->user->name,
                            'profile_image' => $profileImage,
                        ];
                    }),
                    'last_message' => $chat->lastMessage ? [
                        'id' => $chat->lastMessage->id,
                        'chat_id' => $chat->lastMessage->chat_id,
                        'sender_id' => $chat->lastMessage->user_id,
                        'sender_name' => $chat->lastMessage->user->name,
                        'message' => $chat->lastMessage->message,
                        'created_at' => $chat->lastMessage->created_at,
                    ] : null,
                ];
            });

        return response()->json([
            'status' => 'success',
            'message' => 'Chats fetched successfully!',
            'chats' => $chats,
        ]);
    }

    public function messages($chatId)
    {
        $userId = Auth::id();

        // Ensure the user is a participant in this chat
        $chat = Chat::whereHas('participants', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
            ->findOrFail($chatId);

        // Fetch messages with sender info
        $messages = $chat->messages()
            ->with(['user.details', 'user.astrologer']) // eager load sender relationship
            ->orderBy('created_at', 'desc')
            ->paginate(50); // paginate for mobile scrolling

        // Transform response
        $messagesData = $messages->through(function ($msg) {
            $profileImage = null;

            // Check role and pick image accordingly
            if ($msg->user->hasRole('User')) {
                $profileImage = $msg->user->details->profile_image ?? null;
            } elseif ($msg->user->hasRole('Astrologer')) {
                $profileImage = $msg->user->astrologer->profile_image ?? null;
            }
            return [
                'id' => $msg->id,
                'sender' => [
                    'id' => $msg->user->id,
                    'name' => $msg->user->name,
                    'profile_image' => $profileImage,
                ],
                'message' => $msg->message,
                'created_at' => $msg->created_at->toDateTimeString(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Messages fetched successfully!',
            'chat_id' => $chat->id,
            'messages' => $messagesData,
        ]);
    }
}
