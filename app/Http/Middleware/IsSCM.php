<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class IsSCM
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
       
        // 1. Cek apakah sudah login?
        if (!Auth::check()) {
            return redirect('/login');
        }
        
        // 2. Daftar role (Gunakan huruf kecil semua di sini)
        $scmRoles = ['admindc', 'leader', 'spv'];
    
        // 3. Ambil role user dan paksa jadi huruf kecil (strtolower) untuk dibandingin
        $userRole = strtolower(Auth::user()->role);

        if (in_array($userRole, $scmRoles)) {
            return $next($request);
        }
    
        // Jika bukan role yang diizinkan
        return redirect('/')->with('error', 'Anda tidak memiliki akses ke area SCM.');
    }
}
