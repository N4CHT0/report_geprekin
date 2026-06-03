<?php

namespace App\Services;

use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EsbLedgerLivePnlService
{
    protected string $ledgerUrl = 'https://core-api.esb.co.id/corev1/general-ledger';

    public function buildLivePnlByBranches(
        string $credentialCode,
        int $credentialId,
        Collection $units,
        array $branchCodes,
        string $startDate,
        string $endDate,
        string $syncKey
    ): array {
        $credential = $this->getCredential($credentialCode);
        $session = $this->getSessionByCredentialId($credential->id);

        $matrix = $this->buildMatrixTemplate($units);

        $errors = [];
        $logs = [];
        $successBranches = 0;
        $failedBranches = 0;
        $processedBranches = 0;

        foreach ($branchCodes as $branchCode) {
            try {
                $branchRows = $this->getLedgerRowsPerBranch(
                    $credential,
                    $session,
                    $branchCode,
                    $startDate,
                    $endDate
                );

                foreach ($branchRows as $item) {
                    $group = $this->mapPnlGroup(
                        $item['account_no'] ?? null,
                        $item['account_description'] ?? null,
                        $item['transaction_type'] ?? null,
                        $item['reference_number'] ?? null,
                        $item['general_ledger_info'] ?? null,
                        $item['notes'] ?? null
                    );

                    if (! $group) {
                        continue;
                    }

                    $match = $this->findOutletByBranch(
                        $units,
                        $credentialId,
                        (int) ($item['branch_id'] ?? 0),
                        (string) ($item['branch_code'] ?? '')
                    );

                    if (! $match) {
                        $logs[] = [
                            'branch_code' => $branchCode,
                            'status' => 'skip',
                            'message' => 'Outlet tidak ditemukan untuk row ledger.',
                            'branch_id' => $item['branch_id'] ?? null,
                            'incoming_branch_code' => $item['branch_code'] ?? null,
                            'account_no' => $item['account_no'] ?? null,
                        ];
                        continue;
                    }

                    $outlet = $match['outlet'];

                    $debit = (float) ($item['debit'] ?? 0);
                    $credit = (float) ($item['credit'] ?? 0);

                    $nominal = $this->resolveNominalByGroup($group, $debit, $credit);

                    $matrix[$group][$outlet->id] += $nominal;
                }

                $successBranches++;
                $logs[] = [
                    'branch_code' => $branchCode,
                    'status' => 'success',
                    'message' => 'Branch berhasil diproses.',
                    'rows' => count($branchRows),
                ];
            } catch (\Throwable $e) {
                $failedBranches++;
                $errors[] = "BRANCH {$branchCode}: {$e->getMessage()}";

                $logs[] = [
                    'branch_code' => $branchCode,
                    'status' => 'failed',
                    'message' => $e->getMessage(),
                ];

                Log::warning('PNL LIVE BRANCH FAILED', [
                    'credential_code' => $credentialCode,
                    'credential_id' => $credentialId,
                    'branch_code' => $branchCode,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'message' => $e->getMessage(),
                ]);
            }

            $processedBranches++;

            $rows = $this->buildRowsFromMatrix($units, $matrix);
            $summary = $this->buildGrandSummary($rows);

            $this->updateProgressCache(
                $syncKey,
                $processedBranches,
                count($branchCodes),
                $successBranches,
                $failedBranches,
                $rows,
                $summary,
                $errors,
                $logs
            );
        }

        $rows = $this->buildRowsFromMatrix($units, $matrix);
        $summary = $this->buildGrandSummary($rows);

        return [
            'rows' => $rows,
            'grandPendapatan' => $summary['grandPendapatan'],
            'grandLaba' => $summary['grandLaba'],
            'grandNpm' => $summary['grandNpm'],
            'success_branches' => $successBranches,
            'failed_branches' => $failedBranches,
            'errors' => array_slice($errors, -100),
            'logs' => array_slice($logs, -100),
        ];
    }

    protected function updateProgressCache(
        string $syncKey,
        int $processedBranches,
        int $totalBranches,
        int $successBranches,
        int $failedBranches,
        array $rows,
        array $summary,
        array $errors,
        array $logs
    ): void {
        $cacheKey = "pnl_live_sync:{$syncKey}";
        $state = Cache::get($cacheKey);

        if (! $state) {
            return;
        }

        $progress = $totalBranches > 0
            ? (int) floor(($processedBranches / $totalBranches) * 100)
            : 100;

        Cache::put($cacheKey, array_merge($state, [
            'status' => 'processing',
            'message' => 'Sync PNL live sedang berjalan.',
            'processed_branches' => $processedBranches,
            'success_branches' => $successBranches,
            'failed_branches' => $failedBranches,
            'progress' => $progress,
            'updated_at' => now()->toDateTimeString(),
            'rows' => $rows,
            'grandPendapatan' => $summary['grandPendapatan'],
            'grandLaba' => $summary['grandLaba'],
            'grandNpm' => $summary['grandNpm'],
            'errors' => array_slice($errors, -100),
            'logs' => array_slice($logs, -100),
        ]), now()->addHours(6));
    }

    protected function getLedgerRowsPerBranch(
        object $credential,
        ?object $session,
        string $branchCode,
        string $startDate,
        string $endDate
    ): array {
        $rows = [];
        $page = 1;
        $totalPage = null;
        $maxPageSafety = 5000;

        do {
            $response = $this->requestLedger(
                $credential,
                $session,
                $startDate,
                $endDate,
                $branchCode,
                $page
            );

            $json = $response->json();

            if (! is_array($json)) {
                throw new Exception("Response ledger bukan JSON array [branch={$branchCode}, page={$page}]");
            }

            if (($json['status'] ?? null) !== 'ok') {
                throw new Exception(
                    'Ambil live ledger gagal [branch=' . $branchCode . ', page=' . $page . ']: '
                    . json_encode($json, JSON_UNESCAPED_UNICODE)
                );
            }

            $groups = $json['result']['data'] ?? [];

            if (! is_array($groups) || empty($groups)) {
                break;
            }

            $apiDetailsCount = 0;

            foreach ($groups as $group) {
                $journalDetails = $group['journalDetail'] ?? [];

                if (! is_array($journalDetails)) {
                    continue;
                }

                foreach ($journalDetails as $detail) {
                    if (! is_array($detail)) {
                        continue;
                    }

                    $apiDetailsCount++;

                    if ($this->isOpeningBalance($detail)) {
                        continue;
                    }

                    $rows[] = [
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
                'start_date' => $startDate,
                'end_date' => $endDate,
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

        return $rows;
    }

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

        if (! $credential) {
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

        if ($session && ! empty($session->bearer_token)) {
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

    protected function truncateBody(string $body, int $max = 1000): string
    {
        return mb_strlen($body) > $max
            ? mb_substr($body, 0, $max) . '...'
            : $body;
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

    protected function findOutletByBranch(Collection $units, int $credentialId, int $branchId, string $branchCode): ?array
    {
        if ($branchId > 0) {
            $outlet = $units->first(function ($unit) use ($credentialId, $branchId) {
                return (int) $unit->credential_id === $credentialId
                    && (int) ($unit->esb_branch_id ?? 0) === $branchId;
            });

            if ($outlet) {
                return [
                    'outlet' => $outlet,
                    'matched_by' => 'branch_id',
                ];
            }
        }

        if ($branchCode !== '') {
            $normalizedBranchCode = strtoupper(trim($branchCode));

            $outlet = $units->first(function ($unit) use ($credentialId, $normalizedBranchCode) {
                return (int) $unit->credential_id === $credentialId
                    && strtoupper(trim((string) ($unit->esb_branch_code ?? ''))) === $normalizedBranchCode;
            });

            if ($outlet) {
                return [
                    'outlet' => $outlet,
                    'matched_by' => 'branch_code',
                ];
            }
        }

        return null;
    }

    protected function normalizeText(?string $value): string
    {
        $value = strtoupper(trim((string) $value));
        return preg_replace('/\s+/', ' ', $value);
    }

    protected function normalizeAccountNo(?string $value): string
    {
        return preg_replace('/\s+/', '', strtoupper(trim((string) $value)));
    }

    protected function mapPnlGroup(
        ?string $accountNo,
        ?string $accountDescription,
        ?string $transactionType = null,
        ?string $referenceNumber = null,
        ?string $generalLedgerInfo = null,
        ?string $notes = null
    ): ?string {
        $accountNo = $this->normalizeAccountNo($accountNo);
        $desc = $this->normalizeText($accountDescription);
        $trx = $this->normalizeText($transactionType);
        $ref = $this->normalizeText($referenceNumber);
        $info = $this->normalizeText($generalLedgerInfo);
        $notes = $this->normalizeText($notes);

        if ($accountNo !== '') {
            if (Str::startsWith($accountNo, '4')) {
                return 'Pendapatan';
            }

            if (Str::startsWith($accountNo, '5')) {
                return 'HPP';
            }

            if (Str::startsWith($accountNo, '6')) {
                return 'Opex';
            }

            if (Str::startsWith($accountNo, ['7', '8'])) {
                return 'Pendapatan (Biaya Lainnya)';
            }
        }

        $text = implode(' | ', array_filter([$desc, $trx, $ref, $info, $notes]));

        if (Str::contains($text, ['PENJUALAN', 'SALES', 'GOFOOD', 'GRABFOOD', 'SHOPEEFOOD'])) {
            return 'Pendapatan';
        }

        if (Str::contains($text, ['HPP', 'COGS', 'BAHAN BAKU', 'COST OF GOODS'])) {
            return 'HPP';
        }

        if (Str::contains($text, ['GAJI', 'SEWA', 'LISTRIK', 'AIR', 'BEBAN', 'OPERASIONAL', 'PROMOSI'])) {
            return 'Opex';
        }

        if (Str::contains($text, ['PENDAPATAN LAIN', 'BIAYA LAIN', 'LAIN-LAIN', 'ADMIN BANK', 'BUNGA', 'DENDA'])) {
            return 'Pendapatan (Biaya Lainnya)';
        }

        return null;
    }

    protected function resolveNominalByGroup(string $group, float $debit, float $credit): float
    {
        if ($group === 'Pendapatan') {
            return $credit - $debit;
        }

        if (in_array($group, ['HPP', 'Opex'], true)) {
            return $debit - $credit;
        }

        return $credit - $debit;
    }

    protected function buildMatrixTemplate(Collection $units): array
    {
        $keys = $units->pluck('id')->all();

        return [
            'Pendapatan' => array_fill_keys($keys, 0),
            'HPP' => array_fill_keys($keys, 0),
            'Opex' => array_fill_keys($keys, 0),
            'Pendapatan (Biaya Lainnya)' => array_fill_keys($keys, 0),
        ];
    }

    protected function buildRowsFromMatrix(Collection $units, array $matrix): array
    {
        $pendapatan = [];
        $hpp = [];
        $opex = [];
        $lainnya = [];
        $labaBersih = [];
        $npm = [];

        foreach ($units as $unit) {
            $outletId = (int) $unit->id;

            $p = (float) ($matrix['Pendapatan'][$outletId] ?? 0);
            $h = (float) ($matrix['HPP'][$outletId] ?? 0);
            $o = (float) ($matrix['Opex'][$outletId] ?? 0);
            $l = (float) ($matrix['Pendapatan (Biaya Lainnya)'][$outletId] ?? 0);

            $n = $p - $h - $o + $l;

            $pendapatan[] = $p;
            $hpp[] = $h;
            $opex[] = $o;
            $lainnya[] = $l;
            $labaBersih[] = $n;
            $npm[] = $p != 0 ? ($n / $p) * 100 : 0;
        }

        return [
            ['keterangan' => 'Pendapatan', 'values' => $pendapatan],
            ['keterangan' => 'HPP', 'values' => $hpp],
            ['keterangan' => 'Opex', 'values' => $opex],
            ['keterangan' => 'Pendapatan (Biaya Lainnya)', 'values' => $lainnya],
            ['keterangan' => 'Laba (rugi) Bersih', 'values' => $labaBersih],
            ['keterangan' => 'NPM', 'values' => $npm, 'is_percent' => true],
        ];
    }

    protected function buildGrandSummary(array $rows): array
    {
        $pendapatan = $rows[0]['values'] ?? [];
        $labaBersih = $rows[4]['values'] ?? [];

        $grandPendapatan = array_sum($pendapatan);
        $grandLaba = array_sum($labaBersih);
        $grandNpm = $grandPendapatan != 0
            ? ($grandLaba / $grandPendapatan) * 100
            : 0;

        return [
            'grandPendapatan' => $grandPendapatan,
            'grandLaba' => $grandLaba,
            'grandNpm' => $grandNpm,
        ];
    }
}