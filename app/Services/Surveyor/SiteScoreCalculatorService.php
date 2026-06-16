<?php

namespace App\Services\Surveyor;

use Illuminate\Support\Facades\DB;

/**
 * SiteScoreCalculatorService
 * Formula sesuai spreadsheet GC - Site Score Outlet Modified 06-2025
 *
 * Threshold:
 *   LDP: APPROVED >= 60%, CONSIDERATION 50-60%, REJECTED < 50%
 *   BDP: APPROVED >= 100%, CONSIDERATION 60-100%, REJECTED < 60%
 */
class SiteScoreCalculatorService
{
    // Rasio CU untuk potensi omset (dari spreadsheet)
    private const RASIO_MOTOR    = 0.0050;
    private const RASIO_PEJALAN  = 0.0025;
    private const RASIO_RUMAH_Q1 = 0.0100;
    private const RASIO_RUMAH_Q2 = 0.0025;
    private const RASIO_RUMAH_Q3 = 0.00125;
    private const RASIO_RUMAH_Q4 = 0.00125;
    private const MOE_DEFAULT    = 0.20;

    // Threshold sesuai spreadsheet
    private const THRESHOLD = [
        'LDP' => ['approved' => 0.60, 'consideration' => 0.50],
        'BDP' => ['approved' => 1.00, 'consideration' => 0.60],
    ];

    public function calculate(array $data, float $moe = self::MOE_DEFAULT, string $tipeOutlet = 'LDP', array $customConfig = []): array
    {
        $isCustom = ($data['formula_type'] ?? 'DEFAULT') === 'CUSTOM' && !empty($customConfig);

        $multiplierRumah = 1.0;
        $multiplierTraffic = 1.0;
        if ($isCustom && isset($customConfig['multipliers'])) {
            if (($customConfig['multipliers']['rumah'] ?? 0) > 0) $multiplierRumah = (float) $customConfig['multipliers']['rumah'];
            if (($customConfig['multipliers']['traffic'] ?? 0) > 0) $multiplierTraffic = (float) $customConfig['multipliers']['traffic'];
        }

        // Terapkan multiplier ke individual inputs agar sejalan dengan Frontend
        $data['motor_weekday_pagi'] = ($data['motor_weekday_pagi'] ?? 0) * $multiplierTraffic;
        $data['motor_weekday_siang'] = ($data['motor_weekday_siang'] ?? 0) * $multiplierTraffic;
        $data['motor_weekday_sore'] = ($data['motor_weekday_sore'] ?? 0) * $multiplierTraffic;
        $data['motor_weekend_pagi'] = ($data['motor_weekend_pagi'] ?? 0) * $multiplierTraffic;
        $data['motor_weekend_siang'] = ($data['motor_weekend_siang'] ?? 0) * $multiplierTraffic;
        $data['motor_weekend_sore'] = ($data['motor_weekend_sore'] ?? 0) * $multiplierTraffic;

        $data['pejalan_weekday_pagi'] = ($data['pejalan_weekday_pagi'] ?? 0) * $multiplierTraffic;
        $data['pejalan_weekday_siang'] = ($data['pejalan_weekday_siang'] ?? 0) * $multiplierTraffic;
        $data['pejalan_weekday_sore'] = ($data['pejalan_weekday_sore'] ?? 0) * $multiplierTraffic;
        $data['pejalan_weekend_pagi'] = ($data['pejalan_weekend_pagi'] ?? 0) * $multiplierTraffic;
        $data['pejalan_weekend_siang'] = ($data['pejalan_weekend_siang'] ?? 0) * $multiplierTraffic;
        $data['pejalan_weekend_sore'] = ($data['pejalan_weekend_sore'] ?? 0) * $multiplierTraffic;

        $data['rumah_q1'] = ($data['rumah_q1'] ?? 0) * $multiplierRumah;
        $data['rumah_q2'] = ($data['rumah_q2'] ?? 0) * $multiplierRumah;
        $data['rumah_q3'] = ($data['rumah_q3'] ?? 0) * $multiplierRumah;
        $data['rumah_q4'] = ($data['rumah_q4'] ?? 0) * $multiplierRumah;

        $totalMotor   = $this->sumMotor($data);
        $totalPejalan = $this->sumPejalan($data);

        $data['traffic_motor']   = $totalMotor;
        $data['traffic_pejalan'] = $totalPejalan;

        // Ambil semua kode dari DB
        $kodeList = DB::table('surveyor_site_score_standards')
            ->select('kode', 'tipe')
            ->groupBy('kode', 'tipe')
            ->get()
            ->keyBy('kode');

        $totalPenambah  = 0.0;
        $totalPengurang = 0.0;
        $detailsPenambah = [];
        $detailsPengurang = [];

        foreach ($kodeList as $kode => $rule) {
            $nilai = (float) ($data[$kode] ?? 0);
            
            // Hitung multiplier berdasarkan kategori
            $multiplier = 1.0;
            if ($isCustom) {
                if ($kode === 'traffic_motor') {
                    $multiplier = ($customConfig['weights']['traffic_motor'] ?? 30) / 30;
                } elseif ($kode === 'traffic_pejalan') {
                    $multiplier = ($customConfig['weights']['traffic_pejalan'] ?? 20) / 20;
                } elseif (in_array($kode, ['rumah_q1', 'rumah_q2', 'rumah_q3', 'rumah_q4'])) {
                    $multiplier = ($customConfig['weights']['rumah'] ?? 35) / 35;
                } elseif (in_array($kode, ['sekolah', 'market', 'perkantoran', 'kesehatan', 'kampus', 'pabrik'])) {
                    $multiplier = ($customConfig['weights']['fasum'] ?? 15) / 15;
                } elseif (in_array($kode, ['kompetitor_geprek', 'kompetitor_lokal', 'jarak_kompetitor'])) {
                    $multiplier = ($customConfig['weights']['pengurang'] ?? 5) / 5;
                }
            }

            $score = $this->hitungScore($kode, $nilai) * $multiplier;

            if (strtoupper($rule->tipe) === 'MINUS') {
                $totalPengurang += $score;
                if ($score > 0) $detailsPengurang[$kode] = $score;
            } else {
                $totalPenambah += $score;
                if ($score > 0) $detailsPenambah[$kode] = $score;
            }
        }
        
        // Harga kompetitor tidak lagi memberikan bonus di default, tapi jika di Custom diset:
        $hargaKomp = (float) ($data['harga_kompetitor'] ?? 0);
        $bonusHarga = 0.0;
        if ($isCustom && isset($customConfig['extras']) && $hargaKomp > 0) {
            $hargaAcuan = (float) ($customConfig['extras']['harga_acuan'] ?? 10000);
            $bonusPersen = (float) ($customConfig['extras']['bonus_persen'] ?? 10) / 100;
            if ($hargaAcuan > 0) {
                if ($hargaKomp > $hargaAcuan) {
                    $bonusHarga = $bonusPersen;
                } elseif ($hargaKomp < $hargaAcuan) {
                    $bonusHarga = -$bonusPersen;
                }
            }
        }
        
        if ($bonusHarga > 0) {
            $totalPenambah += $bonusHarga;
            $detailsPenambah['bonus_harga'] = $bonusHarga;
        } else if ($bonusHarga < 0) {
            $totalPengurang += abs($bonusHarga);
            $detailsPengurang['pinalti_harga'] = abs($bonusHarga);
        }

        // --- SMART CANNIBALIZATION (Internal Network) ---
        $lat = (float) ($data['latitude'] ?? 0);
        $lng = (float) ($data['longitude'] ?? 0);
        
        if ($lat != 0 && $lng != 0) {
            $outlets = DB::table('master_outlets')->whereNotNull('latitude')->whereNotNull('longitude')->get();
            $closestDist = 9999999;
            
            $earthRadius = 6371000; // meters
            $latFrom = deg2rad($lat);
            $lonFrom = deg2rad($lng);
            
            foreach($outlets as $out) {
                $latTo = deg2rad((float)$out->latitude);
                $lonTo = deg2rad((float)$out->longitude);
                $latDelta = $latTo - $latFrom;
                $lonDelta = $lonTo - $lonFrom;
                $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
                $dist = $angle * $earthRadius;
                if ($dist < $closestDist) {
                    $closestDist = $dist;
                }
            }

            if ($closestDist <= 3000) {
                // Base Penalty
                $penalty = ($closestDist <= 1500) ? 0.25 : 0.10; // 25% or 10%
                
                // Density Mitigation
                $rumahTotal = (int)($data['rumah_q1'] ?? 0) + (int)($data['rumah_q2'] ?? 0);
                if ($rumahTotal > 2000) {
                    $penalty = $penalty / 2; // 50% mitigation for high density
                }
                
                $totalPengurang += $penalty;
                $detailsPengurang['pinalti_kanibal_internal'] = $penalty;
            }
        }
        // ------------------------------------------------

        // --- RISK DETECTOR: Dominasi Kuadran (One-Sided Market Penalty) ---
        $q1 = (int)($data['rumah_q1'] ?? 0);
        $q2 = (int)($data['rumah_q2'] ?? 0);
        $q3 = (int)($data['rumah_q3'] ?? 0);
        $q4 = (int)($data['rumah_q4'] ?? 0);
        $totalRumahRisk = $q1 + $q2 + $q3 + $q4;
        
        if ($totalRumahRisk > 0) {
            $maxQ = max($q1, $q2, $q3, $q4);
            $dominanceRatio = $maxQ / $totalRumahRisk;
            
            if ($dominanceRatio >= 0.70) {
                // Jika satu kuadran menguasai >= 70% total populasi rumah tangga, berikan pinalti -5%
                // karena lokasi ini sangat rapuh (hanya mengandalkan satu arah)
                $penaltyRisk = 0.05; // 5% pengurang
                $totalPengurang += $penaltyRisk;
                $detailsPengurang['pinalti_resiko_distribusi'] = $penaltyRisk;
            }
        }

        $finalScore   = min(1.0, max(0, $totalPenambah - $totalPengurang));
        $finalPercent = round($finalScore * 100, 2);

        // Threshold berdasarkan tipe outlet (LDP/BDP)
        $threshold   = self::THRESHOLD[$tipeOutlet] ?? self::THRESHOLD['LDP'];
        $rekomendasi = 'REJECTED';
        if ($finalScore >= $threshold['approved']) {
            $rekomendasi = 'APPROVED';
        } elseif ($finalScore >= $threshold['consideration']) {
            $rekomendasi = 'CONSIDERATION';
        }

        // Potensi omset
        $averageCheck = (float) ($data['average_check'] ?? 21000);
        $omset        = $this->hitungPotensiOmset($data, $totalMotor, $totalPejalan, $averageCheck, $moe, $tipeOutlet, $customConfig);

        return [
            'total_motor'             => $totalMotor,
            'total_pejalan'           => $totalPejalan,
            'total_penambah'          => $totalPenambah,
            'total_pengurang'         => $totalPengurang,
            'final_score'             => $finalScore,
            'final_percent'           => $finalPercent,
            'details_penambah'        => $detailsPenambah,
            'details_pengurang'       => $detailsPengurang,
            'rekomendasi'             => $rekomendasi,
            'tipe_outlet'             => $tipeOutlet,
            'average_check'           => $averageCheck,
            'potensi_cu_motor'        => $omset['cu_motor'],
            'potensi_cu_pejalan'      => $omset['cu_pejalan'],
            'potensi_cu_rumah'        => $omset['cu_rumah'],
            'total_potensi_cu'        => $omset['total_cu'],
            'subtotal_perhari'        => $omset['subtotal_perhari'],
            'moe_percent'             => $omset['moe_percent'],
            'grand_total_perhari'     => $omset['grand_total_perhari'],
            'label_outlet'            => $omset['label_outlet'],
            'potensi_omset_perminggu' => $omset['omset_perminggu'],
            'potensi_omset_perhari'   => $omset['omset_perhari'],
        ];
    }

    private function synthesizeTrafficCurve(int $pagi, int $siang, int $sore): array
    {
        $baseCurve = [
            6 => 0.2, 7 => 0.4, 8 => 0.5, 9 => 0.4, 10 => 0.5,     // Pagi (Sum: 2.0)
            11 => 0.7, 12 => 1.0, 13 => 0.8, 14 => 0.6, 15 => 0.5, // Siang (Sum: 3.6)
            16 => 0.6, 17 => 0.8, 18 => 1.0, 19 => 0.9, 20 => 0.8, 21 => 0.6, 22 => 0.4, 23 => 0.2 // Sore (Sum: 5.3)
        ];

        $sumMorning = 2.0;
        $sumNoon    = 3.6;
        $sumEvening = 5.3;

        $hourlyData = [];
        foreach ($baseCurve as $hour => $ratio) {
            if ($hour <= 10) {
                $count = $pagi * ($ratio / $sumMorning);
            } elseif ($hour <= 15) {
                $count = $siang * ($ratio / $sumNoon);
            } else {
                $count = $sore * ($ratio / $sumEvening);
            }
            $hourlyData[$hour] = (int) round($count);
        }
        return $hourlyData;
    }

    private function hitungPotensiOmset(array $data, int $totalMotor, int $totalPejalan, float $averageCheck, float $moe, string $tipeOutlet, array $customConfig = []): array
    {
        $isCustom = ($data['formula_type'] ?? 'DEFAULT') === 'CUSTOM' && !empty($customConfig);
        $rMotor = $isCustom ? (float)($customConfig['ratios']['motor'] ?? self::RASIO_MOTOR) : self::RASIO_MOTOR;
        $rPejalan = $isCustom ? (float)($customConfig['ratios']['pejalan'] ?? self::RASIO_PEJALAN) : self::RASIO_PEJALAN;
        $rQ1 = $isCustom ? (float)($customConfig['ratios']['q1'] ?? self::RASIO_RUMAH_Q1) : self::RASIO_RUMAH_Q1;
        $rQ2 = $isCustom ? (float)($customConfig['ratios']['q2'] ?? self::RASIO_RUMAH_Q2) : self::RASIO_RUMAH_Q2;
        $rQ3 = $isCustom ? (float)($customConfig['ratios']['q3'] ?? self::RASIO_RUMAH_Q3) : self::RASIO_RUMAH_Q3;
        $rQ4 = $isCustom ? (float)($customConfig['ratios']['q4'] ?? self::RASIO_RUMAH_Q4) : self::RASIO_RUMAH_Q4;

        // 1. Traffic (Synthesized to 18-hour daily full curve)
        $motorWdCurve = $this->synthesizeTrafficCurve((int)($data['motor_weekday_pagi']??0), (int)($data['motor_weekday_siang']??0), (int)($data['motor_weekday_sore']??0));
        $motorWeCurve = $this->synthesizeTrafficCurve((int)($data['motor_weekend_pagi']??0), (int)($data['motor_weekend_siang']??0), (int)($data['motor_weekend_sore']??0));
        $pejalanWdCurve = $this->synthesizeTrafficCurve((int)($data['pejalan_weekday_pagi']??0), (int)($data['pejalan_weekday_siang']??0), (int)($data['pejalan_weekday_sore']??0));
        $pejalanWeCurve = $this->synthesizeTrafficCurve((int)($data['pejalan_weekend_pagi']??0), (int)($data['pejalan_weekend_siang']??0), (int)($data['pejalan_weekend_sore']??0));

        // Hitung total Mingguan secara eksak seperti di Frontend
        $mWd = (int)($data['motor_weekday_pagi']??0) + (int)($data['motor_weekday_siang']??0) + (int)($data['motor_weekday_sore']??0);
        $mWe = (int)($data['motor_weekend_pagi']??0) + (int)($data['motor_weekend_siang']??0) + (int)($data['motor_weekend_sore']??0);
        $totalMotorWeekly = ($mWd * 5) + ($mWe * 2);
        
        $pWd = (int)($data['pejalan_weekday_pagi']??0) + (int)($data['pejalan_weekday_siang']??0) + (int)($data['pejalan_weekday_sore']??0);
        $pWe = (int)($data['pejalan_weekend_pagi']??0) + (int)($data['pejalan_weekend_siang']??0) + (int)($data['pejalan_weekend_sore']??0);
        $totalPejalanWeekly = ($pWd * 5) + ($pWe * 2);

        // --- ENHANCEMENT 1: Diminishing Returns (Batas Jenuh Jalan Raya/Arteri) ---
        // Jika traffic motor lebih dari 20.000 per hari, rasio tangkapannya menurun eksponensial.
        $totalMotorDaily = $totalMotorWeekly / 7;
        $effectiveMotorDaily = $totalMotorDaily;
        if ($totalMotorDaily > 20000) {
            // Segala traffic di atas 20k hanya dihitung 20% efektivitasnya (karena melaju cepat/tidak sempat mampir)
            $effectiveMotorDaily = 20000 + (($totalMotorDaily - 20000) * 0.20);
        }

        // --- ENHANCEMENT 2: Traffic Friction Multiplier (Efek Hambatan Jalan 1 Arah) ---
        $trafficFriction = 1.0;
        if (($data['jenis_jalan'] ?? '') === '1 Arah' && ((int)($data['u_turn_lampu_merah'] ?? 0)) === 0) {
            $trafficFriction = 0.7; // Potong 30% karena sulit putar balik
        }

        $omsetMotorPerhari   = ($effectiveMotorDaily * $rMotor * $trafficFriction * $averageCheck);
        $omsetPejalanPerhari = ($totalPejalanWeekly * $rPejalan * $averageCheck) / 7;

        // 2. Rumah Penduduk (Diperbaiki menjadi harian, perminggu / 7, bukan dikali 4)
        $omsetQ1Weekly = ($data['rumah_q1'] ?? 0) * $rQ1 * $averageCheck;
        $omsetQ2Weekly = ($data['rumah_q2'] ?? 0) * $rQ2 * $averageCheck;
        $omsetQ3Weekly = ($data['rumah_q3'] ?? 0) * $rQ3 * $averageCheck;
        $omsetQ4Weekly = ($data['rumah_q4'] ?? 0) * $rQ4 * $averageCheck;
        
        $omsetQ1 = $omsetQ1Weekly / 7;
        $omsetQ2 = $omsetQ2Weekly / 7;
        $omsetQ3 = $omsetQ3Weekly / 7;
        $omsetQ4 = $omsetQ4Weekly / 7;

        $subTotalOmset   = $omsetMotorPerhari + $omsetPejalanPerhari + $omsetQ1 + $omsetQ2 + $omsetQ3 + $omsetQ4;
        
        // --- ENHANCEMENT 3: Visibility Gravity Bonus ---
        $visibilityMultiplier = 1.0;
        if (((int)($data['posisi_hook'] ?? 0)) === 1 && ((int)($data['terlihat_jalan_utama'] ?? 0)) === 1) {
            $visibilityMultiplier = 1.15; // +15% bonus omset karena impulse buying di hook utama
        }
        
        $subTotalOmset = $subTotalOmset * $visibilityMultiplier;
        $grandTotalOmset = $subTotalOmset * (1 - $moe);
        
        $label = 'Rejected';
        if ($tipeOutlet === 'LDP') {
            if ($grandTotalOmset > 3500000) $label = 'Plus';
            elseif ($grandTotalOmset > 2000000) $label = 'Flagship';
            elseif ($grandTotalOmset > 1400000) $label = 'Express';
            elseif ($grandTotalOmset >= 750000) $label = 'Mini';
        } else {
            if ($grandTotalOmset > 3500000) $label = 'Flagship';
            elseif ($grandTotalOmset > 2000000) $label = 'Express';
            elseif ($grandTotalOmset >= 1400000) $label = 'Mini';
        }
        
        // Simulasikan CU untuk keperluan response backend
        $cuMotor   = round($omsetMotorPerhari / $averageCheck, 2);
        $cuPejalan = round($omsetPejalanPerhari / $averageCheck, 2);
        $cuRumah   = round(($omsetQ1 + $omsetQ2 + $omsetQ3 + $omsetQ4) / $averageCheck, 2);
        $totalCu   = round($cuMotor + $cuPejalan + $cuRumah, 2);

        return [
            'cu_motor'        => $cuMotor,
            'cu_pejalan'      => $cuPejalan,
            'cu_rumah'        => $cuRumah,
            'total_cu'        => $totalCu,
            'subtotal_perhari' => round($subTotalOmset, 2),
            'moe_percent'     => round($moe * 100, 2),
            'grand_total_perhari' => round($grandTotalOmset, 2),
            'label_outlet'    => $label,
            'omset_perminggu' => round($grandTotalOmset * 7, 2),
            'omset_perhari'   => round($grandTotalOmset, 2),
            'curve_motor_wd'  => $motorWdCurve,
            'curve_motor_we'  => $motorWeCurve,
            'curve_pejalan_wd' => $pejalanWdCurve,
            'curve_pejalan_we' => $pejalanWeCurve,
        ];
    }

    public function hitungScore(string $kode, $value): float
    {
        $value = (int) ($value ?? 0);
        if ($value <= 0) return 0.0;

        $rule = DB::table('surveyor_site_score_standards')
            ->where('kode', $kode)
            ->where('min_value', '<=', $value)
            ->where('max_value', '>=', $value)
            ->orderByDesc('grade')
            ->first();

        if (!$rule) return 0.0;
        return (float) $rule->bobot; // Bobot sudah = nilai kontribusi langsung
    }

    public function normalizePayload(array $payload): array
    {
        $aliases = [
            'motor_pagi'    => 'motor_weekday_pagi',
            'motor_siang'   => 'motor_weekday_siang',
            'motor_sore'    => 'motor_weekday_sore',
            'pejalan_pagi'  => 'pejalan_weekday_pagi',
            'pejalan_siang' => 'pejalan_weekday_siang',
            'pejalan_sore'  => 'pejalan_weekday_sore',
            'q1'            => 'rumah_q1',
            'q2'            => 'rumah_q2',
            'q3'            => 'rumah_q3',
            'q4'            => 'rumah_q4',
            'kompetitor'    => 'kompetitor_geprek',
            'kompetitor_fc' => 'kompetitor_geprek',
        ];

        $normalized = [];
        foreach ($payload as $key => $value) {
            $normalized[$aliases[$key] ?? $key] = $value;
        }
        return $normalized;
    }

    public function sumMotor(array $data): int
    {
        return
            (int)($data['motor_weekday_pagi']  ?? 0) + (int)($data['motor_weekday_siang'] ?? 0) +
            (int)($data['motor_weekday_sore']  ?? 0) + (int)($data['motor_weekend_pagi']  ?? 0) +
            (int)($data['motor_weekend_siang'] ?? 0) + (int)($data['motor_weekend_sore']  ?? 0);
    }

    public function sumPejalan(array $data): int
    {
        return
            (int)($data['pejalan_weekday_pagi']  ?? 0) + (int)($data['pejalan_weekday_siang'] ?? 0) +
            (int)($data['pejalan_weekday_sore']  ?? 0) + (int)($data['pejalan_weekend_pagi']  ?? 0) +
            (int)($data['pejalan_weekend_siang'] ?? 0) + (int)($data['pejalan_weekend_sore']  ?? 0);
    }
}