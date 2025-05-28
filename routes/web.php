<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;   // <-- Impor DashboardController
use App\Http\Controllers\ListUjianController;  // <-- Impor ListUjianController
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Rute Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

// Grup rute yang memerlukan autentikasi
Route::middleware('auth')->group(function () {
    // Rute Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // Route::get('/user/profile', function() { return Inertia::render('Profile'); })->name('profile.show'); // Jika ini halaman statis

    // Rute-rute terkait Ujian
    Route::prefix('ujian')->name('ujian.')->group(function () { // Menggunakan prefix dan name group untuk ujian
        Route::get('/mata-kuliah/{id_mata_kuliah}', [ListUjianController::class, 'daftarPerMataKuliah'])->name('daftarPerMataKuliah');
        Route::get('/{id_ujian}/kerjakan', [ListUjianController::class, 'kerjakanUjian'])->name('kerjakan');
        Route::get('/{id_ujian}/selesai-konfirmasi', [ListUjianController::class, 'konfirmasiSelesaiUjian'])->name('selesai.konfirmasi');
        Route::get('/hasil/{id_attempt}', [ListUjianController::class, 'detailHasilUjian'])->name('hasil.detail');
        Route::get('/riwayat', [ListUjianController::class, 'riwayatUjian'])->name('riwayat');
        
        // Anda mungkin akan menambahkan rute POST untuk submit ujian di sini, misalnya:
        // Route::post('/submit', [PengerjaanUjianController::class, 'store'])->name('submit'); // Membutuhkan PengerjaanUjianController
    });
});

require __DIR__.'/auth.php';