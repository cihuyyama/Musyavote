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

    /**
     * Casting nilai pleno & total supaya tidak terkirim sebagai string.
     * MariaDB biasanya mengembalikan string "1" / "0".
     */
    protected $casts = [
        'pleno_1' => 'integer',
        'pleno_2' => 'integer',
        'pleno_3' => 'integer',
        'pleno_4' => 'integer',
        'total_kehadiran' => 'integer',
    ];

    // Relasi ke Peserta
    public function peserta()
    {
        return $this->belongsTo(Peserta::class);
    }
}
