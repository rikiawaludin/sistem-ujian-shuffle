<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MigrationHistory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'migration_history';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'is_dosen',
        'is_prodi',
        'is_admin',
        'is_mahasiswa',
        'is_mata_kuliah',
        // Saran dari respons sebelumnya:
        // 'entity_type',
        // 'records_added',
        // 'records_updated',
        // 'records_failed',
        // 'notes',
        // 'status_success'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_dosen' => 'boolean',
        'is_prodi' => 'boolean',
        'is_admin' => 'boolean',
        'is_mahasiswa' => 'boolean',
        'is_mata_kuliah' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}