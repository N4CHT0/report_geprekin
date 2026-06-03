<?php

namespace App\Jobs;

use App\Services\EsbBranchService;
use App\Services\EsbOutletSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SyncEsbBranchesAndOutletsAllJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $syncKey;
    public int $timeout = 7200;
    public int $tries = 1;

    public function __construct(string $syncKey)
    {
        $this->syncKey = $syncKey;
        $this->onQueue('default');
    }

    public function handle(
        EsbBranchService $branchService,
        EsbOutletSyncService $outletSyncService
    ): void {
        $credentials = DB::table('tbl_api_credentials')
            ->where('is_active', 1)
            ->orderBy('id')
            ->get();

        $totalCredentials = $credentials->count();
        $processedCredentials = 0;
        $successCredentials = 0;
        $failedCredentials = 0;
        $totalInserted = 0;
        $totalUpdated = 0;
        $totalSkipped = 0;
        $totalFailedRows = 0;
        $logs = [];
        $perCredential = [];

        Cache::put("outlet_sync_multi:{$this->syncKey}", [
            'status' => 'processing',
            'message' => 'Sinkronisasi branch + outlet semua credential sedang berjalan.',
            'total_credentials' => $totalCredentials,
            'processed_credentials' => 0,
            'success_credentials' => 0,
            'failed_credentials' => 0,
            'total_inserted' => 0,
            'total_updated' => 0,
            'total_skipped' => 0,
            'total_failed_rows' => 0,
            'progress' => 0,
            'logs' => [],
            'per_credential' => [],
            'updated_at' => now()->toDateTimeString(),
        ], now()->addHours(6));

        foreach ($credentials as $credential) {
            try {
                // 1. refresh branch dari ESB
                $branchService->syncBranchesByCredential($credential->credential_code);

                // 2. sinkron branch yang sudah masuk tbl_api_credential_branches ke tbl_outlets
                $result = $outletSyncService->syncBranchesToOutletsByCredential($credential, $this->syncKey);

                $processedCredentials++;
                $successCredentials++;
                $totalInserted += (int) $result['inserted'];
                $totalUpdated += (int) $result['updated'];
                $totalSkipped += (int) $result['skipped'];
                $totalFailedRows += (int) $result['failed'];

                $perCredential[$credential->credential_code] = [
                    'status' => 'success',
                    'total' => (int) $result['total'],
                    'processed' => (int) $result['processed'],
                    'inserted' => (int) $result['inserted'],
                    'updated' => (int) $result['updated'],
                    'skipped' => (int) $result['skipped'],
                    'failed' => (int) $result['failed'],
                ];

                $logs[] = [
                    'credential_code' => $credential->credential_code,
                    'status' => 'success',
                    'inserted' => (int) $result['inserted'],
                    'updated' => (int) $result['updated'],
                    'skipped' => (int) $result['skipped'],
                    'failed' => (int) $result['failed'],
                ];
            } catch (\Throwable $e) {
                $processedCredentials++;
                $failedCredentials++;

                $perCredential[$credential->credential_code] = [
                    'status' => 'failed',
                    'message' => $e->getMessage(),
                ];

                $logs[] = [
                    'credential_code' => $credential->credential_code,
                    'status' => 'failed',
                    'inserted' => 0,
                    'updated' => 0,
                    'skipped' => 0,
                    'failed' => 1,
                    'message' => $e->getMessage(),
                ];
            }

            $progress = $totalCredentials > 0
                ? (int) floor(($processedCredentials / $totalCredentials) * 100)
                : 100;

            Cache::put("outlet_sync_multi:{$this->syncKey}", [
                'status' => 'processing',
                'message' => 'Sinkronisasi branch + outlet semua credential sedang berjalan.',
                'total_credentials' => $totalCredentials,
                'processed_credentials' => $processedCredentials,
                'success_credentials' => $successCredentials,
                'failed_credentials' => $failedCredentials,
                'total_inserted' => $totalInserted,
                'total_updated' => $totalUpdated,
                'total_skipped' => $totalSkipped,
                'total_failed_rows' => $totalFailedRows,
                'progress' => $progress,
                'logs' => array_slice($logs, -20),
                'per_credential' => $perCredential,
                'updated_at' => now()->toDateTimeString(),
            ], now()->addHours(6));

            // Optional: kecilkan burst ke ESB
            usleep(200000); // 0.2 detik
        }

        Cache::put("outlet_sync_multi:{$this->syncKey}", [
            'status' => 'done',
            'message' => 'Sinkronisasi branch + outlet semua credential selesai.',
            'total_credentials' => $totalCredentials,
            'processed_credentials' => $processedCredentials,
            'success_credentials' => $successCredentials,
            'failed_credentials' => $failedCredentials,
            'total_inserted' => $totalInserted,
            'total_updated' => $totalUpdated,
            'total_skipped' => $totalSkipped,
            'total_failed_rows' => $totalFailedRows,
            'progress' => 100,
            'logs' => array_slice($logs, -50),
            'per_credential' => $perCredential,
            'updated_at' => now()->toDateTimeString(),
        ], now()->addHours(6));
    }

    public function failed(\Throwable $e): void
    {
        Cache::put("outlet_sync_multi:{$this->syncKey}", [
            'status' => 'failed',
            'message' => $e->getMessage(),
            'progress' => 0,
            'updated_at' => now()->toDateTimeString(),
        ], now()->addHours(6));
    }
}