<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterBoq extends Model
{
    use HasFactory;

    protected $table = 'master_boqs';

    protected $fillable = [
        'kategori',
        'slug_id',
        'nama_item',
        'harga_satuan',
        'is_active',
    ];
}
