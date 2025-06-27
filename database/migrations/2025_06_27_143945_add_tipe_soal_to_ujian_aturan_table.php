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
        Schema::table('ujian_aturan', function (Blueprint $table) {
            // 'non_esai' untuk soal biasa, 'esai' untuk soal esai
            $table->string('tipe_soal')->default('non_esai')->after('ujian_id');
        });
    }

    public function down(): void
    {
        Schema::table('ujian_aturan', function (Blueprint $table) {
            $table->dropColumn('tipe_soal');
        });
    }
};
