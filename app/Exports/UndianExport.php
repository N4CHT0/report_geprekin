<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class UndianExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(
        protected ?string $tanggalMulai,
        protected ?string $tanggalSelesai,
        protected ?string $outletId
    ) {}

    public function headings(): array
    {
        // HEADER = NAMA KOLOM / ALIAS DARI QUERY
        return [
            'id',
            'outlet_id',
            'outlet',
            'nama_lengkap',
            'no_telp',
            'nomor_struk',
            'total_belanja',
            'nomor_undian',
            'tanggal_struk',
            'periode',
        ];
    }

    public function collection(): Collection
    {
        $q = DB::table('tbl_undian_struk as us')
            ->leftJoin('tbl_outlets as o', 'o.id', '=', 'us.outlet_id')
            ->select([
                'us.id',
                'us.outlet_id',
                DB::raw("COALESCE(o.nama_outlet, '-') as outlet"),
                'us.nama_lengkap',
                'us.no_telp',
                'us.nomor_struk',
                'us.total_belanja',
                'us.nomor_undian',
                'us.tanggal_struk',
                'us.periode',
            ])
            ->orderBy('us.tanggal_struk', 'desc');

        if ($this->tanggalMulai) {
            $q->whereDate('us.tanggal_struk', '>=', $this->tanggalMulai);
        }

        if ($this->tanggalSelesai) {
            $q->whereDate('us.tanggal_struk', '<=', $this->tanggalSelesai);
        }

        if ($this->outletId) {
            $q->where('us.outlet_id', $this->outletId);
        }

        return $q->get();
    }
}