<?php

use App\Http\Controllers\AdminKehadiranAuthController;
use App\Http\Controllers\AdminKehadiranController;
use App\Http\Controllers\BilikAuthController;
use App\Http\Controllers\BilikController;
use App\Http\Controllers\CalonController;
use App\Http\Controllers\HasilPemilihanController;
use App\Http\Controllers\KartuPesertaController;
use App\Http\Controllers\PemilihanController;
use App\Http\Controllers\PesertaController;
use App\Http\Controllers\PemilihanCalonController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\VotingController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');


Route::middleware(['auth:web', 'verified'])->group(function () {
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

    // Get all QR Codes - Hanya yang ini perlu auth karena menampilkan semua QR code
    Route::get('/qrcode', [QrCodeController::class, 'generateAll']);

    Route::resource('admin-presensi', AdminKehadiranController::class);

    Route::prefix('hasil-pemilihan')->name('hasil-pemilihan.')->group(function () {
        Route::get('/', [HasilPemilihanController::class, 'index'])->name('index');
        Route::get('/{pemilihanId}', [HasilPemilihanController::class, 'show'])->name('show');
        Route::get('/{pemilihanId}/data', [HasilPemilihanController::class, 'getHasilData'])->name('data');
        Route::get('/{pemilihanId}/export/pdf', [HasilPemilihanController::class, 'exportPDF'])->name('hasil-pemilihan.export.pdf');
        Route::get('/{pemilihanId}/export/excel', [HasilPemilihanController::class, 'exportExcel'])->name('hasil-pemilihan.export.excel');
    });

    // Kartu Peserta Routes
    Route::get('/kartu-peserta/export/all', [KartuPesertaController::class, 'exportAll'])->name('kartu-peserta.export.all');
    Route::get('/kartu-peserta/export/{peserta}', [KartuPesertaController::class, 'exportSingle'])->name('kartu-peserta.export.single');
    Route::get('/kartu-peserta/export/kode/{kodeUnik}', [KartuPesertaController::class, 'exportByKodeUnik'])->name('kartu-peserta.export.kode');
    Route::get('/kartu-peserta/preview', [KartuPesertaController::class, 'preview'])->name('kartu-peserta.preview');
    Route::get('/kartu-peserta/preview/{peserta}', [KartuPesertaController::class, 'preview'])->name('kartu-peserta.preview.single');
});

Route::get('daftar-kehadiran', [AdminKehadiranController::class, 'index_public'])->name('admin-presensi.public.index');



Route::prefix('bilik')->name('bilik.')->group(function () {
    Route::get('/login', [BilikAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [BilikAuthController::class, 'login']);

    // Group untuk routes yang membutuhkan auth bilik
    Route::middleware(['auth:bilik', 'check.bilik.pemilihan', 'voting.timeout'])->group(function () {
        Route::post('/logout', [BilikAuthController::class, 'logout'])->name('logout');

        // Route::get('/dashboard', function () {
        //     $bilik = Auth::guard('bilik')->user();
        //     $pemilihans = $bilik->pemilihan()->withCount('calon')->get();

        //     return Inertia::render('Bilik/Dashboard', [
        //         'bilik' => $bilik,
        //         'pemilihans' => $pemilihans
        //     ]);
        // })->name('dashboard');

        // Routes voting dengan multiple middleware
        Route::prefix('voting')->name('voting.')->group(function () {
            // Halaman input kode unik
            Route::get('/', [VotingController::class, 'index'])
                ->name('index')
                ->middleware('prevent.back.after.voting');

            // Verifikasi peserta
            Route::post('/verify', [VotingController::class, 'verifyPeserta'])
                ->name('verify')
                ->middleware('prevent.back.after.voting');

            // Halaman daftar calon untuk semua pemilihan
            Route::get('/calon', [VotingController::class, 'showCalon'])
                ->name('calon')
                ->middleware(['voting.session', 'prevent.back.after.voting']);

            // Proses voting untuk semua pemilihan
            Route::post('/submit', [VotingController::class, 'submitVoting'])
                ->name('submit')
                ->middleware('prevent.back.after.voting');

            // Logout dari voting session
            Route::post('/logout', [VotingController::class, 'logout'])
                ->name('logout')
                ->middleware('prevent.back.after.voting');
        });
    });
});


// QR Code Routes by ID - Publik
Route::get('/qrcode/{pesertaId}', [QrCodeController::class, 'generate']);
Route::get('/qrcode/{pesertaId}/download', [QrCodeController::class, 'download']);
Route::get('/qrcode/{pesertaId}/base64', [QrCodeController::class, 'base64']);

// QR Code Routes by Kode Unik - Publik
Route::get('/qrcode/kode/{kodeUnik}', [QrCodeController::class, 'generateByKodeUnik']);
Route::get('/qrcode/kode/{kodeUnik}/download', [QrCodeController::class, 'downloadByKodeUnik']);
Route::get('/qrcode/kode/{kodeUnik}/base64', [QrCodeController::class, 'base64ByKodeUnik']);

// Presensi Routes - Publik karena perlu diakses oleh scanner
Route::post('/presensi/pleno/{pleno}', [PresensiController::class, 'scanPresensi']);
Route::get('/peserta/riwayat/all', [PresensiController::class, 'getAllRiwayatKehadiran'])->middleware('auth');
Route::get('/daftar-kehadiran', [PresensiController::class, 'getAllRiwayatKehadiranPublik']);
Route::get('/peserta/{kode_unik}', [PresensiController::class, 'getPesertaByKode']);
Route::get('/peserta/{kode_unik}/riwayat', [PresensiController::class, 'getRiwayatKehadiran']);

// routes/web.php
Route::prefix('admin-kehadiran')->group(function () {
    // Public routes
    Route::get('/login', [AdminKehadiranAuthController::class, 'login'])->name('admin-kehadiran.login');
    Route::post('/login', [AdminKehadiranAuthController::class, 'authenticate']);

    // Protected routes - SPECIFY THE GUARD
    Route::middleware(['auth:admin_kehadiran'])->group(function () {
        Route::get('/dashboard', [AdminKehadiranAuthController::class, 'dashboard'])->name('admin-kehadiran.dashboard');
        Route::post('/logout', [AdminKehadiranAuthController::class, 'logout'])->name('admin-kehadiran.logout');
        Route::post('/generate-session-qr', [AdminKehadiranAuthController::class, 'generateSessionQR'])->name('admin-kehadiran.generate-session-qr');
        // Route untuk scan QR (hanya mendapatkan data peserta)
        Route::post('/scan-qr', [AdminKehadiranController::class, 'scanQR']);
        // Route untuk presensi (setelah konfirmasi)
        Route::post('/presensi', [AdminKehadiranController::class, 'presensi']);
    });
});

require __DIR__ . '/settings.php';
