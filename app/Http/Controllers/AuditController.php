<?php

namespace App\Http\Controllers;

use App\Exports\LaporanPencairanExport;
use App\Imports\OutletsKuisionerImport;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Termwind\Question;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AuditController extends Controller
{
    protected int $slotDurationMinutes = 30;
    protected int $lateToleranceMinutes = 30;
        
    // HELPERS ROUTING
    protected $viewPath = 'Investor.Audit.Backoffice.OPERASIONAL';

    private function render($path, $data = [])
    {
        return view($this->viewPath . '.' . $path, $data);
    }
    
    /* =========================
       HELPER BAKU
    ========================= */
    
    private function getScoreMeta(float $score): array
    {
        if ($score > 90) {
            return ['label' => 'Excellent', 'class' => 'success'];
        }
    
        if ($score >= 60) {
            return ['label' => 'Good', 'class' => 'warning'];
        }
    
        return ['label' => 'Bad', 'class' => 'danger'];
    }
    
    private function getMasterQuestions(): array
    {
        $questions = DB::table('tbl_pertanyaan_dcr')
            ->whereNotNull('jam')
            ->select('jam', 'pertanyaan')
            ->orderBy('jam')
            ->orderBy('id')
            ->get();
    
        $questionsByJam = $questions->groupBy(function ($item) {
            return Carbon::parse($item->jam)->format('H:i:s');
        })->map(function ($items) {
            return $items->pluck('pertanyaan')
                ->map(fn ($q) => trim($q))
                ->unique()
                ->values();
        });
    
        $jamList = $questionsByJam->keys()->values();
    
        return [
            'questions' => $questions,
            'questionsByJam' => $questionsByJam,
            'jamList' => $jamList,
        ];
    }
    
    private function getDateList(string $tanggalAwal, string $tanggalAkhir): Collection
    {
        return collect(CarbonPeriod::create($tanggalAwal, $tanggalAkhir))
            ->map(fn ($date) => $date->format('Y-m-d'))
            ->values();
    }
    
    private function getAuditRows(string $from, string $to, $outletIds = null)
    {
        $query = DB::table('audit_harian as ah')
            ->leftJoin('tbl_outlets as o', 'ah.outlet_id', '=', 'o.id')
            ->leftJoin('tbl_user_responden as r', 'ah.id_responden', '=', 'r.id')
            ->leftJoin('tbl_pic as leader', 'ah.leader_pic_id', '=', 'leader.id')
            ->leftJoin('tbl_pic as spv', 'ah.spv_pic_id', '=', 'spv.id')
            ->leftJoin('tbl_pic as tm', 'ah.tm_pic_id', '=', 'tm.id')
            ->select([
                'ah.id',
                'ah.outlet_id',
                'ah.id_responden',
                'ah.tanggal',
                'ah.jam_aktivitas',
                'ah.pertanyaan',
                'ah.jawaban',
                'ah.alasan',
                'ah.foto',
                'ah.foto_perbaikan',
                'ah.created_at',
                'ah.updated_at',

                'ah.leader_pic_id',
                'ah.spv_pic_id',
                'ah.tm_pic_id',

                'o.nama_outlet',
                'r.nama_lengkap as nama_responden',
                'leader.nama_lengkap as leader_nama',
                'spv.nama_lengkap as spv_nama',
                'tm.nama_lengkap as tm_nama',
            ])
            ->whereBetween('ah.tanggal', [$from, $to]);

        if (is_array($outletIds) && !empty($outletIds)) {
            $query->whereIn('ah.outlet_id', array_map('intval', $outletIds));
        } elseif (!is_array($outletIds) && !empty($outletIds)) {
            $query->where('ah.outlet_id', (int) $outletIds);
        }

        return $query
            ->orderBy('ah.tanggal')
            ->orderBy('ah.jam_aktivitas')
            ->orderBy('ah.created_at')
            ->get();
    }
    
    private function groupAuditByOutletDateJam(Collection $auditRows): Collection
    {
        return $auditRows->groupBy(function ($row) {
            return implode('|', [
                $row->outlet_id,
                Carbon::parse($row->tanggal)->format('Y-m-d'),
                Carbon::parse($row->jam_aktivitas)->format('H:i:s'),
            ]);
        });
    }
    
    private function calculateSlotResult(
        Collection $slotRows,
        Collection $expectedQuestions,
        string $tanggal,
        string $jam
    ): array {
        $maxQuestions = $expectedQuestions->count();
    
        if ($maxQuestions === 0) {
            return [
                'point' => 0,
                'max' => 0,
                'status' => 'danger',
                'status_label' => 'No master',
                'jam_input' => '-',
            ];
        }
    
        if ($slotRows->isEmpty()) {
            return [
                'point' => 0,
                'max' => $maxQuestions,
                'status' => 'danger',
                'status_label' => 'Belum input',
                'jam_input' => '-',
            ];
        }
    
        // Ambil input paling awal dalam slot
        $firstInput = $slotRows
            ->filter(fn ($row) => !empty($row->created_at))
            ->sortBy('created_at')
            ->first();
    
        if (!$firstInput) {
            return [
                'point' => 0,
                'max' => $maxQuestions,
                'status' => 'danger',
                'status_label' => 'Belum input',
                'jam_input' => '-',
            ];
        }
    
        // created_at dari DB diasumsikan UTC, lalu dikonversi ke Asia/Jakarta
        $submittedAt = Carbon::parse($firstInput->created_at, 'UTC')
            ->setTimezone('Asia/Jakarta');
    
        $jamInput = $submittedAt->format('H:i:s');
    
        $slotStart = Carbon::parse($tanggal . ' ' . $jam, 'Asia/Jakarta');
        $lateMinutes = $slotStart->diffInMinutes($submittedAt, false);

        if ($lateMinutes >= 0 && $lateMinutes <= 29) {
            $timeWeight = 1;
            $timeStatus = 'success';
            $timeStatusLabel = 'Ontime';
        } elseif ($lateMinutes > 29) {
            $timeWeight = 0.5;
            $timeStatus = 'warning';
            $timeStatusLabel = 'Late';
        } else {
            $timeWeight = 1;
            $timeStatus = 'success';
            $timeStatusLabel = 'Ontime';
        }
    
        $actualByQuestion = $slotRows
            ->groupBy(fn ($item) => trim((string) $item->pertanyaan))
            ->map(fn ($items) => $items->sortBy('created_at')->first());

        $point = 0;

        foreach ($expectedQuestions as $question) {
            $row = $actualByQuestion->get(trim((string) $question));

            if (!$row) {
                continue;
            }

            $answerWeight = trim((string) $row->jawaban) === 'Ya' ? 1 : 0;
            $point += ($answerWeight * $timeWeight);
        }

        /*
        |--------------------------------------------------------------------------
        | PENTING: point tidak boleh melebihi max
        |--------------------------------------------------------------------------
        */
        $point = min($point, $maxQuestions);
    
        $slotPercent = $maxQuestions > 0
            ? round(($point / $maxQuestions) * 100, 2)
            : 0;
    
        if ($slotPercent > 90) {
            $status = 'success';
            $statusLabel = 'Excellent';
        } elseif ($slotPercent >= 60) {
            $status = 'warning';
            $statusLabel = 'Good';
        } else {
            $status = 'danger';
            $statusLabel = 'Bad';
        }

        $point = min($point, $maxQuestions);
    
        return [
            'point' => $point,
            'max' => $maxQuestions,
            'status' => $timeStatus,
            'status_label' => $timeStatusLabel,
            'jam_input' => $jamInput,
        ];
    }
    // END HELPERS
    
    private function normalizeOutletName(?string $name): string
    {
        $name = strtoupper(trim((string) $name));
        $name = preg_replace('/\s+/', ' ', $name);
        $name = preg_replace('/[^A-Z0-9 ]/', '', $name);
        $name = preg_replace('/\s+/', ' ', $name);

        return trim($name) ?: '';
    }
    
    private function normalizeOutletGroupKey(?string $name): string
    {
        $name = strtoupper(trim((string) $name));
        $name = preg_replace('/[^A-Z0-9]+/', ' ', $name);
        $name = preg_replace('/\s+/', ' ', $name);

        return trim($name);
    }

    private function getAuditOutletGroups()
    {
        $rows = DB::table('tbl_outlets')
            ->select('id', 'nama_outlet')
            ->whereNotNull('nama_outlet')
            ->where('nama_outlet', '<>', '')
            ->orderBy('nama_outlet')
            ->orderBy('id')
            ->get();

        return $rows
            ->groupBy(function ($row) {
                return $this->normalizeOutletName($row->nama_outlet);
            })
            ->map(function ($group) {
                $primary = $group->sortBy('nama_outlet')->first();

                $ids = $group
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->sort()
                    ->values()
                    ->all();

                return (object) [
                    'id' => (int) $primary->id,
                    'nama_outlet' => $primary->nama_outlet . ' [' . implode(', ', $ids) . ']',
                    'nama_outlet_asli' => $primary->nama_outlet,
                    'alias_ids' => $ids,
                ];
            })
            ->sortBy('nama_outlet_asli')
            ->values();
    }
        
    public function auditDashboard(Request $request)
    {
        $authUser = \Illuminate\Support\Facades\Auth::user();

        $userId   = $authUser->id ?? session('responden_id');
        $userNama = $authUser->name ?? session('responden_nama');
        $username = $authUser->email ?? session('responden_username');

        $today = now('Asia/Jakarta')->toDateString();
        $now   = now('Asia/Jakarta');

        $slotDurationMinutes = 30;
        $lateToleranceMinutes = 30;

        $allQuestions = \Illuminate\Support\Facades\DB::table('tbl_pertanyaan_dcr')
            ->whereNotNull('jam')
            ->orderBy('jam', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $questionsByJam = $allQuestions->groupBy(function ($item) {
            return \Carbon\Carbon::parse($item->jam)->format('H:i:s');
        });

        $jamAuditList = $allQuestions
            ->pluck('jam')
            ->map(function ($jam) {
                return \Carbon\Carbon::parse($jam)->format('H:i:s');
            })
            ->unique()
            ->values();

        $filledSlotsToday = collect();
        $alreadyFilledToday = false;
        $alreadyFilledCurrentSlot = false;

        if ($userId) {
            $filledSlotsToday = \Illuminate\Support\Facades\DB::table('audit_harian')
                ->where('id_responden', $userId)
                ->whereDate('tanggal', $today)
                ->groupBy('jam_aktivitas')
                ->pluck('jam_aktivitas')
                ->map(function ($jam) {
                    return \Carbon\Carbon::parse($jam)->format('H:i:s');
                })
                ->values();

            $alreadyFilledToday = $filledSlotsToday->isNotEmpty();
        }

        $slotTimeline = $jamAuditList->map(function ($jam) use (
            $today,
            $now,
            $filledSlotsToday,
            $questionsByJam,
            $lateToleranceMinutes,
            $slotDurationMinutes
        ) {
            $start = \Carbon\Carbon::parse($today . ' ' . $jam, 'Asia/Jakarta');
            $end = $start->copy()->addMinutes($slotDurationMinutes)->subSecond();
            $lateToleranceEnd = $end->copy()->addMinutes($lateToleranceMinutes);

            $status = 'waiting';
            $isAccessible = false;
            $isLateTolerance = false;

            if ($filledSlotsToday->contains($jam)) {
                $status = 'done';
            } elseif ($now->between($start, $end)) {
                $status = 'active';
                $isAccessible = true;
            } elseif ($now->gt($end) && $now->lte($lateToleranceEnd)) {
                $status = 'late_tolerance';
                $isAccessible = true;
                $isLateTolerance = true;
            } elseif ($now->gt($lateToleranceEnd)) {
                $status = 'locked';
            }

            return (object) [
                'jam'                => $jam,
                'label'              => \Carbon\Carbon::parse($jam)->format('H:i'),
                'start'              => $start,
                'end'                => $end,
                'late_tolerance_end' => $lateToleranceEnd,
                'status'             => $status,
                'is_accessible'      => $isAccessible,
                'is_late_tolerance'  => $isLateTolerance,
                'items'              => $questionsByJam->get($jam, collect()),
            ];
        });

        $currentOpenSlot = $slotTimeline->first(function ($slot) {
            return in_array($slot->status, ['active', 'late_tolerance'], true);
        });

        $viewJam = $request->view_jam
            ? \Carbon\Carbon::parse($request->view_jam)->format('H:i:s')
            : ($currentOpenSlot->jam ?? ($jamAuditList->first() ?? null));

        $questions = collect();
        $selectedSlot = null;
        $isSelectedSlotActive = false;
        $isSelectedSlotLateTolerance = false;
        $isSelectedSlotAccessible = false;
        $isSelectedSlotLocked = false;
        $alreadyFilledSelectedSlot = false;
        $slotLewat = false;
        $slotAktif = null;
        $selectedSlotWarning = null;

        if ($viewJam) {
            $questions = $questionsByJam->get($viewJam, collect());

            $selectedStart = \Carbon\Carbon::parse($today . ' ' . $viewJam, 'Asia/Jakarta');
            $selectedEnd = $selectedStart->copy()->addMinutes($slotDurationMinutes)->subSecond();
            $selectedLateToleranceEnd = $selectedEnd->copy()->addMinutes($lateToleranceMinutes);

            $isSelectedSlotActive = $now->between($selectedStart, $selectedEnd);
            $isSelectedSlotLateTolerance = $now->gt($selectedEnd) && $now->lte($selectedLateToleranceEnd);
            $isSelectedSlotAccessible = $isSelectedSlotActive || $isSelectedSlotLateTolerance;
            $isSelectedSlotLocked = $now->gt($selectedLateToleranceEnd);

            $selectedSlot = [
                'jam_aktivitas'      => $viewJam,
                'start'              => $selectedStart,
                'end'                => $selectedEnd,
                'late_tolerance_end' => $selectedLateToleranceEnd,
            ];

            if ($isSelectedSlotLateTolerance) {
                $selectedSlotWarning = 'Slot ini sudah melewati jam normal, tetapi masih bisa diisi dalam toleransi 30 menit.';
            }

            if ($isSelectedSlotLocked) {
                $selectedSlotWarning = 'Slot ini sudah melewati batas toleransi 30 menit, sehingga tidak bisa diakses lagi.';
                $slotLewat = true;
            }

            if ($userId) {
                $alreadyFilledSelectedSlot = \Illuminate\Support\Facades\DB::table('audit_harian')
                    ->where('id_responden', $userId)
                    ->whereDate('tanggal', $today)
                    ->where('jam_aktivitas', $viewJam)
                    ->exists();
            }
        }

        if ($currentOpenSlot) {
            $slotAktif = [
                'jam_aktivitas'      => $currentOpenSlot->jam,
                'start'              => $currentOpenSlot->start,
                'end'                => $currentOpenSlot->end,
                'late_tolerance_end' => $currentOpenSlot->late_tolerance_end,
                'status'             => $currentOpenSlot->status,
            ];
        }

        if ($slotAktif && $userId) {
            $alreadyFilledCurrentSlot = \Illuminate\Support\Facades\DB::table('audit_harian')
                ->where('id_responden', $userId)
                ->whereDate('tanggal', $today)
                ->where('jam_aktivitas', $slotAktif['jam_aktivitas'])
                ->exists();
        }

        $selectedIndex = collect($jamAuditList)->search($viewJam);

        $prevJam = ($selectedIndex !== false && $selectedIndex > 0)
            ? $jamAuditList[$selectedIndex - 1]
            : null;

        $nextJam = ($selectedIndex !== false && isset($jamAuditList[$selectedIndex + 1]))
            ? $jamAuditList[$selectedIndex + 1]
            : null;

        $lockedOutletId = null;
        $outlets = $this->getAuditOutletGroups();

        $selectedOutletId = old('outlet_id');
        if (empty($selectedOutletId)) {
            $selectedOutletId = $outlets->first()->id ?? null;
        }

        $leaderPics = \Illuminate\Support\Facades\DB::table('tbl_pic_mapping as pm')
            ->join('tbl_pic as p', 'pm.pic_id', '=', 'p.id')
            ->select('pm.pic_id', 'pm.level_pic', 'p.nama_lengkap')
            ->where('pm.level_pic', 'LEADER')
            ->groupBy('pm.pic_id', 'pm.level_pic', 'p.nama_lengkap')
            ->orderBy('p.nama_lengkap')
            ->get();

        $spvPics = \Illuminate\Support\Facades\DB::table('tbl_pic_mapping as pm')
            ->join('tbl_pic as p', 'pm.pic_id', '=', 'p.id')
            ->select('pm.pic_id', 'pm.level_pic', 'p.nama_lengkap')
            ->where('pm.level_pic', 'SPV')
            ->groupBy('pm.pic_id', 'pm.level_pic', 'p.nama_lengkap')
            ->orderBy('p.nama_lengkap')
            ->get();

        $tmPics = \Illuminate\Support\Facades\DB::table('tbl_pic_mapping as pm')
            ->join('tbl_pic as p', 'pm.pic_id', '=', 'p.id')
            ->select('pm.pic_id', 'pm.level_pic', 'p.nama_lengkap')
            ->where('pm.level_pic', 'TM')
            ->groupBy('pm.pic_id', 'pm.level_pic', 'p.nama_lengkap')
            ->orderBy('p.nama_lengkap')
            ->get();

        $filterOutlet = $request->outlet_id;
        $filterJam    = $request->jam;
        $filterFrom   = $request->from ?: now('Asia/Jakarta')->startOfMonth()->toDateString();
        $filterTo     = $request->to ?: now('Asia/Jakarta')->toDateString();

        $baseAuditQuery = \Illuminate\Support\Facades\DB::table('audit_harian as ah')
            ->leftJoin('tbl_outlets as o', 'ah.outlet_id', '=', 'o.id')
            ->leftJoin('tbl_user_responden as r', 'ah.id_responden', '=', 'r.id')
            ->whereBetween('ah.tanggal', [$filterFrom, $filterTo]);

        if (!empty($filterOutlet)) {
            $filterOutlet = (int) $filterOutlet;
            $filterAliasIds = [$filterOutlet];

            foreach ($outlets as $outletItem) {
                if ((int) $outletItem->id === $filterOutlet) {
                    $filterAliasIds = collect($outletItem->alias_ids ?? [$filterOutlet])
                        ->map(fn ($id) => (int) $id)
                        ->unique()
                        ->values()
                        ->all();
                    break;
                }
            }

            $baseAuditQuery->whereIn('ah.outlet_id', $filterAliasIds);
        }

        if (!empty($filterJam)) {
            $baseAuditQuery->where('ah.jam_aktivitas', \Carbon\Carbon::parse($filterJam)->format('H:i:s'));
        }

        $totalJawaban = (clone $baseAuditQuery)->count();
        $totalOutlet = \Illuminate\Support\Facades\DB::table('tbl_outlets')->count();
        $totalResponden = \Illuminate\Support\Facades\DB::table('tbl_user_responden')->count();

        $totalPoin = 0;
        $saldo = 0;
        $progress = 0;

        if ($userId) {
            $totalPoin = \Illuminate\Support\Facades\DB::table('tbl_poin_transaksi')
                ->where('id_user_responden', $userId)
                ->sum('jumlah_poin');

            $saldo = $totalPoin * 5000;
            $progress = min(100, ($totalPoin / 120) * 100);
        }

        $totalUang = $saldo;

        $perTanggal = (clone $baseAuditQuery)
            ->selectRaw("
                ah.tanggal,
                COUNT(*) as total_jawaban,
                SUM(CASE WHEN ah.jawaban = 'Ya' THEN 1 ELSE 0 END) as total_ya,
                SUM(CASE WHEN ah.jawaban = 'Tidak' THEN 1 ELSE 0 END) as total_tidak
            ")
            ->groupBy('ah.tanggal')
            ->orderBy('ah.tanggal')
            ->get();

        $respondenPerTanggal = \Illuminate\Support\Facades\DB::table('tbl_user_responden')
            ->selectRaw('DATE(created_at) as tanggal, COUNT(*) as total_registrasi')
            ->whereBetween(\Illuminate\Support\Facades\DB::raw('DATE(created_at)'), [$filterFrom, $filterTo])
            ->groupBy(\Illuminate\Support\Facades\DB::raw('DATE(created_at)'))
            ->orderBy('tanggal')
            ->get();

        $perOutlet = (clone $baseAuditQuery)
            ->selectRaw("
                ah.outlet_id,
                o.nama_outlet,
                COUNT(*) as total_jawaban,
                SUM(CASE WHEN ah.jawaban = 'Ya' THEN 1 ELSE 0 END) as total_ya,
                SUM(CASE WHEN ah.jawaban = 'Tidak' THEN 1 ELSE 0 END) as total_tidak
            ")
            ->groupBy('ah.outlet_id', 'o.nama_outlet')
            ->orderByDesc('total_jawaban')
            ->get();

        $questionStats = (clone $baseAuditQuery)
            ->selectRaw("
                ah.pertanyaan,
                COUNT(*) as total_jawaban,
                SUM(CASE WHEN ah.jawaban = 'Ya' THEN 1 ELSE 0 END) as total_ya,
                SUM(CASE WHEN ah.jawaban = 'Tidak' THEN 1 ELSE 0 END) as total_tidak
            ")
            ->groupBy('ah.pertanyaan')
            ->orderBy('ah.pertanyaan')
            ->get();

        $recentResponses = (clone $baseAuditQuery)
            ->select(
                'ah.id',
                'ah.created_at',
                'ah.jam_aktivitas',
                'o.nama_outlet',
                'r.nama_lengkap',
                'ah.id_responden as user_id',
                \Illuminate\Support\Facades\DB::raw("CASE WHEN COALESCE(ah.foto_perbaikan, '') <> '' THEN 1 ELSE 0 END as status_perbaikan")
            )
            ->where('ah.jawaban', 'Tidak')
            ->orderByDesc('ah.created_at')
            ->limit(20)
            ->get();

        return view('Investor.Audit.dashboard', compact(
            'outlets',
            'questions',
            'allQuestions',
            'questionsByJam',
            'leaderPics',
            'spvPics',
            'tmPics',
            'totalPoin',
            'saldo',
            'progress',
            'userNama',
            'username',
            'slotAktif',
            'selectedSlot',
            'isSelectedSlotActive',
            'isSelectedSlotLateTolerance',
            'isSelectedSlotAccessible',
            'isSelectedSlotLocked',
            'selectedSlotWarning',
            'alreadyFilledSelectedSlot',
            'alreadyFilledCurrentSlot',
            'alreadyFilledToday',
            'slotLewat',
            'slotTimeline',
            'filledSlotsToday',
            'totalJawaban',
            'totalOutlet',
            'totalResponden',
            'totalUang',
            'perTanggal',
            'respondenPerTanggal',
            'perOutlet',
            'questionStats',
            'recentResponses',
            'jamAuditList',
            'filterOutlet',
            'filterJam',
            'filterFrom',
            'filterTo',
            'viewJam',
            'prevJam',
            'nextJam',
            'lockedOutletId',
            'selectedOutletId',
            'lateToleranceMinutes'
        ));
    }
        
    public function dashboardHarian(Request $request)
    {
        $tanggal = $request->filled('tanggal')
            ? Carbon::parse($request->tanggal)->format('Y-m-d')
            : now('Asia/Jakarta')->format('Y-m-d');
    
        $filterOutlet = $request->filled('outlet_id')
            ? (int) $request->outlet_id
            : null;
    
        // Pakai grouped outlets, bukan tbl_outlets mentah
        $outlets = $this->getAuditOutletGroups()
            ->map(function ($item) {
                $item->id = (int) $item->id;
                $item->alias_ids = collect($item->alias_ids ?? [$item->id])
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values()
                    ->all();
    
                return $item;
            })
            ->sortBy('nama_outlet')
            ->values();
    
        $master = $this->getMasterQuestions();
    
        $questionsByJam = collect($master['questionsByJam'])->mapWithKeys(function ($items, $jam) {
            return [
                Carbon::parse($jam)->format('H:i:s') => collect($items),
            ];
        });
    
        $jamList = collect($master['jamList'])
            ->map(fn ($jam) => Carbon::parse($jam)->format('H:i:s'))
            ->unique()
            ->values();
    
        // Tentukan outlet source
        $outletSource = $filterOutlet
            ? $outlets->where('id', $filterOutlet)->values()
            : $outlets->values();
    
        // Ambil semua alias ID yang perlu dibaca
        $selectedAliasIds = $outletSource
            ->flatMap(fn ($outlet) => $outlet->alias_ids)
            ->unique()
            ->values()
            ->all();
    
        $auditRows = $this->getAuditRows($tanggal, $tanggal, $selectedAliasIds);
    
        // Group audit per jam saja dulu, nanti outlet dicocokkan via alias_ids
        $auditGroupedByJam = $auditRows->groupBy(function ($row) {
            return Carbon::parse($row->jam_aktivitas)->format('H:i:s');
        });
    
        $dashboardData = [];
    
        foreach ($outletSource as $outlet) {
            $rowsPerOutlet = [];
            $totalPoint = 0;
            $totalMax = 0;
    
            $leader = '-';
            $spv = '-';
            $am = '-';
    
            $aliasIds = collect($outlet->alias_ids)->map(fn ($id) => (int) $id)->all();
    
            foreach ($jamList as $jam) {
                $expectedQuestions = $questionsByJam->get($jam, collect());
    
                $slotRows = collect($auditGroupedByJam->get($jam, collect()))
                    ->filter(function ($row) use ($aliasIds) {
                        return in_array((int) $row->outlet_id, $aliasIds, true);
                    })
                    ->values();
    
                $result = $this->calculateSlotResult(
                    $slotRows,
                    $expectedQuestions,
                    $tanggal,
                    $jam
                );
    
                $totalPoint += $result['point'] ?? 0;
                $totalMax += $result['max'] ?? 0;
    
                $first = $slotRows->sortBy('created_at')->first();
    
                if ($first) {
                    if ($leader === '-' && !empty($first->leader_nama)) {
                        $leader = $first->leader_nama;
                    }
    
                    if ($spv === '-' && !empty($first->spv_nama)) {
                        $spv = $first->spv_nama;
                    }
    
                    if ($am === '-' && !empty($first->am_nama)) {
                        $am = $first->am_nama;
                    }
                }
    
                $rowsPerOutlet[] = [
                    'jam' => 'Jam ' . Carbon::parse($jam)->format('H:i'),
                    'pertanyaan' => $expectedQuestions->implode("\n"),
                    'status' => $result['status'] ?? 'danger',
                    'status_label' => $result['status_label'] ?? 'Belum diisi',
                    'jam_input' => $result['jam_input'] ?? '-',
                ];
            }
    
            $score = $totalMax > 0
                ? round(($totalPoint / $totalMax) * 100, 2)
                : 0;
    
            $meta = $this->getScoreMeta($score);
    
            $dashboardData[] = [
                'tanggal' => Carbon::parse($tanggal)->format('d/m/Y'),
                'bulan' => Carbon::parse($tanggal)->translatedFormat('F'),
                'tahun' => Carbon::parse($tanggal)->format('Y'),
                'outlet_id' => $outlet->id,
                'outlet' => $outlet->nama_outlet,
                'leader' => $leader,
                'spv' => $spv,
                'am' => $am,
                'score' => $score,
                'kategori' => $meta['label'],
                'badge_class' => $meta['class'],
                'rows' => $rowsPerOutlet,
            ];
        }
    
        return $this->render('Dashboard.harian', [
            'tanggal' => $tanggal,
            'outlets' => $outlets,
            'filterOutlet' => $filterOutlet,
            'dashboardData' => collect($dashboardData),
        ]);
    }

    private function getActiveAuditSlotFromDb()
    {
        $now = now('Asia/Jakarta');
        $today = $now->toDateString();
        $slotDurationMinutes = 30;
    
        $jamList = DB::table('tbl_pertanyaan_dcr')
            ->whereNotNull('jam')
            ->groupBy('jam')
            ->orderBy('jam', 'asc')
            ->pluck('jam')
            ->map(function ($jam) {
                return Carbon::parse($jam)->format('H:i:s');
            })
            ->values()
            ->toArray();
    
        if (empty($jamList)) {
            return null;
        }
    
        foreach ($jamList as $jam) {
            $start = Carbon::parse($today . ' ' . $jam, 'Asia/Jakarta');
            $end = $start->copy()->addMinutes($slotDurationMinutes)->subSecond();
    
            if ($now->between($start, $end)) {
                return [
                    'jam_aktivitas' => $jam,
                    'start' => $start,
                    'end' => $end,
                ];
            }
        }
    
        return null;
    }
    
    private function getAccessibleAuditSlotFromDb(int $lateToleranceMinutes = 60): ?array
    {
        $now = now('Asia/Jakarta');
        $today = $now->toDateString();
        $slotDurationMinutes = 30;
    
        $jamList = DB::table('tbl_pertanyaan_dcr')
            ->whereNotNull('jam')
            ->groupBy('jam')
            ->orderBy('jam', 'asc')
            ->pluck('jam')
            ->map(function ($jam) {
                return Carbon::parse($jam)->format('H:i:s');
            })
            ->values()
            ->toArray();
    
        if (empty($jamList)) {
            return null;
        }
    
        foreach ($jamList as $jam) {
            $start = Carbon::parse($today . ' ' . $jam, 'Asia/Jakarta');
            $end = $start->copy()->addMinutes($slotDurationMinutes)->subSecond();
            $lateToleranceEnd = $end->copy()->addMinutes($lateToleranceMinutes);
    
            if ($now->between($start, $end)) {
                return [
                    'jam_aktivitas'      => $jam,
                    'start'              => $start,
                    'end'                => $end,
                    'late_tolerance_end' => $lateToleranceEnd,
                    'status'             => 'active',
                    'is_accessible'      => true,
                ];
            }
    
            if ($now->gt($end) && $now->lte($lateToleranceEnd)) {
                return [
                    'jam_aktivitas'      => $jam,
                    'start'              => $start,
                    'end'                => $end,
                    'late_tolerance_end' => $lateToleranceEnd,
                    'status'             => 'late_tolerance',
                    'is_accessible'      => true,
                ];
            }
        }
    
        return null;
    }
    
    private function publicHtmlPath(string $rel = ''): string
    {
        return base_path('../public_html/' . ltrim($rel, '/'));
    }
    
    private function storeAuditPhotoToPublicFromBase64(string $base64Image, string $folder = 'audit/foto_responden'): string
    {
        if (!preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
            throw new \Exception('Format foto tidak valid.');
        }
    
        $image = substr($base64Image, strpos($base64Image, ',') + 1);
        $image = str_replace(' ', '+', $image);
        $imageData = base64_decode($image);
    
        if ($imageData === false) {
            throw new \Exception('Foto gagal diproses.');
        }
    
        $extension = strtolower($type[1]);
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
            $extension = 'jpg';
        }
    
        $filename = 'audit_' . time() . '_' . Str::random(8) . '.' . $extension;
        $path = trim($folder, '/') . '/' . $filename;
    
        $saved = Storage::disk('public')->put($path, $imageData);
    
        if (!$saved) {
            throw new \Exception('Gagal menyimpan foto ke storage public.');
        }
    
        return $path;
    }
    
    private function storeAuditPhotoToPublic($file, string $folder = 'audit/foto_responden'): string
    {
        if (!$file || !$file->isValid()) {
            throw new \Exception('File foto tidak valid.');
        }
    
        $ext = strtolower($file->getClientOriginalExtension());
    
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $ext = 'jpg';
        }
    
        $filename = 'audit_' . time() . '_' . Str::random(8) . '.' . $ext;
        $folder = trim($folder, '/');
    
        $storedPath = Storage::disk('public')->putFileAs($folder, $file, $filename);
    
        if (!$storedPath) {
            throw new \Exception('Gagal upload file foto ke storage public.');
        }
    
        return $storedPath;
    }
    
    private function deletePublicAuditPhotoIfExists(?string $dbPath): void
    {
        if (!$dbPath) {
            return;
        }
    
        $p = trim((string) $dbPath);
        $p = preg_replace('#^(public/|storage/)#', '', $p);
        $p = ltrim($p, '/');
    
        if (Storage::disk('public')->exists($p)) {
            Storage::disk('public')->delete($p);
        }
    }
    
    private function getManualAuditSlotOverride(string $jam, string $tanggal): ?array
    {
        return null;
    }

    public function storeInternalAudit(Request $request)
    {
        $authUser = auth()->user();
    
        $userId       = $authUser->id ?? session('responden_id');
        $userRole     = $authUser->role ?? null;
        $userOutletId = $authUser->outlet_id ?? null;
    
        if (!$userId) {
            return back()->with('error', 'Silakan login terlebih dahulu.');
        }
    
        $request->validate([
            'outlet_id'     => 'nullable|exists:tbl_outlets,id',
            // 'nama_pic' => 'required|regex:/^[a-zA-Z\s]+$/',
            'nama_pic' => ['required', 'string', 'max:100'],
            'leader_pic_id' => 'required|exists:tbl_pic,id',
            'spv_pic_id'    => 'required|exists:tbl_pic,id',
            'tm_pic_id'     => 'required|exists:tbl_pic,id',
            'jam_aktivitas' => 'required',
        ]);
    
        $today = now('Asia/Jakarta')->toDateString();
        $now   = now('Asia/Jakarta');
    
        $namaPic = trim($request->nama_pic);
    
        if ($userRole === 'crew') {
            $outletId = $userOutletId;
        } else {
            $outletId = $request->outlet_id;
        }
    
        $jamRequest = Carbon::parse($request->jam_aktivitas)->format('H:i:s');
    
        $jamList = DB::table('tbl_pertanyaan_dcr')
            ->whereNotNull('jam')
            ->groupBy('jam')
            ->orderBy('jam')
            ->pluck('jam')
            ->map(fn($j) => Carbon::parse($j)->format('H:i:s'))
            ->values()
            ->toArray();
    
        if (!in_array($jamRequest, $jamList, true)) {
            return back()->with('error', 'Jam aktivitas tidak valid.');
        }
    
        $slotDurationMinutes = 30;
        $lateToleranceMinutes = 30;
    
        $start = Carbon::parse("$today $jamRequest", 'Asia/Jakarta');
        $end = $start->copy()->addMinutes($slotDurationMinutes)->subSecond();
        $lateEnd = $end->copy()->addMinutes($lateToleranceMinutes);
    
        $isActive = $now->between($start, $end);
        $isLate   = $now->gt($end) && $now->lte($lateEnd);
        $isAccessible = $isActive || $isLate;
    
        $manualOverride = $this->getManualAuditSlotOverride($jamRequest, $today);
    
        if ($manualOverride) {
            $isAccessible = true;
        }
    
        if (!$isAccessible) {
            return back()->with('error', 'Slot sudah terkunci.');
        }
    
        $exists = DB::table('audit_harian')
            ->where('id_responden', $userId)
            ->whereDate('tanggal', $today)
            ->where('jam_aktivitas', $jamRequest)
            ->exists();
    
        if ($exists) {
            return back()->with('error', 'Slot sudah diisi.');
        }
    
        $questions = DB::table('tbl_pertanyaan_dcr')
            ->where('jam', $jamRequest)
            ->orderBy('id')
            ->get();
    
        DB::beginTransaction();
    
        try {
            foreach ($questions as $q) {
                $jawaban = $request->input("pertanyaan_$q->id");
    
                if (!in_array($jawaban, ['Ya', 'Tidak'], true)) {
                    throw new \Exception('Semua harus dijawab');
                }
    
                $foto = null;
                $alasan = null;
    
                if ($jawaban === 'Ya') {
                    $base64List = $request->input("pertanyaan_{$q->id}_foto_base64", []);
    
                    if (!is_array($base64List) || count($base64List) === 0) {
                        throw new \Exception("Foto wajib untuk pertanyaan: {$q->pertanyaan}");
                    }
    
                    $savedPhotos = [];
    
                    foreach ($base64List as $base64) {
                        if (!empty($base64)) {
                            $savedPhotos[] = $this->storeAuditPhotoToPublicFromBase64(
                                $base64,
                                'audit/foto_responden'
                            );
                        }
                    }
    
                    if (count($savedPhotos) === 0) {
                        throw new \Exception("Foto wajib untuk pertanyaan: {$q->pertanyaan}");
                    }
    
                    $foto = json_encode($savedPhotos);
                }
    
                if ($jawaban === 'Tidak') {
                    $alasan = trim((string) $request->input("pertanyaan_{$q->id}_alasan"));
    
                    if ($alasan === '') {
                        throw new \Exception("Alasan wajib untuk pertanyaan: {$q->pertanyaan}");
                    }
                }
    
                DB::table('audit_harian')->insert([
                    'outlet_id'     => $outletId,
                    'id_responden'  => $userId,
                    'nama_pic'      => $namaPic,
                    'leader_pic_id' => $request->leader_pic_id,
                    'spv_pic_id'    => $request->spv_pic_id,
                    'tm_pic_id'     => $request->tm_pic_id,
                    'tanggal'       => $today,
                    'jam_aktivitas' => $jamRequest,
                    'pertanyaan'    => $q->pertanyaan,
                    'jawaban'       => $jawaban,
                    'alasan'        => $alasan,
                    'foto'          => $foto,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }
    
            DB::commit();
    
            return back()->with('success', 'Audit berhasil disimpan');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function updateInternalAudit(Request $request, $id)
    {
        $request->validate([
            'outlet_id'     => 'required|exists:tbl_outlets,id',
            'jam_aktivitas' => 'required',
            'jawaban'       => 'required|in:Ya,Tidak',
            'alasan'        => 'nullable|string',
            'foto'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);
    
        $data = DB::table('audit_harian')->where('id', $id)->first();
    
        if (!$data) {
            return redirect()->back()->with('error', 'Data audit tidak ditemukan.');
        }
    
        $fotoPath = $data->foto;
    
        if ($request->jawaban === 'Ya' && $request->hasFile('foto')) {
            $this->deletePublicAuditPhotoIfExists($fotoPath);
    
            $fotoPath = $this->storeAuditPhotoToPublic(
                $request->file('foto'),
                'audit/foto_responden'
            );
        }
    
        if ($request->jawaban === 'Tidak') {
            $this->deletePublicAuditPhotoIfExists($fotoPath);
            $fotoPath = null;
        }
    
        DB::table('audit_harian')
            ->where('id', $id)
            ->update([
                'outlet_id'     => $request->outlet_id,
                'jam_aktivitas' => Carbon::parse($request->jam_aktivitas)->format('H:i:s'),
                'jawaban'       => $request->jawaban,
                'alasan'        => $request->jawaban === 'Tidak'
                    ? trim((string) $request->alasan)
                    : null,
                'foto'          => $request->jawaban === 'Ya' ? $fotoPath : null,
                'updated_at'    => now('Asia/Jakarta'),
            ]);
    
        return redirect()->back()->with('success', 'Audit harian berhasil diupdate.');
    }

    public function validateInternalAudit(Request $request)
    {
        $request->validate([
            'jam_aktivitas' => 'required',
        ]);
    
        $userId = session('responden_id');
    
        if (!$userId) {
            return response()->json([
                'status'  => false,
                'exists'  => false,
                'message' => 'Session user tidak ditemukan.'
            ], 401);
        }
    
        $jam = Carbon::parse($request->jam_aktivitas)->format('H:i:s');
    
        $exists = DB::table('audit_harian')
            ->where('id_responden', $userId)
            ->whereDate('tanggal', now('Asia/Jakarta')->toDateString())
            ->where('jam_aktivitas', $jam)
            ->exists();
    
        return response()->json([
            'status'  => true,
            'exists'  => $exists,
            'message' => $exists
                ? 'Slot ini sudah pernah diisi.'
                : 'Slot ini masih bisa diisi.'
        ]);
    }

    public function getNegativeResponses($id)
    {
        $rows = DB::table('audit_harian')
            ->where('id', $id)
            ->orWhere(function ($q) use ($id) {
                $parent = DB::table('audit_harian')->where('id', $id)->first();
                if ($parent) {
                    $q->where('id_responden', $parent->id_responden)
                        ->whereDate('tanggal', $parent->tanggal)
                        ->where('jam_aktivitas', $parent->jam_aktivitas)
                        ->where('outlet_id', $parent->outlet_id)
                        ->where('jawaban', 'Tidak');
                } else {
                    $q->whereRaw('1 = 0');
                }
            })
            ->orderBy('id')
            ->get();

        $data = $rows
            ->where('jawaban', 'Tidak')
            ->map(function ($item) {
                return [
                    'detail_id' => $item->id,
                    'pertanyaan' => $item->pertanyaan,
                    'jawaban_text' => $item->jawaban,
                    'foto_jawaban' => $item->foto,
                    'foto_perbaikan_jawaban' => $item->foto_perbaikan ?? null,
                ];
            })
            ->values();

        return response()->json([
            'status' => 'success',
            'message' => $data->isEmpty() ? 'Tidak ada jawaban "Tidak"' : 'OK',
            'data' => $data,
        ]);
    }

    public function uploadBuktiPerbaikan(Request $request)
    {
        $request->validate([
            'detail_id'          => 'required|exists:audit_harian,id',
            'respon_id'          => 'required',
            'foto_bukti_outlet'  => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ]);
    
        $row = DB::table('audit_harian')->where('id', $request->detail_id)->first();
    
        if (!$row) {
            return response()->json([
                'success' => false,
                'message' => 'Data audit tidak ditemukan.'
            ], 404);
        }
    
        $file = $request->file('foto_bukti_outlet');
    
        if (!$file || !$file->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'File upload tidak valid.'
            ], 422);
        }
    
        $ext = strtolower($file->getClientOriginalExtension());
        $filename = 'bukti_perbaikan_' . time() . '_' . Str::random(8) . '.' . $ext;
    
        $path = Storage::disk('public')->putFileAs('audit/bukti_perbaikan', $file, $filename);
    
        if (!$path) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan bukti perbaikan.'
            ], 500);
        }
    
        if (!empty($row->foto_perbaikan) && Storage::disk('public')->exists($row->foto_perbaikan)) {
            Storage::disk('public')->delete($row->foto_perbaikan);
        }
    
        DB::table('audit_harian')
            ->where('id', $request->detail_id)
            ->update([
                'foto_perbaikan' => $path,
                'updated_at'     => now('Asia/Jakarta'),
            ]);
    
        return response()->json([
            'success' => true,
            'message' => 'Bukti perbaikan berhasil diupload.',
            'path'    => $path,
            'url'     => Storage::url($path),
        ]);
    }

    public function importInternalAudit(Request $request)
    {
        return redirect()->back()->with('success', 'Fitur import audit belum diaktifkan.');
    }

    public function auditLogin()
    {
        return view('Investor.Audit.auditLogin');
    }

    public function auditLoginProses(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Cek user dari database
        $user = DB::table('tbl_user_responden')->where('username', $request->username)->first();

        if (! $user) {
            return back()->with('error', 'Username tidak ditemukan.');
        }

        if (! Hash::check($request->password, $user->password)) {
            return back()->with('error', 'Password salah.');
        }

        // Simpan sesi login
        session([
            'responden_id' => $user->id,
            'responden_nama' => $user->nama_lengkap,
            'responden_username' => $user->username,
        ]);

        return redirect()->route('auditDashboard.index')->with('success', 'Login berhasil!');
    }

    public function auditLogout(Request $request)
    {
        $request->session()->flush();

        auth()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah logout.');
    }

    public function auditRegistrasi()
    {
        return view('Investor.Audit.auditRegistrasi');
    }
    
    private function storeRegistrasiPhotoFromBase64(string $base64Image, string $namaLengkap): string
    {
        if (!preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
            throw new \Exception('Format foto registrasi tidak valid.');
        }
    
        $image = substr($base64Image, strpos($base64Image, ',') + 1);
        $image = str_replace(' ', '+', $image);
        $decoded = base64_decode($image);
    
        if ($decoded === false) {
            throw new \Exception('Foto registrasi gagal diproses.');
        }
    
        $extension = strtolower($type[1]);
        if ($extension === 'jpeg') {
            $extension = 'jpg';
        }
    
        if (!in_array($extension, ['jpg', 'png', 'webp'])) {
            $extension = 'jpg';
        }
    
        $safeName = Str::slug(substr($namaLengkap, 0, 30));
        $fileName = time() . '_' . $safeName . '.' . $extension;
        $path = 'audit/foto_registrasi/' . $fileName;
    
        $saved = Storage::disk('public')->put($path, $decoded);
    
        if (!$saved) {
            throw new \Exception('Gagal menyimpan foto registrasi.');
        }
    
        return $path;
    }
    
    private function storeRegistrasiPhotoToPublic($file, string $namaLengkap): string
    {
        if (!$file || !$file->isValid()) {
            throw new \Exception('File foto registrasi tidak valid.');
        }
    
        $extension = strtolower($file->getClientOriginalExtension());
        if ($extension === 'jpeg') {
            $extension = 'jpg';
        }
    
        if (!in_array($extension, ['jpg', 'png', 'webp'])) {
            $extension = 'jpg';
        }
    
        $safeName = Str::slug(substr($namaLengkap, 0, 30));
        $fileName = time() . '_' . $safeName . '.' . $extension;
    
        $storedPath = Storage::disk('public')->putFileAs('audit/foto_registrasi', $file, $fileName);
    
        if (!$storedPath) {
            throw new \Exception('Gagal upload foto registrasi.');
        }
    
        return $storedPath;
    }

    public function storeAuditRegistrasi(Request $request)
    {
        try {
            $request->validate([
                'nama_lengkap' => 'required|string|max:150|unique:tbl_user_responden,nama_lengkap',
                'username'     => 'required|string|max:255|unique:tbl_user_responden,username',
                'password'     => 'required|string|min:6',
                'nomor_telp'   => 'required|string|max:20|unique:tbl_user_responden,nomor_telp',
                'foto'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
                'fotoBase64'   => 'nullable|string',
            ]);
    
            if (!$request->filled('fotoBase64') && !$request->hasFile('foto')) {
                throw new \Exception('Foto wajib diisi, baik dari kamera maupun upload file.');
            }
    
            $savedPhotoPath = null;
    
            if ($request->filled('fotoBase64')) {
                $savedPhotoPath = $this->storeRegistrasiPhotoFromBase64(
                    $request->fotoBase64,
                    $request->nama_lengkap
                );
            } elseif ($request->hasFile('foto')) {
                $savedPhotoPath = $this->storeRegistrasiPhotoToPublic(
                    $request->file('foto'),
                    $request->nama_lengkap
                );
            }
    
            DB::table('tbl_user_responden')->insert([
                'nama_lengkap'   => $request->nama_lengkap,
                'username'       => $request->username,
                'password'       => bcrypt($request->password),
                'nomor_telp'     => $request->nomor_telp,
                'foto_user'      => $savedPhotoPath,
                'created_at'     => now('Asia/Jakarta'),
                'updated_at'     => now('Asia/Jakarta'),
            ]);
    
            return redirect()->route('auditDashboard.auditLogin')->with([
                'sweetalert' => [
                    'icon'  => 'success',
                    'title' => 'Registrasi Berhasil!',
                    'text'  => 'Akun berhasil dibuat. Silakan login.',
                ]
            ]);
        } catch (\Throwable $th) {
            return redirect()->back()->withInput()->with([
                'sweetalert' => [
                    'icon'  => 'error',
                    'title' => 'Registrasi Gagal!',
                    'text'  => $th->getMessage(),
                ]
            ]);
        }
    }

    // BACKOFFICE
    public function backOffice(Request $request)
    {
        // --- filters ---
        $filterOutlet = $request->input('outlet_id');
        $filterFrom = $request->input('from');
        $filterTo   = $request->input('to');

        // --- SUMMARY ---
        $totalJawaban = DB::table('tbl_audit_respon_detail')->count(); // total baris jawaban
        $totalOutlet  = DB::table('tbl_outlet_kuisioner')->count();
        $totalResponden = DB::table('tbl_user_responden')->count(); // gunakan tabel user yang ada
        $totalPoin = DB::table('tbl_poin_transaksi')->sum('jumlah_poin');
        $totalUang = $totalPoin * 5000; // konversi

        // --- Base respon query (header) untuk filter date/outlet usage ---
        $responHeader = DB::table('tbl_audit_respon as r')
            ->select('r.*');

        if ($filterOutlet) {
            $responHeader->where('r.outlet_id', $filterOutlet);
        }
        if ($filterFrom && $filterTo) {
            $responHeader->whereBetween(DB::raw('DATE(r.created_at)'), [$filterFrom, $filterTo]);
        } elseif ($filterFrom) {
            $responHeader->whereDate('r.created_at', '>=', $filterFrom);
        } elseif ($filterTo) {
            $responHeader->whereDate('r.created_at', '<=', $filterTo);
        }

        // --- 1) Jawaban per outlet (ya/tidak) ---
        $perOutletQuery = DB::table('tbl_audit_respon_detail as d')
            ->join('tbl_audit_respon as r', 'r.id', '=', 'd.respon_id')
            ->join('tbl_outlet_kuisioner as o', 'o.id', '=', 'r.outlet_id')
            ->select(
                'o.id as outlet_id',
                'o.nama_outlet',
                DB::raw("SUM(CASE WHEN d.jawaban = 1 THEN 1 ELSE 0 END) as total_ya"),
                DB::raw("SUM(CASE WHEN d.jawaban = 0 THEN 1 ELSE 0 END) as total_tidak"),
                DB::raw("COUNT(*) as total_jawaban")
            );

        if ($filterOutlet) {
            $perOutletQuery->where('r.outlet_id', $filterOutlet);
        }
        if ($filterFrom && $filterTo) {
            $perOutletQuery->whereBetween(DB::raw('DATE(r.created_at)'), [$filterFrom, $filterTo]);
        } elseif ($filterFrom) {
            $perOutletQuery->whereDate('r.created_at', '>=', $filterFrom);
        } elseif ($filterTo) {
            $perOutletQuery->whereDate('r.created_at', '<=', $filterTo);
        }

        $perOutlet = $perOutletQuery
            ->groupBy('o.id', 'o.nama_outlet')
            ->orderByDesc('total_jawaban')
            ->get();

        // --- 2) Registrasi user per tanggal (pakai tbl_user_responden) ---
        $respondenQuery = DB::table('tbl_user_responden')
            ->selectRaw("DATE(created_at) as tanggal, COUNT(*) as total_registrasi")
            ->groupBy('tanggal')
            ->orderBy('tanggal');

        if ($filterFrom && $filterTo) {
            $respondenQuery->whereBetween(DB::raw('DATE(created_at)'), [$filterFrom, $filterTo]);
        } elseif ($filterFrom) {
            $respondenQuery->whereDate('created_at', '>=', $filterFrom);
        } elseif ($filterTo) {
            $respondenQuery->whereDate('created_at', '<=', $filterTo);
        }

        $respondenPerTanggal = $respondenQuery->get();

        // --- 3) Statistik per pertanyaan (ya/tidak) ---
        $questionStatsQuery = DB::table('tbl_audit_respon_detail as d')
            ->join('tbl_kuisioner as q', 'q.id', '=', 'd.kuisioner_id')
            ->join('tbl_audit_respon as r', 'r.id', '=', 'd.respon_id')
            ->select(
                'q.id as kuisioner_id',
                'q.pertanyaan',
                // total Ya = jawaban == 1
                DB::raw("SUM(CASE WHEN d.jawaban = 1 THEN 1 ELSE 0 END) as total_ya"),
                // total Tidak = jawaban is not 1 and not null (any other code)
                DB::raw("SUM(CASE WHEN d.jawaban IS NOT NULL AND d.jawaban <> 1 THEN 1 ELSE 0 END) as total_tidak"),
                // total_jawaban = count of non-null jawaban
                DB::raw("SUM(CASE WHEN d.jawaban IS NOT NULL THEN 1 ELSE 0 END) as total_jawaban")
            );

        if ($filterOutlet) {
            $questionStatsQuery->where('r.outlet_id', $filterOutlet);
        }
        if ($filterFrom && $filterTo) {
            $questionStatsQuery->whereBetween(DB::raw('DATE(r.created_at)'), [$filterFrom, $filterTo]);
        } elseif ($filterFrom) {
            $questionStatsQuery->whereDate('r.created_at', '>=', $filterFrom);
        } elseif ($filterTo) {
            $questionStatsQuery->whereDate('r.created_at', '<=', $filterTo);
        }

        $questionStats = $questionStatsQuery
            ->groupBy('q.id', 'q.pertanyaan')
            ->orderByDesc('total_jawaban')
            ->get();

        // --- 4) Recent responses (sample) ---
        $recentResponses = DB::table('tbl_audit_respon as r')
            ->leftJoin('tbl_outlet_kuisioner as o', 'o.id', '=', 'r.outlet_id')
            ->leftJoin('tbl_user_responden as u', 'u.id', '=', 'r.user_id')
            ->select('r.id', 'r.user_id', 'u.nama_lengkap', 'o.nama_outlet', 'r.status_verifikasi', 'r.created_at', 'r.foto_bukti_outlet')
            ->when($filterOutlet, function ($q) use ($filterOutlet) {
                return $q->where('r.outlet_id', $filterOutlet);
            })
            ->when($filterFrom && $filterTo, function ($q) use ($filterFrom, $filterTo) {
                return $q->whereBetween(DB::raw('DATE(r.created_at)'), [$filterFrom, $filterTo]);
            })
            ->orderBy('r.created_at', 'desc')
            ->limit(50)
            ->get();

        // --- computed insights ---
        $avgJawabanPerOutlet = $totalOutlet > 0 ? round($totalJawaban / $totalOutlet, 1) : 0;
        $avgJawabanPerUser = $totalResponden > 0 ? round($totalJawaban / $totalResponden, 1) : 0;

        // --- outlet list for filter dropdown ---
        $outlets = DB::table('tbl_outlet_kuisioner')->select('id', 'nama_outlet')->orderBy('nama_outlet')->get();

        return view('Investor.Audit.Backoffice.dashboard', compact(
            'totalJawaban',
            'totalOutlet',
            'totalResponden',
            'totalPoin',
            'totalUang',
            'perOutlet',
            'respondenPerTanggal',
            'questionStats',
            'recentResponses',
            'avgJawabanPerOutlet',
            'avgJawabanPerUser',
            'outlets',
            'filterOutlet',
            'filterFrom',
            'filterTo'
        ));
    }

    public function logoutBackoffice(Request $request)
    {
        $request->session()->flush();

        return redirect()->route('login')->with('success', 'Anda telah logout.');
    }

    // Data Daftar Outlet Kuisioner
    public function outletKuisionerIndex()
    {
        $outlets = DB::table('tbl_outlet_kuisioner')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('Investor.Audit.Backoffice.daftarOutlet', compact('outlets'));
    }

    public function outletKuisionerStore(Request $request)
    {
        $request->validate([
            'nama_outlet' => 'required|string|max:150',
            'alamat' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        DB::table('tbl_outlet_kuisioner')->insert([
            'nama_outlet' => $request->nama_outlet,
            'alamat' => $request->alamat,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('auditDashboard.outlet.index')
            ->with('success', 'Outlet kuisioner berhasil ditambahkan');
    }

    public function outletKuisionerUpdate(Request $request, $id)
    {
        $request->validate([
            'nama_outlet' => 'required|string|max:150',
            'alamat' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        DB::table('tbl_outlet_kuisioner')->where('id', $id)->update([
            'nama_outlet' => $request->nama_outlet,
            'alamat' => $request->alamat,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'updated_at' => now(),
        ]);

        return redirect()->route('auditDashboard.outlet.index')
            ->with('success', 'Outlet kuisioner berhasil diperbarui');
    }

    public function outletKuisionerDestroy($id)
    {
        DB::table('tbl_outlet_kuisioner')->where('id', $id)->delete();

        return redirect()->route('auditDashboard.outlet.index')
            ->with('success', 'Outlet kuisioner berhasil dihapus');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        Excel::import(new OutletsKuisionerImport, $request->file('file'));

        return redirect()->back()->with('success', 'Outlet berhasil diimport!');
    }

    // Daftar Kuisioner
    public function daftarKuisioner()
    {
        // ambil semua pertanyaan dari tabel
        $pertanyaan = DB::table('pertanyaan_audit')->get();

        return view('Investor.Audit.Backoffice.daftarKuisioner', compact('pertanyaan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'jam' => 'nullable|string',
            'pertanyaan' => 'required|string',
        ]);

        DB::table('pertanyaan_audit')->insert([
            'pertanyaan' => $request->pertanyaan,
            'jam' => $request->jam,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('auditDashboard.daftarKuisioner')
            ->with('success', 'Kuisioner berhasil ditambahkan');
    }

    public function update(Request $request)
    {
        $request->validate([
            'id'         => 'required|exists:pertanyaan_audit,id',
            'pertanyaan' => 'required|string',
            'jam'        => 'nullable|string',
        ]);

        DB::table('pertanyaan_audit')->where('id', $request->id)->update([
            'pertanyaan' => $request->pertanyaan,
            'jam' => $request->jam,
            'updated_at' => now(),
        ]);

        return redirect()->route('auditDashboard.daftarKuisioner')
            ->with('success', 'Kuisioner berhasil diperbarui');
    }

    public function destroy($id)
    {
        $kuisioner = DB::table('tbl_kuisioner')->where('id', $id)->first();
        if ($kuisioner) {
            // kalau ada jam_dibuka, bisa ikut dihapus atau dibiarkan (opsional)
            DB::table('tbl_kuisioner')->where('id', $id)->delete();
        }

        return redirect()->route('auditDashboard.daftarKuisioner')
            ->with('success', 'Kuisioner berhasil dihapus');
    }

    // Jam buka kuisioner
    public function jamBukaKuisioner()
    {
        $jamBuka = DB::table('tbl_jam_dibuka')->orderBy('id', 'desc')->get();

        return view('Investor.Audit.Backoffice.jamBukaKuisioner', compact('jamBuka'));
    }

    public function jamBukaStore(Request $request)
    {
        $request->validate([
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'keterangan' => 'required',
        ]);

        DB::table('tbl_jam_dibuka')->insert([
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'keterangan' => $request->keterangan,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('auditDashboard.jamBuka.index')
            ->with('success', 'Jam Buka berhasil ditambahkan');
    }

    public function jamBukaUpdate(Request $request, $id)
    {
        $request->validate([
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'keterangan' => 'required',
        ]);

        DB::table('tbl_jam_dibuka')->where('id', $id)->update([
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'keterangan' => $request->keterangan,
            'updated_at' => now(),
        ]);

        return redirect()->route('auditDashboard.jamBuka.index')
            ->with('success', 'Jam Buka berhasil diperbarui');
    }

    public function jamBukaDestroy($id)
    {
        DB::table('tbl_jam_dibuka')->where('id', $id)->delete();

        return redirect()->route('auditDashboard.jamBuka.index')
            ->with('success', 'Jam Buka berhasil dihapus');
    }

    // Daftar Responden
    public function daftarResponden()
    {
        $respondens = DB::table('tbl_user_responden')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('Investor.Audit.Backoffice.daftarResponden', compact('respondens'));
    }

    public function respondenStore(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:150',
            'username' => 'required|string|max:50|unique:tbl_user_responden,username',
            'password' => 'required|string|min:6',
            'nomor_telp' => 'nullable|string|max:20',
            'foto_user' => 'nullable|image|max:2048',
        ]);

        $fotoFolder = public_path('audit/foto_registrasi');
        if (! file_exists($fotoFolder)) {
            mkdir($fotoFolder, 0755, true);
        }

        $fotoPath = null;
        if ($request->hasFile('foto_user')) {
            $file = $request->file('foto_user');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move($fotoFolder, $fileName);
            $fotoPath = 'audit/foto_registrasi/' . $fileName;
        }

        DB::table('tbl_user_responden')->insert([
            'nama_lengkap' => $request->nama_lengkap,
            'username' => $request->username,
            'password' => bcrypt($request->password),
            'nomor_telp' => $request->nomor_telp,
            'foto_user' => $fotoPath,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('responden.index')->with('success', 'Responden berhasil ditambahkan.');
    }

    public function respondenUpdate(Request $request, $id)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:150',
            'username' => 'required|string|max:50|unique:tbl_user_responden,username,' . $id,
            'password' => 'nullable|string|min:6',
            'nomor_telp' => 'nullable|string|max:20',
            'foto_user' => 'nullable|image|max:2048',
        ]);

        $responden = DB::table('tbl_user_responden')->where('id', $id)->first();
        if (! $responden) {
            return redirect()->route('responden.index')->with('error', 'Responden tidak ditemukan.');
        }

        $fotoFolder = public_path('audit/foto_registrasi');
        if (! file_exists($fotoFolder)) {
            mkdir($fotoFolder, 0755, true);
        }

        $fotoPath = $responden->foto_user;
        if ($request->hasFile('foto_user')) {
            if ($fotoPath && file_exists(public_path($fotoPath))) {
                unlink(public_path($fotoPath));
            }
            $file = $request->file('foto_user');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move($fotoFolder, $fileName);
            $fotoPath = 'audit/foto_registrasi/' . $fileName;
        }

        $dataUpdate = [
            'nama_lengkap' => $request->nama_lengkap,
            'username' => $request->username,
            'nomor_telp' => $request->nomor_telp,
            'foto_user' => $fotoPath,
            'updated_at' => now(),
        ];

        if ($request->password) {
            $dataUpdate['password'] = bcrypt($request->password);
        }

        DB::table('tbl_user_responden')->where('id', $id)->update($dataUpdate);

        return redirect()->route('responden.index')->with('success', 'Responden berhasil diperbarui.');
    }

    public function respondenDestroy($id)
    {
        $responden = DB::table('tbl_user_responden')->where('id', $id)->first();

        if ($responden) {
            if ($responden->foto_user) {
                Storage::disk('public')->delete($responden->foto_user);
            }

            DB::table('tbl_user_responden')->where('id', $id)->delete();

            return redirect()->route('responden.index')
                ->with('success', 'Responden berhasil dihapus.');
        }

        return redirect()->route('responden.index')
            ->with('error', 'Responden tidak ditemukan.');
    }

    // Jawaban Responden
    public function jawabanRespondenIndex()
    {
        // Ambil data utama respon
        $jawabanResponden = DB::table('tbl_audit_respon')
            ->join('tbl_user_responden', 'tbl_audit_respon.user_id', '=', 'tbl_user_responden.id')
            ->join('tbl_outlet_kuisioner', 'tbl_audit_respon.outlet_id', '=', 'tbl_outlet_kuisioner.id')
            ->select(
                'tbl_audit_respon.id',
                'tbl_user_responden.nama_lengkap as name', // tetap bisa akses ->name
                'tbl_outlet_kuisioner.nama_outlet',
                'tbl_audit_respon.foto_bukti_outlet',
                'tbl_audit_respon.created_at'
            )
            ->orderBy('tbl_audit_respon.created_at', 'desc')
            ->get();

        // Data tambahan untuk modal edit
        $respondens = DB::table('tbl_user_responden')
            ->select('id', 'nama_lengkap as name')
            ->get();

        $outlets = DB::table('tbl_outlet_kuisioner')
            ->select('id', 'nama_outlet')
            ->get();

        $kuisioners = DB::table('tbl_kuisioner')
            ->select('id', 'pertanyaan', 'needs_foto')
            ->get();

        return view('Investor.Audit.Backoffice.jawabanResponden', compact(
            'jawabanResponden',
            'respondens',
            'outlets',
            'kuisioners'
        ));
    }

    public function jawabanRespondenDetail($id)
    {
        // Ambil data master respon
        $respon = DB::table('tbl_audit_respon')
            ->join('tbl_user_responden', 'tbl_audit_respon.user_id', '=', 'tbl_user_responden.id')
            ->join('tbl_outlet_kuisioner', 'tbl_audit_respon.outlet_id', '=', 'tbl_outlet_kuisioner.id')
            ->select(
                'tbl_audit_respon.foto_bukti_outlet',
                'tbl_user_responden.nama_lengkap as nama_responden',
                'tbl_outlet_kuisioner.nama_outlet'
            )
            ->where('tbl_audit_respon.id', $id)
            ->first();

        if (! $respon) {
            return '<p class="text-danger">Data tidak ditemukan.</p>';
        }

        // Ambil jawaban detail
        $details = DB::table('tbl_audit_respon_detail')
            ->join('tbl_kuisioner', 'tbl_audit_respon_detail.kuisioner_id', '=', 'tbl_kuisioner.id')
            ->select(
                'tbl_kuisioner.pertanyaan',
                'tbl_audit_respon_detail.jawaban',
                'tbl_audit_respon_detail.foto_jawaban'
            )
            ->where('tbl_audit_respon_detail.respon_id', $id)
            ->get();

        // Render HTML modal
        $html = "<p><strong>Responden:</strong> {$respon->nama_responden}</p>";
        $html .= "<p><strong>Outlet:</strong> {$respon->nama_outlet}</p>";
        $html .= '<p><strong>Foto Bukti Outlet:</strong><br>';

        if ($respon->foto_bukti_outlet) {
            $html .= "<a href='" . asset($respon->foto_bukti_outlet) . "' target='_blank'>
                        <img src='" . asset($respon->foto_bukti_outlet) . "' alt='Foto Bukti' width='200' class='rounded shadow-sm'/>
                    </a>";
        } else {
            $html .= '<span class="text-muted">Tidak ada foto</span>';
        }
        $html .= '</p>';

        $html .= '<table class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="40%">Pertanyaan</th>
                            <th width="30%">Jawaban</th>
                            <th width="30%">Foto</th>
                        </tr>
                    </thead>
                    <tbody>';

        foreach ($details as $d) {
            $html .= "<tr>
                        <td>{$d->pertanyaan}</td>
                        <td>{$d->jawaban}</td>
                        <td>" . ($d->foto_jawaban
                ? "<a href='" . asset($d->foto_jawaban) . "' target='_blank'>
                                    <img src='" . asset($d->foto_jawaban) . "' alt='Foto Jawaban' width='100' class='rounded shadow-sm'/>
                            </a>"
                : '<span class="text-muted">Tidak ada foto</span>') . '</td>
                    </tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }

    public function jawabanRespondenStore(Request $request)
    {
        $userId = session('responden_id');

        if (! $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu.',
            ], 401);
        }

        $request->validate([
            'outlet' => 'required|numeric',
            'jawaban' => 'required|array',
            'foto.*' => 'nullable|image|max:2048',
            'foto_bukti_outlet' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        DB::beginTransaction();
        try {
            // ===== Simpan foto bukti outlet =====
            $buktiPath = null;
            if ($request->foto_bukti_outlet) {
                $img = str_replace('data:image/png;base64,', '', $request->foto_bukti_outlet);
                $img = str_replace(' ', '+', $img);
                $buktiPath = 'kuisioner/bukti_' . uniqid() . '.png';
                Storage::disk('public')->put($buktiPath, base64_decode($img));
            }

            // ===== Insert master respon =====
            $responId = DB::table('tbl_audit_respon')->insertGetId([
                'outlet_id' => $request->outlet,
                'user_id' => $userId,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'foto_bukti_outlet' => $buktiPath,
                'status_verifikasi' => 'pending',
                'created_at' => now(),
            ]);

            // ===== Insert detail jawaban =====
            foreach ($request->jawaban as $questionId => $answer) {
                $fotoPath = null;
                if ($request->hasFile("foto.$questionId")) {
                    $fotoPath = $request->file("foto.$questionId")->store('kuisioner', 'public');
                }

                DB::table('tbl_audit_respon_detail')->insert([
                    'respon_id' => $responId,
                    'kuisioner_id' => $questionId,
                    'jawaban' => $answer,
                    'foto_jawaban' => $fotoPath,
                    'created_at' => now(),
                ]);
            }

            // ===== Tambahkan 1 poin saja per pengisian =====
            DB::table('tbl_poin_transaksi')->insert([
                'id_user_responden' => $userId,
                'jumlah_poin' => 1,
                'created_at' => now(),
            ]);

            DB::commit();

            // Hitung total poin
            $totalPoin = DB::table('tbl_poin_transaksi')
                ->where('id_user_responden', $userId)
                ->sum('jumlah_poin');

            $saldo = $totalPoin * 5000;
            $progress = min(100, ($totalPoin / 120) * 100);

            return response()->json([
                'success' => true,
                'message' => 'Jawaban berhasil ditambahkan.',
                'total_poin' => $totalPoin,
                'saldo' => $saldo,
                'progress' => $progress,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan jawaban: ' . $e->getMessage(),
            ], 500);
        }
    }

    // public function jawabanRespondenStoreUser(Request $request)
    // {
    //     $userId = session('responden_id');
    //     if (! $userId) {
    //         return response()->json(['success' => false, 'message' => 'Silakan login terlebih dahulu.'], 401);
    //     }

    //     $request->validate([
    //         'outlet' => 'required|numeric',
    //         'jawaban' => 'required|array',
    //         'foto.*' => 'nullable|image|max:2048',
    //         'foto_bukti_outlet' => 'required|string',
    //         'latitude' => 'required|numeric',
    //         'longitude' => 'required|numeric',
    //     ]);

    //     DB::beginTransaction();
    //     try {
    //         // Folder publik
    //         $kuisionerFolder = public_path('audit/kuisioner');
    //         $buktiFolder = public_path('audit/foto_bukti');
    //         if (! file_exists($kuisionerFolder)) {
    //             mkdir($kuisionerFolder, 0755, true);
    //         }
    //         if (! file_exists($buktiFolder)) {
    //             mkdir($buktiFolder, 0755, true);
    //         }

    //         // Foto bukti outlet
    //         $buktiPath = null;
    //         if ($request->foto_bukti_outlet) {
    //             $img = str_replace('data:image/png;base64,', '', $request->foto_bukti_outlet);
    //             $img = str_replace(' ', '+', $img);
    //             $fileName = 'bukti_'.uniqid().'.png';
    //             file_put_contents($buktiFolder.'/'.$fileName, base64_decode($img));
    //             $buktiPath = 'audit/foto_bukti/'.$fileName;
    //         }

    //         // Insert master respon
    //         $responId = DB::table('tbl_audit_respon')->insertGetId([
    //             'outlet_id' => $request->outlet,
    //             'user_id' => $userId,
    //             'latitude' => $request->latitude,
    //             'longitude' => $request->longitude,
    //             'foto_bukti_outlet' => $buktiPath,
    //             'status_verifikasi' => 'pending',
    //             'created_at' => now(),
    //         ]);

    //         // Insert jawaban detail
    //         foreach ($request->jawaban as $questionId => $answer) {
    //             $fotoPath = null;
    //             if ($request->hasFile("foto.$questionId")) {
    //                 $file = $request->file("foto.$questionId");
    //                 $fileName = uniqid().'_'.$file->getClientOriginalName();
    //                 $file->move($kuisionerFolder, $fileName);
    //                 $fotoPath = 'audit/kuisioner/'.$fileName;
    //             }

    //             DB::table('tbl_audit_respon_detail')->insert([
    //                 'respon_id' => $responId,
    //                 'kuisioner_id' => $questionId,
    //                 'jawaban' => $answer,
    //                 'foto_jawaban' => $fotoPath,
    //                 'created_at' => now(),
    //             ]);
    //         }

    //         // Update poin user
    //         $poinUser = DB::table('tbl_poin_transaksi')->where('id_user_responden', $userId)->first();
    //         if ($poinUser) {
    //             DB::table('tbl_poin_transaksi')->where('id_user_responden', $userId)
    //                 ->update(['jumlah_poin' => $poinUser->jumlah_poin + 1, 'updated_at' => now()]);
    //         } else {
    //             DB::table('tbl_poin_transaksi')->insert([
    //                 'id_user_responden' => $userId,
    //                 'jumlah_poin' => 1,
    //                 'created_at' => now(),
    //                 'updated_at' => now(),
    //             ]);
    //         }

    //         DB::commit();

    //         $totalPoin = DB::table('tbl_poin_transaksi')->where('id_user_responden', $userId)->sum('jumlah_poin');
    //         $saldo = $totalPoin * 5000;
    //         $progress = min(100, ($totalPoin / 120) * 100);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Jawaban berhasil ditambahkan.',
    //             'total_poin' => $totalPoin,
    //             'saldo' => $saldo,
    //             'progress' => $progress,
    //         ]);

    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         return response()->json(['success' => false, 'message' => 'Gagal menyimpan jawaban: '.$e->getMessage()], 500);
    //     }
    // }

    public function jawabanRespondenStoreUser(Request $request)
    {
        $userId = session('responden_id');
        if (! $userId) {
            return response()->json(['success' => false, 'message' => 'Silakan login terlebih dahulu.'], 401);
        }

        // ✅ Ubah validasi supaya base64 diterima
        $request->validate([
            'outlet' => 'required|numeric',
            'jawaban' => 'required|array',
            'foto' => 'nullable|array',
            'foto_bukti_outlet' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        DB::beginTransaction();
        try {
            // 1️⃣ Pastikan folder publik ada
            $kuisionerFolder = public_path('audit/kuisioner');
            $buktiFolder = public_path('audit/foto_bukti');
            if (!file_exists($kuisionerFolder)) mkdir($kuisionerFolder, 0755, true);
            if (!file_exists($buktiFolder)) mkdir($buktiFolder, 0755, true);

            // 2️⃣ Simpan foto bukti outlet (base64)
            $buktiPath = null;
            if ($request->foto_bukti_outlet) {
                $img = preg_replace('#^data:image/\w+;base64,#i', '', $request->foto_bukti_outlet);
                $img = str_replace(' ', '+', $img);
                $fileName = 'bukti_' . uniqid() . '.png';
                file_put_contents($buktiFolder . '/' . $fileName, base64_decode($img));
                $buktiPath = 'audit/foto_bukti/' . $fileName;
            }

            // 3️⃣ Simpan data respon master
            $responId = DB::table('tbl_audit_respon')->insertGetId([
                'outlet_id' => $request->outlet,
                'user_id' => $userId,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'foto_bukti_outlet' => $buktiPath,
                'status_verifikasi' => 'pending',
                'created_at' => now(),
            ]);

            // 4️⃣ Loop semua jawaban
            foreach ($request->jawaban as $questionId => $answer) {
                $fotoPath = null;

                // a. Jika file diupload secara biasa
                if ($request->hasFile("foto.$questionId")) {
                    $file = $request->file("foto.$questionId");
                    $fileName = uniqid() . '_' . $file->getClientOriginalName();
                    $file->move($kuisionerFolder, $fileName);
                    $fotoPath = 'audit/kuisioner/' . $fileName;
                }

                // b. Jika dikirim dalam bentuk base64 string
                elseif (!empty($request->foto[$questionId]) && is_string($request->foto[$questionId])) {
                    $data = $request->foto[$questionId];
                    if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
                        $data = substr($data, strpos($data, ',') + 1);
                        $extension = strtolower($type[1]);
                        $data = str_replace(' ', '+', $data);
                        $decoded = base64_decode($data);
                        $fileName = 'foto_' . uniqid() . '.' . $extension;
                        file_put_contents($kuisionerFolder . '/' . $fileName, $decoded);
                        $fotoPath = 'audit/kuisioner/' . $fileName;
                    }
                }

                // c. Simpan detail jawaban
                DB::table('tbl_audit_respon_detail')->insert([
                    'respon_id' => $responId,
                    'kuisioner_id' => $questionId,
                    'jawaban' => $answer,
                    'foto_jawaban' => $fotoPath,
                    'created_at' => now(),
                ]);
            }

            // 5️⃣ Update atau insert poin user
            $poinUser = DB::table('tbl_poin_transaksi')->where('id_user_responden', $userId)->first();
            if ($poinUser) {
                DB::table('tbl_poin_transaksi')->where('id_user_responden', $userId)
                    ->update(['jumlah_poin' => $poinUser->jumlah_poin + 1, 'updated_at' => now()]);
            } else {
                DB::table('tbl_poin_transaksi')->insert([
                    'id_user_responden' => $userId,
                    'jumlah_poin' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            // 6️⃣ Hitung total poin & saldo
            $totalPoin = DB::table('tbl_poin_transaksi')->where('id_user_responden', $userId)->sum('jumlah_poin');
            $saldo = $totalPoin * 5000;
            $progress = min(100, ($totalPoin / 120) * 100);

            return response()->json([
                'success' => true,
                'message' => 'Jawaban berhasil disimpan!',
                'total_poin' => $totalPoin,
                'saldo' => $saldo,
                'progress' => $progress,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan jawaban: ' . $e->getMessage()
            ], 500);
        }
    }

    public function jawabanRespondenUpdate(Request $request, $id)
    {
        $request->validate([
            'id_kuisioner' => 'required|numeric',
            'id_outlet' => 'required|numeric',
            'id_user_responden' => 'required|numeric',
            'jawaban' => 'required|in:1,0', // ENUM '1' atau '0'
            'saran' => 'nullable|string',
        ]);

        DB::table('tbl_kuisioner_jawaban')->where('id', $id)->update([
            'id_kuisioner' => $request->id_kuisioner,
            'id_outlet' => $request->id_outlet,
            'id_user_responden' => $request->id_user_responden,
            'jawaban' => $request->jawaban,
            'saran' => $request->saran,
            'created_at' => now(),
        ]);

        return redirect()->route('jawabanResponden.index')->with('success', 'Jawaban berhasil diupdate.');
    }

    public function jawabanRespondenDestroy($id)
    {
        DB::table('tbl_kuisioner_jawaban')->where('id', $id)->delete();

        return redirect()->route('jawabanResponden.index')->with('success', 'Jawaban berhasil dihapus.');
    }

    // Riwayat Poin
    public function riwayatPoinIndex()
    {
        $riwayatPoin = DB::table('tbl_poin_transaksi')
            ->leftJoin('tbl_user_responden', 'tbl_poin_transaksi.id_user_responden', '=', 'tbl_user_responden.id')
            ->select(
                'tbl_poin_transaksi.id',
                'tbl_poin_transaksi.id_user_responden',
                'tbl_poin_transaksi.jumlah_poin',
                'tbl_poin_transaksi.created_at',
                'tbl_user_responden.nama_lengkap as nama_responden',
                'tbl_user_responden.username as email'
            )
            ->orderByDesc('tbl_poin_transaksi.created_at')
            ->get();

        return view('Investor.Audit.Backoffice.riwayatPoin', compact('riwayatPoin'));
    }

    public function riwayatPoinStore(Request $request)
    {
        $validated = $request->validate([
            'id_user_responden' => 'required|exists:tbl_user_responden,id',
            'jumlah_poin' => 'required|numeric|min:1',
        ]);

        DB::table('tbl_poin_transaksi')->insert([
            'id_user_responden' => $validated['id_user_responden'],
            'jumlah_poin' => $validated['jumlah_poin'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Riwayat poin berhasil ditambahkan.');
    }

    public function riwayatPoinUpdate(Request $request, $id)
    {
        $validated = $request->validate([
            'jumlah_poin' => 'required|numeric|min:1',
        ]);

        DB::table('tbl_poin_transaksi')->where('id', $id)->update([
            'jumlah_poin' => $validated['jumlah_poin'],
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Riwayat poin berhasil diperbarui.');
    }

    public function riwayatPoinDestroy($id)
    {
        DB::table('tbl_poin_transaksi')->where('id', $id)->delete();

        return redirect()->back()->with('success', 'Riwayat poin berhasil dihapus.');
    }

    // Daftar Hadiah
    public function daftarHadiahIndex()
    {
        $hadiah = DB::table('tbl_hadiah')->orderByDesc('created_at')->get();

        return view('Investor.Audit.Backoffice.daftarHadiah', compact('hadiah'));
    }

    public function daftarHadiahStore(Request $request)
    {
        $request->validate([
            'nama_hadiah' => 'required|string|max:255',
            'tipe' => 'required|string|in:voucher,transfer',
            'poin_dibutuhkan' => 'required|integer|min:0',
            'deskripsi' => 'nullable|string',
        ]);

        DB::table('tbl_hadiah')->insert([
            'nama_hadiah' => $request->nama_hadiah,
            'tipe' => $request->tipe,
            'poin_dibutuhkan' => $request->poin_dibutuhkan,
            'deskripsi' => $request->deskripsi,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('hadiah.index')->with('success', 'Hadiah berhasil ditambahkan.');
    }

    public function daftarHadiahUpdate(Request $request, $id)
    {
        $request->validate([
            'nama_hadiah' => 'required|string|max:255',
            'tipe' => 'required|string|in:voucher,transfer',
            'poin_dibutuhkan' => 'required|integer|min:0',
            'deskripsi' => 'nullable|string',
        ]);

        DB::table('tbl_hadiah')->where('id', $id)->update([
            'nama_hadiah' => $request->nama_hadiah,
            'tipe' => $request->tipe,
            'poin_dibutuhkan' => $request->poin_dibutuhkan,
            'deskripsi' => $request->deskripsi,
            'updated_at' => now(),
        ]);

        return redirect()->route('hadiah.index')->with('success', 'Hadiah berhasil diperbarui.');
    }

    public function daftarHadiahDestroy($id)
    {
        DB::table('tbl_hadiah')->where('id', $id)->delete();

        return redirect()->route('hadiah.index')->with('success', 'Hadiah berhasil dihapus.');
    }

    // Pencairan Poin
    public function pencairanPoinIndex()
    {
        $pencairan = DB::table('tbl_pencairan_poin as p')
            ->join('tbl_user_responden as u', 'p.id_user_responden', '=', 'u.id')
            ->join('tbl_hadiah as h', 'p.id_hadiah', '=', 'h.id')
            ->select(
                'p.id',
                'u.nama_lengkap as nama_responden',
                'h.nama_hadiah',
                'h.tipe',
                'p.jumlah_poin',
                'p.metode',
                'p.status',
                'p.expired_date',
                'p.created_at'
            )
            ->orderByDesc('p.created_at')
            ->get();

        return view('Investor.Audit.Backoffice.pencairanPoin', compact('pencairan'));
    }

    public function pencairanPoinUpdate(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        DB::table('tbl_pencairan_poin')->where('id', $id)->update([
            'status' => $request->status,
            'updated_at' => now(),
        ]);

        return redirect()->route('pencairanPoin.index')->with('success', 'Status pencairan berhasil diperbarui.');
    }

    public function pencairanPoinDestroy($id)
    {
        DB::table('tbl_pencairan_poin')->where('id', $id)->delete();

        return redirect()->route('pencairanPoin.index')->with('success', 'Data pencairan berhasil dihapus.');
    }

    // Laporan Pencairan Poin
    public function laporanPencairanIndex()
    {
        $pencairan = DB::table('tbl_pencairan_poin')
            ->join('tbl_user_responden', 'tbl_pencairan_poin.id_user_responden', '=', 'tbl_user_responden.id')
            ->join('tbl_hadiah', 'tbl_pencairan_poin.id_hadiah', '=', 'tbl_hadiah.id')
            ->select(
                'tbl_pencairan_poin.*',
                'tbl_user_responden.nama_lengkap as nama_responden',
                'tbl_hadiah.nama_hadiah',
                'tbl_hadiah.tipe'
            )
            ->orderBy('tbl_pencairan_poin.created_at', 'desc')
            ->get();

        return view('Investor.Audit.Backoffice.laporanPencairan', compact('pencairan'));
    }

    public function laporanPencairanExport()
    {
        return Excel::download(new LaporanPencairanExport, 'laporan_pencairan_poin.xlsx');
    }
    
    /* =========================
       DASHBOARD RECAP
    ========================= */
    
    public function dashboardRecap(Request $request)
    {
        $today = now('Asia/Jakarta')->toDateString();

        $tanggalAwal = $request->filled('tanggal_awal')
            ? Carbon::parse($request->tanggal_awal)->format('Y-m-d')
            : $today;

        $tanggalAkhir = $request->filled('tanggal_akhir')
            ? Carbon::parse($request->tanggal_akhir)->format('Y-m-d')
            : $today;

        $filterOutletId = $request->filled('outlet_id')
            ? (int) $request->outlet_id
            : null;

        $outlets = $this->getAuditOutletGroups()
            ->map(function ($item) {
                $item->id = (int) $item->id;
                $item->alias_ids = collect($item->alias_ids ?? [$item->id])
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values()
                    ->all();

                return $item;
            })
            ->sortBy('nama_outlet')
            ->values();

        $master = $this->getMasterQuestions();

        $questionsByJam = collect($master['questionsByJam'])->mapWithKeys(function ($items, $jam) {
            return [
                Carbon::parse($jam)->format('H:i:s') => collect($items),
            ];
        });

        $jamList = collect($master['jamList'])
            ->map(fn ($jam) => Carbon::parse($jam)->format('H:i:s'))
            ->unique()
            ->values();

        $allDates = $this->getDateList($tanggalAwal, $tanggalAkhir);

        $outletSource = $filterOutletId
            ? $outlets->where('id', $filterOutletId)->values()
            : $outlets->values();

        $selectedAliasIds = $outletSource
            ->flatMap(fn ($outlet) => $outlet->alias_ids)
            ->unique()
            ->values()
            ->all();

        $auditRows = $this->getAuditRows($tanggalAwal, $tanggalAkhir, $selectedAliasIds);

        $slotGrouped = collect($auditRows)->groupBy(function ($row) {
            return implode('|', [
                (int) $row->outlet_id,
                Carbon::parse($row->tanggal)->format('Y-m-d'),
                Carbon::parse($row->jam_aktivitas)->format('H:i:s'),
            ]);
        });

        $outletDailyScores = [];

        foreach ($allDates as $tanggal) {
            foreach ($outletSource as $outlet) {
                $totalPoint = 0;
                $totalMax = 0;

                $tmId = null;
                $tmNama = '-';

                $spvId = null;
                $spvNama = '-';

                $leaderId = null;
                $leaderNama = '-';

                $aliasIds = collect($outlet->alias_ids ?? [$outlet->id])
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values()
                    ->all();

                foreach ($jamList as $jam) {
                    $expectedQuestions = $questionsByJam->get($jam, collect());

                    $slotRows = collect();

                    foreach ($aliasIds as $aliasId) {
                        $slotKey = implode('|', [$aliasId, $tanggal, $jam]);
                        $slotRows = $slotRows->merge($slotGrouped->get($slotKey, collect()));
                    }

                    $slotRows = $slotRows->values();

                    $result = $this->calculateSlotResult(
                        $slotRows,
                        $expectedQuestions,
                        $tanggal,
                        $jam
                    );

                    $totalPoint += $result['point'] ?? 0;
                    $totalMax += $result['max'] ?? 0;

                    $first = $slotRows->sortBy('created_at')->first();

                    if ($first) {
                        $tmId = $tmId ?: ($first->tm_pic_id ?? null);
                        $tmNama = ($tmNama === '-' && !empty($first->tm_nama)) ? $first->tm_nama : $tmNama;

                        $spvId = $spvId ?: ($first->spv_pic_id ?? null);
                        $spvNama = ($spvNama === '-' && !empty($first->spv_nama)) ? $first->spv_nama : $spvNama;

                        $leaderId = $leaderId ?: ($first->leader_pic_id ?? null);
                        $leaderNama = ($leaderNama === '-' && !empty($first->leader_nama)) ? $first->leader_nama : $leaderNama;
                    }
                }

                $score = $totalMax > 0
                    ? round(($totalPoint / $totalMax) * 100, 2)
                    : 0;

                $outletDailyScores[] = [
                    'tanggal' => $tanggal,
                    'outlet_id' => $outlet->id,
                    'nama_outlet' => $outlet->nama_outlet,
                    'score' => $score,

                    'tm_pic_id' => $tmId,
                    'tm_nama' => $tmNama,

                    'spv_pic_id' => $spvId,
                    'spv_nama' => $spvNama,

                    'leader_pic_id' => $leaderId,
                    'leader_nama' => $leaderNama,
                ];
            }
        }

        $scoreRows = collect($outletDailyScores)
            ->filter(function ($row) {
                return !empty($row['tm_pic_id'])
                    || !empty($row['spv_pic_id'])
                    || !empty($row['leader_pic_id']);
            })
            ->values();

        $buildRanking = function ($rows, $idKey, $nameKey) {
            return $rows
                ->filter(fn ($row) => !empty($row[$idKey]) && !empty($row[$nameKey]) && $row[$nameKey] !== '-')
                ->groupBy($idKey)
                ->map(function ($items) use ($nameKey) {
                    $avgScore = round($items->avg('score'), 2);
                    $meta = $this->getScoreMeta($avgScore);

                    return (object) [
                        'nama' => $items->first()[$nameKey],
                        'average_score' => $avgScore,
                        'kategori' => $meta['label'],
                        'badge_class' => $meta['class'],
                    ];
                })
                ->sortByDesc('average_score')
                ->values()
                ->map(function ($item, $index) {
                    $item->ranking = $index + 1;
                    return $item;
                })
                ->values();
        };

        $tmRecap = $buildRanking($scoreRows, 'tm_pic_id', 'tm_nama');
        $spvRecap = $buildRanking($scoreRows, 'spv_pic_id', 'spv_nama');

        // sementara AM disamakan dengan TM karena getAuditRows() sekarang belum membawa am_pic_id/am_nama
        $amRecap = $buildRanking($scoreRows, 'tm_pic_id', 'tm_nama');

        $leaderRecap = $buildRanking($scoreRows, 'leader_pic_id', 'leader_nama');

        $perPage = 10;

        $tmRecap = $this->paginateCollection($tmRecap, $perPage, 'tm_page', $request);
        $spvRecap = $this->paginateCollection($spvRecap, $perPage, 'spv_page', $request);
        $amRecap = $this->paginateCollection($amRecap, $perPage, 'am_page', $request);
        $leaderRecap = $this->paginateCollection($leaderRecap, $perPage, 'leader_page', $request);

        return $this->render('Dashboard.recap', [
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'filterOutletId' => $filterOutletId,
            'outlets' => $outlets,
            'tmRecap' => $tmRecap,
            'spvRecap' => $spvRecap,
            'amRecap' => $amRecap,
            'leaderRecap' => $leaderRecap,
        ]);
    }
    
    private function paginateCollection($items, int $perPage, string $pageName, Request $request): LengthAwarePaginator
    {
        $items = $items instanceof Collection ? $items : collect($items);
    
        $currentPage = LengthAwarePaginator::resolveCurrentPage($pageName);
    
        $currentItems = $items
            ->slice(($currentPage - 1) * $perPage, $perPage)
            ->values();
    
        return new LengthAwarePaginator(
            $currentItems,
            $items->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'pageName' => $pageName,
                'query' => $request->query(),
            ]
        );
    }
    
    // REPORT
    
    public function laporan(Request $request)
    {
        $outlets = DB::table('tbl_outlets')
            ->select('id', 'nama_outlet')
            ->orderBy('nama_outlet')
            ->get();
    
        $respondenList = DB::table('tbl_user_responden')
            ->select('id', 'nama_lengkap')
            ->orderBy('nama_lengkap')
            ->get();
    
        $query = DB::table('audit_harian as ah')
            ->leftJoin('tbl_user_responden as ur', 'ah.id_responden', '=', 'ur.id')
            ->leftJoin('tbl_outlets as o', 'ah.outlet_id', '=', 'o.id');
    
        if ($request->filled('tanggal_awal') && $request->filled('tanggal_akhir')) {
            $query->whereBetween('ah.tanggal', [$request->tanggal_awal, $request->tanggal_akhir]);
        } elseif ($request->filled('tanggal_awal')) {
            $query->where('ah.tanggal', '>=', $request->tanggal_awal);
        } elseif ($request->filled('tanggal_akhir')) {
            $query->where('ah.tanggal', '<=', $request->tanggal_akhir);
        }
    
        if ($request->filled('responden')) {
            $query->where('ah.id_responden', $request->responden);
        }
    
        if ($request->filled('outlet')) {
            $query->where('ah.outlet_id', $request->outlet);
        }
    
        $data = $query->select(
                'ah.id',
                'ah.tanggal',
                'ah.jam_aktivitas',
                'ah.outlet_id',
                'ah.id_responden',
                'ah.nama_pic',
                'ah.pertanyaan',
                'ah.jawaban',
                'ah.alasan',
                'ah.foto',
                'ah.foto_perbaikan',
                'ah.created_at',
                'o.nama_outlet',
                DB::raw('COALESCE(NULLIF(ur.nama_lengkap, ""), NULLIF(ah.nama_pic, "")) as pic_nama')
            )
            ->orderByDesc('ah.tanggal')
            ->orderByDesc('ah.created_at')
            ->paginate(10)
            ->through(function ($item) {
                $item->foto_urls = $this->normalizeAuditPhotoUrls($item->foto);
                $item->foto_perbaikan_urls = $this->normalizeAuditPhotoUrls($item->foto_perbaikan);
    
                $item->foto_url = $item->foto_urls[0] ?? null;
                $item->foto_perbaikan_url = $item->foto_perbaikan_urls[0] ?? null;
    
                return $item;
            })
            ->withQueryString();
    
        return $this->render('Master.data_responses', compact('data', 'outlets', 'respondenList'));
    }
    
    private function normalizeAuditPhotoUrls($rawValue): array
    {
        if (empty($rawValue)) {
            return [];
        }
    
        $paths = [];
    
        if (is_array($rawValue)) {
            $paths = $rawValue;
        } elseif (is_string($rawValue)) {
            $rawValue = trim($rawValue);
    
            $decoded = json_decode($rawValue, true);
    
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $paths = $decoded;
            } else {
                $paths = [$rawValue];
            }
        }
    
        $urls = [];
    
        foreach ($paths as $path) {
            $url = $this->publicAuditPhotoUrl($path);
            if ($url) {
                $urls[] = $url;
            }
        }
    
        return $urls;
    }
    
    private function publicAuditPhotoUrl(?string $dbPath): ?string
    {
        $p = trim((string) $dbPath);
    
        if ($p === '') {
            return null;
        }
    
        if (filter_var($p, FILTER_VALIDATE_URL)) {
            return $p;
        }
    
        $p = str_replace('\\/', '/', $p);
        $p = str_replace('\\', '/', $p);
        $p = preg_replace('#^(public/|storage/)#', '', $p);
        $p = ltrim($p, '/');
    
        return asset('storage/' . $p);
    }
    
    public function daftarPertanyaan()
    {
        $data = DB::table('tbl_pertanyaan_dcr')
            ->orderBy('jam', 'asc')
            ->get();
    
        return $this->render('Master.data_pertanyaan', compact('data'));
    }
    
    public function dataOutlet(Request $request)
    {
        $query = DB::table('tbl_outlets')->orderBy('nama_outlet', 'asc');
    
        if ($request->filled('kota')) {
            $query->where('kota', $request->kota);
        }
    
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
    
        if ($request->filled('keyword')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_outlet', 'like', '%' . $request->keyword . '%')
                  ->orWhere('kode_outlet', 'like', '%' . $request->keyword . '%')
                  ->orWhere('alamat', 'like', '%' . $request->keyword . '%')
                  ->orWhere('kota', 'like', '%' . $request->keyword . '%');
            });
        }
    
        $data = $query->get();
    
        $kotaList = DB::table('tbl_outlets')
            ->select('kota')
            ->whereNotNull('kota')
            ->where('kota', '!=', '')
            ->distinct()
            ->orderBy('kota', 'asc')
            ->get();
    
        return $this->render('Master.data_outlet', compact('data', 'kotaList'));
    }
    
    // ================================
    // MASTER - DATA PIC
    // ================================
    
public function dataPic()
{
    $data = DB::table('tbl_pic_mapping as m')
        ->leftJoin('tbl_pic as p', 'm.pic_id', '=', 'p.id')
        ->leftJoin('tbl_outlets as o', 'm.outlet_id', '=', 'o.id')
        ->select(
            'm.id',
            'm.outlet_id',
            'm.pic_id',
            'm.level_pic',
            'm.created_at',
            'm.updated_at',
            'p.nama_lengkap',
            'o.nama_outlet as outlet_nama'
        )
        ->orderBy('p.nama_lengkap', 'asc')
        ->orderBy('m.level_pic', 'asc')
        ->orderBy('o.nama_outlet', 'asc')
        ->get();

    $outlets = DB::table('tbl_outlets')
        ->select('id', 'nama_outlet')
        ->orderBy('nama_outlet', 'asc')
        ->get();

    $groupedMappings = DB::table('tbl_pic_mapping')
        ->select('pic_id', 'level_pic', 'outlet_id')
        ->get()
        ->groupBy(function ($item) {
            return $item->pic_id . '|' . $item->level_pic;
        })
        ->map(function ($items) {
            return $items->pluck('outlet_id')->map(fn ($id) => (string) $id)->values()->toArray();
        })
        ->toArray();

    return $this->render('Master.data_pic', compact('data', 'outlets', 'groupedMappings'));
}

public function storePic(Request $request)
{
    $request->validate([
        'nama_lengkap' => 'required|string|max:255',
        'level_pic'    => 'required|in:LEADER,SPV,TM',
        'outlet_id'    => 'required|array|min:1',
        'outlet_id.*'  => 'required|exists:tbl_outlets,id',
    ], [
        'nama_lengkap.required' => 'Nama PIC wajib diisi.',
        'level_pic.required'    => 'Level PIC wajib dipilih.',
        'level_pic.in'          => 'Level PIC harus LEADER, SPV, atau TM.',
        'outlet_id.required'    => 'Minimal pilih 1 outlet.',
        'outlet_id.array'       => 'Format outlet tidak valid.',
        'outlet_id.*.exists'    => 'Ada outlet yang tidak ditemukan.',
    ]);

    DB::beginTransaction();

    try {
        $picId = DB::table('tbl_pic')->insertGetId([
            'nama_lengkap' => $request->nama_lengkap,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $outletIds = collect($request->outlet_id)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $insertData = $outletIds->map(function ($outletId) use ($picId, $request) {
            return [
                'pic_id'     => $picId,
                'outlet_id'  => $outletId,
                'level_pic'  => $request->level_pic,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();

        DB::table('tbl_pic_mapping')->insert($insertData);

        DB::commit();

        return redirect()
            ->route('master.data_pic')
            ->with('success', 'Data PIC dan mapping outlet berhasil ditambahkan.');
    } catch (\Throwable $e) {
        DB::rollBack();

        return redirect()
            ->route('master.data_pic')
            ->withErrors(['store' => 'Gagal menyimpan data PIC: ' . $e->getMessage()])
            ->withInput();
    }
}

public function updatePic(Request $request, $id)
{
    $request->validate([
        'outlet_id'   => 'required|array|min:1',
        'outlet_id.*' => 'required|exists:tbl_outlets,id',
    ], [
        'outlet_id.required' => 'Minimal pilih 1 outlet.',
        'outlet_id.array'    => 'Format outlet tidak valid.',
        'outlet_id.*.exists' => 'Ada outlet yang tidak ditemukan.',
    ]);

    $mapping = DB::table('tbl_pic_mapping')->where('id', $id)->first();

    if (!$mapping) {
        return redirect()
            ->route('master.data_pic')
            ->withErrors(['update' => 'Data mapping PIC tidak ditemukan.']);
    }

    DB::beginTransaction();

    try {
        $picId = $mapping->pic_id;
        $levelPic = $mapping->level_pic;

        $selectedOutletIds = collect($request->outlet_id)
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values();

        $existingOutletIds = DB::table('tbl_pic_mapping')
            ->where('pic_id', $picId)
            ->where('level_pic', $levelPic)
            ->pluck('outlet_id')
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values();

        $toInsert = $selectedOutletIds->diff($existingOutletIds)->values();
        $toDelete = $existingOutletIds->diff($selectedOutletIds)->values();

        if ($toInsert->isNotEmpty()) {
            $insertData = $toInsert->map(function ($outletId) use ($picId, $levelPic) {
                return [
                    'pic_id'     => $picId,
                    'outlet_id'  => $outletId,
                    'level_pic'  => $levelPic,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            DB::table('tbl_pic_mapping')->insert($insertData);
        }

        if ($toDelete->isNotEmpty()) {
            DB::table('tbl_pic_mapping')
                ->where('pic_id', $picId)
                ->where('level_pic', $levelPic)
                ->whereIn('outlet_id', $toDelete->toArray())
                ->delete();
        }

        DB::commit();

        return redirect()
            ->route('master.data_pic')
            ->with('success', 'Outlet PIC berhasil diupdate.');
    } catch (\Throwable $e) {
        DB::rollBack();

        return redirect()
            ->route('master.data_pic')
            ->withErrors(['update' => 'Gagal update outlet PIC: ' . $e->getMessage()]);
    }
}

public function destroyPic($id)
{
    $mapping = DB::table('tbl_pic_mapping')->where('id', $id)->first();

    if (!$mapping) {
        return redirect()
            ->route('master.data_pic')
            ->withErrors(['delete' => 'Data mapping PIC tidak ditemukan.']);
    }

    DB::table('tbl_pic_mapping')
        ->where('id', $id)
        ->delete();

    return redirect()
        ->route('master.data_pic')
        ->with('success', 'Data mapping PIC berhasil dihapus.');
}
    
    /* =========================
       COMPLIANCE RECAP
    ========================= */
    
    public function complianceRecap(Request $request)
    {
        $tanggalAwal = $request->tanggal_awal ?: now('Asia/Jakarta')->startOfMonth()->toDateString();
        $tanggalAkhir = $request->tanggal_akhir ?: now('Asia/Jakarta')->toDateString();
        $filterOutlet = $request->outlet;

        $outlets = DB::table('tbl_outlets')
            ->select('id', 'nama_outlet')
            ->orderBy('nama_outlet')
            ->get();

        $master = $this->getMasterQuestions();
        $questionsByJam = $master['questionsByJam'];
        $jamList = $master['jamList'];
        $periodeTanggal = $this->getDateList($tanggalAwal, $tanggalAkhir);

        $data = $this->getAuditRows($tanggalAwal, $tanggalAkhir, $filterOutlet);

        $totalAudit = $data->count();
        $totalYa = $data->where('jawaban', 'Ya')->count();
        $totalTidak = $data->where('jawaban', 'Tidak')->count();

        $slotGrouped = $this->groupAuditByOutletDateJam($data);

        $recap = $outlets
            ->when(!empty($filterOutlet), fn ($c) => $c->where('id', $filterOutlet))
            ->map(function ($outlet) use ($periodeTanggal, $jamList, $questionsByJam, $slotGrouped) {
                $dailyScores = [];

                $leader = '-';
                $spv = '-';
                $am = '-';

                $periodPoint = 0;
                $periodMax = 0;

                foreach ($periodeTanggal as $tanggal) {
                    $dayPoint = 0;
                    $dayMax = 0;

                    foreach ($jamList as $jam) {
                        $slotKey = implode('|', [$outlet->id, $tanggal, $jam]);
                        $slotRows = $slotGrouped->get($slotKey, collect());
                        $expectedQuestions = $questionsByJam->get($jam, collect());

                        $result = $this->calculateSlotResult($slotRows, $expectedQuestions, $tanggal, $jam);

                        $slotPoint = min((float) ($result['point'] ?? 0), (float) ($result['max'] ?? 0));
                        $slotMax = (float) ($result['max'] ?? 0);

                        $dayPoint += $slotPoint;
                        $dayMax += $slotMax;

                        $first = $slotRows->sortBy('created_at')->first();

                        if ($first) {
                            $leader = $leader === '-' && !empty($first->leader_nama) ? $first->leader_nama : $leader;
                            $spv = $spv === '-' && !empty($first->spv_nama) ? $first->spv_nama : $spv;
                            $am = $am === '-' && !empty($first->am_nama) ? $first->am_nama : $am;
                        }
                    }

                    $dayPoint = min($dayPoint, $dayMax);

                    $dailyScores[$tanggal] = $dayMax > 0
                        ? min(100, max(0, round(($dayPoint / $dayMax) * 100, 2)))
                        : 0;

                    $periodPoint += $dayPoint;
                    $periodMax += $dayMax;
                }

                $periodPoint = min($periodPoint, $periodMax);

                $score = $periodMax > 0
                    ? min(100, max(0, round(($periodPoint / $periodMax) * 100, 2)))
                    : 0;

                $meta = $this->getScoreMeta($score);

                return (object) [
                    'outlet_id' => $outlet->id,
                    'nama_outlet' => $outlet->nama_outlet,
                    'leader' => $leader,
                    'spv' => $spv,
                    'am' => $am,
                    'score' => $score,
                    'kategori' => $meta['label'],
                    'badge_class' => $meta['class'],
                    'daily_scores' => $dailyScores,
                ];
            })
            ->sortByDesc('score')
            ->values();

        $complianceRate = $recap->count() > 0
            ? min(100, max(0, round($recap->avg('score'), 2)))
            : 0;

        $chart = collect($periodeTanggal)->mapWithKeys(function ($tanggal) use ($recap) {
            $nilaiHari = $recap->pluck('daily_scores')
                ->map(fn ($scores) => min(100, max(0, (float) ($scores[$tanggal] ?? 0))));

            return [
                $tanggal => min(100, max(0, round($nilaiHari->avg(), 2))),
            ];
        });

        $labels = $chart->keys()->values();
        $values = $chart->values();

        return $this->render('Laporan.compliance_recap', compact(
            'outlets',
            'totalAudit',
            'totalYa',
            'totalTidak',
            'complianceRate',
            'recap',
            'labels',
            'values',
            'tanggalAwal',
            'tanggalAkhir',
            'filterOutlet',
            'periodeTanggal'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | WAJIB: tambahkan pengaman ini di calculateSlotResult()
    |--------------------------------------------------------------------------
    | Di akhir function calculateSlotResult(), sebelum return, pastikan point
    | tidak lebih besar dari max.
    |
    | Contoh:
    |
    | $point = min($point, $maxQuestions);
    |
    | return [
    |     'point' => $point,
    |     'max' => $maxQuestions,
    |     ...
    | ];
    */
    
    /* =========================
       RANKING OUTLET
    ========================= */
    
    public function rankingOutlet(Request $request)
    {
        $tanggalAwal = $request->tanggal_awal ?: now('Asia/Jakarta')->startOfMonth()->toDateString();
        $tanggalAkhir = $request->tanggal_akhir ?: now('Asia/Jakarta')->toDateString();
        $filterOutlet = $request->outlet;
    
        $outlets = DB::table('tbl_outlets')
            ->select('id', 'nama_outlet')
            ->orderBy('nama_outlet')
            ->get();
    
        $master = $this->getMasterQuestions();
        $questionsByJam = $master['questionsByJam'];
        $jamList = $master['jamList'];
        $allDates = $this->getDateList($tanggalAwal, $tanggalAkhir);
    
        $auditRows = $this->getAuditRows($tanggalAwal, $tanggalAkhir, $filterOutlet);
        $grouped = $this->groupAuditByOutletDateJam($auditRows);
    
        $rankingOutlet = $outlets
            ->when(!empty($filterOutlet), fn ($c) => $c->where('id', $filterOutlet))
            ->map(function ($outlet) use ($allDates, $jamList, $questionsByJam, $grouped) {
                $totalPoint = 0;
                $totalMax = 0;
                $leader = '-';
                $spv = '-';
                $am = '-';
    
                foreach ($allDates as $tanggal) {
                    foreach ($jamList as $jam) {
                        $key = implode('|', [$outlet->id, $tanggal, $jam]);
                        $slotRows = $grouped->get($key, collect());
                        $expectedQuestions = $questionsByJam->get($jam, collect());
    
                        $result = $this->calculateSlotResult($slotRows, $expectedQuestions, $tanggal, $jam);
    
                        $totalPoint += $result['point'];
                        $totalMax += $result['max'];
    
                        $first = $slotRows->sortBy('created_at')->first();
                        if ($first) {
                            $leader = $leader === '-' && !empty($first->leader_nama) ? $first->leader_nama : $leader;
                            $spv = $spv === '-' && !empty($first->spv_nama) ? $first->spv_nama : $spv;
                            $am = $am === '-' && !empty($first->am_nama) ? $first->am_nama : $am;
                        }
                    }
                }
    
                $score = $totalMax > 0 ? round(($totalPoint / $totalMax) * 100, 2) : 0;
                $meta = $this->getScoreMeta($score);
    
                return (object) [
                    'outlet_id' => $outlet->id,
                    'nama_outlet' => $outlet->nama_outlet,
                    'leader' => $leader,
                    'spv' => $spv,
                    'am' => $am,
                    'score' => $score,
                    'kategori' => $meta['label'],
                    'badge_class' => 'badge-office-' . $meta['class'],
                ];
            })
            ->sortByDesc('score')
            ->values()
            ->map(function ($item, $index) {
                $item->ranking = $index + 1;
                return $item;
            });
    
        $chartLabels = $rankingOutlet->take(10)->pluck('nama_outlet')->values();
        $chartValues = $rankingOutlet->take(10)->pluck('score')->values();
    
        return $this->render('Laporan.ranking_outlet', compact(
            'outlets',
            'rankingOutlet',
            'chartLabels',
            'chartValues',
            'tanggalAwal',
            'tanggalAkhir',
            'filterOutlet'
        ));
    }
    
    /* =========================
       KUMULATIF RANKING PIC
    ========================= */
    
    public function kumulatifRankingPic(Request $request)
    {
        $tanggalAwal = $request->tanggal_awal ?: now('Asia/Jakarta')->startOfMonth()->toDateString();
        $tanggalAkhir = $request->tanggal_akhir ?: now('Asia/Jakarta')->toDateString();
        $filterOutlet = $request->outlet;
        $filterPic = $request->pic;

        $outlets = DB::table('tbl_outlets')
            ->select('id', 'nama_outlet')
            ->orderBy('nama_outlet')
            ->get();

        $picList = DB::table('tbl_pic')
            ->select('id', 'nama_lengkap')
            ->orderBy('nama_lengkap')
            ->get();

        $master = $this->getMasterQuestions();
        $questionsByJam = $master['questionsByJam'];
        $jamList = $master['jamList'];
        $periodeTanggal = $this->getDateList($tanggalAwal, $tanggalAkhir);

        $mappingRows = DB::table('audit_harian as ah')
            ->leftJoin('tbl_outlets as o', 'ah.outlet_id', '=', 'o.id')
            ->leftJoin('tbl_pic as am', 'ah.tm_pic_id', '=', 'am.id')
            ->leftJoin('tbl_pic as spv', 'ah.spv_pic_id', '=', 'spv.id')
            ->leftJoin('tbl_pic as leader', 'ah.leader_pic_id', '=', 'leader.id')
            ->select(
                'ah.outlet_id',
                'o.nama_outlet',
                'ah.tm_pic_id',
                'ah.spv_pic_id',
                'ah.leader_pic_id',
                'am.nama_lengkap as am_nama',
                'spv.nama_lengkap as spv_nama',
                'leader.nama_lengkap as leader_nama',
                'ah.created_at'
            )
            ->when(!empty($filterOutlet), function ($q) use ($filterOutlet) {
                $q->where('ah.outlet_id', $filterOutlet);
            })
            ->whereDate('ah.tanggal', '<=', $tanggalAkhir)
            ->orderByDesc('ah.created_at')
            ->get()
            ->groupBy('outlet_id')
            ->map(function ($items) {
                return $items->first();
            })
            ->values();

        $auditRows = $this->getAuditRows($tanggalAwal, $tanggalAkhir, $filterOutlet)
            ->when(!empty($filterPic), function ($rows) use ($filterPic) {
                return $rows->filter(function ($row) use ($filterPic) {
                    return (string) $row->tm_pic_id === (string) $filterPic
                        || (string) $row->spv_pic_id === (string) $filterPic
                        || (string) $row->leader_pic_id === (string) $filterPic;
                })->values();
            });

        $slotGrouped = $this->groupAuditByOutletDateJam($auditRows);

        $buildRanking = function ($roleIdField, $roleNameField) use (
            $mappingRows,
            $periodeTanggal,
            $jamList,
            $questionsByJam,
            $slotGrouped,
            $filterPic
        ) {
            $result = [];

            foreach ($mappingRows as $mapping) {
                $picId = $mapping->{$roleIdField} ?? null;
                $picName = $mapping->{$roleNameField} ?? null;

                if (empty($picId) || empty($picName) || $picName === '-' || strtolower(trim($picName)) === 'null') {
                    continue;
                }

                if (!empty($filterPic) && (string) $picId !== (string) $filterPic) {
                    continue;
                }

                if (!isset($result[$picId])) {
                    $result[$picId] = [
                        'pic_id' => $picId,
                        'nama' => $picName,
                        'total_point' => 0,
                        'total_max' => 0,
                    ];
                }

                foreach ($periodeTanggal as $tanggal) {
                    foreach ($jamList as $jam) {
                        $slotKey = implode('|', [$mapping->outlet_id, $tanggal, $jam]);
                        $slotRows = $slotGrouped->get($slotKey, collect());
                        $expectedQuestions = $questionsByJam->get($jam, collect());

                        if (!$slotRows->isEmpty()) {
                            $result[$picId]['total_max'] += $expectedQuestions->count();
                        }

                        if ($slotRows->isEmpty()) {
                            continue;
                        }

                        $firstSlot = $slotRows->sortBy('created_at')->first();
                        $actualPicId = $firstSlot->{$roleIdField} ?? null;

                        if (empty($actualPicId)) {
                            continue;
                        }

                        if ((string) $actualPicId !== (string) $picId) {
                            continue;
                        }

                        $calc = $this->calculateSlotResult($slotRows, $expectedQuestions, $tanggal, $jam);
                        $result[$picId]['total_point'] += $calc['point'];
                    }
                }
            }

            return collect($result)
                ->filter(function ($item) {
                    return !empty($item['nama']);
                })
                ->map(function ($item) {
                    $score = $item['total_max'] > 0
                        ? round(($item['total_point'] / $item['total_max']) * 100, 2)
                        : 0;

                    $meta = $this->getScoreMeta($score);

                    return (object) [
                        'pic_id' => $item['pic_id'],
                        'nama' => $item['nama'],
                        'average_score' => $score,
                        'kategori' => $meta['label'],
                        'badge_class' => $meta['class'],
                    ];
                })
                ->sortByDesc('average_score')
                ->values()
                ->map(function ($item, $index) {
                    $item->ranking = $index + 1;
                    return $item;
                })
                ->values();
        };

        $amRanking = $buildRanking('tm_pic_id', 'am_nama');
        $spvRanking = $buildRanking('spv_pic_id', 'spv_nama');
        $leaderRanking = $buildRanking('leader_pic_id', 'leader_nama');

        return $this->render('Laporan.kumulatif_ranking_pic', compact(
            'outlets',
            'picList',
            'amRanking',
            'spvRanking',
            'leaderRanking',
            'tanggalAwal',
            'tanggalAkhir',
            'filterOutlet',
            'filterPic'
        ));
    }
    
    public function setting()
    {
        return $this->render('Master.setting');
    }
    
    public function storeSetting(Request $request)
    {
        return back()->with('success', 'Setting berhasil disimpan.');
    }
    
    public function updateSetting(Request $request, $id)
    {
        return back()->with('success', 'Setting berhasil diperbarui.');
    }
    
    public function destroySetting($id)
    {
        return back()->with('success', 'Setting berhasil dihapus.');
    }
}
