<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AuditAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!Session::has('responden_id')) {
            return redirect()
                ->route('auditDashboard.auditLogin')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        return $next($request);
    }
}
