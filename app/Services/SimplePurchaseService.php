<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SimplePurchaseService
{
    /**
     * Menyimpan data header simple purchase ke database.
     */
    public function syncHeader($data, $credentialId)
    {
        DB::beginTransaction();
        try {
            foreach ($data as $item) {
                DB::table('tbl_simple_purchases')->updateOrInsert(
                    [
                        'purchase_num' => $item['cashPurchaseNum'],
                        'credential_id'   => $credentialId // WAJIB: Identitas pemilik data
                    ],
                    [
                        'purchase_date' => \Carbon\Carbon::parse($item['cashPurchaseDate'])->format('Y-m-d H:i:s'),
                        'branch_id'       => $item['branchID'],
                        'branch_name'     => $item['branchName'],
                        'supplier_id'     => $item['supplierID'],
                        'supplier_name'   => $item['supplierName'],
                        'total_amount'    => $item['cashPurchaseTotal'],
                        'status_id'       => $item['statusID'],
                        'status_name'     => $item['statusName'],
                        'created_by'      => $item['createdBy'],
                        'updated_at'      => now(),
                    ]
                );
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Service Error (Header) Credential {$credentialId}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Menyimpan rincian item barang dan biaya (Costs).
     */
    // public function syncDetail($data)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $purchaseNum = $data['cashPurchaseNum'];

    //         // 1. Simpan Detail Barang
    //         if (!empty($data['simplePurchaseDetails'])) {
    //             foreach ($data['simplePurchaseDetails'] as $item) {
    //                 DB::table('tbl_simple_purchase_details')->updateOrInsert(
    //                     ['purchase_num' => $purchaseNum, 'product_id' => $item['productID']],
    //                     [
    //                         'product_name' => $item['productName'],
    //                         'uom_name'     => $item['uomName'],
    //                         'qty'          => $item['qty'],
    //                         'price'        => $item['price'],
    //                         'subtotal'     => $item['total'],
    //                         'notes'        => $item['notes'] ?? '',
    //                         'updated_at'   => now(),
    //                     ]
    //                 );
    //             }
    //         }

    //         // 2. Simpan Biaya Tambahan (Simple Purchase Costs)
    //         if (!empty($data['simplePurchaseCosts'])) {
    //             foreach ($data['simplePurchaseCosts'] as $cost) {
    //                 DB::table('tbl_simple_purchase_costs')->updateOrInsert(
    //                     ['purchase_num' => $purchaseNum, 'account_name' => $cost['accountName']],
    //                     [
    //                         'amount'     => $cost['amount'],
    //                         'notes'      => $cost['notes'] ?? '',
    //                         'updated_at' => now(),
    //                     ]
    //                 );
    //             }
    //         }

    //         DB::commit();
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error("Service Error (Detail) Purchase {$purchaseNum}: " . $e->getMessage());
    //         throw $e;
    //     }
    // }

    public function syncDetail($data, $credentialId)
    {
        DB::beginTransaction();
        try {
            $purchaseNum = $data['cashPurchaseNum'];

            // 1. Simpan Detail Barang (Tetap sama)
            if (!empty($data['simplePurchaseDetails'])) {
                foreach ($data['simplePurchaseDetails'] as $item) {
                    DB::table('tbl_simple_purchase_details')->updateOrInsert(
                        [
                            'purchase_num'  => $purchaseNum,
                            'product_detail_id'    => $item['productDetailID'],
                            'credential_id' => $credentialId
                        ],
                        [
                            'product_name' => $item['productName'],
                            'uom_name'     => $item['uomName'],
                            'qty'          => $item['qty'],
                            'price'        => $item['price'],
                            'subtotal'     => $item['total'],
                            'notes'        => $item['notes'] ?? '',
                            'updated_at'   => now(),
                        ]
                    );
                }
            }

            // 2. Simpan Biaya Tambahan (Disesuaikan dengan JSON baru)
            if (!empty($data['simplePurchaseCosts'])) {
                foreach ($data['simplePurchaseCosts'] as $cost) {
                    DB::table('tbl_simple_purchase_costs')->updateOrInsert(
                        [
                            // Gunakan ID dari ESB dan credential_id sebagai kunci unik
                            'cost_id_esb'   => $cost['ID'],
                            'purchase_num'  => $purchaseNum,
                            'credential_id' => $credentialId
                        ],
                        [
                            'coa_no'       => $cost['coaNo'],
                            'description'  => $cost['description'], // Sebelumnya account_name
                            'amount'       => $cost['amount'],
                            'notes'        => $cost['notes'] ?? '',
                            'updated_at'   => now(),
                        ]
                    );
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Service Error (Detail) Purchase {$purchaseNum} Mitra {$credentialId}: " . $e->getMessage());
            throw $e;
        }
    }

    public function syncMasterSupplier($result, $credentialId)
    {
        // Berdasarkan JSON kamu, data ada di $result['data']
        $items = $result['data'] ?? [];

        if (!is_array($items)) {
            Log::warning("SyncMasterSupplier: Key 'data' tidak ditemukan atau bukan array.");
            return;
        }

        DB::beginTransaction();
        try {
            foreach ($items as $item) {
                // Pastikan $item adalah array (mencegah error offset pada int)
                if (!is_array($item)) continue;

                DB::table('tbl_suppliers')->updateOrInsert(
                    [
                        'supplier_id'   => $item['supplierID'],
                        'credential_id' => $credentialId
                    ],
                    [
                        'supplier_name'  => $item['supplierName'] ?? '',
                        'supplier_code'  => $item['supplierCode'] ?? '',
                        'address'        => $item['address'] ?? '',
                        'category'       => $item['category'] ?? 'Default',
                        'contact_person' => $item['contactPerson'] ?? '',
                        'flag_active'    => $item['flagActive'] ?? true,
                        // Mapping field tambahan dari JSON jika diperlukan
                        'updated_at'     => now(),
                    ]
                );
            }
            DB::commit();
            Log::info("SyncMasterSupplier: Berhasil sinkron " . count($items) . " supplier untuk ID: {$credentialId}");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error Sync Supplier Credential {$credentialId}: " . $e->getMessage());
            throw $e;
        }
    }
}
