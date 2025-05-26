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
        Schema::create('ujian_soal', function (Blueprint $table) {
            $table->id(); // Primary Key untuk tabel pivot ini

            // Foreign Key ke tabel ujian
            $table->foreignId('ujian_id')->constrained('ujian')->onDelete('cascade'); // Jika ujian dihapus, relasi ini juga dihapus

            // Foreign Key ke tabel soal
            $table->foreignId('soal_id')->constrained('soal')->onDelete('cascade'); // Jika soal dihapus, relasi ini juga dihapus

            $table->integer('nomor_urut_di_ujian')->nullable(); // Jika Anda memerlukan urutan soal yang spesifik dalam ujian
            $table->integer('bobot_nilai_soal')->nullable(); // Jika setiap soal dalam ujian memiliki bobot nilai yang berbeda

            $table->timestamps(); // Kolom created_at dan updated_at

            // Untuk memastikan kombinasi ujian_id dan soal_id unik (opsional, tapi direkomendasikan jika satu soal hanya bisa muncul sekali dalam satu ujian)
            // $table->unique(['ujian_id', 'soal_id']);
            
            // Atau jika satu soal bisa muncul berkali-kali tapi dengan nomor urut berbeda
            // $table->unique(['ujian_id', 'nomor_urut_di_ujian']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ujian_soal');
    }
};
