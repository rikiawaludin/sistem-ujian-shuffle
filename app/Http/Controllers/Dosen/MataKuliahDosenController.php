<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\MataKuliah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MataKuliahDosenController extends Controller
{
    /**
     * Menampilkan halaman detail manajemen untuk satu mata kuliah.
     *
     * @param  \App\Models\MataKuliah  $mata_kuliah
     * @return \Inertia\Response
     */
    public function show(MataKuliah $mata_kuliah)
    {
        // Otorisasi: Pastikan dosen yang login memiliki akses ke mata kuliah ini.
        // Logika ini perlu disesuaikan dengan cara Anda menentukan kepemilikan mata kuliah.
        // Asumsi sementara: Dosen pembuat ujian di mata kuliah ini adalah pemilik.
        $isOwner = $mata_kuliah->ujian()->where('dosen_pembuat_id', Auth::id())->exists();
        if (!$isOwner) {
            // Atau Anda bisa melakukan query ke API di sini untuk memastikan kepemilikan
            // abort(403, 'Anda tidak memiliki akses ke mata kuliah ini.');
        }

        // 1. Ringkasan Bank Soal per Tipe
        $soalSummary = $mata_kuliah->soal
            ->groupBy('tipe_soal')
            ->map(fn ($group) => $group->count());

        // 2. Ringkasan Ujian per Status Publikasi
        $ujianSummary = $mata_kuliah->ujian
            ->groupBy('status_publikasi')
            ->map(fn ($group) => $group->count());


        return Inertia::render('Dosen/MataKuliah/Show', [
            'course' => $mata_kuliah,
            'soalSummary' => $soalSummary,     // <-- Kirim data ringkasan soal
            'ujianSummary' => $ujianSummary,   // <-- Kirim data ringkasan ujian
        ]);
    }
}