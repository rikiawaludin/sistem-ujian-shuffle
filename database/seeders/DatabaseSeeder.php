<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,          // 1. User harus ada duluan
            MataKuliahSeeder::class,    // 2. Mata Kuliah harus ada duluan (butuh dosen dari User)
            UjianSeeder::class,         // 3. Ujian harus ada duluan (butuh Mata Kuliah)
            SoalSeeder::class,          // 4. Soal harus ada duluan (butuh dosen dari User)
            UjianSoalSeeder::class,     // 5. Ujian dan Soal harus ada duluan
            MataKuliahUserSeeder::class, // 6. User dan Mata Kuliah harus ada duluan
            PengerjaanUjianSeeder::class, // 7. User dan Ujian harus ada duluan
            JawabanPesertaDetailSeeder::class, // 8. Pengerjaan Ujian dan Soal harus ada duluan
        ]);
    }
}
