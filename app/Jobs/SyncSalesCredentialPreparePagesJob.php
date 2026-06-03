<?php

namespace App\Jobs;

use App\Services\EsbSalesService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncSalesCredentialPreparePagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Dibuat lebih panjang karena job ini sekarang memproses page secara berurutan
     * untuk 1 credential. Tujuannya menghindari race condition delete/insert per page.
     */
    public int $timeout = 7200;
    public int $tries = 1;

    public function __construct(
        public string $syncKey,
        public string $salesDate,
        public int $credentialId,
        public string $credentialCode = '',
        public string $credentialName = ''
    ) {
        $this->onConnection('redis');
        $this->onQueue('esb-sales');
    }

    public function handle(EsbSalesService $service): void
    {
        $cacheKey = "sales_sync_all:{$this->syncKey}";
        $lockKey = "sales_sync_all_state_lock:{$this->syncKey}";

        $pageCount = 1;
        $totalApiCount = 0;
        $actualApiRows = 0;
        $failedPages = [];
        $lastError = null;

        try {
            $meta = $service->getSalesCredentialPageMeta(
                credentialId: $this->credentialId,
                salesDate: $this->salesDate
            );

            $pageCount = max(1, (int) ($meta['page_count'] ?? 1));
            $totalApiCount = (int) ($meta['total_count'] ?? 0);

            $this->markPrepared($cacheKey, $lockKey, $pageCount, $totalApiCount);

            /*
             |--------------------------------------------------------------------------
             | FIX UTAMA
             |--------------------------------------------------------------------------
             | Sebelumnya semua page di-dispatch sebagai job terpisah dan bisa jalan paralel.
             | Karena syncSalesCredentialPage() melakukan delete + insert per page, proses paralel
             | rawan saling hapus saat pagination API tidak stabil.
             |
             | Sekarang: 1 credential diproses page 1..N secara berurutan di job ini.
             */
            for ($page = 1; $page <= $pageCount; $page++) {
                $result = null;
                $pageError = null;

                for ($attempt = 1; $attempt <= 5; $attempt++) {
                    try {
                        Log::info('SYNC SALES SEQUENTIAL PAGE START', [
                            'sync_key' => $this->syncKey,
                            'sales_date' => $this->salesDate,
                            'credential_id' => $this->credentialId,
                            'credential_code' => $this->credentialCode,
                            'page' => $page,
                            'page_count' => $pageCount,
                            'attempt' => $attempt,
                        ]);

                        $result = $service->syncSalesCredentialPage(
                            credentialId: $this->credentialId,
                            salesDate: $this->salesDate,
                            page: $page
                        );

                        $apiRows = (int) ($result['api_rows'] ?? 0);

                        /*
                         * Jika meta API bilang ada data, page kosong patut dicurigai sebagai
                         * response tidak stabil/timeout parsial. Retry dulu sebelum dianggap gagal.
                         */
                        if ($totalApiCount > 0 && $apiRows <= 0) {
                            throw new \RuntimeException("Page {$page} kosong padahal total API {$totalApiCount}.");
                        }

                        $actualApiRows += $apiRows;
                        $pageError = null;

                        break;
                    } catch (\Throwable $e) {
                        $pageError = $e->getMessage();
                        $lastError = $pageError;

                        Log::warning('SYNC SALES SEQUENTIAL PAGE RETRY', [
                            'sync_key' => $this->syncKey,
                            'sales_date' => $this->salesDate,
                            'credential_id' => $this->credentialId,
                            'credential_code' => $this->credentialCode,
                            'page' => $page,
                            'page_count' => $pageCount,
                            'attempt' => $attempt,
                            'error' => $pageError,
                        ]);

                        if ($attempt < 5) {
                            sleep(2 * $attempt);
                        }
                    }
                }

                if ($pageError !== null || $result === null) {
                    $failedPages[] = $page;

                    $this->updatePageProgress(
                        cacheKey: $cacheKey,
                        lockKey: $lockKey,
                        page: $page,
                        pageCount: $pageCount,
                        result: [
                            'api_rows' => 0,
                            'built_rows' => 0,
                            'inserted_rows' => 0,
                            'transaction_rows' => 0,
                            'menu_rows' => 0,
                        ],
                        error: $pageError ?: "Page {$page} gagal tanpa pesan error."
                    );

                    continue;
                }

                $this->updatePageProgress(
                    cacheKey: $cacheKey,
                    lockKey: $lockKey,
                    page: $page,
                    pageCount: $pageCount,
                    result: $result,
                    error: null
                );
            }

            if ($totalApiCount > 0 && $actualApiRows < $totalApiCount) {
                Log::warning('ESB SALES API ROW MISMATCH AFTER SEQUENTIAL SYNC', [
                    'sync_key' => $this->syncKey,
                    'sales_date' => $this->salesDate,
                    'credential_id' => $this->credentialId,
                    'credential_code' => $this->credentialCode,
                    'expected_api_rows' => $totalApiCount,
                    'actual_api_rows' => $actualApiRows,
                    'missing_api_rows' => $totalApiCount - $actualApiRows,
                    'failed_pages' => $failedPages,
                ]);
            }

            $this->markCredentialFinished(
                cacheKey: $cacheKey,
                lockKey: $lockKey,
                pageCount: $pageCount,
                totalApiCount: $totalApiCount,
                actualApiRows: $actualApiRows,
                failedPages: $failedPages,
                lastError: $lastError
            );
        } catch (\Throwable $e) {
            Log::error('SYNC SALES PREPARE/SEQUENTIAL FAILED', [
                'sync_key' => $this->syncKey,
                'sales_date' => $this->salesDate,
                'credential_id' => $this->credentialId,
                'credential_code' => $this->credentialCode,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);

            $this->markCredentialFatalFailed($cacheKey, $lockKey, $e->getMessage());
        }
    }

    protected function markPrepared(
        string $cacheKey,
        string $lockKey,
        int $pageCount,
        int $totalApiCount
    ): void {
        Cache::store('redis')->lock($lockKey, 10)->block(10, function () use (
            $cacheKey,
            $pageCount,
            $totalApiCount
        ) {
            $state = Cache::store('redis')->get($cacheKey, []);
            $perCredential = $state['per_credential'] ?? [];
            $key = $this->credentialCode !== '' ? $this->credentialCode : (string) $this->credentialId;

            $perCredential[$key] = array_merge($perCredential[$key] ?? [], [
                'status' => 'processing',
                'credential_id' => $this->credentialId,
                'credential_code' => $this->credentialCode,
                'credential_name' => $this->credentialName,
                'page_count' => $pageCount,
                'processed_pages' => 0,
                'success_pages' => 0,
                'failed_pages' => 0,
                'api_total_count' => $totalApiCount,
                'api_rows' => 0,
                'built_rows' => 0,
                'inserted_rows' => 0,
                'prepared_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ]);

            $logs = $state['logs'] ?? [];
            $logs[] = [
                'credential_id' => $this->credentialId,
                'credential_code' => $this->credentialCode,
                'status' => 'prepared',
                'page_count' => $pageCount,
                'api_total_count' => $totalApiCount,
                'message' => "Prepared {$pageCount} page untuk diproses sequential.",
                'updated_at' => now()->toDateTimeString(),
            ];

            $state['prepared_credentials'] = (int) ($state['prepared_credentials'] ?? 0) + 1;
            $state['total_pages'] = (int) ($state['total_pages'] ?? 0) + $pageCount;
            $state['dispatched_pages'] = (int) ($state['dispatched_pages'] ?? 0) + $pageCount;
            $state['per_credential'] = $perCredential;
            $state['logs'] = array_slice($logs, -30);
            $state['message'] = 'Page sales sedang diproses sequential per credential.';
            $state['updated_at'] = now()->toDateTimeString();

            Cache::store('redis')->put($cacheKey, $state, now()->addHours(12));
        });
    }

    protected function updatePageProgress(
        string $cacheKey,
        string $lockKey,
        int $page,
        int $pageCount,
        array $result,
        ?string $error
    ): void {
        Cache::store('redis')->lock($lockKey, 10)->block(10, function () use (
            $cacheKey,
            $page,
            $pageCount,
            $result,
            $error
        ) {
            $state = Cache::store('redis')->get($cacheKey, []);
            $credentialKey = $this->credentialCode !== '' ? $this->credentialCode : (string) $this->credentialId;

            $perCredential = $state['per_credential'] ?? [];
            $current = $perCredential[$credentialKey] ?? [
                'status' => 'processing',
                'credential_id' => $this->credentialId,
                'credential_code' => $this->credentialCode,
                'credential_name' => $this->credentialName,
                'page_count' => $pageCount,
                'processed_pages' => 0,
                'success_pages' => 0,
                'failed_pages' => 0,
                'api_rows' => 0,
                'built_rows' => 0,
                'inserted_rows' => 0,
            ];

            $current['processed_pages'] = (int) ($current['processed_pages'] ?? 0) + 1;
            $current['page_count'] = max((int) ($current['page_count'] ?? 0), $pageCount);
            $current['api_rows'] = (int) ($current['api_rows'] ?? 0) + (int) ($result['api_rows'] ?? 0);
            $current['built_rows'] = (int) ($current['built_rows'] ?? 0) + (int) ($result['built_rows'] ?? 0);
            $current['inserted_rows'] = (int) ($current['inserted_rows'] ?? 0) + (int) ($result['inserted_rows'] ?? 0);
            $current['last_page'] = $page;
            $current['updated_at'] = now()->toDateTimeString();

            if ($error === null) {
                $current['success_pages'] = (int) ($current['success_pages'] ?? 0) + 1;
                $state['success_pages'] = (int) ($state['success_pages'] ?? 0) + 1;
            } else {
                $current['failed_pages'] = (int) ($current['failed_pages'] ?? 0) + 1;
                $current['last_error'] = $error;
                $state['failed_pages'] = (int) ($state['failed_pages'] ?? 0) + 1;
            }

            $state['processed_pages'] = (int) ($state['processed_pages'] ?? 0) + 1;
            $state['total_api_rows'] = (int) ($state['total_api_rows'] ?? 0) + (int) ($result['api_rows'] ?? 0);
            $state['total_built_rows'] = (int) ($state['total_built_rows'] ?? 0) + (int) ($result['built_rows'] ?? 0);
            $state['total_inserted_rows'] = (int) ($state['total_inserted_rows'] ?? 0) + (int) ($result['inserted_rows'] ?? 0);

            $current['status'] = (int) ($current['failed_pages'] ?? 0) > 0
                ? 'processing_with_errors'
                : 'processing';

            $perCredential[$credentialKey] = $current;
            $state['per_credential'] = $perCredential;

            $logs = $state['logs'] ?? [];
            $logs[] = [
                'credential_id' => $this->credentialId,
                'credential_code' => $this->credentialCode,
                'status' => $error === null ? 'success' : 'failed',
                'page' => $page,
                'page_count' => $pageCount,
                'api_rows' => (int) ($result['api_rows'] ?? 0),
                'built_rows' => (int) ($result['built_rows'] ?? 0),
                'inserted_rows' => (int) ($result['inserted_rows'] ?? 0),
                'message' => $error,
                'updated_at' => now()->toDateTimeString(),
            ];

            $state['logs'] = array_slice($logs, -30);

            $totalPages = (int) ($state['total_pages'] ?? 0);
            $processedPages = (int) ($state['processed_pages'] ?? 0);
            $state['progress'] = $totalPages > 0
                ? min(100, (int) floor(($processedPages / $totalPages) * 100))
                : 0;

            $state['message'] = 'Sync sales sequential per credential/page sedang berjalan.';
            $state['updated_at'] = now()->toDateTimeString();

            Cache::store('redis')->put($cacheKey, $state, now()->addHours(12));
        });
    }

    protected function markCredentialFinished(
        string $cacheKey,
        string $lockKey,
        int $pageCount,
        int $totalApiCount,
        int $actualApiRows,
        array $failedPages,
        ?string $lastError
    ): void {
        Cache::store('redis')->lock($lockKey, 10)->block(10, function () use (
            $cacheKey,
            $pageCount,
            $totalApiCount,
            $actualApiRows,
            $failedPages,
            $lastError
        ) {
            $state = Cache::store('redis')->get($cacheKey, []);
            $credentialKey = $this->credentialCode !== '' ? $this->credentialCode : (string) $this->credentialId;

            $perCredential = $state['per_credential'] ?? [];
            $current = $perCredential[$credentialKey] ?? [];

            $hasFailedPage = ! empty($failedPages);
            $hasApiMismatch = $totalApiCount > 0 && $actualApiRows < $totalApiCount;

            $current['status'] = ($hasFailedPage || $hasApiMismatch) ? 'failed' : 'success';
            $current['page_count'] = $pageCount;
            $current['api_total_count'] = $totalApiCount;
            $current['api_rows'] = $actualApiRows;
            $current['api_row_mismatch'] = $hasApiMismatch;
            $current['failed_page_list'] = $failedPages;
            $current['last_error'] = $lastError;
            $current['finished_at'] = now()->toDateTimeString();
            $current['updated_at'] = now()->toDateTimeString();

            $perCredential[$credentialKey] = $current;
            $state['per_credential'] = $perCredential;

            $state['processed_credentials'] = (int) ($state['processed_credentials'] ?? 0) + 1;
            $state['processed_branches'] = (int) ($state['processed_branches'] ?? 0) + 1;

            if ($current['status'] === 'success') {
                $state['success_credentials'] = (int) ($state['success_credentials'] ?? 0) + 1;
                $state['success_branches'] = (int) ($state['success_branches'] ?? 0) + 1;
            } else {
                $state['failed_credentials'] = (int) ($state['failed_credentials'] ?? 0) + 1;
                $state['failed_branches'] = (int) ($state['failed_branches'] ?? 0) + 1;
            }

            $this->finalizeIfAllDone($state);

            Cache::store('redis')->put($cacheKey, $state, now()->addHours(12));
        });
    }

    protected function markCredentialFatalFailed(string $cacheKey, string $lockKey, string $error): void
    {
        Cache::store('redis')->lock($lockKey, 10)->block(10, function () use ($cacheKey, $error) {
            $state = Cache::store('redis')->get($cacheKey, []);
            $credentialKey = $this->credentialCode !== '' ? $this->credentialCode : (string) $this->credentialId;

            $perCredential = $state['per_credential'] ?? [];
            $perCredential[$credentialKey] = array_merge($perCredential[$credentialKey] ?? [], [
                'status' => 'failed',
                'credential_id' => $this->credentialId,
                'credential_code' => $this->credentialCode,
                'credential_name' => $this->credentialName,
                'message' => $error,
                'last_error' => $error,
                'updated_at' => now()->toDateTimeString(),
            ]);

            $logs = $state['logs'] ?? [];
            $logs[] = [
                'credential_id' => $this->credentialId,
                'credential_code' => $this->credentialCode,
                'status' => 'failed',
                'message' => $error,
                'updated_at' => now()->toDateTimeString(),
            ];

            $state['prepared_credentials'] = (int) ($state['prepared_credentials'] ?? 0) + 1;
            $state['processed_credentials'] = (int) ($state['processed_credentials'] ?? 0) + 1;
            $state['failed_credentials'] = (int) ($state['failed_credentials'] ?? 0) + 1;
            $state['processed_branches'] = (int) ($state['processed_branches'] ?? 0) + 1;
            $state['failed_branches'] = (int) ($state['failed_branches'] ?? 0) + 1;
            $state['per_credential'] = $perCredential;
            $state['logs'] = array_slice($logs, -30);
            $state['updated_at'] = now()->toDateTimeString();

            $this->finalizeIfAllDone($state);

            Cache::store('redis')->put($cacheKey, $state, now()->addHours(12));
        });
    }

    protected function finalizeIfAllDone(array &$state): void
    {
        $preparedCredentials = (int) ($state['prepared_credentials'] ?? 0);
        $processedCredentials = (int) ($state['processed_credentials'] ?? 0);
        $totalCredentials = (int) ($state['total_credentials'] ?? 0);

        if (
            $totalCredentials > 0
            && $preparedCredentials >= $totalCredentials
            && $processedCredentials >= $totalCredentials
            && ! ($state['finalized'] ?? false)
        ) {
            $state['status'] = 'done';
            $state['progress'] = 100;
            $state['message'] = 'Sync sales selesai. Summary laporan bulanan sedang dijalankan.';
            $state['finished_at'] = now()->toDateTimeString();
            $state['finalized'] = true;

            SyncSalesSummaryToLaporanBulananJob::dispatch($this->salesDate)
                ->onConnection('redis')
                ->onQueue('esb-sales');
        }
    }
}
