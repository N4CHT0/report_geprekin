<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SalesInformationService
{
    // Fungsi untuk memproses satu halaman API
    public function processSinglePage(int $credentialId, string $date, int $page, string $token)
    {
        $response = Http::timeout(90)->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'application/json',
        ])->get('https://core-api.esb.co.id/corev1/sales/sales-information', [
            'salesDateFrom' => $date,
            'salesDateTo'   => $date,
            'statusName'    => 'Finished',
            'page'          => $page,
        ]);

        if (!$response->successful()) return false;

        $sales = $response->json();
        $rowsToInsert = [];
        $salesNums = [];

        foreach ($sales as $sale) {
            $salesNums[] = $sale['salesNum'];
            
            // 1. Baris Header (Transaction)
            $rowsToInsert[] = [
                'outlet_id'      => $this->findOutletId($sale['branchCode']),
                'nomor'          => $sale['salesNum'],
                'sesi_tanggal'   => $date,
                'tr_waktu'       => date('H:i:s', strtotime($sale['salesDateIn'])),
                'tr_metode'      => $sale['salesPayments'][0]['paymentMethodName'] ?? 'CASH',
                'item_nama'      => '__TRANSACTION__',
                'item_varian'    => $sale['statusName'],
                'item_harga'     => $sale['grandTotal'],
                'item_jumlah'    => 1,
                'item_sub_total' => $sale['grandTotal'],
                'item_status'    => $sale['statusID'],
            ];

            // 2. Baris Detail (Menu) - Tanpa filter status agar angka 76 muncul
            foreach ($sale['salesMenus'] as $menu) {
                $rowsToInsert[] = [
                    'outlet_id'      => $this->findOutletId($sale['branchCode']),
                    'nomor'          => $sale['salesNum'],
                    'sesi_tanggal'   => $date,
                    'tr_waktu'       => date('H:i:s', strtotime($sale['salesDateIn'])),
                    'tr_metode'      => $sale['salesPayments'][0]['paymentMethodName'] ?? 'CASH',
                    'item_nama'      => $menu['menuName'],
                    'item_varian'    => $sale['visitPurposeName'],
                    'item_harga'     => $menu['price'],
                    'item_jumlah'    => $menu['qty'],
                    'item_sub_total' => $menu['total'],
                    'item_status'    => $menu['statusID'], // Tetap simpan 13 (Preparing)
                ];
            }
        }

        if (!empty($rowsToInsert)) {
            DB::transaction(function () use ($salesNums, $rowsToInsert) {
                // Hapus yang lama hanya nomor ini saja (Kunci agar data tidak hilang)
                DB::table('tbl_transaction_day')->whereIn('nomor', $salesNums)->delete();
                DB::table('tbl_transaction_day')->insert($rowsToInsert);
            });
        }

        return count($salesNums);
    }

    private function findOutletId($branchCode) {
        return DB::table('tbl_outlets')->where('branch_code', $branchCode)->value('id') ?? 0;
    }
}