<?php

namespace App\Jobs;

use App\Services\EsbOutletSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncMissingOutletsFromEsbJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $credentialCode;
    public string $syncKey;

    public $timeout = 3600;
    public $tries = 1;

    public function __construct(string $credentialCode, string $syncKey)
    {
        $this->credentialCode = $credentialCode;
        $this->syncKey = $syncKey;
        $this->onQueue('default');
    }

    public function handle(EsbOutletSyncService $service): void
    {
        $service->syncMissingOutletsFromBranches($this->credentialCode, $this->syncKey);
    }

    public function failed(\Throwable $e): void
    {
        cache()->put("esb_outlet_sync:{$this->syncKey}", [
            'status' => 'failed',
            'message' => $e->getMessage(),
            'credential_code' => $this->credentialCode,
            'progress' => 0,
        ], now()->addHours(6));
    }
}