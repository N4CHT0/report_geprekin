<?php
namespace App\Jobs;

use App\Services\CustomerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncCustomerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Tentukan timeout dan jumlah percobaan
     */
    public $timeout = 600; 
    public $tries = 3;

    public function __construct()
    {
        // Set nama queue agar spesifik
        $this->onQueue('sync-cust');
    }

    public function handle(CustomerService $service)
    {
        $page = 1;
        $hasNext = true;

        Log::info("Job SyncCustomer dimulai untuk antrean sync-cust.");

        while ($hasNext) {
            // Memanggil service yang sudah kita buat tadi
            $result = $service->syncFromApi($page);

            // Berhenti jika tidak ada data yang diproses atau sudah tidak ada halaman berikutnya
            if ($result['processed'] === 0 || $result['next_page'] === false) {
                $hasNext = false;
            } else {
                $page++;
            }
            
            // Opsional: Log progres per halaman
            Log::info("Sync Customer Page $page: Berhasil memproses {$result['processed']} data.");
        }

        Log::info("Job SyncCustomer Selesai.");
    }
}