<?php

namespace App\Services\UjianProses;

use App\Models\Soal; // Untuk type hinting jika diperlukan
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SoalFormatterService
{
    /**
     * Memformat koleksi Soal Eloquent menjadi array untuk dikirim ke Express.js.
     *
     * @param Collection $laravelSoalCollection Koleksi model App\Models\Soal.
     * @return array
     */
    public function formatForExpress(Collection $laravelSoalCollection): array
    {
        Log::debug('[SoalFormatterService] Memulai format soal untuk Express. Jumlah soal: ' . $laravelSoalCollection->count());
        return $laravelSoalCollection->map(function (Soal $itemSoalLaravel) {
            $tipeSoalAsli = $itemSoalLaravel->tipe_soal;
            $opsiJawabanDariModel = $itemSoalLaravel->opsi_jawaban;
            $pasanganDariModel = $itemSoalLaravel->pasangan;

            // Decode manual opsi_jawaban jika masih string (workaround jika casting model tidak bekerja)
            $opsiJawabanArray = null;
            if (is_string($opsiJawabanDariModel)) {
                $decoded = json_decode($opsiJawabanDariModel, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $opsiJawabanArray = $decoded;
                } else {
                    Log::warning('[SoalFormatterService] Gagal decode opsi_jawaban dari string untuk Soal ID: ' . $itemSoalLaravel->id, ['string_asli' => $opsiJawabanDariModel]);
                    $opsiJawabanArray = [];
                }
            } elseif (is_array($opsiJawabanDariModel)) {
                $opsiJawabanArray = $opsiJawabanDariModel;
            } else {
                $opsiJawabanArray = [];
            }

            $pilihanUntukExpress = null;
            if ($tipeSoalAsli === 'pilihan_ganda' || $tipeSoalAsli === 'benar_salah') {
                $pilihanUntukExpress = !empty($opsiJawabanArray) ? $opsiJawabanArray : [];
            }

            // Decode manual pasangan jika masih string
            $pasanganArray = null;
            if (is_string($pasanganDariModel)) {
                $decodedPasangan = json_decode($pasanganDariModel, true);
                 if (json_last_error() === JSON_ERROR_NONE && is_array($decodedPasangan)) {
                    $pasanganArray = $decodedPasangan;
                } else { $pasanganArray = [];}
            } elseif (is_array($pasanganDariModel)) {
                $pasanganArray = $pasanganDariModel;
            } else { $pasanganArray = []; }

            $pasanganUntukExpress = null;
            if ($tipeSoalAsli === 'menjodohkan') {
                $pasanganUntukExpress = !empty($pasanganArray) ? $pasanganArray : [];
            }
            
            // Mapping tipe soal jika ada perbedaan (misal 'esai' di Laravel vs 'uraian' di Express)
            $tipeSoalUntukExpress = ($tipeSoalAsli === 'esai') ? 'uraian' : $tipeSoalAsli;

            Log::debug('[SoalFormatterService] Memformat Soal ID: ' . $itemSoalLaravel->id . ' untuk Express', [
                'pilihan_final_untuk_express' => $pilihanUntukExpress
            ]);

            return [
                'id' => $itemSoalLaravel->id,
                'pertanyaan' => $itemSoalLaravel->pertanyaan,
                'tipe' => $tipeSoalUntukExpress,
                'level_soal' => $itemSoalLaravel->level_kesulitan, // Asumsi nama field di Express adalah 'level_soal'
                'pilihan' => $pilihanUntukExpress,
                'pasangan' => $pasanganUntukExpress,
                'jawaban' => $itemSoalLaravel->kunci_jawaban, // Untuk internal Express, jangan kirim ke client
            ];
        })->values()->all();
    }

    /**
     * Memformat daftar soal yang sudah diacak dari Express menjadi format untuk frontend.
     *
     * @param array $shuffledSoalListFromExpress
     * @return array
     */
    public function formatForFrontend(array $shuffledSoalListFromExpress): array
    {
        Log::debug('[SoalFormatterService] Memulai format soal dari Express untuk Frontend. Jumlah soal: ' . count($shuffledSoalListFromExpress));
        return array_map(function ($itemSoalExpress, $index) {
            // Mapping tipe soal kembali jika ada perbedaan
            $tipeSoalUntukLaravel = ($itemSoalExpress['tipe'] === 'uraian') ? 'esai' : $itemSoalExpress['tipe'];
            
            $opsiFinal = (isset($itemSoalExpress['pilihan']) && is_array($itemSoalExpress['pilihan'])) ? $itemSoalExpress['pilihan'] : null;
            $pasanganFinal = (isset($itemSoalExpress['pasangan']) && is_array($itemSoalExpress['pasangan'])) ? $itemSoalExpress['pasangan'] : null;

            Log::debug('[SoalFormatterService] Memformat Soal ID Express: ' . ($itemSoalExpress['id'] ?? 'N/A') . ' untuk Frontend', [
                'opsi_diterima_dari_express' => $itemSoalExpress['pilihan'] ?? 'TIDAK ADA FIELD PILIHAN',
                'opsi_final_untuk_frontend' => $opsiFinal
            ]);

            return [
                'id' => $itemSoalExpress['id'],
                'nomor' => $index + 1,
                'tipe' => $tipeSoalUntukLaravel,
                'pertanyaan' => $itemSoalExpress['pertanyaan'],
                'opsi' => $opsiFinal,
                'pasangan' => $pasanganFinal,
                'jawabanUser' => null,
                'raguRagu' => false,
            ];
        }, $shuffledSoalListFromExpress, array_keys($shuffledSoalListFromExpress));
    }
}