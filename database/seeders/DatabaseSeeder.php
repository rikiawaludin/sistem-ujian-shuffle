<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Metode ini akan menjalankan semua seeder yang terdaftar
     * dalam urutan yang spesifik untuk memastikan dependensi data terpenuhi.
     * Jalankan dengan perintah: `php artisan db:seed`
     */
    public function run(): void
    {
        $this->command->info('Memulai proses seeding database...');

        $this->call([
            // --- Tahap 1: Membuat Data Master ---
            // Data ini adalah fondasi dan harus ada terlebih dahulu.
            UserSeeder::class,          // Membuat data pengguna (mahasiswa, dosen, admin).
            MataKuliahSeeder::class,    // Membuat data mata kuliah.
            
            // --- Tahap 2: Membuat Konten Ujian ---
            // Data ini bergantung pada data master.
            UjianSeeder::class,         // Membuat data ujian (bergantung pada MataKuliah).
            SoalSeeder::class,          // Membuat bank soal (bergantung pada User/Dosen).
            
            // --- Tahap 3: Menghubungkan Konten ke Master (Tabel Pivot) ---
            // Data ini adalah relasi antara data master dan konten.
            UjianSoalSeeder::class,     // Menghubungkan Soal ke Ujian (bergantung pada Ujian dan Soal).
            MataKuliahUserSeeder::class,// Mendaftarkan User (mahasiswa) ke Mata Kuliah (bergantung pada User dan MataKuliah).
            
            // --- Tahap 4: Membuat Data Transaksional / Riwayat ---
            // Data ini mensimulasikan aktivitas pengguna.
            PengerjaanUjianSeeder::class,       // Membuat data pengerjaan ujian oleh User (bergantung pada User dan Ujian).
            JawabanPesertaDetailSeeder::class,  // Membuat detail jawaban (bergantung pada PengerjaanUjian dan Soal).
        ]);

        $this->command->info('Proses seeding database telah selesai dengan sukses.');
    }
}
