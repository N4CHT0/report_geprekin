<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * DEPRECATED.
 *
 * Jangan dispatch job ini lagi untuk sync sales ESB.
 *
 * Alasan:
 * - Job ini memproses 1 page per queue.
 * - Jika banyak page berjalan paralel, proses delete + insert di EsbSalesService::syncSalesCredentialPage()
 *   bisa race condition dan menyebabkan menu terlihat kurang.
 *
 * Pengganti:
 * - SyncSalesCredentialPreparePagesJob sekarang memproses page secara sequential per credential.
 *
 * Catatan deployment:
 * - Setelah deploy, jalankan queue:clear untuk queue esb-sales agar job lama yang masih pending tidak jalan.
 */
class SyncSalesCredentialPageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 60;
    public int $tries = 1;

    public function __construct(
        public string $syncKey,
        public string $salesDate,
        public int $credentialId,
        public string $credentialCode,
        public string $credentialName,
        public int $page,
        public int $pageCount
    ) {
        $this->onConnection('redis');
        $this->onQueue('esb-sales');
    }

    public function handle(): void
    {
        Log::warning('DEPRECATED SYNC SALES PAGE JOB SKIPPED', [
            'sync_key' => $this->syncKey,
            'sales_date' => $this->salesDate,
            'credential_id' => $this->credentialId,
            'credential_code' => $this->credentialCode,
            'page' => $this->page,
            'page_count' => $this->pageCount,
            'message' => 'Page job paralel dinonaktifkan. Gunakan SyncSalesCredentialPreparePagesJob sequential.',
        ]);
    }
}
