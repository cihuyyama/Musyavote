<?php

use App\Http\Controllers\CalonController;
use App\Http\Controllers\PesertaController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::resource('peserta', PesertaController::class)
    ->middleware(['auth', 'verified']);

Route::resource('calon', CalonController::class)->only(['index', 'create', 'store','destroy'])
    ->middleware(['auth', 'verified']);

require __DIR__.'/settings.php';
