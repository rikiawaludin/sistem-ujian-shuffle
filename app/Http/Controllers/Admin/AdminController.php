<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User; // Jika Anda punya model User
use App\Models\MataKuliah; // Jika Anda punya model MataKuliah
use App\Models\MigrationHistory; // Jika Anda punya model MigrationHistory
use App\Http\Controllers\Dosen\Concerns\ManagesDosenAuth;
use Illuminate\Http\Request;
use Inertia\Inertia;


class AdminController extends Controller
{

    use ManagesDosenAuth;

    public function dashboard(Request $request) {

    $authProps = $this->getAuthProps();

    // Pastikan hanya admin yang bisa mengakses
    if (!($authProps['user'] && $authProps['user']['is_admin'])) {
        abort(403, 'Akses ditolak.');
    }
    
    // Ambil data untuk ditampilkan di tabel
    $mahasiswaData = User::where('is_mahasiswa', true)->orderBy('created_at', 'desc')->get();
    $dosenData = User::where('is_dosen', true)->orderBy('created_at', 'desc')->get();
    $prodiData = User::where('is_prodi', true)->orderBy('created_at', 'desc')->get();
    $adminData = User::where('is_admin', true)->orderBy('created_at', 'desc')->get(); 
    // ... data untuk prodi dan admin

    $mataKuliahData = MataKuliah::orderBy('created_at', 'desc')->get();

    // Ambil histori migrasi
    // Lebih efisien jika difilter di backend, tapi untuk contoh ini bisa difilter di frontend
    $migrationHistoryUsers = MigrationHistory::where('is_mahasiswa', true)
                                ->orWhere('is_dosen', true)
                                ->orWhere('is_prodi', true)
                                ->orWhere('is_admin', true)
                                ->orderBy('created_at', 'desc')
                                ->get();
    $migrationHistoryMataKuliah = MigrationHistory::where('is_mata_kuliah', true)
                                ->orderBy('created_at', 'desc')
                                ->get();

    return Inertia::render('Admin/DashboardAdminPage', [
        'auth' => $authProps,
        'mahasiswaData' => $mahasiswaData,
        'dosenData' => $dosenData,
        'prodiData' => $prodiData,
        'adminData' => $adminData, 
        'mataKuliahData' => $mataKuliahData,
        'migrationHistoryUsers' => $migrationHistoryUsers,
        'migrationHistoryMataKuliah' => $migrationHistoryMataKuliah,
        'flash' => session('flash') // Untuk notifikasi
    ]);
    }
}
