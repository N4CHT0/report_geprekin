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
        if (!$creds)
            return null;

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
            "simpleTransferDate" => date('Y-m-d', strtotime($header->transfer_date)),
            "originLocationID" => (int) $header->origin_location_id,
            "destinationLocationID" => (int) $header->destination_location_id,
            "additionalInfo" => $header->additional_info ?? "Sync SCM - $transferNum",
            "simpleTransferDetails" => $details->map(function ($item) {
                return [
                    "productDetailID" => (int) $item->product_detail_id,
                    "qty" => (float) $item->qty
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
            "simpleProductSalesDate" => date('Y-m-d', strtotime($header->sales_date)),
            "productSalesTypeID" => 1, // Default sesuai JSON mentah
            "branchID" => (int) $header->branch_id,
            "locationID" => (int) $header->location_id,
            "customerID" => (int) $header->customer_id,
            "currencyID" => 1, // IDR biasanya 1
            "rate" => 1,
            "paymentID" => 2,
            "coaNo" => "",
            "simpleProductSalesDetails" => $details->map(function ($item) {
                return [
                    "ID" => -1, // Sesuai JSON mentah
                    "productDetailID" => (int) $item->product_detail_id, // Pastikan ini ID versi ESB
                    "flagLuxuryItem" => 0,
                    "qty" => round((float) $item->qty, 2),
                    "price" => round((float) $item->price, 2),
                    "discount" => 0,
                    "discountPercent" => 0,
                    "vatValue" => 0,
                    "taxRate" => 0,
                    "notes" => ""
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
            "cashPurchaseDate" => date('Y-m-d', strtotime($header->purchase_date)),
            "purchaseTypeID" => null,
            "mode" => 1,
            "supplierID" => (int) $header->supplier_id,
            "branchID" => (int) $header->branch_id,
            "locationID" => (int) $header->location_id,
            "paymentID" => 2, // Sesuai JSON mentah
            "currencyID" => 1,
            "supplierInvoiceNum" => "",
            "dueDate" => 14,
            "rate" => 1,
            "additionalInfo" => $header->additional_info ?? "Sync SCM Purchase",
            "simplePurchaseDetails" => $details->map(function ($item) use ($header) {
                return [
                    "productDetailID" => (int) $item->product_detail_id, // ID versi ESB
                    "qty" => (float) $item->qty,
                    "pricelistPrice" => 0,
                    "price" => (float) $item->price,
                    "discount" => 0,
                    "discountPercent" => 0,
                    "vat" => 0,
                    "taxID" => null,
                    "taxRate" => null,
                    "notes" => "",
                    "simplePurchaseExpirations" => [
                        [
                            "productDetailID" => (int) $item->product_detail_id,
                            "qty" => (float) $item->qty,
                            "expiredDate" => date('Y-m-d', strtotime($header->purchase_date))
                            // Default expiredDate disamakan dengan tanggal purchase jika tidak ada input khusus
                        ]
                    ]
                ];
            })->toArray(),
            "simplePurchaseCosts" => []
        ];
    }

    /**
     * Format Data untuk Goods Delivery sesuai JSON ESB
     */
    public function formatGoodsDelivery($deliveryNum)
    {
        Log::info("ESB-SERVICE: Formatting Goods Delivery $deliveryNum");

        // 1. Ambil data header dan detail dari DB lokal
        $header = DB::table('tbl_goods_deliveries')->where('delivery_num', $deliveryNum)->first();
        $details = DB::table('tbl_goods_delivery_details')->where('delivery_num', $deliveryNum)->get();

        if (!$header || $details->isEmpty()) {
            Log::warning("ESB-SERVICE: Data Goods Delivery tidak lengkap untuk $deliveryNum");
            return null;
        }

        return [
            "locationID" => (int) $header->location_id,
            "goodsDeliveryDate" => date('Y-m-d', strtotime($header->delivery_date)),
            "goodsDeliveryDetails" => $details->map(function ($item) use ($header) {
                return [
                    "productDetailID" => (int) $item->product_id, // ID versi ESB
                    "qty" => round((float) $item->qty, 2),
                    "expiredDate" => [
                        [
                            // Kita samakan dengan tanggal delivery atau kolom expired jika ada di tabel detail
                            "expiredDate" => !empty($item->expired_date) ? date('Y-m-d', strtotime($item->expired_date)) : date('Y-m-d', strtotime($header->delivery_date)),
                            "qty" => round((float) $item->qty, 2)
                        ]
                    ],
                    "notes" => $item->notes ?? ""
                ];
            })->toArray(),
            // Jika sistem kamu belum mengelola asset, kosongkan saja sesuai contoh kontrak ESB
            "goodsDeliveryAssetDetails" => [],
            "additionalInfo" => $header->additional_info ?? "Sync SCM Goods Delivery"
        ];
    }

    /**
     * Format Data untuk Goods Transfer sesuai JSON ESB
     */
    public function formatGoodsTransfer($transferNum)
    {
        Log::info("ESB-SERVICE: Formatting Goods Transfer $transferNum");

        // 1. Ambil data header dan detail dari DB lokal
        $header = DB::table('tbl_goods_transfers')->where('transfer_num', $transferNum)->first();
        $details = DB::table('tbl_goods_transfer_details')->where('transfer_num', $transferNum)->get();

        if (!$header || $details->isEmpty()) {
            Log::warning("ESB-SERVICE: Data Goods Transfer tidak lengkap untuk $transferNum");
            return null;
        }

        return [
            "originBranchID" => (int) $header->origin_branch_id,
            "destinationBranchID" => (int) $header->destination_branch_id,
            "transferDate" => date('Y-m-d', strtotime($header->transfer_date)),
            "categoryTypeID" => (int) ($header->category_type_id ?? 3), // Default 3 sesuai JSON mentah
            "additionalInfo" => $header->additional_info ?? "Sync SCM Goods Transfer",
            "originLocationID" => $header->origin_location_id ? (int) $header->origin_location_id : null,

            // Di JSON berupa array string, kita bungkus no_pr lokal ke dalam array
            "purchaseRequestNum" => [
                $header->purchase_request_num ?? ""
            ],

            "transferDetails" => $details->map(function ($item) {
                return [
                    "productDetailID" => (int) $item->product_id, // ID versi ESB
                    "qty" => round((float) $item->qty, 2),
                    "requestQty" => round((float) ($item->request_qty ?? 0), 2) // Default 0 jika tidak melacak request_qty
                ];
            })->toArray(),

            // Kosongkan jika sistem lokal belum mencatat aset khusus
            "transferDetailAssets" => []
        ];
    }

    public function formatGoodsReceipt($receiptNum)
    {
        Log::info("ESB-SERVICE: Formatting Goods Receipt untuk nomor: $receiptNum");

        // 1. Ambil data header dari tabel lokal (sesuaikan nama tabel dengan DB-mu)
        $header = DB::table('tbl_goods_receipts')->where('receipt_num', $receiptNum)->first();

        if (!$header) {
            Log::warning("ESB-SERVICE: Header Goods Receipt tidak ditemukan untuk $receiptNum");
            return null;
        }

        // 2. Ambil data detail item yang diserahterimakan
        $details = DB::table('tbl_goods_receipt_details')->where('receipt_num', $receiptNum)->get();

        if ($details->isEmpty()) {
            Log::warning("ESB-SERVICE: Detail Goods Receipt kosong untuk $receiptNum");
            return null;
        }

        // 3. Susun ke dalam struktur array JSON sesuai spesifikasi kontrak ESB
        return [
            "goodsReceiptDate" => date('Y-m-d', strtotime($header->receipt_date)),
            "locationID" => (int) $header->location_id, // ID Gudang/Lokasi penerima
            "deliveryNum" => $header->delivery_num ?? null, // No. Surat Jalan/Delivery dari supplier (jika ada)
            "additionalInfo" => $header->additional_info ?? null,
            "selectedAssetID" => null, // Set null jika bisnis tidak melacak nomor seri aset seperti palet/tabung
            "autoClosePO" => (bool) ($header->auto_close_po ?? false), // Dipaksa bool murni (true/false) agar Java ESB tidak bingung

            "goodsReceiptDetail" => $details->map(function ($item) {
                return [
                    "productID" => (int) $item->master_product_id, // ID Master/Group Produk di ESB
                    "productDetailID" => (int) $item->product_id,        // ID SKU Varian Spesifik di ESB
                    "qty" => round((float) $item->qty, 2),   // Paksa format desimal aman
                    "deviationVal" => round((float) ($item->deviation_val ?? 0), 2), // Selisih kuantitas (jika ada)
                    "notes" => $item->notes ?? "",

                    // Struktur nested expiredDates (wajib array of object walaupun datanya kosong/null)
                    "expiredDates" => [
                        [
                            "expiredDate" => !empty($item->expired_date) ? date('Y-m-d', strtotime($item->expired_date)) : null,
                            "qty" => !empty($item->expired_date) ? round((float) $item->qty, 2) : 0
                        ]
                    ]
                ];
            })->toArray()
        ];
    }

    /**
     * Format Data untuk Purchase Order sesuai JSON ESB
     */
    public function formatPurchaseOrder($poNum)
    {
        Log::info("ESB-SERVICE: Formatting Purchase Order $poNum");

        // 1. Ambil data header dan detail dari DB lokal
        $header = DB::table('tbl_purchase_orders')->where('po_num', $poNum)->first();
        $details = DB::table('tbl_purchase_order_details')->where('po_num', $poNum)->get();

        if (!$header || $details->isEmpty()) {
            Log::warning("ESB-SERVICE: Data Purchase Order tidak lengkap untuk $poNum");
            return null;
        }

        // 2. Mengatasi Multi-PR jika disimpan dalam bentuk string pisah koma di database (misal: "PR1,PR2")
        $prNums = [];
        if (!empty($header->purchase_request_nums)) {
            $prNums = array_map('trim', explode(',', $header->purchase_request_nums));
        }

        return [
            "branchID" => (int) $header->branch_id,
            "purchaseDate" => date('Y-m-d', strtotime($header->purchase_date)),
            "requiredDate" => !empty($header->required_date) ? date('Y-m-d', strtotime($header->required_date)) : date('Y-m-d', strtotime($header->purchase_date)),
            "currencyID" => (int) ($header->currency_id ?? 1),
            "rate" => round((float) ($header->rate ?? 1), 2),
            "supplierID" => (int) $header->supplier_id,

            // Mengirimkan array string no PR terhubung
            "purchaseRequestNums" => $prNums,

            "flagImportDoc" => (int) ($header->flag_import_doc ?? 0),
            "dueDay" => (int) ($header->due_day ?? 14),
            "costCenterID" => $header->cost_center_id ? (int) $header->cost_center_id : null,
            "projectID" => $header->project_id ? (int) $header->project_id : null,
            "additionalInfo" => $header->additional_info ?? "",
            "footNote" => $header->foot_note ?? "",

            "purchaseDetails" => $details->map(function ($item) {
                return [
                    "productDetailID" => (int) $item->product_id, // ID versi ESB
                    "qty" => round((float) $item->qty, 2),
                    "price" => round((float) $item->price, 2),
                    "discountPercent" => round((float) ($item->discount_percent ?? 0), 2),
                    "discount" => round((float) ($item->discount ?? 0), 2),
                    "vat" => round((float) ($item->vat ?? 0), 2),
                    "notes" => $item->notes ?? ""
                ];
            })->toArray()
        ];
    }

    /**
     * Format Data untuk Sales Order sesuai JSON ESB
     */
    public function formatSalesOrder($soNum)
    {
        Log::info("ESB-SERVICE: Formatting Sales Order $soNum");

        // 1. Ambil data header dan detail dari DB lokal
        $header = DB::table('tbl_sales_orders')->where('so_num', $soNum)->first();
        $details = DB::table('tbl_sales_order_details')->where('so_num', $soNum)->get();

        if (!$header || $details->isEmpty()) {
            Log::warning("ESB-SERVICE: Data Sales Order tidak lengkap untuk $soNum");
            return null;
        }

        return [
            "branchID" => (int) $header->branch_id,
            "productSalesDate" => date('Y-m-d', strtotime($header->sales_date)),
            "requiredDate" => !empty($header->required_date) ? date('Y-m-d', strtotime($header->required_date)) : date('Y-m-d', strtotime($header->sales_date)),
            "currencyID" => (int) ($header->currency_id ?? 1),
            "rate" => round((float) ($header->rate ?? 1), 2),
            "customerID" => (int) $header->customer_id,
            "salesRepID" => $header->sales_rep_id ? (int) $header->sales_rep_id : null,
            "customerBranchID" => $header->customer_branch_id ? (int) $header->customer_branch_id : null,
            "linkPurchaseNum" => $header->link_purchase_num ?? null, // Jika ada link ke PO terkait
            "customerAddress" => $header->customer_address ?? "",
            "additionalInfo" => $header->additional_info ?? "",

            "productSalesDetails" => $details->map(function ($item) {
                return [
                    "productDetailID" => (int) $item->product_id, // ID versi ESB
                    "qty" => round((float) $item->qty, 2),
                    "priceListPrice" => round((float) ($item->pricelist_price ?? 0), 2),
                    "price" => round((float) $item->price, 2),
                    "discount" => round((float) ($item->discount ?? 0), 2),
                    "discountPercent" => round((float) ($item->discount_percent ?? 0), 2),
                    "vatValue" => round((float) ($item->vat_value ?? 0), 2),
                    "dppValue" => round((float) ($item->dpp_value ?? 0), 2), // Dasar Pengenaan Pajak
                    "notes" => $item->notes ?? ""
                ];
            })->toArray()
        ];
    }

    public function formatPurchaseRequest($prNum)
    {
        Log::info("ESB-SERVICE: Formatting Purchase Request $prNum");

        // 1. Ambil data header dan detail dari DB lokal (sesuaikan nama tabel dengan DB SCM-mu)
        $header = DB::table('tbl_purchase_requests')->where('pr_num', $prNum)->first();
        $details = DB::table('tbl_purchase_request_details')->where('pr_num', $prNum)->get();

        if (!$header || $details->isEmpty()) {
            Log::warning("ESB-SERVICE: Data Purchase Request tidak lengkap untuk $prNum");
            return null;
        }

        return [
            "branchID" => (int) $header->branch_id,
            "purchaseRequestDate" => date('Y-m-d', strtotime($header->request_date)),
            "requiredDate" => !empty($header->required_date) ? date('Y-m-d', strtotime($header->required_date)) : date('Y-m-d', strtotime($header->request_date)),
            "costCenterID" => $header->cost_center_id ? (int) $header->cost_center_id : null,
            "projectID" => $header->project_id ? (int) $header->project_id : null,
            "requestTemplateID" => $header->request_template_id ? (int) $header->request_template_id : null,

            "purchaseRequestDetails" => $details->map(function ($item) {
                return [
                    "productDetailID" => (int) $item->product_id, // ID SKU varian versi ESB
                    "requestProcessID" => (int) ($item->request_process_id ?? 1), // Default 1 sesuai JSON mentah
                    "qty" => round((float) $item->qty, 2),
                    "notes" => $item->notes ?? ""
                ];
            })->toArray(),

            "additionalInfo" => $header->additional_info ?? "",
            "isTemplate" => (bool) ($header->is_template ?? false) // Paksa format boolean murni murni
        ];
    }
}
