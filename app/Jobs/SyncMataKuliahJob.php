<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\MataKuliah;
use App\Models\MigrationHistory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon; // Untuk timestamp

class SyncMataKuliahJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userApiToken;

    /**
     * Create a new job instance.
     * @param string|null $token Token otentikasi jika diperlukan oleh endpoint ini
     */
    public function __construct(string $token = null)
    {
        $this->userApiToken = $token;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $jobStartTime = microtime(true);
        Log::info('Memulai SyncMataKuliahJob...');

        $processedMatkulCount = 0;
        $parseFailedMatkulCount = 0;
        $dbFailedMatkulCount = 0;
        $errorsMatkul = [];

        $apiBaseUrl = config('myconfig.api.base_url', env('API_BASE_URL'));

        if (!$apiBaseUrl) {
            Log::error('SyncMataKuliahJob: API_BASE_URL tidak diset di config atau .env');
            MigrationHistory::create([
                'is_mata_kuliah' => true,
                'is_dosen' => false,
                'is_mahasiswa' => false,
                'is_prodi' => false,
                'is_admin' => false,
                // 'notes' => 'Konfigurasi API_BASE_URL tidak ditemukan.'
            ]);
            return;
        }

        // Token handling: Sesuaikan jika endpoint ini tidak memerlukan token atau menggunakan token sistem
        if (empty($this->userApiToken)) {
            // Jika endpoint ini adalah publik atau menggunakan token sistem yang berbeda,
            // Anda mungkin tidak perlu melempar error di sini atau bahkan tidak perlu $userApiToken.
            // Namun, jika endpoint ini tetap memerlukan otentikasi yang sama:
            Log::error('SyncMataKuliahJob: Token API pengguna kosong atau tidak valid dari Controller.');
            MigrationHistory::create([
                'is_mata_kuliah' => true,
                'is_dosen' => false,
                // 'notes' => 'Token API kosong dari controller.'
            ]);
            return;
        }

        $endpoint = '/ujian/migrations/matakuliah'; // ENDPOINT BARU
        $apiUrl = rtrim($apiBaseUrl, '/') . $endpoint;

        try {
            $fetchApiStartTime = microtime(true);
            
            $httpRequest = Http::withHeaders(['Accept' => 'application/json']);
            // Hanya tambahkan header Authorization jika token memang ada dan diperlukan oleh endpoint ini
            if (!empty($this->userApiToken)) {
                $httpRequest->withToken($this->userApiToken); // Cara lain: ->withHeaders(['Authorization' => 'Bearer ' . $this->userApiToken])
            }
            
            $response = $httpRequest->get($apiUrl);
            
            $fetchApiEndTime = microtime(true);
            $fetchApiDuration = round(($fetchApiEndTime - $fetchApiStartTime) * 1000, 2);
            Log::info("SyncMataKuliahJob: Pengambilan data dari API ({$apiUrl}) selesai dalam {$fetchApiDuration} ms.");

            if (!$response->successful()) {
                Log::error("SyncMataKuliahJob: Gagal mengambil data dari API: {$apiUrl}. Status: {$response->status()}", [
                    'response_body' => $response->json() ?: $response->body(),
                    'token_prefix' => $this->userApiToken ? substr($this->userApiToken, 0, 5) . '...' : 'No token used'
                ]);
                $errorsMatkul[] = "Gagal fetch dari API (Status: {$response->status()}) - " . ($response->json()['message'] ?? substr($response->body(),0,100));
                $dbFailedMatkulCount = -1; // Indikasi gagal fetch keseluruhan
            } else {
                // Sesuaikan dengan struktur respons baru: {"success": true, "data": [...]}
                $apiMataKuliahItems = $response->json()['data'] ?? [];

                if (empty($apiMataKuliahItems)) {
                    Log::info('SyncMataKuliahJob: Tidak ada data mata kuliah baru dari API.');
                } else {
                    $matakuliahToUpsert = [];
                    
                    foreach ($apiMataKuliahItems as $matkulData) {
                        // Mapping data dari API ke kolom tabel Anda
                        // API: mk_id, kd_mk, nm_mk
                        // Tabel Anda: external_id, kode, nama
                        if (isset($matkulData['mk_id']) && isset($matkulData['kd_mk']) && isset($matkulData['nm_mk'])) {
                            $matakuliahToUpsert[] = [
                                'external_id' => $matkulData['mk_id'],
                                'kode' => $matkulData['kd_mk'],
                                'nama' => $matkulData['nm_mk'],
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        } else {
                            Log::warning('SyncMataKuliahJob: Data item matakuliah (mk_id/kd_mk/nm_mk) tidak lengkap:', ['item' => $matkulData]);
                            $parseFailedMatkulCount++;
                        }
                    }

                    // Upsert Mata Kuliah
                    if (!empty($matakuliahToUpsert)) {
                        foreach (array_chunk($matakuliahToUpsert, 250) as $chunk) { // Batching
                            try {
                                $affectedRows = MataKuliah::upsert(
                                    $chunk,
                                    ['external_id'], // Kolom unik
                                    ['nama', 'kode', 'updated_at'] // Kolom yang diupdate
                                );
                                $processedMatkulCount += $affectedRows;
                            } catch (\Illuminate\Database\QueryException $e) {
                                Log::error('SyncMataKuliahJob: DB Error saat upsert matakuliah batch: ' . $e->getMessage());
                                $dbFailedMatkulCount += count($chunk);
                                $errorsMatkul[] = "Gagal upsert matakuliah batch: " . substr($e->getMessage(),0,100);
                            }
                        }
                    }
                }
            }

            // Catat ke migration_history
            $finalStatusSuccess = ($dbFailedMatkulCount === 0 && $parseFailedMatkulCount === 0);
            if ($dbFailedMatkulCount === -1) $finalStatusSuccess = false;

            MigrationHistory::create([
                'is_mata_kuliah' => true,
                'is_dosen' => false, // Tidak ada sinkronisasi dosen di job ini lagi
                'is_mahasiswa' => false,
                'is_prodi' => false,
                'is_admin' => false,
                // Tambahkan 'status_success', 'notes', dll. jika kolomnya ada di tabel Anda
                // 'status_success' => $finalStatusSuccess,
                // 'notes' => implode('; ', $errorsMatkul) ?: ($finalStatusSuccess ? 'Sinkronisasi matakuliah berhasil.' : 'Gagal mengambil data matakuliah dari API.'),
            ]);

            Log::info("SyncMataKuliahJob Selesai. Matkul Diproses: $processedMatkulCount, Gagal Parsing Matkul: $parseFailedMatkulCount, Gagal DB Matkul: $dbFailedMatkulCount.");

        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::critical('SyncMataKuliahJob: RequestException ke API: ' . $e->getMessage(), [
                'url' => $apiUrl,
                'response' => $e->response ? $e->response->body() : 'No response body'
            ]);
            MigrationHistory::create([ 'is_mata_kuliah' => true, 'is_dosen' => false, 'notes' => 'Request API Gagal: '.substr($e->getMessage(),0,100) ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('SyncMataKuliahJob: General Exception: ' . $e->getMessage(), ['trace' => substr($e->getTraceAsString(),0,500)]);
            MigrationHistory::create([ 'is_mata_kuliah' => true, 'is_dosen' => false, 'notes' => 'Kesalahan Umum Job: '.substr($e->getMessage(),0,100) ]);
            throw $e;
        } finally {
            $jobEndTime = microtime(true);
            $jobDuration = round(($jobEndTime - $jobStartTime) * 1000, 2);
            Log::info("SyncMataKuliahJob: Total eksekusi job selesai dalam {$jobDuration} ms.");
        }
    }
}