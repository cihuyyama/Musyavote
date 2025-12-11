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
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class Peserta extends Authenticatable
{
    use HasFactory, HasUlids, HasApiTokens;

    protected $table = 'peserta';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode_unik',
        'foto',
        'nama',
        'asal_pimpinan',
        'jenis_kelamin',
        'status',
        'password',
        'password_plain'
    ];

    protected $appends = ['foto_url'];

    protected $hidden = [
        'password',
        'remember_token',
        'password_plain' // Sembunyikan dari JSON response biasa
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($peserta) {
            if (empty($peserta->kode_unik)) {
                DB::transaction(function () use ($peserta) {
                    $lastPeserta = static::lockForUpdate()
                        ->orderBy('created_at', 'desc')
                        ->first();

                    $nextNumber = $lastPeserta ? (int) substr($lastPeserta->kode_unik, 3) + 1 : 1;

                    $existingKode = static::where('kode_unik', 'PST' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT))->exists();

                    if ($existingKode) {
                        $maxNumber = static::max(DB::raw('CAST(SUBSTRING(kode_unik, 4) AS UNSIGNED)'));
                        $nextNumber = $maxNumber + 1;
                    }

                    $peserta->kode_unik = 'PST' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
                });
            }

            // Auto generate random 6 digit password
            if (empty($peserta->password)) {
                $randomPassword = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
                $peserta->password = Hash::make($randomPassword);
                $peserta->password_plain = $randomPassword; // Simpan plain password
            }
        });
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

    /**
     * Get QR Code data dengan informasi lengkap
     */
    public function getQrCodeDataAttribute()
    {
        return [
            'kode_unik' => $this->kode_unik,
            'nama' => $this->nama,
            'asal_pimpinan' => $this->asal_pimpinan,
            'jenis_kelamin' => $this->jenis_kelamin,
            'status' => $this->status,
            'foto' => $this->foto ? asset('storage/' . $this->foto) : null,
            'timestamp' => now()->toISOString()
        ];
    }

    /**
     * Generate QR Code dengan data lengkap
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

    /**
     * Get plain password untuk keperluan display/export
     */
    public function getPasswordPlainAttribute()
    {
        return $this->attributes['password_plain'] ?? null;
    }

    /**
     * Generate new random password
     */
    public function generateNewPassword()
    {
        $randomPassword = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        $this->password = Hash::make($randomPassword);
        $this->password_plain = $randomPassword;
        $this->save();

        return $randomPassword;
    }

    /**
     * Scope untuk include plain password (hanya untuk admin)
     */
    public function scopeWithPlainPassword($query)
    {
        return $query->addSelect('*');
    }

    /**
     * Get URL untuk download kartu peserta
     */
    public function getKartuPesertaUrl()
    {
        return url("/kartu-peserta/export/{$this->id}");
    }

    /**
     * Get URL untuk download kartu peserta by kode unik
     */
    public function getKartuPesertaByKodeUrl()
    {
        return url("/kartu-peserta/export/kode/{$this->kode_unik}");
    }

    /**
     * Generate QR Code dengan caching
     */
    public function generateQrCodeWithCache($size = 300, $cacheMinutes = 60)
    {
        $cacheKey = "qrcode_{$this->id}_{$size}";

        return Cache::remember($cacheKey, $cacheMinutes * 60, function () use ($size) {
            return $this->generateQrCode($size);
        });
    }

    /**
     * Generate QR Code base64 dengan caching
     */
    public function generateQrCodeBase64WithCache($size = 300, $cacheMinutes = 60)
    {
        $cacheKey = "qrcode_base64_{$this->id}_{$size}";

        return Cache::remember($cacheKey, $cacheMinutes * 60, function () use ($size) {
            return $this->generateQrCodeBase64($size);
        });
    }

    /**
     * Clear QR Code cache
     */
    public function clearQrCodeCache($size = 300)
    {
        Cache::forget("qrcode_{$this->id}_{$size}");
        Cache::forget("qrcode_base64_{$this->id}_{$size}");
    }

    /**
     * Accessor untuk foto URL
     */
    public function getFotoUrlAttribute()
    {
        if (!$this->foto) {
            return url('default-avatar.png');
        }
        
        $filename = basename($this->foto);
        return route('images.peserta', ['filename' => $filename]);
    }

    /**
     * Accessor untuk URL foto berdasarkan kode unik
     */
    public function getFotoByKodeUrlAttribute()
    {
        if (!$this->foto || !$this->kode_unik) {
            return asset('default-avatar.png');
        }
        
        // URL untuk mendapatkan foto berdasarkan kode unik
        return route('images.bykode', ['kode_unik' => $this->kode_unik]);
    }
}
