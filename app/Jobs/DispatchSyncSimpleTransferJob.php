<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DispatchSyncSimpleTransferJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 1;

    public function __construct(
        public string $syncKey,
        public string $startDate,
        public string $endDate,
        public string $companyCode = 'OKNHO',
        public int $limit = 100
    ) {
        $this->onConnection('redis');
        $this->onQueue('transfer-sync');
    }

    public function handle(): void
    {
        $companyCode = strtoupper(trim($this->companyCode));
        $cacheKey = "stf_sync:{$this->syncKey}";

        Cache::store('redis')->put($cacheKey, [
            'status' => 'processing',
            'message' => 'Menyiapkan job Simple Transfer per hari dan per page.',
            'sync_key' => $this->syncKey,
            'company_code' => $companyCode,
            'date_from' => $this->startDate,
            'date_to' => $this->endDate,
            'limit' => $this->limit,
            'total_pages' => 0,
            'dispatched_pages' => 0,
            'processed_pages' => 0,
            'success_pages' => 0,
            'failed_pages' => 0,
            'total_api_rows' => 0,
            'total_saved_header' => 0,
            'total_detail_jobs' => 0,
            'total_skipped_rows' => 0,
            'total_duplicate_rows' => 0,
            'progress' => 0,
            'logs' => [],
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
            'finished_at' => null,
        ], now()->addHours(12));

        SyncSimpleTransferPreparePagesJob::dispatch(
            $this->syncKey,
            $this->startDate,
            $this->endDate,
            $companyCode,
            $this->limit
        )->onConnection('redis')->onQueue('transfer-sync');

        Log::info('STF DISPATCH STARTED', [
            'sync_key' => $this->syncKey,
            'company_code' => $companyCode,
            'date_from' => $this->startDate,
            'date_to' => $this->endDate,
            'limit' => $this->limit,
        ]);
    }
}
