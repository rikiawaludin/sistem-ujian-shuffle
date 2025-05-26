<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JawabanPesertaDetail;
use App\Models\PengerjaanUjian;
use App\Models\Soal;
use App\Models\User;    // Pastikan User diimpor
use App\Models\Ujian;   // Pastikan Ujian diimpor

class JawabanPesertaDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Cari User
        $userAndi = User::where('email', 'andi.siswa@example.com')->first();
        $userCitra = User::where('email', 'citra.siswa@example.com')->first();

        // 2. Cari Ujian
        $ujianKalkulusLanjutan = Ujian::where('judul_ujian', 'UTS Kalkulus Lanjutan')->first();
        $ujianKuisPBO = Ujian::where('judul_ujian', 'Kuis 1 Pemrograman Berorientasi Objek')->first();
        $ujianUASSDA = Ujian::where('judul_ujian', 'UAS Struktur Data')->first();

        // 3. Cari Soal
        $soalTurunan = Soal::where('kategori_soal', 'Turunan')->first();
        $soalOOP = Soal::where('kategori_soal', 'Konsep Dasar OOP')->first();
        $soalEnkapsulasi = Soal::where('kategori_soal', 'Enkapsulasi OOP')->first();
        $soalStrukturData = Soal::where('kategori_soal', 'Struktur Data')->first();

        // 4. Cari Pengerjaan Ujian dan buat detail jawaban HANYA JIKA SEMUA ADA
        if ($userAndi && $ujianKalkulusLanjutan) {
            $pengerjaanSiswa1UtsKalkulus = PengerjaanUjian::where('user_id', $userAndi->id)
                                              ->where('ujian_id', $ujianKalkulusLanjutan->id)
                                              ->first();
            if ($pengerjaanSiswa1UtsKalkulus && $soalTurunan) {
                JawabanPesertaDetail::updateOrCreate(
                    [
                        'pengerjaan_ujian_id' => $pengerjaanSiswa1UtsKalkulus->id,
                        'soal_id' => $soalTurunan->id,
                    ],
                    [
                        'jawaban_user' => json_encode(['C']),
                        'is_benar' => true,
                        'skor_per_soal' => 50.00,
                        'is_ragu_ragu' => false,
                    ]
                );
            }
        }

        if ($userCitra && $ujianKalkulusLanjutan) {
            $pengerjaanSiswa2UtsKalkulus = PengerjaanUjian::where('user_id', $userCitra->id)
                                              ->where('ujian_id', $ujianKalkulusLanjutan->id)
                                              ->first();
            if ($pengerjaanSiswa2UtsKalkulus && $soalTurunan) {
                JawabanPesertaDetail::updateOrCreate(
                    [
                        'pengerjaan_ujian_id' => $pengerjaanSiswa2UtsKalkulus->id,
                        'soal_id' => $soalTurunan->id,
                    ],
                    [
                        'jawaban_user' => json_encode(['B']),
                        'is_benar' => false,
                        'skor_per_soal' => 0.00,
                        'is_ragu_ragu' => true,
                    ]
                );
            }
        }

        if ($userAndi && $ujianKuisPBO) {
            $pengerjaanSiswa1KuisPBO = PengerjaanUjian::where('user_id', $userAndi->id)
                                          ->where('ujian_id', $ujianKuisPBO->id)
                                          ->first();
            if ($pengerjaanSiswa1KuisPBO && $soalOOP) {
                JawabanPesertaDetail::updateOrCreate(
                    [
                        'pengerjaan_ujian_id' => $pengerjaanSiswa1KuisPBO->id,
                        'soal_id' => $soalOOP->id,
                    ],
                    [
                        'jawaban_user' => json_encode(['A']),
                        'is_benar' => true,
                        'skor_per_soal' => 60.00,
                    ]
                );
            }
            if ($pengerjaanSiswa1KuisPBO && $soalEnkapsulasi) {
                JawabanPesertaDetail::updateOrCreate(
                    [
                        'pengerjaan_ujian_id' => $pengerjaanSiswa1KuisPBO->id,
                        'soal_id' => $soalEnkapsulasi->id,
                    ],
                    [
                        'jawaban_user' => 'Ini adalah jawaban esai siswa untuk enkapsulasi.',
                        'is_benar' => null,
                        'skor_per_soal' => null,
                    ]
                );
            }
        }
        
        if ($userCitra && $ujianUASSDA) {
            $pengerjaanSiswa2UasSDA = PengerjaanUjian::where('user_id', $userCitra->id)
                                          ->where('ujian_id', $ujianUASSDA->id)
                                          ->first();
            if ($pengerjaanSiswa2UasSDA && $soalStrukturData) {
                 JawabanPesertaDetail::updateOrCreate(
                    [
                        'pengerjaan_ujian_id' => $pengerjaanSiswa2UasSDA->id,
                        'soal_id' => $soalStrukturData->id,
                    ],
                    [
                        'jawaban_user' => json_encode(['Benar']),
                        'is_benar' => true,
                        'skor_per_soal' => 100.00,
                    ]
                );
            }
        }
    }
}