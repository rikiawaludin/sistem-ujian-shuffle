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
            // Tambahkan kolom 'external_user_id'
            // Sesuaikan posisi 'after' dengan kolom yang relevan, misalnya 'id' atau 'name'
            $table->string('external_user_id')->unique()->nullable()->after('id'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Hati-hati saat drop unique index jika ada data, atau drop index dulu baru kolom
            // $table->dropUnique(['external_user_id']); // Jika diperlukan
            $table->dropColumn('external_user_id');
        });
    }
};