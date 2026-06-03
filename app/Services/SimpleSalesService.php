<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SimpleSalesService
{
    private function refreshTokenService()
    {
        try {
            // 1. Ambil kredensial dari database
            $creds = DB::table('tbl_api_credentials')
                ->where('credential_code', 'OKNHO')
                ->first();

            if (!$creds) {
                Log::error("Refresh Token Gagal: Kredensial OKNHO tidak ditemukan di database.");
                return null;
            }

            // 2. Request token baru ke API ESB (Sesuaikan URL login ESB jika berbeda)
            $response = Http::withoutVerifying()
                ->post('https://services.esb.co.id/core/auth/login', [
                    'username'    => $creds->username,
                    'password'    => $creds->password,
                ]);

            if ($response->successful()) {
                $newToken = data_get($response->json(), 'result.accessToken');

                if ($newToken) {
                    // 3. Simpan token baru ke tbl_api_sessions
                    DB::table('tbl_api_sessions')->updateOrInsert(
                        ['id' => 1], // Sesuaikan ID atau kriteria baris di tabel session Anda
                        [
                            'bearer_token' => $newToken,
                            'updated_at'   => now()
                        ]
                    );
                    Log::info("Token ESB berhasil diperbarui.");
                    return $newToken;
                }
            }

            Log::error("Gagal Login ke ESB API: " . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error("Exception saat Refresh Token: " . $e->getMessage());
            return null;
        }
    }

    // public function syncFromApi($page = 1, $limit = 20, $start = null, $end = null)
    // {
    //     // 1. Inisialisasi return default untuk mencegah "Undefined Array Key" di Job
    //     $defaultReturn = [
    //         'processed' => 0,
    //         'total'     => 0,
    //         'next_page' => false
    //     ];

    //     try {
    //         // Ambil token
    //         $token = DB::table('tbl_api_sessions')->value('bearer_token');
    //         if (!$token) {
    //             Log::error("SimpleSalesService: Token tidak ditemukan.");
    //             return $defaultReturn;
    //         }

    //         $params = [
    //             'page'     => $page,
    //             'limit'    => $limit,
    //             'dateFrom' => $start ?? date('Y-m-d'),
    //             'dateTo'   => $end ?? date('Y-m-d'),
    //             'sort'     => '-simpleProductSalesNum'
    //         ];

    //         $response = Http::withToken($token)
    //             ->withoutVerifying()
    //             ->withHeaders(['company-code' => 'OKNHO'])
    //             ->get('https://services.esb.co.id/pilot-core/sales/simple-product-sales', $params);

    //         if (!$response->successful()) {
    //             Log::error("API Sales Error: " . $response->body());
    //             return $defaultReturn;
    //         }

    //         $resBody = $response->json();
    //         $salesData = data_get($resBody, 'result.data', []);
    //         $totalCount = data_get($resBody, 'result.count', 0);

    //         foreach ($salesData as $row) {
    //             $salesNum = $row['simpleProductSalesNum'];

    //             // Gunakan try-catch per baris agar satu error tidak menghentikan seluruh proses
    //             try {
    //                 DB::table('tbl_simple_sales')->updateOrInsert(
    //                     ['sales_num' => $salesNum],
    //                     [
    //                         // Perbaikan: Konversi format tanggal agar diterima MySQL DATETIME
    //                         'sales_date'    => \Carbon\Carbon::parse($row['simpleProductSalesDate'])->format('Y-m-d H:i:s'),
    //                         'branch_id'     => $row['branchID'],
    //                         'branch_name'   => $row['branchName'],
    //                         'customer_id'   => $row['customerID'],
    //                         'customer_name' => $row['customerName'],
    //                         // Perbaikan: Pastikan angka tidak null
    //                         'total_amount'  => $row['simpleProductSalesTotal'] ?? 0,
    //                         'status_id'     => $row['statusID'],
    //                         'status_name'   => $row['statusName'],
    //                         'notes'         => $row['additionalInfo'] ?? '',
    //                         'updated_at'    => now()
    //                     ]
    //                 );

    //                 $this->fetchSalesDetail($salesNum, $token);
    //             } catch (\Exception $e) {
    //                 // Jika gagal, log error-nya untuk debugging
    //                 Log::error("Gagal simpan Sales Num $salesNum: " . $e->getMessage());
    //             }
    //         }

    //         return [
    //             'processed' => count($salesData),
    //             'total'     => (int)$totalCount,
    //             'next_page' => !empty(data_get($resBody, 'result.next'))
    //         ];
    //     } catch (\Exception $e) {
    //         Log::error("SimpleSalesService Exception: " . $e->getMessage());
    //         return $defaultReturn;
    //     }
    // }

    public function syncFromApi($page = 1, $limit = 20, $start = null, $end = null)
    {
        $defaultReturn = [
            'processed' => 0,
            'total'     => 0,
            'next_page' => false
        ];

        try {
            // Ambil token dari DB
            $token = DB::table('tbl_api_sessions')->value('bearer_token');

            $params = [
                'page'     => $page,
                'limit'    => $limit,
                'dateFrom' => $start ?? date('Y-m-d'),
                'dateTo'   => $end ?? date('Y-m-d'),
                'sort'     => '-simpleProductSalesNum'
            ];

            // Bungkus request dalam fungsi pembantu agar bisa dipanggil ulang
            $response = $this->makeRequest($token, $params);

            // --- LOGIKA AUTO-REFRESH DIMULAI ---
            // Jika error adalah EC03100001 (Invalid Token), coba login ulang sekali
            if ($response->json('code') === 'EC03100001') {
                Log::info("Token expired, mencoba refresh token...");

                // Panggil method login Anda (sesuaikan namanya)
                $newToken = $this->refreshTokenService();

                if ($newToken) {
                    // Coba request sekali lagi dengan token baru
                    $response = $this->makeRequest($newToken, $params);
                    $token = $newToken; // Update variabel token untuk fetchSalesDetail nanti
                }
            }
            // --- LOGIKA AUTO-REFRESH SELESAI ---

            if (!$response->successful()) {
                Log::error("API Sales Error: " . $response->body());
                return $defaultReturn;
            }

            $resBody = $response->json();
            $salesData = data_get($resBody, 'result.data', []);
            $totalCount = data_get($resBody, 'result.count', 0);

            if (!is_array($salesData)) {
                Log::warning("Sales data bukan array untuk tanggal tersebut. Isi body: " . json_encode($resBody));
                $salesData = []; // Paksa jadi array kosong agar foreach tidak crash
            }

            foreach ($salesData as $row) {
                $salesNum = $row['simpleProductSalesNum'];
                try {
                    DB::table('tbl_simple_sales')->updateOrInsert(
                        ['sales_num' => $salesNum],
                        [
                            'sales_date'    => \Carbon\Carbon::parse($row['simpleProductSalesDate'])->format('Y-m-d H:i:s'),
                            'branch_id'     => $row['branchID'],
                            'branch_name'   => $row['branchName'],
                            'customer_id'   => $row['customerID'],
                            'customer_name' => $row['customerName'],
                            'total_amount'  => $row['simpleProductSalesTotal'] ?? 0,
                            'status_id'     => $row['statusID'],
                            'status_name'   => $row['statusName'],
                            'notes'         => $row['additionalInfo'] ?? '',
                            'updated_at'    => now()
                        ]
                    );

                    $this->fetchSalesDetail($salesNum, $token);
                } catch (\Exception $e) {
                    Log::error("Gagal simpan Sales Num $salesNum: " . $e->getMessage());
                }
            }

            return [
                'processed' => count($salesData),
                'total'     => (int)$totalCount,
                'next_page' => !empty(data_get($resBody, 'result.next'))
            ];
        } catch (\Exception $e) {
            Log::error("SimpleSalesService Exception: " . $e->getMessage());
            return $defaultReturn;
        }
    }

    /**
     * Fungsi pembantu untuk meminimalisir duplikasi kode request
     */
    private function makeRequest($token, $params)
    {
        return Http::withToken($token)
            ->withoutVerifying()
            ->withHeaders(['company-code' => 'OKNHO'])
            ->get('https://services.esb.co.id/pilot-core/sales/simple-product-sales', $params);
    }

    private function fetchSalesDetail($salesNum, $token)
    {
        try {
            $response = Http::withToken($token)
                ->withoutVerifying()
                ->withHeaders(['company-code' => 'OKNHO'])
                ->get("https://services.esb.co.id/pilot-core/sales/simple-product-sales/{$salesNum}");

            if ($response->successful()) {
                $details = data_get($response->json(), 'result.simpleProductSalesDetails', []);

                foreach ($details as $item) {
                    DB::table('tbl_simple_sales_detail')->updateOrInsert(
                        [
                            'sales_num'         => $salesNum,
                            'product_detail_id' => $item['productDetailID']
                        ],
                        [
                            'product_id'   => $item['productID'],
                            'product_name' => $item['productName'],
                            'uom_id'       => $item['uomID'],
                            'uom_name'     => $item['uomName'],
                            'qty'          => $item['qty'],
                            'price'        => $item['price'],
                            'total_line'   => $item['total'],
                            'hpp'          => $item['hpp'],
                            'updated_at'   => now()
                        ]
                    );
                }
            }
        } catch (\Exception $e) {
            Log::error("fetchSalesDetail Error ($salesNum): " . $e->getMessage());
        }
    }
}
