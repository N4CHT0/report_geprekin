<?php

namespace App\Services;

use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SimpleTransferService
{
    protected string $baseUrl = 'https://services.esb.co.id/core';

    public function getTokenByCompanyCode(string $companyCode): ?string
    {
        $companyCode = strtoupper(trim($companyCode));

        if ($companyCode === '') {
            return null;
        }

        return cache()->remember("stf_token_login:{$companyCode}", 3000, function () use ($companyCode) {
            $credential = DB::table('tbl_api_credentials')
                ->whereRaw('UPPER(credential_code) = ?', [$companyCode])
                ->where('is_active', 1)
                ->first();

            if (! $credential) {
                throw new \Exception("Credential {$companyCode} tidak ditemukan.");
            }

            $loginUrl = rtrim($this->baseUrl, '/') . '/auth/login';

            $response = Http::withoutVerifying()
                ->timeout(90)
                ->acceptJson()
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($loginUrl, [
                    'username' => $credential->username,
                    'password' => $credential->password,
                ]);

            if (! $response->successful()) {
                throw new \Exception(
                    "Login STF gagal [{$companyCode}] HTTP {$response->status()} => " . $response->body()
                );
            }

            $token =
                data_get($response->json(), 'result.accessToken')
                ?? data_get($response->json(), 'result.token')
                ?? data_get($response->json(), 'accessToken')
                ?? data_get($response->json(), 'token');

            if (! $token) {
                throw new \Exception("Login STF sukses tapi token tidak ditemukan: " . $response->body());
            }

            return $token;
        });
    }

    protected function getWithToken(string $companyCode, string $path, array $params = [])
    {
        $companyCode = strtoupper(trim($companyCode));
        $token = $this->getTokenByCompanyCode($companyCode);

        if (! $token) {
            throw new Exception("Token Simple Transfer tidak ditemukan untuk company_code {$companyCode}.");
        }

        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($path, '/');

        $response = Http::withoutVerifying()
            ->timeout(90)
            ->retry(3, 2000, throw: false)
            ->acceptJson()
            ->withHeaders([
                'Authorization' => 'Bearer ' . trim($token),
                'Content-Type' => 'application/json',
                'company-code' => $companyCode,
            ])
            ->get($url, $params);

            Log::info('STF REQUEST DEBUG', [
                'company_code' => $companyCode,
                'url' => $url,
                'params' => $params,
                'token_exists' => !empty($token),
                'token_length' => strlen((string) $token),
                'token_prefix' => substr((string) $token, 0, 10),
            ]);

        if (
            $response->status() === 401 ||
            data_get($response->json(), 'message') === 'Invalid Token' ||
            data_get($response->json(), 'message') === 'Unauthorized'
        ) {
            cache()->forget("stf_token:{$companyCode}");

            Log::error('STF API Unauthorized.', [
                'company_code' => $companyCode,
                'url' => $url,
                'params' => $params,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new Exception("STF API Unauthorized untuk company_code {$companyCode}: " . $response->body());
        }

        if ($response->successful()) {
            DB::table('tbl_api_sessions')
                ->whereRaw('UPPER(company_code) = ?', [$companyCode])
                ->update(['last_used_at' => now()]);
        }

        return $response;
    }

    public function getSimpleTransferCredentialPageMeta(
        string $companyCode,
        string $startDate,
        string $endDate,
        int $limit = 100
    ): array {
        $companyCode = strtoupper(trim($companyCode));
        $limit = max(1, (int) $limit);

        $response = $this->getWithToken($companyCode, '/simple-transfer', [
            'dateFrom' => $startDate,
            'dateTo'   => $endDate,
            'page'     => 1,
            'limit'    => $limit,
            'sort'     => '-simpleTransferDate',
        ]);

        if (! $response->successful()) {
            throw new Exception(
                "STF meta gagal [{$companyCode}] HTTP {$response->status()} => " . $response->body()
            );
        }

        $totalCount = (int) data_get($response->json(), 'result.count', 0);
        $pageCount  = $totalCount > 0 ? (int) ceil($totalCount / $limit) : 1;

        return [
            'company_code' => $companyCode,
            'date_from'    => $startDate,
            'date_to'      => $endDate,
            'page_count'   => $pageCount,
            'total_count'  => $totalCount,
            'per_page'     => $limit,
        ];
    }

    /**
     * Ambil semua data berdasarkan range tanggal, tapi dieksekusi per hari.
     * Ini lebih aman untuk API yang pagination-nya tidak stabil ketika range tanggal panjang.
     */
    public function syncSimpleTransferCredentialDateRange(
        string $companyCode,
        string $startDate,
        string $endDate,
        int $limit = 100
    ): array {
        $companyCode = strtoupper(trim($companyCode));
        $limit = max(1, (int) $limit);

        $totalApiRows = 0;
        $totalSavedHeader = 0;
        $totalDetailJobs = 0;
        $totalSkippedRows = 0;
        $totalDuplicateRows = 0;
        $totalPages = 0;
        $totalCountFromMeta = 0;

        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');

            $meta = $this->getSimpleTransferCredentialPageMeta(
                companyCode: $companyCode,
                startDate: $dateString,
                endDate: $dateString,
                limit: $limit
            );

            $pageCount = max(1, (int) ($meta['page_count'] ?? 1));
            $totalPages += $pageCount;
            $totalCountFromMeta += (int) ($meta['total_count'] ?? 0);

            for ($page = 1; $page <= $pageCount; $page++) {
                $result = $this->syncSimpleTransferCredentialPage(
                    companyCode: $companyCode,
                    startDate: $dateString,
                    endDate: $dateString,
                    page: $page,
                    limit: $limit
                );

                $totalApiRows += (int) ($result['api_rows'] ?? 0);
                $totalSavedHeader += (int) ($result['saved_header'] ?? 0);
                $totalDetailJobs += (int) ($result['detail_jobs'] ?? 0);
                $totalSkippedRows += (int) ($result['skipped_rows'] ?? 0);
                $totalDuplicateRows += (int) ($result['duplicate_rows'] ?? 0);
            }
        }

        return [
            'company_code'            => $companyCode,
            'date_from'               => $startDate,
            'date_to'                 => $endDate,
            'limit'                   => $limit,
            'total_pages'             => $totalPages,
            'total_count_from_meta'   => $totalCountFromMeta,
            'total_api_rows'          => $totalApiRows,
            'total_saved_header'      => $totalSavedHeader,
            'total_detail_jobs'       => $totalDetailJobs,
            'total_skipped_rows'      => $totalSkippedRows,
            'total_duplicate_rows'    => $totalDuplicateRows,
        ];
    }

    public function syncSimpleTransferCredentialPage(
        string $companyCode,
        string $startDate,
        string $endDate,
        int $page,
        int $limit = 100
    ): array {
        $companyCode = strtoupper(trim($companyCode));
        $page = max(1, (int) $page);
        $limit = max(1, (int) $limit);

        $response = $this->getWithToken($companyCode, '/simple-transfer', [
            'dateFrom' => $startDate,
            'dateTo'   => $endDate,
            'page'     => $page,
            'limit'    => $limit,
            'sort'     => '-simpleTransferDate',
        ]);

        if (! $response->successful()) {
            throw new Exception(
                "STF API gagal [{$companyCode}][{$startDate} - {$endDate}][page {$page}] HTTP {$response->status()} => " . $response->body()
            );
        }

        $rows = data_get($response->json(), 'result.data', []);
        $totalCount = (int) data_get($response->json(), 'result.count', 0);
        $pageCount = $totalCount > 0 ? (int) ceil($totalCount / $limit) : 1;

        $savedHeader = 0;
        $detailJobs = 0;
        $skippedRows = 0;
        $duplicateRows = 0;
        $seenInPage = [];

        foreach ($rows as $row) {
            $stfNum = trim((string) ($row['simpleTransferNum'] ?? ''));

            if ($stfNum === '') {
                $skippedRows++;
                continue;
            }

            if (isset($seenInPage[$stfNum])) {
                $duplicateRows++;
            }

            $seenInPage[$stfNum] = true;

            try {
                $exists = DB::table('tbl_simple_transfer')
                    ->where('transfer_num', $stfNum)
                    ->exists();

                DB::table('tbl_simple_transfer')->updateOrInsert(
                    [
                        'transfer_num' => $stfNum,
                    ],
                    [
                        'transfer_date'             => $row['simpleTransferDate'] ?? null,
                        'origin_location_id'        => $row['originLocationID'] ?? null,
                        'origin_location_name'      => $row['originLocationName'] ?? null,
                        'destination_location_id'   => $row['destinationLocationID'] ?? null,
                        'destination_location_name' => $row['destinationLocationName'] ?? null,
                        'status_name'               => $row['statusName'] ?? null,
                        'additional_info'           => $row['additionalInfo'] ?? null,
                        'updated_at'                => now(),
                    ]
                );

                if (! $exists) {
                    $savedHeader++;
                }

                \App\Jobs\SyncStfDetailJob::dispatch($companyCode, $stfNum)
                    ->onConnection('redis')
                    ->onQueue('transfer-detail');

                $detailJobs++;
            } catch (\Throwable $e) {
                Log::error('STF PAGE: gagal simpan header.', [
                    'company_code' => $companyCode,
                    'date_from'    => $startDate,
                    'date_to'      => $endDate,
                    'stf_num'      => $stfNum,
                    'page'         => $page,
                    'error'        => $e->getMessage(),
                    'line'         => $e->getLine(),
                ]);
            }
        }

        Log::info('STF PAGE SUMMARY', [
            'company_code'       => $companyCode,
            'date_from'          => $startDate,
            'date_to'            => $endDate,
            'page'               => $page,
            'page_count'         => $pageCount,
            'api_rows'           => count($rows),
            'unique_stf_in_page' => count($seenInPage),
            'saved_header'       => $savedHeader,
            'detail_jobs'        => $detailJobs,
            'skipped_rows'       => $skippedRows,
            'duplicate_rows'     => $duplicateRows,
            'total_count'        => $totalCount,
        ]);

        return [
            'company_code'       => $companyCode,
            'date_from'          => $startDate,
            'date_to'            => $endDate,
            'page'               => $page,
            'page_count'         => $pageCount,
            'api_rows'           => count($rows),
            'unique_stf_in_page' => count($seenInPage),
            'saved_header'       => $savedHeader,
            'detail_jobs'        => $detailJobs,
            'skipped_rows'       => $skippedRows,
            'duplicate_rows'     => $duplicateRows,
            'total_count'        => $totalCount,
        ];
    }

    public function syncFromApi($page = 1, $limit = 100, $start = null, $end = null): array
    {
        return $this->syncSimpleTransferCredentialPage(
            companyCode: 'OKNHO',
            startDate: (string) $start,
            endDate: (string) $end,
            page: (int) $page,
            limit: (int) $limit
        );
    }

    public function syncAllFromApi(
        string $startDate,
        string $endDate,
        int $limit = 100,
        string $companyCode = 'OKNHO'
    ): array {
        return $this->syncSimpleTransferCredentialDateRange($companyCode, $startDate, $endDate, $limit);
    }

    public function fetchAndStoreDetail(string $companyCode, string $stfNum): array
    {
        $companyCode = strtoupper(trim($companyCode));

        $response = $this->getWithToken($companyCode, '/simple-transfer/' . $stfNum);

        if (! $response->successful()) {
            throw new Exception(
                "STF detail API gagal [{$companyCode}][{$stfNum}] HTTP {$response->status()} => " . $response->body()
            );
        }

        $details = data_get($response->json(), 'result.simpleTransferDetails', []);
        $savedDetail = 0;

        foreach ($details as $item) {
            $productDetailId = $item['productDetailID'] ?? null;

            if (! $productDetailId) {
                continue;
            }

            DB::table('tbl_simple_transfer_detail')->updateOrInsert(
                [
                    'transfer_num'      => $stfNum,
                    'product_detail_id' => $productDetailId,
                ],
                [
                    'product_name'  => $item['productName']  ?? null,
                    'uom_name'      => $item['uomName']      ?? null,
                    'qty'           => $item['qty']          ?? 0,
                    'stock_qty'     => $item['stockQty']     ?? 0,
                    'available_qty' => $item['availableQty'] ?? 0,
                    'is_asset'      => ($item['isAsset'] ?? false) ? 1 : 0,
                    'updated_at'    => now(),
                ]
            );

            $savedDetail++;
        }

        Log::info('STF DETAIL SUMMARY', [
            'company_code'    => $companyCode,
            'stf_num'         => $stfNum,
            'api_detail_rows' => count($details),
            'saved_detail'    => $savedDetail,
        ]);

        return [
            'company_code'    => $companyCode,
            'stf_num'         => $stfNum,
            'api_detail_rows' => count($details),
            'saved_detail'    => $savedDetail,
        ];
    }
}
