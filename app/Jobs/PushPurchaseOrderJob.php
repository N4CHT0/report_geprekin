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

class PushPurchaseOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 30;

    protected $poNum;

    /**
     * Create a new job instance.
     */
    public function __construct($poNum)
    {
        $this->poNum = $poNum;
        $this->onQueue('push-po');
    }

    /**
     * Execute the job.
     */
    public function handle(EsbIntegrationService $service)
    {
        Log::info("ESB-PO-JOB [START]: Menyiapkan sinkronisasi untuk No. PO: $this->poNum");

        // 1. Ambil Data Header Lokal
        $header = DB::table('tbl_purchase_orders')->where('po_num', $this->poNum)->first();
        
        if (!$header) {
            Log::warning("ESB-PO-JOB [SKIP]: Data Header tidak ditemukan di database untuk No. PO: $this->poNum");
            return;
        }
        Log::info("ESB-PO-JOB [STEP 1]: Header lokal ditemukan. Credential ID terikat: " . ($header->credential_id ?? 'NULL'));

        // 2. Ambil Data Credential & Token Dinamis
        $credential = DB::table('tbl_api_credentials')->where('id', $header->credential_id)->first();
        if (!$credential) {
            Log::error("ESB-PO-JOB [ERROR]: Credential ID [" . ($header->credential_id ?? 'NULL') . "] tidak terdaftar di tbl_api_credentials.");
            return;
        }

        $token = $service->getTokenByCode($credential->credential_code);
        if (!$token) {
            Log::error("ESB-PO-JOB [ABORT]: Gagal mendapatkan token dari session untuk code: $credential->credential_code");
            return;
        }
        Log::info("ESB-PO-JOB [STEP 2]: Berhasil mendapatkan token awal untuk code: $credential->credential_code");

        // 3. Format Payload & Definisikan Endpoint Staging/Pilot
        $payload = $service->formatPurchaseOrder($this->poNum);
        $endpoint = 'https://services.esb.co.id/pilot-core/purchase/purchase-order';

        if (!$payload) {
            Log::error("ESB-PO-JOB [ABORT]: Formatter menghasilkan array kosong/null untuk No. PO: $this->poNum");
            return;
        }

        try {
            Log::debug("ESB-PO-JOB [PAYLOAD]: ", $payload);

            // 4. PERCOBAAN PERTAMA: Push Data ke ESB
            Log::info("ESB-PO-JOB [STEP 3]: Menembak API Percobaan Pertama ke $endpoint");
            $response = Http::withToken($token)
                ->asJson()
                ->acceptJson()
                ->timeout(35)
                ->post($endpoint, $payload);

            // 5. HANDLING AUTO-RELOGIN (JIKA TOKEN EXPIRED / 401)
            if ($response->status() == 401) {
                Log::warning("ESB-PO-JOB [401]: Token expired/invalid. Mencoba relogin otomatis...");
                
                $newToken = $service->relogin($header->credential_id);

                if ($newToken) {
                    Log::info("ESB-PO-JOB [RELOGIN-SUCCESS]: Mendapatkan token baru. Mencoba kirim ulang (Percobaan Kedua)...");
                    
                    // PERCOBAAN KEDUA: Kirim ulang data dengan token baru
                    $response = Http::withToken($newToken)
                        ->asJson()
                        ->acceptJson()
                        ->timeout(35)
                        ->post($endpoint, $payload);
                } else {
                    Log::error("ESB-PO-JOB [RELOGIN-FAILED]: Proses relogin ke ESB gagal. Tidak bisa melanjutkan pengiriman.");
                }
            }

            // 6. EVALUASI HASIL AKHIR API RESPONS
            if ($response->successful()) {
                Log::info("ESB-PO-JOB [SUCCESS]: Sinkronisasi $this->poNum BERHASIL. Status Kode: " . $response->status());

                // Update status tracking ke database lokal
                DB::table('tbl_purchase_orders')
                    ->where('po_num', $this->poNum)
                    ->update([
                        'pushed_at' => now(),
                        'esb_response' => 'SUCCESS'
                    ]);
            } else {
                Log::error("ESB-PO-JOB [FAILED]: Server ESB Menolak data PO $this->poNum. Status Kode: " . $response->status(), [
                    'error_body' => $response->json()
                ]);

                // Melempar exception agar antrean otomatis melakukan Retry (Tries: 3)
                throw new \Exception("Gagal push PO ke ESB. Status: " . $response->status());
            }

        } catch (\Exception $e) {
            Log::critical("ESB-PO-JOB [CRITICAL-ERROR]: Terjadi kegagalan sistem pada No. PO: $this->poNum", [
                'pesan_error' => $e->getMessage()
            ]);

            // Catat potongan pesan error ke database lokal untuk memudahkan monitoring via UI SCM
            DB::table('tbl_purchase_orders')
                ->where('po_num', $this->poNum)
                ->update([
                    'esb_response' => 'ERROR: ' . substr($e->getMessage(), 0, 100)
                ]);

            throw $e;
        }
    }
}