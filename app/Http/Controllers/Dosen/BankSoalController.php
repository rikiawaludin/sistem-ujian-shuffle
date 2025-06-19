<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Soal;
use App\Models\User;
use App\Models\MataKuliah;
use App\Models\OpsiJawaban;
use App\Http\Controllers\Dosen\Concerns\ManagesDosenAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http; // <-- Impor HTTP Client
use Illuminate\Support\Facades\Log; // <-- Impor Log untuk debugging
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class BankSoalController extends Controller
{

    use ManagesDosenAuth;

    public function index(Request $request)
    {
        $authProps = $this->getAuthProps();

        // Pastikan user adalah dosen sebelum melanjutkan
        if (!($authProps['user'] && $authProps['user']['is_dosen'])) {
            abort(403, 'Akses ditolak.');
        }

        // Gunakan query builder agar bisa menambahkan kondisi secara dinamis
        $query = Soal::where('dosen_pembuat_id', Auth::id())->with('mataKuliah');

        // === LOGIKA FILTER ===
        // Jika ada request filter_mk dan nilainya tidak kosong
        if ($request->filled('filter_mk')) {
            $query->where('mata_kuliah_id', $request->filter_mk);
        }

        $soalList = $query->orderBy('created_at', 'desc')->get();

        // Ambil daftar mata kuliah untuk dropdown filter
        $mataKuliahOptions = $this->getDosenMataKuliahOptions($request);

        return Inertia::render('Dosen/BankSoal/Index', [
            'soalList' => $soalList,
            'auth' => $authProps,
            'mataKuliahOptions' => $mataKuliahOptions, // <-- Kirim opsi MK
            'filters' => $request->only(['filter_mk']), // <-- Kirim filter yang sedang aktif
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('Dosen/BankSoal/Form', [
            'auth' => $this->getAuthProps(),
            'mataKuliahOptions' => $this->getDosenMataKuliahOptions($request), // <-- Kirim data MK
        ]);
    }

    public function edit(Request $request, Soal $bank_soal)
    {
        $authProps = $this->getAuthProps();
        
        // Otorisasi: pastikan hanya pemilik soal yang bisa mengedit
        if ($bank_soal->dosen_pembuat_id !== ($authProps['user']['id'] ?? null)) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit soal ini.');
        }

        // Eager load relasi opsi jawaban
        $bank_soal->load('opsiJawaban');

        // Ambil data soal untuk form, pastikan mengirimkan mata_kuliah_id
        $soalData = [
            'id' => $bank_soal->id,
            'pertanyaan' => $bank_soal->pertanyaan,
            'tipe_soal' => $bank_soal->tipe_soal,
            'level_kesulitan' => $bank_soal->level_kesulitan,
            'bobot' => $bank_soal->bobot,
            'penjelasan' => $bank_soal->penjelasan,
            'mata_kuliah_id' => $bank_soal->mata_kuliah_id,
            'opsi_jawaban' => $bank_soal->opsiJawaban, // <-- Masukkan relasi secara eksplisit
        ];

        // dd($soalData);

        return Inertia::render('Dosen/BankSoal/Form', [
            'soal' => $soalData,
            'auth' => $authProps,
            'mataKuliahOptions' => $this->getDosenMataKuliahOptions($request),
        ]);
    }

    public function store(Request $request)
    {
        $this->getAuthProps();
        
        $validated = $request->validate([
            'pertanyaan' => 'required|string',
            // 1. Perbarui daftar tipe soal yang valid
            'tipe_soal' => 'required|in:pilihan_ganda,pilihan_jawaban_ganda,benar_salah,isian_singkat,menjodohkan,esai',
            'mata_kuliah_id' => 'required|integer|exists:mata_kuliah,id',
            'level_kesulitan' => 'required|in:mudah,sedang,sulit',
            'bobot' => 'required|integer|min:0',
            'penjelasan' => 'nullable|string',
            'opsi_jawaban' => 'nullable|array',
            'kunci_jawaban_id' => 'nullable', // Validasi kunci jawaban akan lebih spesifik di bawah
        ]);

        DB::beginTransaction();
        try {
            $soal = Soal::create([
                'dosen_pembuat_id' => Auth::id(),
                'pertanyaan' => $validated['pertanyaan'],
                'tipe_soal' => $validated['tipe_soal'],
                'mata_kuliah_id' => $validated['mata_kuliah_id'],
                'bobot' => $validated['bobot'],
                'level_kesulitan' => $validated['level_kesulitan'],
                'penjelasan' => $validated['penjelasan'] ?? null,
            ]);

            // 2. Logika penyimpanan dinamis berdasarkan tipe soal
            if (isset($validated['opsi_jawaban'])) {
                switch ($validated['tipe_soal']) {
                    case 'pilihan_ganda':
                    case 'benar_salah':
                        foreach ($validated['opsi_jawaban'] as $opsi) {
                            OpsiJawaban::create([
                                'soal_id' => $soal->id,
                                'teks_opsi' => $opsi['teks'],
                                'is_kunci_jawaban' => ($opsi['id'] === $validated['kunci_jawaban_id']),
                            ]);
                        }
                        break;
                    
                    case 'pilihan_jawaban_ganda':
                        $kunciJawabanArr = $validated['kunci_jawaban_id'] ?? [];
                        foreach ($validated['opsi_jawaban'] as $opsi) {
                            OpsiJawaban::create([
                                'soal_id' => $soal->id,
                                'teks_opsi' => $opsi['teks'],
                                'is_kunci_jawaban' => in_array($opsi['id'], $kunciJawabanArr),
                            ]);
                        }
                        break;

                    case 'isian_singkat':
                        foreach ($validated['opsi_jawaban'] as $opsi) {
                            if (!empty($opsi['teks'])) {
                                OpsiJawaban::create([
                                    'soal_id' => $soal->id,
                                    'teks_opsi' => $opsi['teks'],
                                    'is_kunci_jawaban' => true, // Semua opsi adalah jawaban benar
                                ]);
                            }
                        }
                        break;
                    
                    case 'menjodohkan':
                        foreach ($validated['opsi_jawaban'] as $opsi) {
                             if (!empty($opsi['teks']) || !empty($opsi['pasangan_teks'])) {
                                OpsiJawaban::create([
                                    'soal_id' => $soal->id,
                                    'teks_opsi' => $opsi['teks'],
                                    'pasangan_teks' => $opsi['pasangan_teks'],
                                    'is_kunci_jawaban' => false,
                                ]);
                            }
                        }
                        break;
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan soal baru: ' . $e->getMessage());
            return back()->withErrors(['db_error' => 'Gagal menyimpan soal. Silakan coba lagi.']);
        }

        return back()->with('success', 'Soal berhasil dibuat.');
    }

    public function update(Request $request, Soal $bank_soal)
    {
        $this->getAuthProps();
        if ($bank_soal->dosen_pembuat_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'pertanyaan' => 'required|string',
            'tipe_soal' => 'required|in:pilihan_ganda,pilihan_jawaban_ganda,benar_salah,isian_singkat,menjodohkan,esai',
            'mata_kuliah_id' => 'required|integer|exists:mata_kuliah,id',
            'level_kesulitan' => 'required|in:mudah,sedang,sulit',
            'bobot' => 'required|integer|min:0',
            'penjelasan' => 'nullable|string',
            'opsi_jawaban' => 'nullable|array',
            'kunci_jawaban_id' => 'nullable',
        ]);

        DB::beginTransaction();
        try {
            // 1. Update data soal utama
            $bank_soal->update($validated);

            // 2. Hapus semua opsi jawaban lama untuk diganti dengan yang baru
            $bank_soal->opsiJawaban()->delete();

            // 3. Logika penyimpanan baru yang dinamis (sama seperti di 'store')
             if (isset($validated['opsi_jawaban'])) {
                switch ($validated['tipe_soal']) {
                    case 'pilihan_ganda':
                    case 'benar_salah':
                        foreach ($validated['opsi_jawaban'] as $opsi) {
                            OpsiJawaban::create([
                                'soal_id' => $bank_soal->id,
                                'teks_opsi' => $opsi['teks'],
                                'is_kunci_jawaban' => ($opsi['id'] === $validated['kunci_jawaban_id']),
                            ]);
                        }
                        break;
                    
                    case 'pilihan_jawaban_ganda':
                        $kunciJawabanArr = $validated['kunci_jawaban_id'] ?? [];
                        foreach ($validated['opsi_jawaban'] as $opsi) {
                            OpsiJawaban::create([
                                'soal_id' => $bank_soal->id,
                                'teks_opsi' => $opsi['teks'],
                                'is_kunci_jawaban' => in_array($opsi['id'], $kunciJawabanArr),
                            ]);
                        }
                        break;

                    case 'isian_singkat':
                        foreach ($validated['opsi_jawaban'] as $opsi) {
                            if (!empty($opsi['teks'])) {
                                OpsiJawaban::create([
                                    'soal_id' => $bank_soal->id,
                                    'teks_opsi' => $opsi['teks'],
                                    'is_kunci_jawaban' => true,
                                ]);
                            }
                        }
                        break;
                    
                    case 'menjodohkan':
                        foreach ($validated['opsi_jawaban'] as $opsi) {
                             if (!empty($opsi['teks']) || !empty($opsi['pasangan_teks'])) {
                                OpsiJawaban::create([
                                    'soal_id' => $bank_soal->id,
                                    'teks_opsi' => $opsi['teks'],
                                    'pasangan_teks' => $opsi['pasangan_teks'],
                                    'is_kunci_jawaban' => false,
                                ]);
                            }
                        }
                        break;
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memperbarui soal ID ' . $bank_soal->id . ': ' . $e->getMessage());
            return back()->withErrors(['db_error' => 'Gagal memperbarui soal. Silakan coba lagi.']);
        }

        return back()->with('success', 'Soal berhasil diperbarui.');
    }

    public function destroy(Soal $bank_soal)
    {
        $this->getAuthProps();
        if ($bank_soal->dosen_pembuat_id !== Auth::id()) {
            abort(403);
        }
        $bank_soal->delete();
        return back()->with('success', 'Soal berhasil dihapus.');
    }
}