<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MataKuliah extends Model
{
    use HasFactory;

    protected $table = 'mata_kuliah'; // Eksplisit jika nama tabel berbeda dari konvensi

    protected $fillable = [
        'nama_mata_kuliah',
        'kode_mata_kuliah',
        'deskripsi',
        'dosen_id',
        'icon_url',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Dosen yang mengampu mata kuliah ini.
     */
    public function dosen()
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }

    /**
     * Ujian yang ada dalam mata kuliah ini.
     */
    public function ujian() // Nama relasi bisa juga 'ujians'
    {
        return $this->hasMany(Ujian::class, 'mata_kuliah_id');
    }

    /**
     * Siswa yang mengikuti mata kuliah ini (melalui tabel pivot mata_kuliah_user).
     */
    public function siswa()
    {
        return $this->belongsToMany(User::class, 'mata_kuliah_user')
                    ->withTimestamps()
                    ->withPivot('status_progres', 'tanggal_pendaftaran');
    }
}
