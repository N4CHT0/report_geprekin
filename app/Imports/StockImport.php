<?php

namespace App\Imports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StockImport implements ToCollection, WithHeadingRow
{
    protected $errors;

    public function __construct(&$errors = [])
    {
        $this->errors = &$errors;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $namaOutlet = trim($row['nama_outlet'] ?? '');
            $namaBahan  = trim($row['nama_barang'] ?? '');
            $tanggalInput = $row['tanggal'] ?? null;

            if ($namaOutlet === '' || $namaBahan === '') {
                $this->errors[] = "❌ Data tidak lengkap: Outlet/Bahan kosong.";
                continue;
            }

            // Cari outlet
            $outletId = DB::table('tbl_outlets')->where('nama_outlet', $namaOutlet)->value('id');
            if (!$outletId) {
                $this->errors[] = "⚠️ Outlet tidak ditemukan: {$namaOutlet}";
                continue;
            }

            // Cari bahan (fuzzy matching)
            $bahanList = DB::table('tbl_bahan')->pluck('id', 'nama_bahan')->toArray();
            $bahanId = null;
            $closestName = null;
            $shortest = -1;

            foreach ($bahanList as $namaDB => $idDB) {
                $distance = levenshtein(strtolower($namaBahan), strtolower($namaDB));
                if ($distance === 0) {
                    $bahanId = $idDB;
                    $closestName = $namaDB;
                    $shortest = 0;
                    break;
                }
                if ($distance <= 3 && ($distance < $shortest || $shortest < 0)) {
                    $bahanId = $idDB;
                    $closestName = $namaDB;
                    $shortest = $distance;
                }
            }

            // Kalau belum ada, auto insert
            if (!$bahanId) {
                $bahanId = DB::table('tbl_bahan')->insertGetId([
                    'nama_bahan' => $namaBahan,
                    'satuan' => $row['sat'] ?? 'PCS',
                    'konversi' => 1,
                    'isi_per_unit' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->errors[] = "✅ Bahan baru ditambahkan: {$namaBahan}";
            } elseif ($shortest > 0) {
                $this->errors[] = "ℹ️ Nama mirip: '{$namaBahan}' cocok dengan '{$closestName}' (jarak: {$shortest})";
            }

            // Konversi tanggal dari Excel
            if (is_numeric($tanggalInput)) {
                $tanggal = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tanggalInput))
                    ->format('Y-m-d');
            } else {
                try {
                    $tanggal = Carbon::parse($tanggalInput)->format('Y-m-d');
                } catch (\Exception $e) {
                    $tanggal = now()->format('Y-m-d');
                }
            }

            // Insert data stock
            try {
                DB::table('tbl_stock')->insert([
                    'outlet_id' => $outletId,
                    'bahan_id' => $bahanId,
                    'tanggal' => $tanggal,
                    'shift' => $row['shift'] ?? 1,
                    'satuan' => $row['sat'] ?? 'PCS',
                    'opening_stock' => $row['open_stock'] ?? 0,
                    'purchase_in' => $row['purchase_in'] ?? 0,
                    'mutasi_in' => $row['mutasi_in'] ?? 0,
                    'mutasi_out' => $row['mutasi_out'] ?? 0,
                    'used_qty' => $row['actual_used'] ?? 0,
                    'waste_product' => $row['waste_product'] ?? 0,
                    'waste_bahan' => $row['waste_bahan'] ?? 0,
                    'waste_tepung' => $row['waste_tepung'] ?? 0,
                    'ending_stock' => $row['ending_stock'] ?? 0,
                    'actual_tepung' => $row['actual_tepung'] ?? 0,
                    'uang_plus' => $row['uang_plus'] ?? 0,
                    'keterangan' => $row['keterangan'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Exception $e) {
                $this->errors[] = "❌ Gagal insert stok: {$namaOutlet} - {$namaBahan} ({$e->getMessage()})";
            }
        }
    }
}