<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\MataKuliah; // [ DIBERIKAN ]
use App\Models\Ujian; // [ DIBERIKAN ]
use App\Models\PengerjaanUjian; // Asumsi Anda memiliki model ini
use App\Models\User; // Asumsi Anda memiliki model ini untuk dosen_pembuat_id, user_id dll.
// Tambahkan JawabanPesertaDetail jika Anda perlu melakukan query langsung, meskipun sering diakses melalui PengerjaanUjian
use App\Models\JawabanPesertaDetail; // Asumsi Anda memiliki model ini

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Di sini Anda dapat mendaftarkan rute web untuk aplikasi Anda. Rute-rute
| ini dimuat oleh RouteServiceProvider dalam grup yang
| berisi middleware "web". Sekarang buat sesuatu yang hebat!
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
    // Ambil Mata Kuliah beserta jumlah Ujian
    $daftarMataKuliahProcessed = MataKuliah::withCount('ujian AS jumlah_ujian_tersedia')
        ->get()
        ->map(function ($mk) {
            // Simulasikan dosen dan img untuk saat ini, ganti dengan relasi/field aktual jika ada
            return [
                'id' => $mk->id,
                'nama' => $mk->nama_mata_kuliah, // [ DIBERIKAN ]
                'dosen' => ['nama' => $mk->dosen->name ?? 'Dosen Pengampu'], // Asumsi MataKuliah memiliki relasi 'dosen' ke model User
                'deskripsi_singkat' => $mk->deskripsi, // [ DIBERIKAN ]
                'img' => $mk->icon_url ?? '/images/default-course.png', // Gunakan icon_url dari db
                'jumlah_ujian_tersedia' => $mk->jumlah_ujian_tersedia,
            ];
        });

    // Ambil Histori Ujian (misalnya, 5 terakhir untuk pengguna yang terautentikasi)
    $historiUjianUntukDashboard = [];
    if (auth()->check()) {
        $pengerjaanUjianTerakhir = PengerjaanUjian::where('user_id', auth()->id())
            ->with(['ujian.mataKuliah']) // Eager load Ujian dan MataKuliah-nya
            ->orderBy('created_at', 'desc') // Ambil yang terbaru
            ->take(5) // Batasi hingga 5, misalnya
            ->get();

        $historiUjianUntukDashboard = $pengerjaanUjianTerakhir->map(function ($attempt) {
            $ujian = $attempt->ujian;
            $mataKuliah = $ujian ? $ujian->mataKuliah : null;
            $kkm = $ujian->kkm ?? 0; // [ DIBERIKAN ]
            $statusKelulusan = "Belum Dinilai";
            if (isset($attempt->skor_total)) {
                 $statusKelulusan = ($attempt->skor_total >= $kkm ? "Lulus" : "Tidak Lulus");
            }

            return [
                'id_pengerjaan' => $attempt->id,
                'namaUjian' => $ujian->judul_ujian ?? 'Ujian Tidak Ditemukan', // [ DIBERIKAN ]
                'namaMataKuliah' => $mataKuliah->nama_mata_kuliah ?? 'Mata Kuliah Tidak Ditemukan', // [ DIBERIKAN ]
                'tanggalPengerjaan' => $attempt->waktu_selesai ? $attempt->waktu_selesai->format('d M Y') : $attempt->created_at->format('d M Y'),
                'skor' => $attempt->skor_total,
                'kkm' => $kkm,
                'statusKelulusan' => $statusKelulusan,
            ];
        });
    }

    return Inertia::render('Dashboard', [
        'daftarMataKuliah' => $daftarMataKuliahProcessed,
        'historiUjian' => $historiUjianUntukDashboard,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // Route::get('/user/profile', function() { return Inertia::render('Profile'); })->name('profile.show'); // Ini sepertinya halaman statis, atau mungkin juga memerlukan data

    Route::get('/mata-kuliah/{id_mata_kuliah}/ujian', function ($id_mata_kuliah) {
        $mataKuliah = MataKuliah::with(['dosen'])->find($id_mata_kuliah); // Asumsi ada relasi dosen pada MataKuliah
        if (!$mataKuliah) {
            abort(404, 'Mata Kuliah tidak ditemukan.');
        }

        // Ambil Ujian untuk Mata Kuliah ini
        // Pilih hanya field yang diperlukan untuk menghindari pengiriman data yang terlalu banyak
        $daftarUjianFiltered = Ujian::where('mata_kuliah_id', $id_mata_kuliah) // [ DIBERIKAN ]
            ->select(['id', 'mata_kuliah_id', 'judul_ujian as nama', 'deskripsi', 'durasi', 'kkm', 'tanggal_selesai as batasWaktuPengerjaan', 'status_publikasi as status']) // [ DIBERIKAN ]
            // Tambahkan field lain sesuai kebutuhan DaftarUjianPage, pastikan namanya cocok
            ->get()
            ->map(function($ujian) {
                // Anda mungkin perlu menentukan 'jumlahSoal' dan 'id_pengerjaan_terakhir', 'skor' jika diperlukan
                // 'jumlahSoal' bisa berupa hitungan soal terkait: $ujian->soal()->count() (memerlukan relasi 'soal')
                // 'status' mungkin memerlukan pemetaan dari 'status_publikasi'
                // 'id_pengerjaan_terakhir' dan 'skor' akan memerlukan pemeriksaan PengerjaanUjian untuk pengguna saat ini
                $pengerjaanTerakhir = PengerjaanUjian::where('ujian_id', $ujian->id)
                                        ->where('user_id', auth()->id())
                                        ->orderBy('created_at', 'desc')
                                        ->first();
                return [
                    'id' => $ujian->id,
                    'mata_kuliah_id' => $ujian->mata_kuliah_id,
                    'nama' => $ujian->nama,
                    'deskripsi' => $ujian->deskripsi,
                    'durasi' => $ujian->durasi . " Menit", // Format sesuai kebutuhan
                    'jumlahSoal' => $ujian->soal()->count(), // Memerlukan relasi 'soal' dan pengaturan tabel pivot
                    'batasWaktuPengerjaan' => $ujian->batasWaktuPengerjaan ? \Carbon\Carbon::parse($ujian->batasWaktuPengerjaan)->format('d F Y') : 'Kapan saja',
                    'status' => $pengerjaanTerakhir ? ($pengerjaanTerakhir->status_pengerjaan == 'selesai' ? 'Selesai' : 'Belum Selesai') : 'Belum Dikerjakan', // Logika yang lebih kompleks mungkin diperlukan
                    'kkm' => $ujian->kkm, // [ DIBERIKAN ]
                    'id_pengerjaan_terakhir' => $pengerjaanTerakhir->id ?? null,
                    'skor' => $pengerjaanTerakhir->skor_total ?? null,
                ];
            });


        return Inertia::render('Ujian/DaftarUjianPage', [
            'mataKuliah' => (object)[ // Casting ke object agar sesuai struktur asli jika dibutuhkan frontend
                'id' => $mataKuliah->id,
                'nama' => $mataKuliah->nama_mata_kuliah, // [ DIBERIKAN ]
                'dosen' => ['nama' => $mataKuliah->dosen->name ?? 'Dosen Pengampu'], // [ DIBERIKAN ]
                'deskripsi_singkat' => $mataKuliah->deskripsi, // [ DIBERIKAN ]
                'img' => $mataKuliah->icon_url ?? '/images/default-course.png', // [ DIBERIKAN ]
            ],
            'daftarUjian' => $daftarUjianFiltered
        ]);
    })->name('ujian.daftarPerMataKuliah');

    Route::get('/ujian/{id_ujian}/kerjakan', function ($id_ujian) {
        $ujian = Ujian::find((int)$id_ujian); // [ DIBERIKAN ]
        if (!$ujian) {
            abort(404, 'Ujian tidak ditemukan di database.');
        }
        // PengerjaanUjianPage.jsx sekarang mengambil datanya sendiri melalui API
        // Jadi, kita hanya perlu meneruskan ID.
        return Inertia::render('Ujian/PengerjaanUjianPage', ['idUjianAktif' => (int)$id_ujian]); // [ DIBERIKAN ]
    })->name('ujian.kerjakan');

    Route::get('/ujian/{id_ujian}/selesai-konfirmasi', function ($id_ujian) {
        $ujian = Ujian::with('mataKuliah')->find((int)$id_ujian); // [ DIBERIKAN ]
        if (!$ujian) {
            abort(404, 'Informasi ujian tidak ditemukan.');
        }
        $dataKonfirmasi = [
            'namaUjian' => $ujian->judul_ujian ?? 'Ujian Telah Selesai', // [ DIBERIKAN ]
            'namaMataKuliah' => $ujian->mataKuliah->nama_mata_kuliah ?? 'Mata Kuliah Terkait' // [ DIBERIKAN ]
        ];
        return Inertia::render('Ujian/KonfirmasiSelesaiUjianPage', $dataKonfirmasi);
    })->name('ujian.selesai.konfirmasi');

    Route::get('/ujian/hasil/{id_attempt}', function ($id_attempt) {
        $attempt = PengerjaanUjian::with([
            'ujian.mataKuliah', // [ DIBERIKAN ]
            'ujian.soal', // Eager load soal untuk ujian
            'jawabanPesertaDetail.soal' // Eager load soal terkait setiap detail jawaban
        ])->find((int)$id_attempt);

        if (!$attempt || $attempt->user_id !== auth()->id()) { // Pastikan pengguna hanya bisa melihat hasil ujiannya sendiri
            abort(404, 'Hasil pengerjaan ujian tidak ditemukan atau tidak diizinkan.');
        }

        $ujianDetail = $attempt->ujian;
        if (!$ujianDetail) {
            abort(404, 'Detail ujian untuk hasil ini tidak ditemukan.');
        }

        $mataKuliahDetail = $ujianDetail->mataKuliah;
        $kkmUjian = $ujianDetail->kkm ?? 0; // [ DIBERIKAN ]
        $statusKelulusan = "Belum Dinilai";
         if (isset($attempt->skor_total)) {
            $statusKelulusan = ($attempt->skor_total >= $kkmUjian ? "Lulus" : "Tidak Lulus");
        }


        // Petakan jawaban peserta ke soal ujian
        $jawabanUserPerSoalMap = $attempt->jawabanPesertaDetail->keyBy('soal_id');

        $detailSoalJawaban = $ujianDetail->soal->map(function($soalMaster, $index) use ($jawabanUserPerSoalMap) { // [ DIBERIKAN ]
            $jawabanDataAttempt = $jawabanUserPerSoalMap->get($soalMaster->id);

            return [
                'idSoal' => $soalMaster->id, // [ DIBERIKAN ]
                'nomorSoal' => $index + 1, // Asumsi soal terurut atau gunakan pivot 'nomor_urut_di_ujian' jika tersedia
                'pertanyaan' => $soalMaster->pertanyaan, // [ DIBERIKAN ]
                'tipeSoal' => $soalMaster->tipe_soal, // [ DIBERIKAN ]
                'opsiJawaban' => $soalMaster->opsi_jawaban, // Asumsi 'opsi_jawaban' di-cast ke array/json di model Soal
                'jawabanPengguna' => $jawabanDataAttempt->jawaban_user ?? ($soalMaster->tipe_soal === 'esai' ? '' : null), // [ DIBERIKAN ]
                'kunciJawaban' => $soalMaster->kunci_jawaban, // Asumsi 'kunci_jawaban' di-cast ke array/json di model Soal
                'isBenar' => $jawabanDataAttempt->is_benar ?? null,
                'penjelasan' => $soalMaster->penjelasan, // [ DIBERIKAN ]
            ];
        });

        // Hitung ringkasan: jumlahBenar, jumlahSalah, jumlahTidakDijawab
        $jumlahSoal = $ujianDetail->soal->count(); // [ DIBERIKAN ]
        $jumlahBenar = 0;
        $jumlahSalah = 0;
        $jumlahDijawab = 0;

        foreach($attempt->jawabanPesertaDetail as $jawaban) {
            if ($jawaban->jawaban_user !== null && $jawaban->jawaban_user !== '') {
                $jumlahDijawab++;
            }
            if ($jawaban->is_benar === true) $jumlahBenar++;
            else if ($jawaban->is_benar === false) $jumlahSalah++;
        }
        // Untuk soal esai, is_benar mungkin null sampai dinilai.
        // 'jumlahTidakDijawab' yang lebih presisi mungkin perlu memeriksa apakah jawaban_user null atau kosong.
        $jumlahTidakDijawab = $jumlahSoal - $jumlahDijawab;


        $hasilUjianData = [
            'idAttempt' => $attempt->id,
            'namaMataKuliah' => $mataKuliahDetail->nama_mata_kuliah ?? 'Mata Kuliah Tidak Diketahui', // [ DIBERIKAN ]
            'judulUjian' => $ujianDetail->judul_ujian ?? 'Judul Ujian Tidak Diketahui', // [ DIBERIKAN ]
            'tanggalPengerjaan' => $attempt->waktu_selesai ? $attempt->waktu_selesai->format('d M Y, H:i') : 'N/A',
            'skorTotal' => $attempt->skor_total,
            'kkm' => $kkmUjian, // [ DIBERIKAN ]
            'statusKelulusan' => $statusKelulusan,
            'waktuDihabiskan' => $attempt->waktu_dihabiskan_detik ? gmdate("H\j i\m d\d", $attempt->waktu_dihabiskan_detik) : "N/A", // Format ke Jam, Menit, Detik
            'jumlahSoalBenar' => $jumlahBenar,
            'jumlahSoalSalah' => $jumlahSalah,
            'jumlahSoalTidakDijawab' => $jumlahTidakDijawab,
            'detailSoalJawaban' => $detailSoalJawaban,
        ];

        return Inertia::render('Ujian/DetailHasilUjianPage', ['hasilUjian' => $hasilUjianData]);
    })->name('ujian.hasil.detail');

    Route::get('/ujian/riwayat', function () {
        $semuaHistoriUjian = [];
        if (auth()->check()) {
            $pengerjaanUjian = PengerjaanUjian::where('user_id', auth()->id())
                ->with(['ujian.mataKuliah']) // [ DIBERIKAN ]
                ->orderBy('created_at', 'desc')
                ->get();

            $semuaHistoriUjian = $pengerjaanUjian->map(function ($attempt) {
                $ujian = $attempt->ujian;
                $mataKuliah = $ujian ? $ujian->mataKuliah : null;
                $kkmUjian = $ujian->kkm ?? 0; // [ DIBERIKAN ]
                $statusKelulusan = "Belum Dinilai";
                if (isset($attempt->skor_total)) {
                    $statusKelulusan = ($attempt->skor_total >= $kkmUjian ? "Lulus" : "Tidak Lulus");
                }

                return [
                    'id_pengerjaan' => $attempt->id,
                    'namaUjian' => $ujian->judul_ujian ?? 'Ujian Tidak Ditemukan', // [ DIBERIKAN ]
                    'namaMataKuliah' => $mataKuliah->nama_mata_kuliah ?? 'Mata Kuliah Tidak Ditemukan', // [ DIBERIKAN ]
                    'tanggalPengerjaan' => $attempt->waktu_selesai ? $attempt->waktu_selesai->format('d M Y') : $attempt->created_at->format('d M Y'),
                    'skor' => $attempt->skor_total,
                    'kkm' => $kkmUjian,
                    'statusKelulusan' => $statusKelulusan,
                ];
            });
        }
        // Asumsi Anda akan membuat 'Ujian/HistoriUjianListPage.jsx' atau sejenisnya
        return Inertia::render('Ujian/HistoriUjianListPage', ['semuaHistoriUjian' => $semuaHistoriUjian]);
        // Jika halaman belum siap, Anda bisa mengarahkan seperti sebelumnya:
        // return redirect()->route('dashboard')->with('info', 'Halaman histori ujian terpisah belum diimplementasikan.');
    })->name('ujian.riwayat');
});

require __DIR__.'/auth.php';