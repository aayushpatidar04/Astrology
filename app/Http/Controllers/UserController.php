<?php

namespace App\Http\Controllers;

use App\Events\CallSignal;
use App\Events\MessageSent;
use App\Models\Astrologer;
use App\Models\CallHistory;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\Order;
use App\Models\RechargePackage;
use App\Models\TempOrder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
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

    public function start(Request $request)
    {
        $roomId = (int)$request->roomId;

        // Broadcast signal for real-time channel updates
        broadcast(new CallSignal(
            $roomId,
            'call_started',
            null,
            Auth::id()
        ))->toOthers();

        // Create call history record
        $chat = Chat::find($roomId);
        if ($chat) {
            $recipientId = $chat->participants()
                ->where('user_id', '!=', Auth::id())
                ->value('user_id');

            if ($recipientId) {
                $callHistory = CallHistory::create([
                    'user_id' => Auth::id(),
                    'astrologer_id' => $recipientId,
                    'call_type' => 'voice',
                    'status' => 'ringing',
                    'started_at' => now(),
                ]);

                // Send Beams push to astrologer from this chat room
                $beamsClient = new PushNotifications([
                    'instanceId' => config('app.VITE_PUSHER_BEAMS_INSTANCE_ID'),
                    'secretKey' => config('app.VITE_PUSHER_BEAMS_SECRET_KEY'),
                ]);

                $beamsClient->publishToUsers(
                    [(string) $recipientId],
                    [
                        'web' => [
                            'notification' => [
                                'title' => 'Incoming Call',
                                'body' => 'A user has started a call. Tap to join.',
                                'icon' => asset(Auth::user()?->details?->profile_image ?: '/images/favicon.png'),
                                'deep_link' => route('astrologer.call.show', ['id' => Auth::id()]),
                                'data' => [
                                    'type' => 'incoming_call',
                                    'chatId' => $chat->id,
                                    'callerId' => Auth::id(),
                                ],
                            ],
                        ],
                    ]
                );
            }
        }

        return response()->json(['status' => 'ok', 'call_history' => $callHistory ?? null]);
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

        return Inertia::render('User/Recharge', [
            'firstTimeOffers' => $firstTimeOffers,
            'regularOffers'   => $regularOffers,
            'specialOffers'   => $specialOffers,
            'walletBalance'   => $user->wallet->balance,
        ]);
    }

    public function phonePe(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:recharge_packages,id',
            'amount' => 'required|numeric',
            'bonus_amount' => 'nullable|numeric',
            'gst' => 'required|numeric',
            'total_payable' => 'required|numeric',
            'coupon_code' => 'nullable',
        ]);
        $user = auth()->user();

        $tempOrder = TempOrder::create([
            'user_id'        => $user->id,
            'phone'        => $user->phone,
            'recharge_package_id'     => $request->package_id,
            'amount'         => $request->amount,
            'bonus_amount'   => $request->bonus_amount ?? 0,
            'payable_amount'   => $request->total_payable ?? 0,
            'status'         => 'pending',
            'payment_gateway' => 'phonepe',
            'transaction_ref' => uniqid('txn_'),
        ]);
        $data = [
            'merchantId' => 'PGTESTPAYUAT86',
            'merchantTransactionId' => $tempOrder->transaction_ref,
            'merchantUserId' => 'MUID' . $user->id,
            'amount' => intval($request->total_payable * 100),
            'redirectUrl' => route('user.response', ['id' => $tempOrder->id]),
            'redirectMode' => 'GET',
            'callbackUrl' => route('user.response', ['id' => $tempOrder->id]),
            'mobileNumber' => $user->phone ?? '9999999999',
            'paymentInstrument' => [
                'type' => 'PAY_PAGE',
            ],
        ];

        // Encode request
        $encode = base64_encode(json_encode($data));

        // Salt key and index from PhonePe sandbox
        $saltKey   = '96434309-7796-489d-8924-ab56988a6076';
        $saltIndex = 1;

        // Create string to hash
        $string = $encode . '/pg/v1/pay' . $saltKey;
        $sha256 = hash('sha256', $string);

        // Final header
        $finalXHeader = $sha256 . '###' . $saltIndex;

        // Sandbox URL
        $url = "https://api-preprod.phonepe.com/apis/pg-sandbox/pg/v1/pay";

        // Send request using Laravel HTTP client
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-VERIFY'     => $finalXHeader,
        ])->post($url, [
            'request' => $encode,
        ]);

        $rData = $response->json();

        return response()->json([
            'redirect_url' => $rData['data']['instrumentResponse']['redirectInfo']['url'],
        ]);
    }

    public function talkToAstrologers()
    {
        $astrologers = Astrologer::with('user')->where('online', 1)->paginate(20);
        return Inertia::render('Astrologers/Index2', [
            'user' => Auth::user()?->load(['details', 'wallet']),
            'astrologers' => $astrologers,
        ]);
    }

    public function showCall($id)
    {
        $user = auth()->user()->load('wallet');
        $astrologer = Astrologer::with('user')->findOrFail($id);

        $chat = Chat::whereHas('participants', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->whereHas('participants', function ($q) use ($astrologer) {
                $q->where('user_id', $astrologer->user->id);
            })
            ->first();

        $ratePerMinute = $astrologer->charged_call_price;
        $minRequiredBalance = $ratePerMinute * 4; // 4 minutes minimum

        if ($user->wallet->balance < $minRequiredBalance) {
            // Redirect to recharge page or show a message
            return redirect()->route('user.chat-with-astrologers')
                ->with('error', 'You need at least 4 minutes balance to start a call.');
        }
        $history = CallHistory::where('user_id', $user->id)->where('astrologer_id', $astrologer->user_id)->get();
        return Inertia::render('User/CallWindow', [
            'auth'     => ['user' => $user],
            'chat' => $chat,
            'history'     => $history,
            'astrologer' => $astrologer,
        ]);
    }

    public function callHistory()
    {
        $history = CallHistory::with(['user', 'astrologer'])->where('user_id', auth()->id())->latest()->get();
        return Inertia::render('User/CallHistory', [
            'history' => $history,
        ]);
    }

    public function transactions()
    {
        $transactions = Order::where('user_id', Auth::id())->latest()->get();
        return Inertia::render('User/Transactions', [
            'transactions' => $transactions
        ]);
    }
}
