<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User; // Jika Anda punya model User
use App\Models\MataKuliah; // Jika Anda punya model MataKuliah
use App\Models\MigrationHistory; // Jika Anda punya model MigrationHistory
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function dashboard(Request $request) {
    // Ambil data untuk ditampilkan di tabel
    $mahasiswaData = User::where('is_mahasiswa', true)->orderBy('created_at', 'desc')->get();
    $dosenData = User::where('is_dosen', true)->orderBy('created_at', 'desc')->get();
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
        'mahasiswaData' => $mahasiswaData,
        'dosenData' => $dosenData,
        // ... prodiData, adminData
        'mataKuliahData' => $mataKuliahData,
        'migrationHistoryUsers' => $migrationHistoryUsers,
        'migrationHistoryMataKuliah' => $migrationHistoryMataKuliah,
        'flash' => session('flash') // Untuk notifikasi
    ]);
    }
}
