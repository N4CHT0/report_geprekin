<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Role yang selalu lolos semua permission.
     */
    private array $superRoles = [
        'superadmin',
    ];

    /**
     * Route yang memang boleh bypass permission.
     */
    private array $bypassRouteNames = [
        'login',
        'auth.investor.login',
        'auth.investor.logout',
        'password.request',
        'password.sendOtp',
        'password.verifyOtp',
        'password.reset.custom',

        // Halaman permission jangan dikunci permission, supaya admin tidak terkunci.
        'permissions.index',
        'permissions.update',
        'permissions.seed-current-role',
        'permissions.sync-routes',
    ];

    public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
        if (! auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = auth()->user();
        $role = $this->normalizeRole($user->role ?? '');
        $routeName = (string) optional($request->route())->getName();

        if ($role === '') {
            abort(403, 'Role user kosong.');
        }

        if (in_array($role, $this->superRoles, true)) {
            return $next($request);
        }

        if ($routeName !== '' && in_array($routeName, $this->bypassRouteNames, true)) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | STRICT MODE
        |--------------------------------------------------------------------------
        | Permission yang dicek hanya dari parameter middleware.
        |
        | Contoh route:
        |   ->middleware('permission:master.qcr.dataqcr')
        |
        | Maka role wajib punya:
        |   master.qcr.dataqcr
        |
        | URL /inventory/... atau /master/... tidak berpengaruh.
        | Yang berpengaruh adalah permission key di middleware.
        |--------------------------------------------------------------------------
        */
        $requiredPermission = trim((string) $permission);

        if ($requiredPermission === '') {
            abort(403, 'Route ini belum memiliki permission yang valid.');
        }

        if ($this->roleHasPermission($role, $requiredPermission)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses.',
                'role' => $role,
                'required_permission' => $requiredPermission,
                'route_name' => $routeName,
            ], 403);
        }

        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }

    private function roleHasPermission(string $role, string $permission): bool
    {
        $permissions = Cache::remember($this->cacheKey($role), now()->addMinutes(5), function () use ($role) {
            return DB::table('role_permissions')
                ->whereRaw('LOWER(TRIM(role)) = ?', [$role])
                ->pluck('permission')
                ->map(fn ($permission) => trim((string) $permission))
                ->filter()
                ->unique()
                ->values()
                ->toArray();
        });

        return in_array($permission, $permissions, true);
    }

    private function normalizeRole($role): string
    {
        return strtolower(trim((string) $role));
    }

    private function cacheKey(string $role): string
    {
        return "role_permissions:{$role}";
    }
}
