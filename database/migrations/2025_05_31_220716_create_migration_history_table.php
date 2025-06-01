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
        Schema::create('migration_history', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_dosen')->default(false);
            $table->boolean('is_prodi')->default(false);
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_mahasiswa')->default(false);
            $table->boolean('is_mata_kuliah')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('migration_history');
    }
};
