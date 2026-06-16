<?php

namespace App\Services;

use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EsbLedgerLivePnlService
{
    protected string $ledgerUrl = 'https://core-api.esb.co.id/corev1/general-ledger';

    public function getLiveLedgerPageMeta(
        string $credentialCode,
        int $credentialId,
        string $branchCode,
        string $startDate,
        string $endDate,
        int $limit = 100
    ): array {
        $credential = $this->getCredential($credentialCode, $credentialId);
        $session = $this->getSessionByCredentialId($credential->id);

        $response = $this->requestLedger(
            credential: $credential,
            session: $session,
            startPeriod: $startDate,
            endPeriod: $endDate,
            branchCode: $branchCode,
            page: 1,
            limit: $limit
        );

        $json = $response->json();

        if (! is_array($json)) {
            throw new Exception("Response ledger bukan JSON array [branch={$branchCode}, page=1]");
        }

        if (($json['status'] ?? null) !== 'ok') {
            throw new Exception('Ambil meta ledger gagal [branch=' . $branchCode . ']: ' . json_encode($json, JSON_UNESCAPED_UNICODE));
        }

        $apiDetailsCount = $this->countJournalDetails($json);
        [$count, $apiLimit] = $this->extractPaging($json, $apiDetailsCount);

        $pageCount = ($count > 0 && $apiLimit > 0)
            ? (int) ceil($count / $apiLimit)
            : 1;

        return [
            'count' => $count,
            'limit' => $apiLimit,
            'page_count' => max(1, $pageCount),
            'api_details_count' => $apiDetailsCount,
        ];
    }

    public function syncLiveLedgerBranchPageToStaging(
        string $syncKey,
        string $credentialCode,
        int $credentialId,
        string $branchCode,
        string $date,
        int $page,
        int $limit = 100
    ): array {
        $credential = $this->getCredential($credentialCode, $credentialId);
        $session = $this->getSessionByCredentialId($credential->id);

        $response = $this->requestLedger(
            credential: $credential,
            session: $session,
            startPeriod: $date,
            endPeriod: $date,
            branchCode: $branchCode,
            page: $page,
            limit: $limit
        );

        $json = $response->json();

        if (! is_array($json)) {
            throw new Exception("Response ledger bukan JSON array [branch={$branchCode}, page={$page}]");
        }

        if (($json['status'] ?? null) !== 'ok') {
            throw new Exception('Ambil live ledger gagal [branch=' . $branchCode . ', page=' . $page . ']: ' . json_encode($json, JSON_UNESCAPED_UNICODE));
        }

        $units = DB::table('tbl_outlets')
            ->where('credential_id', $credentialId)
            ->whereNotNull('esb_branch_code')
            ->where('esb_branch_code', '!=', '')
            ->select('id', 'nama_outlet', 'credential_id', 'esb_branch_id', 'esb_branch_code')
            ->get();

        $rows = $this->flattenLedgerRows($json, $branchCode);
        $savedRows = 0;
        $skippedRows = 0;
        $now = now();

        foreach ($rows as $index => $row) {
            $group = $this->mapPnlGroup(
                $row['account_no'] ?? null,
                $row['account_description'] ?? null,
                $row['transaction_type'] ?? null,
                $row['reference_number'] ?? null,
                $row['general_ledger_info'] ?? null,
                $row['notes'] ?? null
            );

            if (! $group) {
                $skippedRows++;
                continue;
            }

            $match = $this->findOutletByBranch(
                $units,
                $credentialId,
                (int) ($row['branch_id'] ?? 0),
                (string) ($row['branch_code'] ?? $branchCode)
            );

            if (! $match) {
                $skippedRows++;
                continue;
            }

            $debit = (float) ($row['debit'] ?? 0);
            $credit = (float) ($row['credit'] ?? 0);
            $nominal = $this->resolveNominalByGroup($group, $debit, $credit);
            $outlet = $match['outlet'];

            $hashPayload = [
                $credentialId,
                $date,
                $branchCode,
                $page,
                $index,
                $row['branch_id'] ?? null,
                $row['branch_code'] ?? null,
                $row['account_no'] ?? null,
                $row['reference_number'] ?? null,
                $row['transaction_type'] ?? null,
                $row['general_ledger_info'] ?? null,
                $row['notes'] ?? null,
                $debit,
                $credit,
            ];

            $rowHash = sha1(json_encode($hashPayload, JSON_UNESCAPED_UNICODE));

            DB::table('tbl_pnl_live_ledger_staging')->updateOrInsert(
                [
                    'sync_key' => $syncKey,
                    'row_hash' => $rowHash,
                ],
                [
                    'credential_id' => $credentialId,
                    'credential_code' => strtoupper($credentialCode),
                    'outlet_id' => (int) $outlet->id,
                    'branch_id' => (int) ($row['branch_id'] ?? 0) ?: null,
                    'branch_code' => trim((string) ($row['branch_code'] ?? $branchCode)),
                    'ledger_date' => $date,
                    'page' => $page,
                    'account_no' => $row['account_no'] ?? null,
                    'account_description' => $row['account_description'] ?? null,
                    'pnl_group' => $group,
                    'transaction_type' => $row['transaction_type'] ?? null,
                    'reference_number' => $row['reference_number'] ?? null,
                    'general_ledger_info' => $row['general_ledger_info'] ?? null,
                    'notes' => $row['notes'] ?? null,
                    'debit' => $debit,
                    'credit' => $credit,
                    'nominal' => $nominal,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            $savedRows++;
        }

        return [
            'api_rows' => count($rows),
            'saved_rows' => $savedRows,
            'skipped_rows' => $skippedRows,
        ];
    }

    public function buildPnlResultFromStaging(string $syncKey): array
    {
        $first = DB::table('tbl_pnl_live_ledger_staging')
            ->where('sync_key', $syncKey)
            ->first();

        $credentialId = $first ? (int) $first->credential_id : (int) (DB::table('tbl_pnl_live_ledger_staging')->where('sync_key', $syncKey)->value('credential_id') ?? 0);

        $units = collect([]);
        if ($credentialId > 0) {
            $units = DB::table('tbl_outlets')
                ->where('credential_id', $credentialId)
                ->whereNotNull('esb_branch_code')
                ->where('esb_branch_code', '!=', '')
                ->select('id', 'nama_outlet', 'credential_id', 'esb_branch_id', 'esb_branch_code')
                ->orderBy('nama_outlet')
                ->get();
        }

        $matrix = $this->buildMatrixTemplate($units);

        $aggregates = DB::table('tbl_pnl_live_ledger_staging')
            ->where('sync_key', $syncKey)
            ->whereNotNull('outlet_id')
            ->whereNotNull('pnl_group')
            ->select('outlet_id', 'pnl_group', DB::raw('SUM(nominal) as nominal'))
            ->groupBy('outlet_id', 'pnl_group')
            ->get();

        foreach ($aggregates as $row) {
            $group = (string) $row->pnl_group;
            $outletId = (int) $row->outlet_id;
            if (isset($matrix[$group][$outletId])) {
                $matrix[$group][$outletId] = (float) $row->nominal;
            }
        }

        $rows = $this->buildRowsFromMatrix($units, $matrix);
        $summary = $this->buildGrandSummary($rows);

        return [
            'units' => $units->map(fn ($u) => (array) $u)->values()->all(),
            'rows' => $rows,
            'grandPendapatan' => $summary['grandPendapatan'],
            'grandLaba' => $summary['grandLaba'],
            'grandNpm' => $summary['grandNpm'],
        ];
    }

    protected function flattenLedgerRows(array $json, string $branchCode): array
    {
        $rows = [];
        $groups = $json['result']['data'] ?? [];

        if (! is_array($groups)) {
            return [];
        }

        foreach ($groups as $group) {
            $journalDetails = $group['journalDetail'] ?? [];

            if (! is_array($journalDetails)) {
                continue;
            }

            foreach ($journalDetails as $detail) {
                if (! is_array($detail)) {
                    continue;
                }

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

        return $rows;
    }

    protected function countJournalDetails(array $json): int
    {
        $count = 0;
        $groups = $json['result']['data'] ?? [];

        if (! is_array($groups)) {
            return 0;
        }

        foreach ($groups as $group) {
            $details = $group['journalDetail'] ?? [];
            if (is_array($details)) {
                $count += count($details);
            }
        }

        return $count;
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

    protected function getCredential(string $credentialCode, ?int $credentialId = null): object
    {
        $query = DB::table('tbl_api_credentials')
            ->where('credential_code', $credentialCode)
            ->where('is_active', 1);

        if ($credentialId) {
            $query->where('id', $credentialId);
        }

        $credential = $query->first();

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
        int $page,
        int $limit = 100
    ): Response {
        $query = [
            'startPeriod' => $startPeriod,
            'endPeriod' => $endPeriod,
            'branchCode' => $branchCode,
            'costCenter' => 'No',
            'page' => $page,
            'limit' => $limit,
        ];

        $attemptConfigs = [];

        if ($session && ! empty($session->bearer_token)) {
            $attemptConfigs['bearer_plus_x_api_token'] = [
                'Authorization' => 'Bearer ' . $session->bearer_token,
                'X-API-TOKEN' => $credential->static_token,
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
                    'limit' => $limit,
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
        return mb_strlen($body) > $max ? mb_substr($body, 0, $max) . '...' : $body;
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
                return ['outlet' => $outlet, 'matched_by' => 'branch_id'];
            }
        }

        if ($branchCode !== '') {
            $normalizedBranchCode = strtoupper(trim($branchCode));
            $outlet = $units->first(function ($unit) use ($credentialId, $normalizedBranchCode) {
                return (int) $unit->credential_id === $credentialId
                    && strtoupper(trim((string) ($unit->esb_branch_code ?? ''))) === $normalizedBranchCode;
            });

            if ($outlet) {
                return ['outlet' => $outlet, 'matched_by' => 'branch_code'];
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
        $text = $this->normalizeText(implode(' ', array_filter([$accountDescription, $transactionType, $referenceNumber, $generalLedgerInfo, $notes])));

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
            if (Str::startsWith($accountNo, '7') || Str::startsWith($accountNo, '8')) {
                return 'Pendapatan (Biaya Lainnya)';
            }
        }

        if (Str::contains($desc, ['PENDAPATAN', 'PENJUALAN', 'SALES', 'OMSET'])) {
            return 'Pendapatan';
        }
        if (Str::contains($desc, ['HPP', 'COGS', 'BEBAN POKOK'])) {
            return 'HPP';
        }
        if (Str::contains($desc, ['OPEX', 'OPERASIONAL', 'BEBAN', 'BIAYA'])) {
            return 'Opex';
        }
        if (Str::contains($text, ['PENDAPATAN LAIN', 'BIAYA LAIN', 'OTHER INCOME', 'OTHER EXPENSE'])) {
            return 'Pendapatan (Biaya Lainnya)';
        }

        return null;
    }

    protected function resolveNominalByGroup(string $group, float $debit, float $credit): float
    {
        return match ($group) {
            'Pendapatan', 'Pendapatan (Biaya Lainnya)' => $credit - $debit,
            'HPP', 'Opex' => $debit - $credit,
            default => $debit - $credit,
        };
    }

    protected function buildMatrixTemplate(Collection $units): array
    {
        $groups = ['Pendapatan', 'HPP', 'Opex', 'Pendapatan (Biaya Lainnya)'];
        $matrix = [];

        foreach ($groups as $group) {
            $matrix[$group] = [];
            foreach ($units as $unit) {
                $matrix[$group][(int) $unit->id] = 0.0;
            }
        }

        return $matrix;
    }

    protected function buildRowsFromMatrix(Collection $units, array $matrix): array
    {
        $getValues = function (string $group) use ($units, $matrix) {
            return $units->map(fn ($u) => (float) ($matrix[$group][(int) $u->id] ?? 0))->values()->all();
        };

        $pendapatan = $getValues('Pendapatan');
        $hpp = $getValues('HPP');
        $opex = $getValues('Opex');
        $lainnya = $getValues('Pendapatan (Biaya Lainnya)');

        $laba = [];
        $npm = [];

        foreach ($units as $idx => $unit) {
            $p = (float) ($pendapatan[$idx] ?? 0);
            $h = (float) ($hpp[$idx] ?? 0);
            $o = (float) ($opex[$idx] ?? 0);
            $l = (float) ($lainnya[$idx] ?? 0);
            $net = $p - $h - $o + $l;

            $laba[] = $net;
            $npm[] = $p != 0.0 ? ($net / $p) * 100 : 0.0;
        }

        return [
            ['keterangan' => 'Pendapatan', 'values' => $pendapatan],
            ['keterangan' => 'HPP', 'values' => $hpp],
            ['keterangan' => 'Opex', 'values' => $opex],
            ['keterangan' => 'Pendapatan (Biaya Lainnya)', 'values' => $lainnya],
            ['keterangan' => 'Laba (Rugi) Bersih', 'values' => $laba],
            ['keterangan' => 'NPM', 'values' => $npm, 'is_percent' => true],
        ];
    }

    protected function buildGrandSummary(array $rows): array
    {
        $rowMap = [];
        foreach ($rows as $row) {
            $rowMap[$row['keterangan']] = $row;
        }

        $grandPendapatan = array_sum($rowMap['Pendapatan']['values'] ?? []);
        $grandLaba = array_sum($rowMap['Laba (Rugi) Bersih']['values'] ?? []);
        $grandNpm = $grandPendapatan != 0.0 ? ($grandLaba / $grandPendapatan) * 100 : 0.0;

        return [
            'grandPendapatan' => $grandPendapatan,
            'grandLaba' => $grandLaba,
            'grandNpm' => $grandNpm,
        ];
    }
}
