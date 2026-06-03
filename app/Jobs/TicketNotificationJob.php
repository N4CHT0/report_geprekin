<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class TicketNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $ticketId, public string $event) {}

    public function handle(): void
    {
        $ticket = DB::table('tickets')->where('id', $this->ticketId)->first();
        if (!$ticket) return;
        $targets = array_filter([$ticket->created_by, $ticket->pic_user_id, $ticket->vendor_user_id]);
        foreach (array_unique($targets) as $uid) {
            DB::table('ticket_notifications')->insert([
                'ticket_id'=>$ticket->id, 'user_id'=>$uid, 'title'=>'Update '.$ticket->ticket_number,
                'message'=>'Event: '.$this->event.' | Status: '.$ticket->status,
                'is_read'=>false, 'created_at'=>now(), 'updated_at'=>now(),
            ]);
        }
    }
}
