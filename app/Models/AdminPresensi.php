<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;

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
        'password_plain', // Tambahkan field ini
        'pleno_akses',
        'status', // Tambahkan status
    ];

    protected $hidden = [
        'password',
        'password_plain', // Sembunyikan dari JSON response biasa
        'remember_token',
    ];

    protected $casts = [
        'pleno_akses' => 'array',
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
        $this->password_plain = $newPlainPassword;
        $this->save();
        
        return $this;
    }

    /**
     * Cek apakah admin memiliki akses ke pleno tertentu
     */
    public function hasAccessToPleno(int $pleno): bool
    {
        $plenoAkses = $this->pleno_akses ?? [];
        return in_array($pleno, $plenoAkses);
    }

    /**
     * Get akses pleno sebagai string untuk display
     */
    public function getPlenoAksesText(): string
    {
        $plenoAkses = $this->pleno_akses ?? [];
        
        if (empty($plenoAkses)) {
            return 'Tidak ada akses';
        }

        sort($plenoAkses);
        $plenoList = array_map(function($pleno) {
            return "Pleno $pleno";
        }, $plenoAkses);

        return implode(', ', $plenoList);
    }

    /**
     * Get pleno pertama yang bisa diakses (untuk presensi otomatis)
     */
    public function getFirstPlenoAccess(): ?int
    {
        $plenoAkses = $this->pleno_akses ?? [];
        
        if (empty($plenoAkses)) {
            return null;
        }

        sort($plenoAkses);
        return $plenoAkses[0];
    }

    /**
     * Scope untuk hanya admin aktif
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope untuk admin tidak aktif
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Cek apakah admin aktif
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Cek apakah admin tidak aktif
     */
    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }

    /**
     * Aktifkan admin
     */
    public function activate()
    {
        $this->status = 'active';
        $this->save();
        return $this;
    }

    /**
     * Nonaktifkan admin
     */
    public function deactivate()
    {
        $this->status = 'inactive';
        $this->save();
        return $this;
    }
}