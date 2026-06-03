<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncSalesSelectedOutletsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;
    public int $tries = 1;

    public function __construct(
        public string $syncKey,
        public array $outletIds,
        public string $startDate,
        public string $endDate
    ) {
        $this->onConnection('redis');
        $this->onQueue('esb-sales');
    }

public function handle(): void
{
    $outlets = DB::table('tbl_outlets')
        ->whereIn('id', $this->outletIds)
        ->get(['id', 'nama_outlet', 'credential_id', 'branch_code']);

    $start = Carbon::parse($this->startDate);
    $end = Carbon::parse($this->endDate);

    $totalDays = $start->diffInDays($end) + 1;

    $validOutlets = $outlets->filter(function ($outlet) {
        return !empty($outlet->credential_id) && !empty($outlet->branch_code);
    });

    $totalJobs = $validOutlets->count() * $totalDays;

    $outletSummary = $outlets->map(function ($outlet) {
        return [
            'outlet_id' => $outlet->id,
            'outlet_name' => $outlet->nama_outlet,
            'credential_id' => $outlet->credential_id,
            'branch_code' => $outlet->branch_code,
            'status' => !empty($outlet->credential_id) && !empty($outlet->branch_code)
                ? 'valid'
                : 'skipped',
            'message' => !empty($outlet->credential_id) && !empty($outlet->branch_code)
                ? 'Outlet siap diproses.'
                : 'Outlet dilewati karena credential_id atau branch_code kosong.',
        ];
    })->values()->toArray();

    Cache::store('redis')->put("sales_selected_sync:{$this->syncKey}", [
        'status' => 'processing',
        'message' => 'Dispatch job sales outlet pilihan sedang berjalan.',
        'total_jobs' => $totalJobs,
        'dispatched_jobs' => 0,
        'requested_outlet_count' => count($this->outletIds),
        'found_outlet_count' => $outlets->count(),
        'valid_outlet_count' => $validOutlets->count(),
        'start_date' => $this->startDate,
        'end_date' => $this->endDate,
        'progress' => 0,
        'outlets' => $outletSummary,
        'logs' => [],
        'updated_at' => now()->toDateTimeString(),
    ], now()->addHours(6));

    $dispatched = 0;
    $logs = [];

    foreach ($validOutlets as $outlet) {
        $date = $start->copy();

        while ($date->lte($end)) {
            $salesDate = $date->format('Y-m-d');

            SyncSalesByBranchJob::dispatch(
                $this->syncKey,
                (int) $outlet->credential_id,
                (string) $outlet->branch_code,
                $salesDate,
                (int) $outlet->id
            )->onConnection('redis')->onQueue('esb-sales');

            $dispatched++;

            $logs[] = [
                'outlet_id' => $outlet->id,
                'outlet_name' => $outlet->nama_outlet,
                'branch_code' => $outlet->branch_code,
                'sales_date' => $salesDate,
                'status' => 'dispatched',
            ];

            Cache::store('redis')->put("sales_selected_sync:{$this->syncKey}", [
                'status' => 'processing',
                'message' => 'Job sales outlet pilihan sedang dikirim.',
                'total_jobs' => $totalJobs,
                'dispatched_jobs' => $dispatched,
                'requested_outlet_count' => count($this->outletIds),
                'found_outlet_count' => $outlets->count(),
                'valid_outlet_count' => $validOutlets->count(),
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'progress' => $totalJobs > 0
                    ? floor(($dispatched / $totalJobs) * 100)
                    : 100,
                'outlets' => $outletSummary,
                'logs' => array_slice($logs, -50),
                'updated_at' => now()->toDateTimeString(),
            ], now()->addHours(6));

            Log::info('DISPATCH SALES SELECTED OUTLET', end($logs));

            $date->addDay();
        }
    }

    Cache::store('redis')->put("sales_selected_sync:{$this->syncKey}", [
        'status' => 'done',
        'message' => 'Semua job sales outlet pilihan berhasil dikirim.',
        'total_jobs' => $totalJobs,
        'dispatched_jobs' => $dispatched,
        'requested_outlet_count' => count($this->outletIds),
        'found_outlet_count' => $outlets->count(),
        'valid_outlet_count' => $validOutlets->count(),
        'start_date' => $this->startDate,
        'end_date' => $this->endDate,
        'progress' => 100,
        'outlets' => $outletSummary,
        'logs' => array_slice($logs, -50),
        'updated_at' => now()->toDateTimeString(),
    ], now()->addHours(6));
}
}