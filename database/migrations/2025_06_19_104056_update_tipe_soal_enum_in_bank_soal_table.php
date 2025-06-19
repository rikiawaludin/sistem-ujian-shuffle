<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // <-- 1. Tambahkan impor DB

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 2. Definisikan tipe soal baru dan lama
        $tipeSoalBaru = [
            'pilihan_ganda', 
            'benar_salah', 
            'esai', 
            'pilihan_jawaban_ganda',
            'isian_singkat',
            'menjodohkan'
        ];
        // Buat string untuk query SQL
        $enumList = "'" . implode("','", $tipeSoalBaru) . "'";

        // 3. Jalankan query mentah untuk mengubah kolom
        DB::statement("ALTER TABLE bank_soal MODIFY COLUMN tipe_soal ENUM({$enumList}) NOT NULL DEFAULT 'pilihan_ganda'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Logika untuk mengembalikan jika migration di-rollback
        $tipeSoalLama = ['pilihan_ganda', 'benar_salah', 'esai'];
        $enumListLama = "'" . implode("','", $tipeSoalLama) . "'";

        DB::statement("ALTER TABLE bank_soal MODIFY COLUMN tipe_soal ENUM({$enumListLama}) NOT NULL DEFAULT 'pilihan_ganda'");
    }
};