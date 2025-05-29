<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ujian;
use App\Models\PengerjaanUjian; // Untuk membuat attempt 'sedang_dikerjakan'
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\UjianProses\SoalFormatterService; // Asumsi service ini ada
use App\Services\UjianProses\ExpressShuffleClientService; // Asumsi service ini ada

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
        $ujian = Ujian::with(['mataKuliah'])->findOrFail($id_ujian);
        $user = Auth::user();

        $sessionKeySoal = 'ujian_berlangsung_' . $ujian->id . '_user_' . $user->id . '_soal';
        $sessionKeyWaktuMulai = 'ujian_berlangsung_' . $ujian->id . '_user_' . $user->id . '_waktu_mulai';
        $sessionKeyAttemptId = 'ujian_berlangsung_' . $ujian->id . '_user_' . $user->id . '_attempt_id'; // Untuk menyimpan ID PengerjaanUjian

        $soalListDariSesi = session($sessionKeySoal);
        $waktuMulaiDariSesi = session($sessionKeyWaktuMulai);

        $soalListFormatted = null;
        $sisaWaktuDetikDihitung = $ujian->durasi * 60; // Default durasi penuh

        if ($soalListDariSesi && $waktuMulaiDariSesi) {
            // Ujian sudah dimulai sebelumnya (misalnya refresh)
            $soalListFormatted = $soalListDariSesi;
            $waktuMulaiCarbon = Carbon::parse($waktuMulaiDariSesi);
            $detikTelahBerlalu = now()->diffInSeconds($waktuMulaiCarbon);
            $sisaWaktuDetikDihitung = max(0, ($ujian->durasi * 60) - $detikTelahBerlalu);

            Log::info("[API UjianSoalCtrl] Ujian ID {$ujian->id} dilanjutkan dari sesi oleh User ID {$user->id}. Sisa waktu: {$sisaWaktuDetikDihitung} detik.");

        } else {
            // Ujian baru dimulai atau sesi tidak ada/kadaluarsa
            Log::info("[API UjianSoalCtrl] Ujian ID {$ujian->id} dimulai baru oleh User ID {$user->id}. Mengambil soal dari Express.");
            
            // Hapus pengerjaan 'sedang_dikerjakan' sebelumnya jika ada, atau tandai 'dibatalkan'
            PengerjaanUjian::where('ujian_id', $ujian->id)
                           ->where('user_id', $user->id)
                           ->where('status_pengerjaan', 'sedang_dikerjakan')
                           ->update(['status_pengerjaan' => 'dibatalkan_sistem', 'waktu_selesai' => now()]);


            // Buat record PengerjaanUjian dengan status 'sedang_dikerjakan'
            $pengerjaanBaru = PengerjaanUjian::create([
                'ujian_id' => $ujian->id,
                'user_id' => $user->id,
                'waktu_mulai' => now(),
                'status_pengerjaan' => 'sedang_dikerjakan',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            session([$sessionKeyAttemptId => $pengerjaanBaru->id]);


            if ($ujian->soal->isEmpty()) {
                Log::warning('[API UjianSoalCtrl] Tidak ada soal untuk Ujian ID: ' . $ujian->id);
                // Tetap simpan sesi agar tidak query terus menerus jika soal memang kosong
                session([$sessionKeySoal => []]);
                session([$sessionKeyWaktuMulai => now()->toIso8601String()]);
                $soalListFormatted = [];
            } else {
                try {
                    $soalUntukExpress = $this->soalFormatter->formatForExpress($ujian->soal);
                    $configPengacakan = [
                        'acakUrutanSoal' => $ujian->acak_soal ?? true,
                        'acakUrutanOpsi' => $ujian->acak_opsi ?? true,
                        'acakUrutanPasangan' => $ujian->acak_opsi_pasangan ?? true,
                    ];
                    $shuffledSoalFromExpress = $this->expressClient->shuffleSoalList($soalUntukExpress, $configPengacakan);

                    if ($shuffledSoalFromExpress === null) {
                        return response()->json(['message' => 'Gagal mendapatkan data soal yang diacak dari service.'], 500);
                    }
                    $soalListFormatted = $this->soalFormatter->formatForFrontend($shuffledSoalFromExpress);

                    session([$sessionKeySoal => $soalListFormatted]);
                    session([$sessionKeyWaktuMulai => $pengerjaanBaru->waktu_mulai->toIso8601String()]); // Gunakan waktu mulai dari record PengerjaanUjian

                } catch (ConnectionException $e) {
                    Log::error('[API UjianSoalCtrl] Koneksi ke Express.js gagal', ['error' => $e->getMessage()]);
                    return response()->json(['message' => 'Tidak dapat terhubung ke layanan soal.'], 503);
                } catch (\RuntimeException $e) {
                    Log::error('[API UjianSoalCtrl] Error saat memproses soal', ['error' => $e->getMessage()]);
                    return response()->json(['message' => $e->getMessage()], ($e->getCode() && is_int($e->getCode())) ? $e->getCode() : 500);
                } catch (\Exception $e) {
                    Log::error('[API UjianSoalCtrl] Error umum tidak terduga', ['error' => $e->getMessage()]);
                    report($e);
                    return response()->json(['message' => 'Terjadi kesalahan internal.'], 500);
                }
            }
        }

        return response()->json([
            'id' => $ujian->id,
            'namaMataKuliah' => $ujian->mataKuliah->nama_mata_kuliah ?? 'N/A',
            'judulUjian' => $ujian->judul_ujian,
            'durasiTotalDetik' => $sisaWaktuDetikDihitung, // Ini adalah SISA WAKTU
            'soalList' => $soalListFormatted,
        ]);
    }
}