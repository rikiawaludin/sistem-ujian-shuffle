<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail; // Aktifkan jika Anda menggunakan verifikasi email
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Jika Anda menggunakan Sanctum untuk API token

class User extends Authenticatable // implements MustVerifyEmail (jika perlu)
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'external_user_id',
        'role',
        'avatar_url',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime', // Jika Anda memiliki kolom ini
        'password' => 'hashed',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Mata kuliah yang diajar oleh dosen ini.
     */
    public function mataKuliahDiajar()
    {
        return $this->hasMany(MataKuliah::class, 'dosen_id');
    }

    /**
     * Soal yang dibuat oleh dosen ini.
     */
    public function soalDibuat()
    {
        return $this->hasMany(Soal::class, 'dosen_pembuat_id');
    }

    /**
     * Pengerjaan ujian yang dilakukan oleh siswa ini.
     */
    public function pengerjaanUjian()
    {
        return $this->hasMany(PengerjaanUjian::class, 'user_id');
    }

    /**
     * Mata kuliah yang diikuti oleh siswa ini (melalui tabel pivot mata_kuliah_user).
     */
    public function mataKuliahDiikuti()
    {
        return $this->belongsToMany(MataKuliah::class, 'mata_kuliah_user')
                    ->withTimestamps()
                    ->withPivot('status_progres', 'tanggal_pendaftaran');
    }
}
