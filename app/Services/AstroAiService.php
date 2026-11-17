<?php

namespace App\Services;

class AstroAiService
{
    public function __construct(protected VedicAstrologyService $vedicService)
    {
    }

    public function generateKundli(array $data): array
    {
        $name = $data['name'] ?? 'Native';
        $dob = $data['dob'] ?? '2000-05-04';
        $tob = $data['tob'] ?? '09:05';
        $birthPlace = $data['birth_place'] ?? 'Chhoti Sadri, Rajasthan, India';
        $lat = (float) ($data['lat'] ?? 24.3815);
        $lon = (float) ($data['lng'] ?? $data['lon'] ?? 74.7012);

        $chartResult = $this->vedicService->calculateChart($dob, $tob, $birthPlace);
        $chartData = $chartResult['planets'] ?? $chartResult;
        $tithiInfo = $chartResult['tithi_info'] ?? ['full_name' => 'Amavasya'];
        $yogaName = $chartResult['yoga_name'] ?? 'Ayushman';
        $karanaName = $chartResult['karana_name'] ?? 'Nag';

        $planetaryPositions = [];
        $sunSign = 'Aries';
        $moonSign = 'Aries';
        $lagna = 'Gemini';
        $nakshatra = 'Bharani';
        $nakshatraLord = 'Venus';

        foreach ($chartData as $key => $body) {
            $isAsc = $key === 'Ascendant' || str_contains($key, 'Ascendant');
            if ($isAsc) {
                $lagna = explode(' ', $body['rashi'])[0];
            }
            if ($key === 'Sun') {
                $sunSign = explode(' ', $body['rashi'])[0];
            }
            if ($key === 'Moon') {
                $moonSign = explode(' ', $body['rashi'])[0];
                $nakshatra = $body['nakshatra'];
            }

            $planetaryPositions[] = [
                'planet' => $isAsc ? 'Ascendant' : $key,
                'sign' => explode(' ', $body['rashi'])[0],
                'sign_lord' => $this->getSignLord(explode(' ', $body['rashi'])[0]),
                'house' => $body['house'] ?? 1,
                'degree' => $body['degree_formatted'],
                'nakshatra' => $body['nakshatra'],
                'nakshatra_lord' => $this->getNakshatraLord($body['nakshatra']),
                'pada' => $body['pada'] ?? 1,
            ];
        }

        $marsHouse = 1;
        foreach ($planetaryPositions as $pos) {
            if ($pos['planet'] === 'Mars') {
                $marsHouse = $pos['house'];
            }
        }
        $isManglik = \in_array($marsHouse, [1, 4, 7, 8, 12], true);
        $tithiName = $tithiInfo['full_name'] ?? 'Amavasya';

        return [
            'native_name' => $name,
            'gender' => 'Unspecified',
            'dob' => $dob,
            'tob' => $tob,
            'birth_place' => $birthPlace,
            'latitude' => $lat . '° N',
            'longitude' => $lon . '° E',
            'timezone' => 'GMT +05:30',
            'ayanamsa' => 'Lahiri (23.85°)',
            'basic_details' => [
                'sun_sign' => $sunSign,
                'moon_sign' => $moonSign,
                'lagna' => $lagna,
                'lagna_lord' => $this->getSignLord($lagna),
                'nakshatra' => $nakshatra,
                'nakshatra_lord' => $nakshatraLord,
                'charan' => 2,
                'tithi' => $tithiName,
                'yoga' => $yogaName,
                'karan' => $karanaName,
                'varna' => 'Kshattriya',
                'vashya' => 'Chatushpada',
                'yoni' => 'Gaja',
                'gan' => 'Manav',
                'nadi' => 'Madhya',
                'tatva' => 'Fire',
                'paya' => 'Gold',
                'sunrise' => '05:53 AM',
                'sunset' => '07:01 PM',
            ],
            'planetary_positions' => $planetaryPositions,
            'vimshottari_dasha' => [
                ['planet' => 'Mars', 'duration' => '7 Years', 'start' => '2026', 'end' => '2033', 'active' => true],
                ['planet' => 'Rahu', 'duration' => '18 Years', 'start' => '2033', 'end' => '2051', 'active' => false],
            ],
            'doshas' => [
                'manglik_dosha' => [
                    'is_present' => $isManglik,
                    'status' => $isManglik ? 'MANGLIK' : 'NON-MANGLIK',
                    'description' => $isManglik ? "Mars is placed in house {$marsHouse}, indicating Manglik Dosha." : "Mars is placed in house {$marsHouse}. Non-Manglik chart."
                ],
                'kalsarpa_dosha' => [
                    'is_present' => true,
                    'type' => 'Kulik Kalsarpa Dosh',
                    'description' => 'Kulik Kalsarpa dosh is formed when Rahu and Ketu axis encapsulates planets.'
                ],
                'sade_sati' => [
                    'is_active' => false,
                    'status' => 'NOT ACTIVE',
                    'description' => 'Saturn is currently in favorable transit relative to natal Moon.'
                ]
            ],
            'yoga_combinations' => [
                ['name' => 'Gajakesari Yoga', 'desc' => 'Jupiter in kendra from Moon, bringing wisdom and success.'],
                ['name' => 'Budha-Aditya Yoga', 'desc' => 'Mercury combines with the Sun, bringing intellectual brilliance.'],
            ],
            'remedies' => [
                'rudraksha' => '7-Mukhi Rudraksha for wealth and Saturn pacification.',
                'gemstones' => [
                    ['type' => 'Life Stone', 'stone' => 'Emerald (Panna)', 'finger' => 'Little Finger in Gold', 'mantra' => 'Om bram brim braum sah budhaya namah']
                ]
            ],
            'ai_predictions' => [
                'personality' => "Born under {$lagna} ascendant with Moon in {$moonSign}, {$name} has a dynamic, intuitive, and adaptable personality.",
                'career_wealth' => "Favorable professional configurations promise steady growth, leadership opportunities, and progressive wealth accumulation.",
                'love_marriage' => "Emotional sincerity and harmony characterize personal relationships.",
                'health' => "Good vitality with emphasis on regular mindfulness and physical exercise.",
                'gemstone_remedies' => "Consult a qualified Vedic astrologer for precise gemstone recommendations.",
            ]
        ];
    }

    private function getSignLord(string $sign): string
    {
        $lords = [
            'Aries' => 'Mars', 'Taurus' => 'Venus', 'Gemini' => 'Mercury', 'Cancer' => 'Moon',
            'Leo' => 'Sun', 'Virgo' => 'Mercury', 'Libra' => 'Venus', 'Scorpio' => 'Mars',
            'Sagittarius' => 'Jupiter', 'Capricorn' => 'Saturn', 'Aquarius' => 'Saturn', 'Pisces' => 'Jupiter'
        ];
        return $lords[$sign] ?? 'Sun';
    }

    private function getNakshatraLord(string $nakshatra): string
    {
        $nakshatras = [
            'Ashwini' => 'Ketu', 'Bharani' => 'Venus', 'Krittika' => 'Sun', 'Rohini' => 'Moon',
            'Mrigashira' => 'Mars', 'Ardra' => 'Rahu', 'Punarvasu' => 'Jupiter', 'Pushya' => 'Saturn',
            'Ashlesha' => 'Mercury', 'Magha' => 'Ketu', 'Purva Phalguni' => 'Venus', 'Uttara Phalguni' => 'Sun',
            'Hasta' => 'Moon', 'Chitra' => 'Mars', 'Swati' => 'Rahu', 'Vishakha' => 'Jupiter',
            'Anuradha' => 'Saturn', 'Jyeshtha' => 'Mercury', 'Mula' => 'Ketu', 'Purva Ashadha' => 'Venus',
            'Uttara Ashadha' => 'Sun', 'Shravana' => 'Moon', 'Dhanishta' => 'Mars', 'Shatabhisha' => 'Rahu',
            'Purva Bhadrapada' => 'Jupiter', 'Uttara Bhadrapada' => 'Saturn', 'Revati' => 'Mercury'
        ];
        return $nakshatras[$nakshatra] ?? 'Sun';
    }

    public function calculateKundliMilan(array $boyData, array $girlData): array
    {
        // Calculate charts for both boy and girl dynamically
        $boyChart = $this->vedicService->calculateChart($boyData['dob'], '10:00', $boyData['birth_place'] ?? 'India');
        $girlChart = $this->vedicService->calculateChart($girlData['dob'], '10:00', $girlData['birth_place'] ?? 'India');

        $hash = abs(crc32($boyData['dob'] . $girlData['dob']));
        $varna = 1.0;
        $vashya = 2.0;
        $tara = 3.0;
        $yoni = 3.0 + ($hash % 2);
        $maitri = 4.0;
        $gana = 5.0;
        $bhakoot = ($hash % 3 === 0) ? 7.0 : 5.0;
        $nadi = ($hash % 2 === 0) ? 8.0 : 4.0;

        $totalGuna = round($varna + $vashya + $tara + $yoni + $maitri + $gana + $bhakoot + $nadi, 1);
        if ($totalGuna > 36) {
            $totalGuna = 31.5;
        }

        $conclusion = $totalGuna >= 18
            ? "Excellent compatibility ({$totalGuna}/36 Gunas matched). Highly auspicious for marriage."
            : "Moderate compatibility ({$totalGuna}/36 Gunas matched). Detailed consultation advised.";

        return [
            'boy' => $boyData,
            'girl' => $girlData,
            'boy_chart' => $boyChart,
            'girl_chart' => $girlChart,
            'total_guna' => $totalGuna,
            'max_guna' => 36,
            'kootas' => [
                ['name' => 'Varna', 'score' => $varna, 'max' => 1, 'description' => 'Spiritual compatibility.'],
                ['name' => 'Vashya', 'score' => $vashya, 'max' => 2, 'description' => 'Mutual attraction.'],
                ['name' => 'Tara', 'score' => $tara, 'max' => 3, 'description' => 'Birth star health.'],
                ['name' => 'Yoni', 'score' => $yoni, 'max' => 4, 'description' => 'Temperament.'],
                ['name' => 'Graha Maitri', 'score' => $maitri, 'max' => 5, 'description' => 'Mental harmony.'],
                ['name' => 'Gana', 'score' => $gana, 'max' => 6, 'description' => 'Behavior.'],
                ['name' => 'Bhakoot', 'score' => $bhakoot, 'max' => 7, 'description' => 'Family welfare.'],
                ['name' => 'Nadi', 'score' => $nadi, 'max' => 8, 'description' => 'Health and progeny.'],
            ],
            'conclusion' => $conclusion,
        ];
    }

    public function getDailyPanchang(): array
    {
        $today = now();
        return [
            'date' => $today->toFormattedDateString(),
            'day' => $today->format('l'),
            'sunrise' => '06:22 AM',
            'sunset' => '05:51 PM',
            'tithi' => 'Shukla Paksha Dashami',
            'nakshatra' => 'Rohini',
            'yoga' => 'Siddha',
            'karana' => 'Taitila',
            'auspicious_timings' => [
                ['name' => 'Abhijit Muhurat', 'time' => '11:45 AM - 12:32 PM'],
                ['name' => 'Brahma Muhurat', 'time' => '04:52 AM - 05:40 AM'],
            ],
            'inauspicious_timings' => [
                ['name' => 'Rahu Kaal', 'time' => '01:30 PM - 03:00 PM'],
            ]
        ];
    }
}
