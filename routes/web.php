<?php

/*
|--------------------------------------------------------------------------
| Master Data Statis (Simulasi Database)
|--------------------------------------------------------------------------
*/

// DATA MASTER MATA KULIAH
$masterMataKuliah = [
    1 => ['id' => 1, 'nama' => 'Pemrograman Web Lanjut', 'dosen' => ['nama' => 'Dr. Indah K., M.Kom.'], 'deskripsi_singkat' => 'Mempelajari konsep lanjutan pengembangan web dengan framework modern dan praktik terbaik.', 'img' => '/images/web-lanjut.jfif'],
    2 => ['id' => 2, 'nama' => 'Kalkulus Dasar', 'dosen' => ['nama' => 'Dr. Retno W., M.Si.'], 'deskripsi_singkat' => 'Pengenalan konsep fundamental limit, turunan, dan integral untuk aplikasi rekayasa.', 'img' => '/images/kalkulus-dasar.jfif'],
    123 => ['id' => 123, 'nama' => 'Fisika Mekanika Lanjutan', 'dosen' => ['nama' => 'Prof. Dr. Agus H.'], 'deskripsi_singkat' => 'Studi mendalam tentang gerak benda, energi, dan hukum Newton.', 'img' => '/images/fisika.jpg'],
];

// DATA MASTER SEMUA UJIAN BESERTA SOALNYA
$masterSemuaUjian = [
    // Ujian untuk Mata Kuliah ID 1 (Pemrograman Web Lanjut)
    101 => ['id' => 101, 'mata_kuliah_id' => 1, 'nama' => "UTS Pemrograman Web Lanjut", 'deskripsi' => "Materi HTML, CSS, JavaScript Dasar, dan PHP.", 'durasi' => "90 Menit", 'jumlahSoal' => 2, 'batasWaktuPengerjaan' => "10 Juni 2025", 'status' => "Belum Dikerjakan", 'kkm' => 75, 'id_pengerjaan_terakhir' => null, 'skor' => null,
        'durasiTotalDetik' => 50 * 60, // 50 menit
        'soalList' => [
            [ 'id' => 1, 'nomor' => 1, 'tipe' => "pilihan_ganda", 'pertanyaan' => "Tag HTML apa yang digunakan untuk membuat hyperlink?", 'opsi' => ["<link>", "<a>", "<href>", "<hyperlink>"], 'jawabanUser' => null, 'raguRagu' => false, 'kunciJawaban' => '<a>', 'penjelasan' => 'Tag <a> (anchor) digunakan untuk membuat hyperlink.'],
            [ 'id' => 2, 'nomor' => 2, 'tipe' => "esai", 'pertanyaan' => "Jelaskan perbedaan antara GET dan POST request dalam HTTP!", 'jawabanUser' => "", 'raguRagu' => false, 'kunciJawaban' => 'GET digunakan untuk meminta data, parameter dikirim via URL. POST digunakan untuk mengirim data, parameter dikirim dalam body request.', 'penjelasan' => 'GET bersifat idempoten, POST tidak.'],
        ]
    ],
    103 => ['id' => 103, 'mata_kuliah_id' => 1, 'nama' => "UAS Pemrograman Web Lanjut", 'deskripsi' => "Materi Framework dan Database.", 'durasi' => "120 Menit", 'jumlahSoal' => 1, 'batasWaktuPengerjaan' => "20 Juni 2025", 'status' => "Selesai", 'kkm' => 70, 'id_pengerjaan_terakhir' => 701, 'skor' => 85, // Ada id_pengerjaan_terakhir
        'durasiTotalDetik' => 60 * 60,
        'soalList' => [
            [ 'id' => 1, 'nomor' => 1, 'tipe' => "esai", 'pertanyaan' => "Apa keuntungan menggunakan ORM?", 'jawabanUser' => "Memudahkan interaksi dengan database.", 'raguRagu' => false, 'kunciJawaban' => 'Abstraksi database, keamanan, produktivitas.', 'penjelasan' => 'ORM membantu developer bekerja dengan database menggunakan paradigma OOP.'],
        ]
    ],
    // Ujian untuk Mata Kuliah ID 2 (Kalkulus Dasar)
    202 => ['id' => 202, 'mata_kuliah_id' => 2, 'nama' => "UTS Kalkulus", 'deskripsi' => "Materi limit dan turunan.", 'durasi' => "100 Menit", 'jumlahSoal' => 2, 'batasWaktuPengerjaan' => "12 Juni 2025", 'status' => "Selesai", 'kkm' => 65, 'id_pengerjaan_terakhir' => 702, 'skor' => 70,
        'durasiTotalDetik' => 30 * 60,
        'soalList' => [
            [ 'id' => 1, 'nomor' => 1, 'tipe' => "pilihan_ganda", 'pertanyaan' => "Turunan dari f(x) = 3x^2 + 2x - 5 adalah...", 'opsi' => ["6x + 2", "3x + 2", "6x", "2"], 'jawabanUser' => null, 'raguRagu' => false, 'kunciJawaban' => '6x + 2', 'penjelasan' => 'Gunakan aturan turunan dasar.' ],
            [ 'id' => 2, 'nomor' => 2, 'tipe' => "esai", 'pertanyaan' => "Apa itu limit fungsi?", 'jawabanUser' => "", 'raguRagu' => false, 'kunciJawaban' => 'Nilai yang didekati fungsi saat variabel mendekati suatu titik.', 'penjelasan' => 'Konsep dasar kalkulus.' ],
        ]
    ],
    // Ujian untuk Mata Kuliah ID 123 (Fisika Mekanika Lanjutan)
    201 => ['id' => 201, 'mata_kuliah_id' => 123, 'nama' => "Simulasi Ujian Akhir Semester Fisika", 'deskripsi' => "Mencakup semua bab.", 'durasi' => "30 Menit", 'jumlahSoal' => 3, 'batasWaktuPengerjaan' => "Kapan saja", 'status' => "Belum Dikerjakan", 'kkm' => 70, 'id_pengerjaan_terakhir' => null, 'skor' => null,
        'durasiTotalDetik' => 30 * 60,
        'soalList' => [
            [ 'id' => 1, 'nomor' => 1, 'tipe' => "pilihan_ganda", 'pertanyaan' => "Sebuah partikel bergerak melingkar dengan kecepatan sudut konstan. Manakah pernyataan berikut yang BENAR mengenai percepatan sentripetalnya?", 'opsi' => ["Besarnya konstan dan arahnya menuju pusat lingkaran", "Besarnya konstan dan arahnya menjauhi pusat lingkaran", "Besarnya berubah dan arahnya menuju pusat lingkaran", "Besarnya berubah dan arahnya menjauhi pusat lingkaran"], 'jawabanUser' => null, 'raguRagu' => false, 'kunciJawaban' => 'Besarnya konstan dan arahnya menuju pusat lingkaran', 'penjelasan' => 'Penjelasan soal 1.' ],
            [ 'id' => 2, 'nomor' => 2, 'tipe' => "pilihan_ganda", 'pertanyaan' => "Sebuah benda jatuh bebas dari ketinggian H. Jika percepatan gravitasi adalah g, waktu yang dibutuhkan benda untuk mencapai tanah adalah...", 'opsi' => ["√(2H/g)", "√(H/g)", "2H/g", "H/g"], 'jawabanUser' => null, 'raguRagu' => false, 'kunciJawaban' => '√(2H/g)', 'penjelasan' => 'Penjelasan soal 2.' ],
            [ 'id' => 3, 'nomor' => 3, 'tipe' => "esai", 'pertanyaan' => "Jelaskan prinsip dasar dari Teorema Usaha-Energi dan berikan satu contoh aplikasinya dalam kehidupan sehari-hari!", 'jawabanUser' => "", 'raguRagu' => false, 'kunciJawaban' => 'Kunci jawaban esai...', 'penjelasan' => 'Penjelasan soal 3.' ],
        ]
    ],
];

// DATA MASTER HISTORI PENGERJAAN UJIAN (SIMULASI)
$masterHistoriPengerjaan = [
    701 => ['id_pengerjaan' => 701, 'id_ujian' => 103, 'skor' => 85, 'tanggalPengerjaan' => '20 Juni 2025', 'waktuDihabiskan' => '110 Menit',
        'jawabanUserPerSoal' => [
            1 => ['jawabanPengguna' => "Memudahkan interaksi dengan database dan meningkatkan produktivitas.", 'isBenar' => true],
        ]
    ],
    702 => ['id_pengerjaan' => 702, 'id_ujian' => 202, 'skor' => 70, 'tanggalPengerjaan' => '12 Juni 2025', 'waktuDihabiskan' => '80 Menit',
        'jawabanUserPerSoal' => [
            1 => ['jawabanPengguna' => "6x + 2", 'isBenar' => true],
            2 => ['jawabanPengguna' => "Limit adalah pendekatan nilai.", 'isBenar' => null], // Esai perlu dinilai
        ]
    ],
    // ID Pengerjaan untuk Ujian yang baru selesai (ID Ujian 201 dari PengerjaanUjianPage)
    // Ini akan disimulasikan dibuat setelah ujian ID 201 selesai
    703 => ['id_pengerjaan' => 703, 'id_ujian' => 201, 'skor' => 67, 'tanggalPengerjaan' => '26 Mei 2025', 'waktuDihabiskan' => '25 Menit 32 Detik',
        'jawabanUserPerSoal' => [ // Contoh jawaban untuk ujian ID 201
            1 => ['jawabanPengguna' => "Besarnya konstan dan arahnya menuju pusat lingkaran", 'isBenar' => true],
            2 => ['jawabanPengguna' => "√(H/g)", 'isBenar' => false],
            3 => ['jawabanPengguna' => "Teorema usaha energi adalah usaha sama dengan perubahan energi kinetik.", 'isBenar' => null],
        ]
    ],
];

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

Route::get('/dashboard', function () use ($masterMataKuliah, $masterSemuaUjian, $masterHistoriPengerjaan) {
    $daftarMataKuliahProcessed = array_map(function ($mk) use ($masterSemuaUjian) {
        $jumlahUjian = 0;
        foreach ($masterSemuaUjian as $ujian) {
            if ($ujian['mata_kuliah_id'] == $mk['id']) {
                $jumlahUjian++;
            }
        }
        return array_merge($mk, ['jumlah_ujian_tersedia' => $jumlahUjian]);
    }, $masterMataKuliah);

    $historiUjianProcessed = array_map(function ($attempt) use ($masterSemuaUjian, $masterMataKuliah) {
        $ujianDetail = $masterSemuaUjian[$attempt['id_ujian']] ?? null;
        $mataKuliahDetail = $ujianDetail ? ($masterMataKuliah[$ujianDetail['mata_kuliah_id']] ?? null) : null;
        return [
            'id_pengerjaan' => $attempt['id_pengerjaan'],
            'namaUjian' => $ujianDetail['nama'] ?? 'Ujian Tidak Ditemukan',
            'namaMataKuliah' => $mataKuliahDetail['nama'] ?? 'Mata Kuliah Tidak Ditemukan',
            'tanggalPengerjaan' => $attempt['tanggalPengerjaan'],
            'skor' => $attempt['skor'],
            'kkm' => $ujianDetail['kkm'] ?? 70, // Ambil KKM dari detail ujian atau default
        ];
    }, $masterHistoriPengerjaan);


    return Inertia::render('Dashboard', [
        'daftarMataKuliah' => array_values($daftarMataKuliahProcessed), // array_values untuk reindex
        'historiUjian' => $historiUjianProcessed,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () use ($masterMataKuliah, $masterSemuaUjian, $masterHistoriPengerjaan) { // Share master data jika perlu
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/user/profile', function() { return Inertia::render('Profile'); })->name('profile.show');

    Route::get('/mata-kuliah/{id_mata_kuliah}/ujian', function ($id_mata_kuliah) use ($masterMataKuliah, $masterSemuaUjian) {
        if (!isset($masterMataKuliah[$id_mata_kuliah])) { abort(404, 'Mata Kuliah tidak ditemukan.'); }
        $mataKuliah = (object) $masterMataKuliah[$id_mata_kuliah];
        
        $daftarUjianFiltered = [];
        foreach ($masterSemuaUjian as $ujian) {
            if ($ujian['mata_kuliah_id'] == $id_mata_kuliah) {
                // Untuk DaftarUjianPage, kita mungkin tidak perlu soalList lengkap
                $ujianData = $ujian;
                unset($ujianData['soalList']); // Hapus soalList agar tidak terlalu berat
                unset($ujianData['durasiTotalDetik']);
                $daftarUjianFiltered[] = $ujianData;
            }
        }
        return Inertia::render('Ujian/DaftarUjianPage', [
            'mataKuliah' => $mataKuliah,
            'daftarUjian' => $daftarUjianFiltered,
        ]);
    })->name('ujian.daftarPerMataKuliah');

    Route::get('/ujian/{id_ujian}/kerjakan', function ($id_ujian) use ($masterSemuaUjian, $masterMataKuliah) {
        if (!isset($masterSemuaUjian[$id_ujian])) { abort(404, 'Ujian tidak ditemukan.'); }
        $detailUjian = $masterSemuaUjian[$id_ujian];
        // Tambahkan nama mata kuliah ke detail ujian jika belum ada atau untuk konsistensi
        $detailUjian['namaMataKuliah'] = $masterMataKuliah[$detailUjian['mata_kuliah_id']]['nama'] ?? 'Mata Kuliah Tidak Diketahui';
        
        return Inertia::render('Ujian/PengerjaanUjianPage', [
            'idUjianAktif' => (int)$id_ujian,
            'detailUjianProp' => $detailUjian, // Kirim detail ujian lengkap
        ]);
    })->name('ujian.kerjakan');

    // Rute untuk halaman konfirmasi selesai ujian - sekarang menerima id_ujian
    Route::get('/ujian/{id_ujian}/selesai-konfirmasi', function ($id_ujian) use ($masterSemuaUjian, $masterMataKuliah) {
        if (!isset($masterSemuaUjian[$id_ujian])) { abort(404, 'Informasi ujian tidak ditemukan.'); }
        $ujianInfo = $masterSemuaUjian[$id_ujian];
        $mataKuliahInfo = $masterMataKuliah[$ujianInfo['mata_kuliah_id']] ?? null;

        $dataKonfirmasi = [
            'namaUjian' => $ujianInfo['nama'] ?? 'Ujian Telah Selesai',
            'namaMataKuliah' => $mataKuliahInfo['nama'] ?? 'Mata Kuliah Terkait',
        ];
        return Inertia::render('Ujian/KonfirmasiSelesaiUjianPage', $dataKonfirmasi);
    })->name('ujian.selesai.konfirmasi');

    Route::get('/ujian/hasil/{id_attempt}', function ($id_attempt) use ($masterHistoriPengerjaan, $masterSemuaUjian, $masterMataKuliah) {
        if (!isset($masterHistoriPengerjaan[$id_attempt])) { abort(404, 'Hasil pengerjaan ujian tidak ditemukan.'); }
        
        $attempt = $masterHistoriPengerjaan[$id_attempt];
        $ujianDetail = $masterSemuaUjian[$attempt['id_ujian']] ?? null;
        if (!$ujianDetail) { abort(404, 'Detail ujian untuk hasil ini tidak ditemukan.'); }

        $mataKuliahDetail = $masterMataKuliah[$ujianDetail['mata_kuliah_id']] ?? null;

        $hasilUjianData = [
            'idAttempt' => (int)$id_attempt,
            'namaMataKuliah' => $mataKuliahDetail['nama'] ?? 'Mata Kuliah Tidak Diketahui',
            'judulUjian' => $ujianDetail['nama'] ?? 'Judul Ujian Tidak Diketahui',
            'tanggalPengerjaan' => $attempt['tanggalPengerjaan'],
            'skorTotal' => $attempt['skor'],
            'kkm' => $ujianDetail['kkm'] ?? 70,
            'statusKelulusan' => ($attempt['skor'] >= ($ujianDetail['kkm'] ?? 70) ? "Lulus" : "Tidak Lulus"),
            'waktuDihabiskan' => $attempt['waktuDihabiskan'] ?? "N/A",
            // Hitung jumlah benar/salah berdasarkan jawaban user di attempt dan kunci jawaban di masterUjian
            // Ini contoh sederhana, Anda perlu logika perbandingan yang lebih baik
            'jumlahSoalBenar' => count(array_filter($attempt['jawabanUserPerSoal'] ?? [], fn($j) => $j['isBenar'] === true)),
            'jumlahSoalSalah' => count(array_filter($attempt['jawabanUserPerSoal'] ?? [], fn($j) => $j['isBenar'] === false)),
            'jumlahSoalTidakDijawab' => count($ujianDetail['soalList']) - count(array_filter($attempt['jawabanUserPerSoal'] ?? [], fn($j) => $j['isBenar'] !== null)),
            'detailSoalJawaban' => array_map(function($soalMaster) use ($attempt) {
                $jawabanAttempt = null;
                foreach (($attempt['jawabanUserPerSoal'] ?? []) as $idSoalAttempt => $dataAttempt) {
                    if ($idSoalAttempt == $soalMaster['id']) { // Cocokkan ID soal, bukan indeks
                        $jawabanAttempt = $dataAttempt;
                        break;
                    }
                }
                return [
                    'idSoal' => $soalMaster['id'],
                    'nomorSoal' => $soalMaster['nomor'],
                    'pertanyaan' => $soalMaster['pertanyaan'],
                    'tipeSoal' => $soalMaster['tipe'],
                    'opsiJawaban' => $soalMaster['opsi'] ?? null,
                    'jawabanPengguna' => $jawabanAttempt['jawabanPengguna'] ?? ($soalMaster['tipe'] === 'esai' ? '' : null),
                    'kunciJawaban' => $soalMaster['kunciJawaban'] ?? null,
                    'isBenar' => $jawabanAttempt['isBenar'] ?? null,
                    'penjelasan' => $soalMaster['penjelasan'] ?? null,
                ];
            }, $ujianDetail['soalList'])
        ];
        return Inertia::render('Ujian/DetailHasilUjianPage', ['hasilUjian' => $hasilUjianData]);
    })->name('ujian.hasil.detail');
});


require __DIR__.'/auth.php';
