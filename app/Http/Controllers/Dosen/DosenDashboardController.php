<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\PengerjaanUjian;
use App\Http\Controllers\Dosen\Concerns\ManagesDosenAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DosenDashboardController extends Controller
{
    use ManagesDosenAuth;

    /**
     * Menampilkan dashboard utama untuk dosen.
     * Berisi daftar semua pengerjaan ujian dari ujian yang dibuat oleh dosen ini.
     */
    public function index(Request $request)
    {
        $authProps = $this->getAuthProps();
        if (!($authProps['user'] && $authProps['user']['is_dosen'])) {
            abort(403);
        }
        $dosenId = Auth::id();

        // 1. Ambil data mahasiswa dari API dan buat menjadi map
        $mahasiswaApiMap = $this->getMahasiswaAktifMap($request);
        
        // 2. Ambil data pengerjaan dari DB lokal, pastikan mengambil external_id user
        $pengerjaanList = PengerjaanUjian::with([
                'user:id,email', 
                'ujian:id,judul_ujian,dosen_pembuat_id,mata_kuliah_id',
                'ujian.mataKuliah:id,nama'
            ])
            ->whereHas('ujian', fn($q) => $q->where('dosen_pembuat_id', $dosenId))
            ->whereIn('status_pengerjaan', ['selesai', 'selesai_waktu_habis'])
            ->orderBy('waktu_selesai', 'desc')
            ->paginate(20);

        return inertia('Dosen/Dashboard/Index', [
            'pengerjaanList' => $pengerjaanList,
            'auth' => $authProps,
        ]);
    }
}