<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanService extends Model
{
    use HasFactory;

    protected $table = 'tbl_laporan_service';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'outlet_id',
        'tanggal',
        'service_type',
        'total_count',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'total_count' => 'integer',
    ];

    public function outlet()
    {
        return $this->belongsTo(M_Outlet::class, 'outlet_id', 'id');
    }
}
