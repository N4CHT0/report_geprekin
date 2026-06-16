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

class FinalizePnlLiveSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;
    public int $tries = 1;

    public function __construct(public string $syncKey)
    {
        $this->onConnection('redis');
        $this->onQueue('esb-pnl');
    }

    public function handle(EsbLedgerLivePnlService $service): void
    {
        $cacheKey = "pnl_live_sync:{$this->syncKey}";
        $state = Cache::store('redis')->get($cacheKey, []);

        try {
            Cache::store('redis')->put($cacheKey, array_merge($state, [
                'status' => 'finalizing',
                'message' => 'Menghitung ulang matrix PNL dari staging.',
                'updated_at' => now()->toDateTimeString(),
            ]), now()->addHours(12));

            $result = $service->buildPnlResultFromStaging($this->syncKey);

            $current = Cache::store('redis')->get($cacheKey, []);
            Cache::store('redis')->put($cacheKey, array_merge($current, [
                'status' => ((int) ($current['failed_pages'] ?? 0) > 0 || (int) ($current['failed_branch_tasks'] ?? 0) > 0) ? 'done_with_errors' : 'done',
                'message' => 'Sync PNL live selesai.',
                'progress' => 100,
                'rows' => $result['rows'],
                'units' => $result['units'],
                'grandPendapatan' => $result['grandPendapatan'],
                'grandLaba' => $result['grandLaba'],
                'grandNpm' => $result['grandNpm'],
                'finished_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
                'finalized' => true,
            ]), now()->addHours(12));

            Cache::store('redis')->forget('pnl_live_sync_oknho_active_key');

            Log::info('PNL LIVE FINALIZED', [
                'sync_key' => $this->syncKey,
                'grandPendapatan' => $result['grandPendapatan'],
                'grandLaba' => $result['grandLaba'],
                'grandNpm' => $result['grandNpm'],
            ]);
        } catch (Throwable $e) {
            Log::error('PNL LIVE FINALIZE FAILED', [
                'sync_key' => $this->syncKey,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            Cache::store('redis')->put($cacheKey, array_merge($state, [
                'status' => 'failed',
                'message' => $e->getMessage(),
                'progress' => 100,
                'finished_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ]), now()->addHours(12));

            Cache::store('redis')->forget('pnl_live_sync_oknho_active_key');
        }
    }
}
