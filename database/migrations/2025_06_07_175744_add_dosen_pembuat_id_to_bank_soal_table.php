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
        Schema::table('bank_soal', function (Blueprint $table) {
            // Menambahkan kolom foreign key untuk dosen pembuat
            $table->foreignId('dosen_pembuat_id')
                  ->nullable() // atau ->constrained() jika Anda ingin foreign key constraint
                  ->after('video_url'); // Atur posisi kolom jika perlu
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_soal', function (Blueprint $table) {
            // Logika untuk menghapus kolom jika migration di-rollback
            $table->dropColumn('dosen_pembuat_id');
        });
    }
};