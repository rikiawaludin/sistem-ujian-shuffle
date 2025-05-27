<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ujian; // Pastikan model Ujian diimpor
use App\Models\Soal;  // Pastikan model Soal diimpor

class UjianSoalController extends Controller
{
    public function getSoalUntukUjian(Request $request, $id_ujian)
    {
        $ujian = Ujian::with('soal')->find($id_ujian); // Mengambil ujian beserta relasi soal-soalnya

        if (!$ujian) {
            return response()->json(['message' => 'Ujian tidak ditemukan'], 404);
        }

        // Format soal agar sesuai dengan yang mungkin dibutuhkan frontend
        // Ini adalah soal SEBELUM diacak oleh layanan Express.js
        $soalListFormatted = $ujian->soal->map(function ($itemSoal, $index) {
                // Untuk debugging:
                    \Log::info('Soal ID: ' . $itemSoal->id . ' - Tipe Opsi Jawaban dari Model: ' . gettype($itemSoal->opsi_jawaban));
                    if (is_string($itemSoal->opsi_jawaban)) {
                        \Log::info('Soal ID: ' . $itemSoal->id . ' - Isi Opsi Jawaban (string): ' . $itemSoal->opsi_jawaban);
                    } else {
                        \Log::info('Soal ID: ' . $itemSoal->id . ' - Isi Opsi Jawaban (sudah di-cast): ', (array) $itemSoal->opsi_jawaban);
                    }
            return [
                'id' => $itemSoal->id, // ID soal dari database
                'nomor' => $index + 1, // Nomor urut berdasarkan pengambilan dari DB
                'tipe' => $itemSoal->tipe_soal,
                'pertanyaan' => $itemSoal->pertanyaan,
                'opsi' => $itemSoal->opsi_jawaban, // Ini masih JSON string, perlu di-decode di frontend atau di-cast di model Soal
                'jawabanUser' => null, // Default untuk frontend
                'raguRagu' => false,   // Default untuk frontend
                // Kunci jawaban dan penjelasan bisa tidak disertakan di sini untuk keamanan,
                // atau disertakan jika API ini hanya untuk simulasi frontend awal.
                // 'kunciJawaban' => $itemSoal->kunci_jawaban,
                // 'penjelasan' => $itemSoal->penjelasan,
            ];
        });

        // Di sinilah nantinya Anda akan memanggil layanan Express.js untuk pengacakan
        // Untuk sekarang, kita kembalikan soal yang belum diacak dari Laravel.
        // $soalTeracak = $this->panggilLayananExpressUntukPengacakan($soalListFormatted);

        return response()->json([
            'id' => $ujian->id,
            'namaMataKuliah' => $ujian->mataKuliah->nama_mata_kuliah ?? 'N/A', // Asumsi ada relasi mataKuliah() di model Ujian
            'judulUjian' => $ujian->judul_ujian,
            'durasiTotalDetik' => $ujian->durasi * 60, // Asumsi durasi di DB dalam menit
            'soalList' => $soalListFormatted, // atau $soalTeracak nantinya
        ]);
    }

    // Placeholder fungsi untuk memanggil Express.js (implementasi nanti)
    // private function panggilLayananExpressUntukPengacakan($soalList) {
    //   // Logika HTTP request ke Express.js Anda
    //   // Mengirim $soalList, menerima soal yang sudah teracak
    //   return $soalList; // Placeholder
    // }
}