<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User; // Model User untuk menyimpan data dosen
use App\Models\MigrationHistory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon; // Untuk timestamp

class SyncDosenJob implements ShouldQueue
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
        Log::info('Memulai SyncDosenJob...');

        $processedCount = 0;
        $parseFailedCount = 0;
        $dbFailedCount = 0;
        $errors = [];
        $apiBaseUrl = config('myconfig.api.base_url', env('API_BASE_URL'));

        if (!$apiBaseUrl) {
            Log::error('SyncDosenJob: API_BASE_URL tidak diset di config atau .env');
            MigrationHistory::create([
                'is_dosen' => true,
                'is_mahasiswa' => false,
                'is_prodi' => false,
                'is_admin' => false,
                'is_mata_kuliah' => false,
                // 'notes' => 'Konfigurasi API_BASE_URL tidak ditemukan.'
            ]);
            return;
        }

        if (empty($this->userApiToken) && env('EXTERNAL_API_DOSEN_REQUIRES_TOKEN', true)) { // Tambahkan check ENV jika token wajib
            Log::error('SyncDosenJob: Menerima token API pengguna yang kosong atau tidak valid dari Controller, dan token dibutuhkan.');
            MigrationHistory::create([
                'is_dosen' => true,
                'is_mahasiswa' => false,
                // 'notes' => 'Token API kosong dari controller.'
            ]);
            return;
        }

        $endpoint = '/ujian/migrations/users/dosen'; // Endpoint API Dosen
        $apiUrl = rtrim($apiBaseUrl, '/') . $endpoint;

        try {
            $fetchApiStartTime = microtime(true);
            
            $httpRequest = Http::withHeaders(['Accept' => 'application/json']);
            if (!empty($this->userApiToken)) {
                $httpRequest->withToken($this->userApiToken);
            }
            
            $response = $httpRequest->get($apiUrl);
            
            $fetchApiEndTime = microtime(true);
            $fetchApiDuration = round(($fetchApiEndTime - $fetchApiStartTime) * 1000, 2);
            Log::info("SyncDosenJob: Pengambilan data dari API ({$apiUrl}) selesai dalam {$fetchApiDuration} ms.");

            if (!$response->successful()) {
                Log::error("SyncDosenJob: Gagal mengambil data dari API: {$apiUrl}. Status: {$response->status()}", [
                    'response_body' => $response->json() ?: $response->body(),
                    'token_prefix' => $this->userApiToken ? substr($this->userApiToken, 0, 5) . '...' : 'No token used'
                ]);
                $errors[] = "Gagal fetch dari API (Status: {$response->status()}) - " . ($response->json()['message'] ?? substr($response->body(),0,100));
                $dbFailedCount = -1; // Indikasi gagal fetch keseluruhan
            } else {
                $apiDosenItems = $response->json()['data'] ?? [];

                if (empty($apiDosenItems)) {
                    Log::info('SyncDosenJob: Tidak ada data dosen baru dari API.');
                } else {
                    $dataToUpsert = [];
                    foreach ($apiDosenItems as $dosenData) {
                        // API menyediakan 'id' dan 'email', tapi kita hanya simpan 'id' sebagai 'external_id'
                        if (!isset($dosenData['id'])) { // Hanya cek 'id'
                            Log::warning('SyncDosenJob: Data item dosen (ID) tidak lengkap dari API:', ['item' => $dosenData]);
                            $parseFailedCount++;
                            $errors[] = "Data item (ID) tidak lengkap: " . ($dosenData['id'] ?? 'ID_TidakAda');
                            continue;
                        }
                        $dataToUpsert[] = [
                            'external_id' => $dosenData['id'],
                            'email' => $dosenData['email'],
                            'is_dosen' => true, // Set role ini ke true
                            // 'email' dan 'name' tidak ada dari API ini, jadi tidak perlu disertakan.
                            // Role lain (mahasiswa, prodi, admin) tidak diatur di sini.
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    if (!empty($dataToUpsert)) {
                        foreach (array_chunk($dataToUpsert, 250) as $chunk) {
                            try {
                                $affectedRows = User::upsert(
                                    $chunk,
                                    ['external_id'], // Kunci unik HANYA external_id
                                    ['is_dosen', 'email', 'updated_at'] // Kolom yang diupdate HANYA role ini dan timestamp
                                );
                                $processedCount += $affectedRows;
                            } catch (\Illuminate\Database\QueryException $e) {
                                Log::error('SyncDosenJob: DB Error saat upsert dosen batch: ' . $e->getMessage(), ['query' => $e->getSql()]);
                                $dbFailedCount += count($chunk);
                                $errors[] = "Gagal upsert dosen batch: " . substr($e->getMessage(),0,100);
                            }
                        }
                    }
                }
            }

            $finalStatusSuccess = ($dbFailedCount === 0 && $parseFailedCount === 0);
            if ($dbFailedCount === -1) $finalStatusSuccess = false;

            MigrationHistory::create([
                'is_dosen' => true,
                'is_mahasiswa' => false,
                'is_prodi' => false,
                'is_admin' => false,
                'is_mata_kuliah' => false,
                // 'status_success' => $finalStatusSuccess,
                // 'notes' => implode('; ', $errors) ?: ($finalStatusSuccess ? 'Sinkronisasi dosen berhasil.' : ($dbFailedCount === -1 ? 'Gagal mengambil data dosen dari API.' : 'Sebagian data dosen gagal diproses.')),
            ]);

            Log::info("SyncDosenJob Selesai. Diproses (upserted): $processedCount, Gagal Parsing API: $parseFailedCount, Gagal DB: $dbFailedCount.");

        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::critical('SyncDosenJob: RequestException ke API: ' . $e->getMessage(), [
                'url' => $apiUrl,
                'response' => $e->response ? $e->response->body() : 'No response body'
            ]);
            MigrationHistory::create([ 'is_dosen' => true, 'notes' => 'Request API Dosen Gagal: '.substr($e->getMessage(),0,100) ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('SyncDosenJob: General Exception: ' . $e->getMessage(), ['trace' => substr($e->getTraceAsString(),0,500)]);
            MigrationHistory::create([ 'is_dosen' => true, 'notes' => 'Kesalahan Umum Job Dosen: '.substr($e->getMessage(),0,100) ]);
            throw $e;
        } finally {
            $jobEndTime = microtime(true);
            $jobDuration = round(($jobEndTime - $jobStartTime) * 1000, 2);
            Log::info("SyncDosenJob: Total eksekusi job selesai dalam {$jobDuration} ms.");
        }
    }
}