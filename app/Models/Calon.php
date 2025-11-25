<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calon extends Model
{
    use HasFactory;
    
    protected $table = 'calon';

    protected $fillable = [
        'peserta_id',
        'jabatan',
    ];

    public function peserta()
    {
        return $this->belongsTo(Peserta::class); 
    }
}
