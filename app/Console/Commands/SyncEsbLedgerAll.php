<?php

namespace App\Console\Commands;

use App\Jobs\SyncEsbLedgerBranch;
use App\Services\EsbLedgerService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class SyncEsbLedgerAll extends Command
{
    protected $signature = 'sync:esb-ledger-all
                            {start : Start period format Y-m-d}
                            {end : End period format Y-m-d}
                            {--limit=0 : Batasi jumlah branch}
                            {--outlet_id=0 : Sync hanya 1 outlet}';

    protected $description = 'Sync general ledger ESB otomatis berdasarkan branch ESB aktif';

    public function handle(EsbLedgerService $service): int
    {
        set_time_limit(0);

        try {
            $startPeriod = Carbon::createFromFormat('Y-m-d', $this->argument('start'))->format('Y-m-d');
            $endPeriod   = Carbon::createFromFormat('Y-m-d', $this->argument('end'))->format('Y-m-d');
        } catch (Throwable $e) {
            $this->error('Format tanggal harus Y-m-d. Contoh: 2026-01-01');
            return self::FAILURE;
        }

        $limit = (int) $this->option('limit');
        $outletId = (int) $this->option('outlet_id');

        if ($startPeriod > $endPeriod) {
            $this->error('Start period tidak boleh lebih besar dari end period.');
            return self::FAILURE;
        }

        $this->info('Mulai sync ESB ledger');
        $this->line("Periode      : {$startPeriod} s/d {$endPeriod}");
        $this->line("Limit branch : " . ($limit > 0 ? $limit : 'ALL'));
        $this->line("Outlet ID    : " . ($outletId > 0 ? $outletId : 'ALL'));
        $this->newLine();

        try {
            if ($outletId > 0) {
                $this->info("Mode single outlet: {$outletId}");

                $result = $service->syncGeneralLedgerByOutletIdAllPagesAuto(
                    $outletId,
                    $startPeriod,
                    $endPeriod
                );

                $this->table(
                    ['Outlet ID', 'Outlet', 'Credential', 'Branch Code', 'Total Page', 'Saved Rows'],
                    [[
                        $result['outlet_id'],
                        $result['nama_outlet'],
                        $result['credential_code'],
                        $result['branch_code'],
                        $result['total_page'],
                        $result['total_saved_rows'],
                    ]]
                );

                $this->info('Selesai.');
                return self::SUCCESS;
            }

            $branchQuery = DB::table('tbl_api_credential_branches as b')
                ->join('tbl_api_credentials as c', 'c.id', '=', 'b.credential_id')
                ->where('c.is_active', 1)
                ->whereNotNull('b.branch_code')
                ->where('b.branch_code', '!=', '')
                ->select(
                    'c.credential_code',
                    'b.branch_code',
                    'b.branch_name'
                )
                ->groupBy('c.credential_code', 'b.branch_code', 'b.branch_name')
                ->orderBy('c.credential_code')
                ->orderBy('b.branch_code');

            if ($limit > 0) {
                $branchQuery->limit($limit);
            }

            $branches = $branchQuery->get();

            if ($branches->isEmpty()) {
                $this->warn('Tidak ada branch aktif untuk di-dispatch.');
                return self::FAILURE;
            }

            $dates = collect(CarbonPeriod::create($startPeriod, $endPeriod))
                ->map(fn ($date) => $date->format('Y-m-d'));

            $totalJobs = 0;

            foreach ($dates as $syncDate) {
                foreach ($branches as $branch) {
                    SyncEsbLedgerBranch::dispatch(
                        $branch->credential_code,
                        $branch->branch_code,
                        $syncDate,
                        null,
                        $branch->branch_name
                    );

                    $totalJobs++;
                }
            }

            $this->info('Total tanggal        : ' . $dates->count());
            $this->info('Total branch aktif   : ' . $branches->count());
            $this->info('Total job dispatch   : ' . $totalJobs);
            $this->info('Job berhasil dimasukkan ke queue esb-ledger.');

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Gagal dispatch job: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}