<?php

namespace App\Http\Controllers\Concerns;

use App\Models\PengerjaanUjian;
use App\Models\Ujian;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait ManagesMahasiswaData
{
    /**
     * Mengambil riwayat ujian yang sudah selesai untuk mahasiswa yang login.
     */
    private function getHistoriUjian()
    {
        if (!Auth::check()) return collect([]);

        return PengerjaanUjian::where('user_id', Auth::id())
            ->whereIn('status_pengerjaan', ['selesai', 'selesai_waktu_habis'])
            ->with(['ujian:id,judul_ujian,kkm', 'ujian.mataKuliah:id,nama'])
            ->orderBy('waktu_selesai', 'desc')
            ->limit(4) // Ambil 4 teratas untuk ditampilkan di dashboard
            ->get()
            ->map(function ($attempt) {
                return [
                    'id_pengerjaan' => $attempt->id,
                    'namaUjian' => $attempt->ujian->judul_ujian ?? 'N/A',
                    'namaMataKuliah' => $attempt->ujian->mataKuliah->nama ?? 'N/A',
                    'skor' => $attempt->skor_total,
                    'kkm' => $attempt->ujian->kkm ?? 0,
                ];
            });
    }

    /**
     * Mengambil daftar ujian yang aktif dan akan datang.
     */
    private function getDaftarUjian()
    {
        if (!Auth::check()) return collect([]);
        
        $now = Carbon::now();
        $ujianTersedia = Ujian::where('status_publikasi', 'published')
                            ->where('tanggal_selesai', '>=', $now)
                            ->withCount('soal')
                            ->get();

        return $ujianTersedia->map(function ($ujian) use ($now) {
            $pengerjaanTerakhir = PengerjaanUjian::where('ujian_id', $ujian->id)
                                    ->where('user_id', Auth::id())
                                    ->latest('waktu_mulai')
                                    ->first();
            $statusUjian = "Tidak Tersedia";
            if ($now->between($ujian->tanggal_mulai, $ujian->tanggal_selesai)) {
                if (!$pengerjaanTerakhir) { $statusUjian = "Belum Dikerjakan"; }
                elseif ($pengerjaanTerakhir->status_pengerjaan === 'sedang_dikerjakan') { $statusUjian = "Sedang Dikerjakan"; }
            } elseif ($now->lt($ujian->tanggal_mulai)) {
                $statusUjian = "Akan Datang";
            }
            return ['status' => $statusUjian];
        });
    }
}