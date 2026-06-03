<?php

namespace App\Jobs;

use App\Services\EsbPurchaseService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SyncSimPurchaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $credentialCode;
    public $date;
    public $dateTo;          // 🔥 Tambah properti baru
    public $branch;
    public $syncKey;
    public $cashPurchaseNum; // 🔥 Tambah properti baru

    public int $timeout = 1800;
    public int $tries = 2; 

    // Update __construct untuk menerima parameter baru
    public function __construct($credentialCode, $date, $branch, $syncKey, $dateTo = null, $cashPurchaseNum = null)
    {
        $this->credentialCode = $credentialCode;
        $this->date = $date;
        $this->branch = $branch;
        $this->syncKey = $syncKey;
        $this->dateTo = $dateTo;
        $this->cashPurchaseNum = $cashPurchaseNum;

        $this->onQueue('esb-purchase');
    }

    public function handle(EsbPurchaseService $service)
    {
        $branchName = $this->branch->nama_outlet ?? $this->branch->branch_code;

        try {
            // Panggil service dengan parameter lengkap
            $result = $service->syncSingleBranch(
                $this->credentialCode,
                $this->date,
                $this->branch,
                $this->dateTo,
                $this->cashPurchaseNum
            );

            $inserted = $result['inserted'] ?? 0;

            // update progress
            $this->updateProgress($branchName, $inserted, null);

        } catch (\Throwable $e) {
            Log::error("Sync gagal [{$branchName}] => " . $e->getMessage());

            // tetap update progress tapi dengan error
            $this->updateProgress($branchName, 0, $e->getMessage());

            throw $e; // biar retry jalan
        }
    }

    /**
     * 🔥 FIX: Atomic update biar ga tabrakan antar job (Tetap seperti kode kamu)
     */
    private function updateProgress($branchName, $inserted, $error = null)
    {
        $cacheKey = "purchase_sync:{$this->syncKey}";

        Cache::lock($cacheKey . ':lock', 10)->block(5, function () use ($cacheKey, $branchName, $inserted, $error) {

            $progress = Cache::get($cacheKey, []);

            $progress = array_merge([
                'status' => 'processing',
                'total_jobs' => 1,
                'processed_jobs' => 0,
                'inserted' => 0,
                'percentage' => 0,
                'branches_done' => [],
                'errors' => []
            ], $progress);
            
            $progress['processed_jobs'] += 1;
            $progress['inserted'] += $inserted;
            $progress['last_branch'] = $branchName;

            $progress['branches_done'][] = [
                'branch' => $branchName,
                'inserted' => $inserted
            ];

            if ($error) {
                $progress['errors'][] = [
                    'branch' => $branchName,
                    'message' => $error
                ];
            }

           $total = max($progress['total_jobs'] ?? 1, 1);
            $progress['percentage'] = round(($progress['processed_jobs'] / $total) * 100);

            if ($progress['processed_jobs'] >= $progress['total_jobs']) {
                $progress['status'] = 'done';
            }

            Cache::put($cacheKey, $progress, now()->addHours(2));
        });
    }

    public function failed(\Throwable $e): void
    {
        $branchName = $this->branch->nama_outlet ?? $this->branch->branch_code;
        Log::error("Job FINAL FAIL [{$branchName}] => " . $e->getMessage());

        $cacheKey = "purchase_sync:{$this->syncKey}";

        Cache::lock($cacheKey . ':lock', 10)->block(5, function () use ($cacheKey, $branchName, $e) {
            $progress = Cache::get($cacheKey, []);
            $progress['status'] = 'partial_failed';
            $progress['errors'][] = [
                'branch' => $branchName,
                'message' => $e->getMessage()
            ];
            Cache::put($cacheKey, $progress, now()->addHours(2));
        });
    }
}