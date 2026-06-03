<?php

namespace App\Livewire\Audit;

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class LaporanKumulatifRankingPic extends Component
{
    #[Url]
    public $tanggalAwal = '';

    #[Url]
    public $tanggalAkhir = '';

    #[Url]
    public $filterOutlet = '';

    #[Url]
    public $filterPic = '';

    public function mount()
    {
        $this->tanggalAwal = $this->tanggalAwal ?: now('Asia/Jakarta')->startOfMonth()->toDateString();
        $this->tanggalAkhir = $this->tanggalAkhir ?: now('Asia/Jakarta')->toDateString();
    }

    // Reset data jika filter berubah
    public function updating($property)
    {
        if (in_array($property, ['tanggalAwal', 'tanggalAkhir', 'filterOutlet', 'filterPic'])) {
            unset($this->rankings);
        }
    }

    #[Computed]
    public function dropdownOutlets()
    {
        return DB::table('tbl_outlets')->select('id', 'nama_outlet')->orderBy('nama_outlet')->get();
    }

    #[Computed]
    public function dropdownPics()
    {
        return DB::table('tbl_pic')
            ->select('id', 'nama_lengkap')
            ->orderBy('nama_lengkap')
            ->get();
    }

    // Helper untuk kalkulasi bobot waktu & poin persis seperti legacy
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
        if ($submittedAt->lte($slotEnd)) {
            $timeWeight = 1;
        } elseif ($submittedAt->lte($lateEnd)) {
            $timeWeight = 0.5;
        }

        $jawabanYa = $slotRows->where('jawaban', 'Ya')->count();

        return min($jawabanYa, $expectedCount) * $timeWeight;
    }

    #[Computed]
    public function rankings()
    {
        // 1. Ambil Master Pertanyaan & Jam
        $questionsByJam = DB::table('tbl_pertanyaan_dcr')
            ->whereNotNull('jam')
            ->get()
            ->groupBy(fn($item) => Carbon::parse($item->jam)->format('H:i:s'));
        $jamList = $questionsByJam->keys()->values();
        $periodeTanggal = collect(CarbonPeriod::create($this->tanggalAwal, $this->tanggalAkhir))->map(fn($d) => $d->format('Y-m-d'));

        // 2. Query Data Audit Harian
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

        if (!empty($this->filterOutlet)) {
            $query->where('ah.outlet_id', $this->filterOutlet);
        }

        $auditRows = $query->get();

        // Filter spesifik PIC jika dipilih
        if (!empty($this->filterPic)) {
            $auditRows = $auditRows->filter(
                fn($row) =>
                (string)$row->tm_pic_id === (string)$this->filterPic ||
                    (string)$row->spv_pic_id === (string)$this->filterPic ||
                    (string)$row->leader_pic_id === (string)$this->filterPic
            )->values();
        }

        $slotGrouped = $auditRows->groupBy(fn($row) => $row->outlet_id . '|' . $row->tanggal . '|' . Carbon::parse($row->jam_aktivitas)->format('H:i:s'));

        // 3. Mapping Master PIC dari data audit yang ada
        $amData = [];
        $spvData = [];
        $leaderData = [];

        foreach ($auditRows as $row) {
            // Setup array awal untuk PIC
            if ($row->tm_pic_id && !isset($amData[$row->tm_pic_id])) $amData[$row->tm_pic_id] = ['id' => $row->tm_pic_id, 'nama' => $row->am_nama, 'point' => 0, 'max' => 0];
            if ($row->spv_pic_id && !isset($spvData[$row->spv_pic_id])) $spvData[$row->spv_pic_id] = ['id' => $row->spv_pic_id, 'nama' => $row->spv_nama, 'point' => 0, 'max' => 0];
            if ($row->leader_pic_id && !isset($leaderData[$row->leader_pic_id])) $leaderData[$row->leader_pic_id] = ['id' => $row->leader_pic_id, 'nama' => $row->leader_nama, 'point' => 0, 'max' => 0];
        }

        // 4. Kalkulasi Poin Kumulatif
        foreach ($periodeTanggal as $tanggal) {
            foreach ($jamList as $jam) {
                $expectedCount = $questionsByJam->get($jam)->count();
                if ($expectedCount === 0) continue;

                // Ambil unique outlet_id dari auditRows untuk membatasi iterasi
                $uniqueOutlets = $auditRows->pluck('outlet_id')->unique();

                foreach ($uniqueOutlets as $outletId) {
                    $key = $outletId . '|' . $tanggal . '|' . $jam;
                    $slotRows = $slotGrouped->get($key, collect());
                    if ($slotRows->isEmpty()) continue;

                    $firstRow = $slotRows->sortBy('created_at')->first();
                    $score = $this->calculateSlotScore($slotRows, $expectedCount, $tanggal, $jam);

                    // Distribusi skor ke masing-masing PIC terkait
                    if ($firstRow->tm_pic_id) {
                        $amData[$firstRow->tm_pic_id]['point'] += $score;
                        $amData[$firstRow->tm_pic_id]['max'] += $expectedCount;
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

        // Format dan Sorting
        $formatRanking = function ($data) {
            return collect($data)->map(function ($item) {
                $avg = $item['max'] > 0 ? round(($item['point'] / $item['max']) * 100, 2) : 0;
                return (object) [
                    'nama' => $item['nama'],
                    'average_score' => $avg,
                    'status' => $avg >= 90 ? 'excellent' : ($avg >= 60 ? 'good' : 'poor')
                ];
            })->sortByDesc('average_score')->values()->map(function ($item, $key) {
                $item->ranking = $key + 1;
                return $item;
            });
        };

        return [
            'am' => $formatRanking($amData),
            'spv' => $formatRanking($spvData),
            'leader' => $formatRanking($leaderData),
        ];
    }

    public function render()
    {
        return view('livewire.audit.laporan-kumulatif-ranking-pic');
    }
}
