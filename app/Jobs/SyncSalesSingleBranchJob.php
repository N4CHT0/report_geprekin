<?php

namespace App\Jobs;

use App\Services\EsbSalesService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncSalesSingleBranchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $syncKey;
    public string $salesDate;
    public int $credentialId;
    public string $branchCode;
    public string $branchName;
    public string $credentialCode;
    public string $credentialName;

    public int $timeout = 2400;
    public int $tries = 3;

    /**
     * Retry delay jika job benar-benar gagal di level queue.
     */
    public array $backoff = [15, 30, 60];

    public function __construct(
        string $syncKey,
        string $salesDate,
        int $credentialId,
        string $branchCode,
        string $branchName = '',
        string $credentialCode = '',
        string $credentialName = ''
    ) {
        $this->syncKey = $syncKey;
        $this->salesDate = $salesDate;
        $this->credentialId = $credentialId;
        $this->branchCode = $branchCode;
        $this->branchName = $branchName;
        $this->credentialCode = $credentialCode;
        $this->credentialName = $credentialName;

        $this->onConnection('redis');
        $this->onQueue('esb-sales');
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping(
                "sales:{$this->salesDate}:{$this->credentialId}:{$this->branchCode}"
            ))
                ->expireAfter(2400)
                ->releaseAfter(20),
        ];
    }

    public function handle(EsbSalesService $service): void
    {
        try {
            $result = $service->syncSalesByBranchAndDate(
                $this->credentialId,
                $this->branchCode,
                $this->salesDate,
                $this->salesDate
            );

            $this->updateAggregate([
                'status'        => 'success',
                'inserted_rows' => (int) ($result['inserted_rows'] ?? 0),
                'api_rows'      => (int) ($result['api_rows'] ?? 0),
                'built_rows'    => (int) ($result['built_rows'] ?? 0),
                'message'       => null,
                'outlet_id'     => (int) ($result['outlet_id'] ?? 0),
                'outlet_name'   => (string) ($result['outlet_name'] ?? ''),
                'matched_by'    => (string) ($result['matched_by'] ?? ''),
                'match_score'   => (float) ($result['match_score'] ?? 0),
            ]);
        } catch (Throwable $e) {
            Log::error('SYNC SALES BRANCH FAILED', [
                'sync_key'        => $this->syncKey,
                'credential_id'   => $this->credentialId,
                'credential_code' => $this->credentialCode,
                'credential_name' => $this->credentialName,
                'branch_code'     => $this->branchCode,
                'branch_name'     => $this->branchName,
                'sales_date'      => $this->salesDate,
                'error'           => $e->getMessage(),
            ]);

            $this->updateAggregate([
                'status'        => 'failed',
                'inserted_rows' => 0,
                'api_rows'      => 0,
                'built_rows'    => 0,
                'message'       => $e->getMessage(),
                'outlet_id'     => 0,
                'outlet_name'   => '',
                'matched_by'    => '',
                'match_score'   => 0,
            ]);
        }
    }

    public function failed(Throwable $e): void
    {
        Log::warning('SYNC SALES BRANCH QUEUE FAILED', [
            'sync_key'        => $this->syncKey,
            'credential_id'   => $this->credentialId,
            'credential_code' => $this->credentialCode,
            'credential_name' => $this->credentialName,
            'branch_code'     => $this->branchCode,
            'branch_name'     => $this->branchName,
            'sales_date'      => $this->salesDate,
            'error'           => $e->getMessage(),
        ]);

        $this->updateAggregateIfNotProcessed([
            'status'        => 'failed',
            'inserted_rows' => 0,
            'api_rows'      => 0,
            'built_rows'    => 0,
            'message'       => $e->getMessage(),
            'outlet_id'     => 0,
            'outlet_name'   => '',
            'matched_by'    => '',
            'match_score'   => 0,
        ]);
    }

    protected function updateAggregate(array $payload): void
    {
        $lockKey = "sales_sync_all_state_lock:{$this->syncKey}";
        $cacheKey = "sales_sync_all:{$this->syncKey}";

        Cache::store('redis')->lock($lockKey, 60)->block(30, function () use ($cacheKey, $payload) {
            $state = Cache::store('redis')->get($cacheKey);

            if (! $state) {
                return;
            }

            $processed = (int) ($state['processed_branches'] ?? 0) + 1;
            $success = (int) ($state['success_branches'] ?? 0);
            $failed = (int) ($state['failed_branches'] ?? 0);
            $total = (int) ($state['total_branches'] ?? 0);
            $totalInserted = (int) ($state['total_inserted_rows'] ?? 0);

            if (($payload['status'] ?? '') === 'success') {
                $success++;
                $totalInserted += (int) ($payload['inserted_rows'] ?? 0);
            } else {
                $failed++;
            }

            $finishedAt = now()->toDateTimeString();
            $perBranchKey = $this->credentialId . ':' . $this->branchCode;

            $perBranch = $state['per_branch'] ?? [];
            $perBranch[$perBranchKey] = [
                'credential_id'   => $this->credentialId,
                'credential_code' => $this->credentialCode,
                'credential_name' => $this->credentialName,
                'branch_code'     => $this->branchCode,
                'branch_name'     => $this->branchName,
                'status'          => $payload['status'],
                'inserted_rows'   => (int) $payload['inserted_rows'],
                'api_rows'        => (int) $payload['api_rows'],
                'built_rows'      => (int) $payload['built_rows'],
                'message'         => $payload['message'],
                'outlet_id'       => (int) $payload['outlet_id'],
                'outlet_name'     => $payload['outlet_name'],
                'matched_by'      => $payload['matched_by'],
                'match_score'     => $payload['match_score'],
                'finished_at'     => $finishedAt,
            ];

            $logs = $state['logs'] ?? [];
            $logs[] = [
                'credential_id'   => $this->credentialId,
                'credential_code' => $this->credentialCode,
                'credential_name' => $this->credentialName,
                'branch_code'     => $this->branchCode,
                'branch_name'     => $this->branchName,
                'status'          => $payload['status'],
                'inserted_rows'   => (int) $payload['inserted_rows'],
                'api_rows'        => (int) $payload['api_rows'],
                'built_rows'      => (int) $payload['built_rows'],
                'message'         => $payload['message'],
                'outlet_id'       => (int) $payload['outlet_id'],
                'outlet_name'     => $payload['outlet_name'],
                'matched_by'      => $payload['matched_by'],
                'match_score'     => $payload['match_score'],
                'finished_at'     => $finishedAt,
            ];

            $progress = $total > 0 ? (int) floor(($processed / $total) * 100) : 100;
            $isFinished = $processed >= $total;

            $state['status'] = $isFinished ? 'done' : 'processing';
            $state['message'] = $isFinished
                ? 'Sinkronisasi sales all branch selesai.'
                : 'Sinkronisasi sales all branch sedang berjalan.';
            $state['processed_branches'] = $processed;
            $state['success_branches'] = $success;
            $state['failed_branches'] = $failed;
            $state['total_inserted_rows'] = $totalInserted;
            $state['progress'] = $isFinished ? 100 : $progress;
            $state['per_branch'] = $perBranch;
            $state['logs'] = array_slice($logs, -100);
            $state['updated_at'] = $finishedAt;

            if ($isFinished && ! ($state['finalized'] ?? false)) {
                $state['finalized'] = true;
                $state['finished_at'] = $finishedAt;

                SyncSalesSummaryToLaporanBulananJob::dispatch($this->salesDate)
                    ->onConnection('redis')
                    ->onQueue('esb-sales');

                Cache::store('redis')->forget('sales_sync_all_active_key');
            }

            Cache::store('redis')->put($cacheKey, $state, now()->addHours(6));
        });
    }

    protected function updateAggregateIfNotProcessed(array $payload): void
    {
        $lockKey = "sales_sync_all_state_lock:{$this->syncKey}";
        $cacheKey = "sales_sync_all:{$this->syncKey}";

        Cache::store('redis')->lock($lockKey, 60)->block(30, function () use ($cacheKey, $payload) {
            $state = Cache::store('redis')->get($cacheKey);

            if (! $state) {
                return;
            }

            $perBranchKey = $this->credentialId . ':' . $this->branchCode;
            $existing = $state['per_branch'][$perBranchKey] ?? null;

            if ($existing && in_array(($existing['status'] ?? null), ['success', 'failed'], true)) {
                return;
            }

            $processed = (int) ($state['processed_branches'] ?? 0) + 1;
            $success = (int) ($state['success_branches'] ?? 0);
            $failed = (int) ($state['failed_branches'] ?? 0) + 1;
            $total = (int) ($state['total_branches'] ?? 0);

            $finishedAt = now()->toDateTimeString();

            $perBranch = $state['per_branch'] ?? [];
            $perBranch[$perBranchKey] = [
                'credential_id'   => $this->credentialId,
                'credential_code' => $this->credentialCode,
                'credential_name' => $this->credentialName,
                'branch_code'     => $this->branchCode,
                'branch_name'     => $this->branchName,
                'status'          => $payload['status'],
                'inserted_rows'   => (int) $payload['inserted_rows'],
                'api_rows'        => (int) $payload['api_rows'],
                'built_rows'      => (int) $payload['built_rows'],
                'message'         => $payload['message'],
                'outlet_id'       => (int) $payload['outlet_id'],
                'outlet_name'     => $payload['outlet_name'],
                'matched_by'      => $payload['matched_by'],
                'match_score'     => $payload['match_score'],
                'finished_at'     => $finishedAt,
            ];

            $logs = $state['logs'] ?? [];
            $logs[] = [
                'credential_id'   => $this->credentialId,
                'credential_code' => $this->credentialCode,
                'credential_name' => $this->credentialName,
                'branch_code'     => $this->branchCode,
                'branch_name'     => $this->branchName,
                'status'          => $payload['status'],
                'inserted_rows'   => (int) $payload['inserted_rows'],
                'api_rows'        => (int) $payload['api_rows'],
                'built_rows'      => (int) $payload['built_rows'],
                'message'         => $payload['message'],
                'outlet_id'       => (int) $payload['outlet_id'],
                'outlet_name'     => $payload['outlet_name'],
                'matched_by'      => $payload['matched_by'],
                'match_score'     => $payload['match_score'],
                'finished_at'     => $finishedAt,
            ];

            $progress = $total > 0 ? (int) floor(($processed / $total) * 100) : 100;
            $isFinished = $processed >= $total;

            $state['status'] = $isFinished ? 'done' : 'processing';
            $state['message'] = $isFinished
                ? 'Sinkronisasi sales all branch selesai.'
                : 'Sinkronisasi sales all branch sedang berjalan.';
            $state['processed_branches'] = $processed;
            $state['success_branches'] = $success;
            $state['failed_branches'] = $failed;
            $state['progress'] = $isFinished ? 100 : $progress;
            $state['per_branch'] = $perBranch;
            $state['logs'] = array_slice($logs, -100);
            $state['updated_at'] = $finishedAt;

            if ($isFinished && ! ($state['finalized'] ?? false)) {
                $state['finalized'] = true;
                $state['finished_at'] = $finishedAt;

                SyncSalesSummaryToLaporanBulananJob::dispatch($this->salesDate)
                    ->onConnection('redis')
                    ->onQueue('esb-sales');

                Cache::store('redis')->forget('sales_sync_all_active_key');
            }

            Cache::store('redis')->put($cacheKey, $state, now()->addHours(6));
        });
    }
}