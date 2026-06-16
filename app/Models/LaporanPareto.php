<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanPareto extends Model
{
    use HasFactory;

    protected $table = 'tbl_laporan_pareto';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'outlet_id',
        'tanggal',
        'item_nama',
        'item_varian',
        'total_jumlah',
        'harga_satuan',
        'total_harga',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'total_jumlah' => 'integer',
        'harga_satuan' => 'decimal:2',
        'total_harga' => 'integer',
    ];

    public function outlet()
    {
        return $this->belongsTo(M_Outlet::class, 'outlet_id', 'id');
    }
}
