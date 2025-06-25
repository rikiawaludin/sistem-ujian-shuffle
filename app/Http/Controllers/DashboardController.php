<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\MataKuliah;
use App\Models\PengerjaanUjian; // <-- Impor model ini
use App\Models\Ujian;            // <-- Impor model ini
use Illuminate\Support\Facades\Auth;  // <-- Impor Auth
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;                // <-- Impor Carbon

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
        $userAccount = Session::get('account');
        $userProfile = Session::get('profile');
        $activeRoleArray = Session::get('role');
        $sessionToken = $this->getUserAuthToken($request);
        $authProp = ['user' => null];
        $localUser = null;

        if ($userAccount && isset($userAccount['id'])) {
            $externalId = $userAccount['id'];
            $localUser = User::where('external_id', (string) $externalId)->first();

            if ($localUser) {
                // Login-kan user agar Auth::id() berfungsi
                Auth::login($localUser);
                $authProp['user'] = [
                    'id' => $localUser->id,
                    'external_id' => $externalId,
                    'name' => $userProfile['nama'] ?? $userAccount['email'] ?? 'Pengguna Terdaftar',
                    'email' => $userAccount['email'] ?? null,
                    'image' => $userAccount['image'] ?? null,
                    'roles' => $activeRoleArray ?? [],
                    'is_mhs' => $userAccount['is_mhs'] ?? false,
                    'is_dosen' => $userAccount['is_dosen'] ?? false, // Tambahkan untuk konsistensi
                    'is_admin' => $userAccount['is_admin'] ?? false, // Tambahkan untuk konsistensi
                    'nim' => $userProfile['nim'] ?? null,
                ];
            } else {
                Log::warning('DashboardController: Pengguna dengan external_id sesi ' . $externalId . ' tidak ditemukan di DB lokal.');
            }
        }
        
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Sesi tidak valid, silakan login ulang.');
        }

        // --- AWAL PERBAIKAN LOGIKA PENGAMBILAN DATA ---

        // 1. Ambil Histori Ujian (yang sudah selesai)
        $pengerjaanSelesai = PengerjaanUjian::where('user_id', Auth::id())
            ->whereIn('status_pengerjaan', ['selesai', 'selesai_waktu_habis'])
            ->with(['ujian:id,judul_ujian,kkm,durasi', 'ujian.mataKuliah:id,nama']) // Ambil juga durasi
            ->orderBy('waktu_selesai', 'desc')
            ->get();
            
        // Format data riwayat ujian menjadi lebih kaya
        $historiUjian = $pengerjaanSelesai->map(function ($pengerjaan) {
            if (!$pengerjaan->ujian) return null; // Skip jika relasi ujiannya tidak ada
            return [
                'id_pengerjaan' => $pengerjaan->id,
                'id_ujian' => $pengerjaan->ujian->id,
                'judul' => $pengerjaan->ujian->judul_ujian,
                'matakuliah' => $pengerjaan->ujian->mataKuliah->nama ?? 'N/A',
                'skor' => $pengerjaan->skor_total,
                'kkm' => $pengerjaan->ujian->kkm ?? 0,
                'durasi' => $pengerjaan->ujian->durasi,
                'waktu_selesai' => $pengerjaan->waktu_selesai->format('d M Y, H:i'),
                'status' => 'Selesai', // Tambahkan status eksplisit
            ];
        })->filter();

        // 2. Ambil semua ujian yang relevan (yang belum berakhir)
        $now = Carbon::now();
        $ujianTersedia = Ujian::where('status', 'published')
                            ->where('tanggal_selesai', '>=', $now)
                            ->with('mataKuliah:id,nama')
                            ->with(['pengerjaanUjian' => function ($query) {
                                $query->where('user_id', Auth::id())->latest('waktu_mulai');
                            }])
                            ->get();

        // Format data ujian yang akan datang / sedang aktif
        $daftarUjian = $ujianTersedia->map(function ($ujian) use ($now) {
            $pengerjaanTerakhir = $ujian->pengerjaanUjian->first();
            $statusUjian = "Tidak Tersedia";

            if ($now->between($ujian->tanggal_mulai, $ujian->tanggal_selesai)) {
                if (!$pengerjaanTerakhir) {
                    $statusUjian = "Aktif";
                } elseif ($pengerjaanTerakhir->status_pengerjaan === 'sedang_dikerjakan') {
                    $statusUjian = "Aktif"; // Sedang dikerjakan juga termasuk aktif
                } else {
                    // Sudah pernah dikerjakan dan selesai, jadi tidak muncul di daftar aktif lagi
                    return null;
                }
            } elseif ($now->lt($ujian->tanggal_mulai)) {
                $statusUjian = "Mendatang";
            }

            if ($statusUjian === "Tidak Tersedia") return null;

            return [
                'id_ujian' => $ujian->id,
                'judul' => $ujian->judul_ujian,
                'matakuliah' => $ujian->mataKuliah->nama ?? 'N/A',
                'durasi' => $ujian->durasi,
                'tanggal_mulai' => $ujian->tanggal_mulai->format('d M Y, H:i'),
                'tanggal_selesai' => $ujian->tanggal_selesai->format('d M Y, H:i'),
                'status' => $statusUjian,
            ];
        })->filter(); // filter() tanpa argumen akan menghapus nilai null


        // --- AKHIR PERBAIKAN LOGIKA ---

        $daftarMataKuliahLokalMap = MataKuliah::get()->mapWithKeys(function ($mk) {
            return [$mk->external_id => [
                'id_lokal' => $mk->id,
                'nama_lokal' => $mk->nama,
                'kode_lokal' => $mk->kode,
                'img_lokal' => $mk->icon_url ? asset('storage/' . trim($mk->icon_url, '/')) : null,
            ]];
        });
        
        // $availableSemesters = MataKuliah::distinct()->orderBy('semester', 'asc')->pluck('semester');

        return Inertia::render('Dashboard', [
            'auth' => $authProp,
            'daftarMataKuliahLokal' => $daftarMataKuliahLokalMap,
            'historiUjian' => $historiUjian,
            'daftarUjian' => $daftarUjian,   // <-- KIRIM PROP YANG SEBELUMNYA HILANG
            // 'availableSemesters' => $availableSemesters->all(),
            'filters' => $request->only(['semester', 'tahun_ajaran']),
            'apiBaseUrl' => config('myconfig.api.base_url', env('API_BASE_URL')),
            'sessionToken' => $sessionToken,
        ]);
    }
}