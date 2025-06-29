<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PengerjaanUjian;
use App\Models\Soal;
use App\Models\Ujian;
use App\Services\UjianProses\SoalFormatterService;
use App\Services\UjianProses\UjianShuffleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UjianSoalController extends Controller
{
    protected SoalFormatterService $soalFormatter;
    protected UjianShuffleService $ujianShuffleService;

    public function __construct(
        SoalFormatterService $soalFormatter,
        UjianShuffleService $ujianShuffleService
    ) {
        $this->soalFormatter = $soalFormatter;
        $this->ujianShuffleService = $ujianShuffleService;
    }

    public function getSoalUntukUjian(Request $request, $id_ujian)
    {
        $ujian = Ujian::with('mataKuliah', 'aturan')->findOrFail($id_ujian);
        $user = Auth::user();
        $now = Carbon::now();

        // Validasi Jendela Waktu Pengerjaan
        if ($ujian->tanggal_mulai && $now->lt(Carbon::parse($ujian->tanggal_mulai))) {
            return response()->json(['message' => 'Ujian belum dapat dimulai.'], 403);
        }
        if ($ujian->tanggal_selesai && $now->gte(Carbon::parse($ujian->tanggal_selesai))) {
            return response()->json(['message' => 'Periode ujian telah berakhir.'], 403);
        }

        // Mencari atau membuat sesi pengerjaan ujian untuk mahasiswa
        $pengerjaanUjian = PengerjaanUjian::where('ujian_id', $ujian->id)
            ->where('user_id', $user->id)
            ->first();

        if ($pengerjaanUjian) {
            // Jika ujian sudah pernah dikerjakan dan statusnya bukan 'sedang_dikerjakan'
            if ($pengerjaanUjian->status_pengerjaan !== 'sedang_dikerjakan') {
                Log::warning("[API UjianSoalCtrl] Percobaan akses ke ujian yang sudah selesai. Ujian ID: {$ujian->id}, Pengerjaan ID: {$pengerjaanUjian->id}.");
                return response()->json([
                    'message' => 'Ujian ini telah selesai Anda kerjakan.',
                    'redirect_url' => route('ujian.hasil.detail', ['id_attempt' => $pengerjaanUjian->id])
                ], 403);
            }
            $pengerjaanAktif = $pengerjaanUjian;
        } else {
            // Jika belum pernah mengerjakan, buat sesi baru
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

        // Tentukan kunci session yang unik untuk pengerjaan ini
        $sessionKey = 'ujian_attempt_' . $pengerjaanAktif->id;
        $soalListFormatted = session($sessionKey);

        // Jika soal tidak ada di session, proses pembuatan soal
        if (!$soalListFormatted) {
            Log::info("[API UjianSoalCtrl] Session soal untuk '{$sessionKey}' tidak ditemukan. Membuat baru via UjianShuffleService.");
            $ujian->load('soal');

            $soalTerpilihDanTeracak = [];

            // A. Jika soal belum pernah di-generate sama sekali (percobaan pertama)
            if ($ujian->soal->isEmpty()) {
                Log::info("[API UjianSoalCtrl] Ujian ID {$ujian->id} belum memiliki soal. Memulai proses seleksi.");

                if ($ujian->aturan->isEmpty()) {
                    return response()->json(['message' => 'Ujian tidak memiliki aturan pemilihan soal.'], 500);
                }

                // Siapkan pools dan rules dari aturan ujian
                $pools = [];
                $rules = [];
                foreach ($ujian->aturan as $aturan) {
                    if ($aturan->jumlah_soal > 0) {
                        // Buat query dasar untuk soal
                        $kandidatSoalQuery = Soal::where('mata_kuliah_id', $ujian->mata_kuliah_id)
                            ->where('level_kesulitan', $aturan->level_kesulitan);
                                
                        // **PERBAIKAN UTAMA: Tambahkan filter berdasarkan tipe soal dari aturan**
                        if ($aturan->tipe_soal === 'esai') {
                            $kandidatSoalQuery->where('tipe_soal', 'esai');
                        } else {
                            // Asumsi 'non_esai' berarti semua tipe selain 'esai'
                            $kandidatSoalQuery->where('tipe_soal', '!=', 'esai');
                        }

                        // Ambil soal yang sudah difilter dengan benar
                        $kandidatSoal = $kandidatSoalQuery->with('opsiJawaban')->get();

                        // Gunakan kunci komposit untuk mencegah tumpang tindih (cth: mudah-esai vs mudah-non_esai)
                        $poolKey = $aturan->tipe_soal . '_' . $aturan->level_kesulitan;
                                
                        // Hanya tambahkan ke pool jika ada kandidat soal
                        if ($kandidatSoal->isNotEmpty()) {
                            $pools[$poolKey] = $this->soalFormatter->formatForExpress($kandidatSoal);
                            $rules[$poolKey] = $aturan->jumlah_soal;
                        }
                    }
                }

                // Buat payload dan panggil SERVICE LOKAL
                $payload = [
                    'pools' => $pools,
                    'rules' => $rules,
                    'config' => ['acakUrutanSoal' => $ujian->acak_soal, 'acakUrutanOpsi' => $ujian->acak_opsi]
                ];

                try {
                    // ** PERUBAHAN UTAMA: Memanggil service lokal **
                    $soalTerpilihDanTeracak = $this->ujianShuffleService->process($payload);

                    // Simpan ID soal ke database (pivot table)
                    DB::transaction(function () use ($ujian, $soalTerpilihDanTeracak) {
                        $syncData = [];
                        foreach ($soalTerpilihDanTeracak as $index => $soal) {
                            $opsiOrder = collect($soal['pilihan'] ?? [])->pluck('id')->all();

                            $syncData[$soal['id']] = [
                                'nomor_urut_di_ujian' => $index + 1,
                                // 'opsi_jawaban_order'  => json_encode($opsiOrder),
                                'bobot_nilai_soal'    => $soal['bobot'] ?? 0, // Pastikan baris ini ada
                            ];
                        }
                        if (!empty($syncData)) {
                            $ujian->soal()->sync($syncData);
                        }
                    });

                } catch (\Exception $e) {
                    Log::error("[API UjianSoalCtrl] Gagal memproses soal via UjianShuffleService: " . $e->getMessage());
                    return response()->json(['message' => 'Terjadi kesalahan saat mempersiapkan soal ujian.'], 500);
                }

            // B. Jika soal sudah ada di DB tapi tidak ada di session (misal session expired)
            } else {
                Log::info("[API UjianSoalCtrl] Session '{$sessionKey}' hilang, mengacak ulang soal yang sudah ada dari DB.");
                $ujian->load('soal.opsiJawaban');

                $soalUntukDiacak = $this->soalFormatter->formatForExpress($ujian->soal);
                $config = ['acakUrutanSoal' => $ujian->acak_soal, 'acakUrutanOpsi' => $ujian->acak_opsi];

                // Buat payload dan panggil SERVICE LOKAL hanya untuk mengacak
                $payload = ['soalList' => $soalUntukDiacak, 'config' => $config];
                
                // ** PERUBAHAN UTAMA: Memanggil service lokal **
                $soalTerpilihDanTeracak = $this->ujianShuffleService->process($payload);
            }

            // Format untuk frontend (langkah ini tetap sama)
            $soalListFormatted = $this->soalFormatter->formatForFrontend($soalTerpilihDanTeracak);

            // SIMPAN hasil akhir ke session
            if (!empty($soalListFormatted)) {
                session([$sessionKey => $soalListFormatted]);
                Log::info("[API UjianSoalCtrl] Daftar soal untuk '{$sessionKey}' berhasil disimpan ke session.");
            }
        } else {
            Log::info("[API UjianSoalCtrl] Daftar soal untuk '{$sessionKey}' berhasil diambil dari session.");
        }

        // Hitung sisa waktu pengerjaan
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