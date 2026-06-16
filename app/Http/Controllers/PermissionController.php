<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PermissionController extends Controller
{
    /**
     * Role yang dianggap root dan selalu lolos.
     */
    private array $superRoles = [
        'superadmin',
    ];

    /**
     * Route publik / auth / utilitas yang jangan dimasukkan ke daftar permission.
     * Tambahkan route name di sini kalau ada endpoint yang memang harus bebas.
     */
    private array $excludedRouteNames = [
        'login',
        'auth.investor.login',
        'auth.investor.logout',
        'password.request',
        'password.sendOtp',
        'password.verifyOtp',
        'password.reset.custom',

        'auditDashboard.auditLogin',
        'auditDashboard.auditLoginProses',
        'auditDashboard.auditRegistrasi',
        'auditDashboard.storeAuditRegistrasi',
        'auditDashboard.auditLogout',

        'hospace.login',
        'hospace.login.post',
        'hospace.register',
        'hospace.register.post',
        'hospace.logout',

        'telegram.site-score.webhook',
    ];

    /**
     * Prefix route yang tidak perlu masuk permission.
     */
    private array $excludedRoutePrefixes = [
        'ignition.',
        'sanctum.',
        'debugbar.',
        'livewire.',
    ];

    public function index(Request $request)
    {
        $roles = $this->getRoles();

        $selectedRole = (string) $request->get('role', auth()->user()->role ?? '');
        if ($selectedRole === '' && ! empty($roles)) {
            $selectedRole = $roles[0];
        }

        $permissionGroups = $this->permissionGroups();

        $selectedPermissions = [];
        if ($selectedRole !== '') {
            $selectedPermissions = DB::table('role_permissions')
                ->where('role', $selectedRole)
                ->pluck('permission')
                ->toArray();
        }

        return view('permissions.index', [
            'roles' => $roles,
            'selectedRole' => $selectedRole,
            'permissionGroups' => $permissionGroups,
            'selectedPermissions' => $selectedPermissions,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'role' => ['required', 'string', 'max:100'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'max:255'],
        ]);

        $role = trim((string) $request->input('role'));
        $permissions = collect($request->input('permissions', []))
            ->map(fn ($permission) => trim((string) $permission))
            ->filter()
            ->unique()
            ->values()
            ->all();

        DB::transaction(function () use ($role, $permissions) {
            DB::table('role_permissions')->where('role', $role)->delete();

            if (! empty($permissions)) {
                $now = now();

                $rows = array_map(function ($permission) use ($role, $now) {
                    return [
                        'role' => $role,
                        'permission' => $permission,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }, $permissions);

                foreach (array_chunk($rows, 500) as $chunk) {
                    DB::table('role_permissions')->insert($chunk);
                }
            }
        });

        $this->forgetRoleCache($role);

        return redirect()
            ->route('permissions.index', ['role' => $role])
            ->with('success', 'Hak akses role ' . strtoupper($role) . ' berhasil disimpan.');
    }

    /**
     * Beri semua permission hasil scan route ke role yang sedang login.
     * Ini tombol darurat, hanya root role yang sebaiknya memakai.
     */
    public function seedCurrentRole(Request $request)
    {
        $user = auth()->user();
        abort_unless($user, 401);

        $role = trim((string) ($user->role ?? ''));
        abort_if($role === '', 422, 'Role user kosong.');

        $allPermissions = collect($this->permissionGroups())
            ->flatMap(fn ($permissions) => array_keys($permissions))
            ->unique()
            ->values()
            ->all();

        $now = now();

        foreach ($allPermissions as $permission) {
            DB::table('role_permissions')->updateOrInsert(
                ['role' => $role, 'permission' => $permission],
                ['updated_at' => $now, 'created_at' => $now]
            );
        }

        $this->forgetRoleCache($role);

        return redirect()
            ->route('permissions.index', ['role' => $role])
            ->with('success', 'Semua hak akses berhasil diberikan ke role ' . strtoupper($role) . '.');
    }

    /**
     * Sync semua route bernama ke tabel role_permissions untuk role superadmin.
     * Berguna setelah menambah route baru.
     */
    public function syncRoutesToSuperadmin(Request $request)
    {
        $role = 'superadmin';

        $allPermissions = collect($this->permissionGroups())
            ->flatMap(fn ($permissions) => array_keys($permissions))
            ->unique()
            ->values()
            ->all();

        $now = now();

        foreach ($allPermissions as $permission) {
            DB::table('role_permissions')->updateOrInsert(
                ['role' => $role, 'permission' => $permission],
                ['updated_at' => $now, 'created_at' => $now]
            );
        }

        $this->forgetRoleCache($role);

        return redirect()
            ->route('permissions.index', ['role' => $role])
            ->with('success', 'Route permission berhasil disinkronkan ke SUPERADMIN.');
    }

    /**
     * Auto-generate permission dari seluruh route bernama.
     * Kelebihan:
     * - Tidak perlu tulis manual permission satu per satu.
     * - Route baru otomatis muncul di halaman hak akses.
     * - Permission key mengikuti route name.
     */
    private function permissionGroups(): array
    {
        $groups = [];

        foreach (Route::getRoutes() as $route) {
            $name = $route->getName();

            if (! $name || ! is_string($name)) {
                continue;
            }

            if ($this->isExcludedRouteName($name)) {
                continue;
            }

            $middleware = $route->gatherMiddleware();
            $uri = $route->uri();

            // Route yang tidak pakai auth jangan dimasukkan ke permission,
            // kecuali memang sudah punya middleware permission.
            $hasAuth = collect($middleware)->contains(fn ($mw) => $mw === 'auth' || str_starts_with((string) $mw, 'auth:'));
            $hasPermission = collect($middleware)->contains(fn ($mw) => str_starts_with((string) $mw, 'permission'));

            if (! $hasAuth && ! $hasPermission) {
                continue;
            }

            $groupName = $this->guessGroupName($name, $uri);
            $label = $this->makeLabel($name);

            $groups[$groupName][$name] = $label;
        }

        ksort($groups);

        foreach ($groups as $groupName => $permissions) {
            ksort($permissions);
            $groups[$groupName] = $permissions;
        }

        return $groups;
    }

    private function isExcludedRouteName(string $name): bool
    {
        if (in_array($name, $this->excludedRouteNames, true)) {
            return true;
        }

        foreach ($this->excludedRoutePrefixes as $prefix) {
            if (str_starts_with($name, $prefix)) {
                return true;
            }
        }

        return false;
    }

    private function guessGroupName(string $routeName, string $uri): string
    {
        $map = [
            'investor.sales.dashboardBOD' => 'Dashboard BOD',
            'investor.sales' => 'Dashboard',
            'investor.laporan' => 'Laporan Investor',
            'laporan.' => 'Laporan',
            'master.qcr' => 'Inventory QCR',
            'master.dsc' => 'Inventory DSC',
            'master.dscFormulir' => 'Inventory DSC',
            'master.dscImport' => 'Inventory DSC',
            'dsc.' => 'Inventory DSC',
            'stock.' => 'Master QCR / Stock',
            'menu.' => 'Master QCR / Stock',
            'bahan.' => 'Master QCR / Stock',
            'bahan-dsc.' => 'Master QCR / Stock',
            'bum.' => 'Master QCR / Stock',
            'purchasing.' => 'Purchasing',
            'scm.' => 'SCM',
            'supplier.' => 'Setup SCM',
            'customers.' => 'Setup SCM',
            'simple-' => 'Simple Transaction',
            'purchase-order.' => 'PO-SO Integrated',
            'sales-order.' => 'PO-SO Integrated',
            'goods-' => 'PO-SO Integrated',
            'sales-invoice.' => 'PO-SO Integrated',
            'purchase-invoice.' => 'PO-SO Integrated',
            'reports.' => 'SCM Reports',
            'ticketing.' => 'Ticketing',
            'audit' => 'Audit / Backoffice',
            'dashboard.harian' => 'Audit / Backoffice',
            'dashboard.recap' => 'Audit / Backoffice',
            'reservation.' => 'Reservation / Hospace',
            'hospace.' => 'Reservation / Hospace',
            'rooms.' => 'Reservation / Hospace',
            'time_slots.' => 'Reservation / Hospace',
            'admin.approvals' => 'Reservation / Hospace',
            'investor.surveyor' => 'Surveyor',
            'permissions.' => 'Hak Akses',
        ];

        foreach ($map as $prefix => $group) {
            if (str_starts_with($routeName, $prefix)) {
                return $group;
            }
        }

        if (str_contains($uri, 'master')) {
            return 'Master Data';
        }

        return Str::headline(strtok($routeName, '.') ?: 'Lainnya');
    }

    private function makeLabel(string $routeName): string
    {
        $last = Str::of($routeName)->afterLast('.')->replace(['-', '_'], ' ')->headline();

        $prefix = Str::of($routeName)->beforeLast('.')->replace(['.', '-', '_'], ' ')->headline();

        if ($prefix->isEmpty()) {
            return (string) $last;
        }

        return trim((string) $prefix . ' - ' . (string) $last);
    }

    private function getRoles(): array
    {
        $roles = [];

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'role')) {
            $roles = DB::table('users')
                ->whereNotNull('role')
                ->where('role', '!=', '')
                ->distinct()
                ->orderBy('role')
                ->pluck('role')
                ->toArray();
        }

        $fallbackRoles = [
            'superadmin',
            'superadmin_audit',
            'tm_manager',
            'spv',
            'leader',
            'crew',
            'investor',
            'admin',
        ];

        return array_values(array_unique(array_merge($fallbackRoles, $roles)));
    }

    private function forgetRoleCache(string $role): void
    {
        $role = strtolower(trim($role));

        Cache::forget("role_permissions:{$role}");

        /*
        * Kompatibilitas dengan cache lama dari versi sebelumnya.
        * Aman dipanggil walau key tidak ada.
        */
        Cache::forget("role_permission_check:{$role}:master.qcr.dataqcr");
        Cache::forget("role_permission_check:{$role}:master.qcr.index");
        Cache::forget("role_permission_check:{$role}:master.qcr.export");
        Cache::forget("role_permission_check:{$role}:master.qcr.hide.save");
        Cache::forget("role_permission_check:{$role}:master.qcr.uangplus.save");
    }

}
