<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ujian', function (Blueprint $table) {
            // Menambahkan kolom foreign key ke tabel users
            $table->foreignId('dosen_pembuat_id')
                  ->nullable() // Bisa null jika dosen dihapus, ujiannya tidak ikut terhapus
                  ->after('mata_kuliah_id') // Penempatan kolom (opsional)
                  ->constrained('users') // Menghubungkan ke kolom 'id' di tabel 'users'
                  ->onDelete('set null'); // Jika dosen dihapus, set ID ini menjadi NULL
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ujian', function (Blueprint $table) {
            // Hapus foreign key constraint terlebih dahulu
            $table->dropForeign(['dosen_pembuat_id']);
            // Kemudian hapus kolomnya
            $table->dropColumn('dosen_pembuat_id');
        });
    }
};