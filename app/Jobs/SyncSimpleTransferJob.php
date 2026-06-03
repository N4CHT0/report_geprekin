<?php

namespace App\Jobs;

use App\Services\SimpleTransferService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncSimpleTransferJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;
    public int $tries = 1;

    public function __construct(
        protected int $page = 1,
        protected int $limit = 100,
        protected ?string $start = null,
        protected ?string $end = null,
        protected string $companyCode = 'OKNHO'
    ) {
        $this->onConnection('redis');
        $this->onQueue('transfer-sync');
    }

    public function handle(SimpleTransferService $service): void
    {
        $result = $service->syncSimpleTransferCredentialPage(
            companyCode: $this->companyCode,
            startDate: (string) $this->start,
            endDate: (string) $this->end,
            page: $this->page,
            limit: $this->limit
        );

        if ((int) ($result['api_rows'] ?? 0) <= 0) {
            return;
        }

        self::dispatch(
            $this->page + 1,
            $this->limit,
            $this->start,
            $this->end,
            $this->companyCode
        )->onConnection('redis')->onQueue('transfer-sync');
    }
}