<?php

namespace App\Services\UjianProses;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\ConnectionException;
// Anda bisa membuat custom exception jika diperlukan, misalnya:
// use App\Exceptions\ShuffleServiceException;

class ExpressShuffleClientService
{
    protected string $serviceUrl;
    protected int $timeout;

    public function __construct()
    {
        $this->serviceUrl = env('EXPRESS_SHUFFLE_SERVICE_URL', 'http://localhost:8080/shuffle/specific-list');
        $this->timeout = (int)env('EXPRESS_SHUFFLE_TIMEOUT', 30); // Ambil timeout dari .env, default 30 detik
    }

    /**
     * Mengirim daftar soal ke layanan Express.js untuk diacak.
     *
     * @param array $soalListForExpress
     * @param array $shuffleConfig Konfigurasi pengacakan.
     * @return array|null Daftar soal yang sudah diacak ('shuffledSoalList') atau null jika gagal.
     * @throws ConnectionException Jika koneksi gagal.
     * @throws \Exception Jika terjadi error lain atau respons tidak valid (atau custom exception).
     */
    public function shuffleSoalList(array $soalListForExpress, array $shuffleConfig): ?array
    {
        Log::info('[ExpressClientService] Mengirim permintaan shuffle ke Express.js', [
            'url' => $this->serviceUrl,
            'jumlah_soal' => count($soalListForExpress),
            'config' => $shuffleConfig
        ]);

        $response = Http::timeout($this->timeout)->post($this->serviceUrl, [
            'soalList' => $soalListForExpress,
            'config' => $shuffleConfig,
        ]);

        if ($response->failed()) {
            Log::error('[ExpressClientService] Gagal request ke Express.js', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            // throw new ShuffleServiceException('Gagal mengambil soal teracak dari layanan eksternal.', $response->status());
            // Atau kembalikan null / throw \Exception biasa agar controller bisa menangani
            throw new \RuntimeException('Gagal memproses soal melalui layanan eksternal (Kode: HTTP_ERR)', $response->status());
        }

        $responseData = $response->json();

        if (!isset($responseData['shuffledSoalList']) || !is_array($responseData['shuffledSoalList'])) {
            Log::error('[ExpressClientService] Format respons dari Express.js tidak valid', ['response_excerpt' => substr(json_encode($responseData), 0, 500)]);
            // throw new ShuffleServiceException('Format respons soal teracak dari layanan eksternal tidak valid.');
            throw new \RuntimeException('Format respons soal teracak dari layanan eksternal tidak valid. (Kode: RESP_FORMAT_ERR)');
        }
        
        Log::info('[ExpressClientService] Berhasil mendapatkan respons shuffle dari Express.js', ['jumlah_soal_diterima' => count($responseData['shuffledSoalList'])]);
        return $responseData['shuffledSoalList'];
    }
}