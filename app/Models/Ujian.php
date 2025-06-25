<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
        'tampilkan_hasil' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['status_terkini'];

    public function getStatusTerkiniAttribute(): string
    {
        // Pastikan status_publikasi bukan 'published', jangan tampilkan status dinamis
        // Ini adalah fallback jika Anda ingin menambahkan status seperti 'draft' di masa depan
        if ($this->status_publikasi !== 'published') {
            return ucfirst($this->status_publikasi);
        }

        $now = Carbon::now(config('app.timezone'));
        $mulai = $this->tanggal_mulai;
        $selesai = $this->tanggal_selesai;

        // Prioritas 1: Cek apakah waktu ujian sudah lewat.
        if ($selesai && $now->isAfter($selesai)) {
            return 'Selesai';
        }

        // Prioritas 2: Cek apakah ujian sedang berlangsung.
        if ($mulai && $selesai && $now->between($mulai, $selesai)) {
            return 'Berlangsung';
        }
        
        // Prioritas 3: Cek apakah ujian dijadwalkan untuk masa depan.
        if ($mulai && $now->isBefore($mulai)) {
            return 'Terjadwal';
        }

        // Fallback jika tidak ada kondisi waktu yang cocok (seharusnya jarang terjadi)
        return 'Published';
    }

    /**
     * Aturan pemilihan soal untuk ujian ini.
     */
    public function aturan()
    {
        return $this->hasMany(UjianAturan::class, 'ujian_id');
    }

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