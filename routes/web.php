<?php

use App\Http\Controllers\BilikController;
use App\Http\Controllers\CalonController;
use App\Http\Controllers\PemilihanController;
use App\Http\Controllers\PesertaController;
use App\Http\Controllers\PemilihanCalonController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\QrCodeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
    Route::resource('peserta', PesertaController::class);
    Route::get('/pesertas/import', [PesertaController::class, 'showImportForm'])->name('peserta.import.form');
    Route::post('/pesertas/import', [PesertaController::class, 'import'])->name('peserta.import');
    Route::get('/pesertas/export', [PesertaController::class, 'export'])->name('peserta.export');
    Route::resource('calon', CalonController::class);
    Route::resource('pemilihan', PemilihanController::class);
    Route::resource('biliks', BilikController::class);
    // Rute untuk mengelola Calon (Anak dari Pemilihan)
    Route::resource('pemilihan.calon', PemilihanCalonController::class)
        ->only(['index', 'store']);

    // Rute khusus untuk DELETE (Karena kita perlu ID Peserta DAN Jabatan)
    Route::delete('pemilihan/{pemilihan}/calon/{peserta}/{jabatan}', [PemilihanCalonController::class, 'destroy'])
        ->name('pemilihan.calon.destroy');
});


Route::prefix('bilik')->name('bilik.')->group(function () {
    Route::get('/login', [App\Http\Controllers\BilikAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [App\Http\Controllers\BilikAuthController::class, 'login']);

    Route::middleware(['auth:bilik'])->group(function () {
        Route::post('/logout', [App\Http\Controllers\BilikAuthController::class, 'logout'])->name('logout');

        // Route::get('/dashboard', function () {
        //     return Inertia::render('Bilik/Dashboard');
        // })->name('dashboard');
    });
});

// QR Code Routes by ID
Route::get('/qrcode/{pesertaId}', [QrCodeController::class, 'generate']);
Route::get('/qrcode/{pesertaId}/download', [QrCodeController::class, 'download']);
Route::get('/qrcode/{pesertaId}/base64', [QrCodeController::class, 'base64']);

// QR Code Routes by Kode Unik
Route::get('/qrcode/kode/{kodeUnik}', [QrCodeController::class, 'generateByKodeUnik']);
Route::get('/qrcode/kode/{kodeUnik}/download', [QrCodeController::class, 'downloadByKodeUnik']);
Route::get('/qrcode/kode/{kodeUnik}/base64', [QrCodeController::class, 'base64ByKodeUnik']);

// Get all QR Codes
Route::get('/qrcode', [QrCodeController::class, 'generateAll']);

// Presensi Routes
Route::post('/presensi/pleno/{pleno}', [PresensiController::class, 'scanPresensi']);
Route::get('/peserta/riwayat/all', [PresensiController::class, 'getAllRiwayatKehadiran']);
Route::get('/peserta/{kode_unik}', [PresensiController::class, 'getPesertaByKode']);
Route::get('/peserta/{kode_unik}/riwayat', [PresensiController::class, 'getRiwayatKehadiran']);

require __DIR__ . '/settings.php';
