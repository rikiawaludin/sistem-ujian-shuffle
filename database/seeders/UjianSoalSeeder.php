<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Ujian;
use App\Models\Soal;

class UjianSoalSeeder extends Seeder
{
    public function run(): void
    {
        // ... (Relasi ujian_soal lain yang sudah ada bisa tetap di sini) ...

        $ujianWeb = Ujian::where('judul_ujian', 'Ujian Komprehensif Web Lanjut')->first();

        $soalWeb1 = Soal::where('pertanyaan', 'Framework PHP populer yang menggunakan pola desain MVC adalah...')->first();
        $soalWeb2 = Soal::where('pertanyaan', 'Manakah dari berikut ini yang BUKAN merupakan HTTP Method?')->first();
        $soalWeb3 = Soal::where('pertanyaan', 'Jelaskan secara singkat apa itu REST API!')->first();
        $soalWeb4 = Soal::where('pertanyaan', 'CSS adalah singkatan dari Cascading Style Sheets.')->first();
        $soalWeb5 = Soal::where('pertanyaan', 'Manakah yang digunakan untuk mengelola state dalam aplikasi React skala besar?')->first();

        $soalsWeb = [$soalWeb1, $soalWeb2, $soalWeb3, $soalWeb4, $soalWeb5];
        $nomorUrut = 1;

        if ($ujianWeb) {
            foreach ($soalsWeb as $soal) {
                if ($soal) {
                    // Periksa apakah kombinasi sudah ada sebelum insert
                    $exists = DB::table('ujian_soal')
                                ->where('ujian_id', $ujianWeb->id)
                                ->where('soal_id', $soal->id)
                                ->exists();
                    if (!$exists) {
                        DB::table('ujian_soal')->insert([
                            'ujian_id' => $ujianWeb->id,
                            'soal_id' => $soal->id,
                            'nomor_urut_di_ujian' => $nomorUrut++,
                            'bobot_nilai_soal' => (100 / count(array_filter($soalsWeb))), // Bagi rata bobot
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }
}