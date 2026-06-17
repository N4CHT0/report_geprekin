<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EsbMenuService;

class SyncEsbMenus extends Command
{
    protected $signature = 'esb:sync-menu';
    protected $description = 'Sync menu ESB ke tbl_menus_esb';

    public function handle()
    {
        $this->info('Mulai sync menu ESB...');

        $total = app(EsbMenuService::class)->syncMenus();

        $this->info("Selesai. Total menu tersync: {$total}");

        return self::SUCCESS;
    }
}