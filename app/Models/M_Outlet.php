<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class M_Outlet extends Model
{
    use HasFactory;

    protected $table = 'tbl_outlets';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = ['nama_outlet', 'status', 'latitude', 'longitude'];
    public $timestamps = true;

    // Kalau mau proteksi kolom (opsional, kebalikan dari fillable)
    // protected $guarded = ['id'];

    // Casts (opsional biar rapi format data)
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
