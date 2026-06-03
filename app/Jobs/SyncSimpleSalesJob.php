<?php

namespace App\Jobs;

use App\Services\SimpleSalesService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncSimpleSalesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $page, $start, $end;

    public function __construct($page = 1, $start = null, $end = null)
    {
        $this->page = $page;
        $this->start = $start;
        $this->end = $end;
    }

    public function handle()
    {
        $service = new \App\Services\SimpleSalesService();
        $result = $service->syncFromApi($this->page, 20, $this->start, $this->end);

        // Gunakan null coalescing operator (??) untuk mencegah Undefined Array Key
        $hasNextPage = $result['next_page'] ?? false;

        if ($hasNextPage) {
            dispatch(new self($this->page + 1, $this->start, $this->end))
                ->onQueue('sim-sales-sync')
                ->delay(now()->addSeconds(2));
        }
    }
}
