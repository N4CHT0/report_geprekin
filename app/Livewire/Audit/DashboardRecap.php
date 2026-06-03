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

class DashboardRecap extends Component
{
    use WithPagination;

    #[Url] public $tanggalAwal = '';
    #[Url] public $tanggalAkhir = '';
    #[Url] public $filterOutletId = '';

    public function mount()
    {
        $this->tanggalAwal = $this->tanggalAwal ?: now('Asia/Jakarta')->toDateString();
        $this->tanggalAkhir = $this->tanggalAkhir ?: now('Asia/Jakarta')->toDateString();
    }

    public function updating($property)
    {
        if (in_array($property, ['tanggalAwal', 'tanggalAkhir', 'filterOutletId'])) {
            // Reset semua paginasi jika filter diubah
            $this->resetPage('tm_page');
            $this->resetPage('spv_page');
            $this->resetPage('am_page');
            $this->resetPage('leader_page');
            unset($this->rankings);
        }
    }

    public function resetFilter()
    {
        $this->tanggalAwal = now('Asia/Jakarta')->toDateString();
        $this->tanggalAkhir = now('Asia/Jakarta')->toDateString();
        $this->filterOutletId = '';
        $this->resetPage('tm_page');
        $this->resetPage('spv_page');
        $this->resetPage('am_page');
        $this->resetPage('leader_page');
        unset($this->rankings);
    }

    #[Computed]
    public function dropdownOutlets()
    {
        return DB::table('tbl_outlets')->select('id', 'nama_outlet')->orderBy('nama_outlet')->get();
    }

    private function calculateSlotScore($slotRows, $expectedCount, $tanggal, $jam)
    {
        if ($expectedCount === 0 || $slotRows->isEmpty()) return 0;

        $firstInput = $slotRows->sortBy('created_at')->first();
        if (!$firstInput) return 0;

        $submittedAt = Carbon::parse($firstInput->created_at, 'UTC')->setTimezone('Asia/Jakarta');
        $slotStart = Carbon::parse($tanggal . ' ' . $jam, 'Asia/Jakarta');

        $timeWeight = 0;
        if ($submittedAt->lte($slotStart->copy()->addMinutes(30)->subSecond())) $timeWeight = 1;
        elseif ($submittedAt->lte($slotStart->copy()->addMinutes(60))) $timeWeight = 0.5;

        $yaCount = $slotRows->where('jawaban', 'Ya')->count();
        return min($yaCount, $expectedCount) * $timeWeight;
    }

    #[Computed]
    public function rankings()
    {
        $questionsByJam = DB::table('tbl_pertanyaan_dcr')->whereNotNull('jam')->get()->groupBy(fn($item) => Carbon::parse($item->jam)->format('H:i:s'));
        $jamList = $questionsByJam->keys()->values();
        $periodeTanggal = collect(CarbonPeriod::create($this->tanggalAwal, $this->tanggalAkhir))->map(fn($d) => $d->format('Y-m-d'));

        $query = DB::table('audit_harian as ah')
            ->leftJoin('tbl_pic as am', 'ah.tm_pic_id', '=', 'am.id')
            ->leftJoin('tbl_pic as spv', 'ah.spv_pic_id', '=', 'spv.id')
            ->leftJoin('tbl_pic as leader', 'ah.leader_pic_id', '=', 'leader.id')
            ->select(
                'ah.outlet_id',
                'ah.tanggal',
                'ah.jam_aktivitas',
                'ah.jawaban',
                'ah.created_at',
                'ah.tm_pic_id',
                'ah.spv_pic_id',
                'ah.leader_pic_id',
                'am.nama_lengkap as am_nama',
                'spv.nama_lengkap as spv_nama',
                'leader.nama_lengkap as leader_nama'
            )
            ->whereBetween('ah.tanggal', [$this->tanggalAwal, $this->tanggalAkhir]);

        if (!empty($this->filterOutletId)) {
            $query->where('ah.outlet_id', $this->filterOutletId);
        }

        $auditRows = $query->get();
        $slotGrouped = $auditRows->groupBy(fn($row) => $row->outlet_id . '|' . $row->tanggal . '|' . Carbon::parse($row->jam_aktivitas)->format('H:i:s'));

        $tmData = [];
        $spvData = [];
        $amData = [];
        $leaderData = [];

        // Inisialisasi Data berdasarkan ID PIC yang muncul
        foreach ($auditRows as $row) {
            // Catatan: Asumsi TM dan AM menggunakan tm_pic_id di legacy, disesuaikan ke struktur Anda
            if ($row->tm_pic_id) {
                if (!isset($amData[$row->tm_pic_id])) $amData[$row->tm_pic_id] = ['nama' => $row->am_nama, 'point' => 0, 'max' => 0];
                if (!isset($tmData[$row->tm_pic_id])) $tmData[$row->tm_pic_id] = ['nama' => $row->am_nama, 'point' => 0, 'max' => 0]; // Duplicate mapping like legacy
            }
            if ($row->spv_pic_id && !isset($spvData[$row->spv_pic_id])) $spvData[$row->spv_pic_id] = ['nama' => $row->spv_nama, 'point' => 0, 'max' => 0];
            if ($row->leader_pic_id && !isset($leaderData[$row->leader_pic_id])) $leaderData[$row->leader_pic_id] = ['nama' => $row->leader_nama, 'point' => 0, 'max' => 0];
        }

        $uniqueOutlets = $auditRows->pluck('outlet_id')->unique();

        foreach ($periodeTanggal as $tanggal) {
            foreach ($jamList as $jam) {
                $expectedCount = $questionsByJam->get($jam)->count();
                if ($expectedCount === 0) continue;

                foreach ($uniqueOutlets as $outletId) {
                    $key = $outletId . '|' . $tanggal . '|' . $jam;
                    $slotRows = $slotGrouped->get($key, collect());
                    if ($slotRows->isEmpty()) continue;

                    $firstRow = $slotRows->sortBy('created_at')->first();
                    $score = $this->calculateSlotScore($slotRows, $expectedCount, $tanggal, $jam);

                    if ($firstRow->tm_pic_id) {
                        $amData[$firstRow->tm_pic_id]['point'] += $score;
                        $amData[$firstRow->tm_pic_id]['max'] += $expectedCount;
                        $tmData[$firstRow->tm_pic_id]['point'] += $score;
                        $tmData[$firstRow->tm_pic_id]['max'] += $expectedCount;
                    }
                    if ($firstRow->spv_pic_id) {
                        $spvData[$firstRow->spv_pic_id]['point'] += $score;
                        $spvData[$firstRow->spv_pic_id]['max'] += $expectedCount;
                    }
                    if ($firstRow->leader_pic_id) {
                        $leaderData[$firstRow->leader_pic_id]['point'] += $score;
                        $leaderData[$firstRow->leader_pic_id]['max'] += $expectedCount;
                    }
                }
            }
        }

        $formatRanking = function ($data) {
            return collect($data)->map(function ($item) {
                return (object) [
                    'nama' => $item['nama'],
                    'average_score' => $item['max'] > 0 ? round(($item['point'] / $item['max']) * 100, 2) : 0,
                ];
            })->sortByDesc('average_score')->values()->map(function ($item, $key) {
                $item->ranking = $key + 1;
                return $item;
            });
        };

        return [
            'tm' => $formatRanking($tmData),
            'spv' => $formatRanking($spvData),
            'am' => $formatRanking($amData),
            'leader' => $formatRanking($leaderData),
        ];
    }

    private function paginateArray($items, $pageName, $perPage = 10)
    {
        $page = $this->getPage($pageName);
        return new LengthAwarePaginator(
            $items->slice(($page - 1) * $perPage, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query(), 'pageName' => $pageName]
        );
    }

    public function render()
    {
        $rankings = $this->rankings;

        return view('livewire.audit.dashboard-recap', [
            'paginatedTm' => $this->paginateArray($rankings['tm'], 'tm_page'),
            'paginatedSpv' => $this->paginateArray($rankings['spv'], 'spv_page'),
            'paginatedAm' => $this->paginateArray($rankings['am'], 'am_page'),
            'paginatedLeader' => $this->paginateArray($rankings['leader'], 'leader_page'),
            'selectedOutletName' => $this->filterOutletId ? ($this->dropdownOutlets->firstWhere('id', $this->filterOutletId)->nama_outlet ?? 'Semua Outlet') : 'Semua Outlet'
        ]);
    }
}
