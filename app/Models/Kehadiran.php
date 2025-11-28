<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kehadiran extends Model
{
    use HasFactory;
    
    protected $table = 'kehadiran';

    protected $fillable = [
        'peserta_id',
        'pleno_1',
        'pleno_2',
        'pleno_3',
        'pleno_4',
        'total_kehadiran',
    ];
    
    // Relasi ke Peserta
    public function peserta()
    {
        return $this->belongsTo(Peserta::class);
    }
}
