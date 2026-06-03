<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessVideoDetectionJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class SurveyorVideoDetectionController extends Controller
{
    public function index()
    {
        $savedResults = DB::table('surveyor_video_detections')
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        $candidates = DB::table('surveyor_candidate_locations')
            ->orderByDesc('id')
            ->limit(300)
            ->get();

        return view('Surveyor.video-detection.video-detection', compact('savedResults', 'candidates'));
    }

    public function submit(Request $request)
    {
        $request->validate([
            'candidate_location_id' => ['nullable', 'integer'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'source_type' => ['required', 'in:upload,google_drive'],
            'video_file' => ['nullable', 'file', 'mimes:mp4,mov,avi,mkv,webm', 'max:512000'],
            'google_drive_url' => ['nullable', 'url'],
        ]);

        $candidate = null;

        if ($request->filled('candidate_location_id')) {
            $candidate = DB::table('surveyor_candidate_locations')
                ->where('id', $request->candidate_location_id)
                ->first();
        }

        $lokasi = $request->lokasi ?: ($candidate->nama_lokasi ?? null);
        $jobId = 'VD-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6));
        $tempDir = storage_path('app/tmp/video-detection');

        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $videoPath = null;
        $sourceLabel = null;

        if ($request->source_type === 'upload') {
            if (!$request->hasFile('video_file')) {
                return back()->with('error', 'Upload video wajib diisi.');
            }

            $file = $request->file('video_file');
            $filename = $jobId . '.' . $file->getClientOriginalExtension();
            $file->move($tempDir, $filename);

            $videoPath = $tempDir . DIRECTORY_SEPARATOR . $filename;
            $sourceLabel = 'UPLOAD';
        }

        if ($request->source_type === 'google_drive') {
            if (!$request->filled('google_drive_url')) {
                return back()->with('error', 'Link Google Drive wajib diisi.');
            }

            $videoPath = $this->convertGoogleDriveToDirect($request->google_drive_url);

            if (!$videoPath) {
                return back()->with('error', 'Link Google Drive tidak valid.');
            }

            $sourceLabel = 'GOOGLE_DRIVE';
        }

        Redis::setex('video_detection:' . $jobId, 3600, json_encode([
            'job_id' => $jobId,
            'status' => 'queued',
            'message' => 'Job masuk antrean Redis.',
            'lokasi' => $lokasi,
            'candidate_location_id' => $candidate->id ?? null,
            'source_type' => $request->source_type,
            'source' => $sourceLabel,
            'created_at' => now()->toDateTimeString(),
        ]));

        ProcessVideoDetectionJob::dispatch(
            jobId: $jobId,
            videoPath: $videoPath,
            sourceType: $request->source_type,
            lokasi: $lokasi,
            candidateLocationId: $candidate->id ?? null,
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'job_id' => $jobId,
                'message' => 'Video masuk antrean analisis.'
            ]);
        }

        return redirect()
            ->route('investor.surveyor.video-detection.index')
            ->with('job_id', $jobId)
            ->with('success', 'Video masuk antrean analisis.');
    }

    private function convertGoogleDriveToDirect(string $url): ?string
    {
        if (preg_match('/\/d\/([^\/]+)/', $url, $matches)) {
            return 'https://drive.google.com/uc?export=download&id=' . $matches[1];
        }

        if (preg_match('/id=([^&]+)/', $url, $matches)) {
            return 'https://drive.google.com/uc?export=download&id=' . $matches[1];
        }

        return null;
    }

    public function status(string $jobId)
    {
        $payload = Redis::get('video_detection:' . $jobId);

        if (!$payload) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Job tidak ditemukan atau sudah expired.',
            ], 404);
        }

        return response()->json(json_decode($payload, true));
    }

    public function saveResult(Request $request, string $jobId)
    {
        $payload = Redis::get('video_detection:' . $jobId);

        if (!$payload) {
            return back()->with('error', 'Hasil temporary tidak ditemukan.');
        }

        $data = json_decode($payload, true);

        if (($data['status'] ?? null) !== 'done') {
            return back()->with('error', 'Job belum selesai.');
        }

        DB::table('surveyor_video_detections')->insert([
            'job_id' => $jobId,
            'candidate_location_id' => $data['candidate_location_id'] ?? null,
            'lokasi' => $data['lokasi'] ?? null,
            'source_type' => $data['source_type'] ?? null,
            'duration_seconds' => $data['duration_seconds'] ?? 0,
            'person_count' => $data['counts']['person'] ?? 0,
            'motorcycle_count' => $data['counts']['motorcycle'] ?? 0,
            'car_count' => $data['counts']['car'] ?? 0,
            'bus_count' => $data['counts']['bus'] ?? 0,
            'truck_count' => $data['counts']['truck'] ?? 0,
            'peak_minute' => $data['peak_minute'] ?? null,
            'raw_result' => json_encode($data),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (!empty($data['candidate_location_id'])) {
            DB::table('surveyor_candidate_locations')
                ->where('id', $data['candidate_location_id'])
                ->update([
                    'status' => 'SURVEYED',
                    'updated_at' => now(),
                ]);
        }

        Redis::del('video_detection:' . $jobId);

        return redirect()
            ->route('investor.surveyor.video-detection.index')
            ->with('success', 'Hasil analisis disimpan.');
    }

    public function discardResult(string $jobId)
    {
        Redis::del('video_detection:' . $jobId);

        return redirect()
            ->route('investor.surveyor.video-detection.index')
            ->with('success', 'Hasil temporary dibuang.');
    }
}
