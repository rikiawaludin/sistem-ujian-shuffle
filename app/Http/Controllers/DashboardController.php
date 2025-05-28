<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\MataKuliah;
use App\Models\PengerjaanUjian;
use Illuminate\Support\Facades\Auth; // Import Auth facade

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $daftarMataKuliahProcessed = MataKuliah::with(['dosen']) // Eager load dosen
            ->withCount('ujian AS jumlah_ujian_tersedia')
            ->get()
            ->map(function ($mk) {
                $dosenNama = 'Dosen Belum Ditugaskan';
                if ($mk->dosen) {
                    $dosenNama = $mk->dosen->name;
                }

                // Menggunakan asset() untuk URL gambar jika disimpan di public/storage
                // Pastikan Anda sudah menjalankan `php artisan storage:link`
                // dan icon_url menyimpan path relatif dari folder 'storage/app/public/'
                // Contoh: jika icon_url adalah 'mata_kuliah_icons/kalkulus.jpg',
                // maka asset('storage/mata_kuliah_icons/kalkulus.jpg') akan menghasilkan URL yang benar.
                // Jika icon_url sudah URL absolut atau path publik langsung, sesuaikan.
                $imageUrl = $mk->icon_url ? asset('storage/' . trim($mk->icon_url, '/')) : '/images/placeholder-matakuliah.png';


                return [
                    'id' => $mk->id,
                    'nama' => $mk->nama_mata_kuliah,
                    'dosen' => ['nama' => $dosenNama],
                    'deskripsi_singkat' => $mk->deskripsi,
                    'img' => $imageUrl,
                    'jumlah_ujian_tersedia' => $mk->jumlah_ujian_tersedia,
                ];
            });

        $historiUjianUntukDashboard = [];
        if (Auth::check()) { // Gunakan Auth::check()
            $pengerjaanUjianTerakhir = PengerjaanUjian::where('user_id', Auth::id()) // Gunakan Auth::id()
                ->with(['ujian.mataKuliah'])
                ->orderBy('waktu_selesai', 'desc') // Urutkan berdasarkan waktu selesai, atau created_at jika waktu_selesai bisa null
                ->take(5)
                ->get();

            $historiUjianUntukDashboard = $pengerjaanUjianTerakhir->map(function ($attempt) {
                $ujian = $attempt->ujian;
                $mataKuliah = $ujian ? $ujian->mataKuliah : null;
                $kkm = $ujian ? ($ujian->kkm ?? 0) : 0; // Pastikan $ujian ada sebelum akses kkm
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
            // 'auth' => ['user' => Auth::user()] // Inertia biasanya otomatis membagikan data auth.user jika middleware ShareInertiaData dikonfigurasi
        ]);
    }
}