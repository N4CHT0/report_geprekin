<?php

namespace App\Jobs;

use App\Services\EsbSalesService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncSalesSingleCredentialJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $syncKey;
    public string $salesDate;
    public int $credentialId;

    public int $timeout = 7200;
    public int $tries = 1;

    public function __construct(
        string $syncKey,
        string $salesDate,
        int $credentialId
    ) {
        $this->syncKey = $syncKey;
        $this->salesDate = $salesDate;
        $this->credentialId = $credentialId;

        $this->onConnection('redis');
        $this->onQueue('esb-sales');
    }

    // public function handle(EsbSalesService $service): void
    // {
    //     Log::info('START FULL SEQUENTIAL SALES SYNC', [
    //         'credential_id' => $this->credentialId,
    //         'sales_date' => $this->salesDate,
    //     ]);

    //     $maxPass = 3;

    //     $finalExpectedRows = 0;
    //     $finalActualRows = 0;
    //     $finalInsertedRows = 0;

    //     for ($pass = 1; $pass <= $maxPass; $pass++) {

    //         Log::info('START SALES SYNC PASS', [
    //             'credential_id' => $this->credentialId,
    //             'sales_date' => $this->salesDate,
    //             'pass' => $pass,
    //         ]);

    //         $meta = $service->getSalesCredentialPageMeta(
    //             $this->credentialId,
    //             $this->salesDate
    //         );

    //         $pageCount = max(1, (int) ($meta['page_count'] ?? 1));
    //         $expectedRows = (int) ($meta['total_count'] ?? 0);

    //         $actualRows = 0;
    //         $insertedRows = 0;

    //         $failedPages = [];

    //         /*
    //         |--------------------------------------------------------------------------
    //         | TARIK SEMUA PAGE BERURUTAN
    //         |--------------------------------------------------------------------------
    //         */
    //         for ($page = 1; $page <= $pageCount; $page++) {

    //             $success = false;

    //             /*
    //             |--------------------------------------------------------------------------
    //             | RETRY PER PAGE
    //             |--------------------------------------------------------------------------
    //             */
    //             for ($attempt = 1; $attempt <= 5; $attempt++) {

    //                 try {

    //                     Log::info('SYNC SALES PAGE', [
    //                         'credential_id' => $this->credentialId,
    //                         'sales_date' => $this->salesDate,
    //                         'page' => $page,
    //                         'page_count' => $pageCount,
    //                         'attempt' => $attempt,
    //                     ]);

    //                     $result = $service->syncSalesCredentialPage(
    //                         credentialId: $this->credentialId,
    //                         salesDate: $this->salesDate,
    //                         page: $page
    //                     );

    //                     $apiRows = (int) ($result['api_rows'] ?? 0);
    //                     $insertRows = (int) ($result['inserted_rows'] ?? 0);

    //                     /*
    //                     |--------------------------------------------------------------------------
    //                     | VALIDASI PAGE KOSONG
    //                     |--------------------------------------------------------------------------
    //                     */
    //                     if ($apiRows <= 0) {

    //                         Log::warning('EMPTY SALES PAGE RESPONSE', [
    //                             'credential_id' => $this->credentialId,
    //                             'sales_date' => $this->salesDate,
    //                             'page' => $page,
    //                             'attempt' => $attempt,
    //                         ]);

    //                         sleep(2 * $attempt);

    //                         continue;
    //                     }

    //                     $actualRows += $apiRows;
    //                     $insertedRows += $insertRows;

    //                     $success = true;

    //                     Log::info('SYNC SALES PAGE SUCCESS', [
    //                         'credential_id' => $this->credentialId,
    //                         'sales_date' => $this->salesDate,
    //                         'page' => $page,
    //                         'api_rows' => $apiRows,
    //                         'inserted_rows' => $insertRows,
    //                     ]);

    //                     break;

    //                 } catch (\Throwable $e) {

    //                     Log::warning('SYNC SALES PAGE RETRY', [
    //                         'credential_id' => $this->credentialId,
    //                         'sales_date' => $this->salesDate,
    //                         'page' => $page,
    //                         'attempt' => $attempt,
    //                         'error' => $e->getMessage(),
    //                     ]);

    //                     sleep(2 * $attempt);
    //                 }
    //             }

    //             /*
    //             |--------------------------------------------------------------------------
    //             | PAGE GAGAL TOTAL
    //             |--------------------------------------------------------------------------
    //             */
    //             if (! $success) {

    //                 $failedPages[] = $page;

    //                 Log::error('SYNC SALES PAGE FAILED TOTAL', [
    //                     'credential_id' => $this->credentialId,
    //                     'sales_date' => $this->salesDate,
    //                     'page' => $page,
    //                 ]);
    //             }
    //         }

    //         $finalExpectedRows = $expectedRows;
    //         $finalActualRows = $actualRows;
    //         $finalInsertedRows += $insertedRows;

    //         Log::info('SYNC SALES PASS RESULT', [
    //             'credential_id' => $this->credentialId,
    //             'sales_date' => $this->salesDate,
    //             'pass' => $pass,
    //             'expected_rows' => $expectedRows,
    //             'actual_rows' => $actualRows,
    //             'inserted_rows' => $insertedRows,
    //             'failed_pages' => $failedPages,
    //         ]);

    //         /*
    //         |--------------------------------------------------------------------------
    //         | SEMUA PAGE SUKSES DAN ROW MATCH
    //         |--------------------------------------------------------------------------
    //         */
    //         if (
    //             empty($failedPages)
    //             && $actualRows >= $expectedRows
    //         ) {

    //             Log::info('FULL SALES SYNC SUCCESS', [
    //                 'credential_id' => $this->credentialId,
    //                 'sales_date' => $this->salesDate,
    //                 'expected_rows' => $expectedRows,
    //                 'actual_rows' => $actualRows,
    //             ]);

    //             break;
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | RETRY FULL PASS
    //         |--------------------------------------------------------------------------
    //         */
    //         Log::warning('RETRY FULL SALES PASS', [
    //             'credential_id' => $this->credentialId,
    //             'sales_date' => $this->salesDate,
    //             'next_pass' => $pass + 1,
    //         ]);

    //         sleep(5);
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | BUILD SUMMARY DAN SEMUA LAPORAN TURUNAN
    //     |--------------------------------------------------------------------------
    //     */
    //     $service->syncDailySummaryToLaporanBulanan(
    //         $this->salesDate
    //     );

    //     Log::info('FINAL FULL SALES SYNC DONE', [
    //         'credential_id' => $this->credentialId,
    //         'sales_date' => $this->salesDate,
    //         'expected_rows' => $finalExpectedRows,
    //         'actual_rows' => $finalActualRows,
    //         'inserted_rows' => $finalInsertedRows,
    //     ]);
    // }

    public function handle(EsbSalesService $service): void
        {
            Log::info('START SALES SYNC CREDENTIAL JOB', [
                'sync_key' => $this->syncKey,
                'credential_id' => $this->credentialId,
                'sales_date' => $this->salesDate,
            ]);

            try {
                $result = $service->syncSalesCredentialSequentialFull(
                    credentialId: $this->credentialId,
                    salesDate: $this->salesDate
                );

                Log::info('DONE SALES SYNC CREDENTIAL JOB', [
                    'sync_key' => $this->syncKey,
                    'credential_id' => $this->credentialId,
                    'sales_date' => $this->salesDate,
                    'result' => $result,
                ]);

            } catch (\Throwable $e) {
                Log::error('FAILED SALES SYNC CREDENTIAL JOB - SKIPPED', [
                    'sync_key' => $this->syncKey,
                    'credential_id' => $this->credentialId,
                    'sales_date' => $this->salesDate,
                    'message' => $e->getMessage(),
                ]);

                return;
            }
        }
}