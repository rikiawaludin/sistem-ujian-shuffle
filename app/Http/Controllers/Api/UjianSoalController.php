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
            return response()->json(['message' => 'Ujian belum dapat dimulai karena belum memasuki periode pelaksanaan.'], 403);
        }
        if ($ujian->tanggal_selesai && $now->gte(Carbon::parse($ujian->tanggal_selesai))) {
            return response()->json(['message' => 'Periode untuk memulai atau melanjutkan ujian ini sudah berakhir.'], 403);
        }

        $pengerjaanAktif = PengerjaanUjian::where('ujian_id', $ujian->id)
            ->where('user_id', $user->id)
            ->where('status_pengerjaan', 'sedang_dikerjakan')
            ->first();

        $soalListFormatted = null;
        $sisaWaktuDetikFinal = 0;
        $durasiTotalUjianDetik = $ujian->durasi * 60;
        $sessionKeySoal = null;

        if ($pengerjaanAktif) {
            Log::info("[API UjianSoalCtrl] Ditemukan pengerjaan aktif ID: {$pengerjaanAktif->id} untuk Ujian ID {$ujian->id}, User ID {$user->id}.");
            $sessionKeySoal = 'ujian_attempt_' . $pengerjaanAktif->id . '_soal';
            $waktuMulaiAbsolut = Carbon::parse($pengerjaanAktif->waktu_mulai);
            
            // Hitung sisa waktu berdasarkan durasi individu
            $detikTelahBerlaluDariMulai = $now->diffInSeconds($waktuMulaiAbsolut);
            $sisaWaktuDariDurasiIndividu = max(0, $durasiTotalUjianDetik - $detikTelahBerlaluDariMulai);

            // Hitung sisa waktu sampai batas akhir ujian global (tanggal_selesai ujian)
            $sisaWaktuSampaiBatasGlobal = PHP_INT_MAX; // Anggap tak terbatas jika tidak ada tanggal_selesai
            if ($ujian->tanggal_selesai) {
                $tanggalSelesaiGlobal = Carbon::parse($ujian->tanggal_selesai);
                if ($now->lt($tanggalSelesaiGlobal)) {
                    $sisaWaktuSampaiBatasGlobal = $tanggalSelesaiGlobal->diffInSeconds($now);
                } else {
                    $sisaWaktuSampaiBatasGlobal = 0; // Waktu global sudah habis
                }
            }
            
            // Sisa waktu aktual adalah yang terkecil dari keduanya
            $sisaWaktuDetikFinal = max(0, min($sisaWaktuDariDurasiIndividu, $sisaWaktuSampaiBatasGlobal));

            if ($sisaWaktuDetikFinal <= 0 && $pengerjaanAktif->status_pengerjaan === 'sedang_dikerjakan') {
                Log::info("[API UjianSoalCtrl] Waktu untuk Pengerjaan ID {$pengerjaanAktif->id} sudah habis (server calc). Sisa: {$sisaWaktuDetikFinal}");
                // Biarkan scheduler atau frontend yang men-trigger submit
            }
            
            $soalListDariSesi = session($sessionKeySoal);
            if ($soalListDariSesi && is_array($soalListDariSesi)) {
                $soalListFormatted = $soalListDariSesi;
                Log::info("[API UjianSoalCtrl] SoalList diambil dari sesi untuk Pengerjaan ID {$pengerjaanAktif->id}.");
            } else {
                Log::warning("[API UjianSoalCtrl] Sesi soalList hilang untuk Pengerjaan ID {$pengerjaanAktif->id}. Mengambil & acak ulang.");
                // Lanjutkan ke blok di bawah untuk generate soal
            }
        }
        
        if (!$pengerjaanAktif || $soalListFormatted === null) {
            if (!$pengerjaanAktif) {
                // ... (logika batalkan attempt lama & buat PengerjaanUjian baru dengan status 'sedang_dikerjakan') ...
                PengerjaanUjian::where('ujian_id', $ujian->id) // Batalkan attempt 'sedang_dikerjakan' sebelumnya
                               ->where('user_id', $user->id)
                               ->where('status_pengerjaan', 'sedang_dikerjakan')
                               ->update([
                                   'status_pengerjaan' => 'dibatalkan_memulai_baru',
                                   'waktu_selesai' => $now
                                ]);
                $pengerjaanAktif = PengerjaanUjian::create([ /* ... data pengerjaan baru ... */ 
                    'ujian_id' => $ujian->id, 'user_id' => $user->id, 'waktu_mulai' => $now,
                    'status_pengerjaan' => 'sedang_dikerjakan', 'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
                Log::info("[API UjianSoalCtrl] PengerjaanUjian ID {$pengerjaanAktif->id} dibuat (status 'sedang_dikerjakan').");
            }
            $sessionKeySoal = 'ujian_attempt_' . $pengerjaanAktif->id . '_soal';
            $waktuMulaiAbsolut = Carbon::parse($pengerjaanAktif->waktu_mulai);

            // Hitung ulang sisa waktu efektif setelah pengerjaan dibuat/ditemukan
            $detikTelahBerlaluDariMulai = $now->diffInSeconds($waktuMulaiAbsolut);
            $sisaWaktuDariDurasiIndividu = max(0, $durasiTotalUjianDetik - $detikTelahBerlaluDariMulai);
            $sisaWaktuSampaiBatasGlobal = PHP_INT_MAX;
            if ($ujian->tanggal_selesai) {
                $tanggalSelesaiGlobal = Carbon::parse($ujian->tanggal_selesai);
                $sisaWaktuSampaiBatasGlobal = $now->lt($tanggalSelesaiGlobal) ? $tanggalSelesaiGlobal->diffInSeconds($now) : 0;
            }
            $sisaWaktuDetikFinal = max(0, min($sisaWaktuDariDurasiIndividu, $sisaWaktuSampaiBatasGlobal));

            if ($ujian->soal->isEmpty()) { /* ... handling soal kosong ... */ }
            else {
                try {
                    $soalUntukExpress = $this->soalFormatter->formatForExpress($ujian->soal);
                    $configPengacakan = [ /* ... config ... */ ];
                    $shuffledSoalFromExpress = $this->expressClient->shuffleSoalList($soalUntukExpress, $configPengacakan);
                    if ($shuffledSoalFromExpress === null) { /* ... error ... */ }
                    $soalListFormatted = $this->soalFormatter->formatForFrontend($shuffledSoalFromExpress);
                } catch (ConnectionException $e) { /* ... error ... */ return response()->json(['message' => 'Tidak dapat terhubung ke layanan soal.'], 503); }
                  catch (\RuntimeException $e) { /* ... error ... */ return response()->json(['message' => $e->getMessage()], ($e->getCode() && is_int($e->getCode())) ? $e->getCode() : 500); }
                  catch (\Exception $e) { /* ... error ... */ report($e); return response()->json(['message' => 'Terjadi kesalahan internal.'], 500); }
            }
            session([$sessionKeySoal => $soalListFormatted]);
            Log::info("[API UjianSoalCtrl] SoalList disimpan ke sesi untuk Pengerjaan ID {$pengerjaanAktif->id}.");
        }
        
        session(['pengerjaan_ujian_aktif_id' => $pengerjaanAktif->id]);

        return response()->json([
            'id' => $ujian->id,
            'pengerjaanId' => $pengerjaanAktif->id,
            'namaMataKuliah' => $ujian->mataKuliah->nama_mata_kuliah ?? 'N/A',
            'judulUjian' => $ujian->judul_ujian,
            'durasiTotalDetik' => $sisaWaktuDetikFinal, // Ini SISA WAKTU EFEKTIF
            'soalList' => $soalListFormatted,
        ]);
    }
}