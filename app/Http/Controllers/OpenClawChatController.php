<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OpenClawChatController extends Controller
{
    public function chat(Request $request)
    {
        $message = $request->message;

        $ai = OpenAI::chat()->create([
            'model' => 'gpt-4.1-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => '
                        Kamu parser pertanyaan sales.
                        Balas JSON saja.
                        Format:
                        {
                        "intent": "sales_today|sales_month|top_outlet_today|sales_outlet_today|unknown",
                        "outlet": null|string
                        }
                        Jangan jawab selain JSON.'
                ],
                [
                    'role' => 'user',
                    'content' => $message
                ]
            ],
        ]);

        $json = $ai->choices[0]->message->content ?? '{}';
        $parsed = json_decode($json, true);

        $intent = $parsed['intent'] ?? 'unknown';
        $outlet = $parsed['outlet'] ?? null;

        if ($intent === 'sales_today') {
            $sales = DB::table('tbl_transaksi_perhari')
                ->whereDate('tanggal', today())
                ->sum('grand_total');

            return response()->json([
                'reply' => 'Sales hari ini Rp ' . number_format($sales, 0, ',', '.')
            ]);
        }

        if ($intent === 'sales_month') {
            $sales = DB::table('tbl_transaksi_perhari')
                ->whereMonth('tanggal', now()->month)
                ->whereYear('tanggal', now()->year)
                ->sum('grand_total');

            return response()->json([
                'reply' => 'Sales bulan ini Rp ' . number_format($sales, 0, ',', '.')
            ]);
        }

        if ($intent === 'top_outlet_today') {
            $top = DB::table('tbl_transaksi_perhari')
                ->select('nama_outlet', DB::raw('SUM(grand_total) as total'))
                ->whereDate('tanggal', today())
                ->groupBy('nama_outlet')
                ->orderByDesc('total')
                ->first();

            return response()->json([
                'reply' => $top
                    ? 'Top outlet hari ini: ' . $top->nama_outlet . ' Rp ' . number_format($top->total, 0, ',', '.')
                    : 'Belum ada data sales hari ini.'
            ]);
        }

        if ($intent === 'sales_outlet_today' && $outlet) {
            $sales = DB::table('tbl_transaksi_perhari')
                ->whereDate('tanggal', today())
                ->where('nama_outlet', 'like', '%' . $outlet . '%')
                ->sum('grand_total');

            return response()->json([
                'reply' => 'Sales outlet ' . $outlet . ' hari ini Rp ' . number_format($sales, 0, ',', '.')
            ]);
        }

        return response()->json([
            'reply' => 'Saya belum paham. Coba tanya: sales hari ini, sales bulan ini, top outlet hari ini.'
        ]);
    }
}