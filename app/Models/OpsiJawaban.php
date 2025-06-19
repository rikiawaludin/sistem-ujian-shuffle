<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpsiJawaban extends Model
{
    use HasFactory;
    protected $table = 'opsi_jawaban';

    protected $fillable = [
        'soal_id',
        'teks_opsi',
        'pasangan_teks',
        'is_kunci_jawaban',
    ];

    protected $casts = [
        'is_kunci_jawaban' => 'boolean',
    ];

    /**
     * Relasi ke soal induknya.
     */
    public function soal()
    {
        return $this->belongsTo(Soal::class, 'soal_id');
    }
}