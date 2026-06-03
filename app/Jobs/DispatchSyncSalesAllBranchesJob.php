<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DispatchSyncSalesAllBranchesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $syncKey;
    public string $salesDate;

    public int $timeout = 300;
    public int $tries = 1;

    /**
     * Constructor tetap 2 parameter supaya cocok dengan controller/UI lama.
     */
    public function __construct(string $syncKey, string $salesDate)
    {
        $this->syncKey = $syncKey;
        $this->salesDate = $salesDate;

        $this->onConnection('redis');
        $this->onQueue('esb-sales');
    }

    public function handle(): void
    {
        $skipCredentialCodes = collect(explode(',', env('ESB_SALES_SYNC_SKIP_CREDENTIAL_CODES', '')))
            ->map(fn ($code) => strtoupper(trim($code)))
            ->filter()
            ->values()
            ->all();

        $now = now()->toDateTimeString();

        /*
        |--------------------------------------------------------------------------
        | Ambil branch VALID dari tbl_api_credential_branches
        |--------------------------------------------------------------------------
        | Jangan ambil semua tbl_outlets langsung, karena beberapa outlet bisa punya
        | branch_code yang tidak valid untuk token/credential tertentu dan ESB akan
        | balas HTTP 400.
        |
        | Flow:
        | credential aktif + static_token
        | -> branch valid di tbl_api_credential_branches
        | -> outlet aktif yang match credential_id + branch_code/esb_branch_code/esb_branch_id
        | -> dispatch per outlet dengan forcedOutletId.
        */
        $validBranchesQuery = DB::table('tbl_api_credentials as c')
            ->join('tbl_api_credential_branches as b', 'b.credential_id', '=', 'c.id')
            ->where('c.is_active', 1)
            ->whereNotNull('c.static_token')
            ->where('c.static_token', '!=', '')
            ->whereRaw("LOWER(TRIM(c.static_token)) <> 'none'")
            ->whereNotNull('b.branch_code')
            ->where('b.branch_code', '!=', '');

        if (! empty($skipCredentialCodes)) {
            $validBranchesQuery->whereNotIn(DB::raw('UPPER(c.credential_code)'), $skipCredentialCodes);
        }

        $validBranches = $validBranchesQuery
            ->orderBy('c.id')
            ->orderBy('b.branch_code')
            ->get([
                'c.id as credential_id',
                'c.credential_code',
                'c.credential_name',
                'b.branch_id',
                'b.branch_code',
                'b.branch_name',
            ]);

        $jobs = [];
        $skippedBranches = [];

        foreach ($validBranches as $branch) {
            $credentialId = (int) $branch->credential_id;
            $branchId = (int) ($branch->branch_id ?? 0);
            $branchCode = strtoupper(trim((string) ($branch->branch_code ?? '')));
            $branchName = trim((string) ($branch->branch_name ?? ''));

            if ($branchCode === '') {
                continue;
            }

            $outletQuery = DB::table('tbl_outlets')
                ->where('credential_id', $credentialId)
                ->where('is_active', 1);

            /*
             * Prioritas match:
             * 1. esb_branch_id exact
             * 2. esb_branch_code exact / normalized
             * 3. branch_code exact / normalized
             * 4. branch_name exact normalized
             *
             * Tidak pakai fuzzy agar tidak salah outlet untuk HPP/BOM.
             */
            $outlet = null;

            if ($branchId > 0) {
                $outlet = (clone $outletQuery)
                    ->where('esb_branch_id', $branchId)
                    ->orderBy('id')
                    ->first();
            }

            if (! $outlet) {
                $outlet = (clone $outletQuery)
                    ->where(function ($q) use ($branchCode) {
                        $q->whereRaw('UPPER(TRIM(COALESCE(esb_branch_code, ""))) = ?', [$branchCode])
                          ->orWhereRaw('UPPER(TRIM(COALESCE(branch_code, ""))) = ?', [$branchCode]);
                    })
                    ->orderBy('id')
                    ->first();
            }

            if (! $outlet && $branchName !== '') {
                $normalizedBranchName = strtolower(preg_replace('/\s+/', ' ', trim($branchName)));

                $outlet = (clone $outletQuery)
                    ->whereRaw('LOWER(TRIM(COALESCE(branch_name, ""))) = ?', [$normalizedBranchName])
                    ->orderBy('id')
                    ->first();
            }

            if (! $outlet) {
                $skippedBranches[] = [
                    'credential_id' => $credentialId,
                    'credential_code' => strtoupper(trim((string) ($branch->credential_code ?? ''))),
                    'branch_id' => $branchId,
                    'branch_code' => $branchCode,
                    'branch_name' => $branchName,
                    'reason' => 'Branch valid credential ditemukan, tapi outlet aktif tidak ketemu di tbl_outlets.',
                ];

                continue;
            }

            $jobs[] = [
                'credential_id' => $credentialId,
                'credential_code' => strtoupper(trim((string) ($branch->credential_code ?? ''))),
                'credential_name' => trim((string) ($branch->credential_name ?? '')),
                'branch_id' => $branchId,
                'branch_code' => $branchCode,
                'branch_name' => $branchName,
                'outlet_id' => (int) $outlet->id,
                'outlet_name' => (string) ($outlet->nama_outlet ?? ''),
            ];
        }

        /*
         * Hindari duplikat outlet/branch karena data mapping bisa punya overlap.
         */
        $jobs = collect($jobs)
            ->unique(fn ($row) => $row['credential_id'] . '|' . $row['branch_code'] . '|' . $row['outlet_id'])
            ->values()
            ->all();

        $totalJobs = count($jobs);
        $totalCredentials = collect($jobs)->pluck('credential_id')->unique()->count();

        $cacheKey = "sales_sync_all:{$this->syncKey}";
        $lockKey = "sales_sync_all_state_lock:{$this->syncKey}";

        Cache::store('redis')->lock($lockKey, 10)->block(10, function () use (
            $cacheKey,
            $totalJobs,
            $totalCredentials,
            $skipCredentialCodes,
            $skippedBranches,
            $now
        ) {
            Cache::store('redis')->put($cacheKey, [
                'status' => $totalJobs > 0 ? 'processing' : 'done',
                'message' => $totalJobs > 0
                    ? 'Menyiapkan job sync sales per outlet/branch valid.'
                    : 'Tidak ada outlet/branch valid untuk diproses.',
                'sales_date' => $this->salesDate,

                'total_credentials' => $totalCredentials,
                'prepared_credentials' => $totalCredentials,
                'processed_credentials' => 0,
                'success_credentials' => 0,
                'failed_credentials' => 0,

                'total_pages' => $totalJobs,
                'dispatched_pages' => 0,
                'processed_pages' => 0,
                'success_pages' => 0,
                'failed_pages' => 0,

                'total_branches' => $totalJobs,
                'processed_branches' => 0,
                'success_branches' => 0,
                'failed_branches' => 0,

                'total_api_rows' => 0,
                'total_built_rows' => 0,
                'total_inserted_rows' => 0,
                'progress' => $totalJobs > 0 ? 0 : 100,
                'skipped_credential_codes' => $skipCredentialCodes,
                'skipped_branches' => $skippedBranches,
                'requested_at' => $now,
                'started_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
                'finished_at' => $totalJobs > 0 ? null : $now,
                'logs' => [],
                'per_credential' => [],
                'per_branch' => [],
                'finalized' => $totalJobs === 0,
            ], now()->addHours(12));
        });

        if ($totalJobs === 0) {
            Cache::store('redis')->forget('sales_sync_all_active_key');
            return;
        }

        Cache::store('redis')->put('sales_sync_all_active_key', $this->syncKey, now()->addHours(12));

        $dispatched = 0;

        foreach ($jobs as $row) {
            SyncSalesAllOutletBranchJob::dispatch(
                $this->syncKey,
                $this->salesDate,
                (int) $row['credential_id'],
                (string) $row['credential_code'],
                (string) $row['credential_name'],
                (string) $row['branch_code'],
                (int) $row['outlet_id'],
                (string) $row['outlet_name'],
                $totalJobs
            )->onConnection('redis')->onQueue('esb-sales');

            $dispatched++;
        }

        Cache::store('redis')->lock($lockKey, 10)->block(10, function () use ($cacheKey, $dispatched) {
            $state = Cache::store('redis')->get($cacheKey, []);
            $state['dispatched_pages'] = $dispatched;
            $state['message'] = 'Job sync sales per outlet/branch valid sudah dikirim ke worker.';
            $state['updated_at'] = now()->toDateTimeString();

            Cache::store('redis')->put($cacheKey, $state, now()->addHours(12));
        });
    }
}
