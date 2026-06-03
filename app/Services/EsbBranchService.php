<?php

namespace App\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class EsbBranchService
{
    protected string $branchUrl = 'https://services.esb.co.id/core/branch/user';

    protected function fetchBranches(object $credential, object $session): array
    {
        $response = Http::timeout(120)
            ->acceptJson()
            ->withToken($session->bearer_token)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->get($this->branchUrl, [
                'flagActive' => 1,
            ]);

        if ($response->status() === 401) {
            throw new Exception(
                "Unauthorized [auth_credential={$credential->credential_code}, credential_id={$credential->id}, company_code={$session->company_code}] => "
                . $response->body()
            );
        }

        if (! $response->successful()) {
            throw new Exception(
                "Fetch branch gagal [auth_credential={$credential->credential_code}] HTTP {$response->status()} => "
                . $response->body()
            );
        }

        $json = $response->json();

        if (($json['status'] ?? null) !== 'ok') {
            throw new Exception(
                "Fetch branch gagal [auth_credential={$credential->credential_code}] => "
                . json_encode($json)
            );
        }

        $result = $json['result'] ?? [];

        if (! is_array($result)) {
            throw new Exception("Format result branch tidak valid [{$credential->credential_code}]");
        }

        return $result;
    }

    protected function fetchBranchesWithRetry(
        object $credential,
        object $session,
        EsbAuthService $authService
    ): array {
        $lastError = null;

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                if ($attempt > 1) {
                    $session = $authService->refreshOrLogin($credential);
                }

                $branches = $this->fetchBranches($credential, $session);

                if (empty($branches)) {
                    throw new Exception("ESB mengembalikan branch kosong [{$credential->credential_code}]");
                }

                return [$branches, $session];
            } catch (\Throwable $e) {
                $lastError = $e;
                usleep($attempt * 500000);
            }
        }

        throw $lastError ?? new Exception("Fetch branch gagal tanpa detail");
    }

    protected function normalizeCode(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $code = strtoupper(trim((string) $value));

        return $code === '' ? null : $code;
    }

    protected function normalizeName(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $name = trim((string) $value);

        return $name === '' ? null : $name;
    }

    protected function getCredentialByCode(string $credentialCode): object
    {
        $credential = DB::table('tbl_api_credentials')
            ->whereRaw('UPPER(credential_code) = ?', [strtoupper(trim($credentialCode))])
            ->where('is_active', 1)
            ->first();

        if (! $credential) {
            throw new Exception("Credential {$credentialCode} tidak ditemukan / tidak aktif.");
        }

        return $credential;
    }

    protected function getCredentialById(int $credentialId): object
    {
        $credential = DB::table('tbl_api_credentials')
            ->where('id', $credentialId)
            ->where('is_active', 1)
            ->first();

        if (! $credential) {
            throw new Exception("Credential id {$credentialId} tidak ditemukan / tidak aktif.");
        }

        return $credential;
    }

    protected function resolveTargetCredential(object $authCredential, object $session): object
    {
        /*
        |--------------------------------------------------------------------------
        | PENTING
        |--------------------------------------------------------------------------
        | Jangan simpan branch berdasarkan credential login mentah-mentah.
        |
        | Contoh kasus kamu:
        | - auth credential id 1 = BOD
        | - session company_code = OKNHO
        | - credential OKNHO asli = id 144
        |
        | Maka branch harus masuk ke credential_id 144, bukan 1.
        |--------------------------------------------------------------------------
        */

        $sessionCompanyCode = $this->normalizeCode($session->company_code ?? null);

        if (! $sessionCompanyCode) {
            return $authCredential;
        }

        $targetCredential = DB::table('tbl_api_credentials')
            ->whereRaw('UPPER(credential_code) = ?', [$sessionCompanyCode])
            ->where('is_active', 1)
            ->first();

        return $targetCredential ?: $authCredential;
    }

    protected function buildRows(array $branches, object $targetCredential, Carbon $now): array
    {
        $seen = [];
        $rows = [];

        $stats = [
            'api_total_rows'                    => count($branches),
            'valid_rows'                        => 0,
            'duplicate_rows'                    => 0,
            'missing_branch_code'               => 0,
            'missing_branch_id'                 => 0,
            'unique_branch_codes_in_credential' => 0,
        ];

        foreach ($branches as $branch) {
            $branchIdRaw = $branch['branchID']
                ?? $branch['branchId']
                ?? $branch['id']
                ?? null;

            $branchCode = $this->normalizeCode(
                $branch['branchCode']
                ?? $branch['code']
                ?? null
            );

            $branchName = $this->normalizeName(
                $branch['branchName']
                ?? $branch['name']
                ?? null
            );

            if (! $branchCode) {
                $stats['missing_branch_code']++;
                continue;
            }

            if ($branchIdRaw === null || trim((string) $branchIdRaw) === '') {
                $stats['missing_branch_id']++;
            }

            $seenKey = (int) $targetCredential->id . ':' . $branchCode;

            if (isset($seen[$seenKey])) {
                $stats['duplicate_rows']++;
                continue;
            }

            $seen[$seenKey] = true;

            $rows[] = [
                'credential_id' => (int) $targetCredential->id,
                'branch_id'     => $branchIdRaw !== null ? trim((string) $branchIdRaw) : null,
                'branch_code'   => $branchCode,
                'branch_name'   => $branchName ?: $branchCode,
                'created_at'    => $now,
                'updated_at'    => $now,
            ];

            $stats['valid_rows']++;
        }

        $stats['unique_branch_codes_in_credential'] = count($seen);

        return [$rows, $stats];
    }

    protected function upsertBranchRow(array $row): void
    {
        $exists = DB::table('tbl_api_credential_branches')
            ->where('credential_id', $row['credential_id'])
            ->where('branch_code', $row['branch_code'])
            ->exists();

        if ($exists) {
            DB::table('tbl_api_credential_branches')
                ->where('credential_id', $row['credential_id'])
                ->where('branch_code', $row['branch_code'])
                ->update([
                    'branch_id'   => $row['branch_id'],
                    'branch_name' => $row['branch_name'],
                    'updated_at'  => $row['updated_at'],
                ]);

            return;
        }

        DB::table('tbl_api_credential_branches')->insert($row);
    }

    public function syncBranchesByCredential(string $credentialCode): array
    {
        $credential = $this->getCredentialByCode($credentialCode);

        return $this->syncBranchesByCredentialObject(
            $credential,
            app(EsbAuthService::class)
        );
    }

    public function syncBranchesByCredentialId(int $credentialId, ?EsbAuthService $authService = null): array
    {
        $credential = $this->getCredentialById($credentialId);

        return $this->syncBranchesByCredentialObject(
            $credential,
            $authService ?: app(EsbAuthService::class)
        );
    }

    protected function syncBranchesByCredentialObject(
        object $authCredential,
        EsbAuthService $authService
    ): array {
        $authLockKey = "esb-branch-sync-auth:{$authCredential->id}";

        return Cache::store('redis')->lock($authLockKey, 600)->block(30, function () use (
            $authCredential,
            $authService
        ) {
            $session = $authService->getUsableSession($authCredential);

            [$branches, $session] = $this->fetchBranchesWithRetry(
                $authCredential,
                $session,
                $authService
            );

            $targetCredential = $this->resolveTargetCredential($authCredential, $session);

            $targetLockKey = "esb-branch-sync-target:{$targetCredential->id}";

            return Cache::store('redis')->lock($targetLockKey, 600)->block(30, function () use (
                $authCredential,
                $targetCredential,
                $session,
                $branches
            ) {
                $now = Carbon::now();

                [$rows, $stats] = $this->buildRows($branches, $targetCredential, $now);

                if (empty($rows)) {
                    throw new Exception(
                        "Tidak ada branch valid dari ESB [auth={$authCredential->credential_code}, target={$targetCredential->credential_code}]"
                    );
                }

                $beforeCount = DB::table('tbl_api_credential_branches')
                    ->where('credential_id', (int) $targetCredential->id)
                    ->count();

                $newCodes = collect($rows)
                    ->pluck('branch_code')
                    ->unique()
                    ->values()
                    ->all();

                DB::transaction(function () use (
                    $authCredential,
                    $targetCredential,
                    $session,
                    $rows,
                    $newCodes,
                    $now
                ) {
                    foreach (array_chunk($rows, 500) as $chunk) {
                        foreach ($chunk as $row) {
                            $this->upsertBranchRow($row);
                        }
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Hapus branch cache yang sudah tidak ada dari ESB untuk TARGET credential.
                    | Ini hanya tbl_api_credential_branches, bukan tbl_outlets.
                    |--------------------------------------------------------------------------
                    */
                    DB::table('tbl_api_credential_branches')
                        ->where('credential_id', (int) $targetCredential->id)
                        ->whereNotIn('branch_code', $newCodes)
                        ->delete();

                    /*
                    |--------------------------------------------------------------------------
                    | Kalau credential login adalah alias.
                    | Contoh: BOD login menghasilkan session company_code OKNHO.
                    | Jangan biarkan branch OKNHO tertinggal di credential_id BOD.
                    |--------------------------------------------------------------------------
                    */
                    if ((int) $authCredential->id !== (int) $targetCredential->id) {
                        DB::table('tbl_api_credential_branches')
                            ->where('credential_id', (int) $authCredential->id)
                            ->delete();
                    }

                    DB::table('tbl_api_sessions')
                        ->where('id', $session->id)
                        ->update([
                            'last_used_at' => $now,
                            'updated_at'   => $now,
                        ]);
                });

                $afterCount = DB::table('tbl_api_credential_branches')
                    ->where('credential_id', (int) $targetCredential->id)
                    ->count();

                return [
                    'status'                          => 'done',
                    'auth_credential_id'              => (int) $authCredential->id,
                    'auth_credential_code'            => $authCredential->credential_code,
                    'session_company_code'            => $session->company_code,
                    'target_credential_id'            => (int) $targetCredential->id,
                    'target_credential_code'          => $targetCredential->credential_code,
                    'credential_id'                   => (int) $targetCredential->id,
                    'credential_code'                 => $targetCredential->credential_code,
                    'api_total_rows'                  => $stats['api_total_rows'],
                    'valid_rows'                      => $stats['valid_rows'],
                    'duplicate_rows'                  => $stats['duplicate_rows'],
                    'missing_branch_code'             => $stats['missing_branch_code'],
                    'missing_branch_id'               => $stats['missing_branch_id'],
                    'unique_branch_codes_in_credential' => $stats['unique_branch_codes_in_credential'],
                    'db_total_before'                 => $beforeCount,
                    'db_total_after'                  => $afterCount,
                    'saved_branches'                  => $afterCount,
                ];
            });
        });
    }
}