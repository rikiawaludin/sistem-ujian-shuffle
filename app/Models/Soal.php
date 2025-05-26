<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Soal extends Model
{
    use HasFactory;

    protected $table = 'soal';

    protected $fillable = [
        'pertanyaan',
        'tipe_soal',
        'opsi_jawaban',
        'kunci_jawaban',
        'pasangan', // Jika Anda menggunakan tipe 'menjodohkan'
        'penjelasan',
        'level_kesulitan', // Sebelumnya 'level_soal' di Prisma Anda
        'kategori_soal',
        'gambar_url',
        'audio_url',
        'video_url',
        'dosen_pembuat_id',
    ];

    protected $casts = [
        'opsi_jawaban' => 'json', // Atau 'array' jika Anda prefer
        'kunci_jawaban' => 'json', // Atau 'array'
        'pasangan' => 'json',    // Atau 'array'
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Dosen yang membuat soal ini.
     */
    public function dosenPembuat()
    {
        return $this->belongsTo(User::class, 'dosen_pembuat_id');
    }

    /**
     * Ujian-ujian yang menggunakan soal ini (melalui tabel pivot ujian_soal).
     */
    public function ujian() // Nama relasi bisa juga 'ujians'
    {
        return $this->belongsToMany(Ujian::class, 'ujian_soal')
                    ->withTimestamps()
                    ->withPivot('nomor_urut_di_ujian', 'bobot_nilai_soal');
    }

    /**
     * Detail jawaban peserta untuk soal ini.
     */
    public function jawabanPesertaDetail()
    {
        return $this->hasMany(JawabanPesertaDetail::class, 'soal_id');
    }
}
