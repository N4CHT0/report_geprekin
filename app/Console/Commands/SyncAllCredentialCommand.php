<?php

namespace App\Console\Commands;

use App\Jobs\SyncEsbSingleCredentialJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SyncAllCredentialCommand extends Command
{
    protected $signature = 'esb:sync-all-outlets';
    protected $description = 'Sync branch + outlet semua credential aktif';

    public function handle(): int
    {
        $credentials = DB::table('tbl_api_credentials')
            ->where('is_active', 1)
            ->orderBy('id')
            ->get(['id', 'credential_code']);

        if ($credentials->isEmpty()) {
            $this->warn('Tidak ada credential aktif.');
            return self::SUCCESS;
        }

        $syncKey = 'outlet-sync-' . now()->format('YmdHis');

        Cache::store('redis')->put("outlet_sync_multi:{$syncKey}", [
            'status'                => 'processing',
            'message'               => 'Sinkronisasi branch + outlet semua credential sedang berjalan.',
            'total_credentials'     => $credentials->count(),
            'processed_credentials' => 0,
            'success_credentials'   => 0,
            'failed_credentials'    => 0,
            'total_inserted'        => 0,
            'total_updated'         => 0,
            'total_skipped'         => 0,
            'total_failed_rows'     => 0,
            'progress'              => 0,
            'logs'                  => [],
            'per_credential'        => [],
            'updated_at'            => now()->toDateTimeString(),
            'finished_at'           => null,
            'finalized'             => false,
        ], now()->addHours(6));

        foreach ($credentials as $credential) {
            SyncEsbSingleCredentialJob::dispatch($syncKey, (int) $credential->id);
        }

        $this->info("Dispatch selesai. sync_key={$syncKey}, total={$credentials->count()}");

        return self::SUCCESS;
    }
}