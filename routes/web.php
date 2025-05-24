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
    // Data statis untuk daftar mata kuliah (nantinya ambil dari database)
    $daftarMataKuliah = [
        [
            'id' => 1, // ID Mata Kuliah ini PENTING untuk link ke DaftarUjianPage
            'nama' => 'Pemrograman Web Lanjut',
            'dosen' => ['nama' => 'Dr. Indah K., M.Kom.'],
            'deskripsi_singkat' => 'Mempelajari konsep lanjutan pengembangan web.',
            'img' => '/images/web-lanjut.jfif', // Sediakan gambar ini di public/images
            // Kita akan hitung jumlah ujian di frontend dari data ujian statis untuk sementara
            // atau Anda bisa tambahkan 'jumlah_ujian_tersedia' langsung dari backend
        ],
        [
            'id' => 2,
            'nama' => 'Kalkulus Dasar',
            'dosen' => ['nama' => 'Dr. Retno W., M.Si.'],
            'deskripsi_singkat' => 'Pengenalan konsep limit, turunan, dan integral.',
            'img' => '/images/kalkulus-dasar.jfif',
        ],
         [
            'id' => 123, // ID ini akan kita gunakan untuk contoh link
            'nama' => 'Fisika Mekanika',
            'dosen' => ['nama' => 'Prof. Dr. Agus H.'],
            'deskripsi_singkat' => 'Studi tentang gerak benda.',
            'img' => '/images/fisika.jpg',
        ],
    ];

    return Inertia::render('Dashboard', [
        'daftarMataKuliah' => $daftarMataKuliah,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rute untuk halaman profil pengguna (jika berbeda dari edit)
    Route::get('/user/profile', function() {
        // Logika untuk menampilkan halaman profil
        // Anda mungkin ingin membuat ProfileController@show atau sejenisnya
        // Untuk saat ini, kita bisa render komponen Profile yang Anda kirim
        // Asumsikan komponen Profile.jsx ada di resources/js/Pages/Profile/ProfilePage.jsx
        // Jika file Profile.jsx yang Anda kirim adalah halaman, pindahkan ke Pages/Profile/
        // dan beri nama misalnya ProfilePage.jsx
        // return Inertia::render('Profile/ProfilePage');

        // Jika Profile.jsx yang Anda kirim (denganTabs, dll.) adalah halaman profil:
        // 1. Pindahkan Profile.jsx ke resources/js/Pages/Profile.jsx
        // 2. Gunakan:
        return Inertia::render('Profile'); // Inertia akan mencari Profile.jsx di Pages
    })->name('profile.show'); // Nama rute ini digunakan di AppNavbar
});

// Rute untuk menampilkan daftar ujian per mata kuliah
Route::get('/mata-kuliah/{id_mata_kuliah}/ujian', function ($id_mata_kuliah) {
    $mataKuliah = (object) [
        'id' => $id_mata_kuliah,
        'nama' => 'Mata Kuliah ID: ' . $id_mata_kuliah, // Ganti dengan pengambilan nama dari DB
    ];

    // Data statis ujian, nantinya ini akan diambil berdasarkan $id_mata_kuliah
    $daftarUjian = [
        [ 'id' => 101, 'mata_kuliah_id' => $id_mata_kuliah, 'nama' => "Ujian Tengah Semester", 'deskripsi' => "Materi bab 1-7.", 'durasi' => "90 Menit", 'jumlahSoal' => 30, 'batasWaktuPengerjaan' => "25 Des 2025", 'status' => "Belum Dikerjakan", 'kkm' => 75, 'skor' => null ],
        [ 'id' => 102, 'mata_kuliah_id' => $id_mata_kuliah, 'nama' => "Kuis Framework", 'durasi' => "45 Menit", 'jumlahSoal' => 15, 'batasWaktuPengerjaan' => "Tiap Jumat", 'status' => "Sedang Dikerjakan", 'kkm' => 70, 'skor' => null ],
        [ 'id' => 103, 'mata_kuliah_id' => $id_mata_kuliah, 'nama' => "Praktikum Akhir", 'durasi' => "120 Menit", 'jumlahSoal' => 1, 'batasWaktuPengerjaan' => "30 Des 2025", 'status' => "Selesai", 'skor' => 88, 'kkm' => 65 ],
    ];

    return Inertia::render('Ujian/DaftarUjianPage', [
        'mataKuliah' => $mataKuliah,
        'daftarUjian' => $daftarUjian,
    ]);
})->middleware(['auth'])->name('ujian.daftarPerMataKuliah');

// Rute untuk halaman pengerjaan ujian
Route::get('/ujian/{id_ujian}/kerjakan', function ($id_ujian) {
    // Nanti, Anda akan mengambil detail ujian dari DB berdasarkan $id_ujian
    // Untuk sekarang, PengerjaanUjianPage.jsx menggunakan data statis internalnya
    return Inertia::render('Ujian/PengerjaanUjianPage', [
        'idUjianAktif' => $id_ujian, // Kirim ID ujian agar halaman tahu ujian mana yang dikerjakan
        // Anda juga bisa mengirim semua detail soal dari sini jika tidak ingin data statis di frontend
    ]);
})->middleware(['auth'])->name('ujian.kerjakan');


require __DIR__.'/auth.php';
