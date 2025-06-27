<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UjianAturan extends Model
{
    use HasFactory;

    protected $table = 'ujian_aturan';

    protected $fillable = [
        'ujian_id',
        'level_kesulitan',
        'jumlah_soal',
        'tipe_soal',
    ];

    public function ujian()
    {
        return $this->belongsTo(Ujian::class, 'ujian_id');
    }

    public $timestamps = false;
}