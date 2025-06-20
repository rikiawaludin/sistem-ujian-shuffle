<?php

namespace App\Services\UjianProses;

use App\Traits\FisherYatesShuffle; // <-- Gunakan Trait yang baru dibuat

class UjianShuffleService
{
    // Menggunakan Trait agar kita bisa memanggil $this->fisherYates()
    use FisherYatesShuffle;

    /**
     * Titik masuk utama untuk memproses payload ujian.
     * Fungsi ini meniru logika utama di worker Express.js.
     *
     * @param array $payload
     * @return array
     */
    public function process(array $payload): array
    {
        // Jika input memiliki 'pools' dan 'rules', jalankan logika pemilihan & pengacakan.
        if (isset($payload['pools']) && isset($payload['rules']) && isset($payload['config'])) {
            return $this->pickAndShuffleFromPools($payload['pools'], $payload['rules'], $payload['config']);
        }

        // Jika tidak, jalankan logika pengacakan untuk daftar soal yang sudah ada.
        if (isset($payload['soalList']) && isset($payload['config'])) {
            return $this->performFullShuffle($payload['soalList'], $payload['config']);
        }

        // Jika format input tidak dikenali, lemparkan exception.
        throw new \InvalidArgumentException("Input tidak valid. Service memerlukan format { pools, rules, config } atau { soalList, config }.");
    }

    /**
     * Mengambil soal dari 'pools' berdasarkan 'rules', lalu mengacaknya.
     * Menerjemahkan fungsi pickAndShuffleFromPools dari JS.
     */
    protected function pickAndShuffleFromPools(array $pools, array $rules, array $config): array
    {
        $finalSelection = [];

        foreach ($rules as $level => $countToPick) {
            if (isset($pools[$level]) && is_array($pools[$level]) && count($pools[$level]) > 0) {
                // Acak seluruh pool untuk level ini
                $shuffledPool = $this->fisherYates($pools[$level]);
                // Ambil N soal pertama dari pool yang sudah diacak
                $pickedSoal = array_slice($shuffledPool, 0, $countToPick);
                // Gabungkan ke hasil akhir
                $finalSelection = array_merge($finalSelection, $pickedSoal);
            }
        }

        // Setelah semua soal terpilih, panggil fungsi shuffle utama
        return $this->performFullShuffle($finalSelection, $config);
    }

    /**
     * Melakukan pengacakan penuh (soal & opsi) pada daftar soal.
     * Menerjemahkan fungsi performFullShuffle dari JS.
     */
    protected function performFullShuffle(array $soalList, array $config): array
    {
        $listToShuffle = $soalList;

        // 1. Acak urutan soal
        if ($config['acakUrutanSoal'] ?? false) {
            $listToShuffle = $this->fisherYates($listToShuffle);
        }

        // 2. Proses setiap soal (acak opsi, hapus kunci jawaban)
        $processedList = array_map(function ($soal) use ($config) {
            $newSoal = $soal;

            // Acak opsi jawaban jika diperlukan
            if (($config['acakUrutanOpsi'] ?? false) && isset($newSoal['pilihan']) && is_array($newSoal['pilihan'])) {
                $newSoal['pilihan'] = $this->fisherYates($newSoal['pilihan']);
            }
            
            // Logika untuk 'menjodohkan' jika perlu mengacak kolom kanan
            if (($config['acakUrutanOpsi'] ?? false) && isset($newSoal['pasangan']) && is_array($newSoal['pasangan'])) {
                $newSoal['pasangan'] = $this->fisherYates($newSoal['pasangan']);
            }

            // Hapus kunci jawaban internal sebelum dikirim
            unset($newSoal['jawaban']);
            return $newSoal;
        }, $listToShuffle);

        return $processedList;
    }
}