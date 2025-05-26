<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User; // Pastikan model User diimpor
use Illuminate\Support\Facades\Hash; // Untuk hashing password

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Dosen
        User::create([
            'name' => 'Dr. Budi Santoso',
            'email' => 'budi.dosen@example.com',
            'password' => Hash::make('password'),
            'role' => 'dosen',
            'avatar_url' => '/images/avatars/dosen1.png', // Sediakan gambar ini jika perlu
        ]);

        User::create([
            'name' => 'Prof. Siti Aminah',
            'email' => 'siti.dosen@example.com',
            'password' => Hash::make('password'),
            'role' => 'dosen',
            'avatar_url' => '/images/avatars/dosen2.png',
        ]);

        // Siswa
        User::create([
            'name' => 'Andi Pratama',
            'email' => 'andi.siswa@example.com',
            'password' => Hash::make('password'),
            'role' => 'siswa',
            'avatar_url' => '/images/avatars/siswa1.png',
        ]);

        User::create([
            'name' => 'Citra Lestari',
            'email' => 'citra.siswa@example.com',
            'password' => Hash::make('password'),
            'role' => 'siswa',
            'avatar_url' => '/images/avatars/siswa2.png',
        ]);

        User::create([
            'name' => 'Eka Wijaya',
            'email' => 'eka.siswa@example.com',
            'password' => Hash::make('password'),
            'role' => 'siswa',
            'avatar_url' => '/images/avatars/siswa3.png',
        ]);
        
        // Admin (Opsional)
        User::create([
            'name' => 'Admin Sistem',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
    }
}
