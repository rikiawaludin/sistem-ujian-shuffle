<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bank_soal', function (Blueprint $table) {
            $table->id(); // Primary Key, Integer, Auto Increment
            $table->text('pertanyaan'); // Isi pertanyaan
            
            // Menggunakan string untuk tipe soal, bisa juga menggunakan enum jika didukung dan diinginkan
            $table->string('tipe_soal'); // Contoh: 'pilihan_ganda', 'esai', 'benar_salah', dll.
            
            $table->json('opsi_jawaban')->nullable(); // Untuk pilihan ganda, dll. (format: [{id: 'A', teks: '...'}, ...])
            $table->json('kunci_jawaban')->nullable(); // Jawaban yang benar
            $table->json('pasangan')->nullable(); // Untuk tipe soal menjodohkan (format: [{kiri: '...', kanan: '...'}, ...])
            $table->text('penjelasan')->nullable(); // Penjelasan atau pembahasan soal
            $table->string('level_kesulitan')->nullable(); // Contoh: 'mudah', 'sedang', 'sulit' atau C1-C6
            $table->string('kategori_soal')->nullable(); // Contoh: "Bab 1", "Turunan"
            $table->string('gambar_url')->nullable(); // Path atau URL ke gambar pendukung
            $table->string('audio_url')->nullable(); // Path atau URL ke audio pendukung
            $table->string('video_url')->nullable(); // Path atau URL ke video pendukung
            
            // Foreign Key ke tabel users (dosen pembuat soal)
            // $table->foreignId('dosen_pembuat_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_soal');
    }
};
