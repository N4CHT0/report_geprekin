<?php

namespace App\Jobs;

use App\Services\BahanService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncBahanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $page;
    public $tries = 3;
    public $timeout = 300; // 2 menit cukup karena cuma per halaman

    public function __construct($page = 1)
    {
        $this->page = $page;
    }

    public function handle(BahanService $bahanService)
    {
        try {
            $limit = 10;
            $result = $bahanService->syncFromApi($this->page, $limit);
            
            $total = $result['total'];
            // $processedSoFar = $this->page * $limit;
            $processedSoFar = (($this->page - 1) * $limit) + $result['processed'];
            if ($processedSoFar > $total) $processedSoFar = $total;
    
            // HITUNG PERSENTASE & SIMPAN DI CACHE
            // $progress = round(($processedSoFar / $total) * 100);
            $progress = $total > 0 
            ? round(($processedSoFar / $total) * 100)
            : 0;
            \Cache::put('sync_product_progress', [
                'percentage' => $progress,
                'processed' => $processedSoFar,
                'total' => $total,
                'status' => ($progress >= 100) ? 'completed' : 'running'
            ], 600); // simpan selama 10 menit
            
            \Log::info('Progress calculation', [
                'processed' => $processedSoFar,
                'total' => $total
            ]);
    
            // Lanjut ke halaman berikutnya jika belum selesai
            if ($processedSoFar < $total && $result['processed'] > 0) {
                SyncBahanJob::dispatch($this->page + 1)->onQueue('bahan')->delay(now()->addSeconds(1));
            }
        } catch (\Exception $e) {
            \Cache::put('sync_product_progress', ['status' => 'error', 'message' => $e->getMessage()], 600);
            throw $e;
        }
    }
}