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
        
        $ujianList = Ujian::with('mataKuliah:id,nama')->withCount('soal')
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

        // Logika untuk mengambil soal terpilih dan bank soal tidak berubah
        $ujian->load(['soal' => fn($q) => $q->select('bank_soal.id', 'pertanyaan', 'tipe_soal')]);
        $soalTerpilih = $ujian->soal->mapWithKeys(fn($s) => [$s->id => ['id' => $s->id, 'pertanyaan' => $s->pertanyaan, 'tipe_soal' => $s->tipe_soal, 'bobot_nilai_soal' => $s->pivot->bobot_nilai_soal]]);
        $queryBankSoal = \App\Models\Soal::where('dosen_pembuat_id', Auth::id())->select('id', 'pertanyaan', 'tipe_soal', 'kategori_soal')->orderBy('kategori_soal');
        if ($request->filled('kategori')) {
            $queryBankSoal->where('kategori_soal', $request->kategori);
        }
        $bankSoal = $queryBankSoal->get();
        $kategoriOptions = \App\Models\Soal::where('dosen_pembuat_id', Auth::id())->distinct()->pluck('kategori_soal');

        return inertia('Dosen/Ujian/Edit', [
            'ujian' => $ujian,
            'soalTerpilih' => $soalTerpilih,
            'bankSoal' => $bankSoal,
            'kategoriOptions' => $kategoriOptions,
            'filters' => $request->only(['kategori']),
            'auth' => $authProps,
        ]);
    }

    /**
     * Update ujian (baik detail ujian maupun sinkronisasi soal).
     */
    public function update(Request $request, Ujian $ujian)
    {
        $this->getAuthProps();
        if ($ujian->dosen_pembuat_id !== Auth::id()) {
            abort(403);
        }
        
        if($request->has('soal_sync_data')) {
            $data = $request->validate([
                'soal_sync_data' => 'present|array',
                'soal_sync_data.*.id' => 'required|exists:bank_soal,id',
                'soal_sync_data.*.bobot_nilai_soal' => 'required|numeric|min:0',
            ]);
            $syncData = collect($data['soal_sync_data'])->mapWithKeys(fn($item) => [$item['id'] => ['bobot_nilai_soal' => $item['bobot_nilai_soal']]]);
            $ujian->soal()->sync($syncData);
            return redirect()->back()->with('success', 'Daftar soal untuk ujian berhasil diperbarui.');
        } else {
            $validated = $request->validate([
                'mata_kuliah_id' => 'required|exists:mata_kuliah,id',
                'judul_ujian' => 'required|string|max:255',
                'deskripsi' => 'nullable|string',
                'durasi' => 'required|integer|min:1',
                'kkm' => 'nullable|numeric|min:0|max:100',
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
                'acak_soal' => 'required|boolean',
                'tampilkan_hasil' => 'required|boolean',
            ]);
            $ujian->update($validated);
            return redirect()->route('dosen.ujian.index')->with('success', 'Detail ujian berhasil diperbarui.');
        }
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