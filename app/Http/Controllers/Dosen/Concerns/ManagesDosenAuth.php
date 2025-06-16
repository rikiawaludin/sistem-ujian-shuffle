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
                'email' => $userAccount['email'] ?? null,
                'image' => $userAccount['image'] ?? null,
                'is_dosen' => $userAccount['is_dosen'] ?? false,
            ],
        ];
    }

    /**
     * Mengambil mata kuliah yang diajar dosen dari API.
     */
    private function getDosenMataKuliahOptions(Request $request): array
    {
        $token = $request->session()->get('token');
        // Pastikan path API ini benar untuk mengambil MK Dosen
        $pathApi = 'ujian/mata-kuliah/dosen'; 
        $apiUrl = config('myconfig.api.base_url', env('API_BASE_URL')) . $pathApi;

        if (!$token) {
            Log::warning('[Trait ManagesDosenAuth] Tidak ada token sesi untuk memanggil API mata kuliah.');
            return [];
        }

        try {
            $response = Http::withToken($token)->timeout(15)->get($apiUrl);


            if ($response->failed()) {
                Log::error('[Trait ManagesDosenAuth] Gagal mengambil data MK dari API.', ['status' => $response->status(), 'url' => $apiUrl]);
                return [];
            }
            
            $mataKuliahFromApi = $response->json('data.kelas_kuliah', []);
            $externalIds = collect($mataKuliahFromApi)->pluck('matakuliah.mk_id')->filter()->unique()->all();
            
            $mataKuliahLokal = MataKuliah::whereIn('external_id', $externalIds)->get(['id', 'nama']);
            
            

            // Diubah agar value adalah ID, bukan nama, agar lebih andal
            return $mataKuliahLokal->map(function ($mk) {
                return ['value' => $mk->id, 'label' => $mk->nama];
            })->all();

        } catch (\Exception $e) {
            Log::error('[Trait ManagesDosenAuth] Exception saat mengambil data MK dari API: ' . $e->getMessage());
            return [];
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

    private function getSemesterMap(Request $request): array
    {
        $token = $request->session()->get('token');
        $pathApi = 'ujian/mata-kuliah/dosen'; // Path API yang sama
        $apiUrl = config('myconfig.api.base_url', env('API_BASE_URL')) . $pathApi;

        if (!$token) {
            Log::warning('[Trait ManagesDosenAuth] Tidak ada token sesi untuk memanggil API semester.');
            return [];
        }

        try {
            $response = Http::withToken($token)->timeout(15)->get($apiUrl);

            if ($response->failed()) {
                Log::error('[Trait ManagesDosenAuth] Gagal mengambil data semester dari API.', ['status' => $response->status(), 'url' => $apiUrl]);
                return [];
            }
            
            $mataKuliahFromApi = $response->json('data.kelas_kuliah', []);
            $semesterMap = [];

            foreach ($mataKuliahFromApi as $kelas) {
                // Pastikan data yang dibutuhkan ada di dalam respons API
                if (isset($kelas['matakuliah']['mk_id']) && isset($kelas['semester'])) {
                    $externalId = $kelas['matakuliah']['mk_id'];
                    
                    // Gunakan data pertama yang ditemukan untuk setiap external_id
                    if (!isset($semesterMap[$externalId])) {
                        $semesterMap[$externalId] = $kelas['semester'];
                    }
                }
            }
            
            return $semesterMap;

        } catch (\Exception $e) {
            Log::error('[Trait ManagesDosenAuth] Exception saat mengambil data semester dari API: ' . $e->getMessage());
            return [];
        }
    }
}