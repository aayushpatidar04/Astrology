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
        $avakhada = $chartResult['avakhada'] ?? [];
        $sunrise = $chartResult['sunrise'] ?? '06:00 AM';
        $sunset = $chartResult['sunset'] ?? '06:30 PM';
        $ayanamsaStr = $chartResult['ayanamsa_formatted'] ?? 'Lahiri (23.85°)';

        $planetaryPositions = [];
        $sunSign = 'Aries';
        $moonSign = 'Aries';
        $lagna = 'Gemini';
        $nakshatra = 'Bharani';
        $nakshatraLord = 'Venus';
        $moonPada = 1;
        $moonFullDegree = 0.0;

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
                $nakshatraLord = $body['nakshatra_lord'];
                $moonPada = $body['pada'] ?? 1;
                $moonFullDegree = $body['full_degree'] ?? 0.0;
            }

            $planetaryPositions[$key] = [
                'planet' => $isAsc ? 'Ascendant' : $key,
                'sign' => explode(' ', $body['rashi'])[0],
                'sign_lord' => $this->getSignLord(explode(' ', $body['rashi'])[0]),
                'house' => $body['house'] ?? 1,
                'degree' => $body['degree_formatted'],
                'full_degree' => $body['full_degree'] ?? 0.0,
                'nakshatra' => $body['nakshatra'],
                'nakshatra_lord' => $body['nakshatra_lord'],
                'rashi_number' => $body['rashi_number'] ?? 1,
                'pada' => $body['pada'] ?? 1,
            ];
        }

        // Evaluate professional multi-chart Manglik, Kalsarpa, Sade Sati & Yogas
        $manglikAnalysis = $this->evaluateManglikDosha($planetaryPositions, $dob);
        $kalsarpaAnalysis = $this->evaluateKalsarpaDosha($planetaryPositions);
        $sadeSatiAnalysis = $this->evaluateSadeSati($moonFullDegree, $moonSign);
        $yogaCombinations = $this->evaluateYogas($planetaryPositions);
        $tithiName = $tithiInfo['full_name'] ?? 'Amavasya';

        $doshasData = array(
            'manglik_dosha'  => $manglikAnalysis,
            'kalsarpa_dosha' => $kalsarpaAnalysis,
            'sade_sati'      => $sadeSatiAnalysis,
        );

        $lagnaLord = $this->getSignLord($lagna);

        $aiInsights = $this->generateKundliAiInsights(
            $name,
            $lagna,
            $lagnaLord,
            $moonSign,
            $sunSign,
            $nakshatra,
            (int) $moonPada,
            $planetaryPositions,
            $vimshottariDasha,
            $doshasData,
            $yogaCombinations
        );

        return [
            'native_name' => $name,
            'gender' => 'Unspecified',
            'dob' => $dob,
            'tob' => $tob,
            'birth_place' => $birthPlace,
            'latitude' => "{$lat}° N",
            'longitude' => "{$lon}° E",
            'timezone' => 'GMT +05:30',
            'ayanamsa' => $ayanamsaStr,
            'basic_details' => [
                'sun_sign' => $sunSign,
                'moon_sign' => $moonSign,
                'lagna' => $lagna,
                'lagna_lord' => $lagnaLord,
                'nakshatra' => $nakshatra,
                'nakshatra_lord' => $nakshatraLord,
                'charan' => $moonPada,
                'tithi' => $tithiName,
                'yoga' => $yogaName,
                'karan' => $karanaName,
                'varna' => $avakhada['varna'] ?? 'Brahmin',
                'vashya' => $avakhada['vashya'] ?? 'Manav',
                'yoni' => $avakhada['yoni'] ?? 'Ashwa',
                'gan' => $avakhada['gan'] ?? 'Dev',
                'nadi' => $avakhada['nadi'] ?? 'Adi',
                'tatva' => $avakhada['tatva'] ?? 'Fire',
                'paya' => $avakhada['paya'] ?? 'Gold',
                'sunrise' => $sunrise,
                'sunset' => $sunset,
            ],
            'planetary_positions' => array_values($planetaryPositions),
            'vimshottari_dasha' => $vimshottariDasha,
            'doshas'            => $doshasData,
            'yoga_combinations' => $yogaCombinations,
            'remedies'          => $aiInsights['remedies'],
            'ai_predictions'    => $aiInsights['ai_predictions'],
        ];
    }

    /**
     * Dynamically detect major Vedic Yogas from calculated chart data
     */
    public function evaluateYogas(array $chartData): array
    {
        $yogas = [];

        $lagnaSign = $chartData['Ascendant']['rashi_number'] ?? $chartData['Ascendant (Lagna)']['rashi_number'] ?? 1;
        $sunSign = $chartData['Sun']['rashi_number'] ?? 1;
        $moonSign = $chartData['Moon']['rashi_number'] ?? 1;
        $marsSign = $chartData['Mars']['rashi_number'] ?? 1;
        $mercSign = $chartData['Mercury']['rashi_number'] ?? 1;
        $jupSign = $chartData['Jupiter']['rashi_number'] ?? 1;
        $venSign = $chartData['Venus']['rashi_number'] ?? 1;
        $satSign = $chartData['Saturn']['rashi_number'] ?? 1;

        $marsHouse = $chartData['Mars']['house'] ?? 1;
        $mercHouse = $chartData['Mercury']['house'] ?? 1;
        $jupHouse = $chartData['Jupiter']['house'] ?? 1;
        $venHouse = $chartData['Venus']['house'] ?? 1;
        $satHouse = $chartData['Saturn']['house'] ?? 1;

        $rashiRulers = [
            1 => 'Mars',
            2 => 'Venus',
            3 => 'Mercury',
            4 => 'Moon',
            5 => 'Sun',
            6 => 'Mercury',
            7 => 'Venus',
            8 => 'Mars',
            9 => 'Jupiter',
            10 => 'Saturn',
            11 => 'Saturn',
            12 => 'Jupiter'
        ];

        // 1. Gajakesari Yoga (Jupiter in 1, 4, 7, 10 from Moon)
        $jupFromMoon = (($jupSign - $moonSign + 12) % 12) + 1;
        if (\in_array($jupFromMoon, [1, 4, 7, 10], true)) {
            $yogas[] = [
                'name' => 'Gajakesari Yoga',
                'desc' => "Jupiter is in the {$jupFromMoon}th house from Moon (Kendra), bestowing wisdom, respect, leadership, and lasting reputation.",
            ];
        }

        // 2. Budha-Aditya Yoga (Sun + Mercury conjunction)
        if ($sunSign === $mercSign) {
            $yogas[] = [
                'name' => 'Budha-Aditya Yoga',
                'desc' => "Sun and Mercury combine in sign {$sunSign}, sharpening analytical intelligence, communication skills, and administrative ability.",
            ];
        }

        // 3. Chandra-Mangal Yoga (Moon + Mars conjunction or 7th mutual aspect)
        if ($moonSign === $marsSign || abs($moonSign - $marsSign) === 6) {
            $yogas[] = [
                'name' => 'Chandra-Mangal Yoga',
                'desc' => 'Moon and Mars form a direct connection, generating strong financial drive, entrepreneurial energy, and self-earned prosperity.',
            ];
        }

        // 4. Pancha Mahapurusha Yogas (Planet in Kendra 1,4,7,10 in Own/Exalted Sign)
        $kendras = [1, 4, 7, 10];
        if (\in_array($marsHouse, $kendras, true) && \in_array($marsSign, [1, 8, 10], true)) {
            $yogas[] = ['name' => 'Ruchaka Mahapurusha Yoga', 'desc' => 'Mars is strong in a Kendra house, conferring courage, physical vitality, and commanding leadership.'];
        }
        if (\in_array($mercHouse, $kendras, true) && \in_array($mercSign, [3, 6], true)) {
            $yogas[] = ['name' => 'Bhadra Mahapurusha Yoga', 'desc' => 'Mercury is strong in a Kendra house, giving sharp intellect, eloquence, and business acumen.'];
        }
        if (\in_array($jupHouse, $kendras, true) && \in_array($jupSign, [4, 9, 12], true)) {
            $yogas[] = ['name' => 'Hamsa Mahapurusha Yoga', 'desc' => 'Jupiter is strong in a Kendra house, bringing ethical wisdom, spiritual grace, and good fortune.'];
        }
        if (\in_array($venHouse, $kendras, true) && \in_array($venSign, [2, 7, 12], true)) {
            $yogas[] = ['name' => 'Malavya Mahapurusha Yoga', 'desc' => 'Venus is strong in a Kendra house, granting artistic refinement, charm, luxury, and harmonious relationships.'];
        }
        if (\in_array($satHouse, $kendras, true) && \in_array($satSign, [7, 10, 11], true)) {
            $yogas[] = ['name' => 'Sasha Mahapurusha Yoga', 'desc' => 'Saturn is strong in a Kendra house, providing discipline, organizational mastery, and enduring authority.'];
        }

        // 5. Dharma-Karma Adhipati Raja Yoga (9th Lord + 10th Lord conjunction)
        $ninthRashi = (($lagnaSign - 1 + 8) % 12) + 1;
        $tenthRashi = (($lagnaSign - 1 + 9) % 12) + 1;
        $ninthLord = $rashiRulers[$ninthRashi];
        $tenthLord = $rashiRulers[$tenthRashi];

        if ($ninthLord !== $tenthLord && isset($chartData[$ninthLord], $chartData[$tenthLord])) {
            if ($chartData[$ninthLord]['house'] === $chartData[$tenthLord]['house']) {
                $h = $chartData[$ninthLord]['house'];
                $yogas[] = [
                    'name' => 'Dharma-Karma Adhipati Raja Yoga',
                    'desc' => "9th Lord ({$ninthLord}) and 10th Lord ({$tenthLord}) unite in house {$h}, aligning fortune with career achievement.",
                ];
            }
        }

        // 6. Sunapha / Anapha / Durudhara Lunar Yogas (Planets in 2nd or 12th from Moon)
        $otherPlanets = ['Mars', 'Mercury', 'Jupiter', 'Venus', 'Saturn'];
        $in2ndFromMoon = false;
        $in12thFromMoon = false;

        foreach ($otherPlanets as $p) {
            $pSign = $chartData[$p]['rashi_number'] ?? 1;
            $rel = (($pSign - $moonSign + 12) % 12) + 1;
            if ($rel === 2) {
                $in2ndFromMoon = true;
            }
            if ($rel === 12) {
                $in12thFromMoon = true;
            }
        }

        if ($in2ndFromMoon && $in12thFromMoon) {
            $yogas[] = ['name' => 'Durudhara Yoga', 'desc' => 'Planets flank both sides of the Moon (2nd and 12th), providing balanced support, wealth, and stability.'];
        } elseif ($in2ndFromMoon) {
            $yogas[] = ['name' => 'Sunapha Yoga', 'desc' => 'A planet occupies the 2nd house from the Moon, indicating self-earned wealth, resourcefulness, and respect.'];
        } elseif ($in12thFromMoon) {
            $yogas[] = ['name' => 'Anapha Yoga', 'desc' => 'A planet occupies the 12th house from the Moon, supporting personal dignity, comfort, and inner peace.'];
        }

        // 7. Neechabhanga Raja Yoga
        $debilitated = ['Sun' => 7, 'Moon' => 8, 'Mars' => 4, 'Mercury' => 12, 'Jupiter' => 10, 'Venus' => 6, 'Saturn' => 1];
        $exalted = ['Sun' => 1, 'Moon' => 2, 'Mars' => 10, 'Mercury' => 6, 'Jupiter' => 4, 'Venus' => 12, 'Saturn' => 7];

        foreach ($debilitated as $debPlanet => $debSign) {
            if (($chartData[$debPlanet]['rashi_number'] ?? 0) === $debSign) {
                foreach ($exalted as $exPlanet => $exSign) {
                    if (($chartData[$exPlanet]['rashi_number'] ?? 0) === $exSign && $debSign === $exSign) {
                        $yogas[] = [
                            'name' => 'Neechabhanga Raja Yoga',
                            'desc' => "{$debPlanet}'s debilitation is cancelled by conjunction with exalted {$exPlanet}, turning initial struggles into remarkable success.",
                        ];
                    }
                }
            }
        }

        if (empty($yogas)) {
            $yogas[] = [
                'name' => 'Sama Graha Yoga',
                'desc' => 'Planets are evenly distributed across houses, supporting steady, balanced progress through personal effort.',
            ];
        }

        return $yogas;
    }

    /**
     * Complete Multi-Chart Manglik Dosha Detection & Cancellation Engine
     */
    public function evaluateManglikDosha(array $chartData, string $dob): array
    {
        $lagnaSign = $chartData['Ascendant']['rashi_number'] ?? $chartData['Ascendant (Lagna)']['rashi_number'] ?? 1;
        $marsSign = $chartData['Mars']['rashi_number'] ?? 1;
        $moonSign = $chartData['Moon']['rashi_number'] ?? 1;
        $venusSign = $chartData['Venus']['rashi_number'] ?? 1;
        $jupSign = $chartData['Jupiter']['rashi_number'] ?? 1;

        $houseFromLagna = ($marsSign - $lagnaSign + 12) % 12 + 1;
        $houseFromMoon = ($marsSign - $moonSign + 12) % 12 + 1;
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
                'is_present' => false,
                'status' => 'NON-MANGLIK',
                'severity' => 'NONE',
                'score' => 0,
                'cancellations' => [],
                'description' => "Mars is placed in house {$houseFromLagna} from Lagna and house {$houseFromMoon} from Moon. No Manglik Dosha is present.",
            ];
        }

        $cancellations = [];

        if (\in_array($marsSign, [1, 8, 10], true)) {
            $score = 0;
            $cancellations[] = 'Mars is in its own or exalted sign (Aries/Scorpio/Capricorn), fully neutralizing the dosha.';
        }

        $houseSignExceptions = [
            1 => [1, 5],
            2 => [3, 6],
            4 => [1, 8],
            7 => [4, 10],
            8 => [9, 12],
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
            'is_present' => $isPresent,
            'status' => $status,
            'severity' => $severity,
            'score' => $finalScore,
            'houses' => [
                'lagna' => $houseFromLagna,
                'moon' => $houseFromMoon,
                'venus' => $houseFromVenus,
            ],
            'cancellations' => $cancellations,
            'description' => $summary,
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
        $isPartial = (!$isComplete && ($count1 === 6 || $count2 === 6));

        if (!$isComplete && !$isPartial) {
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
        if (!$lagnaInHemmedArc) {
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

    /**
     * Dynamically calculate Sade Sati & Dhaiya (Panoti) using live Saturn transit
     */
    public function evaluateSadeSati(float $natalMoonDegree, string $moonSignName): array
    {
        $today = now();
        $currentTransit = $this->vedicService->calculateChart(
            $today->format('Y-m-d'),
            $today->format('H:i'),
            'Ujjain, India'
        );

        $transitSaturn = $currentTransit['planets']['Saturn'] ?? null;
        $transitSaturnSign = explode(' ', $transitSaturn['rashi'] ?? 'Aries')[0];

        $natalMoonRashiIndex = (int) floor($natalMoonDegree / 30.0);
        $transitSaturnRashiIndex = ($transitSaturn['rashi_number'] ?? 1) - 1;

        $houseFromMoon = (($transitSaturnRashiIndex - $natalMoonRashiIndex + 12) % 12) + 1;

        if ($houseFromMoon === 12) {
            return [
                'is_active' => true,
                'status' => 'ACTIVE (1ST PHASE - RISING)',
                'phase' => 'Rising Phase (Udaya)',
                'transit_sign' => $transitSaturnSign,
                'description' => "Sade Sati is active in its 1st (Rising) phase. Transit Saturn is in {$transitSaturnSign} (12th house from your natal Moon in {$moonSignName}).",
            ];
        }

        if ($houseFromMoon === 1) {
            return [
                'is_active' => true,
                'status' => 'ACTIVE (2ND PHASE - PEAK)',
                'phase' => 'Peak Phase (Shikhar)',
                'transit_sign' => $transitSaturnSign,
                'description' => "Sade Sati is active in its 2nd (Peak) phase. Transit Saturn is transiting directly over your natal Moon in {$moonSignName}.",
            ];
        }

        if ($houseFromMoon === 2) {
            return [
                'is_active' => true,
                'status' => 'ACTIVE (3RD PHASE - SETTING)',
                'phase' => 'Setting Phase (Ast)',
                'transit_sign' => $transitSaturnSign,
                'description' => "Sade Sati is active in its 3rd (Setting) phase. Transit Saturn is in {$transitSaturnSign} (2nd house from your natal Moon in {$moonSignName}).",
            ];
        }

        if ($houseFromMoon === 4 || $houseFromMoon === 8) {
            $dhaiyaName = $houseFromMoon === 4 ? 'Kantaka Shani (4th)' : 'Ashtama Shani (8th)';
            return [
                'is_active' => false,
                'status' => "NOT ACTIVE ({$dhaiyaName} Dhaiya)",
                'phase' => 'Dhaiya (2.5-Year Transit)',
                'transit_sign' => $transitSaturnSign,
                'description' => "Sade Sati is not active, though Saturn in {$transitSaturnSign} is in the {$houseFromMoon}th house from your natal Moon ({$dhaiyaName}).",
            ];
        }

        return [
            'is_active' => false,
            'status' => 'NOT ACTIVE',
            'phase' => 'None',
            'transit_sign' => $transitSaturnSign,
            'description' => "Sade Sati is not active. Transit Saturn is currently in {$transitSaturnSign} ({$houseFromMoon}th house from your natal Moon in {$moonSignName}).",
        ];
    }

    private function getSignLord(string $sign): string
    {
        $lords = [
            'Aries' => 'Mars',
            'Taurus' => 'Venus',
            'Gemini' => 'Mercury',
            'Cancer' => 'Moon',
            'Leo' => 'Sun',
            'Virgo' => 'Mercury',
            'Libra' => 'Venus',
            'Scorpio' => 'Mars',
            'Sagittarius' => 'Jupiter',
            'Capricorn' => 'Saturn',
            'Aquarius' => 'Saturn',
            'Pisces' => 'Jupiter'
        ];
        return $lords[$sign] ?? 'Sun';
    }

    private function getNakshatraLord(string $nakshatra): string
    {
        $nakshatras = [
            'Ashwini' => 'Ketu',
            'Bharani' => 'Venus',
            'Krittika' => 'Sun',
            'Rohini' => 'Moon',
            'Mrigashira' => 'Mars',
            'Ardra' => 'Rahu',
            'Punarvasu' => 'Jupiter',
            'Pushya' => 'Saturn',
            'Ashlesha' => 'Mercury',
            'Magha' => 'Ketu',
            'Purva Phalguni' => 'Venus',
            'Uttara Phalguni' => 'Sun',
            'Hasta' => 'Moon',
            'Chitra' => 'Mars',
            'Swati' => 'Rahu',
            'Vishakha' => 'Jupiter',
            'Anuradha' => 'Saturn',
            'Jyeshtha' => 'Mercury',
            'Mula' => 'Ketu',
            'Purva Ashadha' => 'Venus',
            'Uttara Ashadha' => 'Sun',
            'Shravana' => 'Moon',
            'Dhanishta' => 'Mars',
            'Shatabhisha' => 'Rahu',
            'Purva Bhadrapada' => 'Jupiter',
            'Uttara Bhadrapada' => 'Saturn',
            'Revati' => 'Mercury'
        ];
        return $nakshatras[$nakshatra] ?? 'Sun';
    }

    /**
     * Complete 36-Guna Ashtakoota Kundli Milan & Manglik Compatibility Engine
     */
    public function calculateKundliMilan(array $boyData, array $girlData): array
    {
        $boyChart = $this->vedicService->calculateChart(
            $boyData['dob'],
            $boyData['tob'] ?? '10:00',
            $boyData['birth_place'] ?? 'India',
            isset($boyData['lat']) ? (float) $boyData['lat'] : null,
            isset($boyData['lng']) ? (float) $boyData['lng'] : null
        );

        $girlChart = $this->vedicService->calculateChart(
            $girlData['dob'],
            $girlData['tob'] ?? '10:00',
            $girlData['birth_place'] ?? 'India',
            isset($girlData['lat']) ? (float) $girlData['lat'] : null,
            isset($girlData['lng']) ? (float) $girlData['lng'] : null
        );

        $boyMoon = $boyChart['planets']['Moon'] ?? $boyChart['Moon'] ?? [];
        $girlMoon = $girlChart['planets']['Moon'] ?? $girlChart['Moon'] ?? [];

        $bSign = ($boyMoon['rashi_number'] ?? 1) - 1;
        $gSign = ($girlMoon['rashi_number'] ?? 1) - 1;

        $bNak = (int) floor(($boyMoon['full_degree'] ?? 0.0) / (360.0 / 27.0));
        $gNak = (int) floor(($girlMoon['full_degree'] ?? 0.0) / (360.0 / 27.0));

        $bPada = $boyMoon['pada'] ?? 1;
        $gPada = $girlMoon['pada'] ?? 1;

        // --- 1. VARNA KOOTA (Max 1) ---
        $varnaRanks = [3, 1, 3, 0, 3, 1, 3, 0, 3, 1, 3, 0];
        $varnaNames = [0 => 'Shudra', 1 => 'Vaishya', 2 => 'Kshatriya', 3 => 'Brahmin'];
        $bVarna = $varnaRanks[$bSign] ?? 2;
        $gVarna = $varnaRanks[$gSign] ?? 2;
        $varnaScore = ($bVarna >= $gVarna) ? 1.0 : 0.0;

        // --- 2. VASHYA KOOTA (Max 2) ---
        $vashyaTypes = [
            'Chatushpada',
            'Chatushpada',
            'Manav',
            'Jalachara',
            'Vanachara',
            'Manav',
            'Manav',
            'Keeta',
            'Chatushpada',
            'Jalachara',
            'Manav',
            'Jalachara'
        ];
        $bVashya = $vashyaTypes[$bSign] ?? 'Manav';
        $gVashya = $vashyaTypes[$gSign] ?? 'Manav';
        if ($bVashya === $gVashya) {
            $vashyaScore = 2.0;
        } elseif (
            ($bVashya === 'Manav' && \in_array($gVashya, ['Chatushpada', 'Jalachara'], true)) ||
            ($gVashya === 'Manav' && \in_array($bVashya, ['Chatushpada', 'Jalachara'], true))
        ) {
            $vashyaScore = 1.0;
        } elseif ($bVashya === 'Vanachara' || $gVashya === 'Vanachara') {
            $vashyaScore = 0.0;
        } else {
            $vashyaScore = 0.5;
        }

        // --- 3. TARA KOOTA (Max 3) ---
        $auspiciousTaras = [1, 2, 4, 6, 8, 9];
        $taraGtoB = (($bNak - $gNak + 27) % 27) % 9;
        $taraGtoB = $taraGtoB === 0 ? 9 : $taraGtoB;

        $taraBtoG = (($gNak - $bNak + 27) % 27) % 9;
        $taraBtoG = $taraBtoG === 0 ? 9 : $taraBtoG;

        $t1Good = \in_array($taraGtoB, $auspiciousTaras, true);
        $t2Good = \in_array($taraBtoG, $auspiciousTaras, true);
        $taraScore = ($t1Good && $t2Good) ? 3.0 : (($t1Good || $t2Good) ? 1.5 : 0.0);

        // --- 4. YONI KOOTA (Max 4) ---
        $yoniList = [
            'Ashwa',
            'Gaja',
            'Mesha',
            'Sarpa',
            'Sarpa',
            'Shwan',
            'Marjara',
            'Mesha',
            'Marjara',
            'Mushaka',
            'Mushaka',
            'Gau',
            'Mahisha',
            'Vyaghra',
            'Mahisha',
            'Vyaghra',
            'Mriga',
            'Mriga',
            'Shwan',
            'Vanara',
            'Nakula',
            'Vanara',
            'Simha',
            'Ashwa',
            'Simha',
            'Gau',
            'Gaja'
        ];
        $swornEnemies = [
            'Ashwa' => 'Mahisha',
            'Mahisha' => 'Ashwa',
            'Gaja' => 'Simha',
            'Simha' => 'Gaja',
            'Mesha' => 'Vanara',
            'Vanara' => 'Mesha',
            'Sarpa' => 'Nakula',
            'Nakula' => 'Sarpa',
            'Shwan' => 'Mriga',
            'Mrigash' => 'Shwan',
            'Marjara' => 'Mushaka',
            'Mushaka' => 'Marjara',
            'Gau' => 'Vyaghra',
            'Vyaghra' => 'Gau',
        ];
        $bYoni = $yoniList[$bNak % 27] ?? 'Ashwa';
        $gYoni = $yoniList[$gNak % 27] ?? 'Ashwa';
        if ($bYoni === $gYoni) {
            $yoniScore = 4.0;
        } elseif (($swornEnemies[$bYoni] ?? '') === $gYoni) {
            $yoniScore = 0.0;
        } else {
            $yoniScore = 2.0;
        }

        // --- 5. GRAHA MAITRI KOOTA (Max 5) ---
        $signLords = ['Mars', 'Venus', 'Mercury', 'Moon', 'Sun', 'Mercury', 'Venus', 'Mars', 'Jupiter', 'Saturn', 'Saturn', 'Jupiter'];
        $friends = [
            'Sun' => ['Moon', 'Mars', 'Jupiter'],
            'Moon' => ['Sun', 'Mercury'],
            'Mars' => ['Sun', 'Moon', 'Jupiter'],
            'Mercury' => ['Sun', 'Venus'],
            'Jupiter' => ['Sun', 'Moon', 'Mars'],
            'Venus' => ['Mercury', 'Saturn'],
            'Saturn' => ['Mercury', 'Venus'],
        ];
        $enemies = [
            'Sun' => ['Venus', 'Saturn'],
            'Moon' => [],
            'Mars' => ['Mercury'],
            'Mercury' => ['Moon'],
            'Jupiter' => ['Mercury', 'Venus'],
            'Venus' => ['Sun', 'Moon'],
            'Saturn' => ['Sun', 'Moon', 'Mars'],
        ];

        $bLord = $signLords[$bSign] ?? 'Sun';
        $gLord = $signLords[$gSign] ?? 'Sun';

        $getRelation = function ($p1, $p2) use ($friends, $enemies) {
            if ($p1 === $p2)
                return 'Friend';
            if (\in_array($p2, $friends[$p1] ?? [], true))
                return 'Friend';
            if (\in_array($p2, $enemies[$p1] ?? [], true))
                return 'Enemy';
            return 'Neutral';
        };

        $rel1 = $getRelation($bLord, $gLord);
        $rel2 = $getRelation($gLord, $bLord);

        if ($rel1 === 'Friend' && $rel2 === 'Friend')
            $maitriScore = 5.0;
        elseif (($rel1 === 'Friend' && $rel2 === 'Neutral') || ($rel1 === 'Neutral' && $rel2 === 'Friend'))
            $maitriScore = 4.0;
        elseif ($rel1 === 'Neutral' && $rel2 === 'Neutral')
            $maitriScore = 3.0;
        elseif (($rel1 === 'Friend' && $rel2 === 'Enemy') || ($rel1 === 'Enemy' && $rel2 === 'Friend'))
            $maitriScore = 1.0;
        elseif (($rel1 === 'Neutral' && $rel2 === 'Enemy') || ($rel1 === 'Enemy' && $rel2 === 'Neutral'))
            $maitriScore = 0.5;
        else
            $maitriScore = 0.0;

        // --- 6. GANA KOOTA (Max 6) ---
        $ganaList = [
            'Dev',
            'Manav',
            'Rakshasa',
            'Manav',
            'Dev',
            'Manav',
            'Dev',
            'Dev',
            'Rakshasa',
            'Rakshasa',
            'Manav',
            'Manav',
            'Dev',
            'Rakshasa',
            'Dev',
            'Rakshasa',
            'Dev',
            'Rakshasa',
            'Rakshasa',
            'Manav',
            'Manav',
            'Dev',
            'Rakshasa',
            'Rakshasa',
            'Manav',
            'Manav',
            'Dev'
        ];
        $bGana = $ganaList[$bNak % 27] ?? 'Dev';
        $gGana = $ganaList[$gNak % 27] ?? 'Dev';

        if ($bGana === $gGana) {
            $ganaScore = 6.0;
        } elseif ($bGana === 'Dev' && $gGana === 'Manav') {
            $ganaScore = 6.0;
        } elseif ($bGana === 'Manav' && $gGana === 'Dev') {
            $ganaScore = 5.0;
        } elseif ($bGana === 'Dev' && $gGana === 'Rakshasa') {
            $ganaScore = 1.0;
        } else {
            $ganaScore = 0.0;
        }

        // --- 7. BHAKOOT KOOTA (Max 7) ---
        $signDiff = (($bSign - $gSign + 12) % 12) + 1;
        $goodBhakoot = [1, 3, 4, 7, 10, 11];
        $bhakootCancelled = false;

        if (\in_array($signDiff, $goodBhakoot, true)) {
            $bhakootScore = 7.0;
        } elseif ($bLord === $gLord || ($rel1 === 'Friend' && $rel2 === 'Friend')) {
            $bhakootScore = 7.0;
            $bhakootCancelled = true;
        } else {
            $bhakootScore = 0.0;
        }

        // --- 8. NADI KOOTA (Max 8) ---
        $nadiList = [
            'Adi',
            'Madhya',
            'Antya',
            'Antya',
            'Madhya',
            'Adi',
            'Adi',
            'Madhya',
            'Antya',
            'Antya',
            'Madhya',
            'Adi',
            'Adi',
            'Madhya',
            'Antya',
            'Antya',
            'Madhya',
            'Adi',
            'Adi',
            'Madhya',
            'Antya',
            'Antya',
            'Madhya',
            'Adi',
            'Adi',
            'Madhya',
            'Antya'
        ];
        $bNadi = $nadiList[$bNak % 27] ?? 'Adi';
        $gNadi = $nadiList[$gNak % 27] ?? 'Adi';
        $nadiCancelled = false;

        if ($bNadi !== $gNadi) {
            $nadiScore = 8.0;
        } elseif (($bSign === $gSign && $bNak !== $gNak) || ($bNak === $gNak && $bSign !== $gSign)) {
            $nadiScore = 8.0;
            $nadiCancelled = true;
        } else {
            $nadiScore = 0.0;
        }

        $totalGuna = round($varnaScore + $vashyaScore + $taraScore + $yoniScore + $maitriScore + $ganaScore + $bhakootScore + $nadiScore, 1);
        if ($totalGuna > 36) {
            $totalGuna = 31.5;
        }

        $boyPlanets = $boyChart['planets'] ?? $boyChart;
        $girlPlanets = $girlChart['planets'] ?? $girlChart;
        $boyManglik = $this->evaluateManglikDosha($boyPlanets, $boyData['dob']);
        $girlManglik = $this->evaluateManglikDosha($girlPlanets, $girlData['dob']);

        $manglikCompatible = ($boyManglik['is_present'] === $girlManglik['is_present'])
            || ($boyManglik['severity'] === 'LOW' && !$girlManglik['is_present'])
            || ($girlManglik['severity'] === 'LOW' && !$boyManglik['is_present']);

        if ($totalGuna >= 25 && $manglikCompatible) {
            $conclusion = "Excellent compatibility ({$totalGuna}/36 Gunas matched) with harmonious Manglik alignment. Highly auspicious for marriage.";
        } elseif ($totalGuna >= 18) {
            $conclusion = "Good compatibility ({$totalGuna}/36 Gunas matched). " . ($manglikCompatible ? 'Manglik status is balanced.' : 'Note: Consult an astrologer regarding the Manglik difference.');
        } else {
            $conclusion = "Low Guna score ({$totalGuna}/36 Gunas matched). Detailed horoscope consultation is advised before proceeding.";
        }

        return [
            'boy' => [
                'name' => $boyData['name'],
                'moon_sign' => explode(' ', $boyMoon['rashi'] ?? 'Aries')[0],
                'nakshatra' => ($boyMoon['nakshatra'] ?? 'Ashwini') . " (Pada {$bPada})",
                'manglik' => $boyManglik['status'],
            ],
            'girl' => [
                'name' => $girlData['name'],
                'moon_sign' => explode(' ', $girlMoon['rashi'] ?? 'Aries')[0],
                'nakshatra' => ($girlMoon['nakshatra'] ?? 'Ashwini') . " (Pada {$gPada})",
                'manglik' => $girlManglik['status'],
            ],
            'boy_chart' => $boyChart,
            'girl_chart' => $girlChart,
            'total_guna' => $totalGuna,
            'max_guna' => 36,
            'kootas' => [
                ['name' => 'Varna', 'score' => $varnaScore, 'max' => 1, 'boy' => $varnaNames[$bVarna] ?? 'Shudra', 'girl' => $varnaNames[$gVarna] ?? 'Shudra', 'description' => 'Spiritual compatibility and ego balance.'],
                ['name' => 'Vashya', 'score' => $vashyaScore, 'max' => 2, 'boy' => $bVashya, 'girl' => $gVashya, 'description' => 'Mutual attraction and harmony.'],
                ['name' => 'Tara', 'score' => $taraScore, 'max' => 3, 'boy' => $boyMoon['nakshatra'] ?? 'Ashwini', 'girl' => $girlMoon['nakshatra'] ?? 'Ashwini', 'description' => 'Birth star destiny, health, and luck.'],
                ['name' => 'Yoni', 'score' => $yoniScore, 'max' => 4, 'boy' => $bYoni, 'girl' => $gYoni, 'description' => 'Physical and biological compatibility.'],
                ['name' => 'Graha Maitri', 'score' => $maitriScore, 'max' => 5, 'boy' => $bLord, 'girl' => $gLord, 'description' => 'Mental harmony between Moon sign lords.'],
                ['name' => 'Gana', 'score' => $ganaScore, 'max' => 6, 'boy' => $bGana, 'girl' => $gGana, 'description' => 'Temperament and daily behavior match.'],
                ['name' => 'Bhakoot', 'score' => $bhakootScore, 'max' => 7, 'boy' => explode(' ', $boyMoon['rashi'] ?? 'Aries')[0], 'girl' => explode(' ', $girlMoon['rashi'] ?? 'Aries')[0], 'description' => $bhakootCancelled ? 'Bhakoot Dosha cancelled by Rashi Lord harmony.' : 'Family welfare and emotional bonding.'],
                ['name' => 'Nadi', 'score' => $nadiScore, 'max' => 8, 'boy' => $bNadi, 'girl' => $gNadi, 'description' => $nadiCancelled ? 'Nadi Dosha cancelled by Rashi/Nakshatra exception.' : 'Health, genetics, and progeny compatibility.'],
            ],
            'manglik_comparison' => [
                'is_compatible' => $manglikCompatible,
                'boy_status' => $boyManglik['status'],
                'girl_status' => $girlManglik['status'],
            ],
            'conclusion' => $conclusion,
        ];
    }

    public function calculateZodiacCompatibility(string $sign1, string $sign2, string $mode = 'love'): array
    {
        $signs = [
            'Aries',
            'Taurus',
            'Gemini',
            'Cancer',
            'Leo',
            'Virgo',
            'Libra',
            'Scorpio',
            'Sagittarius',
            'Capricorn',
            'Aquarius',
            'Pisces'
        ];

        $elements = [
            'Aries' => 'Fire',
            'Leo' => 'Fire',
            'Sagittarius' => 'Fire',
            'Taurus' => 'Earth',
            'Virgo' => 'Earth',
            'Capricorn' => 'Earth',
            'Gemini' => 'Air',
            'Libra' => 'Air',
            'Aquarius' => 'Air',
            'Cancer' => 'Water',
            'Scorpio' => 'Water',
            'Pisces' => 'Water',
        ];

        $qualities = [
            'Aries' => 'Cardinal',
            'Cancer' => 'Cardinal',
            'Libra' => 'Cardinal',
            'Capricorn' => 'Cardinal',
            'Taurus' => 'Fixed',
            'Leo' => 'Fixed',
            'Scorpio' => 'Fixed',
            'Aquarius' => 'Fixed',
            'Gemini' => 'Mutable',
            'Virgo' => 'Mutable',
            'Sagittarius' => 'Mutable',
            'Pisces' => 'Mutable',
        ];

        $rulers = [
            'Aries' => 'Mars',
            'Taurus' => 'Venus',
            'Gemini' => 'Mercury',
            'Cancer' => 'Moon',
            'Leo' => 'Sun',
            'Virgo' => 'Mercury',
            'Libra' => 'Venus',
            'Scorpio' => 'Mars',
            'Sagittarius' => 'Jupiter',
            'Capricorn' => 'Saturn',
            'Aquarius' => 'Saturn',
            'Pisces' => 'Jupiter',
        ];

        $isFriendship = strtolower(trim($mode)) === 'friendship';
        $activeMode = $isFriendship ? 'friendship' : 'love';

        $s1 = ucfirst(strtolower(trim($sign1)));
        $s2 = ucfirst(strtolower(trim($sign2)));

        $idx1 = array_search($s1, $signs, true);
        $idx2 = array_search($s2, $signs, true);

        if ($idx1 === false)
            $idx1 = 0;
        if ($idx2 === false)
            $idx2 = 6;

        $s1 = $signs[$idx1];
        $s2 = $signs[$idx2];

        $el1 = $elements[$s1];
        $el2 = $elements[$s2];
        $r1 = $rulers[$s1];
        $r2 = $rulers[$s2];

        // 1. Element Score (Max 40)
        $complementary = ['Fire-Air', 'Air-Fire', 'Earth-Water', 'Water-Earth'];
        if (\in_array("{$el1}-{$el2}", $complementary, true)) {
            $elementScore = $isFriendship ? 38 : 40;
            $elementNote = $isFriendship
                ? "{$el1} and {$el2} spark lively conversations and effortless teamwork."
                : "{$el1} and {$el2} naturally energize and balance each other romantically.";
        } elseif ($el1 === $el2) {
            $elementScore = $isFriendship ? 40 : 36;
            $elementNote = $isFriendship
                ? "Sharing the {$el1} element creates an instant 'best friend' wavelength."
                : "Both share the {$el1} element, creating instinctive emotional understanding.";
        } elseif (\in_array("{$el1}-{$el2}", ['Fire-Earth', 'Earth-Fire', 'Air-Water', 'Water-Air'], true)) {
            $elementScore = $isFriendship ? 26 : 22;
            $elementNote = "{$el1} and {$el2} bring contrasting perspectives that broaden each other's worldview.";
        } else {
            $elementScore = $isFriendship ? 20 : 15;
            $elementNote = "{$el1} and {$el2} operate differently, making open communication essential.";
        }

        // 2. Sign Distance / Aspect Score (Max 35)
        $distance = (($idx2 - $idx1 + 12) % 12) + 1;
        if (\in_array($distance, [5, 9], true)) {
            $aspectScore = 35;
            $aspectName = 'Trine (5-9 Harmony)';
        } elseif (\in_array($distance, [3, 11], true)) {
            $aspectScore = $isFriendship ? 35 : 32;
            $aspectName = 'Sextile (3-11 Camaraderie)';
        } elseif ($distance === 7) {
            $aspectScore = $isFriendship ? 26 : 31;
            $aspectName = $isFriendship ? 'Opposition (1-7 Dynamic Debate)' : 'Opposition (1-7 Magnetic Attraction)';
        } elseif ($distance === 1) {
            $aspectScore = $isFriendship ? 33 : 28;
            $aspectName = 'Conjunction (Same Sign Mirror)';
        } elseif (\in_array($distance, [4, 10], true)) {
            $aspectScore = $isFriendship ? 22 : 20;
            $aspectName = 'Square (4-10 Dynamic Tension)';
        } else {
            $aspectScore = $isFriendship ? 18 : 14;
            $aspectName = 'Adjustment Aspect (2-12 / 6-8)';
        }

        // 3. Planetary Ruler Score (Max 25)
        $friends = [
            'Sun' => ['Moon', 'Mars', 'Jupiter'],
            'Moon' => ['Sun', 'Mercury'],
            'Mars' => ['Sun', 'Moon', 'Jupiter'],
            'Mercury' => ['Sun', 'Venus'],
            'Jupiter' => ['Sun', 'Moon', 'Mars'],
            'Venus' => ['Mercury', 'Saturn'],
            'Saturn' => ['Mercury', 'Venus'],
        ];

        $getRelation = function ($p1, $p2) use ($friends) {
            if ($p1 === $p2)
                return 'Friend';
            if (\in_array($p2, $friends[$p1] ?? [], true))
                return 'Friend';
            return 'Neutral';
        };

        $rel1 = $getRelation($r1, $r2);
        $rel2 = $getRelation($r2, $r1);

        if ($r1 === $r2 || ($rel1 === 'Friend' && $rel2 === 'Friend')) {
            $rulerScore = 25;
        } elseif ($rel1 === 'Friend' || $rel2 === 'Friend') {
            $rulerScore = 18;
        } else {
            $rulerScore = $isFriendship ? 15 : 12;
        }

        $overall = min(99, $elementScore + $aspectScore + $rulerScore);

        if ($isFriendship) {
            $breakdown = [
                ['label' => 'Humor & Banter', 'score' => min(99, (int) round($aspectScore * 1.6 + $rulerScore * 1.4))],
                ['label' => 'Communication Flow', 'score' => min(99, (int) round($rulerScore * 2.2 + $aspectScore * 1.1))],
                ['label' => 'Loyalty & Trust', 'score' => min(99, (int) round($elementScore * 1.4 + $rulerScore * 1.5))],
                ['label' => 'Shared Adventures', 'score' => min(99, (int) round($elementScore * 1.5 + $aspectScore * 0.9))],
            ];

            if ($overall >= 80) {
                $verdict = 'Best Friends for Life';
                $summary = "As friends, {$s1} and {$s2} share effortless camaraderie, mutual loyalty, and endless conversation. {$elementNote}";
            } elseif ($overall >= 65) {
                $verdict = 'Great Companions';
                $summary = "{$s1} and {$s2} make dependable, supportive friends who bring out the best in one another. {$elementNote}";
            } elseif ($overall >= 50) {
                $verdict = 'Casual & Fun Allies';
                $summary = "{$s1} and {$s2} enjoy an interesting friendship built on different strengths and fresh perspectives. {$elementNote}";
            } else {
                $verdict = 'Different Social Wavelengths';
                $summary = "{$s1} and {$s2} have very different social styles, thriving best around shared hobbies or structured group settings. {$elementNote}";
            }
        } else {
            $breakdown = [
                ['label' => 'Love & Romance', 'score' => min(99, (int) round($elementScore * 1.3 + $aspectScore * 1.1 + 8))],
                ['label' => 'Communication', 'score' => min(99, (int) round($rulerScore * 2.1 + $aspectScore * 1.2))],
                ['label' => 'Trust & Stability', 'score' => min(99, (int) round($elementScore * 1.2 + $rulerScore * 1.8 + 6))],
                ['label' => 'Passion & Chemistry', 'score' => min(99, (int) round($aspectScore * 1.6 + $elementScore * 0.9 + 5))],
            ];

            if ($overall >= 80) {
                $verdict = 'Soulmate Synergy';
                $summary = "Romantically, {$s1} and {$s2} share an exceptional natural bond filled with warmth and attraction. {$elementNote}";
            } elseif ($overall >= 65) {
                $verdict = 'Strong Romantic Match';
                $summary = "{$s1} and {$s2} complement each other well with strong potential for lasting love and partnership. {$elementNote}";
            } elseif ($overall >= 50) {
                $verdict = 'Balanced Potential';
                $summary = "{$s1} and {$s2} have a dynamic romantic relationship that thrives when both partners honor their differences. {$elementNote}";
            } else {
                $verdict = 'Growth-Oriented Match';
                $summary = "{$s1} and {$s2} approach love from very different wavelengths, requiring patience and clear emotional communication. {$elementNote}";
            }
        }

        return [
            'mode' => $activeMode,
            'partner1' => [
                'sign' => $s1,
                'element' => $el1,
                'quality' => $qualities[$s1],
                'ruler' => $r1,
            ],
            'partner2' => [
                'sign' => $s2,
                'element' => $el2,
                'quality' => $qualities[$s2],
                'ruler' => $r2,
            ],
            'overall_score' => $overall,
            'verdict' => $verdict,
            'aspect' => $aspectName,
            'summary' => $summary,
            'breakdown' => $breakdown,
        ];
    }

    public function getDailyPanchang(?string $date = null, string $city = 'Jaipur, Rajasthan, India', float $lat = 26.9124, float $lng = 75.7873): array
    {
        $tz = new \DateTimeZone('Asia/Kolkata');
        $targetDate = $date
            ? \DateTime::createFromFormat('Y-m-d', $date, $tz) ?: new \DateTime('now', $tz)
            : new \DateTime('now', $tz);

        $dateStr = $targetDate->format('Y-m-d');
        $dayOfWeek = (int) $targetDate->format('w'); // 0 = Sunday ... 6 = Saturday

        // 1. Exact Sunrise and Sunset timestamps
        $sunInfo = date_sun_info($targetDate->getTimestamp(), $lat, $lng);
        $sunriseTs = is_int($sunInfo['sunrise'] ?? null) ? $sunInfo['sunrise'] : strtotime("$dateStr 06:15:00");
        $sunsetTs = is_int($sunInfo['sunset'] ?? null) ? $sunInfo['sunset'] : strtotime("$dateStr 18:15:00");

        $sunriseDt = (new \DateTime("@{$sunriseTs}"))->setTimezone($tz);
        $sunsetDt = (new \DateTime("@{$sunsetTs}"))->setTimezone($tz);

        // 2. Calculate Panchang at Sunrise (traditional Vedic rule)
        $chart = $this->vedicService->calculateChart(
            $dateStr,
            $sunriseDt->format('H:i'),
            $city,
            $lat,
            $lng
        );

        $moon = $chart['planets']['Moon'];
        $sun = $chart['planets']['Sun'];

        // 3. Calculate Daytime Octants (for Rahu Kaal, Yamaganda, Gulika)
        $dayDurationSec = $sunsetTs - $sunriseTs;
        $octantSec = (int) round($dayDurationSec / 8);

        $formatWindow = function (int $octantNum) use ($sunriseTs, $octantSec, $tz): string {
            $start = (new \DateTime('@' . ($sunriseTs + ($octantNum - 1) * $octantSec)))->setTimezone($tz);
            $end = (new \DateTime('@' . ($sunriseTs + $octantNum * $octantSec)))->setTimezone($tz);
            return $start->format('h:i A') . ' - ' . $end->format('h:i A');
        };

        // Octant lookup tables by weekday (0=Sun, 1=Mon, 2=Tue, 3=Wed, 4=Thu, 5=Fri, 6=Sat)
        $rahuOctant = array(8, 2, 7, 5, 6, 4, 3);
        $yamagandaOctant = array(5, 4, 3, 2, 1, 7, 6);
        $gulikaOctant = array(7, 6, 5, 4, 3, 2, 1);

        // 4. Abhijit & Brahma Muhurat
        $solarNoonTs = $sunriseTs + (int) round($dayDurationSec / 2);
        $abhijitStart = (new \DateTime('@' . ($solarNoonTs - 24 * 60)))->setTimezone($tz)->format('h:i A');
        $abhijitEnd = (new \DateTime('@' . ($solarNoonTs + 24 * 60)))->setTimezone($tz)->format('h:i A');

        $brahmaStart = (new \DateTime('@' . ($sunriseTs - 96 * 60)))->setTimezone($tz)->format('h:i A');
        $brahmaEnd = (new \DateTime('@' . ($sunriseTs - 48 * 60)))->setTimezone($tz)->format('h:i A');

        $hindiDays = ['Ravivaar (Sunday)', 'Somvaar (Monday)', 'Mangalvaar (Tuesday)', 'Budhvaar (Wednesday)', 'Guruvaar (Thursday)', 'Shukravaar (Friday)', 'Shanivaar (Saturday)'];

        return [
            'date' => $targetDate->format('d F Y'),
            'raw_date' => $dateStr,
            'city' => $city,
            'latitude' => $lat,
            'longitude' => $lng,
            'day' => $hindiDays[$dayOfWeek],
            'sunrise' => $sunriseDt->format('h:i A'),
            'sunset' => $sunsetDt->format('h:i A'),
            'tithi' => $chart['tithi_info']['full_name'],
            'tithi_pct' => $chart['tithi_info']['elapsed_pct'] . '%',
            'nakshatra' => $moon['nakshatra'] . ' (Pada ' . $moon['pada'] . ')',
            'nakshatra_lord' => $moon['nakshatra_lord'],
            'yoga' => $chart['yoga_name'],
            'karana' => $chart['karana_name'],
            'moon_sign' => $moon['rashi'],
            'sun_sign' => $sun['rashi'],
            'auspicious_timings' => [
                ['name' => 'Abhijit Muhurat', 'time' => "{$abhijitStart} - {$abhijitEnd}", 'note' => 'Best midday window for new beginnings'],
                ['name' => 'Brahma Muhurat', 'time' => "{$brahmaStart} - {$brahmaEnd}", 'note' => 'Ideal for meditation, study & spiritual practice'],
            ],
            'inauspicious_timings' => [
                ['name' => 'Rahu Kaal', 'time' => $formatWindow($rahuOctant[$dayOfWeek]), 'note' => 'Avoid starting auspicious or financial ventures'],
                ['name' => 'Yamaganda Kaal', 'time' => $formatWindow($yamagandaOctant[$dayOfWeek]), 'note' => 'Avoid important travel or ceremonies'],
                ['name' => 'Gulika Kaal', 'time' => $formatWindow($gulikaOctant[$dayOfWeek]), 'note' => 'Neutral/cautious period'],
            ],
        ];
    }

    public function buildHoroscopePayload(string $sign, string $type = 'daily', ?string $targetDateStr = null): array
    {
        $signs = [
            'Aries',
            'Taurus',
            'Gemini',
            'Cancer',
            'Leo',
            'Virgo',
            'Libra',
            'Scorpio',
            'Sagittarius',
            'Capricorn',
            'Aquarius',
            'Pisces'
        ];

        $tz = new \DateTimeZone('Asia/Kolkata');
        $dt = $targetDateStr
            ? \DateTime::createFromFormat('Y-m-d', $targetDateStr, $tz) ?: new \DateTime('now', $tz)
            : new \DateTime('now', $tz);

        $dateKey = null;
        $weekKey = null;
        $monthKey = null;
        $yearKey = null;

        if ($type === 'daily') {
            $dateKey = $dt->format('Y-m-d');
            $seed = (int) $dt->format('z');
        } elseif ($type === 'weekly') {
            $weekKey = $dt->format('o-\WW');
            $seed = (int) $dt->format('W');
            $dt->modify('wednesday this week');
        } elseif ($type === 'monthly') {
            $monthKey = $dt->format('Y-m');
            $seed = (int) $dt->format('n');
            $dt->setDate((int) $dt->format('Y'), (int) $dt->format('m'), 15);
        } else {
            $type = 'yearly';
            $yearKey = $dt->format('Y');
            $seed = (int) $dt->format('Y');
            $dt->setDate((int) $dt->format('Y'), 7, 1);
        }

        $chart = $this->vedicService->calculateChart($dt->format('Y-m-d'), '06:00', 'Ujjain, India');
        $planets = $chart['planets'] ?? $chart;

        $signIdx = array_search(ucfirst(strtolower(trim($sign))), $signs, true);
        if ($signIdx === false) {
            $signIdx = 0;
        }
        $signName = $signs[$signIdx];

        $houseOf = function (string $planet) use ($planets, $signIdx): int {
            $pRashiIdx = ($planets[$planet]['rashi_number'] ?? 1) - 1;
            return (($pRashiIdx - $signIdx + 12) % 12) + 1;
        };

        $moonH = $houseOf('Moon');
        $sunH = $houseOf('Sun');
        $mercH = $houseOf('Mercury');
        $venH = $houseOf('Venus');
        $marsH = $houseOf('Mars');
        $jupH = $houseOf('Jupiter');
        $satH = $houseOf('Saturn');

        $primaryHouse = match ($type) {
            'daily' => $moonH,
            'weekly' => $mercH,
            'monthly' => $sunH,
            'yearly' => $jupH,
        };

        $signAlphabets = [
            0 => ['A', 'L', 'E', 'Chu', 'La'],
            1 => ['B', 'V', 'U', 'Va', 'O'],
            2 => ['K', 'Ch', 'Gh', 'Ka', 'Ha'],
            3 => ['D', 'H', 'Hi', 'Da', 'Ho'],
            4 => ['M', 'T', 'Ma', 'Ta', 'Me'],
            5 => ['P', 'Th', 'N', 'Pa', 'To'],
            6 => ['R', 'T', 'Ra', 'Ta', 'Re'],
            7 => ['N', 'Y', 'Na', 'Ya', 'Ni'],
            8 => ['Bh', 'Dh', 'Ph', 'Ye', 'Bha'],
            9 => ['Kh', 'J', 'Ja', 'Kha', 'Ga'],
            10 => ['G', 'S', 'Sh', 'Gu', 'Sa'],
            11 => ['D', 'Ch', 'Z', 'Th', 'Di'],
        ];

        $rulerNumbers = [9, 6, 5, 2, 1, 5, 6, 9, 3, 8, 8, 3];
        $signColors = [
            'Coral Red, Saffron',
            'Pearl White, Pastel Pink',
            'Emerald Green, Mint',
            'Silver Mist, Sea Green',
            'Golden Yellow, Ruby Red',
            'Olive Green, Ivory',
            'Rose Quartz, Cream',
            'Crimson, Maroon',
            'Turmeric Yellow, Amber',
            'Royal Blue, Slate Grey',
            'Electric Blue, Indigo',
            'Seafoam, Marigold'
        ];

        $alphaList = $signAlphabets[$signIdx];
        $a1 = $alphaList[$seed % \count($alphaList)];
        $a2 = $alphaList[($seed + 2) % \count($alphaList)];

        $nums = array_unique([
            $rulerNumbers[$signIdx],
            (($signIdx + $seed) % 9) + 1,
            (($primaryHouse + $venH) % 9) + 1,
        ]);

        $loveGood = \in_array($venH, [1, 2, 4, 5, 7, 9, 11], true);
        $careerGood = \in_array($sunH, [1, 2, 3, 6, 10, 11], true) || \in_array($jupH, [1, 2, 5, 9, 10, 11], true);
        $healthGood = !\in_array($marsH, [6, 8, 12], true);
        $travelGood = \in_array($mercH, [3, 7, 9, 11, 12], true) || \in_array($moonH, [3, 9, 12], true);

        $transitContext = [
            'moonH' => $moonH,
            'sunH' => $sunH,
            'venH' => $venH,
            'marsH' => $marsH,
            'jupH' => $jupH,
            'satH' => $satH,
        ];

        $aiEnrichment = app(GeminiHoroscopeService::class)->enrichHoroscope($signName, $type, $transitContext);

        return [
            'sign' => $signName,
            'type' => $type,
            'date' => $dateKey,
            'week_key' => $weekKey,
            'month_key' => $monthKey,
            'year_key' => $yearKey,
            'colors' => $aiEnrichment['colors'] ?? $signColors[$signIdx],
            'numbers' => implode(', ', $nums),
            'alphabets' => "{$a1}, {$a2}",
            'love' => $aiEnrichment['love'] ?? ($loveGood ? "Venus in your {$venH}th house brings warmth, mutual understanding, and smooth emotional connection." : "Venus in your {$venH}th house calls for patience, gentle listening, and avoiding hasty assumptions."),
            'tip_for_singles' => $aiEnrichment['tip_for_singles'] ?? ($loveGood ? "Stay open to social introductions and genuine conversations—someone aligned with your values may stand out." : "Focus on self-care and clarity about what you truly want before rushing into a new commitment."),
            'tip_for_couples' => $aiEnrichment['tip_for_couples'] ?? ($loveGood ? "Plan quality uninterrupted time together to strengthen your shared bond." : "Keep communication calm and practical; small thoughtful gestures will diffuse minor tension."),
            'career' => $aiEnrichment['career'] ?? ($careerGood ? "Strong planetary backing in your {$sunH}th/{$jupH}th houses favors leadership, smart decisions, and steady progress." : "Focus on organizing existing workflows, double-checking details, and avoiding impulsive financial risks."),
            'health' => $aiEnrichment['health'] ?? ($healthGood ? "Mars in your {$marsH}th house supports steady stamina; maintain balanced hydration and regular movement." : "Mars in your {$marsH}th house advises pacing yourself, prioritizing sleep, and avoiding physical overexertion."),
            'emotions' => $aiEnrichment['emotions'] ?? (\in_array($moonH, [1, 3, 5, 7, 9, 11], true) ? "With the Moon activating your {$moonH}th house, your mindset feels upbeat, clear, and socially receptive." : "With the Moon moving through your {$moonH}th house, take quiet moments to process feelings and stay grounded."),
            'travel' => $aiEnrichment['travel'] ?? ($travelGood ? "Favorable alignment in your {$mercH}th/{$moonH}th houses supports productive short trips, errands, or travel planning." : "Keep schedules flexible and double-check itineraries if stepping out for work or personal errands."),
            'description' => $aiEnrichment['description'] ?? "For {$signName}, this {$type} cycle is anchored by transit activity in your {$primaryHouse}th house, highlighting personal growth, measured action, and clarity.",
            'cosmic_tip' => $aiEnrichment['cosmic_tip'] ?? (\in_array($jupH, [1, 2, 5, 9, 11], true) ? "Jupiter aspects favor long-term planning—commit to one meaningful goal and act with confidence." : "Saturn in your {$satH}th house rewards discipline and patience—focus on steady consistency over shortcuts."),
        ];
    }

    public function generateManglikReport(
        string $name,
        string $gender,
        string $dob,
        string $tob,
        string $birthPlace,
        ?float $lat = null,
        ?float $lng = null
    ): array {
        $timeNormalized = !empty($tob) ? $tob : '12:00';
        $chartResult = $this->vedicService->calculateChart($dob, $timeNormalized, $birthPlace, $lat, $lng);
        $planets = $chartResult['planets'];

        $lagna = $planets['Ascendant (Lagna)'];
        $moon = $planets['Moon'];
        $venus = $planets['Venus'];
        $mars = $planets['Mars'];
        $jup = $planets['Jupiter'];

        $lagnaSign = $lagna['rashi_number'];
        $moonSign = $moon['rashi_number'];
        $venusSign = $venus['rashi_number'];
        $marsSign = $mars['rashi_number'];
        $jupSign = $jup['rashi_number'];

        // 1. Mars House Position from Lagna, Moon, and Venus
        $houseFromLagna = (($marsSign - $lagnaSign + 12) % 12) + 1;
        $houseFromMoon = (($marsSign - $moonSign + 12) % 12) + 1;
        $houseFromVenus = (($marsSign - $venusSign + 12) % 12) + 1;

        $primaryHouses = array(1, 4, 7, 8, 12);
        $secondaryHouses = array(2);

        $score = 0;
        $triggers = array();

        $lagnaManglik = false;
        if (in_array($houseFromLagna, $primaryHouses, true)) {
            $score += 50;
            $lagnaManglik = true;
            $triggers[] = "Mars is in the {$houseFromLagna}th house from your Ascendant (Lagna).";
        } elseif (in_array($houseFromLagna, $secondaryHouses, true)) {
            $score += 25;
            $lagnaManglik = true;
            $triggers[] = "Mars is in the 2nd house from your Ascendant (South Indian Kuja Dosha house).";
        }

        $moonManglik = false;
        if (in_array($houseFromMoon, $primaryHouses, true)) {
            $score += 30;
            $moonManglik = true;
            $triggers[] = "Mars is in the {$houseFromMoon}th house from your Moon Sign (Chandra Kundli).";
        } elseif (in_array($houseFromMoon, $secondaryHouses, true)) {
            $score += 15;
            $moonManglik = true;
            $triggers[] = "Mars is in the 2nd house from your Moon Sign (Chandra Kundli).";
        }

        $venusManglik = false;
        if (in_array($houseFromVenus, $primaryHouses, true)) {
            $score += 20;
            $venusManglik = true;
            $triggers[] = "Mars is in the {$houseFromVenus}th house from Venus (Shukra Kundli).";
        } elseif (in_array($houseFromVenus, $secondaryHouses, true)) {
            $score += 10;
            $venusManglik = true;
            $triggers[] = "Mars is in the 2nd house from Venus (Shukra Kundli).";
        }

        // 2. Classical Cancellation Rules (Mangal Dosha Bhanga)
        $cancellations = array();
        $rawScore = $score;

        if ($score > 0) {
            // Rule A: Own Sign (Aries=1, Scorpio=8) or Exalted (Capricorn=10)
            if (in_array($marsSign, array(1, 8, 10), true)) {
                $score = 0;
                $cancellations[] = 'Mars is placed in its own sign (Aries/Scorpio) or exalted sign (Capricorn), which completely neutralizes Mangal Dosha.';
            }

            // Rule B: Specific House-Sign Exceptions
            $houseSignExceptions = array(
                1 => array(1, 5),
                2 => array(3, 6),
                4 => array(1, 8),
                7 => array(4, 10),
                8 => array(9, 12),
                12 => array(2, 7),
            );

            if ($score > 0 && isset($houseSignExceptions[$houseFromLagna]) && in_array($marsSign, $houseSignExceptions[$houseFromLagna], true)) {
                $score *= 0.5;
                $cancellations[] = "Mars in house {$houseFromLagna} occupies a classically neutralizing sign, reducing dosha intensity by 50%.";
            }

            // Rule C: Benefic Jupiter Conjunction (1) or Aspect (5, 7, 9) on Mars
            $jupToMarsHouse = (($marsSign - $jupSign + 12) % 12) + 1;
            if ($score > 0 && in_array($jupToMarsHouse, array(1, 5, 7, 9), true)) {
                $score *= 0.5;
                $cancellations[] = 'Benefic Jupiter aspects or conjuncts Mars, softening fiery energy and reducing intensity by 50%.';
            }

            // Rule D: Age 28+ Maturity Reduction
            $age = (new \DateTime($dob))->diff(new \DateTime('now'))->y;
            if ($score > 0 && $age >= 28) {
                $score *= 0.75;
                $cancellations[] = "Native is {$age} years old (above age 28), naturally mellowing Mars's intensity.";
            }
        }

        $finalScore = (int) round($score);

        // 3. Final Classification
        if ($rawScore === 0) {
            $isManglik = false;
            $status = 'Non-Manglik';
            $severity = 'None';
            $verdict = 'You do not have Manglik Dosha in your birth chart. Mars is harmoniously placed and poses no Kuja Dosha restrictions for marriage.';
        } elseif ($finalScore === 0) {
            $isManglik = false;
            $status = 'Non-Manglik (Dosha Cancelled)';
            $severity = 'Cancelled';
            $verdict = 'Although Mars occupies a Manglik house position, classical Vedic cancellation rules (Mangal Dosha Bhanga) fully neutralize its effects.';
        } elseif ($finalScore < 50) {
            $isManglik = true;
            $status = 'Anshik Manglik (Low / Partial)';
            $severity = 'Low';
            $verdict = 'You have a mild (Anshik) Manglik Dosha. Its influence is gentle and easily balanced through basic horoscope matching or simple remedies.';
        } elseif ($finalScore < 80) {
            $isManglik = true;
            $status = 'Manglik (Moderate)';
            $severity = 'Moderate';
            $verdict = 'You have Manglik Dosha in your birth chart. Matching horoscopes carefully before marriage helps channel Mars’s dynamic energy constructively.';
        } else {
            $isManglik = true;
            $status = 'Purna Manglik (High)';
            $severity = 'High';
            $verdict = 'You have High (Purna) Manglik Dosha across multiple reference charts. Marriage with another Manglik partner or following Vedic remedies is recommended.';
        }

        $houseEffects = array(
            1 => 'Mars in the 1st house gives strong willpower, courage, and directness, which can sometimes come across as impatience or assertiveness in close relationships.',
            2 => 'Mars in the 2nd house impacts family dynamics, finances, and speech—encouraging mindful, gentle communication with family and spouse.',
            4 => 'Mars in the 4th house brings high energy into domestic life and property matters, requiring conscious calm to maintain peace at home.',
            7 => 'Mars in the 7th house directly energizes marriage and partnerships, making mutual respect, patience, and shared goals essential.',
            8 => 'Mars in the 8th house intensifies emotional transformation, shared finances, and in-law dynamics, rewarding transparency and trust.',
            12 => 'Mars in the 12th house influences sleep, personal space, and expenses, encouraging emotional openness and balanced rest.',
        );

        $effectNote = $houseEffects[$houseFromLagna]
            ?? "Mars is placed in your {$houseFromLagna}th house, supporting energy and drive without triggering traditional Manglik house friction.";

        $dt = new \DateTime("{$dob} {$timeNormalized}", new \DateTimeZone('Asia/Kolkata'));

        return array(
            'native_name' => trim($name),
            'gender' => ucfirst(strtolower(trim($gender))),
            'dob' => $dt->format('j F Y'),
            'tob' => $dt->format('h:i A'),
            'birth_place' => trim($birthPlace),
            'is_manglik' => $isManglik,
            'status' => $status,
            'severity' => $severity,
            'score' => $finalScore,
            'verdict' => $verdict,
            'effect_note' => $effectNote,
            'mars_details' => array(
                'rashi' => explode(' ', $mars['rashi'])[0],
                'degree' => $mars['degree_formatted'],
                'nakshatra' => $mars['nakshatra'] . ' (Pada ' . $mars['pada'] . ')',
                'lagna_sign' => explode(' ', $lagna['rashi'])[0],
                'moon_sign' => explode(' ', $moon['rashi'])[0],
                'house_from_lagna' => $houseFromLagna,
                'house_from_moon' => $houseFromMoon,
                'house_from_venus' => $houseFromVenus,
                'lagna_manglik' => $lagnaManglik,
                'moon_manglik' => $moonManglik,
                'venus_manglik' => $venusManglik,
            ),
            'triggers' => $triggers,
            'cancellations' => $cancellations,
            'remedies' => array(
                'Recite or listen to Hanuman Chalisa regularly, especially on Tuesdays.',
                'Offer red lentils (Masoor Dal) or jaggery to those in need on Tuesdays.',
                'Chant the Mangal Beej Mantra: "Om Kram Kreem Kroum Sah Bhaumaya Namah" (108 times on Tuesdays).',
                'Perform proper Ashtakoota & Mangal Dosha Kundli Milan before finalizing marriage.',
            ),
        );
    }

    public function generateSadeSatiReport(
        string $name,
        string $gender,
        string $dob,
        string $tob,
        string $birthPlace,
        ?float $lat = null,
        ?float $lng = null
    ): array {
        $timeNormalized = !empty($tob) ? $tob : '12:00';

        // 1. Natal Birth Chart (to get Janma Rashi / Moon Sign)
        $natalChart = $this->vedicService->calculateChart($dob, $timeNormalized, $birthPlace, $lat, $lng);
        $natalMoon = $natalChart['planets']['Moon'];
        $natalLagna = $natalChart['planets']['Ascendant (Lagna)'];

        $moonSignName = explode(' ', $natalMoon['rashi'])[0];
        $moonSignIdx = $natalMoon['rashi_number'] - 1; // 0 = Aries .. 11 = Pisces

        // 2. Live Transit Saturn Position Today
        $today = now('Asia/Kolkata');
        $transitChart = $this->vedicService->calculateChart(
            $today->format('Y-m-d'),
            '06:00',
            'Ujjain, India',
            23.1765,
            75.7885
        );
        $transitSaturn = $transitChart['planets']['Saturn'];
        $transitSaturnSign = explode(' ', $transitSaturn['rashi'])[0];
        $saturnSignIdx = $transitSaturn['rashi_number'] - 1;

        // 1-indexed house of Transit Saturn from Natal Moon (1 to 12)
        $houseFromMoon = (($saturnSignIdx - $moonSignIdx + 12) % 12) + 1;

        $signs = array(
            'Aries',
            'Taurus',
            'Gemini',
            'Cancer',
            'Leo',
            'Virgo',
            'Libra',
            'Scorpio',
            'Sagittarius',
            'Capricorn',
            'Aquarius',
            'Pisces'
        );

        // 3. Determine Current Sade Sati / Dhaiya Status
        $isActive = false;
        $status = 'Not Active';
        $phaseTitle = 'No Active Sade Sati';
        $phaseDesc = '';

        if ($houseFromMoon === 12) {
            $isActive = true;
            $status = 'Active — 1st Phase (Rising / Udaya)';
            $phaseTitle = '1st Phase: Rising Phase (Udaya)';
            $phaseDesc = "Transit Saturn is in {$transitSaturnSign} (12th house from your natal Moon in {$moonSignName}). This opening 2.5-year phase brings financial restructuring, rising expenses, deeper introspection, and preparation for major personal growth.";
        } elseif ($houseFromMoon === 1) {
            $isActive = true;
            $status = 'Active — 2nd Phase (Peak / Shikhar)';
            $phaseTitle = '2nd Phase: Peak Phase (Shikhar)';
            $phaseDesc = "Transit Saturn is transiting directly over your natal Moon in {$moonSignName} (1st house). This middle 2.5-year phase tests emotional resilience, discipline, health routines, and patience while reshaping your long-term priorities.";
        } elseif ($houseFromMoon === 2) {
            $isActive = true;
            $status = 'Active — 3rd Phase (Setting / Ast)';
            $phaseTitle = '3rd Phase: Setting Phase (Ast)';
            $phaseDesc = "Transit Saturn is in {$transitSaturnSign} (2nd house from your natal Moon in {$moonSignName}). In this closing 2.5-year phase, mental pressure steadily lifts as Saturn rewards past hard work and teaches mindful speech and disciplined savings.";
        } elseif ($houseFromMoon === 4 || $houseFromMoon === 8) {
            $dhaiyaType = $houseFromMoon === 4 ? 'Kantaka Shani (4th House Dhaiya)' : 'Ashtama Shani (8th House Dhaiya)';
            $status = "Not Active ({$dhaiyaType})";
            $phaseTitle = $dhaiyaType;
            $phaseDesc = "Sade Sati is not active, though Saturn in {$transitSaturnSign} is currently in the {$houseFromMoon}th house from your natal Moon ({$dhaiyaType}), a 2.5-year transit that encourages patience in domestic and career matters.";
        } else {
            $phaseDesc = "Shani Sade Sati is currently not active for your chart. Transit Saturn is in {$transitSaturnSign} ({$houseFromMoon}th house from your natal Moon in {$moonSignName}), allowing steady progress without 7.5-year Saturn pressure.";
        }

        // 4. Calculate Saturn Ingress Dates for the 3 Sade Sati Signs (12th, 1st, 2nd from Moon)
        $piscesEntry = new \DateTime('2025-03-29', new \DateTimeZone('Asia/Kolkata'));
        $daysPerSign = 898; // ~2.46 years per Rashi

        $sign12Idx = ($moonSignIdx + 11) % 12; // 12th from Moon
        $sign1Idx = $moonSignIdx;             // 1st (Moon Sign)
        $sign2Idx = ($moonSignIdx + 1) % 12;  // 2nd from Moon

        $offsetSigns = ($sign12Idx - 11 + 12) % 12;
        $cycleStart = (clone $piscesEntry)->modify('+' . ($offsetSigns * $daysPerSign) . ' days');

        if ($cycleStart > (clone $today)->modify('+2 years') && $houseFromMoon !== 12) {
            $cycleStart->modify('-10776 days');
        }
        $cycleEnd = (clone $cycleStart)->modify('+' . (3 * $daysPerSign) . ' days');
        if ($cycleEnd < $today) {
            $cycleStart->modify('+10776 days');
        }

        $p1Start = clone $cycleStart;
        $p1End = (clone $p1Start)->modify("+{$daysPerSign} days");

        $p2Start = clone $p1End;
        $p2End = (clone $p2Start)->modify("+{$daysPerSign} days");

        $p3Start = clone $p2End;
        $p3End = (clone $p3Start)->modify("+{$daysPerSign} days");

        $phases = array(
            array(
                'phase' => '1st Phase (Rising / Udaya)',
                'transit' => "Saturn in {$signs[$sign12Idx]} (12th from Moon)",
                'start' => $p1Start->format('M Y'),
                'end' => $p1End->format('M Y'),
                'is_current' => ($houseFromMoon === 12),
                'focus' => 'Expenses, travel/relocation, mental restructuring, and letting go of old habits.',
            ),
            array(
                'phase' => '2nd Phase (Peak / Shikhar)',
                'transit' => "Saturn in {$signs[$sign1Idx]} (Over Natal Moon)",
                'start' => $p2Start->format('M Y'),
                'end' => $p2End->format('M Y'),
                'is_current' => ($houseFromMoon === 1),
                'focus' => 'Emotional maturity, career discipline, health balance, and core life responsibility.',
            ),
            array(
                'phase' => '3rd Phase (Setting / Ast)',
                'transit' => "Saturn in {$signs[$sign2Idx]} (2nd from Moon)",
                'start' => $p3Start->format('M Y'),
                'end' => $p3End->format('M Y'),
                'is_current' => ($houseFromMoon === 2),
                'focus' => 'Financial stabilization, family communication, and reaping rewards of sustained effort.',
            ),
        );

        $dt = new \DateTime("{$dob} {$timeNormalized}", new \DateTimeZone('Asia/Kolkata'));

        return array(
            'native_name' => trim($name),
            'gender' => ucfirst(strtolower(trim($gender))),
            'dob' => $dt->format('j F Y'),
            'tob' => $dt->format('h:i A'),
            'birth_place' => trim($birthPlace),
            'moon_sign' => $moonSignName,
            'lagna' => explode(' ', $natalLagna['rashi'])[0],
            'nakshatra' => $natalMoon['nakshatra'] . ' (Pada ' . $natalMoon['pada'] . ')',
            'transit_saturn_sign' => $transitSaturnSign,
            'house_from_moon' => $houseFromMoon,
            'is_active' => $isActive,
            'status' => $status,
            'phase_title' => $phaseTitle,
            'phase_description' => $phaseDesc,
            'phases' => $phases,
            'remedies' => array(
                'Light a mustard or sesame oil diya under a Peepal tree on Saturday evenings.',
                'Recite Hanuman Chalisa or Shani Chalisa regularly, especially on Saturdays.',
                'Chant the Shani Beej Mantra: "Om Pram Preem Proum Sah Shanaischaraya Namah" (108 times on Saturdays).',
                'Practice patience, honesty, and service toward elderly or underprivileged individuals.',
            ),
        );
    }

    public function getChoghadiyaTimetable(
        ?string $date = null,
        string $city = 'Jaipur, Rajasthan, India',
        float $lat = 26.9124,
        float $lng = 75.7873
    ): array {
        $tz = new \DateTimeZone('Asia/Kolkata');
        $targetDate = $date
            ? \DateTime::createFromFormat('Y-m-d', $date, $tz) ?: new \DateTime('now', $tz)
            : new \DateTime('now', $tz);

        $dateStr = $targetDate->format('Y-m-d');
        $nextDate = (clone $targetDate)->modify('+1 day');
        $nextDateStr = $nextDate->format('Y-m-d');
        $dayOfWeek = (int) $targetDate->format('w'); // 0 = Sun ... 6 = Sat

        // 1. Exact Sunrise, Sunset & Next Day Sunrise for Location Coordinates
        $sunToday = date_sun_info($targetDate->getTimestamp(), $lat, $lng);
        $sunNext = date_sun_info($nextDate->getTimestamp(), $lat, $lng);

        $sunriseTs = is_int($sunToday['sunrise'] ?? null) ? $sunToday['sunrise'] : strtotime("{$dateStr} 06:30:00");
        $sunsetTs = is_int($sunToday['sunset'] ?? null) ? $sunToday['sunset'] : strtotime("{$dateStr} 17:45:00");
        $nextSunriseTs = is_int($sunNext['sunrise'] ?? null) ? $sunNext['sunrise'] : strtotime("{$nextDateStr} 06:31:00");

        $choghadiyaMeta = array(
            'Amrit' => array('ruler' => 'Moon', 'nature' => 'Most Auspicious', 'type' => 'good', 'activity' => 'All auspicious works, ceremonies, new ventures & key decisions'),
            'Shubh' => array('ruler' => 'Jupiter', 'nature' => 'Auspicious', 'type' => 'good', 'activity' => 'Marriage, education, spiritual rituals & sacred beginnings'),
            'Labh' => array('ruler' => 'Mercury', 'nature' => 'Auspicious', 'type' => 'good', 'activity' => 'Business start, investments, accounts, trade & learning'),
            'Char' => array('ruler' => 'Venus', 'nature' => 'Good / Dynamic', 'type' => 'neutral', 'activity' => 'Travel, journeys, shifting & movement-related tasks'),
            'Udveg' => array('ruler' => 'Sun', 'nature' => 'Inauspicious', 'type' => 'bad', 'activity' => 'Government or administrative tasks only; avoid new beginnings'),
            'Rog' => array('ruler' => 'Mars', 'nature' => 'Inauspicious', 'type' => 'bad', 'activity' => 'Avoid starting auspicious events or major commitments'),
            'Kaal' => array('ruler' => 'Saturn', 'nature' => 'Inauspicious', 'type' => 'bad', 'activity' => 'Avoid financial investments, travel & auspicious ceremonies'),
        );

        // 8 Day Choghadiya sequences by weekday (0=Sun .. 6=Sat)
        $daySequences = array(
            0 => array('Udveg', 'Char', 'Labh', 'Amrit', 'Kaal', 'Shubh', 'Rog', 'Udveg'),
            1 => array('Amrit', 'Kaal', 'Shubh', 'Rog', 'Udveg', 'Char', 'Labh', 'Amrit'),
            2 => array('Rog', 'Udveg', 'Char', 'Labh', 'Amrit', 'Kaal', 'Shubh', 'Rog'),
            3 => array('Labh', 'Amrit', 'Kaal', 'Shubh', 'Rog', 'Udveg', 'Char', 'Labh'),
            4 => array('Shubh', 'Rog', 'Udveg', 'Char', 'Labh', 'Amrit', 'Kaal', 'Shubh'),
            5 => array('Char', 'Labh', 'Amrit', 'Kaal', 'Shubh', 'Rog', 'Udveg', 'Char'),
            6 => array('Kaal', 'Shubh', 'Rog', 'Udveg', 'Char', 'Labh', 'Amrit', 'Kaal'),
        );

        // 8 Night Choghadiya sequences by weekday (0=Sun .. 6=Sat)
        $nightSequences = array(
            0 => array('Shubh', 'Amrit', 'Char', 'Rog', 'Kaal', 'Labh', 'Udveg', 'Shubh'),
            1 => array('Char', 'Rog', 'Kaal', 'Labh', 'Udveg', 'Shubh', 'Amrit', 'Char'),
            2 => array('Kaal', 'Labh', 'Udveg', 'Shubh', 'Amrit', 'Char', 'Rog', 'Kaal'),
            3 => array('Udveg', 'Shubh', 'Amrit', 'Char', 'Rog', 'Kaal', 'Labh', 'Udveg'),
            4 => array('Amrit', 'Char', 'Rog', 'Kaal', 'Labh', 'Udveg', 'Shubh', 'Amrit'),
            5 => array('Rog', 'Kaal', 'Labh', 'Udveg', 'Shubh', 'Amrit', 'Char', 'Rog'),
            6 => array('Labh', 'Udveg', 'Shubh', 'Amrit', 'Char', 'Rog', 'Kaal', 'Labh'),
        );

        $nowTs = now('Asia/Kolkata')->getTimestamp();
        $currentChoghadiya = null;

        // 2. Build 8 Day Slots
        $daySlotSec = ($sunsetTs - $sunriseTs) / 8;
        $daySlots = array();

        for ($i = 0; $i < 8; $i++) {
            $startTs = (int) round($sunriseTs + ($i * $daySlotSec));
            $endTs = (int) round($sunriseTs + (($i + 1) * $daySlotSec));
            $name = $daySequences[$dayOfWeek][$i];
            $meta = $choghadiyaMeta[$name];
            $isNow = ($nowTs >= $startTs && $nowTs < $endTs);

            $startDt = (new \DateTime("@{$startTs}"))->setTimezone($tz);
            $endDt = (new \DateTime("@{$endTs}"))->setTimezone($tz);

            $slot = array(
                'slot_number' => $i + 1,
                'period' => 'Day',
                'name' => $name,
                'ruler' => $meta['ruler'],
                'nature' => $meta['nature'],
                'type' => $meta['type'],
                'activity' => $meta['activity'],
                'start_time' => $startDt->format('h:i A'),
                'end_time' => $endDt->format('h:i A'),
                'time_range' => $startDt->format('h:i A') . ' - ' . $endDt->format('h:i A'),
                'is_current' => $isNow,
                'minutes_left' => $isNow ? max(1, (int) ceil(($endTs - $nowTs) / 60)) : null,
            );

            if ($isNow) {
                $currentChoghadiya = $slot;
            }
            $daySlots[] = $slot;
        }

        // 3. Build 8 Night Slots
        $nightSlotSec = ($nextSunriseTs - $sunsetTs) / 8;
        $nightSlots = array();

        for ($i = 0; $i < 8; $i++) {
            $startTs = (int) round($sunsetTs + ($i * $nightSlotSec));
            $endTs = (int) round($sunsetTs + (($i + 1) * $nightSlotSec));
            $name = $nightSequences[$dayOfWeek][$i];
            $meta = $choghadiyaMeta[$name];
            $isNow = ($nowTs >= $startTs && $nowTs < $endTs);

            $startDt = (new \DateTime("@{$startTs}"))->setTimezone($tz);
            $endDt = (new \DateTime("@{$endTs}"))->setTimezone($tz);

            $slot = array(
                'slot_number' => $i + 1,
                'period' => 'Night',
                'name' => $name,
                'ruler' => $meta['ruler'],
                'nature' => $meta['nature'],
                'type' => $meta['type'],
                'activity' => $meta['activity'],
                'start_time' => $startDt->format('h:i A'),
                'end_time' => $endDt->format('h:i A'),
                'time_range' => $startDt->format('h:i A') . ' - ' . $endDt->format('h:i A'),
                'is_current' => $isNow,
                'minutes_left' => $isNow ? max(1, (int) ceil(($endTs - $nowTs) / 60)) : null,
            );

            if ($isNow) {
                $currentChoghadiya = $slot;
            }
            $nightSlots[] = $slot;
        }

        $sunriseFormatted = (new \DateTime("@{$sunriseTs}"))->setTimezone($tz)->format('h:i A');
        $sunsetFormatted = (new \DateTime("@{$sunsetTs}"))->setTimezone($tz)->format('h:i A');
        $nextSunriseFormatted = (new \DateTime("@{$nextSunriseTs}"))->setTimezone($tz)->format('h:i A');

        return array(
            'date' => $targetDate->format('j F Y'),
            'raw_date' => $dateStr,
            'day_name' => $targetDate->format('l'),
            'city' => $city,
            'latitude' => $lat,
            'longitude' => $lng,
            'sunrise' => $sunriseFormatted,
            'sunset' => $sunsetFormatted,
            'next_sunrise' => $nextSunriseFormatted,
            'is_today' => ($dateStr === now('Asia/Kolkata')->format('Y-m-d')),
            'current_choghadiya' => $currentChoghadiya,
            'day_choghadiya' => $daySlots,
            'night_choghadiya' => $nightSlots,
        );
    }

    protected function generateKundliAiInsights(
        string $name,
        string $lagna,
        string $lagnaLord,
        string $moonSign,
        string $sunSign,
        string $nakshatra,
        int $moonPada,
        array $planetaryPositions,
        array $vimshottariDasha,
        array $doshas,
        array $yogas
    ): array {
        // 1. Summarize Planetary Placements for the Prompt
        $planetLines = array();
        foreach ($planetaryPositions as $p) {
            if ($p['planet'] === 'Ascendant')
                continue;
            $planetLines[] = "{$p['planet']} in {$p['sign']} (House {$p['house']}, {$p['nakshatra']})";
        }
        $planetsSummary = implode('; ', $planetLines);

        // 2. Identify Currently Active Mahadasha
        $activeDashaText = 'Unknown';
        foreach ($vimshottariDasha as $d) {
            if (!empty($d['active'])) {
                $activeDashaText = "{$d['planet']} Mahadasha ({$d['start']} to {$d['end']})";
                break;
            }
        }

        // 3. Summarize Doshas & Yogas
        $manglikStatus = $doshas['manglik_dosha']['status'] ?? 'Non-Manglik';
        $kalsarpaStatus = !empty($doshas['kalsarpa_dosha']['is_present'])
            ? ($doshas['kalsarpa_dosha']['type'] ?? 'Present')
            : 'None';
        $sadeSatiStatus = $doshas['sade_sati']['status'] ?? 'Not Active';

        $yogaNames = array_map(fn($y) => $y['name'], $yogas);
        $yogasSummary = !empty($yogaNames) ? implode(', ', $yogaNames) : 'Standard planetary distribution';

        $apiKey = config('services.gemini.key');

        if (!empty($apiKey)) {
            $prompt = <<<PROMPT
                You are a senior Vedic Astrologer (Jyotish Acharya) writing a personalized Janam Kundli analysis and remedial report for {$name}.

                CALCULATED BIRTH CHART (NIRAYANA / LAHIRI):
                - Ascendant (Lagna): {$lagna} (Lagna Lord: {$lagnaLord})
                - Moon Sign (Janma Rashi): {$moonSign}
                - Sun Sign (Surya Rashi): {$sunSign}
                - Birth Nakshatra: {$nakshatra} (Pada {$moonPada})
                - Planetary Placements: {$planetsSummary}
                - Current Active Period: {$activeDashaText}
                - Dosha Analysis: Manglik = {$manglikStatus}; Kalsarpa = {$kalsarpaStatus}; Sade Sati = {$sadeSatiStatus}
                - Active Yogas: {$yogasSummary}

                STRICT ASTROLOGICAL RULES:
                1. Base every insight strictly on the exact house placements, Lagna Lord, active Mahadasha, Yogas, and Doshas listed above. Reference specific houses/planets naturally (e.g., 10th house for career, 7th house for marriage, Lagna lord strength).
                2. GEMSTONE SAFETY RULE (CRITICAL): Recommend gemstones ONLY for functional benefic planets (especially the 1st/Lagna Lord, 5th Lord, or 9th Lord for {$lagna} Ascendant). NEVER recommend gemstones for dusthana lords (6th, 8th, 12th) or malefic dosha planets (for example, never recommend Red Coral or Blue Sapphire if Mars/Saturn is a functional malefic for {$lagna} Lagna).
                3. Keep the tone warm, authoritative, practical, and encouraging. Avoid generic AI filler words ("tapestry", "symphony", "cosmic dance", "delve").

                REQUIRED JSON OUTPUT FORMAT:
                Return ONLY a raw, valid JSON object with these exact keys:
                {
                "remedies": {
                    "rudraksha": "Specific Mukhi Rudraksha suited to {$lagna} Lagna & active doshas/dasha with brief 1-sentence reason",
                    "gemstones": [
                    {
                        "type": "Life Stone (Lagna Lord)",
                        "stone": "Gemstone name with Hindi name, e.g. 'Emerald (Panna)'",
                        "finger": "Exact finger, hand, metal & wearing day, e.g. 'Little Finger of Right Hand in Gold on Wednesday'",
                        "mantra": "Authentic Vedic Beej Mantra for that planet"
                    },
                    {
                        "type": "Lucky / Bhagya Stone (9th Lord)",
                        "stone": "Gemstone name with Hindi name",
                        "finger": "Exact finger, hand, metal & wearing day",
                        "mantra": "Authentic Vedic Beej Mantra for that planet"
                    }
                    ],
                    "general_remedies": [
                    "Actionable Vedic remedy #1 tailored to active Doshas ({$manglikStatus}, {$kalsarpaStatus}, {$sadeSatiStatus}) or {$activeDashaText}",
                    "Actionable Vedic remedy #2 (mantra, charity/daan, or weekday habit)",
                    "Actionable Vedic remedy #3 (lifestyle or spiritual practice)"
                    ]
                },
                "ai_predictions": {
                    "personality": "3-4 sentences analyzing {$name}'s core temperament, strengths, and mindset based on {$lagna} Lagna, {$moonSign} Moon, and {$nakshatra} Nakshatra.",
                    "career_wealth": "3-4 sentences analyzing career direction, financial growth, and active {$activeDashaText} opportunities based on 2nd, 10th, and 11th house placements.",
                    "love_marriage": "3-4 sentences analyzing relationship style, marriage timing/harmony, 7th house influences, and {$manglikStatus} status.",
                    "health": "2-3 sentences highlighting physical constitution, areas needing preventive care based on {$lagna} Lagna and 6th/8th house factors, and wellness habits.",
                    "gemstone_remedies": "2-3 sentences summarizing why the recommended gemstones and Rudraksha harmonize {$name}'s chart and advising proper purification before wearing."
                }
                }
                PROMPT;

            try {
                $response = \Illuminate\Support\Facades\Http::timeout(30)->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.1-flash-lite:generateContent?key={$apiKey}",
                    [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt]
                                ]
                            ]
                        ],
                        'generationConfig' => [
                            'responseMimeType' => 'application/json',
                            'temperature' => 0.65,
                        ],
                    ]
                );

                if ($response->successful()) {
                    $rawText = $response->json('candidates.0.content.parts.0.text', '');
                    $parsed = json_decode(trim($rawText), true);

                    if (is_array($parsed) && isset($parsed['remedies'], $parsed['ai_predictions'])) {
                        return $parsed;
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Gemini Kundli Insights Failed: ' . $e->getMessage());
            }
        }

        // Lagna-accurate Fallback if API is offline
        $lagnaGemstones = array(
            'Aries' => array('stone' => 'Red Coral (Moonga)', 'finger' => 'Ring Finger in Gold/Copper on Tuesday', 'mantra' => 'Om Kram Kreem Kroum Sah Bhaumaya Namah', 'rudraksha' => '3-Mukhi Rudraksha for Mars vitality and confidence.'),
            'Taurus' => array('stone' => 'Diamond / White Sapphire', 'finger' => 'Middle or Ring Finger in Silver/Platinum on Friday', 'mantra' => 'Om Dram Dreem Droum Sah Shukraya Namah', 'rudraksha' => '6-Mukhi Rudraksha for Venus harmony and stability.'),
            'Gemini' => array('stone' => 'Emerald (Panna)', 'finger' => 'Little Finger in Gold on Wednesday', 'mantra' => 'Om Bram Brim Broum Sah Budhaya Namah', 'rudraksha' => '4-Mukhi Rudraksha for Mercury intellect and clarity.'),
            'Cancer' => array('stone' => 'Natural Pearl (Moti)', 'finger' => 'Little Finger in Silver on Monday', 'mantra' => 'Om Shram Shreem Shroum Sah Chandraya Namah', 'rudraksha' => '2-Mukhi Rudraksha for emotional balance and peace.'),
            'Leo' => array('stone' => 'Ruby (Manik)', 'finger' => 'Ring Finger in Gold on Sunday', 'mantra' => 'Om Hram Hreem Hroum Sah Suryaya Namah', 'rudraksha' => '12-Mukhi Rudraksha for Surya leadership and vitality.'),
            'Virgo' => array('stone' => 'Emerald (Panna)', 'finger' => 'Little Finger in Gold on Wednesday', 'mantra' => 'Om Bram Brim Broum Sah Budhaya Namah', 'rudraksha' => '4-Mukhi Rudraksha for analytical clarity and focus.'),
            'Libra' => array('stone' => 'Opal / White Sapphire', 'finger' => 'Ring Finger in Silver on Friday', 'mantra' => 'Om Dram Dreem Droum Sah Shukraya Namah', 'rudraksha' => '6-Mukhi Rudraksha for balance and relationship harmony.'),
            'Scorpio' => array('stone' => 'Red Coral (Moonga)', 'finger' => 'Ring Finger in Gold/Copper on Tuesday', 'mantra' => 'Om Kram Kreem Kroum Sah Bhaumaya Namah', 'rudraksha' => '3-Mukhi Rudraksha for resilience and focused energy.'),
            'Sagittarius' => array('stone' => 'Yellow Sapphire (Pukhraj)', 'finger' => 'Index Finger in Gold on Thursday', 'mantra' => 'Om Gram Greem Groum Sah Gurave Namah', 'rudraksha' => '5-Mukhi Rudraksha for Jupiter wisdom and fortune.'),
            'Capricorn' => array('stone' => 'Blue Sapphire / Amethyst', 'finger' => 'Middle Finger in Silver/Panchdhatu on Saturday', 'mantra' => 'Om Pram Preem Proum Sah Shanaischaraya Namah', 'rudraksha' => '7-Mukhi Rudraksha for Saturn stability and career growth.'),
            'Aquarius' => array('stone' => 'Blue Sapphire / Amethyst', 'finger' => 'Middle Finger in Silver/Panchdhatu on Saturday', 'mantra' => 'Om Pram Preem Proum Sah Shanaischaraya Namah', 'rudraksha' => '7-Mukhi Rudraksha for steady progress and discipline.'),
            'Pisces' => array('stone' => 'Yellow Sapphire (Pukhraj)', 'finger' => 'Index Finger in Gold on Thursday', 'mantra' => 'Om Gram Greem Groum Sah Gurave Namah', 'rudraksha' => '5-Mukhi Rudraksha for spiritual clarity and prosperity.'),
        );

        $gem = $lagnaGemstones[$lagna] ?? $lagnaGemstones['Aries'];

        return array(
            'remedies' => array(
                'rudraksha' => $gem['rudraksha'],
                'gemstones' => array(
                    array(
                        'type' => 'Life Stone (Lagna Lord)',
                        'stone' => $gem['stone'],
                        'finger' => $gem['finger'],
                        'mantra' => $gem['mantra'],
                    ),
                ),
                'general_remedies' => array(
                    "Offer water to the rising Sun each morning and chant your Lagna Lord ({$lagnaLord}) mantra.",
                    'Practice regular charity (Daan) on Saturdays or Tuesdays to harmonize active planetary transits.',
                ),
            ),
            'ai_predictions' => array(
                'personality' => "Born under {$lagna} Ascendant with Moon in {$moonSign} ({$nakshatra} Nakshatra), {$name} blends adaptability, sharp intuition, and thoughtful determination.",
                'career_wealth' => "Guided by {$lagnaLord} as Lagna Lord and currently running {$activeDashaText}, steady skill mastery and strategic planning support progressive career and financial growth.",
                'love_marriage' => "Relationships thrive on open communication, mutual respect, and emotional sincerity ({$manglikStatus}).",
                'health' => "Maintaining consistent sleep, balanced nutrition, and regular physical movement keeps vitality strong.",
                'gemstone_remedies' => "Wearing {$gem['stone']} strengthens your Lagna Lord {$lagnaLord}; consult a qualified Vedic astrologer for exact carat weight and purification.",
            ),
        );
    }

    public function calculateMuhuratFinder(
        string $eventType,
        string $date,
        string $cityName = 'Jaipur, Rajasthan, India',
        float $lat = 26.9124,
        float $lng = 75.7873
    ): array {
        $choghadiya = $this->getChoghadiyaTimetable($date, $cityName, $lat, $lng);
        $panchang = $this->getDailyPanchang($date, $cityName, $lat, $lng);

        $goodTypes = ['Amrit', 'Shubh', 'Labh'];
        if ($eventType === 'travel' || $eventType === 'vehicle') {
            $goodTypes[] = 'Char';
        }

        $auspiciousWindows = [];
        $allSlots = array_merge($choghadiya['day_choghadiya'], $choghadiya['night_choghadiya']);

        foreach ($allSlots as $slot) {
            $isAuspicious = in_array($slot['name'], $goodTypes, true);
            $score = $isAuspicious ? (match($slot['name']) {
                'Amrit' => 98,
                'Shubh' => 92,
                'Labh'  => 88,
                'Char'  => 78,
                default => 60
            }) : 35;

            if ($isAuspicious) {
                $auspiciousWindows[] = [
                    'event_type'     => ucwords(str_replace('_', ' ', $eventType)),
                    'window_name'    => "{$slot['name']} Muhurat ({$slot['period']})",
                    'ruler'          => $slot['ruler'],
                    'time_range'     => $slot['time_range'],
                    'score'          => $score,
                    'nature'         => $slot['nature'],
                    'recommendation' => "Highly favorable window governed by {$slot['ruler']} ({$slot['name']}). {$slot['activity']}.",
                ];
            }
        }

        usort($auspiciousWindows, fn($a, $b) => $b['score'] <=> $a['score']);

        return [
            'event_type'     => ucwords(str_replace('_', ' ', $eventType)),
            'date'           => $choghadiya['date'],
            'city'           => $cityName,
            'sunrise'        => $choghadiya['sunrise'],
            'sunset'         => $choghadiya['sunset'],
            'panchang'       => $panchang,
            'windows'        => $auspiciousWindows,
            'general_advice' => match($eventType) {
                'business' => 'For new business ventures and investments, prioritize Labh and Amrit Muhurats during Shukla Paksha.',
                'property' => 'For buying property or housewarming (Griha Pravesh), Shubh and Amrit Choghadiya windows bring lasting stability.',
                'vehicle'  => 'For vehicle purchase and travel, Char and Shubh muhurats ensure safe journeys and smooth transit.',
                'marriage' => 'For marriage and ceremonies, verify full Ashtakoota Guna Milan alongside Shubh and Amrit Muhurat windows.',
                default    => 'For new beginnings, always select Amrit, Shubh, or Labh windows while avoiding Rahu Kaal.'
            }
        ];
    }

}
