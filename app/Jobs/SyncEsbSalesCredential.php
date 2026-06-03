<?php

namespace App\Jobs;

use App\Services\EsbSalesService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncEsbSalesCredential implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 0;
    public int $tries = 1;

    protected int $credentialId;
    protected string $start;
    protected string $end;

    public function __construct(int $credentialId, string $start, string $end)
    {
        $this->credentialId = $credentialId;
        $this->start = $start;
        $this->end = $end;
    }

    public function handle(EsbSalesService $service): void
    {
        $service->syncSalesByCredentialId(
            $this->credentialId,
            $this->start,
            $this->end
        );
    }
}