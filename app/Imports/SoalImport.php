<?php

namespace App\Imports;

use App\Models\Soal;
use App\Models\OpsiJawaban;
use App\Models\MataKuliah;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rule;

class SoalImport implements ToCollection, WithHeadingRow, WithValidation
{
    private $dosenId;
    private $mataKuliahMap;

    public function __construct(int $dosenId)
    {
        $this->dosenId = $dosenId;
        // Buat cache/map untuk ID Mata Kuliah agar tidak query berulang-ulang
        $this->mataKuliahMap = MataKuliah::pluck('id', 'kode');
    }

    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                // 1. Cari Mata Kuliah ID
                $mataKuliahId = $this->mataKuliahMap[$row['mata_kuliah_kode']] ?? null;

                if (!$mataKuliahId) {
                    Log::warning("Import Soal: Mata Kuliah dengan kode '{$row['mata_kuliah_kode']}' tidak ditemukan. Baris dilewati.");
                    continue; // Lewati baris ini jika MK tidak ada
                }

                // 2. Buat Soal
                $soal = Soal::create([
                    'dosen_pembuat_id' => $this->dosenId,
                    'mata_kuliah_id' => $mataKuliahId,
                    'tipe_soal' => $row['tipe_soal'],
                    'level_kesulitan' => $row['level_kesulitan'],
                    'bobot' => $row['bobot'],
                    'pertanyaan' => $row['pertanyaan'],
                    'penjelasan' => $row['penjelasan'] ?? null,
                ]);

                // 3. Buat Opsi Jawaban berdasarkan Tipe Soal
                $this->createOpsiJawaban($soal, $row);
            }
        });
    }

    private function createOpsiJawaban(Soal $soal, $row)
    {
        $tipe = $row['tipe_soal'];
        $opsiStr = $row['opsi'] ?? '';
        $kunciStr = $row['kunci_jawaban'] ?? '';

        switch ($tipe) {
            case 'pilihan_ganda':
            case 'benar_salah':
                $opsiArr = explode('|', $opsiStr);
                foreach ($opsiArr as $teks) {
                    if (empty($teks)) continue;
                    OpsiJawaban::create([
                        'soal_id' => $soal->id,
                        'teks_opsi' => trim($teks),
                        'is_kunci_jawaban' => (trim($teks) === trim($kunciStr)),
                    ]);
                }
                break;

            case 'pilihan_jawaban_ganda':
                $opsiArr = explode('|', $opsiStr);
                $kunciArr = array_map('trim', explode('|', $kunciStr));
                foreach ($opsiArr as $teks) {
                    if (empty($teks)) continue;
                    OpsiJawaban::create([
                        'soal_id' => $soal->id,
                        'teks_opsi' => trim($teks),
                        'is_kunci_jawaban' => in_array(trim($teks), $kunciArr),
                    ]);
                }
                break;

            case 'isian_singkat':
                $kunciArr = explode('|', $kunciStr);
                foreach ($kunciArr as $teks) {
                    if (empty($teks)) continue;
                    OpsiJawaban::create([
                        'soal_id' => $soal->id,
                        'teks_opsi' => trim($teks),
                        'is_kunci_jawaban' => true,
                    ]);
                }
                break;

            case 'menjodohkan':
                $pasanganArr = explode('|', $opsiStr);
                foreach ($pasanganArr as $pasangan) {
                    if (empty($pasangan)) continue;
                    list($itemSoal, $itemJawaban) = array_map('trim', explode(':', $pasangan, 2));
                    OpsiJawaban::create([
                        'soal_id' => $soal->id,
                        'teks_opsi' => $itemSoal,
                        'pasangan_teks' => $itemJawaban,
                        'is_kunci_jawaban' => true,
                    ]);
                }
                break;
        }
    }

    public function rules(): array
    {
        return [
            '*.mata_kuliah_kode' => ['required', 'string'],
            '*.tipe_soal' => ['required', Rule::in(['pilihan_ganda', 'pilihan_jawaban_ganda', 'benar_salah', 'isian_singkat', 'menjodohkan', 'esai'])],
            '*.level_kesulitan' => ['required', Rule::in(['mudah', 'sedang', 'sulit'])],
            '*.bobot' => ['required', 'integer', 'min:0'],
            '*.pertanyaan' => ['required', 'string'],
        ];
    }
}