<?php

namespace App\Services;

class VedicAstrologyService
{
    protected array $rashis = [
        'Aries (Mesha)',
        'Taurus (Vrishabha)',
        'Gemini (Mithuna)',
        'Cancer (Karka)',
        'Leo (Simha)',
        'Virgo (Kanya)',
        'Libra (Tula)',
        'Scorpio (Vrishchika)',
        'Sagittarius (Dhanu)',
        'Capricorn (Makara)',
        'Aquarius (Kumbha)',
        'Pisces (Meena)'
    ];

    protected array $nakshatras = [
        ['name' => 'Ashwini', 'lord' => 'Ketu', 'gan' => 'Dev', 'yoni' => 'Ashwa', 'nadi' => 'Adi'],
        ['name' => 'Bharani', 'lord' => 'Venus', 'gan' => 'Manav', 'yoni' => 'Gaja', 'nadi' => 'Madhya'],
        ['name' => 'Krittika', 'lord' => 'Sun', 'gan' => 'Rakshasa', 'yoni' => 'Mesha', 'nadi' => 'Antya'],
        ['name' => 'Rohini', 'lord' => 'Moon', 'gan' => 'Manav', 'yoni' => 'Sarpa', 'nadi' => 'Antya'],
        ['name' => 'Mrigashira', 'lord' => 'Mars', 'gan' => 'Dev', 'yoni' => 'Sarpa', 'nadi' => 'Madhya'],
        ['name' => 'Ardra', 'lord' => 'Rahu', 'gan' => 'Manav', 'yoni' => 'Shwan', 'nadi' => 'Adi'],
        ['name' => 'Punarvasu', 'lord' => 'Jupiter', 'gan' => 'Dev', 'yoni' => 'Marjara', 'nadi' => 'Adi'],
        ['name' => 'Pushya', 'lord' => 'Saturn', 'gan' => 'Dev', 'yoni' => 'Mesha', 'nadi' => 'Madhya'],
        ['name' => 'Ashlesha', 'lord' => 'Mercury', 'gan' => 'Rakshasa', 'yoni' => 'Marjara', 'nadi' => 'Antya'],
        ['name' => 'Magha', 'lord' => 'Ketu', 'gan' => 'Rakshasa', 'yoni' => 'Mushaka', 'nadi' => 'Antya'],
        ['name' => 'Purva Phalguni', 'lord' => 'Venus', 'gan' => 'Manav', 'yoni' => 'Mushaka', 'nadi' => 'Madhya'],
        ['name' => 'Uttara Phalguni', 'lord' => 'Sun', 'gan' => 'Manav', 'yoni' => 'Gau', 'nadi' => 'Adi'],
        ['name' => 'Hasta', 'lord' => 'Moon', 'gan' => 'Dev', 'yoni' => 'Mahisha', 'nadi' => 'Adi'],
        ['name' => 'Chitra', 'lord' => 'Mars', 'gan' => 'Rakshasa', 'yoni' => 'Vyaghra', 'nadi' => 'Madhya'],
        ['name' => 'Swati', 'lord' => 'Rahu', 'gan' => 'Dev', 'yoni' => 'Mahisha', 'nadi' => 'Antya'],
        ['name' => 'Vishakha', 'lord' => 'Jupiter', 'gan' => 'Rakshasa', 'yoni' => 'Vyaghra', 'nadi' => 'Antya'],
        ['name' => 'Anuradha', 'lord' => 'Saturn', 'gan' => 'Dev', 'yoni' => 'Mriga', 'nadi' => 'Madhya'],
        ['name' => 'Jyeshtha', 'lord' => 'Mercury', 'gan' => 'Rakshasa', 'yoni' => 'Mriga', 'nadi' => 'Adi'],
        ['name' => 'Mula', 'lord' => 'Ketu', 'gan' => 'Rakshasa', 'yoni' => 'Shwan', 'nadi' => 'Adi'],
        ['name' => 'Purva Ashadha', 'lord' => 'Venus', 'gan' => 'Manav', 'yoni' => 'Vanara', 'nadi' => 'Madhya'],
        ['name' => 'Uttara Ashadha', 'lord' => 'Sun', 'gan' => 'Manav', 'yoni' => 'Nakula', 'nadi' => 'Antya'],
        ['name' => 'Shravana', 'lord' => 'Moon', 'gan' => 'Dev', 'yoni' => 'Vanara', 'nadi' => 'Antya'],
        ['name' => 'Dhanishta', 'lord' => 'Mars', 'gan' => 'Rakshasa', 'yoni' => 'Simha', 'nadi' => 'Madhya'],
        ['name' => 'Shatabhisha', 'lord' => 'Rahu', 'gan' => 'Rakshasa', 'yoni' => 'Ashwa', 'nadi' => 'Adi'],
        ['name' => 'Purva Bhadrapada', 'lord' => 'Jupiter', 'gan' => 'Manav', 'yoni' => 'Simha', 'nadi' => 'Adi'],
        ['name' => 'Uttara Bhadrapada', 'lord' => 'Saturn', 'gan' => 'Manav', 'yoni' => 'Gau', 'nadi' => 'Madhya'],
        ['name' => 'Revati', 'lord' => 'Mercury', 'gan' => 'Dev', 'yoni' => 'Gaja', 'nadi' => 'Antya'],
    ];

    protected array $nityaYogas = [
        'Vishkumbha',
        'Priti',
        'Ayushman',
        'Saubhagya',
        'Shobhana',
        'Atiganda',
        'Sukarma',
        'Dhriti',
        'Shoola',
        'Ganda',
        'Vriddhi',
        'Dhruva',
        'Vyaghata',
        'Harshana',
        'Vajra',
        'Siddhi',
        'Vyatipata',
        'Variyan',
        'Parigha',
        'Shiva',
        'Siddha',
        'Sadhya',
        'Shubha',
        'Shukla',
        'Brahma',
        'Indra',
        'Vaidhriti'
    ];

    protected array $movableKaranas = [
        'Bava',
        'Balava',
        'Kaulava',
        'Taitila',
        'Gara',
        'Vanija',
        'Vishti'
    ];

    private function rev(float $x): float
    {
        $r = fmod($x, 360.0);
        return $r < 0 ? $r + 360.0 : $r;
    }

    private function sind(float $x): float
    {
        return sin(deg2rad($x));
    }

    private function cosd(float $x): float
    {
        return cos(deg2rad($x));
    }

    private function atan2d(float $y, float $x): float
    {
        return rad2deg(atan2($y, $x));
    }

    private function tropicalToSidereal(float $tropicalDeg, float $ayanamsa): float
    {
        $sidereal = fmod($tropicalDeg - $ayanamsa, 360);
        if ($sidereal < 0) {
            $sidereal += 360;
        }
        return $sidereal;
    }

    public function calculateTithi(float $sunDegree, float $moonDegree): array
    {
        $tithiNames = [
            1 => 'Pratipada',
            2 => 'Dwitiya',
            3 => 'Tritiya',
            4 => 'Chaturthi',
            5 => 'Panchami',
            6 => 'Shashthi',
            7 => 'Saptami',
            8 => 'Ashtami',
            9 => 'Navami',
            10 => 'Dashami',
            11 => 'Ekadashi',
            12 => 'Dwadashi',
            13 => 'Trayodashi',
            14 => 'Chaturdashi',
            15 => 'Purnima',
            30 => 'Amavasya'
        ];

        $diff = fmod(($moonDegree - $sunDegree + 360.0), 360.0);
        $tithiNumber = (int) floor($diff / 12.0) + 1;

        if ($tithiNumber <= 15) {
            $paksha = 'Shukla Paksha';
            $pakshaTithi = $tithiNumber;
        } else {
            $paksha = 'Krishna Paksha';
            $pakshaTithi = $tithiNumber - 15;
        }

        $name = ($tithiNumber === 15 || $tithiNumber === 30)
            ? $tithiNames[$tithiNumber]
            : $tithiNames[$pakshaTithi];

        $elapsedPercentage = round((fmod($diff, 12.0) / 12.0) * 100, 2);

        return [
            'tithi_number' => $tithiNumber,
            'paksha' => $paksha,
            'name' => $name,
            'full_name' => "$paksha $name",
            'elapsed_pct' => $elapsedPercentage,
        ];
    }

    public function calculateYoga(float $sunDegree, float $moonDegree): string
    {
        $total = fmod($sunDegree + $moonDegree, 360.0);
        $yogaIndex = (int) floor($total / (360.0 / 27.0));
        return $this->nityaYogas[$yogaIndex % 27];
    }

    public function calculateKarana(float $sunDegree, float $moonDegree): string
    {
        $diff = fmod(($moonDegree - $sunDegree + 360.0), 360.0);
        $karanaNumber = (int) floor($diff / 6.0) + 1;

        if ($karanaNumber === 1) {
            return 'Kimstughna';
        }
        if ($karanaNumber >= 2 && $karanaNumber <= 57) {
            return $this->movableKaranas[($karanaNumber - 2) % 7];
        }
        if ($karanaNumber === 58) {
            return 'Shakuni';
        }
        if ($karanaNumber === 59) {
            return 'Chatushpada';
        }
        return 'Naga';
    }

    public function calculateVimshottariDasha(float $moonSidereal, \DateTime $birthDate): array
    {
        $dashaOrder = [
            ['planet' => 'Ketu', 'years' => 7],
            ['planet' => 'Venus', 'years' => 20],
            ['planet' => 'Sun', 'years' => 6],
            ['planet' => 'Moon', 'years' => 10],
            ['planet' => 'Mars', 'years' => 7],
            ['planet' => 'Rahu', 'years' => 18],
            ['planet' => 'Jupiter', 'years' => 16],
            ['planet' => 'Saturn', 'years' => 19],
            ['planet' => 'Mercury', 'years' => 17],
        ];

        $nakshatraSpan = 360.0 / 27.0;
        $nakshatraIndex = (int) floor($moonSidereal / $nakshatraSpan);
        $degInNakshatra = fmod($moonSidereal, $nakshatraSpan);
        $elapsedFraction = $degInNakshatra / $nakshatraSpan;

        $startIndex = $nakshatraIndex % 9;
        $now = new \DateTime('now', $birthDate->getTimezone());

        $firstPlanetYears = $dashaOrder[$startIndex]['years'];
        $elapsedDaysBeforeBirth = (int) round($firstPlanetYears * $elapsedFraction * 365.2425);
        $cursorDate = (clone $birthDate)->modify("-{$elapsedDaysBeforeBirth} days");

        $mahadashas = [];

        for ($i = 0; $i < 9; $i++) {
            $mIdx = ($startIndex + $i) % 9;
            $mPlanet = $dashaOrder[$mIdx]['planet'];
            $mYears = $dashaOrder[$mIdx]['years'];

            $mStart = ($i === 0) ? clone $birthDate : clone $cursorDate;
            $antardashas = [];

            for ($j = 0; $j < 9; $j++) {
                $aIdx = ($mIdx + $j) % 9;
                $aPlanet = $dashaOrder[$aIdx]['planet'];
                $aFullYears = $dashaOrder[$aIdx]['years'];

                $antarYears = ($mYears * $aFullYears) / 120.0;
                $antarDays = (int) round($antarYears * 365.2425);

                $aStart = clone $cursorDate;
                $aEnd = (clone $cursorDate)->modify("+{$antarDays} days");

                if ($aEnd > $birthDate) {
                    $effectiveStart = ($aStart < $birthDate) ? clone $birthDate : $aStart;
                    $antardashas[] = [
                        'planet' => "{$mPlanet}-{$aPlanet}",
                        'start' => $effectiveStart->format('d M Y'),
                        'end' => $aEnd->format('d M Y'),
                        'active' => ($now >= $effectiveStart && $now <= $aEnd),
                    ];
                }

                $cursorDate = $aEnd;
            }

            $mEnd = clone $cursorDate;
            $actualDuration = ($i === 0) ? round($mYears * (1.0 - $elapsedFraction), 2) : $mYears;

            $mahadashas[] = [
                'planet' => $mPlanet,
                'duration' => $actualDuration . ' Years',
                'start' => $mStart->format('d M Y'),
                'end' => $mEnd->format('d M Y'),
                'active' => ($now >= $mStart && $now <= $mEnd),
                'antardashas' => $antardashas,
            ];
        }

        return $mahadashas;
    }

    public function getAvakhadaDetails(int $moonRashiIndex, int $nakshatraIndex, int $moonHouse): array
    {
        $varnas = ['Kshatriya', 'Vaishya', 'Shudra', 'Brahmin', 'Kshatriya', 'Vaishya', 'Shudra', 'Brahmin', 'Kshatriya', 'Vaishya', 'Shudra', 'Brahmin'];
        $vashyas = ['Chatushpada', 'Chatushpada', 'Manav', 'Jalachara', 'Vanachara', 'Manav', 'Manav', 'Keeta', 'Chatushpada', 'Jalachara', 'Manav', 'Jalachara'];
        $tatvas = ['Fire', 'Earth', 'Air', 'Water', 'Fire', 'Earth', 'Air', 'Water', 'Fire', 'Earth', 'Air', 'Water'];

        $payaMap = [
            1 => 'Gold',
            6 => 'Gold',
            11 => 'Gold',
            2 => 'Silver',
            5 => 'Silver',
            9 => 'Silver',
            3 => 'Copper',
            7 => 'Copper',
            10 => 'Copper',
            4 => 'Iron',
            8 => 'Iron',
            12 => 'Iron',
        ];

        $nak = $this->nakshatras[$nakshatraIndex];

        return [
            'varna' => $varnas[$moonRashiIndex],
            'vashya' => $vashyas[$moonRashiIndex],
            'tatva' => $tatvas[$moonRashiIndex],
            'yoni' => $nak['yoni'],
            'gan' => $nak['gan'],
            'nadi' => $nak['nadi'],
            'paya' => $payaMap[$moonHouse] ?? 'Gold',
        ];
    }

    protected function getCoordinates(string $birthPlace): array
    {
        $place = trim(explode(',', $birthPlace)[0]);
        $location = \App\Models\Location::where('city_name', 'like', "%{$place}%")->first();

        if ($location) {
            return ['lat' => (float) $location->latitude, 'lon' => (float) $location->longitude];
        }
        return ['lat' => 24.3815, 'lon' => 74.7012];
    }

    public function calculateChart(string $date, string $time, string $birthPlace, ?float $lat = null, ?float $lon = null): array
    {
        if ($lat === null || $lon === null) {
            $coords = $this->getCoordinates($birthPlace);
            $lat = $coords['lat'];
            $lon = $coords['lon'];
        }
        $timeNormalized = strlen($time) === 5 ? "$time:00" : $time;
        $localDt = \DateTime::createFromFormat('Y-m-d H:i:s', "$date $timeNormalized", new \DateTimeZone('Asia/Kolkata'))
            ?: new \DateTime("$date $timeNormalized", new \DateTimeZone('Asia/Kolkata'));
        $sunInfo = date_sun_info($localDt->getTimestamp(), $lat, $lon);
        $sunrise = isset($sunInfo['sunrise']) && is_int($sunInfo['sunrise'])
            ? (new \DateTime('@' . $sunInfo['sunrise']))->setTimezone(new \DateTimeZone('Asia/Kolkata'))->format('h:i A')
            : '06:00 AM';
        $sunset = isset($sunInfo['sunset']) && is_int($sunInfo['sunset'])
            ? (new \DateTime('@' . $sunInfo['sunset']))->setTimezone(new \DateTimeZone('Asia/Kolkata'))->format('h:i A')
            : '06:30 PM';
        $utcDt = (clone $localDt)->setTimezone(new \DateTimeZone('UTC'));
        $year = (int) $utcDt->format('Y');
        $month = (int) $utcDt->format('m');
        $day = (int) $utcDt->format('d');
        $hour = (int) $utcDt->format('H') + ((int) $utcDt->format('i') / 60.0) + ((int) $utcDt->format('s') / 3600.0);
        if ($month <= 2) {
            $year -= 1;
            $month += 12;
        }
        $A = floor($year / 100);
        $B = 2 - $A + floor($A / 4);
        $jd = floor(365.25 * ($year + 4716)) + floor(30.6001 * ($month + 1)) + $day + $B - 1524.5 + $hour / 24.0;
        $d = $jd - 2451543.5;
        $T = ($jd - 2451545.0) / 36525.0;
        $ayanamsa = 23.8577 + 1.396971 * $T;
        // 1. Sun
        $w_sun = 282.9404 + 4.70935e-5 * $d;
        $e_sun = 0.016709 - 1.151e-9 * $d;
        $M_sun = $this->rev(356.0470 + 0.9856002585 * $d);
        $E_sun = $M_sun + rad2deg($e_sun) * $this->sind($M_sun) * (1.0 + $e_sun * $this->cosd($M_sun));
        $xv = $this->cosd($E_sun) - $e_sun;
        $yv = sqrt(1.0 - $e_sun ** 2) * $this->sind($E_sun);
        $v_sun = $this->atan2d($yv, $xv);
        $r_sun = sqrt($xv ** 2 + $yv ** 2);
        $lon_sun = $this->rev($v_sun + $w_sun);
        // 2. Moon
        $N_moon = $this->rev(125.1228 - 0.0529538083 * $d);
        $i_moon = 5.1454;
        $w_moon = $this->rev(318.0634 + 0.1643573223 * $d);
        $a_moon = 60.2666;
        $e_moon = 0.054900;
        $M_moon = $this->rev(115.3654 + 13.0649929509 * $d);
        $E0 = $M_moon + rad2deg($e_moon) * $this->sind($M_moon) * (1.0 + $e_moon * $this->cosd($M_moon));
        for ($k = 0; $k < 5; $k++) {
            $E0 -= ($E0 - rad2deg($e_moon) * $this->sind($E0) - $M_moon) / (1.0 - $e_moon * $this->cosd($E0));
        }
        $xv_m = $a_moon * ($this->cosd($E0) - $e_moon);
        $yv_m = $a_moon * (sqrt(1.0 - $e_moon ** 2) * $this->sind($E0));
        $v_m = $this->atan2d($yv_m, $xv_m);
        $r_m = sqrt($xv_m ** 2 + $yv_m ** 2);
        $xh_m = $r_m * ($this->cosd($N_moon) * $this->cosd($v_m + $w_moon) - $this->sind($N_moon) * $this->sind($v_m + $w_moon) * $this->cosd($i_moon));
        $yh_m = $r_m * ($this->sind($N_moon) * $this->cosd($v_m + $w_moon) + $this->cosd($N_moon) * $this->sind($v_m + $w_moon) * $this->cosd($i_moon));
        $lon_m_unpert = $this->atan2d($yh_m, $xh_m);
        $Ls = $this->rev($M_sun + $w_sun);
        $Lm = $this->rev($M_moon + $w_moon + $N_moon);
        $D = $this->rev($Lm - $Ls);
        $F = $this->rev($Lm - $N_moon);
        $pert_m = (
            -1.274 * $this->sind($M_moon - 2 * $D)
            + 0.658 * $this->sind(2 * $D)
            - 0.186 * $this->sind($M_sun)
            - 0.059 * $this->sind(2 * $M_moon - 2 * $D)
            - 0.057 * $this->sind($M_moon - 2 * $D + $M_sun)
            + 0.053 * $this->sind($M_moon + 2 * $D)
            + 0.046 * $this->sind(2 * $D - $M_sun)
            + 0.041 * $this->sind($M_moon - $M_sun)
            - 0.035 * $this->sind($D)
            - 0.031 * $this->sind($M_moon + $M_sun)
            - 0.015 * $this->sind(2 * $F - 2 * $D)
            + 0.011 * $this->sind($M_moon - 4 * $D)
        );
        $lon_moon = $this->rev($lon_m_unpert + $pert_m);
        // 3. Planets
        $geoPlanet = function ($N, $i, $w, $a, $e, $M, $extra_lon = 0.0) use ($r_sun, $lon_sun) {
            $M = $this->rev($M);
            $E = $M + rad2deg($e) * $this->sind($M) * (1.0 + $e * $this->cosd($M));
            for ($k = 0; $k < 6; $k++) {
                $E -= ($E - rad2deg($e) * $this->sind($E) - $M) / (1.0 - $e * $this->cosd($E));
            }
            $xv = $a * ($this->cosd($E) - $e);
            $yv = $a * (sqrt(1.0 - $e ** 2) * $this->sind($E));
            $v = $this->atan2d($yv, $xv);
            $r = sqrt($xv ** 2 + $yv ** 2);
            $xh = $r * ($this->cosd($N) * $this->cosd($v + $w) - $this->sind($N) * $this->sind($v + $w) * $this->cosd($i));
            $yh = $r * ($this->sind($N) * $this->cosd($v + $w) + $this->cosd($N) * $this->sind($v + $w) * $this->cosd($i));
            $zh = $r * ($this->sind($v + $w) * $this->sind($i));
            $lon_h = $this->atan2d($yh, $xh) + $extra_lon;
            $lat_h = $this->atan2d($zh, sqrt($xh ** 2 + $yh ** 2));
            $xh = $r * $this->cosd($lat_h) * $this->cosd($lon_h);
            $yh = $r * $this->cosd($lat_h) * $this->sind($lon_h);
            $xs = $r_sun * $this->cosd($lon_sun);
            $ys = $r_sun * $this->sind($lon_sun);
            $xg = $xh + $xs;
            $yg = $yh + $ys;
            return $this->rev($this->atan2d($yg, $xg));
        };
        $lon_merc = $geoPlanet(48.3313 + 3.24587e-5 * $d, 7.0047 + 5.00e-8 * $d, 29.1241 + 1.01444e-5 * $d, 0.387098, 0.205635 + 5.59e-10 * $d, 168.6562 + 4.0923344368 * $d);
        $lon_venus = $geoPlanet(76.6799 + 2.46590e-5 * $d, 3.3946 + 2.75e-8 * $d, 54.8910 + 1.38374e-5 * $d, 0.723330, 0.006773 - 1.302e-9 * $d, 48.0052 + 1.6021302244 * $d);
        $lon_mars = $geoPlanet(49.5574 + 2.11081e-5 * $d, 1.8497 - 1.78e-8 * $d, 286.5016 + 2.92961e-5 * $d, 1.523688, 0.093405 + 2.516e-9 * $d, 18.6021 + 0.5240207766 * $d);
        $Mj = $this->rev(19.8950 + 0.0830853001 * $d);
        $Ms = $this->rev(316.9670 + 0.0334442282 * $d);
        $pert_jup = -0.332 * $this->sind(2 * $Mj - 5 * $Ms - 67.6) - 0.056 * $this->sind(2 * $Mj - 2 * $Ms + 21);
        $lon_jup = $geoPlanet(100.4542 + 2.76854e-5 * $d, 1.3030 - 1.557e-7 * $d, 273.8777 + 1.64505e-5 * $d, 5.20256, 0.048498 + 4.469e-9 * $d, $Mj, $pert_jup);
        $pert_sat = 0.812 * $this->sind(2 * $Mj - 5 * $Ms - 67.6) - 0.229 * $this->cosd(2 * $Mj - 4 * $Ms - 2);
        $lon_sat = $geoPlanet(113.6634 + 2.38980e-5 * $d, 2.4886 - 1.081e-7 * $d, 339.3939 + 2.97661e-5 * $d, 9.55475, 0.055546 - 9.499e-9 * $d, $Ms, $pert_sat);
        $lon_rahu = $this->rev(125.04452 - 1934.136261 * $T);
        $lon_ketu = $this->rev($lon_rahu + 180.0);
        // 4. Ascendant
        $gmst = $this->rev(280.46061837 + 360.98564736629 * ($jd - 2451545.0));
        $ramc = $this->rev($gmst + $lon);
        $epsRad = deg2rad(23.439291);
        $ramcRad = deg2rad($ramc);
        $latRad = deg2rad($lat);
        $ascTropical = rad2deg(atan2(
            cos($ramcRad),
            -sin($ramcRad) * cos($epsRad) - tan($latRad) * sin($epsRad)
        ));
        $ascTropical = $this->rev($ascTropical);
        $ascSidereal = $this->tropicalToSidereal($ascTropical, $ayanamsa);
        $sunSidereal = $this->tropicalToSidereal($lon_sun, $ayanamsa);
        $moonSidereal = $this->tropicalToSidereal($lon_moon, $ayanamsa);

        // Calculate Tithi, Yoga, Karana dynamically
        $tithiInfo = $this->calculateTithi($sunSidereal, $moonSidereal);
        $yogaName = $this->calculateYoga($sunSidereal, $moonSidereal);
        $karanaName = $this->calculateKarana($sunSidereal, $moonSidereal);
        $vimshottariDasha = $this->calculateVimshottariDasha($moonSidereal, $localDt);

        $bodies = [
            'Ascendant' => $ascSidereal,
            'Sun' => $sunSidereal,
            'Moon' => $moonSidereal,
            'Mars' => $this->tropicalToSidereal($lon_mars, $ayanamsa),
            'Mercury' => $this->tropicalToSidereal($lon_merc, $ayanamsa),
            'Jupiter' => $this->tropicalToSidereal($lon_jup, $ayanamsa),
            'Venus' => $this->tropicalToSidereal($lon_venus, $ayanamsa),
            'Saturn' => $this->tropicalToSidereal($lon_sat, $ayanamsa),
            'Rahu' => $this->tropicalToSidereal($lon_rahu, $ayanamsa),
            'Ketu' => $this->tropicalToSidereal($lon_ketu, $ayanamsa),
        ];
        $ascRashiIndex = (int) floor($bodies['Ascendant'] / 30);
        $moonRashiIndex = (int) floor($bodies['Moon'] / 30);
        $moonNakshatraIndex = (int) floor($bodies['Moon'] / (360.0 / 27.0));
        $moonHouse = (($moonRashiIndex - $ascRashiIndex + 12) % 12) + 1;
        $ayanDeg = (int) floor($ayanamsa);
        $ayanMin = (int) floor(($ayanamsa - $ayanDeg) * 60);
        $ayanSec = (int) floor((($ayanamsa - $ayanDeg) * 60 - $ayanMin) * 60);
        $chart = [
            'planets' => [],
            'tithi_info' => $tithiInfo,
            'yoga_name' => $yogaName,
            'karana_name' => $karanaName,
            'ayanamsa_formatted' => sprintf(
                "Lahiri (%02d° %02d' %02d\")",
                $ayanDeg,
                $ayanMin,
                $ayanSec
            ),
            'sunrise' => $sunrise,
            'sunset' => $sunset,
            'avakhada' => $this->getAvakhadaDetails($moonRashiIndex, $moonNakshatraIndex, $moonHouse),
            'vimshottari_dasha' => $vimshottariDasha,
        ];
        foreach ($bodies as $name => $sidDegree) {
            $rashiIndex = (int) floor($sidDegree / 30);
            $degInSign = fmod($sidDegree, 30.0);
            $deg = (int) floor($degInSign);
            $min = (int) floor(($degInSign - $deg) * 60);
            $sec = (int) floor((($degInSign - $deg) * 60 - $min) * 60);
            $nakshatraSpan = 360.0 / 27.0;
            $nakshatraIndex = (int) floor($sidDegree / $nakshatraSpan);
            $degInNakshatra = fmod($sidDegree, $nakshatraSpan);
            $pada = (int) floor($degInNakshatra / ($nakshatraSpan / 4.0)) + 1;
            $house = (($rashiIndex - $ascRashiIndex + 12) % 12) + 1;
            $label = $name === 'Ascendant' ? 'Ascendant (Lagna)' : $name;
            $chart['planets'][$label] = [
                'name' => $label,
                'full_degree' => round($sidDegree, 4),
                'rashi' => $this->rashis[$rashiIndex],
                'rashi_number' => $rashiIndex + 1,
                'degree_formatted' => \sprintf('%02d° %02d\' %02d"', $deg, $min, $sec),
                'nakshatra' => $this->nakshatras[$nakshatraIndex]['name'],
                'nakshatra_lord' => $this->nakshatras[$nakshatraIndex]['lord'],
                'pada' => $pada,
                'house' => $house,
            ];
        }
        return $chart;
    }

    public function calculateSunSign(
        string $name,
        string $gender,
        string $dob,
        string $tob,
        string $birthPlace,
        ?float $lat = null,
        ?float $lng = null
    ): array {
        $signProfiles = array(
            'Aries' => array(
                'element' => 'Fire',
                'ruler' => 'Mars',
                'dates' => 'March 21 - April 19',
            ),
            'Taurus' => array(
                'element' => 'Earth',
                'ruler' => 'Venus',
                'dates' => 'April 20 - May 20',
            ),
            'Gemini' => array(
                'element' => 'Air',
                'ruler' => 'Mercury',
                'dates' => 'May 21 - June 20',
            ),
            'Cancer' => array(
                'element' => 'Water',
                'ruler' => 'Moon',
                'dates' => 'June 21 - July 22',
            ),
            'Leo' => array(
                'element' => 'Fire',
                'ruler' => 'Sun',
                'dates' => 'July 23 - August 22',
            ),
            'Virgo' => array(
                'element' => 'Earth',
                'ruler' => 'Mercury',
                'dates' => 'August 23 - September 22',
            ),
            'Libra' => array(
                'element' => 'Air',
                'ruler' => 'Venus',
                'dates' => 'September 23 - October 22',
            ),
            'Scorpio' => array(
                'element' => 'Water',
                'ruler' => 'Mars',
                'dates' => 'October 23 - November 21',
            ),
            'Sagittarius' => array(
                'element' => 'Fire',
                'ruler' => 'Jupiter',
                'dates' => 'November 22 - December 21',
            ),
            'Capricorn' => array(
                'element' => 'Earth',
                'ruler' => 'Saturn',
                'dates' => 'December 22 - January 19',
            ),
            'Aquarius' => array(
                'element' => 'Air',
                'ruler' => 'Saturn',
                'dates' => 'January 20 - February 18',
            ),
            'Pisces' => array(
                'element' => 'Water',
                'ruler' => 'Jupiter',
                'dates' => 'February 19 - March 20',
            ),
        );

        $signsList = array_keys($signProfiles);
        $timeNormalized = !empty($tob) ? $tob : '12:00';

        if ($lat === null || $lng === null) {
            $coords = $this->getCoordinates($birthPlace);
            $lat = $coords['lat'];
            $lng = $coords['lon'];
        }

        // 1. Calculate Sidereal (Vedic) Sun Sign using exact birth details
        $chart = $this->calculateChart($dob, $timeNormalized, $birthPlace, $lat, $lng);
        $siderealSun = $chart['planets']['Sun'];
        $vedicSignName = explode(' ', $siderealSun['rashi'])[0];

        // 2. Reconstruct Tropical (Western) Sun Longitude by adding Ayanamsa back
        $dt = new \DateTime("{$dob} {$timeNormalized}", new \DateTimeZone('Asia/Kolkata'));
        $utcDt = (clone $dt)->setTimezone(new \DateTimeZone('UTC'));

        $year = (int) $utcDt->format('Y');
        $month = (int) $utcDt->format('m');
        $day = (int) $utcDt->format('d');
        $hour = (int) $utcDt->format('H') + ((int) $utcDt->format('i') / 60.0);

        if ($month <= 2) {
            $year -= 1;
            $month += 12;
        }

        $A = floor($year / 100);
        $B = 2 - $A + floor($A / 4);
        $jd = floor(365.25 * ($year + 4716)) + floor(30.6001 * ($month + 1)) + $day + $B - 1524.5 + $hour / 24.0;
        $T = ($jd - 2451545.0) / 36525.0;
        $ayanamsa = 23.8577 + 1.396971 * $T;

        $tropicalSunDeg = $this->rev($siderealSun['full_degree'] + $ayanamsa);
        $westernSignIdx = (int) floor($tropicalSunDeg / 30.0);
        $westernSignName = $signsList[$westernSignIdx];

        $formattedDob = $dt->format('j F Y'); // e.g. "4 May 2000"

        if ($westernSignName !== $vedicSignName) {
            $comparisonNote = "Your Western Sun Sign is {$westernSignName}, while your Vedic Sun Sign is {$vedicSignName}. This difference is because Western astrology uses the Tropical zodiac based on the seasons, while Vedic astrology uses the Sidereal zodiac based on the fixed stars.";
        } else {
            $comparisonNote = "Both your Western Sun Sign and Vedic Sun Sign are {$westernSignName}. Because Western astrology uses the Tropical zodiac based on the seasons and Vedic astrology uses the Sidereal zodiac based on the fixed stars, your birth date falls in the overlap window where both systems align.";
        }

        return array(
            'native_name' => trim($name),
            'gender' => ucfirst(strtolower(trim($gender))),
            'formatted_dob' => $formattedDob,
            'tob' => $dt->format('h:i A'),
            'birth_place' => trim($birthPlace),
            'western' => array_merge(array('name' => $westernSignName), $signProfiles[$westernSignName]),
            'vedic' => array_merge(array('name' => $vedicSignName), $signProfiles[$vedicSignName]),
            'profile_note' => $comparisonNote,
        );
    }

    public function calculateRisingSign(
        string $name,
        string $gender,
        string $dob,
        string $tob,
        string $birthPlace,
        ?float $lat = null,
        ?float $lng = null
    ): array {
        if ($lat === null || $lng === null) {
            $coords = $this->getCoordinates($birthPlace);
            $lat = $coords['lat'];
            $lng = $coords['lon'];
        }

        $timeNormalized = !empty($tob) ? $tob : '12:00';
        $chart = $this->calculateChart($dob, $timeNormalized, $birthPlace, $lat, $lng);
        $asc = $chart['planets']['Ascendant (Lagna)'];

        $risingSignName = explode(' ', $asc['rashi'])[0];

        $profiles = [
            'Aries' => [
                'hindi'         => 'Mesha',
                'ruler'         => 'Mars',
                'element'       => 'Fire',
                'personality'   => 'Those born with the Aries ascendant possess bold, energetic, and pioneering personalities. Ruled by Mars, they approach life head-on with courage, independence, and an instinctive drive to initiate new ventures. They are direct in speech, quick to act, and naturally step into leadership roles during crises. While their enthusiasm and fearlessness inspire others, their fast-moving nature can occasionally make them impatient, impulsive, or overly competitive when results take time.',
                'physical'      => 'Aries ascendant natives typically have an athletic, agile, and well-proportioned build with brisk, purposeful movements. They often feature sharp facial contours, prominent eyebrows, a strong jawline, and an alert, determined gaze. A warm complexion or reddish undertone is common, along with abundant energy and quick reflexes.',
                'health'        => 'Because Aries governs the head and brain, natives may be prone to tension headaches, migraines, eye strain, or sinus inflammation when overworked. High internal heat can also lead to occasional fevers, skin rashes, or minor injuries from rushing. Regular vigorous exercise, cooling hydration, and stress management keep their vitality strong.',
                'career'        => 'Action-oriented and self-reliant, Aries rising individuals excel in entrepreneurship, leadership, defense, engineering, sports, surgery, and project management. They thrive in dynamic environments where initiative, quick decision-making, and autonomy are rewarded rather than repetitive desk routines.',
                'relationships' => 'In love and partnerships, Aries ascendants are passionate, protective, and straightforward about their feelings. They admire confident, independent partners who match their zest for life. However, learning patience, active listening, and compromise helps prevent impulsive arguments and builds lasting domestic harmony.',
            ],
            'Taurus' => [
                'hindi'         => 'Vrishabha',
                'ruler'         => 'Venus',
                'element'       => 'Earth',
                'personality'   => 'Those born with the Taurus ascendant radiate calm strength, reliability, and grounded charm. Ruled by Venus, they value stability, comfort, honesty, and tangible results over fleeting trends. They move thoughtfully before committing, yet once their mind is made up, their perseverance is unmatched. Their steady presence reassures everyone around them, though they can become stubborn when pushed to change too abruptly.',
                'physical'      => 'Taurus ascendants generally have a sturdy, well-built, and graceful physique with harmonious facial symmetry. Expressive eyes, full lips, a strong neck and shoulders, and a pleasant, melodious speaking voice are hallmark traits. They carry themselves with natural poise and understated elegance.',
                'health'        => 'Taurus rules the throat, neck, thyroid, and vocal cords, making natives sensitive to sore throats, tonsillitis, or thyroid fluctuations if stressed. Because they enjoy rich food and comfort, maintaining portion balance and consistent cardio helps prevent sluggish metabolism or weight gain.',
                'career'        => 'Practical and resource-minded, Taurus rising natives thrive in finance, banking, architecture, interior design, luxury retail, culinary arts, agriculture, and real estate. They possess strong financial instincts and excel at building lasting wealth and dependable institutions.',
                'relationships' => 'Devoted, sensual, and deeply loyal, Taurus ascendants seek long-term security and emotional consistency in marriage and love. They express affection through quality time, physical warmth, and practical support, requiring mutual trust and patience to feel fully secure.',
            ],
            'Gemini' => [
                'hindi'         => 'Mithuna',
                'ruler'         => 'Mercury',
                'element'       => 'Air',
                'personality'   => 'Those born with the Gemini ascendant are very lively and charming personalities. They are the owner of a very active mind and as a twin sign, also very open to change. It is a good thing but also shady because it prompts their thoughts to wander around and rarely stick on one thing. These natives are restless, easily bored, and find it tough to concentrate on one thing for a long time. Geminis are also very good with manipulation. Most of the time, one can\'t figure out what they are thinking. Thus, people, especially their loved ones, struggle to understand them. Nevertheless, the Gemini\'s intellect dominates them, and thus when serious, these people make the best decisions. They rarely get deeply absorbed in any task, and anything they haven\'t touched intrigues them the most.',
                'physical'      => 'Ruled by the planet Mercury, Gemini ascendant people have bright eyes and emotive, well-defined moving characteristics. They have a flexible, thin, and tense body and are of medium to tall stature. The legs are frequently lengthy, resembling fashion models\', and a Gemini individual has a pleasant and charming personality. Their beautiful face traits include a tiny chin and light skin. Their appealing attitude is boosted by their wavy hair. They usually have a wide forehead and bright eyes. Despite their appealing features and charisma, they may be vulnerable to sickness and medical conditions.',
                'health'        => 'Medical problems with the arms, wrists, or forearms are prominent among Gemini ascendants. Their respiratory tract and lungs may be fragile, making them susceptible to lung and chest blockage occasionally, as well as frequent ailment from the common cold, flu, plus illnesses like bronchitis. They are prone to stress and anxiety and a depressive attitude on life. People born under the rising sign of Gemini are more likely to experience a psychological collapse and chronic stress. Tuberculosis, asthma, and anemia are all major illnesses among these people.',
                'career'        => 'These people are problem solvers who enjoy putting puzzles together. Gemini rising folks enjoy doing any form of investigation or research. This ability qualifies them as great investigators or investigative journalists. Any career in journalism or telecommunications is also a good fit for them. They can\'t resist but share any fresh information with anyone who would listen. Gemini ascendants enjoy being the person who knows everything and communicates the information in an accurate but compelling manner. It also qualifies them as great writers, as writing is yet another means of conveying knowledge. They are interested in everything that permits them to interact with people and locations. Also, anything that involves the internet is ideal for them since it enables them to access information from all around the world.',
                'relationships' => 'Natives of Gemini ascendant make a concerted effort to follow their real calling. They are weighed down by the thought of commitment, and their emotions and feelings are frequently out of line. When it comes to a Gemini ascendant, the opposite gender has plenty of excuses to be nervous. Gemini ascendants are not the type to emotionally rely on their mate and demand the same in return. In addition, if their companion is extremely demanding, it becomes a mood killer and a reason for a Gemini rising to abandon them. As a result, a Gemini ascendant feels it hard to commit to a single relationship because their choices and moods are constantly shifting and evolving.',
            ],
            'Cancer' => [
                'hindi'         => 'Karka',
                'ruler'         => 'Moon',
                'element'       => 'Water',
                'personality'   => 'Those born with the Cancer ascendant are deeply intuitive, empathetic, and protective individuals. Ruled by the Moon, their moods and perceptions shift subtly with their emotional environment, making them perceptive judges of human character. While they may appear reserved or cautious upon first meeting, they are fiercely loyal, imaginative, and nurturing once trust is established.',
                'physical'      => 'Cancer ascendant natives often have a soft, expressive face with round or luminous eyes, full cheeks, and a gentle, approachable demeanor. Their build tends to be medium with a broader chest or upper torso, and their facial expressions clearly mirror their inner feelings.',
                'health'        => 'Cancer governs the chest, stomach, and digestive system, making natives sensitive to acidity, indigestion, fluid retention, or stress-related stomach discomfort. Emotional calm, mindful eating habits, and adequate hydration are essential for maintaining strong immunity.',
                'career'        => 'Nurturing and perceptive, Cancer rising individuals excel in hospitality, healthcare, counseling, psychology, real estate, education, human resources, and food or wellness enterprises. They thrive in roles where empathy, memory, and care for people create meaningful impact.',
                'relationships' => 'Family-oriented and deeply affectionate, Cancer ascendants seek emotional safety, loyalty, and a warm home life in relationships. They give wholeheartedly to their partners, though they must guard against over-sensitivity or withdrawing into silence when hurt.',
            ],
            'Leo' => [
                'hindi'         => 'Simha',
                'ruler'         => 'Sun',
                'element'       => 'Fire',
                'personality'   => 'Those born with the Leo ascendant carry natural dignity, warmth, and magnetic confidence. Ruled by the Sun, they have a generous heart, strong self-respect, and an innate desire to inspire and protect those around them. They dislike pettiness and prefer living with honor, creativity, and clear purpose, though pride can occasionally make them sensitive to criticism.',
                'physical'      => 'Leo ascendants frequently have a commanding posture, broad shoulders, an upright walk, and thick, prominent hair. Their facial features are expressive and noble with warm, confident eyes and a radiant smile that draws attention effortlessly.',
                'health'        => 'Leo rules the heart, spine, and upper back, so natives should prioritize cardiovascular fitness, proper posture, and regular physical activity. Overexertion or chronic stress can manifest as back stiffness or blood pressure fluctuations.',
                'career'        => 'Natural leaders and performers, Leo rising natives shine in executive management, politics, entertainment, creative arts, entrepreneurship, law, and public administration. They perform best when given authority, creative freedom, and visible responsibility.',
                'relationships' => 'Warm-hearted, loyal, and grand in affection, Leo ascendants treat their partners with generosity and devotion. They thrive with an appreciative, supportive spouse who respects their pride while encouraging mutual humility and openness.',
            ],
            'Virgo' => [
                'hindi'         => 'Kanya',
                'ruler'         => 'Mercury',
                'element'       => 'Earth',
                'personality'   => 'Those born with the Virgo ascendant possess sharp analytical minds, modesty, and a keen eye for detail. Ruled by Mercury, they notice nuances others overlook and constantly seek ways to improve systems, health, and efficiency. Practical and helpful by nature, they can sometimes become overly self-critical or anxious when environments feel chaotic.',
                'physical'      => 'Virgo ascendants typically have a neat, youthful, and slender appearance with refined, symmetrical facial features. Clear eyes, an attentive expression, and a well-groomed, composed bearing give them an approachable and intelligent look.',
                'health'        => 'Virgo governs the intestines, digestive tract, and nervous system. Natives may experience food sensitivities, irritable digestion, or nervous tension caused by overthinking. Clean nutrition, fiber-rich meals, and calming routines keep them balanced.',
                'career'        => 'Methodical and observant, Virgo rising individuals excel in data science, accounting, medicine, editing, research, quality assurance, software engineering, and nutrition. Their precision and problem-solving skill make them indispensable in technical and analytical fields.',
                'relationships' => 'Thoughtful and devoted, Virgo ascendants show love through practical acts of service, reliability, and attentiveness to their partner\'s needs. Learning to relax perfectionism and express vulnerability openly deepens emotional intimacy.',
            ],
            'Libra' => [
                'hindi'         => 'Tula',
                'ruler'         => 'Venus',
                'element'       => 'Air',
                'personality'   => 'Those born with the Libra ascendant are diplomatic, gracious, and naturally drawn to harmony and fairness. Ruled by Venus, they appreciate beauty, balanced dialogue, and refined social interactions. They see multiple sides of every issue, which makes them gifted mediators, though weighing every option can occasionally cause indecisiveness.',
                'physical'      => 'Libra ascendant natives often possess balanced, attractive facial proportions, a charming smile, dimples, and a graceful, well-proportioned build. Their movements are smooth and poised, reflecting an innate sense of style and aesthetics.',
                'health'        => 'Libra rules the kidneys, lower back, and endocrine balance. Natives benefit from staying well-hydrated, moderating sugar intake, and protecting lower-back strength through gentle stretching and regular movement.',
                'career'        => 'Socially adept and fair-minded, Libra rising individuals thrive in law, diplomacy, public relations, fashion, design, consulting, human resources, and client-facing partnerships. They excel wherever negotiation, aesthetics, and collaboration are valued.',
                'relationships' => 'Partnership is central to Libra ascendants; they are romantic, considerate, and happiest when sharing life with a harmonious companion. Maintaining clear personal boundaries prevents people-pleasing at the expense of their own needs.',
            ],
            'Scorpio' => [
                'hindi'         => 'Vrishchika',
                'ruler'         => 'Mars',
                'element'       => 'Water',
                'personality'   => 'Those born with the Scorpio ascendant possess intense focus, emotional depth, and remarkable resilience. Co-ruled by Mars and Ketu, they rarely reveal all their thoughts on the surface, preferring to observe quietly and understand hidden motivations. Once committed to a goal or person, their determination and loyalty are unwavering.',
                'physical'      => 'Scorpio ascendants are recognized by their penetrating, magnetic eyes, strong brow, and composed, impactful presence. Their physique is typically sturdy and muscular with strong endurance and sharp, defined facial lines.',
                'health'        => 'Scorpio governs the reproductive system, pelvic region, and eliminative organs. Natives have strong regenerative capacity when they rest properly, though suppressing intense emotions can cause internal stress or inflammatory conditions.',
                'career'        => 'Investigative and strategic, Scorpio rising natives excel in research, finance, surgery, psychology, cybersecurity, crisis management, forensics, and transformative leadership. They thrive in high-stakes roles requiring discretion and deep analysis.',
                'relationships' => 'In love, Scorpio ascendants seek deep soul-level trust, loyalty, and emotional authenticity rather than casual connections. When trust is honored, they are fiercely devoted partners; overcoming jealousy and guardedness unlocks lasting closeness.',
            ],
            'Sagittarius' => [
                'hindi'         => 'Dhanu',
                'ruler'         => 'Jupiter',
                'element'       => 'Fire',
                'personality'   => 'Those born with the Sagittarius ascendant are optimistic, philosophical, and driven by a love for truth and exploration. Ruled by Jupiter, they look at the big picture, value personal freedom, and enthusiastically pursue higher knowledge and new horizons. Their candid honesty is refreshing, though blunt remarks can occasionally catch others off guard.',
                'physical'      => 'Sagittarius ascendant natives generally have an energetic, athletic, or tall frame with an open, cheerful expression. A broad forehead, bright eyes, and an active, forward-leaning stride reflect their restless curiosity.',
                'health'        => 'Sagittarius rules the hips, thighs, sciatic nerve, and liver. Natives stay healthiest with outdoor movement, leg-strengthening exercise, and moderation in rich or indulgent foods.',
                'career'        => 'Visionary and inspiring, Sagittarius rising individuals thrive in education, law, publishing, international travel, consulting, philosophy, sports, and entrepreneurship. They flourish in roles offering growth, learning, and broad perspective.',
                'relationships' => 'Honest, adventurous, and generous, Sagittarius ascendants value friendship, shared ideals, and mutual freedom within marriage. A partner who embraces growth, humor, and open communication keeps the bond vibrant.',
            ],
            'Capricorn' => [
                'hindi'         => 'Makara',
                'ruler'         => 'Saturn',
                'element'       => 'Earth',
                'personality'   => 'Those born with the Capricorn ascendant are disciplined, pragmatic, and quietly ambitious. Ruled by Saturn, they take responsibilities seriously from an early age and build success steadily through patience, integrity, and strategic planning. While reserved initially, they develop dry wit, wisdom, and remarkable strength over time.',
                'physical'      => 'Capricorn ascendants often have a lean, well-defined bone structure, prominent cheekbones, and a composed, mature expression. Many appear serious in youth yet grow more youthful, vital, and distinguished as they age.',
                'health'        => 'Capricorn governs the knees, joints, skeletal system, and skin. Natives benefit from calcium- and mineral-rich nutrition, joint mobility exercises, and balancing hard work with genuine relaxation.',
                'career'        => 'Structured and goal-oriented, Capricorn rising natives excel in corporate leadership, administration, engineering, finance, government, law, and long-term business building. Their reliability and endurance consistently earn senior authority.',
                'relationships' => 'Loyal, protective, and committed, Capricorn ascendants view marriage as a serious lifelong partnership. Though slow to open up emotionally, they express deep devotion through stability, reliability, and enduring support.',
            ],
            'Aquarius' => [
                'hindi'         => 'Kumbha',
                'ruler'         => 'Saturn',
                'element'       => 'Air',
                'personality'   => 'Those born with the Aquarius ascendant are independent, inventive, and forward-thinking individuals. Ruled by Saturn, they combine progressive, humanitarian ideals with logical objectivity. They value intellectual freedom, dislike rigid conformity, and often bring original solutions to community and technological challenges.',
                'physical'      => 'Aquarius ascendant natives typically have a tall or slender frame, clean facial symmetry, a broad forehead, and thoughtful, observant eyes. Their style often carries a distinctive or unconventional touch.',
                'health'        => 'Aquarius rules the calves, ankles, and circulatory system. Natives should prioritize healthy blood circulation, regular walking or cycling, and grounding practices to calm an overactive nervous system.',
                'career'        => 'Innovative and analytical, Aquarius rising individuals thrive in technology, software architecture, scientific research, social entrepreneurship, media, aviation, and network-driven organizations. They excel where originality and systems thinking meet.',
                'relationships' => 'Friendship forms the foundation of love for Aquarius ascendants. They seek an intellectually stimulating, open-minded partner who respects individuality while sharing progressive values and mutual trust.',
            ],
            'Pisces' => [
                'hindi'         => 'Meena',
                'ruler'         => 'Jupiter',
                'element'       => 'Water',
                'personality'   => 'Those born with the Pisces ascendant are compassionate, imaginative, and deeply receptive to the subtle currents of life. Ruled by Jupiter, they possess natural empathy, artistic sensitivity, and philosophical wisdom. While gentle and adaptable, they must maintain healthy boundaries so they do not absorb the stress of others.',
                'physical'      => 'Pisces ascendant natives often have soulful, expressive eyes, soft facial contours, and a fluid, gentle demeanor. Their build tends to be medium or supple, accompanied by a calm, approachable aura.',
                'health'        => 'Pisces governs the feet, lymphatic system, and sleep quality. Natives thrive when they protect restful sleep, wear supportive footwear, stay hydrated, and spend quiet time near nature or water to recharge.',
                'career'        => 'Creative and empathetic, Pisces rising individuals excel in music, visual arts, filmmaking, healing professions, counseling, spirituality, education, and charitable organizations. They shine in work driven by meaning, imagination, and compassion.',
                'relationships' => 'Romantic, devoted, and deeply understanding, Pisces ascendants seek an emotional and spiritual connection in love. Grounding idealism with clear communication helps build a secure, mutually supportive partnership.',
            ],
        ];

        $profile = $profiles[$risingSignName] ?? $profiles['Aries'];
        $dt = new \DateTime("{$dob} {$timeNormalized}", new \DateTimeZone('Asia/Kolkata'));

        return [
            'native_name'   => trim($name),
            'gender'        => ucfirst(strtolower(trim($gender))),
            'dob'           => $dt->format('j F Y'),
            'tob'           => $dt->format('h:i A'),
            'birth_place'   => trim($birthPlace),
            'rising_sign'   => $risingSignName,
            'hindi_name'    => $profile['hindi'],
            'ruler'         => $profile['ruler'],
            'element'       => $profile['element'],
            'degree'        => $asc['degree_formatted'],
            'nakshatra'     => $asc['nakshatra'] . ' (Pada ' . $asc['pada'] . ')',
            'about'         => "Ascendant is one of the most sought concepts in astrology when it comes to predicting the minute events in your life. At the time of birth, the sign that rises in the sky is the person's ascendant. It helps in making predictions about the minute events, unlike your Moon or Sun sign that help in making weekly, monthly or yearly predictions for you. Your ascendant is {$risingSignName}.",
            'personality'   => $profile['personality'],
            'physical'      => $profile['physical'],
            'health'        => $profile['health'],
            'career'        => $profile['career'],
            'relationships' => $profile['relationships'],
        ];
    }

    public function calculateRashi(
        string $name,
        string $gender,
        string $dob,
        string $tob,
        string $birthPlace,
        ?float $lat = null,
        ?float $lng = null
    ): array {
        if ($lat === null || $lng === null) {
            $coords = $this->getCoordinates($birthPlace);
            $lat = $coords['lat'];
            $lng = $coords['lon'];
        }

        $timeNormalized = !empty($tob) ? $tob : '12:00';
        $chart = $this->calculateChart($dob, $timeNormalized, $birthPlace, $lat, $lng);
        $moon = $chart['planets']['Moon'];

        $rashiName = explode(' ', $moon['rashi'])[0];

        $rashiProfiles = array(
            'Aries' => array(
                'hindi'         => 'Mesha',
                'ruler'         => 'Mars',
                'element'       => 'Fire',
                'syllables'     => 'Chu, Che, Cho, La, Li, Lu, Le, Lo, A',
                'lucky_color'   => 'Coral Red, Saffron',
                'lucky_stone'   => 'Red Coral (Moonga)',
                'personality'   => 'People born under Mesha (Aries) Moon Sign are emotionally dynamic, courageous, and spontaneous. Ruled by Mars, their mind reacts quickly to challenges and thrives on action, independence, and fresh initiatives. They are honest, warm-hearted, and straightforward, though impatience or quick temper can surface when things move too slowly.',
                'career'        => 'Aries Rashi natives excel in leadership, entrepreneurship, defense, engineering, sports, surgery, and fast-paced management roles where initiative and decisive action are rewarded.',
                'relationships' => 'Passionate, protective, and direct in love, Aries Moon individuals value loyalty and mutual independence. Practicing patience and calm listening brings lasting harmony in marriage and family life.',
            ),
            'Taurus' => array(
                'hindi'         => 'Vrishabha',
                'ruler'         => 'Venus',
                'element'       => 'Earth',
                'syllables'     => 'Ee, U, Ae, O, Va, Vi, Vu, Ve, Vo',
                'lucky_color'   => 'Pearl White, Pastel Pink',
                'lucky_stone'   => 'Diamond / White Sapphire',
                'personality'   => 'People born under Vrishabha (Taurus) Moon Sign possess emotional stability, patience, and a deep appreciation for peace, beauty, and comfort. Ruled by Venus, they remain calm under pressure, value loyalty, and prefer steady, reliable progress over sudden change.',
                'career'        => 'Taurus Rashi natives thrive in banking, finance, architecture, interior design, luxury retail, hospitality, music, and real estate where patience and resource management shine.',
                'relationships' => 'Devoted, sensual, and dependable, Taurus Moon individuals seek long-term emotional security and warmth in relationships, showing affection through steady support and loyalty.',
            ),
            'Gemini' => array(
                'hindi'         => 'Mithuna',
                'ruler'         => 'Mercury',
                'element'       => 'Air',
                'syllables'     => 'Ka, Ki, Ku, Gha, Ng, Chha, Ke, Ko, Ha',
                'lucky_color'   => 'Emerald Green, Yellow',
                'lucky_stone'   => 'Emerald (Panna)',
                'personality'   => 'People born under Mithuna (Gemini) Moon Sign are intellectually curious, expressive, and adaptable. Ruled by Mercury, they process emotions through conversation, learning, and social interaction, remaining youthful and quick-witted throughout life.',
                'career'        => 'Gemini Rashi natives excel in media, journalism, writing, teaching, marketing, telecommunications, consulting, and technology roles driven by information and communication.',
                'relationships' => 'Playful, communicative, and open-minded, Gemini Moon individuals need mental stimulation and lively dialogue in a partner to feel deeply connected.',
            ),
            'Cancer' => array(
                'hindi'         => 'Karka',
                'ruler'         => 'Moon',
                'element'       => 'Water',
                'syllables'     => 'Hi, Hu, He, Ho, Da, Di, Du, De, Do',
                'lucky_color'   => 'Pearl White, Silver',
                'lucky_stone'   => 'Natural Pearl (Moti)',
                'personality'   => 'People born under Karka (Cancer) Moon Sign are deeply intuitive, compassionate, and emotionally perceptive. Because the Moon rules this sign directly, they possess strong memory, imagination, and natural empathy toward family and close friends.',
                'career'        => 'Cancer Rashi natives flourish in healthcare, counseling, hospitality, real estate, education, human resources, and public welfare roles where care and intuition matter.',
                'relationships' => 'Nurturing, loyal, and family-centered, Cancer Moon individuals cherish emotional security and a warm domestic bond, flourishing with a gentle, reassuring partner.',
            ),
            'Leo' => array(
                'hindi'         => 'Simha',
                'ruler'         => 'Sun',
                'element'       => 'Fire',
                'syllables'     => 'Ma, Mi, Mu, Me, Mo, Ta, Ti, Tu, Te',
                'lucky_color'   => 'Golden Yellow, Ruby Red',
                'lucky_stone'   => 'Ruby (Manik)',
                'personality'   => 'People born under Simha (Leo) Moon Sign carry natural warmth, self-respect, and generous leadership. Ruled by the Sun, they have a noble, courageous heart, value integrity, and naturally inspire confidence in those around them.',
                'career'        => 'Leo Rashi natives shine in administration, executive leadership, politics, creative arts, entertainment, law, and entrepreneurship where authority and vision are valued.',
                'relationships' => 'Generous, loyal, and protective in love, Leo Moon individuals thrive when mutual appreciation, respect, and open-hearted affection anchor the partnership.',
            ),
            'Virgo' => array(
                'hindi'         => 'Kanya',
                'ruler'         => 'Mercury',
                'element'       => 'Earth',
                'syllables'     => 'To, Pa, Pi, Pu, Sha, Na, Tha, Pe, Po',
                'lucky_color'   => 'Emerald Green, Ivory',
                'lucky_stone'   => 'Emerald (Panna)',
                'personality'   => 'People born under Kanya (Virgo) Moon Sign are thoughtful, observant, and practical. Ruled by Mercury, they seek order, clarity, and meaningful service, analyzing situations carefully before making decisions.',
                'career'        => 'Virgo Rashi natives excel in data analytics, accounting, medicine, research, editing, software engineering, and operations where precision and problem-solving are essential.',
                'relationships' => 'Sincere, attentive, and supportive, Virgo Moon individuals express love through thoughtful actions and dependability, thriving with a calm, understanding partner.',
            ),
            'Libra' => array(
                'hindi'         => 'Tula',
                'ruler'         => 'Venus',
                'element'       => 'Air',
                'syllables'     => 'Ra, Ri, Ru, Re, Ro, Ta, Ti, Tu, Te',
                'lucky_color'   => 'Sky Blue, White',
                'lucky_stone'   => 'Opal / White Sapphire',
                'personality'   => 'People born under Tula (Libra) Moon Sign are diplomatic, gracious, and naturally drawn to balance and fairness. Ruled by Venus, they value peaceful relationships, refined aesthetics, and thoughtful cooperation.',
                'career'        => 'Libra Rashi natives succeed in law, diplomacy, public relations, design, fashion, human resources, and client-focused consulting.',
                'relationships' => 'Romantic, considerate, and partnership-oriented, Libra Moon individuals prioritize mutual respect, harmony, and open dialogue in marriage.',
            ),
            'Scorpio' => array(
                'hindi'         => 'Vrishchika',
                'ruler'         => 'Mars',
                'element'       => 'Water',
                'syllables'     => 'To, Na, Ni, Nu, Ne, No, Ya, Yi, Yu',
                'lucky_color'   => 'Crimson, Maroon',
                'lucky_stone'   => 'Red Coral (Moonga)',
                'personality'   => 'People born under Vrishchika (Scorpio) Moon Sign possess deep emotional resilience, sharp intuition, and unwavering focus. Ruled by Mars, they feel things intensely, value authenticity, and demonstrate remarkable inner strength during transformations.',
                'career'        => 'Scorpio Rashi natives excel in research, finance, psychology, surgery, cybersecurity, investigation, and strategic leadership.',
                'relationships' => 'Fiercely loyal and deeply devoted, Scorpio Moon individuals seek complete trust and emotional honesty, forming profound lifelong bonds.',
            ),
            'Sagittarius' => array(
                'hindi'         => 'Dhanu',
                'ruler'         => 'Jupiter',
                'element'       => 'Fire',
                'syllables'     => 'Ye, Yo, Bha, Bhi, Bhu, Dha, Pha, Bhe',
                'lucky_color'   => 'Turmeric Yellow, Saffron',
                'lucky_stone'   => 'Yellow Sapphire (Pukhraj)',
                'personality'   => 'People born under Dhanu (Sagittarius) Moon Sign are optimistic, principled, and philosophical. Ruled by Jupiter, they value honesty, higher wisdom, spiritual growth, and an expansive outlook on life.',
                'career'        => 'Sagittarius Rashi natives thrive in teaching, law, advisory roles, publishing, international business, travel, and leadership.',
                'relationships' => 'Warm, candid, and supportive, Sagittarius Moon individuals value shared ideals, friendship, and mutual growth in relationships.',
            ),
            'Capricorn' => array(
                'hindi'         => 'Makara',
                'ruler'         => 'Saturn',
                'element'       => 'Earth',
                'syllables'     => 'Bho, Ja, Ji, Khi, Khu, Khe, Kho, Ga, Gi',
                'lucky_color'   => 'Royal Blue, Charcoal',
                'lucky_stone'   => 'Blue Sapphire / Amethyst',
                'personality'   => 'People born under Makara (Capricorn) Moon Sign are disciplined, grounded, and quietly determined. Ruled by Saturn, they approach responsibilities with patience, integrity, and practical wisdom.',
                'career'        => 'Capricorn Rashi natives excel in corporate management, engineering, finance, government administration, law, and long-term business building.',
                'relationships' => 'Committed, protective, and reliable, Capricorn Moon individuals express love through steady loyalty and enduring family support.',
            ),
            'Aquarius' => array(
                'hindi'         => 'Kumbha',
                'ruler'         => 'Saturn',
                'element'       => 'Air',
                'syllables'     => 'Gu, Ge, Go, Sa, Si, Su, Se, So, Da',
                'lucky_color'   => 'Electric Blue, Indigo',
                'lucky_stone'   => 'Blue Sapphire / Amethyst',
                'personality'   => 'People born under Kumbha (Aquarius) Moon Sign are independent, thoughtful, and progressive. Ruled by Saturn, they balance humanitarian vision with calm objectivity and original thinking.',
                'career'        => 'Aquarius Rashi natives thrive in technology, software engineering, scientific research, social enterprise, media, and systems strategy.',
                'relationships' => 'Honest, friendly, and open-minded, Aquarius Moon individuals value intellectual rapport, trust, and mutual freedom in love.',
            ),
            'Pisces' => array(
                'hindi'         => 'Meena',
                'ruler'         => 'Jupiter',
                'element'       => 'Water',
                'syllables'     => 'Di, Du, Tha, Jha, De, Do, Cha, Chi',
                'lucky_color'   => 'Turmeric Yellow, Seafoam',
                'lucky_stone'   => 'Yellow Sapphire (Pukhraj)',
                'personality'   => 'People born under Meena (Pisces) Moon Sign are empathetic, imaginative, and spiritually inclined. Ruled by Jupiter, they possess gentle wisdom, artistic sensitivity, and deep compassion.',
                'career'        => 'Pisces Rashi natives excel in education, healing arts, counseling, creative media, music, spirituality, and advisory professions.',
                'relationships' => 'Affectionate, understanding, and devoted, Pisces Moon individuals seek a soulful, harmonious emotional connection with their partner.',
            ),
        );

        $profile = $rashiProfiles[$rashiName] ?? $rashiProfiles['Aries'];
        $dt = new \DateTime("{$dob} {$timeNormalized}", new \DateTimeZone('Asia/Kolkata'));

        return array(
            'native_name'   => trim($name),
            'gender'        => ucfirst(strtolower(trim($gender))),
            'dob'           => $dt->format('j F Y'),
            'tob'           => $dt->format('h:i A'),
            'birth_place'   => trim($birthPlace),
            'rashi'         => $rashiName,
            'hindi_name'    => $profile['hindi'],
            'ruler'         => $profile['ruler'],
            'element'       => $profile['element'],
            'nakshatra'     => $moon['nakshatra'] . ' (Pada ' . $moon['pada'] . ')',
            'nakshatra_lord'=> $moon['nakshatra_lord'],
            'degree'        => $moon['degree_formatted'],
            'syllables'     => $profile['syllables'],
            'lucky_color'   => $profile['lucky_color'],
            'lucky_stone'   => $profile['lucky_stone'],
            'about'         => "In Vedic astrology, your Moon Sign (Janma Rashi) is determined by the zodiac sign occupied by the Moon at the exact time and place of your birth. It governs your emotional nature, mind, daily Horoscope (Gochar), and compatibility. Your Janma Rashi is {$rashiName} ({$profile['hindi']}).",
            'personality'   => $profile['personality'],
            'career'        => $profile['career'],
            'relationships' => $profile['relationships'],
        );
    }

    public function calculateCharaKarakas(
        string $name,
        string $gender,
        string $dob,
        string $tob,
        string $birthPlace,
        ?float $lat = null,
        ?float $lng = null
    ): array {
        if ($lat === null || $lng === null) {
            $coords = $this->getCoordinates($birthPlace);
            $lat = $coords['lat'];
            $lng = $coords['lon'];
        }

        $timeNormalized = !empty($tob) ? $tob : '12:00';
        $chart = $this->calculateChart($dob, $timeNormalized, $birthPlace, $lat, $lng);
        $planets = $chart['planets'];

        $sevenPlanets = ['Sun', 'Moon', 'Mars', 'Mercury', 'Jupiter', 'Venus', 'Saturn'];
        $ranked = [];

        foreach ($sevenPlanets as $pName) {
            $fullDeg = $planets[$pName]['full_degree'];
            $degInSign = fmod($fullDeg, 30.0);

            $ranked[] = [
                'planet'           => $pName,
                'rashi'            => explode(' ', $planets[$pName]['rashi'])[0],
                'house'            => $planets[$pName]['house'],
                'nakshatra'        => $planets[$pName]['nakshatra'] . ' (Pada ' . $planets[$pName]['pada'] . ')',
                'degree_in_sign'   => round($degInSign, 4),
                'degree_formatted' => $planets[$pName]['degree_formatted'],
            ];
        }

        usort($ranked, function ($a, $b) {
            return $b['degree_in_sign'] <=> $a['degree_in_sign'];
        });

        $karakaTitles = [
            ['code' => 'AK',  'title' => 'Atmakaraka (Soul)',        'area' => 'Self, Soul Purpose & Primary Karmic Lesson'],
            ['code' => 'AmK', 'title' => 'Amatyakaraka (Career)',    'area' => 'Profession, Status & Mentors'],
            ['code' => 'BK',  'title' => 'Bhratrukaraka (Courage)',  'area' => 'Siblings, Initiative & Guru'],
            ['code' => 'MK',  'title' => 'Matrukaraka (Mind/Home)',  'area' => 'Mother, Emotional Peace & Property'],
            ['code' => 'PK',  'title' => 'Putrakaraka (Creativity)', 'area' => 'Children, Intelligence & Speculation'],
            ['code' => 'GK',  'title' => 'Gnatikaraka (Challenges)', 'area' => 'Obstacles, Health & Competitive Effort'],
            ['code' => 'DK',  'title' => 'Darakaraka (Spouse)',      'area' => 'Marriage, Life Partner & Relationships'],
        ];

        foreach ($ranked as $index => &$item) {
            $item['karaka_code']  = $karakaTitles[$index]['code'];
            $item['karaka_title'] = $karakaTitles[$index]['title'];
            $item['life_area']    = $karakaTitles[$index]['area'];
        }
        unset($item);

        $akInterpretations = [
            'Sun'     => 'With Sun as your Atmakaraka, your soul lesson centers on authentic leadership, humility over ego, self-reliance, and shining your light to uplift others.',
            'Moon'    => 'With Moon as your Atmakaraka, your soul seeks emotional wisdom, compassion, nurturing others, and mastering inner peace across life changes.',
            'Mars'    => 'With Mars as your Atmakaraka, your soul lesson involves channeling courage, discipline, and protective strength constructively without anger or impulsiveness.',
            'Mercury' => 'With Mercury as your Atmakaraka, your soul thrives on truth, clear communication, continuous learning, and using intellect with integrity.',
            'Jupiter' => 'With Jupiter as your Atmakaraka, your soul path revolves around wisdom, ethical principles, mentorship, spirituality, and generous guidance.',
            'Venus'   => 'With Venus as your Atmakaraka, your soul lesson centers on unconditional love, creative harmony, appreciating beauty, and valuing relationships deeply.',
            'Saturn'  => 'With Saturn as your Atmakaraka, your soul path emphasizes patience, perseverance, accountability, service, and building lasting strength through discipline.',
        ];

        $dkInterpretations = [
            'Sun'     => 'Sun as Darakaraka indicates a proud, confident, principled, and strong-willed spouse who commands natural respect and values honor in marriage.',
            'Moon'    => 'Moon as Darakaraka points to a caring, emotionally intuitive, family-oriented, and adaptable spouse who brings warmth and nurturing to the home.',
            'Mars'    => 'Mars as Darakaraka indicates an energetic, courageous, independent, and straightforward spouse who is protective and action-oriented.',
            'Mercury' => 'Mercury as Darakaraka suggests a witty, youthful, communicative, and intellectually curious spouse who values friendship and lively conversation.',
            'Jupiter' => 'Jupiter as Darakaraka indicates a wise, ethical, supportive, and optimistic spouse who brings stability, good counsel, and shared values.',
            'Venus'   => 'Venus as Darakaraka points to an affectionate, charming, artistic, and harmony-loving spouse who appreciates beauty, comfort, and romance.',
            'Saturn'  => 'Saturn as Darakaraka suggests a mature, loyal, practical, and responsible spouse who values long-term commitment, stability, and steady devotion.',
        ];

        $ak = $ranked[0];
        $dk = $ranked[6];
        $dt = new \DateTime("{$dob} {$timeNormalized}", new \DateTimeZone('Asia/Kolkata'));

        return [
            'native_name' => trim($name),
            'gender'      => ucfirst(strtolower(trim($gender))),
            'dob'         => $dt->format('j F Y'),
            'tob'         => $dt->format('h:i A'),
            'birth_place' => trim($birthPlace),
            'atmakaraka'  => array_merge($ak, [
                'interpretation' => $akInterpretations[$ak['planet']] ?? '',
            ]),
            'darakaraka'  => array_merge($dk, [
                'interpretation' => $dkInterpretations[$dk['planet']] ?? '',
            ]),
            'all_karakas' => $ranked,
        ];
    }

    public function calculateDetailedDashaReport(
        string $name,
        string $gender,
        string $dob,
        string $tob,
        string $birthPlace,
        ?float $lat = null,
        ?float $lng = null
    ): array {
        if ($lat === null || $lng === null) {
            $coords = $this->getCoordinates($birthPlace);
            $lat = $coords['lat'];
            $lng = $coords['lon'];
        }

        $timeNormalized = !empty($tob) ? $tob : '12:00';
        $chart = $this->calculateChart($dob, $timeNormalized, $birthPlace, $lat, $lng);

        $planets = $chart['planets'];
        $moon    = $planets['Moon'];
        $lagna   = $planets['Ascendant (Lagna)'];

        $localDt = new \DateTime("{$dob} {$timeNormalized}", new \DateTimeZone('Asia/Kolkata'));
        $now     = new \DateTime('now', new \DateTimeZone('Asia/Kolkata'));

        $dashaOrder = [
            ['planet' => 'Ketu',    'years' => 7],
            ['planet' => 'Venus',   'years' => 20],
            ['planet' => 'Sun',     'years' => 6],
            ['planet' => 'Moon',    'years' => 10],
            ['planet' => 'Mars',    'years' => 7],
            ['planet' => 'Rahu',    'years' => 18],
            ['planet' => 'Jupiter', 'years' => 16],
            ['planet' => 'Saturn',  'years' => 19],
            ['planet' => 'Mercury', 'years' => 17],
        ];

        $planetAnalytics = [
            'Sun' => [
                'theme'         => 'Authority, Career Visibility, Leadership & Self-Respect',
                'possibilities' => 'Promotions, government or leadership recognition, father-related milestones, clarity of purpose, and strong personal vitality.',
                'make_positive' => 'Lead with humility rather than ego, maintain a disciplined morning routine, and take ownership of high-visibility projects.',
                'remedy'        => 'Offer water (Arghya) to the rising Sun in a copper vessel each morning and chant "Om Hram Hreem Hroum Sah Suryaya Namah".',
            ],
            'Moon' => [
                'theme'         => 'Emotional Growth, Home, Mother, Creativity & Public Connection',
                'possibilities' => 'Changes in residence or domestic life, heightened intuition, creative breakthroughs, travel near water, and deeper family bonding.',
                'make_positive' => 'Protect your mental peace, stay hydrated, journal or meditate regularly, and avoid making major financial choices based on temporary moods.',
                'remedy'        => 'Offer raw milk or water to a Shiva Lingam on Mondays and chant "Om Shram Shreem Shroum Sah Chandraya Namah".',
            ],
            'Mars' => [
                'theme'         => 'Courage, Ambition, Property, Energy & Decisive Action',
                'possibilities' => 'Bold career moves, property or land acquisition, athletic stamina, technical mastery, and overcoming competitors—alongside occasional impatience or impulsiveness.',
                'make_positive' => 'Channel excess energy into structured fitness or sports, double-check contracts before signing, and pause before reacting in heated discussions.',
                'remedy'        => 'Recite Hanuman Chalisa on Tuesdays, donate red lentils (masoor dal), and chant "Om Kram Kreem Kroum Sah Bhaumaya Namah".',
            ],
            'Mercury' => [
                'theme'         => 'Intellect, Business, Communication, Learning & Networking',
                'possibilities' => 'Skill upgrades, business expansion, writing or media success, profitable negotiations, analytical breakthroughs, and short-distance travel.',
                'make_positive' => 'Keep clear written records, focus on continuous skill-building, speak truthfully, and avoid overthinking minor details.',
                'remedy'        => 'Feed green fodder to cows or donate green gram on Wednesdays and chant "Om Bram Brim Broum Sah Budhaya Namah".',
            ],
            'Jupiter' => [
                'theme'         => 'Wisdom, Fortune, Wealth Expansion, Mentorship & Family Growth',
                'possibilities' => 'Financial prosperity, marriage or childbirth blessings, higher education, spiritual guidance, ethical recognition, and long-term stability.',
                'make_positive' => 'Respect teachers and elders, share knowledge generously, invest in ethical long-term assets, and avoid complacency or over-indulgence.',
                'remedy'        => 'Wear yellow on Thursdays, donate turmeric or bananas, and chant "Om Gram Greem Groum Sah Gurave Namah".',
            ],
            'Venus' => [
                'theme'         => 'Relationships, Comfort, Art, Luxury & Financial Harmony',
                'possibilities' => 'Romance and marriage milestones, artistic or design success, vehicle or home upgrades, social popularity, and material comfort.',
                'make_positive' => 'Cultivate gratitude, maintain healthy boundaries in relationships, balance luxury spending with savings, and nurture creative talents.',
                'remedy'        => 'Offer white flowers or sweets on Fridays, practice kindness toward women, and chant "Om Dram Dreem Droum Sah Shukraya Namah".',
            ],
            'Saturn' => [
                'theme'         => 'Discipline, Karmic Rewards, Career Structure & Endurance',
                'possibilities' => 'Steady long-term authority, career restructuring, real estate or industrial gains, maturity, and lasting rewards earned through patient hard work.',
                'make_positive' => 'Embrace routine, honor every commitment on time, serve underprivileged workers, and avoid shortcuts or procrastination.',
                'remedy'        => 'Light a sesame oil lamp under a Peepal tree on Saturday evenings and chant "Om Pram Preem Proum Sah Shanaischaraya Namah".',
            ],
            'Rahu' => [
                'theme'         => 'Ambition, Foreign Connections, Innovation & Rapid Breakthroughs',
                'possibilities' => 'Sudden career leaps, foreign travel or relocation, technology/digital success, unconventional opportunities, and breaking past old limitations.',
                'make_positive' => 'Stay grounded in ethics, verify all financial and legal details thoroughly, and avoid speculative gambling or impulsive illusions.',
                'remedy'        => 'Chant "Om Bhram Bhreem Bhroum Sah Rahave Namah", keep your living space clutter-free, and donate blankets or black sesame on Saturdays.',
            ],
            'Ketu' => [
                'theme'         => 'Spiritual Insight, Detachment, Research & Inner Mastery',
                'possibilities' => 'Deep self-discovery, mastery in specialized research or coding, spiritual awakening, releasing toxic attachments, and intuitive clarity.',
                'make_positive' => 'Focus on inner growth and skill depth rather than external validation, practice mindfulness, and let go of situations that have run their course.',
                'remedy'        => 'Feed stray dogs, meditate regularly, and chant "Om Sram Sreem Sroum Sah Ketave Namah".',
            ],
        ];

        $moonSidereal    = $moon['full_degree'];
        $nakshatraSpan   = 360.0 / 27.0;
        $nakshatraIndex  = (int) floor($moonSidereal / $nakshatraSpan);
        $degInNakshatra  = fmod($moonSidereal, $nakshatraSpan);
        $elapsedFraction = $degInNakshatra / $nakshatraSpan;

        $startIndex = $nakshatraIndex % 9;
        $firstPlanetYears = $dashaOrder[$startIndex]['years'];
        $elapsedDaysBeforeBirth = (int) round($firstPlanetYears * $elapsedFraction * 365.2425);
        $cursorDate = (clone $localDt)->modify("-{$elapsedDaysBeforeBirth} days");

        $mahadashas = [];
        $currentMahadasha = null;
        $currentAntardasha = null;

        for ($i = 0; $i < 9; $i++) {
            $mIdx = ($startIndex + $i) % 9;
            $mPlanet = $dashaOrder[$mIdx]['planet'];
            $mYears = $dashaOrder[$mIdx]['years'];

            $mStart = ($i === 0) ? clone $localDt : clone $cursorDate;
            $antardashas = [];

            for ($j = 0; $j < 9; $j++) {
                $aIdx = ($mIdx + $j) % 9;
                $aPlanet = $dashaOrder[$aIdx]['planet'];
                $aFullYears = $dashaOrder[$aIdx]['years'];

                $antarYears = ($mYears * $aFullYears) / 120.0;
                $antarDays  = (int) round($antarYears * 365.2425);

                $aStart = clone $cursorDate;
                $aEnd   = (clone $cursorDate)->modify("+{$antarDays} days");

                if ($aEnd > $localDt) {
                    $effectiveStart = ($aStart < $localDt) ? clone $localDt : $aStart;
                    $isAntarActive  = ($now >= $effectiveStart && $now <= $aEnd);

                    $antarEntry = [
                        'planet'   => $aPlanet,
                        'pair'     => "{$mPlanet} – {$aPlanet}",
                        'start'    => $effectiveStart->format('d M Y'),
                        'end'      => $aEnd->format('d M Y'),
                        'active'   => $isAntarActive,
                        'focus'    => $planetAnalytics[$aPlanet]['theme'],
                        'guidance' => $planetAnalytics[$aPlanet]['make_positive'],
                    ];

                    if ($isAntarActive) {
                        $currentAntardasha = $antarEntry;
                    }

                    $antardashas[] = $antarEntry;
                }

                $cursorDate = $aEnd;
            }

            $mEnd = clone $cursorDate;
            $isMahaActive = ($now >= $mStart && $now <= $mEnd);
            $actualDuration = ($i === 0) ? round($mYears * (1.0 - $elapsedFraction), 2) : $mYears;

            $natalBody = $planets[$mPlanet];
            $natalHouse = $natalBody['house'];
            $natalRashi = explode(' ', $natalBody['rashi'])[0];

            $mahaEntry = [
                'planet'        => $mPlanet,
                'duration'      => $actualDuration . ' Years',
                'start'         => $mStart->format('d M Y'),
                'end'         => $mEnd->format('d M Y'),
                'active'        => $isMahaActive,
                'natal_house'   => $natalHouse,
                'natal_rashi'   => $natalRashi,
                'natal_context' => "{$mPlanet} is placed in {$natalRashi} in your {$natalHouse}th house, activating {$natalHouse}th-house themes throughout this period.",
                'theme'         => $planetAnalytics[$mPlanet]['theme'],
                'possibilities' => $planetAnalytics[$mPlanet]['possibilities'],
                'make_positive' => $planetAnalytics[$mPlanet]['make_positive'],
                'remedy'        => $planetAnalytics[$mPlanet]['remedy'],
                'antardashas'   => $antardashas,
            ];

            if ($isMahaActive) {
                $currentMahadasha = $mahaEntry;
            }
            $mahadashas[] = $mahaEntry;
        }

        return [
            'native_name'        => trim($name),
            'gender'             => ucfirst(strtolower(trim($gender))),
            'dob'                => $localDt->format('j F Y'),
            'tob'                => $localDt->format('h:i A'),
            'birth_place'        => trim($birthPlace),
            'lagna'              => explode(' ', $lagna['rashi'])[0],
            'moon_sign'          => explode(' ', $moon['rashi'])[0],
            'nakshatra'          => $moon['nakshatra'] . ' (Pada ' . $moon['pada'] . ')',
            'birth_dasha_lord'   => $dashaOrder[$startIndex]['planet'],
            'current_mahadasha'  => $currentMahadasha ?? $mahadashas[0],
            'current_antardasha' => $currentAntardasha,
            'mahadashas'         => $mahadashas,
        ];
    }

    public function calculateNakshatraReport(
        string $name,
        string $gender,
        string $dob,
        string $tob,
        string $birthPlace,
        ?float $lat = null,
        ?float $lng = null
    ): array {
        if ($lat === null || $lng === null) {
            $coords = $this->getCoordinates($birthPlace);
            $lat = $coords['lat'];
            $lng = $coords['lon'];
        }

        $timeNormalized = !empty($tob) ? $tob : '12:00';
        $chart = $this->calculateChart($dob, $timeNormalized, $birthPlace, $lat, $lng);

        $moon  = $chart['planets']['Moon'];
        $sun   = $chart['planets']['Sun'];
        $lagna = $chart['planets']['Ascendant (Lagna)'];

        $nakshatraSpan  = 360.0 / 27.0;
        $nakshatraIndex = (int) floor($moon['full_degree'] / $nakshatraSpan);
        $pada           = (int) $moon['pada'];

        $profiles = array(
            0  => array(
                'name'        => 'Ashwini',
                'lord'        => 'Ketu',
                'deity'       => 'Ashwini Kumaras (Physicians of the Gods)',
                'symbol'      => 'Horse’s Head',
                'animal'      => 'Male Horse (Ashwa)',
                'gana'        => 'Dev (Divine)',
                'nadi'        => 'Adi (Vata)',
                'tatva'       => 'Earth',
                'syllables'   => array(1 => 'Chu', 2 => 'Che', 3 => 'Cho', 4 => 'La'),
                'lucky_color' => 'Blood Red, Saffron',
                'gemstone'    => 'Cat’s Eye (Lehsunia)',
                'personality' => 'Ashwini natives are swift, energetic, pioneering, and naturally drawn to healing and helping others. Ruled by Ketu in Aries, they possess sharp intuition, youthful vitality, and an eagerness to initiate new paths, though occasional impatience can lead to hasty decisions.',
                'career'      => 'Excels in medicine, surgery, emergency services, sports, engineering, entrepreneurship, transportation, and fitness coaching.',
                'relationships' => 'Passionate, protective, and loyal in love, Ashwini individuals need mutual independence and honest communication to keep domestic life harmonious.',
            ),
            1  => array(
                'name'        => 'Bharani',
                'lord'        => 'Venus',
                'deity'       => 'Yama (Lord of Dharma & Discipline)',
                'symbol'      => 'Yoni (Womb / Vessel of Creation)',
                'animal'      => 'Male Elephant (Gaja)',
                'gana'        => 'Manav (Human)',
                'nadi'        => 'Madhya (Pitta)',
                'tatva'       => 'Earth',
                'syllables'   => array(1 => 'Li', 2 => 'Lu', 3 => 'Le', 4 => 'Lo'),
                'lucky_color' => 'Pastel Pink, White',
                'gemstone'    => 'Diamond / White Sapphire',
                'personality' => 'Bharani natives possess deep creative strength, strong moral conviction, and remarkable endurance. Ruled by Venus, they carry responsibilities seriously, feel life intensely, and undergo meaningful personal transformations that mature into wisdom.',
                'career'      => 'Thrives in creative arts, filmmaking, hospitality, law, finance, healthcare, obstetrics, and strategic leadership.',
                'relationships' => 'Deeply devoted, sensual, and protective partners who value sincerity and loyalty; balancing intensity with patience brings lasting marital bliss.',
            ),
            2  => array(
                'name'        => 'Krittika',
                'lord'        => 'Sun',
                'deity'       => 'Agni (God of Sacred Fire)',
                'symbol'      => 'Razor or Flame',
                'animal'      => 'Female Sheep (Mesha)',
                'gana'        => 'Rakshasa (Fierce)',
                'nadi'        => 'Antya (Kapha)',
                'tatva'       => 'Earth',
                'syllables'   => array(1 => 'A', 2 => 'Ee', 3 => 'U', 4 => 'Ae'),
                'lucky_color' => 'Golden Yellow, Ruby Red',
                'gemstone'    => 'Ruby (Manik)',
                'personality' => 'Krittika natives are sharp-minded, principled, courageous, and fiercely honest. Ruled by the Sun and Agni, they cut through illusion with clarity, protect loved ones steadfastly, and hold themselves to high standards of integrity.',
                'career'      => 'Shines in administration, military/defense, culinary arts, metallurgy, law, teaching, and executive leadership.',
                'relationships' => 'Loyal, dignified, and protective in love; softening blunt speech with warmth ensures deep harmony with their partner.',
            ),
            3  => array(
                'name'        => 'Rohini',
                'lord'        => 'Moon',
                'deity'       => 'Brahma / Prajapati (Creator)',
                'symbol'      => 'Ox Cart or Chariot',
                'animal'      => 'Male Serpent (Sarpa)',
                'gana'        => 'Manav (Human)',
                'nadi'        => 'Antya (Kapha)',
                'tatva'       => 'Earth',
                'syllables'   => array(1 => 'O', 2 => 'Va', 3 => 'Vi', 4 => 'Vu'),
                'lucky_color' => 'Pearl White, Silver',
                'gemstone'    => 'Natural Pearl (Moti)',
                'personality' => 'Rohini is the Moon’s favorite Nakshatra, bestowing magnetic charm, artistic refinement, emotional warmth, and a love for beauty and growth. Natives are grounded, patient, and skilled at manifesting abundance.',
                'career'      => 'Excels in design, fashion, music, agriculture, banking, luxury retail, hospitality, and public relations.',
                'relationships' => 'Romantic, affectionate, and deeply devoted to family comfort; thrives with a faithful, emotionally expressive spouse.',
            ),
            4  => array(
                'name'        => 'Mrigashira',
                'lord'        => 'Mars',
                'deity'       => 'Soma (Moon God of Nectar)',
                'symbol'      => 'Deer’s Head',
                'animal'      => 'Female Serpent (Sarpa)',
                'gana'        => 'Dev (Divine)',
                'nadi'        => 'Madhya (Pitta)',
                'tatva'       => 'Earth',
                'syllables'   => array(1 => 'Ve', 2 => 'Vo', 3 => 'Ka', 4 => 'Ki'),
                'lucky_color' => 'Coral Red, Silver Grey',
                'gemstone'    => 'Red Coral (Moonga)',
                'personality' => 'Mrigashira natives are curious, gentle, perceptive seekers who love exploring ideas, places, and knowledge. Ruled by Mars, they combine alert mental agility with a tender, artistic heart.',
                'career'      => 'Thrives in research, writing, travel, marketing, textiles, architecture, teaching, and consulting.',
                'relationships' => 'Playful, communicative, and loyal once settled; open conversation prevents restlessness or misunderstandings.',
            ),
            5  => array(
                'name'        => 'Ardra',
                'lord'        => 'Rahu',
                'deity'       => 'Rudra (Storm Form of Lord Shiva)',
                'symbol'      => 'Teardrop or Diamond',
                'animal'      => 'Female Dog (Shwan)',
                'gana'        => 'Manav (Human)',
                'nadi'        => 'Adi (Vata)',
                'tatva'       => 'Water',
                'syllables'   => array(1 => 'Ku', 2 => 'Gha', 3 => 'Ng', 4 => 'Chha'),
                'lucky_color' => 'Electric Blue, Emerald Green',
                'gemstone'    => 'Hessonite (Gomed)',
                'personality' => 'Ardra natives possess brilliant analytical intellect, emotional depth, and transformative resilience. Like a cleansing rainstorm after thunder, they turn intense life challenges into clarity and empathy.',
                'career'      => 'Excels in software engineering, data science, research, pharmaceuticals, psychology, electronics, and investigative journalism.',
                'relationships' => 'Deeply caring and fiercely loyal beneath a thoughtful exterior; cultivating emotional calm strengthens partnership trust.',
            ),
            6  => array(
                'name'        => 'Punarvasu',
                'lord'        => 'Jupiter',
                'deity'       => 'Aditi (Mother of the Gods)',
                'symbol'      => 'Bow and Quiver of Arrows',
                'animal'      => 'Female Cat (Marjara)',
                'gana'        => 'Dev (Divine)',
                'nadi'        => 'Adi (Vata)',
                'tatva'       => 'Water',
                'syllables'   => array(1 => 'Ke', 2 => 'Ko', 3 => 'Ha', 4 => 'Hi'),
                'lucky_color' => 'Turmeric Yellow, Cream',
                'gemstone'    => 'Yellow Sapphire (Pukhraj)',
                'personality' => 'Punarvasu signifies renewal and return of light. Ruled by Jupiter, natives are optimistic, forgiving, wise, and adaptable, bouncing back gracefully from setbacks with warmth and integrity.',
                'career'      => 'Thrives in teaching, counseling, publishing, hospitality, architecture, import-export, and spiritual mentorship.',
                'relationships' => 'Harmonious, supportive, and family-oriented; brings peace, forgiveness, and shared wisdom into marriage.',
            ),
            7  => array(
                'name'        => 'Pushya',
                'lord'        => 'Saturn',
                'deity'       => 'Brihaspati (Guru of the Gods)',
                'symbol'      => 'Cow’s Udder, Lotus, or Circle',
                'animal'      => 'Male Sheep (Mesha)',
                'gana'        => 'Dev (Divine)',
                'nadi'        => 'Madhya (Pitta)',
                'tatva'       => 'Water',
                'syllables'   => array(1 => 'Hu', 2 => 'He', 3 => 'Ho', 4 => 'Da'),
                'lucky_color' => 'Royal Blue, Golden Yellow',
                'gemstone'    => 'Blue Sapphire / Yellow Sapphire',
                'personality' => 'Considered one of the most auspicious Nakshatras, Pushya bestows nourishment, wisdom, discipline, and selfless service. Natives are dependable, ethical, and naturally respected in their communities.',
                'career'      => 'Excels in finance, governance, education, healthcare, food/dairy industries, real estate, and advisory leadership.',
                'relationships' => 'Deeply nurturing, faithful, and protective in family life; builds a stable, respected, and caring household.',
            ),
            8  => array(
                'name'        => 'Ashlesha',
                'lord'        => 'Mercury',
                'deity'       => 'Nagas (Serpent Deities of Wisdom)',
                'symbol'      => 'Coiled Serpent',
                'animal'      => 'Male Cat (Marjara)',
                'gana'        => 'Rakshasa (Fierce)',
                'nadi'        => 'Antya (Kapha)',
                'tatva'       => 'Water',
                'syllables'   => array(1 => 'Di', 2 => 'Du', 3 => 'De', 4 => 'Do'),
                'lucky_color' => 'Emerald Green, Silver',
                'gemstone'    => 'Emerald (Panna)',
                'personality' => 'Ashlesha natives have razor-sharp intuition, psychological insight, and strategic focus. Ruled by Mercury in Cancer, they read people effortlessly and possess strong protective instincts.',
                'career'      => 'Thrives in psychology, medicine, pharmacology, strategy, law, investment analysis, and research.',
                'relationships' => 'Intensely loyal and perceptive; practicing openness and letting go of over-protectiveness deepens marital trust.',
            ),
            9  => array(
                'name'        => 'Magha',
                'lord'        => 'Ketu',
                'deity'       => 'Pitris (Ancestral Guardians)',
                'symbol'      => 'Royal Throne Room',
                'animal'      => 'Male Rat (Mushaka)',
                'gana'        => 'Rakshasa (Fierce)',
                'nadi'        => 'Antya (Kapha)',
                'tatva'       => 'Water',
                'syllables'   => array(1 => 'Ma', 2 => 'Mi', 3 => 'Mu', 4 => 'Me'),
                'lucky_color' => 'Golden Amber, Ivory',
                'gemstone'    => 'Cat’s Eye / Ruby',
                'personality' => 'Magha natives carry natural dignity, respect for heritage, generosity, and leadership presence. Anchored in Leo, they value honor, tradition, and making a meaningful legacy.',
                'career'      => 'Shines in executive leadership, government, law, history, corporate management, and public institutions.',
                'relationships' => 'Proud, loyal, and generous toward family; pairing leadership with humility creates deep domestic harmony.',
            ),
            10 => array(
                'name'        => 'Purva Phalguni',
                'lord'        => 'Venus',
                'deity'       => 'Bhaga (Deity of Fortune & Marital Bliss)',
                'symbol'      => 'Front Legs of a Bed or Hammock',
                'animal'      => 'Female Rat (Mushaka)',
                'gana'        => 'Manav (Human)',
                'nadi'        => 'Madhya (Pitta)',
                'tatva'       => 'Water',
                'syllables'   => array(1 => 'Mo', 2 => 'Ta', 3 => 'Ti', 4 => 'Tu'),
                'lucky_color' => 'Rose Pink, Gold',
                'gemstone'    => 'Diamond / White Sapphire',
                'personality' => 'Purva Phalguni natives are warm, creative, sociable, and appreciative of joy, art, and beauty. Ruled by Venus in Leo, they radiate charm, generosity, and affectionate enthusiasm.',
                'career'      => 'Excels in entertainment, design, hospitality, event management, luxury brands, music, and public relations.',
                'relationships' => 'Romantic, affectionate, and devoted to marital happiness; thrives in partnerships filled with warmth and mutual appreciation.',
            ),
            11 => array(
                'name'        => 'Uttara Phalguni',
                'lord'        => 'Sun',
                'deity'       => 'Aryaman (Deity of Patronage & Contracts)',
                'symbol'      => 'Back Legs of a Bed',
                'animal'      => 'Male Cow / Bull (Gau)',
                'gana'        => 'Manav (Human)',
                'nadi'        => 'Adi (Vata)',
                'tatva'       => 'Fire',
                'syllables'   => array(1 => 'Te', 2 => 'To', 3 => 'Pa', 4 => 'Pi'),
                'lucky_color' => 'Golden Yellow, Emerald Green',
                'gemstone'    => 'Ruby (Manik)',
                'personality' => 'Uttara Phalguni natives blend warmth with reliability, integrity, and organizational skill. They honor commitments, support friends steadfastly, and build lasting success through ethical effort.',
                'career'      => 'Thrives in administration, human resources, finance, healthcare, social leadership, and consulting.',
                'relationships' => 'Faithful, responsible, and supportive partners who value long-term commitment and family stability.',
            ),
            12 => array(
                'name'        => 'Hasta',
                'lord'        => 'Moon',
                'deity'       => 'Savitar (Creative Solar Deity)',
                'symbol'      => 'Open Hand or Palm',
                'animal'      => 'Female Buffalo (Mahisha)',
                'gana'        => 'Dev (Divine)',
                'nadi'        => 'Adi (Vata)',
                'tatva'       => 'Fire',
                'syllables'   => array(1 => 'Pu', 2 => 'Sha', 3 => 'Na', 4 => 'Tha'),
                'lucky_color' => 'Pearl White, Mint Green',
                'gemstone'    => 'Natural Pearl (Moti)',
                'personality' => 'Hasta natives are skillful, resourceful, witty, and practical problem-solvers. Ruled by the Moon in Virgo, they possess dexterous talent, sharp memory, and a calm, helpful demeanor.',
                'career'      => 'Excels in craftsmanship, surgery, software engineering, accounting, writing, therapy, and precision trades.',
                'relationships' => 'Attentive, caring, and dependable in love; releasing overthinking allows warmth to flow effortlessly.',
            ),
            13 => array(
                'name'        => 'Chitra',
                'lord'        => 'Mars',
                'deity'       => 'Tvashtar (Celestial Architect)',
                'symbol'      => 'Bright Jewel or Pearl',
                'animal'      => 'Female Tiger (Vyaghra)',
                'gana'        => 'Rakshasa (Fierce)',
                'nadi'        => 'Madhya (Pitta)',
                'tatva'       => 'Fire',
                'syllables'   => array(1 => 'Pe', 2 => 'Po', 3 => 'Ra', 4 => 'Ri'),
                'lucky_color' => 'Coral Red, Ivory',
                'gemstone'    => 'Red Coral (Moonga)',
                'personality' => 'Chitra natives are visionary designers, charismatic, independent, and drawn to elegance and structure. Ruled by Mars, they combine creative aesthetics with bold execution.',
                'career'      => 'Thrives in architecture, UI/UX design, engineering, fashion, photography, branding, and entrepreneurship.',
                'relationships' => 'Passionate, honest, and protective partners who thrive with an intellectually engaging and supportive spouse.',
            ),
            14 => array(
                'name'        => 'Swati',
                'lord'        => 'Rahu',
                'deity'       => 'Vayu (God of the Wind)',
                'symbol'      => 'Young Shoot Swaying in the Wind',
                'animal'      => 'Male Buffalo (Mahisha)',
                'gana'        => 'Dev (Divine)',
                'nadi'        => 'Antya (Kapha)',
                'tatva'       => 'Fire',
                'syllables'   => array(1 => 'Ru', 2 => 'Re', 3 => 'Ro', 4 => 'Ta'),
                'lucky_color' => 'Sky Blue, Electric Blue',
                'gemstone'    => 'Hessonite (Gomed)',
                'personality' => 'Swati natives are adaptable, diplomatic, independent, and socially graceful. Like a young plant bending in the breeze, they stay resilient through change while valuing personal freedom.',
                'career'      => 'Excels in business, trade, aviation, diplomacy, law, communications, and technology networking.',
                'relationships' => 'Courteous, fair-minded, and harmonious in love; thrives when both partners enjoy mutual respect and space.',
            ),
            15 => array(
                'name'        => 'Vishakha',
                'lord'        => 'Jupiter',
                'deity'       => 'Indra & Agni (Power & Transformation)',
                'symbol'      => 'Triumphal Archway or Potter’s Wheel',
                'animal'      => 'Male Tiger (Vyaghra)',
                'gana'        => 'Rakshasa (Fierce)',
                'nadi'        => 'Antya (Kapha)',
                'tatva'       => 'Fire',
                'syllables'   => array(1 => 'Ti', 2 => 'Tu', 3 => 'Te', 4 => 'To'),
                'lucky_color' => 'Turmeric Yellow, Saffron',
                'gemstone'    => 'Yellow Sapphire (Pukhraj)',
                'personality' => 'Vishakha natives are goal-oriented, determined, persuasive, and courageous. Ruled by Jupiter, once they set their sights on a meaningful milestone, their focus and perseverance are unstoppable.',
                'career'      => 'Thrives in leadership, law, politics, research, finance, public speaking, and competitive business.',
                'relationships' => 'Deeply loyal and committed once bonded; balancing ambition with quality family time brings lasting joy.',
            ),
            16 => array(
                'name'        => 'Anuradha',
                'lord'        => 'Saturn',
                'deity'       => 'Mitra (Deity of Friendship & Devotion)',
                'symbol'      => 'Lotus Flower or Staff',
                'animal'      => 'Female Deer (Mriga)',
                'gana'        => 'Dev (Divine)',
                'nadi'        => 'Madhya (Pitta)',
                'tatva'       => 'Fire',
                'syllables'   => array(1 => 'Na', 2 => 'Ni', 3 => 'Nu', 4 => 'Ne'),
                'lucky_color' => 'Royal Blue, Crimson',
                'gemstone'    => 'Blue Sapphire / Amethyst',
                'personality' => 'Anuradha natives are loyal friends, organized leaders, and emotionally resilient. Like a lotus blooming cleanly in deep water, they maintain grace, devotion, and balance in every situation.',
                'career'      => 'Excels in management, international relations, finance, data analytics, counseling, and organizational leadership.',
                'relationships' => 'Devoted, faithful, and deeply supportive partners who value friendship as the core of marriage.',
            ),
            17 => array(
                'name'        => 'Jyeshtha',
                'lord'        => 'Mercury',
                'deity'       => 'Indra (King of the Gods)',
                'symbol'      => 'Circular Amulet or Umbrella',
                'animal'      => 'Male Deer (Mriga)',
                'gana'        => 'Rakshasa (Fierce)',
                'nadi'        => 'Adi (Vata)',
                'tatva'       => 'Air',
                'syllables'   => array(1 => 'No', 2 => 'Ya', 3 => 'Yi', 4 => 'Yu'),
                'lucky_color' => 'Emerald Green, Maroon',
                'gemstone'    => 'Emerald (Panna)',
                'personality' => 'Jyeshtha natives are perceptive, protective, resourceful, and mentally sharp. Ruled by Mercury in Scorpio, they naturally take responsibility in crises and possess deep strategic wisdom.',
                'career'      => 'Thrives in executive strategy, defense, surgery, investigative research, finance, and senior administration.',
                'relationships' => 'Fiercely protective and loyal to loved ones; sharing vulnerability openly builds deep emotional closeness.',
            ),
            18 => array(
                'name'        => 'Mula',
                'lord'        => 'Ketu',
                'deity'       => 'Nirriti (Goddess of Dissolution & Roots)',
                'symbol'      => 'Tied Bunch of Roots',
                'animal'      => 'Male Dog (Shwan)',
                'gana'        => 'Rakshasa (Fierce)',
                'nadi'        => 'Adi (Vata)',
                'tatva'       => 'Air',
                'syllables'   => array(1 => 'Ye', 2 => 'Yo', 3 => 'Bha', 4 => 'Bhi'),
                'lucky_color' => 'Turmeric Yellow, Smoky Grey',
                'gemstone'    => 'Cat’s Eye / Yellow Sapphire',
                'personality' => 'Mula natives seek the root truth of everything. Ruled by Ketu in Sagittarius, they are philosophical, courageous, honest, and unafraid to rebuild life on authentic principles.',
                'career'      => 'Excels in research, medicine, philosophy, law, software architecture, spirituality, and investigative fields.',
                'relationships' => 'Honest, principled, and loyal partners who thrive with an understanding, spiritually aligned spouse.',
            ),
            19 => array(
                'name'        => 'Purva Ashadha',
                'lord'        => 'Venus',
                'deity'       => 'Apas (Cosmic Waters)',
                'symbol'      => 'Elephant Tusk or Winnowing Fan',
                'animal'      => 'Male Monkey (Vanara)',
                'gana'        => 'Manav (Human)',
                'nadi'        => 'Madhya (Pitta)',
                'tatva'       => 'Air',
                'syllables'   => array(1 => 'Bhu', 2 => 'Dha', 3 => 'Pha', 4 => 'Dha'),
                'lucky_color' => 'White, Turmeric Yellow',
                'gemstone'    => 'Diamond / White Sapphire',
                'personality' => 'Purva Ashadha means "early victory," conferring optimism, eloquence, creative flair, and persuasive charm. Natives inspire others effortlessly and stand firmly by their convictions.',
                'career'      => 'Thrives in public speaking, law, arts, media, education, consulting, and international commerce.',
                'relationships' => 'Warm, expressive, and devoted in love; mutual respect and open dialogue sustain lasting harmony.',
            ),
            20 => array(
                'name'        => 'Uttara Ashadha',
                'lord'        => 'Sun',
                'deity'       => 'Vishvadevas (Universal Gods of Dharma)',
                'symbol'      => 'Elephant Tusk or Planks of a Bed',
                'animal'      => 'Male Mongoose (Nakula)',
                'gana'        => 'Manav (Human)',
                'nadi'        => 'Antya (Kapha)',
                'tatva'       => 'Air',
                'syllables'   => array(1 => 'Bhe', 2 => 'Bho', 3 => 'Ja', 4 => 'Ji'),
                'lucky_color' => 'Golden Yellow, Royal Blue',
                'gemstone'    => 'Ruby (Manik)',
                'personality' => 'Uttara Ashadha signifies "lasting victory," blessing natives with patience, ethical integrity, leadership, and enduring achievement earned through steady perseverance.',
                'career'      => 'Excels in governance, corporate leadership, law, social welfare, engineering, and institutional management.',
                'relationships' => 'Faithful, dignified, and deeply dependable partners who build stable, honorable family foundations.',
            ),
            21 => array(
                'name'        => 'Shravana',
                'lord'        => 'Moon',
                'deity'       => 'Lord Vishnu (Preserver of Cosmic Order)',
                'symbol'      => 'Ear or Three Footprints',
                'animal'      => 'Female Monkey (Vanara)',
                'gana'        => 'Dev (Divine)',
                'nadi'        => 'Antya (Kapha)',
                'tatva'       => 'Air',
                'syllables'   => array(1 => 'Khi', 2 => 'Khu', 3 => 'Khe', 4 => 'Kho'),
                'lucky_color' => 'Pearl White, Sky Blue',
                'gemstone'    => 'Natural Pearl (Moti)',
                'personality' => 'Shravana is the star of sacred listening and learning. Natives are observant, wise, empathetic, and articulate, absorbing knowledge quickly and offering thoughtful counsel.',
                'career'      => 'Thrives in education, media, counseling, languages, music, consulting, and technology communication.',
                'relationships' => 'Attentive, loyal, and supportive in marriage; deep listening creates strong emotional intimacy.',
            ),
            22 => array(
                'name'        => 'Dhanishta',
                'lord'        => 'Mars',
                'deity'       => 'Ashta Vasus (Eight Elemental Deities)',
                'symbol'      => 'Musical Drum (Mridanga) or Flute',
                'animal'      => 'Female Lion (Simha)',
                'gana'        => 'Rakshasa (Fierce)',
                'nadi'        => 'Madhya (Pitta)',
                'tatva'       => 'Air',
                'syllables'   => array(1 => 'Ga', 2 => 'Gi', 3 => 'Gu', 4 => 'Ge'),
                'lucky_color' => 'Coral Red, Royal Blue',
                'gemstone'    => 'Red Coral (Moonga)',
                'personality' => 'Dhanishta natives are energetic, rhythmic, ambitious, and resourceful. Ruled by Mars, they excel at timing, teamwork, and turning creative or technical discipline into prosperity.',
                'career'      => 'Excels in finance, music, engineering, real estate, project management, and sports.',
                'relationships' => 'Generous, protective, and action-oriented in love; balancing career drive with emotional warmth ensures harmony.',
            ),
            23 => array(
                'name'        => 'Shatabhisha',
                'lord'        => 'Rahu',
                'deity'       => 'Varuna (Cosmic Guardian of Waters & Law)',
                'symbol'      => 'Empty Circle or 100 Stars',
                'animal'      => 'Female Horse (Ashwa)',
                'gana'        => 'Rakshasa (Fierce)',
                'nadi'        => 'Adi (Vata)',
                'tatva'       => 'Ether',
                'syllables'   => array(1 => 'Go', 2 => 'Sa', 3 => 'Si', 4 => 'Su'),
                'lucky_color' => 'Electric Blue, Indigo',
                'gemstone'    => 'Hessonite (Gomed)',
                'personality' => 'Known as the star of a hundred healers, Shatabhisha bestows independent thinking, scientific curiosity, humanitarian ideals, and deep problem-solving ability.',
                'career'      => 'Thrives in medicine, pharmacology, software architecture, astronomy, research, and social innovation.',
                'relationships' => 'Principled, loyal, and self-reliant; sharing thoughts openly helps partners connect with their thoughtful nature.',
            ),
            24 => array(
                'name'        => 'Purva Bhadrapada',
                'lord'        => 'Jupiter',
                'deity'       => 'Aja Ekapada (Mystic Fire Deity)',
                'symbol'      => 'Sword or Front Legs of a Cot',
                'animal'      => 'Male Lion (Simha)',
                'gana'        => 'Manav (Human)',
                'nadi'        => 'Adi (Vata)',
                'tatva'       => 'Ether',
                'syllables'   => array(1 => 'Se', 2 => 'So', 3 => 'Da', 4 => 'Di'),
                'lucky_color' => 'Turmeric Yellow, Indigo',
                'gemstone'    => 'Yellow Sapphire (Pukhraj)',
                'personality' => 'Purva Bhadrapada natives are idealistic, perceptive, courageous, and deeply philosophical. Ruled by Jupiter, they combine sharp intellect with a sincere desire to uplift others.',
                'career'      => 'Excels in teaching, research, finance, writing, counseling, technology, and advisory roles.',
                'relationships' => 'Devoted, honest, and protective in love; maintaining optimism and patience nurtures lasting marital peace.',
            ),
            25 => array(
                'name'        => 'Uttara Bhadrapada',
                'lord'        => 'Saturn',
                'deity'       => 'Ahir Budhnya (Serpent of the Deep Waters)',
                'symbol'      => 'Twins or Back Legs of a Cot',
                'animal'      => 'Female Cow (Gau)',
                'gana'        => 'Manav (Human)',
                'nadi'        => 'Madhya (Pitta)',
                'tatva'       => 'Ether',
                'syllables'   => array(1 => 'Du', 2 => 'Tha', 3 => 'Jha', 4 => 'Da'),
                'lucky_color' => 'Royal Blue, Seafoam Green',
                'gemstone'    => 'Blue Sapphire / Yellow Sapphire',
                'personality' => 'Uttara Bhadrapada natives possess calm wisdom, emotional maturity, compassion, and steady endurance. They remain composed during storms and grow steadily in respect and prosperity.',
                'career'      => 'Thrives in counseling, finance, philanthropy, research, education, healthcare, and strategic management.',
                'relationships' => 'Gentle, faithful, and deeply supportive partners who bring stability and harmony to family life.',
            ),
            26 => array(
                'name'        => 'Revati',
                'lord'        => 'Mercury',
                'deity'       => 'Pushan (Nurturer & Protector of Travelers)',
                'symbol'      => 'Pair of Fish or Drum',
                'animal'      => 'Female Elephant (Gaja)',
                'gana'        => 'Dev (Divine)',
                'nadi'        => 'Antya (Kapha)',
                'tatva'       => 'Ether',
                'syllables'   => array(1 => 'De', 2 => 'Do', 3 => 'Cha', 4 => 'Chi'),
                'lucky_color' => 'Emerald Green, Golden Yellow',
                'gemstone'    => 'Emerald / Yellow Sapphire',
                'personality' => 'As the final Nakshatra of the zodiac, Revati bestows empathy, creativity, spiritual grace, and nurturing kindness. Ruled by Mercury in Pisces, natives blend intuition with articulate intelligence.',
                'career'      => 'Excels in creative arts, healing professions, education, travel, media, social work, and advisory roles.',
                'relationships' => 'Affectionate, forgiving, and deeply devoted in love; thrives in a kind, emotionally harmonious partnership.',
            ),
        );

        $profile = $profiles[$nakshatraIndex] ?? $profiles[0];
        $activeSyllable = $profile['syllables'][$pada] ?? $profile['syllables'][1];
        $dt = new \DateTime("{$dob} {$timeNormalized}", new \DateTimeZone('Asia/Kolkata'));

        $padaFocus = array(
            1 => 'Pada 1 emphasizes initiative, self-discovery, and pioneering courage in expressing this Nakshatra’s gifts.',
            2 => 'Pada 2 emphasizes practical stability, resourcefulness, material growth, and steady dedication.',
            3 => 'Pada 3 emphasizes communication, intellect, adaptability, and social or creative expression.',
            4 => 'Pada 4 emphasizes emotional wisdom, empathy, intuition, and spiritual or family fulfillment.',
        );

        return array(
            'native_name'      => trim($name),
            'gender'           => ucfirst(strtolower(trim($gender))),
            'dob'              => $dt->format('j F Y'),
            'tob'              => $dt->format('h:i A'),
            'birth_place'      => trim($birthPlace),
            'nakshatra'        => $profile['name'],
            'pada'             => $pada,
            'lord'             => $profile['lord'],
            'deity'            => $profile['deity'],
            'symbol'           => $profile['symbol'],
            'animal'           => $profile['animal'],
            'gana'             => $profile['gana'],
            'nadi'             => $profile['nadi'],
            'tatva'            => $profile['tatva'],
            'rashi'            => explode(' ', $moon['rashi'])[0],
            'lagna'            => explode(' ', $lagna['rashi'])[0],
            'moon_degree'      => $moon['degree_formatted'],
            'active_syllable'  => $activeSyllable,
            'all_syllables'    => array_values($profile['syllables']),
            'lucky_color'      => $profile['lucky_color'],
            'gemstone'         => $profile['gemstone'],
            'pada_insight'     => $padaFocus[$pada],
            'personality'      => $profile['personality'],
            'career'           => $profile['career'],
            'relationships'    => $profile['relationships'],
        );
    }
}