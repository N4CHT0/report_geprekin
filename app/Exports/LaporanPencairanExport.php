<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class LaporanPencairanExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    /**
     * Data mentah dari database.
     */
    public function collection()
    {
        return DB::table('tbl_pencairan_poin as p')
            ->join('tbl_user_responden as u', 'p.id_user_responden', '=', 'u.id')
            ->leftJoin('tbl_hadiah as h', 'p.id_hadiah', '=', 'h.id')
            ->select(
                'p.id',
                'u.nama_lengkap as nama_responden',
                'u.kode_reedem',
                'u.email',
                'u.nomor_telp',
                'h.nama_hadiah',
                'h.tipe as tipe_hadiah',
                'h.poin_dibutuhkan',
                'p.jumlah_poin',
                'p.metode',
                'p.status',
                'p.expired_date',
                'p.created_at'
            )
            ->orderByDesc('p.created_at')
            ->get();
    }

    /**
     * Mapping per baris ke kolom Excel.
     */
    public function map($row): array
    {
        return [
            // 1. ID pencairan
            $row->id,

            // 2. Nama responden (bukan ID user)
            $row->nama_responden,

            // 3. Kode redeem
            $row->kode_reedem,

            // 4. Email
            $row->email,

            // 5. No. Telp
            $row->nomor_telp,

            // 6. Nama hadiah
            $row->nama_hadiah,

            // 7. Tipe hadiah
            $row->tipe_hadiah,

            // 8. Poin dibutuhkan hadiah
            $row->poin_dibutuhkan,

            // 9. Jumlah poin yang ditukar
            $row->jumlah_poin,

            // 10. Metode
            $row->metode,

            // 11. Status
            ucfirst($row->status),

            // 12. Expired Date
            $row->expired_date
                ? Carbon::parse($row->expired_date)->format('Y-m-d H:i')
                : '',

            // 13. Tanggal Pencairan
            $row->created_at
                ? Carbon::parse($row->created_at)->format('Y-m-d H:i')
                : '',
        ];
    }

    /**
     * Header kolom Excel.
     */
    public function headings(): array
    {
        return [
            'ID',
            'Nama Responden',
            'Kode Redeem',
            'Email',
            'No. Telp',
            'Nama Hadiah',
            'Tipe Hadiah',
            'Poin Dibutuhkan Hadiah',
            'Jumlah Poin Ditukar',
            'Metode',
            'Status',
            'Expired Date',
            'Tanggal Pencairan',
        ];
    }
}