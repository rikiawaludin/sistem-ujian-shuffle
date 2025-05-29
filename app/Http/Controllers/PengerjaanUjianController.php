<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ujian;
use App\Models\PengerjaanUjian;
use App\Models\JawabanPesertaDetail;
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
            'pengerjaanId' => 'sometimes|integer|exists:pengerjaan_ujian,id', // ID pengerjaan dari frontend (opsional tapi bagus)
            'jawaban' => 'required|array',
            'jawaban.*' => 'nullable',
            'statusRaguRagu' => 'required|array',
            'statusRaguRagu.*' => 'boolean',
        ]);

        $user = Auth::user();
        $ujianId = $request->input('ujianId');
        $pengerjaanIdDariRequest = $request->input('pengerjaanId'); // Bisa dikirim dari frontend
        $jawabanUserMap = $request->input('jawaban');
        $statusRaguRaguMap = $request->input('statusRaguRagu');

        $pengerjaan = null;
        if ($pengerjaanIdDariRequest) {
            $pengerjaan = PengerjaanUjian::where('id', $pengerjaanIdDariRequest)
                            ->where('user_id', $user->id)
                            ->where('ujian_id', $ujianId)
                            ->first();
        }
        
        // Jika tidak ada pengerjaanId dari request, atau tidak ketemu, coba cari yang sedang dikerjakan
        if (!$pengerjaan) {
             $pengerjaan = PengerjaanUjian::where('ujian_id', $ujianId)
                            ->where('user_id', $user->id)
                            ->where('status_pengerjaan', 'sedang_dikerjakan')
                            ->orderBy('created_at', 'desc') // Ambil yang terbaru jika ada duplikat (seharusnya tidak)
                            ->first();
        }


        if (!$pengerjaan) {
            Log::error("Submit Ujian: PengerjaanUjian tidak ditemukan untuk Ujian ID {$ujianId}, User ID {$user->id}. Data request:", $request->all());
            return back()->withErrors(['submit_error' => 'Sesi pengerjaan ujian tidak ditemukan atau sudah selesai.']);
        }
        
        if ($pengerjaan->status_pengerjaan === 'selesai' || $pengerjaan->status_pengerjaan === 'selesai_waktu_habis') {
            Log::warning("Submit Ujian: PengerjaanUjian ID {$pengerjaan->id} sudah selesai. Percobaan submit ganda?");
            return redirect()->route('ujian.hasil.detail', ['id_attempt' => $pengerjaan->id])
                             ->with('info_message', 'Ujian ini sudah pernah dikumpulkan sebelumnya.');
        }


        DB::beginTransaction();
        try {
            $waktuMulaiCarbon = Carbon::parse($pengerjaan->waktu_mulai);
            $waktuSelesai = now();
            $waktuDihabiskanDetik = $waktuSelesai->diffInSeconds($waktuMulaiCarbon);
            
            // Pastikan waktu dihabiskan tidak melebihi durasi ujian + sedikit toleransi
            $durasiUjianDetik = $pengerjaan->ujian->durasi * 60;
            if($waktuDihabiskanDetik > ($durasiUjianDetik + 60) ){ // Toleransi 1 menit
                // Jika waktu habis signifikan, mungkin statusnya sudah diubah oleh scheduler
                // atau ini adalah submit yang sangat terlambat.
                // Kita tetap set waktunya sesuai durasi.
                Log::warning("Submit Ujian: Pengerjaan ID {$pengerjaan->id} disubmit jauh setelah waktu habis. Menggunakan durasi ujian.");
                $waktuDihabiskanDetik = $durasiUjianDetik;
                $waktuSelesai = $waktuMulaiCarbon->copy()->addSeconds($durasiUjianDetik);
            }


            $pengerjaan->waktu_selesai = $waktuSelesai;
            $pengerjaan->waktu_dihabiskan_detik = $waktuDihabiskanDetik;
            $pengerjaan->status_pengerjaan = 'selesai';

            $totalSkor = 0;
            $soalUjianRefs = $pengerjaan->ujian->soal()->get()->keyBy('id');

            // Hapus jawaban detail lama jika ada untuk pengerjaan ini (jika ingin menimpa)
            // JawabanPesertaDetail::where('pengerjaan_ujian_id', $pengerjaan->id)->delete();

            foreach ($jawabanUserMap as $soalId => $jawaban) {
                $soalRef = $soalUjianRefs->get((int)$soalId);
                if (!$soalRef) {
                    Log::warning("Soal ID {$soalId} tidak ditemukan saat submit Pengerjaan ID {$pengerjaan->id}. Skipping.");
                    continue;
                }

                $isBenar = null;
                $skorPerSoal = 0;

                if (($soalRef->tipe_soal === 'pilihan_ganda' || $soalRef->tipe_soal === 'benar_salah') && $soalRef->kunci_jawaban) {
                    $kunciObj = is_array($soalRef->kunci_jawaban) ? $soalRef->kunci_jawaban : json_decode($soalRef->kunci_jawaban, true);
                    $kunciNilai = null;
                    if(is_array($kunciObj)){
                        $kunciNilai = $kunciObj['id'] ?? ($kunciObj[0]['id'] ?? ($kunciObj[0] ?? null));
                        if($kunciNilai === null && isset($kunciObj['teks'])) $kunciNilai = $kunciObj['teks']; // fallback jika id tidak ada tapi teks ada (untuk format simpel ["Benar", "Salah"])
                    } else {
                        $kunciNilai = $kunciObj;
                    }
                    
                    if (isset($jawaban) && $jawaban !== '' && strval($jawaban) === strval($kunciNilai)) {
                        $isBenar = true;
                        $pivotData = DB::table('ujian_soal')->where('ujian_id', $ujianId)->where('soal_id', $soalId)->first();
                        $bobotSoal = $pivotData->bobot_nilai_soal ?? 1;
                        $skorPerSoal = $bobotSoal;
                    } else if (isset($jawaban) && $jawaban !== '') {
                        $isBenar = false;
                    }
                }
                $totalSkor += $skorPerSoal;

                JawabanPesertaDetail::updateOrCreate(
                    ['pengerjaan_ujian_id' => $pengerjaan->id, 'soal_id' => (int)$soalId],
                    [
                        'jawaban_user' => is_array($jawaban) ? json_encode($jawaban) : $jawaban,
                        'is_benar' => $isBenar,
                        'skor_per_soal' => $skorPerSoal,
                        'is_ragu_ragu' => $statusRaguRaguMap[$soalId] ?? false,
                    ]
                );
            }

            $pengerjaan->skor_total = $totalSkor;
            $pengerjaan->save();

            DB::commit();

            $sessionKeySoal = 'ujian_attempt_' . $pengerjaan->id . '_soal';
            session()->forget($sessionKeySoal);
            // session()->forget('pengerjaan_ujian_aktif_id'); // Dihapus oleh Api\UjianSoalController jika attempt baru dimulai

            Log::info("Ujian ID: {$ujianId} berhasil dikumpulkan oleh User ID: {$user->id}. Pengerjaan ID: {$pengerjaan->id}");
            return redirect()->route('ujian.selesai.konfirmasi', ['id_ujian' => $ujianId])
                             ->with('success_message', 'Ujian berhasil dikumpulkan!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal submit ujian untuk Ujian ID: ' . $ujianId . ' oleh User ID: ' . $user->id, [
                'error' => $e->getMessage(),
                'pengerjaan_id_on_error' => $pengerjaan->id ?? 'N/A',
                'trace_snippet' => substr($e->getTraceAsString(), 0, 1000)
            ]);
            return back()->withErrors(['submit_error' => 'Gagal menyimpan jawaban ujian. Silakan coba lagi. Detail: ' . $e->getMessage()]);
        }
    }
}