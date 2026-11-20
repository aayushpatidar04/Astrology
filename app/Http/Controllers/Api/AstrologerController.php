<?php

namespace App\Http\Controllers\Api;

use App\Events\AstrologerStatusChanged;
use App\Events\AstrologerStatusUpdated;
use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Astrologer;
use App\Models\BankDetail;
use App\Models\CallHistory;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\Transaction;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class AstrologerController extends Controller
{
    private function sendNotification(?string $fcmToken, string $title, string $body, array $data): bool
    {
        if (blank($fcmToken)) {
            return false;
        }

        try {
            app(FirebaseNotificationService::class)->sendNotification($fcmToken, $title, $body, $data);

            return true;
        } catch (Throwable $exception) {
            Log::warning('Firebase notification delivery failed.', [
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }
    public function wallet()
    {
        $user = Auth::user();
        $wallet = $user->ensureWallet();

        return response()->json([
            'status' => 'success',
            'wallet' => [
                'balance' => $wallet->balance,
                'transactions' => $wallet->transactions()
                    ->latest()
                    ->paginate(20),
            ],
        ]);
    }

    public function getStatus(Request $request, $id = null)
    {
        $astrologer = $id ? Astrologer::find($id) : Auth::user()?->astrologer;

        if (! $astrologer) {
            return response()->json([
                'status' => 'error',
                'message' => 'Astrologer profile not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'astrologer_id' => $astrologer->id,
            'user_id' => $astrologer->user_id,
            'online' => (bool) $astrologer->online,
            'is_busy' => (bool) $astrologer->user?->is_busy,
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

    public function stats($id)
    {
        $astrologer = $this->authorizedAstrologer($id);
        $astrologerUserId = $astrologer->user_id;
        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        // --- Chats ---
        $todayChats = ChatSession::where('astrologer_id', $astrologerUserId)
            ->whereDate('created_at', $today)
            ->distinct('chat_id')
            ->count('chat_id');

        $monthChats = ChatSession::where('astrologer_id', $astrologerUserId)
            ->whereBetween('created_at', [$monthStart, Carbon::now()])
            ->distinct('chat_id')
            ->count('chat_id');

        // --- Calls ---
        $todayCalls = CallHistory::where('astrologer_id', $astrologerUserId)
            ->whereDate('created_at', $today)
            ->distinct('id')
            ->count('id');

        $monthCalls = CallHistory::where('astrologer_id', $astrologerUserId)
            ->whereBetween('created_at', [$monthStart, Carbon::now()])
            ->distinct('id')
            ->count('id');

        // --- Earnings based on asked prices ---
        // Calls: duration (seconds) → minutes → multiply by asked_call_price
        $callDurationToday = CallHistory::where('astrologer_id', $astrologerUserId)
            ->whereDate('created_at', $today)
            ->sum('duration');

        $callDurationMonth = CallHistory::where('astrologer_id', $astrologerUserId)
            ->whereBetween('created_at', [$monthStart, Carbon::now()])
            ->sum('duration');

        $callEarningToday = ($callDurationToday / 60) * $astrologer->asked_call_price;
        $callEarningMonth = ($callDurationMonth / 60) * $astrologer->asked_call_price;

        // Chats: duration_seconds → minutes → multiply by asked_text_price
        $chatDurationToday = ChatSession::where('astrologer_id', $astrologerUserId)
            ->whereDate('created_at', $today)
            ->sum('duration_seconds');

        $chatDurationMonth = ChatSession::where('astrologer_id', $astrologerUserId)
            ->whereBetween('created_at', [$monthStart, Carbon::now()])
            ->sum('duration_seconds');

        $chatEarningToday = ($chatDurationToday / 60) * $astrologer->asked_text_price;
        $chatEarningMonth = ($chatDurationMonth / 60) * $astrologer->asked_text_price;

        // --- Last 3 consultations (chat or call) ---
        $lastChats = ChatSession::where('astrologer_id', $astrologerUserId)
            ->join('users', 'chat_sessions.user_id', '=', 'users.id')
            ->select(
                'chat_sessions.id',
                'chat_sessions.chat_id',
                'chat_sessions.user_id',
                'users.name as user_name',
                'chat_sessions.duration_seconds as duration',
                'chat_sessions.ended_at',
                'chat_sessions.created_at',
                DB::raw("'chat' as type")
            )
            ->orderBy('chat_sessions.created_at', 'desc')
            ->limit(3);

        $lastCalls = CallHistory::where('astrologer_id', $astrologerUserId)
            ->join('users', 'call_histories.user_id', '=', 'users.id')
            ->select(
                'call_histories.id',
                DB::raw('NULL as chat_id'), // align columns with chat_sessions
                'call_histories.user_id',
                'users.name as user_name',
                'call_histories.duration',
                'call_histories.ended_at',
                'call_histories.created_at',
                DB::raw("'call' as type")
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
                'video_calls' => 0,
                'earning' => round($callEarningToday + $chatEarningToday, 2),
            ],
            'month' => [
                'chats' => $monthChats,
                'calls' => $monthCalls,
                'video_calls' => 0,
                'earning' => round($callEarningMonth + $chatEarningMonth, 2),
            ],
            'last_consultations' => $lastConsultations,
            'astrologer' => $astrologer,
        ]);
    }

    public function storeBankDetails(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'account_holder' => 'required|string',
                'account_number' => 'required|string',
                'ifsc_code' => 'required|string',
                'bank_name' => 'required|string',
                'branch_name' => 'nullable|string',
            ]);
        } catch (ValidationException $exception) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $exception->errors(),
            ], 422);
        }

        $astrologer = $this->authorizedAstrologer($id);

        $details = BankDetail::updateOrCreate(
            ['astrologer_id' => $astrologer->id],
            $validated
        );

        return response()->json(['message' => 'Bank details saved', 'data' => $details]);
    }

    public function getBankDetails($id)
    {
        $astrologer = $this->authorizedAstrologer($id);
        $details = BankDetail::where('astrologer_id', $astrologer->id)->first();

        return response()->json($details);
    }

    public function getAstrologerTransactions($id)
    {
        $astrologer = $this->authorizedAstrologer($id);
        $transactions = Transaction::where('astrologer_id', $astrologer->id)
            ->orderBy('transacted_at', 'desc')
            ->get()
            ->map(function ($txn) {
                $txn->proof_url = $txn->proof ? asset($txn->proof) : null;

                return $txn;
            });

        return response()->json($transactions);
    }

    private function authorizedAstrologer($id): Astrologer
    {
        $astrologer = Auth::user()->astrologer()->with('user')->first();

        abort_unless(
            $astrologer && $astrologer->id === (int) $id,
            404
        );

        return $astrologer;
    }

    public function status(Request $request)
    {
        $request->validate([
            'online' => 'required|boolean',
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
            broadcast(new AstrologerStatusUpdated(
                $astrologer->id,
                (bool) Auth::user()->is_busy,
                (bool) $astrologer->online
            ))->toOthers();

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

    public function storeMessage(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $chat = Chat::findOrFail($id);
        abort_unless($chat->participants()->where('user_id', Auth::id())->exists(), 403);

        $message = ChatMessage::create([
            'chat_id' => $chat->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
        ]);

        broadcast(new MessageSent($message))->toOthers();

        $recipientId = $chat->participants()
            ->where('user_id', '!=', Auth::id())
            ->value('user_id');

        if ($recipientId) {
            $recipient = User::find($recipientId);
            $this->sendNotification(
                $recipient?->device_token,
                Auth::user()->name,
                $message->message,
                [
                    'type' => 'chat_message',
                    'chat_id' => $chat->id,
                    'sender_id' => Auth::id(),
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users')->ignore($request->user()->id)],
            'phone' => ['required', 'numeric', 'digits:10', \Illuminate\Validation\Rule::unique('users')->ignore($request->user()->id)],
        ]);

        $request->user()->fill($validated);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully.',
            'user' => $request->user(),
        ]);
    }

    public function updateAstrologer(Request $request)
    {
        $validated = $request->validate([
            'bio' => ['required', 'string'],
            'expertise' => ['required', 'array'],
            'experience_years' => ['required', 'numeric'],
            'profile_image' => ['nullable'],
            'documents' => ['nullable', 'array'],
            'documents.*.name' => ['required_with:documents', 'string'],
            'documents.*.file' => ['nullable', 'file', 'mimes:pdf,jpg,png', 'max:2048'],
            'documents.*.document_path' => ['nullable', 'string'],
        ]);

        $astrologer = Auth::user()->astrologer;
        if (! $astrologer) {
            return response()->json(['status' => 'error', 'message' => 'Astrologer not found'], 404);
        }

        if ($request->hasFile('profile_image')) {
            $file = $request->profile_image;
            $ext = $file->getClientOriginalExtension();
            $filename = '_' . $request->user()->phone . '_.' . $ext;
            $file->move(public_path('uploads/astrologer_profile/'), $filename);
            $validated['profile_image'] = 'uploads/astrologer_profile/' . $filename;
        }

        $astrologer->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Astrologer profile updated successfully.',
            'astrologer' => $astrologer,
        ]);
    }

    public function calls($id = null)
    {
        $user = Auth::user();
        $astrologer = $user->astrologer;

        $calls = CallHistory::where('astrologer_id', $user->id)
            ->with('user')
            ->orderByDesc('created_at')
            ->get()
            ->unique('user_id')
            ->values();

        $selectedUser = null;
        $chat = null;
        $history = [];

        if ($id) {
            $selectedUser = User::findOrFail($id);
            $chat = Chat::whereHas('participants', function ($q) use ($selectedUser) {
                $q->where('user_id', $selectedUser->id);
            })
            ->whereHas('participants', function ($q) use ($astrologer) {
                $q->where('user_id', $astrologer->user_id);
            })
            ->first();

            $history = CallHistory::where('astrologer_id', $astrologer->user_id)
                ->where('user_id', $selectedUser->id)
                ->get();
        }

        return response()->json([
            'status' => 'success',
            'calls' => $calls,
            'selected_user' => $selectedUser,
            'chat' => $chat,
            'history' => $history,
        ]);
    }

    public function setBusy(Request $request, $id)
    {
        $astrologerUser = User::findOrFail($id);
        $astrologerUser->is_busy = $request->busy ? 1 : 0;
        $astrologerUser->save();

        if ($astrologerUser->astrologer) {
            broadcast(new AstrologerStatusUpdated($astrologerUser->astrologer->id, $astrologerUser->is_busy));
        }

        return response()->json(['is_busy' => $astrologerUser->is_busy]);
    }
}
