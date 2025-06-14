<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ujian;
use App\Models\PengerjaanUjian;
use App\Models\JawabanPesertaDetail;
use App\Models\OpsiJawaban;
use App\Models\Soal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PengerjaanUjianController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'ujianId' => 'required|integer|exists:ujian,id',
            'pengerjaanId' => 'sometimes|nullable|integer|exists:pengerjaan_ujian,id',
            'jawaban' => 'required|array',
            'jawaban.*' => 'nullable',
            'statusRaguRagu' => 'required|array',
            'statusRaguRagu.*' => 'boolean',
        ]);

        $user = Auth::user();
        $ujianId = $request->input('ujianId');
        $pengerjaanIdDariRequest = $request->input('pengerjaanId');
        $jawabanUserMap = $request->input('jawaban');
        $statusRaguRaguMap = $request->input('statusRaguRagu');

        $pengerjaan = null;
        if ($pengerjaanIdDariRequest) {
            $pengerjaan = PengerjaanUjian::where('id', $pengerjaanIdDariRequest)
                            ->where('user_id', $user->id)
                            ->where('ujian_id', $ujianId)
                            ->first();
        }
        
        if (!$pengerjaan) {
             $pengerjaan = PengerjaanUjian::where('ujian_id', $ujianId)
                            ->where('user_id', $user->id)
                            ->where('status_pengerjaan', 'sedang_dikerjakan')
                            ->orderBy('created_at', 'desc')
                            ->first();
        }

        if (!$pengerjaan) {
            Log::error("Submit Ujian GAGAL: PengerjaanUjian tidak ditemukan. Ujian ID {$ujianId}, User ID {$user->id}.", $request->all());
            return back()->withErrors(['submit_error' => 'Sesi pengerjaan ujian tidak valid atau tidak ditemukan.']);
        }
        
        if ($pengerjaan->status_pengerjaan !== 'sedang_dikerjakan') {
            Log::warning("Submit Ujian DITOLAK: PengerjaanUjian ID {$pengerjaan->id} statusnya bukan 'sedang_dikerjakan' (status: {$pengerjaan->status_pengerjaan}). Mungkin sudah disubmit.", $request->all());
            return redirect()->route('ujian.hasil.detail', ['id_attempt' => $pengerjaan->id])
                             ->with('info_message', 'Ujian ini sudah pernah dikumpulkan atau statusnya tidak valid untuk submit.');
        }

        DB::beginTransaction();
        try {
            $waktuMulaiCarbon = Carbon::parse($pengerjaan->waktu_mulai);
            $waktuSelesai = now();
            $waktuDihabiskanDetik = $waktuSelesai->diffInSeconds($waktuMulaiCarbon);
            
            $durasiUjianDetik = $pengerjaan->ujian->durasi * 60;
            if($waktuDihabiskanDetik > ($durasiUjianDetik + 120) ){ // Toleransi 2 menit untuk keterlambatan jaringan/proses
                Log::warning("Submit Ujian: Pengerjaan ID {$pengerjaan->id} disubmit terlambat. Waktu dihabiskan: {$waktuDihabiskanDetik} vs Durasi: {$durasiUjianDetik}. Waktu selesai disesuaikan.");
                $waktuDihabiskanDetik = $durasiUjianDetik; // Cap waktu dihabiskan ke durasi ujian
                $waktuSelesai = $waktuMulaiCarbon->copy()->addSeconds($durasiUjianDetik); // Waktu selesai juga disesuaikan
                $pengerjaan->status_pengerjaan = 'selesai_waktu_habis'; // Tandai waktu habis jika disubmit sangat terlambat
            } else {
                $pengerjaan->status_pengerjaan = 'selesai';
            }

            $pengerjaan->waktu_selesai = $waktuSelesai;
            $pengerjaan->waktu_dihabiskan_detik = $waktuDihabiskanDetik;
            
            $totalSkor = 0;
            $adaSoalEsai = false;
            $soalUjianRefs = $pengerjaan->ujian->soal()->withPivot('bobot_nilai_soal')->get()->keyBy('id');

            // 1. Ambil semua Kunci Jawaban yang benar untuk soal-soal ini dalam satu query
            $kunciJawabanMap = OpsiJawaban::whereIn('soal_id', $soalUjianRefs->pluck('id'))
                                           ->where('is_kunci_jawaban', true)
                                           ->pluck('id', 'soal_id'); // Hasil: [soal_id => id_opsi_jawaban_benar]

            foreach ($jawabanUserMap as $soalId => $jawabanDiterima) {
                $soalRef = $soalUjianRefs->get((int)$soalId);
                if (!$soalRef) continue;

                $isBenar = null;
                $skorPerSoal = 0;

                // Hanya proses jika ada jawaban
                if (isset($jawabanDiterima) && $jawabanDiterima !== '') {
                    if ($soalRef->tipe_soal === 'pilihan_ganda' || $soalRef->tipe_soal === 'benar_salah') {
                        // 2. Bandingkan jawaban user dengan kunci jawaban dari map
                        $kunciJawabanId = $kunciJawabanMap->get((int)$soalId);

                        if ($kunciJawabanId !== null && intval($jawabanDiterima) === $kunciJawabanId) {
                            $isBenar = true;
                            $skorPerSoal = $soalRef->pivot->bobot_nilai_soal ?? 10;
                        } else {
                            $isBenar = false;
                        }
                    } elseif ($soalRef->tipe_soal === 'esai') {
                        $adaSoalEsai = true;
                        // Untuk esai, tidak ada penilaian otomatis
                        $isBenar = null;
                        $skorPerSoal = 0;
                    }
                }
                
                $totalSkor += $skorPerSoal;

                JawabanPesertaDetail::updateOrCreate(
                    ['pengerjaan_ujian_id' => $pengerjaan->id, 'soal_id' => (int)$soalId],
                    [
                        'jawaban_user' => json_encode($jawabanDiterima),
                        'is_benar' => $isBenar,
                        'skor_per_soal' => $skorPerSoal,
                        'is_ragu_ragu' => $statusRaguRaguMap[$soalId] ?? false,
                    ]
                );
            }

            // Tentukan status akhir dan skor total
            if ($adaSoalEsai) {
                $pengerjaan->status_pengerjaan = 'menunggu_penilaian';
                $pengerjaan->skor_total = $totalSkor; // Skor sementara
            } else {
                $pengerjaan->status_pengerjaan = 'selesai';
                $pengerjaan->skor_total = $totalSkor; // Skor final
            }

            $pengerjaan->save();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal submit ujian untuk Pengerjaan ID: " . ($pengerjaan->id ?? 'Tidak Diketahui') . " Ujian ID: {$ujianId}, User: {$user->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString() // Lebih detail untuk debugging
            ]);
            return back()->withErrors(['submit_error' => 'Gagal menyimpan jawaban ujian. Silakan coba lagi. (' . $e->getMessage() . ')']);
        }
    }
}