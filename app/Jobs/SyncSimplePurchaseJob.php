<?php

namespace App\Jobs;

use App\Services\SimplePurchaseService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Pastikan Facade ini di-import

class SyncSimplePurchaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300;

    protected $dateFrom, $dateTo, $credentialId;

    public function __construct($dateFrom, $dateTo, $credentialId)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->credentialId = $credentialId;

        $this->onQueue('sync-purchase');
    }

    public function handle(SimplePurchaseService $service)
    {
        // 1. Log awal untuk memastikan Job terpanggil oleh worker
        Log::info("=== START SYNC ID: {$this->credentialId} ===");

        // 2. Ambil Session & Kredensial Login
        // Cek kembali: apakah c.credential_code benar-benar berisi ID numerik yang sama dengan s.credential_id?
        // Jika c.credential_code adalah string (seperti 'BOD'), ganti join-nya ke c.id
        $session = DB::table('tbl_api_sessions as s')
            ->join('tbl_api_credentials as c', 's.credential_id', '=', 'c.id') 
            ->where('s.credential_id', $this->credentialId)
            ->select('s.*', 'c.username', 'c.password')
            ->first();

        if (!$session) {
            Log::error("SYNC ABORTED: Session/Credential tidak ditemukan untuk ID: {$this->credentialId}");
            return;
        }

        $token = $session->bearer_token;
        Log::info("Processing for user: {$session->username}");

        // 3. Hit API ESB
        try {
            $response = Http::withToken($token)->get("https://services.esb.co.id/core/purchase/simple-purchase", [
                'dateFrom' => $this->dateFrom,
                'dateTo'   => $this->dateTo,
            ]);

            // 4. Handle Unauthorized (Auto-Login)
            if ($response->status() == 401) {
                Log::warning("Token expired for {$session->username}. Attempting Auto-Login...");

                $loginRes = Http::post("https://services.esb.co.id/core/auth/login", [
                    'username' => $session->username,
                    'password' => $session->password
                ]);

                if ($loginRes->successful()) {
                    $token = $loginRes->json()['result']['accessToken'];

                    DB::table('tbl_api_sessions')->where('credential_id', $this->credentialId)->update([
                        'bearer_token' => $token,
                        'updated_at'   => now()
                    ]);

                    Log::info("Re-Login Success. Retrying API request...");

                    $response = Http::withToken($token)->get("https://services.esb.co.id/core/purchase/simple-purchase", [
                        'dateFrom' => $this->dateFrom,
                        'dateTo'   => $this->dateTo,
                        'sort'     => '-cashPurchaseNum'
                    ]);
                } else {
                    Log::error("Auto-Login Failed for {$session->username}. API Response: " . $loginRes->body());
                    return;
                }
            }

            // 5. Proses Data jika sukses
            if ($response->successful()) {
                $listData = $response->json()['result']['data'] ?? [];
                Log::info("Data found: " . count($listData) . " records for {$session->username}");

                foreach ($listData as $headerItem) {
                    $service->syncHeader([$headerItem], $this->credentialId);

                    $purchaseNum = $headerItem['cashPurchaseNum'];
                    $detailResponse = Http::withToken($token)
                        ->get("https://services.esb.co.id/core/purchase/simple-purchase/{$purchaseNum}");

                    if ($detailResponse->successful()) {
                        $detailData = $detailResponse->json()['result'];
                        // $service->syncDetail($detailResponse->json()['result']);
                        $service->syncDetail($detailData, $this->credentialId);
                    }
                    
                    usleep(200000); // Jeda singkat 200ms
                }
                
                Log::info("=== FINISHED SYNC ID: {$this->credentialId} ===");
            } else {
                Log::error("API Request Failed. Status: " . $response->status());
            }

        } catch (\Exception $e) {
            Log::error("CRITICAL ERROR in Job Sync: " . $e->getMessage());
        }
    }
}