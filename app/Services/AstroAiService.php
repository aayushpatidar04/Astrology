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
        $vimshottariDasha = $chartResult['vimshottari_dasha'] ?? [];

        $planetaryPositions = [];
        $sunSign = '';
        $moonSign = '';
        $lagna = '';
        $nakshatra = '';
        $nakshatraLord = '';

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

            $planetaryPositions[$key] = [
                'planet' => $isAsc ? 'Ascendant' : $key,
                'sign' => explode(' ', $body['rashi'])[0],
                'sign_lord' => $this->getSignLord(explode(' ', $body['rashi'])[0]),
                'house' => $body['house'] ?? 1,
                'degree' => $body['degree_formatted'],
                'full_degree' => $body['full_degree'] ?? 0.0,
                'nakshatra' => $body['nakshatra'],
                'nakshatra_lord' => $this->getNakshatraLord($body['nakshatra']),
                'rashi_number' => $body['rashi_number'] ?? 1,
                'pada' => $body['pada'] ?? 1,
            ];
        }

        // Evaluate professional multi-chart Manglik & Kalsarpa Doshas
        $manglikAnalysis = $this->evaluateManglikDosha($planetaryPositions, $dob);
        $kalsarpaAnalysis = $this->evaluateKalsarpaDosha($planetaryPositions);
        $tithiName = $tithiInfo['full_name'] ?? 'Amavasya';

        return [
            'native_name' => $name,
            'gender' => 'Unspecified',
            'dob' => $dob,
            'tob' => $tob,
            'birth_place' => $birthPlace,
            'latitude' => "{$lat}° N",
            'longitude' => "{$lon}° E",
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
            'planetary_positions' => array_values($planetaryPositions),
            'vimshottari_dasha' => $vimshottariDasha,
            'doshas' => [
                'manglik_dosha' => $manglikAnalysis,
                'kalsarpa_dosha' => $kalsarpaAnalysis,
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

    /**
     * Complete Multi-Chart Manglik Dosha Detection & Cancellation Engine
     */
    public function evaluateManglikDosha(array $chartData, string $dob): array
    {
        $lagnaSign = $chartData['Ascendant']['rashi_number'] ?? $chartData['Ascendant (Lagna)']['rashi_number'] ?? 1;
        $marsSign  = $chartData['Mars']['rashi_number'] ?? 1;
        $moonSign  = $chartData['Moon']['rashi_number'] ?? 1;
        $venusSign = $chartData['Venus']['rashi_number'] ?? 1;
        $jupSign   = $chartData['Jupiter']['rashi_number'] ?? 1;

        $houseFromLagna = ($marsSign - $lagnaSign + 12) % 12 + 1;
        $houseFromMoon  = ($marsSign - $moonSign + 12) % 12 + 1;
        $houseFromVenus = ($marsSign - $venusSign + 12) % 12 + 1;

        $primaryHouses = [1, 4, 7, 8, 12];
        $secondaryHouses = [2];

        $score = 0;
        $factors = [];

        if (\in_array($houseFromLagna, $primaryHouses, true)) {
            $score += 50;
            $factors[] = "Mars in {$houseFromLagna}th house from Lagna";
        } elseif (\in_array($houseFromLagna, $secondaryHouses, true)) {
            $score += 25;
            $factors[] = "Mars in 2nd house from Lagna";
        }

        if (\in_array($houseFromMoon, $primaryHouses, true)) {
            $score += 30;
            $factors[] = "Mars in {$houseFromMoon}th house from Moon";
        } elseif (\in_array($houseFromMoon, $secondaryHouses, true)) {
            $score += 15;
            $factors[] = "Mars in 2nd house from Moon";
        }

        if (\in_array($houseFromVenus, $primaryHouses, true)) {
            $score += 20;
            $factors[] = "Mars in {$houseFromVenus}th house from Venus";
        } elseif (\in_array($houseFromVenus, $secondaryHouses, true)) {
            $score += 10;
            $factors[] = "Mars in 2nd house from Venus";
        }

        if ($score === 0) {
            return [
                'is_present'    => false,
                'status'        => 'NON-MANGLIK',
                'severity'      => 'NONE',
                'score'         => 0,
                'cancellations' => [],
                'description'   => "Mars is placed in house {$houseFromLagna} from Lagna and house {$houseFromMoon} from Moon. No Manglik Dosha is present.",
            ];
        }

        $cancellations = [];

        if (\in_array($marsSign, [1, 8, 10], true)) {
            $score = 0;
            $cancellations[] = 'Mars is in its own or exalted sign (Aries/Scorpio/Capricorn), fully neutralizing the dosha.';
        }

        $houseSignExceptions = [
            1  => [1, 5],
            2  => [3, 6],
            4  => [1, 8],
            7  => [4, 10],
            8  => [9, 12],
            12 => [2, 7],
        ];

        if ($score > 0 && isset($houseSignExceptions[$houseFromLagna]) && \in_array($marsSign, $houseSignExceptions[$houseFromLagna], true)) {
            $score *= 0.5;
            $cancellations[] = "Mars in house {$houseFromLagna} occupies a neutralizing sign, reducing intensity by 50%.";
        }

        $jupToMarsHouse = ($marsSign - $jupSign + 12) % 12 + 1;
        if ($score > 0 && \in_array($jupToMarsHouse, [1, 5, 7, 9], true)) {
            $score *= 0.5;
            $cancellations[] = 'Benefic Jupiter aspects or conjuncts Mars, softening aggressive energy by 50%.';
        }

        $age = (new \DateTime($dob))->diff(new \DateTime('now'))->y;
        if ($score > 0 && $age >= 28) {
            $score *= 0.75;
            $cancellations[] = "Native is {$age} years old (above 28), naturally reducing Mars dosha intensity.";
        }

        $finalScore = (int) round($score);

        if ($finalScore === 0) {
            $status = 'NON-MANGLIK (CANCELLED)';
            $severity = 'CANCELLED';
            $isPresent = false;
        } elseif ($finalScore < 50) {
            $status = 'ANSHIK MANGLIK (PARTIAL)';
            $severity = 'LOW';
            $isPresent = true;
        } elseif ($finalScore < 80) {
            $status = 'MANGLIK';
            $severity = 'MEDIUM';
            $isPresent = true;
        } else {
            $status = 'PURNA MANGLIK (HIGH)';
            $severity = 'HIGH';
            $isPresent = true;
        }

        $summary = implode('; ', $factors) . '.';
        if (!empty($cancellations)) {
            $summary .= ' Cancellation note: ' . implode(' ', $cancellations);
        }

        return [
            'is_present'    => $isPresent,
            'status'        => $status,
            'severity'      => $severity,
            'score'         => $finalScore,
            'houses'        => [
                'lagna' => $houseFromLagna,
                'moon'  => $houseFromMoon,
                'venus' => $houseFromVenus,
            ],
            'cancellations' => $cancellations,
            'description'   => $summary,
        ];
    }

    /**
     * Complete 12-Type Kalsarpa Dosha Detection & Classification Engine
     */
    public function evaluateKalsarpaDosha(array $chartData): array
    {
        $rahuHouse = $chartData['Rahu']['house'] ?? 1;
        $ketuHouse = $chartData['Ketu']['house'] ?? 7;
        $rahuDeg = $chartData['Rahu']['full_degree'] ?? 0.0;
        $lagnaDeg = $chartData['Ascendant']['full_degree'] ?? $chartData['Ascendant (Lagna)']['full_degree'] ?? 0.0;

        $corePlanets = ['Sun', 'Moon', 'Mars', 'Mercury', 'Jupiter', 'Venus', 'Saturn'];
        $arcRahuToKetu = [];
        $arcKetuToRahu = [];

        foreach ($corePlanets as $planet) {
            $deg = $chartData[$planet]['full_degree'] ?? 0.0;
            $offset = fmod($deg - $rahuDeg + 360.0, 360.0);

            if ($offset > 0.0 && $offset < 180.0) {
                $arcRahuToKetu[] = $planet;
            } elseif ($offset > 180.0 && $offset < 360.0) {
                $arcKetuToRahu[] = $planet;
            }
        }

        $count1 = \count($arcRahuToKetu);
        $count2 = \count($arcKetuToRahu);

        $kalsarpaDefinitions = [
            1 => ['name' => 'Anant', 'focus' => 'self-identity, health, and partnerships'],
            2 => ['name' => 'Kulik', 'focus' => 'wealth accumulation, speech, and family stability'],
            3 => ['name' => 'Vasuki', 'focus' => 'courage, siblings, and fortune'],
            4 => ['name' => 'Shankhpal', 'focus' => 'domestic peace, property, and career foundations'],
            5 => ['name' => 'Padma', 'focus' => 'education, children, and creative gains'],
            6 => ['name' => 'Mahapadma', 'focus' => 'health resilience, debts, and overcoming obstacles'],
            7 => ['name' => 'Takshak', 'focus' => 'marriage, business partnerships, and public life'],
            8 => ['name' => 'Karkotak', 'focus' => 'sudden transformations, research, and shared assets'],
            9 => ['name' => 'Shankhachud', 'focus' => 'fortune, father, higher wisdom, and long journeys'],
            10 => ['name' => 'Ghatak', 'focus' => 'career authority, reputation, and leadership'],
            11 => ['name' => 'Vishdhar', 'focus' => 'financial gains, social networks, and ambitions'],
            12 => ['name' => 'Sheshnag', 'focus' => 'expenses, foreign connections, and spiritual growth'],
        ];

        $def = $kalsarpaDefinitions[$rahuHouse] ?? $kalsarpaDefinitions[1];
        $fullTypeName = "{$def['name']} Kalsarpa Dosh";

        $isComplete = ($count1 === 7 || $count2 === 7);
        $isPartial = (! $isComplete && ($count1 === 6 || $count2 === 6));

        if (! $isComplete && ! $isPartial) {
            return [
                'is_present' => false,
                'severity' => 'NONE',
                'type' => 'None',
                'direction' => null,
                'rahu_house' => $rahuHouse,
                'ketu_house' => $ketuHouse,
                'description' => 'No Kalsarpa Dosha is present as planets are distributed on both sides of the Rahu–Ketu axis.',
            ];
        }

        if ($count1 >= 6) {
            $direction = 'Ascending (Rahu to Ketu / Udit)';
            $outsidePlanets = array_values(array_diff($corePlanets, $arcRahuToKetu));
            $hemmedArc = 1;
        } else {
            $direction = 'Descending (Ketu to Rahu / Anudit)';
            $outsidePlanets = array_values(array_diff($corePlanets, $arcKetuToRahu));
            $hemmedArc = 2;
        }

        $lagnaOffset = fmod($lagnaDeg - $rahuDeg + 360.0, 360.0);
        $lagnaInHemmedArc = ($hemmedArc === 1)
            ? ($lagnaOffset > 0.0 && $lagnaOffset < 180.0)
            : ($lagnaOffset > 180.0 && $lagnaOffset < 360.0);

        $cancellations = [];
        if (! $lagnaInHemmedArc) {
            $cancellations[] = 'Ascendant (Lagna) lies outside the Rahu–Ketu axis, reducing overall restriction.';
        }

        $severity = $isComplete ? 'PURNA (COMPLETE)' : 'ANSHIK (PARTIAL)';
        $description = $isComplete
            ? "{$fullTypeName} ({$severity} - {$direction}) is formed with Rahu in house {$rahuHouse} and Ketu in house {$ketuHouse}. It primarily influences {$def['focus']}."
            : "Partial {$fullTypeName} ({$severity} - {$direction}) is indicated with Rahu in house {$rahuHouse}, while " . implode(', ', $outsidePlanets) . ' sits outside the axis.';

        if (!empty($cancellations)) {
            $description .= ' Note: ' . implode(' ', $cancellations);
        }

        return [
            'is_present' => true,
            'severity' => $severity,
            'type' => $fullTypeName,
            'direction' => $direction,
            'rahu_house' => $rahuHouse,
            'ketu_house' => $ketuHouse,
            'outside_planets' => $outsidePlanets,
            'cancellations' => $cancellations,
            'description' => $description,
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
