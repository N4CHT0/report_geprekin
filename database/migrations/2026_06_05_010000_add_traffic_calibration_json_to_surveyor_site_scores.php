<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surveyor_site_scores', function (Blueprint $table) {
            $table->json('traffic_calibration_json')->nullable()->after('pejalan_weekend_sore');
        });
    }

    public function down(): void
    {
        Schema::table('surveyor_site_scores', function (Blueprint $table) {
            $table->dropColumn('traffic_calibration_json');
        });
    }
};
