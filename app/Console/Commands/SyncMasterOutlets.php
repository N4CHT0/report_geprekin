<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MasterOutlet;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncMasterOutlets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:outlets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync master outlets from Google Sheets and auto-geocode missing coordinates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting sync...');

        $csvUrl = 'https://docs.google.com/spreadsheets/d/1rbH4dgTvfaX6FXJdW3P7cBWPP96fTqXcjA9XW1L95Dk/export?format=csv&gid=1614934901';
        $response = Http::get($csvUrl);

        if (!$response->successful()) {
            $this->error('Failed to fetch CSV from Google Sheets');
            return;
        }

        $csvData = $response->body();
        $lines = explode("\n", $csvData);
        
        $apiKey = env('GOOGLE_MAPS_API_KEY');
        if (!$apiKey) {
            $this->warn('GOOGLE_MAPS_API_KEY is not set in .env! Geocoding will be skipped.');
        }

        $count = 0;
        foreach ($lines as $index => $line) {
            // Data starts at row 14 (index 13)
            if ($index < 13) continue;

            $data = str_getcsv($line);
            
            // Expected indices:
            // 1: NO
            // 2: Nama
            // 3: Tanggal Open
            // 4: Tanggal Closed
            // 5: Kota/Kab
            // 6: Provinsi
            // 7: AM
            // 8: Gmaps
            
            if (!isset($data[2]) || trim($data[2]) === '') continue;

            $nama = trim($data[2]);
            $no_urut = trim($data[1] ?? '');
            $kota = trim($data[5] ?? '');
            
            $outlet = MasterOutlet::where('nama_outlet', $nama)->first();
            if (!$outlet) {
                $outlet = new MasterOutlet();
                $outlet->nama_outlet = $nama;
            }

            $outlet->no_urut = $no_urut;
            $outlet->tanggal_open = trim($data[3] ?? '');
            $outlet->tanggal_closed = trim($data[4] ?? '');
            $outlet->kota_kab = $kota;
            $outlet->provinsi = trim($data[6] ?? '');
            $outlet->area_manager = trim($data[7] ?? '');
            $outlet->gmaps_url = trim($data[8] ?? '');

            // Check if we need to geocode
            if (empty($outlet->latitude) || empty($outlet->longitude)) {
                if ($apiKey) {
                    $searchQuery = "Geprekin Aja " . $nama . " " . $kota;
                    $this->info("Geocoding: {$searchQuery}");
                    
                    $placesUrl = "https://places.googleapis.com/v1/places:searchText";
                    $placeRes = Http::withHeaders([
                        'X-Goog-Api-Key' => $apiKey,
                        'X-Goog-FieldMask' => 'places.location',
                        'Content-Type' => 'application/json',
                        'Referer' => 'https://report.geprekincloud.tech/'
                    ])->post($placesUrl, [
                        'textQuery' => $searchQuery
                    ]);

                    if ($placeRes->successful()) {
                        $json = $placeRes->json();
                        if (isset($json['places'][0]['location'])) {
                            $loc = $json['places'][0]['location'];
                            $outlet->latitude = $loc['latitude'];
                            $outlet->longitude = $loc['longitude'];
                            $this->info("  -> Found: {$loc['latitude']}, {$loc['longitude']}");
                        } else {
                            $this->warn("  -> Not found (No results)");
                        }
                    } else {
                        $this->warn("  -> HTTP Error: " . $placeRes->status() . " - " . $placeRes->body());
                    }
                }
            }

            $outlet->save();
            $count++;
        }

        $this->info("Sync completed! Processed {$count} outlets.");
    }
}
