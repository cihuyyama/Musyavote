<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peserta extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'peserta';
    
    public $incrementing = false;
    
    protected $keyType = 'string';
    
    protected $fillable = [
        'foto',
        'nama',
        'asal_pimpinan',
        'jenis_kelamin',
        'status',
    ];

    public function calon()
    {
        return $this->hasOne(Calon::class); 
    }
    
    // Metode untuk menampilkan status calon saat ini (Opsional)
    public function getJabatanCalonAttribute()
    {
        return $this->calon->pluck('jabatan')->implode(', ');
    }

    // Relasi: Peserta memiliki satu rekor Kehadiran
    public function kehadiran()
    {
        return $this->hasOne(Kehadiran::class);
    }
    
    /**
     * Mengecek apakah peserta layak memilih dalam Pemilihan tertentu 
     * berdasarkan syarat minimal kehadiran Pemilihan tersebut.
     */
    public function isEligibleToVote(Pemilihan $pemilihan): bool
    {
        if (!$this->relationLoaded('kehadiran')) {
            $this->load('kehadiran');
        }
        
        // Memastikan model kehadiran tersedia
        $totalKehadiranPeserta = $this->kehadiran ? $this->kehadiran->total_kehadiran : 0;
        $syaratMinimal = $pemilihan->minimal_kehadiran;

        return $totalKehadiranPeserta >= $syaratMinimal;
    }
}
