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

}