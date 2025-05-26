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
        Schema::create('pengerjaan_ujian', function (Blueprint $table) {
            $table->id(); // Primary Key, bisa juga $table->uuid('id')->primary();

            // Foreign Key ke tabel ujian
            $table->foreignId('ujian_id')->constrained('ujian')->onDelete('cascade');

            // Foreign Key ke tabel users (siswa yang mengerjakan)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->timestamp('waktu_mulai')->nullable();
            $table->timestamp('waktu_selesai')->nullable();
            $table->integer('waktu_dihabiskan_detik')->nullable(); // Durasi pengerjaan dalam detik
            $table->decimal('skor_total', 5, 2)->nullable(); // Skor dengan 2 angka desimal, misal 85.75
            
            // Status pengerjaan ujian
            $table->string('status_pengerjaan')->default('belum_mulai'); // Contoh: 'belum_mulai', 'sedang_dikerjakan', 'selesai', 'waktu_habis'
            
            $table->ipAddress('ip_address')->nullable(); // IP address pengguna saat mengerjakan
            $table->string('user_agent')->nullable(); // User agent browser pengguna
            
            $table->timestamps(); // created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengerjaan_ujian');
    }
};
