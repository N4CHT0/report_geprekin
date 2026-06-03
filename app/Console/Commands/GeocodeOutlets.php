<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class GeocodeOutlets extends Command
{
    protected $signature = 'outlets:geocode';
    protected $description = 'Generate latitude & longitude untuk outlet yang belum punya';

    public function handle()
    {
        $this->info("Starting geocoding...");

        $outlets = DB::table('tbl_outlet_kuisioner')
            ->whereNull('latitude')
            ->orWhereNull('longitude')
            ->get();

        if ($outlets->isEmpty()) {
            $this->info("Semua outlet sudah memiliki koordinat.");
            return 0;
        }

        foreach ($outlets as $outlet) {
            $this->line("Geocoding: {$outlet->nama_outlet}");
            $alamatFull = $outlet->alamat;

            if (!$alamatFull) {
                $this->warn(" - Alamat kosong, dilewati");
                continue;
            }

            // Buat array fallback alamat
            $parts = array_map('trim', explode(',', $alamatFull));
            $count = count($parts);

            $fallbacks = [];

            if ($count >= 3) {
                $fallbacks[] = implode(', ', $parts); // alamat lengkap
                $fallbacks[] = implode(', ', array_slice($parts, -3)); // kecamatan+kab/kota+provinsi
                $fallbacks[] = implode(', ', array_slice($parts, -2)); // kab/kota+provinsi
                $fallbacks[] = $parts[$count-1]; // provinsi
            } elseif ($count == 2) {
                $fallbacks[] = implode(', ', $parts);
                $fallbacks[] = $parts[1]; // provinsi
            } else {
                $fallbacks[] = $alamatFull;
            }

            $lat = $lon = null;

            // Coba setiap fallback
            foreach ($fallbacks as $addr) {
                $q = urlencode($addr);
                $response = Http::withHeaders(['User-Agent' => 'LaravelApp/1.0'])
                    ->get("https://nominatim.openstreetmap.org/search?format=json&limit=1&q={$q}");
                $data = $response->json();

                if (!empty($data[0]['lat']) && !empty($data[0]['lon'])) {
                    $lat = $data[0]['lat'];
                    $lon = $data[0]['lon'];
                    $this->info(" ✅ Sukses: $lat, $lon (dari '$addr')");
                    break;
                }
            }

            if ($lat && $lon) {
                DB::table('tbl_outlet_kuisioner')
                    ->where('id', $outlet->id)
                    ->update([
                        'latitude' => $lat,
                        'longitude' => $lon,
                        'updated_at' => now(),
                    ]);
            } else {
                $this->error(" ❌ Gagal semua fallback: $alamatFull");
            }

            sleep(1); // rate limit
        }

        $this->info("Geocoding finished!");
        return 0;
    }
}
