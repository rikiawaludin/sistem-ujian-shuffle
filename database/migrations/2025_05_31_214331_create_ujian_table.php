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
        Schema::create('ujian', function (Blueprint $table) {
            $table->id(); // Primary Key, Integer, Auto Increment
            
            // Foreign Key ke tabel mata_kuliah
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliah')->onDelete('cascade'); // Jika mata kuliah dihapus, ujian terkait juga dihapus

            $table->string('judul_ujian');
            $table->text('deskripsi')->nullable();
            $table->integer('durasi'); // Misal dalam menit
            $table->integer('kkm')->nullable();
            $table->timestamp('tanggal_mulai')->nullable();
            $table->timestamp('tanggal_selesai')->nullable();
            $table->string('jenis_ujian')->nullable(); // Bisa juga enum: 'kuis', 'uts', 'uas', 'praktikum'
            $table->boolean('acak_soal')->default(false);
            $table->boolean('acak_opsi')->default(false);
            $table->string('tampilkan_hasil')->default('setelah_selesai'); // enum: 'langsung', 'setelah_selesai', 'manual_dosen'
            $table->string('status_publikasi')->default('draft'); // enum: 'draft', 'terbit', 'arsip'
            
            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ujian');
    }
};
