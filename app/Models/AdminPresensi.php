<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class AdminPresensi extends Authenticatable
{
    use HasFactory, HasUlids, HasApiTokens;

    protected $table = 'admin_kehadiran';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nama',
        'username',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}