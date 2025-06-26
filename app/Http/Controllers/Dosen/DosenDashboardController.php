<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\PengerjaanUjian;
use App\Models\MataKuliah;
use App\Models\Ujian;
use App\Models\Soal;
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
    // public function index(Request $request)
    // {
    //     $authProps = $this->getAuthProps();
    //     if (!($authProps['user'] && $authProps['user']['is_dosen'])) {
    //         abort(403);
    //     }
    //     $dosenId = Auth::id();

    //     // 1. Ambil daftar mata kuliah yang diajar dosen dari API
    //     // Kita hanya butuh ID-nya untuk query lokal
    //     $mataKuliahApi = $this->getDosenMataKuliahOptions($request);
    //     $mataKuliahIds = collect($mataKuliahApi)->pluck('value')->all();
        
    //     // 2. Ambil data mata kuliah dari database lokal beserta data agregatnya
    //     // Ini adalah query utama yang efisien untuk mendapatkan semua data yang dibutuhkan
    //     $courses = MataKuliah::whereIn('id', $mataKuliahIds)
    //         ->withCount([
    //             'soal',
    //             'ujian as active_exams_count' => function ($query) {
    //                 $query->whereIn('status_publikasi', ['published', 'scheduled'])
    //                       ->whereDate('tanggal_selesai', '>=', now());
    //             },
    //             // Query untuk menghitung jumlah mahasiswa unik berdasarkan pengerjaan ujian
    //             'pengerjaanUjian as students_count' => function ($query) {
    //                 $query->select(DB::raw('count(distinct user_id)'));
    //             }
    //         ])
    //         ->get();
        
    //     // Ambil juga peta semester dari API untuk melengkapi data
    //     $semesterMap = $this->getSemesterMap($request);
        
    //     // 3. Gabungkan data dari database lokal dengan data dari API (misal: semester)
    //     $dashboardData = $courses->map(function ($course) use ($semesterMap) {
    //         // Logika untuk menghitung mahasiswa per matkul bisa ditambahkan di sini jika ada datanya
    //         // Untuk saat ini kita beri nilai 0
    //         // $course->students_count = 0; 
    //         $course->semester = $semesterMap[$course->external_id] ?? 'N/A';
    //         return $course;
    //     });

    //     // 4. Siapkan data untuk kartu statistik di bagian atas
    //     $stats = [
    //         'total_courses' => $dashboardData->count(),
    //         'total_students' => $dashboardData->sum('students_count'), // Akan 0 untuk sekarang
    //         'active_exams' => $dashboardData->sum('active_exams_count'),
    //         'total_questions' => $dashboardData->sum('soal_count'),
    //     ];

    //     return Inertia::render('Dosen/Dashboard/Index', [
    //         'auth' => $authProps,
    //         'dashboardData' => $dashboardData,
    //         'stats' => $stats,
    //     ]);
    // }

    public function index(Request $request)
    {
        $authProps = $this->getAuthProps();
        if (!($authProps['user'] && $authProps['user']['is_dosen'])) {
            abort(403);
        }
        
        // 1. Ambil data mata kuliah dari API sebagai sumber utama (termasuk semester, nama, kode).
        $apiCourses = $this->getDosenCoursesFromApi($request);
        if ($apiCourses->isEmpty()) {
            // Jika tidak ada MK dari API, langsung render halaman dengan data kosong.
            return Inertia::render('Dosen/Dashboard/Index', [
                'auth' => $authProps,
                'dashboardData' => [],
                'stats' => ['total_courses' => 0, 'total_students' => 0, 'active_exams' => 0, 'total_questions' => 0],
            ]);
        }

        // 2. Ambil ID eksternal untuk query data lokal yang relevan.
        $mataKuliahExternalIds = $apiCourses->pluck('external_id')->all();
        
        // 3. Ambil data agregat dari database lokal.
        // `keyBy('external_id')` sangat penting untuk penggabungan yang efisien.
        $localCourseData = MataKuliah::whereIn('external_id', $mataKuliahExternalIds)
            ->withCount([
                'soal',
                // Hapus kondisi 'where' untuk menghitung semua ujian.
                // Eloquent akan secara otomatis membuat properti 'ujian_count'
                'ujian', 
                'pengerjaanUjian as students_count' => function ($query) {
                    $query->select(DB::raw('count(distinct user_id)'));
                }
            ])
            ->get(['id', 'external_id', 'soal_count', 'ujian_count']) // Ambil juga 'ujian_count'
            ->keyBy('external_id');
        
        // 4. Gabungkan data API (sumber utama) dengan data lokal (data tambahan).
        $dashboardData = $apiCourses->map(function ($apiCourse) use ($localCourseData) {
            $localData = $localCourseData->get($apiCourse['external_id']);
            
            // Menggabungkan data. Data dari API adalah basisnya.
            $apiCourse['id'] = $localData->id ?? null; // ID lokal penting untuk link
            $apiCourse['ujian_count'] = $localData->ujian_count ?? 0;
            $apiCourse['students_count'] = $localData->students_count ?? 0;
            $apiCourse['soal_count'] = $localData->soal_count ?? 0;
            
            return $apiCourse;
        });

        // 5. Siapkan data untuk kartu statistik di bagian atas.
        // Logika diubah untuk menghitung total langsung dari database.
        $dosenId = Auth::id();

        // Hitung total bank soal yang dibuat oleh dosen ini
        $totalBankSoal = Soal::where('dosen_pembuat_id', $dosenId)->count();

        $totalUjianKeseluruhan = Ujian::where('dosen_pembuat_id', $dosenId)->count();

        $stats = [
            'total_courses' => $dashboardData->count(),
            'total_students' => $dashboardData->sum('students_count'),
            // Gunakan nama key dan value yang baru
            'total_exams' => $totalUjianKeseluruhan,
            'total_questions' => $totalBankSoal,
        ];

        return Inertia::render('Dosen/Dashboard/Index', [
            'auth' => $authProps,
            'dashboardData' => $dashboardData,
            'stats' => $stats,
        ]);
    }
}