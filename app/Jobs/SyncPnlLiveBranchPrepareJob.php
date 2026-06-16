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

class SyncPnlLiveBranchPrepareJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;
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
            $meta = $service->getLiveLedgerPageMeta(
                credentialCode: $this->credentialCode,
                credentialId: $this->credentialId,
                branchCode: $this->branchCode,
                startDate: $this->date,
                endDate: $this->date,
                limit: $this->limit
            );

            $pageCount = max(1, (int) ($meta['page_count'] ?? 1));

            Cache::store('redis')->lock($lockKey, 10)->block(10, function () use ($cacheKey, $pageCount, $meta) {
                $state = Cache::store('redis')->get($cacheKey, []);

                $state['status'] = 'processing';
                $state['message'] = 'Mengambil meta dan dispatch page job PNL.';
                $state['prepared_branch_tasks'] = (int) ($state['prepared_branch_tasks'] ?? 0) + 1;
                $state['success_branch_tasks'] = (int) ($state['success_branch_tasks'] ?? 0) + 1;
                $state['total_pages'] = (int) ($state['total_pages'] ?? 0) + $pageCount;
                $state['dispatched_pages'] = (int) ($state['dispatched_pages'] ?? 0) + $pageCount;
                $state['updated_at'] = now()->toDateTimeString();

                $logs = $state['logs'] ?? [];
                $logs[] = [
                    'status' => 'meta_success',
                    'date' => $this->date,
                    'branch_code' => $this->branchCode,
                    'page_count' => $pageCount,
                    'count' => $meta['count'] ?? null,
                    'limit' => $meta['limit'] ?? null,
                    'updated_at' => now()->toDateTimeString(),
                ];
                $state['logs'] = array_slice($logs, -80);

                Cache::store('redis')->put($cacheKey, $state, now()->addHours(12));
            });

            for ($page = 1; $page <= $pageCount; $page++) {
                SyncPnlLivePageJob::dispatch(
                    syncKey: $this->syncKey,
                    credentialCode: $this->credentialCode,
                    credentialId: $this->credentialId,
                    date: $this->date,
                    branchCode: $this->branchCode,
                    page: $page,
                    pageCount: $pageCount,
                    limit: $this->limit
                )->onConnection('redis')->onQueue('esb-pnl');
            }

            Log::info('PNL LIVE BRANCH PREPARE SUCCESS', [
                'sync_key' => $this->syncKey,
                'date' => $this->date,
                'branch_code' => $this->branchCode,
                'page_count' => $pageCount,
            ]);
        } catch (Throwable $e) {
            if ($this->attempts() < $this->tries) {
                throw $e;
            }

            Log::error('PNL LIVE BRANCH PREPARE FAILED', [
                'sync_key' => $this->syncKey,
                'date' => $this->date,
                'branch_code' => $this->branchCode,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);

            Cache::store('redis')->lock($lockKey, 10)->block(10, function () use ($cacheKey, $e) {
                $state = Cache::store('redis')->get($cacheKey, []);
                $state['prepared_branch_tasks'] = (int) ($state['prepared_branch_tasks'] ?? 0) + 1;
                $state['failed_branch_tasks'] = (int) ($state['failed_branch_tasks'] ?? 0) + 1;
                $state['updated_at'] = now()->toDateTimeString();

                $errors = $state['errors'] ?? [];
                $errors[] = "META {$this->date} {$this->branchCode}: {$e->getMessage()}";
                $state['errors'] = array_slice($errors, -100);

                $logs = $state['logs'] ?? [];
                $logs[] = [
                    'status' => 'meta_failed',
                    'date' => $this->date,
                    'branch_code' => $this->branchCode,
                    'message' => $e->getMessage(),
                    'updated_at' => now()->toDateTimeString(),
                ];
                $state['logs'] = array_slice($logs, -80);

                Cache::store('redis')->put($cacheKey, $state, now()->addHours(12));
            });

            $this->tryDispatchFinalize($cacheKey, $lockKey);
        }
    }

    protected function tryDispatchFinalize(string $cacheKey, string $lockKey): void
    {
        Cache::store('redis')->lock($lockKey, 10)->block(10, function () use ($cacheKey) {
            $state = Cache::store('redis')->get($cacheKey, []);
            $totalBranchTasks = (int) ($state['total_branch_tasks'] ?? 0);
            $preparedBranchTasks = (int) ($state['prepared_branch_tasks'] ?? 0);
            $totalPages = (int) ($state['total_pages'] ?? 0);
            $processedPages = (int) ($state['processed_pages'] ?? 0);

            if ($totalBranchTasks > 0
                && $preparedBranchTasks >= $totalBranchTasks
                && $processedPages >= $totalPages
                && empty($state['finalize_dispatched'])
            ) {
                $state['finalize_dispatched'] = true;
                $state['updated_at'] = now()->toDateTimeString();
                Cache::store('redis')->put($cacheKey, $state, now()->addHours(12));

                FinalizePnlLiveSyncJob::dispatch($this->syncKey)
                    ->onConnection('redis')
                    ->onQueue('esb-pnl');
            }
        });
    }
}
