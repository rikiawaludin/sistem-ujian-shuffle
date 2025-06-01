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
                                       ->with('ujian') // Eager load Ujian untuk durasi dan tanggal_selesai
                                       ->get();
        // ... (handling jika $activeAttempts kosong) ...

        $submittedCount = 0;
        foreach ($activeAttempts as $attempt) {
            if (!$attempt->ujian) { /* ... skip ... */ continue; }

            $waktuMulai = Carbon::parse($attempt->waktu_mulai);
            $durasiUjianDetik = $attempt->ujian->durasi * 60;
            
            // Batas waktu berdasarkan durasi individu
            $batasWaktuIndividu = $waktuMulai->copy()->addSeconds($durasiUjianDetik);
            
            // Batas waktu berdasarkan jadwal ujian global (tanggal_selesai ujian)
            $batasWaktuGlobal = null;
            if ($attempt->ujian->tanggal_selesai) {
                $batasWaktuGlobal = Carbon::parse($attempt->ujian->tanggal_selesai);
            }

            // Tentukan batas waktu aktual yang paling dulu tercapai
            $batasWaktuAktual = $batasWaktuIndividu;
            if ($batasWaktuGlobal && $batasWaktuGlobal->lt($batasWaktuIndividu)) {
                $batasWaktuAktual = $batasWaktuGlobal;
                Log::info("[AutoSubmitScheduler] Pengerjaan ID {$attempt->id} akan menggunakan batas waktu global ({$batasWaktuGlobal->toDateTimeString()}) karena lebih dulu dari batas individu ({$batasWaktuIndividu->toDateTimeString()}).");
            } else {
                 Log::info("[AutoSubmitScheduler] Pengerjaan ID {$attempt->id} akan menggunakan batas waktu individu ({$batasWaktuIndividu->toDateTimeString()}).");
            }


            if ($now->gte($batasWaktuAktual)) { // Jika waktu sekarang sudah melewati batas waktu aktual
                Log::info("[AutoSubmitScheduler] Pengerjaan ID {$attempt->id} telah melewati batas waktu aktual ({$batasWaktuAktual->toDateTimeString()}). Memproses submit otomatis.");
                DB::beginTransaction();
                try {
                    // Gunakan $batasWaktuAktual sebagai waktu selesai yang sebenarnya
                    $attempt->waktu_selesai = $batasWaktuAktual; 
                    // Waktu dihabiskan dihitung dari waktu mulai sampai batas waktu aktual
                    $attempt->waktu_dihabiskan_detik = $batasWaktuAktual->diffInSeconds($waktuMulai);
                    // Pastikan waktu dihabiskan tidak melebihi durasi asli ujian (jika batas global lebih dulu)
                    if ($attempt->waktu_dihabiskan_detik > $durasiUjianDetik) {
                        $attempt->waktu_dihabiskan_detik = $durasiUjianDetik;
                    }


                    $attempt->status_pengerjaan = 'selesai_waktu_habis';
                    
                    $this->calculateScoreIfApplicable($attempt); // Fungsi penilaian
                    $attempt->save();
                    DB::commit();

                    $sessionKeySoal = 'ujian_attempt_' . $attempt->id . '_soal';
                    session()->forget($sessionKeySoal);
                    if(session('pengerjaan_ujian_aktif_id') == $attempt->id) {
                        session()->forget('pengerjaan_ujian_aktif_id');
                    }

                    $submittedCount++;
                    Log::info("[AutoSubmitScheduler] Pengerjaan ID {$attempt->id} berhasil di-submit otomatis.");
                } catch (\Exception $e) { /* ... error handling ... */ }
            }
        }
        // ... (logging akhir) ...
        return Command::SUCCESS;
    }

    protected function calculateScoreIfApplicable(PengerjaanUjian $attempt) { /* ... (fungsi tetap sama) ... */ }
}