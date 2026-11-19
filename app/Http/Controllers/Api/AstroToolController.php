<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\Location;
use App\Models\User;
use App\Models\UserWallet;
use App\Models\Astrologer;
use App\Models\TarotCard;
use App\Services\AstroAiService;
use App\Services\BabyNameService;
use App\Services\GeminiTarotService;
use App\Services\NumerologyService;
use App\Services\VedicAstrologyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AstroToolController extends Controller
{
    public function __construct(
        protected AstroAiService $astroService,
        protected NumerologyService $numerologyService,
        protected BabyNameService $babyNameService,
        protected GeminiTarotService $tarotService,
        protected VedicAstrologyService $vedicService
    ) {}

    public function kundli(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'dob' => 'required|date',
            'tob' => 'required|string',
            'birth_place' => 'required|string|max:255',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
        ]);

        $kundliData = $this->astroService->generateKundli($validated);

        return response()->json([
            'status' => 'success',
            'kundliData' => $kundliData,
        ]);
    }

    public function matchMaking(Request $request)
    {
        $validated = $request->validate([
            'boy_name' => 'required|string|max:255',
            'boy_dob' => 'required|date',
            'boy_tob' => 'nullable|string',
            'boy_birth_place' => 'nullable|string|max:255',
            'boy_lat' => 'nullable|numeric',
            'boy_lng' => 'nullable|numeric',
            'girl_name' => 'required|string|max:255',
            'girl_dob' => 'required|date',
            'girl_tob' => 'nullable|string',
            'girl_birth_place' => 'nullable|string|max:255',
            'girl_lat' => 'nullable|numeric',
            'girl_lng' => 'nullable|numeric',
        ]);

        $boyData = [
            'name' => $validated['boy_name'],
            'dob' => $validated['boy_dob'],
            'tob' => $validated['boy_tob'] ?? '10:00',
            'birth_place' => $validated['boy_birth_place'] ?? 'India',
            'lat' => $validated['boy_lat'] ?? null,
            'lng' => $validated['boy_lng'] ?? null,
        ];
        $girlData = [
            'name' => $validated['girl_name'],
            'dob' => $validated['girl_dob'],
            'tob' => $validated['girl_tob'] ?? '10:00',
            'birth_place' => $validated['girl_birth_place'] ?? 'India',
            'lat' => $validated['girl_lat'] ?? null,
            'lng' => $validated['girl_lng'] ?? null,
        ];

        $milanData = $this->astroService->calculateKundliMilan($boyData, $girlData);

        return response()->json([
            'status' => 'success',
            'milanData' => $milanData,
        ]);
    }

    public function zodiacCompatibility(Request $request)
    {
        $validated = $request->validate([
            'sign1' => 'required|string|max:30',
            'sign2' => 'required|string|max:30',
            'mode' => 'nullable|in:love,friendship',
        ]);

        $result = $this->astroService->calculateZodiacCompatibility(
            $validated['sign1'],
            $validated['sign2'],
            $validated['mode'] ?? 'love'
        );

        return response()->json([
            'status' => 'success',
            'compatibilityData' => $result,
        ]);
    }

    public function panchang(Request $request)
    {
        $date = $request->input('date', now('Asia/Kolkata')->format('Y-m-d'));
        $city = $request->input('city', 'Jaipur, Rajasthan, India');
        $lat = (float) $request->input('lat', 26.9124);
        $lng = (float) $request->input('lng', 75.7873);

        $panchangData = $this->astroService->getDailyPanchang($date, $city, $lat, $lng);

        return response()->json([
            'status' => 'success',
            'panchangData' => $panchangData,
        ]);
    }

    public function numerology(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'dob' => 'required|date',
            'system' => 'nullable|in:chaldean,pythagorean',
        ]);

        $numerologyData = $this->numerologyService->calculateNumerology(
            $validated['name'],
            $validated['dob'],
            $validated['system'] ?? 'chaldean'
        );

        return response()->json([
            'status' => 'success',
            'numerologyData' => $numerologyData,
        ]);
    }

    public function babyNames(Request $request)
    {
        $validated = $request->validate([
            'dob' => 'required|date',
            'tob' => 'required|string',
            'birth_place' => 'required|string|max:255',
            'gender' => 'required|in:boy,girl,unisex',
            'style' => 'nullable|string|max:60',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
        ]);

        $babyNameData = $this->babyNameService->generateBabyNames(
            $validated['dob'],
            $validated['tob'],
            $validated['birth_place'],
            $validated['gender'],
            $validated['style'] ?? 'Modern & Sanskrit',
            isset($validated['lat']) ? (float) $validated['lat'] : null,
            isset($validated['lng']) ? (float) $validated['lng'] : null
        );

        return response()->json([
            'status' => 'success',
            'babyNameData' => $babyNameData,
        ]);
    }

    public function sunSignCalculator(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:120',
            'gender'      => 'required|in:male,female,other',
            'dob'         => 'required|date',
            'tob'         => 'required|string',
            'birth_place' => 'required|string|max:255',
            'lat'         => 'nullable|numeric',
            'lng'         => 'nullable|numeric',
        ]);

        $chart = $this->astroService->generateKundli([
            'name'        => $validated['name'],
            'dob'         => $validated['dob'],
            'tob'         => $validated['tob'],
            'birth_place' => $validated['birth_place'],
            'lat'         => $validated['lat'] ?? 24.3815,
            'lng'         => $validated['lng'] ?? 74.7012,
        ]);

        $vedicSunSign = $chart['basic_details']['sun_sign'] ?? 'Aries';

        return response()->json([
            'status' => 'success',
            'sunSignData' => [
                'native_name'   => $validated['name'],
                'vedic_sun_sign' => $vedicSunSign,
                'chart' => $chart,
            ],
        ]);
    }

    public function risingSignCalculator(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:120',
            'gender'      => 'required|in:male,female,other',
            'dob'         => 'required|date',
            'tob'         => 'required|string',
            'birth_place' => 'required|string|max:255',
            'lat'         => 'nullable|numeric',
            'lng'         => 'nullable|numeric',
        ]);

        $risingSignData = $this->vedicService->calculateRisingSign(
            $validated['name'],
            $validated['gender'],
            $validated['dob'],
            $validated['tob'],
            $validated['birth_place'],
            isset($validated['lat']) ? (float) $validated['lat'] : null,
            isset($validated['lng']) ? (float) $validated['lng'] : null
        );

        return response()->json([
            'status' => 'success',
            'risingSignData' => $risingSignData,
        ]);
    }

    public function rashiCalculator(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:120',
            'gender'      => 'required|in:male,female,other',
            'dob'         => 'required|date',
            'tob'         => 'required|string',
            'birth_place' => 'required|string|max:255',
            'lat'         => 'nullable|numeric',
            'lng'         => 'nullable|numeric',
        ]);

        $rashiData = $this->vedicService->calculateRashi(
            $validated['name'],
            $validated['gender'],
            $validated['dob'],
            $validated['tob'],
            $validated['birth_place'],
            isset($validated['lat']) ? (float) $validated['lat'] : null,
            isset($validated['lng']) ? (float) $validated['lng'] : null
        );

        return response()->json([
            'status' => 'success',
            'rashiData' => $rashiData,
        ]);
    }

    public function loveCalculator(Request $request)
    {
        $validated = $request->validate([
            'name1' => 'required|string|max:100',
            'gender1' => 'required|in:male,female,other',
            'name2' => 'required|string|max:100',
            'gender2' => 'required|in:male,female,other',
        ]);

        $loveData = $this->numerologyService->calculateLoveMatch(
            $validated['name1'],
            $validated['gender1'],
            $validated['name2'],
            $validated['gender2']
        );

        return response()->json([
            'status' => 'success',
            'loveData' => $loveData,
        ]);
    }

    public function tarotReading(Request $request)
    {
        $validated = $request->validate([
            'spread_type' => 'required|in:single,three_card,love,career',
            'category' => 'required|string|max:50',
            'question' => 'nullable|string|max:300',
            'chosen_cards' => 'required|array',
            'chosen_cards.*' => 'integer|exists:tarot_cards,id',
        ]);

        $readingData = $this->tarotService->generateReading(
            $validated['spread_type'],
            $validated['category'],
            $validated['question'] ?? null,
            $validated['chosen_cards']
        );

        return response()->json([
            'status' => 'success',
            'readingData' => $readingData,
        ]);
    }

    public function atmakarakaDarakaraka(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:120',
            'gender'      => 'required|in:male,female,other',
            'dob'         => 'required|date',
            'tob'         => 'required|string',
            'birth_place' => 'required|string|max:255',
            'lat'         => 'nullable|numeric',
            'lng'         => 'nullable|numeric',
        ]);

        $karakaData = $this->vedicService->calculateCharaKarakas(
            $validated['name'],
            $validated['gender'],
            $validated['dob'],
            $validated['tob'],
            $validated['birth_place'],
            isset($validated['lat']) ? (float) $validated['lat'] : null,
            isset($validated['lng']) ? (float) $validated['lng'] : null
        );

        return response()->json([
            'status' => 'success',
            'karakaData' => $karakaData,
        ]);
    }

    public function dashaCalculator(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:120',
            'gender'      => 'required|in:male,female,other',
            'dob'         => 'required|date',
            'tob'         => 'required|string',
            'birth_place' => 'required|string|max:255',
            'lat'         => 'nullable|numeric',
            'lng'         => 'nullable|numeric',
        ]);

        $dashaData = $this->vedicService->calculateDetailedDashaReport(
            $validated['name'],
            $validated['gender'],
            $validated['dob'],
            $validated['tob'],
            $validated['birth_place'],
            isset($validated['lat']) ? (float) $validated['lat'] : null,
            isset($validated['lng']) ? (float) $validated['lng'] : null
        );

        return response()->json([
            'status' => 'success',
            'dashaData' => $dashaData,
        ]);
    }

    public function nakshatraCalculator(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:120',
            'gender'      => 'required|in:male,female,other',
            'dob'         => 'required|date',
            'tob'         => 'required|string',
            'birth_place' => 'required|string|max:255',
            'lat'         => 'nullable|numeric',
            'lng'         => 'nullable|numeric',
        ]);

        $nakshatraData = $this->vedicService->calculateNakshatraReport(
            $validated['name'],
            $validated['gender'],
            $validated['dob'],
            $validated['tob'],
            $validated['birth_place'],
            isset($validated['lat']) ? (float) $validated['lat'] : null,
            isset($validated['lng']) ? (float) $validated['lng'] : null
        );

        return response()->json([
            'status' => 'success',
            'nakshatraData' => $nakshatraData,
        ]);
    }

    public function manglikCalculator(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:120',
            'gender'      => 'required|in:male,female,other',
            'dob'         => 'required|date',
            'tob'         => 'required|string',
            'birth_place' => 'required|string|max:255',
            'lat'         => 'nullable|numeric',
            'lng'         => 'nullable|numeric',
        ]);

        $manglikData = $this->astroService->generateManglikReport(
            $validated['name'],
            $validated['gender'],
            $validated['dob'],
            $validated['tob'],
            $validated['birth_place'],
            isset($validated['lat']) ? (float) $validated['lat'] : null,
            isset($validated['lng']) ? (float) $validated['lng'] : null
        );

        return response()->json([
            'status' => 'success',
            'manglikData' => $manglikData,
        ]);
    }

    public function sadeSatiCalculator(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:120',
            'gender'      => 'required|in:male,female,other',
            'dob'         => 'required|date',
            'tob'         => 'required|string',
            'birth_place' => 'required|string|max:255',
            'lat'         => 'nullable|numeric',
            'lng'         => 'nullable|numeric',
        ]);

        $sadeSatiData = $this->astroService->generateSadeSatiReport(
            $validated['name'],
            $validated['gender'],
            $validated['dob'],
            $validated['tob'],
            $validated['birth_place'],
            isset($validated['lat']) ? (float) $validated['lat'] : null,
            isset($validated['lng']) ? (float) $validated['lng'] : null
        );

        return response()->json([
            'status' => 'success',
            'sadeSatiData' => $sadeSatiData,
        ]);
    }

    public function choghadiya(Request $request)
    {
        $date = $request->input('date', now('Asia/Kolkata')->format('Y-m-d'));
        $city = $request->input('city', 'Jaipur, Rajasthan, India');
        $lat  = (float) $request->input('lat', 26.9124);
        $lng  = (float) $request->input('lng', 75.7873);

        $choghadiyaData = $this->astroService->getChoghadiyaTimetable($date, $city, $lat, $lng);

        return response()->json([
            'status' => 'success',
            'choghadiyaData' => $choghadiyaData,
        ]);
    }

    public function muhuratFinder(Request $request)
    {
        $eventType = $request->input('event_type', 'business');
        $date      = $request->input('date', now('Asia/Kolkata')->format('Y-m-d'));
        $city      = $request->input('city', 'Jaipur, Rajasthan, India');
        $lat       = (float) $request->input('lat', 26.9124);
        $lng       = (float) $request->input('lng', 75.7873);

        $muhuratData = $this->astroService->calculateMuhuratFinder($eventType, $date, $city, $lat, $lng);

        return response()->json([
            'status' => 'success',
            'muhuratData' => $muhuratData,
        ]);
    }

    public function aiAstrologerInit(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = $user->load('wallet');
        $ratePerMin = 5.00;

        $acharyaUser = User::firstOrCreate(
            ['email' => 'acharya.ai@myastrosathi.com'],
            [
                'name' => 'Acharya AI Astrologer',
                'password' => bcrypt('secret123'),
            ]
        );

        Astrologer::firstOrCreate(
            ['user_id' => $acharyaUser->id],
            [
                'bio' => 'Expert Vedic Astrologer powered by Advanced AI. Available 24/7 for instant consultations based on your Janma Kundli.',
                'expertise' => json_encode(['Vedic', 'Kundli', 'Jaimini', 'Dasha', 'AI Jyotish']),
                'experience_years' => 100,
                'profile_image' => 'images/acharya_ai.png',
                'charged_text_price' => $ratePerMin,
                'asked_text_price' => $ratePerMin,
                'online' => 1,
                'status' => 'verified',
            ]
        );

        $recentSession = ChatSession::where('user_id', $user->id)
            ->where('astrologer_id', $acharyaUser->id)
            ->whereNull('ended_at')
            ->where('updated_at', '>=', now()->subMinutes(30))
            ->latest('id')
            ->first();

        if (!$recentSession) {
            $chat = Chat::create(['name' => 'ai_chat_' . $user->id . '_' . time()]);
            $recentSession = ChatSession::create([
                'chat_id' => $chat->getKey(),
                'session_reference' => (string) \Illuminate\Support\Str::uuid(),
                'user_id' => $user->id,
                'astrologer_id' => $acharyaUser->getKey(),
            ]);
        } else {
            $chat = Chat::find($recentSession->chat_id);
            if (!$chat) {
                $chat = Chat::create(['name' => 'ai_chat_' . $user->id . '_' . time()]);
                $recentSession->chat_id = $chat->getKey();
                $recentSession->save();
            }
        }

        $messages = ChatMessage::where('chat_id', $chat->getKey())->orderBy('created_at')->get();

        if ($messages->isEmpty()) {
            $welcomeMsg = ChatMessage::create([
                'chat_id' => $chat->getKey(),
                'user_id' => $acharyaUser->getKey(),
                'type' => 'text',
                'message' => "Namaste, {$user->name}! I am Acharya AI, your personal Vedic Astrologer. I have accessed your Janma Kundli context. Ask me anything about your career, marriage, health, or life path. (Rate: ₹{$ratePerMin}/min)",
            ]);
            $messages = collect([$welcomeMsg]);
        }

        return response()->json([
            'chat_id' => $chat->getKey(),
            'session_reference' => $recentSession->session_reference,
            'messages' => $messages,
            'balance' => $user->wallet?->balance ?? 0.00,
            'rate_per_min' => $ratePerMin,
            'acharya_id' => $acharyaUser->getKey(),
        ]);
    }

    public function aiAstrologerSend(Request $request)
    {
        $request->validate([
            'chat_id' => 'required|exists:chats,id',
            'message' => 'required|string|max:500',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = $user->load('wallet');
        $ratePerMin = 5.00;

        if (!$user->wallet || $user->wallet->balance < ($ratePerMin / 4)) {
            return response()->json([
                'error' => 'Insufficient wallet balance. Please recharge to continue chatting with Acharya AI.',
                'balance' => $user->wallet?->balance ?? 0
            ], 402);
        }

        $acharyaUser = User::where('email', 'acharya.ai@myastrosathi.com')->first();
        $acharyaId = $acharyaUser?->getKey() ?? 0;

        $chatId = $request->input('chat_id');
        $userMessage = $request->input('message');

        ChatMessage::create([
            'chat_id' => $chatId,
            'user_id' => $user->getKey(),
            'type' => 'text',
            'message' => $userMessage,
        ]);

        $history = ChatMessage::where('chat_id', $chatId)->orderBy('created_at')->get()->map(function($m) use ($user, $acharyaId) {
            return [
                'sender' => $m->user_id === $user->getKey() ? 'user' : 'ai',
                'text' => $m->message,
            ];
        })->toArray();

        $aiReply = $this->astroService->chatWithAiAstrologer($userMessage, $history);

        $replyMsg = ChatMessage::create([
            'chat_id' => $chatId,
            'user_id' => $acharyaId,
            'type' => 'text',
            'message' => $aiReply,
        ]);

        ChatSession::where('chat_id', $chatId)->where('user_id', $user->getKey())->whereNull('ended_at')->update(['updated_at' => now()]);

        return response()->json([
            'message' => $replyMsg,
            'reply' => $aiReply,
            'balance' => $user->wallet->balance,
        ]);
    }

    public function aiAstrologerSettle(Request $request)
    {
        $request->validate([
            'chat_id' => 'required|exists:chats,id',
            'elapsed_seconds' => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = $user->load('wallet');
        $ratePerMin = 5.00;
        $elapsedSeconds = (int) $request->input('elapsed_seconds');
        $fractionalDeduction = round(($elapsedSeconds / 60) * $ratePerMin, 2);

        $wallet = UserWallet::where('user_id', $user->getKey())->first();
        if ($wallet && $fractionalDeduction > 0) {
            $deduction = min($fractionalDeduction, (float) $wallet->balance);
            if ($deduction > 0) {
                $wallet->decrement('balance', $deduction);
            }
        }

        ChatSession::where('chat_id', $request->input('chat_id'))
            ->where('user_id', $user->getKey())
            ->whereNull('ended_at')
            ->increment('duration_seconds', $elapsedSeconds);

        $wallet->refresh();

        return response()->json([
            'balance' => $wallet->balance,
            'deducted' => $fractionalDeduction,
        ]);
    }

    public function searchCities(Request $request)
    {
        $query = $request->input('key', '');
        if (\strlen($query) < 2) {
            return response()->json(['data' => []]);
        }

        try {
            $response = Http::withHeaders([
                'Origin' => 'https://astrotalk.com',
                'Referer' => 'https://astrotalk.com/',
                'User-Agent' => 'Mozilla/5.0 (Linux; Android 16; Pixel 10) AppleWebKit/537.36',
            ])->get('https://api.supportchat.astrotalk.com/AstroChat/cities/allcountries/autocomplete', [
                'limit' => 10,
                'key' => $query,
            ]);

            $data = $response->json();
            $cities = $data['data'] ?? [];

            foreach ($cities as $city) {
                if (!empty($city['name']) && isset($city['latitude'], $city['longitude'])) {
                    Location::updateOrCreate(
                        ['city_name' => $city['name']],
                        [
                            'latitude' => $city['latitude'],
                            'longitude' => $city['longitude'],
                            'country' => $city['country'] ?? 'India',
                        ]
                    );
                }
            }

            return response()->json(['data' => $cities]);
        } catch (\Exception $e) {
            return response()->json(['data' => []]);
        }
    }
}
