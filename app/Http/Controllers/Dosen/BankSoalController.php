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
            'tipe_soal' => 'required|in:pilihan_ganda,benar_salah,esai',
            'mata_kuliah_id' => 'required|integer|exists:mata_kuliah,id',
            'level_kesulitan' => 'required|in:mudah,sedang,sulit',
            'bobot' => 'required|integer|min:0',
            'penjelasan' => 'nullable|string',
            'opsi_jawaban' => 'nullable|array|required_if:tipe_soal,pilihan_ganda,benar_salah|min:2',
            'opsi_jawaban.*.id' => 'present|string',
            'opsi_jawaban.*.teks' => 'required|string|max:1000',
            // Kita ubah validasi kunci jawaban
            'kunci_jawaban_id' => [
                Rule::requiredIf(fn () => in_array($request->tipe_soal, ['pilihan_ganda', 'benar_salah'])),
                'nullable',
                'string',
            ],
        ]);

        DB::beginTransaction();
        try {
            // 1. Buat Soal Utama
            $soal = Soal::create([
                'dosen_pembuat_id' => Auth::id(),
                'pertanyaan' => $validated['pertanyaan'],
                'tipe_soal' => $validated['tipe_soal'],
                'mata_kuliah_id' => $validated['mata_kuliah_id'],
                'bobot' => $validated['bobot'],
                'level_kesulitan' => $validated['level_kesulitan'],
                'penjelasan' => $validated['penjelasan'] ?? null,

                // Tambahkan nilai default untuk kolom yang wajib diisi
                'pasangan' => null,
                'gambar_url' => null,
                'audio_url' => null,
                'video_url' => null,
            ]);

            // 2. Simpan Opsi Jawaban jika ada
            if (isset($validated['opsi_jawaban'])) {
                foreach ($validated['opsi_jawaban'] as $opsi) {
                    OpsiJawaban::create([
                        'soal_id' => $soal->id,
                        'teks_opsi' => $opsi['teks'],
                        // Cek apakah ID sementara dari frontend sama dengan ID kunci jawaban
                        'is_kunci_jawaban' => ($opsi['id'] === $validated['kunci_jawaban_id']),
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            // Tulis log error untuk debugging
            \Illuminate\Support\Facades\Log::error('Gagal menyimpan soal baru: ' . $e->getMessage());
            return redirect()->back()->withErrors(['db_error' => 'Gagal menyimpan soal. Silakan coba lagi.']);
        }

        return redirect()->route('dosen.bank-soal.index')->with('success', 'Soal berhasil dibuat.');
    }

    public function update(Request $request, Soal $bank_soal)
    {
        $this->getAuthProps();

        // dd($request->all());

        if ($bank_soal->dosen_pembuat_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'pertanyaan' => 'required|string',
            'tipe_soal' => 'required|in:pilihan_ganda,benar_salah,esai',
            'mata_kuliah_id' => 'required|integer|exists:mata_kuliah,id',
            'level_kesulitan' => 'required|in:mudah,sedang,sulit',
            'bobot' => 'required|integer|min:0',
            'penjelasan' => 'nullable|string',
            'opsi_jawaban.*.id' => 'present|nullable',
            'opsi_jawaban.*.teks' => 'required_with:opsi_jawaban|string|max:1000',
            // Kita ubah validasi kunci jawaban
            'kunci_jawaban_id' => [
                Rule::requiredIf(fn () => in_array($request->tipe_soal, ['pilihan_ganda', 'benar_salah'])),
                'nullable',
                // Ganti 'string' dengan aturan 'in' yang lebih cerdas
                Rule::in(collect($request->input('opsi_jawaban', []))->pluck('id')),
            ],
        ]);

        DB::beginTransaction();
        try {
            // 1. Update data soal utama
            $bank_soal->update([
                'pertanyaan' => $validated['pertanyaan'],
                'tipe_soal' => $validated['tipe_soal'],
                'mata_kuliah_id' => $validated['mata_kuliah_id'],
                'level_kesulitan' => $validated['level_kesulitan'],
                'bobot' => $validated['bobot'],
                'penjelasan' => $validated['penjelasan'] ?? null,
            ]);

            // 2. Hapus semua opsi jawaban lama
            $bank_soal->opsiJawaban()->delete();

            // 3. Buat ulang opsi jawaban yang baru
            if (isset($validated['opsi_jawaban'])) {
                foreach ($validated['opsi_jawaban'] as $opsi) {
                    OpsiJawaban::create([
                        'soal_id' => $bank_soal->id,
                        'teks_opsi' => $opsi['teks'],
                        'is_kunci_jawaban' => ($opsi['id'] === $validated['kunci_jawaban_id']),
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Gagal memperbarui soal ID ' . $bank_soal->id . ': ' . $e->getMessage());
            return redirect()->back()->withErrors(['db_error' => 'Gagal memperbarui soal. Silakan coba lagi.']);
        }

        return redirect()->route('dosen.bank-soal.index')->with('success', 'Soal berhasil diperbarui.');
    }

    public function destroy(Soal $bank_soal)
    {
        $this->getAuthProps();
        if ($bank_soal->dosen_pembuat_id !== Auth::id()) {
            abort(403);
        }
        $bank_soal->delete();
        return redirect()->route('dosen.bank-soal.index')->with('success', 'Soal berhasil dihapus.');
    }
}