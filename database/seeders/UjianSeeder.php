<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ujian;
use App\Models\MataKuliah;
use Carbon\Carbon;

class UjianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // --- DATA MATA KULIAH BERDASARKAN EXTERNAL_ID DARI API ---
        // Catatan: Pastikan Anda sudah menjalankan SyncMataKuliahController sehingga
        // data mata kuliah dengan external_id ini sudah ada di database lokal Anda.

        // Contoh: external_id untuk "Kecerdasan Buatan" dari API Anda adalah 114
        $mkKecerdasanBuatan = MataKuliah::where('external_id', '114')->first();

        // Contoh lain, misal "Teori Bahasa dan Otomata" memiliki external_id 67
        $mkTBO = MataKuliah::where('external_id', '115')->first();

        // Contoh lain, misal "Basis Data" memiliki external_id yang lain
        $mkBasisData = MataKuliah::where('external_id', '118')->first(); // Ganti '42' dengan external_id yang benar

        // Menggunakan Waktu Indonesia Barat (WIB) sebagai referensi
        $now = Carbon::now('Asia/Jakarta');

        // --- UJIAN UNTUK KECERDASAN BUATAN (external_id: 114) ---
        if ($mkKecerdasanBuatan) {
            // Ujian 1: Kuis yang sedang berlangsung
            Ujian::updateOrCreate(
                [
                    'mata_kuliah_id' => $mkKecerdasanBuatan->id,
                    'judul_ujian' => 'Kuis 1: Pengenalan AI & Agent Cerdas'
                ],
                [
                    'deskripsi' => 'Kuis singkat mencakup materi dasar tentang definisi AI dan tipe-tipe agent.',
                    'durasi' => 5, // menit
                    'kkm' => 70,
                    'tanggal_mulai' => $now,
                    'tanggal_selesai' => $now->copy()->addMinutes(5),
                    'jenis_ujian' => 'kuis',
                    'acak_soal' => true,
                    'acak_opsi' => true,
                    'tampilkan_hasil' => 'langsung',
                    'status_publikasi' => 'published',
                ]
            );

            // Ujian 2: UTS yang akan datang
            Ujian::updateOrCreate(
                [
                    'mata_kuliah_id' => $mkKecerdasanBuatan->id,
                    'judul_ujian' => 'UTS Kecerdasan Buatan'
                ],
                [
                    'deskripsi' => 'Ujian Tengah Semester mencakup materi hingga Algoritma Pencarian.',
                    'durasi' => 90,
                    'kkm' => 65,
                    'tanggal_mulai' => $now->copy()->addDays(5), // Mulai dalam 5 hari
                    'tanggal_selesai' => $now->copy()->addDays(5)->addHours(2), // Jendela waktu 2 jam
                    'jenis_ujian' => 'uts',
                    'acak_soal' => true,
                    'acak_opsi' => true,
                    'tampilkan_hasil' => 'setelah_selesai',
                    'status_publikasi' => 'published',
                ]
            );
        }

        // --- UJIAN UNTUK TEORI BAHASA DAN OTOMATA (external_id: 67) ---
        if ($mkTBO) {
            // Ujian 1: UAS yang sudah lewat
            Ujian::updateOrCreate(
                [
                    'mata_kuliah_id' => $mkTBO->id,
                    'judul_ujian' => 'UAS Teori Bahasa dan Otomata'
                ],
                [
                    'deskripsi' => 'Ujian Akhir Semester mencakup semua materi hingga Mesin Turing.',
                    'durasi' => 120,
                    'kkm' => 60,
                    'tanggal_mulai' => $now->copy()->subDays(10), // Mulai 10 hari yang lalu
                    'tanggal_selesai' => $now->copy()->subDays(10)->addHours(3), // Selesai pada hari yang sama
                    'jenis_ujian' => 'uas',
                    'acak_soal' => true,
                    'acak_opsi' => false,
                    'tampilkan_hasil' => 'manual_dosen',
                    'status_publikasi' => 'published',
                ]
            );
        }
        
        // --- UJIAN UNTUK BASIS DATA (ganti external_id) ---
        if ($mkBasisData) {
            // Ujian 1: Ujian yang tidak dipublikasikan (draft)
            Ujian::updateOrCreate(
                [
                    'mata_kuliah_id' => $mkBasisData->id,
                    'judul_ujian' => 'Kuis Tambahan Basis Data'
                ],
                [
                    'deskripsi' => 'Kuis ini masih dalam tahap persiapan.',
                    'durasi' => 15,
                    'kkm' => 75,
                    'tanggal_mulai' => $now->copy()->addMonth(), // Jauh di masa depan
                    'tanggal_selesai' => $now->copy()->addMonth()->addDay(),
                    'jenis_ujian' => 'kuis',
                    'acak_soal' => true,
                    'acak_opsi' => true,
                    'tampilkan_hasil' => 'langsung',
                    'status_publikasi' => 'draft', // Statusnya draft, tidak akan muncul untuk mahasiswa
                ]
            );
        }

        $this->command->info('Seeder Ujian yang relevan dengan data API telah selesai.');
    }
}