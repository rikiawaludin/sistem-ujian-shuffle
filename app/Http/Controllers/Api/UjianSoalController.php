<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ujian;
use App\Services\UjianProses\SoalFormatterService;
use App\Services\UjianProses\ExpressShuffleClientService;
use Illuminate\Support\Facades\Log;
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
        $ujian = Ujian::with(['soal', 'mataKuliah'])->find($id_ujian);

        if (!$ujian) {
            return response()->json(['message' => 'Ujian tidak ditemukan'], 404);
        }

        if ($ujian->soal->isEmpty()) {
            Log::warning('[UjianSoalCtrl] Tidak ada soal untuk Ujian ID: ' . $id_ujian);
            return response()->json([
                'id' => $ujian->id,
                'namaMataKuliah' => $ujian->mataKuliah->nama_mata_kuliah ?? 'N/A',
                'judulUjian' => $ujian->judul_ujian,
                'durasiTotalDetik' => $ujian->durasi * 60,
                'soalList' => [],
            ]);
        }

        try {
            // 1. Format Soal dari Laravel untuk dikirim ke Express.js
            $soalUntukExpress = $this->soalFormatter->formatForExpress($ujian->soal);
            
            // 2. Ambil konfigurasi pengacakan dari model Ujian
            $configPengacakan = [
                'acakUrutanSoal' => $ujian->acak_soal ?? true,
                'acakUrutanOpsi' => $ujian->acak_opsi ?? true,
                'acakUrutanPasangan' => $ujian->acak_opsi_pasangan ?? true, // Anda mungkin perlu menambahkan field ini ke model Ujian
            ];

            // 3. Panggil Express.js untuk melakukan pengacakan
            $shuffledSoalFromExpress = $this->expressClient->shuffleSoalList($soalUntukExpress, $configPengacakan);

            if ($shuffledSoalFromExpress === null) {
                // Error sudah di-log oleh ExpressShuffleClientService
                return response()->json(['message' => 'Gagal mendapatkan data soal yang diacak.'], 500);
            }

            // 4. Format daftar soal yang sudah diacak untuk frontend
            $soalListFormatted = $this->soalFormatter->formatForFrontend($shuffledSoalFromExpress);

        } catch (ConnectionException $e) {
            Log::error('[UjianSoalCtrl] Koneksi ke Express.js gagal untuk Ujian ID: ' . $id_ujian, ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Tidak dapat terhubung ke layanan pengacakan soal.'], 503); // 503 Service Unavailable
        } catch (\RuntimeException $e) { // Menangkap RuntimeException dari ExpressClient atau SoalFormatter
            Log::error('[UjianSoalCtrl] Error saat memproses soal untuk Ujian ID: ' . $id_ujian, ['error' => $e->getMessage()]);
            return response()->json(['message' => $e->getMessage()], ($e->getCode() && is_int($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 500);
        } catch (\Exception $e) {
            Log::error('[UjianSoalCtrl] Error umum tidak terduga untuk Ujian ID: ' . $id_ujian, ['error' => $e->getMessage()]);
            report($e); // Melaporkan error ke sistem error reporting Laravel
            return response()->json(['message' => 'Terjadi kesalahan internal tidak terduga saat memproses soal.'], 500);
        }

        Log::info('[UjianSoalCtrl] Mengirim data final ke frontend untuk Ujian ID: ' . $id_ujian, ['jumlah_soal' => count($soalListFormatted)]);

        return response()->json([
            'id' => $ujian->id,
            'namaMataKuliah' => $ujian->mataKuliah->nama_mata_kuliah ?? 'N/A',
            'judulUjian' => $ujian->judul_ujian,
            'durasiTotalDetik' => $ujian->durasi * 60,
            'soalList' => $soalListFormatted,
        ]);
    }
}