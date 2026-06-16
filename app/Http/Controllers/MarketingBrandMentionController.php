<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\M_MarketingBrandMention;
use App\Imports\BrandMentionsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class MarketingBrandMentionController extends Controller
{
    public function index()
    {
        $mentions = M_MarketingBrandMention::orderBy('created_at', 'desc')->get();
        
        $stats = [
            'total' => $mentions->count(),
            'positive' => $mentions->where('sentiment', 'Positif')->count(),
            'negative' => $mentions->where('sentiment', 'Negatif')->count(),
            'neutral' => $mentions->where('sentiment', 'Netral')->count(),
            'service' => $mentions->where('category', 'Service')->count(),
            'product' => $mentions->where('category', 'Product')->count(),
            'production' => $mentions->where('category', 'Metode Produksi')->count(),
        ];

        return view('Marketing.brand24.index', compact('mentions', 'stats'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls',
        ]);

        Excel::import(new BrandMentionsImport, $request->file('file'));

        return redirect()->back()->with('success', 'Data ulasan berhasil diimport. Silakan jalankan AI Analysis.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'source' => 'required',
            'username' => 'required',
            'review_text' => 'required',
        ]);

        $mention = M_MarketingBrandMention::create([
            'source' => $request->source,
            'username' => $request->username,
            'review_text' => $request->review_text,
            'status' => 'Open'
        ]);
        
        $this->runAiAnalysis($mention);

        return redirect()->back()->with('success', 'Review ditambahkan dan otomatis dianalisis oleh AI.');
    }

    public function analyze($id)
    {
        $mention = M_MarketingBrandMention::findOrFail($id);
        
        $result = $this->runAiAnalysis($mention);
        
        if ($result) {
            return response()->json(['success' => true, 'message' => 'Analisis AI selesai.']);
        }

        return response()->json(['success' => false, 'message' => 'Gagal memanggil AI Gemini.'], 500);
    }

    private function runAiAnalysis($mention)
    {
        $apiKey = env('GEMINI_API_KEY');
        if (empty($apiKey)) {
            Log::error("GEMINI_API_KEY kosong.");
            return false;
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";
        
        $prompt = "Kamu adalah Konsultan Bisnis F&B dan Analis Sentimen Profesional untuk brand Geprekin. Analisis ulasan pelanggan berikut.
Kamu WAJIB mengembalikan balasan dalam format JSON murni TANPA block code markdown seperti ```json.
Format Wajib:
{
  \"sentiment\": \"Positif\" atau \"Negatif\" atau \"Netral\",
  \"category\": \"Service\" atau \"Product\" atau \"Metode Produksi\" atau \"Lainnya\",
  \"ai_root_cause\": \"Akar masalah dari keluhan (maksimal 2 kalimat)\",
  \"ai_marketing_step\": \"Saran balasan sopan ke pelanggan untuk meredakan isu\",
  \"ai_business_solution\": \"Saran evaluasi internal untuk tim cabang / pusat (sudut pandang manajemen F&B)\"
}

Ulasan Pelanggan: \"" . $mention->review_text . "\"";

        try {
            $response = Http::post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                "generationConfig" => [
                    "temperature" => 0.2,
                    "responseMimeType" => "application/json"
                ]
            ]);

            if ($response->successful()) {
                $jsonResult = $response->json();
                $text = $jsonResult['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
                
                // Clean markdown artifacts just in case
                $text = str_replace(['```json', '```'], '', trim($text));
                $parsed = json_decode($text, true);

                if ($parsed) {
                    $mention->update([
                        'sentiment' => $parsed['sentiment'] ?? 'Netral',
                        'category' => $parsed['category'] ?? 'Lainnya',
                        'ai_root_cause' => $parsed['ai_root_cause'] ?? '-',
                        'ai_marketing_step' => $parsed['ai_marketing_step'] ?? '-',
                        'ai_business_solution' => $parsed['ai_business_solution'] ?? '-',
                    ]);
                    return true;
                }
            } else {
                Log::error("Gemini Brand24 Error: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Gemini Brand24 Exception: " . $e->getMessage());
        }

        return false;
    }

    public function resolve($id)
    {
        $mention = M_MarketingBrandMention::findOrFail($id);
        $mention->update(['status' => 'Resolved']);
        return redirect()->back()->with('success', 'Status ulasan berhasil diselesaikan.');
    }

    public function autoScrape(Request $request)
    {
        $platform = $request->input('platform', 'gmaps');
        $urlInput = $request->input('url', ''); // e.g. maps URL or IG profile

        // 1. Ambil Token Apify dari tabel marketing_content_posting_settings
        $settings = DB::table('marketing_content_posting_settings')->where('id', 1)->first();
        $apifyToken = $settings->apify_token ?? null;

        if (!$apifyToken) {
            return redirect()->back()->with('error', 'Apify Token tidak ditemukan di setting Content Posting. Harap isi terlebih dahulu.');
        }

        // 2. Pilih Actor Apify berdasarkan Platform
        if ($platform === 'gmaps') {
            // Kita pakai Google Maps Scraper (misal: compass/google-maps-reviews-scraper)
            $actorId = 'compass~google-maps-reviews-scraper';
            $inputJson = [
                "startUrls" => [["url" => $urlInput]],
                "maxReviews" => 10, // batasi 10 ulasan terbaru agar cepat
                "language" => "id"
            ];
        } elseif ($platform === 'instagram') {
            // Kita pakai IG Scraper (misal: apify/instagram-post-scraper)
            $actorId = $settings->apify_instagram_actor ?? 'apify~instagram-post-scraper';
            $inputJson = [
                "directUrls" => [$urlInput],
                "resultsType" => "comments",
                "resultsLimit" => 10
            ];
        } else {
            return redirect()->back()->with('error', 'Platform tidak didukung.');
        }

        // 3. Panggil API Apify (Run Synchronously)
        // Endpoint: https://api.apify.com/v2/acts/{actorId}/run-sync-get-dataset-items
        $apiUrl = "https://api.apify.com/v2/acts/{$actorId}/run-sync-get-dataset-items?token={$apifyToken}";

        try {
            $response = Http::timeout(120)->post($apiUrl, $inputJson);

            if ($response->successful()) {
                $items = $response->json();
                $count = 0;

                foreach ($items as $item) {
                    $text = '';
                    $username = 'Anonymous';

                    // Mapping hasil dari Google Maps
                    if ($platform === 'gmaps') {
                        $text = $item['text'] ?? $item['reviewText'] ?? '';
                        $username = $item['name'] ?? $item['reviewerName'] ?? 'Gmaps User';
                    } 
                    // Mapping hasil dari Instagram
                    elseif ($platform === 'instagram') {
                        $text = $item['text'] ?? '';
                        $username = $item['ownerUsername'] ?? 'IG User';
                    }

                    // Hanya simpan jika ada teks ulasan
                    if (!empty(trim($text))) {
                        $mention = M_MarketingBrandMention::create([
                            'source' => $platform,
                            'username' => $username,
                            'review_text' => $text,
                            'status' => 'Open'
                        ]);
                        
                        // Auto-analisis (opsional: bisa dikomen jika ingin menghemat kuota Gemini, tapi user maunya instan)
                        $this->runAiAnalysis($mention);
                        $count++;
                    }
                }

                return redirect()->back()->with('success', "Berhasil menarik $count ulasan baru dari $platform dan dianalisis AI.");
            } else {
                Log::error('Apify Error: ' . $response->body());
                return redirect()->back()->with('error', 'Gagal memanggil Apify: ' . $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Apify Exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghubungi Apify.');
        }
    }
}
