<?php

namespace App\Console\Commands;

use App\Jobs\SyncEsbSalesCredential;
use App\Services\EsbSalesService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncEsbSalesAll extends Command
{
    protected $signature = 'sync:esb-sales-all
                            {start : Start date format Y-m-d}
                            {end : End date format Y-m-d}
                            {--limit=0 : Batasi credential}
                            {--credential_id=0 : Sync hanya 1 credential}';

    protected $description = 'Sync sales ESB per credential, lalu mapping branchCode ke outlet';

    public function handle(EsbSalesService $service): int
    {
        set_time_limit(0);

        $start = $this->argument('start');
        $end = $this->argument('end');
        $limit = (int) $this->option('limit');
        $credentialId = (int) $this->option('credential_id');

        $this->info('Mulai sync ESB sales');
        $this->line("Periode    : {$start} s/d {$end}");

        try {
            if ($credentialId > 0) {
                $this->info("Mode: 1 credential (ID {$credentialId})");

                $result = $service->syncSalesByCredentialId($credentialId, $start, $end);

                $this->table(
                    ['Credential ID', 'Credential', 'Sales Header', 'Saved Rows', 'Unmapped Branch'],
                    [[
                        $result['credential_id'],
                        $result['credential_code'],
                        $result['total_sales_header'],
                        $result['saved_rows'],
                        count($result['unmapped_branches']),
                    ]]
                );

                $this->info('Selesai.');
                return self::SUCCESS;
            }

            $query = DB::table('tbl_api_credentials')
                ->select('id')
                ->where('is_active', 1)
                ->orderBy('id');

            if ($limit > 0) {
                $query->limit($limit);
            }

            $credentialIds = $query->pluck('id');

            if ($credentialIds->isEmpty()) {
                $this->warn('Tidak ada credential aktif.');
                return self::FAILURE;
            }

            foreach ($credentialIds as $id) {
                SyncEsbSalesCredential::dispatch($id, $start, $end)
                    ->onQueue('esb-sales');
            }

            $this->info('Total credential di-dispatch: ' . $credentialIds->count());
            $this->info('Job berhasil dimasukkan ke queue esb-sales.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }
}