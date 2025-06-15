<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ujian;
use App\Models\PengerjaanUjian;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Services\UjianProses\SoalFormatterService;
use App\Services\UjianProses\ExpressShuffleClientService;
use Illuminate\Http\Client\ConnectionException;

use App\Models\Soal;
use Illuminate\Support\Facades\DB;

class UjianSoalController extends Controller
{
    protected SoalFormatterService $soalFormatter;
    protected ExpressShuffleClientService $expressClient;

    public function __construct(
        SoalFormatterService $soalFormatter,
        ExpressShuffleClientService $expressClient
    ) {
        $this->soalFormatter = $soalFormatter;
        $this->expressClient = $expressClient;
    }

    public function getSoalUntukUjian(Request $request, $id_ujian)
    {
        $ujian = Ujian::with('mataKuliah', 'aturan')->findOrFail($id_ujian);
        $user = Auth::user();
        $now = Carbon::now();

        // Validasi Jendela Waktu (tidak berubah)
        if ($ujian->tanggal_mulai && $now->lt(Carbon::parse($ujian->tanggal_mulai))) {
            return response()->json(['message' => 'Ujian belum dapat dimulai.'], 403);
        }
        if ($ujian->tanggal_selesai && $now->gte(Carbon::parse($ujian->tanggal_selesai))) {
            return response()->json(['message' => 'Periode ujian telah berakhir.'], 403);
        }

        // =========================================================================
        // AWAL BLOK LOGIKA PengerjaanUjian YANG DIPERBARUI
        // =========================================================================
        
        // 1. Cari pengerjaan APAPUN yang sudah ada untuk user dan ujian ini.
        $pengerjaanUjian = PengerjaanUjian::where('ujian_id', $ujian->id)
            ->where('user_id', $user->id)
            ->first();

        // 2. Jika pengerjaan sudah ada, periksa statusnya.
        if ($pengerjaanUjian) {
            // Jika statusnya BUKAN 'sedang_dikerjakan', berarti sudah selesai atau dievaluasi.
            // Jangan biarkan mahasiswa mengerjakan ulang. Arahkan ke halaman hasil.
            if ($pengerjaanUjian->status_pengerjaan !== 'sedang_dikerjakan') {
                Log::warning("[API UjianSoalCtrl] Percobaan akses ke ujian yang sudah selesai. Ujian ID: {$ujian->id}, Pengerjaan ID: {$pengerjaanUjian->id}.");
                return response()->json([
                    'message' => 'Ujian ini telah selesai Anda kerjakan.',
                    // Memberikan URL redirect akan sangat membantu frontend untuk mengarahkan pengguna.
                    'redirect_url' => route('ujian.hasil.detail', ['id_attempt' => $pengerjaanUjian->id]) 
                ], 403); // 403 Forbidden adalah status yang sesuai.
            }
            // Jika statusnya 'sedang_dikerjakan', kita gunakan record ini.
            $pengerjaanAktif = $pengerjaanUjian;
        } else {
            // 3. Jika TIDAK ADA pengerjaan sama sekali, buat yang baru.
            Log::info("[API UjianSoalCtrl] Membuat PengerjaanUjian baru untuk Ujian ID: {$ujian->id}, User ID: {$user->id}.");
            $pengerjaanAktif = PengerjaanUjian::create([
                'ujian_id' => $ujian->id,
                'user_id' => $user->id,
                'status_pengerjaan' => 'sedang_dikerjakan',
                'waktu_mulai' => $now,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }
        // =========================================================================
        // AKHIR BLOK LOGIKA PengerjaanUjian YANG DIPERBARUI
        // =========================================================================


        // Tentukan kunci session yang unik untuk pengerjaan ini
        $sessionKey = 'ujian_attempt_' . $pengerjaanAktif->id;

        // Coba ambil daftar soal dari session terlebih dahulu
        $soalListFormatted = session($sessionKey);

        // Jika TIDAK ADA di session, maka kita proses untuk membuatnya
        if (!$soalListFormatted) {
            Log::info("[API UjianSoalCtrl] Session soal untuk '{$sessionKey}' tidak ditemukan. Membuat baru.");
            $ujian->load('soal');

            // A. Jika soal belum pernah di-generate sama sekali (percobaan pertama)
            if ($ujian->soal->isEmpty()) {
                Log::info("[API UjianSoalCtrl] Ujian ID {$ujian->id} belum memiliki soal. Memulai proses seleksi via Express.");

                if ($ujian->aturan->isEmpty()) {
                    return response()->json(['message' => 'Ujian tidak memiliki aturan pemilihan soal.'], 500);
                }

                // Siapkan pools dan rules
                $pools = [];
                $rules = [];
                foreach ($ujian->aturan as $aturan) {
                    if ($aturan->jumlah_soal > 0) {
                        $level = $aturan->level_kesulitan;
                        $kandidatSoal = Soal::where('mata_kuliah_id', $ujian->mata_kuliah_id)
                            ->where('level_kesulitan', $level)->with('opsiJawaban')->get();
                        
                        $pools[$level] = $this->soalFormatter->formatForExpress($kandidatSoal);
                        $rules[$level] = $aturan->jumlah_soal;
                    }
                }
                
                // Buat payload dan panggil Express
                $payloadForExpress = [
                    'pools' => $pools, 'rules' => $rules,
                    'config' => ['acakUrutanSoal' => $ujian->acak_soal, 'acakUrutanOpsi' => $ujian->acak_opsi]
                ];
                
                try {
                    $soalTerpilihDariExpress = $this->expressClient->pickAndShuffle($payloadForExpress);
                    
                    // Simpan ID soal ke database (pivot table)
                    DB::transaction(function () use ($ujian, $soalTerpilihDariExpress) {
                        $selectedSoalIds = collect($soalTerpilihDariExpress)->pluck('id')->all();
                        if (!empty($selectedSoalIds)) {
                            $ujian->soal()->attach($selectedSoalIds);
                        }
                    });

                    // Format untuk frontend
                    $soalListFormatted = $this->soalFormatter->formatForFrontend($soalTerpilihDariExpress);

                } catch (\Exception $e) {
                    Log::error("[API UjianSoalCtrl] Gagal memproses soal via Express: " . $e->getMessage());
                    return response()->json(['message' => 'Terjadi kesalahan saat mempersiapkan soal ujian.'], 500);
                }

            // B. Jika soal sudah ada di DB tapi tidak ada di session (misal session expired)
            } else {
                Log::info("[API UjianSoalCtrl] Session '{$sessionKey}' hilang, mengacak ulang soal yang sudah ada dari DB.");
                $ujian->load('soal.opsiJawaban');

                $soalUntukDiacak = $this->soalFormatter->formatForExpress($ujian->soal);
                $config = ['acakUrutanSoal' => $ujian->acak_soal, 'acakUrutanOpsi' => $ujian->acak_opsi];

                // Panggil Express hanya untuk mengacak, bukan memilih lagi
                $soalTerpilihDariExpress = $this->expressClient->pickAndShuffle(['soalList' => $soalUntukDiacak, 'config' => $config]);
                $soalListFormatted = $this->soalFormatter->formatForFrontend($soalTerpilihDariExpress);
            }

            // SIMPAN hasil akhir ke session!
            if (!empty($soalListFormatted)) {
                session([$sessionKey => $soalListFormatted]);
                Log::info("[API UjianSoalCtrl] Daftar soal untuk '{$sessionKey}' berhasil disimpan ke session.");
            }
        } else {
            Log::info("[API UjianSoalCtrl] Daftar soal untuk '{$sessionKey}' berhasil diambil dari session.");
        }
        
        // Hitung sisa waktu (tidak berubah)
        $durasiTotalUjianDetik = $ujian->durasi * 60;
        $detikTelahBerlalu = $now->diffInSeconds(Carbon::parse($pengerjaanAktif->waktu_mulai));
        $sisaWaktuDariDurasiIndividu = max(0, $durasiTotalUjianDetik - $detikTelahBerlalu);
        $sisaWaktuSampaiBatasGlobal = $ujian->tanggal_selesai ? max(0, Carbon::parse($ujian->tanggal_selesai)->diffInSeconds($now)) : PHP_INT_MAX;
        $sisaWaktuDetikFinal = min($sisaWaktuDariDurasiIndividu, $sisaWaktuSampaiBatasGlobal);
        
        // Kirim respons final ke frontend
        return response()->json([
            'id' => $ujian->id,
            'pengerjaanId' => $pengerjaanAktif->id,
            'namaMataKuliah' => $ujian->mataKuliah->nama ?? 'N/A',
            'judulUjian' => $ujian->judul_ujian,
            'durasiTotalDetik' => $sisaWaktuDetikFinal,
            'soalList' => $soalListFormatted ?? [],
        ]);
    }
}