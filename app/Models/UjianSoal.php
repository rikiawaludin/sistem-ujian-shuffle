<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot; // Penting untuk model pivot

class UjianSoal extends Pivot
{
    // Jika Anda ingin timestamps otomatis (created_at, updated_at) di tabel pivot
    // public $timestamps = true;

    protected $table = 'ujian_soal';

    // Anda bisa menambahkan fillable jika ada kolom tambahan di pivot yang ingin di-mass assign
    // protected $fillable = ['nomor_urut_di_ujian', 'bobot_nilai_soal'];

    // Relasi ke Ujian (opsional, biasanya diakses via relasi belongsToMany di Ujian/Soal)
    public function ujian()
    {
        return $this->belongsTo(Ujian::class);
    }

    // Relasi ke Soal (opsional)
    public function soal()
    {
        return $this->belongsTo(Soal::class);
    }
}