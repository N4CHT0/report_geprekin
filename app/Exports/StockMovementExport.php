<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class StockMovementExport implements FromCollection, WithHeadings, WithMapping
{
    protected $movements;

    // Kita gunakan constructor agar bisa menerima data yang sudah difilter dari Controller
    public function __construct($movements)
    {
        $this->movements = $movements;
    }

    public function collection()
    {
        return $this->movements;
    }

    // Mengatur Header (Baris Pertama Excel)
    public function headings(): array
    {
        return [
            'Tanggal',
            'Waktu',
            'Nama Produk',
            'Kode Produk',
            'Gudang',
            'Tipe',
            'Qty',
            'Unit',
            'Stok Sebelum',
            'Stok Sesudah',
            'Total Nilai (Rp)',
            'Keterangan',
            'Tipe Referensi',
            'ID Referensi'
        ];
    }

    // Mengatur isi baris per data
    public function map($movement): array
    {
        // Menentukan tanda plus/minus sesuai tipe (seperti di Blade)
        $tandaQty = ($movement->tipe === 'KELUAR') ? '-' : '+';

        return [
            Carbon::parse($movement->created_at)->format('d M Y'),
            Carbon::parse($movement->created_at)->format('H:i'),
            $movement->nama_bahan,
            $movement->product_code,
            $movement->nama_warehouse,
            $movement->tipe,
            $tandaQty . $movement->jumlah,
            $movement->nama_unit,
            $movement->stok_sebelum,
            $movement->stok_sesudah,
            $movement->total_nilai,
            $movement->keterangan ?? '-',
            $movement->reference_type,
            $movement->reference_id,
        ];
    }
}