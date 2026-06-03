<?php

namespace App\Jobs;

use App\Services\SimpleTransferService;
use Carbon\CarbonPeriod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncSimpleTransferPreparePagesJob implements ShouldQueue
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

    public function handle(SimpleTransferService $service): void
    {
        $cacheKey = "stf_sync:{$this->syncKey}";
        $lockKey = "stf_sync_lock:{$this->syncKey}";

        try {
            $companyCode = strtoupper(trim($this->companyCode));
            $period = CarbonPeriod::create($this->startDate, $this->endDate);

            $pageJobs = [];

            foreach ($period as $date) {
                $dateString = $date->format('Y-m-d');

                $meta = $service->getSimpleTransferCredentialPageMeta(
                    companyCode: $companyCode,
                    startDate: $dateString,
                    endDate: $dateString,
                    limit: $this->limit
                );

                Log::info('STF META', [
                    'sync_key' => $this->syncKey,
                    'date' => $dateString,
                    'meta' => $meta,
                ]);

                $pageCount = max(1, (int) ($meta['page_count'] ?? 1));

                Log::info('STF PAGE COUNT', [
                    'sync_key' => $this->syncKey,
                    'date' => $dateString,
                    'page_count' => $pageCount,
                ]);

                for ($page = 1; $page <= $pageCount; $page++) {
                    $pageJobs[] = [
                        'date' => $dateString,
                        'page' => $page,
                    ];
                }
            }

            $totalPages = count($pageJobs);

            Log::info('STF TOTAL PAGE JOBS', [
                'sync_key' => $this->syncKey,
                'total_pages' => $totalPages,
                'limit' => $this->limit,
            ]);

            Cache::store('redis')->lock($lockKey, 10)->block(10, function () use ($cacheKey, $totalPages) {
                $state = Cache::store('redis')->get($cacheKey, []);

                $state['status'] = 'processing';
                $state['message'] = "Dispatch {$totalPages} page job Simple Transfer.";
                $state['total_pages'] = $totalPages;
                $state['dispatched_pages'] = $totalPages;
                $state['updated_at'] = now()->toDateTimeString();

                Cache::store('redis')->put($cacheKey, $state, now()->addHours(12));
            });

            foreach ($pageJobs as $job) {
                Log::info('STF DISPATCH PAGE JOB', [
                    'sync_key' => $this->syncKey,
                    'date' => $job['date'],
                    'page' => $job['page'],
                    'total_pages' => $totalPages,
                    'limit' => $this->limit,
                ]);

                SyncSimpleTransferPageJob::dispatch(
                    $this->syncKey,
                    $job['date'],
                    $job['date'],
                    $companyCode,
                    $job['page'],
                    $totalPages,
                    $this->limit
                )->onConnection('redis')->onQueue('transfer-sync');
            }
        } catch (\Throwable $e) {
            Log::error('STF PREPARE PAGES FAILED', [
                'sync_key' => $this->syncKey,
                'company_code' => $this->companyCode,
                'date_from' => $this->startDate,
                'date_to' => $this->endDate,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);

            Cache::store('redis')->lock($lockKey, 10)->block(10, function () use ($cacheKey, $e) {
                $state = Cache::store('redis')->get($cacheKey, []);
                $state['status'] = 'failed';
                $state['message'] = $e->getMessage();
                $state['finished_at'] = now()->toDateTimeString();
                $state['updated_at'] = now()->toDateTimeString();

                Cache::store('redis')->put($cacheKey, $state, now()->addHours(12));
            });
        }
    }
}