<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\DB;

class Peserta extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'peserta';
    
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'kode_unik', 'foto', 'nama', 'asal_pimpinan', 'jenis_kelamin', 'status',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($peserta) {
            if (empty($peserta->kode_unik)) {
                // Gunakan database transaction untuk avoid race condition
                DB::transaction(function () use ($peserta) {
                    $lastPeserta = static::lockForUpdate()
                        ->orderBy('created_at', 'desc')
                        ->first();
                    
                    $nextNumber = $lastPeserta ? (int) substr($lastPeserta->kode_unik, 3) + 1 : 1;
                    
                    // Cek jika nomor sudah ada (untuk jaga-jaga)
                    $existingKode = static::where('kode_unik', 'PST' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT))->exists();
                    
                    if ($existingKode) {
                        // Jika sudah ada, cari nomor berikutnya yang available
                        $maxNumber = static::max(DB::raw('CAST(SUBSTRING(kode_unik, 4) AS UNSIGNED)'));
                        $nextNumber = $maxNumber + 1;
                    }
                    
                    $peserta->kode_unik = 'PST' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
                });
            }
        });
    }

    public function calon()
    {
        return $this->hasOne(Calon::class); 
    }
    
    public function getJabatanCalonAttribute()
    {
        return $this->calon ? $this->calon->jabatan : 'Bukan Calon';
    }

    public function kehadiran()
    {
        return $this->hasOne(Kehadiran::class);
    }
    
    public function isEligibleToVote(Pemilihan $pemilihan): bool
    {
        if (!$this->relationLoaded('kehadiran')) {
            $this->load('kehadiran');
        }
        
        $totalKehadiranPeserta = $this->kehadiran ? $this->kehadiran->total_kehadiran : 0;
        $syaratMinimal = $pemilihan->minimal_kehadiran;

        return $totalKehadiranPeserta >= $syaratMinimal;
    }

    public function scopeByKodeUnik($query, $kodeUnik)
    {
        return $query->where('kode_unik', $kodeUnik);
    }

    public function getQrCodeDataAttribute()
    {
        return [
            'kode_unik' => $this->kode_unik,
            'nama' => $this->nama,
            'asal_pimpinan' => $this->asal_pimpinan,
            'timestamp' => now()->toISOString()
        ];
    }

    /**
     * Generate QR Code menggunakan Builder (Cara Modern)
     */
    public function generateQrCode($size = 300)
    {
        $qrData = json_encode($this->qr_code_data);
        
        $builder = new Builder(
            writer: new PngWriter(),
            writerOptions: [],
            validateResult: false,
            data: $qrData,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: $size,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin
        );

        $result = $builder->build();
        return $result->getString();
    }

    /**
     * Generate QR Code sebagai base64 untuk embed di HTML
     */
    public function generateQrCodeBase64($size = 300)
    {
        $qrCodeString = $this->generateQrCode($size);
        return 'data:image/png;base64,' . base64_encode($qrCodeString);
    }

    
}