<?php
// File: app/Models/Calon.php - DIUBAH

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Calon extends Model
{
    use HasFactory;

    protected $table = 'calon';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'nama',
        'asal_pimpinan',
        'jenis_kelamin',
        'foto',
        'nomor_urut',
        'jabatan',
    ];

    protected $appends = ['foto_url'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::ulid();
            }
        });
    }

    public function pemilihan()
    {
        return $this->belongsToMany(Pemilihan::class, 'pemilihan_calon', 'calon_id', 'pemilihan_id')
            ->withTimestamps();
    }

    // Scope untuk filter
    public function scopeByJabatan($query, $jabatan)
    {
        return $query->where('jabatan', $jabatan);
    }

    public function scopeByNomorUrut($query, $nomorUrut)
    {
        return $query->where('nomor_urut', $nomorUrut);
    }

    public function scopeOrderByNomorUrut($query, $direction = 'asc')
    {
        return $query->orderBy('nomor_urut', $direction);
    }

    /**
     * Accessor untuk foto URL
     */
    public function getFotoUrlAttribute()
    {
        if (!$this->foto) {
            return url('default-avatar.png');
        }

        // Ekstrak nama file dari path
        $filename = basename($this->foto);

        // Generate URL menggunakan route
        return route('images.calon', ['filename' => $filename]);

        // Atau jika path sudah lengkap dengan folder
        // return route('images.show', [
        //     'folder' => 'calon_fotos',
        //     'filename' => $filename
        // ]);
    }

    /**
     * Accessor untuk mendapatkan nama file saja
     */
    public function getFotoFilenameAttribute()
    {
        if (!$this->foto) {
            return null;
        }

        return basename($this->foto);
    }
}
