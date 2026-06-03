<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('Temp.Investor.header', function ($view) {

            $user = auth()->user();
            if (!$user) {
                $view->with([
                    'notifikasiTurunSales' => collect(),
                    'notifikasiNaikSales'  => collect(),
                ]);
                return;
            }

            $role = (string) ($user->role ?? '');

            /**
             * ============================================================
             * 1) Tentukan siapa yang boleh lihat semua outlet (GLOBAL)
             * ============================================================
             * Default:
             * - superadmin, superadmin_audit: global
             * - spv, tm_manager: aku set global juga supaya "bisa lihat"
             *   (kalau nanti ada mapping area/outlet khusus, ini bisa diganti)
             */
            $globalRoles = ['superadmin', 'superadmin_audit', 'spv', 'tm_manager'];
            $isGlobal = in_array($role, $globalRoles, true);

            /**
             * ============================================================
             * 2) Ambil outlet sesuai scope user
             * ============================================================
             */
            if ($isGlobal) {
                $outlets = DB::table('tbl_outlets')
                    ->select('id', 'nama_outlet', 'mitra_id')
                    ->get();
            } else {
                // investor (atau role lain yang scope-nya berbasis investor)
                $investorId = session('investor_id') ?? ($user->investor_id ?? null);

                if (!$investorId) {
                    $view->with([
                        'notifikasiTurunSales' => collect(),
                        'notifikasiNaikSales'  => collect(),
                    ]);
                    return;
                }

                $outlets = DB::table('tbl_outlets')
                    ->select('id', 'nama_outlet', 'mitra_id')
                    ->whereIn('mitra_id', function ($q) use ($investorId) {
                        $q->select('id')
                            ->from('tbl_mitra')
                            ->where('investor_id', $investorId);
                    })
                    ->get();
            }

            $outletIds = $outlets->pluck('id')->filter()->values()->all();

            if (empty($outletIds)) {
                $view->with([
                    'notifikasiTurunSales' => collect(),
                    'notifikasiNaikSales'  => collect(),
                ]);
                return;
            }

            /**
             * ============================================================
             * 3) Ambil 2 tanggal terakhir yang tersedia di tbl_laporan_bulanan
             * ============================================================
             */
            $tanggalTersedia = DB::table('tbl_laporan_bulanan')
                ->select(DB::raw('DATE(tanggal) as tanggal'))
                ->groupBy(DB::raw('DATE(tanggal)'))
                ->orderByDesc('tanggal')
                ->limit(2)
                ->pluck('tanggal');

            $notifikasiTurunSales = collect();
            $notifikasiNaikSales  = collect();

            if ($tanggalTersedia->count() < 2) {
                $view->with([
                    'notifikasiTurunSales' => $notifikasiTurunSales,
                    'notifikasiNaikSales'  => $notifikasiNaikSales,
                ]);
                return;
            }

            $tglBaru = $tanggalTersedia[0];
            $tglLama = $tanggalTersedia[1];

            /**
             * ============================================================
             * 4) Ambil total omset per outlet untuk 2 tanggal tsb (lebih hemat query)
             * ============================================================
             * Hasil: rows dengan outlet_id, tanggal, total
             */
            $rows = DB::table('tbl_laporan_bulanan')
                ->whereIn(DB::raw('DATE(tanggal)'), [$tglBaru, $tglLama])
                ->whereIn('outlet_id', $outletIds)
                ->select(
                    'outlet_id',
                    DB::raw('DATE(tanggal) as tanggal'),
                    DB::raw('SUM(total_omset) as total')
                )
                ->groupBy('outlet_id', DB::raw('DATE(tanggal)'))
                ->get();

            // Map: [outlet_id][tanggal] => total
            $totals = [];
            foreach ($rows as $r) {
                $oid = (int) $r->outlet_id;
                $tgl = (string) $r->tanggal;
                $totals[$oid][$tgl] = (float) ($r->total ?? 0);
            }

            // Untuk nama outlet cepat lookup
            $outletNameById = $outlets->keyBy('id')->map(fn($o) => $o->nama_outlet);

            /**
             * ============================================================
             * 5) Hitung perubahan: turun > 50%, naik > 50%
             * ============================================================
             */
            foreach ($outletIds as $oid) {
                $totalBaru = (float) ($totals[$oid][$tglBaru] ?? 0);
                $totalLama = (float) ($totals[$oid][$tglLama] ?? 0);

                // Kalau pembanding 0:
                // - Turun tidak relevan
                // - Naik: kalau baru > 0, bisa dianggap "naik besar" (opsional)
                if ($totalLama <= 0) {
                    if ($totalBaru > 0) {
                        // treat as naik "besar" (karena dari 0)
                        // Kalau kamu tidak mau ini, hapus blok ini.
                        $notifikasiNaikSales->push([
                            'outlet_id'          => $oid,
                            'nama_outlet'        => $outletNameById[$oid] ?? '-',
                            'tanggal_terbaru'    => $tglBaru,
                            'tanggal_pembanding' => $tglLama,
                            'total_hari_ini'     => $totalBaru,
                            'total_kemarin'      => $totalLama,
                        ]);
                    }
                    continue;
                }

                $pct = (($totalBaru - $totalLama) / $totalLama) * 100;

                if ($pct < 0 && abs($pct) > 50) {
                    $notifikasiTurunSales->push([
                        'outlet_id'          => $oid,
                        'nama_outlet'        => $outletNameById[$oid] ?? '-',
                        'tanggal_terbaru'    => $tglBaru,
                        'tanggal_pembanding' => $tglLama,
                        'total_hari_ini'     => $totalBaru,
                        'total_kemarin'      => $totalLama,
                    ]);
                }

                if ($pct > 50) {
                    $notifikasiNaikSales->push([
                        'outlet_id'          => $oid,
                        'nama_outlet'        => $outletNameById[$oid] ?? '-',
                        'tanggal_terbaru'    => $tglBaru,
                        'tanggal_pembanding' => $tglLama,
                        'total_hari_ini'     => $totalBaru,
                        'total_kemarin'      => $totalLama,
                    ]);
                }
            }

            /**
             * ============================================================
             * 6) Kirim ke view
             * ============================================================
             */
            $view->with([
                'notifikasiTurunSales' => $notifikasiTurunSales,
                'notifikasiNaikSales'  => $notifikasiNaikSales,
            ]);
        });
    }
}