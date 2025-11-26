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
}
