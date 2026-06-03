<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncLocationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $credentialId;

    public function __construct($credentialId)
    {
        $this->credentialId = $credentialId;
        // Ingat jalankan worker: php artisan queue:work --queue=location-data
        $this->queue = 'location-data';
    }

    public function handle()
    {
        Log::info("=== [MITRA ID: {$this->credentialId}] Memulai Job Sinkronisasi ===");

        try {
            // 1. Ambil session terbaru dari DB
            $session = DB::table('tbl_api_sessions')->where('credential_id', $this->credentialId)->first();
            
            if (!$session) {
                Log::error("[MITRA ID: {$this->credentialId}] Session tidak ditemukan di tbl_api_sessions.");
                return;
            }

            $token = $session->bearer_token;
            Log::info("[MITRA ID: {$this->credentialId}] Mencoba hit API ESB...");

            // 2. Hit API Lokasi
            $response = Http::withToken($token)->timeout(120)->get('https://services.esb.co.id/core/location');

            // 3. Logika Auto-Refresh jika 401
            if ($response->status() == 401) {
                Log::warning("[MITRA ID: {$this->credentialId}] Token 401 (Expired). Mencoba refresh token...");
                
                $refreshResponse = Http::withToken($token)->get('https://services.esb.co.id/core/auth/refresh');
                
                if ($refreshResponse->successful()) {
                    $newData = $refreshResponse->json()['result'];
                    $token = $newData['bearer_token'];

                    DB::table('tbl_api_sessions')->where('credential_id', $this->credentialId)->update([
                        'bearer_token' => $token,
                        'updated_at' => now()
                    ]);

                    Log::info("[MITRA ID: {$this->credentialId}] Token baru berhasil disimpan. Mengulangi request API...");
                    $response = Http::withToken($token)->timeout(120)->get('https://services.esb.co.id/core/location');
                } else {
                    Log::error("[MITRA ID: {$this->credentialId}] Gagal refresh token. Status: " . $refreshResponse->status());
                    Log::error("Detail Error ESB untuk {$this->credentialId}: " . $response->body());
                    return;
                }
            }

            // 4. Jika Sukses Tarik Data
            if ($response->successful()) {
                $allLocations = $response->json()['result'] ?? [];
                $totalData = count($allLocations);
                
                Log::info("[MITRA ID: {$this->credentialId}] Berhasil menarik {$totalData} data lokasi.");

                // --- PROSES BATCHING PELAN-PELAN ---
                $chunks = array_chunk($allLocations, 100);
                $totalChunks = count($chunks);

                foreach ($chunks as $index => $chunk) {
                    $batchNumber = $index + 1;
                    $batchData = [];
                    
                    foreach ($chunk as $loc) {
                        $batchData[] = [
                            'location_id_esb' => $loc['locationID'],
                            'credential_id'   => $this->credentialId,
                            'location_name'   => $loc['locationName'],
                            'updated_at'      => now(),
                        ];
                    }

                    DB::table('tbl_esb_locations')->upsert(
                        $batchData,
                        ['location_id_esb', 'credential_id'],
                        ['location_name', 'updated_at']
                    );

                    Log::info("[MITRA ID: {$this->credentialId}] Batch {$batchNumber}/{$totalChunks} berhasil disimpan.");

                    if ($batchNumber < $totalChunks) {
                        sleep(1); // Napas dulu sebentar
                    }
                }
                
                Log::info("=== [MITRA ID: {$this->credentialId}] Sinkronisasi SELESAI ===");
            } else {
                Log::error("[MITRA ID: {$this->credentialId}] API Gagal: Status " . $response->status() . " - " . $response->body());
            }

        } catch (\Exception $e) {
            Log::error("[MITRA ID: {$this->credentialId}] CRITICAL ERROR: " . $e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
        }
    }
}