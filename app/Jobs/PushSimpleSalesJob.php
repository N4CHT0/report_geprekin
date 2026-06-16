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

class PushSimpleSalesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Menggunakan queue yang sama atau dipisah (misal: push-sales)
     */
    public $tries = 3;
    public $backoff = 30;

    protected $salesNum;

    /**
     * Create a new job instance.
     */
    public function __construct($salesNum)
    {
        $this->salesNum = $salesNum;
        // Kamu bisa sesuaikan nama queue-nya di sini
        $this->onQueue('push-sales');
    }

    /**
     * Execute the job.
     */
    public function handle(EsbIntegrationService $service)
    {
        Log::info("ESB-SALES-JOB [START]: Menyiapkan sinkronisasi untuk $this->salesNum");

        // 1. Ambil Data Header untuk mendapatkan credential_code/id
        $header = DB::table('tbl_simple_sales')->where('sales_num', $this->salesNum)->first();
        
        if (!$header) {
            Log::warning("ESB-SALES-JOB [SKIP]: Header tidak ditemukan untuk $this->salesNum");
            return;
        }

        // 2. Ambil Token Dinamis
        // Kita butuh credential_code outlet untuk ambil token yang benar
        // $outlet = DB::table('tbl_api_credentials')
        //     ->where('id', $header->credential_id) // Atau sesuaikan dengan kolom di tabel salesmu
        //     ->first();
            
        $token = $service->getTokenByCode('OKNHO');

        if (!$token) {
            Log::error("ESB-SALES-JOB: Gagal push $this->salesNum karena token tidak ditemukan.");
            return;
        }

        // 3. Format Payload menggunakan Service yang baru dibuat
        $payload = $service->formatSimpleSales($this->salesNum);
        $endpoint = 'https://services.esb.co.id/core/sales/simple-product-sales';

        try {
            Log::debug("ESB-SALES-JOB [PAYLOAD]: ", $payload);

            $response = Http::withToken($token)
                ->withHeaders(['Accept' => 'application/json'])
                ->timeout(30)
                ->post($endpoint, $payload);

            if ($response->successful()) {
                Log::info("ESB-SALES-JOB [SUCCESS]: $this->salesNum Berhasil. Status: " . $response->status());

                // Update status di DB lokal tbl_simple_sales
                DB::table('tbl_simple_sales')
                    ->where('sales_num', $this->salesNum)
                    ->update([
                        // 'is_pushed' => 1,
                        'pushed_at' => now(),
                        'esb_response' => 'SUCCESS'
                    ]);
            } else {
                Log::error("ESB-SALES-JOB [FAILED]: Server ESB menolak data $this->salesNum", [
                    'status' => $response->status(),
                    'error_body' => $response->json()
                ]);

                throw new \Exception("Gagal push Sales ke ESB. Status: " . $response->status());
            }
        } catch (\Exception $e) {
            Log::critical("ESB-SALES-JOB [CRITICAL]: Error pada $this->salesNum", [
                'error_message' => $e->getMessage()
            ]);

            DB::table('tbl_simple_sales')
                ->where('sales_num', $this->salesNum)
                ->update(['esb_response' => 'ERROR: ' . substr($e->getMessage(), 0, 100)]);

            throw $e;
        }
    }
}