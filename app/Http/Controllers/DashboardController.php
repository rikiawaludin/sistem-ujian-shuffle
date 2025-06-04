<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\MataKuliah;
use App\Models\PengerjaanUjian;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    private function getUserAuthToken(Request $request): ?string
    {
        $sessionToken = $request->session()->get('token');
        if ($sessionToken) {
            return $sessionToken;
        }
        Log::warning('DashboardController: Token sesi "token" tidak ditemukan.');
        return null;
    }

    public function index(Request $request)
    {
        $userAccount = $this->user_account();
        $userProfile = $this->user_profile();
        $activeRoleArray = Session::get('role');
        $sessionToken = $this->getUserAuthToken($request); // Token untuk client-side API call

        $authProp = ['user' => null];
        $localUser = null;

        if ($userAccount && isset($userAccount['id'])) {
            $externalId = $userAccount['id'];
            // Asumsi Anda mungkin masih perlu $localUser untuk histori ujian atau validasi lain
            $localUser = User::where('external_id', $externalId)->where('is_mahasiswa', true)->first();

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
            Log::warning('DashboardController: user_account dari sesi tidak ada atau tidak memiliki ID.');
        }

        $selectedSemester = $request->query('semester', 'semua');
        $selectedTahunAjaran = $request->query('tahun_ajaran', '2024/2025');

        // Data MK Lokal: bisa digunakan frontend untuk validasi/enrichment
        // Kita buat ini sebagai objek/kamus agar mudah dicari berdasarkan external_id di frontend
        $daftarMataKuliahLokalMap = collect([]);
        $availableSemesters = collect([]); // Akan diisi dari data lokal untuk filter awal

        try {
            // Ambil semua mata kuliah yang relevan dari DB lokal
            // Tidak perlu difilter semester di sini, karena daftar MK mahasiswa akan datang dari API
            // Tapi availableSemesters dan filter tahun ajaran bisa tetap dari sini
            $queryMkLokal = MataKuliah::query();
            if ($selectedTahunAjaran) { // Filter tahun ajaran untuk availableSemesters dan data lokal
                 $queryMkLokal->where('tahun_ajaran', $selectedTahunAjaran);
            }

            $daftarMataKuliahLokal = $queryMkLokal
                ->withCount('ujian AS jumlah_ujian_tersedia') // Jumlah ujian dari DB lokal
                ->get();

            $daftarMataKuliahLokalMap = $daftarMataKuliahLokal->mapWithKeys(function ($mk) {
                // Asumsikan 'external_id' di tabel 'mata_kuliah' Anda menyimpan 'mk_id' dari sistem eksternal
                return [$mk->external_id => [
                    'id_lokal' => $mk->id,
                    'nama_lokal' => $mk->nama, // atau 'nama_mata_kuliah'
                    'kode_lokal' => $mk->kode,
                    'deskripsi_lokal' => $mk->deskripsi,
                    'img_lokal' => $mk->icon_url ? asset('storage/' . trim($mk->icon_url, '/')) : asset('/images/placeholder-matakuliah.png'),
                    'jumlah_ujian_tersedia_lokal' => $mk->jumlah_ujian_tersedia,
                    'semester_lokal' => $mk->semester, // Untuk validasi atau info tambahan
                    'tahun_ajaran_lokal' => $mk->tahun_ajaran,
                ]];
            });

            // Available semesters untuk filter dropdown, bisa berdasarkan semua MK lokal di tahun ajaran aktif
            $availableSemesters = MataKuliah::where('tahun_ajaran', $selectedTahunAjaran)
                                    ->distinct()
                                    ->orderBy('semester', 'asc')
                                    ->pluck('semester');

        } catch (\Exception $e) {
            Log::error('DashboardController: Error saat mengambil data mata kuliah lokal: ' . $e->getMessage());
        }

        // Histori Ujian
        $historiUjianUntukDashboard = [];
        if ($localUser) { /* ... logika histori ujian tetap sama ... */ }

        return Inertia::render('Dashboard', [
            'auth' => $authProp,
            'daftarMataKuliahLokal' => $daftarMataKuliahLokalMap, // Kirim data MK lokal (sebagai map)
            'historiUjian' => $historiUjianUntukDashboard,
            'availableSemesters' => $availableSemesters->all(),
            'filters' => ['semester' => $selectedSemester, 'tahun_ajaran' => $selectedTahunAjaran],
            'apiBaseUrl' => config('myconfig.api.base_url', env('API_BASE_URL')),
            'sessionToken' => $sessionToken,
        ]);
    }
}