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
    private const RASIO_MOTOR    = 0.0075;
    private const RASIO_PEJALAN  = 0.0050;
    private const RASIO_RUMAH_Q1 = 0.0125;
    private const RASIO_RUMAH_Q2 = 0.0075;
    private const RASIO_RUMAH_Q3 = 0.0050;
    private const RASIO_RUMAH_Q4 = 0.0025;
    private const MOE_DEFAULT    = 0.20;

    // Threshold sesuai spreadsheet
    private const THRESHOLD = [
        'LDP' => ['approved' => 0.60, 'consideration' => 0.50],
        'BDP' => ['approved' => 1.00, 'consideration' => 0.60],
    ];

    public function calculate(array $data, float $moe = self::MOE_DEFAULT, string $tipeOutlet = 'LDP'): array
    {
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

        foreach ($kodeList as $kode => $rule) {
            $nilai = (float) ($data[$kode] ?? 0);
            $score = $this->hitungScore($kode, $nilai);

            if (strtoupper($rule->tipe) === 'MINUS') {
                $totalPengurang += $score;
            } else {
                $totalPenambah += $score;
            }
        }
        
        // Harga kompetitor tidak lagi memberikan bonus (sesuai SOP Spreadsheet)
        $hargaKompetitor = (float) ($data['harga_kompetitor'] ?? 0);

        $finalScore   = max(0, $totalPenambah - $totalPengurang);
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
        $omset        = $this->hitungPotensiOmset($data, $totalMotor, $totalPejalan, $averageCheck, $moe, $tipeOutlet);

        return [
            'total_motor'             => $totalMotor,
            'total_pejalan'           => $totalPejalan,
            'total_penambah'          => $totalPenambah,
            'total_pengurang'         => $totalPengurang,
            'final_score'             => $finalScore,
            'final_percent'           => $finalPercent,
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

    private function hitungPotensiOmset(array $data, int $totalMotor, int $totalPejalan, float $averageCheck, float $moe, string $tipeOutlet): array
    {
        // 1. Traffic (Synthesized to 18-hour daily full curve)
        $motorWdCurve = $this->synthesizeTrafficCurve((int)($data['motor_weekday_pagi']??0), (int)($data['motor_weekday_siang']??0), (int)($data['motor_weekday_sore']??0));
        $motorWeCurve = $this->synthesizeTrafficCurve((int)($data['motor_weekend_pagi']??0), (int)($data['motor_weekend_siang']??0), (int)($data['motor_weekend_sore']??0));
        $pejalanWdCurve = $this->synthesizeTrafficCurve((int)($data['pejalan_weekday_pagi']??0), (int)($data['pejalan_weekday_siang']??0), (int)($data['pejalan_weekday_sore']??0));
        $pejalanWeCurve = $this->synthesizeTrafficCurve((int)($data['pejalan_weekend_pagi']??0), (int)($data['pejalan_weekend_siang']??0), (int)($data['pejalan_weekend_sore']??0));

        $mWd = array_sum($motorWdCurve);
        $mWe = array_sum($motorWeCurve);
        $totalMotorWeekly = ($mWd * 5) + ($mWe * 2);
        
        $pWd = array_sum($pejalanWdCurve);
        $pWe = array_sum($pejalanWeCurve);
        $totalPejalanWeekly = ($pWd * 5) + ($pWe * 2);

        $omsetMotorPerhari   = ($totalMotorWeekly * self::RASIO_MOTOR * $averageCheck) / 7;
        $omsetPejalanPerhari = ($totalPejalanWeekly * self::RASIO_PEJALAN * $averageCheck) / 7;

        // 2. Rumah Penduduk (Diperbaiki menjadi harian, perminggu / 7, bukan dikali 4)
        $omsetQ1Weekly = ($data['rumah_q1'] ?? 0) * self::RASIO_RUMAH_Q1 * $averageCheck;
        $omsetQ2Weekly = ($data['rumah_q2'] ?? 0) * self::RASIO_RUMAH_Q2 * $averageCheck;
        $omsetQ3Weekly = ($data['rumah_q3'] ?? 0) * self::RASIO_RUMAH_Q3 * $averageCheck;
        $omsetQ4Weekly = ($data['rumah_q4'] ?? 0) * self::RASIO_RUMAH_Q4 * $averageCheck;
        
        $omsetQ1 = $omsetQ1Weekly / 7;
        $omsetQ2 = $omsetQ2Weekly / 7;
        $omsetQ3 = $omsetQ3Weekly / 7;
        $omsetQ4 = $omsetQ4Weekly / 7;

        $subTotalOmset   = $omsetMotorPerhari + $omsetPejalanPerhari + $omsetQ1 + $omsetQ2 + $omsetQ3 + $omsetQ4;
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