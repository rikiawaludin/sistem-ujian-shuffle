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
        'status',
        'visibilitas_hasil',
    ];

    protected $casts = [
        'tanggal_mulai' => 'datetime',
        'tanggal_selesai' => 'datetime',
        'acak_soal' => 'boolean',
        'acak_opsi' => 'boolean',
        'visibilitas_hasil' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['status_terkini'];

    public function getStatusTerkiniAttribute(): string
    {
        $now = Carbon::now(config('app.timezone'));
        $mulai = $this->tanggal_mulai;
        $selesai = $this->tanggal_selesai;

        // Jika ujian belum di-publish, statusnya selalu Draft.
        if ($this->status === 'draft') {
            return 'Draft';
        }

        // Cek apakah waktu ujian sudah lewat
        $isFinished = $selesai && $now->isAfter($selesai);

        if ($isFinished) {
            // Jika sudah selesai DAN diarsipkan, cek visibilitas hasil
            if ($this->status === 'archived' && !$this->visibilitas_hasil) {
                // Tidak seharusnya terlihat oleh mahasiswa, tapi sebagai info untuk dosen
                return 'Diarsipkan (Tersembunyi)';
            }
            return 'Selesai';
        }

        // Cek apakah ujian sedang berlangsung (hanya jika statusnya published)
        if ($this->status === 'published' && $mulai && $selesai && $now->between($mulai, $selesai)) {
            return 'Berlangsung';
        }
        
        // Cek apakah ujian dijadwalkan untuk masa depan (hanya jika statusnya published)
        if ($this->status === 'published' && $mulai && $now->isBefore($mulai)) {
            return 'Terjadwal';
        }

        // Fallback untuk status lainnya, misal 'archived' tapi belum lewat waktu (kasus aneh)
        return ucfirst($this->status);
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