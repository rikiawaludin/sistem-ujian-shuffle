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

        // 1. Ambil data mentah dari API.
        $apiCourses = $this->getDosenCoursesFromApi($request);

        // 2. Ambil semua external_id dari hasil API.
        $externalIds = $apiCourses->pluck('external_id')->filter()->unique();

        // 3. Buat peta (map) dari external_id ke id lokal untuk pencocokan.
        $localIdMap = MataKuliah::whereIn('external_id', $externalIds)
            ->pluck('id', 'external_id');

        // 4. Format ulang data agar sesuai dengan yang dibutuhkan frontend (value & label).
        $mataKuliahOptions = $apiCourses->map(function ($apiCourse) use ($localIdMap) {
            // Cocokkan external_id dari API dengan id lokal dari peta.
            $localId = $localIdMap->get($apiCourse['external_id']);
            
            // Hanya kembalikan data jika ada padanannya di database lokal.
            if ($localId) {
                return [
                    'value' => $localId,
                    'label' => $apiCourse['nama'],
                ];
            }
            return null;
        })->filter()->values();

        return Inertia::render('Dosen/MataKuliah/Show', [
            'course' => $mata_kuliah,
            'soalSummary' => $soalSummary,  
            'ujianSummary' => $ujianSummary,
            'bankSoalSummaryByDifficulty' => $bankSoalSummaryByDifficulty,  
            'mataKuliahOptions' => $mataKuliahOptions,
        ]);
    }
}