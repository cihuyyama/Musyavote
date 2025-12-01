<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Pemilihan extends Model
{
    use HasFactory; 
    
    protected $table = 'pemilihan';
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
        'nama_pemilihan',
        'boleh_tidak_memilih',
        'minimal_kehadiran',
        'jumlah_formatur_terpilih',
    ];

    public function biliks()
    {
        return $this->belongsToMany(Bilik::class, 'pemilihan_bilik', 'pemilihan_id', 'bilik_id')
            ->withTimestamps();
    }

    public function calon()
    {
        return $this->belongsToMany(Calon::class, 'pemilihan_calon', 'pemilihan_id', 'calon_id')
                ->withTimestamps();
    }

    public function voting_records()
    {
        return $this->hasMany(VotingRecord::class, 'pemilihan_id', 'id');
    }

    public function isVotingMandatory(): bool
    {
        return !$this->boleh_tidak_memilih;
    }
}
