<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsHospaceAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // Jika yang login adalah userhospace biasa, tendang dengan error 403
        if (auth()->check() && auth()->user()->role === 'userhospace') {
            abort(403, 'Akses Ditolak. Anda tidak memiliki izin untuk membuka halaman Sistem Admin.');
        }

        // Jika dia admin, biarkan lewat
        return $next($request);
    }
}