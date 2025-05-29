<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PengerjaanUjian;
use App\Models\Ujian;
use App\Models\JawabanPesertaDetail;
use App\Models\Soal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoSubmitExpiredExams extends Command
{
    protected $signature = 'ujian:auto-submit-expired';
    protected $description = 'Secara otomatis men-submit ujian yang waktunya sudah habis dan masih berstatus sedang dikerjakan.';

    public function handle()
    {
        Log::info('[AutoSubmitScheduler] Memulai pengecekan ujian yang waktu habis...');
        $now = Carbon::now();

        $activeAttempts = PengerjaanUjian::where('status_pengerjaan', 'sedang_dikerjakan')
                                       ->whereNotNull('waktu_mulai')
                                       ->with('ujian')
                                       ->get();

        if ($activeAttempts->isEmpty()) {
            Log::info('[AutoSubmitScheduler] Tidak ada pengerjaan ujian aktif yang perlu dicek.');
            $this->info('Tidak ada pengerjaan ujian aktif yang perlu dicek.');
            return Command::SUCCESS;
        }

        $submittedCount = 0;
        foreach ($activeAttempts as $attempt) {
            if (!$attempt->ujian) {
                Log::warning("[AutoSubmitScheduler] Pengerjaan ID {$attempt->id} tidak memiliki data ujian terkait. Dilewati.");
                continue;
            }

            $waktuMulai = Carbon::parse($attempt->waktu_mulai);
            $durasiUjianDetik = $attempt->ujian->durasi * 60;
            $waktuSeharusnyaSelesai = $waktuMulai->copy()->addSeconds($durasiUjianDetik);

            if ($now->gte($waktuSeharusnyaSelesai)) {
                Log::info("[AutoSubmitScheduler] Ujian ID {$attempt->ujian_id} (Pengerjaan ID {$attempt->id}) oleh User ID {$attempt->user_id} telah melewati batas waktu.");
                DB::beginTransaction();
                try {
                    $attempt->waktu_selesai = $waktuSeharusnyaSelesai;
                    $attempt->waktu_dihabiskan_detik = $durasiUjianDetik;
                    $attempt->status_pengerjaan = 'selesai_waktu_habis'; // Status baru
                    
                    // Penilaian otomatis jika diperlukan dan jawaban sudah tersimpan periodik
                    // Untuk saat ini, kita asumsikan jawaban sudah ada via submit normal atau akan dinilai manual jika esai
                    // Jika ingin hitung skor di sini, panggil fungsi calculateScore
                    $this->calculateScoreIfApplicable($attempt);

                    $attempt->save();
                    DB::commit();

                    // Hapus sesi terkait jika masih ada (walaupun seharusnya user sudah tidak aktif)
                    $sessionKeySoal = 'ujian_attempt_' . $attempt->id . '_soal';
                    session()->forget($sessionKeySoal);
                    // session()->forget('pengerjaan_ujian_aktif_id'); // Ini lebih relevan dengan sesi user aktif

                    $submittedCount++;
                    Log::info("[AutoSubmitScheduler] Pengerjaan ID {$attempt->id} berhasil di-submit otomatis karena waktu habis.");
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error("[AutoSubmitScheduler] Gagal auto-submit Pengerjaan ID {$attempt->id}: " . $e->getMessage());
                }
            }
        }

        Log::info("[AutoSubmitScheduler] Selesai. Jumlah ujian yang di-submit otomatis: {$submittedCount}");
        $this->info("Proses auto-submit selesai. Jumlah ujian yang di-submit: {$submittedCount}");
        return Command::SUCCESS;
    }

    protected function calculateScoreIfApplicable(PengerjaanUjian $attempt)
    {
        // Fungsi ini bisa diisi dengan logika penilaian yang sama seperti di PengerjaanUjianController
        // jika Anda ingin skor dihitung saat auto-submit.
        // Pastikan untuk mengambil jawaban dari $attempt->detailJawaban.
        // Contoh sederhana:
        if($attempt->skor_total === null) { // Hanya hitung jika belum ada skor
            $totalSkor = 0;
            $jawabanDetails = $attempt->detailJawaban()->with('soal')->get(); // Eager load soal
            $soalUjianRefs = $attempt->ujian->soal()->get()->keyBy('id');


            foreach ($jawabanDetails as $detail) {
                $soalRef = $detail->soal; // $soalUjianRefs->get($detail->soal_id);
                if (!$soalRef) continue;

                $isBenar = null;
                $skorPerSoal = 0;

                if (($soalRef->tipe_soal === 'pilihan_ganda' || $soalRef->tipe_soal === 'benar_salah') && $soalRef->kunci_jawaban) {
                     $kunciObj = is_array($soalRef->kunci_jawaban) ? $soalRef->kunci_jawaban : json_decode($soalRef->kunci_jawaban, true);
                     $kunciNilai = null;
                     if(is_array($kunciObj)){
                         $kunciNilai = $kunciObj['id'] ?? ($kunciObj[0]['id'] ?? ($kunciObj[0] ?? null));
                         if($kunciNilai === null && isset($kunciObj['teks'])) $kunciNilai = $kunciObj['teks'];
                     } else {
                         $kunciNilai = $kunciObj;
                     }

                    if (isset($detail->jawaban_user) && $detail->jawaban_user !== '' && strval($detail->jawaban_user) === strval($kunciNilai)) {
                        $isBenar = true;
                        $pivotData = DB::table('ujian_soal')->where('ujian_id', $attempt->ujian_id)->where('soal_id', $soalRef->id)->first();
                        $bobotSoal = $pivotData->bobot_nilai_soal ?? 1;
                        $skorPerSoal = $bobotSoal;
                    } else if (isset($detail->jawaban_user) && $detail->jawaban_user !== '') {
                        $isBenar = false;
                    }
                }
                $totalSkor += $skorPerSoal;
                // Update is_benar dan skor_per_soal di detail jawaban jika belum
                if ($detail->is_benar === null && $isBenar !== null) {
                    $detail->is_benar = $isBenar;
                    $detail->skor_per_soal = $skorPerSoal;
                    $detail->save();
                }
            }
            $attempt->skor_total = $totalSkor;
            Log::info("[AutoSubmitScheduler] Skor dihitung untuk Pengerjaan ID {$attempt->id}: {$totalSkor}");
        }
    }
}