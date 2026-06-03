<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckSuperAdmin
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        // Bisa pakai email khusus atau tambahkan kolom role di users
        if ($user && $user->email === 'superadmin@gmail.com') {
            return $next($request);
        }

        abort(403, 'Akses ditolak. Hanya Superadmin yang boleh mengakses menu ini.');
    }
}
