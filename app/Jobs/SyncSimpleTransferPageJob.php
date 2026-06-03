<?php

namespace App\Jobs;

use App\Services\SimpleTransferService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncSimpleTransferPageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;
    public int $tries = 3;

    public function backoff(): array
    {
        return [5, 15, 30];
    }

    public function __construct(
        public string $syncKey,
        public string $startDate,
        public string $endDate,
        public string $companyCode,
        public int $page,
        public int $pageCount,
        public int $limit = 100
    ) {
        $this->onConnection('redis');
        $this->onQueue('transfer-sync');
    }

    public function handle(SimpleTransferService $service): void
    {
        $cacheKey = "stf_sync:{$this->syncKey}";
        $lockKey = "stf_sync_lock:{$this->syncKey}";

        try {
            $result = $service->syncSimpleTransferCredentialPage(
                companyCode: $this->companyCode,
                startDate: $this->startDate,
                endDate: $this->endDate,
                page: $this->page,
                limit: $this->limit
            );

            $this->updateState($cacheKey, $lockKey, $result, null);
        } catch (\Throwable $e) {
            Log::error('STF PAGE FAILED', [
                'sync_key' => $this->syncKey,
                'company_code' => $this->companyCode,
                'date_from' => $this->startDate,
                'date_to' => $this->endDate,
                'page' => $this->page,
                'page_count' => $this->pageCount,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);

            if ($this->attempts() < $this->tries) {
                throw $e;
            }

            $this->updateState($cacheKey, $lockKey, [
                'api_rows' => 0,
                'saved_header' => 0,
                'detail_jobs' => 0,
                'skipped_rows' => 0,
                'duplicate_rows' => 0,
            ], $e->getMessage());
        }
    }

    protected function updateState(string $cacheKey, string $lockKey, array $result, ?string $error): void
    {
        Cache::store('redis')->lock($lockKey, 10)->block(10, function () use ($cacheKey, $result, $error) {
            $state = Cache::store('redis')->get($cacheKey, []);

            $state['processed_pages'] = (int) ($state['processed_pages'] ?? 0) + 1;
            $state['total_api_rows'] = (int) ($state['total_api_rows'] ?? 0) + (int) ($result['api_rows'] ?? 0);
            $state['total_saved_header'] = (int) ($state['total_saved_header'] ?? 0) + (int) ($result['saved_header'] ?? 0);
            $state['total_detail_jobs'] = (int) ($state['total_detail_jobs'] ?? 0) + (int) ($result['detail_jobs'] ?? 0);
            $state['total_skipped_rows'] = (int) ($state['total_skipped_rows'] ?? 0) + (int) ($result['skipped_rows'] ?? 0);
            $state['total_duplicate_rows'] = (int) ($state['total_duplicate_rows'] ?? 0) + (int) ($result['duplicate_rows'] ?? 0);

            if ($error === null) {
                $state['success_pages'] = (int) ($state['success_pages'] ?? 0) + 1;
            } else {
                $state['failed_pages'] = (int) ($state['failed_pages'] ?? 0) + 1;
            }

            $logs = $state['logs'] ?? [];
            $logs[] = [
                'status' => $error === null ? 'success' : 'failed',
                'company_code' => $this->companyCode,
                'date_from' => $this->startDate,
                'date_to' => $this->endDate,
                'page' => $this->page,
                'page_count' => $this->pageCount,
                'api_rows' => (int) ($result['api_rows'] ?? 0),
                'saved_header' => (int) ($result['saved_header'] ?? 0),
                'duplicate_rows' => (int) ($result['duplicate_rows'] ?? 0),
                'message' => $error,
                'updated_at' => now()->toDateTimeString(),
            ];

            $state['logs'] = array_slice($logs, -30);

            $totalPages = (int) ($state['total_pages'] ?? $this->pageCount);
            $processedPages = (int) ($state['processed_pages'] ?? 0);

            $state['progress'] = $totalPages > 0
                ? min(100, (int) floor(($processedPages / $totalPages) * 100))
                : 0;

            $state['updated_at'] = now()->toDateTimeString();

            if ($totalPages > 0 && $processedPages >= $totalPages) {
                $state['status'] = (int) ($state['failed_pages'] ?? 0) > 0 ? 'done_with_errors' : 'done';
                $state['message'] = 'Sync Simple Transfer selesai.';
                $state['progress'] = 100;
                $state['finished_at'] = now()->toDateTimeString();

                Cache::store('redis')->forget('stf_running');
            }

            Cache::store('redis')->put($cacheKey, $state, now()->addHours(12));
        });
    }
}
