<?php

namespace App\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EsbSalesService
{
    protected string $baseUrl = 'https://core-api.esb.co.id';

    /**
     * Cache mapping outlet per credential biar tidak query berulang.
     * Format:
     * [
     *   credential_id => [
     *      'by_branch_id' => [],
     *      'by_esb_branch_code' => [],
     *      'by_branch_code' => [],
     *      'by_branch_name' => [],
     *      'by_nama_outlet' => [],
     *   ]
     * ]
     */
    protected array $outletMapCache = [];

    /**
     * Cache hasil pencarian outlet per kombinasi branch.
     */
    protected array $resolvedOutletCache = [];

    // public function syncSalesByBranchAndDate(
    //     int $credentialId,
    //     string $branchCode,
    //     string $branchName,
    //     string $salesDate
    // ): array {
        // public function syncSalesByBranchAndDate(
        //     int $credentialId,
        //     string $branchCode,
        //     string $startDate,
        //     string $endDate,
        //     ?int $forcedOutletId = null
        // ): array {
        // $staticToken = $this->getStaticTokenByCredentialId($credentialId);
    /**
     * Backward compatible wrapper untuk pemanggilan lama per branch.
     * Sekarang branchCode boleh null/blank. Kalau null/blank, ESB akan return semua branch
     * untuk credential/token tersebut.
     */
    public function syncSalesByBranchAndDate(
        int $credentialId,
        ?string $branchCode,
        string $startDate,
        string $endDate,
        ?int $forcedOutletId = null
    ): array {
        return $this->syncSalesByCredentialAndDate(
            credentialId: $credentialId,
            startDate: $startDate,
            endDate: $endDate,
            branchCode: $branchCode,
            forcedOutletId: $forcedOutletId
        );
    }

    /**
     * Ambil metadata page count untuk credential tanpa memproses seluruh data.
     * Dipakai oleh SyncSalesCredentialPreparePagesJob agar job utama ringan.
     */
    public function getSalesCredentialPageMeta(int $credentialId, string $salesDate): array
    {
        $staticToken = $this->getStaticTokenByCredentialId($credentialId);

        if (! $staticToken) {
            throw new Exception("Static token untuk credential_id {$credentialId} tidak ditemukan.");
        }

        $response = Http::timeout(90)
            ->retry(3, 2000)
            ->acceptJson()
            ->withHeaders([
                'Authorization' => 'Bearer ' . $staticToken,
                'Content-Type'  => 'application/json',
            ])
            ->get(rtrim($this->baseUrl, '/') . '/corev1/sales/sales-information', [
                'salesDateFrom' => $salesDate,
                'salesDateTo'   => $salesDate,
                'sortBy'        => 'salesDateIn',
                'sortOrder'     => 'asc',
                'page'          => 1,
                'statusName'    => 'Finished',
                // branchCode sengaja tidak dikirim. Null/omitted = all branch untuk token ini.
            ]);

        if (! $response->successful()) {
            throw new Exception(
                "Sales API meta gagal [credential_id {$credentialId}][ALL] HTTP {$response->status()} => " . $response->body()
            );
        }

        return [
            'credential_id' => $credentialId,
            'sales_date'    => $salesDate,
            'page_count'    => max(1, (int) $response->header('x-pagination-page-count', 1)),
            'total_count'   => (int) $response->header('x-pagination-total-count', 0),
            'per_page'      => (int) $response->header('x-pagination-per-page', 0),
        ];
    }

    /**
     * Proses 1 page saja supaya job kecil, cepat, dan tidak timeout.
     * Tidak delete tbl_outlets. Delete hanya transaksi yang nomor+outletnya ada di page ini.
     */
    public function syncSalesCredentialPage(int $credentialId, string $salesDate, int $page): array
    {
        $staticToken = $this->getStaticTokenByCredentialId($credentialId);

        if (! $staticToken) {
            throw new Exception("Static token untuk credential_id {$credentialId} tidak ditemukan.");
        }

        $this->buildOutletMapCache($credentialId);

        $response = Http::timeout(90)
            ->retry(3, 2000)
            ->acceptJson()
            ->withHeaders([
                'Authorization' => 'Bearer ' . $staticToken,
                'Content-Type'  => 'application/json',
            ])
            ->get(rtrim($this->baseUrl, '/') . '/corev1/sales/sales-information', [
                'salesDateFrom' => $salesDate,
                'salesDateTo'   => $salesDate,
                'sortBy'        => 'salesDateIn',
                'sortOrder'     => 'asc',
                'page'          => $page,
                'statusName'    => 'Finished',
                // branchCode sengaja tidak dikirim. Null/omitted = all branch untuk token ini.
            ]);

        if (! $response->successful()) {
            throw new Exception(
                "Sales API gagal [credential_id {$credentialId}][ALL][page {$page}] HTTP {$response->status()} => " . $response->body()
            );
        }

        $pageCount = max(1, (int) $response->header('x-pagination-page-count', 1));
        $json = $response->json();

        if (! is_array($json)) {
            throw new Exception("Response sales API tidak valid [credential_id {$credentialId}][ALL][page {$page}]");
        }

        $transactionRows = [];
        $menuRows = [];
        $skippedRows = 0;
        $missingOutletRows = 0;

        foreach ($json as $sale) {
            $salesNum = trim((string) ($sale['salesNum'] ?? ''));
            if ($salesNum === '') {
                $skippedRows++;
                continue;
            }

            $salesDateOnly = (string) ($sale['salesDate'] ?? $salesDate);
            $salesDateIn   = $sale['salesDateIn'] ?? null;
            $visitPurpose  = trim((string) ($sale['visitPurposeName'] ?? ''));
            $statusId      = isset($sale['statusID']) ? (int) ($sale['statusID']) : null;
            $statusName    = trim((string) ($sale['statusName'] ?? ''));

            $saleBranchId   = (int) ($sale['branchID'] ?? 0);
            $saleBranchCode = trim((string) ($sale['branchCode'] ?? ''));
            $saleBranchName = trim((string) ($sale['branchName'] ?? ''));

            $outletMatch = $this->findOutletByBranch(
                $credentialId,
                $saleBranchId,
                $saleBranchCode,
                $saleBranchName
            );

            if (! $outletMatch) {
                $missingOutletRows++;

                // Jangan log setiap row agar job tetap ringan. Sampling saja.
                if ($missingOutletRows <= 10) {
                    Log::warning('OUTLET NOT FOUND FOR SALES PAGE', [
                        'credential_id' => $credentialId,
                        'branch_id'     => $saleBranchId,
                        'branch_code'   => $saleBranchCode,
                        'branch_name'   => $saleBranchName,
                        'sales_date'    => $salesDate,
                        'sales_num'     => $salesNum,
                        'page'          => $page,
                    ]);
                }

                continue;
            }

            $outlet = $outletMatch['outlet'];
            $matchedBy = $outletMatch['matched_by'];

            if (! in_array($matchedBy, ['esb_branch_id', 'esb_branch_code'], true) && $page % 100 === 0) {
                Log::warning('OUTLET MATCH FALLBACK PAGE SAMPLE', [
                    'credential_id' => $credentialId,
                    'branch_id'     => $saleBranchId,
                    'branch_code'   => $saleBranchCode,
                    'branch_name'   => $saleBranchName,
                    'matched_by'    => $matchedBy,
                    'outlet_id'     => $outlet->id ?? null,
                    'page'          => $page,
                ]);
            }

            $paxTotal      = (int) ($sale['paxTotal'] ?? 1);
            $grandTotal    = (float) ($sale['grandTotal'] ?? 0);
            $paymentTotal  = (float) ($sale['paymentTotal'] ?? 0);

            // Untuk menyesuaikan angka API payment/net collected, pakai paymentTotal.
            // Kalau dashboard target adalah grandTotal, ganti env ESB_SALES_HEADER_AMOUNT_FIELD=grandTotal.
            $amountField = env('ESB_SALES_HEADER_AMOUNT_FIELD', 'grandTotal');
            $headerTotal = $amountField === 'paymentTotal' ? $paymentTotal : $grandTotal;

            $trWaktu = '00:00:00';
            if ($salesDateIn) {
                try {
                    $trWaktu = Carbon::parse($salesDateIn)->format('H:i:s');
                } catch (\Throwable $e) {
                    $trWaktu = '00:00:00';
                }
            }

            $paymentMethod = $this->extractPaymentMethod($sale['salesPayments'] ?? []);
            $now = now();

            $transactionRows[] = [
                'nomor'          => $salesNum,
                'outlet_id'      => $outlet->id,
                'menu_id'        => null,
                'sesi_tanggal'   => $salesDateOnly,
                'tr_waktu'       => $trWaktu,
                'tr_metode'      => $paymentMethod,
                'item_nama'      => '__TRANSACTION__',
                'item_varian'    => $statusName !== '' ? $statusName : $visitPurpose,
                'item_harga'     => $headerTotal,
                'item_jumlah'    => 1,
                'item_sub_total' => $headerTotal,
                'customer_unit'  => (string) max($paxTotal, 1),
                'item_status'    => $statusId,
                'created_at'     => $now,
                'updated_at'     => $now,
            ];

            foreach (($sale['salesMenus'] ?? []) as $menu) {
                $menuName = trim((string) ($menu['menuName'] ?? ''));
                $qty      = (int) ($menu['qty'] ?? 0);
                $price    = (float) ($menu['price'] ?? 0);
                $total    = (float) ($menu['total'] ?? 0);
                $menuId   = $menu['menuID'] ?? null;

                if ($menuName === '' || $qty <= 0) {
                    continue;
                }

                $menuRows[] = [
                    'nomor'          => $salesNum,
                    'outlet_id'      => $outlet->id,
                    'menu_id'        => $menuId,
                    'sesi_tanggal'   => $salesDateOnly,
                    'tr_waktu'       => $trWaktu,
                    'tr_metode'      => $paymentMethod,
                    'item_nama'      => $menuName,
                    'item_varian'    => $visitPurpose,
                    'item_harga'     => $price,
                    'item_jumlah'    => $qty,
                    'item_sub_total' => $total,
                    'customer_unit'  => (string) max($paxTotal, 1),
                    'item_status'    => $statusId,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ];
            }
        }

        $allRows = array_merge($transactionRows, $menuRows);
        $inserted = 0;

        $nomorList = collect($allRows)
            ->pluck('nomor')
            ->filter(fn ($v) => trim((string) $v) !== '')
            ->unique()
            ->values()
            ->all();

        $outletIds = collect($allRows)
            ->pluck('outlet_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        DB::transaction(function () use ($salesDate, $nomorList, $outletIds, $allRows, &$inserted) {
            if (! empty($nomorList) && ! empty($outletIds)) {
                DB::table('tbl_transaksi_perhari')
                    ->where('sesi_tanggal', $salesDate)
                    ->whereIn('outlet_id', $outletIds)
                    ->whereIn('nomor', $nomorList)
                    ->delete();
            }

            if (! empty($allRows)) {
                foreach (array_chunk($allRows, 500) as $chunk) {
                    DB::table('tbl_transaksi_perhari')->insert($chunk);
                    $inserted += count($chunk);
                }
            }
        });

        if ($page === 1 || $page % 50 === 0 || $page >= $pageCount) {
            Log::info('ESB SALES PAGE JOB SUMMARY', [
                'credential_id' => $credentialId,
                'sales_date' => $salesDate,
                'page' => $page,
                'page_count' => $pageCount,
                'api_rows' => count($json),
                'transaction_rows' => count($transactionRows),
                'menu_rows' => count($menuRows),
                'built_rows' => count($allRows),
                'inserted_rows' => $inserted,
                'missing_outlet_rows' => $missingOutletRows,
                'skipped_rows' => $skippedRows,
            ]);
        }

        return [
            'credential_id' => $credentialId,
            'sales_date' => $salesDate,
            'page' => $page,
            'page_count' => $pageCount,
            'api_rows' => count($json),
            'transaction_rows' => count($transactionRows),
            'menu_rows' => count($menuRows),
            'built_rows' => count($allRows),
            'inserted_rows' => $inserted,
            'missing_outlet_rows' => $missingOutletRows,
            'skipped_rows' => $skippedRows,
        ];
    }

    /**
     * Sync sales ESB per credential.
     *
     * Penting:
     * - branchCode null/blank = ambil semua branch dari API untuk credential tersebut.
     * - Tidak delete tbl_outlets.
     * - tbl_outlets hanya dipakai untuk mapping outlet_id berdasarkan response API
     *   branchID/branchCode/branchName.
     * - Delete transaksi harian hanya scope: tanggal + outlet_id + nomor transaksi.
     */
    public function syncSalesByCredentialAndDate(
        int $credentialId,
        string $startDate,
        string $endDate,
        ?string $branchCode = null,
        ?int $forcedOutletId = null
    ): array {
        $branchCode = $branchCode !== null ? strtoupper(trim($branchCode)) : null;
        if ($branchCode === '') {
            $branchCode = null;
        }

        $salesDate = $startDate;
        $staticToken = $this->getStaticTokenByCredentialId($credentialId);

        if (! $staticToken) {
            throw new Exception("Static token untuk credential_id {$credentialId} tidak ditemukan.");
        }

        $this->buildOutletMapCache($credentialId);

        $forcedOutlet = null;
        if ($forcedOutletId) {
            /*
             * HOTFIX:
             * Jangan validasi forced outlet dengan tbl_outlets.branch_code.
             *
             * BranchCode yang dikirim dari job ALL sudah berasal dari
             * tbl_api_credential_branches, sedangkan outlet bisa match lewat
             * esb_branch_id / esb_branch_code / branch_code. Kalau dipaksa
             * branch_code harus sama, outlet valid bisa dianggap invalid dan
             * job akan failed massal.
             */
            $forcedOutlet = DB::table('tbl_outlets')
                ->where('id', $forcedOutletId)
                ->where('credential_id', $credentialId)
                ->where('is_active', 1)
                ->first();

            if (! $forcedOutlet) {
                throw new Exception(
                    "Outlet forced tidak valid untuk credential {$credentialId}, outlet {$forcedOutletId}"
                );
            }
        }

        $page = 1;
        $pageCount = 1;

        $transactionRows = [];
        $menuRows = [];
        $totalApiSalesRows = 0;

        $resolvedOutlet = null;
        $resolvedMatchedBy = null;
        $resolvedMatchScore = 0.0;

        do {
            $params = [
                'salesDateFrom' => $startDate,
                'salesDateTo'   => $endDate,
                'sortBy'        => 'salesDateIn',
                'sortOrder'     => 'asc',
                'page'          => $page,
                'statusName'    => 'Finished',
            ];

            // Kalau branchCode null, jangan kirim branchCode supaya API return semua branch
            // untuk token/credential ini.
            if ($branchCode !== null) {
                $params['branchCode'] = $branchCode;
            }

            $response = Http::timeout(90)
                ->retry(3, 2000)
                ->acceptJson()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $staticToken,
                    'Content-Type'  => 'application/json',
                ])
                ->get(rtrim($this->baseUrl, '/') . '/corev1/sales/sales-information', $params);

            if (! $response->successful()) {
                throw new Exception(
                    "Sales API gagal [credential_id {$credentialId}][branch " . ($branchCode ?? 'ALL') . "] HTTP {$response->status()} => " . $response->body()
                );
            }

            $pageCount = (int) $response->header('x-pagination-page-count', 1);
            $json = $response->json();

            if (! is_array($json)) {
                throw new Exception("Response sales API tidak valid [credential_id {$credentialId}][branch " . ($branchCode ?? 'ALL') . "]");
            }

            $totalApiSalesRows += count($json);

            foreach ($json as $sale) {
                $salesNum      = trim((string) ($sale['salesNum'] ?? ''));
                $salesDateOnly = (string) ($sale['salesDate'] ?? $salesDate);
                $salesDateIn   = $sale['salesDateIn'] ?? null;
                $visitPurpose  = trim((string) ($sale['visitPurposeName'] ?? ''));
                $statusId      = isset($sale['statusID']) ? (int) ($sale['statusID']) : null;
                $statusName    = trim((string) ($sale['statusName'] ?? ''));

                $saleBranchId   = (int) ($sale['branchID'] ?? 0);
                $saleBranchCode = trim((string) ($sale['branchCode'] ?? ''));
                $saleBranchName = trim((string) ($sale['branchName'] ?? ''));

                if ($salesNum === '') {
                    continue;
                }

                if ($forcedOutlet) {
                    $outlet = $forcedOutlet;
                    $matchedBy = 'forced_outlet_id';
                    $score = 100.0;
                } else {
                    $outletMatch = $this->findOutletByBranch(
                        $credentialId,
                        $saleBranchId,
                        $saleBranchCode,
                        $saleBranchName
                    );

                    if (! $outletMatch) {
                        Log::error('OUTLET NOT FOUND FOR SALES', [
                            'credential_id' => $credentialId,
                            'branch_id'     => $saleBranchId,
                            'branch_code'   => $saleBranchCode,
                            'branch_name'   => $saleBranchName,
                            'sales_date'    => $salesDateOnly,
                            'sales_num'     => $salesNum,
                        ]);

                        continue;
                    }

                    $outlet = $outletMatch['outlet'];
                    $matchedBy = $outletMatch['matched_by'];
                    $score = (float) ($outletMatch['score'] ?? 0);
                }

                if (! $resolvedOutlet) {
                    $resolvedOutlet = $outlet;
                    $resolvedMatchedBy = $matchedBy;
                    $resolvedMatchScore = $score;
                }

                if (! in_array($matchedBy, ['esb_branch_id', 'esb_branch_code'], true)) {
                    Log::warning('OUTLET MATCH FALLBACK', [
                        'credential_id' => $credentialId,
                        'branch_id'     => $saleBranchId,
                        'branch_code'   => $saleBranchCode,
                        'branch_name'   => $saleBranchName,
                        'sales_date'    => $salesDateOnly,
                        'sales_num'     => $salesNum,
                        'matched_by'    => $matchedBy,
                        'match_score'   => $score,
                        'outlet_id'     => $outlet->id ?? null,
                        'outlet_name'   => $outlet->nama_outlet ?? null,
                    ]);
                }

                $paxTotal             = (int) ($sale['paxTotal'] ?? 1);
                $subTotal             = (float) ($sale['subTotal'] ?? 0);
                $discountTotal        = (float) ($sale['discountTotal'] ?? 0);
                $menuDiscountTotal    = (float) ($sale['menuDiscountTotal'] ?? 0);
                $voucherDiscountTotal = (float) ($sale['voucherDiscountTotal'] ?? 0);
                $otherTaxTotal        = (float) ($sale['otherTaxTotal'] ?? 0);
                $vatTotal             = (float) ($sale['vatTotal'] ?? 0);
                $otherVatTotal        = (float) ($sale['otherVatTotal'] ?? 0);
                $deliveryCost         = (float) ($sale['deliveryCost'] ?? 0);
                $orderFee             = (float) ($sale['orderFee'] ?? 0);
                $grandTotal           = (float) ($sale['grandTotal'] ?? 0);
                $roundingTotal        = (float) ($sale['roundingTotal'] ?? 0);
                $paymentTotal         = (float) ($sale['paymentTotal'] ?? 0);
                $voucherTotal         = (float) ($sale['voucherTotal'] ?? 0);

                // Net sales disamakan dengan API paymentTotal.
                // Fallback ke rumus API kalau paymentTotal tidak dikirim.
                $headerTotal = $grandTotal;

                $trWaktu = '00:00:00';

                if ($salesDateIn) {
                    try {
                        $dt = Carbon::parse($salesDateIn);
                        $trWaktu = $dt->format('H:i:s');
                    } catch (\Throwable $e) {
                        $trWaktu = '00:00:00';
                    }
                }

                $paymentMethod = $this->extractPaymentMethod($sale['salesPayments'] ?? []);

                $transactionRows[] = [
                    'nomor'          => $salesNum,
                    'outlet_id'      => $outlet->id,
                    'menu_id'        => null,
                    'sesi_tanggal'   => $salesDateOnly,
                    'tr_waktu'       => $trWaktu,
                    'tr_metode'      => $paymentMethod,
                    'item_nama'      => '__TRANSACTION__',
                    'item_varian'    => $statusName !== '' ? $statusName : $visitPurpose,
                    'item_harga'     => $headerTotal,
                    'item_jumlah'    => 1,
                    'item_sub_total' => $headerTotal,
                    'customer_unit'  => (string) max($paxTotal, 1),
                    'item_status'    => $statusId,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];

                foreach (($sale['salesMenus'] ?? []) as $menu) {
                    $menuName = trim((string) ($menu['menuName'] ?? ''));
                    $qty      = (int) ($menu['qty'] ?? 0);
                    $price    = (float) ($menu['price'] ?? 0);
                    $total    = (float) ($menu['total'] ?? 0);
                    $menuId   = $menu['menuID'] ?? null;

                    if ($menuName === '' || $qty <= 0) {
                        continue;
                    }

                    $menuRows[] = [
                        'nomor'          => $salesNum,
                        'outlet_id'      => $outlet->id,
                        'menu_id'        => $menuId,
                        'sesi_tanggal'   => $salesDateOnly,
                        'tr_waktu'       => $trWaktu,
                        'tr_metode'      => $paymentMethod,
                        'item_nama'      => $menuName,
                        'item_varian'    => $visitPurpose,
                        'item_harga'     => $price,
                        'item_jumlah'    => $qty,
                        'item_sub_total' => $total,
                        'customer_unit'  => (string) max($paxTotal, 1),
                        'item_status'    => $statusId,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ];
                }

                Log::info('ESB NET SALES TRACE', [
                    'credential_id'          => $credentialId,
                    'sales_num'              => $salesNum,
                    'sales_date'             => $salesDateOnly,
                    'branch_id'              => $saleBranchId,
                    'branch_code'            => $saleBranchCode,
                    'branch_name'            => $saleBranchName,
                    'status_id'              => $statusId,
                    'status_name'            => $statusName,
                    'sub_total'              => $subTotal,
                    'discount_total'         => $discountTotal,
                    'menu_discount_total'    => $menuDiscountTotal,
                    'voucher_discount_total' => $voucherDiscountTotal,
                    'other_tax_total'        => $otherTaxTotal,
                    'vat_total'              => $vatTotal,
                    'other_vat_total'        => $otherVatTotal,
                    'delivery_cost'          => $deliveryCost,
                    'order_fee'              => $orderFee,
                    'grand_total'            => $grandTotal,
                    'rounding_total'         => $roundingTotal,
                    'payment_total'          => $paymentTotal,
                    'voucher_total'          => $voucherTotal,
                    'header_total_used'      => $headerTotal,
                    'payment_method'         => $paymentMethod,
                    'outlet_id'              => $outlet->id ?? null,
                    'outlet_name'            => $outlet->nama_outlet ?? null,
                ]);
            }

            Log::info('ESB SALES PAGE SUMMARY', [
                'credential_id'           => $credentialId,
                'branch_code_request'     => $branchCode ?? 'ALL',
                'sales_date_from'         => $startDate,
                'sales_date_to'           => $endDate,
                'page'                    => $page,
                'page_count'              => $pageCount,
                'api_sales_rows_so_far'   => $totalApiSalesRows,
                'transaction_rows_so_far' => count($transactionRows),
                'menu_rows_so_far'        => count($menuRows),
                'resolved_outlet_id'      => $resolvedOutlet->id ?? null,
                'resolved_outlet_name'    => $resolvedOutlet->nama_outlet ?? null,
                'matched_by'              => $resolvedMatchedBy,
                'match_score'             => $resolvedMatchScore,
            ]);

            $page++;
        } while ($page <= $pageCount);

        if (! $resolvedOutlet) {
            Log::warning('NO OUTLET RESOLVED FOR CREDENTIAL SALES', [
                'credential_id' => $credentialId,
                'branch_code'   => $branchCode ?? 'ALL',
                'sales_date'    => $salesDate,
                'api_rows'      => $totalApiSalesRows,
            ]);

            return [
                'credential_id'    => $credentialId,
                'branch_code'      => $branchCode ?? 'ALL',
                'sales_date'       => $salesDate,
                'outlet_id'        => 0,
                'outlet_name'      => '',
                'matched_by'       => null,
                'match_score'      => 0.0,
                'api_rows'         => $totalApiSalesRows,
                'transaction_rows' => count($transactionRows),
                'menu_rows'        => count($menuRows),
                'built_rows'       => 0,
                'inserted_rows'    => 0,
            ];
        }

        $allRows = array_merge($transactionRows, $menuRows);
        $inserted = 0;

        $nomorList = collect($allRows)
            ->pluck('nomor')
            ->filter(fn ($v) => trim((string) $v) !== '')
            ->unique()
            ->values()
            ->all();

        $outletIds = collect($allRows)
            ->pluck('outlet_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        DB::transaction(function () use ($salesDate, $nomorList, $outletIds, $allRows, &$inserted) {
            if (! empty($nomorList) && ! empty($outletIds)) {
                DB::table('tbl_transaksi_perhari')
                    ->where('sesi_tanggal', $salesDate)
                    ->whereIn('outlet_id', $outletIds)
                    ->whereIn('nomor', $nomorList)
                    ->delete();
            }

            if (! empty($allRows)) {
                foreach (array_chunk($allRows, 500) as $chunk) {
                    DB::table('tbl_transaksi_perhari')->insert($chunk);
                    $inserted += count($chunk);
                }
            }
        });

        Log::info('ESB SALES FINAL SUMMARY', [
            'credential_id'     => $credentialId,
            'branch_code'       => $branchCode ?? 'ALL',
            'sales_date'        => $salesDate,
            'outlet_ids'        => $outletIds,
            'api_sales_rows'    => $totalApiSalesRows,
            'transaction_rows'  => count($transactionRows),
            'menu_rows'         => count($menuRows),
            'built_rows'        => count($allRows),
            'inserted_rows'     => $inserted,
            'deleted_by_nomor'  => count($nomorList),
            'deleted_scope'     => 'sales_date + outlet_id + nomor',
        ]);

        return [
            'credential_id'    => $credentialId,
            'branch_code'      => $branchCode ?? 'ALL',
            'sales_date'       => $salesDate,
            'outlet_id'        => $resolvedOutlet->id,
            'outlet_name'      => $resolvedOutlet->nama_outlet,
            'matched_by'       => $resolvedMatchedBy,
            'match_score'      => $resolvedMatchScore,
            'api_rows'         => $totalApiSalesRows,
            'transaction_rows' => count($transactionRows),
            'menu_rows'        => count($menuRows),
            'built_rows'       => count($allRows),
            'inserted_rows'    => $inserted,
        ];
    }

    public function syncDailySummaryToLaporanBulanan(string $salesDate): int
    {
        $rows = DB::table('tbl_transaksi_perhari')
            ->selectRaw("
                outlet_id,
                sesi_tanggal as tanggal,

                SUM(
                    CASE
                        WHEN item_nama = '__TRANSACTION__'
                         AND item_status = 8
                         AND UPPER(COALESCE(tr_metode, '')) <> 'OTHER COST'
                        THEN item_sub_total
                        ELSE 0
                    END
                ) as total_omset,

                SUM(
                    CASE
                        WHEN item_nama = '__TRANSACTION__'
                         AND item_status = 8
                         AND UPPER(COALESCE(tr_metode, '')) = 'OTHER COST'
                        THEN item_sub_total
                        ELSE 0
                    END
                ) as total_non_sales,

                COUNT(DISTINCT CASE
                    WHEN item_nama = '__TRANSACTION__'
                     AND item_status = 8
                    THEN nomor
                    ELSE NULL
                END) as total_cu
            ")
            ->where('sesi_tanggal', $salesDate)
            ->groupBy('outlet_id', 'sesi_tanggal')
            ->get();

        $affected = 0;

        DB::transaction(function () use ($salesDate, $rows, &$affected) {
            DB::table('tbl_laporan_bulanan')
                ->where('tanggal', $salesDate)
                ->delete();

            $insertRows = [];

            foreach ($rows as $row) {
                $insertRows[] = [
                    'outlet_id'       => $row->outlet_id,
                    'tanggal'         => $row->tanggal,
                    'total_omset'     => (float) ($row->total_omset ?? 0),
                    'total_non_sales' => (float) ($row->total_non_sales ?? 0),
                    'total_cu'        => (int) ($row->total_cu ?? 0),
                    'platform'        => 'lainnya',
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
            }

            if (! empty($insertRows)) {
                foreach (array_chunk($insertRows, 500) as $chunk) {
                    DB::table('tbl_laporan_bulanan')->insert($chunk);
                    $affected += count($chunk);
                }
            }
        });

        Log::info('SYNC SUMMARY DONE', [
            'tanggal' => $salesDate,
            'rows'    => $affected,
        ]);

        // TAMBAHAN BARU:
        // setelah bulanan selesai, rebuild tabel turunan harian
        $this->syncDailyDerivedReports($salesDate);

        return $affected;
    }

    /**
     * Tambahan baru:
     * rebuild semua tabel turunan harian tanpa menghapus logika lama.
     */
    public function syncDailyDerivedReports(string $salesDate): array
    {
        $result = [
            'tanggal'          => $salesDate,
            'ecommerce_rows'   => 0,
            'pareto_rows'      => 0,
            'summary_jam_rows' => 0,
            'payment_rows'     => 0,
            'service_rows'     => 0,
        ];

        DB::transaction(function () use ($salesDate, &$result) {
            $result['ecommerce_rows']   = $this->rebuildDailyEcommerce($salesDate);
            $result['pareto_rows']      = $this->rebuildDailyPareto($salesDate);
            $result['summary_jam_rows'] = $this->rebuildDailySummaryJamOutlet($salesDate);
            $result['payment_rows']     = $this->rebuildDailyLaporanPayment($salesDate);
            $result['service_rows']     = $this->rebuildDailyLaporanService($salesDate);
        });

        Log::info('SYNC DERIVED DAILY REPORTS DONE', $result);

        return $result;
    }

    protected function rebuildDerivedTableByDate(string $table, string $salesDate): void
    {
        DB::table($table)
            ->where('tanggal', $salesDate)
            ->delete();
    }

    /**
     * tbl_laporan_ecommerce
     * sumber: HEADER transaksi saja
     */
    protected function rebuildDailyEcommerce(string $salesDate): int
    {
        $this->rebuildDerivedTableByDate('tbl_laporan_ecommerce', $salesDate);

        $rows = DB::table('tbl_transaksi_perhari as t')
            ->selectRaw("
                t.outlet_id,
                t.sesi_tanggal as tanggal,
                t.item_varian,
                SUM(t.item_sub_total) as total_jumlah
            ")
            ->where('t.sesi_tanggal', $salesDate)
            ->where('t.item_nama', '__TRANSACTION__')
            ->groupBy('t.outlet_id', 't.sesi_tanggal', 't.item_varian')
            ->get();

        $insertRows = [];

        foreach ($rows as $row) {
            $insertRows[] = [
                'outlet_id'    => $row->outlet_id,
                'tanggal'      => $row->tanggal,
                'item_varian'  => $row->item_varian,
                'total_jumlah' => (float) ($row->total_jumlah ?? 0),
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
        }

        $affected = 0;

        if (! empty($insertRows)) {
            foreach (array_chunk($insertRows, 500) as $chunk) {
                DB::table('tbl_laporan_ecommerce')->insert($chunk);
                $affected += count($chunk);
            }
        }

        return $affected;
    }

    /**
     * tbl_laporan_pareto
     * sumber: DETAIL menu saja
     */
    protected function rebuildDailyPareto(string $salesDate): int
    {
        $this->rebuildDerivedTableByDate('tbl_laporan_pareto', $salesDate);

        $rows = DB::table('tbl_transaksi_perhari as t')
            ->selectRaw("
                t.outlet_id,
                t.sesi_tanggal as tanggal,
                t.item_nama,
                SUM(t.item_jumlah) as total_jumlah,
                SUM(t.item_harga * t.item_jumlah) as total_harga
            ")
            ->where('t.sesi_tanggal', $salesDate)
            ->where('t.item_nama', '<>', '__TRANSACTION__')
            ->groupBy('t.outlet_id', 't.sesi_tanggal', 't.item_nama')
            ->get();

        $insertRows = [];

        foreach ($rows as $row) {
            $insertRows[] = [
                'outlet_id'    => $row->outlet_id,
                'tanggal'      => $row->tanggal,
                'item_nama'    => $row->item_nama,
                'total_jumlah' => (float) ($row->total_jumlah ?? 0),
                'total_harga'  => (float) ($row->total_harga ?? 0),
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
        }

        $affected = 0;

        if (! empty($insertRows)) {
            foreach (array_chunk($insertRows, 500) as $chunk) {
                DB::table('tbl_laporan_pareto')->insert($chunk);
                $affected += count($chunk);
            }
        }

        return $affected;
    }

    /**
     * tbl_summary_jam_outlet
     * sumber: HEADER transaksi saja
     */
    protected function rebuildDailySummaryJamOutlet(string $salesDate): int
    {
        $this->rebuildDerivedTableByDate('tbl_summary_jam_outlet', $salesDate);

        $rows = DB::table('tbl_transaksi_perhari as t')
            ->selectRaw("
                t.sesi_tanggal as tanggal,
                t.outlet_id,
                HOUR(t.tr_waktu) as jam,
                COUNT(DISTINCT t.nomor) as total_transaksi,
                SUM(t.item_sub_total) as total_omset
            ")
            ->where('t.sesi_tanggal', $salesDate)
            ->where('t.item_nama', '__TRANSACTION__')
            ->whereNotNull('t.tr_waktu')
            ->groupBy('t.sesi_tanggal', 't.outlet_id', DB::raw('HOUR(t.tr_waktu)'))
            ->get();

        $insertRows = [];

        foreach ($rows as $row) {
            $insertRows[] = [
                'tanggal'         => $row->tanggal,
                'outlet_id'       => $row->outlet_id,
                'jam'             => $row->jam,
                'total_transaksi' => (int) ($row->total_transaksi ?? 0),
                'total_omset'     => (float) ($row->total_omset ?? 0),
                'created_at'      => now(),
                'updated_at'      => now(),
            ];
        }

        $affected = 0;

        if (! empty($insertRows)) {
            foreach (array_chunk($insertRows, 500) as $chunk) {
                DB::table('tbl_summary_jam_outlet')->insert($chunk);
                $affected += count($chunk);
            }
        }

        return $affected;
    }

    /**
     * tbl_laporan_payment
     * sumber: HEADER transaksi saja
     */
    protected function rebuildDailyLaporanPayment(string $salesDate): int
    {
        $this->rebuildDerivedTableByDate('tbl_laporan_payment', $salesDate);

        $rows = DB::table('tbl_transaksi_perhari as t')
            ->selectRaw("
                t.outlet_id,
                t.sesi_tanggal as tanggal,
                CASE
                    WHEN UPPER(COALESCE(t.tr_metode, '')) LIKE '%CASH%' THEN 'cash'
                    WHEN UPPER(COALESCE(t.tr_metode, '')) LIKE '%TRANSFER%' THEN 'transfer'
                    WHEN UPPER(COALESCE(t.tr_metode, '')) LIKE '%QRIS BCA%' THEN 'qris_bca'
                    WHEN UPPER(COALESCE(t.tr_metode, '')) LIKE '%QRIS BUKUPAY%' THEN 'qris_bukupay'
                    WHEN UPPER(COALESCE(t.tr_metode, '')) LIKE '%QRIS ESB%' THEN 'qris_esb'
                    WHEN UPPER(COALESCE(t.tr_metode, '')) LIKE '%QRIS GOPAY%' THEN 'qris_gopay'
                    WHEN UPPER(COALESCE(t.tr_metode, '')) LIKE '%QRIS SHOPEEPAY%' THEN 'qris_shopeepay'
                    WHEN UPPER(COALESCE(t.tr_metode, '')) LIKE '%TIKTOK SHOP%' THEN 'tiktok_shop'
                    ELSE 'lainnya'
                END as metode_key,
                SUM(t.item_sub_total) as total_omset
            ")
            ->where('t.sesi_tanggal', $salesDate)
            ->where('t.item_nama', '__TRANSACTION__')
            ->groupBy('t.outlet_id', 't.sesi_tanggal', 'metode_key')
            ->get();

        $insertRows = [];

        foreach ($rows as $row) {
            $insertRows[] = [
                'outlet_id'   => $row->outlet_id,
                'tanggal'     => $row->tanggal,
                'metode_key'  => $row->metode_key,
                'total_omset' => (float) ($row->total_omset ?? 0),
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }

        $affected = 0;

        if (! empty($insertRows)) {
            foreach (array_chunk($insertRows, 500) as $chunk) {
                DB::table('tbl_laporan_payment')->insert($chunk);
                $affected += count($chunk);
            }
        }

        return $affected;
    }

    /**
     * tbl_laporan_service
     * sumber: HEADER transaksi saja
     */
    protected function rebuildDailyLaporanService(string $salesDate): int
    {
        $this->rebuildDerivedTableByDate('tbl_laporan_service', $salesDate);

        $rows = DB::table('tbl_transaksi_perhari as t')
            ->selectRaw("
                t.outlet_id,
                t.sesi_tanggal as tanggal,
                CASE
                    WHEN UPPER(COALESCE(t.item_varian, '')) LIKE '%TAKEAWAY%' THEN 'takeaway'
                    WHEN UPPER(COALESCE(t.item_varian, '')) LIKE '%DINE IN%' THEN 'dinein'
                    WHEN UPPER(COALESCE(t.item_varian, '')) LIKE '%DINEIN%' THEN 'dinein'
                    ELSE 'lainnya'
                END as service_key,
                SUM(t.item_sub_total) as total_omset
            ")
            ->where('t.sesi_tanggal', $salesDate)
            ->where('t.item_nama', '__TRANSACTION__')
            ->groupBy('t.outlet_id', 't.sesi_tanggal', 'service_key')
            ->get();

        $insertRows = [];

        foreach ($rows as $row) {
            $insertRows[] = [
                'outlet_id'   => $row->outlet_id,
                'tanggal'     => $row->tanggal,
                'service_key' => $row->service_key,
                'total_omset' => (float) ($row->total_omset ?? 0),
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }

        $affected = 0;

        if (! empty($insertRows)) {
            foreach (array_chunk($insertRows, 500) as $chunk) {
                DB::table('tbl_laporan_service')->insert($chunk);
                $affected += count($chunk);
            }
        }

        return $affected;
    }

    protected function getStaticTokenByCredentialId(int $credentialId): ?string
    {
        return DB::table('tbl_api_credentials')
            ->where('id', $credentialId)
            ->where('is_active', 1)
            ->whereNotNull('static_token')
            ->where('static_token', '!=', '')
            ->value('static_token');
    }

    protected function normalizeText(?string $value): string
    {
        $value = strtolower(trim((string) $value));
        $value = preg_replace('/[^a-z0-9\s]/', ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value);

        return trim($value);
    }

    protected function buildOutletMapCache(int $credentialId): void
    {
        if (isset($this->outletMapCache[$credentialId])) {
            return;
        }

        $outlets = DB::table('tbl_outlets')
            ->where('credential_id', $credentialId)
            ->where('is_active', 1)
            ->get();

        $map = [
            'by_branch_id'       => [],
            'by_esb_branch_code' => [],
            'by_branch_code'     => [],
            'by_branch_name'     => [],
            'by_nama_outlet'     => [],
        ];

        foreach ($outlets as $outlet) {
            if (! empty($outlet->esb_branch_id)) {
                $map['by_branch_id'][(int) $outlet->esb_branch_id] = $outlet;
            }

            $esbBranchCode = $this->normalizeText($outlet->esb_branch_code ?? '');
            if ($esbBranchCode !== '') {
                $map['by_esb_branch_code'][$esbBranchCode] = $outlet;
            }

            $branchCode = $this->normalizeText($outlet->branch_code ?? '');
            if ($branchCode !== '') {
                $map['by_branch_code'][$branchCode] = $outlet;
            }

            $branchName = $this->normalizeText($outlet->branch_name ?? '');
            if ($branchName !== '') {
                $map['by_branch_name'][$branchName] = $outlet;
            }

            $namaOutlet = $this->normalizeText($outlet->nama_outlet ?? '');
            if ($namaOutlet !== '') {
                $map['by_nama_outlet'][$namaOutlet] = $outlet;
            }
        }

        $this->outletMapCache[$credentialId] = $map;
    }

    protected function findOutletByBranch(
        int $credentialId,
        int $branchId,
        string $branchCode,
        string $branchName = ''
    ): ?array {
        $cacheKey = implode('|', [
            $credentialId,
            $branchId,
            $this->normalizeText($branchCode),
            $this->normalizeText($branchName),
        ]);

        if (array_key_exists($cacheKey, $this->resolvedOutletCache)) {
            return $this->resolvedOutletCache[$cacheKey];
        }

        $this->buildOutletMapCache($credentialId);
        $map = $this->outletMapCache[$credentialId] ?? [];

        if ($branchId > 0 && isset($map['by_branch_id'][$branchId])) {
            return $this->resolvedOutletCache[$cacheKey] = [
                'outlet'     => $map['by_branch_id'][$branchId],
                'matched_by' => 'esb_branch_id',
                'score'      => 100.0,
            ];
        }

        $normalizedBranchCode = $this->normalizeText($branchCode);
        if ($normalizedBranchCode !== '') {
            if (isset($map['by_esb_branch_code'][$normalizedBranchCode])) {
                return $this->resolvedOutletCache[$cacheKey] = [
                    'outlet'     => $map['by_esb_branch_code'][$normalizedBranchCode],
                    'matched_by' => 'esb_branch_code',
                    'score'      => 95.0,
                ];
            }

            if (isset($map['by_branch_code'][$normalizedBranchCode])) {
                return $this->resolvedOutletCache[$cacheKey] = [
                    'outlet'     => $map['by_branch_code'][$normalizedBranchCode],
                    'matched_by' => 'branch_code',
                    'score'      => 90.0,
                ];
            }
        }

        $normalizedBranchName = $this->normalizeText($branchName);
        if ($normalizedBranchName !== '') {
            if (isset($map['by_branch_name'][$normalizedBranchName])) {
                return $this->resolvedOutletCache[$cacheKey] = [
                    'outlet'     => $map['by_branch_name'][$normalizedBranchName],
                    'matched_by' => 'branch_name',
                    'score'      => 85.0,
                ];
            }

            if (isset($map['by_nama_outlet'][$normalizedBranchName])) {
                return $this->resolvedOutletCache[$cacheKey] = [
                    'outlet'     => $map['by_nama_outlet'][$normalizedBranchName],
                    'matched_by' => 'nama_outlet',
                    'score'      => 80.0,
                ];
            }
        }

        return $this->resolvedOutletCache[$cacheKey] = null;
    }

    protected function extractPaymentMethod(array $payments): string
    {
        if (empty($payments)) {
            return 'UNKNOWN';
        }

        $methods = [];

        foreach ($payments as $payment) {
            $name = $payment['paymentMethodTypeName'] ?? $payment['paymentMethodName'] ?? null;

            if ($name) {
                $methods[] = trim($name);
            }
        }

        $methods = array_values(array_unique(array_filter($methods)));

        return ! empty($methods) ? implode(' + ', $methods) : 'UNKNOWN';
    }
}