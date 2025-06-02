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
use Illuminate\Support\Facades\Session; // Pastikan Session di-import jika menggunakan Session::get() secara global

class SyncController extends Controller
{
    private function getUserAuthToken(Request $request): ?string
    {
        // Coba ambil token dari sesi dengan kunci 'token' seperti di MyWebService
        $sessionToken = $request->session()->get('token'); // Cara standar mengambil dari sesi dalam controller

        if ($sessionToken) {
            Log::info('SyncController::getUserAuthToken: Token ditemukan di sesi dengan kunci "token".');
            return $sessionToken;
        }

        // Sebagai fallback, cek bearer token jika ada (sesuai implementasi Anda sebelumnya)
        $bearerToken = $request->bearerToken();
        if ($bearerToken) {
            Log::info('SyncController::getUserAuthToken: Token ditemukan sebagai Bearer token di request.');
            return $bearerToken;
        }

        Log::warning('SyncController::getUserAuthToken: Tidak dapat menemukan token otentikasi pengguna dari sesi (kunci "token") maupun sebagai Bearer token.');
        return null;
    }

    public function syncMahasiswa(Request $request)
    {
        $userToken = $this->getUserAuthToken($request);

        if (!$userToken) {
            return back()->with('flash', [
                'type' => 'error',
                'message' => 'Gagal memulai sinkronisasi: Token otentikasi pengguna tidak tersedia di sesi.'
            ]);
        }

        SyncMahasiswaJob::dispatch($userToken);

        return back()->with('flash', [
            'type' => 'info',
            'message' => 'Proses sinkronisasi data mahasiswa telah dimulai di latar belakang.'
        ]);
    }

    public function syncMataKuliah(Request $request)
    {
        $userToken = $this->getUserAuthToken($request);

        if (!$userToken) {
            return back()->with('flash', [
                'type' => 'error',
                'message' => 'Gagal memulai sinkronisasi: Token otentikasi pengguna tidak tersedia di sesi.'
            ]);
        }

        SyncMataKuliahJob::dispatch($userToken); // Teruskan token ke job

        return back()->with('flash', [
            'type' => 'info',
            'message' => 'Proses sinkronisasi data mata kuliah telah dimulai di latar belakang.'
        ]);
    }

    public function syncDosen(Request $request)
    {
    $userToken = $this->getUserAuthToken($request); // Gunakan method yang sudah ada

    if (!$userToken && env('EXTERNAL_API_REQUIRES_TOKEN', true)) { // Tambah check jika token wajib
        return back()->with('flash', [
            'type' => 'error',
            'message' => 'Gagal memulai sinkronisasi dosen: Token otentikasi pengguna tidak tersedia.'
        ]);
    }

        SyncDosenJob::dispatch($userToken); // Teruskan token

        return back()->with('flash', [
            'type' => 'info',
            'message' => 'Proses sinkronisasi data dosen telah dimulai di latar belakang.'
        ]);
    }

    public function syncProdi(Request $request)
    {
        $userToken = $this->getUserAuthToken($request);
        if (!$userToken && env('EXTERNAL_API_PRODI_REQUIRES_TOKEN', true)) {
            return back()->with('flash', [ /* ... error token ... */ ]);
        }
        SyncProdiJob::dispatch($userToken);
        return back()->with('flash', ['type' => 'info', 'message' => 'Sinkronisasi data prodi telah dimulai.']);
    }

    public function syncAdmin(Request $request)
    {
        $userToken = $this->getUserAuthToken($request);
        if (!$userToken && env('EXTERNAL_API_ADMIN_REQUIRES_TOKEN', true)) {
            return back()->with('flash', [ /* ... error token ... */ ]);
        }
        SyncAdminJob::dispatch($userToken);
        return back()->with('flash', ['type' => 'info', 'message' => 'Sinkronisasi data admin telah dimulai.']);
    }
}