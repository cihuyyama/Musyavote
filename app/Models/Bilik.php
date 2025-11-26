<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Bilik extends Model
{
    use HasFactory;

    protected $table = 'bilik';

    // Konfigurasi ULID primary key
    protected $keyType = 'string';
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::ulid();
            }
        });
    }

    protected $fillable = [
        'nama',
        'status',
        'username',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function pemilihan()
    {
        return $this->belongsToMany(Pemilihan::class, 'pemilihan_bilik', 'bilik_id', 'pemilihan_id')
            ->withTimestamps();
    }
}
