<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ujian extends Model
{
    use HasFactory;

    protected $table = 'ujian';

    protected $fillable = [
        'mata_kuliah_id',
        'dosen_pembuat_id',
        'judul_ujian',
        'deskripsi',
        'durasi', // dalam menit atau detik, pastikan konsisten
        'kkm',
        'tanggal_mulai',
        'tanggal_selesai',
        'jenis_ujian',
        'acak_soal',
        'acak_opsi',
        'tampilkan_hasil',
        'status_publikasi',
    ];

    protected $casts = [
        'tanggal_mulai' => 'datetime',
        'tanggal_selesai' => 'datetime',
        'acak_soal' => 'boolean',
        'acak_opsi' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Mata kuliah pemilik ujian ini.
     */
    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }

    /**
     * Soal-soal yang ada dalam ujian ini (melalui tabel pivot ujian_soal).
     */
    public function soal() // Nama relasi bisa juga 'soals'
    {
        return $this->belongsToMany(Soal::class, 'ujian_soal', 'ujian_id', 'soal_id')
                    ->withTimestamps()
                    ->withPivot('nomor_urut_di_ujian', 'bobot_nilai_soal');
    }

    /**
     * Sesi pengerjaan ujian untuk ujian ini.
     */
    public function pengerjaanUjian()
    {
        return $this->hasMany(PengerjaanUjian::class, 'ujian_id');
    }
}