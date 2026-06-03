<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Jobs\SyncSimPurchaseJob;
use App\Services\EsbAuthService;

class EsbPurchaseService
{
    protected string $baseUrl = 'https://services.esb.co.id/core';

    /**
     * FUNGSI 1: Dispatch Job ke Queue
     */
    public function dispatchSync($credentialCode, $dateFrom, $masterKey, $dateTo = null, $cashPurchaseNum = null)
    {
        $authService = app(EsbAuthService::class);
        $credential = $authService->getCredentialByCode($credentialCode);

        $assignedBranches = DB::table('tbl_api_credential_branches as pivot')
            ->join('tbl_outlets as o', 'pivot.branch_id', '=', 'o.id')
            ->where('pivot.credential_id', $credential->id)
            ->select('o.id', 'o.branch_code', 'o.nama_outlet')
            ->get();

        foreach ($assignedBranches as $index => $branch) {
            SyncSimPurchaseJob::dispatch(
                $credentialCode, 
                $dateFrom, 
                $branch, 
                $masterKey, 
                $dateTo, 
                $cashPurchaseNum
            )
            ->onQueue('esb-purchase')
            ->delay(now()->addSeconds($index * 1));
        }

        return ['total_branch' => $assignedBranches->count()];
    }

    /**
     * FUNGSI 2: Sync per Branch (Header + Details)
     */
    public function syncSingleBranch($credentialCode, $dateFrom, $branch, $dateTo = null, $cashPurchaseNum = null)
    {
        $authService = app(EsbAuthService::class);
        $credential = $authService->getCredentialByCode($credentialCode);
        $session = $authService->getUsableSession($credential);
        $token = $session->bearer_token;

        $url = rtrim($this->baseUrl, '/') . '/purchase/simple-purchase';

        // 1. Ambil List Header
        $response = $this->callApi($url, $token, $companyCode, $dateFrom, null, $dateTo, $cashPurchaseNum);
        // $response = $this->callApi($url, $token, $dateFrom, $branch, $dateTo, $cashPurchaseNum);

        if ($response->status() === 401) {
            $session = $authService->refreshOrLogin($credential);
            $token = $session->bearer_token;
            $response = $this->callApi($url, $token, $dateFrom, $branch, $dateTo, $cashPurchaseNum);
        }

        if (!$response->successful()) {
            throw new \Exception("Gagal koneksi ESB ({$branch->branch_code}) => " . $response->status());
        }

        $dataEsb = $response->json()['result']['data'] ?? [];

        foreach ($dataEsb as $row) {
            $cpNum = $row['cashPurchaseNum'];

            // 2. Simpan Header (Gunakan updateOrInsert agar tidak duplikat)
            DB::table('simple_purchases')->updateOrInsert(
                ['sequence' => $cpNum],
                [
                    'credential_id' => $credential->id,
                    'outlet_id'     => $branch->id,
                    'supplier_name' => $row['supplierName'] ?? '-',
                    'purchase_date' => date('Y-m-d', strtotime($row['cashPurchaseDate'])),
                    'branch'        => $row['branchName'],
                    'purchase_total'=> $row['cashPurchaseTotal'] ?? 0,
                    'grand_total'   => $row['cashPurchaseTotal'] ?? 0,
                    'updated_at'    => now(),
                    'created_at'    => now(),
                ]
            );

            // Ambil ID Header untuk relasi detail
            $headerId = DB::table('simple_purchases')->where('sequence', $cpNum)->value('id');

            // 3. TARIK DETAIL (Path Parameter: /simple-purchase/{cashPurchaseNum})
            $urlDetail = rtrim($this->baseUrl, '/') . "/purchase/simple-purchase/" . $cpNum;
            $resDetail = Http::timeout(300)->withoutVerifying()
                ->withHeaders(['Authorization' => 'Bearer ' . $token])
                ->get($urlDetail);

            if ($resDetail->successful()) {
                $detailData = $resDetail->json()['result'] ?? [];

                // Simpan ke simple_purchase_items
                if (isset($detailData['items'])) {
                    DB::table('simple_purchase_items')->where('purchase_id', $headerId)->delete();
                    $items = array_map(fn($item) => [
                        'purchase_id'  => $headerId,
                        'product_name' => $item['productName'],
                        'qty'          => $item['qty'],
                        'price'        => $item['price'],
                        'total_line'   => $item['totalLine'],
                    ], $detailData['items']);
                    
                    if (!empty($items)) DB::table('simple_purchase_items')->insert($items);
                }

                // Simpan ke simple_purchase_costs
                if (isset($detailData['costs'])) {
                    DB::table('simple_purchase_costs')->where('purchase_id', $headerId)->delete();
                    $costs = array_map(fn($cost) => [
                        'purchase_id'  => $headerId,
                        'account_name' => $cost['accountName'],
                        'amount'       => $cost['amount'],
                    ], $detailData['costs']);

                    if (!empty($costs)) DB::table('simple_purchase_costs')->insert($costs);
                }
            }
        }

        return [
            'branch'   => $branch->branch_code,
            'inserted' => count($dataEsb)
        ];
    }

    private function callApi($url, $token, $dateFrom, $branch, $dateTo = null, $cashPurchaseNum = null)
    {
        $queryParams = [
            'start_date'  => $dateFrom,
            'end_date'    => $dateTo ?? $dateFrom,
            'branch_code' => $branch->branch_code
        ];

        if ($cashPurchaseNum) {
            $queryParams['cashPurchaseNum'] = $cashPurchaseNum;
        }

        return Http::timeout(300)->withoutVerifying()
            ->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json',
            ])
            ->get($url, $queryParams);
    }
}