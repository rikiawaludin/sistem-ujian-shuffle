<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ujian', function (Blueprint $table) {
            // 1. Ubah nama 'status_publikasi' menjadi 'status' dengan tipe enum
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft')->after('tampilkan_hasil')->comment('Status administratif ujian');

            // 2. Tambahkan kolom baru 'visibilitas_hasil'
            $table->boolean('visibilitas_hasil')->default(true)->after('status')->comment('Apakah hasil bisa dilihat mahasiswa setelah selesai');
        });

        // Pindahkan data lama jika ada (opsional tapi disarankan)
        \Illuminate\Support\Facades\DB::statement("UPDATE ujian SET status = 'published' WHERE status_publikasi = 'published'");

        Schema::table('ujian', function (Blueprint $table) {
            // 3. Hapus kolom lama
            $table->dropColumn('status_publikasi');
        });
    }

    public function down(): void
    {
        Schema::table('ujian', function (Blueprint $table) {
            $table->string('status_publikasi')->default('published');
            \Illuminate\Support\Facades\DB::statement("UPDATE ujian SET status_publikasi = status");
            $table->dropColumn('status');
            $table->dropColumn('visibilitas_hasil');
        });
    }
};