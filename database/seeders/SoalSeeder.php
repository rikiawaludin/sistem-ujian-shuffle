<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Soal;
use App\Models\User;

class SoalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Ambil satu user dosen secara acak untuk menjadi pembuat soal
        $dosen = User::where('is_dosen', true)->first();

        if (!$dosen) {
            $this->command->error('Tidak ada user dengan peran Dosen ditemukan. Jalankan UserSeeder terlebih dahulu.');
            return;
        }

        // ===================================================================
        // SOAL UNTUK MATA KULIAH: KECERDASAN BUATAN (AI)
        // ===================================================================

        // Soal 1 (Pilihan Ganda)
        Soal::updateOrCreate(
            ['pertanyaan' => 'Siapakah yang dianggap sebagai "Bapak Kecerdasan Buatan"?'],
            [
                'tipe_soal'       => 'pilihan_ganda',
                'opsi_jawaban'    => [
                    ['id' => 'A', 'teks' => 'Alan Turing'],
                    ['id' => 'B', 'teks' => 'John McCarthy'],
                    ['id' => 'C', 'teks' => 'Geoffrey Hinton'],
                    ['id' => 'D', 'teks' => 'Bill Gates'],
                ],
                'kunci_jawaban'   => ['B'],
                'penjelasan'      => 'John McCarthy adalah ilmuwan komputer yang pertama kali menciptakan istilah "Artificial Intelligence" pada tahun 1956.',
                'level_kesulitan' => 'mudah',
                'kategori_soal'   => 'Sejarah AI',
                'dosen_pembuat_id'=> $dosen->id,
            ]
        );

        // Soal 2 (Benar/Salah)
        Soal::updateOrCreate(
            ['pertanyaan' => 'Algoritma A* (A-Star) selalu menemukan jalur terpendek jika heuristik yang digunakan bersifat admissible.'],
            [
                'tipe_soal'       => 'benar_salah',
                'opsi_jawaban'    => [
                    ['id' => 'Benar', 'teks' => 'Benar'],
                    ['id' => 'Salah', 'teks' => 'Salah'],
                ],
                'kunci_jawaban'   => ['Benar'],
                'penjelasan'      => 'Heuristik yang admissible (tidak pernah melebih-lebihkan biaya sebenarnya) menjamin bahwa A* akan menemukan solusi optimal.',
                'level_kesulitan' => 'sedang',
                'kategori_soal'   => 'Algoritma Pencarian',
                'dosen_pembuat_id'=> $dosen->id,
            ]
        );

        // Soal 3 (Esai)
        Soal::updateOrCreate(
            ['pertanyaan' => 'Jelaskan perbedaan mendasar antara supervised learning dan unsupervised learning!'],
            [
                'tipe_soal'       => 'esai',
                'kunci_jawaban'   => 'Supervised learning menggunakan data berlabel (input dan output yang diketahui) untuk melatih model, sedangkan unsupervised learning menggunakan data tanpa label untuk menemukan pola atau struktur tersembunyi.',
                'penjelasan'      => 'Perbedaan kuncinya terletak pada ada atau tidaknya label pada data training.',
                'level_kesulitan' => 'sedang',
                'kategori_soal'   => 'Machine Learning',
                'dosen_pembuat_id'=> $dosen->id,
            ]
        );

        // ===================================================================
        // SOAL UNTUK MATA KULIAH: TEORI BAHASA & OTOMATA
        // ===================================================================

        // Soal 4 (Pilihan Ganda)
        Soal::updateOrCreate(
            ['pertanyaan' => 'Mesin automata yang digunakan untuk mengenali bahasa reguler adalah...'],
            [
                'tipe_soal'       => 'pilihan_ganda',
                'opsi_jawaban'    => [
                    ['id' => 'A', 'teks' => 'Pushdown Automata (PDA)'],
                    ['id' => 'B', 'teks' => 'Mesin Turing'],
                    ['id' => 'C', 'teks' => 'Finite Automata (FA)'],
                    ['id' => 'D', 'teks' => 'Linear Bounded Automata (LBA)'],
                ],
                'kunci_jawaban'   => ['C'],
                'penjelasan'      => 'Finite Automata (baik DFA maupun NFA) adalah model komputasi yang tepat untuk mengenali bahasa dalam kelas bahasa reguler.',
                'level_kesulitan' => 'mudah',
                'kategori_soal'   => 'Finite Automata',
                'dosen_pembuat_id'=> $dosen->id,
            ]
        );

        // ===================================================================
        // SOAL UNTUK MATA KULIAH: BASIS DATA
        // ===================================================================

        // Soal 5 (Benar/Salah)
        Soal::updateOrCreate(
            ['pertanyaan' => 'Perintah "DROP TABLE" dan "TRUNCATE TABLE" memiliki fungsi yang sama persis dalam SQL.'],
            [
                'tipe_soal'       => 'benar_salah',
                'opsi_jawaban'    => [
                    ['id' => 'Benar', 'teks' => 'Benar'],
                    ['id' => 'Salah', 'teks' => 'Salah'],
                ],
                'kunci_jawaban'   => ['Salah'],
                'penjelasan'      => 'DROP TABLE menghapus seluruh struktur dan data tabel, sedangkan TRUNCATE TABLE hanya menghapus semua data (rows) tetapi struktur tabelnya tetap ada.',
                'level_kesulitan' => 'sedang',
                'kategori_soal'   => 'SQL DDL',
                'dosen_pembuat_id'=> $dosen->id,
            ]
        );

        $this->command->info('Seeder Soal telah selesai dijalankan.');
    }
}
