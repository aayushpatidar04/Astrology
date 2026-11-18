<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BabyNameService
{
    public function __construct(
        protected VedicAstrologyService $vedicService,
        protected NumerologyService $numerologyService
    ) {
    }

    // Complete 27 Nakshatras x 4 Padas = 108 Vedic Namakshar Syllables
    protected array $nakshatraSyllables = [
        0  => ['name' => 'Ashwini',            'padas' => [1 => 'Chu',  2 => 'Che',  3 => 'Cho',  4 => 'La']],
        1  => ['name' => 'Bharani',            'padas' => [1 => 'Li',   2 => 'Lu',   3 => 'Le',   4 => 'Lo']],
        2  => ['name' => 'Krittika',           'padas' => [1 => 'A',    2 => 'Ee',   3 => 'U',    4 => 'Ae']],
        3  => ['name' => 'Rohini',             'padas' => [1 => 'O',    2 => 'Va',   3 => 'Vi',   4 => 'Vu']],
        4  => ['name' => 'Mrigashira',         'padas' => [1 => 'Ve',   2 => 'Vo',   3 => 'Ka',   4 => 'Ki']],
        5  => ['name' => 'Ardra',              'padas' => [1 => 'Ku',   2 => 'Gha',  3 => 'Ng',   4 => 'Chha']],
        6  => ['name' => 'Punarvasu',          'padas' => [1 => 'Ke',   2 => 'Ko',   3 => 'Ha',   4 => 'Hi']],
        7  => ['name' => 'Pushya',             'padas' => [1 => 'Hu',   2 => 'He',   3 => 'Ho',   4 => 'Da']],
        8  => ['name' => 'Ashlesha',           'padas' => [1 => 'Di',   2 => 'Du',   3 => 'De',   4 => 'Do']],
        9  => ['name' => 'Magha',              'padas' => [1 => 'Ma',   2 => 'Mi',   3 => 'Mu',   4 => 'Me']],
        10 => ['name' => 'Purva Phalguni',     'padas' => [1 => 'Mo',   2 => 'Ta',   3 => 'Ti',   4 => 'Tu']],
        11 => ['name' => 'Uttara Phalguni',    'padas' => [1 => 'Te',   2 => 'To',   3 => 'Pa',   4 => 'Pi']],
        12 => ['name' => 'Hasta',              'padas' => [1 => 'Pu',   2 => 'Sha',  3 => 'Na',   4 => 'Tha']],
        13 => ['name' => 'Chitra',             'padas' => [1 => 'Pe',   2 => 'Po',   3 => 'Ra',   4 => 'Ri']],
        14 => ['name' => 'Swati',              'padas' => [1 => 'Ru',   2 => 'Re',   3 => 'Ro',   4 => 'Ta']],
        15 => ['name' => 'Vishakha',           'padas' => [1 => 'Ti',   2 => 'Tu',   3 => 'Te',   4 => 'To']],
        16 => ['name' => 'Anuradha',           'padas' => [1 => 'Na',   2 => 'Ni',   3 => 'Nu',   4 => 'Ne']],
        17 => ['name' => 'Jyeshtha',           'padas' => [1 => 'No',   2 => 'Ya',   3 => 'Yi',   4 => 'Yu']],
        18 => ['name' => 'Mula',               'padas' => [1 => 'Ye',   2 => 'Yo',   3 => 'Bha',  4 => 'Bhi']],
        19 => ['name' => 'Purva Ashadha',      'padas' => [1 => 'Bhu',  2 => 'Dha',  3 => 'Pha',  4 => 'Dha']],
        20 => ['name' => 'Uttara Ashadha',     'padas' => [1 => 'Bhe',  2 => 'Bho',  3 => 'Ja',   4 => 'Ji']],
        21 => ['name' => 'Shravana',           'padas' => [1 => 'Khi',  2 => 'Khu',  3 => 'Khe',  4 => 'Kho']],
        22 => ['name' => 'Dhanishta',          'padas' => [1 => 'Ga',   2 => 'Gi',   3 => 'Gu',   4 => 'Ge']],
        23 => ['name' => 'Shatabhisha',        'padas' => [1 => 'Go',   2 => 'Sa',   3 => 'Si',   4 => 'Su']],
        24 => ['name' => 'Purva Bhadrapada',   'padas' => [1 => 'Se',   2 => 'So',   3 => 'Da',   4 => 'Di']],
        25 => ['name' => 'Uttara Bhadrapada',  'padas' => [1 => 'Du',   2 => 'Tha',  3 => 'Jha',  4 => 'Da']],
        26 => ['name' => 'Revati',             'padas' => [1 => 'De',   2 => 'Do',   3 => 'Cha',  4 => 'Chi']],
    ];

    public function generateBabyNames(
        string $dob,
        string $tob,
        string $birthPlace,
        string $gender = 'boy',
        string $style = 'Modern & Sanskrit',
        ?float $lat = null,
        ?float $lng = null
    ): array {
        $chart = $this->vedicService->calculateChart($dob, $tob, $birthPlace);
        $moon  = $chart['planets']['Moon'] ?? [];
        $lagna = $chart['planets']['Ascendant (Lagna)'] ?? [];

        $nakshatraSpan  = 360.0 / 27.0;
        $nakshatraIndex = (int) floor(($moon['full_degree'] ?? 0.0) / $nakshatraSpan);
        $pada           = (int) ($moon['pada'] ?? 1);

        $nakData          = $this->nakshatraSyllables[$nakshatraIndex] ?? $this->nakshatraSyllables[0];
        $primarySyllable  = $nakData['padas'][$pada] ?? 'A';
        $allPadaSyllables = array_values($nakData['padas']);
        $moonSignName     = explode(' ', $moon['rashi'] ?? 'Aries')[0];

        $genderLabel = match (strtolower(trim($gender))) {
            'girl', 'female' => 'Baby Girl',
            'unisex'         => 'Unisex / Gender-Neutral',
            default          => 'Baby Boy',
        };

        $names = $this->fetchNamesFromGemini(
            $primarySyllable,
            $allPadaSyllables,
            $nakData['name'],
            $pada,
            $moonSignName,
            $genderLabel,
            $style
        );

        foreach ($names as &$item) {
            $numResult = $this->numerologyService->calculateNumerology($item['name'], $dob, 'chaldean');
            $chaldeanData = $numResult['core_numbers']['name_number'] ?? $numResult['core_numbers']['chaldean_name'] ?? [];
            $item['numerology_number'] = $chaldeanData['number'] ?? 1;
            $item['is_aligned']        = $chaldeanData['is_aligned'] ?? true;
        }

        return [
            'astrology_profile' => [
                'dob'              => $dob,
                'tob'              => $tob,
                'birth_place'      => $birthPlace,
                'moon_sign'        => $moonSignName,
                'lagna'            => explode(' ', $lagna['rashi'] ?? 'Gemini')[0],
                'nakshatra'        => $nakData['name'],
                'nakshatra_lord'   => $moon['nakshatra_lord'] ?? 'Venus',
                'pada'             => $pada,
                'primary_syllable' => $primarySyllable,
                'all_syllables'    => $allPadaSyllables,
            ],
            'filters' => [
                'gender' => $genderLabel,
                'style'  => $style,
            ],
            'names' => $names,
        ];
    }

    protected function fetchNamesFromGemini(
        string $primarySyllable,
        array $allSyllables,
        string $nakshatra,
        int $pada,
        string $rashi,
        string $genderLabel,
        string $style
    ): array {
        $apiKey = config('services.gemini.key');
        $syllablesList = implode(', ', $allSyllables);

        if (!empty($apiKey)) {
            $prompt = "You are an expert Vedic Pandit and Indian Baby Naming Specialist (Namakaran Acharya).\n\n"
                . "BABY ASTROLOGICAL DETAILS:\n"
                . "- Moon Sign (Rashi): {$rashi}\n"
                . "- Birth Star (Nakshatra): {$nakshatra} (Pada {$pada})\n"
                . "- Primary Auspicious Starting Syllable (Namakshar): \"{$primarySyllable}\"\n"
                . "- Other Acceptable Syllables for this Nakshatra: {$syllablesList}\n"
                . "- Gender: {$genderLabel}\n"
                . "- Preferred Naming Style: {$style}\n\n"
                . "TASK:\n"
                . "Suggest 12 meaningful, authentic, easy-to-pronounce Indian baby names.\n"
                . "- At least 9 names MUST start with the exact phonetic sound \"{$primarySyllable}\".\n"
                . "- Up to 3 names may start with alternate syllables ({$syllablesList}) of {$nakshatra}.\n"
                . "- Provide accurate Sanskrit/Hindi meanings and origin tags.\n"
                . "- Return ONLY a valid JSON object with a single key \"names\" containing an array of 12 objects:\n"
                . "{\n"
                . "  \"names\": [\n"
                . "    {\n"
                . "      \"name\": \"Name\",\n"
                . "      \"starting_syllable\": \"{$primarySyllable}\",\n"
                . "      \"meaning\": \"Clear, authentic meaning in 6-12 words\",\n"
                . "      \"origin\": \"Sanskrit / Vedic / Modern Indian\",\n"
                . "      \"rashi_note\": \"Why this name suits {$rashi} & {$nakshatra}\"\n"
                . "    }\n"
                . "  ]\n"
                . "}";

            try {
                $response = Http::timeout(25)->post(
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
                            'temperature'      => 0.75,
                        ],
                    ]
                );

                if ($response->successful()) {
                    $rawText = $response->json('candidates.0.content.parts.0.text', '');
                    $parsed = json_decode(trim($rawText), true);
                    if (\is_array($parsed) && !empty($parsed['names'])) {
                        return $parsed['names'];
                    }
                }
            } catch (\Exception $e) {
                Log::error('Gemini Baby Name Generation Failed: ' . $e->getMessage());
            }
        }

        $s = ucfirst(strtolower($primarySyllable));
        return [
            ['name' => "{$s}haan",   'starting_syllable' => $s, 'meaning' => 'Dawn, first ray of light, auspicious new beginning', 'origin' => 'Sanskrit', 'rashi_note' => "Harmonizes with {$nakshatra} Pada {$pada}"],
            ['name' => "{$s}ansh",   'starting_syllable' => $s, 'meaning' => 'Divine part, bright spark of wisdom and vitality',   'origin' => 'Sanskrit', 'rashi_note' => "Aligned with {$rashi} Moon energy"],
            ['name' => "{$s}vik",    'starting_syllable' => $s, 'meaning' => 'Unique, courageous, full of positive radiance',      'origin' => 'Vedic',    'rashi_note' => "Carries {$primarySyllable} phonetic vibration"],
            ['name' => "{$s}yaan",   'starting_syllable' => $s, 'meaning' => 'Knowledge, swift progress, and graceful wisdom',     'origin' => 'Sanskrit', 'rashi_note' => "Supports {$nakshatra} qualities"],
            ['name' => "{$s}anya",   'starting_syllable' => $s, 'meaning' => 'Graceful, incomparable, blessed with good fortune',  'origin' => 'Sanskrit', 'rashi_note' => "Auspicious for {$rashi} Rashi"],
            ['name' => "{$s}ika",    'starting_syllable' => $s, 'meaning' => 'Pure, radiant, symbol of harmony and prosperity',   'origin' => 'Sanskrit', 'rashi_note' => "Matches {$nakshatra} Pada {$pada}"],
        ];
    }
}
