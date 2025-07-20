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
        
        // 1. Ambil SEMUA KELAS yang diajar dosen dari API.
        //    Hasilnya berisi duplikat mata kuliah jika diajar di kelas berbeda.
        $apiClasses = $this->getDosenCoursesFromApi($request);

        if ($apiClasses->isEmpty()) {
            return Inertia::render('Dosen/Dashboard/Index', [
                'auth' => $authProps,
                'dashboardData' => [],
                'stats' => ['total_courses' => 0, 'total_students' => 0, 'total_exams' => 0, 'total_questions' => 0],
            ]);
        }

        // 2. Kelompokkan kelas berdasarkan external_id (ID mata kuliah).
        $coursesGroupedByExternalId = $apiClasses->groupBy('external_id');

        // 3. Ambil semua external_id untuk query data lokal yang relevan dalam satu kali jalan.
        $mataKuliahExternalIds = $coursesGroupedByExternalId->keys()->all();
        
        // 4. Ambil data agregat (soal, ujian) dari database lokal.
        $localCourseData = MataKuliah::whereIn('external_id', $mataKuliahExternalIds)
            ->withCount(['soal', 'ujian'])
            ->get(['id', 'external_id', 'soal_count', 'ujian_count'])
            ->keyBy('external_id');
            
        // 5. Proses setiap kelompok mata kuliah untuk mengagregasi data.
        $dashboardData = $coursesGroupedByExternalId->map(function ($classesInCourse, $externalId) use ($localCourseData, $request) {
            
            // Gunakan data dari kelas pertama sebagai representasi (nama, kode, dll).
            $representativeCourse = $classesInCourse->first();
            $localData = $localCourseData->get($externalId);

            // Hitung total mahasiswa dengan memanggil API untuk setiap kelas dalam kelompok ini.
            $totalStudents = $classesInCourse->reduce(function ($carry, $class) use ($request) {
                $studentCount = 0;
                if (!empty($class['id_kelas_kuliah'])) {
                    // Panggil method baru yang kita buat di trait.
                    $studentCount = $this->getStudentCountForClassApi($request, $class['id_kelas_kuliah']);
                }
                return $carry + $studentCount;
            }, 0); // Inisialisasi jumlah dengan 0.

            // Gabungkan semua data menjadi satu objek untuk card di frontend.
            return [
                'id' => $localData->id ?? null,
                'external_id' => $externalId,
                'nama' => $representativeCourse['nama'],
                'kode' => $representativeCourse['kode'],
                'semester' => $representativeCourse['semester'],
                'ujian_count' => $localData->ujian_count ?? 0,
                'soal_count' => $localData->soal_count ?? 0,
                'students_count' => $totalStudents, // Gunakan hasil kalkulasi baru.
            ];
        })->values(); // Reset index array agar menjadi [0, 1, 2, ...]

        // 6. Siapkan data untuk kartu statistik di bagian atas.
        $dosenId = Auth::id();
        $totalBankSoal = Soal::where('dosen_pembuat_id', $dosenId)->count();
        $totalUjianKeseluruhan = Ujian::where('dosen_pembuat_id', $dosenId)->count();

        $stats = [
            'total_courses' => $dashboardData->count(),
            'total_students' => $dashboardData->sum('students_count'), // Jumlahkan dari data yang sudah diproses
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