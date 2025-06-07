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
}