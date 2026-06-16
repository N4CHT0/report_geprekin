<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\Process\Process;

class ProcessVideoDetectionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;
    public int $tries = 1;

    public function __construct(
        public string $jobId,
        public string $videoPath,
        public string $sourceType,
        public ?string $lokasi = null,
        public ?int $candidateLocationId = null,
    ) {}

    public function handle(): void
    {
        // BUG FIX: Progress tracking - Stage 1: Extracting frames
        $this->putStatus([
            'status' => 'processing',
            'stage' => 'extracting_frames',
            'stage_name' => 'Extracting frames',
            'message' => 'Mengekstrak frame dari video...',
            'progress' => 20,
        ]);

        try {
            $python = base_path('python/detect_video.py');

            // Bypass config cache by reading .env directly
            $envPath = base_path('.env');
            $groqKey = '';
            if (file_exists($envPath)) {
                $envContent = file_get_contents($envPath);
                if (preg_match('/^GROQ_API_KEY=(.*)$/m', $envContent, $matches)) {
                    $groqKey = trim($matches[1]);
                }
            }

            // Set Environment Variables to strictly limit PyTorch & OpenCV CPU Usage!
            // Ini akan memaksa AI hanya menggunakan 1-2 inti prosesor (thread) maksimal
            $envVars = [
                'OMP_NUM_THREADS' => '1',
                'OPENBLAS_NUM_THREADS' => '1',
                'MKL_NUM_THREADS' => '1',
                'VECLIB_MAXIMUM_THREADS' => '1',
                'NUMEXPR_NUM_THREADS' => '1',
            ];

            // Gunakan 'python' untuk Windows, 'python3' untuk Linux/Mac
            $pythonExe = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'python' : 'python3';

            $process = new Process([
                $pythonExe,
                $python,
                '--video', $this->videoPath,
                '--job', $this->jobId,
                '--fps', '3', // Turunkan FPS ke 3 agar tidak membuat CPU server 100% (Overload) tapi tetap akurat
                '--max-duration', '900',
                '--source-type', $this->sourceType,
                '--groq-key', $groqKey,
            ], null, $envVars);

            $process->setTimeout(1800);
            $process->run();

            // BUG FIX: Progress tracking - Stage 2: Detecting objects
            $this->putStatus([
                'status' => 'processing',
                'stage' => 'detecting_objects',
                'stage_name' => 'Detecting objects',
                'message' => 'Mendeteksi dan menghitung objek...',
                'progress' => 60,
            ]);

            $output = trim($process->getOutput());
            $errorOutput = trim($process->getErrorOutput());
            
            // YOLO atau library Python kadang nge-print log/warning ke stdout/stderr.
            // Kita ambil teks hanya dari kurung kurawal pertama { sampai kurung kurawal terakhir }
            $start = strpos($output, '{');
            $end = strrpos($output, '}');
            
            $result = null;
            if ($start !== false && $end !== false) {
                $json = substr($output, $start, $end - $start + 1);
                $result = json_decode($json, true);
            }

            if (!is_array($result)) {
                $fullError = "ERR: " . substr($errorOutput, 0, 150) . " | OUT: " . substr($output, 0, 150);
                $this->putStatus([
                    'status' => 'failed',
                    'message' => 'Python detector gagal (Non-JSON). ' . $fullError,
                    'progress' => 100,
                ]);

                $this->deleteLocalVideo();
                return;
            }

            if (($result['status'] ?? 'done') === 'failed') {
                $this->putStatus([
                    'status' => 'failed',
                    'message' => 'Python error: ' . ($result['error'] ?? 'Unknown error'),
                    'progress' => 100,
                ]);

                $this->deleteLocalVideo();
                return;
            }

            if (!$process->isSuccessful()) {
                $this->putStatus([
                    'status' => 'failed',
                    'message' => 'Python detector gagal (Exit code != 0).',
                    'progress' => 100,
                ]);

                $this->deleteLocalVideo();
                return;
            }

            $result['job_id'] = $this->jobId;
            $result['status'] = 'done';
            $result['message'] = 'Analisis selesai. Pilih simpan hasil atau buang hasil.';
            $result['lokasi'] = $this->lokasi;
            $result['candidate_location_id'] = $this->candidateLocationId;
            $result['source_type'] = $this->sourceType;
            $result['progress'] = 100;
            $result['finished_at'] = now()->toDateTimeString();

            // BUG FIX: Progress tracking - Stage 3: Processing results
            $this->putStatus([
                'status' => 'processing',
                'stage' => 'processing_results',
                'stage_name' => 'Processing results',
                'message' => 'Memproses hasil analisis...',
                'progress' => 90,
            ]);

            // Wait 1 second for user to see processing stage
            sleep(1);

            // BUG FIX: Progress tracking - Stage 4: Completed
            $result['stage'] = 'completed';
            $result['stage_name'] = 'Complete';

            Redis::setex('video_detection:' . $this->jobId, 3600, json_encode($result));

            $this->deleteLocalVideo();
        } catch (\Throwable $e) {
            Log::error('Video detection exception', [
                'job_id' => $this->jobId,
                'error' => $e->getMessage(),
            ]);

            $this->putStatus([
                'status' => 'failed',
                'message' => 'Exception saat analisis video.',
                'error' => $e->getMessage(),
                'progress' => 100,
            ]);

            $this->deleteLocalVideo();
        }
    }

    private function putStatus(array $extra): void
    {
        Redis::setex('video_detection:' . $this->jobId, 3600, json_encode(array_merge([
            'job_id' => $this->jobId,
            'lokasi' => $this->lokasi,
            'candidate_location_id' => $this->candidateLocationId,
            'source_type' => $this->sourceType,
            'updated_at' => now()->toDateTimeString(),
        ], $extra)));
    }

    private function deleteLocalVideo(): void
    {
        if ($this->sourceType === 'upload' && is_file($this->videoPath)) {
            @unlink($this->videoPath);
        }
    }
}
