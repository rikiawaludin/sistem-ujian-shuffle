<?php

namespace App\Traits;

trait FisherYatesShuffle
{
    /**
     * Mengacak sebuah array menggunakan algoritma Fisher-Yates.
     * Fungsi ini mengembalikan array baru yang sudah diacak (tidak mengubah array asli).
     *
     * @param array $array Array yang akan diacak.
     * @return array Array baru yang sudah diacak.
     */
    protected function fisherYates(array $array): array
    {
        $shuffled = $array; // Salin array agar tidak mengubah array asli
        $count = count($shuffled);
        for ($n = $count - 1; $n > 0; $n--) {
            $k = random_int(0, $n);
            // Tukar elemen
            $temp = $shuffled[$n];
            $shuffled[$n] = $shuffled[$k];
            $shuffled[$k] = $temp;
        }
        return $shuffled;
    }
}