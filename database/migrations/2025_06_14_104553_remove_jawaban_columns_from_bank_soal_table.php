<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bank_soal', function (Blueprint $table) {
            // Hapus kolom-kolom yang tidak lagi digunakan
            $table->dropColumn(['opsi_jawaban', 'kunci_jawaban']);
        });
    }

    public function down()
    {
        Schema::table('bank_soal', function (Blueprint $table) {
            // Jika rollback, tambahkan kembali kolomnya
            $table->json('opsi_jawaban')->nullable();
            $table->json('kunci_jawaban')->nullable();
        });
    }
};