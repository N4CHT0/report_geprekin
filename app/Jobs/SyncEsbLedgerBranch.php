<?php

namespace App\Jobs;

use App\Services\EsbLedgerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncEsbLedgerBranch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;
    public int $tries = 3;

    public string $credentialCode;
    public string $branchCode;
    public string $date;
    public ?int $outletId;
    public ?string $outletName;

    public function __construct(
        string $credentialCode,
        string $branchCode,
        string $date,
        ?int $outletId = null,
        ?string $outletName = null
    ) {
        $this->credentialCode = $credentialCode;
        $this->branchCode = $branchCode;
        $this->date = $date;
        $this->outletId = $outletId;
        $this->outletName = $outletName;

        $this->onQueue('esb-ledger');
    }

    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function handle(EsbLedgerService $service): void
    {
        Log::info('SyncEsbLedgerBranch START', [
            'job_id' => $this->job?->getJobId(),
            'attempt' => $this->attempts(),
            'credential_code' => $this->credentialCode,
            'branch_code' => $this->branchCode,
            'date' => $this->date,
            'outlet_id' => $this->outletId,
            'outlet_name' => $this->outletName,
        ]);

        try {
            $result = $service->syncAllPages(
                $this->credentialCode,
                $this->branchCode,
                $this->date,
                $this->date
            );

            Log::info('SyncEsbLedgerBranch END', [
                'job_id' => $this->job?->getJobId(),
                'attempt' => $this->attempts(),
                'credential_code' => $this->credentialCode,
                'branch_code' => $this->branchCode,
                'date' => $this->date,
                'outlet_id' => $this->outletId,
                'outlet_name' => $this->outletName,
                'result' => $result,
            ]);
        } catch (Throwable $e) {
            Log::error('SyncEsbLedgerBranch HANDLE ERROR', [
                'job_id' => $this->job?->getJobId(),
                'attempt' => $this->attempts(),
                'credential_code' => $this->credentialCode,
                'branch_code' => $this->branchCode,
                'date' => $this->date,
                'outlet_id' => $this->outletId,
                'outlet_name' => $this->outletName,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    public function failed(Throwable $e): void
    {
        Log::error('SyncEsbLedgerBranch FAILED', [
            'job_id' => $this->job?->getJobId(),
            'credential_code' => $this->credentialCode,
            'branch_code' => $this->branchCode,
            'date' => $this->date,
            'outlet_id' => $this->outletId,
            'outlet_name' => $this->outletName,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    }
}