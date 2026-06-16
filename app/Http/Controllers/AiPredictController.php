<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class AiPredictController extends Controller
{
    public function predict(Request $request)
    {
        $x1 = floatval($request->input('sekolah', 0));
        $x2 = floatval($request->input('kampus', 0));
        $x3 = floatval($request->input('kompetitor', 0));

        $filePath = storage_path('app/ai_training_dataset.json');
        
        if (!File::exists($filePath)) {
            // Fallback default omset jika dataset belum ada
            return response()->json([
                'status' => 'success',
                'predicted_omset_harian' => 5000000,
                'k_neighbors' => []
            ]);
        }

        $dataset = json_decode(File::get($filePath), true);

        if (count($dataset) == 0) {
            return response()->json([
                'status' => 'success',
                'predicted_omset_harian' => 5000000,
                'k_neighbors' => []
            ]);
        }

        // 1. Temukan Min dan Max untuk Normalisasi (Min-Max Scaling)
        $minX1 = 0; $maxX1 = 1;
        $minX2 = 0; $maxX2 = 1;
        $minX3 = 0; $maxX3 = 1;

        foreach ($dataset as $row) {
            $minX1 = min($minX1, $row['sekolah']); $maxX1 = max($maxX1, $row['sekolah']);
            $minX2 = min($minX2, $row['kampus']);  $maxX2 = max($maxX2, $row['kampus']);
            $minX3 = min($minX3, $row['kompetitor']); $maxX3 = max($maxX3, $row['kompetitor']);
        }
        
        // Hindari pembagian dengan nol
        if ($maxX1 == $minX1) $maxX1 = $minX1 + 1;
        if ($maxX2 == $minX2) $maxX2 = $minX2 + 1;
        if ($maxX3 == $minX3) $maxX3 = $minX3 + 1;

        // Normalisasi input target
        $normTargetX1 = ($x1 - $minX1) / ($maxX1 - $minX1);
        $normTargetX2 = ($x2 - $minX2) / ($maxX2 - $minX2);
        $normTargetX3 = ($x3 - $minX3) / ($maxX3 - $minX3);

        // 2. Hitung Euclidean Distance untuk semua baris
        $distances = [];
        foreach ($dataset as $row) {
            $normX1 = ($row['sekolah'] - $minX1) / ($maxX1 - $minX1);
            $normX2 = ($row['kampus'] - $minX2) / ($maxX2 - $minX2);
            $normX3 = ($row['kompetitor'] - $minX3) / ($maxX3 - $minX3);

            $distance = sqrt(
                pow($normTargetX1 - $normX1, 2) + 
                pow($normTargetX2 - $normX2, 2) + 
                pow($normTargetX3 - $normX3, 2)
            );

            $distances[] = [
                'distance' => $distance,
                'omset' => $row['avg_omset'],
                'nama' => $row['nama']
            ];
        }

        // 3. Urutkan berdasarkan jarak terdekat (ascending)
        usort($distances, function($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });

        // 4. Ambil K tetangga terdekat (K = 3)
        $k = 3;
        $topK = array_slice($distances, 0, $k);

        // 5. Hitung rata-rata omset dari Top K (Bisa juga pakai Inverse Distance Weighting)
        $totalOmset = 0;
        $totalWeight = 0;
        $neighbors = [];

        foreach ($topK as $neighbor) {
            // Inverse Distance Weighting: jarak lebih dekat bobotnya lebih besar
            // Tambahkan nilai epsilon kecil agar tidak error bagi 0
            $weight = 1 / ($neighbor['distance'] + 0.0001); 
            $totalOmset += ($neighbor['omset'] * $weight);
            $totalWeight += $weight;
            
            $neighbors[] = $neighbor['nama'];
        }

        $predictedOmsetHarian = round($totalOmset / $totalWeight);

        return response()->json([
            'status' => 'success',
            'predicted_omset_harian' => $predictedOmsetHarian,
            'k_neighbors' => $neighbors
        ]);
    }
}
