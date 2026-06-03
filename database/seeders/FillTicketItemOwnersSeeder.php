<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class FillTicketItemOwnersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $scmKeywords = [
            'pos','printer','printer','meja','modem','router','switch','keyboard','monitor','pc','computer','laptop','scanner','ups'
        ];

        $bndKeywords = [
            'perbaikan','repair','service','rusak','broken','ganti','maintenance','kerusakan'
        ];

        $rows = DB::table('ticket_items')
            ->select('id','item','owner')
            ->get();

        $updated = 0;
        foreach ($rows as $r) {
            $item = strtolower((string) ($r->item ?? ''));
            $owner = null;

            foreach ($scmKeywords as $k) {
                if ($k !== '' && strpos($item, $k) !== false) {
                    $owner = 'SCM';
                    break;
                }
            }

            if ($owner === null) {
                foreach ($bndKeywords as $k) {
                    if ($k !== '' && strpos($item, $k) !== false) {
                        $owner = 'BND';
                        break;
                    }
                }
            }

            if ($owner !== null && trim((string)($r->owner ?? '')) === '') {
                DB::table('ticket_items')
                    ->where('id', $r->id)
                    ->update(['owner' => $owner]);
                $updated++;
            }
        }

        $this->command->info('FillTicketItemOwnersSeeder: updated ' . $updated . ' rows.');

        $remainingRows = DB::table('ticket_items')
            ->whereNull('owner')
            ->orWhere('owner', '')
            ->get(['id','item']);

        $remaining = $remainingRows->count();
        $this->command->info('Remaining items without owner: ' . $remaining);
        if ($remaining > 0) {
            $this->command->info('Sample items without owner:');
            foreach ($remainingRows->take(20) as $r) {
                $this->command->line(" - [{$r->id}] {$r->item}");
            }
        }

        // clear lookup cache via Cache facade
        try {
            Cache::forget('ticketing:lookups');
            $this->command->info('Cache ticketing:lookups cleared.');
        } catch (\Throwable $e) {
            $this->command->warn('Unable to clear cache via facade: ' . $e->getMessage());
        }
    }
}
