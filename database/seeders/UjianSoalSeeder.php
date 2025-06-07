<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ujian;
use App\Models\Soal;

class UjianSoalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Langkah 1: Definisikan hubungan antara Ujian dan Soal
        // menggunakan judul dan pertanyaan yang persis dari seeder lain.
        $ujianDanSoalnya = [
            'Kuis 1: Pengenalan AI & Agent Cerdas' => [
                [
                    'pertanyaan' => 'Siapakah yang dianggap sebagai "Bapak Kecerdasan Buatan"?',
                    'nomor' => 1, 'bobot' => 50
                ],
                [
                    'pertanyaan' => 'Algoritma A* (A-Star) selalu menemukan jalur terpendek jika heuristik yang digunakan bersifat admissible.',
                    'nomor' => 2, 'bobot' => 50
                ],
            ],
            'UTS Kecerdasan Buatan' => [
                [
                    'pertanyaan' => 'Siapakah yang dianggap sebagai "Bapak Kecerdasan Buatan"?',
                    'nomor' => 1, 'bobot' => 30
                ],
                [
                    'pertanyaan' => 'Algoritma A* (A-Star) selalu menemukan jalur terpendek jika heuristik yang digunakan bersifat admissible.',
                    'nomor' => 2, 'bobot' => 30
                ],
                [
                    'pertanyaan' => 'Jelaskan perbedaan mendasar antara supervised learning dan unsupervised learning!',
                    'nomor' => 3, 'bobot' => 40
                ],
            ],
            'UAS Teori Bahasa dan Otomata' => [
                [
                    'pertanyaan' => 'Mesin automata yang digunakan untuk mengenali bahasa reguler adalah...',
                    'nomor' => 1, 'bobot' => 100
                ],
            ],
            'Kuis Tambahan Basis Data' => [
                // Ujian ini di UjianSeeder statusnya 'draft', jadi mungkin tidak perlu soal.
                // Tapi kita tetap bisa menghubungkannya untuk kelengkapan data.
                [
                    'pertanyaan' => 'Perintah "DROP TABLE" dan "TRUNCATE TABLE" memiliki fungsi yang sama persis dalam SQL.',
                    'nomor' => 1, 'bobot' => 100
                ],
            ],
        ];

        // Langkah 2: Ambil semua data relevan sekali saja untuk efisiensi
        $semuaUjian = Ujian::all()->keyBy('judul_ujian');
        $semuaSoal = Soal::all()->keyBy('pertanyaan');

        // Kosongkan tabel pivot untuk memastikan data bersih setiap kali seeder dijalankan
        \Illuminate\Support\Facades\DB::table('ujian_soal')->truncate();

        $this->command->info('Memulai proses menghubungkan Ujian dengan Soal...');

        // Langkah 3: Loop dan hubungkan data menggunakan Eloquent
        foreach ($ujianDanSoalnya as $judulUjian => $daftarSoal) {
            $ujian = $semuaUjian->get($judulUjian);

            if (!$ujian) {
                $this->command->warn("Ujian '{$judulUjian}' tidak ditemukan, dilewati.");
                continue;
            }

            foreach ($daftarSoal as $detailSoal) {
                $soal = $semuaSoal->get($detailSoal['pertanyaan']);

                if (!$soal) {
                    $this->command->warn("Soal dengan pertanyaan '{$detailSoal['pertanyaan']}' tidak ditemukan, dilewati.");
                    continue;
                }

                // Gunakan attach() untuk menyisipkan ke tabel pivot
                $ujian->soal()->attach($soal->id, [
                    'nomor_urut_di_ujian' => $detailSoal['nomor'],
                    'bobot_nilai_soal' => $detailSoal['bobot'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
             $this->command->line(" -> Soal-soal untuk '{$judulUjian}' berhasil dihubungkan.");
        }

        $this->command->info('Seeder UjianSoal telah selesai dijalankan dengan data yang sesuai.');
    }
}