<?php

namespace App\Jobs;

use App\Services\EsbAuthService;
use App\Services\EsbBranchService;
use App\Services\EsbOutletSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SyncEsbSingleCredentialJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $syncKey;
    public int $credentialId;

    public int $timeout = 1800;
    public int $tries = 1;

    public function __construct(string $syncKey, int $credentialId)
    {
        $this->syncKey = $syncKey;
        $this->credentialId = $credentialId;

        $this->onConnection('redis');
        $this->onQueue('esb-sync');
    }

    public function middleware(): array
    {
        return [
            new WithoutOverlapping("esb-sync:credential:{$this->credentialId}"),
        ];
    }

    public function handle(
        EsbAuthService $authService,
        EsbBranchService $branchService,
        EsbOutletSyncService $outletSyncService
    ): void {
        $authCredential = $authService->getCredentialById($this->credentialId);

        if (! $authCredential) {
            $this->updateAggregateFailure(
                credentialCode: 'UNKNOWN',
                error: "Credential id {$this->credentialId} tidak ditemukan"
            );

            return;
        }

        try {
            $branchResult = $branchService->syncBranchesByCredentialId(
                $this->credentialId,
                $authService
            );

            $targetCredentialId = (int) (
                $branchResult['target_credential_id']
                ?? $branchResult['credential_id']
                ?? $authCredential->id
            );

            $targetCredential = DB::table('tbl_api_credentials')
                ->where('id', $targetCredentialId)
                ->where('is_active', 1)
                ->first();

            if (! $targetCredential) {
                throw new \RuntimeException(
                    "Target credential id {$targetCredentialId} tidak ditemukan setelah sync branch."
                );
            }

            $outletResult = $outletSyncService->syncBranchesToOutletsByCredential(
                $targetCredential,
                $this->syncKey
            );

            $this->updateAggregateSuccess(
                credentialCode: $targetCredential->credential_code,
                branchResult: $branchResult,
                outletResult: $outletResult
            );
        } catch (\Throwable $e) {
            Log::error('SYNC ESB SINGLE CREDENTIAL FAILED', [
                'sync_key'        => $this->syncKey,
                'credential_id'   => $this->credentialId,
                'credential_code' => $authCredential->credential_code ?? null,
                'error'           => $e->getMessage(),
                'trace'           => $e->getTraceAsString(),
            ]);

            $this->updateAggregateFailure(
                credentialCode: $authCredential->credential_code ?? 'UNKNOWN',
                error: $e->getMessage()
            );
        }
    }

    protected function updateAggregateSuccess(
        string $credentialCode,
        array $branchResult,
        array $outletResult
    ): void {
        $cacheKey = "outlet_sync_multi:{$this->syncKey}";
        $lockKey = "outlet_sync_multi_lock:{$this->syncKey}";

        Cache::store('redis')->lock($lockKey, 10)->block(10, function () use (
            $cacheKey,
            $credentialCode,
            $branchResult,
            $outletResult
        ) {
            $state = Cache::store('redis')->get($cacheKey, []);

            $total = (int) ($state['total_credentials'] ?? 0);
            $processed = min(
                $total > 0 ? $total : PHP_INT_MAX,
                (int) ($state['processed_credentials'] ?? 0) + 1
            );

            $success = min(
                $total > 0 ? $total : PHP_INT_MAX,
                (int) ($state['success_credentials'] ?? 0) + 1
            );

            $failed = (int) ($state['failed_credentials'] ?? 0);

            $totalInserted = (int) ($state['total_inserted'] ?? 0) + (int) ($outletResult['inserted'] ?? 0);
            $totalUpdated = (int) ($state['total_updated'] ?? 0) + (int) ($outletResult['updated'] ?? 0);
            $totalSkipped = (int) ($state['total_skipped'] ?? 0) + (int) ($outletResult['skipped'] ?? 0);
            $totalFailedRows = (int) ($state['total_failed_rows'] ?? 0) + (int) ($outletResult['failed'] ?? 0);
            $totalDetached = (int) ($state['total_detached'] ?? 0) + (int) ($outletResult['detached'] ?? 0);

            $perCredential = $state['per_credential'] ?? [];
            $perCredential[$credentialCode] = [
                'status'                      => 'success',
                'credential_id'               => $this->credentialId,
                'branch_saved'                => (int) ($branchResult['saved_branches'] ?? 0),
                'branch_valid_rows'           => (int) ($branchResult['valid_rows'] ?? 0),
                'branch_duplicates'           => (int) ($branchResult['duplicate_rows'] ?? 0),
                'branch_missing_branch_code'  => (int) ($branchResult['missing_branch_code'] ?? 0),
                'branch_missing_branch_id'    => (int) ($branchResult['missing_branch_id'] ?? 0),
                'branch_db_before'            => (int) ($branchResult['db_total_before'] ?? 0),
                'branch_db_after'             => (int) ($branchResult['db_total_after'] ?? 0),
                'total'                       => (int) ($outletResult['total'] ?? 0),
                'processed'                   => (int) ($outletResult['processed'] ?? 0),
                'inserted'                    => (int) ($outletResult['inserted'] ?? 0),
                'updated'                     => (int) ($outletResult['updated'] ?? 0),
                'skipped'                     => (int) ($outletResult['skipped'] ?? 0),
                'failed'                      => (int) ($outletResult['failed'] ?? 0),
                'detached'                    => (int) ($outletResult['detached'] ?? 0),
                'updated_at'                  => now()->toDateTimeString(),
            ];

            $logs = $state['logs'] ?? [];
            $logs[] = [
                'credential_id'               => $this->credentialId,
                'credential_code'             => $credentialCode,
                'status'                      => 'success',
                'branch_saved'                => (int) ($branchResult['saved_branches'] ?? 0),
                'branch_valid_rows'           => (int) ($branchResult['valid_rows'] ?? 0),
                'branch_duplicates'           => (int) ($branchResult['duplicate_rows'] ?? 0),
                'branch_missing_branch_code'  => (int) ($branchResult['missing_branch_code'] ?? 0),
                'branch_missing_branch_id'    => (int) ($branchResult['missing_branch_id'] ?? 0),
                'branch_db_before'            => (int) ($branchResult['db_total_before'] ?? 0),
                'branch_db_after'             => (int) ($branchResult['db_total_after'] ?? 0),
                'inserted'                    => (int) ($outletResult['inserted'] ?? 0),
                'updated'                     => (int) ($outletResult['updated'] ?? 0),
                'skipped'                     => (int) ($outletResult['skipped'] ?? 0),
                'failed'                      => (int) ($outletResult['failed'] ?? 0),
                'detached'                    => (int) ($outletResult['detached'] ?? 0),
                'details'                     => array_slice($outletResult['details'] ?? [], -5),
                'updated_at'                  => now()->toDateTimeString(),
            ];

            $progress = $total > 0 ? (int) floor(($processed / $total) * 100) : 100;
            $isFinished = $total > 0 ? $processed >= $total : true;

            $newState = [
                'status'                => $isFinished ? 'done' : 'processing',
                'message'               => $isFinished
                    ? 'Sinkronisasi branch + outlet semua credential selesai.'
                    : 'Sinkronisasi branch + outlet semua credential sedang berjalan.',
                'total_credentials'     => $total,
                'processed_credentials' => $processed,
                'success_credentials'   => $success,
                'failed_credentials'    => $failed,
                'total_inserted'        => $totalInserted,
                'total_updated'         => $totalUpdated,
                'total_skipped'         => $totalSkipped,
                'total_failed_rows'     => $totalFailedRows,
                'total_detached'        => $totalDetached,
                'progress'              => $isFinished ? 100 : $progress,
                'logs'                  => array_slice($logs, -20),
                'per_credential'        => $perCredential,
                'started_at'            => $state['started_at'] ?? now()->toDateTimeString(),
                'updated_at'            => now()->toDateTimeString(),
                'finished_at'           => $isFinished ? now()->toDateTimeString() : null,
                'finalized'             => $isFinished,
            ];

            Cache::store('redis')->put($cacheKey, $newState, now()->addHours(6));
        });
    }

    protected function updateAggregateFailure(string $credentialCode, string $error): void
    {
        $cacheKey = "outlet_sync_multi:{$this->syncKey}";
        $lockKey = "outlet_sync_multi_lock:{$this->syncKey}";

        Cache::store('redis')->lock($lockKey, 10)->block(10, function () use (
            $cacheKey,
            $credentialCode,
            $error
        ) {
            $state = Cache::store('redis')->get($cacheKey, []);

            $total = (int) ($state['total_credentials'] ?? 0);
            $processed = min(
                $total > 0 ? $total : PHP_INT_MAX,
                (int) ($state['processed_credentials'] ?? 0) + 1
            );

            $success = (int) ($state['success_credentials'] ?? 0);

            $failed = min(
                $total > 0 ? $total : PHP_INT_MAX,
                (int) ($state['failed_credentials'] ?? 0) + 1
            );

            $perCredential = $state['per_credential'] ?? [];
            $perCredential[$credentialCode] = [
                'status'        => 'failed',
                'credential_id' => $this->credentialId,
                'message'       => $error,
                'updated_at'    => now()->toDateTimeString(),
            ];

            $logs = $state['logs'] ?? [];
            $logs[] = [
                'credential_id'   => $this->credentialId,
                'credential_code' => $credentialCode,
                'status'          => 'failed',
                'inserted'        => 0,
                'updated'         => 0,
                'skipped'         => 0,
                'failed'          => 1,
                'detached'        => 0,
                'message'         => $error,
                'updated_at'      => now()->toDateTimeString(),
            ];

            $progress = $total > 0 ? (int) floor(($processed / $total) * 100) : 100;
            $isFinished = $total > 0 ? $processed >= $total : true;

            $newState = [
                'status'                => $isFinished ? 'done' : 'processing',
                'message'               => $isFinished
                    ? 'Sinkronisasi branch + outlet semua credential selesai.'
                    : 'Sinkronisasi branch + outlet semua credential sedang berjalan.',
                'total_credentials'     => $total,
                'processed_credentials' => $processed,
                'success_credentials'   => $success,
                'failed_credentials'    => $failed,
                'total_inserted'        => (int) ($state['total_inserted'] ?? 0),
                'total_updated'         => (int) ($state['total_updated'] ?? 0),
                'total_skipped'         => (int) ($state['total_skipped'] ?? 0),
                'total_failed_rows'     => (int) ($state['total_failed_rows'] ?? 0) + 1,
                'total_detached'        => (int) ($state['total_detached'] ?? 0),
                'progress'              => $isFinished ? 100 : $progress,
                'logs'                  => array_slice($logs, -20),
                'per_credential'        => $perCredential,
                'started_at'            => $state['started_at'] ?? now()->toDateTimeString(),
                'updated_at'            => now()->toDateTimeString(),
                'finished_at'           => $isFinished ? now()->toDateTimeString() : null,
                'finalized'             => $isFinished,
            ];

            Cache::store('redis')->put($cacheKey, $newState, now()->addHours(6));
        });
    }
}