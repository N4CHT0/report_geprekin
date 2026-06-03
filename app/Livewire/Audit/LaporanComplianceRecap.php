<?php

namespace App\Livewire\Audit;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Pagination\LengthAwarePaginator;

class LaporanComplianceRecap extends Component
{
    use WithPagination;

    #[Url] public $tanggalAwal = '';
    #[Url] public $tanggalAkhir = '';
    #[Url] public $filterOutlet = '';

    public $search = '';

    public function mount()
    {
        $this->tanggalAwal = $this->tanggalAwal ?: now('Asia/Jakarta')->startOfMonth()->toDateString();
        $this->tanggalAkhir = $this->tanggalAkhir ?: now('Asia/Jakarta')->toDateString();
    }

    public function updating($property)
    {
        if (in_array($property, ['tanggalAwal', 'tanggalAkhir', 'filterOutlet', 'search'])) {
            $this->resetPage();
            unset($this->matrixData); // Bersihkan cache saat filter berubah
        }
    }

    #[Computed]
    public function dropdownOutlets()
    {
        return DB::table('tbl_outlets')->select('id', 'nama_outlet')->orderBy('nama_outlet')->get();
    }

    #[Computed]
    public function matrixData()
    {
        $periodeTanggal = collect(CarbonPeriod::create($this->tanggalAwal, $this->tanggalAkhir))->map(fn($d) => $d->format('Y-m-d'))->toArray();
        $questionsByJam = DB::table('tbl_pertanyaan_dcr')->whereNotNull('jam')->get()->groupBy(fn($item) => Carbon::parse($item->jam)->format('H:i:s'));
        $jamList = $questionsByJam->keys()->values();

        $query = DB::table('audit_harian as ah')
            ->leftJoin('tbl_outlets as o', 'ah.outlet_id', '=', 'o.id')
            ->leftJoin('tbl_pic as am', 'ah.tm_pic_id', '=', 'am.id')
            ->leftJoin('tbl_pic as spv', 'ah.spv_pic_id', '=', 'spv.id')
            ->leftJoin('tbl_pic as leader', 'ah.leader_pic_id', '=', 'leader.id')
            ->select(
                'ah.outlet_id',
                'o.nama_outlet',
                'ah.tanggal',
                'ah.jam_aktivitas',
                'ah.jawaban',
                'ah.created_at',
                'am.nama_lengkap as am_nama',
                'spv.nama_lengkap as spv_nama',
                'leader.nama_lengkap as leader_nama'
            )
            ->whereBetween('ah.tanggal', [$this->tanggalAwal, $this->tanggalAkhir]);

        if (!empty($this->filterOutlet)) {
            $query->where('ah.outlet_id', $this->filterOutlet);
        }

        $auditRows = $query->get();
        $totalAudit = $auditRows->count();
        $totalYa = $auditRows->where('jawaban', 'Ya')->count();
        $grouped = $auditRows->groupBy(fn($r) => $r->outlet_id . '|' . $r->tanggal . '|' . Carbon::parse($r->jam_aktivitas)->format('H:i:s'));

        $outlets = $this->filterOutlet ? $this->dropdownOutlets->where('id', $this->filterOutlet) : $this->dropdownOutlets;

        $recap = $outlets->map(function ($outlet) use ($periodeTanggal, $jamList, $questionsByJam, $grouped) {
            $periodPoint = 0;
            $periodMax = 0;
            $leader = '-';
            $spv = '-';
            $am = '-';
            $dailyScores = [];

            foreach ($periodeTanggal as $tanggal) {
                $dayPoint = 0;
                $dayMax = 0;
                foreach ($jamList as $jam) {
                    $key = $outlet->id . '|' . $tanggal . '|' . $jam;
                    $slotRows = $grouped->get($key, collect());
                    $expectedCount = $questionsByJam->get($jam)->count();

                    if ($expectedCount > 0) {
                        $dayMax += $expectedCount;
                        if ($slotRows->isNotEmpty()) {
                            $firstInput = $slotRows->sortBy('created_at')->first();
                            $submittedAt = Carbon::parse($firstInput->created_at, 'UTC')->setTimezone('Asia/Jakarta');
                            $slotStart = Carbon::parse($tanggal . ' ' . $jam, 'Asia/Jakarta');

                            $timeWeight = 0;
                            if ($submittedAt->lte($slotStart->copy()->addMinutes(30)->subSecond())) $timeWeight = 1;
                            elseif ($submittedAt->lte($slotStart->copy()->addMinutes(60))) $timeWeight = 0.5;

                            $dayPoint += $slotRows->where('jawaban', 'Ya')->count() * $timeWeight;
                            $leader = $leader === '-' && $firstInput->leader_nama ? $firstInput->leader_nama : $leader;
                            $spv = $spv === '-' && $firstInput->spv_nama ? $firstInput->spv_nama : $spv;
                            $am = $am === '-' && $firstInput->am_nama ? $firstInput->am_nama : $am;
                        }
                    }
                }
                $dailyScores[$tanggal] = $dayMax > 0 ? round(($dayPoint / $dayMax) * 100, 2) : 0;
                $periodPoint += $dayPoint;
                $periodMax += $dayMax;
            }

            $score = $periodMax > 0 ? round(($periodPoint / $periodMax) * 100, 2) : 0;
            return (object) [
                'outlet_id' => $outlet->id,
                'nama_outlet' => $outlet->nama_outlet,
                'leader' => $leader,
                'spv' => $spv,
                'am' => $am,
                'score' => $score,
                'daily_scores' => $dailyScores,
            ];
        })->sortByDesc('score')->values();

        $complianceRate = $recap->count() > 0 ? round($recap->avg('score'), 2) : 0;

        return [
            'recap' => $recap,
            'totalAudit' => $totalAudit,
            'totalYa' => $totalYa,
            'complianceRate' => $complianceRate,
            'periodeTanggal' => $periodeTanggal
        ];
    }

    public function render()
    {
        $matrixData = $this->matrixData;
        $recap = $matrixData['recap'];

        // Fitur Pencarian Real-time (Pengganti DataTables)
        if (!empty($this->search)) {
            $search = strtolower($this->search);
            $recap = $recap->filter(function ($item) use ($search) {
                return str_contains(strtolower($item->nama_outlet), $search) ||
                    str_contains(strtolower($item->leader), $search) ||
                    str_contains(strtolower($item->spv), $search) ||
                    str_contains(strtolower($item->am), $search);
            })->values();
        }

        // Paginasi Manual
        $page = $this->getPage();
        $perPage = 10;
        $paginatedRecap = new LengthAwarePaginator(
            $recap->slice(($page - 1) * $perPage, $perPage)->values(),
            $recap->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('livewire.audit.laporan-compliance-recap', [
            'paginatedRecap' => $paginatedRecap,
            'totalAudit' => $matrixData['totalAudit'],
            'totalYa' => $matrixData['totalYa'],
            'complianceRate' => $matrixData['complianceRate'],
            'periodeTanggal' => $matrixData['periodeTanggal']
        ]);
    }
}
