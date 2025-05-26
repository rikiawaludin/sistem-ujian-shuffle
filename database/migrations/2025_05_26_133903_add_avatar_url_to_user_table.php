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
        Schema::table('users', function (Blueprint $table) { // Pastikan nama tabel 'users'
            // Tambahkan kolom 'avatar_url'
            // Sesuaikan posisi 'after' jika perlu, misalnya setelah 'role' atau 'email'
            $table->string('avatar_url')->nullable()->after('role'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('avatar_url');
        });
    }
};