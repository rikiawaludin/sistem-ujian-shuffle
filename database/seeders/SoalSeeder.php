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
        $dosen = User::find(503);

        if (!$dosen) {
            $this->command->error('User dengan ID 503 tidak ditemukan.');
            return;
        }

        // ===================================================================
        // SOAL YANG SUDAH ADA
        // ===================================================================

        // Soal 1 (Pilihan Ganda) - Sejarah AI
        Soal::updateOrCreate(
            ['pertanyaan' => 'Siapakah yang dianggap sebagai "Bapak Kecerdasan Buatan"?'],
            [
                'tipe_soal' => 'pilihan_ganda', 'opsi_jawaban' => [['id' => 'A', 'teks' => 'Alan Turing'],['id' => 'B', 'teks' => 'John McCarthy'],['id' => 'C', 'teks' => 'Geoffrey Hinton'],['id' => 'D', 'teks' => 'Bill Gates'],],
                'kunci_jawaban' => ['B'], 'penjelasan' => 'John McCarthy adalah ilmuwan komputer yang pertama kali menciptakan istilah "Artificial Intelligence" pada tahun 1956.',
                'level_kesulitan' => 'mudah', 'kategori_soal' => 'Sejarah AI', 'dosen_pembuat_id'=> $dosen->id,
            ]
        );

        // Soal 2 (Benar/Salah) - Algoritma Pencarian
        Soal::updateOrCreate(
            ['pertanyaan' => 'Algoritma A* (A-Star) selalu menemukan jalur terpendek jika heuristik yang digunakan bersifat admissible.'],
            [
                'tipe_soal' => 'benar_salah', 'opsi_jawaban' => [['id' => 'Benar', 'teks' => 'Benar'],['id' => 'Salah', 'teks' => 'Salah'],],
                'kunci_jawaban' => ['Benar'], 'penjelasan' => 'Heuristik yang admissible (tidak pernah melebih-lebihkan biaya sebenarnya) menjamin bahwa A* akan menemukan solusi optimal.',
                'level_kesulitan' => 'sedang', 'kategori_soal' => 'Algoritma Pencarian', 'dosen_pembuat_id'=> $dosen->id,
            ]
        );

        // Soal 3 (Esai) - Machine Learning
        Soal::updateOrCreate(
            ['pertanyaan' => 'Jelaskan perbedaan mendasar antara supervised learning dan unsupervised learning!'],
            [
                'tipe_soal' => 'esai', 'kunci_jawaban' => 'Supervised learning menggunakan data berlabel, sedangkan unsupervised learning menggunakan data tanpa label untuk menemukan pola.',
                'penjelasan' => 'Perbedaan kuncinya terletak pada ada atau tidaknya label pada data training.',
                'level_kesulitan' => 'sedang', 'kategori_soal' => 'Machine Learning', 'dosen_pembuat_id'=> $dosen->id,
            ]
        );

        // Soal 4 (Pilihan Ganda) - Finite Automata
        Soal::updateOrCreate(
            ['pertanyaan' => 'Mesin automata yang digunakan untuk mengenali bahasa reguler adalah...'],
            [
                'tipe_soal' => 'pilihan_ganda', 'opsi_jawaban' => [['id' => 'A', 'teks' => 'Pushdown Automata (PDA)'],['id' => 'B', 'teks' => 'Mesin Turing'],['id' => 'C', 'teks' => 'Finite Automata (FA)'],['id' => 'D', 'teks' => 'Linear Bounded Automata (LBA)'],],
                'kunci_jawaban' => ['C'], 'penjelasan' => 'Finite Automata (baik DFA maupun NFA) adalah model komputasi yang tepat untuk mengenali bahasa dalam kelas bahasa reguler.',
                'level_kesulitan' => 'mudah', 'kategori_soal' => 'Finite Automata', 'dosen_pembuat_id'=> $dosen->id,
            ]
        );

        // Soal 5 (Benar/Salah) - SQL DDL
        Soal::updateOrCreate(
            ['pertanyaan' => 'Perintah "DROP TABLE" dan "TRUNCATE TABLE" memiliki fungsi yang sama persis dalam SQL.'],
            [
                'tipe_soal' => 'benar_salah', 'opsi_jawaban' => [['id' => 'Benar', 'teks' => 'Benar'],['id' => 'Salah', 'teks' => 'Salah'],],
                'kunci_jawaban' => ['Salah'], 'penjelasan' => 'DROP TABLE menghapus seluruh struktur dan data tabel, sedangkan TRUNCATE TABLE hanya menghapus semua data.',
                'level_kesulitan' => 'sedang', 'kategori_soal' => 'SQL DDL', 'dosen_pembuat_id'=> $dosen->id,
            ]
        );


        // ===================================================================
        // PENAMBAHAN SOAL BARU
        // ===================================================================

        // --- Kategori: Jaringan Komputer ---
        Soal::updateOrCreate(
            ['pertanyaan' => 'Apa kepanjangan dari OSI dalam konteks model jaringan?'],
            [
                'tipe_soal' => 'pilihan_ganda', 'opsi_jawaban' => [['id' => 'A', 'teks' => 'Open Source Initiative'],['id' => 'B', 'teks' => 'Open System Interconnection'],['id' => 'C', 'teks' => 'Optical Source Interlink'],['id' => 'D', 'teks' => 'Outbound System Interface'],],
                'kunci_jawaban' => ['B'], 'level_kesulitan' => 'mudah', 'kategori_soal' => 'Jaringan Komputer', 'dosen_pembuat_id'=> $dosen->id,
            ]
        );
        Soal::updateOrCreate(
            ['pertanyaan' => 'Layer manakah pada model OSI yang bertanggung jawab untuk enkripsi dan dekripsi data?'],
            [
                'tipe_soal' => 'pilihan_ganda', 'opsi_jawaban' => [['id' => 'A', 'teks' => 'Application Layer'],['id' => 'B', 'teks' => 'Transport Layer'],['id' => 'C', 'teks' => 'Session Layer'],['id' => 'D', 'teks' => 'Presentation Layer'],],
                'kunci_jawaban' => ['D'], 'level_kesulitan' => 'sedang', 'kategori_soal' => 'Jaringan Komputer', 'dosen_pembuat_id'=> $dosen->id,
            ]
        );
        Soal::updateOrCreate(
            ['pertanyaan' => 'Protokol TCP (Transmission Control Protocol) beroperasi pada Layer Transport.'],
            [
                'tipe_soal' => 'benar_salah', 'opsi_jawaban' => [['id' => 'Benar', 'teks' => 'Benar'],['id' => 'Salah', 'teks' => 'Salah'],],
                'kunci_jawaban' => ['Benar'], 'level_kesulitan' => 'mudah', 'kategori_soal' => 'Jaringan Komputer', 'dosen_pembuat_id'=> $dosen->id,
            ]
        );

        // --- Kategori: Sistem Operasi ---
        Soal::updateOrCreate(
            ['pertanyaan' => 'Kondisi di mana dua atau lebih proses saling menunggu sumber daya yang dipegang oleh proses lain disebut...'],
            [
                'tipe_soal' => 'pilihan_ganda', 'opsi_jawaban' => [['id' => 'A', 'teks' => 'Starvation'],['id' => 'B', 'teks' => 'Race Condition'],['id' => 'C', 'teks' => 'Deadlock'],['id' => 'D', 'teks' => 'Semaphore'],],
                'kunci_jawaban' => ['C'], 'level_kesulitan' => 'sedang', 'kategori_soal' => 'Sistem Operasi', 'dosen_pembuat_id'=> $dosen->id,
            ]
        );
        Soal::updateOrCreate(
            ['pertanyaan' => 'Jelaskan secara singkat apa itu "virtual memory" dalam sistem operasi!'],
            [
                'tipe_soal' => 'esai', 'kunci_jawaban' => 'Memori virtual adalah teknik manajemen memori yang memberikan ilusi kepada program bahwa ia memiliki memori utama yang berdekatan, padahal sebenarnya terfragmentasi dan mungkin disimpan di penyimpanan sekunder.',
                'level_kesulitan' => 'sulit', 'kategori_soal' => 'Sistem Operasi', 'dosen_pembuat_id'=> $dosen->id,
            ]
        );

        // --- Kategori: Struktur Data ---
        Soal::updateOrCreate(
            ['pertanyaan' => 'Struktur data yang menggunakan prinsip Last-In, First-Out (LIFO) adalah...'],
            [
                'tipe_soal' => 'pilihan_ganda', 'opsi_jawaban' => [['id' => 'A', 'teks' => 'Queue (Antrian)'],['id' => 'B', 'teks' => 'Stack (Tumpukan)'],['id' => 'C', 'teks' => 'Linked List'],['id' => 'D', 'teks' => 'Tree (Pohon)'],],
                'kunci_jawaban' => ['B'], 'level_kesulitan' => 'mudah', 'kategori_soal' => 'Struktur Data', 'dosen_pembuat_id'=> $dosen->id,
            ]
        );
        Soal::updateOrCreate(
            ['pertanyaan' => 'Sebuah "binary search tree" menjamin bahwa semua node di subtree kiri memiliki nilai lebih kecil dari root, dan semua node di subtree kanan memiliki nilai lebih besar.'],
            [
                'tipe_soal' => 'benar_salah', 'opsi_jawaban' => [['id' => 'Benar', 'teks' => 'Benar'],['id' => 'Salah', 'teks' => 'Salah'],],
                'kunci_jawaban' => ['Benar'], 'level_kesulitan' => 'sedang', 'kategori_soal' => 'Struktur Data', 'dosen_pembuat_id'=> $dosen->id,
            ]
        );

        // --- Kategori: Rekayasa Perangkat Lunak ---
        Soal::updateOrCreate(
            ['pertanyaan' => 'Model proses pengembangan perangkat lunak yang paling sekuensial dan klasik adalah...'],
            [
                'tipe_soal' => 'pilihan_ganda', 'opsi_jawaban' => [['id' => 'A', 'teks' => 'Agile'],['id' => 'B', 'teks' => 'Spiral'],['id' => 'C', 'teks' => 'Waterfall'],['id' => 'D', 'teks' => 'Scrum'],],
                'kunci_jawaban' => ['C'], 'level_kesulitan' => 'mudah', 'kategori_soal' => 'Rekayasa Perangkat Lunak', 'dosen_pembuat_id'=> $dosen->id,
            ]
        );
        Soal::updateOrCreate(
            ['pertanyaan' => 'Apa tujuan dari "code review" dalam siklus pengembangan perangkat lunak?'],
            [
                'tipe_soal' => 'esai', 'kunci_jawaban' => 'Tujuan utamanya adalah untuk menemukan dan memperbaiki kesalahan (bug), meningkatkan kualitas kode, serta berbagi pengetahuan antar anggota tim.',
                'level_kesulitan' => 'sedang', 'kategori_soal' => 'Rekayasa Perangkat Lunak', 'dosen_pembuat_id'=> $dosen->id,
            ]
        );
        
        // --- Kategori: Basis Data (Lanjutan) ---
        Soal::updateOrCreate(
            ['pertanyaan' => 'Proses untuk mengorganisir kolom dan tabel dalam database relasional untuk meminimalkan redundansi data disebut...'],
            [
                'tipe_soal' => 'pilihan_ganda', 'opsi_jawaban' => [['id' => 'A', 'teks' => 'Denormalisasi'],['id' => 'B', 'teks' => 'Indeksasi'],['id' => 'C', 'teks' => 'Normalisasi'],['id' => 'D', 'teks' => 'Fragmentasi'],],
                'kunci_jawaban' => ['C'], 'level_kesulitan' => 'sedang', 'kategori_soal' => 'Basis Data Lanjutan', 'dosen_pembuat_id'=> $dosen->id,
            ]
        );
        Soal::updateOrCreate(
            ['pertanyaan' => 'Primary Key dalam sebuah tabel harus unik dan tidak boleh bernilai NULL.'],
            [
                'tipe_soal' => 'benar_salah', 'opsi_jawaban' => [['id' => 'Benar', 'teks' => 'Benar'],['id' => 'Salah', 'teks' => 'Salah'],],
                'kunci_jawaban' => ['Benar'], 'level_kesulitan' => 'mudah', 'kategori_soal' => 'Basis Data Lanjutan', 'dosen_pembuat_id'=> $dosen->id,
            ]
        );
        
        // --- Kategori: Konsep OOP (Lanjutan) ---
        Soal::updateOrCreate(
            ['pertanyaan' => 'Kemampuan sebuah objek untuk mengambil banyak bentuk melalui method yang sama disebut...'],
            [
                'tipe_soal' => 'pilihan_ganda', 'opsi_jawaban' => [['id' => 'A', 'teks' => 'Inheritance'],['id' => 'B', 'teks' => 'Polimorfisme'],['id' => 'C', 'teks' => 'Enkapsulasi'],['id' => 'D', 'teks' => 'Abstraksi'],],
                'kunci_jawaban' => ['B'], 'level_kesulitan' => 'sedang', 'kategori_soal' => 'Konsep OOP', 'dosen_pembuat_id'=> $dosen->id,
            ]
        );
        Soal::updateOrCreate(
            ['pertanyaan' => 'Sebuah "abstract class" dapat diinstansiasi menjadi objek secara langsung.'],
            [
                'tipe_soal' => 'benar_salah', 'opsi_jawaban' => [['id' => 'Benar', 'teks' => 'Benar'],['id' => 'Salah', 'teks' => 'Salah'],],
                'kunci_jawaban' => ['Salah'], 'penjelasan' => 'Abstract class tidak bisa diinstansiasi, ia harus di-extend oleh class lain.',
                'level_kesulitan' => 'sedang', 'kategori_soal' => 'Konsep OOP', 'dosen_pembuat_id'=> $dosen->id,
            ]
        );
        
        // --- Kategori: Algoritma Pencarian (Lanjutan) ---
        Soal::updateOrCreate(
            ['pertanyaan' => 'Algoritma pencarian yang membagi dua larik data yang sudah terurut secara berulang hingga menemukan elemen yang dicari adalah...'],
            [
                'tipe_soal' => 'pilihan_ganda', 'opsi_jawaban' => [['id' => 'A', 'teks' => 'Linear Search'],['id' => 'B', 'teks' => 'Depth-First Search (DFS)'],['id' => 'C', 'teks' => 'Breadth-First Search (BFS)'],['id' => 'D', 'teks' => 'Binary Search'],],
                'kunci_jawaban' => ['D'], 'level_kesulitan' => 'sedang', 'kategori_soal' => 'Algoritma Pencarian', 'dosen_pembuat_id'=> $dosen->id,
            ]
        );

        $this->command->info('Seeder Soal telah selesai dijalankan dengan penambahan soal baru.');
    }
}