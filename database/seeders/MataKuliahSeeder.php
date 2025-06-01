<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MataKuliah;
use App\Models\User;

class MataKuliahSeeder extends Seeder
{
    public function run(): void
    {
        $dosen1 = User::where('email', 'budi.dosen@example.com')->first();
        $dosen2 = User::where('email', 'siti.dosen@example.com')->first();

        if (!$dosen1 || !$dosen2) {
            $this->command->error('Dosen tidak ditemukan. Pastikan UserSeeder dijalankan terlebih dahulu dan email dosen sesuai.');
            // Buat dosen dummy jika tidak ada untuk melanjutkan seeder, atau hentikan.
            // Untuk contoh ini, kita akan coba buat jika tidak ada (tidak ideal untuk produksi)
            if(!$dosen1) $dosen1 = User::factory()->create(['email' => 'budi.dosen@example.com', 'role' => 'dosen']);
            if(!$dosen2) $dosen2 = User::factory()->create(['email' => 'siti.dosen@example.com', 'role' => 'dosen']);
        }

        $mataKuliahData = [
            // Semester 1
            ['nama' => 'Kalkulus Dasar', 'kode' => 'KAL101', 'desk' => 'Konsep fundamental limit, turunan, dan integral.', 'dosen_id' => $dosen1->id, 'icon' => 'icons/kalkulus.png', 'smt' => 1, 'ta' => '2024/2025'],
            ['nama' => 'Fisika Dasar I', 'kode' => 'FIS101', 'desk' => 'Mekanika, panas, dan bunyi.', 'dosen_id' => $dosen2->id, 'icon' => 'icons/fisika.png', 'smt' => 1, 'ta' => '2024/2025'],
            // Semester 2
            ['nama' => 'Algoritma & Pemrograman', 'kode' => 'ALP201', 'desk' => 'Dasar-dasar logika pemrograman dan implementasi.', 'dosen_id' => $dosen1->id, 'icon' => 'icons/algo.png', 'smt' => 2, 'ta' => '2024/2025'],
            ['nama' => 'Bahasa Inggris I', 'kode' => 'ENG201', 'desk' => 'Keterampilan dasar berbahasa Inggris.', 'dosen_id' => $dosen2->id, 'icon' => 'icons/english.png', 'smt' => 2, 'ta' => '2024/2025'],
            // Semester 3
            ['nama' => 'Struktur Data', 'kode' => 'SDA301', 'desk' => 'Mempelajari berbagai struktur data.', 'dosen_id' => $dosen1->id, 'icon' => 'icons/sda.png', 'smt' => 3, 'ta' => '2024/2025'],
            ['nama' => 'Pemrograman Berorientasi Objek', 'kode' => 'PBO302', 'desk' => 'Konsep OOP dan implementasinya.', 'dosen_id' => $dosen2->id, 'icon' => 'icons/oop.png', 'smt' => 3, 'ta' => '2024/2025'],
            // Semester 4
            ['nama' => 'Basis Data', 'kode' => 'BAS401', 'desk' => 'Desain dan manajemen basis data relasional.', 'dosen_id' => $dosen1->id, 'icon' => 'icons/database.png', 'smt' => 4, 'ta' => '2024/2025'],
            ['nama' => 'Jaringan Komputer', 'kode' => 'JAR402', 'desk' => 'Konsep dasar jaringan komputer.', 'dosen_id' => $dosen2->id, 'icon' => 'icons/jaringan.png', 'smt' => 4, 'ta' => '2024/2025'],
            // Semester 5
            ['nama' => 'Pemrograman Web Dasar', 'kode' => 'PWD501', 'desk' => 'Pengembangan web dengan HTML, CSS, JavaScript.', 'dosen_id' => $dosen1->id, 'icon' => 'icons/web.png', 'smt' => 5, 'ta' => '2024/2025'],
            // Semester 6
            ['nama' => 'Pemrograman Web Lanjut', 'kode' => 'PWL601', 'desk' => 'Pengembangan web dengan framework backend.', 'dosen_id' => $dosen2->id, 'icon' => 'icons/web_lanjut.png', 'smt' => 6, 'ta' => '2024/2025'],
            ['nama' => 'Kecerdasan Buatan', 'kode' => 'KCB602', 'desk' => 'Pengenalan konsep AI dan machine learning.', 'dosen_id' => $dosen1->id, 'icon' => 'icons/ai.png', 'smt' => 6, 'ta' => '2024/2025'],
            // Semester 7
            ['nama' => 'Proyek Perangkat Lunak', 'kode' => 'PPL701', 'desk' => 'Pengembangan proyek perangkat lunak tim.', 'dosen_id' => $dosen2->id, 'icon' => 'icons/software_project.png', 'smt' => 7, 'ta' => '2024/2025'],
            ['nama' => 'Keamanan Informasi', 'kode' => 'KI702', 'desk' => 'Aspek keamanan dalam sistem informasi.', 'dosen_id' => $dosen1->id, 'icon' => 'icons/security.png', 'smt' => 7, 'ta' => '2024/2025'],
            // Semester 8
            ['nama' => 'Skripsi', 'kode' => 'SKR801', 'desk' => 'Penulisan tugas akhir penelitian.', 'dosen_id' => $dosen2->id, 'icon' => 'icons/thesis.png', 'smt' => 8, 'ta' => '2024/2025'],
            ['nama' => 'Etika Profesi', 'kode' => 'ETP802', 'desk' => 'Studi tentang etika dalam profesi TI.', 'dosen_id' => $dosen1->id, 'icon' => 'icons/ethics.png', 'smt' => 8, 'ta' => '2024/2025'],
        ];

        foreach ($mataKuliahData as $data) {
            MataKuliah::updateOrCreate(
                ['kode_mata_kuliah' => $data['kode']], // Kunci unik untuk update atau create
                [
                    'nama_mata_kuliah' => $data['nama'],
                    'deskripsi' => $data['desk'],
                    'dosen_id' => $data['dosen_id'],
                    'icon_url' => '/images/' . $data['icon'], // Sesuaikan path jika perlu
                    'semester' => $data['smt'],
                    'tahun_ajaran' => $data['ta'],
                ]
            );
        }
        $this->command->info('Seeder Mata Kuliah dengan data semester dan tahun ajaran selesai.');
    }
}