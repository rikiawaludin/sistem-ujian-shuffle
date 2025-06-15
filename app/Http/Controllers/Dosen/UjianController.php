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
        $validated = $request->validate([
            'mata_kuliah_id' => 'required|exists:mata_kuliah,id',
            'judul_ujian' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'durasi' => 'required|integer|min:1',
            'kkm' => 'nullable|numeric|min:0|max:100',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'acak_soal' => 'required|boolean',
            'acak_opsi' => 'required|boolean',
            'tampilkan_hasil' => 'required|boolean',
        ]);
        
        $validated['dosen_pembuat_id'] = Auth::id();
        $validated['status_publikasi'] = 'published'; 
        $validated['jenis_ujian'] = 'kuis';
        $ujian = Ujian::create($validated);

        return redirect()->route('dosen.ujian.edit', $ujian->id)->with('success', 'Ujian berhasil dibuat. Silakan tambahkan soal.');
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
        
        // Logika untuk menyimpan aturan soal
        if ($request->has('aturan_soal')) {
            $validated = $request->validate([
                'aturan_soal' => 'required|array',
                'aturan_soal.mudah' => 'required|integer|min:0',
                'aturan_soal.sedang' => 'required|integer|min:0',
                'aturan_soal.sulit' => 'required|integer|min:0',
            ]);

            DB::transaction(function () use ($ujian, $validated) {
                // Hapus aturan lama
                $ujian->aturan()->delete();

                // Buat aturan baru berdasarkan input
                foreach ($validated['aturan_soal'] as $level => $jumlah) {
                    if ($jumlah > 0) {
                        $ujian->aturan()->create([
                            'level_kesulitan' => $level,
                            'jumlah_soal' => $jumlah,
                        ]);
                    }
                }
                
                // Hapus juga relasi lama di pivot table untuk membersihkan
                $ujian->soal()->detach();
            });

            return redirect()->route('dosen.ujian.index')->with('success', 'Aturan soal untuk ujian berhasil disimpan.');
        }
        
        // Blok "else" ini bisa Anda gunakan jika ingin ada form update detail ujian terpisah
        // atau gabungkan validasinya di atas.
        return redirect()->back()->withErrors(['error' => 'Request tidak valid.']);
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
        return redirect()->route('dosen.ujian.index')->with('success', 'Ujian berhasil dihapus.');
    }
}