<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\MigrationHistory;
use Illuminate\Support\Facades\Http; // Gunakan HTTP Client bawaan Laravel
use Illuminate\Support\Facades\Log;

class SyncMahasiswaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userApiToken;

    public function __construct(string $token)
    {
        $this->userApiToken = $token;
    }

    public function handle(): void
    {
        Log::info('Memulai SyncMahasiswaJob dengan token yang diterima...');
        $processedCount = 0;
        $parseFailedCount = 0;
        $dbFailedCount = 0; // Untuk menghitung kegagalan pada operasi DB massal
        $errors = [];
        $apiBaseUrl = config('myconfig.api.base_url');

        if (!$apiBaseUrl) {
            Log::error('SyncMahasiswaJob: Konfigurasi myconfig.api.base_url tidak ditemukan atau kosong.');
            MigrationHistory::create(['is_mahasiswa' => true, 'notes' => 'Config API base URL kosong.']);
            return;
        }
        if (empty($this->userApiToken)) {
            Log::error('SyncMahasiswaJob: Menerima token API pengguna yang kosong atau tidak valid dari Controller.');
            MigrationHistory::create(['is_mahasiswa' => true, 'notes' => 'Token API kosong dari controller.']);
            return;
        }

        $endpoint = '/ujian/migrations/users/mahasiswa';
        $apiUrl = rtrim($apiBaseUrl, '/') . $endpoint;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->userApiToken,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->get($apiUrl);

            if (!$response->successful()) {
                Log::error("SyncMahasiswaJob: Gagal mengambil data dari API: {$apiUrl}. Status: {$response->status()}", [
                    'response_body' => $response->json() ?: $response->body(),
                    'token_prefix' => substr($this->userApiToken, 0, 5) . '...'
                ]);
                $errors[] = "Gagal fetch dari API (Status: {$response->status()}) - " . ($response->json()['message'] ?? substr($response->body(),0,100));
                $dbFailedCount = -1; // Indikasi gagal fetch keseluruhan
            } else {
                $apiMahasiswaItems = $response->json()['data'] ?? [];
                if (empty($apiMahasiswaItems)) {
                    Log::info('SyncMahasiswaJob: Tidak ada data mahasiswa baru dari API.');
                } else {
                    $dataToUpsert = [];
                    foreach ($apiMahasiswaItems as $mhsData) {
                        // API Anda untuk mahasiswa hanya mengembalikan 'id' dan 'email'
                        // jadi 'nama' tidak bisa diambil dari $mhsData['nama']
                        if (!isset($mhsData['id'])) { // Hanya cek 'id' karena 'email' tidak akan disimpan sekarang
                            Log::warning('SyncMahasiswaJob: Data item mahasiswa (ID) tidak lengkap dari API:', ['item' => $mhsData]);
                            $parseFailedCount++;
                            $errors[] = "Data item (ID) tidak lengkap: " . ($mhsData['id'] ?? 'ID_TidakAda');
                            continue;
                        }
                        $dataToUpsert[] = [
                            'external_id' => $mhsData['id'],
                            'is_mahasiswa' => true,
                            'email' => $mhsData['email'],
                            'is_dosen' => false,
                            'is_prodi' => false,
                            'is_admin' => false,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    if (!empty($dataToUpsert)) {
                        foreach (array_chunk($dataToUpsert, 250) as $chunk) { // Proses 250 item per batch
                            try {
                                $affectedRows = User::upsert(
                                    $chunk,
                                    ['external_id', 'is_mahasiswa'], // Kolom unik
                                    // HAPUS 'name' dan 'email' dari daftar kolom yang diupdate
                                    // Jika tidak ada kolom lain yang perlu diupdate selain timestamp,
                                    // Anda bisa mengosongkan array ini atau hanya menyertakan 'updated_at'.
                                    // Jika array ketiga kosong, hanya record baru yang akan diinsert,
                                    // record yang sudah ada tidak akan diupdate (kecuali timestamp jika model menggunakannya).
                                    // Untuk memastikan record yang ada diupdate timestamp-nya:
                                    ['email', 'is_dosen', 'is_prodi', 'is_admin', 'updated_at']
                                );
                                $processedCount += $affectedRows;
                            } catch (\Illuminate\Database\QueryException $e) {
                                Log::error('SyncMahasiswaJob: DB Error saat upsert mahasiswa batch: ' . $e->getMessage(), ['query' => $e->getSql()]);
                                $dbFailedCount += count($chunk);
                                $errors[] = "Gagal upsert batch: " . substr($e->getMessage(),0,100);
                            }
                        }
                    }
                }
            }

            $finalStatusSuccess = ($dbFailedCount === 0 && $parseFailedCount === 0);
            if ($dbFailedCount === -1) $finalStatusSuccess = false; // Gagal fetch dari API

            MigrationHistory::create([
                'is_mahasiswa' => true, 'is_dosen' => false, 'is_prodi' => false, 'is_admin' => false, 'is_mata_kuliah' => false,
                // Sesuaikan pencatatan status dan notes
                // 'status_success' => $finalStatusSuccess,
                // 'records_added' => $processedCount, // Jumlah baris terpengaruh
                // 'records_failed' => $parseFailedCount + ($dbFailedCount > 0 ? $dbFailedCount : 0),
                // 'notes' => implode('; ', $errors) ?: ($finalStatusSuccess ? 'Sinkronisasi berhasil.' : ($dbFailedCount === -1 ? 'Gagal mengambil data dari API.' : 'Sebagian data gagal diproses.')),
            ]);

            Log::info("SyncMahasiswaJob Selesai. Diproses (upserted): $processedCount, Gagal Parsing API: $parseFailedCount, Gagal DB: $dbFailedCount.");

        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::critical('SyncMahasiswaJob: RequestException ke API: ' . $e->getMessage(), [
                'url' => $apiUrl,
                'response' => $e->response ? $e->response->body() : 'No response body'
            ]);
            MigrationHistory::create([ 'is_mahasiswa' => true, 'notes' => 'Request API Gagal: '.substr($e->getMessage(),0,100) ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('SyncMahasiswaJob: General Exception: ' . $e->getMessage(), ['trace' => substr($e->getTraceAsString(),0,500)]);
            MigrationHistory::create([ 'is_mahasiswa' => true, 'notes' => 'Kesalahan Umum Job: '.substr($e->getMessage(),0,100) ]);
            throw $e;
        }
    }
}