<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Astrologer;
use App\Models\Chat;
use App\Models\ChatSession;
use App\Models\CallHistory;
use Carbon\Carbon;
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

    public function stats($id)
    {
        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        $astrologer = Astrologer::where('user_id', $id)->first();
        // --- Chats ---
        $todayChats = ChatSession::where('astrologer_id', $id)
            ->whereDate('created_at', $today)
            ->distinct('chat_id')
            ->count('chat_id');

        $monthChats = ChatSession::where('astrologer_id', $id)
            ->whereBetween('created_at', [$monthStart, Carbon::now()])
            ->distinct('chat_id')
            ->count('chat_id');

        // --- Calls ---
        $todayCalls = CallHistory::where('astrologer_id', $id)
            ->whereDate('created_at', $today)
            ->distinct('id')
            ->count('id');

        $monthCalls = CallHistory::where('astrologer_id', $id)
            ->whereBetween('created_at', [$monthStart, Carbon::now()])
            ->distinct('id')
            ->count('id');

        // --- Earnings based on asked prices ---
        // Calls: duration (seconds) → minutes → multiply by asked_call_price
        $callDurationToday = CallHistory::where('astrologer_id', $id)
            ->whereDate('created_at', $today)
            ->sum('duration');

        $callDurationMonth = CallHistory::where('astrologer_id', $id)
            ->whereBetween('created_at', [$monthStart, Carbon::now()])
            ->sum('duration');

        $callEarningToday = ($callDurationToday / 60) * $astrologer->asked_call_price;
        $callEarningMonth = ($callDurationMonth / 60) * $astrologer->asked_call_price;

        // Chats: duration_seconds → minutes → multiply by asked_text_price
        $chatDurationToday = ChatSession::where('astrologer_id', $id)
            ->whereDate('created_at', $today)
            ->sum('duration_seconds');

        $chatDurationMonth = ChatSession::where('astrologer_id', $id)
            ->whereBetween('created_at', [$monthStart, Carbon::now()])
            ->sum('duration_seconds');

        $chatEarningToday = ($chatDurationToday / 60) * $astrologer->asked_text_price;
        $chatEarningMonth = ($chatDurationMonth / 60) * $astrologer->asked_text_price;

        // --- Last 3 consultations (chat or call) ---
        $lastChats = ChatSession::where('astrologer_id', $id)
            ->join('users', 'chat_sessions.user_id', '=', 'users.id')
            ->select(
                'chat_sessions.id',
                'chat_sessions.chat_id',
                'chat_sessions.user_id',
                'users.name as user_name',
                'chat_sessions.duration_seconds as duration',
                'chat_sessions.ended_at',
                'chat_sessions.created_at',
                \DB::raw("'chat' as type")
            )
            ->orderBy('chat_sessions.created_at', 'desc')
            ->limit(3);

        $lastCalls = CallHistory::where('astrologer_id', $id)
            ->join('users', 'call_histories.user_id', '=', 'users.id')
            ->select(
                'call_histories.id',
                \DB::raw('NULL as chat_id'), // align columns with chat_sessions
                'call_histories.user_id',
                'users.name as user_name',
                'call_histories.duration',
                'call_histories.ended_at',
                'call_histories.created_at',
                \DB::raw("'call' as type")
            )
            ->orderBy('call_histories.created_at', 'desc')
            ->limit(3);

        $lastConsultations = $lastChats
            ->unionAll($lastCalls)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();



        return response()->json([
            'today' => [
                'chats' => $todayChats,
                'calls' => $todayCalls,
                'earning' => round($callEarningToday + $chatEarningToday, 2),
            ],
            'month' => [
                'chats' => $monthChats,
                'calls' => $monthCalls,
                'earning' => round($callEarningMonth + $chatEarningMonth, 2),
            ],
            'last_consultations' => $lastConsultations,
        ]);
    }
}
