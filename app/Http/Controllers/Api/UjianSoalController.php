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
        $ujian = Ujian::with(['mataKuliah', 'soal'])->findOrFail($id_ujian);
        $user = Auth::user();
        $now = Carbon::now();

        // Validasi Jendela Waktu Pelaksanaan Ujian Global
        if ($ujian->tanggal_mulai && $now->lt(Carbon::parse($ujian->tanggal_mulai))) {
            return response()->json(['message' => 'Ujian belum dapat dimulai.'], 403);
        }
        if ($ujian->tanggal_selesai && $now->gte(Carbon::parse($ujian->tanggal_selesai))) {
            return response()->json(['message' => 'Periode ujian telah berakhir.'], 403);
        }

        // Cari pengerjaan yang sedang berlangsung
        $pengerjaanAktif = PengerjaanUjian::where('ujian_id', $ujian->id)
            ->where('user_id', $user->id)
            ->where('status_pengerjaan', 'sedang_dikerjakan')
            ->first();

        $soalListFormatted = null;
        $sisaWaktuDetikFinal = 0;
        $durasiTotalUjianDetik = $ujian->durasi * 60;

        // --- BLOK 1: Jika sudah ada pengerjaan aktif (melanjutkan ujian) ---
        if ($pengerjaanAktif) {
            $sessionKeySoal = 'ujian_attempt_' . $pengerjaanAktif->id . '_soal';
            
            // Coba ambil soal yang sudah diacak dari sesi
            $soalListFormatted = session($sessionKeySoal);

            if ($soalListFormatted && is_array($soalListFormatted)) {
                Log::info("[API UjianSoalCtrl] SoalList diambil dari sesi untuk Pengerjaan ID {$pengerjaanAktif->id}.");
            } else {
                Log::warning("[API UjianSoalCtrl] Sesi soalList hilang untuk Pengerjaan ID {$pengerjaanAktif->id}. Akan mengacak ulang.");
                $soalListFormatted = null; // Pastikan null agar masuk ke blok generate ulang
            }
        }
        
        // --- BLOK 2: Jika memulai ujian baru ATAU sesi hilang ---
        if (!$pengerjaanAktif || $soalListFormatted === null) {
            if (!$pengerjaanAktif) {
                // Buat pengerjaan baru
                $pengerjaanAktif = PengerjaanUjian::create([
                    'ujian_id' => $ujian->id,
                    'user_id' => $user->id,
                    'waktu_mulai' => $now,
                    'status_pengerjaan' => 'sedang_dikerjakan',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
                Log::info("[API UjianSoalCtrl] PengerjaanUjian ID {$pengerjaanAktif->id} dibuat.");
            }
            
            $sessionKeySoal = 'ujian_attempt_' . $pengerjaanAktif->id . '_soal';

            // Jika tidak ada soal sama sekali, langsung kembalikan array kosong
            if ($ujian->soal->isEmpty()) {
                 Log::warning("[API UjianSoalCtrl] Ujian ID {$ujian->id} tidak memiliki soal sama sekali.");
                 $soalListFormatted = [];
            } else {
                // Proses pengambilan dan pengacakan soal
                try {
                    $soalUntukExpress = $this->soalFormatter->formatForExpress($ujian->soal);
                    $configPengacakan = ['acakSoal' => $ujian->acak_soal, 'acakOpsi' => $ujian->acak_opsi];
                    
                    // Panggil Express Service
                    $shuffledSoalFromExpress = $this->expressClient->shuffleSoalList($soalUntukExpress, $configPengacakan);

                    // Format hasil untuk frontend
                    $soalListFormatted = $this->soalFormatter->formatForFrontend($shuffledSoalFromExpress);
                    
                    // Simpan soal yang sudah diacak ke sesi
                    session([$sessionKeySoal => $soalListFormatted]);
                    Log::info("[API UjianSoalCtrl] SoalList disimpan ke sesi untuk Pengerjaan ID {$pengerjaanAktif->id}.");

                } catch (ConnectionException $e) {
                    Log::error("[API UjianSoalCtrl] Gagal terhubung ke Express Service: " . $e->getMessage());
                    return response()->json(['message' => 'Tidak dapat terhubung ke layanan soal.'], 503);
                } catch (\RuntimeException $e) {
                    Log::error("[API UjianSoalCtrl] Gagal memproses soal: " . $e->getMessage());
                    return response()->json(['message' => $e->getMessage()], 500);
                } catch (\Exception $e) {
                    report($e);
                    return response()->json(['message' => 'Terjadi kesalahan internal saat memproses soal.'], 500);
                }
            }
        }
        
        // Hitung sisa waktu final setelah semua proses
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
            /**
             * PERBAIKAN UTAMA:
             * Menggunakan `?? []` untuk memastikan `soalList` selalu berupa array.
             * Jika `$soalListFormatted` karena suatu hal bernilai null, frontend akan menerima `[]` (array kosong).
             * Ini akan mencegah error "format tidak benar" dan membuat frontend lebih stabil.
             */
            'soalList' => $soalListFormatted ?? [],
        ]);
    }
}