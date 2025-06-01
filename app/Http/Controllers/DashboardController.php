<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; // Tambahkan Request
use Inertia\Inertia;
use App\Models\MataKuliah;
use App\Models\PengerjaanUjian;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class DashboardController extends Controller
{

    public function index(Request $request) // Tambahkan Request $request
    {

        // $user = $this->user_service()->getMyProfile();

        // dd($user['profile']);

        $user = $this->user_profile();

         dd($user);

        // Ambil parameter filter semester dari request
        $selectedSemester = $request->query('semester'); // contoh: ?semester=1
        $selectedTahunAjaran = $request->query('tahun_ajaran', '2024/2025'); // Default tahun ajaran jika tidak ada

        $mataKuliahQuery = MataKuliah::with(['dosen'])
            ->withCount('ujian AS jumlah_ujian_tersedia')
            ->tahunAjaran($selectedTahunAjaran); // Filter berdasarkan tahun ajaran (menggunakan scope)

        if ($selectedSemester && $selectedSemester !== 'semua') { // Jika ada filter semester dan bukan 'semua'
            $mataKuliahQuery->semester((int)$selectedSemester); // Filter berdasarkan semester (menggunakan scope)
        }

        $daftarMataKuliahProcessed = $mataKuliahQuery->orderBy('semester')->orderBy('nama_mata_kuliah')->get()
            ->map(function ($mk) {
                // ... (logika map yang sudah ada untuk dosen dan img) ...
                $dosenNama = 'Dosen Belum Ditugaskan';
                if ($mk->dosen) {
                    $dosenNama = $mk->dosen->name;
                }
                $imageUrl = $mk->icon_url ? asset('storage/' . trim($mk->icon_url, '/')) : '/images/placeholder-matakuliah.png';

                return [
                    'id' => $mk->id,
                    'nama' => $mk->nama_mata_kuliah,
                    'dosen' => ['nama' => $dosenNama],
                    'deskripsi_singkat' => $mk->deskripsi,
                    'img' => $imageUrl,
                    'jumlah_ujian_tersedia' => $mk->jumlah_ujian_tersedia,
                    'semester' => $mk->semester, // Kirim data semester
                    'tahun_ajaran' => $mk->tahun_ajaran, // Kirim data tahun ajaran
                ];
            });

        // Ambil daftar semester yang unik dari mata kuliah yang ada untuk tahun ajaran terpilih
        $availableSemesters = MataKuliah::where('tahun_ajaran', $selectedTahunAjaran)
                                ->distinct()
                                ->orderBy('semester', 'asc')
                                ->pluck('semester');

        // ... (logika historiUjianUntukDashboard tetap sama) ...
        $historiUjianUntukDashboard = [];
        if (Auth::check()) {
            $pengerjaanUjianTerakhir = PengerjaanUjian::where('user_id', Auth::id())
                ->with(['ujian.mataKuliah'])
                ->orderBy('waktu_selesai', 'desc')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            $historiUjianUntukDashboard = $pengerjaanUjianTerakhir->map(function ($attempt) {
                // ... (logika map histori ujian) ...
                 $ujian = $attempt->ujian;
                $mataKuliah = $ujian ? $ujian->mataKuliah : null;
                $kkm = $ujian ? ($ujian->kkm ?? 0) : 0;
                $statusKelulusan = "Belum Dinilai";
                if (isset($attempt->skor_total)) {
                    $statusKelulusan = ($attempt->skor_total >= $kkm ? "Lulus" : "Tidak Lulus");
                }
                return [
                    'id_pengerjaan' => $attempt->id,
                    'namaUjian' => $ujian->judul_ujian ?? 'Ujian Tidak Ditemukan',
                    'namaMataKuliah' => $mataKuliah->nama_mata_kuliah ?? 'Mata Kuliah Tidak Ditemukan',
                    'tanggalPengerjaan' => $attempt->waktu_selesai ? $attempt->waktu_selesai->format('d M Y') : ($attempt->created_at ? $attempt->created_at->format('d M Y') : 'N/A'),
                    'skor' => $attempt->skor_total,
                    'kkm' => $kkm,
                    'statusKelulusan' => $statusKelulusan,
                ];
            });
        }


        return Inertia::render('Dashboard', [
            'daftarMataKuliah' => $daftarMataKuliahProcessed,
            'historiUjian' => $historiUjianUntukDashboard,
            'availableSemesters' => $availableSemesters,
            'filters' => ['semester' => $selectedSemester, 'tahun_ajaran' => $selectedTahunAjaran] // <-- PASTIKAN INI DIKIRIM DENGAN BENAR
        ]);
    }
}