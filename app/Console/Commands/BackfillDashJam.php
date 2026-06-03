<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BackfillDashJam extends Command
{
    protected $signature = 'dash:backfill-jam {start=2025-09-01} {end=2026-03-01}';
    protected $description = 'Backfill tbl_dash_jam per hari (anti timeout)';

    public function handle()
    {
        $start = Carbon::parse($this->argument('start'));
        $end   = Carbon::parse($this->argument('end')); // end exclusive

        $this->info("Backfill tbl_dash_jam dari {$start->toDateString()} sampai {$end->toDateString()} (end exclusive)");

        $date = $start->copy();
        $totalDays = 0;

        while ($date->lt($end)) {
            $d = $date->toDateString();
            $this->line("-> Proses tanggal: {$d}");

            // pakai raw DB biar cepat
            DB::statement("
                INSERT INTO tbl_dash_jam (outlet_id, tanggal, jam, omset, cu, orders)
                SELECT
                    outlet_id,
                    sesi_tanggal AS tanggal,
                    HOUR(tr_waktu) AS jam,
                    SUM(item_sub_total) AS omset,
                    SUM(customer_unit) AS cu,
                    COUNT(*) AS orders
                FROM tbl_transaksi_perhari
                WHERE sesi_tanggal = ?
                GROUP BY outlet_id, sesi_tanggal, HOUR(tr_waktu)
                ON DUPLICATE KEY UPDATE
                    omset  = VALUES(omset),
                    cu     = VALUES(cu),
                    orders = VALUES(orders)
            ", [$d]);

            $totalDays++;
            $date->addDay();
        }

        $this->info("Selesai. Total hari diproses: {$totalDays}");
        return Command::SUCCESS;
    }
}