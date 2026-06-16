<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanPayment extends Model
{
    use HasFactory;

    protected $table = 'tbl_laporan_payment';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'outlet_id',
        'tanggal',
        'payment_method',
        'total_amount',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'total_amount' => 'double',
    ];

    public function outlet()
    {
        return $this->belongsTo(M_Outlet::class, 'outlet_id', 'id');
    }
}
