<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ListUjianController;
use App\Http\Controllers\PengerjaanUjianController; // <-- IMPORT BARU
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ... (welcome route, dashboard route, auth middleware group untuk profile) ...
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('ujian')->name('ujian.')->group(function () {
        Route::get('/mata-kuliah/{id_mata_kuliah}', [ListUjianController::class, 'daftarPerMataKuliah'])->name('daftarPerMataKuliah');
        Route::get('/{id_ujian}/kerjakan', [ListUjianController::class, 'kerjakanUjian'])->name('kerjakan');
        Route::get('/{id_ujian}/selesai-konfirmasi', [ListUjianController::class, 'konfirmasiSelesaiUjian'])->name('selesai.konfirmasi');
        Route::get('/hasil/{id_attempt}', [ListUjianController::class, 'detailHasilUjian'])->name('hasil.detail');
        Route::get('/riwayat', [ListUjianController::class, 'riwayatUjian'])->name('riwayat');
        
        Route::post('/submit', [PengerjaanUjianController::class, 'store'])->name('submit'); // <-- RUTE BARU UNTUK SUBMIT
    });
});

require __DIR__.'/auth.php';