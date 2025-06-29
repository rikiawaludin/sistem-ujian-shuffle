<?php

namespace App\Services\UjianProses;

use App\Models\Soal;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SoalFormatterService
{
    public function formatForExpress(Collection $laravelSoalCollection): array
    {
        $laravelSoalCollection->load('opsiJawaban');

        return $laravelSoalCollection->map(function (Soal $itemSoal) {
            $tipeSoal = $itemSoal->tipe_soal;
            $pilihanUntukExpress = null;
            $kunciJawabanUntukExpress = null;
            $pasanganUntukExpress = null;

            if (in_array($tipeSoal, ['pilihan_ganda', 'benar_salah', 'pilihan_jawaban_ganda'])) {
                $pilihanUntukExpress = $itemSoal->opsiJawaban->map(fn($opsi) => ['id' => $opsi->id, 'teks' => $opsi->teks_opsi])->all();
                
                if ($tipeSoal === 'pilihan_jawaban_ganda') {
                    $kunciJawabanUntukExpress = $itemSoal->opsiJawaban->where('is_kunci_jawaban', true)->pluck('id')->all();
                } else {
                    $kunciJawabanObj = $itemSoal->opsiJawaban->firstWhere('is_kunci_jawaban', true);
                    $kunciJawabanUntukExpress = $kunciJawabanObj ? $kunciJawabanObj->id : null;
                }
            } elseif ($tipeSoal === 'isian_singkat') {
                $kunciJawabanTeks = $itemSoal->opsiJawaban->pluck('teks_opsi')->all();
                $kunciJawabanUntukExpress = $kunciJawabanTeks;
            } elseif ($tipeSoal === 'menjodohkan') {
                $pilihanUntukExpress = $itemSoal->opsiJawaban->map(fn($opsi) => ['id' => $opsi->id, 'teks' => $opsi->teks_opsi])->all();
                $pasanganUntukExpress = $itemSoal->opsiJawaban->map(fn($opsi) => ['id' => $opsi->id, 'teks' => $opsi->pasangan_teks])->all();
                $kunciJawabanUntukExpress = $itemSoal->opsiJawaban->pluck('id', 'id')->all();
            } elseif ($tipeSoal === 'esai') {
                $kunciJawabanUntukExpress = $itemSoal->penjelasan;
            }

            return [
                'id' => $itemSoal->id,
                'pertanyaan' => $itemSoal->pertanyaan,
                'tipe' => $tipeSoal,
                'pilihan' => $pilihanUntukExpress,
                'pasangan' => $pasanganUntukExpress,
                'jawaban' => $kunciJawabanUntukExpress,
                'bobot' => $itemSoal->bobot,
            ];
        })->values()->all();
    }

    public function formatForFrontend(array $shuffledSoalListFromExpress): array
    {
        return array_map(function ($itemSoalExpress, $index) {
            return [
                'id' => $itemSoalExpress['id'],
                'nomor' => $index + 1,
                'tipe' => $itemSoalExpress['tipe'],
                'pertanyaan' => $itemSoalExpress['pertanyaan'],
                'opsi' => $itemSoalExpress['pilihan'] ?? null,
                'pasangan' => $itemSoalExpress['pasangan'] ?? null,
            ];
        }, $shuffledSoalListFromExpress, array_keys($shuffledSoalListFromExpress));
    }
}