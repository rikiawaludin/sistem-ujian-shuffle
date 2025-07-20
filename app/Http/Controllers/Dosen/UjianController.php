<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Http\Controllers\Dosen\Concerns\ManagesDosenAuth; // Trait Anda
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UjianController extends Controller
{
    // Gunakan Trait untuk mewarisi fungsi getAuthProps() dan getDosenMataKuliahOptions()
    use ManagesDosenAuth;

    /**
     * Menampilkan daftar ujian yang dibuat oleh dosen.
     */
    public function index()
    {
        $authProps = $this->getAuthProps();
        if (!($authProps['user'] && $authProps['user']['is_dosen'])) {
            abort(403);
        }
        
        $ujianList = Ujian::with('mataKuliah:id,nama')
                        ->withSum('aturan', 'jumlah_soal')
                        ->where('dosen_pembuat_id', Auth::id())
                        ->orderBy('created_at', 'desc')
                        ->paginate(10);

        return inertia('Dosen/Ujian/Index', [
            'ujianList' => $ujianList,
            'auth' => $authProps,
        ]);
    }

    /**
     * Menampilkan form untuk membuat ujian baru. (Tidak berubah)
     */
    public function create(Request $request)
    {
        $authProps = $this->getAuthProps();
        $mataKuliahOptions = $this->getDosenMataKuliahOptions($request);

        return inertia('Dosen/Ujian/Form', [
            'mataKuliahOptions' => $mataKuliahOptions,
            'auth' => $authProps,
        ]);
    }

    /**
     * Menyimpan ujian baru ke database. (Logika Diperbarui)
     */
    public function store(Request $request)
    {
        $this->getAuthProps(); // Panggil untuk memastikan Auth::id() valid

        // ==========================================================
        // PERUBAHAN 1: Validasi diperbarui untuk menangani struktur data baru
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
            'status' => ['required', Rule::in(['draft', 'published'])],
            'visibilitas_hasil' => 'required|boolean',
            // Validasi untuk opsi esai
            'sertakan_esai' => 'boolean',
            'persentase_esai' => 'required_if:sertakan_esai,true|nullable|integer|max:100',
            // Validasi untuk aturan soal yang bersarang (nested)
            'aturan_soal' => 'required|array',
            'aturan_soal.non_esai' => 'required|array',
            'aturan_soal.non_esai.mudah' => 'required|integer|min:0',
            'aturan_soal.non_esai.sedang' => 'required|integer|min:0',
            'aturan_soal.non_esai.sulit' => 'required|integer|min:0',
            'aturan_soal.esai' => 'required|array',
            'aturan_soal.esai.mudah' => 'required|integer|min:0',
            'aturan_soal.esai.sedang' => 'required|integer|min:0',
            'aturan_soal.esai.sulit' => 'required|integer|min:0',
        ]);

        // ==========================================================
        // PERUBAHAN 2: Logika pengecekan total soal diperbarui
        // ==========================================================
        $totalNonEsai = array_sum($validatedData['aturan_soal']['non_esai']);
        $totalEsai = $validatedData['sertakan_esai'] ? array_sum($validatedData['aturan_soal']['esai']) : 0;
        $totalSoal = $totalNonEsai + $totalEsai;

        if ($totalSoal === 0) {
            throw ValidationException::withMessages([
                'aturan_soal' => 'Anda harus memilih setidaknya satu soal untuk ujian ini.',
            ]);
        }

        // Gunakan transaksi database untuk memastikan semua data tersimpan atau tidak sama sekali
        DB::transaction(function () use ($validatedData) {
            // ==========================================================
            // PERUBAHAN 3: Logika penyimpanan diperbarui
            // ==========================================================

            // 1. Pisahkan data untuk tabel 'ujians'. `except` akan menangani semua field
            $ujianDetails = collect($validatedData)->except('aturan_soal')->all();
            $aturanSoal = $validatedData['aturan_soal'];

            // 2. Tambahkan data default ke detail ujian
            $ujianDetails['dosen_pembuat_id'] = Auth::id();
            $ujianDetails['jenis_ujian'] = 'kuis';

            // 3. Buat record Ujian
            $ujian = Ujian::create($ujianDetails);

            // 4. Buat record UjianAturan untuk NON-ESAI
            foreach ($aturanSoal['non_esai'] as $level => $jumlah) {
                if ($jumlah > 0) {
                    $ujian->aturan()->create([
                        'tipe_soal' => 'non_esai', // <-- KOLOM BARU
                        'level_kesulitan' => $level,
                        'jumlah_soal' => $jumlah,
                    ]);
                }
            }
            
            // 5. Buat record UjianAturan untuk ESAI jika disertakan
            if ($validatedData['sertakan_esai']) {
                foreach ($aturanSoal['esai'] as $level => $jumlah) {
                    if ($jumlah > 0) {
                        $ujian->aturan()->create([
                            'tipe_soal' => 'esai', // <-- KOLOM BARU
                            'level_kesulitan' => $level,
                            'jumlah_soal' => $jumlah,
                        ]);
                    }
                }
            }
        });

        // Redirect ke halaman detail mata kuliah agar list ujian ter-update
        return redirect()->route('dosen.matakuliah.show', $validatedData['mata_kuliah_id'])
                        ->with('success', 'Ujian berhasil dibuat beserta aturannya.');
    }

    /**
     * Menampilkan halaman "Perakit Soal" (Soal Picker). (Tidak berubah)
     */
    public function edit(Request $request, Ujian $ujian)
    {
        $authProps = $this->getAuthProps();
        if ($ujian->dosen_pembuat_id !== ($authProps['user']['id'] ?? null)) {
            abort(403);
        }

        $allSoal = Soal::where('dosen_pembuat_id', Auth::id())
            ->where('mata_kuliah_id', $ujian->mata_kuliah_id)
            ->get();

        $allSoal = Soal::where('dosen_pembuat_id', Auth::id())
            ->where('mata_kuliah_id', $ujian->mata_kuliah_id)
            ->get();

        // 1. Pisahkan koleksi soal menjadi dua: satu untuk 'esai', satu untuk sisanya.
        [$esaiSoals, $nonEsaiSoals] = $allSoal->partition(function ($soal) {
            return $soal->tipe_soal === 'esai';
        });

        // 2. Hitung ringkasan untuk soal NON-ESAI
        $nonEsaiSummary = $nonEsaiSoals->groupBy('level_kesulitan')->map->count();

        // 3. Hitung ringkasan untuk soal ESAI
        $esaiSummary = $esaiSoals->groupBy('level_kesulitan')->map->count();

        // 4. Gabungkan keduanya ke dalam format yang diharapkan frontend
        $bankSoalSummary = collect([
            'non_esai' => [
                'mudah' => $nonEsaiSummary->get('mudah', 0),
                'sedang' => $nonEsaiSummary->get('sedang', 0),
                'sulit' => $nonEsaiSummary->get('sulit', 0),
            ],
            'esai' => [
                'mudah' => $esaiSummary->get('mudah', 0),
                'sedang' => $esaiSummary->get('sedang', 0),
                'sulit' => $esaiSummary->get('sulit', 0),
            ],
        ]);

        $ujian->load('aturan');

        return inertia('Dosen/Ujian/Edit', [
            'ujian' => $ujian,
            'bankSoalSummary' => $bankSoalSummary,
            'auth' => $authProps,
        ]);
    }

    /**
     * Update ujian (baik detail ujian maupun aturan soal). (Logika Diperbarui)
     */
    public function update(Request $request, Ujian $ujian)
    {
        $this->getAuthProps();
        if ($ujian->dosen_pembuat_id !== Auth::id()) {
            abort(403);
        }
        
        // SCENARIO 1: Menyimpan ATURAN SOAL dari modal "Atur Soal"
        if ($request->has('aturan_soal')) {
            // Validasi untuk aturan soal yang bersarang
            $validated = $request->validate([
                'aturan_soal' => 'required|array',
                'aturan_soal.non_esai' => 'required|array',
                'aturan_soal.non_esai.*' => 'required|integer|min:0',
                // Validasi untuk esai juga diperlukan di sini, untuk konsistensi
                'aturan_soal.esai' => 'required|array',
                'aturan_soal.esai.*' => 'required|integer|min:0',
            ]);
            
            DB::transaction(function () use ($ujian, $validated) {
                // Hapus semua aturan lama
                $ujian->aturan()->delete();
                
                // Simpan aturan NON-ESAI yang baru
                foreach ($validated['aturan_soal']['non_esai'] as $level => $jumlah) {
                    if ($jumlah > 0) {
                        $ujian->aturan()->create([
                            'tipe_soal' => 'non_esai',
                            'level_kesulitan' => $level,
                            'jumlah_soal' => $jumlah,
                        ]);
                    }
                }

                // Simpan aturan ESAI yang baru (jika ujian memang menyertakan esai)
                if ($ujian->sertakan_esai) {
                    foreach ($validated['aturan_soal']['esai'] as $level => $jumlah) {
                        if ($jumlah > 0) {
                            $ujian->aturan()->create([
                                'tipe_soal' => 'esai',
                                'level_kesulitan' => $level,
                                'jumlah_soal' => $jumlah,
                            ]);
                        }
                    }
                }
                
                // Reset daftar soal yang sudah dirakit sebelumnya, karena aturan berubah
                $ujian->soal()->detach();
            });

            return back()->with('success', 'Aturan soal berhasil diperbarui.');
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
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'visibilitas_hasil' => 'required|boolean',
            // Validasi untuk field baru saat update
            'sertakan_esai' => 'boolean',
            'persentase_esai' => 'required_if:sertakan_esai,true|nullable|integer|max:100',
        ]);

        $ujian->update($validated);
        
        return back()->with('success', 'Detail ujian berhasil diperbarui.');
    }

    public function destroyAll(\App\Models\MataKuliah $mataKuliah)
    {
        // 1. Otorisasi: Pastikan dosen yang login adalah pemilik ujian di mata kuliah ini
        $this->getAuthProps();
        $dosenId = Auth::id();

        // 2. Ambil semua ujian yang dibuat oleh dosen ini untuk mata kuliah yang spesifik
        $ujians = Ujian::where('mata_kuliah_id', $mataKuliah->id)
                       ->where('dosen_pembuat_id', $dosenId)
                       ->get();

        // Jika tidak ada ujian untuk dihapus, kembalikan dengan pesan info
        if ($ujians->isEmpty()) {
            return back()->with('info', 'Tidak ada ujian yang bisa dihapus untuk mata kuliah ini.');
        }

        // 3. Gunakan transaksi untuk memastikan semua operasi berhasil
        DB::transaction(function () use ($ujians) {
            foreach ($ujians as $ujian) {
                // Hapus relasi dari pivot table 'ujian_soal'
                $ujian->soal()->detach();
                // Hapus aturan ujian yang terkait
                $ujian->aturan()->delete();
            }
            // Hapus semua record ujian sekaligus setelah relasi dibersihkan
            Ujian::whereIn('id', $ujians->pluck('id'))->delete();
        });
        
        // 4. Redirect kembali dengan pesan sukses
        return back()->with('success', 'Semua ujian untuk mata kuliah ' . $mataKuliah->nama . ' berhasil dihapus.');
    }

    /**
     * Hapus ujian. (Tidak berubah)
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

