<?php

namespace App\Jobs;

use App\Services\EsbSalesService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class SyncSalesByBranchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;
    public int $tries = 1;

    public function __construct(
        public string $syncKey,
        public int $credentialId,
        public string $branchCode,
        public string $salesDate,
        public ?int $forcedOutletId = null
    ) {
        $this->onConnection('redis');
        $this->onQueue('esb-sales');
    }

    public function handle(EsbSalesService $service): void
    {
        $result = $service->syncSalesByBranchAndDate(
            $this->credentialId,
            $this->branchCode,
            $this->salesDate,
            $this->salesDate,
            $this->forcedOutletId
        );

        Cache::store('redis')->put("sales_sync_item:{$this->syncKey}:{$this->forcedOutletId}:{$this->salesDate}", [
            'status' => 'done',
            'credential_id' => $this->credentialId,
            'branch_code' => $this->branchCode,
            'outlet_id' => $this->forcedOutletId,
            'sales_date' => $this->salesDate,
            'inserted' => (int) ($result['inserted_rows'] ?? $result['inserted'] ?? 0),
            'updated_at' => now()->toDateTimeString(),
        ], now()->addHours(6));
    }

    public function failed(\Throwable $e): void
    {
        Cache::store('redis')->put("sales_sync_item:{$this->syncKey}:{$this->forcedOutletId}:{$this->salesDate}", [
            'status' => 'failed',
            'message' => $e->getMessage(),
            'credential_id' => $this->credentialId,
            'branch_code' => $this->branchCode,
            'outlet_id' => $this->forcedOutletId,
            'sales_date' => $this->salesDate,
            'updated_at' => now()->toDateTimeString(),
        ], now()->addHours(6));
    }
}