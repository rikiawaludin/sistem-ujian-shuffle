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
        Schema::create('mata_kuliah_user', function (Blueprint $table) {
            $table->id(); // Primary Key untuk tabel pivot ini

            // Foreign Key ke tabel users
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Foreign Key ke tabel mata_kuliah
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliah')->onDelete('cascade');

            $table->timestamp('tanggal_pendaftaran')->useCurrent(); // Tanggal kapan user mendaftar ke mata kuliah, defaultnya waktu saat ini
            $table->string('status_progres')->nullable(); // Contoh: "Belum Mulai", "Sedang Berlangsung", "Selesai"
            
            $table->timestamps(); // Kolom created_at dan updated_at

            // Membuat kombinasi user_id dan mata_kuliah_id unik untuk mencegah duplikasi pendaftaran
            $table->unique(['user_id', 'mata_kuliah_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mata_kuliah_user');
    }
};
