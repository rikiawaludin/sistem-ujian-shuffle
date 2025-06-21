<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User; // Model User untuk menyimpan data admin
use App\Models\MigrationHistory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class SyncAdminJob implements ShouldQueue
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
        Log::info('Memulai SyncAdminJob...');

        $processedCount = 0;
        $parseFailedCount = 0;
        $dbFailedCount = 0;
        $errors = [];
        $apiBaseUrl = config('myconfig.api.base_url', env('API_BASE_URL'));

        if (!$apiBaseUrl) {
            Log::error('SyncAdminJob: API_BASE_URL tidak diset di config atau .env');
            MigrationHistory::create([
                'is_admin' => true,
                'is_mahasiswa' => false,
                'is_dosen' => false,
                'is_prodi' => false,
                'is_mata_kuliah' => false,
                // 'notes' => 'Konfigurasi API_BASE_URL tidak ditemukan.'
            ]);
            return;
        }

        if (empty($this->userApiToken) && env('EXTERNAL_API_ADMIN_REQUIRES_TOKEN', true)) {
            Log::error('SyncAdminJob: Menerima token API pengguna yang kosong atau tidak valid dari Controller, dan token dibutuhkan.');
            MigrationHistory::create([
                'is_admin' => true,
                'is_mahasiswa' => false,
                // 'notes' => 'Token API kosong dari controller.'
            ]);
            return;
        }

        $endpoint = '/ujian/migrations/users/admin'; // Endpoint API Admin
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
            Log::info("SyncAdminJob: Pengambilan data dari API ({$apiUrl}) selesai dalam {$fetchApiDuration} ms.");

            if (!$response->successful()) {
                Log::error("SyncAdminJob: Gagal mengambil data dari API: {$apiUrl}. Status: {$response->status()}", [
                    'response_body' => $response->json() ?: $response->body(),
                    'token_prefix' => $this->userApiToken ? substr($this->userApiToken, 0, 5) . '...' : 'No token used'
                ]);
                $errors[] = "Gagal fetch dari API (Status: {$response->status()}) - " . ($response->json()['message'] ?? substr($response->body(),0,100));
                $dbFailedCount = -1; // Indikasi gagal fetch keseluruhan
            } else {
                $apiAdminItems = $response->json()['data'] ?? [];

                if (empty($apiAdminItems)) {
                    Log::info('SyncAdminJob: Tidak ada data admin baru dari API.');
                } else {
                    $dataToUpsert = [];
                    foreach ($apiAdminItems as $adminData) {
                        if (!isset($adminData['id'])) {
                            Log::warning('SyncAdminJob: Data item admin (ID) tidak lengkap dari API:', ['item' => $adminData]);
                            $parseFailedCount++;
                            $errors[] = "Data item admin (ID) tidak lengkap: " . ($adminData['id'] ?? 'ID_TidakAda');
                            continue;
                        }
                        $dataToUpsert[] = [
                            'external_id' => $adminData['id'],
                            'email' => $adminData['email'],
                            'is_admin' => true, // Set role ini ke true
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
                                    ['is_admin', 'email', 'updated_at'] // Kolom yang diupdate
                                );
                                $processedCount += $affectedRows;
                            } catch (\Illuminate\Database\QueryException $e) {
                                Log::error('SyncAdminJob: DB Error saat upsert admin batch: ' . $e->getMessage(), ['query' => $e->getSql()]);
                                $dbFailedCount += count($chunk);
                                $errors[] = "Gagal upsert admin batch: " . substr($e->getMessage(),0,100);
                            }
                        }
                    }
                }
            }

            $finalStatusSuccess = ($dbFailedCount === 0 && $parseFailedCount === 0);
            if ($dbFailedCount === -1) $finalStatusSuccess = false;

            MigrationHistory::create([
                'is_admin' => true,
                'is_mahasiswa' => false,
                'is_dosen' => false,
                'is_prodi' => false,
                'is_mata_kuliah' => false,
                // 'status_success' => $finalStatusSuccess,
                // 'notes' => implode('; ', $errors) ?: ($finalStatusSuccess ? 'Sinkronisasi admin berhasil.' : ($dbFailedCount === -1 ? 'Gagal mengambil data admin dari API.' : 'Sebagian data admin gagal diproses.')),
            ]);

            Log::info("SyncAdminJob Selesai. Diproses (upserted): $processedCount, Gagal Parsing API: $parseFailedCount, Gagal DB: $dbFailedCount.");

        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::critical('SyncAdminJob: RequestException ke API: ' . $e->getMessage(), [
                'url' => $apiUrl,
                'response' => $e->response ? $e->response->body() : 'No response body'
            ]);
            MigrationHistory::create([ 'is_admin' => true, 'notes' => 'Request API Admin Gagal: '.substr($e->getMessage(),0,100) ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('SyncAdminJob: General Exception: ' . $e->getMessage(), ['trace' => substr($e->getTraceAsString(),0,500)]);
            MigrationHistory::create([ 'is_admin' => true, 'notes' => 'Kesalahan Umum Job Admin: '.substr($e->getMessage(),0,100) ]);
            throw $e;
        } finally {
            $jobEndTime = microtime(true);
            $jobDuration = round(($jobEndTime - $jobStartTime) * 1000, 2);
            Log::info("SyncAdminJob: Total eksekusi job selesai dalam {$jobDuration} ms.");
        }
    }
}