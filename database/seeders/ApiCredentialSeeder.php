<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ApiCredentialSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        $data = [];

        // GP100 - GP160
        for ($i = 100; $i <= 160; $i++) {
            $code = 'GP' . $i;

            $data[] = [
                'credential_code' => $code,
                'credential_name' => $code,
                'username' => $code . 'Nuha',
                'password' => 'abc123',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // GPR01 - GPR99
        for ($i = 1; $i <= 99; $i++) {
            $num = str_pad($i, 2, '0', STR_PAD_LEFT);
            $code = 'GPR' . $num;

            $data[] = [
                'credential_code' => $code,
                'credential_name' => $code,
                'username' => $code . 'Nuha',
                'password' => 'abc123',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // HO
        $data[] = [
            'credential_code' => 'OKNHO',
            'credential_name' => 'HEAD OFFICE',
            'username' => 'OKNHONuha',
            'password' => 'abc123',
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        DB::table('tbl_api_credentials')->upsert(
            $data,
            ['credential_code'],
            ['username', 'password', 'is_active', 'updated_at']
        );
    }
}