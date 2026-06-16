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

class PushPurchaseRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 30;

    protected $prNum;

    /**
     * Create a new job instance.
     */
    public function __construct($prNum)
    {
        $this->prNum = $prNum;
        $this->onQueue('push-pr');
    }

    /**
     * Execute the job.
     */
    public function handle(EsbIntegrationService $service)
    {
        Log::info("ESB-PR-JOB [START]: Menyiapkan sinkronisasi untuk No. PR: $this->prNum");

        // 1. Ambil Data Header Lokal
        $header = DB::table('tbl_purchase_requests')->where('pr_num', $this->prNum)->first();
        
        if (!$header) {
            Log::warning("ESB-PR-JOB [SKIP]: Data Header tidak ditemukan di database untuk No. PR: $this->prNum");
            return;
        }
        Log::info("ESB-PR-JOB [STEP 1]: Header lokal ditemukan. Credential ID terikat: " . ($header->credential_id ?? 'NULL'));

        // 2. Ambil Data Credential & Token Dinamis
        $credential = DB::table('tbl_api_credentials')->where('id', $header->credential_id)->first();
        if (!$credential) {
            Log::error("ESB-PR-JOB [ERROR]: Credential ID [" . ($header->credential_id ?? 'NULL') . "] tidak terdaftar di tbl_api_credentials.");
            return;
        }

        $token = $service->getTokenByCode($credential->credential_code);
        if (!$token) {
            Log::error("ESB-PR-JOB [ABORT]: Gagal mendapatkan token dari session untuk code: $credential->credential_code");
            return;
        }
        Log::info("ESB-PR-JOB [STEP 2]: Berhasil mendapatkan token awal untuk code: $credential->credential_code");

        // 3. Format Payload & Definisikan Endpoint Staging/Pilot
        $payload = $service->formatPurchaseRequest($this->prNum);
        $endpoint = 'https://services.esb.co.id/core/purchase/purchase-request';

        if (!$payload) {
            Log::error("ESB-PR-JOB [ABORT]: Formatter menghasilkan array kosong/null untuk No. PR: $this->prNum");
            return;
        }

        try {
            Log::debug("ESB-PR-JOB [PAYLOAD]: ", $payload);

            // 4. PERCOBAAN PERTAMA: Push Data ke ESB
            Log::info("ESB-PR-JOB [STEP 3]: Menembak API Percobaan Pertama ke $endpoint");
            $response = Http::withToken($token)
                ->asJson()
                ->acceptJson()
                ->timeout(35)
                ->post($endpoint, $payload);

            // 5. HANDLING AUTO-RELOGIN (JIKA TOKEN EXPIRED / 401)
            if ($response->status() == 401) {
                Log::warning("ESB-PR-JOB [401]: Token expired/invalid. Mencoba relogin otomatis...");
                
                $newToken = $service->relogin($header->credential_id);

                if ($newToken) {
                    Log::info("ESB-PR-JOB [RELOGIN-SUCCESS]: Mendapatkan token baru. Mencoba kirim ulang (Percobaan Kedua)...");
                    
                    // PERCOBAAN KEDUA: Kirim ulang data dengan token baru
                    $response = Http::withToken($newToken)
                        ->asJson()
                        ->acceptJson()
                        ->timeout(35)
                        ->post($endpoint, $payload);
                } else {
                    Log::error("ESB-PR-JOB [RELOGIN-FAILED]: Proses relogin ke ESB gagal. Tidak bisa melanjutkan pengiriman.");
                }
            }

            // 6. EVALUASI HASIL AKHIR API RESPONS
            if ($response->successful()) {
                Log::info("ESB-PR-JOB [SUCCESS]: Sinkronisasi $this->prNum BERHASIL. Status Kode: " . $response->status());

                // Update status tracking ke database lokal
                DB::table('tbl_purchase_requests')
                    ->where('pr_num', $this->prNum)
                    ->update([
                        'pushed_at' => now(),
                        'esb_response' => 'SUCCESS'
                    ]);
            } else {
                Log::error("ESB-PR-JOB [FAILED]: Server ESB Menolak data PR $this->prNum. Status Kode: " . $response->status(), [
                    'error_body' => $response->json()
                ]);

                throw new \Exception("Gagal push Purchase Request ke ESB. Status: " . $response->status());
            }

        } catch (\Exception $e) {
            Log::critical("ESB-PR-JOB [CRITICAL-ERROR]: Terjadi kegagalan sistem pada No. PR: $this->prNum", [
                'pesan_error' => $e->getMessage()
            ]);

            DB::table('tbl_purchase_requests')
                ->where('pr_num', $this->prNum)
                ->update([
                    'esb_response' => 'ERROR: ' . substr($e->getMessage(), 0, 100)
                ]);

            throw $e;
        }
    }
}