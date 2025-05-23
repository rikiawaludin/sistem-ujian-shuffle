<?php

use App\Http\Controllers\ProfileController;
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

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rute untuk menampilkan daftar ujian per mata kuliah
Route::get('/mata-kuliah/{id_mata_kuliah}/ujian', function ($id_mata_kuliah) {
    // Untuk saat ini, kita gunakan data statis
    // Nanti, Anda akan mengambil data ini dari database berdasarkan $id_mata_kuliah

    $mataKuliah = (object) [ // Menggunakan (object) agar mirip dengan data dari Eloquent model
        'id' => $id_mata_kuliah,
        'nama' => 'Pemrograman Web Lanjut - ID: ' . $id_mata_kuliah, // Contoh nama dinamis
        // 'linkKembali' => route('halaman.daftar.mata.kuliah'), // Opsional jika ada halaman daftar mata kuliah
    ];

    $daftarUjian = [
        [ 'id' => 101, 'nama' => "Ujian Tengah Semester", 'deskripsi' => "Materi dari pertemuan 1 hingga 7.", 'durasi' => "90 Menit", 'jumlahSoal' => 30, 'batasWaktuPengerjaan' => "25 Mei 2025, 23:59", 'status' => "Belum Dikerjakan", 'kkm' => 75, 'skor' => null ],
        [ 'id' => 102, 'nama' => "Kuis Mingguan - Bab Framework", 'deskripsi' => "Konsep dasar dan penggunaan framework.", 'durasi' => "45 Menit", 'jumlahSoal' => 15, 'batasWaktuPengerjaan' => "Setiap Jumat", 'status' => "Sedang Dikerjakan", 'kkm' => 70, 'skor' => null ],
        [ 'id' => 103, 'nama' => "Ujian Praktikum Akhir", 'deskripsi' => "Implementasi proyek akhir.", 'durasi' => "120 Menit", 'jumlahSoal' => 1, 'batasWaktuPengerjaan' => "1 Juni 2025", 'status' => "Selesai", 'skor' => 88, 'kkm' => 65 ],
        [ 'id' => 104, 'nama' => "Kuis Dadakan - API", 'deskripsi' => "Pemahaman tentang REST API.", 'durasi' => "20 Menit", 'jumlahSoal' => 10, 'batasWaktuPengerjaan' => "20 Mei 2025 (Terlewat)", 'status' => "Waktu Habis", 'kkm' => 70, 'skor' => null ],
    ];

    // Render komponen Inertia dan kirim data sebagai props
    // Pastikan nama komponen ('Ujian/DaftarUjianPage') sesuai dengan lokasi file JSX Anda
    // relatif terhadap resources/js/Pages/
    return Inertia::render('Ujian/DaftarUjianPage', [
        'mataKuliah' => $mataKuliah,
        'daftarUjian' => $daftarUjian,
        // Anda juga bisa mengirim props lain jika diperlukan, misalnya 'auth' user
        // 'auth' => ['user' => auth()->user()] // Inertia biasanya sudah handle ini via middleware
    ]);
})->middleware(['auth'])->name('ujian.daftarPerMataKuliah'); // Beri nama rute & pastikan ada middleware auth

require __DIR__.'/auth.php';
