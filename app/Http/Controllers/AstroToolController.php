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
                Location::updateOrCreate(
                    ['city_name' => trim($validated['birth_place'])],
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
        if ($request->filled('boy_dob')) {
            $validated = $request->validate([
                'boy_name' => 'required|string|max:255',
                'boy_dob' => 'required|date',
                'girl_name' => 'required|string|max:255',
                'girl_dob' => 'required|date',
            ]);

            $boyData = [
                'name' => $validated['boy_name'],
                'dob' => $validated['boy_dob'],
            ];
            $girlData = [
                'name' => $validated['girl_name'],
                'dob' => $validated['girl_dob'],
            ];

            $milanData = $this->astroService->calculateKundliMilan($boyData, $girlData);
        }

        $user = Auth::user();
        if ($user) {
            $user = $user->load('wallet');
        }

        return Inertia::render('Tools/MatchMaking', [
            'milanData' => $milanData,
            'filters' => $request->only(['boy_name', 'boy_dob', 'girl_name', 'girl_dob']),
            'user' => $user,
        ]);
    }

    public function panchang()
    {
        $panchangData = $this->astroService->getDailyPanchang();

        $user = Auth::user();
        if ($user) {
            $user = $user->load('wallet');
        }

        return Inertia::render('Tools/Panchang', [
            'panchangData' => $panchangData,
            'user' => $user,
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
}
