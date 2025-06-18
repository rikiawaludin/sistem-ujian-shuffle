<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\PengerjaanUjian;
use App\Models\MataKuliah;
use App\Http\Controllers\Dosen\Concerns\ManagesDosenAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        // 1. Ambil daftar mata kuliah yang diajar dosen dari API
        // Kita hanya butuh ID-nya untuk query lokal
        $mataKuliahApi = $this->getDosenMataKuliahOptions($request);
        $mataKuliahIds = collect($mataKuliahApi)->pluck('value')->all();
        
        // 2. Ambil data mata kuliah dari database lokal beserta data agregatnya
        // Ini adalah query utama yang efisien untuk mendapatkan semua data yang dibutuhkan
        $courses = MataKuliah::whereIn('id', $mataKuliahIds)
            ->withCount([
                'soal',
                'ujian as active_exams_count' => function ($query) {
                    $query->whereIn('status_publikasi', ['published', 'scheduled'])
                          ->whereDate('tanggal_selesai', '>=', now());
                },
                // Query untuk menghitung jumlah mahasiswa unik berdasarkan pengerjaan ujian
                'pengerjaanUjian as students_count' => function ($query) {
                    $query->select(DB::raw('count(distinct user_id)'));
                }
            ])
            ->get();
        
        // Ambil juga peta semester dari API untuk melengkapi data
        $semesterMap = $this->getSemesterMap($request);
        
        // 3. Gabungkan data dari database lokal dengan data dari API (misal: semester)
        $dashboardData = $courses->map(function ($course) use ($semesterMap) {
            // Logika untuk menghitung mahasiswa per matkul bisa ditambahkan di sini jika ada datanya
            // Untuk saat ini kita beri nilai 0
            // $course->students_count = 0; 
            $course->semester = $semesterMap[$course->external_id] ?? 'N/A';
            return $course;
        });

        // 4. Siapkan data untuk kartu statistik di bagian atas
        $stats = [
            'total_courses' => $dashboardData->count(),
            'total_students' => $dashboardData->sum('students_count'), // Akan 0 untuk sekarang
            'active_exams' => $dashboardData->sum('active_exams_count'),
            'total_questions' => $dashboardData->sum('soal_count'),
        ];

        return Inertia::render('Dosen/Dashboard/Index', [
            'auth' => $authProps,
            'dashboardData' => $dashboardData,
            'stats' => $stats,
        ]);
    }
}