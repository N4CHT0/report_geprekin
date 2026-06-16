<?php

namespace App\Http\Controllers;

use App\Jobs\SyncPnlLivePreparePagesJob;
use App\Services\EsbLedgerLivePnlService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PnlLiveSyncController extends Controller
{
    public function start(Request $request, EsbLedgerLivePnlService $service)
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'credential_code' => ['nullable', 'string', 'max:50'],
        ]);

        $credentialCode = strtoupper(trim($validated['credential_code'] ?? 'OKNHO'));
        $credential = $service->getActiveCredentialByCode($credentialCode);

        $startDate = Carbon::parse($validated['start_date'])->toDateString();
        $endDate = Carbon::parse($validated['end_date'])->toDateString();
        $syncKey = now()->format('YmdHis') . '_' . strtolower($credentialCode) . '_' . Str::random(6);
        $cacheKey = "pnl_live_sync:{$syncKey}";

        Cache::put($cacheKey, [
            'status' => 'queued',
            'message' => 'Sync PNL live masuk antrian.',
            'sync_key' => $syncKey,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'credential_id' => (int) $credential->id,
            'credential_code' => $credentialCode,
            'progress' => 0,
            'requested_at' => now()->toDateTimeString(),
            'started_at' => null,
            'finished_at' => null,
            'updated_at' => now()->toDateTimeString(),
            'finalized' => false,
        ], now()->addHours(12));

        Cache::put('pnl_live_sync_oknho_active_key', $syncKey, now()->addHours(12));

        SyncPnlLivePreparePagesJob::dispatch(
            syncKey: $syncKey,
            credentialCode: $credentialCode,
            credentialId: (int) $credential->id,
            startDate: $startDate,
            endDate: $endDate
        )->onConnection('redis')->onQueue('esb-pnl');

        return response()->json([
            'ok' => true,
            'sync_key' => $syncKey,
            'message' => 'Sync PNL live dimulai.',
        ]);
    }

    public function status(?string $syncKey = null)
    {
        $syncKey = $syncKey ?: Cache::get('pnl_live_sync_oknho_active_key');

        if (! $syncKey) {
            return response()->json([
                'ok' => false,
                'message' => 'Sync key tidak ditemukan.',
            ], 404);
        }

        $state = Cache::get("pnl_live_sync:{$syncKey}");

        if (! $state) {
            return response()->json([
                'ok' => false,
                'sync_key' => $syncKey,
                'message' => 'Status sync tidak ditemukan atau sudah expired.',
            ], 404);
        }

        return response()->json(array_merge(['ok' => true, 'sync_key' => $syncKey], $state));
    }
}
