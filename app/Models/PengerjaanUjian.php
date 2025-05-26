<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengerjaanUjian extends Model
{
    use HasFactory;

    protected $table = 'pengerjaan_ujian';

    protected $fillable = [
        'ujian_id',
        'user_id',
        'waktu_mulai',
        'waktu_selesai',
        'waktu_dihabiskan_detik',
        'skor_total',
        'status_pengerjaan',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'skor_total' => 'float', // atau 'decimal:2' jika ingin presisi
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Ujian yang dikerjakan.
     */
    public function ujian()
    {
        return $this->belongsTo(Ujian::class, 'ujian_id');
    }

    /**
     * Siswa yang mengerjakan ujian.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Detail jawaban untuk pengerjaan ujian ini.
     */
    public function detailJawaban()
    {
        return $this->hasMany(JawabanPesertaDetail::class, 'pengerjaan_ujian_id');
    }
}