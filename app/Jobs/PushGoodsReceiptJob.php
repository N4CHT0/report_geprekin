<?php
namespace App\Jobs;

use App\Services\EsbIntegrationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PushGoodsReceiptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 30;
    protected $receiptNum;

    public function __construct($receiptNum)
    {
        $this->receiptNum = $receiptNum;
        $this->onQueue('push-receipt');
    }

    public function handle(EsbIntegrationService $service)
    {
        Log::info("ESB-RECEIPT-JOB [START]: $this->receiptNum");

        $header = DB::table('tbl_goods_receipts')->where('receipt_num', $this->receiptNum)->first();
        if (!$header) return;

        $payload = $service->formatGoodsReceipt($this->receiptNum);
        if (!$payload) return;

        $result = $service->pushToEsb($header->credential_id, '/purchase/goods-receipt', $payload);

        if ($result['success']) {
            Log::info("ESB-RECEIPT-JOB [SUCCESS]: $this->receiptNum");
            DB::table('tbl_goods_receipts')->where('receipt_num', $this->receiptNum)->update([
                'is_pushed' => 1, 'pushed_at' => now(), 'esb_response' => 'SUCCESS'
            ]);
        } else {
            DB::table('tbl_goods_receipts')->where('receipt_num', $this->receiptNum)->update([
                'esb_response' => 'ERROR: ' . ($result['status'] ?? 500)
            ]);
            throw new \Exception("Gagal push Goods Receipt. Status: " . $result['status']);
        }
    }
}