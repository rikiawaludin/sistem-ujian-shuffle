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
            $soalUjianRefs = $pengerjaan->ujian->soal()->withPivot('bobot_nilai_soal')->get()->keyBy('id');

            foreach ($jawabanUserMap as $soalId => $jawabanDiterima) {
                $soalRef = $soalUjianRefs->get((int)$soalId);
                if (!$soalRef) {
                    Log::warning("Soal ID {$soalId} tidak ditemukan saat submit Pengerjaan ID {$pengerjaan->id}. Skipping.");
                    continue;
                }

                $isBenar = null;
                $skorPerSoal = 0;
                $jawabanUntukDisimpan = null;

                if (isset($jawabanDiterima) && $jawabanDiterima !== '') { // Hanya proses jika ada jawaban
                    if ($soalRef->tipe_soal === 'pilihan_ganda' || $soalRef->tipe_soal === 'benar_salah') {
                        $jawabanUntukDisimpan = json_encode(strval($jawabanDiterima)); // Simpan sebagai string JSON tunggal
                        
                        if ($soalRef->kunci_jawaban) {
                            $kunciObj = is_array($soalRef->kunci_jawaban) ? $soalRef->kunci_jawaban : json_decode($soalRef->kunci_jawaban, true);
                            $kunciNilai = null;
                            if(is_array($kunciObj) && count($kunciObj) > 0){ // Jika kunci adalah array ["A"] atau [{"id":"A"}]
                                $kunciPertama = $kunciObj[0];
                                $kunciNilai = is_object($kunciPertama) ? ($kunciPertama->id ?? $kunciPertama->teks) : $kunciPertama;
                            } elseif (!is_array($kunciObj)) { // Jika kunci adalah string atau objek tunggal
                               $kunciNilai = is_object($kunciObj) ? ($kunciObj->id ?? $kunciObj->teks) : $kunciObj;
                            }

                            if (strval($jawabanDiterima) === strval($kunciNilai)) {
                                $isBenar = true;
                                $skorPerSoal = $soalRef->pivot->bobot_nilai_soal ?? 10; // Default bobot jika tidak ada
                            } else {
                                $isBenar = false;
                            }
                        }
                    } elseif ($soalRef->tipe_soal === 'esai') {
                        $jawabanUntukDisimpan = json_encode($jawabanDiterima); // Esai juga simpan sebagai string JSON
                    } else if (is_array($jawabanDiterima)) {
                        $jawabanUntukDisimpan = json_encode($jawabanDiterima);
                    } else {
                        $jawabanUntukDisimpan = json_encode(strval($jawabanDiterima));
                    }
                } else {
                    // Jika jawaban kosong atau null, simpan null (atau string JSON "null")
                    $jawabanUntukDisimpan = null; // Akan disimpan sebagai NULL di DB jika kolom nullable
                                                // atau json_encode(null) akan jadi "null"
                }
                $totalSkor += $skorPerSoal;

                JawabanPesertaDetail::updateOrCreate(
                    ['pengerjaan_ujian_id' => $pengerjaan->id, 'soal_id' => (int)$soalId],
                    [
                        'jawaban_user' => $jawabanUntukDisimpan,
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
            // Hapus juga session pengerjaan_ujian_aktif_id agar tidak terpakai lagi
            if(session('pengerjaan_ujian_aktif_id') == $pengerjaan->id) {
                session()->forget('pengerjaan_ujian_aktif_id');
            }


            Log::info("Ujian ID: {$ujianId} berhasil dikumpulkan oleh User ID: {$user->id}. Pengerjaan ID: {$pengerjaan->id}, Skor: {$totalSkor}");
            return redirect()->route('ujian.selesai.konfirmasi', ['id_ujian' => $ujianId])
                             ->with('success_message', 'Ujian berhasil dikumpulkan!');

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