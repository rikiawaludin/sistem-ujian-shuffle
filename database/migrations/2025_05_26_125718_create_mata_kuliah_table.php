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
        Schema::create('mata_kuliah', function (Blueprint $table) {
            $table->id(); // Kolom ID (Primary Key, BigInt, Unsigned, Auto Increment)
            $table->string('nama_mata_kuliah'); // Kolom untuk nama mata kuliah
            $table->string('kode_mata_kuliah')->unique()->nullable(); // Kode unik, bisa null
            $table->text('deskripsi')->nullable(); // Deskripsi mata kuliah, bisa null
            
            // Kolom Foreign Key untuk dosen_id
            // 'users' adalah nama tabel users Anda.
            // onDelete('set null') berarti jika dosen dihapus, kolom dosen_id di mata_kuliah akan menjadi NULL.
            // Anda bisa juga menggunakan onDelete('cascade') jika ingin mata kuliah ikut terhapus saat dosen dihapus (hati-hati dengan ini).
            $table->foreignId('dosen_id')->nullable()->constrained('users')->onDelete('set null'); 
            
            $table->string('icon_url')->nullable(); // Path atau URL ke ikon mata kuliah, bisa null
            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mata_kuliah');
    }
};