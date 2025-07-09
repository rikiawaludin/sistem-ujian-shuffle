<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\JawabanPesertaDetail;
use App\Models\PengerjaanUjian;
use App\Models\Ujian;
use App\Services\SkorAkhir;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class HasilUjianController extends Controller
{
    /**
     * Menampilkan daftar pengerjaan ujian yang perlu dinilai.
     */
    public function index(Ujian $ujian)
    {
        if ($ujian->dosen_pembuat_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke hasil ujian ini.');
        }

        $ujian->load(['pengerjaanUjian.user:id,email']); // Load relasi

        $pengerjaanMenunggu = $ujian->pengerjaanUjian()
            ->where('status_pengerjaan', 'menunggu_penilaian')
            ->with('user:id,email') // Ambil nama user
            ->get();
            
        $pengerjaanSelesai = $ujian->pengerjaanUjian()
            ->where('status_pengerjaan', 'selesai')
            ->with('user:id,email')
            ->get();

        return Inertia::render('Dosen/Hasil/Index', [ // Halaman frontend yang akan kita buat
            'ujian' => $ujian,
            'pengerjaanMenunggu' => $pengerjaanMenunggu,
            'pengerjaanSelesai' => $pengerjaanSelesai,
        ]);
    }

    /**
     * Menampilkan form untuk mengoreksi jawaban esai satu mahasiswa.
     */
    public function showKoreksiForm(PengerjaanUjian $pengerjaan)
    {

        $pengerjaan->load([
            'user:id,name,email',
            'ujian' => function ($query) {
                // Ambil judul ujian dan soal dari ujian beserta pivotnya untuk mendapatkan bobot
                $query->select('id', 'judul_ujian')->with(['soal' => function ($q) {
                    $q->select('soal.id')->withPivot('bobot_nilai_soal');
                }]);
            },
            'detailJawaban' => function ($query) {
                // Filter hanya untuk soal esai
                $query->whereHas('soal', function ($q) {
                    $q->where('tipe_soal', 'esai');
                })
                // Load soal yang terkait dengan jawaban
                ->with('soal:id,pertanyaan');
            }
        ]);

        // Buat map dari soal_id ke bobot_nilai_soal untuk kemudahan akses
        $bobotSoalMap = $pengerjaan->ujian->soal->pluck('pivot.bobot_nilai_soal', 'id');

        // Tambahkan bobot ke setiap objek detail jawaban
        // Ini akan memodifikasi koleksi yang ada di dalam objek $pengerjaan
        $pengerjaan->detail_jawaban->each(function ($jawaban) use ($bobotSoalMap) {
            // Kita tambahkan properti baru 'bobot_maksimal' ke objek 'soal' yang sudah ada
            $jawaban->soal->bobot_maksimal = $bobotSoalMap[$jawaban->soal_id] ?? 0;
        });

        return Inertia::render('Dosen/Hasil/Koreksi', [ // Halaman frontend untuk koreksi
            'pengerjaan' => $pengerjaan
        ]);
    }

    /**
     * Menyimpan skor untuk satu soal esai.
     */
    public function simpanSkorEsai(Request $request, JawabanPesertaDetail $jawabanDetail)
    {
        // DITAMBAHKAN: Eager load relasi untuk otorisasi
        $jawabanDetail->load('pengerjaanUjian.ujian:id,dosen_pembuat_id');

        // DITAMBAHKAN: Otorisasi - Cek kepemilikan melalui relasi yang lebih dalam
        if ($jawabanDetail->pengerjaanUjian->ujian->dosen_pembuat_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses untuk menyimpan skor ini.');
        }

        // Ambil bobot maksimal soal ini dari tabel pivot secara langsung dan efisien
        $pivotData = DB::table('ujian_soal')
            ->where('ujian_id', $jawabanDetail->pengerjaanUjian->ujian_id)
            ->where('soal_id', $jawabanDetail->soal_id)
            ->first();

        // Jika data pivot tidak ditemukan atau bobotnya null, gunakan 0 sebagai fallback aman.
        // Ini PENTING untuk mencegah nilai null masuk ke validasi.
        $bobotMaksimalSoal = $pivotData->bobot_nilai_soal ?? 0;

        // Validasi input dari dosen dengan batas maksimal yang sudah pasti
        $request->validate([
            'skor_per_soal' => "required|numeric|min:0|max:{$bobotMaksimalSoal}",
        ]);

        // Jika validasi lolos, simpan skornya
        $jawabanDetail->update([
            'skor_per_soal' => $request->skor_per_soal,
        ]);

        $pengerjaan = $jawabanDetail->pengerjaanUjian;
        $semuaJawabanEsai = $pengerjaan->detailJawaban()
                                    ->whereHas('soal', fn($q) => $q->where('tipe_soal', 'esai'))
                                    ->get();

        // Cek apakah ada jawaban esai yang skornya masih null
        $masihAdaYangBelumDinilai = $semuaJawabanEsai->contains('skor_per_soal', null);
        
        // Kirim flash message ke session yang akan dibaca oleh Inertia
        return back()->with([
            'success' => 'Skor berhasil disimpan.',
            'all_essays_graded' => !$masihAdaYangBelumDinilai, // Kirim status ini ke frontend
        ]);

        return back()->with('success', 'Skor berhasil disimpan.');
    }

    /**
     * Memfinalisasi skor total setelah semua esai dinilai.
     */
    public function finalisasiSkor(Request $request, PengerjaanUjian $pengerjaan, SkorAkhir $calculator)
    {
        $pengerjaan->load('ujian:id,dosen_pembuat_id');

        // DITAMBAHKAN: Otorisasi - Cek kepemilikan sebelum finalisasi
        if ($pengerjaan->ujian->dosen_pembuat_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses untuk finalisasi skor ini.');
        }

        // Hitung skor akhir menggunakan service yang sudah ada
        $skorTotal = $calculator->calculate($pengerjaan);

        $pengerjaan->update([
            'skor_total' => $skorTotal,
            'status_pengerjaan' => 'selesai',
        ]);
        
        // Redirect kembali ke halaman daftar penilaian untuk ujian ini
        return redirect()->route('dosen.ujian.hasil.index', $pengerjaan->ujian_id)
                         ->with('success', 'Skor akhir berhasil dihitung dan disimpan!');
    }
}