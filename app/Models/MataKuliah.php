<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MataKuliah extends Model
{
    use HasFactory;

    protected $table = 'mata_kuliah';

    protected $fillable = [
        'nama',
        'kode',
        'external_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Definisikan relasi ke model Ujian.
     * Satu Mata Kuliah bisa memiliki banyak Ujian.
     */
    public function ujian()
    {
        // Relasi ini dibutuhkan untuk withCount('ujian') di controller lain jika perlu
        return $this->hasMany(Ujian::class, 'mata_kuliah_id');
    }

    /**
     * Definisikan relasi ke model Soal.
     * Satu Mata Kuliah bisa memiliki banyak Soal di bank soal.
     */
    public function soal()
    {
        return $this->hasMany(Soal::class, 'mata_kuliah_id');
    }

    public function pengerjaanUjian()
    {
        return $this->hasManyThrough(
            \App\Models\PengerjaanUjian::class, 
            \App\Models\Ujian::class,
            'mata_kuliah_id', // Foreign key on Ujian table
            'ujian_id',       // Foreign key on PengerjaanUjian table
            'id',             // Local key on MataKuliah table
            'id'              // Local key on Ujian table
        );
    }
}