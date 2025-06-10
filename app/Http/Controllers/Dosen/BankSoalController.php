<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Soal;
use App\Models\User;
use App\Models\MataKuliah;
use App\Http\Controllers\Dosen\Concerns\ManagesDosenAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http; // <-- Impor HTTP Client
use Illuminate\Support\Facades\Log; // <-- Impor Log untuk debugging
use Inertia\Inertia;

class BankSoalController extends Controller
{

    use ManagesDosenAuth;

    public function index()
    {
        $authProps = $this->getAuthProps();

        // Pastikan user adalah dosen sebelum melanjutkan
        if (!($authProps['user'] && $authProps['user']['is_dosen'])) {
            abort(403, 'Akses ditolak.');
        }

        // Ambil semua soal milik dosen yang login.
        // Gunakan get() agar sesuai dengan komponen AdvancedTable di frontend.
        $soalList = Soal::where('dosen_pembuat_id', Auth::id())
                        ->orderBy('created_at', 'desc')
                        ->get();

        return Inertia::render('Dosen/BankSoal/Index', [
            'soalList' => $soalList,
            'auth' => $authProps, // Kirim prop auth secara manual
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

        return Inertia::render('Dosen/BankSoal/Form', [
            'soal' => $bank_soal,
            'auth' => $authProps,
            'mataKuliahOptions' => $this->getDosenMataKuliahOptions($request),
        ]);
    }

    public function store(Request $request)
    {
        $this->getAuthProps(); // Panggil untuk memastikan Auth::id() valid
        $validatedData = $request->validate([
            'pertanyaan' => 'required|string',
            'tipe_soal' => 'required|in:pilihan_ganda,benar_salah,esai',
            'opsi_jawaban' => 'nullable|array|required_if:tipe_soal,pilihan_ganda,benar_salah',
            'kunci_jawaban' => 'required_if:tipe_soal,pilihan_ganda,benar_salah',
            'penjelasan' => 'nullable|string',
            'kategori_soal' => 'nullable|string|max:100',
        ]);
        $validatedData['dosen_pembuat_id'] = Auth::id();
        Soal::create($validatedData);
        return redirect()->route('dosen.bank-soal.index')->with('success', 'Soal berhasil dibuat.');
    }

    public function update(Request $request, Soal $bank_soal)
    {
        $this->getAuthProps();

        // dd($request->all());

        if ($bank_soal->dosen_pembuat_id !== Auth::id()) {
            abort(403);
        }

        // Validasi input. Aturan 'string' tidak akan menghapus tag HTML.
        $validatedData = $request->validate([
            'pertanyaan' => 'required|string',
            'tipe_soal' => 'required|in:pilihan_ganda,benar_salah,esai',
            'opsi_jawaban' => 'nullable|array|required_if:tipe_soal,pilihan_ganda,benar_salah',
            'kunci_jawaban' => 'nullable', // Kunci bisa null/kosong, terutama untuk esai
            'penjelasan' => 'nullable|string',
            'kategori_soal' => 'nullable|string|max:100',
        ]);

        // Lakukan update pada model dengan data yang sudah tervalidasi
        $bank_soal->update($validatedData);

        // ... (logika validasi dan update)
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