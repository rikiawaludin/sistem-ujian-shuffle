<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'), // Ganti 'password' dengan password dummy Anda
            // Tambahkan field lain jika ada, misal 'email_verified_at'
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('adminpass'), // Password dummy lain
            'email_verified_at' => now(),
        ]);

        // Anda bisa menambahkan lebih banyak user dummy di sini
        // Atau menggunakan User Factory jika ingin data yang lebih banyak dan acak
        // \App\Models\User::factory(10)->create();
    }
}
