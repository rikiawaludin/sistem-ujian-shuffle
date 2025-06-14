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
        'pasangan',
        'penjelasan',
        'level_kesulitan',
        'mata_kuliah_id',
        'gambar_url',
        'audio_url',
        'video_url',
        'dosen_pembuat_id',
    ];

    protected $casts = [
        'pasangan' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke semua opsi jawaban milik soal ini.
     */
    public function opsiJawaban()
    {
        return $this->hasMany(OpsiJawaban::class, 'soal_id');
    }
    
    /**
     * Relasi untuk mengambil satu opsi yang merupakan kunci jawaban.
     */
    public function kunciJawaban()
    {
        return $this->hasOne(OpsiJawaban::class, 'soal_id')->where('is_kunci_jawaban', true);
    }

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

    /**
     * Definisikan relasi ke MataKuliah.
     */
    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }
}