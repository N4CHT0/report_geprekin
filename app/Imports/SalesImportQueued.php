<?php

namespace App\Imports;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\ImportFailed;

class SalesImportQueued extends SalesImport implements ShouldQueue, WithEvents
{
    public int $timeout = 1200;
    public int $tries = 1;

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function (AfterImport $event) {
                Cache::forget('sales_chart');
                Cache::forget('sales_dashboard');

                Log::info('Queued import tbl_transaksi_perhari selesai', [
                    'file_path'  => $this->filePath,
                    'batch_code' => $this->batchCode,
                ]);
            },

            ImportFailed::class => function (ImportFailed $event) {
                Log::error('Queued import tbl_transaksi_perhari gagal', [
                    'file_path'  => $this->filePath,
                    'batch_code' => $this->batchCode,
                    'message'    => $event->getException()->getMessage(),
                ]);
            },
        ];
    }
}