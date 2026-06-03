<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ProcessSalesPreviewJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1200;
    public $tries = 1;

    protected $storedPath;
    protected $fileName;
    protected $previewKey;

    public function __construct(string $storedPath, string $fileName, string $previewKey)
    {
        $this->storedPath = $storedPath;
        $this->fileName = $fileName;
        $this->previewKey = $previewKey;
    }

    public function handle(): void
    {
        Cache::put($this->previewKey, [
            'status' => 'processing',
            'message' => "Preview file {$this->fileName} sedang diproses",
            'validRowsCount' => 0,
            'failedRows' => [],
            'progress' => 25,
        ], now()->addHours(6));

        try {
            $fullPath = Storage::disk('local')->path($this->storedPath);

            $rows = Excel::toArray([], $fullPath);
            $sheet = $rows[0] ?? [];

            if (empty($sheet)) {
                Cache::put($this->previewKey, [
                    'status' => 'done',
                    'message' => 'File kosong',
                    'validRowsCount' => 0,
                    'failedRows' => [],
                    'progress' => 100,
                ], now()->addHours(6));

                return;
            }

            $header = array_map(function ($value) {
                return strtolower(trim((string) $value));
            }, $sheet[0] ?? []);

            $dataRows = array_slice($sheet, 1, 100);

            $validCount = 0;
            $failedRows = [];
            $totalRows = count($dataRows);

            foreach ($dataRows as $index => $row) {
                $mapped = [];

                foreach ($header as $colIndex => $key) {
                    $mapped[$key] = $row[$colIndex] ?? null;
                }

                $namaOutlet = trim((string) ($mapped['nama_outlet'] ?? ''));
                $sesiTanggal = $mapped['sesi_tanggal'] ?? null;

                if ($namaOutlet !== '' && !empty($sesiTanggal)) {
                    $validCount++;
                } else {
                    $failedRows[] = [
                        'row' => $index + 2,
                        'nama_outlet_excel' => $namaOutlet,
                        'reason' => 'nama_outlet / sesi_tanggal invalid',
                    ];
                }

                if (($index + 1) % 20 === 0 || ($index + 1) === $totalRows) {
                    $progress = $totalRows > 0
                        ? min(95, (int) round((($index + 1) / $totalRows) * 100))
                        : 95;

                    Cache::put($this->previewKey, [
                        'status' => 'processing',
                        'message' => "Preview file {$this->fileName} sedang diproses",
                        'validRowsCount' => $validCount,
                        'failedRows' => array_slice($failedRows, 0, 5),
                        'progress' => $progress,
                    ], now()->addHours(6));
                }
            }

            Cache::put($this->previewKey, [
                'status' => 'done',
                'message' => 'Preview berhasil',
                'validRowsCount' => $validCount,
                'failedRows' => array_slice($failedRows, 0, 5),
                'progress' => 100,
            ], now()->addHours(6));

        } catch (\Throwable $e) {
            Log::error('Preview import sales error', [
                'message' => $e->getMessage(),
                'file' => $this->fileName,
                'trace' => $e->getTraceAsString(),
            ]);

            Cache::put($this->previewKey, [
                'status' => 'failed',
                'message' => 'Terjadi kesalahan saat preview file',
                'error' => $e->getMessage(),
                'progress' => 100,
            ], now()->addHours(6));
        } finally {
            if (Storage::disk('local')->exists($this->storedPath)) {
                Storage::disk('local')->delete($this->storedPath);
            }
        }
    }
}