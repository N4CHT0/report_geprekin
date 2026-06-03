<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckInvestorOrSuperAdmin
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        // 1) SUPERADMIN: boleh lewat
        if (
            ($user->email ?? '') === 'superadmin@gmail.com' ||
            ($user->role ?? '') === 'superadmin'
        ) {
            return $next($request);
        }

        // 2) ROLE OPERASIONAL: boleh akses dashboard investor tanpa tbl_investor
        $opsRoles = ['leader', 'spv', 'tm_manager'];
        if (in_array(($user->role ?? ''), $opsRoles, true)) {
            // optional: bersihin/isi investor_id biar dashboard gak salah baca session lama
            session()->forget('investor_id');
            return $next($request);
        }

        // 3) INVESTOR: wajib punya relasi tbl_investor
        if (($user->role ?? '') === 'investor') {
            $investor = DB::table('tbl_investor')->where('user_id', $user->id)->first();
            if (!$investor) {
                return redirect()->route('login')->with('error', 'Investor tidak ditemukan.');
            }

            session(['investor_id' => $investor->id]);
            return $next($request);
        }

        // 4) Role lain: tolak
        return redirect()->route('login')->with('error', 'Akun tidak punya akses.');
    }
}