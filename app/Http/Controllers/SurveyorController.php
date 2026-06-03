<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessTrafficDetection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SurveyorController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('site_surveys');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('surveyor_name', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('location_type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('grade')) {
            $query->where('grade', $request->grade);
        }

        if ($request->filled('ai_status')) {
            $query->where('traffic_detection_status', $request->ai_status);
        }

        $surveys = $query
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => DB::table('site_surveys')->count(),
            'priority' => DB::table('site_surveys')->whereIn('grade', ['A', 'B'])->count(),
            'avg_score' => round((float) DB::table('site_surveys')->avg('site_score'), 1),
            'avg_sales' => round((float) DB::table('site_surveys')->avg('estimated_monthly_sales')),
            'valid' => DB::table('site_surveys')->where('status', 'Valid')->count(),
            'revision' => DB::table('site_surveys')->where('status', 'Perlu Revisi')->count(),
            'ai_waiting' => DB::table('site_surveys')->where('traffic_detection_status', 'Waiting')->count(),
            'ai_processing' => DB::table('site_surveys')->where('traffic_detection_status', 'Processing')->count(),
            'ai_done' => DB::table('site_surveys')->where('traffic_detection_status', 'Done')->count(),
            'ai_failed' => DB::table('site_surveys')->where('traffic_detection_status', 'Failed')->count(),
            'ai_cancelled' => DB::table('site_surveys')->where('traffic_detection_status', 'Cancelled')->count(),
        ];

        $gradeChart = DB::table('site_surveys')
            ->select('grade', DB::raw('COUNT(*) as total'))
            ->groupBy('grade')
            ->pluck('total', 'grade');

        $gradeLabels = ['A', 'B', 'C', 'D'];
        $gradeData = [];

        foreach ($gradeLabels as $grade) {
            $gradeData[] = (int) ($gradeChart[$grade] ?? 0);
        }

        $cityRanking = DB::table('site_surveys')
            ->select(
                'city',
                DB::raw('COUNT(*) as total_survey'),
                DB::raw('ROUND(AVG(site_score), 1) as avg_score'),
                DB::raw('ROUND(AVG(estimated_monthly_sales)) as avg_sales')
            )
            ->groupBy('city')
            ->orderByDesc('avg_score')
            ->limit(8)
            ->get();

        $topLocations = DB::table('site_surveys')
            ->orderByDesc('site_score')
            ->orderByDesc('estimated_monthly_sales')
            ->limit(5)
            ->get();

        $mapLocations = DB::table('site_surveys')
            ->select(
                'id',
                'surveyor_name',
                'location_type',
                'city',
                'address',
                'site_score',
                'grade',
                'estimated_monthly_sales',
                'status',
                'latitude',
                'longitude',
                'traffic_detection_status'
            )
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        return view('Investor.Surveyor.surveyor', compact(
            'surveys',
            'stats',
            'gradeLabels',
            'gradeData',
            'cityRanking',
            'topLocations',
            'mapLocations'
        ));
    }

    public function create()
    {
        $survey = (object) [
            'input_method' => 'Laravel Web',
            'status' => 'Draft',
            'traffic_detection_status' => 'Manual',
            'traffic_detection_progress' => 0,
            'detected_motor_traffic' => 0,
            'detected_car_traffic' => 0,
            'detected_pedestrian_traffic' => 0,
        ];

        return view('Investor.Surveyor.form', compact('survey'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $hasNewVideo = $request->hasFile('traffic_video');

        $data = $this->handleVideoUpload($request, $data);
        $data = array_merge($data, $this->calculateScoreAndSales($data));

        $data['created_at'] = now();
        $data['updated_at'] = now();

        $surveyId = DB::table('site_surveys')->insertGetId($data);

        if ($hasNewVideo && !empty($data['traffic_video_path'])) {
            ProcessTrafficDetection::dispatch($surveyId)->onQueue('traffic-detection');
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $hasNewVideo
                    ? 'Survey tersimpan. AI traffic detection masuk antrian Redis.'
                    : 'Survey tersimpan.',
                'redirect' => route('master.surveyor.show', $surveyId),
                'survey_id' => $surveyId,
            ]);
        }

        return redirect()
            ->route('master.surveyor.show', $surveyId)
            ->with(
                'success',
                $hasNewVideo
                ? 'Data survey berhasil dibuat. Video masuk antrian AI traffic detection.'
                : 'Data survey berhasil dibuat.'
            );
    }

    public function show($id)
    {
        $survey = DB::table('site_surveys')->where('id', $id)->first();

        if (!$survey) {
            abort(404);
        }

        return view('Investor.Surveyor.show', compact('survey'));
    }

    public function edit($id)
    {
        $survey = DB::table('site_surveys')->where('id', $id)->first();

        if (!$survey) {
            abort(404);
        }

        return view('Investor.Surveyor.form', compact('survey'));
    }

    public function update(Request $request, $id)
    {
        $survey = DB::table('site_surveys')->where('id', $id)->first();

        if (!$survey) {
            abort(404);
        }

        $data = $this->validatedData($request);
        $hasNewVideo = $request->hasFile('traffic_video');

        $data = $this->handleVideoUpload($request, $data, $survey);

        $mergedForScore = array_merge((array) $survey, $data);
        $data = array_merge($data, $this->calculateScoreAndSales($mergedForScore));

        $data['updated_at'] = now();

        DB::table('site_surveys')->where('id', $id)->update($data);

        if ($hasNewVideo && !empty($data['traffic_video_path'])) {
            ProcessTrafficDetection::dispatch($id)->onQueue('traffic-detection');
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $hasNewVideo
                    ? 'Survey diperbarui. Video baru masuk antrian Redis.'
                    : 'Survey diperbarui.',
                'redirect' => route('master.surveyor.show', $id),
                'survey_id' => $id,
            ]);
        }

        return redirect()
            ->route('master.surveyor.show', $id)
            ->with(
                'success',
                $hasNewVideo
                ? 'Data survey berhasil diperbarui. Video baru masuk antrian AI traffic detection.'
                : 'Data survey berhasil diperbarui.'
            );
    }

    public function destroy($id)
    {
        $survey = DB::table('site_surveys')->where('id', $id)->first();

        if ($survey) {
            if (!empty($survey->traffic_video_path)) {
                Storage::disk('public')->delete($survey->traffic_video_path);
            }

            if (!empty($survey->traffic_result_video_path)) {
                Storage::disk('public')->delete($survey->traffic_result_video_path);
            }
        }

        DB::table('site_surveys')->where('id', $id)->delete();

        return redirect()
            ->route('master.surveyor.index')
            ->with('success', 'Data survey berhasil dihapus.');
    }

    public function detectTraffic($id)
    {
        $survey = DB::table('site_surveys')->where('id', $id)->first();

        if (!$survey) {
            abort(404);
        }

        if (empty($survey->traffic_video_path)) {
            return redirect()
                ->back()
                ->withErrors(['traffic_video' => 'Video traffic belum tersedia.']);
        }

        DB::table('site_surveys')->where('id', $id)->update([
            'traffic_detection_status' => 'Waiting',
            'traffic_detection_progress' => 0,
            'traffic_detection_error' => null,
            'updated_at' => now(),
        ]);

        ProcessTrafficDetection::dispatch($id)->onQueue('traffic-detection');

        return redirect()
            ->back()
            ->with('success', 'AI traffic detection dimasukkan ulang ke antrian Redis.');
    }

    public function cancelTrafficDetection($id)
    {
        $survey = DB::table('site_surveys')->where('id', $id)->first();

        if (!$survey) {
            abort(404);
        }

        if (in_array($survey->traffic_detection_status, ['Done', 'Failed', 'Cancelled', 'Manual'])) {
            return redirect()
                ->back()
                ->with('success', 'Tidak ada proses AI aktif untuk dibatalkan.');
        }

        DB::table('site_surveys')->where('id', $id)->update([
            'traffic_detection_status' => 'Cancelled',
            'traffic_detection_progress' => 0,
            'traffic_detection_error' => 'Proses dibatalkan dari UI.',
            'updated_at' => now(),
        ]);

        return redirect()
            ->back()
            ->with('success', 'Status AI detection dibatalkan. Jika worker sedang berjalan, proses akan berhenti saat job cek status.');
    }

    public function trafficStatus($id)
    {
        $survey = DB::table('site_surveys')
            ->select(
                'id',
                'detected_motor_traffic',
                'detected_car_traffic',
                'detected_pedestrian_traffic',
                'traffic_detection_status',
                'traffic_detection_progress',
                'traffic_detection_error',
                'traffic_video_path',
                'traffic_result_video_path',
                'site_score',
                'grade',
                'estimated_daily_sales',
                'estimated_monthly_sales',
                'updated_at'
            )
            ->where('id', $id)
            ->first();

        if (!$survey) {
            return response()->json([
                'success' => false,
                'message' => 'Survey tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $survey,
        ]);
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'surveyor_name' => 'required|string|max:120',
            'input_method' => 'required|string|max:100',
            'location_type' => 'required|string|max:120',
            'city' => 'required|string|max:120',
            'address' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',

            'motor_weekday_morning' => 'required|integer|min:0',
            'motor_weekday_noon' => 'required|integer|min:0',
            'motor_weekday_evening' => 'required|integer|min:0',
            'motor_weekend_morning' => 'required|integer|min:0',
            'motor_weekend_noon' => 'required|integer|min:0',
            'motor_weekend_evening' => 'required|integer|min:0',

            'pedestrian_weekday_morning' => 'required|integer|min:0',
            'pedestrian_weekday_noon' => 'required|integer|min:0',
            'pedestrian_weekday_evening' => 'required|integer|min:0',
            'pedestrian_weekend_morning' => 'required|integer|min:0',
            'pedestrian_weekend_noon' => 'required|integer|min:0',
            'pedestrian_weekend_evening' => 'required|integer|min:0',

            'houses_north' => 'required|integer|min:0',
            'houses_south' => 'required|integer|min:0',
            'houses_east' => 'required|integer|min:0',
            'houses_west' => 'required|integer|min:0',
            'public_facilities_15km' => 'required|integer|min:0',
            'culinary_centers' => 'required|integer|min:0',
            'competitors' => 'required|integer|min:0',
            'average_price' => 'required|integer|min:0',

            'detected_motor_traffic' => 'nullable|integer|min:0',
            'detected_car_traffic' => 'nullable|integer|min:0',
            'detected_pedestrian_traffic' => 'nullable|integer|min:0',

            'status' => 'required|string|max:50',
            'notes' => 'nullable|string',
            'traffic_video' => 'nullable|file|mimes:mp4,mov,avi,mkv|max:20480',
        ]);
    }

    private function handleVideoUpload(Request $request, array $data, $survey = null): array
    {
        if ($request->hasFile('traffic_video')) {
            if ($survey && !empty($survey->traffic_video_path)) {
                Storage::disk('public')->delete($survey->traffic_video_path);
            }

            if ($survey && !empty($survey->traffic_result_video_path)) {
                Storage::disk('public')->delete($survey->traffic_result_video_path);
            }

            $data['traffic_video_path'] = $request
                ->file('traffic_video')
                ->store('traffic-videos', 'public');

            $data['traffic_result_video_path'] = null;
            $data['traffic_detection_status'] = 'Waiting';
            $data['traffic_detection_progress'] = 0;
            $data['traffic_detection_error'] = null;

            $data['detected_motor_traffic'] = 0;
            $data['detected_car_traffic'] = 0;
            $data['detected_pedestrian_traffic'] = 0;
        } else {
            $data['detected_motor_traffic'] =
                $data['detected_motor_traffic'] ?? ($survey->detected_motor_traffic ?? 0);

            $data['detected_car_traffic'] =
                $data['detected_car_traffic'] ?? ($survey->detected_car_traffic ?? 0);

            $data['detected_pedestrian_traffic'] =
                $data['detected_pedestrian_traffic'] ?? ($survey->detected_pedestrian_traffic ?? 0);

            if ($survey && !empty($survey->traffic_video_path)) {
                $data['traffic_video_path'] = $survey->traffic_video_path;
            }

            if ($survey && !empty($survey->traffic_result_video_path)) {
                $data['traffic_result_video_path'] = $survey->traffic_result_video_path;
            }

            if ($survey && !empty($survey->traffic_detection_status)) {
                $data['traffic_detection_status'] = $survey->traffic_detection_status;
            }

            if ($survey && isset($survey->traffic_detection_progress)) {
                $data['traffic_detection_progress'] = $survey->traffic_detection_progress;
            }

            if ($survey && !empty($survey->traffic_detection_error)) {
                $data['traffic_detection_error'] = $survey->traffic_detection_error;
            }
        }

        unset($data['traffic_video']);

        return $data;
    }

    private function calculateScoreAndSales(array $data): array
    {
        $manualMotor =
            (int) ($data['motor_weekday_morning'] ?? 0) +
            (int) ($data['motor_weekday_noon'] ?? 0) +
            (int) ($data['motor_weekday_evening'] ?? 0) +
            (int) ($data['motor_weekend_morning'] ?? 0) +
            (int) ($data['motor_weekend_noon'] ?? 0) +
            (int) ($data['motor_weekend_evening'] ?? 0);

        $manualPedestrian =
            (int) ($data['pedestrian_weekday_morning'] ?? 0) +
            (int) ($data['pedestrian_weekday_noon'] ?? 0) +
            (int) ($data['pedestrian_weekday_evening'] ?? 0) +
            (int) ($data['pedestrian_weekend_morning'] ?? 0) +
            (int) ($data['pedestrian_weekend_noon'] ?? 0) +
            (int) ($data['pedestrian_weekend_evening'] ?? 0);

        $detectedMotor = (int) ($data['detected_motor_traffic'] ?? 0);
        $detectedCar = (int) ($data['detected_car_traffic'] ?? 0);
        $detectedPedestrian = (int) ($data['detected_pedestrian_traffic'] ?? 0);

        $motor = $manualMotor + $detectedMotor + $detectedCar;
        $pedestrian = $manualPedestrian + $detectedPedestrian;

        $houses =
            (int) ($data['houses_north'] ?? 0) +
            (int) ($data['houses_south'] ?? 0) +
            (int) ($data['houses_east'] ?? 0) +
            (int) ($data['houses_west'] ?? 0);

        $facilities = (int) ($data['public_facilities_15km'] ?? 0);
        $culinary = (int) ($data['culinary_centers'] ?? 0);
        $competitors = (int) ($data['competitors'] ?? 0);
        $price = max(1, (int) ($data['average_price'] ?? 1));

        $trafficScore = min(100, (($motor / 9000) * 60) + (($pedestrian / 4500) * 40));
        $residentialScore = min(100, ($houses / 1200) * 100);
        $facilityScore = min(100, ($facilities / 40) * 100);
        $culinaryScore = min(100, ($culinary / 20) * 100);
        $competitorScore = max(0, 100 - ($competitors * 10));
        $priceScore = $price <= 15000 ? 100 : ($price <= 25000 ? 85 : ($price <= 35000 ? 65 : 45));

        $score = round(
            ($trafficScore * 0.30) +
            ($residentialScore * 0.20) +
            ($facilityScore * 0.15) +
            ($culinaryScore * 0.10) +
            ($competitorScore * 0.15) +
            ($priceScore * 0.10),
            2
        );

        $grade = $score >= 85 ? 'A' : ($score >= 70 ? 'B' : ($score >= 55 ? 'C' : 'D'));

        $estimatedVisitors = max(
            20,
            round(($motor * 0.015) + ($pedestrian * 0.08) + ($houses * 0.03) + ($facilities * 2))
        );

        $conversionRate = $score >= 85 ? 0.17 : ($score >= 70 ? 0.13 : ($score >= 55 ? 0.09 : 0.05));
        $estimatedTransactions = max(5, round($estimatedVisitors * $conversionRate));
        $estimatedDailySales = $estimatedTransactions * $price;

        return [
            'site_score' => $score,
            'grade' => $grade,
            'estimated_daily_sales' => $estimatedDailySales,
            'estimated_monthly_sales' => $estimatedDailySales * 30,
        ];
    }
}
