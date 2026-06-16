<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class M_MarketingAreaPotensi extends Model
{
    use HasFactory;

    protected $table = 'marketing_area_potensis';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'provinsi',
        'kota',
        'kecamatan',
        'existing_count',
        'sehat_target',
        'agresif_target',
        'traffic_generator',
        'zona_prioritas',
        'latitude',
        'longitude'
    ];
}
