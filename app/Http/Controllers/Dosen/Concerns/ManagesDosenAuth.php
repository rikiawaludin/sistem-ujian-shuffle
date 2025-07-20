<?php

namespace App\Http\Controllers\Dosen\Concerns;

use App\Models\MataKuliah;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

trait ManagesDosenAuth
{
    /**
     * Mengambil data otentikasi dari sesi dan meloginkan user.
     */
    private function getAuthProps(): array
    {
        $userAccount = Session::get('account');
        $activeRoleArray = Session::get('role');

        if (!$userAccount || !isset($userAccount['id'])) {
            return ['user' => null];
        }

        $localUser = User::where('external_id', $userAccount['id'])->first();
        if ($localUser) {
            Auth::login($localUser);
        }

        return [
            'user' => [
                'id' => $localUser->id ?? null,
                'name' => Session::get('profile')['nama'] ?? 'Pengguna Dosen',
                'gelar' => Session::get('profile')['gelar'] ?? null,
                'email' => $userAccount['email'] ?? null,
                'image' => $userAccount['image'] ?? null,
                'is_dosen' => $activeRoleArray['is_dosen'] ?? false,
                'is_mahasiswa' => $activeRoleArray['is_mahasiswa'] ?? false,
                'is_admin' => $activeRoleArray['is_admin'] ?? false,
            ],
        ];
    }

    /**
     * Mengambil mata kuliah yang diajar dosen dari API.
     */
    // private function getDosenMataKuliahOptions(Request $request): array
    // {
    //     $token = $request->session()->get('token');
    //     // Pastikan path API ini benar untuk mengambil MK Dosen
    //     $pathApi = 'ujian/mata-kuliah/dosen'; 
    //     $apiUrl = config('myconfig.api.base_url', env('API_BASE_URL')) . $pathApi;

    //     if (!$token) {
    //         Log::warning('[Trait ManagesDosenAuth] Tidak ada token sesi untuk memanggil API mata kuliah.');
    //         return [];
    //     }

    //     try {
    //         $response = Http::withToken($token)->timeout(15)->get($apiUrl);


    //         if ($response->failed()) {
    //             Log::error('[Trait ManagesDosenAuth] Gagal mengambil data MK dari API.', ['status' => $response->status(), 'url' => $apiUrl]);
    //             return [];
    //         }
            
    //         $mataKuliahFromApi = $response->json('data.kelas_kuliah', []);
    //         $externalIds = collect($mataKuliahFromApi)->pluck('matakuliah.mk_id')->filter()->unique()->all();
            
    //         $mataKuliahLokal = MataKuliah::whereIn('external_id', $externalIds)->get(['id', 'nama']);
            
            

    //         // Diubah agar value adalah ID, bukan nama, agar lebih andal
    //         return $mataKuliahLokal->map(function ($mk) {
    //             return ['value' => $mk->id, 'label' => $mk->nama];
    //         })->all();

    //     } catch (\Exception $e) {
    //         Log::error('[Trait ManagesDosenAuth] Exception saat mengambil data MK dari API: ' . $e->getMessage());
    //         return [];
    //     }
    // }

    private function getDosenCoursesFromApi(Request $request): \Illuminate\Support\Collection
    {
        $token = $request->session()->get('token');
        // Menggunakan path API yang sama seperti sebelumnya, yang berisi data lengkap.
        $pathApi = 'ujian/mata-kuliah/dosen'; 
        $apiUrl = config('myconfig.api.base_url', env('API_BASE_URL')) . $pathApi;

        if (!$token) {
            Log::warning('[Trait ManagesDosenAuth] Tidak ada token sesi untuk memanggil API mata kuliah.');
            return collect();
        }

        try {
            $response = Http::withToken($token)->timeout(15)->get($apiUrl);

            if ($response->failed()) {
                Log::error('[Trait ManagesDosenAuth] Gagal mengambil data MK dari API.', ['status' => $response->status(), 'url' => $apiUrl]);
                return collect();
            }
            
            $mataKuliahFromApi = $response->json('data.kelas_kuliah', []);
            
            $courses = collect($mataKuliahFromApi)->map(function ($kelas) {
                // Pastikan kedua sub-array yang dibutuhkan ada
                if (!isset($kelas['data_kelas']) || !isset($kelas['matakuliah'])) {
                    return null;
                }

                // Ambil data dari struktur yang benar sesuai hasil dd()
                return [
                    // Mengambil dari dalam 'data_kelas'
                    'id_kelas_kuliah' => $kelas['data_kelas']['kelas_kuliah_id'] ?? null,

                    // Mengambil dari dalam 'matakuliah'
                    'external_id'     => $kelas['matakuliah']['mk_id'] ?? null,
                    'nama'            => $kelas['matakuliah']['nm_mk'] ?? 'Nama MK Tidak Ditemukan',
                    'kode'            => $kelas['matakuliah']['kd_mk'] ?? 'KODE-MK',
                    'semester'        => $kelas['matakuliah']['semester'] ?? 'N/A',
                ];
            })->filter();

            // Karena satu mata kuliah bisa diajar di beberapa kelas, kita ambil yang unik berdasarkan ID eksternal.
            // return $courses->unique('external_id')->values();
            return $courses;

        } catch (\Exception $e) {
            Log::error('[Trait ManagesDosenAuth] Exception saat mengambil data MK dari API: ' . $e->getMessage());
            return collect();
        }
    }

    public function getStudentCountForClassApi(Request $request, $kelasKuliahId): int
    {
        $token = $request->session()->get('token');
        // Path API baru sesuai permintaan
        $pathApi = 'ujian/mata-kuliah/dosen/count-mahasiswa/kelas-kuliah-id/' . $kelasKuliahId;
        $apiUrl = config('myconfig.api.base_url', env('API_BASE_URL')) . $pathApi;

        if (!$token || !$kelasKuliahId) {
            Log::warning('[Trait ManagesDosenAuth] Token atau ID Kelas Kuliah tidak tersedia untuk API hitung mahasiswa.', ['class_id' => $kelasKuliahId]);
            return 0;
        }

        try {
            $response = Http::withToken($token)->timeout(10)->get($apiUrl);

            if ($response->failed()) {
                Log::error('[Trait ManagesDosenAuth] Gagal mengambil jumlah mahasiswa.', [
                    'status' => $response->status(),
                    'url' => $apiUrl
                ]);
                return 0;
            }

            // Mengambil nilai dari key "data" dan default ke 0 jika tidak ada atau null
            return $response->json('data', 0);

        } catch (\Exception $e) {
            Log::error('[Trait ManagesDosenAuth] Exception saat mengambil jumlah mahasiswa: ' . $e->getMessage());
            return 0;
        }
    }

    private function getMahasiswaAktifMap(Request $request): array
    {
        $token = $request->session()->get('token');
        // Gunakan endpoint yang sama dengan dashboard mahasiswa, karena berisi data mahasiswa per kelas
        $pathApi = 'ujian/mata-kuliah/mahasiswa';
        $apiUrl = config('myconfig.api.base_url', env('API_BASE_URL')) . $pathApi;

        if (!$token) {
            Log::warning('[Trait ManagesDosenAuth] Tidak ada token sesi untuk memanggil API mahasiswa.');
            return [];
        }

        try {
            $response = Http::withToken($token)->timeout(15)->get($apiUrl);
            if ($response->failed()) {
                Log::error('[Trait ManagesDosenAuth] Gagal mengambil data mahasiswa dari API.', ['status' => $response->status(), 'url' => $apiUrl]);
                return [];
            }
            
            $kelasKuliahFromApi = $response->json('data.kelas_kuliah', []);
            $mahasiswaMap = [];

            foreach ($kelasKuliahFromApi as $kelas) {
                // Asumsi di dalam setiap kelas ada data mahasiswa
                if (isset($kelas['mahasiswa']) && is_array($kelas['mahasiswa'])) {
                    foreach ($kelas['mahasiswa'] as $mahasiswa) {
                        if (isset($mahasiswa['mhs_id']) && !isset($mahasiswaMap[$mahasiswa['mhs_id']])) {
                            $mahasiswaMap[$mahasiswa['mhs_id']] = [
                                'nama' => $mahasiswa['nm_mhs'] ?? 'Nama Tidak Ditemukan',
                                'nim' => $mahasiswa['nim'] ?? 'N/A',
                            ];
                        }
                    }
                }
            }
            
            return $mahasiswaMap;

        } catch (\Exception $e) {
            Log::error('[Trait ManagesDosenAuth] Exception saat mengambil data mahasiswa dari API: ' . $e->getMessage());
            return [];
        }
    }
}