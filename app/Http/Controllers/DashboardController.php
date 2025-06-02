<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\MataKuliah;
use App\Models\PengerjaanUjian;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log; // Tambahkan Log untuk debugging jika perlu
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder; // Untuk type hinting jika membuat scope

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil data pengguna dari sesi
        $user = $this->user_profile();
        dd($user);

        $userAccount = $this->user_account(); // Dari Session::get('account')
        $userProfile = $this->user_profile(); // Dari Session::get('profile')
        $activeRoleArray = Session::get('role');

        // 2. Persiapkan prop 'auth' untuk Inertia
        $authProp = ['user' => null]; // Default jika pengguna tidak ada di sesi
        $localUser = null;

        if ($userAccount && isset($userAccount['id'])) { // Pastikan $userAccount dan ID-nya ada
            $externalId = $userAccount['id'];
            $localUser = User::where('external_id', $externalId)->first();

            $authProp['user'] = [
                'id' => $localUser ? $localUser->id : null,
                'external_id' => $externalId,
                'name' => $userProfile['nama'] ?? $userAccount['email'] ?? 'Pengguna',
                'email' => $userAccount['email'] ?? null,
                'image' => $userAccount['image'] ?? null,
                'roles' => $activeRoleArray ?? [],
                'is_mhs' => $userAccount['is_mhs'] ?? false,
                'nim' => $userProfile['nim'] ?? null,
                'nama_jurusan' => $userProfile['nama_jurusan'] ?? null,
                'kd_user' => $userAccount['kd_user'] ?? null,
            ];
        } else {
            // Log jika data sesi utama tidak ada, ini bisa jadi masalah di alur login/SSO
            Log::warning('DashboardController: user_account dari sesi tidak ditemukan atau tidak memiliki ID.');
            // Anda bisa memutuskan untuk mengarahkan ke halaman login di sini jika ini adalah kondisi error
            // return redirect()->route('check')->withErrors('Sesi Anda tidak valid.');
        }

        // 3. Filter dan Data Default untuk Mata Kuliah
        // Default filter jika tidak ada di query string
        $selectedSemester = $request->query('semester', 'semua'); // Default ke 'semua' jika tidak ada
        $selectedTahunAjaran = $request->query('tahun_ajaran', '2024/2025'); // Default tahun ajaran

        // Inisialisasi data dengan array kosong sebagai default
        $daftarMataKuliahProcessed = collect([]); // Gunakan collection kosong
        $availableSemesters = collect([]);      // Gunakan collection kosong

        try {
            $mataKuliahQuery = MataKuliah::query();

            $mataKuliahQuery->with(['dosen' => function($query) {
                $query->select('id', 'name');
            }])->withCount('ujian AS jumlah_ujian_tersedia');

            // Pastikan nama kolom 'tahun_ajaran' dan 'semester' sesuai dengan tabel mata_kuliah
            $mataKuliahQuery->where('tahun_ajaran', $selectedTahunAjaran);

            if ($selectedSemester && $selectedSemester !== 'semua') {
                $mataKuliahQuery->where('semester', (int)$selectedSemester);
            }

            // Pastikan nama kolom untuk orderBy ('semester', 'nama') sesuai
            // Ganti 'nama' dengan 'nama_mata_kuliah' jika itu nama kolom sebenarnya
            $daftarMataKuliahProcessed = $mataKuliahQuery
                ->orderBy('semester')
                ->orderBy('nama') // atau 'nama_mata_kuliah'
                ->get()
                ->map(function ($mk) {
                    $dosenNama = 'Dosen Belum Ditugaskan';
                    if ($mk->dosen && $mk->dosen->name) {
                        $dosenNama = $mk->dosen->name;
                    }
                    $imageUrl = $mk->icon_url ? asset('storage/' . trim($mk->icon_url, '/')) : asset('/images/placeholder-matakuliah.png');

                    return [
                        'id' => $mk->id,
                        'nama' => $mk->nama, // atau 'nama_mata_kuliah'
                        'dosen' => ['nama' => $dosenNama],
                        'deskripsi_singkat' => $mk->deskripsi,
                        'img' => $imageUrl,
                        'jumlah_ujian_tersedia' => $mk->jumlah_ujian_tersedia,
                        'semester' => $mk->semester,
                        'tahun_ajaran' => $mk->tahun_ajaran,
                    ];
                });

            $availableSemesters = MataKuliah::where('tahun_ajaran', $selectedTahunAjaran)
                                    ->distinct()
                                    ->orderBy('semester', 'asc')
                                    ->pluck('semester');

        } catch (\Exception $e) {
            Log::error('DashboardController: Error saat mengambil data mata kuliah: ' . $e->getMessage());
            // $daftarMataKuliahProcessed dan $availableSemesters akan tetap collection kosong (default)
        }


        // 4. Histori Ujian (sudah diinisialisasi dengan array kosong)
        $historiUjianUntukDashboard = [];
        if ($localUser) { // Hanya proses jika pengguna lokal teridentifikasi
            try {
                $pengerjaanUjianTerakhir = PengerjaanUjian::where('user_id', $localUser->id)
                    ->with(['ujian.mataKuliah'])
                    ->orderBy('waktu_selesai', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get();

                $historiUjianUntukDashboard = $pengerjaanUjianTerakhir->map(function ($attempt) {
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
                        'namaMataKuliah' => $mataKuliah->nama ?? 'Mata Kuliah Tidak Ditemukan', // Sesuaikan dengan nama kolom
                        'tanggalPengerjaan' => $attempt->waktu_selesai ? Carbon::parse($attempt->waktu_selesai)->format('d M Y') : ($attempt->created_at ? Carbon::parse($attempt->created_at)->format('d M Y') : 'N/A'),
                        'skor' => $attempt->skor_total,
                        'kkm' => $kkm,
                        'statusKelulusan' => $statusKelulusan,
                    ];
                });
            } catch (\Exception $e) {
                Log::error('DashboardController: Error saat mengambil histori ujian: ' . $e->getMessage());
                // $historiUjianUntukDashboard akan tetap array kosong
            }
        } else {
            Log::info('DashboardController: Pengguna lokal tidak ditemukan, tidak mengambil histori ujian.');
        }

        // 5. Render halaman dengan semua prop, termasuk yang mungkin kosong/default
        return Inertia::render('Dashboard', [
            'auth' => $authProp,
            'daftarMataKuliah' => $daftarMataKuliahProcessed,
            'historiUjian' => $historiUjianUntukDashboard,
            'availableSemesters' => $availableSemesters,
            'filters' => ['semester' => $selectedSemester, 'tahun_ajaran' => $selectedTahunAjaran]
        ]);
    }
}