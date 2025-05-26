<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PengerjaanUjian;
use App\Models\Ujian;
use App\Models\User;

class PengerjaanUjianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $siswa1 = User::where('email', 'andi.siswa@example.com')->first();
        $siswa2 = User::where('email', 'citra.siswa@example.com')->first();

        $ujianUTSMatkul1 = Ujian::where('judul_ujian', 'UTS Kalkulus Lanjutan')->first();
        $ujianKuisPBO = Ujian::where('judul_ujian', 'Kuis 1 Pemrograman Berorientasi Objek')->first();
        // Ambil ID Ujian UAS Struktur Data
        $ujianUASSDA = Ujian::where('judul_ujian', 'UAS Struktur Data')->first();


        if ($siswa1 && $ujianUTSMatkul1) {
            PengerjaanUjian::create([
                'ujian_id' => $ujianUTSMatkul1->id,
                'user_id' => $siswa1->id,
                'waktu_mulai' => now()->subHours(2),
                'waktu_selesai' => now()->subMinutes(30), // Selesai 90 menit kemudian
                'waktu_dihabiskan_detik' => 90 * 60,
                'skor_total' => 85.00,
                'status_pengerjaan' => 'selesai',
            ]);
        }
        if ($siswa1 && $ujianKuisPBO) {
            PengerjaanUjian::create([
                'ujian_id' => $ujianKuisPBO->id,
                'user_id' => $siswa1->id,
                'waktu_mulai' => now()->subHour(),
                'status_pengerjaan' => 'sedang_dikerjakan', // Contoh sedang dikerjakan
            ]);
        }
        if ($siswa2 && $ujianUTSMatkul1) {
            PengerjaanUjian::create([
                'ujian_id' => $ujianUTSMatkul1->id,
                'user_id' => $siswa2->id,
                'waktu_mulai' => now()->subDays(1)->subHours(3),
                'waktu_selesai' => now()->subDays(1)->subMinutes(100),
                'waktu_dihabiskan_detik' => 80 * 60,
                'skor_total' => 65.50,
                'status_pengerjaan' => 'selesai',
            ]);
        }
         if ($siswa2 && $ujianUASSDA) {
            PengerjaanUjian::create([
                'ujian_id' => $ujianUASSDA->id,
                'user_id' => $siswa2->id,
                'waktu_mulai' => now()->subDays(2)->subHours(1),
                'waktu_selesai' => now()->subDays(2),
                'waktu_dihabiskan_detik' => 60 * 60,
                'skor_total' => 90.00,
                'status_pengerjaan' => 'selesai',
            ]);
        }
        // Buat 1 lagi pengerjaan
        $siswa3 = User::where('email', 'eka.siswa@example.com')->first();
        if ($siswa3 && $ujianKuisPBO) {
            PengerjaanUjian::create([
                'ujian_id' => $ujianKuisPBO->id,
                'user_id' => $siswa3->id,
                'waktu_mulai' => now()->subHours(5),
                'waktu_selesai' => now()->subHours(4),
                'waktu_dihabiskan_detik' => 60 * 60,
                'skor_total' => 70.00,
                'status_pengerjaan' => 'waktu_habis', // Contoh waktu habis
            ]);
        }
    }
}