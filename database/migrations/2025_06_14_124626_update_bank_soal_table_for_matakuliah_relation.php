<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('bank_soal', function (Blueprint $table) {
            // Hapus kolom lama jika ada dan tidak terpakai lagi
            $table->dropColumn('kategori_soal');
            
            // Tambahkan kolom foreign key baru setelah dosen_pembuat_id
            $table->foreignId('mata_kuliah_id')->nullable()->after('dosen_pembuat_id')->constrained('mata_kuliah')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('bank_soal', function (Blueprint $table) {
            $table->dropForeign(['mata_kuliah_id']);
            $table->dropColumn('mata_kuliah_id');
            $table->string('kategori_soal', 100)->nullable(); // Kembalikan kolom lama jika rollback
        });
    }
};
