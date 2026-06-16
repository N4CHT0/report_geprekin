<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class M_Warehouse extends Model
{
    protected $table = 'tbl_warehouse';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'credential_id',
        'branch_id',
        'nama_warehouse',
        'alamat'
    ];

    public function branch()
    {
        return $this->belongsTo(M_Outlet::class, 'branch_id', 'id');
    }
}
