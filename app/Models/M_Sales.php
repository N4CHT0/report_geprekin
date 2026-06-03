<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class M_Sales extends Model
{
    use HasFactory;

    // Nama tabel
    protected $table = 'tbl_transaksi_perhari';

    // Primary Key
    protected $primaryKey = 'id';

    // Tipe PK auto increment
    public $incrementing = true;
    protected $keyType = 'int';

    // Kolom yang bisa diisi (fillable)
    protected $fillable = [
        'nomor',
        'outlet_id',
        'sesi_tanggal',
        'tr_waktu',
        'tr_metode',
        'item_nama',
        'item_varian',
        'item_harga',
        'item_jumlah',
        'item_sub_total',
        'customer_unit',
        'item_status',
    ];

    // Cast agar otomatis jadi tipe data sesuai
    protected $casts = [
        'sesi_tanggal'  => 'date',
        'tr_waktu'      => 'datetime:H:i:s',
        'item_harga'    => 'integer',
        'item_jumlah'   => 'integer',
        'item_sub_total' => 'integer',
    ];

    // Relasi ke Outlet
    public function outlet()
    {
        return $this->belongsTo(M_Outlet::class, 'outlet_id', 'id');
    }
}
