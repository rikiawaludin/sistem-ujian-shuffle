<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MataKuliah;
use App\Models\User;

class MataKuliahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dosen1 = User::where('email', 'budi.dosen@example.com')->first();
        $dosen2 = User::where('email', 'siti.dosen@example.com')->first();

        MataKuliah::create([
            'nama_mata_kuliah' => 'Kalkulus Lanjutan',
            'kode_mata_kuliah' => 'KAL001',
            'deskripsi' => 'Pembahasan mendalam tentang limit, turunan, dan integral lanjutan.',
            'dosen_id' => $dosen1->id,
            'icon_url' => '/images/icons/kalkulus.png',
        ]);

        MataKuliah::create([
            'nama_mata_kuliah' => 'Pemrograman Berorientasi Objek',
            'kode_mata_kuliah' => 'PBO001',
            'deskripsi' => 'Konsep dasar dan implementasi OOP menggunakan Java.',
            'dosen_id' => $dosen2->id,
            'icon_url' => '/images/icons/oop.png',
        ]);

        MataKuliah::create([
            'nama_mata_kuliah' => 'Struktur Data dan Algoritma',
            'kode_mata_kuliah' => 'SDA001',
            'deskripsi' => 'Mempelajari berbagai struktur data dan algoritma fundamental.',
            'dosen_id' => $dosen1->id,
            'icon_url' => '/images/icons/sda.png',
        ]);

        MataKuliah::create([
            'nama_mata_kuliah' => 'Jaringan Komputer Dasar',
            'kode_mata_kuliah' => 'JAR001',
            'deskripsi' => 'Pengenalan konsep jaringan komputer, TCP/IP, dan OSI Layer.',
            'dosen_id' => $dosen2->id,
            'icon_url' => '/images/icons/jaringan.png',
        ]);
        
        MataKuliah::create([
            'nama_mata_kuliah' => 'Bahasa Inggris Teknik',
            'kode_mata_kuliah' => 'ENG002',
            'deskripsi' => 'Pembelajaran Bahasa Inggris untuk keperluan teknis dan akademis.',
            'dosen_id' => $dosen1->id, // Atau dosen lain jika ada
            'icon_url' => '/images/icons/english.png',
        ]);
    }
}
