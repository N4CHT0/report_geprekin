<?php

namespace App\Jobs;

use App\Imports\SalesImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

use App\Models\M_ImportStatus;

class ImportSalesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $statusId;

    public function __construct($filePath, $statusId)
    {
        $this->filePath = $filePath;
        $this->statusId = $statusId;
    }

    public function handle()
    {
        $status = M_ImportStatus::find($this->statusId);
        $status->update(['status' => 'processing']);

        try {
            Excel::import(new SalesImport, $this->filePath);

            $status->update([
                'status' => 'success',
                'message' => 'Data berhasil diimport',
            ]);
        } catch (\Exception $e) {
            $status->update([
                'status' => 'failed',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function failed(\Exception $exception)
    {
        $status = M_ImportStatus::find($this->statusId);
        $status->update([
            'status' => 'failed',
            'message' => $exception->getMessage(),
        ]);
    }
}
