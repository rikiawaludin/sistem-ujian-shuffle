<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Soal;
use App\Models\User;

class SoalSeeder extends Seeder
{
    public function run(): void
    {
        $dosenWeb = User::where('email', 'siti.dosen@example.com')->first(); // Asumsi Dosen Siti mengajar Web

        // Soal untuk Mata Kuliah Pemrograman Web Lanjut (ID: 1)
        // Soal 1 (PG)
        Soal::updateOrCreate(
            ['pertanyaan' => 'Framework PHP populer yang menggunakan pola desain MVC adalah...'],
            [
                'tipe_soal' => 'pilihan_ganda',
                'opsi_jawaban' => json_encode([
                    ['id' => 'A', 'teks' => 'React'],
                    ['id' => 'B', 'teks' => 'Laravel'], // Jawaban Benar
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

        // Soal 2 (PG)
        Soal::updateOrCreate(
            ['pertanyaan' => 'Manakah dari berikut ini yang BUKAN merupakan HTTP Method?'],
            [
                'tipe_soal' => 'pilihan_ganda',
                'opsi_jawaban' => json_encode([
                    ['id' => 'A', 'teks' => 'GET'],
                    ['id' => 'B', 'teks' => 'POST'],
                    ['id' => 'C', 'teks' => 'UPDATE'], // Jawaban Benar (PUT/PATCH yang umum)
                    ['id' => 'D', 'teks' => 'DELETE'],
                ]),
                'kunci_jawaban' => json_encode(['C']),
                'penjelasan' => 'Metode HTTP standar adalah GET, POST, PUT, DELETE, PATCH, HEAD, OPTIONS, dll. UPDATE bukan standar.',
                'level_kesulitan' => 'mudah',
                'kategori_soal' => 'Konsep HTTP',
                'dosen_pembuat_id' => $dosenWeb->id,
            ]
        );

        // Soal 3 (Esai)
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

        // Soal 4 (Benar/Salah)
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
        
        // Soal 5 (Pilihan Ganda)
        Soal::updateOrCreate(
            ['pertanyaan' => 'Manakah yang digunakan untuk mengelola state dalam aplikasi React skala besar?'],
            [
                'tipe_soal' => 'pilihan_ganda',
                'opsi_jawaban' => json_encode([
                    ['id' => 'A', 'teks' => 'useState Hook saja'],
                    ['id' => 'B', 'teks' => 'Redux atau Zustand'], // Jawaban Benar
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
    }
}