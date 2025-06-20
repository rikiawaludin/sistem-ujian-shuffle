<?php

namespace App\Http\Controllers;

use App\Jobs\ProsesHasilUjian;
use App\Models\PengerjaanUjian;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PengerjaanUjianController extends Controller
{
    /**
     * Menerima jawaban ujian dari mahasiswa, lalu memasukkan proses penilaian ke dalam antrean.
     */
    public function store(Request $request)
    {
        $request->validate([
            'ujianId' => 'required|integer|exists:ujian,id',
            'pengerjaanId' => 'required|integer|exists:pengerjaan_ujian,id',
            'jawaban' => 'required|array',
            'jawaban.*' => 'nullable',
            'statusRaguRagu' => 'required|array',
            'statusRaguRagu.*' => 'boolean',
        ]);

        $user = Auth::user();
        $ujianId = $request->input('ujianId');
        $pengerjaanId = $request->input('pengerjaanId');
        $jawabanUserMap = $request->input('jawaban');
        $statusRaguRaguMap = $request->input('statusRaguRagu');

        // Menggunakan firstOrFail untuk kode yang lebih bersih dan aman
        $pengerjaan = PengerjaanUjian::where('id', $pengerjaanId)
                        ->where('user_id', $user->id)
                        ->where('ujian_id', $ujianId)
                        ->firstOrFail();
        
        // Cek jika sudah pernah disubmit atau sedang diproses
        if ($pengerjaan->status_pengerjaan !== 'sedang_dikerjakan') {
            Log::warning("Submit DITOLAK: Pengerjaan ID {$pengerjaan->id} statusnya bukan 'sedang_dikerjakan'. Status saat ini: {$pengerjaan->status_pengerjaan}");
            return redirect()->route('ujian.hasil.detail', ['id_attempt' => $pengerjaan->id])
                             ->with('info_message', 'Ujian ini sudah pernah dikumpulkan atau sedang diproses.');
        }

        try {
            // 1. Update status pengerjaan SEKARANG juga untuk mencegah submit ganda
            $pengerjaan->status_pengerjaan = 'diproses'; // Status baru menandakan masuk antrean
            $pengerjaan->waktu_selesai = now();
            
            $waktuMulaiCarbon = Carbon::parse($pengerjaan->waktu_mulai);
            $waktuDihabiskanDetik = $pengerjaan->waktu_selesai->diffInSeconds($waktuMulaiCarbon);
            $pengerjaan->waktu_dihabiskan_detik = $waktuDihabiskanDetik;
            $pengerjaan->save();

            // 2. Lempar tugas penilaian ke antrean (queue)
            ProsesHasilUjian::dispatch($pengerjaan->id, $jawabanUserMap, $statusRaguRaguMap);
            Log::info("Submit Ujian DITERIMA. Pengerjaan ID {$pengerjaan->id} telah dimasukkan ke dalam antrean untuk diproses.");

            // 3. Hapus session soal dari cache
            $sessionKey = 'ujian_attempt_' . $pengerjaan->id;
            $request->session()->forget($sessionKey);
            Log::info("Session '{$sessionKey}' telah dihapus setelah submit.");

            // 4. Langsung redirect pengguna. Prosesnya terasa instan!
            return redirect()->route('ujian.selesai.konfirmasi', ['id_ujian' => $ujianId]);

        } catch (\Exception $e) {
            Log::error("Gagal menempatkan Pengerjaan ID {$pengerjaan->id} ke antrean. Error: {$e->getMessage()}");
            
            // Kembalikan status pengerjaan jika GAGAL masuk antrean agar bisa dicoba lagi oleh pengguna
            $pengerjaan->status_pengerjaan = 'sedang_dikerjakan';
            $pengerjaan->save();
            
            return back()->withErrors(['submit_error' => 'Gagal mengirim jawaban ujian. Sistem sedang sibuk, silakan coba lagi beberapa saat.']);
        }
    }
}