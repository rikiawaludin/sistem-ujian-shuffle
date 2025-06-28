<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ListUjianController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\PengerjaanUjianController; // <-- IMPORT BARU
use App\Http\Controllers\Admin\SyncController;
use App\Http\Controllers\Dosen\BankSoalController;
use App\Http\Controllers\Dosen\UjianController;
use App\Http\Controllers\Dosen\DosenDashboardController;
use App\Http\Controllers\Dosen\MataKuliahDosenController; 
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

Route::controller(AuthController::class)
    ->group(function () {
        Route::get('/', 'checkToken')->name('check');
        Route::get('/logout', 'logout')->name('logout'); // gunakan untuk logout
        Route::get('/roles', 'changeUserRole')->middleware('auth.token');
});

Route::middleware('auth.token', 'auth.mahasiswa')->group(function () {
    Route::get('/home', [DashboardController::class, 'index'])->name('home');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Semua rute ujian untuk mahasiswa dipindahkan ke sini
    Route::prefix('ujian')->name('ujian.')->group(function () {
        Route::get('/mata-kuliah/{id_mata_kuliah}', [ListUjianController::class, 'daftarPerMataKuliah'])->name('daftarPerMataKuliah');
        Route::get('/{id_ujian}/kerjakan', [ListUjianController::class, 'kerjakanUjian'])->name('kerjakan');
        Route::get('/{id_ujian}/selesai-konfirmasi', [ListUjianController::class, 'konfirmasiSelesaiUjian'])->name('selesai.konfirmasi');
        Route::get('/hasil/{id_attempt}', [ListUjianController::class, 'detailHasilUjian'])->name('hasil.detail');
        Route::get('/riwayat', [ListUjianController::class, 'riwayatUjian'])->name('riwayat');
        
        Route::post('/submit', [PengerjaanUjianController::class, 'store'])->name('submit');
    });
});

Route::middleware('auth.token')->group(function () {
    // middleware('auth.admin')->
    Route::middleware('auth.admin')->prefix('admin')->name('admin.')->group(function () {
        
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // Rute untuk sinkronisasi
        Route::post('/sync/mahasiswa', [SyncController::class, 'syncMahasiswa'])->name('sync.mahasiswa');
        Route::post('/sync/matakuliah', [SyncController::class, 'syncMataKuliah'])->name('sync.matakuliah');
        Route::post('/sync/dosen', [SyncController::class, 'SyncDosen'])->name('sync.dosen');
        Route::post('/sync/prodi', [SyncController::class, 'SyncProdi'])->name('sync.prodi');
        Route::post('/sync/admin', [SyncController::class, 'SyncAdmin'])->name('sync.admin');


    });


});


Route::middleware(['auth.token', 'auth.dosen'])->group(function () {

    Route::get('dosen/dashboard', [DosenDashboardController::class, 'index'])->name('dosen.dashboard');
    Route::get('dosen/matakuliah/{mata_kuliah}', [MataKuliahDosenController::class, 'show'])->name('dosen.matakuliah.show');
    Route::get('dosen/bank-soal/export', [BankSoalController::class, 'export'])->name('dosen.bank-soal.export');

    Route::resource('dosen/bank-soal', BankSoalController::class)
        ->names('dosen.bank-soal');
    
    Route::resource('dosen/ujian', UjianController::class)
        ->names('dosen.ujian');
});

require __DIR__.'/auth.php';