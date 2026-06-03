<?php

namespace App\Jobs;

use App\Services\EsbSalesService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncSalesAllCredentialsSequentialJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $syncKey;
    public string $salesDate;

    public int $timeout = 10800;
    public int $tries = 1;

    public function __construct(string $syncKey, string $salesDate)
    {
        $this->syncKey = $syncKey;
        $this->salesDate = $salesDate;

        $this->onConnection('redis');
        $this->onQueue('esb-sales');
    }

    public function handle(EsbSalesService $service): void
    {
        $cacheKey = "sales_sync_all:{$this->syncKey}";

        Cache::put($cacheKey, array_merge(
            Cache::get($cacheKey, []),
            [
                'status' => 'processing',
                'started_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ]
        ), now()->addHours(12));

        Log::info('START ALL CREDENTIAL SEQUENTIAL SALES SYNC', [
            'sync_key' => $this->syncKey,
            'sales_date' => $this->salesDate,
        ]);
        
        $credentials = DB::table('tbl_api_credentials')
            ->where('is_active', 1)
            ->whereNotNull('static_token')
            ->where('static_token', '!=', '')
            ->whereRaw("LOWER(TRIM(static_token)) <> 'none'")
            ->orderBy('id')
            ->get(['id', 'credential_code', 'credential_name']);

        $totalCredentials = $credentials->count();

        $processedCredentials = 0;
        $successCredentials = 0;
        $failedCredentials = 0;

        foreach ($credentials as $credential) {

            $processedCredentials++;

            try {

                Log::info('START CREDENTIAL SALES SYNC', [
                    'credential_id' => $credential->id,
                    'credential_code' => $credential->credential_code,
                    'sales_date' => $this->salesDate,
                ]);

                $result = $service->syncSalesCredentialSequentialFull(
                    credentialId: (int) $credential->id,
                    salesDate: $this->salesDate
                );

                $lastPass = collect($result['passes'] ?? [])->last();

                if (!empty($lastPass['failed_pages'] ?? [])) {

                    $failedCredentials++;

                    throw new \RuntimeException(
                        'Masih ada failed pages: '
                        . implode(',', $lastPass['failed_pages'])
                    );
                }

                $successCredentials++;

                Cache::put($cacheKey, array_merge(
                    Cache::get($cacheKey, []),
                    [
                        'status' => 'processing',

                        'total_credentials' => $totalCredentials,
                        'processed_credentials' => $processedCredentials,
                        'success_credentials' => $successCredentials,
                        'failed_credentials' => $failedCredentials,

                        'progress' => $totalCredentials > 0
                            ? round(($processedCredentials / $totalCredentials) * 100, 2)
                            : 0,

                        'updated_at' => now()->toDateTimeString(),
                    ]
                ), now()->addHours(12));

                Log::info('DONE CREDENTIAL SALES SYNC', [
                    'credential_id' => $credential->id,
                    'credential_code' => $credential->credential_code,
                    'sales_date' => $this->salesDate,
                ]);

            } catch (\Throwable $e) {

                $failedCredentials++;

                Log::error('FAILED CREDENTIAL SALES SYNC', [
                    'credential_id' => $credential->id,
                    'credential_code' => $credential->credential_code,
                    'sales_date' => $this->salesDate,
                    'message' => $e->getMessage(),
                ]);

                throw $e;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Build summary hanya setelah semua credential selesai
        |--------------------------------------------------------------------------
        */
        $service->syncDailySummaryToLaporanBulanan($this->salesDate);

        Cache::put($cacheKey, array_merge(
            Cache::get($cacheKey, []),
            [
                'status' => 'done',

                'total_credentials' => $totalCredentials,
                'processed_credentials' => $processedCredentials,
                'success_credentials' => $successCredentials,
                'failed_credentials' => $failedCredentials,

                'progress' => 100,

                'finished_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ]
        ), now()->addHours(12));

        Cache::forget('sales_sync_all_active_key');

        Log::info('DONE ALL CREDENTIAL SEQUENTIAL SALES SYNC', [
            'sync_key' => $this->syncKey,
            'sales_date' => $this->salesDate,
        ]);
    }
}