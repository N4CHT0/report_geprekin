<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterOutlet extends Model
{
    use HasFactory;

    protected $table = 'master_outlets';

    protected $fillable = [
        'no_urut',
        'nama_outlet',
        'tanggal_open',
        'tanggal_closed',
        'kota_kab',
        'provinsi',
        'area_manager',
        'gmaps_url',
        'latitude',
        'longitude',
    ];
}
