<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User; // Model User untuk menyimpan data prodi
use App\Models\MigrationHistory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class SyncProdiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userApiToken;

    /**
     * Create a new job instance.
     * @param string|null $token Token otentikasi jika diperlukan
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
        Log::info('Memulai SyncProdiJob...');

        $processedCount = 0;
        $parseFailedCount = 0;
        $dbFailedCount = 0;
        $errors = [];
        $apiBaseUrl = config('myconfig.api.base_url', env('API_BASE_URL'));

        if (!$apiBaseUrl) {
            Log::error('SyncProdiJob: API_BASE_URL tidak diset di config atau .env');
            MigrationHistory::create([
                'is_prodi' => true,
                'is_mahasiswa' => false,
                'is_dosen' => false,
                'is_admin' => false,
                'is_mata_kuliah' => false,
                // 'notes' => 'Konfigurasi API_BASE_URL tidak ditemukan.'
            ]);
            return;
        }

        if (empty($this->userApiToken) && env('EXTERNAL_API_PRODI_REQUIRES_TOKEN', true)) {
            Log::error('SyncProdiJob: Menerima token API pengguna yang kosong atau tidak valid dari Controller, dan token dibutuhkan.');
            MigrationHistory::create([
                'is_prodi' => true,
                'is_mahasiswa' => false,
                // 'notes' => 'Token API kosong dari controller.'
            ]);
            return;
        }

        $endpoint = '/ujian/migrations/users/prodi'; // Endpoint API Prodi
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
            Log::info("SyncProdiJob: Pengambilan data dari API ({$apiUrl}) selesai dalam {$fetchApiDuration} ms.");

            if (!$response->successful()) {
                Log::error("SyncProdiJob: Gagal mengambil data dari API: {$apiUrl}. Status: {$response->status()}", [
                    'response_body' => $response->json() ?: $response->body(),
                    'token_prefix' => $this->userApiToken ? substr($this->userApiToken, 0, 5) . '...' : 'No token used'
                ]);
                $errors[] = "Gagal fetch dari API (Status: {$response->status()}) - " . ($response->json()['message'] ?? substr($response->body(),0,100));
                $dbFailedCount = -1; // Indikasi gagal fetch keseluruhan
            } else {
                $apiProdiItems = $response->json()['data'] ?? [];

                if (empty($apiProdiItems)) {
                    Log::info('SyncProdiJob: Tidak ada data prodi baru dari API.');
                } else {
                    $dataToUpsert = [];
                    foreach ($apiProdiItems as $prodiData) {
                        if (!isset($prodiData['id'])) {
                            Log::warning('SyncProdiJob: Data item prodi (ID) tidak lengkap dari API:', ['item' => $prodiData]);
                            $parseFailedCount++;
                            $errors[] = "Data item prodi (ID) tidak lengkap: " . ($prodiData['id'] ?? 'ID_TidakAda');
                            continue;
                        }
                        $dataToUpsert[] = [
                            'external_id' => $prodiData['id'],
                            'email' => $prodiData['email'],
                            'is_prodi' => true, // Set role ini ke true
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    if (!empty($dataToUpsert)) {
                        foreach (array_chunk($dataToUpsert, 250) as $chunk) {
                            try {
                                $affectedRows = User::upsert(
                                    $chunk,
                                    ['external_id'], // Kunci unik
                                    ['is_prodi', 'email', 'updated_at'] // Kolom yang diupdate
                                );
                                $processedCount += $affectedRows;
                            } catch (\Illuminate\Database\QueryException $e) {
                                Log::error('SyncProdiJob: DB Error saat upsert prodi batch: ' . $e->getMessage(), ['query' => $e->getSql()]);
                                $dbFailedCount += count($chunk);
                                $errors[] = "Gagal upsert prodi batch: " . substr($e->getMessage(),0,100);
                            }
                        }
                    }
                }
            }

            $finalStatusSuccess = ($dbFailedCount === 0 && $parseFailedCount === 0);
            if ($dbFailedCount === -1) $finalStatusSuccess = false;

            MigrationHistory::create([
                'is_prodi' => true,
                'is_mahasiswa' => false,
                'is_dosen' => false,
                'is_admin' => false,
                'is_mata_kuliah' => false,
                // 'status_success' => $finalStatusSuccess,
                // 'notes' => implode('; ', $errors) ?: ($finalStatusSuccess ? 'Sinkronisasi prodi berhasil.' : ($dbFailedCount === -1 ? 'Gagal mengambil data prodi dari API.' : 'Sebagian data prodi gagal diproses.')),
            ]);

            Log::info("SyncProdiJob Selesai. Diproses (upserted): $processedCount, Gagal Parsing API: $parseFailedCount, Gagal DB: $dbFailedCount.");

        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::critical('SyncProdiJob: RequestException ke API: ' . $e->getMessage(), [
                'url' => $apiUrl,
                'response' => $e->response ? $e->response->body() : 'No response body'
            ]);
            MigrationHistory::create([ 'is_prodi' => true, 'notes' => 'Request API Prodi Gagal: '.substr($e->getMessage(),0,100) ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('SyncProdiJob: General Exception: ' . $e->getMessage(), ['trace' => substr($e->getTraceAsString(),0,500)]);
            MigrationHistory::create([ 'is_prodi' => true, 'notes' => 'Kesalahan Umum Job Prodi: '.substr($e->getMessage(),0,100) ]);
            throw $e;
        } finally {
            $jobEndTime = microtime(true);
            $jobDuration = round(($jobEndTime - $jobStartTime) * 1000, 2);
            Log::info("SyncProdiJob: Total eksekusi job selesai dalam {$jobDuration} ms.");
        }
    }
}