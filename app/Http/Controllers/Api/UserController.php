<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Models\Astrologer;
use App\Models\Chat;
use App\Models\ChatSession;
use App\Models\RechargePackage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected array $middleware = [
        'auth',
        'role' => 'User'
    ];

    public function astrologers()
    {
        $astrologers = Astrologer::with('user')
            ->where('online', 1)
            ->latest()
            ->paginate(12)
            ->through(function ($astrologer) {
                return [
                    'id' => $astrologer->id,
                    'user_id' => $astrologer->user->id,
                    'name' => $astrologer->user->name,
                    'profile_image' => $astrologer->profile_image,
                    'expertise' => $astrologer->expertise,
                    'experience_years' => $astrologer->experience_years,
                    'online' => $astrologer->online,
                    'bio' => $astrologer->bio,
                    'status' => $astrologer->status,
                    'charged_text_price' => $astrologer->charged_text_price,
                    'charged_call_price' => $astrologer->charged_call_price,
                ];
            });

        return response()->json([
            'status' => 'success',
            'message' => 'Astrologers fetched successfully!',
            'astrologers' => $astrologers,
        ]);
    }

    public function startChat($astrologerId)
    {
        $user = auth()->user();
        $astrologer = User::findOrFail($astrologerId);

        $chat = Chat::whereHas('participants', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->whereHas('participants', function ($q) use ($astrologer) {
            $q->where('user_id', $astrologer->id);
        })->first();

        if (! $chat) {
            $chat = Chat::create([
                'name' => 'chat_' . $user->id . '_' . $astrologer->id,
            ]);

            $chat->participants()->createMany([
                ['user_id' => $user->id],
                ['user_id' => $astrologer->id],
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Chat fetched successfully',
            'chat' => $chat
        ]);
    }

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

    public function recharge()
    {
        $user = auth()->user()->load('wallet');

        $firstTimeOffers = collect();
        $regularOffers   = collect();
        $specialOffers   = collect();

        if ($user) {
            if (!$user->orders()->exists()) {
                $firstTimeOffers = RechargePackage::where('type', 'first_time')->get();
                $specialOffers   = RechargePackage::where('type', 'special')->get();
            } else {
                $regularOffers   = RechargePackage::where('type', 'regular')->get();
                $specialOffers   = RechargePackage::where('type', 'special')->get();
            }
        }

        return response()->json([
            'firstTimeOffers' => $firstTimeOffers,
            'regularOffers'   => $regularOffers,
            'specialOffers'   => $specialOffers,
            'walletBalance'   => $user->wallet->balance,
        ]);
    }

    public function wallet()
    {
        $wallet = auth()->user()->wallet;

        return response()->json([
            'status' => 'success',
            'message' => 'Wallet fetched successfully!',
            'wallet' => $wallet,
        ]);
    }

    public function endChat(Request $request, $chatId)
    {
        try{
            $request->validate([
                'seconds' => 'required|integer|min:0',
                'reason' => 'nullable|string|max:255',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ]);
        }
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

        if ($request->filled('seconds')) {
            $elapsedSeconds = (int) $request->input('seconds', 0);
            $ratePerMinute = $astrologer?->charged_text_price ?? 0;
            $deduction = ($elapsedSeconds / 60) * $ratePerMinute;
        }

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
}
