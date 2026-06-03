<?php

namespace App\Livewire\Audit;

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardHarian extends Component
{
    #[Url] public $tanggal = '';
    #[Url] public $filterOutletId = '';

    public function mount()
    {
        $this->tanggal = $this->tanggal ?: now('Asia/Jakarta')->toDateString();
    }

    public function resetFilter()
    {
        $this->tanggal = now('Asia/Jakarta')->toDateString();
        $this->filterOutletId = '';
    }

    private function normalizeOutletName(?string $name): string
    {
        $name = strtoupper(trim((string) $name));
        $name = preg_replace('/\s+/', ' ', $name);
        $name = preg_replace('/[^A-Z0-9 ]/', '', $name);
        $name = preg_replace('/\s+/', ' ', $name);

        return trim($name) ?: '';
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
                    'alias_ids' => $ids,
                ];
            })
            ->sortBy('nama_outlet')
            ->values();
    }

    #[Computed]
    public function dropdownOutlets()
    {
        return $this->getAuditOutletGroups();
    }

    private function calculateSlotResult($slotRows, $expectedQuestions, $tanggal, $jam)
    {
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

        $firstInput = $slotRows->sortBy('created_at')->first();

        $submittedAt = Carbon::parse($firstInput->created_at, 'UTC')
            ->setTimezone('Asia/Jakarta');

        $jamInput = $submittedAt->format('H:i:s');

        $slotStart = Carbon::parse($tanggal . ' ' . $jam, 'Asia/Jakarta');

        $lateMinutes = $slotStart->diffInMinutes($submittedAt, false);

        if ($lateMinutes <= 29) {
            $timeWeight = 1;
            $timeStatus = 'success';
            $timeStatusLabel = 'Ontime';
        } else {
            $timeWeight = 0.5;
            $timeStatus = 'warning';
            $timeStatusLabel = 'Late';
        }

        $actualByQuestion = $slotRows
            ->groupBy(fn ($item) => trim((string) $item->pertanyaan))
            ->map(fn ($items) => $items->sortBy('created_at')->first());

        $point = 0;

        foreach ($expectedQuestions as $question) {
            $questionText = trim((string) $question->pertanyaan);
            $row = $actualByQuestion->get($questionText);

            if (!$row) {
                continue;
            }

            $answerWeight = trim((string) $row->jawaban) === 'Ya' ? 1 : 0;
            $point += ($answerWeight * $timeWeight);
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

    #[Computed]
    public function dashboardData()
    {
        $masterQuestions = DB::table('tbl_pertanyaan_dcr')
            ->whereNotNull('jam')
            ->get();

        $questionsByJam = $masterQuestions
            ->groupBy(fn ($item) => Carbon::parse($item->jam)->format('H:i:s'));

        $jamList = $questionsByJam->keys()->values();

        $allOutlets = $this->dropdownOutlets;

        $outlets = $this->filterOutletId
            ? $allOutlets->where('id', (int) $this->filterOutletId)->values()
            : $allOutlets;

        $selectedAliasIds = $outlets
            ->flatMap(fn ($outlet) => $outlet->alias_ids ?? [$outlet->id])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $auditRows = DB::table('audit_harian as ah')
            ->leftJoin('tbl_pic as am', 'ah.tm_pic_id', '=', 'am.id')
            ->leftJoin('tbl_pic as spv', 'ah.spv_pic_id', '=', 'spv.id')
            ->leftJoin('tbl_pic as leader', 'ah.leader_pic_id', '=', 'leader.id')
            ->select(
                'ah.outlet_id',
                'ah.jam_aktivitas',
                'ah.pertanyaan',
                'ah.jawaban',
                'ah.created_at',
                'am.nama_lengkap as am_nama',
                'spv.nama_lengkap as spv_nama',
                'leader.nama_lengkap as leader_nama'
            )
            ->whereDate('ah.tanggal', $this->tanggal)
            ->when(!empty($selectedAliasIds), function ($query) use ($selectedAliasIds) {
                $query->whereIn('ah.outlet_id', $selectedAliasIds);
            })
            ->get();

        $auditGroupedByJam = $auditRows->groupBy(function ($row) {
            return Carbon::parse($row->jam_aktivitas)->format('H:i:s');
        });

        $dashboardData = [];
        $tglFormat = Carbon::parse($this->tanggal);

        foreach ($outlets as $outlet) {
            $rowsPerOutlet = [];
            $totalPoint = 0;
            $totalMax = 0;

            $aliasIds = collect($outlet->alias_ids ?? [$outlet->id])
                ->map(fn ($id) => (int) $id)
                ->all();

            foreach ($jamList as $jam) {
                $expectedQuestions = $questionsByJam->get($jam, collect());

                $slotRows = collect($auditGroupedByJam->get($jam, collect()))
                    ->filter(fn ($row) => in_array((int) $row->outlet_id, $aliasIds, true))
                    ->values();

                $result = $this->calculateSlotResult(
                    $slotRows,
                    $expectedQuestions,
                    $this->tanggal,
                    $jam
                );

                $totalPoint += $result['point'];
                $totalMax += $result['max'];

                $rowsPerOutlet[] = [
                    'jam' => 'Jam ' . Carbon::parse($jam)->format('H:i'),
                    'pertanyaan' => $expectedQuestions->pluck('pertanyaan')->implode("\n"),
                    'status' => $result['status'],
                    'status_label' => $result['status_label'],
                    'jam_input' => $result['jam_input'],
                ];
            }

            if ($totalMax > 0 || collect($rowsPerOutlet)->where('jam_input', '!=', '-')->isNotEmpty()) {
                $score = $totalMax > 0
                    ? min(100, round(($totalPoint / $totalMax) * 100, 2))
                    : 0;

                $dashboardData[] = [
                    'tanggal' => $tglFormat->format('d/m/Y'),
                    'bulan' => $tglFormat->translatedFormat('F'),
                    'tahun' => $tglFormat->format('Y'),
                    'outlet_id' => $outlet->id,
                    'outlet' => $outlet->nama_outlet,
                    'score' => $score,
                    'rows' => $rowsPerOutlet,
                ];
            }
        }

        return collect($dashboardData);
    }

    public function render()
    {
        return view('livewire.audit.dashboard-harian');
    }
}