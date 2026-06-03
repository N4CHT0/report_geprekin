<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckInventoryAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Silakan login.');
        }

        $role = (string) ($user->role ?? '');
        $routeName = (string) optional($request->route())->getName();

        // Role yang full inventory
        $fullRoles = ['superadmin', 'superadmin_audit', 'tm_manager', 'spv', 'leader'];

        // Role yang hanya DSC Formulir
        $formOnlyRoles = ['crew'];

        // ✅ CREW: hanya boleh akses DSC Formulir + endpoint API yang dipakai formulir
        if (in_array($role, $formOnlyRoles, true)) {

            $allowedCrewRoutes = [
                // halaman formulir
                'master.dscFormulir.index',

                // API formulir (dipanggil FE)
                'load',
                'saveSo',
                'closeKasir',
                'dsc.closeStatus',
                'importPreview',

                // opsional (kalau FE kamu pakai)
                'saveMovement',
                'outlets',
                'bahan',
            ];

            // ✅ Perbaikan: fallback cek path + method (biar gak ke-block kalau route name kosong/miss)
            $allowedCrewPaths = [
                // halaman formulir
                'GET:master/dsc/formulir',

                // API formulir (dipanggil FE)
                'GET:load',
                'POST:save-so',
                'POST:close-kasir',
                'GET:dsc/close-status',
                'POST:import-preview',

                // opsional (kalau FE kamu pakai)
                'POST:save-movement',
                'GET:outlets',
                'GET:bahan',
            ];

            $pathKey = $request->method() . ':' . ltrim($request->path(), '/');

            if (
                in_array($routeName, $allowedCrewRoutes, true) ||
                in_array($pathKey, $allowedCrewPaths, true)
            ) {
                return $next($request);
            }

            abort(403, 'Akses ditolak. Crew hanya boleh akses DSC Formulir.');
        }

        // ✅ SPV/TM/Superadmin: boleh semua inventory
        if (in_array($role, $fullRoles, true)) {
            return $next($request);
        }

        abort(403, 'Akses ditolak. Anda tidak punya akses Inventory.');
    }
}