<?php

namespace App\Imports;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\RemembersChunkOffset;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\ImportFailed;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class SalesImport implements ToCollection, WithChunkReading, WithEvents, WithHeadingRow, ShouldQueue
{
    use Importable, RemembersChunkOffset;

    protected int $chunkSize = 1000;
    protected int $insertBatchSize = 500;

    protected static bool $loaded = false;
    protected static array $outletKeyMap = [];

    protected string $importKey;
    protected string $fileName;
    protected string $storedPath;

    public int $timeout = 3600;
    public int $tries = 1;

    public function __construct(string $importKey, string $fileName, string $storedPath)
    {
        $this->importKey = $importKey;
        $this->fileName = $fileName;
        $this->storedPath = $storedPath;
    }

    protected function bootCacheOnce(): void
    {
        if (self::$loaded) {
            return;
        }

        $rows = DB::table('tbl_outlets')
            ->select('id', 'outlet_key_fix', 'outlet_key', 'nama_outlet')
            ->get();

        self::$outletKeyMap = [];

        foreach ($rows as $r) {
            if (!empty($r->outlet_key_fix)) {
                $keyFix = $this->normalizeOutletName($r->outlet_key_fix);
                if ($keyFix !== '') {
                    self::$outletKeyMap[$keyFix] = $r->id;
                }
            }

            if (!empty($r->outlet_key)) {
                $key = $this->normalizeOutletName($r->outlet_key);
                if ($key !== '' && !isset(self::$outletKeyMap[$key])) {
                    self::$outletKeyMap[$key] = $r->id;
                }
            }

            if (!empty($r->nama_outlet)) {
                $key2 = $this->normalizeOutletName($r->nama_outlet);
                if ($key2 !== '' && !isset(self::$outletKeyMap[$key2])) {
                    self::$outletKeyMap[$key2] = $r->id;
                }
            }
        }

        self::$loaded = true;
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function () {
                try {
                    $current = Cache::get($this->importKey, []);

                    $failedRows = $current['failedRows'] ?? [];
                    $processedRows = (int) ($current['processedRows'] ?? 0);
                    $insertedRows = (int) ($current['insertedRows'] ?? 0);
                    $skippedRows = (int) ($current['skippedRows'] ?? 0);
                    $failedCount = (int) ($current['failedCount'] ?? 0);
                    $totalItemSubTotal = (float) ($current['totalItemSubTotal'] ?? 0);

                    if (!empty($failedRows)) {
                        $csvPath = 'failed_imports/failed_outlet_' . date('Ymd_His') . '.csv';

                        Storage::disk('local')->put(
                            $csvPath,
                            "row,nama_outlet_excel,sesi_tanggal,reason\n"
                        );

                        foreach ($failedRows as $fail) {
                            $excel = str_replace('"', '""', (string) ($fail['nama_outlet_excel'] ?? ''));
                            $tgl = str_replace('"', '""', (string) ($fail['sesi_tanggal'] ?? ''));
                            $reason = str_replace('"', '""', (string) ($fail['reason'] ?? ''));

                            Storage::disk('local')->append($csvPath, sprintf(
                                "%d,\"%s\",\"%s\",\"%s\"\n",
                                (int) ($fail['row'] ?? 0),
                                $excel,
                                $tgl,
                                $reason
                            ));
                        }
                    }

                    Cache::put($this->importKey, [
                        'status' => 'done',
                        'message' => "Import file {$this->fileName} selesai",
                        'fileName' => $this->fileName,
                        'totalRows' => $processedRows,
                        'processedRows' => $processedRows,
                        'insertedRows' => $insertedRows,
                        'skippedRows' => $skippedRows,
                        'failedCount' => $failedCount,
                        'failedRows' => array_slice($failedRows, 0, 5),
                        'progress' => 100,
                        'totalItemSubTotal' => $totalItemSubTotal,
                    ], now()->addHours(6));

                    if (Storage::disk('local')->exists($this->storedPath)) {
                        Storage::disk('local')->delete($this->storedPath);
                    }
                } catch (\Throwable $e) {
                    Log::error('AfterImport SalesImport error', [
                        'message' => $e->getMessage(),
                        'file' => $this->fileName,
                        'import_key' => $this->importKey,
                    ]);

                    $this->markImportFailed('Terjadi kesalahan saat finalisasi import');
                }
            },

            ImportFailed::class => function (ImportFailed $event) {
                Log::error('SalesImport failed event', [
                    'message' => $event->getException()->getMessage(),
                    'file' => $this->fileName,
                    'import_key' => $this->importKey,
                ]);

                $this->cleanupImportedDates();

                if (Storage::disk('local')->exists($this->storedPath)) {
                    Storage::disk('local')->delete($this->storedPath);
                }

                $this->markImportFailed('Import gagal di tengah proses. Data tanggal terkait dibersihkan.');
            },
        ];
    }

    protected function markImportFailed(string $message): void
    {
        $current = Cache::get($this->importKey, []);

        Cache::put($this->importKey, [
            'status' => 'failed',
            'message' => $message,
            'fileName' => $this->fileName,
            'totalRows' => (int) ($current['totalRows'] ?? 0),
            'processedRows' => (int) ($current['processedRows'] ?? 0),
            'insertedRows' => 0,
            'skippedRows' => (int) ($current['skippedRows'] ?? 0),
            'failedCount' => (int) ($current['failedCount'] ?? 0),
            'failedRows' => array_slice($current['failedRows'] ?? [], 0, 5),
            'progress' => 100,
            'totalItemSubTotal' => 0,
        ], now()->addHours(6));
    }

    protected function cleanupImportedDates(): void
    {
        $current = Cache::get($this->importKey, []);
        $dates = $current['dates'] ?? [];

        if (!empty($dates)) {
            DB::table('tbl_transaksi_perhari')
                ->whereIn('sesi_tanggal', array_values($dates))
                ->delete();
        }
    }

    protected function excelTimeToTime($excelTime): ?string
    {
        if ($excelTime === null || $excelTime === '') {
            return null;
        }

        try {
            if (is_numeric($excelTime)) {
                return ExcelDate::excelToDateTimeObject((float) $excelTime)->format('H:i:s');
            }

            foreach (['H:i:s', 'H:i'] as $format) {
                $dt = \DateTime::createFromFormat($format, (string) $excelTime);
                if ($dt) {
                    return $dt->format('H:i:s');
                }
            }

            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function parseNumber($value): float
    {
        if ($value === null || $value === '') {
            return 0;
        }
    
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }
    
        $value = trim((string) $value);
    
        // hilangkan spasi dan NBSP
        $value = str_replace(["\xC2\xA0", ' '], '', $value);
    
        // kalau format Indonesia: 1.234.567,89
        if (preg_match('/^\d{1,3}(\.\d{3})+(,\d+)?$/', $value)) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
            return (float) $value;
        }
    
        // kalau format US: 1,234,567.89
        if (preg_match('/^\d{1,3}(,\d{3})+(\.\d+)?$/', $value)) {
            $value = str_replace(',', '', $value);
            return (float) $value;
        }
    
        // kalau decimal koma: 123,45
        if (preg_match('/^\d+,\d+$/', $value)) {
            $value = str_replace(',', '.', $value);
            return (float) $value;
        }
    
        // default: hapus koma ribuan
        $value = str_replace(',', '', $value);
    
        return is_numeric($value) ? (float) $value : 0;
    }

    protected function normalizeOutletName($name): string
    {
        $name = strtolower((string) $name);
        $name = str_replace("\xC2\xA0", ' ', $name);
        $name = str_replace('.', '', $name);
        $name = str_replace([',', ';', ':'], ' ', $name);
        $name = str_replace(['(', ')'], ' ', $name);
        $name = preg_replace('/[^a-z0-9\s]/', ' ', $name);
        $name = preg_replace('/\s+/', ' ', $name);

        return trim($name);
    }

    protected function parseDate($dateRaw)
    {
        try {
            if (is_numeric($dateRaw)) {
                return ExcelDate::excelToDateTimeObject((float) $dateRaw)->format('Y-m-d');
            }

            foreach (['Y-m-d', 'd/m/Y', 'd-m-Y'] as $format) {
                try {
                    return Carbon::createFromFormat($format, (string) $dateRaw)->format('Y-m-d');
                } catch (\Throwable $e) {
                }
            }

            return false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function pushFailedRow(
        int $rowNumber,
        string $excelOutlet,
        string $reason,
        $sesiTanggalRaw = null
    ): void {
        $current = Cache::get($this->importKey, []);

        $failedRows = $current['failedRows'] ?? [];
        $failedCount = (int) ($current['failedCount'] ?? 0);

        $failedCount++;

        if (count($failedRows) < 100) {
            $failedRows[] = [
                'row' => $rowNumber,
                'nama_outlet_excel' => $excelOutlet,
                'sesi_tanggal' => $sesiTanggalRaw,
                'reason' => $reason,
            ];
        }

        $current['failedRows'] = $failedRows;
        $current['failedCount'] = $failedCount;

        Cache::put($this->importKey, array_merge([
            'status' => 'processing',
            'message' => "File {$this->fileName} sedang diproses",
            'fileName' => $this->fileName,
            'totalRows' => 0,
            'processedRows' => 0,
            'insertedRows' => 0,
            'skippedRows' => 0,
            'failedCount' => 0,
            'failedRows' => [],
            'progress' => 0,
            'totalItemSubTotal' => 0,
            'dates' => [],
        ], $current), now()->addHours(6));
    }

    protected function touchProgress(
        int $processedIncrement = 0,
        int $insertedIncrement = 0,
        int $skippedIncrement = 0,
        float $subTotalIncrement = 0,
        array $dates = []
    ): void {
        $current = Cache::get($this->importKey, []);

        $processedRows = (int) ($current['processedRows'] ?? 0) + $processedIncrement;
        $insertedRows = (int) ($current['insertedRows'] ?? 0) + $insertedIncrement;
        $skippedRows = (int) ($current['skippedRows'] ?? 0) + $skippedIncrement;
        $failedCount = (int) ($current['failedCount'] ?? 0);
        $failedRows = $current['failedRows'] ?? [];
        $totalItemSubTotal = (float) ($current['totalItemSubTotal'] ?? 0) + $subTotalIncrement;

        $existingDates = $current['dates'] ?? [];
        $mergedDates = array_values(array_unique(array_merge($existingDates, $dates)));

        $progress = 10;
        if ($processedRows > 0) {
            $progress = min(95, 10 + (int) floor($processedRows / 1000));
        }

        Cache::put($this->importKey, [
            'status' => 'processing',
            'message' => "File {$this->fileName} sedang diproses",
            'fileName' => $this->fileName,
            'totalRows' => $processedRows,
            'processedRows' => $processedRows,
            'insertedRows' => $insertedRows,
            'skippedRows' => $skippedRows,
            'failedCount' => $failedCount,
            'failedRows' => array_slice($failedRows, 0, 5),
            'progress' => $progress,
            'totalItemSubTotal' => $totalItemSubTotal,
            'dates' => $mergedDates,
        ], now()->addHours(6));
    }

    public function collection(Collection $rows)
    {
        DB::disableQueryLog();
        $this->bootCacheOnce();

        $chunkOffset = $this->getChunkOffset();

        $batchData = [];
        $localProcessed = 0;
        $localInserted = 0;
        $localSkipped = 0;
        $localSubTotal = 0;
        $localDates = [];

        try {
            foreach ($rows as $index => $row) {
                $rowNumber = $chunkOffset + $index + 2;

                $excelOutlet = trim((string) ($row['nama_outlet'] ?? ''));
                $sesiTanggalRaw = $row['sesi_tanggal'] ?? null;

                $localProcessed++;

                if ($excelOutlet === '') {
                    $this->pushFailedRow($rowNumber, $excelOutlet, 'nama_outlet kosong', $sesiTanggalRaw);
                    continue;
                }

                if (empty($sesiTanggalRaw)) {
                    $this->pushFailedRow($rowNumber, $excelOutlet, 'sesi_tanggal kosong', $sesiTanggalRaw);
                    continue;
                }

                $sesiTanggal = $this->parseDate($sesiTanggalRaw);
                if (!$sesiTanggal) {
                    $this->pushFailedRow($rowNumber, $excelOutlet, 'format sesi_tanggal salah', $sesiTanggalRaw);
                    continue;
                }

                $localDates[] = $sesiTanggal;

                $itemStatus = isset($row['item_status']) ? (int) $row['item_status'] : 1;
                if ($itemStatus === 0) {
                    $localSkipped++;
                    continue;
                }

                $outletKey = $this->normalizeOutletName($excelOutlet);
                $matchedId = self::$outletKeyMap[$outletKey] ?? null;

                if (!$matchedId) {
                    $this->pushFailedRow(
                        $rowNumber,
                        $excelOutlet,
                        'outlet tidak ditemukan (cek outlet_key_fix)',
                        $sesiTanggalRaw
                    );
                    continue;
                }

                $subTotal = $this->parseNumber($row['item_sub_total'] ?? 0);

                $batchData[] = [
                    'nomor' => null,
                    'outlet_id' => $matchedId,
                    'menu_id' => null,
                    'sesi_tanggal' => $sesiTanggal,
                    'tr_waktu' => $this->excelTimeToTime($row['tr_waktu'] ?? null),
                    'tr_metode' => $row['tr_metode'] ?? null,
                    'item_nama' => $row['item_nama'] ?? null,
                    'item_varian' => $row['item_varian'] ?? null,
                    'item_harga' => $this->parseNumber($row['item_harga'] ?? 0),
                    'item_jumlah' => (int) ($row['item_jumlah'] ?? 0),
                    'item_sub_total' => $subTotal,
                    'customer_unit' => (int) ($row['customer_unit'] ?? 0),
                    'item_status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'tr_jam' => null,
                ];

                $localInserted++;
                $localSubTotal += $subTotal;
            }

            if (!empty($batchData)) {
                DB::transaction(function () use ($batchData) {
                    foreach (array_chunk($batchData, $this->insertBatchSize) as $smallBatch) {
                        DB::table('tbl_transaksi_perhari')->insert($smallBatch);
                    }
                });
            }

            $this->touchProgress(
                $localProcessed,
                $localInserted,
                $localSkipped,
                $localSubTotal,
                array_values(array_unique($localDates))
            );
        } catch (\Throwable $e) {
            Log::error('SalesImport chunk error', [
                'message' => $e->getMessage(),
                'file' => $this->fileName,
                'import_key' => $this->importKey,
                'trace' => $e->getTraceAsString(),
            ]);

            $this->cleanupImportedDates();

            if (Storage::disk('local')->exists($this->storedPath)) {
                Storage::disk('local')->delete($this->storedPath);
            }

            $this->markImportFailed('Terjadi kesalahan saat import file. Data tanggal terkait dibersihkan.');

            throw $e;
        }
    }

    public function chunkSize(): int
    {
        return $this->chunkSize;
    }
}