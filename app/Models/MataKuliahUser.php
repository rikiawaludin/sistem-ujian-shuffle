<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MataKuliahUser extends Pivot
{
    protected $table = 'mata_kuliah_user';

    // Jika Anda ingin timestamps otomatis (created_at, updated_at) di tabel pivot
    // public $timestamps = true; 

    // Jika ada kolom tambahan yang ingin di-mass assign
    // protected $fillable = ['status_progres', 'tanggal_pendaftaran'];

    // Anda bisa definisikan $casts jika ada kolom seperti tanggal_pendaftaran
    // protected $casts = [
    //     'tanggal_pendaftaran' => 'datetime',
    // ];

    // Relasi ke User (opsional)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke MataKuliah (opsional)
    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class);
    }
}
