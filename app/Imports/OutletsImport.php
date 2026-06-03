<?php

namespace App\Imports;

use App\Models\M_Outlet;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class OutletsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new M_Outlet([
            'kode_outlet' => $row['kode_outlet'] ?? null,
            'mitra_id'    => $row['mitra_id'] ?? null,
            'nama_outlet' => $row['nama_outlet'],
            'status'      => $row['status'] ?? 'existing',
            'created_at'  => Carbon::now(),
            'updated_at'  => Carbon::now(),
        ]);
    }
}
