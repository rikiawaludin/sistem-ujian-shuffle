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

        // Menggunakan pengerjaanIdDariRequest sebagai prioritas utama untuk mencari pengerjaan
        $pengerjaan = PengerjaanUjian::where('id', $pengerjaanIdDariRequest)
                        ->where('user_id', $user->id)
                        ->where('ujian_id', $ujianId)
                        ->first();

        if (!$pengerjaan) {
            Log::error("Submit Ujian GAGAL: PengerjaanUjian tidak ditemukan dengan ID {$pengerjaanIdDariRequest}. Ujian ID {$ujianId}, User ID {$user->id}.", $request->all());
            return back()->withErrors(['submit_error' => 'Sesi pengerjaan ujian tidak valid atau tidak ditemukan.']);
        }
        
        if ($pengerjaan->status_pengerjaan !== 'sedang_dikerjakan') {
            Log::warning("Submit Ujian DITOLAK: PengerjaanUjian ID {$pengerjaan->id} statusnya bukan 'sedang_dikerjakan' (status: {$pengerjaan->status_pengerjaan}). Mungkin sudah disubmit.", $request->all());
            // Langsung arahkan ke halaman hasil jika sudah pernah disubmit
            return redirect()->route('ujian.hasil.detail', ['id_attempt' => $pengerjaan->id])
                             ->with('info_message', 'Ujian ini sudah pernah dikumpulkan.');
        }

        DB::beginTransaction();
        try {
            $waktuMulaiCarbon = Carbon::parse($pengerjaan->waktu_mulai);
            $waktuSelesai = now();
            $waktuDihabiskanDetik = $waktuSelesai->diffInSeconds($waktuMulaiCarbon);
            
            $durasiUjianDetik = $pengerjaan->ujian->durasi * 60;
            if ($waktuDihabiskanDetik > ($durasiUjianDetik + 120)) { // Toleransi 2 menit
                $pengerjaan->status_pengerjaan = 'selesai_waktu_habis';
                $waktuSelesai = $waktuMulaiCarbon->copy()->addSeconds($durasiUjianDetik);
                $pengerjaan->waktu_dihabiskan_detik = $durasiUjianDetik;
            } else {
                $pengerjaan->status_pengerjaan = 'selesai';
                $pengerjaan->waktu_dihabiskan_detik = $waktuDihabiskanDetik;
            }

            $pengerjaan->waktu_selesai = $waktuSelesai;
            
            $totalSkor = 0;
            $adaSoalEsai = false;
            $soalUjianRefs = $pengerjaan->ujian->soal()->withPivot('bobot_nilai_soal')->get()->keyBy('id');

            // Ambil Kunci Jawaban dalam satu query
            $kunciJawabanMap = OpsiJawaban::whereIn('soal_id', $soalUjianRefs->pluck('id'))
                                           ->where('is_kunci_jawaban', true)
                                           ->pluck('id', 'soal_id');

            foreach ($jawabanUserMap as $soalId => $jawabanDiterima) {
                $soalRef = $soalUjianRefs->get((int)$soalId);
                if (!$soalRef) continue;

                $isBenar = null;
                $skorPerSoal = 0;

                if (isset($jawabanDiterima) && $jawabanDiterima !== '' && $jawabanDiterima !== []) {
                    $tipeSoal = $soalRef->tipe_soal;

                    if (in_array($tipeSoal, ['pilihan_ganda', 'benar_salah'])) {
                        $kunciJawabanId = $kunciJawabanMap->get((int)$soalId);
                        if ($kunciJawabanId !== null && (string)$jawabanDiterima === (string)$kunciJawabanId) {
                            $isBenar = true;
                        } else {
                            $isBenar = false;
                        }
                    } elseif ($tipeSoal === 'pilihan_jawaban_ganda') {
                        $kunciJawabanIds = OpsiJawaban::where('soal_id', $soalId)->where('is_kunci_jawaban', true)->pluck('id')->map(fn($id) => (string)$id)->sort()->values()->all();
                        $jawabanUserArray = collect($jawabanDiterima)->map(fn($id) => (string)$id)->sort()->values()->all();
                        $isBenar = ($kunciJawabanIds === $jawabanUserArray);
                    } elseif ($tipeSoal === 'isian_singkat') {
                        $kunciJawabanTeks = OpsiJawaban::where('soal_id', $soalId)->pluck('teks_opsi')->all();
                        $isBenar = in_array(strtolower(trim($jawabanDiterima)), array_map('strtolower', $kunciJawabanTeks));
                    } elseif (in_array($tipeSoal, ['esai', 'menjodohkan'])) {
                        $adaSoalEsai = true; // Anggap 'menjodohkan' juga perlu dinilai manual untuk saat ini
                        $isBenar = null;
                    }

                    if ($isBenar === true) {
                        $skorPerSoal = $soalRef->pivot->bobot_nilai_soal ?? 10;
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
                // Jika ada esai, skornya belum final
                $pengerjaan->status_pengerjaan = 'menunggu_penilaian';
            }
            $pengerjaan->skor_total = $totalSkor;

            $pengerjaan->save();
            DB::commit();

            // =========================================================================
            // AWAL PERBAIKAN UTAMA
            // =========================================================================
            
            // 1. Hapus session soal setelah ujian berhasil disubmit
            $sessionKey = 'ujian_attempt_' . $pengerjaan->id;
            $request->session()->forget($sessionKey);
            Log::info("Session '{$sessionKey}' telah dihapus setelah submit.");

            // 2. Beri perintah redirect ke halaman konfirmasi selesai
            // Ini akan memberitahu Inertia untuk pindah halaman, bukan me-refresh halaman lama.
            return redirect()->route('ujian.selesai.konfirmasi', ['id_ujian' => $ujianId]);
            
            // =========================================================================
            // AKHIR PERBAIKAN UTAMA
            // =========================================================================

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal submit ujian untuk Pengerjaan ID: {$pengerjaan->id}. Error: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['submit_error' => 'Gagal menyimpan jawaban ujian. Silakan coba lagi.']);
        }
    }
}
