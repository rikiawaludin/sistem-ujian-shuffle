<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ujian;
use App\Models\MataKuliah;
use Carbon\Carbon; // Import Carbon

class UjianSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil mata kuliah berdasarkan kode unik atau nama yang konsisten
        $mkKalkulusDasar = MataKuliah::where('kode_mata_kuliah', 'KAL101')->first();
        $mkAlgoPemrograman = MataKuliah::where('kode_mata_kuliah', 'ALP201')->first();
        $mkStrukturData = MataKuliah::where('kode_mata_kuliah', 'SDA301')->first();
        $mkPemWebLanjut = MataKuliah::where('kode_mata_kuliah', 'PWL601')->first(); // Sesuai seeder mata kuliah sebelumnya
        $mkBasisData = MataKuliah::where('kode_mata_kuliah', 'BAS401')->first();

        $nowWIB = Carbon::now('Asia/Jakarta');

        // Ujian untuk Kalkulus Dasar (Semester 1)
        if ($mkKalkulusDasar) {
            Ujian::updateOrCreate(
                ['judul_ujian' => 'Kuis 1 Kalkulus Dasar', 'mata_kuliah_id' => $mkKalkulusDasar->id],
                [
                    'deskripsi' => 'Kuis mencakup materi dasar limit dan fungsi.',
                    'durasi' => 30, // menit
                    'kkm' => 65,
                    'tanggal_mulai' => $nowWIB->copy()->subDays(7), // Sudah lewat
                    'tanggal_selesai' => $nowWIB->copy()->subDays(7)->addHours(2), // Sudah lewat
                    'jenis_ujian' => 'kuis',
                    'acak_soal' => true,
                    'acak_opsi' => true,
                    'tampilkan_hasil' => 'langsung',
                    'status_publikasi' => 'terbit',
                ]
            );
            Ujian::updateOrCreate(
                ['judul_ujian' => 'UTS Kalkulus Dasar', 'mata_kuliah_id' => $mkKalkulusDasar->id],
                [
                    'deskripsi' => 'Ujian Tengah Semester Kalkulus Dasar.',
                    'durasi' => 90,
                    'kkm' => 60,
                    'tanggal_mulai' => $nowWIB->copy()->addDays(3), // Akan datang
                    'tanggal_selesai' => $nowWIB->copy()->addDays(3)->addHours(3),
                    'jenis_ujian' => 'uts',
                    'acak_soal' => true,
                    'acak_opsi' => true,
                    'tampilkan_hasil' => 'setelah_selesai',
                    'status_publikasi' => 'terbit',
                ]
            );
        }

        // Ujian untuk Algoritma & Pemrograman (Semester 2)
        if ($mkAlgoPemrograman) {
            Ujian::updateOrCreate(
                ['judul_ujian' => 'UAS Algoritma & Pemrograman', 'mata_kuliah_id' => $mkAlgoPemrograman->id],
                [
                    'deskripsi' => 'Ujian Akhir Semester mencakup semua materi Algoritma dan Pemrograman dasar.',
                    'durasi' => 120,
                    'kkm' => 70,
                    'tanggal_mulai' => $nowWIB->copy()->subDays(1), // Baru saja lewat
                    'tanggal_selesai' => $nowWIB->copy()->subDays(1)->addHours(4),
                    'jenis_ujian' => 'uas',
                    'acak_soal' => true,
                    'acak_opsi' => false,
                    'tampilkan_hasil' => 'manual_dosen',
                    'status_publikasi' => 'terbit',
                ]
            );
        }
        
        // Ujian untuk Struktur Data (Semester 3)
        if ($mkStrukturData) {
            Ujian::updateOrCreate(
                ['judul_ujian' => 'Kuis Struktur Data - Linked List', 'mata_kuliah_id' => $mkStrukturData->id],
                [
                    'deskripsi' => 'Kuis singkat tentang konsep Linked List.',
                    'durasi' => 45,
                    'kkm' => 75,
                    'tanggal_mulai' => $nowWIB->copy()->addMinutes(5), // Ujian sedang/akan berlangsung (untuk testing)
                    'tanggal_selesai' => $nowWIB->copy()->addMinutes(50), // Selesai dalam 50 menit dari sekarang
                    'jenis_ujian' => 'kuis',
                    'acak_soal' => false,
                    'acak_opsi' => true,
                    'tampilkan_hasil' => 'langsung',
                    'status_publikasi' => 'terbit',
                ]
            );
        }


        // Ujian untuk Pemrograman Web Lanjut (Semester 6) - Ini yang Anda gunakan untuk testing
        if ($mkPemWebLanjut) {
            Ujian::updateOrCreate(
                ['judul_ujian' => 'Ujian Komprehensif Web Lanjut', 'mata_kuliah_id' => $mkPemWebLanjut->id],
                [
                    'deskripsi' => 'Ujian mencakup semua materi Pemrograman Web Lanjut.',
                    'durasi' => 5, // Durasi singkat untuk testing
                    'kkm' => 70, // KKM disesuaikan
                    'tanggal_mulai' => $nowWIB,
                    'tanggal_selesai' => $nowWIB->copy()->addMinutes(3), 
                    'jenis_ujian' => 'uas',
                    'acak_soal' => true,
                    'acak_opsi' => true,
                    'tampilkan_hasil' => 'manual_dosen',
                    'status_publikasi' => 'terbit',
                ]
            );
             Ujian::updateOrCreate(
                ['judul_ujian' => 'Kuis Cepat API Web Lanjut', 'mata_kuliah_id' => $mkPemWebLanjut->id],
                [
                    'deskripsi' => 'Kuis singkat tentang desain API.',
                    'durasi' => 15, 
                    'kkm' => 80, 
                    'tanggal_mulai' => $nowWIB->copy()->addDays(10), // Jauh di masa depan
                    'tanggal_selesai' => $nowWIB->copy()->addDays(10)->addHours(1),
                    'jenis_ujian' => 'kuis',
                    'acak_soal' => true,
                    'acak_opsi' => true,
                    'tampilkan_hasil' => 'langsung',
                    'status_publikasi' => 'terbit',
                ]
            );
        }
        
        // Ujian untuk Basis Data (Semester 4)
        if ($mkBasisData) {
            Ujian::updateOrCreate(
                ['judul_ujian' => 'UTS Basis Data', 'mata_kuliah_id' => $mkBasisData->id],
                [
                    'deskripsi' => 'Materi Normalisasi dan SQL Dasar.',
                    'durasi' => 75,
                    'kkm' => 60,
                    'tanggal_mulai' => $nowWIB->copy()->addHours(-1), // Sedang berlangsung
                    'tanggal_selesai' => $nowWIB->copy()->addHours(1), // Selesai 1 jam lagi
                    'jenis_ujian' => 'uts',
                    'acak_soal' => true,
                    'acak_opsi' => true,
                    'tampilkan_hasil' => 'setelah_selesai',
                    'status_publikasi' => 'terbit',
                ]
            );
        }

        $this->command->info('Seeder Ujian selesai.');
    }
}