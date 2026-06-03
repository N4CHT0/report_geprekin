<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerService
{
    /**
     * Fungsi Utama: Ambil Data Customer (Sync)
     */
    public function syncFromApi($page = 1, $limit = 20)
    {
        $defaultReturn = ['processed' => 0, 'total' => 0, 'next_page' => false];

        try {
            // 1. Ambil token yang spesifik milik OKNHO
            $token = $this->getExistingToken();

            if (!$token) {
                $token = $this->login(); // Login jika token belum ada di DB
            }

            if (!$token) return $defaultReturn;

            // 2. Request ke API ESB
            $response = $this->requestApi($token, $page, $limit);

            // 3. Tangani Token Expired (Berdasarkan kode error ESB tadi)
            if ($response->json('code') === 'EC03100001' || $response->status() == 401) {
                Log::warning("CustomerService: Token OKNHO invalid/expired, mencoba login ulang...");
                $token = $this->login();
                
                if ($token) {
                    $response = $this->requestApi($token, $page, $limit);
                }
            }

            if (!$response->successful()) {
                Log::error("API Customer Error: " . $response->body());
                return $defaultReturn;
            }

            $resBody = $response->json();
            $customerData = data_get($resBody, 'result.data', []);

            if (!is_array($customerData)) {
                $customerData = [];
            }

            // 4. Looping Simpan Data
            foreach ($customerData as $row) {
                try {
                    DB::table('tbl_customers')->updateOrInsert(
                        ['customerID' => $row['customerID']],
                        [
                            'customerName'         => $row['customerName'],
                            'customerCode'         => $row['customerCode'] ?? '',
                            'customerCategoryID'   => $row['customerCategoryID'],
                            'customerCategoryName' => $row['customerCategoryName'],
                            'paymentDueDays'       => $row['paymentDueDays'] ?? 0,
                            'address'              => $row['address'] ?? '',
                            'picName'              => $row['picName'] ?? '',
                            'picPhone'             => $row['picPhone'] ?? '',
                            'flagActive'           => $row['flagActive'],
                            'lockVat'              => $row['lockVat'] ?? 0,
                            'updated_at'           => now(),
                        ]
                    );
                } catch (\Exception $e) {
                    Log::error("Gagal simpan Customer ID " . ($row['customerID'] ?? '??') . ": " . $e->getMessage());
                }
            }

            return [
                'processed' => count($customerData),
                'total'     => (int)data_get($resBody, 'result.count', 0),
                'next_page' => !empty(data_get($resBody, 'result.next'))
            ];

        } catch (\Exception $e) {
            Log::error("CustomerService Exception: " . $e->getMessage());
            return $defaultReturn;
        }
    }

    /**
     * Helper: Request ke API
     */
    private function requestApi($token, $page, $limit)
    {
        return Http::withToken(trim($token))
            ->withoutVerifying()
            ->withHeaders([
                'company-code' => 'OKNHO',
                'Accept'       => 'application/json'
            ])
            ->timeout(30)
            ->get("https://services.esb.co.id/core/customer", [
                'page' => $page,
                'limit' => $limit
            ]);
    }

    /**
     * Ambil Token OKNHO dari Database
     */
    private function getExistingToken()
    {
        return DB::table('tbl_api_sessions as s')
            ->join('tbl_api_credentials as c', 's.credential_id', '=', 'c.id')
            ->where('c.credential_code', 'OKNHO')
            ->orderBy('s.id', 'desc')
            ->value('s.bearer_token');
    }

    /**
     * Fungsi Login khusus OKNHO
     */
    private function login()
    {
        $creds = DB::table('tbl_api_credentials')->where('credential_code', 'OKNHO')->first();

        if (!$creds) {
            Log::error("Login ESB Gagal: Kredensial OKNHO tidak ditemukan.");
            return null;
        }

        // Gunakan endpoint auth/login sesuai standar ESB biasanya
        $response = Http::withoutVerifying()->post("https://services.esb.co.id/auth/login", [
            'username' => $creds->username,
            'password' => $creds->password
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $newToken = data_get($data, 'result.token');
            
            if ($newToken) {
                $this->updateSession($creds->id, $newToken);
                return $newToken;
            }
        }

        Log::error("Login ESB OKNHO Gagal: " . $response->body());
        return null;
    }

    /**
     * Update/Insert Session
     */
    private function updateSession($credentialId, $token)
    {
        // Gunakan updateOrInsert berdasarkan credential_id agar session tidak duplikat banyak
        DB::table('tbl_api_sessions')->updateOrInsert(
            ['credential_id' => $credentialId],
            [
                'bearer_token' => $token,
                'updated_at'   => now(),
                'created_at'   => now(),
            ]
        );
    }
}