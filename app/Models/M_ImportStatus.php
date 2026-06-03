<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class M_ImportStatus extends Model
{
    protected $fillable = ['file_name', 'status', 'message'];
}
