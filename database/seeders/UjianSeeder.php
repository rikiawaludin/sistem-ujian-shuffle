<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ujian;
use App\Models\MataKuliah;

class UjianSeeder extends Seeder
{
    public function run(): void
    {
        $mkKalkulus = MataKuliah::where('nama_mata_kuliah', 'Kalkulus Lanjutan')->first();
        $mkPBO = MataKuliah::where('nama_mata_kuliah', 'Pemrograman Berorientasi Objek')->first();
        $mkSDA = MataKuliah::where('nama_mata_kuliah', 'Struktur Data dan Algoritma')->first();
        $mkWeb = MataKuliah::where('nama_mata_kuliah', 'Pemrograman Web Lanjut')->first(); // Ini belum ada di MataKuliahSeeder Anda, perlu ditambahkan atau dihilangkan

        // Ujian untuk Kalkulus Lanjutan
        if ($mkKalkulus) {
            Ujian::updateOrCreate(
                ['judul_ujian' => 'UTS Kalkulus Lanjutan', 'mata_kuliah_id' => $mkKalkulus->id],
                [
                    'deskripsi' => 'Ujian Tengah Semester untuk Kalkulus Lanjutan.',
                    'durasi' => 90, // menit
                    'kkm' => 60,
                    'tanggal_mulai' => now()->subDays(5),
                    'tanggal_selesai' => now()->subDays(5)->addHours(2),
                    'jenis_ujian' => 'uts',
                    'acak_soal' => true,
                    'acak_opsi' => true,
                    'tampilkan_hasil' => 'langsung',
                    'status_publikasi' => 'terbit',
                ]
            );
        }

        // Ujian untuk Pemrograman Berorientasi Objek
        if ($mkPBO) {
            Ujian::updateOrCreate(
                ['judul_ujian' => 'Kuis 1 Pemrograman Berorientasi Objek', 'mata_kuliah_id' => $mkPBO->id],
                [
                    'deskripsi' => 'Kuis pertama materi OOP dasar.',
                    'durasi' => 30, // menit
                    'kkm' => 75,
                    'tanggal_mulai' => now()->subDays(10),
                    'tanggal_selesai' => now()->subDays(10)->addMinutes(45),
                    'jenis_ujian' => 'kuis',
                    'acak_soal' => false,
                    'acak_opsi' => true,
                    'tampilkan_hasil' => 'langsung',
                    'status_publikasi' => 'terbit',
                ]
            );
        }

        // Ujian untuk Struktur Data dan Algoritma
        if ($mkSDA) {
            Ujian::updateOrCreate(
                ['judul_ujian' => 'UAS Struktur Data', 'mata_kuliah_id' => $mkSDA->id],
                [
                    'deskripsi' => 'Ujian Akhir Semester untuk Struktur Data dan Algoritma.',
                    'durasi' => 120, // menit
                    'kkm' => 70,
                    'tanggal_mulai' => now()->subDays(2),
                    'tanggal_selesai' => now()->subDays(2)->addHours(3),
                    'jenis_ujian' => 'uas',
                    'acak_soal' => true,
                    'acak_opsi' => true,
                    'tampilkan_hasil' => 'manual_dosen',
                    'status_publikasi' => 'terbit',
                ]
            );
        }

        // Ujian Komprehensif Web Lanjut (yang sudah ada)
        // PERHATIAN: 'Pemrograman Web Lanjut' BELUM ADA di MataKuliahSeeder Anda.
        // Anda perlu menambahkannya di MataKuliahSeeder atau hapus ujian ini jika tidak relevan.
        if ($mkWeb) { // Ini akan menjadi null jika mkWeb tidak ada
            Ujian::updateOrCreate(
                ['judul_ujian' => 'Ujian Komprehensif Web Lanjut', 'mata_kuliah_id' => $mkWeb->id],
                [
                    'deskripsi' => 'Ujian mencakup semua materi Pemrograman Web Lanjut.',
                    'durasi' => 120, // menit
                    'kkm' => 70,
                    'tanggal_mulai' => now()->addDays(20),
                    'tanggal_selesai' => now()->addDays(20)->addHours(3),
                    'jenis_ujian' => 'uas',
                    'acak_soal' => true,
                    'acak_opsi' => true,
                    'tampilkan_hasil' => 'manual_dosen',
                    'status_publikasi' => 'terbit',
                ]
            );
        }
    }
}