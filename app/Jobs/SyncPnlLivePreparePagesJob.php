<?php

namespace App\Jobs;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncPnlLivePreparePagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900;
    public int $tries = 1;

    public function __construct(
        public string $syncKey,
        public string $credentialCode,
        public int $credentialId,
        public string $startDate,
        public string $endDate,
        public int $limit = 100
    ) {
        $this->onConnection('redis');
        $this->onQueue('esb-pnl');
    }

    public function handle(): void
    {
        $cacheKey = "pnl_live_sync:{$this->syncKey}";
        $lockKey = "pnl_live_sync_lock:{$this->syncKey}";

        try {
            $credentialCode = strtoupper(trim($this->credentialCode));

            $units = DB::table('tbl_outlets')
                ->where('credential_id', $this->credentialId)
                ->whereNotNull('esb_branch_code')
                ->where('esb_branch_code', '!=', '')
                ->select('id', 'nama_outlet', 'credential_id', 'esb_branch_id', 'esb_branch_code')
                ->orderBy('nama_outlet')
                ->get();

            $branchCodes = $units
                ->pluck('esb_branch_code')
                ->filter()
                ->map(fn ($v) => strtoupper(trim((string) $v)))
                ->filter(fn ($v) => $v !== '')
                ->unique()
                ->values()
                ->all();

            $dates = [];
            foreach (CarbonPeriod::create($this->startDate, $this->endDate) as $date) {
                $dates[] = $date->format('Y-m-d');
            }

            $totalBranchTasks = count($branchCodes) * count($dates);

            Log::info('PNL LIVE PREPARE OPTIMIZED START', [
                'sync_key' => $this->syncKey,
                'credential_code' => $credentialCode,
                'credential_id' => $this->credentialId,
                'outlet_count' => $units->count(),
                'unique_branch_count' => count($branchCodes),
                'date_count' => count($dates),
                'total_branch_tasks' => $totalBranchTasks,
                'limit' => $this->limit,
            ]);

            Cache::store('redis')->lock($lockKey, 10)->block(10, function () use ($cacheKey, $units, $branchCodes, $dates, $totalBranchTasks) {
                $state = Cache::store('redis')->get($cacheKey, []);

                $state['status'] = 'preparing';
                $state['message'] = 'Menyiapkan sync PNL paralel per branch unik.';
                $state['total_outlets'] = $units->count();
                $state['total_unique_branches'] = count($branchCodes);
                $state['total_dates'] = count($dates);
                $state['total_branch_tasks'] = $totalBranchTasks;
                $state['prepared_branch_tasks'] = 0;
                $state['success_branch_tasks'] = 0;
                $state['failed_branch_tasks'] = 0;
                $state['total_pages'] = 0;
                $state['dispatched_pages'] = 0;
                $state['processed_pages'] = 0;
                $state['success_pages'] = 0;
                $state['failed_pages'] = 0;
                $state['progress'] = 0;
                $state['started_at'] = $state['started_at'] ?: now()->toDateTimeString();
                $state['updated_at'] = now()->toDateTimeString();
                $state['units'] = $units->map(fn ($u) => (array) $u)->values()->all();
                $state['branch_codes'] = $branchCodes;
                $state['finalize_dispatched'] = false;
                $state['finalized'] = false;

                Cache::store('redis')->put($cacheKey, $state, now()->addHours(12));
            });

            if ($totalBranchTasks <= 0) {
                Cache::store('redis')->put($cacheKey, array_merge(Cache::store('redis')->get($cacheKey, []), [
                    'status' => 'failed',
                    'message' => 'Branch ESB untuk credential ini tidak ditemukan.',
                    'progress' => 100,
                    'finished_at' => now()->toDateTimeString(),
                    'updated_at' => now()->toDateTimeString(),
                ]), now()->addHours(12));

                Cache::store('redis')->forget('pnl_live_sync_oknho_active_key');
                return;
            }

            foreach ($dates as $date) {
                foreach ($branchCodes as $branchCode) {
                    SyncPnlLiveBranchPrepareJob::dispatch(
                        syncKey: $this->syncKey,
                        credentialCode: $credentialCode,
                        credentialId: $this->credentialId,
                        date: $date,
                        branchCode: $branchCode,
                        limit: $this->limit
                    )->onConnection('redis')->onQueue('esb-pnl');
                }
            }

            Log::info('PNL LIVE BRANCH PREPARE JOBS DISPATCHED', [
                'sync_key' => $this->syncKey,
                'total_branch_tasks' => $totalBranchTasks,
            ]);
        } catch (Throwable $e) {
            Log::error('PNL LIVE PREPARE OPTIMIZED FAILED', [
                'sync_key' => $this->syncKey,
                'credential_code' => $this->credentialCode,
                'credential_id' => $this->credentialId,
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            Cache::store('redis')->put($cacheKey, array_merge(Cache::store('redis')->get($cacheKey, []), [
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
