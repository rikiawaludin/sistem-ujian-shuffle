<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JawabanPesertaDetail extends Model
{
    use HasFactory;

    protected $table = 'jawaban_peserta_detail';

    protected $fillable = [
        'pengerjaan_ujian_id',
        'soal_id',
        'jawaban_user',
        'is_benar',
        'skor_per_soal',
        'is_ragu_ragu',
        'waktu_jawab_detik',
    ];

    protected $casts = [
        // 'jawaban_user' => 'json', // Atau 'array' jika jawaban PG disimpan sebagai array ID opsi
        'is_benar' => 'boolean',
        'is_ragu_ragu' => 'boolean',
        'skor_per_soal' => 'float', // atau 'decimal:2'
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Sesi pengerjaan ujian pemilik jawaban ini.
     */
    public function pengerjaanUjian()
    {
        return $this->belongsTo(PengerjaanUjian::class, 'pengerjaan_ujian_id');
    }

    /**
     * Soal yang dijawab.
     */
    public function soal()
    {
        return $this->belongsTo(Soal::class, 'soal_id');
    }
}
