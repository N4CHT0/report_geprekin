<?php

namespace App\Jobs;

use Illuminate\Support\Facades\DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\SimplePurchaseService;


class SyncSupplierJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $token;
    protected $credentialId;

    public function __construct($token, $credentialId)
    {
        $this->token = $token;
        $this->credentialId = $credentialId;
        $this->onQueue('sync-supplier');
    }

    public function handle(SimplePurchaseService $service)
    {
        $cred = DB::table('tbl_api_credentials')->where('id', $this->credentialId)->first();
        if (!$cred) return;

        $session = DB::table('tbl_api_sessions')->where('credential_id', $this->credentialId)->first();
        $token = $session ? $session->bearer_token : null;

        $page = 1;
        $limit = 100; // Saran: naikkan limit ke 100 agar lebih cepat
        $continueLoop = true;

        do {
            // Hit API dengan parameter page dan limit
            $response = Http::withToken($token)->get("https://services.esb.co.id/core/supplier", [
                'page' => $page,
                'limit' => $limit
            ]);

            // Login ulang jika token mati (Logika kamu sudah benar)
            if ($response->status() == 401 || !$token) {
                $loginRes = Http::post("https://services.esb.co.id/core/auth/login", [
                    'username' => $cred->username,
                    'password' => $cred->password
                ]);

                if ($loginRes->successful()) {
                    $token = $loginRes->json()['result']['accessToken'];
                    DB::table('tbl_api_sessions')->updateOrInsert(
                        ['credential_id' => $this->credentialId],
                        ['bearer_token' => $token, 'updated_at' => now()]
                    );
                    // Ulangi request
                    $response = Http::withToken($token)->get("https://services.esb.co.id/core/supplier", [
                        'page' => $page,
                        'limit' => $limit
                    ]);
                } else {
                    break;
                }
            }

            if ($response->successful()) {
                $result = $response->json()['result'];
                $items = $result['data'] ?? [];

                // Simpan data lewat service
                $service->syncMasterSupplier($result, $this->credentialId);

                // LOGIKA BERHENTI: 
                // Jika jumlah data yang ditarik kurang dari limit, berarti ini halaman terakhir.
                if (count($items) < $limit) {
                    $continueLoop = false;
                } else {
                    $page++;
                    sleep(1); // Kasih jeda biar aman
                }
            } else {
                $continueLoop = false;
            }
        } while ($continueLoop);
    }
}
