<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Jobs\SyncEsbLedgerOutlet;

class SyncEsbLedgerMissing extends Command
{
    protected $signature = 'sync:esb-ledger-missing 
                            {start : Start period}
                            {end : End period}';

    protected $description = 'Sync ESB ledger hanya untuk outlet yang belum punya data';

    public function handle()
    {
        $start = $this->argument('start');
        $end = $this->argument('end');

        $outletIds = DB::select("
            SELECT o.id
            FROM tbl_outlets o
            JOIN tbl_api_credential_branches b 
                ON b.branch_code = o.esb_branch_code
            JOIN tbl_api_credentials c 
                ON c.id = b.credential_id
            LEFT JOIN (
                SELECT DISTINCT branch_code
                FROM tbl_profit_loss_raw
                WHERE journal_date BETWEEN ? AND ?
            ) r
                ON r.branch_code = o.esb_branch_code
            WHERE o.esb_branch_code IS NOT NULL
            AND o.esb_branch_code <> ''
            AND c.is_active = 1
            AND r.branch_code IS NULL
        ", [$start, $end]);

        if (empty($outletIds)) {
            $this->info('Tidak ada outlet yang missing.');
            return;
        }

        foreach ($outletIds as $row) {
            SyncEsbLedgerOutlet::dispatch(
                $row->id,
                $start,
                $end
            )->onQueue('esb-ledger');
        }

        $this->info('Total outlet missing di-dispatch: ' . count($outletIds));
    }
}