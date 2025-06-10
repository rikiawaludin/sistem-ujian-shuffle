<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Jika Anda menggunakan Sanctum

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'external_id',
        'email',
        'is_dosen',
        'is_mahasiswa',
        'is_prodi',
        'is_admin',
        // Jika Anda menggunakan password untuk login lokal (misal admin)
        // 'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        // 'password',
        // 'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // 'email_verified_at' => 'datetime',
        'is_dosen' => 'boolean',
        'is_mahasiswa' => 'boolean',
        'is_prodi' => 'boolean',
        'is_admin' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relasi contoh jika diperlukan:
    // public function pengerjaanUjian()
    // {
    //     return $this->hasMany(PengerjaanUjian::class);
    // }

    // public function bankSoalDibuat() // Jika dosen membuat soal
    // {
    //     return $this->hasMany(BankSoal::class, 'dosen_pembuat_id');
    // }
}