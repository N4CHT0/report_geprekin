<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\SyncEsbLedgerOutlet;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// ✅ SCHEDULER ESB
Schedule::call(function () {

    $date = now()->subDay()->format('Y-m-d');

    $outletIds = DB::table('tbl_outlets as o')
        ->join('tbl_api_credential_branches as b', 'b.branch_code', '=', 'o.esb_branch_code')
        ->join('tbl_api_credentials as c', 'c.id', '=', 'b.credential_id')
        ->where('c.is_active', 1)
        ->whereNotNull('o.esb_branch_code')
        ->where('o.esb_branch_code', '!=', '')
        ->distinct()
        ->pluck('o.id');

    foreach ($outletIds as $outletId) {
        SyncEsbLedgerOutlet::dispatch((int) $outletId, $date);
    }

})->dailyAt('01:00');