<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Http\Controllers\Dosen\Concerns\ManagesDosenAuth; // <-- Impor Trait
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UjianController extends Controller
{
    // Gunakan Trait untuk mewarisi fungsi getAuthProps() dan getDosenMataKuliahOptions()
    use ManagesDosenAuth;

    // Karena sudah menggunakan Trait, fungsi getAuthProps() yang sebelumnya ada di sini bisa dihapus.

    /**
     * Menampilkan daftar ujian yang dibuat oleh dosen.
     */
    public function index()
    {
        $authProps = $this->getAuthProps();
        if (!($authProps['user'] && $authProps['user']['is_dosen'])) {
            abort(403);
        }
        
        // Ganti withCount('soal') dengan withSum('aturan', 'jumlah_soal')
        $ujianList = Ujian::with('mataKuliah:id,nama')
                        ->withSum('aturan', 'jumlah_soal') // <-- UBAH BARIS INI
                        ->where('dosen_pembuat_id', Auth::id())
                        ->orderBy('created_at', 'desc')
                        ->paginate(10);

        return inertia('Dosen/Ujian/Index', [
            'ujianList' => $ujianList,
            'auth' => $authProps,
        ]);
    }

    /**
     * Menampilkan form untuk membuat ujian baru.
     */
    public function create(Request $request)
    {
        // ==========================================================
        // PERBAIKAN UTAMA DI SINI
        // ==========================================================

        // Panggil helper dari Trait untuk mendapatkan opsi Mata Kuliah dari API
        $authProps = $this->getAuthProps();
        $mataKuliahOptions = $this->getDosenMataKuliahOptions($request);

        return inertia('Dosen/Ujian/Form', [
            'mataKuliahOptions' => $mataKuliahOptions,
            'auth' => $authProps,
        ]);
    }

    /**
     * Menyimpan ujian baru ke database.
     */
    public function store(Request $request)
    {
        $this->getAuthProps(); // Panggil untuk memastikan Auth::id() valid

        // ==========================================================
        // PERBAIKAN UTAMA DI SINI: Gabungkan semua validasi jadi satu
        // ==========================================================
        $validatedData = $request->validate([
            'mata_kuliah_id' => 'required|exists:mata_kuliah,id',
            'judul_ujian' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'durasi' => 'required|integer|min:1',
            'kkm' => 'nullable|numeric|min:0|max:100',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'acak_soal' => 'required|boolean',
            'acak_opsi' => 'required|boolean',
            // 'tampilkan_hasil' => 'required|boolean',
            'status' => ['required', \Illuminate\Validation\Rule::in(['draft', 'published'])],
            'visibilitas_hasil' => 'required|boolean',
            'aturan_soal' => 'required|array',
            'aturan_soal.mudah' => 'required|integer|min:0',
            'aturan_soal.sedang' => 'required|integer|min:0',
            'aturan_soal.sulit' => 'required|integer|min:0',
        ]);

        $totalSoal = array_sum($validatedData['aturan_soal']);

        if ($totalSoal === 0) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'aturan_soal' => 'Anda harus memilih setidaknya satu soal untuk ujian.',
            ]);
        }

        // Gunakan transaksi database untuk memastikan semua data tersimpan atau tidak sama sekali
        DB::transaction(function () use ($validatedData) {
            // 1. Pisahkan data untuk tabel 'ujians' dan data untuk relasi 'aturan'
            $ujianDetails = collect($validatedData)->except('aturan_soal')->all();
            $aturanSoal = $validatedData['aturan_soal'];

            // 2. Tambahkan data default ke detail ujian
            $ujianDetails['dosen_pembuat_id'] = Auth::id();
            // $ujianDetails['status_publikasi'] = 'published';
            $ujianDetails['jenis_ujian'] = 'kuis';

            // 3. Buat record Ujian
            $ujian = Ujian::create($ujianDetails);

            // 4. Buat record UjianAturan dari data yang sudah dipisahkan
            foreach ($aturanSoal as $level => $jumlah) {
                if ($jumlah > 0) {
                    $ujian->aturan()->create([
                        'level_kesulitan' => $level,
                        'jumlah_soal' => $jumlah,
                    ]);
                }
            }
        });

        // Redirect ke halaman detail mata kuliah agar list ujian ter-update
        return redirect()->route('dosen.matakuliah.show', $validatedData['mata_kuliah_id'])
                        ->with('success', 'Ujian berhasil dibuat beserta aturannya.');
    }

    /**
     * Menampilkan halaman "Perakit Soal" (Soal Picker).
     */
    public function edit(Request $request, Ujian $ujian)
    {
        $authProps = $this->getAuthProps();
        if ($ujian->dosen_pembuat_id !== ($authProps['user']['id'] ?? null)) {
            abort(403);
        }

        // Ambil ringkasan jumlah soal yang tersedia di bank soal
        $bankSoalSummary = \App\Models\Soal::where('dosen_pembuat_id', Auth::id())
            ->where('mata_kuliah_id', $ujian->mata_kuliah_id)
            ->groupBy('level_kesulitan')
            ->select('level_kesulitan', DB::raw('count(*) as total'))
            ->pluck('total', 'level_kesulitan');

        // Ambil aturan yang sudah tersimpan sebelumnya
        $ujian->load('aturan');

        return inertia('Dosen/Ujian/Edit', [
            'ujian' => $ujian,
            'bankSoalSummary' => $bankSoalSummary,
            'auth' => $authProps,
        ]);
    }

    /**
     * Update ujian (baik detail ujian maupun sinkronisasi soal).
     */
    /**
 * Update ujian (menyimpan aturan pemilihan soal).
 */
    public function update(Request $request, Ujian $ujian)
    {
        $this->getAuthProps();
        if ($ujian->dosen_pembuat_id !== Auth::id()) {
            abort(403);
        }
        
        if ($request->has('aturan_soal')) {
            $validated = $request->validate([
                'aturan_soal' => 'required|array',
                'aturan_soal.mudah' => 'required|integer|min:0',
                'aturan_soal.sedang' => 'required|integer|min:0',
                'aturan_soal.sulit' => 'required|integer|min:0',
            ]);

            DB::transaction(function () use ($ujian, $validated) {
                $ujian->aturan()->delete();
                foreach ($validated['aturan_soal'] as $level => $jumlah) {
                    if ($jumlah > 0) {
                        $ujian->aturan()->create([
                            'level_kesulitan' => $level,
                            'jumlah_soal' => $jumlah,
                        ]);
                    }
                }
                $ujian->soal()->detach();
            });

            return back()->with('success', 'Aturan soal berhasil disimpan.');
        }

        // SCENARIO 2: Menyimpan DETAIL UJIAN dari modal "Edit Detail Ujian"
        $validated = $request->validate([
            'judul_ujian' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'durasi' => 'required|integer|min:1',
            'kkm' => 'nullable|numeric|min:0|max:100',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'acak_soal' => 'required|boolean',
            'acak_opsi' => 'required|boolean',
            // 'tampilkan_hasil' => 'required|boolean',
            'status' => ['required', \Illuminate\Validation\Rule::in(['draft', 'published', 'archived'])], // Tambahkan 'archived' jika dosen bisa mengarsip manual
            'visibilitas_hasil' => 'required|boolean',
            // mata_kuliah_id tidak perlu divalidasi saat update, karena tidak boleh diubah
        ]);

        $ujian->update($validated);
        
        // Blok "else" ini bisa Anda gunakan jika ingin ada form update detail ujian terpisah
        // atau gabungkan validasinya di atas.
        return back()->with('success', 'Detail ujian berhasil diperbarui.');
    }

    /**
     * Hapus ujian.
     */
    public function destroy(Ujian $ujian)
    {
        $this->getAuthProps();
        if ($ujian->dosen_pembuat_id !== Auth::id()) {
            abort(403);
        }
        
        DB::transaction(function () use ($ujian) {
            $ujian->soal()->detach();
            $ujian->delete();
        });
        return back()->with('success', 'Ujian berhasil dihapus.');
    }
}