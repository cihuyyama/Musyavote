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
        'pleno_akses',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'pleno_akses' => 'array', // Cast ke array
    ];

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
}