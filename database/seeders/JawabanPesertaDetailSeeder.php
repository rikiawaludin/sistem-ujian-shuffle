<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JawabanPesertaDetail;
use App\Models\PengerjaanUjian;
use App\Models\Soal;
use App\Models\User;
use App\Models\Ujian;

class JawabanPesertaDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Mulai JawabanPesertaDetailSeeder...');

        // 1. Cari User
        $userAndi = User::where('email', 'andi.siswa@example.com')->first();
        $userCitra = User::where('email', 'citra.siswa@example.com')->first();

        // 2. Cari Ujian
        $ujianKalkulusLanjutan = Ujian::where('judul_ujian', 'UTS Kalkulus Lanjutan')->first();
        $ujianKuisPBO = Ujian::where('judul_ujian', 'Kuis 1 Pemrograman Berorientasi Objek')->first();
        $ujianUASSDA = Ujian::where('judul_ujian', 'UAS Struktur Data')->first();

        // 3. Cari Soal (pastikan nama kategori_soal sesuai dengan yang di SoalSeeder)
        $soalTurunan = Soal::where('kategori_soal', 'Turunan')->first();
        $soalOOP = Soal::where('kategori_soal', 'Konsep Dasar OOP')->first();
        $soalEnkapsulasi = Soal::where('kategori_soal', 'Enkapsulasi OOP')->first();
        $soalStrukturData = Soal::where('kategori_soal', 'Struktur Data')->first();
        // Anda juga bisa mencari berdasarkan pertanyaan atau ID jika lebih spesifik

        // 4. Cari Pengerjaan Ujian dan buat detail jawaban HANYA JIKA SEMUA ADA
        if ($userAndi && $ujianKalkulusLanjutan && $soalTurunan) {
            $pengerjaanSiswa1UtsKalkulus = PengerjaanUjian::where('user_id', $userAndi->id)
                                              ->where('ujian_id', $ujianKalkulusLanjutan->id)
                                              ->first();
            if ($pengerjaanSiswa1UtsKalkulus) {
                JawabanPesertaDetail::updateOrCreate(
                    [
                        'pengerjaan_ujian_id' => $pengerjaanSiswa1UtsKalkulus->id,
                        'soal_id' => $soalTurunan->id,
                    ],
                    [
                        'jawaban_user' => json_encode(['C']), // Pastikan ini sesuai dengan opsi yang mungkin ada di Soal
                        'is_benar' => true,
                        'skor_per_soal' => 50.00,
                        'is_ragu_ragu' => false,
                    ]
                );
                $this->command->info('Jawaban Andi untuk Kalkulus Lanjutan berhasil.');
            } else {
                $this->command->warn('Pengerjaan Ujian untuk Andi di Kalkulus Lanjutan tidak ditemukan.');
            }
        } else {
            $this->command->warn('Data dasar (User Andi/Ujian Kalkulus/Soal Turunan) untuk jawaban Andi di Kalkulus Lanjutan tidak lengkap.');
        }

        if ($userCitra && $ujianKalkulusLanjutan && $soalTurunan) {
            $pengerjaanSiswa2UtsKalkulus = PengerjaanUjian::where('user_id', $userCitra->id)
                                              ->where('ujian_id', $ujianKalkulusLanjutan->id)
                                              ->first();
            if ($pengerjaanSiswa2UtsKalkulus) {
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
                 $this->command->info('Jawaban Citra untuk Kalkulus Lanjutan berhasil.');
            } else {
                $this->command->warn('Pengerjaan Ujian untuk Citra di Kalkulus Lanjutan tidak ditemukan.');
            }
        } else {
            $this->command->warn('Data dasar (User Citra/Ujian Kalkulus/Soal Turunan) untuk jawaban Citra di Kalkulus Lanjutan tidak lengkap.');
        }

        if ($userAndi && $ujianKuisPBO && $soalOOP) {
            $pengerjaanSiswa1KuisPBO = PengerjaanUjian::where('user_id', $userAndi->id)
                                          ->where('ujian_id', $ujianKuisPBO->id)
                                          ->first();
            if ($pengerjaanSiswa1KuisPBO) {
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
                $this->command->info('Jawaban Andi untuk Kuis PBO (soal OOP) berhasil.');
            }
        } else {
             $this->command->warn('Data dasar (User Andi/Ujian Kuis PBO/Soal OOP) untuk jawaban Andi di Kuis PBO tidak lengkap.');
        }

        if ($userAndi && $ujianKuisPBO && $soalEnkapsulasi) { // Ini perlu if terpisah untuk setiap soal
            $pengerjaanSiswa1KuisPBO = PengerjaanUjian::where('user_id', $userAndi->id)
                                          ->where('ujian_id', $ujianKuisPBO->id)
                                          ->first();
            if ($pengerjaanSiswa1KuisPBO) {
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
                $this->command->info('Jawaban Andi untuk Kuis PBO (soal Enkapsulasi) berhasil.');
            }
        } else {
            $this->command->warn('Data dasar (User Andi/Ujian Kuis PBO/Soal Enkapsulasi) untuk jawaban Andi di Kuis PBO tidak lengkap.');
        }

        if ($userCitra && $ujianUASSDA && $soalStrukturData) {
            $pengerjaanSiswa2UasSDA = PengerjaanUjian::where('user_id', $userCitra->id)
                                          ->where('ujian_id', $ujianUASSDA->id)
                                          ->first();
            if ($pengerjaanSiswa2UasSDA) {
                 JawabanPesertaDetail::updateOrCreate(
                    [
                        'pengerjaan_ujian_id' => $pengerjaanSiswa2UasSDA->id,
                        'soal_id' => $soalStrukturData->id,
                    ],
                    [
                        'jawaban_user' => json_encode(['B']), // Pastikan ini sesuai dengan opsi 'Stack' yang diubah
                        'is_benar' => true,
                        'skor_per_soal' => 100.00,
                    ]
                );
                $this->command->info('Jawaban Citra untuk UAS SDA berhasil.');
            } else {
                 $this->command->warn('Pengerjaan Ujian untuk Citra di UAS SDA tidak ditemukan.');
            }
        } else {
            $this->command->warn('Data dasar (User Citra/Ujian UAS SDA/Soal Struktur Data) untuk jawaban Citra di UAS SDA tidak lengkap.');
        }
        $this->command->info('JawabanPesertaDetailSeeder selesai.');
    }
}