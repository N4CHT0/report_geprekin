<?php

namespace App\Jobs;

use App\Services\EsbIntegrationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PushSimpleTransferJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Nama antrean khusus agar tidak mengganggu proses lain.
     */
    // public $queue = 'esb_sync';

    /**
     * Jumlah percobaan ulang jika gagal (Retry).
     */
    public $tries = 3;

    /**
     * Jeda waktu (detik) sebelum mencoba lagi setelah gagal.
     */
    public $backoff = 30;

    protected $transferNum;

    /**
     * Create a new job instance.
     */
    public function __construct($transferNum)
    {
        $this->transferNum = $transferNum;
        $this->onQueue('push-transfer');
    }

    /**
     * Execute the job.
     */
    public function handle(EsbIntegrationService $service)
    {
        Log::info("ESB-JOB [START]: Menyiapkan sinkronisasi untuk $this->transferNum");

        // 1. Ambil payload dari Service
        $payload = $service->formatSimpleTransfer($this->transferNum);

        if (!$payload) {
            Log::warning("ESB-JOB [SKIP]: Data tidak valid atau tidak ditemukan untuk $this->transferNum");
            return;
        }

        // 2. Persiapkan Konfigurasi API
        // DEFINE ENDPOINT DISINI
        $endpoint = 'https://services.esb.co.id/core/simple-transfer';

        $token = $service->getTokenByCode('OKNHO');

        if (!$token) {
            Log::error("ESB-JOB: Gagal push $this->transferNum karena token OKNHO tidak ada.");
            return;
        }

        try {
            Log::debug("ESB-JOB [PAYLOAD]: ", $payload);

            // 3. Tembak API ESB dengan Timeout agar tidak nge-hang
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])
                ->timeout(30)
                ->post($endpoint, $payload);

            // 4. Cek Respon
            if ($response->successful()) {
                Log::info("ESB-JOB [SUCCESS]: $this->transferNum berhasil sinkron. Status: " . $response->status());

                // Update status di DB lokal
                DB::table('tbl_simple_transfer')
                    ->where('transfer_num', $this->transferNum)
                    ->update([
                        'pushed_at' => now(),
                        'esb_response' => 'SUCCESS'
                    ]);
            } else {
                // Log detail jika error (misal 400 atau 500)
                Log::error("ESB-JOB [FAILED]: Server ESB menolak data $this->transferNum", [
                    'status' => $response->status(),
                    'error_body' => $response->json()
                ]);

                // Lempar exception agar Job melakukan Retry otomatis
                throw new \Exception("Gagal push ke ESB. Status: " . $response->status());
            }
        } catch (\Exception $e) {
            Log::critical("ESB-JOB [CRITICAL]: Terjadi kesalahan teknis pada $this->transferNum", [
                'error_message' => $e->getMessage()
            ]);

            // Catat error terakhir di database lokal agar bisa di-trace manual
            DB::table('tbl_simple_transfer')
                ->where('transfer_num', $this->transferNum)
                ->update(['esb_response' => 'ERROR: ' . substr($e->getMessage(), 0, 100)]);

            throw $e;
        }
    }
}
