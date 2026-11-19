<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Services\AstroAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class AstroToolController extends Controller
{
    public function __construct(protected AstroAiService $astroService)
    {
    }

    public function kundli(Request $request)
    {
        $kundliData = null;
        if ($request->filled('dob')) {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'dob' => 'required|date',
                'tob' => 'required|string',
                'birth_place' => 'required|string|max:255',
                'lat' => 'nullable|numeric',
                'lng' => 'nullable|numeric',
            ]);

            if ($request->filled('lat') && $request->filled('lng')) {
                $cityOnly = trim(explode(',', $validated['birth_place'])[0]);
                Location::updateOrCreate(
                    ['city_name' => $cityOnly],
                    [
                        'latitude' => (float) $request->input('lat'),
                        'longitude' => (float) $request->input('lng'),
                        'country' => 'India',
                    ]
                );
            }

            $kundliData = $this->astroService->generateKundli($validated);
        }

        $user = Auth::user();
        if ($user) {
            $user = $user->load('wallet');
        }

        return Inertia::render('Tools/Kundli', [
            'kundliData' => $kundliData,
            'filters' => $request->only(['name', 'dob', 'tob', 'birth_place', 'lat', 'lng']),
            'user' => $user,
        ]);
    }

    public function matchMaking(Request $request)
    {
        $milanData = null;
        if ($request->filled('boy_dob') && $request->filled('girl_dob')) {
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
        }

        $user = Auth::user();
        if ($user) {
            $user = $user->load('wallet');
        }

        return Inertia::render('Tools/MatchMaking', [
            'milanData' => $milanData,
            'filters' => $request->all(),
            'user' => $user,
        ]);
    }

    public function zodiacCompatibility(Request $request)
    {
        $result = null;

        if ($request->filled('sign1') && $request->filled('sign2')) {
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
        }

        $user = Auth::user();
        if ($user) {
            $user = $user->load('wallet');
        }

        return Inertia::render('Tools/ZodiacCompatibility', [
            'compatibilityData' => $result,
            'filters' => $request->only(['sign1', 'sign2', 'mode']),
            'user' => $user,
        ]);
    }

    public function panchang(Request $request)
    {
        $date = $request->input('date', now('Asia/Kolkata')->format('Y-m-d'));
        $city = $request->input('city', 'Jaipur, Rajasthan, India');
        $lat = (float) $request->input('lat', 26.9124);
        $lng = (float) $request->input('lng', 75.7873);

        if ($request->filled('lat') && $request->filled('lng')) {
            $cityOnly = trim(explode(',', $city)[0]);
            Location::updateOrCreate(
                ['city_name' => $cityOnly],
                [
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'country' => 'India',
                ]
            );
        }

        $panchangData = $this->astroService->getDailyPanchang($date, $city, $lat, $lng);

        $user = Auth::user();
        if ($user) {
            $user = $user->load('wallet');
        }

        return Inertia::render('Tools/Panchang', [
            'panchangData' => $panchangData,
            'filters' => $request->all(),
            'user' => $user,
        ]);
    }

    public function numerology(Request $request, \App\Services\NumerologyService $numerologyService)
    {
        $numerologyData = null;

        if ($request->filled('name') && $request->filled('dob')) {
            $validated = $request->validate([
                'name' => 'required|string|max:120',
                'dob' => 'required|date',
                'system' => 'nullable|in:chaldean,pythagorean',
            ]);

            $numerologyData = $numerologyService->calculateNumerology(
                $validated['name'],
                $validated['dob'],
                $validated['system'] ?? 'chaldean'
            );
        }

        $user = Auth::user();
        if ($user) {
            $user = $user->load('wallet');
        }

        return Inertia::render('Tools/Numerology', [
            'numerologyData' => $numerologyData,
            'filters' => $request->only(['name', 'dob', 'system']),
            'user' => $user,
        ]);
    }

    public function babyNames(Request $request, \App\Services\BabyNameService $babyNameService)
    {
        $babyNameData = null;

        if ($request->filled('dob')) {
            $validated = $request->validate([
                'dob' => 'required|date',
                'tob' => 'required|string',
                'birth_place' => 'required|string|max:255',
                'gender' => 'required|in:boy,girl,unisex',
                'style' => 'nullable|string|max:60',
                'lat' => 'nullable|numeric',
                'lng' => 'nullable|numeric',
            ]);

            if ($request->filled('lat') && $request->filled('lng')) {
                $cityOnly = trim(explode(',', $validated['birth_place'])[0]);
                Location::updateOrCreate(
                    ['city_name' => $cityOnly],
                    [
                        'latitude' => (float) $request->input('lat'),
                        'longitude' => (float) $request->input('lng'),
                        'country' => 'India',
                    ]
                );
            }

            $babyNameData = $babyNameService->generateBabyNames(
                $validated['dob'],
                $validated['tob'],
                $validated['birth_place'],
                $validated['gender'],
                $validated['style'] ?? 'Modern & Sanskrit',
                isset($validated['lat']) ? (float) $validated['lat'] : null,
                isset($validated['lng']) ? (float) $validated['lng'] : null
            );
        }

        $user = Auth::user();
        if ($user) {
            $user = $user->load('wallet');
        }

        return Inertia::render('Tools/BabyNames', [
            'babyNameData' => $babyNameData,
            'filters' => $request->only(['dob', 'tob', 'birth_place', 'gender', 'style', 'lat', 'lng']),
            'user' => $user,
        ]);
    }

    public function sunSignCalculator(Request $request)
    {
        $sunSignData = null;

        if ($request->filled('dob')) {
            $validated = $request->validate([
                'name'        => 'required|string|max:120',
                'gender'      => 'required|in:male,female,other',
                'dob'         => 'required|date',
                'tob'         => 'required|string',
                'birth_place' => 'required|string|max:255',
                'lat'         => 'nullable|numeric',
                'lng'         => 'nullable|numeric',
            ]);

            if ($request->filled('lat') && $request->filled('lng')) {
                $cityOnly = trim(explode(',', $validated['birth_place'])[0]);
                Location::updateOrCreate(
                    ['city_name' => $cityOnly],
                    [
                        'latitude'  => (float) $request->input('lat'),
                        'longitude' => (float) $request->input('lng'),
                        'country'   => 'India',
                    ]
                );
            }

            $chart = $this->astroService->generateKundli([
                'name'        => $validated['name'],
                'dob'         => $validated['dob'],
                'tob'         => $validated['tob'],
                'birth_place' => $validated['birth_place'],
                'lat'         => $validated['lat'] ?? 24.3815,
                'lng'         => $validated['lng'] ?? 74.7012,
            ]);

            $vedicSunSign = $chart['basic_details']['sun_sign'] ?? 'Aries';

            $dt = new \DateTime($validated['dob']);
            $m = (int) $dt->format('n');
            $d = (int) $dt->format('j');

            $westernSigns = [
                ['name' => 'Capricorn', 'start_m' => 12, 'start_d' => 22, 'end_m' => 1, 'end_d' => 19, 'element' => 'Earth', 'ruler' => 'Saturn'],
                ['name' => 'Aquarius',  'start_m' => 1,  'start_d' => 20, 'end_m' => 2, 'end_d' => 18, 'element' => 'Air', 'ruler' => 'Uranus'],
                ['name' => 'Pisces',    'start_m' => 2,  'start_d' => 19, 'end_m' => 3, 'end_d' => 20, 'element' => 'Water', 'ruler' => 'Neptune'],
                ['name' => 'Aries',     'start_m' => 3,  'start_d' => 21, 'end_m' => 4, 'end_d' => 19, 'element' => 'Fire', 'ruler' => 'Mars'],
                ['name' => 'Taurus',    'start_m' => 4,  'start_d' => 20, 'end_m' => 5, 'end_d' => 20, 'element' => 'Earth', 'ruler' => 'Venus'],
                ['name' => 'Gemini',    'start_m' => 5,  'start_d' => 21, 'end_m' => 6, 'end_d' => 20, 'element' => 'Air', 'ruler' => 'Mercury'],
                ['name' => 'Cancer',    'start_m' => 6,  'start_d' => 21, 'end_m' => 7, 'end_d' => 22, 'element' => 'Water', 'ruler' => 'Moon'],
                ['name' => 'Leo',       'start_m' => 7,  'start_d' => 23, 'end_m' => 8, 'end_d' => 22, 'element' => 'Fire', 'ruler' => 'Sun'],
                ['name' => 'Virgo',     'start_m' => 8,  'start_d' => 23, 'end_m' => 9, 'end_d' => 22, 'element' => 'Earth', 'ruler' => 'Mercury'],
                ['name' => 'Libra',     'start_m' => 9,  'start_d' => 23, 'end_m' => 10, 'end_d' => 22, 'element' => 'Air', 'ruler' => 'Venus'],
                ['name' => 'Scorpio',   'start_m' => 10, 'start_d' => 23, 'end_m' => 11, 'end_d' => 21, 'element' => 'Water', 'ruler' => 'Pluto'],
                ['name' => 'Sagittarius','start_m' => 11, 'start_d' => 22, 'end_m' => 12, 'end_d' => 21, 'element' => 'Fire', 'ruler' => 'Jupiter'],
            ];

            $western = $westernSigns[3];
            foreach ($westernSigns as $ws) {
                if ($ws['start_m'] === $ws['end_m']) {
                    if ($m === $ws['start_m'] && $d >= $ws['start_d'] && $d <= $ws['end_d']) {
                        $western = $ws;
                        break;
                    }
                } else {
                    if (($m === $ws['start_m'] && $d >= $ws['start_d']) || ($m === $ws['end_m'] && $d <= $ws['end_d'])) {
                        $western = $ws;
                        break;
                    }
                }
            }

            $sunSignData = [
                'native_name'   => $validated['name'],
                'formatted_dob' => $dt->format('d F Y'),
                'birth_place'   => $validated['birth_place'],
                'western'       => [
                    'name'    => $western['name'],
                    'dates'   => "{$western['start_m']}/{$western['start_d']} - {$western['end_m']}/{$western['end_d']}",
                    'element' => $western['element'],
                    'ruler'   => $western['ruler'],
                ],
                'vedic'         => [
                    'name'    => $vedicSunSign,
                    'dates'   => 'Sidereal Calculations',
                    'element' => 'Varies by Degree',
                    'ruler'   => 'Surya',
                ],
                'profile_note'  => "Your Sun is positioned in {$vedicSunSign} under Vedic Nirayana calculations and {$western['name']} under Western Tropical calculations, highlighting leadership, vitality, and core purposeful drive.",
            ];
        }

        $user = Auth::user();
        if ($user) {
            $user = $user->load('wallet');
        }

        return Inertia::render('Tools/SunSignCalculator', [
            'sunSignData' => $sunSignData,
            'filters'     => $request->all(),
            'user'        => $user,
        ]);
    }

    public function risingSignCalculator(Request $request, \App\Services\VedicAstrologyService $vedicService)
    {
        $risingSignData = null;

        if ($request->filled('dob')) {
            $validated = $request->validate([
                'name'        => 'required|string|max:120',
                'gender'      => 'required|in:male,female,other',
                'dob'         => 'required|date',
                'tob'         => 'required|string',
                'birth_place' => 'required|string|max:255',
                'lat'         => 'nullable|numeric',
                'lng'         => 'nullable|numeric',
            ]);

            if ($request->filled('lat') && $request->filled('lng')) {
                $cityOnly = trim(explode(',', $validated['birth_place'])[0]);
                Location::updateOrCreate(
                    ['city_name' => $cityOnly],
                    [
                        'latitude'  => (float) $request->input('lat'),
                        'longitude' => (float) $request->input('lng'),
                        'country'   => 'India',
                    ]
                );
            }

            $risingSignData = $vedicService->calculateRisingSign(
                $validated['name'],
                $validated['gender'],
                $validated['dob'],
                $validated['tob'],
                $validated['birth_place'],
                isset($validated['lat']) ? (float) $validated['lat'] : null,
                isset($validated['lng']) ? (float) $validated['lng'] : null
            );
        }

        $user = Auth::user();
        if ($user) {
            $user = $user->load('wallet');
        }

        return Inertia::render('Tools/RisingSignCalculator', [
            'risingSignData' => $risingSignData,
            'filters'        => $request->all(),
            'user'           => $user,
        ]);
    }

    public function rashiCalculator(Request $request, \App\Services\VedicAstrologyService $vedicService)
    {
        $rashiData = null;

        if ($request->filled('dob')) {
            $validated = $request->validate([
                'name'        => 'required|string|max:120',
                'gender'      => 'required|in:male,female,other',
                'dob'         => 'required|date',
                'tob'         => 'required|string',
                'birth_place' => 'required|string|max:255',
                'lat'         => 'nullable|numeric',
                'lng'         => 'nullable|numeric',
            ]);

            if ($request->filled('lat') && $request->filled('lng')) {
                $cityOnly = trim(explode(',', $validated['birth_place'])[0]);
                Location::updateOrCreate(
                    ['city_name' => $cityOnly],
                    [
                        'latitude'  => (float) $request->input('lat'),
                        'longitude' => (float) $request->input('lng'),
                        'country'   => 'India',
                    ]
                );
            }

            $rashiData = $vedicService->calculateRashi(
                $validated['name'],
                $validated['gender'],
                $validated['dob'],
                $validated['tob'],
                $validated['birth_place'],
                isset($validated['lat']) ? (float) $validated['lat'] : null,
                isset($validated['lng']) ? (float) $validated['lng'] : null
            );
        }

        $user = Auth::user();
        if ($user) {
            $user = $user->load('wallet');
        }

        return Inertia::render('Tools/RashiCalculator', [
            'rashiData' => $rashiData,
            'filters'   => $request->all(),
            'user'      => $user,
        ]);
    }

    public function loveCalculator(Request $request, \App\Services\NumerologyService $numerologyService)
    {
        $loveData = null;

        if ($request->filled('name1') && $request->filled('name2')) {
            $validated = $request->validate(array(
                'name1' => 'required|string|max:100',
                'gender1' => 'required|in:male,female,other',
                'name2' => 'required|string|max:100',
                'gender2' => 'required|in:male,female,other',
            ));

            $loveData = $numerologyService->calculateLoveMatch(
                $validated['name1'],
                $validated['gender1'],
                $validated['name2'],
                $validated['gender2']
            );
        }

        $user = Auth::user();
        if ($user) {
            $user = $user->load('wallet');
        }

        return Inertia::render('Tools/LoveCalculator', array(
            'loveData' => $loveData,
            'filters' => $request->only(array('name1', 'gender1', 'name2', 'gender2')),
            'user' => $user,
        ));
    }

    public function tarotReading(Request $request, \App\Services\GeminiTarotService $tarotService)
    {
        $readingData = null;

        if ($request->has('spread_type') && $request->has('chosen_cards')) {
            $validated = $request->validate([
                'spread_type' => 'required|in:single,three_card,love,career',
                'category' => 'required|string|max:50',
                'question' => 'nullable|string|max:300',
                'chosen_cards' => 'required|array',
                'chosen_cards.*' => 'integer|exists:tarot_cards,id',
            ]);

            $readingData = $tarotService->generateReading(
                $validated['spread_type'],
                $validated['category'],
                $validated['question'] ?? null,
                $validated['chosen_cards']
            );
        }

        $allCards = \App\Models\TarotCard::select('id', 'name', 'arcana', 'suit')->get();
        $user = Auth::user();
        if ($user) {
            $user = $user->load('wallet');
        }

        return Inertia::render('Tools/TarotReading', [
            'readingData' => $readingData,
            'allCards' => $allCards,
            'filters' => $request->only(['spread_type', 'category', 'question']),
            'user' => $user,
        ]);
    }

    public function atmakarakaDarakaraka(Request $request, \App\Services\VedicAstrologyService $vedicService)
    {
        $karakaData = null;

        if ($request->filled('dob')) {
            $validated = $request->validate([
                'name'        => 'required|string|max:120',
                'gender'      => 'required|in:male,female,other',
                'dob'         => 'required|date',
                'tob'         => 'required|string',
                'birth_place' => 'required|string|max:255',
                'lat'         => 'nullable|numeric',
                'lng'         => 'nullable|numeric',
            ]);

            if ($request->filled('lat') && $request->filled('lng')) {
                $cityOnly = trim(explode(',', $validated['birth_place'])[0]);
                Location::updateOrCreate(
                    ['city_name' => $cityOnly],
                    [
                        'latitude'  => (float) $request->input('lat'),
                        'longitude' => (float) $request->input('lng'),
                        'country'   => 'India',
                    ]
                );
            }

            $karakaData = $vedicService->calculateCharaKarakas(
                $validated['name'],
                $validated['gender'],
                $validated['dob'],
                $validated['tob'],
                $validated['birth_place'],
                isset($validated['lat']) ? (float) $validated['lat'] : null,
                isset($validated['lng']) ? (float) $validated['lng'] : null
            );
        }

        $user = Auth::user();
        if ($user) {
            $user = $user->load('wallet');
        }

        return Inertia::render('Tools/AtmakarakaDarakaraka', [
            'karakaData' => $karakaData,
            'filters'    => $request->all(),
            'user'       => $user,
        ]);
    }

    public function dashaCalculator(Request $request, \App\Services\VedicAstrologyService $vedicService)
    {
        $dashaData = null;

        if ($request->filled('dob')) {
            $validated = $request->validate([
                'name'        => 'required|string|max:120',
                'gender'      => 'required|in:male,female,other',
                'dob'         => 'required|date',
                'tob'         => 'required|string',
                'birth_place' => 'required|string|max:255',
                'lat'         => 'nullable|numeric',
                'lng'         => 'nullable|numeric',
            ]);

            if ($request->filled('lat') && $request->filled('lng')) {
                $cityOnly = trim(explode(',', $validated['birth_place'])[0]);
                Location::updateOrCreate(
                    ['city_name' => $cityOnly],
                    [
                        'latitude'  => (float) $request->input('lat'),
                        'longitude' => (float) $request->input('lng'),
                        'country'   => 'India',
                    ]
                );
            }

            $dashaData = $vedicService->calculateDetailedDashaReport(
                $validated['name'],
                $validated['gender'],
                $validated['dob'],
                $validated['tob'],
                $validated['birth_place'],
                isset($validated['lat']) ? (float) $validated['lat'] : null,
                isset($validated['lng']) ? (float) $validated['lng'] : null
            );
        }

        $user = Auth::user();
        if ($user) {
            $user = $user->load('wallet');
        }

        return Inertia::render('Tools/DashaCalculator', [
            'dashaData' => $dashaData,
            'filters'   => $request->all(),
            'user'      => $user,
        ]);
    }

    public function nakshatraCalculator(Request $request, \App\Services\VedicAstrologyService $vedicService)
    {
        $nakshatraData = null;

        if ($request->filled('dob')) {
            $validated = $request->validate([
                'name'        => 'required|string|max:120',
                'gender'      => 'required|in:male,female,other',
                'dob'         => 'required|date',
                'tob'         => 'required|string',
                'birth_place' => 'required|string|max:255',
                'lat'         => 'nullable|numeric',
                'lng'         => 'nullable|numeric',
            ]);

            if ($request->filled('lat') && $request->filled('lng')) {
                $cityOnly = trim(explode(',', $validated['birth_place'])[0]);
                Location::updateOrCreate(
                    ['city_name' => $cityOnly],
                    [
                        'latitude'  => (float) $request->input('lat'),
                        'longitude' => (float) $request->input('lng'),
                        'country'   => 'India',
                    ]
                );
            }

            $nakshatraData = $vedicService->calculateNakshatraReport(
                $validated['name'],
                $validated['gender'],
                $validated['dob'],
                $validated['tob'],
                $validated['birth_place'],
                isset($validated['lat']) ? (float) $validated['lat'] : null,
                isset($validated['lng']) ? (float) $validated['lng'] : null
            );
        }

        $user = Auth::user();
        if ($user) {
            $user = $user->load('wallet');
        }

        return Inertia::render('Tools/NakshatraCalculator', [
            'nakshatraData' => $nakshatraData,
            'filters'       => $request->all(),
            'user'          => $user,
        ]);
    }

    public function manglikCalculator(Request $request)
    {
        $manglikData = null;

        if ($request->filled('dob')) {
            $validated = $request->validate([
                'name'        => 'required|string|max:120',
                'gender'      => 'required|in:male,female,other',
                'dob'         => 'required|date',
                'tob'         => 'required|string',
                'birth_place' => 'required|string|max:255',
                'lat'         => 'nullable|numeric',
                'lng'         => 'nullable|numeric',
            ]);

            if ($request->filled('lat') && $request->filled('lng')) {
                $cityOnly = trim(explode(',', $validated['birth_place'])[0]);
                Location::updateOrCreate(
                    ['city_name' => $cityOnly],
                    [
                        'latitude'  => (float) $request->input('lat'),
                        'longitude' => (float) $request->input('lng'),
                        'country'   => 'India',
                    ]
                );
            }

            $manglikData = $this->astroService->generateManglikReport(
                $validated['name'],
                $validated['gender'],
                $validated['dob'],
                $validated['tob'],
                $validated['birth_place'],
                isset($validated['lat']) ? (float) $validated['lat'] : null,
                isset($validated['lng']) ? (float) $validated['lng'] : null
            );
        }

        $user = Auth::user();
        if ($user) {
            $user = $user->load('wallet');
        }

        return Inertia::render('Tools/ManglikCalculator', [
            'manglikData' => $manglikData,
            'filters'     => $request->all(),
            'user'        => $user,
        ]);
    }

    public function sadeSatiCalculator(Request $request)
    {
        $sadeSatiData = null;

        if ($request->filled('dob')) {
            $validated = $request->validate([
                'name'        => 'required|string|max:120',
                'gender'      => 'required|in:male,female,other',
                'dob'         => 'required|date',
                'tob'         => 'required|string',
                'birth_place' => 'required|string|max:255',
                'lat'         => 'nullable|numeric',
                'lng'         => 'nullable|numeric',
            ]);

            if ($request->filled('lat') && $request->filled('lng')) {
                $cityOnly = trim(explode(',', $validated['birth_place'])[0]);
                Location::updateOrCreate(
                    ['city_name' => $cityOnly],
                    [
                        'latitude'  => (float) $request->input('lat'),
                        'longitude' => (float) $request->input('lng'),
                        'country'   => 'India',
                    ]
                );
            }

            $sadeSatiData = $this->astroService->generateSadeSatiReport(
                $validated['name'],
                $validated['gender'],
                $validated['dob'],
                $validated['tob'],
                $validated['birth_place'],
                isset($validated['lat']) ? (float) $validated['lat'] : null,
                isset($validated['lng']) ? (float) $validated['lng'] : null
            );
        }

        $user = Auth::user();
        if ($user) {
            $user = $user->load('wallet');
        }

        return Inertia::render('Tools/SadeSatiCalculator', [
            'sadeSatiData' => $sadeSatiData,
            'filters'      => $request->all(),
            'user'         => $user,
        ]);
    }

    public function choghadiya(Request $request)
    {
        $date = $request->input('date', now('Asia/Kolkata')->format('Y-m-d'));
        $city = $request->input('city', 'Jaipur, Rajasthan, India');
        $lat  = (float) $request->input('lat', 26.9124);
        $lng  = (float) $request->input('lng', 75.7873);

        $choghadiyaData = $this->astroService->getChoghadiyaTimetable($date, $city, $lat, $lng);

        $user = Auth::user();
        if ($user) {
            $user = $user->load('wallet');
        }

        return Inertia::render('Tools/Choghadiya', array(
            'choghadiyaData' => $choghadiyaData,
            'filters'        => array(
                'date' => $date,
                'city' => $city,
                'lat'  => $lat,
                'lng'  => $lng,
            ),
            'user' => $user,
        ));
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
                        [
                            'city_name' => $city['name'],
                            'state' => $city['state'] ?? null,
                        ],
                        [
                            'country' => $city['countryName'] ?? 'India',
                            'latitude' => (float) $city['latitude'],
                            'longitude' => (float) $city['longitude'],
                        ]
                    );
                }
            }

            return response()->json($data);
        } catch (\Exception) {
            return response()->json(['data' => []]);
        }
    }

    // public function sunSignCalculator(Request $request, \App\Services\VedicAstrologyService $vedicService)
    // {
    //     $sunSignData = null;

    //     if ($request->filled('dob')) {
    //         $validated = $request->validate(array(
    //             'name' => 'required|string|max:120',
    //             'gender' => 'required|in:male,female,other',
    //             'dob' => 'required|date',
    //             'tob' => 'required|string',
    //             'birth_place' => 'required|string|max:255',
    //             'lat' => 'nullable|numeric',
    //             'lng' => 'nullable|numeric',
    //         ));

    //         if ($request->filled('lat') && $request->filled('lng')) {
    //             $cityOnly = trim(explode(',', $validated['birth_place'])[0]);
    //             Location::updateOrCreate(
    //                 ['city_name' => $cityOnly],
    //                 [
    //                     'latitude' => (float) $request->input('lat'),
    //                     'longitude' => (float) $request->input('lng'),
    //                     'country' => 'India',
    //                 ]
    //             );
    //         }

    //         $sunSignData = $vedicService->calculateSunSign(
    //             $validated['name'],
    //             $validated['gender'],
    //             $validated['dob'],
    //             $validated['tob'],
    //             $validated['birth_place'],
    //             isset($validated['lat']) ? (float) $validated['lat'] : null,
    //             isset($validated['lng']) ? (float) $validated['lng'] : null
    //         );
    //     }

    //     $user = Auth::user();
    //     if ($user) {
    //         $user = $user->load('wallet');
    //     }

    //     return Inertia::render('Tools/SunSignCalculator', array(
    //         'sunSignData' => $sunSignData,
    //         'filters' => $request->only(array('name', 'gender', 'dob', 'tob', 'birth_place', 'lat', 'lng')),
    //         'user' => $user,
    //     ));
    // }

}
