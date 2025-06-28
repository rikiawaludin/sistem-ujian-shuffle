<?php

namespace App\Exports;

use App\Models\Soal;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SoalExport implements FromCollection, WithHeadings, WithMapping
{
    protected $dosenId;

    public function __construct(int $dosenId)
    {
        $this->dosenId = $dosenId;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Ambil semua soal milik dosen yang sedang login, beserta relasinya
        return Soal::where('dosen_pembuat_id', $this->dosenId)
            ->with('mataKuliah', 'opsiJawaban')
            ->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'mata_kuliah_kode',
            'tipe_soal',
            'level_kesulitan',
            'bobot',
            'pertanyaan',
            'opsi',
            'kunci_jawaban',
            'penjelasan',
        ];
    }

    /**
     * @param Soal $soal
     * @return array
     */
    public function map($soal): array
    {
        $opsi = '';
        $kunciJawaban = '';

        switch ($soal->tipe_soal) {
            case 'pilihan_ganda':
            case 'pilihan_jawaban_ganda':
            case 'benar_salah':
                $opsi = $soal->opsiJawaban->pluck('teks_opsi')->implode('|');
                $kunciJawaban = $soal->opsiJawaban->where('is_kunci_jawaban', true)->pluck('teks_opsi')->implode('|');
                break;

            case 'isian_singkat':
                // Opsi kosong, kunci jawaban berisi semua kemungkinan
                $kunciJawaban = $soal->opsiJawaban->pluck('teks_opsi')->implode('|');
                break;

            case 'menjodohkan':
                // Opsi berisi pasangan, kunci jawaban kosong
                $opsi = $soal->opsiJawaban->map(function ($item) {
                    return $item->teks_opsi . ':' . $item->pasangan_teks;
                })->implode('|');
                break;
            
            case 'esai':
                // Opsi dan kunci jawaban kosong
                break;
        }

        return [
            $soal->mataKuliah->kode ?? '',
            $soal->tipe_soal,
            $soal->level_kesulitan,
            $soal->bobot,
            strip_tags($soal->pertanyaan), // Hapus tag HTML dari pertanyaan
            $opsi,
            $kunciJawaban,
            strip_tags($soal->penjelasan),
        ];
    }
}