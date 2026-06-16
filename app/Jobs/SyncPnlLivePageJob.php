<?php

namespace App\Jobs;

use App\Services\EsbLedgerLivePnlService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncPnlLivePageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900;
    public int $tries = 3;

    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function __construct(
        public string $syncKey,
        public string $credentialCode,
        public int $credentialId,
        public string $date,
        public string $branchCode,
        public int $page,
        public int $pageCount,
        public int $limit = 100
    ) {
        $this->onConnection('redis');
        $this->onQueue('esb-pnl');
    }

    public function handle(EsbLedgerLivePnlService $service): void
    {
        $cacheKey = "pnl_live_sync:{$this->syncKey}";
        $lockKey = "pnl_live_sync_lock:{$this->syncKey}";

        try {
            $result = $service->syncLiveLedgerBranchPageToStaging(
                syncKey: $this->syncKey,
                credentialCode: $this->credentialCode,
                credentialId: $this->credentialId,
                branchCode: $this->branchCode,
                date: $this->date,
                page: $this->page,
                limit: $this->limit
            );

            $this->updateState($cacheKey, $lockKey, $result, null);
        } catch (Throwable $e) {
            Log::error('PNL LIVE PAGE FAILED', [
                'sync_key' => $this->syncKey,
                'date' => $this->date,
                'branch_code' => $this->branchCode,
                'page' => $this->page,
                'page_count' => $this->pageCount,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            if ($this->attempts() < $this->tries) {
                throw $e;
            }

            $this->updateState($cacheKey, $lockKey, [
                'api_rows' => 0,
                'saved_rows' => 0,
                'skipped_rows' => 0,
            ], $e->getMessage());
        }
    }

    protected function updateState(string $cacheKey, string $lockKey, array $result, ?string $error): void
    {
        $shouldFinalize = false;

        Cache::store('redis')->lock($lockKey, 10)->block(10, function () use ($cacheKey, $result, $error, &$shouldFinalize) {
            $state = Cache::store('redis')->get($cacheKey, []);

            $state['processed_pages'] = (int) ($state['processed_pages'] ?? 0) + 1;
            $state['total_api_rows'] = (int) ($state['total_api_rows'] ?? 0) + (int) ($result['api_rows'] ?? 0);
            $state['total_saved_rows'] = (int) ($state['total_saved_rows'] ?? 0) + (int) ($result['saved_rows'] ?? 0);
            $state['total_skipped_rows'] = (int) ($state['total_skipped_rows'] ?? 0) + (int) ($result['skipped_rows'] ?? 0);

            if ($error === null) {
                $state['success_pages'] = (int) ($state['success_pages'] ?? 0) + 1;
            } else {
                $state['failed_pages'] = (int) ($state['failed_pages'] ?? 0) + 1;
                $errors = $state['errors'] ?? [];
                $errors[] = "PAGE {$this->date} {$this->branchCode} p{$this->page}: {$error}";
                $state['errors'] = array_slice($errors, -100);
            }

            $logs = $state['logs'] ?? [];
            $logs[] = [
                'status' => $error === null ? 'page_success' : 'page_failed',
                'date' => $this->date,
                'branch_code' => $this->branchCode,
                'page' => $this->page,
                'page_count' => $this->pageCount,
                'api_rows' => (int) ($result['api_rows'] ?? 0),
                'saved_rows' => (int) ($result['saved_rows'] ?? 0),
                'message' => $error,
                'updated_at' => now()->toDateTimeString(),
            ];
            $state['logs'] = array_slice($logs, -80);

            $totalPages = (int) ($state['total_pages'] ?? 0);
            $processedPages = (int) ($state['processed_pages'] ?? 0);
            $preparedBranchTasks = (int) ($state['prepared_branch_tasks'] ?? 0);
            $totalBranchTasks = (int) ($state['total_branch_tasks'] ?? 0);

            $state['progress'] = $totalPages > 0
                ? min(99, (int) floor(($processedPages / $totalPages) * 100))
                : 0;

            $state['message'] = "Memproses page PNL {$processedPages}/{$totalPages}.";
            $state['updated_at'] = now()->toDateTimeString();

            if ($totalBranchTasks > 0
                && $preparedBranchTasks >= $totalBranchTasks
                && $totalPages > 0
                && $processedPages >= $totalPages
                && empty($state['finalize_dispatched'])
            ) {
                $state['finalize_dispatched'] = true;
                $state['message'] = 'Semua page selesai, finalisasi PNL.';
                $shouldFinalize = true;
            }

            Cache::store('redis')->put($cacheKey, $state, now()->addHours(12));
        });

        if ($shouldFinalize) {
            FinalizePnlLiveSyncJob::dispatch($this->syncKey)
                ->onConnection('redis')
                ->onQueue('esb-pnl');
        }
    }
}
