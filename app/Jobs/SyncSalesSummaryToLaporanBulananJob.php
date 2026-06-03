<?php

namespace App\Jobs;

use App\Services\EsbSalesService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncSalesSummaryToLaporanBulananJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $salesDate;

    public int $timeout = 1800;
    public int $tries = 1;

    public function __construct(string $salesDate)
    {
        $this->salesDate = $salesDate;

        $this->onConnection('redis');
        $this->onQueue('esb-sales');
    }

    public function handle(EsbSalesService $service): void
    {
        $affected = $service->syncDailySummaryToLaporanBulanan($this->salesDate);

        Log::info('SYNC SALES SUMMARY TO LAPORAN BULANAN JOB DONE', [
            'sales_date'    => $this->salesDate,
            'affected_rows' => $affected,
        ]);
    }
}