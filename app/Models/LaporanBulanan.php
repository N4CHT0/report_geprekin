<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanBulanan extends Model
{
    use HasFactory;

    protected $table = 'tbl_laporan_bulanan';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'outlet_id',
        'tanggal',
        'total_omset',
        'total_non_sales',
        'total_cu',
        'platform',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'total_omset' => 'double',
        'total_non_sales' => 'double',
        'total_cu' => 'integer',
    ];

    public function outlet()
    {
        return $this->belongsTo(M_Outlet::class, 'outlet_id', 'id');
    }
}
