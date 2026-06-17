<?php

namespace App\Http\Controllers;

use App\Services\Surveyor\SiteScoreCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SurveyorSiteScoreController extends Controller
{
    public function __construct(
        private SiteScoreCalculatorService $calculator
    ) {}

    public function index()
    {
        $scores  = DB::table('surveyor_site_scores')->orderByDesc('id')->get();
        $summary = $this->summary();
        return view('Surveyor.index', compact('scores', 'summary'));
    }

    public function getMasterOutlets()
    {
        $outlets = DB::table('master_outlets')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->select('id', 'nama_outlet', 'kota_kab', 'latitude', 'longitude')
            ->get();
        return response()->json($outlets);
    }

    public function myDrafts()
    {
        $userName = auth()->user()->name ?? '';
        $scores = DB::table('surveyor_site_scores')
            ->where('surveyor', $userName)
            ->whereIn('workflow_status', ['DRAFT', 'REVISION'])
            ->orderByDesc('id')
            ->get();

        return view('Surveyor.my-drafts', compact('scores'));
    }

    public function create(Request $request)
    {
        $candidate = null;
        if ($request->has('candidate_id')) {
            $candidate = DB::table('surveyor_candidate_locations')
                ->where('id', $request->candidate_id)
                ->first();
        }

        $masterBoqs = \App\Models\MasterBoq::where('is_active', true)->get();

        return view('Surveyor.create.create', compact('candidate', 'masterBoqs'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);

        $lat     = $request->latitude  ? (float) $request->latitude  : null;
        $lng     = $request->longitude ? (float) $request->longitude : null;
        $mapsUrl = $request->maps_url;
        if (!$mapsUrl && $lat && $lng) {
            $mapsUrl = 'https://www.google.com/maps?q=' . $lat . ',' . $lng;
        }

        $provinsi   = strtolower($request->provinsi ?? '');
        $kota       = strtolower($request->kota ?? '');
        $isMadura   = str_contains($provinsi, 'madura') || str_contains($kota, 'madura') || 
                      str_contains($kota, 'bangkalan') || str_contains($kota, 'sampang') || 
                      str_contains($kota, 'pamekasan') || str_contains($kota, 'sumenep');
        
        $moe        = $isMadura ? 0.30 : 0.20;
        $tipeOutlet = $request->tipe_outlet ?? 'LDP';
        $avgCheck   = (float) ($request->average_check ?? 21000);
        
        $customConfig = [];
        if ($request->formula_type === 'CUSTOM' && !empty($request->custom_weights_json)) {
            $customConfig = json_decode($request->custom_weights_json, true) ?? [];
        }

        $calc = $this->calculator->calculate(
            array_merge($validated, [
                'average_check'    => $avgCheck,
                'harga_kompetitor' => (float) ($request->harga_kompetitor ?? 0),
                'formula_type'     => $request->formula_type ?? 'DEFAULT',
            ]),
            $moe,
            $tipeOutlet,
            $customConfig
        );

        // Hapus kolom virtual yang hanya untuk view agar tidak error saat disimpan ke DB
        unset(
            $calc['subtotal_perhari'], 
            $calc['moe_percent'], 
            $calc['grand_total_perhari'], 
            $calc['label_outlet'],
            $calc['curve_motor_wd'],
            $calc['curve_motor_we'],
            $calc['curve_pejalan_wd'],
            $calc['curve_pejalan_we'],
            $calc['details_penambah'],
            $calc['details_pengurang']
        );

        $id = DB::table('surveyor_site_scores')->insertGetId(array_merge([
            'kode_score'     => 'SS-' . date('YmdHis'),
            'lokasi'         => $request->lokasi,
            'kota'           => $request->kota,
            'provinsi'       => $request->provinsi,
            'surveyor'       => $request->surveyor,
            'tanggal_survey' => $request->tanggal_survey,
            'latitude'       => $lat,
            'longitude'      => $lng,
            'maps_url'       => $mapsUrl,
            'formula_type'           => $request->formula_type ?? 'DEFAULT',
            'scan_radius_fasum'      => (int)($request->scan_radius_fasum ?? 750),
            'scan_radius_kompetitor' => (int)($request->scan_radius_kompetitor ?? 500),
            'custom_weights_json'    => $request->custom_weights_json,

            'motor_weekday_pagi'    => (int)$request->motor_weekday_pagi,
            'motor_weekday_siang'   => (int)$request->motor_weekday_siang,
            'motor_weekday_sore'    => (int)$request->motor_weekday_sore,
            'motor_weekend_pagi'    => (int)$request->motor_weekend_pagi,
            'motor_weekend_siang'   => (int)$request->motor_weekend_siang,
            'motor_weekend_sore'    => (int)$request->motor_weekend_sore,
            'pejalan_weekday_pagi'  => (int)$request->pejalan_weekday_pagi,
            'pejalan_weekday_siang' => (int)$request->pejalan_weekday_siang,
            'pejalan_weekday_sore'  => (int)$request->pejalan_weekday_sore,
            'pejalan_weekend_pagi'  => (int)$request->pejalan_weekend_pagi,
            'pejalan_weekend_siang' => (int)$request->pejalan_weekend_siang,
            'pejalan_weekend_sore'  => (int)$request->pejalan_weekend_sore,
            'traffic_calibration_json' => $request->traffic_calibration_json,
            'rumah_q1'           => (int)$request->rumah_q1,
            'rumah_q2'           => (int)$request->rumah_q2,
            'rumah_q3'           => (int)$request->rumah_q3,
            'rumah_q4'           => (int)$request->rumah_q4,
            'sekolah'            => (int)$request->sekolah,
            'kampus'             => (int)$request->kampus,
            'market'             => (int)$request->market,
            'perkantoran'        => (int)$request->perkantoran,
            'kesehatan'          => (int)$request->kesehatan,
            'pabrik'             => (int)$request->pabrik,
            'kompetitor_geprek'  => (int)$request->kompetitor_geprek,
            'kompetitor_lokal'   => (int)$request->kompetitor_lokal,
            'jarak_kompetitor'   => (int)$request->jarak_kompetitor,
            'harga_kompetitor'   => (float)($request->harga_kompetitor ?? 0),

            'tipe_bangunan'      => $request->tipe_bangunan,
            'luas_bangunan'      => (int)$request->luas_bangunan,
            'status_sewa_beli'   => $request->status_sewa_beli,
            'harga_sewa'         => (float)$request->harga_sewa,
            'lebar_depan'        => (int)$request->lebar_depan,
            'panjang_bangunan'   => (int)$request->panjang_bangunan,
            'jumlah_lantai'      => (int)$request->jumlah_lantai,
            'kondisi_bangunan'   => $request->kondisi_bangunan,

            'terlihat_jalan_utama'  => (int)$request->terlihat_jalan_utama,
            'posisi_hook'           => (int)$request->posisi_hook,
            'frontage'              => $request->frontage,
            'terhalang_pohon_kabel' => (int)$request->terhalang_pohon_kabel,
            'ruang_signage'         => (int)$request->ruang_signage,
            'penerangan_malam'      => (int)$request->penerangan_malam,
            
            'lebar_jalan'           => (int)$request->lebar_jalan,
            'jenis_jalan'           => $request->jenis_jalan,
            'u_turn_lampu_merah'    => (int)$request->u_turn_lampu_merah,
            'akses_mobil'           => (int)$request->akses_mobil,
            


            'cu_per_hari'           => (float)$request->cu_per_hari,
            'cu_per_minggu'         => (float)$request->cu_per_minggu,
            'cu_per_bulan'          => (float)$request->cu_per_bulan,
            'omset_per_hari'        => (float)$request->omset_per_hari,
            'omset_per_minggu'      => (float)$request->omset_per_minggu,
            'omset_per_bulan'       => (float)$request->omset_per_bulan,

            'rab_renovasi'       => (float)$request->rab_renovasi,
            'rab_kitchen'        => (float)$request->rab_kitchen,
            'rab_signage'        => (float)$request->rab_signage,
            'rab_furniture'      => (float)$request->rab_furniture,
            'rab_listrik'        => (float)$request->rab_listrik,
            'rab_air'            => (float)$request->rab_air,
            'rab_exhaust'        => (float)$request->rab_exhaust,
            'rab_ac_kipas'       => (float)$request->rab_ac_kipas,
            'rab_perizinan'      => (float)$request->rab_perizinan,
            'rab_deposit_sewa'   => (float)$request->rab_deposit_sewa,
            'rab_biaya_opening'  => (float)$request->rab_biaya_opening,

            'kelebihan_lokasi'   => $request->kelebihan_lokasi,
            'kekurangan_lokasi'  => $request->kekurangan_lokasi,
            'risiko'             => $request->risiko,

            'building_score'     => $this->calcBuildingScore($request),
            'visibility_score'   => $this->calcVisibilityScore($request),
            'access_score'       => $this->calcAccessScore($request),
            'environment_score'  => $this->calcEnvironmentScore($request),
            'competitor_score'   => $this->calcCompetitorScore($request),
            'traffic_score'      => $this->calcTrafficScore($request),
            'rab_score'          => $this->calcRabScore($request),

            'workflow_status'    => $request->action_type === 'draft' ? 'DRAFT' : 'PENDING',
            'catatan'            => $request->catatan,
            'created_at'         => now(),
            'updated_at'         => now(),
        ], $calc));

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                if (!$file || !$file->isValid()) continue;
                $path = $file->store('surveyor/site-score/photos', 'public');
                DB::table('surveyor_site_score_photos')->insert([
                    'site_score_id' => $id,
                    'path'          => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }
        }

        if ($request->has('candidate_id')) {
            DB::table('surveyor_candidate_locations')
                ->where('id', $request->candidate_id)
                ->update(['status' => 'SURVEYED', 'updated_at' => now()]);
        }

        return redirect()
            ->route('investor.surveyor.site-score.detail', $id)
            ->with('success', 'Site score berhasil disimpan.');
    }

    public function edit($id)
    {
        $score = DB::table('surveyor_site_scores')->where('id', $id)->first();
        if (!$score) return redirect()->back()->with('error', 'Data tidak ditemukan.');
        $photos = DB::table('surveyor_site_score_photos')->where('site_score_id', $id)->get();
        $masterBoqs = \App\Models\MasterBoq::where('is_active', true)->get();
        return view('Surveyor.create.edit', compact('score', 'photos', 'masterBoqs'));
    }

    public function update(Request $request, $id)
    {
        $validated = $this->validateRequest($request);

        $lat     = $request->latitude  ? (float) $request->latitude  : null;
        $lng     = $request->longitude ? (float) $request->longitude : null;
        $mapsUrl = $request->maps_url;
        if (!$mapsUrl && $lat && $lng) {
            $mapsUrl = 'https://www.google.com/maps?q=' . $lat . ',' . $lng;
        }

        $provinsi   = strtolower($request->provinsi ?? '');
        $kota       = strtolower($request->kota ?? '');
        $isMadura   = str_contains($provinsi, 'madura') || str_contains($kota, 'madura') || 
                      str_contains($kota, 'bangkalan') || str_contains($kota, 'sampang') || 
                      str_contains($kota, 'pamekasan') || str_contains($kota, 'sumenep');
        
        $moe        = $isMadura ? 0.30 : 0.20;
        $tipeOutlet = $request->tipe_outlet ?? 'LDP';
        $avgCheck   = (float) ($request->average_check ?? 21000);
        
        $customConfig = [];
        if ($request->formula_type === 'CUSTOM' && !empty($request->custom_weights_json)) {
            $customConfig = json_decode($request->custom_weights_json, true) ?? [];
        }

        $calc = $this->calculator->calculate(
            array_merge($validated, [
                'average_check'    => $avgCheck,
                'harga_kompetitor' => (float) ($request->harga_kompetitor ?? 0),
                'formula_type'     => $request->formula_type ?? 'DEFAULT',
            ]),
            $moe,
            $tipeOutlet,
            $customConfig
        );

        // Hapus kolom virtual yang hanya untuk view agar tidak error saat disimpan ke DB
        unset(
            $calc['subtotal_perhari'], 
            $calc['moe_percent'], 
            $calc['grand_total_perhari'], 
            $calc['label_outlet'],
            $calc['curve_motor_wd'],
            $calc['curve_motor_we'],
            $calc['curve_pejalan_wd'],
            $calc['curve_pejalan_we'],
            $calc['details_penambah'],
            $calc['details_pengurang']
        );

        DB::table('surveyor_site_scores')->where('id', $id)->update(array_merge([
            'lokasi'         => $request->lokasi,
            'kota'           => $request->kota,
            'provinsi'       => $request->provinsi,
            'surveyor'       => $request->surveyor,
            'tanggal_survey' => $request->tanggal_survey,
            'latitude'       => $lat,
            'longitude'      => $lng,
            'maps_url'       => $mapsUrl,
            'formula_type'           => $request->formula_type ?? 'DEFAULT',
            'scan_radius_fasum'      => (int)($request->scan_radius_fasum ?? 750),
            'scan_radius_kompetitor' => (int)($request->scan_radius_kompetitor ?? 500),
            'custom_weights_json'    => $request->custom_weights_json,

            'motor_weekday_pagi'    => (int)$request->motor_weekday_pagi,
            'motor_weekday_siang'   => (int)$request->motor_weekday_siang,
            'motor_weekday_sore'    => (int)$request->motor_weekday_sore,
            'motor_weekend_pagi'    => (int)$request->motor_weekend_pagi,
            'motor_weekend_siang'   => (int)$request->motor_weekend_siang,
            'motor_weekend_sore'    => (int)$request->motor_weekend_sore,
            'pejalan_weekday_pagi'  => (int)$request->pejalan_weekday_pagi,
            'pejalan_weekday_siang' => (int)$request->pejalan_weekday_siang,
            'pejalan_weekday_sore'  => (int)$request->pejalan_weekday_sore,
            'pejalan_weekend_pagi'  => (int)$request->pejalan_weekend_pagi,
            'pejalan_weekend_siang' => (int)$request->pejalan_weekend_siang,
            'pejalan_weekend_sore'  => (int)$request->pejalan_weekend_sore,
            'traffic_calibration_json' => $request->traffic_calibration_json,
            'rumah_q1'           => (int)$request->rumah_q1,
            'rumah_q2'           => (int)$request->rumah_q2,
            'rumah_q3'           => (int)$request->rumah_q3,
            'rumah_q4'           => (int)$request->rumah_q4,
            'sekolah'            => (int)$request->sekolah,
            'kampus'             => (int)$request->kampus,
            'market'             => (int)$request->market,
            'perkantoran'        => (int)$request->perkantoran,
            'kesehatan'          => (int)$request->kesehatan,
            'pabrik'             => (int)$request->pabrik,
            'kompetitor_geprek'  => (int)$request->kompetitor_geprek,
            'kompetitor_lokal'   => (int)$request->kompetitor_lokal,
            'jarak_kompetitor'   => (int)$request->jarak_kompetitor,
            'harga_kompetitor'   => (float)($request->harga_kompetitor ?? 0),

            'tipe_bangunan'      => $request->tipe_bangunan,
            'luas_bangunan'      => (int)$request->luas_bangunan,
            'status_sewa_beli'   => $request->status_sewa_beli,
            'harga_sewa'         => (float)$request->harga_sewa,
            'lebar_depan'        => (int)$request->lebar_depan,
            'panjang_bangunan'   => (int)$request->panjang_bangunan,
            'jumlah_lantai'      => (int)$request->jumlah_lantai,
            'kondisi_bangunan'   => $request->kondisi_bangunan,

            'terlihat_jalan_utama'  => (int)$request->terlihat_jalan_utama,
            'posisi_hook'           => (int)$request->posisi_hook,
            'frontage'              => $request->frontage,
            'terhalang_pohon_kabel' => (int)$request->terhalang_pohon_kabel,
            'ruang_signage'         => (int)$request->ruang_signage,
            'penerangan_malam'      => (int)$request->penerangan_malam,
            
            'lebar_jalan'           => (int)$request->lebar_jalan,
            'jenis_jalan'           => $request->jenis_jalan,
            'u_turn_lampu_merah'    => (int)$request->u_turn_lampu_merah,
            'akses_mobil'           => (int)$request->akses_mobil,
            


            'cu_per_hari'           => (float)$request->cu_per_hari,
            'cu_per_minggu'         => (float)$request->cu_per_minggu,
            'cu_per_bulan'          => (float)$request->cu_per_bulan,
            'omset_per_hari'        => (float)$request->omset_per_hari,
            'omset_per_minggu'      => (float)$request->omset_per_minggu,
            'omset_per_bulan'       => (float)$request->omset_per_bulan,

            'rab_renovasi'       => (float)$request->rab_renovasi,
            'rab_kitchen'        => (float)$request->rab_kitchen,
            'rab_signage'        => (float)$request->rab_signage,
            'rab_furniture'      => (float)$request->rab_furniture,
            'rab_listrik'        => (float)$request->rab_listrik,
            'rab_air'            => (float)$request->rab_air,
            'rab_exhaust'        => (float)$request->rab_exhaust,
            'rab_ac_kipas'       => (float)$request->rab_ac_kipas,
            'rab_perizinan'      => (float)$request->rab_perizinan,
            'rab_deposit_sewa'   => (float)$request->rab_deposit_sewa,
            'rab_biaya_opening'  => (float)$request->rab_biaya_opening,

            'kelebihan_lokasi'   => $request->kelebihan_lokasi,
            'kekurangan_lokasi'  => $request->kekurangan_lokasi,
            'risiko'             => $request->risiko,

            'building_score'     => $this->calcBuildingScore($request),
            'visibility_score'   => $this->calcVisibilityScore($request),
            'access_score'       => $this->calcAccessScore($request),
            'environment_score'  => $this->calcEnvironmentScore($request),
            'competitor_score'   => $this->calcCompetitorScore($request),
            'traffic_score'      => $this->calcTrafficScore($request),
            'rab_score'          => $this->calcRabScore($request),

            'workflow_status'    => $request->action_type === 'draft' ? 'DRAFT' : 'PENDING',
            'catatan'            => $request->catatan,
            'updated_at'         => now(),
        ], $calc));

        if ($request->hasFile('photos')) {
            DB::table('surveyor_site_score_photos')->where('site_score_id', $id)->delete();
            foreach ($request->file('photos') as $file) {
                if (!$file || !$file->isValid()) continue;
                $path = $file->store('surveyor/site-score/photos', 'public');
                DB::table('surveyor_site_score_photos')->insert([
                    'site_score_id' => $id,
                    'path'          => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }
        }

        return redirect()
            ->route('investor.surveyor.site-score.detail', $id)
            ->with('success', 'Site score berhasil diperbarui.');
    }

    public function detail($id)
    {
        $score  = DB::table('surveyor_site_scores')->where('id', $id)->first();
        $photos = DB::table('surveyor_site_score_photos')->where('site_score_id', $id)->get();
        
        $provinsi   = strtolower($score->provinsi ?? '');
        $kota       = strtolower($score->kota ?? '');
        $isMadura   = str_contains($provinsi, 'madura') || str_contains($kota, 'madura') || 
                      str_contains($kota, 'bangkalan') || str_contains($kota, 'sampang') || 
                      str_contains($kota, 'pamekasan') || str_contains($kota, 'sumenep');
        
        $moe        = $isMadura ? 0.30 : 0.20;
        $tipeOutlet = $score->tipe_outlet ?? 'LDP';
        
        $customConfig = [];
        if (($score->formula_type ?? 'DEFAULT') === 'CUSTOM' && !empty($score->custom_weights_json)) {
            $customConfig = json_decode($score->custom_weights_json, true) ?? [];
        }
        
        // Re-calculate to get the full curves
        $scoreArray = json_decode(json_encode($score), true);
        $calcData = $this->calculator->calculate($scoreArray, $moe, $tipeOutlet, $customConfig);
        
        return view('Surveyor.detail', compact('score', 'photos', 'calcData'));
    }

    public function ranking()
    {
        $scores = DB::table('surveyor_site_scores')->orderByDesc('final_percent')->get();
        return view('Surveyor.ranking', compact('scores'));
    }

    public function map()
    {
        $scores = DB::table('surveyor_site_scores')
            ->whereNotNull('latitude')->whereNotNull('longitude')
            ->orderByDesc('id')->get();
        return view('Surveyor.map', compact('scores'));
    }

    private function calcBuildingScore(Request $request): float
    {
        $score = 50; // base
        if ($request->luas_bangunan > 100) $score += 20;
        elseif ($request->luas_bangunan > 50) $score += 10;
        if ($request->harga_sewa < 50000000 && $request->harga_sewa > 0) $score += 20;
        if ($request->status_sewa_beli === 'Sewa') $score += 10;
        return min(100, $score);
    }

    private function calcVisibilityScore(Request $request): float
    {
        $score = 50;
        if ($request->terlihat_jalan_utama == 1) $score += 20;
        if ($request->posisi_hook == 1) $score += 10;
        if ($request->frontage >= 5) $score += 10;
        if ($request->terhalang_pohon_kabel == 0) $score += 5;
        if ($request->ruang_signage == 1) $score += 5;
        return min(100, $score);
    }

    private function calcAccessScore(Request $request): float
    {
        $score = 50;
        if ($request->akses_mobil == 1) $score += 20;
        if ($request->lebar_jalan > 6) $score += 10;
        if ($request->jenis_jalan === '2 Arah') $score += 10;
        if ($request->u_turn_lampu_merah == 1) $score += 10;
        return min(100, $score);
    }

    private function calcEnvironmentScore(Request $request): float
    {
        $score = 50;
        if ($request->rumah_q1 > 500) $score += 20;
        if ($request->sekolah > 5 || $request->kampus > 0) $score += 15;
        if ($request->market > 1) $score += 10;
        if ($request->pabrik > 0) $score += 5;
        return min(100, $score);
    }

    private function calcCompetitorScore(Request $request): float
    {
        $score = 100;
        if ($request->kompetitor_geprek > 0) $score -= ($request->kompetitor_geprek * 10);
        if ($request->kompetitor_lokal > 5) $score -= 10;
        if ($request->jarak_kompetitor > 0 && $request->jarak_kompetitor < 100) $score -= 15;
        return max(0, min(100, $score));
    }

    private function calcTrafficScore(Request $request): float
    {
        $motor = array_sum([
            (int)$request->motor_weekday_pagi, (int)$request->motor_weekday_siang, (int)$request->motor_weekday_sore,
            (int)$request->motor_weekend_pagi, (int)$request->motor_weekend_siang, (int)$request->motor_weekend_sore
        ]);
        $score = 40;
        if ($motor > 20000) $score = 100;
        elseif ($motor > 10000) $score = 80;
        elseif ($motor > 5000) $score = 60;
        return $score;
    }

    private function calcRabScore(Request $request): float
    {
        $totalRab = (float)$request->rab_renovasi + (float)$request->rab_kitchen + (float)$request->rab_signage
            + (float)$request->rab_furniture + (float)$request->rab_listrik + (float)$request->rab_air
            + (float)$request->rab_exhaust + (float)$request->rab_ac_kipas + (float)$request->rab_perizinan
            + (float)$request->rab_deposit_sewa + (float)$request->rab_biaya_opening;

        if ($totalRab == 0) return 50; // no data
        if ($totalRab < 75000000) return 100;
        if ($totalRab < 150000000) return 80;
        if ($totalRab < 200000000) return 60;
        return 40;
    }

    public function rekap()
    {
        $summary    = $this->summary();
        $bySurveyor = DB::table('surveyor_site_scores')
            ->selectRaw("surveyor, COUNT(*) as total_survey, AVG(final_percent) as avg_score")
            ->selectRaw("SUM(CASE WHEN rekomendasi='APPROVED' THEN 1 ELSE 0 END) as approved")
            ->selectRaw("SUM(CASE WHEN rekomendasi='CONSIDERATION' THEN 1 ELSE 0 END) as consideration")
            ->selectRaw("SUM(CASE WHEN rekomendasi='REJECTED' THEN 1 ELSE 0 END) as rejected")
            ->groupBy('surveyor')->orderByDesc('total_survey')->get();
        return view('Surveyor.rekap', compact('summary', 'bySurveyor'));
    }

    public function trialPengamatan() { return view('Surveyor.trial-pengamatan'); }
    public function byOutlet()
    {
        $outlets = DB::table('tbl_outlets')->limit(300)->get();
        return view('Surveyor.by-outlet', compact('outlets'));
    }

    public function approvalBoard()
    {
        $scores = DB::table('surveyor_site_scores')->orderByDesc('id')->get();
        return view('Surveyor.approval', compact('scores'));
    }

    public function updateApproval(Request $request, int $id)
    {
        $request->validate([
            'workflow_status' => 'required|in:PENDING,APPROVED,REJECTED,REVISION'
        ]);

        DB::table('surveyor_site_scores')
            ->where('id', $id)
            ->update([
                'workflow_status' => $request->workflow_status,
                'approval_note'   => $request->approval_note,
                'approved_by'     => auth()->user()->name ?? 'Manager',
                'updated_at'      => now(),
            ]);

        return back()->with('success', 'Status persetujuan berhasil disimpan: ' . $request->workflow_status);
    }

    public function comparison()
    {
        $scores = DB::table('surveyor_site_scores')->orderByDesc('final_percent')->get();
        return view('Surveyor.comparison', compact('scores'));
    }

    private function summary(): object
    {
        return (object)[
            'total_survey'  => DB::table('surveyor_site_scores')->count(),
            'approved'      => DB::table('surveyor_site_scores')->where('rekomendasi','APPROVED')->count(),
            'consideration' => DB::table('surveyor_site_scores')->where('rekomendasi','CONSIDERATION')->count(),
            'rejected'      => DB::table('surveyor_site_scores')->where('rekomendasi','REJECTED')->count(),
            'avg_score'     => DB::table('surveyor_site_scores')->avg('final_percent') ?? 0,
            'max_score'     => DB::table('surveyor_site_scores')->max('final_percent') ?? 0,
            'avg_omset'     => DB::table('surveyor_site_scores')->avg('potensi_omset_perhari') ?? 0,
        ];
    }

    public function resolveMapsUrl(Request $request)
    {
        $url = $request->query('url');
        if (!$url) {
            return response()->json(['error' => 'URL is required'], 400);
        }

        try {
            // Gunakan Guzzle dengan track_redirects agar kita bisa melacak semua URL pengalihan
            $client = new \GuzzleHttp\Client([
                'timeout' => 15,
                'connect_timeout' => 10,
                'allow_redirects' => [
                    'max'             => 10,
                    'strict'          => false,
                    'referer'         => true,
                    'protocols'       => ['http', 'https'],
                    'track_redirects' => true
                ],
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36'
                ]
            ]);
            
            $response = $client->get($url);
            
            // Ambil semua riwayat URL (termasuk awal, menengah, dan akhir)
            $urls = $response->getHeader('X-Guzzle-Redirect-History');
            $urls[] = $url; // Tambahkan url awal
            
            $finalUrl = (string) $response->getBody()->getMetadata('uri');
            if ($finalUrl) $urls[] = $finalUrl;
            if (method_exists($response, 'getEffectiveUrl')) {
                $urls[] = $response->getEffectiveUrl();
            }

            // Loop melalui setiap URL yang dilewati untuk mencari koordinat
            foreach ($urls as $u) {
                // Pola Utama: PIN EXACT COORDINATE dari parameter pb=!3dLat!4dLng (bisa URL encoded atau tidak)
                if (preg_match('/(?:!|%21)3d(-?\d+\.\d+)(?:!|%21)4d(-?\d+\.\d+)/', $u, $matches)) {
                    return response()->json(['lat' => $matches[1], 'lng' => $matches[2]]);
                }
                
                $u_decoded = urldecode($u); // Ini akan mengubah %2C menjadi , dan + menjadi spasi
                // Pola 1: /@lat,lng
                if (preg_match('/@(-?\d+\.\d+)[,\s]+(-?\d+\.\d+)/', $u_decoded, $matches)) {
                    return response()->json(['lat' => $matches[1], 'lng' => $matches[2]]);
                }
                // Pola 2: ?q=lat,lng atau &q=lat,lng
                if (preg_match('/[?&]q=(-?\d+\.\d+)[,\s]+(-?\d+\.\d+)/', $u_decoded, $matches)) {
                    return response()->json(['lat' => $matches[1], 'lng' => $matches[2]]);
                }
                // Pola 3: ?ll=lat,lng atau &ll=lat,lng
                if (preg_match('/[?&]ll=(-?\d+\.\d+)[,\s]+(-?\d+\.\d+)/', $u_decoded, $matches)) {
                    return response()->json(['lat' => $matches[1], 'lng' => $matches[2]]);
                }
            }

            // Jika dari URL tidak dapat, kita bongkar body HTML-nya
            $body = (string) $response->getBody();
            
            // Cari PIN Exact Coordinate di dalam HTML (biasanya di tag meta url atau script)
            if (preg_match('/(?:!|%21)3d(-?\d+\.\d+)(?:!|%21)4d(-?\d+\.\d+)/', $body, $matches)) {
                return response()->json(['lat' => $matches[1], 'lng' => $matches[2]]);
            }
            
            // Seringkali Google Maps menaruhnya di tag <link href="...q=-7.95%2C+112.77...">
            if (preg_match('/q=(-?\d+\.\d+)(?:%2C|,)(?:\+|%20|\s)*(-?\d+\.\d+)/', $body, $m)) {
                return response()->json(['lat' => $m[1], 'lng' => $m[2]]);
            }
            
            // Cari di meta property og:image center=lat,lng (Fallback ke Viewport Center)
            if (preg_match('/center=(-?\d+\.\d+)(?:%2C|,)(-?\d+\.\d+)/', $body, $metaMatches)) {
                // Pastikan bukan fallback koordinat US (37.0625, -95.677068)
                if (abs($metaMatches[1]) != 37.0625) {
                    return response()->json(['lat' => $metaMatches[1], 'lng' => $metaMatches[2]]);
                }
            }
            
            // Cari di window.APP_INITIALIZATION_STATE
            if (preg_match('/\[\[\[(-?\d+\.\d+),(-?\d+\.\d+)\]/', $body, $appMatches)) {
                $v1 = (float) $appMatches[1];
                $v2 = (float) $appMatches[2];
                if (abs($v1) > 90) {
                    return response()->json(['lat' => $v2, 'lng' => $v1]);
                }
                return response()->json(['lat' => $v1, 'lng' => $v2]);
            }

            return response()->json(['error' => 'Could not extract coordinates', 'final_url' => $finalUrl], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function scanNearbyPlaces(Request $request)
    {
        $lat = (float) $request->input('lat');
        $lng = (float) $request->input('lng');
        $radiusFasum = (float) $request->input('radius_fasum', 750);
        $radiusKompetitor = (float) $request->input('radius_kompetitor', 500);

        if (!$lat || !$lng) {
            return response()->json(['error' => 'Latitude and longitude are required'], 400);
        }

        $apiKey = env('GOOGLE_MAPS_API_KEY');
        if (!$apiKey) {
            return response()->json(['error' => 'Google Maps API Key is missing on the server.'], 500);
        }

        $client = new \GuzzleHttp\Client(['timeout' => 15]);

        $results = [
            'sekolah' => 0,
            'kesehatan' => 0,
            'market' => 0,
            'kompetitor_geprek' => 0,
            'raksasa_ojol' => 0,
            'raksasa_ojol_list' => []
        ];

        $headers = [
            'X-Goog-Api-Key' => $apiKey,
            'X-Goog-FieldMask' => 'places.id,places.userRatingCount,places.displayName',
            'Content-Type' => 'application/json',
            'Referer' => 'https://report.geprekincloud.tech/' // Menambahkan referer agar lolos dari Website Restriction Google
        ];

        // 1. Scan for Schools (Places API New)
        try {
            $payload = [
                'includedTypes' => ['school', 'university'],
                'maxResultCount' => 20,
                'locationRestriction' => [
                    'circle' => [
                        'center' => ['latitude' => $lat, 'longitude' => $lng],
                        'radius' => $radiusFasum
                    ]
                ]
            ];
            $res = $client->post("https://places.googleapis.com/v1/places:searchNearby", [
                'headers' => $headers,
                'json' => $payload
            ]);
            $data = json_decode($res->getBody(), true);
            if (isset($data['places'])) $results['sekolah'] = count($data['places']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Google API Error: ' . $e->getMessage()], 500);
        }

        // 2. Scan for Health
        try {
            $payload = [
                'includedTypes' => ['hospital', 'pharmacy', 'medical_clinic'],
                'maxResultCount' => 20,
                'locationRestriction' => [
                    'circle' => [
                        'center' => ['latitude' => $lat, 'longitude' => $lng],
                        'radius' => $radiusFasum
                    ]
                ]
            ];
            $res = $client->post("https://places.googleapis.com/v1/places:searchNearby", [
                'headers' => $headers,
                'json' => $payload
            ]);
            $data = json_decode($res->getBody(), true);
            if (isset($data['places'])) $results['kesehatan'] = count($data['places']);
        } catch (\Exception $e) {}

        // 3. Scan for Markets
        try {
            $payload = [
                'includedTypes' => ['supermarket', 'convenience_store', 'department_store'],
                'maxResultCount' => 20,
                'locationRestriction' => [
                    'circle' => [
                        'center' => ['latitude' => $lat, 'longitude' => $lng],
                        'radius' => $radiusFasum
                    ]
                ]
            ];
            $res = $client->post("https://places.googleapis.com/v1/places:searchNearby", [
                'headers' => $headers,
                'json' => $payload
            ]);
            $data = json_decode($res->getBody(), true);
            if (isset($data['places'])) $results['market'] = count($data['places']);
        } catch (\Exception $e) {}

        // 4. Scan for Competitors (Text Search API New)
        try {
            $payload = [
                'textQuery' => 'ayam geprek fried chicken kfc mcd',
                'locationBias' => [
                    'circle' => [
                        'center' => ['latitude' => $lat, 'longitude' => $lng],
                        'radius' => $radiusKompetitor
                    ]
                ]
            ];
            $res = $client->post("https://places.googleapis.com/v1/places:searchText", [
                'headers' => $headers,
                'json' => $payload
            ]);
            $data = json_decode($res->getBody(), true);
            if (isset($data['places'])) $results['kompetitor_geprek'] = count($data['places']);
        } catch (\Exception $e) {}

        // 5. Scan for F&B Hotzone (Raksasa Ojol) di radius 2000m
        try {
            $payload = [
                'includedTypes' => ['restaurant', 'cafe', 'fast_food_restaurant'],
                'maxResultCount' => 20,
                'locationRestriction' => [
                    'circle' => [
                        'center' => ['latitude' => $lat, 'longitude' => $lng],
                        'radius' => 2000 // Radius 2KM khusus untuk deteksi Hotzone
                    ]
                ]
            ];
            $res = $client->post("https://places.googleapis.com/v1/places:searchNearby", [
                'headers' => $headers,
                'json' => $payload
            ]);
            $data = json_decode($res->getBody(), true);
            $hotzoneCount = 0;
            $hotzoneList = [];
            if (isset($data['places'])) {
                foreach ($data['places'] as $place) {
                    $ratingCount = isset($place['userRatingCount']) ? (int) $place['userRatingCount'] : 0;
                    if ($ratingCount > 1000) { // Threshold > 1000 ulasan
                        $hotzoneCount++;
                        $hotzoneList[] = [
                            'name' => isset($place['displayName']['text']) ? $place['displayName']['text'] : 'Restoran Besar',
                            'reviews' => $ratingCount
                        ];
                    }
                }
            }
            $results['raksasa_ojol'] = $hotzoneCount;
            $results['raksasa_ojol_list'] = $hotzoneList;
        } catch (\Exception $e) {}

        return response()->json($results);
    }

    public function heatmap()
    {
        $scores = DB::table('surveyor_site_scores')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();
            
        $envPath = base_path('.env');
        $googleMapsApiKey = '';
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);
            if (preg_match('/^GOOGLE_MAPS_API_KEY=(.*)$/m', $envContent, $matches)) {
                $googleMapsApiKey = trim($matches[1]);
            }
        }

        return view('Surveyor.laporan.heatmap', compact('scores', 'googleMapsApiKey'));
    }

    public function trafficAnalytics()
    {
        $scores = DB::table('surveyor_site_scores')
            ->orderBy('id', 'desc')
            ->get();

        $trafficData = [];
        $timeData = [
            'pagi' => 0,
            'siang' => 0,
            'sore' => 0
        ];

        foreach ($scores as $s) {
            $totalMotor = (int)$s->motor_weekday_pagi + (int)$s->motor_weekday_siang + (int)$s->motor_weekday_sore +
                          (int)$s->motor_weekend_pagi + (int)$s->motor_weekend_siang + (int)$s->motor_weekend_sore;
            $totalPejalan = (int)$s->pejalan_weekday_pagi + (int)$s->pejalan_weekday_siang + (int)$s->pejalan_weekday_sore +
                            (int)$s->pejalan_weekend_pagi + (int)$s->pejalan_weekend_siang + (int)$s->pejalan_weekend_sore;
            
            $trafficData[] = [
                'lokasi' => $s->lokasi,
                'total_motor' => $totalMotor,
                'total_pejalan' => $totalPejalan,
                'total_all' => $totalMotor + $totalPejalan
            ];

            // Time analytics
            $timeData['pagi'] += (int)$s->motor_weekday_pagi + (int)$s->motor_weekend_pagi + (int)$s->pejalan_weekday_pagi + (int)$s->pejalan_weekend_pagi;
            $timeData['siang'] += (int)$s->motor_weekday_siang + (int)$s->motor_weekend_siang + (int)$s->pejalan_weekday_siang + (int)$s->pejalan_weekend_siang;
            $timeData['sore'] += (int)$s->motor_weekday_sore + (int)$s->motor_weekend_sore + (int)$s->pejalan_weekday_sore + (int)$s->pejalan_weekend_sore;
        }

        usort($trafficData, function($a, $b) {
            return $b['total_all'] <=> $a['total_all'];
        });

        $top10 = array_slice($trafficData, 0, 10);

        return view('Surveyor.laporan.traffic', compact('top10', 'trafficData', 'timeData'));
    }

    private function validateRequest(Request $request): array
    {
        $isDraft = $request->action_type === 'draft';
        $reqRule = $isDraft ? 'nullable' : 'required';
        $intRule = $isDraft ? 'nullable|integer|min:0' : 'required|integer|min:0';

        return $request->validate([
            'lokasi'         => 'required|string|max:255',
            'kota'           => 'required|string|max:150',
            'provinsi'       => 'nullable|string|max:150',
            'surveyor'       => 'required|string|max:150',
            'tanggal_survey' => 'required|date',
            'tipe_outlet'    => 'nullable|in:LDP,BDP',
            'latitude'       => 'nullable|numeric',
            'longitude'      => 'nullable|numeric',
            'maps_url'       => 'nullable|string',
            'average_check'  => 'nullable|numeric|min:1000',
            
            // Traffic
            'motor_weekday_pagi'    => $intRule,
            'motor_weekday_siang'   => $intRule,
            'motor_weekday_sore'    => $intRule,
            'motor_weekend_pagi'    => $intRule,
            'motor_weekend_siang'   => $intRule,
            'motor_weekend_sore'    => $intRule,
            'pejalan_weekday_pagi'  => $intRule,
            'pejalan_weekday_siang' => $intRule,
            'pejalan_weekday_sore'  => $intRule,
            'pejalan_weekend_pagi'  => $intRule,
            'pejalan_weekend_siang' => $intRule,
            'pejalan_weekend_sore'  => $intRule,
            
            // Fasilitas
            'rumah_q1'          => $intRule,
            'rumah_q2'          => $intRule,
            'rumah_q3'          => $intRule,
            'rumah_q4'          => $intRule,
            'sekolah'           => $intRule,
            'market'            => $intRule,
            'perkantoran'       => $intRule,
            'kesehatan'         => $intRule,
            
            // Kompetitor
            'kompetitor_geprek' => $intRule,
            'kompetitor_lokal'  => $intRule,
            'harga_kompetitor'  => 'nullable|numeric|min:0',
            
            // Data Bangunan
            'tipe_bangunan'     => 'nullable|string|max:150',
            'luas_bangunan'     => 'nullable|numeric|min:0',
            'status_sewa_beli'  => 'nullable|string|max:150',
            'harga_sewa'        => 'nullable|numeric|min:0',
            'lebar_depan'       => 'nullable|numeric|min:0',
            'panjang_bangunan'  => 'nullable|numeric|min:0',
            'jumlah_lantai'     => 'nullable|numeric|min:0',
            'kondisi_bangunan'  => 'nullable|string|max:150',
            
            // Visibilitas & Akses
            'terlihat_jalan_utama'  => 'nullable|boolean',
            'posisi_hook'           => 'nullable|boolean',
            'frontage'              => 'nullable|numeric|min:0',
            'terhalang_pohon_kabel' => 'nullable|boolean',
            'ruang_signage'         => 'nullable|boolean',
            'penerangan_malam'      => 'nullable|boolean',
            'lebar_jalan'           => 'nullable|numeric|min:0',
            'jenis_jalan'           => 'nullable|string|max:150',
            'u_turn_lampu_merah'    => 'nullable|boolean',
            'akses_mobil'           => 'nullable|boolean',
            
            // RAB & Catatan
            'rab_renovasi'      => 'nullable|numeric|min:0',
            'rab_kitchen'       => 'nullable|numeric|min:0',
            'rab_signage'       => 'nullable|numeric|min:0',
            'rab_furniture'     => 'nullable|numeric|min:0',
            'rab_listrik'       => 'nullable|numeric|min:0',
            'rab_air'           => 'nullable|numeric|min:0',
            'rab_exhaust'       => 'nullable|numeric|min:0',
            'rab_ac_kipas'      => 'nullable|numeric|min:0',
            'rab_perizinan'     => 'nullable|numeric|min:0',
            'rab_deposit_sewa'  => 'nullable|numeric|min:0',
            'rab_biaya_opening' => 'nullable|numeric|min:0',
            
            'kelebihan_lokasi'  => 'nullable|string',
            'kekurangan_lokasi' => 'nullable|string',
            'risiko'            => 'nullable|string',
            'catatan'           => 'nullable|string',
            'photos.*'          => 'nullable|image|max:5120',
        ]);
    }
}