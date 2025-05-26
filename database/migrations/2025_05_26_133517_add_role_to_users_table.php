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
        Schema::table('users', function (Blueprint $table) {
            // Tambahkan kolom 'role' setelah kolom tertentu, misalnya 'email' atau 'password'
            // Jika kolom 'email' sudah ada:
            $table->string('role')->default('siswa')->after('email'); // Atau sesuaikan posisi 'after'
            // Alternatif jika ingin menggunakan enum dan database Anda mendukungnya:
            // $table->enum('role', ['siswa', 'dosen', 'admin'])->default('siswa')->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};