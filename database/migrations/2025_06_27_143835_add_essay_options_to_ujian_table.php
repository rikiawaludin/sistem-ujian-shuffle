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
        Schema::table('ujian', function (Blueprint $table) {
            $table->boolean('sertakan_esai')->default(false)->after('visibilitas_hasil');
            $table->unsignedTinyInteger('persentase_esai')->nullable()->after('sertakan_esai');
        });
    }

    public function down(): void
    {
        Schema::table('ujian', function (Blueprint $table) {
            $table->dropColumn(['sertakan_esai', 'persentase_esai']);
        });
    }
};
