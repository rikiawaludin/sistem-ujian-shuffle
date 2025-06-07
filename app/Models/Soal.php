<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Soal extends Model
{
    use HasFactory;

    protected $table = 'bank_soal';

    protected $fillable = [
        'pertanyaan',
        'tipe_soal',
        'opsi_jawaban', // Kolom ini yang kita fokuskan
        'kunci_jawaban',
        'pasangan',
        'penjelasan',
        'level_kesulitan',
        'kategori_soal',
        'gambar_url',
        'audio_url',
        'video_url',
        'dosen_pembuat_id',
    ];

    protected $casts = [
        'opsi_jawaban' => 'json', // <-- PASTIKAN INI BENAR DAN AKTIF
        'kunci_jawaban' => 'json',
        'pasangan' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ... relasi Anda ...
    public function dosenPembuat()
    {
        return $this->belongsTo(User::class, 'dosen_pembuat_id');
    }

    public function ujian()
    {
        return $this->belongsToMany(Ujian::class, 'ujian_soal')
                    ->withTimestamps()
                    ->withPivot('nomor_urut_di_ujian', 'bobot_nilai_soal');
    }

    public function jawabanPesertaDetail()
    {
        return $this->hasMany(JawabanPesertaDetail::class, 'soal_id');
    }
}