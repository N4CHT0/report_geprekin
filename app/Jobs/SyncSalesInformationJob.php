<?php

namespace App\Jobs;

use App\Services\SalesInformationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB; // WAJIB ADA
use Illuminate\Support\Facades\Log;

class SyncSalesInformationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Tambahkan timeout per job agar tidak macet selamanya
    public $timeout = 180; 

    protected $credentialId, $date, $page, $token, $logId;

    public function __construct($credentialId, $date, $page, $token, $logId)
    {
        $this->credentialId = $credentialId;
        $this->date = $date;
        $this->page = $page;
        $this->token = $token;
        $this->logId = $logId;
    }

    public function handle(SalesInformationService $service)
    {
        try {
            $inserted = $service->processSinglePage($this->credentialId, $this->date, $this->page, $this->token);
            
            if ($inserted !== false) {
                // Update progres ke tabel log
                DB::table('tbl_esb_sync_logs')
                    ->where('id', $this->logId)
                    ->update([
                        'total_inserted' => DB::raw("total_inserted + $inserted"),
                        'current_page' => $this->page,
                        'updated_at' => now()
                    ]);
            } else {
                Log::error("Sync ESB Gagal pada Page: {$this->page}", ['log_id' => $this->logId]);
            }
        } catch (\Exception $e) {
            Log::error("Error Job ESB: " . $e->getMessage());
            // Tandai log sebagai failed jika ini halaman terakhir atau error kritis
            throw $e; // Throw agar Laravel Queue tahu job ini gagal dan perlu retry
        }
    }
}