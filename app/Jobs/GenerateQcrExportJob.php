<?php

namespace App\Jobs;

use App\Exports\QcrExport;
use App\Http\Controllers\QCRController;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GenerateQcrExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 7200;
    public int $tries = 1;

    public function __construct(public int $jobId)
    {
    }

    public function handle(): void
    {
        $job = DB::table('qcr_export_jobs')->where('id', $this->jobId)->first();

        if (! $job) {
            return;
        }

        DB::table('qcr_export_jobs')->where('id', $this->jobId)->update([
            'status' => 'running',
            'started_at' => now(),
            'error_message' => null,
            'updated_at' => now(),
        ]);

        try {
            if ((string) $job->outlet_id === 'all') {
                $this->exportAllOutletSummaryPerTanggal($job);
            } else {
                $this->exportSingleOutletDetail($job);
            }
        } catch (\Throwable $e) {
            DB::table('qcr_export_jobs')->where('id', $this->jobId)->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'finished_at' => now(),
                'updated_at' => now(),
            ]);

            throw $e;
        }
    }

    private function exportSingleOutletDetail(object $job): void
    {
        $request = new Request([
            'outlet_id' => (string) $job->outlet_id,
            'start_date' => (string) $job->start_date,
            'end_date' => (string) $job->end_date,
        ]);

        $data = app(QCRController::class)->buildQcrData($request);

        $filename = 'QCR_' . strtoupper((string) $job->outlet_id) . '_' . $job->start_date . '_to_' . $job->end_date . '.xlsx';
        $path = 'qcr_exports/' . $filename;

        Excel::store(new QcrExport($data), $path, 'local');

        DB::table('qcr_export_jobs')->where('id', $this->jobId)->update([
            'status' => 'done',
            'processed_outlet' => 1,
            'total_outlet' => 1,
            'file_path' => $path,
            'finished_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function exportAllOutletSummaryPerTanggal(object $job): void
    {
        $start = Carbon::parse($job->start_date)->startOfDay();
        $end = Carbon::parse($job->end_date)->startOfDay();
        $totalDays = $start->diffInDays($end) + 1;

        DB::table('qcr_export_jobs')->where('id', $this->jobId)->update([
            'total_outlet' => $totalDays,
            'processed_outlet' => 0,
            'updated_at' => now(),
        ]);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Summary All Outlet');

        $headers = [
            'Tanggal',
            'Total Sales',
            'HPP',
            'HPP %',
            'Gross Profit',
            'Gross Profit %',
            'Waste',
            'Waste %',
            'Selisih Loss',
            'Selisih %',
            'Quality Cost',
            'Quality Cost %',
        ];

        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:L1')->getFont()->setBold(true);
        $sheet->getStyle('A1:L1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEFEFEF');
        $sheet->getStyle('A1:L1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $rowNum = 2;
        $processed = 0;

        $totalSales = 0;
        $totalHpp = 0;
        $totalProfit = 0;
        $totalWaste = 0;
        $totalSelisih = 0;
        $totalQc = 0;

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $dateYmd = $date->format('Y-m-d');

            $request = new Request([
                'outlet_id' => 'all',
                'start_date' => $dateYmd,
                'end_date' => $dateYmd,
            ]);

            $data = app(QCRController::class)->buildQcrData($request);
            $summary = $data['summary'] ?? [];

            $sales = (float) ($summary['sales'] ?? 0);
            $hpp = (float) ($summary['hpp'] ?? 0);
            $profit = (float) ($summary['profit'] ?? 0);
            $waste = (float) ($summary['waste'] ?? 0);
            $selisih = (float) ($summary['selisih_loss'] ?? 0);
            $qc = (float) ($summary['quality_cost'] ?? 0);

            $sheet->fromArray([
                $dateYmd,
                $sales,
                $hpp,
                (float) ($summary['hpp_percent'] ?? 0),
                $profit,
                (float) ($summary['profit_percent'] ?? 0),
                $waste,
                (float) ($summary['waste_percent'] ?? 0),
                $selisih,
                (float) ($summary['selisih_percent'] ?? 0),
                $qc,
                (float) ($summary['quality_cost_percent'] ?? 0),
            ], null, 'A' . $rowNum);

            $totalSales += $sales;
            $totalHpp += $hpp;
            $totalProfit += $profit;
            $totalWaste += $waste;
            $totalSelisih += $selisih;
            $totalQc += $qc;

            $rowNum++;
            $processed++;

            DB::table('qcr_export_jobs')->where('id', $this->jobId)->update([
                'processed_outlet' => $processed,
                'updated_at' => now(),
            ]);

            unset($data, $summary);
            gc_collect_cycles();
        }

        $sheet->fromArray([
            'TOTAL',
            $totalSales,
            $totalHpp,
            $totalSales > 0 ? round(($totalHpp / $totalSales) * 100, 2) : 0,
            $totalProfit,
            $totalSales > 0 ? round(($totalProfit / $totalSales) * 100, 2) : 0,
            $totalWaste,
            $totalSales > 0 ? round(($totalWaste / $totalSales) * 100, 2) : 0,
            $totalSelisih,
            $totalSales > 0 ? round(($totalSelisih / $totalSales) * 100, 2) : 0,
            $totalQc,
            $totalSales > 0 ? round(($totalQc / $totalSales) * 100, 2) : 0,
        ], null, 'A' . $rowNum);

        $sheet->getStyle('A' . $rowNum . ':L' . $rowNum)->getFont()->setBold(true);
        $sheet->getStyle('A1:L' . $rowNum)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->getStyle('B2:C' . $rowNum)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('E2:E' . $rowNum)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('G2:G' . $rowNum)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('I2:I' . $rowNum)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('K2:K' . $rowNum)->getNumberFormat()->setFormatCode('#,##0');

        $dir = storage_path('app/qcr_exports');
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $filename = 'QCR_SUMMARY_ALL_OUTLET_' . $job->start_date . '_to_' . $job->end_date . '.xlsx';
        $relativePath = 'qcr_exports/' . $filename;
        $fullPath = storage_path('app/' . $relativePath);

        $writer = new Xlsx($spreadsheet);
        $writer->save($fullPath);

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet, $writer);
        gc_collect_cycles();

        DB::table('qcr_export_jobs')->where('id', $this->jobId)->update([
            'status' => 'done',
            'processed_outlet' => $totalDays,
            'total_outlet' => $totalDays,
            'file_path' => $relativePath,
            'finished_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
