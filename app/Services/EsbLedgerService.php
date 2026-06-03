<?php

namespace App\Services;

use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EsbLedgerService
{
    protected string $ledgerUrl = 'https://core-api.esb.co.id/corev1/general-ledger';

    public function parseNominal($value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return 0.0;
        }

        $isNegative = false;

        if (str_starts_with($value, '(') && str_ends_with($value, ')')) {
            $isNegative = true;
            $value = trim($value, '()');
        }

        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);

        $result = (float) $value;

        return $isNegative ? -1 * $result : $result;
    }

    protected function getCredential(string $credentialCode): object
    {
        $credential = DB::table('tbl_api_credentials')
            ->where('credential_code', $credentialCode)
            ->where('is_active', 1)
            ->first();

        if (!$credential) {
            throw new Exception("Credential {$credentialCode} tidak ditemukan atau tidak aktif.");
        }

        if (empty($credential->static_token)) {
            throw new Exception("Static token untuk credential {$credentialCode} kosong.");
        }

        return $credential;
    }

    protected function getSessionByCredentialId(int $credentialId): ?object
    {
        return DB::table('tbl_api_sessions')
            ->where('credential_id', $credentialId)
            ->whereNotNull('bearer_token')
            ->where('bearer_token', '!=', '')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();
    }

    protected function baseLedgerHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    protected function truncateBody(string $body, int $max = 1000): string
    {
        return mb_strlen($body) > $max
            ? mb_substr($body, 0, $max) . '...'
            : $body;
    }

    protected function sendLedgerRequest(array $headers, array $query): Response
    {
        return Http::timeout(120)
            ->retry(3, 1500, throw: false)
            ->withHeaders(array_merge($this->baseLedgerHeaders(), $headers))
            ->get($this->ledgerUrl, $query);
    }

    protected function requestLedger(
        object $credential,
        ?object $session,
        string $startPeriod,
        string $endPeriod,
        string $branchCode,
        int $page
    ): Response {
        $query = [
            'startPeriod' => $startPeriod,
            'endPeriod'   => $endPeriod,
            'branchCode'  => $branchCode,
            'costCenter'  => 'No',
            'page'        => $page,
        ];

        $attemptConfigs = [];

        if ($session && !empty($session->bearer_token)) {
            $attemptConfigs['bearer_plus_x_api_token'] = [
                'Authorization' => 'Bearer ' . $session->bearer_token,
                'X-API-TOKEN'   => $credential->static_token,
            ];

            $attemptConfigs['bearer_only'] = [
                'Authorization' => 'Bearer ' . $session->bearer_token,
            ];
        }

        $attemptConfigs['static_as_authorization'] = [
            'Authorization' => 'Bearer ' . $credential->static_token,
        ];

        $attemptConfigs['x_api_token_only'] = [
            'X-API-TOKEN' => $credential->static_token,
        ];

        $statuses = [];
        $bodies = [];

        foreach ($attemptConfigs as $name => $headers) {
            $response = $this->sendLedgerRequest($headers, $query);

            $statuses[] = "{$name}=" . $response->status();
            $bodies[$name] = $this->truncateBody((string) $response->body(), 1500);

            if ($response->successful()) {
                Log::info('LEDGER REQUEST SUCCESS', [
                    'credential_code' => $credential->credential_code,
                    'credential_id' => $credential->id,
                    'session_id' => $session->id ?? null,
                    'branch_code' => $branchCode,
                    'page' => $page,
                    'auth_mode' => $name,
                    'status' => $response->status(),
                ]);

                return $response;
            }
        }

        throw new Exception(
            "Ledger request gagal [credential={$credential->credential_code}, credential_id={$credential->id}, branch={$branchCode}, page={$page}] "
            . implode(', ', $statuses)
            . ' | body=' . reset($bodies)
        );
    }

    protected function extractPaging(array $json, int $apiDetailsCount = 0): array
    {
        $count = 0;
        $limit = 0;

        if (isset($json['result']['count']) || isset($json['result']['limit'])) {
            $count = (int) ($json['result']['count'] ?? 0);
            $limit = (int) ($json['result']['limit'] ?? 0);
        } elseif (isset($json['paging'])) {
            $count = (int) ($json['paging']['count'] ?? 0);
            $limit = (int) ($json['paging']['limit'] ?? 0);
        } else {
            $count = (int) ($json['resultCount'] ?? 0);
            $limit = (int) ($json['limit'] ?? 0);
        }

        if ($count <= 0) {
            $count = $apiDetailsCount;
        }

        if ($limit <= 0) {
            $limit = max($apiDetailsCount, 1);
        }

        return [$count, $limit];
    }

    protected function isOpeningBalance(array $detail): bool
    {
        $text = strtoupper(implode(' | ', array_filter([
            $detail['transactionType'] ?? null,
            $detail['referenceNumber'] ?? null,
            $detail['notes'] ?? null,
            $detail['generalLedgerInfo'] ?? null,
        ])));

        return Str::contains($text, ['OPENING BALANCE', 'SALDO AWAL']);
    }

    public function getLiveLedgerRowsByBranches(
        string $credentialCode,
        array $branchCodes,
        string $startPeriod,
        string $endPeriod
    ): array {
        $credential = $this->getCredential($credentialCode);
        $session = $this->getSessionByCredentialId($credential->id);

        $rows = [];
        $errors = [];

        foreach ($branchCodes as $branchCode) {
            try {
                $page = 1;
                $totalPage = null;
                $maxPageSafety = 5000;

                do {
                    $response = $this->requestLedger(
                        $credential,
                        $session,
                        $startPeriod,
                        $endPeriod,
                        $branchCode,
                        $page
                    );

                    $json = $response->json();

                    if (!is_array($json)) {
                        throw new Exception("Response ledger bukan JSON array [branch={$branchCode}, page={$page}]");
                    }

                    if (($json['status'] ?? null) !== 'ok') {
                        throw new Exception(
                            'Ambil live ledger gagal [branch=' . $branchCode . ', page=' . $page . ']: '
                            . json_encode($json, JSON_UNESCAPED_UNICODE)
                        );
                    }

                    $groups = $json['result']['data'] ?? [];

                    if (!is_array($groups) || empty($groups)) {
                        break;
                    }

                    $apiDetailsCount = 0;

                    foreach ($groups as $group) {
                        $journalDetails = $group['journalDetail'] ?? [];

                        if (!is_array($journalDetails)) {
                            continue;
                        }

                        foreach ($journalDetails as $detail) {
                            if (!is_array($detail)) {
                                continue;
                            }

                            $apiDetailsCount++;

                            if ($this->isOpeningBalance($detail)) {
                                continue;
                            }

                            $rows[] = [
                                'credential_id' => (int) $credential->id,
                                'branch_id' => (int) ($detail['branchID'] ?? 0),
                                'branch_code' => trim((string) ($detail['branchCode'] ?? $branchCode)),
                                'branch_name' => trim((string) ($detail['branchName'] ?? '')),
                                'account_no' => trim((string) ($detail['accountNo'] ?? '')),
                                'account_description' => trim((string) ($detail['accountDescription'] ?? '')),
                                'transaction_type' => trim((string) ($detail['transactionType'] ?? '')),
                                'reference_number' => trim((string) ($detail['referenceNumber'] ?? '')),
                                'notes' => trim((string) ($detail['notes'] ?? '')),
                                'general_ledger_info' => trim((string) ($detail['generalLedgerInfo'] ?? '')),
                                'coa_additional_info' => trim((string) ($detail['coaAdditionalInfo'] ?? '')),
                                'debit' => $this->parseNominal($detail['debitAmount'] ?? 0),
                                'credit' => $this->parseNominal($detail['creditAmount'] ?? 0),
                            ];
                        }
                    }

                    [$count, $limit] = $this->extractPaging($json, $apiDetailsCount);

                    if ($page === 1 && $count > 0 && $limit > 0) {
                        $totalPage = (int) ceil($count / $limit);
                    }

                    Log::info('LIVE LEDGER PAGE SUMMARY', [
                        'credential_id' => $credential->id,
                        'branch_code_request' => $branchCode,
                        'start_date' => $startPeriod,
                        'end_date' => $endPeriod,
                        'page' => $page,
                        'count' => $count,
                        'limit' => $limit,
                        'api_details_count' => $apiDetailsCount,
                        'rows_so_far' => count($rows),
                    ]);

                    if ($totalPage !== null) {
                        if ($page >= $totalPage) {
                            break;
                        }
                    } else {
                        if ($apiDetailsCount === 0) {
                            break;
                        }

                        if ($limit > 0 && $apiDetailsCount < $limit) {
                            break;
                        }
                    }

                    $page++;

                    if ($page > $maxPageSafety) {
                        throw new Exception("Stop by safety maxPage={$maxPageSafety} [branch={$branchCode}]");
                    }

                    usleep(150000);
                } while (true);
            } catch (\Throwable $e) {
                $errors[] = "BRANCH {$branchCode}: {$e->getMessage()}";

                Log::warning('LIVE LEDGER BRANCH FAILED', [
                    'credential_code' => $credentialCode,
                    'branch_code' => $branchCode,
                    'start_date' => $startPeriod,
                    'end_date' => $endPeriod,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        if ($session && !empty($session->id)) {
            DB::table('tbl_api_sessions')
                ->where('id', $session->id)
                ->update([
                    'last_used_at' => now(),
                    'updated_at'   => now(),
                ]);
        }

        return [$rows, $errors];
    }
}