<?php

namespace App\Jobs;

use App\Services\EsbSalesService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncSalesAllOutletBranchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;
    public int $tries = 2;

    public function backoff(): array
    {
        return [10, 30];
    }

    public function __construct(
        public string $syncKey,
        public string $salesDate,
        public int $credentialId,
        public string $credentialCode,
        public string $credentialName,
        public string $branchCode,
        public int $outletId,
        public string $outletName,
        public int $totalJobs = 0
    ) {
        $this->onConnection('redis');
        $this->onQueue('esb-sales');
    }

    public function handle(EsbSalesService $service): void
    {
        $cacheKey = "sales_sync_all:{$this->syncKey}";
        $lockKey = "sales_sync_all_state_lock:{$this->syncKey}";

        try {
            /*
             * Penting:
             * - branchCode berasal dari tbl_api_credential_branches, jadi valid untuk token.
             * - forcedOutletId dipakai supaya hasil API masuk ke outlet yang tepat.
             */
            $result = $service->syncSalesByBranchAndDate(
                credentialId: $this->credentialId,
                branchCode: $this->branchCode,
                startDate: $this->salesDate,
                endDate: $this->salesDate,
                forcedOutletId: $this->outletId
            );

            $this->updateProgress($cacheKey, $lockKey, $result, null);
        } catch (\Throwable $e) {
            Log::error('SYNC SALES ALL OUTLET BRANCH FAILED', [
                'sync_key' => $this->syncKey,
                'sales_date' => $this->salesDate,
                'credential_id' => $this->credentialId,
                'credential_code' => $this->credentialCode,
                'outlet_id' => $this->outletId,
                'outlet_name' => $this->outletName,
                'branch_code' => $this->branchCode,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);

            if ($this->attempts() < $this->tries) {
                throw $e;
            }

            $this->updateProgress($cacheKey, $lockKey, [
                'api_rows' => 0,
                'built_rows' => 0,
                'inserted_rows' => 0,
                'transaction_rows' => 0,
                'menu_rows' => 0,
            ], $e->getMessage());
        }
    }

    protected function updateProgress(string $cacheKey, string $lockKey, array $result, ?string $error): void
    {
        Cache::store('redis')->lock($lockKey, 10)->block(10, function () use ($cacheKey, $result, $error) {
            $state = Cache::store('redis')->get($cacheKey, []);

            $branchKey = $this->credentialCode . '|' . $this->branchCode . '|' . $this->outletId;

            $perBranch = $state['per_branch'] ?? [];
            $perBranch[$branchKey] = [
                'status' => $error === null ? 'success' : 'failed',
                'credential_id' => $this->credentialId,
                'credential_code' => $this->credentialCode,
                'credential_name' => $this->credentialName,
                'branch_code' => $this->branchCode,
                'outlet_id' => $this->outletId,
                'outlet_name' => $this->outletName,
                'api_rows' => (int) ($result['api_rows'] ?? 0),
                'transaction_rows' => (int) ($result['transaction_rows'] ?? 0),
                'menu_rows' => (int) ($result['menu_rows'] ?? 0),
                'built_rows' => (int) ($result['built_rows'] ?? 0),
                'inserted_rows' => (int) ($result['inserted_rows'] ?? 0),
                'message' => $error,
                'updated_at' => now()->toDateTimeString(),
            ];

            $state['per_branch'] = $perBranch;

            $state['processed_pages'] = (int) ($state['processed_pages'] ?? 0) + 1;
            $state['processed_branches'] = (int) ($state['processed_branches'] ?? 0) + 1;

            if ($error === null) {
                $state['success_pages'] = (int) ($state['success_pages'] ?? 0) + 1;
                $state['success_branches'] = (int) ($state['success_branches'] ?? 0) + 1;
            } else {
                $state['failed_pages'] = (int) ($state['failed_pages'] ?? 0) + 1;
                $state['failed_branches'] = (int) ($state['failed_branches'] ?? 0) + 1;
            }

            $state['total_api_rows'] = (int) ($state['total_api_rows'] ?? 0) + (int) ($result['api_rows'] ?? 0);
            $state['total_built_rows'] = (int) ($state['total_built_rows'] ?? 0) + (int) ($result['built_rows'] ?? 0);
            $state['total_inserted_rows'] = (int) ($state['total_inserted_rows'] ?? 0) + (int) ($result['inserted_rows'] ?? 0);

            $logs = $state['logs'] ?? [];
            $logs[] = [
                'credential_code' => $this->credentialCode,
                'branch_code' => $this->branchCode,
                'outlet_id' => $this->outletId,
                'outlet_name' => $this->outletName,
                'status' => $error === null ? 'success' : 'failed',
                'api_rows' => (int) ($result['api_rows'] ?? 0),
                'built_rows' => (int) ($result['built_rows'] ?? 0),
                'inserted_rows' => (int) ($result['inserted_rows'] ?? 0),
                'message' => $error,
                'updated_at' => now()->toDateTimeString(),
            ];
            $state['logs'] = array_slice($logs, -50);

            $totalJobs = (int) ($state['total_pages'] ?? $this->totalJobs);
            $processedJobs = (int) ($state['processed_pages'] ?? 0);

            $state['progress'] = $totalJobs > 0
                ? min(100, (int) floor(($processedJobs / $totalJobs) * 100))
                : 100;

            $state['message'] = 'Sync sales per outlet/branch valid sedang berjalan.';
            $state['updated_at'] = now()->toDateTimeString();

            if (
                $totalJobs > 0
                && $processedJobs >= $totalJobs
                && ! ($state['finalized'] ?? false)
            ) {
                $state['status'] = 'done';
                $state['progress'] = 100;
                $state['message'] = 'Sync sales per outlet/branch valid selesai. Summary laporan bulanan sedang dijalankan.';
                $state['finished_at'] = now()->toDateTimeString();
                $state['finalized'] = true;

                SyncSalesSummaryToLaporanBulananJob::dispatch($this->salesDate)
                    ->onConnection('redis')
                    ->onQueue('esb-sales');
            }

            Cache::store('redis')->put($cacheKey, $state, now()->addHours(12));
        });
    }
}
