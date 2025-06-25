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
        $now = Carbon::now(config('app.timezone')); // Gunakan timezone dari config
        $userId = Auth::id();

        // 1. Query yang sudah dioptimalkan untuk mengambil semua data yang dibutuhkan
        $ujianTersedia = Ujian::where('mata_kuliah_id', $id_mata_kuliah)
            ->where(function ($query) {
                // Kondisi 1: Tampilkan ujian yang statusnya 'published'
                $query->where('status', 'published')
                      // Kondisi 2: ATAU tampilkan ujian yang sudah 'archived' TAPI hasilnya boleh dilihat
                      ->orWhere(function ($subQuery) {
                          $subQuery->where('status', 'archived')
                                   ->where('visibilitas_hasil', true);
                      });
            })
            // Eager load pengerjaan terakhir oleh user ini untuk menghindari N+1 query
            ->with(['pengerjaanUjian' => function ($query) use ($userId) {
                $query->where('user_id', $userId)->latest('waktu_mulai');
            }])
            ->withSum('aturan', 'jumlah_soal')
            ->orderBy('tanggal_mulai', 'desc')
            ->get();

        // 2. Proses mapping data dengan logika status baru
        $daftarUjian = $ujianTersedia->map(function ($ujian) use ($now) {
            // Ambil data pengerjaan yang sudah di-load, jauh lebih efisien
            $pengerjaanTerakhir = $ujian->pengerjaanUjian->first();

            $statusUjian = "Tidak Tersedia";
            $isFinished = $now->isAfter($ujian->tanggal_selesai);

            if ($isFinished) {
                // Jika waktu ujian sudah selesai
                if ($pengerjaanTerakhir && in_array($pengerjaanTerakhir->status_pengerjaan, ['selesai', 'selesai_waktu_habis'])) {
                    $statusUjian = $ujian->visibilitas_hasil ? "Selesai" : "Selesai (Hasil Ditutup)";
                } else {
                    $statusUjian = "Waktu Habis";
                }
            } elseif ($now->between($ujian->tanggal_mulai, $ujian->tanggal_selesai)) {
                // Jika waktu ujian sedang berlangsung
                if (!$pengerjaanTerakhir) {
                    $statusUjian = "Belum Dikerjakan";
                } elseif ($pengerjaanTerakhir->status_pengerjaan === 'sedang_dikerjakan') {
                    $statusUjian = "Sedang Dikerjakan";
                } elseif (in_array($pengerjaanTerakhir->status_pengerjaan, ['selesai', 'selesai_waktu_habis'])) {
                    $statusUjian = $ujian->visibilitas_hasil ? "Selesai" : "Selesai (Hasil Ditutup)";
                }
            } elseif ($now->isBefore($ujian->tanggal_mulai)) {
                // Jika waktu ujian belum dimulai
                $statusUjian = "Akan Datang";
            }
            
            // Filter tambahan: Jangan tampilkan ujian 'archived' yang belum pernah dikerjakan mahasiswa
            if ($ujian->status === 'archived' && !$pengerjaanTerakhir) {
                return null;
            }

            return [
                'id' => $ujian->id,
                'nama' => $ujian->judul_ujian,
                'deskripsi' => $ujian->deskripsi,
                'durasi' => $ujian->durasi . " Menit",
                'jumlahSoal' => $ujian->aturan_sum_jumlah_soal ?? 0,
                'kkm' => $ujian->kkm,
                'batasWaktuPengerjaan' => $ujian->tanggal_selesai ? Carbon::parse($ujian->tanggal_selesai)->format('d F Y, H:i') : 'Fleksibel',
                'status' => $statusUjian,
                'skor' => $pengerjaanTerakhir->skor_total ?? null,
                'id_pengerjaan_terakhir' => $pengerjaanTerakhir->id ?? null,
                'tanggal_mulai_raw' => $ujian->tanggal_mulai,
                'jenis_ujian' => $ujian->jenis_ujian,
                'visibilitas_hasil' => $ujian->visibilitas_hasil,
            ];
        })->filter();

        // 3. Aturan sorting dengan status baru
        $statusOrder = [
            "Sedang Dikerjakan" => 1,
            "Belum Dikerjakan" => 2,
            "Akan Datang" => 3,
            "Selesai" => 4,
            "Selesai (Hasil Ditutup)" => 5,
            "Waktu Habis" => 6,
            "Tidak Tersedia" => 99,
        ];

        $sortedDaftarUjian = $daftarUjian->sort(function ($a, $b) use ($statusOrder) {
            $priorityA = $statusOrder[$a['status']] ?? 99;
            $priorityB = $statusOrder[$b['status']] ?? 99;

            if ($priorityA !== $priorityB) {
                return $priorityA <=> $priorityB;
            }
            return $b['tanggal_mulai_raw'] <=> $a['tanggal_mulai_raw'];
        });

        return Inertia::render('Ujian/DaftarUjianPage', [
            'auth' => $authProp,
            'mataKuliah' => [
                'id' => $mataKuliah->id,
                'nama' => $mataKuliah->nama,
            ],
            'daftarUjian' => $sortedDaftarUjian->values()->all(),
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
            'ujian.soal.opsiJawaban',
            'detailJawaban'
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
            
            // 1. Decode jawaban user dari format JSON
            $jawabanPenggunaDecoded = null;
            if ($jawabanDataAttempt && $jawabanDataAttempt->jawaban_user) {
                $decoded = json_decode($jawabanDataAttempt->jawaban_user, true);
                // Jika hasil decode tidak null, gunakan itu. Ini menangani kasus "null" atau string kosong.
                $jawabanPenggunaDecoded = ($decoded !== null && $decoded !== '') ? $decoded : null;
            }

            // 2. Untuk soal PG/BS, jika jawaban berupa array dengan satu elemen, ambil elemen tersebut.
            if (is_array($jawabanPenggunaDecoded) && count($jawabanPenggunaDecoded) === 1) {
                $jawabanPenggunaFinal = $jawabanPenggunaDecoded[0];
            } else {
                $jawabanPenggunaFinal = $jawabanPenggunaDecoded;
            }

            return [
                'idSoal' => $soalMasterUjian->id,
                'nomorSoal' => $soalMasterUjian->pivot->nomor_urut_di_ujian ?? ($index + 1),
                'pertanyaan' => $soalMasterUjian->pertanyaan,
                'tipeSoal' => $soalMasterUjian->tipe_soal,
                'opsiJawaban' => $soalMasterUjian->opsiJawaban, // Ini sekarang sudah berisi data lengkap
                'jawabanPengguna' => $jawabanPenggunaFinal, // Menggunakan jawaban yang sudah di-decode
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