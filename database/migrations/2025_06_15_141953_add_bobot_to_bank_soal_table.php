<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bank_soal', function (Blueprint $table) {
            // Tambahkan kolom bobot setelah level_kesulitan
            // default(10) berarti jika tidak diisi, nilainya akan 10.
            $table->unsignedInteger('bobot')->default(10)->after('level_kesulitan');
        });
    }

    public function down(): void
    {
        Schema::table('bank_soal', function (Blueprint $table) {
            $table->dropColumn('bobot');
        });
    }
};