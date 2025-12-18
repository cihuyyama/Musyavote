<?php
// app/Models/VotingRecord.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VotingRecord extends Model
{
    use HasFactory;

    protected $table = 'voting_records';
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
        'peserta_id',
        'pemilihan_id',
        'bilik_id',
        'pilihan_calon',
        'tidak_memilih',
        'waktu_voting'
    ];

    protected $casts = [
        'pilihan_calon' => 'array',
        'waktu_voting' => 'datetime',
        'tidak_memilih' => 'boolean',
        'jumlah_formatur_terpilih' => 'integer',
        'boleh_tidak_memilih' => 'boolean',
    ];

    public function peserta()
    {
        return $this->belongsTo(Peserta::class);
    }

    public function pemilihan()
    {
        return $this->belongsTo(Pemilihan::class);
    }

    public function bilik()
    {
        return $this->belongsTo(Bilik::class);
    }

    public function calon()
    {
        return $this->belongsToMany(Calon::class, 'pilihan_calon');
    }
}
