<?php

namespace App\Jobs;

use App\Services\EsbSalesService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncEsbSalesOutlet implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 0;
    public int $tries = 1;

    protected int $outletId;
    protected string $start;
    protected string $end;

    public function __construct(int $outletId, string $start, string $end)
    {
        $this->outletId = $outletId;
        $this->start = $start;
        $this->end = $end;

        $this->onConnection('redis');
    }

    public function handle(EsbSalesService $service): void
    {
        $service->syncSalesByOutletId(
            $this->outletId,
            $this->start,
            $this->end
        );
    }
}