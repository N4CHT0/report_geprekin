<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SummaryJamOutlet extends Model
{
    use HasFactory;

    protected $table = 'tbl_summary_jam_outlet';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'outlet_id',
        'tanggal',
        'jam',
        'total_transaksi',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam' => 'integer',
        'total_transaksi' => 'integer',
    ];

    public function outlet()
    {
        return $this->belongsTo(M_Outlet::class, 'outlet_id', 'id');
    }
}
