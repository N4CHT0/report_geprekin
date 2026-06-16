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

class PushGoodsDeliveryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 30;

    protected $deliveryNum;

    /**
     * Create a new job instance.
     */
    public function __construct($deliveryNum)
    {
        $this->deliveryNum = $deliveryNum;
        $this->onQueue('push-delivery');
    }

    /**
     * Execute the job.
     */
    public function handle(EsbIntegrationService $service)
    {
        Log::info("ESB-DELIVERY-JOB [START]: Menyiapkan sinkronisasi untuk No. Delivery: $this->deliveryNum");

        // 1. Ambil Data Header Lokal
        $header = DB::table('tbl_goods_deliveries')->where('delivery_num', $this->deliveryNum)->first();
        
        if (!$header) {
            Log::warning("ESB-DELIVERY-JOB [SKIP]: Data Header tidak ditemukan di database untuk No. Delivery: $this->deliveryNum");
            return;
        }
        Log::info("ESB-DELIVERY-JOB [STEP 1]: Header lokal ditemukan. Credential ID terikat: " . ($header->credential_id ?? 'NULL'));

        // 2. Ambil Data Credential & Token Dinamis
        $credential = DB::table('tbl_api_credentials')->where('id', $header->credential_id)->first();
        if (!$credential) {
            Log::error("ESB-DELIVERY-JOB [ERROR]: Credential ID [" . ($header->credential_id ?? 'NULL') . "] tidak terdaftar di tbl_api_credentials.");
            return;
        }

        $token = $service->getTokenByCode($credential->credential_code);
        if (!$token) {
            Log::error("ESB-DELIVERY-JOB [ABORT]: Gagal mendapatkan token dari session untuk code: $credential->credential_code");
            return;
        }
        Log::info("ESB-DELIVERY-JOB [STEP 2]: Berhasil mendapatkan token awal untuk code: $credential->credential_code");

        // 3. Format Payload & Definisikan Endpoint Staging/Pilot
        $payload = $service->formatGoodsDelivery($this->deliveryNum);
        $endpoint = 'https://services.esb.co.id/pilot-core/sales/goods-delivery';

        if (!$payload) {
            Log::error("ESB-DELIVERY-JOB [ABORT]: Formatter menghasilkan array kosong/null untuk No. Delivery: $this->deliveryNum");
            return;
        }

        try {
            Log::debug("ESB-DELIVERY-JOB [PAYLOAD]: ", $payload);

            // 4. PERCOBAAN PERTAMA: Push Data ke ESB
            Log::info("ESB-DELIVERY-JOB [STEP 3]: Menembak API Percobaan Pertama ke $endpoint");
            $response = Http::withToken($token)
                ->asJson()
                ->acceptJson()
                ->timeout(35)
                ->post($endpoint, $payload);

            // 5. HANDLING AUTO-RELOGIN (JIKA TOKEN EXPIRED / 401)
            if ($response->status() == 401) {
                Log::warning("ESB-DELIVERY-JOB [401]: Token expired/invalid. Mencoba relogin otomatis...");
                
                $newToken = $service->relogin($header->credential_id);

                if ($newToken) {
                    Log::info("ESB-DELIVERY-JOB [RELOGIN-SUCCESS]: Mendapatkan token baru. Mencoba kirim ulang (Percobaan Kedua)...");
                    
                    // PERCOBAAN KEDUA: Kirim ulang data dengan token baru
                    $response = Http::withToken($newToken)
                        ->asJson()
                        ->acceptJson()
                        ->timeout(35)
                        ->post($endpoint, $payload);
                } else {
                    Log::error("ESB-DELIVERY-JOB [RELOGIN-FAILED]: Proses relogin ke ESB gagal. Tidak bisa melanjutkan pengiriman.");
                }
            }

            // 6. EVALUASI HASIL AKHIR API RESPONS
            if ($response->successful()) {
                Log::info("ESB-DELIVERY-JOB [SUCCESS]: Sinkronisasi $this->deliveryNum BERHASIL. Status Kode: " . $response->status());

                // Update status tracking ke database lokal
                DB::table('tbl_goods_deliveries')
                    ->where('delivery_num', $this->deliveryNum)
                    ->update([
                        'pushed_at' => now(),
                        'esb_response' => 'SUCCESS'
                    ]);
            } else {
                Log::error("ESB-DELIVERY-JOB [FAILED]: Server ESB Menolak data Delivery $this->deliveryNum. Status Kode: " . $response->status(), [
                    'error_body' => $response->json()
                ]);

                // Melempar exception agar antrean otomatis melakukan Retry (Tries: 3)
                throw new \Exception("Gagal push Goods Delivery ke ESB. Status: " . $response->status());
            }

        } catch (\Exception $e) {
            Log::critical("ESB-DELIVERY-JOB [CRITICAL-ERROR]: Terjadi kegagalan sistem pada No. Delivery: $this->deliveryNum", [
                'pesan_error' => $e->getMessage()
            ]);

            // Catat potongan pesan error ke database lokal untuk memudahkan monitoring
            DB::table('tbl_goods_deliveries')
                ->where('delivery_num', $this->deliveryNum)
                ->update([
                    'esb_response' => 'ERROR: ' . substr($e->getMessage(), 0, 100)
                ]);

            throw $e;
        }
    }
}