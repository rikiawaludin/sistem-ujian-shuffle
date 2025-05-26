<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\MataKuliah;

class MataKuliahUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $siswa1 = User::where('email', 'andi.siswa@example.com')->first();
        $siswa2 = User::where('email', 'citra.siswa@example.com')->first();
        $siswa3 = User::where('email', 'eka.siswa@example.com')->first();

        $mk1 = MataKuliah::where('kode_mata_kuliah', 'KAL001')->first();
        $mk2 = MataKuliah::where('kode_mata_kuliah', 'PBO001')->first();
        $mk3 = MataKuliah::where('kode_mata_kuliah', 'SDA001')->first();

        if ($siswa1 && $mk1) {
            DB::table('mata_kuliah_user')->insert([
                'user_id' => $siswa1->id,
                'mata_kuliah_id' => $mk1->id,
                'tanggal_pendaftaran' => now(),
                'status_progres' => 'Sedang Berlangsung',
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        if ($siswa1 && $mk2) {
            DB::table('mata_kuliah_user')->insert([
                'user_id' => $siswa1->id,
                'mata_kuliah_id' => $mk2->id,
                'tanggal_pendaftaran' => now(),
                'status_progres' => 'Belum Mulai',
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        if ($siswa2 && $mk1) {
            DB::table('mata_kuliah_user')->insert([
                'user_id' => $siswa2->id,
                'mata_kuliah_id' => $mk1->id,
                'tanggal_pendaftaran' => now(),
                'status_progres' => 'Sedang Berlangsung',
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        if ($siswa2 && $mk3) {
            DB::table('mata_kuliah_user')->insert([
                'user_id' => $siswa2->id,
                'mata_kuliah_id' => $mk3->id,
                'tanggal_pendaftaran' => now(),
                'status_progres' => 'Selesai',
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        if ($siswa3 && $mk2) {
            DB::table('mata_kuliah_user')->insert([
                'user_id' => $siswa3->id,
                'mata_kuliah_id' => $mk2->id,
                'tanggal_pendaftaran' => now(),
                'status_progres' => 'Sedang Berlangsung',
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }
}