<?php

namespace App\Jobs;

use App\Services\EsbLedgerLivePnlService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncPnlLiveAllBranchesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $syncKey;
    public string $credentialCode;
    public int $credentialId;
    public string $startDate;
    public string $endDate;

    public int $timeout = 7200;
    public int $tries = 2;

    public function __construct(
        string $syncKey,
        string $credentialCode,
        int $credentialId,
        string $startDate,
        string $endDate
    ) {
        $this->syncKey = $syncKey;
        $this->credentialCode = $credentialCode;
        $this->credentialId = $credentialId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;

        $this->onConnection('redis');
        $this->onQueue('esb-pnl');
    }

    public function handle(EsbLedgerLivePnlService $service): void
    {
        $cacheKey = "pnl_live_sync:{$this->syncKey}";
        $state = Cache::get($cacheKey);

        if (! $state) {
            return;
        }

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
            ->map(fn ($v) => trim((string) $v))
            ->unique()
            ->values()
            ->all();

        Cache::put($cacheKey, array_merge($state, [
            'status' => 'processing',
            'message' => 'Sync PNL live sedang berjalan.',
            'started_at' => $state['started_at'] ?: now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
            'total_branches' => count($branchCodes),
        ]), now()->addHours(6));

        try {
            $result = $service->buildLivePnlByBranches(
                $this->credentialCode,
                $this->credentialId,
                $units,
                $branchCodes,
                $this->startDate,
                $this->endDate,
                $this->syncKey
            );

            Cache::put($cacheKey, [
                'status' => 'done',
                'message' => 'Sync PNL live selesai.',
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'credential_id' => $this->credentialId,
                'credential_code' => $this->credentialCode,
                'total_branches' => count($branchCodes),
                'processed_branches' => count($branchCodes),
                'success_branches' => $result['success_branches'],
                'failed_branches' => $result['failed_branches'],
                'progress' => 100,
                'requested_at' => $state['requested_at'] ?? now()->toDateTimeString(),
                'started_at' => $state['started_at'] ?: now()->toDateTimeString(),
                'finished_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
                'errors' => $result['errors'],
                'logs' => $result['logs'],
                'rows' => $result['rows'],
                'units' => $units->map(fn ($u) => (array) $u)->values()->all(),
                'grandPendapatan' => $result['grandPendapatan'],
                'grandLaba' => $result['grandLaba'],
                'grandNpm' => $result['grandNpm'],
            ], now()->addHours(6));

            Cache::forget('pnl_live_sync_oknho_active_key');
        } catch (Throwable $e) {
            Log::error('SYNC PNL LIVE FAILED', [
                'sync_key' => $this->syncKey,
                'credential_code' => $this->credentialCode,
                'credential_id' => $this->credentialId,
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'error' => $e->getMessage(),
            ]);

            Cache::put($cacheKey, array_merge($state, [
                'status' => 'failed',
                'message' => $e->getMessage(),
                'finished_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ]), now()->addHours(6));

            Cache::forget('pnl_live_sync_oknho_active_key');

            throw $e;
        }
    }
}