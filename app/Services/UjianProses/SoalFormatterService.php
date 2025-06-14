<?php

namespace App\Services\UjianProses;

use App\Models\Soal;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SoalFormatterService
{
    /**
     * Memformat koleksi Soal Eloquent menjadi array untuk dikirim ke Express.js.
     */
    public function formatForExpress(Collection $laravelSoalCollection): array
    {
        // Eager load relasi untuk menghindari N+1 query problem di dalam loop
        $laravelSoalCollection->load('opsiJawaban');

        return $laravelSoalCollection->map(function (Soal $itemSoal) {
            
            $tipeSoal = $itemSoal->tipe_soal;
            $pilihanUntukExpress = null;
            $kunciJawabanUntukExpress = null;

            if ($tipeSoal === 'pilihan_ganda' || $tipeSoal === 'benar_salah') {
                // Ambil opsi dari relasi
                $pilihanUntukExpress = $itemSoal->opsiJawaban->map(function ($opsi) {
                    return ['id' => $opsi->id, 'teks' => $opsi->teks_opsi];
                })->all();
                
                // Ambil kunci jawaban dari relasi
                $kunciJawabanObj = $itemSoal->opsiJawaban->firstWhere('is_kunci_jawaban', true);
                $kunciJawabanUntukExpress = $kunciJawabanObj ? $kunciJawabanObj->id : null;
            } 
            elseif ($tipeSoal === 'esai') {
                // Untuk esai, kunci jawaban bisa berupa rubrik/panduan dari kolom penjelasan
                $kunciJawabanUntukExpress = $itemSoal->penjelasan; 
            }

            return [
                'id' => $itemSoal->id,
                'pertanyaan' => $itemSoal->pertanyaan,
                'tipe' => $tipeSoal,
                'pilihan' => $pilihanUntukExpress,
                'jawaban' => $kunciJawabanUntukExpress, // Kunci jawaban sekarang adalah ID dari tabel opsi_jawaban
            ];
        })->values()->all();
    }

    /**
     * Memformat daftar soal yang sudah diacak dari Express untuk dikirim ke frontend.
     */
    public function formatForFrontend(array $shuffledSoalListFromExpress): array
    {
        return array_map(function ($itemSoalExpress, $index) {
            return [
                'id' => $itemSoalExpress['id'],
                'nomor' => $index + 1,
                'tipe' => $itemSoalExpress['tipe'],
                'pertanyaan' => $itemSoalExpress['pertanyaan'],
                'opsi' => $itemSoalExpress['pilihan'] ?? null,
            ];
        }, $shuffledSoalListFromExpress, array_keys($shuffledSoalListFromExpress));
    }
}