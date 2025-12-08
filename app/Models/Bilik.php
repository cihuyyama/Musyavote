<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class Bilik extends Authenticatable
{
    use HasFactory, HasApiTokens;

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
        'username',
        'password',
        'password_plain',
        'status'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'password_plain' // Sembunyikan dari JSON response biasa
    ];

    /**
     * Mutator untuk password_plain
     * Ketika password_plain di-set, otomatis hash ke kolom password
     */
    public function setPasswordPlainAttribute($value)
    {
        $this->attributes['password_plain'] = $value;
        // Otomatis hash password jika value tidak kosong
        if (!empty($value)) {
            $this->attributes['password'] = Hash::make($value);
        }
    }

    public function pemilihan()
    {
        return $this->belongsToMany(Pemilihan::class, 'pemilihan_bilik', 'bilik_id', 'pemilihan_id')
            ->withTimestamps();
    }

    /**
     * Get plain password untuk keperluan display/export
     */
    public function getPasswordPlainAttribute()
    {
        return $this->attributes['password_plain'] ?? null;
    }

    /**
     * Generate new random password
     */
    public function generateNewPassword()
    {
        $randomPassword = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        $this->password_plain = $randomPassword; // Akan trigger setPasswordPlainAttribute
        $this->save();

        return $randomPassword;
    }

    /**
     * Update password dengan plain password baru
     */
    public function updatePassword($newPlainPassword)
    {
        $this->password_plain = $newPlainPassword; // Akan trigger setPasswordPlainAttribute
        $this->save();
        
        return $this;
    }

    /**
     * Check if user provided password matches
     */
    public function checkPassword($plainPassword)
    {
        return Hash::check($plainPassword, $this->password);
    }
}