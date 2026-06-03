<?php

namespace App\Imports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterImport;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ExpenseImport implements ToCollection, WithChunkReading, WithEvents, WithHeadingRow
{
    public array $failedRows = [];
    public int $successCount = 0;
    public int $skippedCount = 0;

    protected int $chunkSize = 5000;
    protected int $insertBatchSize = 1000;

    protected static bool $loaded = false;
    protected static array $outletKeyMap = [];

    protected int $rowOffset = 0;
    protected bool $ignoreFk = false;

    public ?string $failedCsvPath = null;

    public function setIgnoreFk(bool $value): void
    {
        $this->ignoreFk = $value;
    }

    /**
     * ✅ Sesuai screenshot: header ada di baris 1
     */
    public function headingRow(): int
    {
        return 1;
    }

    public function chunkSize(): int
    {
        return $this->chunkSize;
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function () {
                if (empty($this->failedRows)) return;

                $csvPath = 'failed_expense_' . date('Ymd_His') . '.csv';
                $this->failedCsvPath = $csvPath;

                Storage::put($csvPath, "row,branch_excel,date,reason\n");

                foreach ($this->failedRows as $fail) {
                    $branch = str_replace('"', '""', (string)($fail['branch_excel'] ?? ''));
                    $tgl    = str_replace('"', '""', (string)($fail['date'] ?? ''));
                    $reason = str_replace('"', '""', (string)($fail['reason'] ?? ''));

                    Storage::append($csvPath, sprintf(
                        "%d,\"%s\",\"%s\",\"%s\"\n",
                        (int)($fail['row'] ?? 0),
                        $branch,
                        $tgl,
                        $reason
                    ));
                }
            },
        ];
    }

    protected function bootCacheOnce(): void
    {
        if (self::$loaded) return;

        $rows = DB::table('tbl_outlets')
            ->select('id', 'outlet_key', 'nama_outlet')
            ->get();

        self::$outletKeyMap = [];

        foreach ($rows as $r) {
            if (!empty($r->outlet_key)) {
                $k = $this->normalizeOutletName($r->outlet_key);
                if ($k !== '') self::$outletKeyMap[$k] = (int)$r->id;
            }

            if (!empty($r->nama_outlet)) {
                $k2 = $this->normalizeOutletName($r->nama_outlet);
                if ($k2 !== '') self::$outletKeyMap[$k2] = (int)$r->id;
            }
        }

        self::$loaded = true;
    }

    protected function normalizeOutletName($name): string
    {
        $name = strtolower((string)$name);
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
            if ($dateRaw === null || $dateRaw === '') return false;

            if (is_numeric($dateRaw)) {
                return ExcelDate::excelToDateTimeObject((float)$dateRaw)->format('Y-m-d');
            }

            $dateRaw = trim((string)$dateRaw);

            foreach (['Y-m-d', 'd/m/Y', 'd-m-Y'] as $format) {
                try {
                    return Carbon::createFromFormat($format, $dateRaw)->format('Y-m-d');
                } catch (\Throwable $e) {
                    // lanjut
                }
            }
        } catch (\Throwable $e) {
            return false;
        }

        return false;
    }

    protected function parseNumber($value): float
    {
        if ($value === null || $value === '') return 0;
        if (is_numeric($value)) return (float)$value;

        // "10.000,00" => "10000.00"
        $clean = str_replace([' ', '.'], '', (string)$value);
        $clean = str_replace(',', '.', $clean);

        return (float)$clean;
    }

    protected function fail(int $rowNumber, string $branchExcel, string $reason, $dateRaw = null): void
    {
        $this->failedRows[] = [
            'row' => $rowNumber,
            'branch_excel' => $branchExcel,
            'date' => $dateRaw,
            'reason' => $reason,
        ];
    }

    protected function findOutletId(string $excelBranch): ?int
    {
        $k1 = $this->normalizeOutletName($excelBranch);
        if (isset(self::$outletKeyMap[$k1])) return self::$outletKeyMap[$k1];

        $beforeComma = trim(explode(',', $excelBranch)[0] ?? '');
        if ($beforeComma !== '') {
            $k2 = $this->normalizeOutletName($beforeComma);
            if (isset(self::$outletKeyMap[$k2])) return self::$outletKeyMap[$k2];
        }

        return null;
    }

    protected function flushBatch(array &$batchData): void
    {
        if (empty($batchData)) return;

        try {
            DB::table('tbl_expenses')->insert($batchData);
            $this->successCount += count($batchData);
            $batchData = [];
        } catch (\Throwable $e) {
            Log::error('Insert tbl_expenses failed', [
                'error' => $e->getMessage(),
                'first_row' => $batchData[0] ?? null,
                'rows_count' => count($batchData),
            ]);
            throw $e;
        }
    }

    public function collection(Collection $rows)
    {
        DB::disableQueryLog();
        $this->bootCacheOnce();

        $baseRow = $this->rowOffset;
        $batchData = [];

        DB::beginTransaction();

        try {
            if ($this->ignoreFk) DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::statement('SET UNIQUE_CHECKS=0;');

            foreach ($rows as $index => $row) {
                // headerRow=1, jadi data dimulai baris 2
                $rowNumber = $baseRow + $index + $this->headingRow() + 1;

                // Dengan WithHeadingRow: "Branch" => "branch", "Amount" => "amount"
                $excelBranch = trim((string)($row['branch'] ?? $row['nama_outlet'] ?? ''));
                $dateRaw     = $row['date'] ?? $row['expense_date'] ?? null;
                $purpose     = trim((string)($row['purpose'] ?? ''));
                $note        = isset($row['note']) ? trim((string)$row['note']) : null;
                $amountRaw   = $row['amount'] ?? null;

                // ✅ Skip baris kosong
                if ($excelBranch === '' && ($dateRaw === null || $dateRaw === '') && $purpose === '') {
                    $this->skippedCount++;
                    continue;
                }

                if ($excelBranch === '') {
                    $this->fail($rowNumber, $excelBranch, 'branch kosong', $dateRaw);
                    continue;
                }

                $expenseDate = $this->parseDate($dateRaw);
                if (!$expenseDate) {
                    $this->fail($rowNumber, $excelBranch, 'format date salah / date kosong', $dateRaw);
                    continue;
                }

                if ($purpose === '') {
                    $this->fail($rowNumber, $excelBranch, 'purpose kosong', $expenseDate);
                    continue;
                }

                $outletId = $this->findOutletId($excelBranch);
                if (!$outletId) {
                    $this->fail($rowNumber, $excelBranch, 'outlet tidak ditemukan (cek outlet_key/nama_outlet)', $expenseDate);
                    continue;
                }

                // ✅ kalau kolom amount tidak kebaca, jadikan FAIL (biar nggak silent SKIP)
                if ($amountRaw === null || $amountRaw === '') {
                    $this->fail($rowNumber, $excelBranch, 'amount kosong / header amount tidak terbaca', $expenseDate);
                    continue;
                }

                $amount = $this->parseNumber($amountRaw);
                if ($amount <= 0) {
                    $this->skippedCount++;
                    continue;
                }

                $now = now();
                $batchData[] = [
                    'outlet_id'     => $outletId,
                    'expense_date'  => $expenseDate,
                    'purpose'       => $purpose,
                    'note'          => $note,
                    'amount'        => $amount,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];

                if (count($batchData) >= $this->insertBatchSize) {
                    $this->flushBatch($batchData);
                }
            }

            $this->flushBatch($batchData);

            DB::statement('SET UNIQUE_CHECKS=1;');
            if ($this->ignoreFk) DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            DB::commit();
        } catch (\Throwable $e) {
            try {
                DB::statement('SET UNIQUE_CHECKS=1;');
                if ($this->ignoreFk) DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            } catch (\Throwable $restoreErr) {}

            DB::rollBack();
            throw $e;
        } finally {
            $this->rowOffset += $rows->count();
        }
    }
}