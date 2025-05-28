<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\MataKuliah;
use App\Models\Ujian;
use App\Models\PengerjaanUjian;
use App\Models\Soal; // diperlukan untuk casting opsi di detail hasil
use Illuminate\Support\Facades\Auth; // Import Auth facade
use Carbon\Carbon; // Import Carbon untuk format tanggal

class ListUjianController extends Controller
{
    /**
     * Menampilkan daftar ujian untuk mata kuliah tertentu.
     */
    public function daftarPerMataKuliah($id_mata_kuliah)
    {
        $mataKuliah = MataKuliah::with(['dosen'])->findOrFail($id_mata_kuliah);

        $daftarUjianFiltered = Ujian::where('mata_kuliah_id', $id_mata_kuliah)
            // ->where('status_publikasi', 'terbit') // Mungkin Anda hanya ingin menampilkan ujian yang sudah terbit
            ->select(['id', 'judul_ujian', 'deskripsi', 'durasi', 'kkm', 'tanggal_selesai', 'status_publikasi'])
            ->get()
            ->map(function($ujianItem) { // Ganti nama variabel agar tidak konflik
                $pengerjaanTerakhir = null;
                if (Auth::check()) {
                    $pengerjaanTerakhir = PengerjaanUjian::where('ujian_id', $ujianItem->id)
                                            ->where('user_id', Auth::id())
                                            ->orderBy('created_at', 'desc')
                                            ->first();
                }

                $statusUjian = 'Belum Dikerjakan';
                if ($pengerjaanTerakhir) {
                    if ($pengerjaanTerakhir->status_pengerjaan === 'selesai') {
                        $statusUjian = 'Selesai';
                    } elseif ($pengerjaanTerakhir->status_pengerjaan === 'sedang_dikerjakan') {
                        // Logika untuk 'Sedang Dikerjakan' mungkin perlu pemeriksaan waktu tersisa
                        // Untuk saat ini, kita bisa tandai saja
                        $statusUjian = 'Sedang Dikerjakan';
                    }
                } elseif ($ujianItem->status_publikasi !== 'terbit') {
                    $statusUjian = 'Tidak Tersedia'; // Atau 'Segera Hadir', 'Ditutup' dll.
                }


                return [
                    'id' => $ujianItem->id,
                    // 'mata_kuliah_id' => $ujianItem->mata_kuliah_id, // Tidak perlu jika sudah di scope mata kuliah
                    'nama' => $ujianItem->judul_ujian,
                    'deskripsi' => $ujianItem->deskripsi,
                    'durasi' => $ujianItem->durasi . " Menit",
                    'jumlahSoal' => $ujianItem->soal()->count(), // Relasi 'soal' di model Ujian
                    'batasWaktuPengerjaan' => $ujianItem->tanggal_selesai ? Carbon::parse($ujianItem->tanggal_selesai)->format('d F Y, H:i') : 'Fleksibel',
                    'status' => $statusUjian,
                    'kkm' => $ujianItem->kkm,
                    'id_pengerjaan_terakhir' => $pengerjaanTerakhir->id ?? null,
                    'skor' => $pengerjaanTerakhir->skor_total ?? null,
                ];
            });

        $dosenNama = 'Dosen Belum Ditugaskan';
        if ($mataKuliah->dosen) {
            $dosenNama = $mataKuliah->dosen->name;
        }
        $imageUrl = $mataKuliah->icon_url ? asset('storage/' . trim($mataKuliah->icon_url, '/')) : '/images/placeholder-matakuliah.png';

        return Inertia::render('Ujian/DaftarUjianPage', [
            'mataKuliah' => (object)[ // Kirim sebagai objek jika frontend mengharapkannya
                'id' => $mataKuliah->id,
                'nama' => $mataKuliah->nama_mata_kuliah,
                'dosen' => ['nama' => $dosenNama],
                'deskripsi_singkat' => $mataKuliah->deskripsi,
                'img' => $imageUrl,
            ],
            'daftarUjian' => $daftarUjianFiltered
        ]);
    }

    /**
     * Menampilkan halaman pengerjaan ujian.
     * Frontend akan mengambil soal via API.
     */
    public function kerjakanUjian($id_ujian)
    {
        // Hanya perlu memastikan ujian ada dan pengguna berhak mengakses (jika ada logika tambahan)
        $ujian = Ujian::select('id', 'judul_ujian')->findOrFail((int)$id_ujian);
        // Anda bisa menambahkan pengecekan apakah ujian sudah/boleh dimulai oleh user ini
        return Inertia::render('Ujian/PengerjaanUjianPage', ['idUjianAktif' => $ujian->id]);
    }

    /**
     * Menampilkan halaman konfirmasi setelah ujian selesai.
     */
    public function konfirmasiSelesaiUjian($id_ujian)
    {
        $ujian = Ujian::with('mataKuliah:id,nama_mata_kuliah') // Hanya ambil kolom yang perlu dari mataKuliah
                    ->select('id', 'judul_ujian', 'mata_kuliah_id')
                    ->findOrFail((int)$id_ujian);
        return Inertia::render('Ujian/KonfirmasiSelesaiUjianPage', [
            'namaUjian' => $ujian->judul_ujian,
            'namaMataKuliah' => $ujian->mataKuliah->nama_mata_kuliah ?? 'Mata Kuliah Tidak Diketahui'
        ]);
    }

    /**
     * Menampilkan detail hasil pengerjaan ujian.
     */
    public function detailHasilUjian($id_attempt)
    {
        $attempt = PengerjaanUjian::with([
            'ujian:id,judul_ujian,kkm,mata_kuliah_id', // Pilih kolom spesifik dari Ujian
            'ujian.mataKuliah:id,nama_mata_kuliah', // Pilih kolom spesifik dari MataKuliah
            'ujian.soal', // Ambil semua soal dari ujian tersebut
            'detailJawaban.soal:id,pertanyaan,tipe_soal,opsi_jawaban,kunci_jawaban,penjelasan' // Pilih kolom spesifik dari Soal yang dijawab
        ])
        ->where('user_id', Auth::id()) // Pastikan user hanya bisa lihat miliknya
        ->findOrFail((int)$id_attempt);

        $ujianDetail = $attempt->ujian;
        if (!$ujianDetail) { // Seharusnya tidak terjadi karena findOrFail di atas, tapi sebagai pengaman
            abort(404, 'Detail ujian untuk hasil ini tidak ditemukan.');
        }
        $mataKuliahDetail = $ujianDetail->mataKuliah;

        $kkmUjian = $ujianDetail->kkm ?? 0;
        $statusKelulusan = "Belum Dinilai";
        if (isset($attempt->skor_total)) {
            $statusKelulusan = ($attempt->skor_total >= $kkmUjian ? "Lulus" : "Tidak Lulus");
        }

        $jawabanUserPerSoalMap = $attempt->detailJawaban->keyBy('soal_id');

        // Ambil semua soal yang seharusnya ada di ujian tersebut
        // Kemudian gabungkan dengan jawaban peserta
        $detailSoalJawaban = $ujianDetail->soal->map(function($soalMasterUjian, $index) use ($jawabanUserPerSoalMap) {
            $jawabanDataAttempt = $jawabanUserPerSoalMap->get($soalMasterUjian->id);

            return [
                'idSoal' => $soalMasterUjian->id,
                'nomorSoal' => $soalMasterUjian->pivot->nomor_urut_di_ujian ?? ($index + 1),
                'pertanyaan' => $soalMasterUjian->pertanyaan,
                'tipeSoal' => $soalMasterUjian->tipe_soal,
                'opsiJawaban' => $soalMasterUjian->opsi_jawaban, // Ini sudah di-cast oleh model Soal
                'jawabanPengguna' => $jawabanDataAttempt->jawaban_user ?? ($soalMasterUjian->tipe_soal === 'esai' ? '' : null), // jawaban_user juga di-cast di JawabanPesertaDetail
                'kunciJawaban' => $soalMasterUjian->kunci_jawaban, // Ini sudah di-cast
                'isBenar' => $jawabanDataAttempt->is_benar ?? null,
                'penjelasan' => $soalMasterUjian->penjelasan,
            ];
        });

        $jumlahSoalDiUjian = $ujianDetail->soal->count();
        $jumlahBenar = $attempt->detailJawaban->where('is_benar', true)->count();
        // $jumlahSalah = $attempt->detailJawaban->where('is_benar', false)->count();
        // Untuk jumlah salah, kita perlu lebih hati-hati jika ada soal yang tidak dinilai (is_benar === null)
        $jumlahSalah = $attempt->detailJawaban->filter(function ($item) {
            return $item->is_benar === false;
        })->count();

        $jumlahDijawab = $attempt->detailJawaban->filter(function ($item) {
            // Definisi dijawab bisa bervariasi, misal tidak null dan tidak string kosong
            return $item->jawaban_user !== null && 
                   (is_string($item->jawaban_user) ? trim($item->jawaban_user) !== '' : true);
        })->count();
        $jumlahTidakDijawab = $jumlahSoalDiUjian - $jumlahDijawab;


        $hasilUjianData = [
            'idAttempt' => $attempt->id,
            'namaMataKuliah' => $mataKuliahDetail->nama_mata_kuliah ?? 'Mata Kuliah Tidak Diketahui',
            'judulUjian' => $ujianDetail->judul_ujian ?? 'Judul Ujian Tidak Diketahui',
            'tanggalPengerjaan' => $attempt->waktu_selesai ? $attempt->waktu_selesai->format('d M Y, H:i') : ($attempt->created_at ? $attempt->created_at->format('d M Y, H:i') : 'N/A'),
            'skorTotal' => $attempt->skor_total,
            'kkm' => $kkmUjian,
            'statusKelulusan' => $statusKelulusan,
            'waktuDihabiskan' => $attempt->waktu_dihabiskan_detik ? gmdate("H\j i\m s\d", $attempt->waktu_dihabiskan_detik) : "N/A", // s bukan d
            'jumlahSoalBenar' => $jumlahBenar,
            'jumlahSoalSalah' => $jumlahSalah,
            'jumlahSoalTidakDijawab' => $jumlahTidakDijawab,
            'detailSoalJawaban' => $detailSoalJawaban,
        ];

        return Inertia::render('Ujian/DetailHasilUjianPage', ['hasilUjian' => $hasilUjianData]);
    }

    /**
     * Menampilkan halaman riwayat semua ujian pengguna.
     */
    public function riwayatUjian()
    {
        $semuaHistoriUjian = [];
        if (Auth::check()) {
            $pengerjaanUjian = PengerjaanUjian::where('user_id', Auth::id())
                ->with(['ujian:id,judul_ujian,kkm,mata_kuliah_id', 'ujian.mataKuliah:id,nama_mata_kuliah'])
                ->orderBy('waktu_selesai', 'desc') // Urutkan berdasarkan waktu selesai
                ->orderBy('created_at', 'desc')   // Lalu berdasarkan waktu dibuat
                ->get();

            $semuaHistoriUjian = $pengerjaanUjian->map(function ($attempt) {
                $ujian = $attempt->ujian;
                $mataKuliah = $ujian ? $ujian->mataKuliah : null;
                $kkmUjian = $ujian ? ($ujian->kkm ?? 0) : 0;
                $statusKelulusan = "Belum Dinilai";

                if (isset($attempt->skor_total)) {
                    $statusKelulusan = ($attempt->skor_total >= $kkmUjian ? "Lulus" : "Tidak Lulus");
                }

                return [
                    'id_pengerjaan' => $attempt->id,
                    'namaUjian' => $ujian->judul_ujian ?? 'Ujian Tidak Ditemukan',
                    'namaMataKuliah' => $mataKuliah->nama_mata_kuliah ?? 'Mata Kuliah Tidak Ditemukan',
                    'tanggalPengerjaan' => $attempt->waktu_selesai ? $attempt->waktu_selesai->format('d M Y') : ($attempt->created_at ? $attempt->created_at->format('d M Y') : 'N/A'),
                    'skor' => $attempt->skor_total,
                    'kkm' => $kkmUjian,
                    'statusKelulusan' => $statusKelulusan,
                ];
            });
        }
        return Inertia::render('Ujian/HistoriUjianListPage', ['semuaHistoriUjian' => $semuaHistoriUjian]);
    }
}