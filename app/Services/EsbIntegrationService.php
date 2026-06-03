<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class EsbIntegrationService
{
    public function getTokenByCode($code)
    {
        // 1. Cari credential_id berdasarkan code di tbl_api_credentials
        $credential = DB::table('tbl_api_credentials')
            ->where('credential_code', $code)
            ->first();

        if (!$credential) {
            Log::error("ESB-TOKEN: Credential Code [$code] tidak ditemukan.");
            return null;
        }

        // 2. Ambil bearer_token dari tbl_api_sessions
        $session = DB::table('tbl_api_sessions')
            ->where('credential_id', $credential->id)
            ->first();

        if (!$session) {
            Log::error("ESB-TOKEN: Session tidak ditemukan untuk Credential ID: $credential->id ($code)");
            return null;
        }

        return $session->bearer_token;
    }

    public function relogin($credentialId)
    {
        Log::info("ESB-SERVICE: Menjalankan Login Ulang untuk ID: $credentialId");

        // 1. Ambil Credential
        $creds = DB::table('tbl_api_credentials')->where('id', $credentialId)->first();
        if (!$creds) return null;

        // 2. Tembak API Login ESB
        // Sesuaikan URL login sesuai dokumentasi ESB kamu
        $loginUrl = 'https://services.esb.co.id/core/auth/login';

        try {
            $response = Http::post($loginUrl, [
                'username' => $creds->username,
                'password' => $creds->password,
                // 'credential_code' => $creds->credential_code
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $newToken = $data['result']['accessToken'] ?? $data['accessToken'] ?? null; // Sesuaikan path json-nya

                if ($newToken) {
                    // 3. Update tbl_api_sessions
                    DB::table('tbl_api_sessions')
                        ->updateOrInsert(
                            ['credential_id' => $credentialId],
                            [
                                'bearer_token' => $newToken,
                                'updated_at' => now()
                            ]
                        );

                    Log::info("ESB-SERVICE: Login Ulang Berhasil.");
                    return $newToken;
                }
            }

            Log::error("ESB-SERVICE: Login Gagal. Status: " . $response->status());
            return null;
        } catch (\Exception $e) {
            Log::error("ESB-SERVICE: Exception saat login: " . $e->getMessage());
            return null;
        }
    }

    public function formatSimpleTransfer($transferNum)
    {
        Log::info("ESB-SERVICE: Memulai formatting data untuk $transferNum");

        $header = DB::table('tbl_simple_transfer')->where('transfer_num', $transferNum)->first();
        $details = DB::table('tbl_simple_transfer_detail')->where('transfer_num', $transferNum)->get();

        if (!$header) {
            Log::error("ESB-SERVICE: Header tidak ditemukan untuk $transferNum");
            return null;
        }

        if ($details->isEmpty()) {
            Log::warning("ESB-SERVICE: Detail kosong untuk $transferNum. Push dibatalkan.");
            return null;
        }

        $payload = [
            "simpleTransferDate"    => date('Y-m-d', strtotime($header->transfer_date)),
            "originLocationID"      => (int)$header->origin_location_id,
            "destinationLocationID" => (int)$header->destination_location_id,
            "additionalInfo"        => $header->additional_info ?? "Sync SCM - $transferNum",
            "simpleTransferDetails" => $details->map(function ($item) {
                return [
                    "productDetailID" => (int)$item->product_detail_id,
                    "qty"             => (float)$item->qty
                ];
            })->toArray(),
            "assetIDs" => []
        ];

        Log::info("ESB-SERVICE: Formatting selesai. Jumlah item: " . count($payload['simpleTransferDetails']));
        return $payload;
    }

    /**
     * Format Data untuk Simple Sales sesuai JSON ESB
     */
    public function formatSimpleSales($salesNum)
    {
        Log::info("ESB-SERVICE: Formatting Simple Sales $salesNum");

        $header = DB::table('tbl_simple_sales')->where('sales_num', $salesNum)->first();
        $details = DB::table('tbl_simple_sales_detail')->where('sales_num', $salesNum)->get();

        if (!$header || $details->isEmpty()) {
            Log::warning("ESB-SERVICE: Data Sales tidak lengkap untuk $salesNum");
            return null;
        }

        return [
            "simpleProductSalesDate"    => date('Y-m-d', strtotime($header->sales_date)),
            "productSalesTypeID"        => 1, // Default sesuai JSON mentah
            "branchID"                  => (int)$header->branch_id,
            "locationID"                => (int)$header->location_id,
            "customerID"                => (int)$header->customer_id,
            "currencyID"                => 1, // IDR biasanya 1
            "rate"                      => 1,
            "paymentID"                 => 1,
            "simpleProductSalesDetails" => $details->map(function ($item) {
                return [
                    "ID"              => -1, // Sesuai JSON mentah
                    "productDetailID" => (int)$item->product_detail_id, // Pastikan ini ID versi ESB
                    "flagLuxuryItem"  => 0,
                    "qty"             => round((float)$item->qty, 2),
                    "price"           => round((float)$item->price, 2),
                    "discount"        => 0,
                    "discountPercent" => 0,
                    "vatValue"        => 0,
                    "taxRate"         => 0,
                    "notes"           => ""
                ];
            })->toArray(),
            "simpleProductSalesCosts" => []
        ];
    }

    /**
     * Format Data untuk Simple Purchase sesuai JSON ESB
     */
    public function formatSimplePurchase($purchaseNum)
    {
        Log::info("ESB-SERVICE: Formatting Simple Purchase $purchaseNum");

        $header = DB::table('tbl_simple_purchases')->where('purchase_num', $purchaseNum)->first();
        $details = DB::table('tbl_simple_purchase_details')->where('purchase_num', $purchaseNum)->get();

        if (!$header || $details->isEmpty()) {
            Log::warning("ESB-SERVICE: Data Purchase tidak lengkap untuk $purchaseNum");
            return null;
        }

        return [
            "cashPurchaseDate"   => date('Y-m-d', strtotime($header->purchase_date)),
            "purchaseTypeID"     => null,
            "mode"               => 1,
            "supplierID"         => (int)$header->supplier_id,
            "branchID"           => (int)$header->branch_id,
            "locationID"         => (int)$header->location_id,
            "paymentID"          => 2, // Sesuai JSON mentah
            "currencyID"         => 1,
            "supplierInvoiceNum" => "",
            "dueDate"            => 14,
            "rate"               => 1,
            "additionalInfo"     => $header->additional_info ?? "Sync SCM Purchase",
            "simplePurchaseDetails" => $details->map(function ($item) use ($header) {
                return [
                    "productDetailID" => (int)$item->product_detail_id, // ID versi ESB
                    "qty"             => (float)$item->qty,
                    "pricelistPrice"  => 0,
                    "price"           => (float)$item->price,
                    "discount"        => 0,
                    "discountPercent" => 0,
                    "vat"             => 0,
                    "taxID"           => null,
                    "taxRate"         => null,
                    "notes"           => "",
                    "simplePurchaseExpirations" => [
                        [
                            "productDetailID" => (int)$item->product_detail_id,
                            "qty"             => (float)$item->qty,
                            "expiredDate"     => date('Y-m-d', strtotime($header->purchase_date))
                            // Default expiredDate disamakan dengan tanggal purchase jika tidak ada input khusus
                        ]
                    ]
                ];
            })->toArray(),
            "simplePurchaseCosts" => []
        ];
    }
}
