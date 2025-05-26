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
        // \App\Models\User::factory(10)->create();

        $this->call([
            UserSeeder::class,
            MataKuliahSeeder::class,
            UjianSeeder::class,
            SoalSeeder::class,
            UjianSoalSeeder::class,       // Setelah UjianSeeder dan SoalSeeder
            MataKuliahUserSeeder::class,  // Setelah UserSeeder dan MataKuliahSeeder
            PengerjaanUjianSeeder::class, // Setelah UserSeeder dan UjianSeeder
            JawabanPesertaDetailSeeder::class, // Setelah PengerjaanUjianSeeder dan SoalSeeder
        ]);
    }
}
