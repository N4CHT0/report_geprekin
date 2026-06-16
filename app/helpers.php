<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

if (! function_exists('normalizePermissionRole')) {
    function normalizePermissionRole($role): string
    {
        return strtolower(trim((string) $role));
    }
}

if (! function_exists('rolePermissionCacheKey')) {
    function rolePermissionCacheKey(string $role): string
    {
        return 'role_permissions:' . normalizePermissionRole($role);
    }
}

if (! function_exists('hasPermission')) {
    function hasPermission(?string $permission): bool
    {
        if (! $permission || ! auth()->check()) {
            return false;
        }

        $user = auth()->user();
        $role = normalizePermissionRole($user->role ?? '');

        if ($role === '') {
            return false;
        }

        if ($role === 'superadmin') {
            return true;
        }

        $permissions = Cache::remember(rolePermissionCacheKey($role), now()->addMinutes(5), function () use ($role) {
            return DB::table('role_permissions')
                ->whereRaw('LOWER(TRIM(role)) = ?', [$role])
                ->pluck('permission')
                ->map(fn ($permission) => trim((string) $permission))
                ->filter()
                ->unique()
                ->values()
                ->toArray();
        });

        return in_array(trim((string) $permission), $permissions, true);
    }
}

if (! function_exists('hasAnyPermission')) {
    function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (hasPermission((string) $permission)) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('hasAllPermissions')) {
    function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (! hasPermission((string) $permission)) {
                return false;
            }
        }

        return true;
    }
}
