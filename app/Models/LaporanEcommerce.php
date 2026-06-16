<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanEcommerce extends Model
{
    use HasFactory;

    protected $table = 'tbl_laporan_ecommerce';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'outlet_id',
        'tanggal',
        'item_varian',
        'total_jumlah',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'total_jumlah' => 'integer',
    ];

    public function outlet()
    {
        return $this->belongsTo(M_Outlet::class, 'outlet_id', 'id');
    }
}
