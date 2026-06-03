<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRolePurchase
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
        return redirect('/login');
    }

    // 2. Ambil role user yang sedang login
    $userRole = Auth::user()->role;

    // 3. Cek apakah role user ada di dalam daftar yang diizinkan?
    // Misal rutenya minta 'spv', tapi user login 'scm', maka ditolak.
    if (in_array($userRole, $roles)) {
        return $next($request);
    }

    // Kalau tidak punya akses, munculkan error 403
    abort(403, 'Maaf, Anda tidak memiliki akses ke halaman ini.');
    }
}
