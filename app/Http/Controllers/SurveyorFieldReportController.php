<?php

namespace App\Http\Controllers;

use App\Services\Surveyor\SiteScoreCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * SurveyorFieldReportController (REFACTOR - SOLUSI GAP #5)
 *
 * Sebelumnya: kolom approved_by, approved_at, created_site_score_id
 *             tidak ada di DB → error saat approveToSiteScore() dijalankan.
 *
 * Setelah migration fix_1_migration.sql dijalankan, kolom sudah tersedia.
 * Controller ini juga memanggil SiteScoreCalculatorService (GAP #2 + #1).
 */
class SurveyorFieldReportController extends Controller
{
    public function __construct(
        private SiteScoreCalculatorService $calculator
    ) {}

    public function index()
    {
        $reports = DB::table('surveyor_field_reports as r')
            ->leftJoin('surveyor_candidate_locations as c', 'c.id', '=', 'r.candidate_location_id')
            ->select('r.*', 'c.kode_lokasi', 'c.nama_lokasi')
            ->orderByDesc('r.id')
            ->limit(300)
            ->get();

        return view('Surveyor.field-report.index', compact('reports'));
    }

    public function detail($id)
    {
        $report = DB::table('surveyor_field_reports as r')
            ->leftJoin('surveyor_candidate_locations as c', 'c.id', '=', 'r.candidate_location_id')
            ->select('r.*', 'c.kode_lokasi', 'c.nama_lokasi', 'c.alamat', 'c.kota', 'c.provinsi')
            ->where('r.id', $id)
            ->first();

        if (!$report) abort(404);

        $payload = json_decode($report->parsed_payload ?? '{}', true) ?: [];

        return view('Surveyor.field-report.detail', compact('report', 'payload'));
    }

    /**
     * Approve field report → buat Site Score final.
     *
     * SOLUSI GAP #5: kolom approved_by, approved_at, created_site_score_id
     * sekarang tersedia di DB (lihat fix_1_migration.sql).
     *
     * SOLUSI GAP #1 + #2: kalkulasi via SiteScoreCalculatorService.
     */
    public function approveToSiteScore(Request $request, $id)
    {
        $report = DB::table('surveyor_field_reports')->where('id', $id)->first();

        if (!$report) abort(404);

        // Cegah double-approve
        if (!empty($report->created_site_score_id)) {
            return back()->with('error', 'Laporan ini sudah dibuat menjadi Site Score.');
        }

        $candidate = null;
        if ($report->candidate_location_id) {
            $candidate = DB::table('surveyor_candidate_locations')
                ->where('id', $report->candidate_location_id)
                ->first();
        }

        // Ambil payload dan normalisasi
        $payload = json_decode($report->parsed_payload ?? '{}', true) ?: [];
        $payload = $this->calculator->normalizePayload($payload);

        // Tentukan MoE: Madura pakai 30%, selainnya 20%
        $provinsi = strtolower($payload['provinsi'] ?? ($candidate->provinsi ?? ''));
        $moe      = str_contains($provinsi, 'madura') ? 0.30 : 0.20;

        // Kalkulasi via service (GAP #1 + #2)
        $averageCheck = (float) ($payload['average_check'] ?? 21000);
        $calc = $this->calculator->calculate(
            array_merge($payload, ['average_check' => $averageCheck]),
            $moe
        );

        $lat     = $payload['latitude']  ?? ($candidate->latitude  ?? null);
        $lng     = $payload['longitude'] ?? ($candidate->longitude ?? null);
        $mapsUrl = ($lat && $lng)
            ? 'https://www.google.com/maps?q=' . $lat . ',' . $lng
            : null;

        // Insert Site Score
        $siteScoreId = DB::table('surveyor_site_scores')->insertGetId(array_merge([
            'candidate_location_id' => $report->candidate_location_id,
            'field_report_id'       => $report->id,
            'kode_score'            => 'SS-' . now()->format('YmdHis'),
            'lokasi'                => $payload['lokasi']   ?? ($candidate->nama_lokasi ?? null),
            'kota'                  => $payload['kota']     ?? ($candidate->kota        ?? null),
            'provinsi'              => $payload['provinsi'] ?? ($candidate->provinsi    ?? null),
            'surveyor'              => $payload['surveyor'] ?? $report->surveyor ?? null,
            'tanggal_survey'        => now(),
            'latitude'              => $lat,
            'longitude'             => $lng,
            'maps_url'              => $mapsUrl,

            'motor_weekday_pagi'    => (int) ($payload['motor_weekday_pagi']   ?? 0),
            'motor_weekday_siang'   => (int) ($payload['motor_weekday_siang']  ?? 0),
            'motor_weekday_sore'    => (int) ($payload['motor_weekday_sore']   ?? 0),
            'motor_weekend_pagi'    => (int) ($payload['motor_weekend_pagi']   ?? 0),
            'motor_weekend_siang'   => (int) ($payload['motor_weekend_siang']  ?? 0),
            'motor_weekend_sore'    => (int) ($payload['motor_weekend_sore']   ?? 0),

            'pejalan_weekday_pagi'  => (int) ($payload['pejalan_weekday_pagi']  ?? 0),
            'pejalan_weekday_siang' => (int) ($payload['pejalan_weekday_siang'] ?? 0),
            'pejalan_weekday_sore'  => (int) ($payload['pejalan_weekday_sore']  ?? 0),
            'pejalan_weekend_pagi'  => (int) ($payload['pejalan_weekend_pagi']  ?? 0),
            'pejalan_weekend_siang' => (int) ($payload['pejalan_weekend_siang'] ?? 0),
            'pejalan_weekend_sore'  => (int) ($payload['pejalan_weekend_sore']  ?? 0),

            'rumah_q1'           => (int) ($payload['rumah_q1']          ?? 0),
            'rumah_q2'           => (int) ($payload['rumah_q2']          ?? 0),
            'rumah_q3'           => (int) ($payload['rumah_q3']          ?? 0),
            'rumah_q4'           => (int) ($payload['rumah_q4']          ?? 0),
            'sekolah'            => (int) ($payload['sekolah']           ?? 0),
            'market'             => (int) ($payload['market']            ?? 0),
            'perkantoran'        => (int) ($payload['perkantoran']       ?? 0),
            'kesehatan'          => (int) ($payload['kesehatan']         ?? 0),
            'kompetitor_geprek'  => (int) ($payload['kompetitor_geprek'] ?? 0),
            'kompetitor_lokal'   => (int) ($payload['kompetitor_lokal']  ?? 0),
            'harga_kompetitor'   => (float) ($payload['harga_kompetitor'] ?? 0),

            'workflow_status' => 'FINAL',
            'catatan'         => $payload['catatan'] ?? null,
            'created_at'      => now(),
            'updated_at'      => now(),
        ], $calc));

        // Update field report dengan kolom yang sekarang sudah ada di DB (GAP #5)
        DB::table('surveyor_field_reports')->where('id', $id)->update([
            'status'               => 'APPROVED_TO_SITE_SCORE',
            'approved_by'          => auth()->user()->name ?? 'SYSTEM',
            'approved_at'          => now(),
            'created_site_score_id' => $siteScoreId,
            'updated_at'           => now(),
        ]);

        // Update status kandidat
        if ($report->candidate_location_id) {
            DB::table('surveyor_candidate_locations')
                ->where('id', $report->candidate_location_id)
                ->update([
                    'status'             => 'CALCULATED',
                    'final_site_score_id' => $siteScoreId,
                    'approved_at'        => now(),
                    'updated_at'         => now(),
                ]);
        }

        // Audit log
        DB::table('surveyor_workflow_logs')->insert([
            'candidate_location_id' => $report->candidate_location_id,
            'field_report_id'       => $report->id,
            'site_score_id'         => $siteScoreId,
            'actor_name'            => auth()->user()->name ?? 'SYSTEM',
            'actor_type'            => 'ADMIN',
            'action'                => 'APPROVE_FIELD_REPORT_TO_SITE_SCORE',
            'note'                  => 'Field report diubah menjadi final Site Score. MoE: ' . ($moe * 100) . '%',
            'payload'               => json_encode($calc),
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        return redirect()
            ->route('investor.surveyor.site-score.detail', $siteScoreId)
            ->with('success', 'Field report berhasil dibuat menjadi Site Score.');
    }
}
