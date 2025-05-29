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
        $ujian = Ujian::with(['mataKuliah', 'soal'])->findOrFail($id_ujian); // Ambil juga relasi soal
        $user = Auth::user();

        // Cari pengerjaan yang sedang berlangsung untuk user dan ujian ini
        $pengerjaanAktif = PengerjaanUjian::where('ujian_id', $ujian->id)
            ->where('user_id', $user->id)
            ->where('status_pengerjaan', 'sedang_dikerjakan')
            ->first();

        $now = Carbon::now();
        $soalListFormatted = null;
        $sisaWaktuDetikDihitung = 0;
        $durasiTotalUjianDetik = $ujian->durasi * 60;

        $sessionKeySoal = null;
        $sessionKeyWaktuMulaiAbsolut = null; // Waktu mulai absolut dari PengerjaanUjian

        if ($pengerjaanAktif) {
            // Ada pengerjaan aktif, coba lanjutkan
            Log::info("[API UjianSoalCtrl] Ditemukan pengerjaan aktif ID: {$pengerjaanAktif->id} untuk Ujian ID {$ujian->id}, User ID {$user->id}.");
            $sessionKeySoal = 'ujian_attempt_' . $pengerjaanAktif->id . '_soal';
            $waktuMulaiAbsolut = Carbon::parse($pengerjaanAktif->waktu_mulai);
            
            $detikTelahBerlalu = $now->diffInSeconds($waktuMulaiAbsolut);
            $sisaWaktuDetikDihitung = max(0, $durasiTotalUjianDetik - $detikTelahBerlalu);

            if ($sisaWaktuDetikDihitung <= 0 && $pengerjaanAktif->status_pengerjaan === 'sedang_dikerjakan') {
                // Waktu sudah habis tapi status masih 'sedang_dikerjakan'
                // Tugas terjadwal akan menangani ini, tapi kita bisa tandai di sini juga.
                // Atau biarkan frontend yang trigger submit. Untuk API ini, kita tetap kirim sisa waktu 0.
                Log::info("[API UjianSoalCtrl] Waktu untuk Pengerjaan ID {$pengerjaanAktif->id} sudah habis berdasarkan kalkulasi server.");
            }
            
            $soalListDariSesi = session($sessionKeySoal);
            if ($soalListDariSesi && is_array($soalListDariSesi)) {
                $soalListFormatted = $soalListDariSesi;
                Log::info("[API UjianSoalCtrl] SoalList diambil dari sesi untuk Pengerjaan ID {$pengerjaanAktif->id}.");
            } else {
                // Sesi soal hilang, tapi ada pengerjaan aktif. Perlu ambil & acak ulang (atau error).
                // Untuk konsistensi, jika sesi soal hilang, kita anggap perlu di-generate ulang.
                // Ini juga berarti jika soal diubah di tengah ujian, user akan dapat versi baru saat refresh.
                // Opsi lain: simpan soalList juga di DB (misal di pengerjaan_ujian jika besar)
                Log::warning("[API UjianSoalCtrl] Sesi soalList hilang untuk Pengerjaan ID {$pengerjaanAktif->id}. Mengambil dan mengacak ulang.");
                // Lanjutkan ke blok pengambilan soal baru di bawah
            }
        }
        
        // Jika tidak ada pengerjaan aktif ATAU sesi soal hilang untuk pengerjaan aktif
        if (!$pengerjaanAktif || $soalListFormatted === null) {
            if (!$pengerjaanAktif) {
                Log::info("[API UjianSoalCtrl] Ujian ID {$ujian->id} dimulai baru oleh User ID {$user->id}. Membuat record PengerjaanUjian.");
                 // Batalkan attempt 'sedang_dikerjakan' sebelumnya jika ada (misalnya karena tab ditutup paksa)
                PengerjaanUjian::where('ujian_id', $ujian->id)
                               ->where('user_id', $user->id)
                               ->where('status_pengerjaan', 'sedang_dikerjakan')
                               ->update([
                                   'status_pengerjaan' => 'dibatalkan_memulai_baru',
                                   'waktu_selesai' => $now
                                ]);

                $pengerjaanAktif = PengerjaanUjian::create([
                    'ujian_id' => $ujian->id,
                    'user_id' => $user->id,
                    'waktu_mulai' => $now,
                    'status_pengerjaan' => 'sedang_dikerjakan',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
                Log::info("[API UjianSoalCtrl] PengerjaanUjian ID {$pengerjaanAktif->id} dibuat dengan status 'sedang_dikerjakan'.");
                $sessionKeySoal = 'ujian_attempt_' . $pengerjaanAktif->id . '_soal'; // Update kunci sesi
            }
            // Waktu mulai untuk perhitungan sisa waktu adalah waktu mulai pengerjaan aktif
            $waktuMulaiAbsolut = Carbon::parse($pengerjaanAktif->waktu_mulai);
            $detikTelahBerlalu = $now->diffInSeconds($waktuMulaiAbsolut);
            $sisaWaktuDetikDihitung = max(0, $durasiTotalUjianDetik - $detikTelahBerlalu);

            if ($ujian->soal->isEmpty()) {
                Log::warning('[API UjianSoalCtrl] Tidak ada soal terkonfigurasi untuk Ujian ID: ' . $ujian->id);
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
                        return response()->json(['message' => 'Gagal mengambil soal teracak dari layanan eksternal.'], 500);
                    }
                    $soalListFormatted = $this->soalFormatter->formatForFrontend($shuffledSoalFromExpress);
                } catch (ConnectionException $e) { /* ... error handling ... */ }
                  catch (\RuntimeException $e) { /* ... error handling ... */ }
                  catch (\Exception $e) { /* ... error handling ... */ }
            }
            session([$sessionKeySoal => $soalListFormatted]);
            Log::info("[API UjianSoalCtrl] SoalList disimpan/diperbarui ke sesi untuk Pengerjaan ID {$pengerjaanAktif->id}.");
        }
        
        // Simpan pengerjaan_id ke sesi agar bisa diakses PengerjaanUjianController saat submit
        // Ini berguna jika frontend tidak mengirim pengerjaan_id secara eksplisit.
        session(['pengerjaan_ujian_aktif_id' => $pengerjaanAktif->id]);


        return response()->json([
            'id' => $ujian->id, // ID Ujian
            'pengerjaanId' => $pengerjaanAktif->id, // ID PengerjaanUjian yang aktif
            'namaMataKuliah' => $ujian->mataKuliah->nama_mata_kuliah ?? 'N/A',
            'judulUjian' => $ujian->judul_ujian,
            'durasiTotalDetik' => $sisaWaktuDetikDihitung, // Ini SISA WAKTU aktual
            'soalList' => $soalListFormatted,
        ]);
    }
}