<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BcaRekonSqlService
{
    private string $omsetTable = 'tbl_dsc_omset_setoran';
    private string $paymentTable = 'tbl_bca_mutasi';
    private string $vaMapTable = 'tbl_payment_va_mapping';

    public function listRekon(?string $startDate = null, ?string $endDate = null, $outletId = null, array $filters = [])
    {
        $startDate = $this->normalizeDate($startDate ?: date('Y-m-d'));
        $endDate = $this->normalizeDate($endDate ?: $startDate);

        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $outletIds = $this->parseOutletIds($filters['outlet_ids'] ?? null);
        if (empty($outletIds) && $outletId !== null && $outletId !== '') {
            $outletIds = [(int) $outletId];
        }

        $status = trim((string) ($filters['status'] ?? ''));
        $provider = trim((string) ($filters['provider'] ?? ''));

        $rows = DB::table($this->omsetTable . ' as o')
            ->leftJoin($this->paymentTable . ' as p1', 'p1.id', '=', 'o.s1_bca_mutasi_id')
            ->leftJoin($this->paymentTable . ' as p2', 'p2.id', '=', 'o.s2_bca_mutasi_id')
            ->whereBetween('o.tanggal', [$startDate, $endDate])
            ->when(!empty($outletIds), fn ($q) => $q->whereIn('o.outlet_id', $outletIds))
            ->select([
                'o.id',
                'o.outlet_id',
                'o.tanggal',
                'o.pic',

                'o.s1_sudah_disetor',
                'o.s1_tanggal_setor',
                'o.s1_bca_ref',
                'o.s1_bca_mutasi_id',
                'o.s1_rekon_status',
                'o.s1_rekon_selisih',
                'o.s1_rekon_note',
                'p1.tanggal as s1_payment_tanggal',
                'p1.nominal as s1_payment_nominal',
                'p1.reference_no as s1_payment_reference',
                'p1.description as s1_payment_description',

                'o.s2_sudah_disetor',
                'o.s2_tanggal_setor',
                'o.s2_bca_ref',
                'o.s2_bca_mutasi_id',
                'o.s2_rekon_status',
                'o.s2_rekon_selisih',
                'o.s2_rekon_note',
                'p2.tanggal as s2_payment_tanggal',
                'p2.nominal as s2_payment_nominal',
                'p2.reference_no as s2_payment_reference',
                'p2.description as s2_payment_description',
            ])
            ->orderBy('o.tanggal')
            ->orderBy('o.outlet_id')
            ->orderBy('o.id')
            ->get();

        if ($status !== '') {
            $rows = $rows->filter(function ($r) use ($status) {
                return (string) ($r->s1_rekon_status ?? 'PENDING') === $status
                    || (string) ($r->s2_rekon_status ?? 'PENDING') === $status;
            })->values();
        }

        if ($provider !== '') {
            $providerLower = strtolower($provider);
            $rows = $rows->filter(function ($r) use ($providerLower) {
                return str_contains(strtolower((string) ($r->s1_payment_description ?? '')), 'provider=' . $providerLower)
                    || str_contains(strtolower((string) ($r->s2_payment_description ?? '')), 'provider=' . $providerLower);
            })->values();
        }

        return $rows;
    }

    public function listOutlets()
    {
        return DB::table('tbl_outlets')
            ->select('id', 'nama_outlet')
            ->orderBy('nama_outlet')
            ->orderBy('id')
            ->get();
    }

    public function listProviders()
    {
        if (!Schema::hasTable($this->paymentTable)) {
            return collect(['MANUAL']);
        }

        return DB::table($this->paymentTable)
            ->select('description')
            ->whereNotNull('description')
            ->limit(500)
            ->get()
            ->map(function ($row) {
                if (preg_match('/PROVIDER=([^|]+)/i', (string) $row->description, $m)) {
                    return trim($m[1]);
                }
                return null;
            })
            ->filter()
            ->unique()
            ->values()
            ->whenEmpty(fn ($c) => collect(['MANUAL']));
    }

    public function listVaMappings()
    {
        if (!Schema::hasTable($this->vaMapTable)) {
            return collect();
        }

        return DB::table($this->vaMapTable . ' as m')
            ->leftJoin('tbl_outlets as o', 'o.id', '=', 'm.outlet_id')
            ->select('m.*', 'o.nama_outlet')
            ->orderBy('m.provider')
            ->orderBy('o.nama_outlet')
            ->orderBy('m.shift')
            ->get();
    }

    public function saveVaMapping(array $data): array
    {
        $outletId = (int) ($data['outlet_id'] ?? 0);
        $shiftRaw = $data['shift'] ?? null;
        $shift = in_array((string) $shiftRaw, ['1', '2'], true) ? (int) $shiftRaw : null;
        $provider = trim((string) ($data['provider'] ?? ''));
        $vaNumber = trim((string) ($data['va_number'] ?? ''));
        $vaName = trim((string) ($data['va_name'] ?? ''));

        if ($outletId <= 0 || $provider === '' || $vaNumber === '') {
            return ['ok' => false, 'message' => 'Outlet, provider, dan VA number wajib diisi.'];
        }

        $payload = [
            'outlet_id' => $outletId,
            'shift' => $shift,
            'provider' => strtoupper($provider),
            'va_number' => $vaNumber,
            'va_name' => $vaName !== '' ? $vaName : null,
            'is_active' => !empty($data['is_active']) ? 1 : 0,
            'updated_at' => now(),
        ];

        $existing = DB::table($this->vaMapTable)
            ->where('provider', $payload['provider'])
            ->where('va_number', $vaNumber)
            ->first();

        if ($existing) {
            DB::table($this->vaMapTable)->where('id', $existing->id)->update($payload);
            return ['ok' => true, 'message' => 'Mapping VA berhasil diupdate.'];
        }

        $payload['created_at'] = now();
        DB::table($this->vaMapTable)->insert($payload);

        return ['ok' => true, 'message' => 'Mapping VA berhasil disimpan.'];
    }

    public function importManual(array $data): array
    {
        $tanggal = $this->normalizeDate((string) ($data['tanggal'] ?? date('Y-m-d')));
        $nominal = $this->num($data['nominal'] ?? 0);
        $tipe = strtoupper(trim((string) ($data['tipe'] ?? 'CR'))) ?: 'CR';
        $referenceNo = trim((string) ($data['reference_no'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));
        $provider = strtoupper(trim((string) ($data['provider'] ?? 'MANUAL'))) ?: 'MANUAL';
        $vaNumber = trim((string) ($data['va_number'] ?? ''));

        if ($nominal <= 0) {
            return ['ok' => false, 'message' => 'Nominal wajib lebih dari 0.'];
        }

        $referenceNo = $referenceNo !== '' ? $referenceNo : 'MANUAL-' . date('YmdHis') . '-' . mt_rand(1000, 9999);

        $exists = DB::table($this->paymentTable)
            ->where('reference_no', $referenceNo)
            ->exists();

        if ($exists) {
            return ['ok' => false, 'message' => 'Reference / transaction id sudah pernah diinput.'];
        }

        $descParts = ['PROVIDER=' . $provider];
        if ($vaNumber !== '') $descParts[] = 'VA=' . $vaNumber;
        if ($description !== '') $descParts[] = $description;

        DB::table($this->paymentTable)->insert([
            'tanggal' => $tanggal,
            'nominal' => $nominal,
            'tipe' => $tipe,
            'reference_no' => $referenceNo,
            'description' => implode(' | ', $descParts),
            'raw_payload' => json_encode($data),
            'is_matched' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return ['ok' => true, 'message' => 'Payment manual berhasil disimpan. Jalankan Auto Validasi untuk mencocokkan dengan setoran.'];
    }

    public function importApi(array $items): array
    {
        // Kosong dulu untuk integrasi API/Xendit nanti.
        return ['ok' => true, 'inserted' => 0, 'skipped' => count($items), 'message' => 'Import API belum diaktifkan. Gunakan input manual dulu.'];
    }

    public function handleVaWebhook(array $payload): array
    {
        // KOSONG DULU SESUAI REQUEST.
        // Nanti bagian ini diisi saat token/callback Xendit sudah siap.
        return [
            'ok' => true,
            'status' => 'WEBHOOK_STUB',
            'message' => 'Webhook Xendit belum diaktifkan. Payload diterima tetapi belum disimpan/diproses.',
        ];
    }

    public function updateRef(int $rowId, int $shift, ?string $ref): array
    {
        $prefix = $this->prefix($shift);
        $ref = trim((string) $ref);

        DB::table($this->omsetTable)
            ->where('id', $rowId)
            ->update([
                $prefix . '_bca_ref' => $ref !== '' ? $ref : null,
                $prefix . '_rekon_status' => 'PENDING',
                $prefix . '_rekon_note' => $ref !== '' ? 'Reference diinput manual, siap divalidasi ulang.' : 'Reference dikosongkan.',
                'updated_at' => now(),
            ]);

        return ['ok' => true, 'message' => 'Reference berhasil diupdate.'];
    }

    public function matchOne(int $rowId, int $shift): array
    {
        $row = DB::table($this->omsetTable)->where('id', $rowId)->first();

        if (!$row) {
            return ['ok' => false, 'message' => 'Data setoran tidak ditemukan.'];
        }

        $result = $this->validateShiftPayment($row, $shift);
        $this->applyMatchResult((int) $row->id, $shift, $result);

        return ['ok' => true, 'message' => $result['note'], 'result' => $result];
    }

    public function matchAll(?string $startDate = null, $outletId = null, ?string $endDate = null, array $filters = []): array
    {
        $startDate = $this->normalizeDate($startDate ?: date('Y-m-d'));
        $endDate = $this->normalizeDate($endDate ?: $startDate);

        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $outletIds = $this->parseOutletIds($filters['outlet_ids'] ?? null);
        if (empty($outletIds) && $outletId !== null && $outletId !== '') {
            $outletIds = [(int) $outletId];
        }

        $rows = DB::table($this->omsetTable)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->when(!empty($outletIds), fn ($q) => $q->whereIn('outlet_id', $outletIds))
            ->get();

        $count = 0;
        $failed = 0;
        $summary = [
            'SUDAH_SESUAI' => 0,
            'UANG_KURANG' => 0,
            'UANG_LEBIH' => 0,
            'BELUM_DITRANSFER' => 0,
            'BUTUH_REVIEW' => 0,
            'PENDING' => 0,
        ];

        foreach ($rows as $row) {
            foreach ([1, 2] as $shift) {
                $prefix = $this->prefix($shift);
                $nominal = $this->num($row->{$prefix . '_sudah_disetor'} ?? 0);
                $tglSetor = $row->{$prefix . '_tanggal_setor'} ?? null;

                if ($nominal <= 0 || !$tglSetor) {
                    $this->updateRekonResult((int) $row->id, $shift, null, 'PENDING', 0, 'Tanggal setor atau nominal setoran belum lengkap.');
                    $summary['PENDING']++;
                    continue;
                }

                try {
                    $result = $this->validateShiftPayment($row, $shift);
                    $this->applyMatchResult((int) $row->id, $shift, $result);
                    $summary[$result['status']] = ($summary[$result['status']] ?? 0) + 1;
                    $count++;
                } catch (\Throwable $e) {
                    report($e);
                    $failed++;
                }
            }
        }

        return [
            'ok' => true,
            'message' => "Auto validasi selesai. Diproses {$count}, gagal {$failed}.",
            'matched' => $count,
            'failed' => $failed,
            'summary' => $summary,
        ];
    }

    public function validateLatestSetoran(int $outletId, string $tanggal, int $shift): array
    {
        $row = DB::table($this->omsetTable)
            ->where('outlet_id', $outletId)
            ->whereDate('tanggal', $this->normalizeDate($tanggal))
            ->orderByDesc('id')
            ->first();

        if (!$row) {
            return ['ok' => false, 'message' => 'Data setoran belum ditemukan.'];
        }

        return $this->matchOne((int) $row->id, $shift);
    }

    private function validateShiftPayment($row, int $shift): array
    {
        $prefix = $this->prefix($shift);
        $tanggalSetor = $row->{$prefix . '_tanggal_setor'} ?? null;
        $nominalForm = $this->num($row->{$prefix . '_sudah_disetor'} ?? 0);
        $ref = trim((string) ($row->{$prefix . '_bca_ref'} ?? ''));

        if (!$tanggalSetor || $nominalForm <= 0) {
            return [
                'status' => 'PENDING',
                'payment_id' => null,
                'reference_no' => $ref ?: null,
                'selisih' => 0,
                'note' => 'Tanggal setor atau nominal setoran belum lengkap.',
                'payment_nominal' => 0,
            ];
        }

        $candidates = $this->findPaymentCandidates($tanggalSetor, $nominalForm, $ref, (int) $row->id, $shift);

        if ($candidates->isEmpty()) {
            return [
                'status' => 'BELUM_DITRANSFER',
                'payment_id' => null,
                'reference_no' => $ref ?: null,
                'selisih' => $nominalForm,
                'note' => 'Uang belum ditemukan masuk ke payment/mutasi.',
                'payment_nominal' => 0,
            ];
        }

        $sameNominal = $candidates->filter(fn ($r) => abs($this->num($r->nominal) - $nominalForm) == 0.0)->values();

        if ($ref === '' && $sameNominal->count() > 1) {
            return [
                'status' => 'BUTUH_REVIEW',
                'payment_id' => null,
                'reference_no' => null,
                'selisih' => 0,
                'note' => 'Ada lebih dari satu payment dengan nominal sama. Butuh review finance/reference.',
                'payment_nominal' => 0,
            ];
        }

        $best = $this->pickBestCandidate($candidates, $nominalForm, $ref);

        if (!$best) {
            return [
                'status' => 'BELUM_DITRANSFER',
                'payment_id' => null,
                'reference_no' => $ref ?: null,
                'selisih' => $nominalForm,
                'note' => 'Uang belum ditemukan masuk ke payment/mutasi.',
                'payment_nominal' => 0,
            ];
        }

        $paymentNominal = $this->num($best->nominal);
        $diff = $paymentNominal - $nominalForm;

        if ($diff == 0.0) {
            return [
                'status' => 'SUDAH_SESUAI',
                'payment_id' => (int) $best->id,
                'reference_no' => $best->reference_no,
                'selisih' => 0,
                'note' => 'Uang sudah masuk dan nominal sesuai dengan setoran form.',
                'payment_nominal' => $paymentNominal,
            ];
        }

        if ($diff < 0) {
            return [
                'status' => 'UANG_KURANG',
                'payment_id' => (int) $best->id,
                'reference_no' => $best->reference_no,
                'selisih' => abs($diff),
                'note' => 'Uang sudah masuk tetapi nominal kurang dari setoran form.',
                'payment_nominal' => $paymentNominal,
            ];
        }

        return [
            'status' => 'UANG_LEBIH',
            'payment_id' => (int) $best->id,
            'reference_no' => $best->reference_no,
            'selisih' => abs($diff),
            'note' => 'Uang sudah masuk tetapi nominal lebih besar dari setoran form.',
            'payment_nominal' => $paymentNominal,
        ];
    }

    private function findPaymentCandidates(string $tanggalSetor, float $nominal, string $ref, ?int $rowId, ?int $shift)
    {
        $start = Carbon::parse($tanggalSetor)->subDays(1)->format('Y-m-d');
        $end = Carbon::parse($tanggalSetor)->addDays(2)->format('Y-m-d');

        $query = DB::table($this->paymentTable)
            ->where('tipe', 'CR')
            ->whereBetween('tanggal', [$start, $end])
            ->where(function ($q) use ($rowId, $shift) {
                $q->where('is_matched', 0);
                if ($rowId && $shift) {
                    $q->orWhere(function ($x) use ($rowId, $shift) {
                        $x->where('matched_row_id', $rowId)->where('matched_shift', $shift);
                    });
                }
            });

        if ($ref !== '') {
            $query->where(function ($q) use ($ref) {
                $q->where('reference_no', $ref)
                  ->orWhere('description', 'like', '%' . $ref . '%');
            });
        }

        return $query->orderBy('tanggal')->orderBy('id')->get();
    }

    private function pickBestCandidate($candidates, float $nominalForm, string $ref)
    {
        $best = null;
        $bestScore = -1;

        foreach ($candidates as $payment) {
            $paymentNominal = $this->num($payment->nominal);
            $selisih = abs($paymentNominal - $nominalForm);
            $score = 0;

            if ($selisih == 0.0) $score += 70;
            elseif ($selisih <= 1000) $score += 20;

            if ($ref !== '') {
                $refLower = strtolower($ref);
                $payRef = strtolower((string) $payment->reference_no);
                $desc = strtolower((string) $payment->description);
                if ($payRef === $refLower || str_contains($desc, $refLower)) $score += 40;
            }

            if ($score > $bestScore) {
                $best = $payment;
                $bestScore = $score;
            }
        }

        return $best;
    }

    private function applyMatchResult(int $rowId, int $shift, array $result): void
    {
        $status = (string) ($result['status'] ?? 'PENDING');
        $paymentId = $result['payment_id'] ?? null;

        $this->updateRekonResult(
            $rowId,
            $shift,
            $paymentId ? (int) $paymentId : null,
            $status,
            (float) ($result['selisih'] ?? 0),
            (string) ($result['note'] ?? ''),
            $result['reference_no'] ?? null
        );

        if (!empty($paymentId)) {
            DB::table($this->paymentTable)->where('id', $paymentId)->update([
                'is_matched' => in_array($status, ['SUDAH_SESUAI', 'UANG_KURANG', 'UANG_LEBIH'], true) ? 1 : 0,
                'matched_table' => $this->omsetTable,
                'matched_row_id' => $rowId,
                'matched_shift' => $shift,
                'updated_at' => now(),
            ]);
        }
    }

    private function updateRekonResult(int $rowId, int $shift, ?int $paymentId, string $status, float $selisih, string $note, ?string $referenceNo = null): void
    {
        $prefix = $this->prefix($shift);

        $payload = [
            $prefix . '_bca_mutasi_id' => $paymentId,
            $prefix . '_rekon_status' => $status,
            $prefix . '_rekon_selisih' => $selisih,
            $prefix . '_rekon_note' => $note,
            'updated_at' => now(),
        ];

        if ($referenceNo !== null && $referenceNo !== '') {
            $payload[$prefix . '_bca_ref'] = $referenceNo;
        }

        DB::table($this->omsetTable)->where('id', $rowId)->update($payload);
    }

    private function prefix(int $shift): string
    {
        return $shift === 1 ? 's1' : 's2';
    }

    private function normalizeDate(?string $raw): string
    {
        try {
            return Carbon::parse($raw ?: date('Y-m-d'))->format('Y-m-d');
        } catch (\Throwable $e) {
            return date('Y-m-d');
        }
    }

    private function num($value): float
    {
        if ($value === null) return 0.0;
        if (is_numeric($value)) return (float) $value;

        $s = trim((string) $value);
        if ($s === '') return 0.0;

        $s = str_replace([' ', '.'], '', $s);
        $s = str_replace(',', '.', $s);

        return is_numeric($s) ? (float) $s : 0.0;
    }

    private function parseOutletIds($raw): array
    {
        if (is_array($raw)) {
            $items = $raw;
        } else {
            $items = explode(',', (string) $raw);
        }

        return collect($items)
            ->map(fn ($v) => (int) trim((string) $v))
            ->filter(fn ($v) => $v > 0)
            ->unique()
            ->values()
            ->all();
    }
}
