<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Ujian; // Impor model Ujian
use App\Models\Soal;  // Impor model Soal

class UjianSoalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Ambil ujian yang relevan
        $ujianWebLanjut = Ujian::where('judul_ujian', 'Ujian Komprehensif Web Lanjut')->first();
        $ujianKalkulus = Ujian::where('judul_ujian', 'UTS Kalkulus Lanjutan')->first();
        $ujianPBO = Ujian::where('judul_ujian', 'Kuis 1 Pemrograman Berorientasi Objek')->first();
        $ujianSDA = Ujian::where('judul_ujian', 'UAS Struktur Data')->first();


        // Ambil soal-soal yang relevan berdasarkan kategori atau pertanyaan
        $soalFrameworkPHP = Soal::where('kategori_soal', 'Framework PHP')->first();
        $soalKonsepHTTP = Soal::where('kategori_soal', 'Konsep HTTP')->first();
        $soalAPIRest = Soal::where('kategori_soal', 'API')->first();
        $soalDasarWeb = Soal::where('kategori_soal', 'Dasar Web')->first();
        $soalReactState = Soal::where('kategori_soal', 'React State Management')->first();
        $soalTurunan = Soal::where('kategori_soal', 'Turunan')->first();
        $soalKonsepOOP = Soal::where('kategori_soal', 'Konsep Dasar OOP')->first();
        $soalEnkapsulasi = Soal::where('kategori_soal', 'Enkapsulasi OOP')->first();
        $soalStrukturData = Soal::where('kategori_soal', 'Struktur Data')->first();


        $dataToInsert = [];

        // Hubungkan soal-soal Web Lanjut ke Ujian Komprehensif Web Lanjut
        if ($ujianWebLanjut && $soalFrameworkPHP) {
            $dataToInsert[] = [
                'ujian_id' => $ujianWebLanjut->id,
                'soal_id' => $soalFrameworkPHP->id,
                'nomor_urut_di_ujian' => 1,
                'bobot_nilai_soal' => 10,
                'created_at' => now(), 'updated_at' => now(),
            ];
        }
        if ($ujianWebLanjut && $soalKonsepHTTP) {
            $dataToInsert[] = [
                'ujian_id' => $ujianWebLanjut->id,
                'soal_id' => $soalKonsepHTTP->id,
                'nomor_urut_di_ujian' => 2,
                'bobot_nilai_soal' => 10,
                'created_at' => now(), 'updated_at' => now(),
            ];
        }
        if ($ujianWebLanjut && $soalAPIRest) {
            $dataToInsert[] = [
                'ujian_id' => $ujianWebLanjut->id,
                'soal_id' => $soalAPIRest->id,
                'nomor_urut_di_ujian' => 3,
                'bobot_nilai_soal' => 15,
                'created_at' => now(), 'updated_at' => now(),
            ];
        }
        if ($ujianWebLanjut && $soalDasarWeb) {
            $dataToInsert[] = [
                'ujian_id' => $ujianWebLanjut->id,
                'soal_id' => $soalDasarWeb->id,
                'nomor_urut_di_ujian' => 4,
                'bobot_nilai_soal' => 10,
                'created_at' => now(), 'updated_at' => now(),
            ];
        }
        if ($ujianWebLanjut && $soalReactState) {
            $dataToInsert[] = [
                'ujian_id' => $ujianWebLanjut->id,
                'soal_id' => $soalReactState->id,
                'nomor_urut_di_ujian' => 5,
                'bobot_nilai_soal' => 10,
                'created_at' => now(), 'updated_at' => now(),
            ];
        }

        // Hubungkan soal Turunan ke UTS Kalkulus Lanjutan
        if ($ujianKalkulus && $soalTurunan) {
            $dataToInsert[] = [
                'ujian_id' => $ujianKalkulus->id,
                'soal_id' => $soalTurunan->id,
                'nomor_urut_di_ujian' => 1,
                'bobot_nilai_soal' => 100, // Misal ini satu-satunya soal
                'created_at' => now(), 'updated_at' => now(),
            ];
        }

        // Hubungkan soal OOP ke Kuis 1 PBO
        if ($ujianPBO && $soalKonsepOOP) {
            $dataToInsert[] = [
                'ujian_id' => $ujianPBO->id,
                'soal_id' => $soalKonsepOOP->id,
                'nomor_urut_di_ujian' => 1,
                'bobot_nilai_soal' => 50,
                'created_at' => now(), 'updated_at' => now(),
            ];
        }
        if ($ujianPBO && $soalEnkapsulasi) {
            $dataToInsert[] = [
                'ujian_id' => $ujianPBO->id,
                'soal_id' => $soalEnkapsulasi->id,
                'nomor_urut_di_ujian' => 2,
                'bobot_nilai_soal' => 50,
                'created_at' => now(), 'updated_at' => now(),
            ];
        }

        // Hubungkan soal Struktur Data ke UAS Struktur Data
        if ($ujianSDA && $soalStrukturData) {
            $dataToInsert[] = [
                'ujian_id' => $ujianSDA->id,
                'soal_id' => $soalStrukturData->id,
                'nomor_urut_di_ujian' => 1,
                'bobot_nilai_soal' => 100,
                'created_at' => now(), 'updated_at' => now(),
            ];
        }

        // Hanya insert jika ada data
        if (!empty($dataToInsert)) {
            DB::table('ujian_soal')->insert($dataToInsert);
            $this->command->info('Tabel ujian_soal berhasil diisi.');
        } else {
            $this->command->warn('Tidak ada data untuk dimasukkan ke ujian_soal. Pastikan Ujian dan Soal yang dicari ada.');
        }
    }
}