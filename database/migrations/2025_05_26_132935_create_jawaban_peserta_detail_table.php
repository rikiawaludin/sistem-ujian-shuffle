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
        Schema::create('jawaban_peserta_detail', function (Blueprint $table) {
            $table->id(); // Primary Key

            // Foreign Key ke tabel pengerjaan_ujian
            $table->foreignId('pengerjaan_ujian_id')->constrained('pengerjaan_ujian')->onDelete('cascade');

            // Foreign Key ke tabel soal
            $table->foreignId('soal_id')->constrained('soal')->onDelete('cascade');

            $table->text('jawaban_user')->nullable(); // Jawaban dari pengguna (bisa teks untuk esai, atau JSON untuk pilihan ganda/menjodohkan)
            $table->boolean('is_benar')->nullable(); // Status kebenaran jawaban, bisa null jika belum dinilai (misal esai)
            $table->decimal('skor_per_soal', 5, 2)->nullable(); // Skor yang didapat untuk soal ini
            $table->boolean('is_ragu_ragu')->default(false); // Apakah pengguna menandai soal ini sebagai ragu-ragu
            $table->integer('waktu_jawab_detik')->nullable(); // Waktu yang dihabiskan untuk menjawab soal ini (opsional)
            
            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jawaban_peserta_detail');
    }
};
