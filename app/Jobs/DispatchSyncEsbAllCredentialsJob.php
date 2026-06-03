<?php

namespace App\Jobs;

use App\Services\EsbAuthService;
use App\Services\EsbBranchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DispatchSyncEsbAllCredentialsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $syncKey;

    public int $timeout = 1800;
    public int $tries = 1;

    public function __construct(string $syncKey)
    {
        $this->syncKey = $syncKey;

        $this->onConnection('redis');
        $this->onQueue('esb-sync');
    }

    public function middleware(): array
    {
        return [
            new WithoutOverlapping("dispatch-esb-sync-all"),
        ];
    }

    public function handle(
        EsbAuthService $authService,
        EsbBranchService $branchService
    ): void {
        try {
            $credentials = DB::table('tbl_api_credentials')
                ->where('is_active', 1)
                ->orderBy('credential_code')
                ->get();

            $total = $credentials->count();

            if ($total <= 0) {
                Cache::put("outlet_sync_multi:{$this->syncKey}", [
                    'status' => 'failed',
                    'message' => 'Credential aktif tidak ditemukan.',
                    'progress' => 100,
                    'total_credentials' => 0,
                    'processed_credentials' => 0,
                    'success_credentials' => 0,
                    'failed_credentials' => 0,
                    'updated_at' => now()->toDateTimeString(),
                    'finished_at' => now()->toDateTimeString(),
                ], now()->addHours(6));

                Cache::forget('outlet_sync_multi_lock');

                return;
            }

            Cache::put("outlet_sync_multi:{$this->syncKey}", [
                'status' => 'processing',
                'message' => 'Dispatch sync outlet semua credential dimulai.',
                'total_credentials' => $total,
                'processed_credentials' => 0,
                'success_credentials' => 0,
                'failed_credentials' => 0,
                'total_inserted' => 0,
                'total_updated' => 0,
                'total_skipped' => 0,
                'total_failed_rows' => 0,
                'total_detached' => 0,
                'progress' => 0,
                'logs' => [],
                'per_credential' => [],
                'started_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
                'finished_at' => null,
                'finalized' => false,
            ], now()->addHours(6));

            foreach ($credentials as $credential) {
                SyncEsbSingleCredentialJob::dispatch(
                    syncKey: $this->syncKey,
                    credentialId: (int) $credential->id
                )
                ->onConnection('redis')
                ->onQueue('esb-sync');
            }

            Log::info('DISPATCH SYNC ESB ALL CREDENTIALS SUCCESS', [
                'sync_key' => $this->syncKey,
                'total_credentials' => $total,
            ]);
        } catch (\Throwable $e) {

            Log::error('DISPATCH SYNC ESB ALL CREDENTIALS FAILED', [
                'sync_key' => $this->syncKey,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            Cache::put("outlet_sync_multi:{$this->syncKey}", [
                'status' => 'failed',
                'message' => $e->getMessage(),
                'progress' => 100,
                'updated_at' => now()->toDateTimeString(),
                'finished_at' => now()->toDateTimeString(),
            ], now()->addHours(6));

            Cache::forget('outlet_sync_multi_lock');

            throw $e;
        }
    }
}