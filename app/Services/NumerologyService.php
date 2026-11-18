<?php

namespace App\Services;

class NumerologyService
{
    protected array $chaldeanMap = [
        'A' => 1, 'I' => 1, 'J' => 1, 'Q' => 1, 'Y' => 1,
        'B' => 2, 'K' => 2, 'R' => 2,
        'C' => 3, 'G' => 3, 'L' => 3, 'S' => 3,
        'D' => 4, 'M' => 4, 'T' => 4,
        'E' => 5, 'H' => 5, 'N' => 5, 'X' => 5,
        'U' => 6, 'V' => 6, 'W' => 6,
        'O' => 7, 'Z' => 7,
        'F' => 8, 'P' => 8,
    ];

    protected array $pythagoreanMap = [
        'A' => 1, 'J' => 1, 'S' => 1,
        'B' => 2, 'K' => 2, 'T' => 2,
        'C' => 3, 'L' => 3, 'U' => 3,
        'D' => 4, 'M' => 4, 'V' => 4,
        'E' => 5, 'N' => 5, 'W' => 5,
        'F' => 6, 'O' => 6, 'X' => 6,
        'G' => 7, 'P' => 7, 'Y' => 7,
        'H' => 8, 'Q' => 8, 'Z' => 8,
        'I' => 9, 'R' => 9,
    ];

    protected array $numberProfiles = [
        1 => [
            'planet'      => 'Sun (Surya)',
            'title'       => 'The Leader & Pioneer',
            'traits'      => 'Independent, ambitious, original, confident, and naturally authoritative.',
            'career'      => 'Leadership roles, entrepreneurship, management, administration, and creative direction.',
            'love'        => 'Loyal and protective partner; thrives when given mutual respect and space for individuality.',
            'lucky_color' => 'Golden Yellow, Ruby Red, Saffron',
            'lucky_days'  => 'Sunday, Monday',
            'gemstone'    => 'Ruby (Manik)',
            'friendly'    => '1, 2, 3, 9',
        ],
        2 => [
            'planet'      => 'Moon (Chandra)',
            'title'       => 'The Peacemaker & Intuitive',
            'traits'      => 'Empathetic, diplomatic, imaginative, cooperative, and emotionally perceptive.',
            'career'      => 'Counseling, design, hospitality, psychology, diplomacy, and healing arts.',
            'love'        => 'Deeply romantic and nurturing; seeks emotional security and gentle communication.',
            'lucky_color' => 'Pearl White, Silver, Sea Green',
            'lucky_days'  => 'Monday, Friday',
            'gemstone'    => 'Natural Pearl (Moti)',
            'friendly'    => '1, 2, 3, 5',
        ],
        3 => [
            'planet'      => 'Jupiter (Brihaspati)',
            'title'       => 'The Creator & Communicator',
            'traits'      => 'Expressive, optimistic, wise, artistic, and socially inspiring.',
            'career'      => 'Teaching, writing, media, law, finance, consulting, and public speaking.',
            'love'        => 'Warm, playful, and generous; values intellectual connection and shared optimism.',
            'lucky_color' => 'Turmeric Yellow, Marigold, Purple',
            'lucky_days'  => 'Thursday, Tuesday',
            'gemstone'    => 'Yellow Sapphire (Pukhraj)',
            'friendly'    => '1, 2, 3, 9',
        ],
        4 => [
            'planet'      => 'Rahu (North Node)',
            'title'       => 'The Builder & Strategist',
            'traits'      => 'Practical, analytical, unconventional, hardworking, and detail-oriented.',
            'career'      => 'Engineering, software architecture, data analytics, operations, and real estate.',
            'love'        => 'Steady, dependable, and honest; shows love through practical support and loyalty.',
            'lucky_color' => 'Electric Blue, Slate Grey, Khaki',
            'lucky_days'  => 'Saturday, Sunday',
            'gemstone'    => 'Hessonite (Gomed)',
            'friendly'    => '1, 5, 6, 8',
        ],
        5 => [
            'planet'      => 'Mercury (Budha)',
            'title'       => 'The Explorer & Catalyst',
            'traits'      => 'Adaptable, witty, freedom-loving, quick-thinking, and resourceful.',
            'career'      => 'Marketing, sales, travel, trading, journalism, and digital entrepreneurship.',
            'love'        => 'Exciting, communicative, and spontaneous; thrives with an open-minded partner.',
            'lucky_color' => 'Emerald Green, Mint, Pastel Grey',
            'lucky_days'  => 'Wednesday, Friday',
            'gemstone'    => 'Emerald (Panna)',
            'friendly'    => '1, 4, 5, 6',
        ],
        6 => [
            'planet'      => 'Venus (Shukra)',
            'title'       => 'The Nurturer & Harmonizer',
            'traits'      => 'Compassionate, artistic, responsible, charming, and family-oriented.',
            'career'      => 'Fashion, interior design, luxury brands, wellness, education, and arts.',
            'love'        => 'Devoted, affectionate, and harmony-seeking; creates a warm, beautiful home life.',
            'lucky_color' => 'Pastel Pink, Ivory White, Sky Blue',
            'lucky_days'  => 'Friday, Wednesday',
            'gemstone'    => 'Diamond / White Sapphire',
            'friendly'    => '4, 5, 6, 8',
        ],
        7 => [
            'planet'      => 'Ketu (South Node)',
            'title'       => 'The Seeker & Analyst',
            'traits'      => 'Introspective, spiritual, observant, philosophical, and deeply analytical.',
            'career'      => 'Research, science, spirituality, cybersecurity, writing, and specialized consulting.',
            'love'        => 'Values deep soulful understanding and authenticity over superficial socializing.',
            'lucky_color' => 'Smoky White, Light Green, Indigo',
            'lucky_days'  => 'Monday, Thursday',
            'gemstone'    => 'Cat’s Eye (Lehsunia)',
            'friendly'    => '1, 2, 3, 5',
        ],
        8 => [
            'planet'      => 'Saturn (Shani)',
            'title'       => 'The Executive & Achiever',
            'traits'      => 'Resilient, disciplined, ambitious, karmic, and built for long-term authority.',
            'career'      => 'Finance, banking, law, manufacturing, corporate leadership, and infrastructure.',
            'love'        => 'Slow to open up, yet fiercely committed, protective, and steadfast once bonded.',
            'lucky_color' => 'Royal Blue, Navy, Charcoal',
            'lucky_days'  => 'Saturday, Friday',
            'gemstone'    => 'Blue Sapphire / Amethyst',
            'friendly'    => '4, 5, 6, 8',
        ],
        9 => [
            'planet'      => 'Mars (Mangal)',
            'title'       => 'The Humanitarian & Warrior',
            'traits'      => 'Courageous, passionate, generous, idealistic, and driven by purpose.',
            'career'      => 'Defense, sports, surgery, social leadership, fitness, and philanthropy.',
            'love'        => 'Passionate, protective, and wholehearted; thrives when balancing intensity with patience.',
            'lucky_color' => 'Coral Red, Crimson, Warm Orange',
            'lucky_days'  => 'Tuesday, Thursday',
            'gemstone'    => 'Red Coral (Moonga)',
            'friendly'    => '1, 2, 3, 9',
        ],
        11 => [
            'planet'      => 'Moon / Higher Intuition (Master Number)',
            'title'       => 'The Visionary & Illuminator',
            'traits'      => 'Highly intuitive, inspiring, empathetic, idealistic, and spiritually perceptive.',
            'career'      => 'Mentorship, creative innovation, psychology, media, and spiritual leadership.',
            'love'        => 'Seeks a deep soul-level partnership built on empathy and shared ideals.',
            'lucky_color' => 'Silver, Pearl White, Violet',
            'lucky_days'  => 'Monday, Thursday',
            'gemstone'    => 'Natural Pearl (Moti)',
            'friendly'    => '1, 2, 3, 5',
        ],
        22 => [
            'planet'      => 'Rahu / Saturn (Master Builder)',
            'title'       => 'The Master Architect',
            'traits'      => 'Combines visionary intuition with disciplined execution to build lasting systems.',
            'career'      => 'Large-scale entrepreneurship, architecture, technology leadership, and public institutions.',
            'love'        => 'Grounded, loyal, and supportive; values stability and shared long-term goals.',
            'lucky_color' => 'Royal Blue, Cream, Earth Tones',
            'lucky_days'  => 'Saturday, Wednesday',
            'gemstone'    => 'Hessonite / Amethyst',
            'friendly'    => '4, 5, 6, 8',
        ],
        33 => [
            'planet'      => 'Jupiter / Venus (Master Teacher)',
            'title'       => 'The Compassionate Guide',
            'traits'      => 'Uplifting, nurturing, creative, wise, and dedicated to helping others thrive.',
            'career'      => 'Education, healing, arts, counseling, and humanitarian leadership.',
            'love'        => 'Deeply devoted, forgiving, and emotionally generous.',
            'lucky_color' => 'Turmeric Yellow, Rose Pink, Ivory',
            'lucky_days'  => 'Thursday, Friday',
            'gemstone'    => 'Yellow Sapphire (Pukhraj)',
            'friendly'    => '1, 2, 3, 6',
        ],
    ];

    protected function reduceNumber(int $num, bool $keepMaster = true): int
    {
        $masterNumbers = [11, 22, 33];

        while ($num > 9) {
            if ($keepMaster && \in_array($num, $masterNumbers, true)) {
                return $num;
            }
            $digits = str_split((string) $num);
            $num = array_sum(array_map('intval', $digits));
        }

        return $num;
    }

    public function calculateNumerology(string $fullName, string $dob, string $system = 'chaldean'): array
    {
        $cleanName = preg_replace('/[^A-Z]/', '', strtoupper($fullName));
        $vowels = ['A', 'E', 'I', 'O', 'U'];
        $map = ($system === 'pythagorean') ? $this->pythagoreanMap : $this->chaldeanMap;

        $dt = new \DateTime($dob);
        $day   = (int) $dt->format('d');
        $month = (int) $dt->format('m');
        $year  = (int) $dt->format('Y');

        $mulank = $this->reduceNumber($day, false);
        $dobDigitsSum = array_sum(array_map('intval', str_split($dt->format('dmY'))));
        $bhagyank = $this->reduceNumber($dobDigitsSum, true);

        $currentYear = (int) now('Asia/Kolkata')->format('Y');
        $personalYearSum = $this->reduceNumber($day, false)
            + $this->reduceNumber($month, false)
            + $this->reduceNumber($currentYear, false);
        $personalYear = $this->reduceNumber($personalYearSum, false);

        $nameSum = 0;
        $vowelSum = 0;
        $consonantSum = 0;

        foreach (str_split($cleanName) as $char) {
            $val = $map[$char] ?? 0;
            $nameSum += $val;
            if (\in_array($char, $vowels, true)) {
                $vowelSum += $val;
            } else {
                $consonantSum += $val;
            }
        }

        $nameNum = ($system === 'pythagorean') ? $this->reduceNumber($nameSum, true) : $this->reduceNumber($nameSum, false);
        $soulUrgeNum = $this->reduceNumber($vowelSum, true);
        $personalityNum = $this->reduceNumber($consonantSum, false);

        $mulankProfile   = $this->numberProfiles[$mulank];
        $bhagyankProfile = $this->numberProfiles[$bhagyank] ?? $this->numberProfiles[$this->reduceNumber($bhagyank, false)];
        $nameProfile     = $this->numberProfiles[$nameNum] ?? $this->numberProfiles[$this->reduceNumber($nameNum, false)];

        $friendlyList = array_map('intval', explode(', ', $mulankProfile['friendly']));
        $isNameAligned = \in_array($nameNum, $friendlyList, true);

        $personalYearThemes = [
            1 => 'New Beginnings, Independence & Fresh Initiatives',
            2 => 'Partnerships, Patience & Emotional Alignment',
            3 => 'Creativity, Expansion & Social Visibility',
            4 => 'Hard Work, Discipline & Building Solid Foundations',
            5 => 'Change, Travel, Networking & New Opportunities',
            6 => 'Family, Home, Relationships & Responsibility',
            7 => 'Introspection, Skill Mastery & Spiritual Clarity',
            8 => 'Career Authority, Financial Gains & Recognition',
            9 => 'Completion, Letting Go & Preparing for a New Cycle',
        ];

        return [
            'native_name' => trim($fullName),
            'dob'         => $dt->format('d F Y'),
            'system'      => ucfirst($system),
            'core_numbers' => [
                'mulank' => [
                    'label'       => 'Psychic Number (Mulank)',
                    'number'      => $mulank,
                    'planet'      => $mulankProfile['planet'],
                    'title'       => $mulankProfile['title'],
                    'description' => $mulankProfile['traits'],
                ],
                'bhagyank' => [
                    'label'       => 'Life Path Number (Bhagyank)',
                    'number'      => $bhagyank,
                    'planet'      => $bhagyankProfile['planet'],
                    'title'       => $bhagyankProfile['title'],
                    'description' => $bhagyankProfile['career'],
                ],
                'name_number' => [
                    'label'          => ucfirst($system) . ' Name Number (Namank)',
                    'number'         => $nameNum,
                    'compound_total' => $nameSum,
                    'planet'         => $nameProfile['planet'] ?? 'Sun',
                    'is_aligned'     => $isNameAligned,
                    'description'    => $isNameAligned
                        ? "Your name vibration ({$nameNum}) harmonizes well with your Birth Number ({$mulank})."
                        : "Your name vibration ({$nameNum}) brings contrasting energy to your Birth Number ({$mulank}).",
                ],
                'soul_urge' => [
                    'label'       => 'Soul Urge (Heart’s Desire)',
                    'number'      => $soulUrgeNum,
                    'description' => ($this->numberProfiles[$soulUrgeNum] ?? $this->numberProfiles[$this->reduceNumber($soulUrgeNum, false)])['love'],
                ],
                'personality' => [
                    'label'       => 'Outer Personality Number',
                    'number'      => $personalityNum,
                    'description' => $this->numberProfiles[$personalityNum]['traits'] ?? 'Balanced and reliable.',
                ],
                'personal_year' => [
                    'label'       => "Personal Year ({$currentYear})",
                    'number'      => $personalYear,
                    'theme'       => $personalYearThemes[$personalYear] ?? 'Steady Growth',
                ],
            ],
            'lucky_factors' => [
                'colors'           => $mulankProfile['lucky_color'],
                'days'             => $mulankProfile['lucky_days'],
                'gemstone'         => $mulankProfile['gemstone'],
                'friendly_numbers' => $mulankProfile['friendly'],
            ],
            'life_guidance' => [
                'personality' => $mulankProfile['traits'],
                'career'      => $bhagyankProfile['career'],
                'love'        => $mulankProfile['love'],
            ],
        ];
    }
}
