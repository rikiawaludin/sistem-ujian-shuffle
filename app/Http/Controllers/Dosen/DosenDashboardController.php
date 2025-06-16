<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\PengerjaanUjian;
use App\Http\Controllers\Dosen\Concerns\ManagesDosenAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Collection;

class DosenDashboardController extends Controller
{
    use ManagesDosenAuth;

    /**
     * Menampilkan dashboard utama untuk dosen.
     * Berisi daftar semua pengerjaan ujian dari ujian yang dibuat oleh dosen ini.
     */
    public function index(Request $request)
    {
        $authProps = $this->getAuthProps();
        if (!($authProps['user'] && $authProps['user']['is_dosen'])) {
            abort(403);
        }
        $dosenId = Auth::id();

        // Panggil metode baru untuk mendapatkan peta semester
        $semesterMap = $this->getSemesterMap($request);

        // 1. Ambil semua pengerjaan ujian yang relevan dengan dosen ini.
        // Eager load relasi yang dibutuhkan untuk menghindari N+1 query problem.
        $allPengerjaan = PengerjaanUjian::with([
            'user:id,email',
            'ujian.mataKuliah:id,nama,external_id', // Tetap ambil info mata kuliah
            'ujian:id,judul_ujian,mata_kuliah_id,dosen_pembuat_id'
        ])
        ->whereHas('ujian', fn($q) => $q->where('dosen_pembuat_id', $dosenId))
        ->whereIn('status_pengerjaan', ['selesai', 'selesai_waktu_habis', 'menunggu_penilaian'])
        ->get();

        // ---- PERUBAHAN UTAMA DI SINI ----
        // Kelompokkan hasil berdasarkan ID Ujian, bukan lagi ID Mata Kuliah.
        $groupedByUjian = $allPengerjaan->groupBy('ujian_id');

        // 3. Transformasi data ke format yang diinginkan frontend (sekarang per UJIAN).
        $dashboardData = $groupedByUjian->map(function (Collection $pengerjaanGroup) {
            
            // Ambil data Ujian dan Mata Kuliah dari item pertama.
            $ujian = $pengerjaanGroup->first()->ujian;
            $mataKuliah = $ujian->mataKuliah;

            // Ambil semester dari peta menggunakan external_id
            $semester = $semesterMap[$mataKuliah->external_id] ?? 'Tidak diketahui';

            // Buat daftar mahasiswa unik. Logika ini tetap sama.
            $studentsData = $pengerjaanGroup
                ->sortByDesc('waktu_selesai')
                ->unique('user_id')
                ->map(function ($pengerjaan) {
                    return [
                        'id' => $pengerjaan->user->id,
                        'name' => $pengerjaan->user->email, 
                        'score' => $pengerjaan->skor_total ?? 0,
                    ];
                })
                ->sortBy('name')
                ->values();

            // Struktur data yang dikirim ke frontend sekarang merepresentasikan satu UJIAN.
            return [
                'id' => $ujian->id,             // ID Ujian
                'name' => $ujian->judul_ujian,  // Judul Kartu adalah Judul Ujian
                'subjectName' => $mataKuliah->nama, // Nama Mata Kuliah sebagai sub-judul
                'semester' => $semester,
                'totalStudents' => $studentsData->count(),
                'students' => $studentsData,
            ];
        })->values(); // Reset keys collection utama

        return inertia('Dosen/Dashboard/Index', [
            'dashboardData' => $dashboardData,
            'auth' => $authProps,
        ]);
    }
}