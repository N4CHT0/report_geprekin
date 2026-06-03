<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyncMasterKamus extends Command
{
    protected $signature = 'sync:kamus';
    protected $description = 'Ambil nama lokasi dan produk unik dari transaksi ke tabel master';

    public function handle()
    {
        $this->info('Memulai sinkronisasi kamus...');

        // 1. Ambil Nama Lokasi Unik (dari Origin & Destination)
        // $locations = DB::table('tbl_simple_transfer')
        //     ->select('origin_location_name as name')->distinct()
        //     ->union(DB::table('tbl_simple_transfer')->select('destination_location_name as name')->distinct())
        //     ->get();

        // foreach ($locations as $loc) {
        //     if (!$loc->name) continue;

        //     $slug = Str::slug($loc->name);
        //     $exists = DB::table('tbl_master_location')->where('slug', $slug)->exists();

        //     if (!$exists) {
        //         DB::table('tbl_master_location')->insert([
        //             'location_name' => $loc->name,
        //             'slug' => $slug,
        //             'esb_id' => 0, // Tugasmu nanti tinggal isi ID-nya di DB
        //             'created_at' => now()
        //         ]);
        //         $this->line("Menambahkan Lokasi: {$loc->name}");
        //     }
        // }

        // 1. Ambil Nama Lokasi Unik
        // Origin: kita paksa id-nya 0 karena memang tidak ada
        $origin = DB::table('tbl_simple_transfer')
            ->select('origin_location_name as name', DB::raw('0 as loc_id'))
            ->whereNotNull('origin_location_name')
            ->distinct();

        // Destination: ambil name dan id aslinya
        $locations = DB::table('tbl_simple_transfer')
            ->select('destination_location_name as name', 'destination_location_id as loc_id')
            ->whereNotNull('destination_location_name')
            ->distinct()
            ->union($origin)
            ->get();

        foreach ($locations as $loc) {
            $slug = Str::slug($loc->name);

            // Gunakan updateOrInsert
            // Kenapa? Supaya kalau lokasi sudah ada (hasil insert origin yang ID-nya 0), 
            // dia bakal ter-update saat memproses destination yang punya ID asli.
            DB::table('tbl_master_location')->updateOrInsert(
                ['slug' => $slug],
                [
                    'location_name' => $loc->name,
                    // Logika: Jangan update jadi 0 jika di database sudah ada ID-nya (sudah diisi manual/sebelumnya)
                    // Tapi untuk awal, kita ambil loc_id dari data yang ada
                    'esb_id'     => $loc->loc_id ?? 0,
                    'created_at' => now()
                ]
            );

            $this->line("Sinkronisasi Lokasi: {$loc->name} (ID: " . ($loc->loc_id ?? 0) . ")");
        }

        // 2. Ambil Nama Produk Unik
        // $products = DB::table('tbl_simple_transfer_detail')
        //     ->select('product_name')->distinct()
        //     ->get();

        // foreach ($products as $prod) {
        //     if (!$prod->product_name) continue;

        //     $slug = Str::slug($prod->product_name);
        //     $exists = DB::table('tbl_master_product')->where('slug', $slug)->exists();

        //     if (!$exists) {
        //         DB::table('tbl_master_product')->insert([
        //             'product_name' => $prod->product_name,
        //             'slug' => $slug,
        //             'esb_id' => 0,
        //             'created_at' => now()
        //         ]);
        //         $this->line("Menambahkan Produk: {$prod->product_name}");
        //     }
        // }
        $products = DB::table('tbl_simple_transfer_detail')
            ->select('product_name', 'product_detail_id')
            ->whereNotNull('product_name')
            ->distinct() // Ini akan mengambil pasangan name & id yang unik
            ->get();

        // foreach ($products as $prod) {
        //     $slug = Str::slug($prod->product_name);

        //     // Cek apakah produk sudah ada di master
        //     $exists = DB::table('tbl_master_product')->where('slug', $slug)->exists();

        //     if (!$exists) {
        //         DB::table('tbl_master_product')->insert([
        //             'product_name' => $prod->product_name,
        //             'slug'         => $slug,
        //             // Jika product_detail_id ada isinya gunakan itu, jika null/kosong gunakan 0
        //             'esb_id'       => $prod->product_detail_id ?? 0,
        //             'created_at'   => now()
        //         ]);

        //         $this->line("Menambahkan Produk: {$prod->product_name} dengan ESB ID: " . ($prod->product_detail_id ?? 0));
        //     }
        // }
        foreach ($products as $prod) {
            $slug = Str::slug($prod->product_name);

            // Pakai updateOrInsert supaya yang tadinya '0' jadi terisi ID asli
            DB::table('tbl_master_product')->updateOrInsert(
                ['slug' => $slug], // Cari berdasarkan slug
                [
                    'product_name' => $prod->product_name,
                    'esb_id'       => $prod->product_detail_id ?? 0,
                    'created_at'   => now(),
                    'updated_at'   => now()
                ]
            );

            $this->line("Sinkronisasi Produk: {$prod->product_name} (ESB ID: " . ($prod->product_detail_id ?? 0) . ")");
        }

        $this->info('Selesai! Sekarang cek database dan isi esb_id yang masih 0.');
    }
}
