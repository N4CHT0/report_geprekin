<?php

namespace App\Jobs;

use App\Exports\QcrExport;
use App\Http\Controllers\QCRController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Throwable;

class GenerateQcrExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900;
    public int $tries = 1;

    public function __construct(public int $exportId)
    {
    }

    public function handle(): void
    {
        $job = DB::table('qcr_export_jobs')->where('id', $this->exportId)->first();

        if (! $job) {
            return;
        }

        DB::table('qcr_export_jobs')->where('id', $this->exportId)->update([
            'status' => 'processing',
            'processed_outlet' => 0,
            'error_message' => null,
            'updated_at' => now(),
        ]);

        try {
            $request = Request::create('/queue/qcr-export', 'GET', [
                'outlet_id' => (string) $job->outlet_id,
                'start_date' => (string) $job->start_date,
                'end_date' => (string) $job->end_date,
                'filter_applied' => 1,
            ]);

            $data = app(QCRController::class)->buildQcrExportPayload($request);

            DB::table('qcr_export_jobs')->where('id', $this->exportId)->update([
                'total_outlet' => 1,
                'processed_outlet' => 0,
                'updated_at' => now(),
            ]);

            $dir = 'qcr_exports';
            Storage::disk('local')->makeDirectory($dir);

            $filename = 'QCR_' . $job->start_date . '_to_' . $job->end_date . '_job_' . $this->exportId . '.xlsx';
            $path = $dir . '/' . $filename;

            Excel::store(
                new QcrExport($data),
                $path,
                'local',
                \Maatwebsite\Excel\Excel::XLSX
            );

            DB::table('qcr_export_jobs')->where('id', $this->exportId)->update([
                'status' => 'done',
                'file_path' => $path,
                'total_outlet' => 1,
                'processed_outlet' => 1,
                'updated_at' => now(),
            ]);
        } catch (Throwable $e) {
            DB::table('qcr_export_jobs')->where('id', $this->exportId)->update([
                'status' => 'failed',
                'error_message' => mb_substr($e->getMessage(), 0, 1000),
                'updated_at' => now(),
            ]);

            throw $e;
        }
    }
}
