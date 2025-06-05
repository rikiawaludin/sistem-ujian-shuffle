<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\MataKuliah;
// use App\Models\PengerjaanUjian; // Komentari jika tidak digunakan langsung di method index
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
// use Illuminate\Support\Carbon; // Komentari jika tidak digunakan
// use Illuminate\Support\Collection; // Komentari jika tidak digunakan secara eksplisit untuk collection baru

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

    public function user_account(): ?array
    {
        return Session::get('account');
    }

    public function user_profile(): ?array
    {
        return Session::get('profile');
    }

    public function index(Request $request)
    {
        $userAccount = $this->user_account();
        $userProfile = $this->user_profile();
        $activeRoleArray = Session::get('role');
        $sessionToken = $this->getUserAuthToken($request);

        $authProp = ['user' => null];
        $localUser = null;

        if ($userAccount && isset($userAccount['id'])) {
            $externalId = $userAccount['id'];
            $localUser = User::where('external_id', (string) $externalId)
                               ->where('is_mahasiswa', true)
                               ->first();

            if ($localUser) {
                $authProp['user'] = [
                    'id' => $localUser->id,
                    'external_id' => $externalId,
                    'name' => $userProfile['nama'] ?? $userAccount['email'] ?? 'Pengguna Terdaftar',
                    'email' => $userAccount['email'] ?? null,
                    'image' => $userAccount['image'] ?? null,
                    'roles' => $activeRoleArray ?? [],
                    'is_mhs' => $userAccount['is_mhs'] ?? false,
                    'nim' => $userProfile['nim'] ?? null,
                    'nama_jurusan' => $userProfile['nama_jurusan'] ?? null,
                    'kd_user' => $userAccount['kd_user'] ?? null,
                ];
            } else {
                Log::warning('DashboardController: Pengguna dengan external_id sesi ' . $externalId . ' tidak ditemukan sebagai mahasiswa di DB lokal.');
                 $authProp['user'] = [
                    'external_id' => $externalId,
                    'name' => $userAccount['email'] ?? 'Pengguna (Data Lokal Tidak Sinkron)',
                    'email' => $userAccount['email'] ?? null,
                    'image' => $userAccount['image'] ?? null,
                    'roles' => $activeRoleArray ?? [],
                    'is_mhs' => $userAccount['is_mhs'] ?? false,
                ];
            }
        } else {
            Log::warning('DashboardController: user_account dari sesi tidak ada atau tidak memiliki ID.');
        }

        $daftarMataKuliahLokalMap = collect([]);
        try {
            // Ambil semua mata kuliah dari DB lokal yang mungkin relevan
            // HAPUS ATAU KOMENTARI withCount
            $daftarMataKuliahLokal = MataKuliah::get(); // Langsung get() tanpa withCount

            $daftarMataKuliahLokalMap = $daftarMataKuliahLokal->mapWithKeys(function ($mk) {
                return [$mk->external_id => [ //
                    'id_lokal' => $mk->id, //
                    'nama_lokal' => $mk->nama, //
                    'kode_lokal' => $mk->kode, //
                    'img_lokal' => $mk->icon_url ? asset('storage/' . trim($mk->icon_url, '/')) : asset('/images/placeholder-matakuliah.png'), //
                    'jumlah_ujian_tersedia_lokal' => 0, // Berikan nilai default karena withCount dihapus
                    // Anda bisa tambahkan field lain dari DB lokal jika perlu untuk enrichment
                ]];
            });
        } catch (\Exception $e) {
            // Jika error terjadi di sini, $daftarMataKuliahLokalMap akan tetap kosong
            Log::error('DashboardController: Error saat mengambil data mata kuliah lokal: ' . $e->getMessage());
        }

        $historiUjianUntukDashboard = [];
        if ($localUser) {
            // Logika histori ujian (sesuaikan)
        }

        // $availableSemestersFromLocal = MataKuliah::distinct()->orderBy('semester', 'asc')->pluck('semester');

        return Inertia::render('Dashboard', [
            'auth' => $authProp,
            'daftarMataKuliahLokal' => $daftarMataKuliahLokalMap, // Kirim data MK lokal (sebagai map)
            'historiUjian' => $historiUjianUntukDashboard,
            // 'availableSemesters' => $availableSemestersFromLocal->all(),
            'filters' => [
                'semester' => $request->query('semester', 'semua'),
                'tahun_ajaran' => $request->query('tahun_ajaran', null)
            ],
            'apiBaseUrl' => config('myconfig.api.base_url', env('API_BASE_URL')),
            'sessionToken' => $sessionToken,
        ]);
    }
}