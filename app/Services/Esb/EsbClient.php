<?php

namespace App\Services\Esb;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class EsbClient
{
    private function servicesBaseUrl(): string
    {
        $url = config('services.esb.services_base_url');

        if (!$url) {
            throw new RuntimeException('Config services.esb.services_base_url belum diset');
        }

        return rtrim($url, '/');
    }

    private function coreBaseUrl(): string
    {
        $url = config('services.esb.core_base_url');

        if (!$url) {
            throw new RuntimeException('Config services.esb.core_base_url belum diset');
        }

        return rtrim($url, '/');
    }

    private function resolveUsername(?string $username = null): string
    {
        $username = $username ?: config('services.esb.username');

        if (!$username) {
            throw new RuntimeException('ESB username belum diset');
        }

        return $username;
    }

    private function resolvePassword(?string $password = null): string
    {
        $password = $password ?: config('services.esb.password');

        if (!$password) {
            throw new RuntimeException('ESB password belum diset');
        }

        return $password;
    }

    private function resolveDefaultTokenKey(): string
    {
        $key = config('services.esb.default_token_key', 'OKNHO');

        if (!$key) {
            throw new RuntimeException('ESB default token key belum diset');
        }

        return strtoupper(trim($key));
    }

    private function staticTokens(): array
    {
        return config('services.esb.static_tokens', []);
    }

    public function staticTokenByKey(?string $tokenKey = null): string
    {
        $tokenKey = strtoupper(trim($tokenKey ?: $this->resolveDefaultTokenKey()));
        $tokens   = $this->staticTokens();
        $token    = $tokens[$tokenKey] ?? null;

        if (!$token) {
            throw new RuntimeException("ESB static token belum diset untuk key={$tokenKey}");
        }

        return trim($token);
    }

    public function login(array $credentials = []): array
    {
        $username = $this->resolveUsername($credentials['username'] ?? null);
        $password = $this->resolvePassword($credentials['password'] ?? null);

        $response = Http::baseUrl($this->servicesBaseUrl())
            ->acceptJson()
            ->timeout(60)
            ->post('/core/auth/login', [
                'username' => $username,
                'password' => $password,
            ]);

        if (!$response->successful()) {
            throw new RuntimeException(
                'Login ESB gagal. HTTP ' . $response->status() . ' :: ' . $response->body()
            );
        }

        $json = $response->json();

        if (!is_array($json)) {
            throw new RuntimeException('Response login ESB tidak valid / bukan JSON array');
        }

        return $json;
    }

    public function extractAccessToken(array $loginResponse): string
    {
        $token =
            data_get($loginResponse, 'accessToken') ??
            data_get($loginResponse, 'token') ??
            data_get($loginResponse, 'result.accessToken') ??
            data_get($loginResponse, 'result.token') ??
            data_get($loginResponse, 'data.accessToken') ??
            data_get($loginResponse, 'data.token');

        if (!$token || !is_string($token)) {
            throw new RuntimeException('accessToken tidak ditemukan pada response login ESB');
        }

        return trim($token);
    }

    public function loginBearerToken(?string $username = null, ?string $password = null): string
    {
        $loginResponse = $this->login([
            'username' => $username,
            'password' => $password,
        ]);

        return $this->extractAccessToken($loginResponse);
    }

    public function loginBearerTokenCached(?string $username = null, ?string $password = null, int $ttlMinutes = 50): string
    {
        $username = $this->resolveUsername($username);
        $password = $this->resolvePassword($password);

        $cacheKey = 'esb_bearer_token_' . md5($username . '|' . $password);

        return Cache::remember($cacheKey, now()->addMinutes($ttlMinutes), function () use ($username, $password) {
            return $this->loginBearerToken($username, $password);
        });
    }

    public function clientWithToken(string $token): PendingRequest
    {
        return Http::baseUrl($this->coreBaseUrl())
            ->acceptJson()
            ->withToken($token)
            ->timeout(60);
    }

    public function clientWithStaticToken(?string $tokenKey = null): PendingRequest
    {
        $token = $this->staticTokenByKey($tokenKey);

        return $this->clientWithToken($token);
    }

    public function clientWithBearerLogin(?string $username = null, ?string $password = null): PendingRequest
    {
        $token = $this->loginBearerTokenCached($username, $password);

        return $this->clientWithToken($token);
    }

    public function coreByAuth(array $auth = []): PendingRequest
    {
        $type = strtolower(trim((string) ($auth['type'] ?? 'static')));

        if ($type === 'bearer') {
            return $this->clientWithBearerLogin(
                $auth['username'] ?? null,
                $auth['password'] ?? null
            );
        }

        return $this->clientWithStaticToken(
            $auth['token_key'] ?? null
        );
    }

    public function resolveAuthMeta(array $auth = []): array
    {
        $type = strtolower(trim((string) ($auth['type'] ?? 'static')));

        if ($type === 'bearer') {
            return [
                'type'     => 'bearer',
                'username' => $this->resolveUsername($auth['username'] ?? null),
            ];
        }

        return [
            'type'      => 'static',
            'token_key' => strtoupper(trim((string) ($auth['token_key'] ?? $this->resolveDefaultTokenKey()))),
        ];
    }

    public function parseIdAmount($s): float
    {
        if ($s === null) return 0.0;

        $s = trim((string) $s);
        if ($s === '') return 0.0;

        $s = str_replace('.', '', $s);
        $s = str_replace(',', '.', $s);
        $s = preg_replace('/[^0-9\.\-]/', '', $s);

        return (float) $s;
    }

    public function toFloatId($value): float
    {
        $s = (string) ($value ?? '0');
        $s = str_replace('.', '', $s);
        $s = str_replace(',', '.', $s);
        $s = preg_replace('/[^0-9\.\-]/', '', $s);

        return (float) $s;
    }

    public function normalizeDate($v): string
    {
        $s = trim((string) $v);

        if ($s === '') {
            return '';
        }

        return strlen($s) >= 10 ? substr($s, 0, 10) : $s;
    }

    public function normalizeAccountNo(?string $acc): string
    {
        return preg_replace('/\s+/', ' ', trim((string) $acc));
    }

    public function normalizeAccountNoStrict(?string $acc): string
    {
        return preg_replace('/\s+/', '', trim((string) $acc));
    }

    private function extractGeneralLedgerRows(array $json): array
    {
        $candidates = [
            'result.data.0.journalDetail',
            'result.data.0.journal_detail',
            'result.data.0.journalDetailList',
            'result.data.0.journal_detail_list',
            'result.data.journalDetail',
            'result.data.journal_detail',
            'result.data.journalDetailList',
            'result.data.journal_detail_list',
            'result.journalDetail',
            'result.journal_detail',
        ];

        foreach ($candidates as $path) {
            $rows = data_get($json, $path);
            if (is_array($rows) && count($rows) > 0) {
                return $rows;
            }
        }

        $rows = data_get($json, 'result.data');
        if (is_array($rows) && isset($rows[0]) && is_array($rows[0])) {
            return $rows;
        }

        return [];
    }

    public function fetchGeneralLedgerAllByTokenKey(array $params, string $tokenKey, int $maxPages = 300): array
    {
        $allRows = [];
        $page    = (int) ($params['page'] ?? 1);

        $limit = null;
        $count = null;

        for ($i = 0; $i < $maxPages; $i++) {
            $params['page'] = $page;

            $res = $this->clientWithStaticToken($tokenKey)
                ->get('/corev1/general-ledger', $params);

            if (!$res->successful()) {
                return [
                    'ok'        => false,
                    'error'     => 'HTTP ' . $res->status(),
                    'token_key' => $tokenKey,
                    'raw'       => $res->json() ?: ['body' => $res->body()],
                    'rows'      => [],
                ];
            }

            $json = $res->json();
            $limit ??= (int) data_get($json, 'result.limit', 0);
            $count ??= (int) data_get($json, 'result.count', 0);

            $rows = $this->extractGeneralLedgerRows($json);
            if (!is_array($rows) || count($rows) === 0) {
                break;
            }

            $allRows = array_merge($allRows, $rows);

            if ($count > 0 && $limit > 0) {
                $totalPage = (int) ceil($count / max(1, $limit));
                if ($page >= $totalPage) {
                    break;
                }
            }

            $page++;
        }

        return [
            'ok'        => true,
            'error'     => null,
            'token_key' => $tokenKey,
            'raw'       => [
                'limit' => $limit,
                'count' => $count,
            ],
            'rows'      => $allRows,
        ];
    }

    public function mapGlRowsWithCoaReference(array $rows): array
    {
        $coaMap = DB::table('coa_reference')
            ->select('account_no', 'account_no_normalized', 'description_system')
            ->get()
            ->keyBy('account_no_normalized');

        $result = [];

        foreach ($rows as $row) {
            $accountNo = $this->normalizeAccountNo($row['accountNo'] ?? '');
            $normalized = $this->normalizeAccountNoStrict($accountNo);

            $coaRef = $coaMap[$normalized] ?? null;
            if (!$coaRef) {
                continue;
            }

            $row['accountNo'] = $accountNo;
            $row['accountDescriptionApi'] = $row['accountDescription'] ?? null;
            $row['accountDescriptionSystem'] = $coaRef->description_system;
            $row['accountDescription'] = $coaRef->description_system;

            $result[] = $row;
        }

        return $result;
    }

    public function fillSalesFromGlByCoaMap(
        array &$data,
        array $glRows,
        array $dateList,
        array $salesMap,
        int $jumlahHari,
        array $salesSign = []
    ): void {
        $dateIndexMap = [];

        foreach ($dateList as $idx => $date) {
            $dateIndexMap[$date] = $idx;
        }

        foreach ($glRows as $row) {
            $accountNo   = $this->normalizeAccountNo($row['accountNo'] ?? '');
            $journalDate = $this->normalizeDate($row['journalDate'] ?? '');
            $notes       = strtolower(trim((string) ($row['notes'] ?? '')));

            if ($notes === 'opening balance') {
                continue;
            }

            if (!isset($salesMap[$accountNo])) {
                continue;
            }

            if (!isset($dateIndexMap[$journalDate])) {
                continue;
            }

            $targetDesc = $salesMap[$accountNo];
            $dayIndex   = $dateIndexMap[$journalDate];

            $debit  = $this->toFloatId($row['debitAmount'] ?? 0);
            $credit = $this->toFloatId($row['creditAmount'] ?? 0);

            $amount = $credit - $debit;

            if (isset($salesSign[$accountNo]) && (int) $salesSign[$accountNo] === -1) {
                $amount *= -1;
            }

            if (!isset($data[$targetDesc])) {
                continue;
            }

            $data[$targetDesc]['hari'][$dayIndex]['sales'] += $amount;
        }
    }

    public function fillHppFromGlDebitCredit(
        array &$data,
        array $glRows,
        array $dateList,
        array $hppMap,
        int $jumlahHari
    ): void {
        $dateIndexMap = [];

        foreach ($dateList as $idx => $date) {
            $dateIndexMap[$date] = $idx;
        }

        foreach ($glRows as $row) {
            $accountNo   = $this->normalizeAccountNo($row['accountNo'] ?? '');
            $journalDate = $this->normalizeDate($row['journalDate'] ?? '');
            $notes       = strtolower(trim((string) ($row['notes'] ?? '')));

            if ($notes === 'opening balance') {
                continue;
            }

            if (!isset($hppMap[$accountNo])) {
                continue;
            }

            if (!isset($dateIndexMap[$journalDate])) {
                continue;
            }

            $targetDesc = $hppMap[$accountNo];
            $dayIndex   = $dateIndexMap[$journalDate];

            $debit  = $this->toFloatId($row['debitAmount'] ?? 0);
            $credit = $this->toFloatId($row['creditAmount'] ?? 0);

            $amount = $debit - $credit;

            if (!isset($data[$targetDesc])) {
                continue;
            }

            $data[$targetDesc]['hari'][$dayIndex]['sales'] += $amount;
        }
    }

    public function computeTotals(array &$data, int $jumlahHari): void
    {
        for ($d = 1; $d <= $jumlahHari; $d++) {
            $penjualanOutlet  = (float) ($data['Penjualan Outlet']['hari'][$d]['sales'] ?? 0);
            $penjualanMakanan = (float) ($data['Penjualan - Makanan']['hari'][$d]['sales'] ?? 0);
            $penjualanMinuman = (float) ($data['Penjualan - Minuman']['hari'][$d]['sales'] ?? 0);
            $penjualanLainnya = (float) ($data['Penjualan - Lainnya']['hari'][$d]['sales'] ?? 0);
            $penjualanBahan   = (float) ($data['Penjualan - Bahan']['hari'][$d]['sales'] ?? 0);

            $data['Total Sales']['hari'][$d]['sales'] =
                $penjualanOutlet +
                $penjualanMakanan +
                $penjualanMinuman +
                $penjualanLainnya +
                $penjualanBahan;

            $hppMakanan = (float) ($data['HPP - Makanan']['hari'][$d]['sales'] ?? 0);
            $hppMinuman = (float) ($data['HPP - Minuman']['hari'][$d]['sales'] ?? 0);
            $hppLainnya = (float) ($data['HPP - Lainnya']['hari'][$d]['sales'] ?? 0);
            $hppBahan   = (float) ($data['HPP - Bahan Terjual']['hari'][$d]['sales'] ?? 0);

            $data['Total Cost of Goods Sold']['hari'][$d]['sales'] =
                $hppMakanan + $hppMinuman + $hppLainnya + $hppBahan;

            $data['Gross Profit']['hari'][$d]['sales'] =
                (float) $data['Total Sales']['hari'][$d]['sales']
                - (float) $data['Total Cost of Goods Sold']['hari'][$d]['sales'];
        }
    }

    public function recalcSubtotals(array &$data, int $jumlahHari): void
    {
        foreach ($data as &$row) {
            $sumSales = 0.0;
            $sumCu    = 0;
            $sumAc    = 0.0;

            for ($d = 1; $d <= $jumlahHari; $d++) {
                $sumSales += (float) ($row['hari'][$d]['sales'] ?? 0);
                $sumCu    += (int) ($row['hari'][$d]['cu'] ?? 0);
                $sumAc    += (float) ($row['hari'][$d]['ac'] ?? 0);
            }

            $row['sub_total']['sales'] = $sumSales;
            $row['sub_total']['cu']    = $sumCu;
            $row['sub_total']['ac']    = $sumAc;
        }

        unset($row);
    }

    public function summarizeGlRows(array $rows): array
    {
        $totalDebit  = 0.0;
        $totalCredit = 0.0;
        $lastBalance = 0.0;

        foreach ($rows as $r) {
            $totalDebit  += $this->parseIdAmount($r['debitAmount'] ?? '0');
            $totalCredit += $this->parseIdAmount($r['creditAmount'] ?? '0');
            $lastBalance  = $this->parseIdAmount($r['balance'] ?? '0');
        }

        return [
            'total_debit'  => $totalDebit,
            'total_credit' => $totalCredit,
            'net'          => $totalDebit - $totalCredit,
            'last_balance' => $lastBalance,
        ];
    }
}