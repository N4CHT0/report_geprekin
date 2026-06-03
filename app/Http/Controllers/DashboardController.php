<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $summary = Cache::remember('dashboard:summary', 300, function () {
            return [
                'total' => DB::table('tickets')->count(),
                'open' => DB::table('tickets')->where('status','Open')->count(),
                'confirmed' => DB::table('tickets')->where('status','Confirmed')->count(),
                'process' => DB::table('tickets')->whereIn('status',['Process','Hold'])->count(),
                'closed' => DB::table('tickets')->where('status','Closed')->count(),
                'cancel' => DB::table('tickets')->where('status','Cancel')->count(),
                'urgent' => DB::table('tickets')->where('priority','Urgent')->whereNotIn('status',['Closed','Cancel'])->count(),
            ];
        });

        $monthly = Cache::remember('dashboard:monthly', 300, fn() => DB::table('tickets')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, status, COUNT(*) as total")
            ->where('created_at','>=',now()->subMonths(6))
            ->groupBy('month','status')->orderBy('month')->get());

        $byDivision = DB::table('tickets')->selectRaw('division, COUNT(*) as total')->groupBy('division')->orderByDesc('total')->get();
        $latest = DB::table('tickets')->orderByDesc('id')->limit(8)->get();
        return view('dashboard.index', compact('summary','monthly','byDivision','latest'));
    }

    public function exportCsv(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['No Ticket','Outlet','Kota','Area','Divisi','Jenis','Item','Priority','Status','Lead Time Menit','Dibuat']);
            DB::table('tickets')->orderByDesc('id')->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $r) {
                    fputcsv($out, [$r->ticket_number,$r->outlet_name,$r->city,$r->area,$r->division,$r->ticket_type,$r->item,$r->priority,$r->status,$r->lead_time_minutes,$r->created_at]);
                }
            });
            fclose($out);
        }, 'rekap-ticket-'.now()->format('Ymd').'.csv');
    }

    public function print()
    {
        $tickets = DB::table('tickets')->orderByDesc('id')->limit(200)->get();
        return view('dashboard.print', compact('tickets'));
    }
}
