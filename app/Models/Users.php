<?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Model;

// class User extends Authenticatable
// {
//     protected $fillable = [
//         'name',
//         'email',
//         'password',
//     ];
// }

//class Users extends Model
// {
//     protected $table = 'users'; // sesuaikan nama tabel
//     protected $primaryKey = 'id'; // jika PK bukan id
//     public $timestamps = false; // jika tidak pakai created_at
// }
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Users extends Authenticatable
{
     use Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}

// namespace App\Models;

// use Illuminate\Foundation\Auth\User as Authenticatable;

// class Investor extends Authenticatable
// {
//     protected $table = 'users';

//     protected $fillable = [
//         'name',
//         'email',
//         'password',
//     ];
// }
