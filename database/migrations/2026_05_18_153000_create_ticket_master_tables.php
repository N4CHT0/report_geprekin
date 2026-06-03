<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_items', function (Blueprint $table) {
            $table->id();
            $table->string('item', 150)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('ticket_divisions', function (Blueprint $table) {
            $table->id();
            $table->string('division', 100)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('ticket_types', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_type', 100)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('ticket_areas', function (Blueprint $table) {
            $table->id();
            $table->string('area', 100)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = Carbon::now();

        $defaultItems = [
            'Meja Lampu Chicken Warm',
            'Kaca Display',
            'Rak Display',
            'Rombong',
            'Neon Sign',
            'Cup Sealer',
            'Kompor',
            'Rice Cooker',
            'Rice Warmer',
            'Frezeer',
            'Exhaust',
            'Kipas',
            'Meja Breeding',
            'Rak Siku Gantung',
            'Palet Beras',
            'Chiller',
            'Meja Persiapan Kerja',
            'Meja Kerja Proses',
            'Meja Penempatan Kompor',
            'Rak Bahan Baku',
            'Sink',
            'Meja Customer',
            'Kursi Customer',
            'Kompor High Pressure',
            'Kompor Dapur Biasa',
            'Dinding',
            'Sekat',
            'Kelistrikan',
            'Pompa Air',
            'Lampu Ruangan',
            'Lantai',
            'Kamar Mandi',
            'Saluran Pembuangan',
            'Kran Air',
            'Pintu Depan (Rolling Door/Besi Lipat)',
            'Pintu',
            'Atap / Genting',
            'Plafon',
            'Pembuangan Limbah (Pipa-Resapan)',
            'Lampu',
            'Plesteran Parkir',
            'Seragam',
            'Perlengkapan Akomodasi - Mess',
            'Branding',
            'Kompetitor Baru',
            'CRO Perusahaan Baru',
            'Wajan',
            'Saringan',
            'Spatula',
            'Thermometer',
            'Timer',
            'Timbangan',
            'Pisau',
            'Gunting',
            'Baskom',
            'Bowl Stainless',
            'Keranjang Penirisan',
            'Serving Tongs',
            'Keranjang Sampah',
            'Sendok',
            'Gelas Ukur',
            'Mangkok Nasi',
            'Centong',
            'Kontainer Thawing',
            'Rantang Gangnam',
            'Saringan Saos',
            'Teko',
            'Termos Es',
            'Panci Air',
            'Loyang Stainless',
            'Printer',
            'HP',
            'Cash Drawer',
            'Alat Kebersihan',
            'Timbangan kujira',
            'Timbangan digital 40kg',
            'Tab',
            'CCTV',
            'APAR 2 kg ( Alat Pemadam Api Ringan )',
            'APAR 3 kg ( Alat Pemadam Api Ringan )',
            'Regulator Win T',
            'Regulator Highpress',
            'Talenan',
            'Keset karpet tebal',
            'Stock basket slim',
            'Mini dust pan',
            'Dust Pan',
            'T-shaped mop',
            'Food clip 12 inch',
            'Nylon broom',
            'Baskom persegi',
            'Tempat dokumen',
            'Jepit kue s/s',
            'Selang lpg',
            'Gas rice cooker',
            'Lpg 3 kg',
            'Lpg 5 kg',
            'Galon aqua',
        ];

        $defaultItems = array_unique(array_map('trim', $defaultItems));

        foreach ($defaultItems as $item) {
            DB::table('ticket_items')->insert([
                'item' => $item,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $existingDivisions = DB::table('ticket_mappings')
            ->selectRaw('DISTINCT TRIM(division) as division')
            ->whereNotNull('division')
            ->whereRaw("TRIM(division) != ''")
            ->pluck('division')
            ->map(fn ($value) => trim($value))
            ->unique()
            ->filter();

        foreach ($existingDivisions as $division) {
            DB::table('ticket_divisions')->insert([
                'division' => $division,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $existingTypes = DB::table('ticket_mappings')
            ->selectRaw('DISTINCT TRIM(ticket_type) as ticket_type')
            ->whereNotNull('ticket_type')
            ->whereRaw("TRIM(ticket_type) != ''")
            ->pluck('ticket_type')
            ->map(fn ($value) => trim($value))
            ->unique()
            ->filter();

        foreach ($existingTypes as $ticketType) {
            DB::table('ticket_types')->insert([
                'ticket_type' => $ticketType,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $existingAreas = DB::table('ticket_mappings')
            ->selectRaw('DISTINCT TRIM(area) as area')
            ->whereNotNull('area')
            ->whereRaw("TRIM(area) != ''")
            ->pluck('area')
            ->map(fn ($value) => trim($value))
            ->unique()
            ->filter();

        foreach ($existingAreas as $area) {
            DB::table('ticket_areas')->insert([
                'area' => $area,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_areas');
        Schema::dropIfExists('ticket_types');
        Schema::dropIfExists('ticket_divisions');
        Schema::dropIfExists('ticket_items');
    }
};
