<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('opsi_jawaban', function (Blueprint $table) {
            $table->id();
            // Foreign key yang menghubungkan ke tabel bank_soal
            $table->foreignId('soal_id')->constrained('bank_soal')->onDelete('cascade');
            $table->text('teks_opsi');
            // Kolom boolean untuk menandai mana yang merupakan kunci jawaban
            $table->boolean('is_kunci_jawaban')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('opsi_jawaban');
    }
};