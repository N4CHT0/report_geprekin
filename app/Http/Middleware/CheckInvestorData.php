<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckInvestorData
{
    public function handle(Request $request, Closure $next)
    {
        $userId = Auth::id();

        if (!$userId) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $investor = DB::table('tbl_investor')->where('user_id', $userId)->first();

        if (!$investor) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Investor tidak ditemukan.');
        }

        session(['investor_id' => $investor->id]);
        return $next($request);
    }
}
