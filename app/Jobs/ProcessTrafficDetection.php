<?php

namespace App\Jobs;

use App\Services\Surveyor\SiteScoreCalculatorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ProcessTrafficDetection (REFACTOR - SOLUSI GAP #2)
 *
 * Sebelumnya: calculateScoreAndSales() hardcode duplikat di job ini.
 * Sekarang didelegasikan ke SiteScoreCalculatorService.
 *
 * Cara inject service ke Job: resolve dari container di handle().
 */
class ProcessTrafficDetection implements ShouldQueue
{
    use Queueable;

    public int $timeout = 600;
    public int $tries   = 1;

    public function __construct(
        protected int $surveyId
    ) {}

    public function handle(SiteScoreCalculatorService $calculator): void
    {
        $survey = DB::table('surveyor_site_scores')->where('id', $this->surveyId)->first();

        // Fallback: cek juga di site_surveys (tabel lama)
        if (!$survey) {
            $survey = DB::table('site_surveys')->where('id', $this->surveyId)->first();
            $table  = 'site_surveys';
        } else {
            $table = 'surveyor_site_scores';
        }

        if (!$survey || empty($survey->traffic_video_path)) {
            return;
        }

        if (($survey->traffic_detection_status ?? null) === 'Cancelled') {
            return;
        }

        DB::table($table)->where('id', $this->surveyId)->update([
            'traffic_detection_status'   => 'Processing',
            'traffic_detection_progress' => 10,
            'traffic_detection_error'    => null,
            'updated_at'                 => now(),
        ]);

        try {
            $videoPath    = storage_path('app/public/' . $survey->traffic_video_path);
            $pythonScript = base_path('python-ai/detect_light.py');

            $command = sprintf(
                '%s %s --video %s --sample-rate %d --resize-width %d --max-seconds %d',
                escapeshellcmd(env('PYTHON_BINARY', 'python3')),
                escapeshellarg($pythonScript),
                escapeshellarg($videoPath),
                (int) env('TRAFFIC_AI_SAMPLE_RATE', 10),
                (int) env('TRAFFIC_AI_RESIZE_WIDTH', 640),
                (int) env('TRAFFIC_AI_MAX_SECONDS', 30)
            );

            DB::table($table)->where('id', $this->surveyId)->update([
                'traffic_detection_progress' => 30,
                'updated_at'                 => now(),
            ]);

            $output   = [];
            $exitCode = 0;
            exec($command . ' 2>&1', $output, $exitCode);

            $rawOutput = implode("\n", $output);
            Log::info('Traffic Detection Output: ' . $rawOutput);

            // Cek cancel di tengah proses
            $freshSurvey = DB::table($table)->where('id', $this->surveyId)->first();
            if ($freshSurvey && ($freshSurvey->traffic_detection_status ?? null) === 'Cancelled') {
                return;
            }

            if ($exitCode !== 0) {
                throw new \Exception($rawOutput);
            }

            // Parse JSON dari output Python
            $json = null;
            foreach (array_reverse($output) as $line) {
                $line = trim($line);
                if (str_starts_with($line, '{') && str_ends_with($line, '}')) {
                    $json = json_decode($line, true);
                    break;
                }
            }

            if (!$json || !is_array($json)) {
                throw new \Exception('JSON output tidak ditemukan. Output: ' . $rawOutput);
            }

            DB::table($table)->where('id', $this->surveyId)->update([
                'traffic_detection_progress' => 80,
                'updated_at'                 => now(),
            ]);

            // Gabungkan data survey + hasil AI, lalu kalkulasi ulang via service
            $detectedMotor     = (int) ($json['motorcycle'] ?? 0);
            $detectedCar       = (int) ($json['car']        ?? 0);
            $detectedPedestrian = (int) ($json['person']    ?? 0);

            // Untuk surveyor_site_scores, field motor berbeda nama dengan site_surveys
            // Normalkan dulu ke format yang dikenali service
            $dataForCalc = $this->normalizeForService($survey, $table, [
                'detected_motor'     => $detectedMotor,
                'detected_car'       => $detectedCar,
                'detected_pedestrian' => $detectedPedestrian,
            ]);

            $averageCheck = (float) ($survey->average_check ?? 21000);
            $calc = $calculator->calculate(
                array_merge($dataForCalc, ['average_check' => $averageCheck])
            );

            DB::table($table)->where('id', $this->surveyId)->update(array_merge([
                'detected_motor_traffic'     => $detectedMotor,
                'detected_car_traffic'       => $detectedCar,
                'detected_pedestrian_traffic' => $detectedPedestrian,
                'traffic_detection_status'   => 'Done',
                'traffic_detection_progress' => 100,
                'traffic_detection_error'    => null,
                'updated_at'                 => now(),
            ], $this->mapCalcToTable($calc, $table)));

        } catch (\Throwable $e) {
            DB::table($table)->where('id', $this->surveyId)->update([
                'traffic_detection_status'   => 'Failed',
                'traffic_detection_progress' => 0,
                'traffic_detection_error'    => mb_substr($e->getMessage(), 0, 5000),
                'updated_at'                 => now(),
            ]);

            Log::error('Traffic Detection Failed: ' . $e->getMessage());
        }
    }

    /**
     * Normalisasi data dari survey (apapun tabelnya) ke format
     * yang dikenali SiteScoreCalculatorService.
     */
    private function normalizeForService(object $survey, string $table, array $detected): array
    {
        if ($table === 'surveyor_site_scores') {
        return [
            'motor_weekday_pagi'    => $survey->motor_weekday_pagi + $detected['detected_motor'] + $detected['detected_car'],
            'motor_weekday_siang'   => $survey->motor_weekday_siang,
            'motor_weekday_sore'    => $survey->motor_weekday_sore,
            'motor_weekend_pagi'    => $survey->motor_weekend_pagi,
            'motor_weekend_siang'   => $survey->motor_weekend_siang,
            'motor_weekend_sore'    => $survey->motor_weekend_sore,
            'pejalan_weekday_pagi'  => $survey->pejalan_weekday_pagi + $detected['detected_pedestrian'],
            'pejalan_weekday_siang' => $survey->pejalan_weekday_siang,
            'pejalan_weekday_sore'  => $survey->pejalan_weekday_sore,
            'pejalan_weekend_pagi'  => $survey->pejalan_weekend_pagi,
            'pejalan_weekend_siang' => $survey->pejalan_weekend_siang,
            'pejalan_weekend_sore'  => $survey->pejalan_weekend_sore,
            'rumah_q1'    => $survey->rumah_q1,
            'rumah_q2'    => $survey->rumah_q2,
            'rumah_q3'    => $survey->rumah_q3,
            'rumah_q4'    => $survey->rumah_q4,
            'sekolah'     => $survey->sekolah,
            'market'      => $survey->market,
            'perkantoran' => $survey->perkantoran,
            'kesehatan'   => $survey->kesehatan,
            'kompetitor_geprek' => $survey->kompetitor_geprek,
            'kompetitor_lokal'  => $survey->kompetitor_lokal,
        ];
    }

        // Format lama (site_surveys): nama kolom berbeda
        return [
            'motor_weekday_pagi'    => ($survey->motor_weekday_morning ?? 0) + $detected['detected_motor'] + $detected['detected_car'],
            'motor_weekday_siang'   => $survey->motor_weekday_noon    ?? 0,
            'motor_weekday_sore'    => $survey->motor_weekday_evening  ?? 0,
            'motor_weekend_pagi'    => $survey->motor_weekend_morning  ?? 0,
            'motor_weekend_siang'   => $survey->motor_weekend_noon     ?? 0,
            'motor_weekend_sore'    => $survey->motor_weekend_evening  ?? 0,
            'pejalan_weekday_pagi'  => ($survey->pedestrian_weekday_morning ?? 0) + $detected['detected_pedestrian'],
            'pejalan_weekday_siang' => $survey->pedestrian_weekday_noon    ?? 0,
            'pejalan_weekday_sore'  => $survey->pedestrian_weekday_evening  ?? 0,
            'pejalan_weekend_pagi'  => $survey->pedestrian_weekend_morning  ?? 0,
            'pejalan_weekend_siang' => $survey->pedestrian_weekend_noon     ?? 0,
            'pejalan_weekend_sore'  => $survey->pedestrian_weekend_evening  ?? 0,
            'rumah_q1'    => $survey->houses_north ?? 0,
            'rumah_q2'    => $survey->houses_south ?? 0,
            'rumah_q3'    => $survey->houses_east  ?? 0,
            'rumah_q4'    => $survey->houses_west  ?? 0,
            'sekolah'     => $survey->public_facilities_15km ?? 0,
            'market'      => 0,
            'perkantoran' => 0,
            'kesehatan'   => 0,
            'kompetitor_geprek' => $survey->competitors ?? 0,
            'kompetitor_lokal'  => 0,
        ];
    }

    /**
     * Map hasil kalkulasi service ke nama kolom tabel yang sesuai.
     */
    private function mapCalcToTable(array $calc, string $table): array
    {
        if ($table === 'surveyor_site_scores') {
            return $calc; // Nama kolom sudah sama
        }

        // site_surveys pakai nama kolom berbeda
        return [
            'site_score'              => $calc['final_percent'],
            'grade'                   => $calc['rekomendasi'] === 'APPROVED'
                ? 'A' : ($calc['rekomendasi'] === 'CONSIDERATION' ? 'B' : 'C'),
            'estimated_daily_sales'   => $calc['potensi_omset_perhari'],
            'estimated_monthly_sales' => $calc['potensi_omset_perhari'] * 30,
        ];
    }
}
