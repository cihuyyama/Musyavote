<?php

use App\Http\Controllers\BilikController;
use App\Http\Controllers\CalonController;
use App\Http\Controllers\PemilihanController;
use App\Http\Controllers\PesertaController;
use App\Http\Controllers\PemilihanCalonController;
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

        Route::get('/dashboard', function () {
            return Inertia::render('Bilik/Dashboard');
        })->name('dashboard');
    });
});

require __DIR__ . '/settings.php';
