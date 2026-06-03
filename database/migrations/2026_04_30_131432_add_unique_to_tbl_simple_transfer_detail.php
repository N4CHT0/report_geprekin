<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('tbl_simple_transfer_detail', function (Blueprint $table) {
            // 1. drop unique lama
            // $table->dropUnique(['transfer_num']);

            // 2. tambah unique baru
            $table->unique(
                ['transfer_num', 'product_detail_id'],
                'unique_transfer_product_detail'
            );
        });
    }

    public function down()
    {
        Schema::table('tbl_simple_transfer_detail', function (Blueprint $table) {
            // rollback: hapus composite
            // $table->dropUnique('unique_transfer_product_detail');

            // balikin unique lama
            $table->unique('transfer_num');
        });
    }
};
