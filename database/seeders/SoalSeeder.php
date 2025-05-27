<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Soal;
use App\Models\User;

class SoalSeeder extends Seeder
{
    public function run(): void
    {
        $dosenWeb = User::where('email', 'siti.dosen@example.com')->first();
        $dosenMat = User::where('email', 'budi.dosen@example.com')->first(); // Asumsi Dosen Budi mengajar Kalkulus/SDA

        if (!$dosenWeb || !$dosenMat) {
            $this->command->error('Dosen tidak ditemukan. Pastikan UserSeeder dijalankan terlebih dahulu.');
            return;
        }

        // Soal untuk Mata Kuliah Pemrograman Web Lanjut (atau umum)
        // Soal 1 (PG) - Kategori: Framework PHP
        Soal::updateOrCreate(
            ['pertanyaan' => 'Framework PHP populer yang menggunakan pola desain MVC adalah...'],
            [
                'tipe_soal' => 'pilihan_ganda',
                'opsi_jawaban' => json_encode([
                    ['id' => 'A', 'teks' => 'React'],
                    ['id' => 'B', 'teks' => 'Laravel'],
                    ['id' => 'C', 'teks' => 'jQuery'],
                    ['id' => 'D', 'teks' => 'Node.js'],
                ]),
                'kunci_jawaban' => json_encode(['B']),
                'penjelasan' => 'Laravel adalah framework PHP yang sangat populer dan mengikuti pola MVC.',
                'level_kesulitan' => 'sedang',
                'kategori_soal' => 'Framework PHP',
                'dosen_pembuat_id' => $dosenWeb->id,
            ]
        );

        // Soal 2 (PG) - Kategori: Konsep HTTP
        Soal::updateOrCreate(
            ['pertanyaan' => 'Manakah dari berikut ini yang BUKAN merupakan HTTP Method?'],
            [
                'tipe_soal' => 'pilihan_ganda',
                'opsi_jawaban' => json_encode([
                    ['id' => 'A', 'teks' => 'GET'],
                    ['id' => 'B', 'teks' => 'POST'],
                    ['id' => 'C', 'teks' => 'UPDATE'],
                    ['id' => 'D', 'teks' => 'DELETE'],
                ]),
                'kunci_jawaban' => json_encode(['C']),
                'penjelasan' => 'Metode HTTP standar adalah GET, POST, PUT, DELETE, PATCH, HEAD, OPTIONS, dll. UPDATE bukan standar.',
                'level_kesulitan' => 'mudah',
                'kategori_soal' => 'Konsep HTTP',
                'dosen_pembuat_id' => $dosenWeb->id,
            ]
        );

        // Soal 3 (Esai) - Kategori: API
        Soal::updateOrCreate(
            ['pertanyaan' => 'Jelaskan secara singkat apa itu REST API!'],
            [
                'tipe_soal' => 'esai',
                'kunci_jawaban' => json_encode('REST (Representational State Transfer) API adalah gaya arsitektur untuk merancang aplikasi jaringan. Ia menggunakan HTTP request untuk mengakses dan menggunakan data.'),
                'penjelasan' => 'REST API biasanya stateless dan menggunakan metode HTTP standar.',
                'level_kesulitan' => 'sedang',
                'kategori_soal' => 'API',
                'dosen_pembuat_id' => $dosenWeb->id,
            ]
        );

        // Soal 4 (Benar/Salah) - Kategori: Dasar Web
        Soal::updateOrCreate(
            ['pertanyaan' => 'CSS adalah singkatan dari Cascading Style Sheets.'],
            [
                'tipe_soal' => 'benar_salah',
                'opsi_jawaban' => json_encode([['id' => 'Benar', 'teks' => 'Benar'], ['id' => 'Salah', 'teks' => 'Salah']]),
                'kunci_jawaban' => json_encode(['Benar']),
                'level_kesulitan' => 'mudah',
                'kategori_soal' => 'Dasar Web',
                'dosen_pembuat_id' => $dosenWeb->id,
            ]
        );
        
        // Soal 5 (Pilihan Ganda) - Kategori: React State Management
        Soal::updateOrCreate(
            ['pertanyaan' => 'Manakah yang digunakan untuk mengelola state dalam aplikasi React skala besar?'],
            [
                'tipe_soal' => 'pilihan_ganda',
                'opsi_jawaban' => json_encode([
                    ['id' => 'A', 'teks' => 'useState Hook saja'],
                    ['id' => 'B', 'teks' => 'Redux atau Zustand'],
                    ['id' => 'C', 'teks' => 'HTML Local Storage'],
                    ['id' => 'D', 'teks' => 'CSS Variables'],
                ]),
                'kunci_jawaban' => json_encode(['B']),
                'penjelasan' => 'Redux, Zustand, atau Context API yang lebih canggih sering digunakan untuk manajemen state global di aplikasi React besar.',
                'level_kesulitan' => 'sedang',
                'kategori_soal' => 'React State Management',
                'dosen_pembuat_id' => $dosenWeb->id,
            ]
        );

        // Tambahan Soal untuk Mata Kuliah lain yang dicari di JawabanPesertaDetailSeeder
        // Soal untuk Kalkulus Lanjutan (kategori: Turunan)
        Soal::updateOrCreate(
            ['pertanyaan' => 'Hitung turunan pertama dari fungsi f(x) = 3x^2 + 2x - 5.'],
            [
                'tipe_soal' => 'esai',
                'kunci_jawaban' => json_encode('6x + 2'),
                'penjelasan' => 'Menggunakan aturan turunan daya dan konstanta.',
                'level_kesulitan' => 'mudah',
                'kategori_soal' => 'Turunan', // Kategori ini dicari di JawabanPesertaDetailSeeder
                'dosen_pembuat_id' => $dosenMat->id,
            ]
        );

        // Soal untuk PBO (kategori: Konsep Dasar OOP)
        Soal::updateOrCreate(
            ['pertanyaan' => 'Prinsip OOP yang memungkinkan objek yang berbeda merespons metode yang sama dengan cara yang berbeda disebut...'],
            [
                'tipe_soal' => 'pilihan_ganda',
                'opsi_jawaban' => json_encode([
                    ['id' => 'A', 'teks' => 'Enkapsulasi'],
                    ['id' => 'B', 'teks' => 'Inheritansi'],
                    ['id' => 'C', 'teks' => 'Polimorfisme'], // Benar
                    ['id' => 'D', 'teks' => 'Abstraksi'],
                ]),
                'kunci_jawaban' => json_encode(['C']),
                'penjelasan' => 'Polimorfisme memungkinkan satu antarmuka untuk berbagai implementasi.',
                'level_kesulitan' => 'sedang',
                'kategori_soal' => 'Konsep Dasar OOP', // Kategori ini dicari
                'dosen_pembuat_id' => $dosenWeb->id, // Dosen Siti mengajar PBO
            ]
        );

        // Soal untuk PBO (kategori: Enkapsulasi OOP)
        Soal::updateOrCreate(
            ['pertanyaan' => 'Jelaskan konsep enkapsulasi dalam Pemrograman Berorientasi Objek.'],
            [
                'tipe_soal' => 'esai',
                'kunci_jawaban' => json_encode('Enkapsulasi adalah pembungkusan data dan metode yang beroperasi pada data tersebut dalam satu unit. Ini menyembunyikan detail implementasi dan mencegah akses langsung ke data.'),
                'penjelasan' => 'Salah satu pilar OOP untuk keamanan dan pemeliharaan kode.',
                'level_kesulitan' => 'sedang',
                'kategori_soal' => 'Enkapsulasi OOP', // Kategori ini dicari
                'dosen_pembuat_id' => $dosenWeb->id, // Dosen Siti mengajar PBO
            ]
        );

        // Soal untuk Struktur Data (kategori: Struktur Data)
        Soal::updateOrCreate(
            ['pertanyaan' => 'Manakah struktur data yang menggunakan prinsip LIFO (Last-In, First-Out)?'],
            [
                'tipe_soal' => 'pilihan_ganda', // Mengubah menjadi pilihan ganda agar bisa diisi di JawabanPesertaDetailSeeder
                'opsi_jawaban' => json_encode([
                    ['id' => 'A', 'teks' => 'Queue'],
                    ['id' => 'B', 'teks' => 'Stack'], // Benar
                    ['id' => 'C', 'teks' => 'Linked List'],
                    ['id' => 'D', 'teks' => 'Tree'],
                ]),
                'kunci_jawaban' => json_encode(['B']),
                'penjelasan' => 'Stack mengikuti prinsip LIFO, di mana elemen terakhir yang ditambahkan adalah yang pertama dihapus.',
                'level_kesulitan' => 'mudah',
                'kategori_soal' => 'Struktur Data', // Kategori ini dicari
                'dosen_pembuat_id' => $dosenMat->id,
            ]
        );
    }
}