<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ujian_aturan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_id')->constrained('ujian')->onDelete('cascade');
            $table->string('level_kesulitan'); // e.g., 'mudah', 'sedang', 'sulit'
            $table->unsignedInteger('jumlah_soal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ujian_aturan');
    }
};