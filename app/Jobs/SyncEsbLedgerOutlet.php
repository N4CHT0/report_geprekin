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

class SyncEsbLedgerOutlet implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $queue = 'esb-ledger';
    public int $timeout = 3600;
    public int $tries = 3;

    public int $outletId;
    public string $start;
    public string $end;

    public function __construct(int $outletId, string $start, string $end)
    {
        $this->outletId = $outletId;
        $this->start = $start;
        $this->end = $end;
    }

    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function handle(EsbLedgerService $service): void
    {
        Log::info('SyncEsbLedgerOutlet START', [
            'job_id' => optional($this->job)->getJobId(),
            'attempt' => method_exists($this, 'attempts') ? $this->attempts() : null,
            'outlet_id' => $this->outletId,
            'start' => $this->start,
            'end' => $this->end,
        ]);

        try {
            $result = $service->syncGeneralLedgerByOutletIdAllPagesAuto(
                $this->outletId,
                $this->start,
                $this->end
            );

            Log::info('SyncEsbLedgerOutlet END', [
                'job_id' => optional($this->job)->getJobId(),
                'attempt' => method_exists($this, 'attempts') ? $this->attempts() : null,
                'result' => $result,
            ]);
        } catch (Throwable $e) {
            Log::error('SyncEsbLedgerOutlet HANDLE ERROR', [
                'job_id' => optional($this->job)->getJobId(),
                'attempt' => method_exists($this, 'attempts') ? $this->attempts() : null,
                'outlet_id' => $this->outletId,
                'start' => $this->start,
                'end' => $this->end,
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
        Log::error('SyncEsbLedgerOutlet FAILED', [
            'job_id' => optional($this->job)->getJobId(),
            'outlet_id' => $this->outletId,
            'start' => $this->start,
            'end' => $this->end,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    }
}