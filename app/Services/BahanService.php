<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\EsbAuthService;

class BahanService
{
    protected $authService;

    public function __construct(EsbAuthService $authService)
    {
        $this->authService = $authService;
    }

    public function syncFromApi($page = 1, $limit = 10)
    {
        // 1. Ambil Credential
        $credential = DB::table('tbl_api_credentials')->where('credential_code', 'OKNHO')->first();
        if (!$credential) throw new \Exception("Credential API tidak ditemukan.");

        $token = trim($credential->static_token);
        $company = $credential->company_code ?? 'OKNHO';

        // 2. Request ke API ESB
        $response = Http::withToken($token)
            ->withoutVerifying()
            ->withHeaders(['company-code' => $company])
            ->get('https://core-api.esb.co.id/corev1/master/product', [
                'page'  => $page,
                'limit' => $limit
            ]);

        if (!$response->successful()) {
            throw new \Exception("ESB API Error: " . $response->status());
        }

        $data = $response->json();
        // REVISI: Pastikan ambil dari result.data
        $products = data_get($data, 'result.data', []);
        $totalData = data_get($data, 'result.count', 0);

        \Log::info("Halaman $page: Memproses " . count($products) . " produk.");

        // 3. Mapping Unit
        $unitMap = DB::table('tbl_units')->get()->mapWithKeys(function ($item) {
            return [strtoupper($item->nama_unit) => $item->id];
        })->toArray();

        // 4. Proses Simpan Data
        foreach ($products as $product) {
            // AMBIL productID (ini pasti unik 372, 373, dst)
            $pID = data_get($product, 'productID');
            $pName = data_get($product, 'productName');
            \Log::info('CHECK PRODUCT', [
                'page' => $page,
                'productID' => $pID,
                'productName' => $pName
            ]);
            if ($pID) {
                DB::transaction(function () use ($pID, $pName, $product, $unitMap) {

                    // GUNAKAN productID dari API sebagai product_code di tabelmu
                    DB::table('tbl_bahan_scm')->updateOrInsert(
                        ['product_code' => (string)$pID], // Ini kunci penguncinya
                        [
                            'nama_bahan'    => $pName,
                            'sumber_barang' => 'GUDANG',
                            'updated_at'    => now(),
                        ]
                    );

                    // Ambil ID primary key tabel lokal kita
                    $bahanId = DB::table('tbl_bahan_scm')->where('product_code', (string)$pID)->value('id');

                    $details = data_get($product, 'productDetails', []);
                    if ($bahanId && is_array($details)) {
                        // Hapus unit lama supaya tidak duplikat
                        DB::table('tbl_bahan_unit')->where('bahan_id', $bahanId)->delete();

                        foreach ($details as $detail) {
                            $uName = strtoupper(data_get($detail, 'unit', ''));
                            $unitId = $unitMap[$uName] ?? null;

                            if ($unitId) {
                                DB::table('tbl_bahan_unit')->insert([
                                    'product_detail_id' => data_get($detail, 'productDetailID'),
                                    'bahan_id'          => $bahanId,
                                    'unit_id'           => $unitId,
                                    'conversion_factor' => data_get($detail, 'conversionFactor', 1),
                                    'base_price'        => data_get($detail, 'basePrice', 0),
                                    'is_base_unit'      => (data_get($detail, 'defaultUnit.baseUnit') === 'Yes') ? 1 : 0,
                                    'is_stock_unit'     => (data_get($detail, 'defaultUnit.stockUnit') === 'Yes') ? 1 : 0,
                                    'is_purchase_unit'  => (data_get($detail, 'defaultUnit.purchaseUnit') === 'Yes') ? 1 : 0,
                                    'is_transfer_unit'  => (data_get($detail, 'defaultUnit.transferUnit') === 'Yes') ? 1 : 0,
                                    'is_sales_unit'     => (data_get($detail, 'defaultUnit.salesUnit') === 'Yes') ? 1 : 0,
                                ]);
                            }
                        }
                    }
                });
            }
        }

        return [
            'processed' => count($products),
            'total'     => $totalData
        ];
    }


    public function syncUnitsFromApi($page = 1)
    {
        $credentialCode = 'OKNHO';
        $baseUrl = 'https://services.esb.co.id/core';

        // 1. Ambil Data
        $session = DB::table('tbl_api_sessions')->where('company_code', $credentialCode)->first();
        $credential = DB::table('tbl_api_credentials')->where('credential_code', $credentialCode)->first();

        if (!$session || !$credential) throw new \Exception("Session/Credential missing.");

        // 2. Fungsi hit API yang diproteksi agar tidak crash jika kena HTML
        $getResponse = function ($token) use ($baseUrl, $credentialCode, $page) {
            return Http::withToken(trim($token))
                ->withHeaders([
                    'company-code' => $credentialCode,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ])
                // TAMBAHKAN query page di sini
                ->get(rtrim($baseUrl, '/') . '/units', ['page' => $page, 'limit' => 20]);
        };

        $response = $getResponse($session->bearer_token);

        // 3. Jika dapat HTML (error <) atau 401, paksa Login Ulang
        // Kita cek apakah body dimulai dengan '<'
        if (str_starts_with(trim($response->body()), '<') || $response->status() === 401) {
            Log::info("Mendapat HTML atau 401, melakukan Login Ulang Otomatis...");

            $loginRes = Http::post(rtrim($baseUrl, '/') . '/auth/login', [
                'username'     => $credential->username,
                'password'     => $credential->password,
                'company_code' => $credentialCode
            ]);

            if ($loginRes->successful()) {
                $loginData = $loginRes->json();
                $newToken = $loginData['result']['accessToken'] ?? null;
                $newRefresh = $loginData['result']['refreshToken'] ?? null;

                if ($newToken) {
                    // Simpan ke DB
                    DB::table('tbl_api_sessions')->where('id', $session->id)->update([
                        'bearer_token'  => $newToken,
                        'refresh_token' => $newRefresh ?? $session->refresh_token,
                        'updated_at'    => now()
                    ]);

                    // Coba ambil data lagi dengan token baru
                    $response = $getResponse($newToken);
                }
            }
        }

        // 4. Validasi Akhir (Pastikan bukan HTML)
        if (str_starts_with(trim($response->body()), '<')) {
            Log::error("ESB Return HTML: " . $response->body());
            throw new \Exception("API ESB mengembalikan HTML, bukan JSON. Cek log.");
        }

        if (!$response->successful()) throw new \Exception("ESB Error: " . $response->body());

        $data = $response->json();

        // Berdasarkan Tinker kamu: data ada di result['data'] atau result['rows']
        // karena result berisi pagination (page, limit)
        $units = $data['result']['data'] ?? $data['result']['rows'] ?? [];
        $total = $data['result']['count'] ?? 0; // Ambil total data untuk hitung progress

        if (empty($units)) return ['processed' => 0, 'total' => $total];

        $count = DB::transaction(function () use ($units) {
            $c = 0;
            foreach ($units as $unit) {
                $namaUnit = strtoupper(trim($unit['uomName'] ?? ''));
                if ($namaUnit) {
                    DB::table('tbl_units')->updateOrInsert(
                        ['nama_unit' => $namaUnit],
                        ['updated_at' => now()]
                    );
                    $c++;
                }
            }
            return $c;
        });

        // Kembalikan info jumlah yang diproses dan total data pusat
        return ['processed' => $count, 'total' => $total];
    }

    public function getUnitsTotal()
    {
        // Logika login & ambil total data saja (tanpa simpan dulu)
        $response = $this->callUnitApi(1); // ambil page 1
        return $response->json('result.count') ?? 0;
    }

    public function syncUnitsByPage($page)
    {
        // Logika ambil data per halaman tertentu dan simpan
        $response = $this->callUnitApi($page);
        $units = $response->json('result.data') ?? [];

        foreach ($units as $unit) {
            $namaUnit = strtoupper(trim($unit['uomName'] ?? ''));
            if ($namaUnit) {
                DB::table('tbl_units')->updateOrInsert(
                    ['nama_unit' => $namaUnit],
                    ['updated_at' => now()]
                );
            }
        }
        return count($units);
    }
    /**
     * Helper request agar kode tidak duplikat
     */
    private function makeEsbRequest($token, $companyCode)
    {
        return Http::withToken($token)
            ->withHeaders([
                'company-code' => $companyCode,
                'Accept' => 'application/json',
            ])
            ->timeout(30)
            ->get('https://services.esb.co.id/core/units');
    }

    public function syncMasterLocation($locations, $credentialId)
    {
        $dataToUpdate = [];

        foreach ($locations as $loc) {
            $dataToUpdate[] = [
                'location_id_esb' => $loc['locationID'],
                'credential_id'   => $credentialId,
                'location_name'   => $loc['locationName'],
                'updated_at'      => now()
            ];
        }

        if (!empty($dataToUpdate)) {
            // Param 1: Data yang dimasukkan
            // Param 2: Kolom yang harus unik (identitas)
            // Param 3: Kolom yang diupdate jika datanya sudah ada
            DB::table('tbl_esb_locations')->upsert(
                $dataToUpdate,
                ['location_id_esb', 'credential_id'],
                ['location_name', 'updated_at']
            );
        }
    }
}
