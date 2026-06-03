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

class PushSimplePurchaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah percobaan ulang jika gagal (Retry).
     */
    public $tries = 3;

    /**
     * Jeda waktu (detik) sebelum mencoba lagi setelah gagal.
     */
    public $backoff = 30;

    protected $purchaseNum;

    /**
     * Create a new job instance.
     */
    public function __construct($purchaseNum)
    {
        $this->purchaseNum = $purchaseNum;
        // Kita set nama queue khusus untuk purchase
        $this->onQueue('push-purchase');
    }

    /**
     * Execute the job.
     */
    public function handle(EsbIntegrationService $service)
    {
        Log::info("ESB-PURCHASE-JOB [START]: Menyiapkan data untuk $this->purchaseNum");

        // 1. Ambil data Header untuk mendapatkan credential_id
        $header = DB::table('tbl_simple_purchases')->where('purchase_num', $this->purchaseNum)->first();

        if (!$header) {
            Log::warning("ESB-PURCHASE-JOB [SKIP]: Header tidak ditemukan untuk $this->purchaseNum");
            return;
        }

        // 2. Ambil Credential Code untuk mendapatkan Token yang Benar
        $credential = DB::table('tbl_api_credentials')->where('id', $header->credential_id)->first();

        if (!$credential) {
            Log::error("ESB-PURCHASE-JOB [ERROR]: Credential tidak ditemukan untuk ID: $header->credential_id");
            return;
        }

        $token = $service->getTokenByCode($credential->credential_code);

        if (!$token) {
            Log::error("ESB-PURCHASE-JOB [ABORT]: Gagal mendapatkan token untuk code: $credential->credential_code");
            return;
        }

        // 3. Persiapkan Payload & Endpoint
        $payload = $service->formatSimplePurchase($this->purchaseNum);
        $endpoint = 'https://services.esb.co.id/core/purchase/simple-purchase';

        if (!$payload) {
            Log::error("ESB-PURCHASE-JOB [SKIP]: Gagal membuat payload untuk $this->purchaseNum");
            return;
        }

        try {
            Log::debug("ESB-PURCHASE-JOB [PAYLOAD]: ", $payload);

            // 4. Eksekusi Push ke ESB
            $response = Http::withToken($token)
                ->withHeaders(['Accept' => 'application/json'])
                ->timeout(45) // Diberi waktu lebih lama karena proses purchase di ESB biasanya berat
                ->post($endpoint, $payload);

            if ($response->status() == 401) {
                Log::warning("Token expired, mencoba login ulang...");

                // Ambil token baru
                $newToken = $service->relogin($header->credential_id);

                if ($newToken) {
                    // Coba tembak lagi pakai token baru
                    $response = Http::withToken($newToken)
                        ->asJson()
                        ->post($endpoint, $payload);
                }
            }

            // 5. Cek Hasil Respon
            if ($response->successful()) {
                Log::info("ESB-PURCHASE-JOB [SUCCESS]: $this->purchaseNum Berhasil. Status: " . $response->status());

                // Update status di DB lokal
                DB::table('tbl_simple_purchases')
                    ->where('purchase_num', $this->purchaseNum)
                    ->update([
                        // 'is_pushed' => 1,
                        'pushed_at' => now(),
                        'esb_response' => 'SUCCESS'
                    ]);
            } else {
                Log::error("ESB-PURCHASE-JOB [FAILED]: ESB Menolak data $this->purchaseNum", [
                    'status' => $response->status(),
                    'error_body' => $response->json()
                ]);

                throw new \Exception("Gagal push Purchase ke ESB. Status: " . $response->status());
            }
        } catch (\Exception $e) {
            Log::critical("ESB-PURCHASE-JOB [CRITICAL]: Terjadi error pada $this->purchaseNum", [
                'message' => $e->getMessage()
            ]);

            // Catat pesan error terakhir di database lokal
            DB::table('tbl_simple_purchases')
                ->where('purchase_num', $this->purchaseNum)
                ->update(['esb_response' => 'ERROR: ' . substr($e->getMessage(), 0, 100)]);

            throw $e; // Memicu Retry otomatis oleh Laravel
        }
    }
}
