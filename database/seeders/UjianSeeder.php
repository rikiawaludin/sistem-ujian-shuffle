<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ujian;
use App\Models\MataKuliah;

class UjianSeeder extends Seeder
{
    public function run(): void
    {
        // ... (Ujian lain yang sudah ada bisa tetap di sini) ...

        $mkWeb = MataKuliah::where('nama_mata_kuliah', 'Pemrograman Web Lanjut')->first();

        if ($mkWeb) {
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