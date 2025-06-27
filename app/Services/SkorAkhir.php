<?php

namespace App\Services;

use App\Models\PengerjaanUjian;

class SkorAkhir
{
    public function calculate(PengerjaanUjian $pengerjaan): float
    {
        $pengerjaan->load(['ujian', 'detailJawaban.soal']);

        $persentaseEsai = $pengerjaan->ujian->sertakan_esai ? ($pengerjaan->ujian->persentase_esai / 100) : 0;
        $persentaseNonEsai = 1 - $persentaseEsai;

        $skorMentahNonEsai = 0;
        $bobotMaxNonEsai = 0;
        $skorMentahEsai = 0;
        $bobotMaxEsai = 0;

        // Iterasi melalui semua soal yang seharusnya ada di ujian
        foreach ($pengerjaan->ujian->soal as $soalUjian) {
            $bobotSoal = $soalUjian->pivot->bobot_nilai_soal ?? 1;
            $jawabanPeserta = $pengerjaan->detailJawaban->firstWhere('soal_id', $soalUjian->id);

            if ($soalUjian->tipe_soal === 'esai') {
                $bobotMaxEsai += $bobotSoal;
                if ($jawabanPeserta) {
                    // Skor esai diambil dari skor manual yang diinput dosen
                    $skorMentahEsai += $jawabanPeserta->skor_per_soal;
                }
            } else {
                $bobotMaxNonEsai += $bobotSoal;
                if ($jawabanPeserta && $jawabanPeserta->is_benar) {
                    // Skor non-esai diambil jika jawaban benar
                    $skorMentahNonEsai += $bobotSoal;
                }
            }
        }

        // Hitung skor normalisasi (0-100) untuk masing-masing tipe
        $skorNormNonEsai = ($bobotMaxNonEsai > 0) ? ($skorMentahNonEsai / $bobotMaxNonEsai) * 100 : 0;
        $skorNormEsai = ($bobotMaxEsai > 0) ? ($skorMentahEsai / $bobotMaxEsai) * 100 : 0;

        // Hitung skor akhir berdasarkan bobot persentase
        $skorAkhir = ($skorNormNonEsai * $persentaseNonEsai) + ($skorNormEsai * $persentaseEsai);

        return round($skorAkhir, 2);
    }
}