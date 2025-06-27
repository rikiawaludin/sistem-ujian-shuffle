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
        $pengerjaan = PengerjaanUjian::with('ujian.soal.pivot', 'ujian.soal.opsiJawaban')->find($this->pengerjaanId);

        if (!$pengerjaan) {
            Log::error("[Job:ProsesHasilUjian] PengerjaanUjian tidak ditemukan dengan ID {$this->pengerjaanId}.");
            return;
        }

        if ($pengerjaan->status_pengerjaan !== 'diproses') {
            Log::warning("[Job:ProsesHasilUjian] Pengerjaan ID {$this->pengerjaanId} statusnya bukan 'diproses'. Job diabaikan.");
            return;
        }

        Log::info("[Job:ProsesHasilUjian] Memulai proses penilaian untuk Pengerjaan ID: {$this->pengerjaanId}.");

        DB::beginTransaction();
        try {
            $adaSoalEsai = false;
            $soalUjianRefs = $pengerjaan->ujian->soal->keyBy('id');

            foreach ($this->jawabanUserMap as $soalId => $jawabanDiterima) {
                $soalRef = $soalUjianRefs->get((int)$soalId);
                if (!$soalRef) continue;

                $isBenar = null;
                $skorPerSoal = 0;
                $tipeSoal = $soalRef->tipe_soal;

                // --- Logika Penilaian Baru ---

                if (in_array($tipeSoal, ['pilihan_ganda', 'benar_salah'])) {
                    $kunciJawaban = $soalRef->opsiJawaban->firstWhere('is_kunci_jawaban', true);
                    if ($kunciJawaban && (string)$jawabanDiterima === (string)$kunciJawaban->id) {
                        $isBenar = true;
                        $skorPerSoal = $soalRef->pivot->bobot_nilai_soal ?? 1;
                    } else {
                        $isBenar = false;
                    }

                } elseif ($tipeSoal === 'pilihan_jawaban_ganda') {
                    $kunciJawabanBenar = $soalRef->opsiJawaban->where('is_kunci_jawaban', true);
                    $jumlahKunciBenar = $kunciJawabanBenar->count();
                    if ($jumlahKunciBenar === 0) continue;

                    $bobotPerOpsi = ($soalRef->pivot->bobot_nilai_soal ?? 1) / $jumlahKunciBenar;
                    $jawabanUserArray = is_array($jawabanDiterima) ? $jawabanDiterima : (is_string($jawabanDiterima) ? explode(',', $jawabanDiterima) : []);

                    $skorAkumulasi = 0;
                    foreach ($kunciJawabanBenar as $kunci) {
                        if (in_array((string)$kunci->id, $jawabanUserArray)) {
                            $skorAkumulasi += $bobotPerOpsi;
                        }
                    }
                    $skorPerSoal = $skorAkumulasi;
                    $isBenar = ($skorPerSoal >= ($soalRef->pivot->bobot_nilai_soal ?? 1));

                } elseif ($tipeSoal === 'isian_singkat') {
                    $kunciJawabanTeks = $soalRef->opsiJawaban->where('is_kunci_jawaban', true)->pluck('teks_opsi')->all();
                    if (in_array(strtolower(trim($jawabanDiterima)), array_map('strtolower', $kunciJawabanTeks))) {
                        $isBenar = true;
                        $skorPerSoal = $soalRef->pivot->bobot_nilai_soal ?? 1;
                    } else {
                        $isBenar = false;
                    }

                } elseif ($tipeSoal === 'menjodohkan') {
                    $opsiSoal = $soalRef->opsiJawaban;
                    $jumlahPasanganBenar = $opsiSoal->count();
                    if ($jumlahPasanganBenar === 0) continue;

                    $bobotPerPasangan = ($soalRef->pivot->bobot_nilai_soal ?? 1) / $jumlahPasanganBenar;
                    $jawabanUserPairs = is_string($jawabanDiterima) ? explode(',', $jawabanDiterima) : [];
                    
                    $skorAkumulasi = 0;
                    // Iterasi setiap pasangan jawaban dari pengguna
                    foreach ($jawabanUserPairs as $pair) {
                        $ids = explode(':', $pair);
                        // Pasangan dianggap benar jika id_kiri dan id_kanan sama
                        if (count($ids) === 2 && (int)$ids[0] === (int)$ids[1]) {
                            $skorAkumulasi += $bobotPerPasangan;
                        }
                    }
                    $skorPerSoal = $skorAkumulasi;
                    $isBenar = ($skorPerSoal >= ($soalRef->pivot->bobot_nilai_soal ?? 1));

                } elseif ($tipeSoal === 'esai') {
                    $adaSoalEsai = true;
                    $isBenar = null; // Menunggu penilaian manual
                }
                
                // Simpan detail jawaban
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
                $pengerjaan->skor_total = null;
            } else {
                $pengerjaan->status_pengerjaan = 'selesai';
                $calculator = new \App\Services\SkorAkhir();
                $pengerjaan->skor_total = $calculator->calculate($pengerjaan);
            }

            $pengerjaan->save();
            DB::commit();
            Log::info("[Job:ProsesHasilUjian] Penilaian untuk Pengerjaan ID: {$this->pengerjaanId} berhasil (model parsial).");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("[Job:ProsesHasilUjian] Gagal memproses penilaian untuk Pengerjaan ID: {$this->pengerjaanId}. Error: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString()
            ]);
            
            if (isset($pengerjaan)) {
                $pengerjaan->status_pengerjaan = 'gagal_diproses';
                $pengerjaan->save();
            }
        }
    }
}