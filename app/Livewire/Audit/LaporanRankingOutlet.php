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

class LaporanRankingOutlet extends Component
{
    use WithPagination;

    #[Url] public $tanggalAwal = '';
    #[Url] public $tanggalAkhir = '';
    #[Url] public $filterOutlet = '';

    // Fitur pencarian pengganti DataTables
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
            unset($this->fullRankingData); // Clear cache computed
        }
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
        $slotEnd = $slotStart->copy()->addMinutes(30)->subSecond();
        $lateEnd = $slotEnd->copy()->addMinutes(30);

        $timeWeight = 0;
        if ($submittedAt->lte($slotEnd)) $timeWeight = 1;
        elseif ($submittedAt->lte($lateEnd)) $timeWeight = 0.5;

        return $slotRows->where('jawaban', 'Ya')->count() * $timeWeight;
    }

    #[Computed]
    public function fullRankingData()
    {
        $questionsByJam = DB::table('tbl_pertanyaan_dcr')->whereNotNull('jam')->get()->groupBy(fn($item) => Carbon::parse($item->jam)->format('H:i:s'));
        $jamList = $questionsByJam->keys()->values();
        $allDates = collect(CarbonPeriod::create($this->tanggalAwal, $this->tanggalAkhir))->map(fn($d) => $d->format('Y-m-d'));

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
        $grouped = $auditRows->groupBy(fn($r) => $r->outlet_id . '|' . $r->tanggal . '|' . Carbon::parse($r->jam_aktivitas)->format('H:i:s'));

        $outlets = $this->filterOutlet ? $this->dropdownOutlets->where('id', $this->filterOutlet) : $this->dropdownOutlets;

        return $outlets->map(function ($outlet) use ($allDates, $jamList, $questionsByJam, $grouped) {
            $totalPoint = 0;
            $totalMax = 0;
            $leader = '-';
            $spv = '-';
            $am = '-';

            foreach ($allDates as $tanggal) {
                foreach ($jamList as $jam) {
                    $key = $outlet->id . '|' . $tanggal . '|' . $jam;
                    $slotRows = $grouped->get($key, collect());
                    $expectedCount = $questionsByJam->get($jam)->count();

                    if ($expectedCount > 0) {
                        $totalMax += $expectedCount;
                        if ($slotRows->isNotEmpty()) {
                            $totalPoint += $this->calculateSlotScore($slotRows, $expectedCount, $tanggal, $jam);
                            $first = $slotRows->sortBy('created_at')->first();
                            $leader = $leader === '-' && $first->leader_nama ? $first->leader_nama : $leader;
                            $spv = $spv === '-' && $first->spv_nama ? $first->spv_nama : $spv;
                            $am = $am === '-' && $first->am_nama ? $first->am_nama : $am;
                        }
                    }
                }
            }

            $score = $totalMax > 0 ? round(($totalPoint / $totalMax) * 100, 2) : 0;
            return (object) [
                'outlet_id' => $outlet->id,
                'nama_outlet' => $outlet->nama_outlet,
                'leader' => $leader,
                'spv' => $spv,
                'am' => $am,
                'score' => $score,
                'kategori' => $score >= 90 ? 'Excellent' : ($score >= 60 ? 'Good' : 'Bad'),
            ];
        })->sortByDesc('score')->values()->map(function ($item, $idx) {
            $item->ranking = $idx + 1;
            return $item;
        });
    }

    public function render()
    {
        $data = $this->fullRankingData;

        // Eksekusi Fitur Pencarian DataTables (Kini murni PHP/Livewire)
        if (!empty($this->search)) {
            $search = strtolower($this->search);
            $data = $data->filter(function ($item) use ($search) {
                return str_contains(strtolower($item->nama_outlet), $search) ||
                    str_contains(strtolower($item->leader), $search) ||
                    str_contains(strtolower($item->spv), $search) ||
                    str_contains(strtolower($item->am), $search);
            })->values();
        }

        // Paginasi Manual
        $page = $this->getPage();
        $perPage = 10;
        $paginatedData = new LengthAwarePaginator(
            $data->slice(($page - 1) * $perPage, $perPage)->values(),
            $data->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Data Chart (Top 10)
        $top10 = $this->fullRankingData->take(10);
        $chartLabels = $top10->pluck('nama_outlet')->toArray();
        $chartValues = $top10->pluck('score')->toArray();

        // Dispatch Event Alpine.js untuk Update Chart tanpa Reload
        $this->dispatch('update-ranking-chart', labels: $chartLabels, values: $chartValues);

        return view('livewire.audit.laporan-ranking-outlet', [
            'paginatedData' => $paginatedData,
            'summary' => [
                'total' => $this->fullRankingData->count(),
                'max' => $this->fullRankingData->max('score') ?? 0,
                'avg' => $this->fullRankingData->avg('score') ?? 0,
            ]
        ]);
    }
}
