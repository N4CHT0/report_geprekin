<?php

namespace App\Jobs;

use App\Http\Controllers\QCRController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GenerateQcrViewDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;
    public int $tries = 1;

    public function __construct(public int $jobId)
    {
    }

    public function handle(): void
    {
        $job = DB::table('qcr_export_jobs')
            ->where('id', $this->jobId)
            ->first();

        if (!$job) {
            return;
        }

        DB::table('qcr_export_jobs')
            ->where('id', $this->jobId)
            ->update([
                'status' => 'running',
                'started_at' => now(),
                'processed_outlet' => 0,
                'total_outlet' => 1,
                'updated_at' => now(),
            ]);

        try {

            $request = new Request([
                'outlet_id' => (string) $job->outlet_id,
                'start_date' => (string) $job->start_date,
                'end_date' => (string) $job->end_date,
            ]);

            $data = app(QCRController::class)
                ->buildQcrData($request);

            $path = 'qcr_view_cache/qcr_view_' .
                $this->jobId .
                '.cache';

            Storage::put(
                $path,
                serialize($data)
            );

            DB::table('qcr_export_jobs')
                ->where('id', $this->jobId)
                ->update([
                    'status' => 'done',
                    'processed_outlet' => 1,
                    'total_outlet' => 1,
                    'file_path' => $path,
                    'finished_at' => now(),
                    'updated_at' => now(),
                ]);

        } catch (\Throwable $e) {

            DB::table('qcr_export_jobs')
                ->where('id', $this->jobId)
                ->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'finished_at' => now(),
                    'updated_at' => now(),
                ]);

            throw $e;
        }
    }
}