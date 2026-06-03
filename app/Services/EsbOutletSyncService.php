<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EsbOutletSyncService
{
    public function normalizeText(?string $value): string
    {
        $value = Str::lower(trim((string) $value));
        $value = preg_replace('/[^a-z0-9\s]/', ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value);

        return trim($value);
    }

    public function syncBranchesToOutletsByCredential(object $credential, string $syncKey): array
    {
        $credentialId = (int) $credential->id;
        $companyCode = strtoupper(trim((string) $credential->credential_code));

        $branches = DB::table('tbl_api_credential_branches')
            ->where('credential_id', $credentialId)
            ->whereNotNull('branch_code')
            ->where('branch_code', '<>', '')
            ->orderBy('branch_code')
            ->get([
                'id',
                'credential_id',
                'branch_id',
                'branch_code',
                'branch_name',
            ]);

        $now = now();

        $processed = 0;
        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        $failed = 0;
        $detached = 0;
        $details = [];

        if ($branches->isEmpty()) {
            $this->appendProgress($syncKey, $companyCode, 0, 0, 0, 0, 0, 0, []);

            return [
                'status'    => 'done',
                'total'     => 0,
                'processed' => 0,
                'inserted'  => 0,
                'updated'   => 0,
                'skipped'   => 0,
                'failed'    => 0,
                'detached'  => 0,
                'details'   => [],
            ];
        }

        DB::beginTransaction();

        try {
            foreach ($branches as $branch) {
                $processed++;

                $branchId = ! empty($branch->branch_id) && is_numeric($branch->branch_id)
                    ? (int) $branch->branch_id
                    : null;

                $branchCode = strtoupper(trim((string) $branch->branch_code));
                $branchName = trim((string) ($branch->branch_name ?? ''));
                $normalizedBranchName = $this->normalizeText($branchName);

                if ($branchCode === '' || $normalizedBranchName === '') {
                    $failed++;

                    $details[] = [
                        'branch_code' => $branchCode ?: null,
                        'branch_name' => $branchName,
                        'status'      => 'failed',
                        'reason'      => 'branch_code / branch_name kosong',
                    ];

                    continue;
                }

                try {
                    $existing = $this->findExistingOutlet(
                        credentialId: $credentialId,
                        companyCode: $companyCode,
                        branchCode: $branchCode,
                        branchName: $branchName
                    );

                    if ($existing) {
                        $needsUpdate =
                            (int) ($existing->credential_id ?? 0) !== $credentialId ||
                            (int) ($existing->esb_branch_id ?? 0) !== (int) $branchId ||
                            strtoupper(trim((string) ($existing->branch_code ?? ''))) !== $branchCode ||
                            strtoupper(trim((string) ($existing->esb_branch_code ?? ''))) !== $branchCode ||
                            trim((string) ($existing->branch_name ?? '')) !== $branchName ||
                            trim((string) ($existing->nama_outlet ?? '')) !== $branchName ||
                            (int) ($existing->is_active ?? 0) !== 1 ||
                            ! is_null($existing->sync_issue ?? null);

                        if ($needsUpdate) {
                            DB::table('tbl_outlets')
                                ->where('id', $existing->id)
                                ->update([
                                    'credential_id'   => $credentialId,
                                    'kode_outlet'     => $companyCode,
                                    'esb_branch_id'   => $branchId,
                                    'esb_branch_code' => $branchCode,
                                    'branch_code'     => $branchCode,
                                    'branch_name'     => $branchName,
                                    'nama_outlet'     => $branchName,
                                    'outlet_key'      => $normalizedBranchName,
                                    'outlet_key_fix'  => $normalizedBranchName,
                                    'is_active'       => 1,
                                    'sync_issue'      => null,
                                    'updated_at'      => $now,
                                ]);

                            $updated++;

                            $details[] = [
                                'branch_code' => $branchCode,
                                'branch_name' => $branchName,
                                'status'      => 'updated',
                                'outlet_id'   => $existing->id,
                                'matched_by'  => $existing->matched_by ?? 'existing',
                            ];
                        } else {
                            $skipped++;

                            $details[] = [
                                'branch_code' => $branchCode,
                                'branch_name' => $branchName,
                                'status'      => 'skipped',
                                'outlet_id'   => $existing->id,
                                'matched_by'  => $existing->matched_by ?? 'existing',
                            ];
                        }

                        continue;
                    }

                    $insertPayload = [
                        'credential_id'   => $credentialId,
                        'kode_outlet'     => $companyCode,
                        'esb_branch_id'   => $branchId,
                        'esb_branch_code' => $branchCode,
                        'branch_code'     => $branchCode,
                        'branch_name'     => $branchName,
                        'nama_outlet'     => $branchName,
                        'outlet_key'      => $normalizedBranchName,
                        'outlet_key_fix'  => $normalizedBranchName,
                        'status'          => 'existing',
                        'is_active'       => 1,
                        'sync_issue'      => null,
                        'created_at'      => $now,
                        'updated_at'      => $now,
                    ];

                    $newId = DB::table('tbl_outlets')->insertGetId($insertPayload);

                    $inserted++;

                    $details[] = [
                        'branch_code' => $branchCode,
                        'branch_name' => $branchName,
                        'status'      => 'inserted',
                        'outlet_id'   => $newId,
                        'matched_by'  => 'new_insert',
                    ];
                } catch (\Throwable $e) {
                    $failed++;

                    $details[] = [
                        'branch_code' => $branchCode,
                        'branch_name' => $branchName,
                        'status'      => 'failed',
                        'reason'      => $e->getMessage(),
                    ];
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }

        $this->appendProgress(
            $syncKey,
            $companyCode,
            $processed,
            $inserted,
            $updated,
            $skipped,
            $failed,
            $detached,
            $details
        );

        return [
            'status'    => 'done',
            'total'     => $branches->count(),
            'processed' => $processed,
            'inserted'  => $inserted,
            'updated'   => $updated,
            'skipped'   => $skipped,
            'failed'    => $failed,
            'detached'  => $detached,
            'details'   => array_slice($details, -20),
        ];
    }

    protected function findExistingOutlet(
        int $credentialId,
        string $companyCode,
        string $branchCode,
        string $branchName
    ): ?object {
        $normalizedBranchName = $this->normalizeText($branchName);

        // Prioritas utama: 1 mitra + 1 branch_code aktif hanya boleh 1 outlet.
        $match = DB::table('tbl_outlets')
            ->where('is_active', 1)
            ->where('credential_id', $credentialId)
            ->where(function ($q) use ($branchCode) {
                $q->whereRaw('UPPER(COALESCE(branch_code, "")) = ?', [$branchCode])
                    ->orWhereRaw('UPPER(COALESCE(esb_branch_code, "")) = ?', [$branchCode]);
            })
            ->orderBy('id')
            ->first();

        if ($match) {
            $match->matched_by = 'credential_branch_code';

            return $match;
        }

        // Fallback aman: nama outlet dalam credential yang sama dan masih aktif.
        $match = $this->findSingleByName(
            DB::table('tbl_outlets')
                ->where('is_active', 1)
                ->where('credential_id', $credentialId)
                ->orderBy('id')
                ->get(),
            $normalizedBranchName,
            'credential_nama_outlet'
        );

        if ($match) {
            return $match;
        }

        // Fallback terakhir: kode_outlet + nama outlet aktif.
        $match = $this->findSingleByName(
            DB::table('tbl_outlets')
                ->where('is_active', 1)
                ->whereRaw('UPPER(COALESCE(kode_outlet, "")) = ?', [$companyCode])
                ->orderBy('id')
                ->get(),
            $normalizedBranchName,
            'kode_outlet_nama_outlet'
        );

        return $match ?: null;
    }

    protected function findSingleByName(
        $candidates,
        string $normalizedBranchName,
        string $matchedBy
    ): ?object {
        $matches = collect($candidates)
            ->filter(function ($row) use ($normalizedBranchName) {
                return $this->normalizeText($row->nama_outlet ?? '') === $normalizedBranchName;
            })
            ->values();

        if ($matches->count() === 0) {
            return null;
        }

        if ($matches->count() > 1) {
            $ids = $matches->pluck('id')->implode(', ');

            throw new \RuntimeException(
                "Duplicate outlet existing match by {$matchedBy}. Outlet IDs: {$ids}"
            );
        }

        $candidate = $matches->first();
        $candidate->matched_by = $matchedBy;

        return $candidate;
    }

    protected function appendProgress(
        string $syncKey,
        string $credentialCode,
        int $processed,
        int $inserted,
        int $updated,
        int $skipped,
        int $failed,
        int $detached,
        array $details
    ): void {
        $state = Cache::store('redis')->get("outlet_sync_multi:{$syncKey}", []);

        $perCredential = $state['per_credential'] ?? [];

        $perCredential[$credentialCode] = [
            'processed'    => $processed,
            'inserted'     => $inserted,
            'updated'      => $updated,
            'skipped'      => $skipped,
            'failed'       => $failed,
            'detached'     => $detached,
            'last_details' => array_slice($details, -5),
        ];

        $state['per_credential'] = $perCredential;
        $state['updated_at'] = now()->toDateTimeString();

        Cache::store('redis')->put(
            "outlet_sync_multi:{$syncKey}",
            $state,
            now()->addHours(6)
        );
    }
}
