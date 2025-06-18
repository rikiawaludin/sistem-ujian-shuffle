<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\MataKuliah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Http\Controllers\Dosen\Concerns\ManagesDosenAuth;

class MataKuliahDosenController extends Controller
{

    use ManagesDosenAuth;

    public function show(Request $request, MataKuliah $mata_kuliah)
    {
        // Otorisasi: Pastikan dosen yang login memiliki akses ke mata kuliah ini.
        // Logika ini perlu disesuaikan dengan cara Anda menentukan kepemilikan mata kuliah.
        // Asumsi sementara: Dosen pembuat ujian di mata kuliah ini adalah pemilik.
        $isOwner = $mata_kuliah->ujian()->where('dosen_pembuat_id', Auth::id())->exists();
        if (!$isOwner) {
            // Atau Anda bisa melakukan query ke API di sini untuk memastikan kepemilikan
            // abort(403, 'Anda tidak memiliki akses ke mata kuliah ini.');
        }

        $mata_kuliah->load([
            // Muat relasi 'soal' DAN sub-relasi 'opsiJawaban' untuk setiap soal
            'soal.opsiJawaban', 
            'ujian.aturan'
        ]);

        // 1. Ringkasan Bank Soal per Tipe
        $soalSummary = $mata_kuliah->soal
            ->groupBy('tipe_soal')
            ->map(fn ($group) => $group->count());

        // 2. Ringkasan Ujian per Status Publikasi
        $ujianSummary = $mata_kuliah->ujian
            ->groupBy('status_publikasi')
            ->map(fn ($group) => $group->count());
        
        $bankSoalSummaryByDifficulty = $mata_kuliah->soal
            ->groupBy('level_kesulitan')
            ->map(fn ($group) => $group->count());

        $mataKuliahOptions = $this->getDosenMataKuliahOptions($request);

        return Inertia::render('Dosen/MataKuliah/Show', [
            'course' => $mata_kuliah,
            'soalSummary' => $soalSummary,  
            'ujianSummary' => $ujianSummary,
            'bankSoalSummaryByDifficulty' => $bankSoalSummaryByDifficulty,  
            'mataKuliahOptions' => $mataKuliahOptions,
        ]);
    }
}