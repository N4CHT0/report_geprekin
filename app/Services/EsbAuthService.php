<?php

namespace App\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class EsbAuthService
{
    protected string $baseUrl = 'https://services.esb.co.id/core';

    public function getCredentialById(int $credentialId): object
    {
        $credential = DB::table('tbl_api_credentials')
            ->where('id', $credentialId)
            ->where('is_active', 1)
            ->first();

        if (!$credential) {
            throw new Exception("Credential id {$credentialId} tidak ditemukan / tidak aktif.");
        }

        return $credential;
    }

    public function getCredentialByCode(string $credentialCode): object
    {
        $credential = DB::table('tbl_api_credentials')
            ->where('credential_code', $credentialCode)
            ->where('is_active', 1)
            ->first();

        if (!$credential) {
            throw new Exception("Credential {$credentialCode} tidak ditemukan / tidak aktif.");
        }

        return $credential;
    }

    public function getLatestSession(int $credentialId): ?object
    {
        return DB::table('tbl_api_sessions')
            ->where('credential_id', $credentialId)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();
    }

    public function getUsableSession(object $credential): object
    {
        $session = $this->getLatestSession($credential->id);

        if (!$session || empty($session->bearer_token)) {
            return $this->loginAndStore($credential);
        }

        return $session;
    }

    public function refreshOrLogin(object $credential): object
    {
        $session = $this->getLatestSession($credential->id);

        if ($session && !empty($session->refresh_token)) {
            try {
                return $this->refreshAndStore($credential, $session);
            } catch (\Throwable $e) {
                // fallback login
            }
        }

        return $this->loginAndStore($credential);
    }

    public function loginAndStore(object $credential): object
    {
        $url = rtrim($this->baseUrl, '/') . '/auth/login';

        // $passwordBaru = 'GeprekinAja123#';

        $response = Http::timeout(30)
            ->acceptJson()
            ->asJson()
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($url, [
                'username' => $credential->username,
                // 'password' => $passwordBaru,
                'password' => $credential->password,
            ]);

        if (!$response->successful()) {
            throw new Exception(
                "Login gagal [{$credential->credential_code}] HTTP {$response->status()} => " . $response->body()
            );
        }

        $json = $response->json();

        if (($json['status'] ?? null) !== 'ok') {
            throw new Exception(
                "Login gagal [{$credential->credential_code}] => " . json_encode($json)
            );
        }

        $result = $json['result'] ?? [];

        $accessToken  = $result['accessToken'] ?? null;
        $refreshToken = $result['refreshToken'] ?? null;
        $companyCode  = $result['companyCode'] ?? $credential->credential_code;
        $companyName  = $result['companyName'] ?? $credential->credential_name ?? null;

        if (!$accessToken) {
            throw new Exception("accessToken tidak ditemukan [{$credential->credential_code}]");
        }

        $existing = $this->getLatestSession($credential->id);

        if ($existing) {
            DB::table('tbl_api_sessions')
                ->where('id', $existing->id)
                ->update([
                    'bearer_token'  => $accessToken,
                    'refresh_token' => $refreshToken,
                    'company_code'  => $companyCode,
                    'company_name'  => $companyName,
                    'last_used_at'  => Carbon::now(),
                    'updated_at'    => Carbon::now(),
                ]);

            return DB::table('tbl_api_sessions')->where('id', $existing->id)->first();
        }

        $id = DB::table('tbl_api_sessions')->insertGetId([
            'credential_id' => $credential->id,
            'bearer_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'company_code'  => $companyCode,
            'company_name'  => $companyName,
            'expired_at'    => null,
            'last_used_at'  => Carbon::now(),
            'created_at'    => Carbon::now(),
            'updated_at'    => Carbon::now(),
        ]);

        return DB::table('tbl_api_sessions')->where('id', $id)->first();
    }

    public function refreshAndStore(object $credential, object $session): object
    {
        if (empty($session->refresh_token)) {
            throw new Exception("Refresh token kosong [{$credential->credential_code}]");
        }

        $url = rtrim($this->baseUrl, '/') . '/auth/refresh';

        $response = Http::timeout(30)
            ->acceptJson()
            ->withToken($session->refresh_token)
            ->get($url);

        if (!$response->successful()) {
            throw new Exception(
                "Refresh gagal [{$credential->credential_code}] HTTP {$response->status()} => " . $response->body()
            );
        }

        $json = $response->json();

        if (($json['status'] ?? null) !== 'ok') {
            throw new Exception(
                "Refresh gagal [{$credential->credential_code}] => " . json_encode($json)
            );
        }

        $result = $json['result'] ?? [];

        $accessToken  = $result['accessToken'] ?? null;
        $refreshToken = $result['refreshToken'] ?? $session->refresh_token;
        $companyCode  = $result['companyCode'] ?? $credential->credential_code;
        $companyName  = $result['companyName'] ?? $credential->credential_name ?? null;

        if (!$accessToken) {
            throw new Exception("accessToken tidak ditemukan dari refresh [{$credential->credential_code}]");
        }

        DB::table('tbl_api_sessions')
            ->where('id', $session->id)
            ->update([
                'bearer_token'  => $accessToken,
                'refresh_token' => $refreshToken,
                'company_code'  => $companyCode,
                'company_name'  => $companyName,
                'last_used_at'  => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ]);

        return DB::table('tbl_api_sessions')->where('id', $session->id)->first();
    }
}