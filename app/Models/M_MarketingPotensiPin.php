<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class M_MarketingPotensiPin extends Model
{
    use HasFactory;

    protected $table = 'marketing_potensi_pins';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'area_potensi_id',
        'latitude',
        'longitude',
        'address',
        'status' // LEAD, SURVEYED, REJECTED
    ];

    public function area()
    {
        return $this->belongsTo(M_MarketingAreaPotensi::class, 'area_potensi_id', 'id');
    }
}
