<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Jobs\SyncMahasiswaJob;
use App\Jobs\SyncMataKuliahJob;
use App\Jobs\SyncDosenJob;
use App\Jobs\SyncProdiJob;
use App\Jobs\SyncAdminJob;
use Illuminate\Support\Facades\Log;
// Session facade tidak perlu di-import di sini jika Anda menggunakan $request->session()

class SyncController extends Controller
{
    /**
     * Mengambil token otentikasi pengguna dari request.
     * Sesuaikan logika ini berdasarkan bagaimana token Anda disimpan/diakses.
     */
    private function getUserAuthToken(Request $request): ?string
    {
        $sessionToken = $request->session()->get('token'); // Kunci sesi utama
        if ($sessionToken) {
            Log::info('SyncController::getUserAuthToken: Token ditemukan di sesi dengan kunci "token".');
            return $sessionToken;
        }

        $bearerToken = $request->bearerToken(); // Fallback ke Bearer token jika ada
        if ($bearerToken) {
            Log::info('SyncController::getUserAuthToken: Token ditemukan sebagai Bearer token di request.');
            return $bearerToken;
        }

        Log::warning('SyncController::getUserAuthToken: Tidak dapat menemukan token otentikasi pengguna dari sesi (kunci "token") maupun sebagai Bearer token.');
        return null;
    }

    /**
     * Menangani error jika token tidak ditemukan dan wajib.
     * Mengembalikan respons JSON agar bisa ditangkap oleh onError di frontend.
     */
    private function handleMissingTokenError(string $syncType)
    {
        $errorMessage = "Gagal memulai sinkronisasi {$syncType}: Token otentikasi pengguna tidak tersedia atau tidak valid.";
        Log::error($errorMessage . ' (dari SyncController)');
        // Mengembalikan respons JSON dengan status error agar Inertia bisa menanganinya di onError
        return response()->json(['message' => $errorMessage], 401); // 401 Unauthorized
    }

    public function syncMahasiswa(Request $request)
    {
        $userToken = $this->getUserAuthToken($request);

        // Asumsikan token selalu wajib untuk mahasiswa berdasarkan diskusi sebelumnya
        if (!$userToken) {
            return $this->handleMissingTokenError('mahasiswa');
        }

        SyncMahasiswaJob::dispatch($userToken);

        // Respon JSON untuk konfirmasi dispatch, bukan flash message untuk "telah dimulai"
        // return response(null, 204);
    }

    public function syncMataKuliah(Request $request)
    {
        $userToken = $this->getUserAuthToken($request);

        // Asumsikan token selalu wajib untuk mata kuliah berdasarkan diskusi sebelumnya
        if (!$userToken) {
            return $this->handleMissingTokenError('mata kuliah');
        }

        SyncMataKuliahJob::dispatch($userToken);
        // return response(null, 204);
    }

    public function syncDosen(Request $request)
    {
        $userToken = $this->getUserAuthToken($request);

        // Anda menambahkan check env di sini, ini bagus jika beberapa endpoint mungkin tidak butuh token
        // Sesuaikan 'EXTERNAL_API_DOSEN_REQUIRES_TOKEN' dengan nama variabel .env Anda jika berbeda
        if (!$userToken && env('EXTERNAL_API_DOSEN_REQUIRES_TOKEN', true)) {
            return $this->handleMissingTokenError('dosen');
        }

        // Jika token tidak wajib dan tidak ada, dispatch tanpa token (atau dengan null)
        // Job SyncDosenJob sudah diatur untuk handle $token = null di constructornya
        SyncDosenJob::dispatch($userToken); // $userToken bisa null jika tidak wajib dan tidak ditemukan
        // return response(null, 204);
    }

    public function syncProdi(Request $request)
    {
        $userToken = $this->getUserAuthToken($request);

        if (!$userToken && env('EXTERNAL_API_PRODI_REQUIRES_TOKEN', true)) {
            return $this->handleMissingTokenError('prodi');
        }
        SyncProdiJob::dispatch($userToken);
        // return response(null, 204);
    }

    public function syncAdmin(Request $request)
    {
        $userToken = $this->getUserAuthToken($request);

        if (!$userToken && env('EXTERNAL_API_ADMIN_REQUIRES_TOKEN', true)) {
            return $this->handleMissingTokenError('admin');
        }
        SyncAdminJob::dispatch($userToken);
        // return response(null, 204);
    }
}