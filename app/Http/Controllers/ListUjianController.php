<?php

namespace App\Http\Controllers;

use App\Models\MataKuliah;
use App\Models\PengerjaanUjian;
use App\Models\Soal;
use App\Models\Ujian;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ListUjianController extends Controller
{

    private function getUserAuthToken(Request $request): ?string
    {
        $sessionToken = $request->session()->get('token');
        if ($sessionToken) {
            return $sessionToken;
        }
        Log::warning('ListUjianController: Token sesi "token" tidak ditemukan.');
        return null;
    }

    /**
     * Menampilkan daftar ujian untuk mata kuliah tertentu.
     */
    public function daftarPerMataKuliah($id_mata_kuliah)
    {
        // =================================================================
        // AWAL PERBAIKAN
        // =================================================================
        $userAccount = Session::get('account');
        $userProfile = Session::get('profile');
        $activeRoleArray = Session::get('role');
        $authProp = ['user' => null];

        if ($userAccount && isset($userAccount['id'])) {
            $localUser = User::where('external_id', $userAccount['id'])->first();
            
            if ($localUser) {
                // JEMBATAN PENTING: Loginkan pengguna ke sistem Auth Laravel
                // agar request API berikutnya dikenali.
                Auth::login($localUser);

                $authProp['user'] = [
                    'id' => $localUser->id,
                    'external_id' => $userAccount['id'],
                    'name' => $userProfile['nama'] ?? $userAccount['email'] ?? 'Pengguna',
                    'email' => $userAccount['email'] ?? null,
                    'image' => $userAccount['image'] ?? null,
                    'roles' => $activeRoleArray ?? [],
                    'is_mhs' => $userAccount['is_mhs'] ?? false,
                    'nim' => $userProfile['nim'] ?? null,
                    'nama_jurusan' => $userProfile['nama_jurusan'] ?? null,
                    'kd_user' => $userAccount['kd_user'] ?? null,
                ];
            }
        }
        
        // Memastikan pengguna benar-benar terotentikasi sebelum melanjutkan
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Sesi Anda tidak valid. Silakan login kembali.');
        }
        // =================================================================
        // AKHIR PERBAIKAN
        // =================================================================

        $mataKuliah = MataKuliah::findOrFail($id_mata_kuliah);

        $ujianTersedia = Ujian::where('mata_kuliah_id', $id_mata_kuliah)
                            ->where('status_publikasi', 'published')
                            ->withCount('soal')
                            ->get();

        $now = Carbon::now();
        
        // Mengambil ID pengguna yang sudah terotentikasi untuk query pengerjaan ujian
        $userId = Auth::id(); 
        $daftarUjian = $ujianTersedia->map(function ($ujian) use ($now, $userId) {
            $pengerjaanTerakhir = PengerjaanUjian::where('ujian_id', $ujian->id)
                                    ->where('user_id', $userId) // Menggunakan $userId yang sudah pasti ada
                                    ->latest('waktu_mulai')
                                    ->first();

            $statusUjian = "Tidak Tersedia";

            if ($now->between($ujian->tanggal_mulai, $ujian->tanggal_selesai)) {
                if (!$pengerjaanTerakhir) {
                    $statusUjian = "Belum Dikerjakan";
                } elseif ($pengerjaanTerakhir->status_pengerjaan === 'sedang_dikerjakan') {
                    $statusUjian = "Sedang Dikerjakan";
                } elseif ($pengerjaanTerakhir->status_pengerjaan === 'selesai') {
                    $statusUjian = "Selesai";
                }
            } elseif ($now->lt($ujian->tanggal_mulai)) {
                $statusUjian = "Akan Datang";
            } elseif ($now->gt($ujian->tanggal_selesai)) {
                if ($pengerjaanTerakhir && $pengerjaanTerakhir->status_pengerjaan === 'selesai') {
                    $statusUjian = "Selesai";
                } else {
                    $statusUjian = "Waktu Habis";
                }
            }
            
            return [
                'id' => $ujian->id,
                'nama' => $ujian->judul_ujian,
                'deskripsi' => $ujian->deskripsi,
                'durasi' => $ujian->durasi . " Menit",
                'jumlahSoal' => $ujian->soal_count,
                'kkm' => $ujian->kkm,
                'batasWaktuPengerjaan' => $ujian->tanggal_selesai ? Carbon::parse($ujian->tanggal_selesai)->format('d F Y, H:i') : 'Fleksibel',
                'status' => $statusUjian,
                'skor' => $pengerjaanTerakhir->skor_total ?? null,
                'id_pengerjaan_terakhir' => $pengerjaanTerakhir->id ?? null,
            ];
        });

        return Inertia::render('Ujian/DaftarUjianPage', [
            'auth' => $authProp,
            'mataKuliah' => [
                'id' => $mataKuliah->id,
                'nama' => $mataKuliah->nama,
            ],
            'daftarUjian' => $daftarUjian
        ]);
    }


    /**
     * Menampilkan halaman pengerjaan ujian.
     */
    public function kerjakanUjian(Request $request, $id_ujian)
    {
        // =================================================================
        // AWAL PERBAIKAN
        // =================================================================
        $userAccount = Session::get('account');
        $userProfile = Session::get('profile');
        $activeRoleArray = Session::get('role');
        $authProp = ['user' => null];

        if ($userAccount && isset($userAccount['id'])) {
            $localUser = User::where('external_id', $userAccount['id'])->first();
            
            if ($localUser) {
                // JEMBATAN PENTING: Loginkan pengguna ke sistem Auth Laravel
                Auth::login($localUser);

                $authProp['user'] = [
                    'id' => $localUser->id,
                    'external_id' => $userAccount['id'],
                    'name' => $userProfile['nama'] ?? 'Pengguna',
                    'email' => $userAccount['email'] ?? null,
                    'image' => $userAccount['image'] ?? null,
                    'roles' => $activeRoleArray ?? [],
                ];
            }
        }

        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Sesi Anda tidak valid. Silakan login kembali.');
        }
        // =================================================================
        // AKHIR PERBAIKAN
        // =================================================================

        $sessionToken = $this->getUserAuthToken($request);
        $ujian = Ujian::select('id', 'judul_ujian')->findOrFail((int)$id_ujian);
        
        return Inertia::render('Ujian/PengerjaanUjianPage', [
            'auth' => $authProp,
            'idUjianAktif' => $ujian->id,
            'sessionToken' => $sessionToken,
            'apiBaseUrl' => config('myconfig.api.base_url', env('API_BASE_URL')),
        ]);
    }

    /**
     * Menampilkan halaman konfirmasi setelah ujian selesai.
     */
    public function konfirmasiSelesaiUjian($id_ujian)
    {
        $ujian = Ujian::with('mataKuliah:id,nama')
                    ->select('id', 'judul_ujian', 'mata_kuliah_id')
                    ->findOrFail((int)$id_ujian);
        return Inertia::render('Ujian/KonfirmasiSelesaiUjianPage', [
            'namaUjian' => $ujian->judul_ujian,
            'namaMataKuliah' => $ujian->mataKuliah->nama ?? 'Mata Kuliah Tidak Diketahui'
        ]);
    }

    /**
     * Menampilkan detail hasil pengerjaan ujian.
     */
    public function detailHasilUjian($id_attempt)
    {
        // Metode ini sudah benar karena mengandalkan Auth::id() yang kini akan valid.
        $attempt = PengerjaanUjian::with([
            'ujian:id,judul_ujian,kkm,mata_kuliah_id,durasi',
            'ujian.mataKuliah:id,nama',
            'ujian.soal',
            'detailJawaban.soal'
        ])
        ->where('user_id', Auth::id())
        ->findOrFail((int)$id_attempt);

        $ujianDetail = $attempt->ujian;
        if (!$ujianDetail) {
            abort(404, 'Detail ujian untuk hasil ini tidak ditemukan.');
        }
        $mataKuliahDetail = $ujianDetail->mataKuliah;

        $kkmUjian = $ujianDetail->kkm ?? 0;
        $statusKelulusan = "Belum Dinilai";
        if (isset($attempt->skor_total)) {
            $statusKelulusan = ($attempt->skor_total >= $kkmUjian ? "Lulus" : "Tidak Lulus");
        }

        $jawabanUserPerSoalMap = $attempt->detailJawaban->keyBy('soal_id');

        $detailSoalJawaban = collect($ujianDetail->soal)->map(function($soalMasterUjian, $index) use ($jawabanUserPerSoalMap) {
            $jawabanDataAttempt = $jawabanUserPerSoalMap->get($soalMasterUjian->id);
            $nomorUrut = $soalMasterUjian->pivot->nomor_urut_di_ujian ?? ($index + 1);
            $opsiJawabanFinal = $soalMasterUjian->opsi_jawaban; 
            $kunciJawabanFinal = $soalMasterUjian->kunci_jawaban;
            
            if (($soalMasterUjian->tipe_soal === 'pilihan_ganda' || $soalMasterUjian->tipe_soal === 'benar_salah') && is_array($kunciJawabanFinal) && count($kunciJawabanFinal) === 1) {
                $firstKey = $kunciJawabanFinal[0];
                $kunciJawabanFinal = is_object($firstKey) && isset($firstKey->id) ? (string)$firstKey->id : $firstKey;
            }

            $jawabanPenggunaFinal = $jawabanDataAttempt->jawaban_user ?? null;
            if (($soalMasterUjian->tipe_soal === 'pilihan_ganda' || $soalMasterUjian->tipe_soal === 'benar_salah') && is_array($jawabanPenggunaFinal) && count($jawabanPenggunaFinal) === 1) {
                 $firstAnswer = $jawabanPenggunaFinal[0];
                 $jawabanPenggunaFinal = is_object($firstAnswer) && isset($firstAnswer->id) ? (string)$firstAnswer->id : $firstAnswer;
            }

            return [
                'idSoal' => $soalMasterUjian->id,
                'nomorSoal' => $nomorUrut,
                'pertanyaan' => $soalMasterUjian->pertanyaan,
                'tipeSoal' => $soalMasterUjian->tipe_soal,
                'opsiJawaban' => $opsiJawabanFinal,
                'jawabanPengguna' => $jawabanPenggunaFinal,
                'kunciJawaban' => $kunciJawabanFinal,
                'isBenar' => $jawabanDataAttempt->is_benar ?? null,
                'penjelasan' => $soalMasterUjian->penjelasan,
            ];
        })->sortBy('nomorSoal')->values()->all();

        $jumlahSoalDiUjian = count($detailSoalJawaban);
        $jumlahBenar = $attempt->detailJawaban->where('is_benar', true)->count();
        $jumlahSalah = $attempt->detailJawaban->filter(fn ($item) => $item->is_benar === false)->count();
        $jumlahDijawab = $attempt->detailJawaban->filter(fn ($item) => $item->jawaban_user !== null)->count();
        $jumlahTidakDijawab = $jumlahSoalDiUjian - $jumlahDijawab;

        $hasilUjianData = [
            'idAttempt' => $attempt->id,
            'namaMataKuliah' => $mataKuliahDetail->nama ?? 'N/A',
            'judulUjian' => $ujianDetail->judul_ujian ?? 'N/A',
            'ujian' => [ 'id' => $ujianDetail->id, 'mata_kuliah_id' => $ujianDetail->mata_kuliah_id ],
            'tanggalPengerjaan' => $attempt->waktu_selesai ? $attempt->waktu_selesai->format('d M Y, H:i') : 'N/A',
            'skorTotal' => $attempt->skor_total,
            'kkm' => $kkmUjian,
            'statusKelulusan' => $statusKelulusan,
            'waktuDihabiskan' => $attempt->waktu_dihabiskan_detik ? gmdate("H\j i\m s\d", $attempt->waktu_dihabiskan_detik) : "N/A",
            'jumlahSoalBenar' => $jumlahBenar,
            'jumlahSoalSalah' => $jumlahSalah,
            'jumlahSoalTidakDijawab' => $jumlahTidakDijawab,
            'detailSoalJawaban' => $detailSoalJawaban,
        ];

        return Inertia::render('Ujian/DetailHasilUjianPage', ['hasilUjian' => $hasilUjianData]);
    }

    /**
     * Menampilkan halaman riwayat semua ujian pengguna.
     */
    public function riwayatUjian()
    {
        // Metode ini sudah benar karena mengandalkan Auth::check() yang kini akan valid.
        $semuaHistoriUjian = [];
        if (Auth::check()) {
            $pengerjaanUjian = PengerjaanUjian::where('user_id', Auth::id())
                ->with(['ujian:id,judul_ujian,kkm,mata_kuliah_id', 'ujian.mataKuliah:id,nama'])
                ->orderBy('waktu_selesai', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            $semuaHistoriUjian = $pengerjaanUjian->map(function ($attempt) {
                $ujian = $attempt->ujian;
                $mataKuliah = $ujian ? $ujian->mataKuliah : null;
                $kkmUjian = $ujian->kkm ?? 0;
                $statusKelulusan = "Belum Dinilai";

                if (isset($attempt->skor_total)) {
                    $statusKelulusan = ($attempt->skor_total >= $kkmUjian ? "Lulus" : "Tidak Lulus");
                }

                return [
                    'id_pengerjaan' => $attempt->id,
                    'namaUjian' => $ujian->judul_ujian ?? 'Ujian Tidak Ditemukan',
                    'namaMataKuliah' => $mataKuliah->nama ?? 'Mata Kuliah Tidak Ditemukan',
                    'tanggalPengerjaan' => $attempt->waktu_selesai ? $attempt->waktu_selesai->format('d M Y') : 'N/A',
                    'skor' => $attempt->skor_total,
                    'kkm' => $kkmUjian,
                    'statusKelulusan' => $statusKelulusan,
                ];
            });
        }
        return Inertia::render('Ujian/HistoriUjianListPage', ['semuaHistoriUjian' => $semuaHistoriUjian]);
    }
}