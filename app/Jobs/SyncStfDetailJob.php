<?php

namespace App\Jobs;

use App\Services\SimpleTransferService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncStfDetailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;
    public int $timeout = 180;

    public function __construct(
        protected string $companyCode,
        protected string $stfNum
    ) {
        $this->onConnection('redis');
        $this->onQueue('transfer-detail');
    }

    public function handle(SimpleTransferService $service): void
    {
        $service->fetchAndStoreDetail($this->companyCode, $this->stfNum);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('STF DETAIL JOB FAILED', [
            'company_code' => $this->companyCode,
            'stf_num' => $this->stfNum,
            'error' => $exception->getMessage(),
            'line' => $exception->getLine(),
        ]);
    }
}
