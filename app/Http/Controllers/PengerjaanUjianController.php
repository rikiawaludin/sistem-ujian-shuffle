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
            'jawaban' => 'required|array',
            'jawaban.*' => 'nullable',
            'statusRaguRagu' => 'required|array',
            'statusRaguRagu.*' => 'boolean',
        ]);

        $user = Auth::user();
        $ujianId = $request->input('ujianId');
        $jawabanUserMap = $request->input('jawaban');
        $statusRaguRaguMap = $request->input('statusRaguRagu');

        $ujian = Ujian::find($ujianId);
        if (!$ujian) {
            // Ini seharusnya tidak terjadi jika validasi 'exists' bekerja,
            // tapi sebagai pengaman tambahan
            return back()->withErrors(['ujianId' => 'Ujian tidak ditemukan.']);
        }

        $sessionKeySoal = 'ujian_berlangsung_' . $ujianId . '_user_' . $user->id . '_soal';
        $sessionKeyWaktuMulai = 'ujian_berlangsung_' . $ujianId . '_user_' . $user->id . '_waktu_mulai';
        $sessionKeyAttemptId = 'ujian_berlangsung_' . $ujianId . '_user_' . $user->id . '_attempt_id';

        DB::beginTransaction();
        try {
            $waktuMulaiDariSesi = session($sessionKeyWaktuMulai);
            if (!$waktuMulaiDariSesi) {
                 Log::warning("Submit ujian tanpa waktu mulai di sesi. Ujian ID: {$ujianId}, User ID: {$user->id}. Menggunakan waktu saat ini sebagai fallback.");
                 $waktuMulaiDariSesi = now()->toIso8601String();
            }
            $waktuMulaiCarbon = Carbon::parse($waktuMulaiDariSesi);
            $waktuSelesai = now();
            $waktuDihabiskanDetik = $waktuSelesai->diffInSeconds($waktuMulaiCarbon);

            $pengerjaanUjianIdDariSesi = session($sessionKeyAttemptId);
            $pengerjaan = null;

            if ($pengerjaanUjianIdDariSesi) {
                $pengerjaan = PengerjaanUjian::find($pengerjaanUjianIdDariSesi);
            }

            if ($pengerjaan && $pengerjaan->user_id === $user->id && $pengerjaan->ujian_id === (int)$ujianId && $pengerjaan->status_pengerjaan === 'sedang_dikerjakan') {
                $pengerjaan->waktu_selesai = $waktuSelesai;
                $pengerjaan->waktu_dihabiskan_detik = $waktuDihabiskanDetik;
                $pengerjaan->status_pengerjaan = 'selesai';
                Log::info("Menyelesaikan pengerjaan ujian ID: {$pengerjaan->id} untuk Ujian ID: {$ujianId}");
            } else {
                Log::info("Membuat pengerjaan ujian baru saat submit untuk Ujian ID: {$ujianId}, karena tidak ada attempt 'sedang_dikerjakan' yang cocok di sesi.");
                $pengerjaan = PengerjaanUjian::create([
                    'ujian_id' => $ujianId,
                    'user_id' => $user->id,
                    'waktu_mulai' => $waktuMulaiCarbon,
                    'waktu_selesai' => $waktuSelesai,
                    'waktu_dihabiskan_detik' => $waktuDihabiskanDetik,
                    'status_pengerjaan' => 'selesai',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }

            $totalSkor = 0;
            $soalUjianRefs = $ujian->soal()->get()->keyBy('id');

            foreach ($jawabanUserMap as $soalId => $jawaban) {
                $soalRef = $soalUjianRefs->get((int)$soalId); // Pastikan $soalId adalah integer jika kunci $soalUjianRefs adalah integer
                if (!$soalRef) {
                    Log::warning("Soal ID {$soalId} tidak ditemukan dalam Ujian ID {$ujianId} saat submit (mungkin tidak termasuk dalam $ujian->soal()). Skipping.");
                    continue;
                }

                $isBenar = null;
                $skorPerSoal = 0;

                if (($soalRef->tipe_soal === 'pilihan_ganda' || $soalRef->tipe_soal === 'benar_salah') && $soalRef->kunci_jawaban) {
                    // $soalRef->kunci_jawaban adalah hasil casting dari model (seharusnya array/objek)
                    $kunciObj = $soalRef->kunci_jawaban;
                    // Asumsi kunci jawaban untuk PG/BS adalah ID opsi atau nilai tunggal
                    // Jika kunci_jawaban adalah {"id":"A", "teks":...}, ambil 'id' nya.
                    // Jika hanya string "A", maka $kunciNilai = "A".
                    $kunciNilai = is_array($kunciObj) ? ($kunciObj['id'] ?? $kunciObj[0]) : $kunciObj;
                    
                    if (isset($jawaban) && $jawaban !== '' && strval($jawaban) === strval($kunciNilai)) {
                        $isBenar = true;
                        // Ambil bobot dari tabel pivot ujian_soal
                        // Perlu memastikan relasi pivot dimuat atau mengambilnya secara manual
                        $pivotData = DB::table('ujian_soal')->where('ujian_id', $ujianId)->where('soal_id', $soalId)->first();
                        $bobotSoal = $pivotData->bobot_nilai_soal ?? 1;
                        $skorPerSoal = $bobotSoal;
                    } else if (isset($jawaban) && $jawaban !== '') {
                        $isBenar = false;
                    }
                }
                $totalSkor += $skorPerSoal;

                JawabanPesertaDetail::updateOrCreate(
                    [
                        'pengerjaan_ujian_id' => $pengerjaan->id,
                        'soal_id' => $soalId,
                    ],
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

            session()->forget($sessionKeySoal);
            session()->forget($sessionKeyWaktuMulai);
            session()->forget($sessionKeyAttemptId);

            Log::info("Ujian ID: {$ujianId} berhasil dikumpulkan oleh User ID: {$user->id}. Pengerjaan ID: {$pengerjaan->id}");
            
            // --- [PERUBAHAN DI SINI] ---
            // Redirect ke halaman konfirmasi dengan pesan sukses
            return redirect()->route('ujian.selesai.konfirmasi', ['id_ujian' => $ujianId])
                             ->with('success_message', 'Ujian berhasil dikumpulkan!');
            // Anda bisa menambahkan 'pengerjaan_id' => $pengerjaan->id ke parameter route jika halaman konfirmasi memerlukannya

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal submit ujian untuk Ujian ID: ' . $ujianId . ' oleh User ID: ' . $user->id, [
                'error' => $e->getMessage(),
                'trace_snippet' => substr($e->getTraceAsString(), 0, 1000)
            ]);
            // Jika terjadi error, redirect kembali ke halaman pengerjaan dengan pesan error
            return back()->withErrors(['submit_error' => 'Gagal menyimpan jawaban ujian. Silakan coba lagi. Error: ' . $e->getMessage()]);
        }
    }
}