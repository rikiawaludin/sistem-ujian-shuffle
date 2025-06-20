<?php

namespace App\Jobs;

use App\Models\JawabanPesertaDetail;
use App\Models\OpsiJawaban;
use App\Models\PengerjaanUjian;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProsesHasilUjian implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Properti untuk menyimpan data yang dibutuhkan oleh Job
    protected int $pengerjaanId;
    protected array $jawabanUserMap;
    protected array $statusRaguRaguMap;

    /**
     * Membuat instance job baru.
     * Data yang dibutuhkan untuk proses penilaian dikirim melalui constructor.
     */
    public function __construct(int $pengerjaanId, array $jawabanUserMap, array $statusRaguRaguMap)
    {
        $this->pengerjaanId = $pengerjaanId;
        $this->jawabanUserMap = $jawabanUserMap;
        $this->statusRaguRaguMap = $statusRaguRaguMap;
    }

    /**
     * Mengeksekusi job.
     * Semua logika penilaian yang berat dijalankan di sini, di latar belakang.
     */
    public function handle(): void
    {
        // Menggunakan pengerjaanId yang dikirim dari controller
        $pengerjaan = PengerjaanUjian::with('ujian.soal')->find($this->pengerjaanId);

        if (!$pengerjaan) {
            Log::error("[Job:ProsesHasilUjian] PengerjaanUjian tidak ditemukan dengan ID {$this->pengerjaanId}. Job dibatalkan.");
            return;
        }

        // Pengecekan penting: Jika statusnya bukan 'diproses', berarti ada proses lain yang sudah menangani. Hentikan.
        if ($pengerjaan->status_pengerjaan !== 'diproses') {
            Log::warning("[Job:ProsesHasilUjian] Pengerjaan ID {$this->pengerjaanId} statusnya bukan 'diproses'. Job diabaikan untuk mencegah eksekusi ganda.");
            return;
        }
        
        Log::info("[Job:ProsesHasilUjian] Memulai proses penilaian untuk Pengerjaan ID: {$this->pengerjaanId}.");

        DB::beginTransaction();
        try {
            $totalSkor = 0;
            $adaSoalEsai = false;
            $soalUjianRefs = $pengerjaan->ujian->soal()->withPivot('bobot_nilai_soal')->get()->keyBy('id');

            // Ambil Kunci Jawaban dalam satu query yang bisa menangani semua tipe soal
            $kunciJawabanMap = OpsiJawaban::whereIn('soal_id', $soalUjianRefs->pluck('id'))
                                           ->where('is_kunci_jawaban', true)
                                           ->get()
                                           ->groupBy('soal_id'); 

            foreach ($this->jawabanUserMap as $soalId => $jawabanDiterima) {
                $soalRef = $soalUjianRefs->get((int)$soalId);
                if (!$soalRef) continue;

                $isBenar = null;
                $skorPerSoal = 0;

                if (isset($jawabanDiterima) && $jawabanDiterima !== '' && $jawabanDiterima !== []) {
                    $tipeSoal = $soalRef->tipe_soal;

                    if (in_array($tipeSoal, ['pilihan_ganda', 'benar_salah'])) {
                        $kunciJawabanCollection = $kunciJawabanMap->get((int)$soalId);
                        $kunciJawabanId = $kunciJawabanCollection ? $kunciJawabanCollection->first()->id : null;
                        $isBenar = ($kunciJawabanId !== null && (string)$jawabanDiterima === (string)$kunciJawabanId);

                    } elseif ($tipeSoal === 'pilihan_jawaban_ganda') {
                        $kunciJawabanIds = $kunciJawabanMap->get((int)$soalId, collect())
                                                       ->pluck('id')
                                                       ->map(fn($id) => (string)$id)
                                                       ->sort()->values()->all();
                        $kunciJawabanString = implode(',', $kunciJawabanIds);
                        $isBenar = ($kunciJawabanString === $jawabanDiterima);
                    
                    } elseif ($tipeSoal === 'isian_singkat') {
                        $kunciJawabanTeks = OpsiJawaban::where('soal_id', $soalId)->where('is_kunci_jawaban', true)->pluck('teks_opsi')->all();
                        $isBenar = in_array(strtolower(trim($jawabanDiterima)), array_map('strtolower', $kunciJawabanTeks));
                    
                    } elseif ($tipeSoal === 'menjodohkan') {
                        $jawabanUserPairs = explode(',', $jawabanDiterima);
                        $kunciJawabanIds = OpsiJawaban::where('soal_id', $soalId)->pluck('id')->all();
                        
                        if (count($jawabanUserPairs) !== count($kunciJawabanIds)) {
                            $isBenar = false;
                        } else {
                            $isBenar = true;
                            foreach ($jawabanUserPairs as $pair) {
                                $ids = explode(':', $pair);
                                if (count($ids) !== 2 || (int)$ids[0] !== (int)$ids[1]) {
                                    $isBenar = false;
                                    break;
                                }
                            }
                        }

                    } elseif ($tipeSoal === 'esai') {
                        $adaSoalEsai = true;
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
                        'jawaban_user' => is_array($jawabanDiterima) ? json_encode($jawabanDiterima) : $jawabanDiterima,
                        'is_benar' => $isBenar,
                        'skor_per_soal' => $skorPerSoal,
                        'is_ragu_ragu' => $this->statusRaguRaguMap[$soalId] ?? false,
                    ]
                );
            }

            // Tentukan status akhir dan skor total
            if ($adaSoalEsai) {
                $pengerjaan->status_pengerjaan = 'menunggu_penilaian';
            } else {
                $pengerjaan->status_pengerjaan = 'selesai';
            }
            $pengerjaan->skor_total = $totalSkor;

            $pengerjaan->save();
            DB::commit();
            Log::info("[Job:ProsesHasilUjian] Penilaian untuk Pengerjaan ID: {$this->pengerjaanId} berhasil.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("[Job:ProsesHasilUjian] Gagal memproses penilaian untuk Pengerjaan ID: {$this->pengerjaanId}. Error: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString()
            ]);
            
            // Update status agar admin tahu ada job yang gagal dan perlu diperiksa
            if (isset($pengerjaan)) {
                $pengerjaan->status_pengerjaan = 'gagal_diproses';
                $pengerjaan->save();
            }
        }
    }
}