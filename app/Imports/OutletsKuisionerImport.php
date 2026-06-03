<?php

namespace App\Imports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class OutletsKuisionerImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $nama = trim($row['nama_outlet'] ?? '');
            if ($nama === '') {
                continue; // skip baris tanpa nama
            }

            // pastikan latitude & longitude null kalau kosong
            $lat = trim($row['latitude'] ?? '');
            $lng = trim($row['longitude'] ?? '');

            DB::table('tbl_outlet_kuisioner')->insert([
                'nama_outlet' => $nama,
                'alamat' => trim($row['alamat'] ?? null),
                'latitude' => $lat !== '' ? (float) $lat : null,
                'longitude' => $lng !== '' ? (float) $lng : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
