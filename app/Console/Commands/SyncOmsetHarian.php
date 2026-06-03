<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncOmsetHarian extends Command
{
    protected $signature = 'sync:omset-harian {--date=}';
    protected $description = 'Sync data transaksi harian ke tbl_omset_harian';

    public function handle()
    {
        $date = $this->option('date') ?? now()->toDateString();

        $this->info("Syncing omset harian untuk tanggal: {$date}");

        $data = DB::table('tbl_transaksi_perhari')
            ->selectRaw('outlet_id, DATE(sesi_tanggal) as tanggal, SUM(item_sub_total) as total_omset, SUM(customer_unit) as total_cu')
            ->where('item_status', 1)
            ->whereDate('sesi_tanggal', $date)
            ->groupBy('outlet_id', 'tanggal')
            ->get();

        foreach ($data as $row) {
            DB::table('tbl_omset_harian')->updateOrInsert(
                [
                    'outlet_id' => $row->outlet_id,
                    'tanggal'   => $row->tanggal,
                ],
                [
                    'total_omset' => $row->total_omset,
                    'total_cu'    => $row->total_cu,
                ]
            );
        }

        $this->info("Sync selesai untuk {$date} dengan total baris: " . $data->count());
    }
}
