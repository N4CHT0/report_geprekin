{{-- resources/views/Admin/permissions/index.blade.php --}}
@include('Temp.Investor.header')

@php
    $totalPermission = collect($permissionGroups)->map(fn ($items) => count($items))->sum();
    $checkedCount = count($selectedPermissions ?? []);
    $selectedPercent = $totalPermission > 0 ? round(($checkedCount / $totalPermission) * 100) : 0;
@endphp

<style>
    :root {
        --perm-bg: #f4f6f9;
        --perm-surface: #ffffff;
        --perm-surface-soft: #f8fafc;
        --perm-text: #111827;
        --perm-text-soft: #374151;
        --perm-muted: #6b7280;
        --perm-border: #e1e7ef;
        --perm-border-strong: #cfd8e3;
        --perm-primary: #2563eb;
        --perm-primary-dark: #1d4ed8;
        --perm-primary-soft: #eff6ff;
        --perm-success: #16a34a;
        --perm-success-soft: #ecfdf3;
        --perm-danger: #dc2626;
        --perm-radius-lg: 18px;
        --perm-radius-md: 14px;
        --perm-radius-sm: 10px;
        --perm-shadow: 0 14px 35px rgba(15, 23, 42, .07);
        --perm-shadow-soft: 0 8px 22px rgba(15, 23, 42, .05);
    }

    .perm-page {
        min-height: 100vh;
        padding: 26px;
        background:
            radial-gradient(circle at top left, rgba(37, 99, 235, .08), transparent 28%),
            var(--perm-bg);
        color: var(--perm-text);
    }

    .perm-container {
        width: 100%;
        margin: 0 auto;
    }

    .perm-topbar {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 18px;
        align-items: center;
        margin-bottom: 18px;
        padding: 22px;
        border: 1px solid var(--perm-border);
        border-radius: var(--perm-radius-lg);
        background: rgba(255, 255, 255, .94);
        box-shadow: var(--perm-shadow);
    }

    .perm-breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        color: var(--perm-muted);
        font-size: .78rem;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .perm-title-wrap h3 {
        margin: 0;
        color: var(--perm-text);
        font-size: 1.62rem;
        font-weight: 850;
        letter-spacing: -.03em;
    }

    .perm-title-wrap p {
        max-width: 920px;
        margin: 8px 0 0;
        color: var(--perm-muted);
        font-size: .96rem;
        font-weight: 500;
        line-height: 1.65;
    }

    .perm-header-action {
        display: flex;
        justify-content: flex-end;
        align-items: center;
    }

    .perm-card {
        overflow: hidden;
        border: 1px solid var(--perm-border);
        border-radius: var(--perm-radius-lg);
        background: var(--perm-surface);
        box-shadow: var(--perm-shadow);
    }

    .perm-card-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        padding: 18px 20px;
        border-bottom: 1px solid var(--perm-border);
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
    }

    .perm-card-head-title {
        margin: 0;
        color: var(--perm-text);
        font-size: 1rem;
        font-weight: 850;
    }

    .perm-card-head-subtitle {
        margin: 4px 0 0;
        color: var(--perm-muted);
        font-size: .86rem;
        font-weight: 500;
    }

    .perm-card-body {
        padding: 20px;
    }

    .perm-toolbar {
        display: grid;
        grid-template-columns: 300px minmax(360px, 1fr) 120px;
        gap: 14px;
        align-items: end;
        margin-bottom: 18px;
        padding: 16px;
        border: 1px solid var(--perm-border);
        border-radius: var(--perm-radius-md);
        background: var(--perm-surface-soft);
    }

    .perm-field label,
    .form-label {
        margin-bottom: 7px;
        color: var(--perm-text-soft);
        font-size: .79rem;
        font-weight: 800;
        letter-spacing: .01em;
    }

    .form-select,
    .form-control {
        min-height: 44px;
        border: 1px solid var(--perm-border-strong);
        border-radius: 11px;
        background-color: #fff;
        color: var(--perm-text);
        font-size: .93rem;
        font-weight: 650;
        box-shadow: none;
    }

    .form-control::placeholder {
        color: #9ca3af;
        font-weight: 500;
    }

    .form-select:focus,
    .form-control:focus {
        border-color: var(--perm-primary);
        box-shadow: 0 0 0 .2rem rgba(37, 99, 235, .11);
    }

    .btn {
        border-radius: 11px !important;
        font-weight: 800 !important;
        box-shadow: none !important;
    }

    .btn-primary {
        border-color: var(--perm-primary) !important;
        background: var(--perm-primary) !important;
    }

    .btn-primary:hover {
        border-color: var(--perm-primary-dark) !important;
        background: var(--perm-primary-dark) !important;
    }

    .btn-success {
        border-color: var(--perm-success) !important;
        background: var(--perm-success) !important;
    }

    .btn-outline-clean {
        border: 1px solid var(--perm-border-strong) !important;
        background: #fff !important;
        color: var(--perm-text-soft) !important;
    }

    .btn-outline-clean:hover {
        border-color: #b8c4d4 !important;
        background: #f8fafc !important;
        color: var(--perm-text) !important;
    }

    .alert {
        border: 0;
        border-radius: var(--perm-radius-md);
        box-shadow: var(--perm-shadow-soft);
        font-weight: 650;
    }

    .perm-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .perm-summary-item {
        position: relative;
        overflow: hidden;
        min-height: 104px;
        padding: 16px;
        border: 1px solid var(--perm-border);
        border-radius: var(--perm-radius-md);
        background: #fff;
        box-shadow: var(--perm-shadow-soft);
    }

    .perm-summary-item::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 74px;
        height: 74px;
        border-radius: 0 0 0 74px;
        background: var(--perm-primary-soft);
    }

    .perm-summary-label {
        position: relative;
        z-index: 1;
        margin-bottom: 8px;
        color: var(--perm-muted);
        font-size: .76rem;
        font-weight: 850;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .perm-summary-value {
        position: relative;
        z-index: 1;
        color: var(--perm-text);
        font-size: 1.42rem;
        font-weight: 850;
        line-height: 1.15;
    }

    .perm-progress {
        position: relative;
        z-index: 1;
        overflow: hidden;
        height: 9px;
        margin-top: 13px;
        border-radius: 999px;
        background: #e5eaf2;
    }

    .perm-progress-bar {
        display: block;
        width: {{ $selectedPercent }}%;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, var(--perm-primary), #60a5fa);
        transition: width .18s ease;
    }

    .perm-bulk-action {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
        margin-bottom: 16px;
        padding: 15px 16px;
        border: 1px solid var(--perm-border);
        border-radius: var(--perm-radius-md);
        background: #fff;
        box-shadow: var(--perm-shadow-soft);
    }

    .perm-section-title {
        color: var(--perm-text);
        font-size: .98rem;
        font-weight: 850;
    }

    .perm-section-note {
        margin-top: 3px;
        color: var(--perm-muted);
        font-size: .86rem;
        font-weight: 500;
    }

    .perm-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }

    .perm-group {
        overflow: hidden;
        border: 1px solid var(--perm-border);
        border-radius: var(--perm-radius-md);
        background: #fff;
        box-shadow: var(--perm-shadow-soft);
        transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
    }

    .perm-group:hover {
        transform: translateY(-1px);
        border-color: #cbd5e1;
        box-shadow: 0 12px 28px rgba(15, 23, 42, .07);
    }

    .perm-group-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        min-height: 66px;
        padding: 14px 15px;
        border-bottom: 1px solid var(--perm-border);
        background: #fbfdff;
    }

    .perm-group-title {
        min-width: 0;
    }

    .perm-group-title strong {
        display: flex;
        align-items: center;
        gap: 7px;
        overflow: hidden;
        color: var(--perm-text);
        font-size: .98rem;
        font-weight: 850;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .perm-group-title span {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin-top: 5px;
        padding: 3px 8px;
        border-radius: 999px;
        background: var(--perm-primary-soft);
        color: var(--perm-primary-dark);
        font-size: .74rem;
        font-weight: 800;
    }

    .perm-group-action {
        display: flex;
        flex: 0 0 auto;
        gap: 6px;
    }

    .perm-group-action .btn {
        min-width: 52px;
        padding-right: 10px;
        padding-left: 10px;
    }

    .perm-list {
        max-height: 440px;
        overflow: auto;
        padding: 9px;
        scrollbar-width: thin;
    }

    .perm-item {
        display: flex;
        align-items: flex-start;
        gap: 11px;
        margin: 0 0 6px;
        padding: 10px;
        border: 1px solid transparent;
        border-radius: 12px;
        background: #fff;
        cursor: pointer;
        transition: background .15s ease, border-color .15s ease, transform .15s ease;
    }

    .perm-item:last-child {
        margin-bottom: 0;
    }

    .perm-item:hover {
        border-color: #bfdbfe;
        background: #f8fbff;
    }

    .perm-item input {
        width: 17px;
        height: 17px;
        margin-top: 3px;
        accent-color: var(--perm-primary);
        cursor: pointer;
    }

    .perm-item-text {
        min-width: 0;
    }

    .perm-name {
        display: block;
        color: var(--perm-text-soft);
        font-size: .92rem;
        font-weight: 800;
        line-height: 1.35;
    }

    .perm-key {
        display: block;
        margin-top: 4px;
        color: var(--perm-muted);
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: .74rem;
        font-weight: 650;
        line-height: 1.45;
        word-break: break-word;
    }

    .perm-empty {
        display: none;
        margin-top: 16px;
        padding: 34px 16px;
        border: 1px dashed var(--perm-border-strong);
        border-radius: var(--perm-radius-md);
        background: #fff;
        color: var(--perm-muted);
        text-align: center;
        font-weight: 700;
    }

    .perm-footer {
        position: sticky;
        right: 0;
        bottom: 14px;
        z-index: 8;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-top: 20px;
        padding: 15px 16px;
        border: 1px solid rgba(203, 213, 225, .9);
        border-radius: var(--perm-radius-md);
        background: rgba(255, 255, 255, .96);
        box-shadow: 0 -10px 30px rgba(15, 23, 42, .08);
        backdrop-filter: blur(10px);
    }

    .perm-footer-title {
        color: var(--perm-text);
        font-weight: 850;
    }

    .perm-footer-subtitle {
        margin-top: 3px;
        color: var(--perm-muted);
        font-size: .86rem;
        font-weight: 500;
    }

    @media (max-width: 1400px) {
        .perm-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 992px) {
        .perm-page {
            padding: 16px;
        }

        .perm-topbar,
        .perm-card-head,
        .perm-bulk-action,
        .perm-footer {
            grid-template-columns: 1fr;
            flex-direction: column;
            align-items: stretch;
        }

        .perm-header-action {
            justify-content: flex-start;
        }

        .perm-toolbar,
        .perm-summary,
        .perm-grid {
            grid-template-columns: 1fr;
        }

        .perm-footer {
            position: static;
        }
    }
</style>

<div class="perm-page">
    <div class="perm-container">
        <div class="perm-topbar">
            <div class="perm-title-wrap">
                <div class="perm-breadcrumb">
                    <i class="bi bi-shield-lock"></i>
                    <span>Admin Access Control</span>
                </div>
                <h3>Hak Akses Role</h3>
                <p>
                    Atur permission berdasarkan role pengguna dengan tampilan yang lebih rapi, jelas, dan mudah dipantau.
                    Centang akses yang diperbolehkan, lalu simpan perubahan untuk menerapkan pengaturan ke role terpilih.
                </p>
            </div>

            <div class="perm-header-action">
                <form method="POST" action="{{ route('permissions.seed-current-role') }}" onsubmit="return confirm('Beri semua permission ke role login saat ini?')">
                    @csrf
                    <button class="btn btn-sm btn-success px-3 py-2" type="submit">
                        <i class="bi bi-lightning-charge me-1"></i> Izinkan Role Saya
                    </button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success mb-3">
                <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger mb-3">
                <strong><i class="bi bi-exclamation-triangle me-1"></i> Gagal menyimpan.</strong>
                <div>{{ $errors->first() }}</div>
            </div>
        @endif

        <div class="perm-card">
            <div class="perm-card-head">
                <div>
                    <h5 class="perm-card-head-title">Manajemen Permission</h5>
                    <p class="perm-card-head-subtitle">Pilih role, cari permission, lalu kelola akses per modul.</p>
                </div>
                <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill">
                    Role: {{ strtoupper($selectedRole) }}
                </span>
            </div>

            <div class="perm-card-body">
                <form method="GET" action="{{ route('permissions.index') }}" class="perm-toolbar">
                    <div class="perm-field">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select" onchange="this.form.submit()">
                            @foreach($roles as $role)
                                <option value="{{ $role }}" {{ $selectedRole === $role ? 'selected' : '' }}>
                                    {{ strtoupper($role) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="perm-field">
                        <label class="form-label">Cari Permission</label>
                        <input type="text" id="permissionSearch" class="form-control" placeholder="Cari nama menu atau permission key...">
                    </div>

                    <div class="perm-field">
                        <label class="form-label d-none d-lg-block">&nbsp;</label>
                        <a href="{{ route('permissions.index', ['role' => $selectedRole]) }}" class="btn btn-outline-clean w-100 py-2">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                        </a>
                    </div>
                </form>

                <div class="perm-summary">
                    <div class="perm-summary-item">
                        <div class="perm-summary-label">Role Aktif</div>
                        <div class="perm-summary-value">{{ strtoupper($selectedRole) }}</div>
                    </div>
                    <div class="perm-summary-item">
                        <div class="perm-summary-label">Permission Aktif</div>
                        <div class="perm-summary-value"><span id="checkedCounter">{{ $checkedCount }}</span></div>
                    </div>
                    <div class="perm-summary-item">
                        <div class="perm-summary-label">Total Permission</div>
                        <div class="perm-summary-value">{{ $totalPermission }}</div>
                    </div>
                    <div class="perm-summary-item">
                        <div class="perm-summary-label">Progress</div>
                        <div class="perm-summary-value"><span id="percentCounter">{{ $selectedPercent }}</span>%</div>
                        <div class="perm-progress">
                            <span class="perm-progress-bar" id="permissionProgress"></span>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('permissions.update') }}" id="permissionForm">
                    @csrf
                    <input type="hidden" name="role" value="{{ $selectedRole }}">

                    <div class="perm-bulk-action">
                        <div>
                            <div class="perm-section-title"><i class="bi bi-list-check me-1"></i> Daftar Permission</div>
                            <div class="perm-section-note">Gunakan tombol cepat untuk mencentang atau mengosongkan semua permission.</div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="btn btn-sm btn-outline-clean" onclick="setAllPermissions(true)">
                                <i class="bi bi-check2-all me-1"></i> Centang Semua
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-clean" onclick="setAllPermissions(false)">
                                <i class="bi bi-x-lg me-1"></i> Kosongkan Semua
                            </button>
                        </div>
                    </div>

                    <div class="perm-grid" id="permissionGrid">
                        @foreach($permissionGroups as $groupName => $permissions)
                            @php
                                $groupId = 'perm_group_' . md5($groupName);
                                $groupChecked = collect($permissions)->keys()->filter(fn ($permission) => in_array($permission, $selectedPermissions ?? [], true))->count();
                            @endphp

                            <div class="perm-group" data-group>
                                <div class="perm-group-head">
                                    <div class="perm-group-title">
                                        <strong><i class="bi bi-folder2-open"></i> {{ $groupName }}</strong>
                                        <span><span data-group-counter>{{ $groupChecked }}</span> / {{ count($permissions) }} aktif</span>
                                    </div>
                                    <div class="perm-group-action">
                                        <button type="button" class="btn btn-sm btn-outline-clean" onclick="setGroupPermissions('{{ $groupId }}', true)">All</button>
                                        <button type="button" class="btn btn-sm btn-outline-clean" onclick="setGroupPermissions('{{ $groupId }}', false)">None</button>
                                    </div>
                                </div>

                                <div class="perm-list" id="{{ $groupId }}">
                                    @foreach($permissions as $permission => $label)
                                        <label class="perm-item" data-permission-item>
                                            <input type="checkbox"
                                                name="permissions[]"
                                                value="{{ $permission }}"
                                                {{ in_array($permission, $selectedPermissions ?? [], true) ? 'checked' : '' }}>
                                            <span class="perm-item-text">
                                                <span class="perm-name">{{ $label }}</span>
                                                <span class="perm-key">{{ $permission }}</span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="perm-empty" id="permissionEmpty">
                        <i class="bi bi-search me-1"></i> Permission tidak ditemukan. Coba gunakan kata kunci lain.
                    </div>

                    <div class="perm-footer">
                        <div>
                            <div class="perm-footer-title">Simpan pengaturan role {{ strtoupper($selectedRole) }}</div>
                            <div class="perm-footer-subtitle">Pastikan permission sudah sesuai sebelum menyimpan perubahan.</div>
                        </div>
                        <button type="submit" class="btn btn-primary px-4 py-2">
                            <i class="bi bi-save me-1"></i> Simpan Hak Akses
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const totalPermissions = {{ (int) $totalPermission }};

function updateCheckedCounter() {
    const checkedCount = document.querySelectorAll('input[name="permissions[]"]:checked').length;
    const checkedCounter = document.getElementById('checkedCounter');
    const percentCounter = document.getElementById('percentCounter');
    const progress = document.getElementById('permissionProgress');
    const percentage = totalPermissions > 0 ? Math.round((checkedCount / totalPermissions) * 100) : 0;

    if (checkedCounter) checkedCounter.textContent = checkedCount;
    if (percentCounter) percentCounter.textContent = percentage;
    if (progress) progress.style.width = percentage + '%';

    updateGroupCounters();
}

function updateGroupCounters() {
    document.querySelectorAll('[data-group]').forEach(group => {
        const counter = group.querySelector('[data-group-counter]');
        const checked = group.querySelectorAll('input[name="permissions[]"]:checked').length;
        if (counter) counter.textContent = checked;
    });
}

function setAllPermissions(checked) {
    document.querySelectorAll('input[name="permissions[]"]').forEach(cb => cb.checked = checked);
    updateCheckedCounter();
}

function setGroupPermissions(groupId, checked) {
    const group = document.getElementById(groupId);
    if (!group) return;

    group.querySelectorAll('input[name="permissions[]"]').forEach(cb => cb.checked = checked);
    updateCheckedCounter();
}

function filterPermissions(keyword) {
    const q = keyword.toLowerCase().trim();
    const emptyState = document.getElementById('permissionEmpty');
    let visibleGroupCount = 0;

    document.querySelectorAll('[data-group]').forEach(group => {
        let visibleItemCount = 0;

        group.querySelectorAll('[data-permission-item]').forEach(item => {
            const text = item.textContent.toLowerCase();
            const show = !q || text.includes(q);
            item.style.display = show ? '' : 'none';
            if (show) visibleItemCount++;
        });

        group.style.display = visibleItemCount > 0 ? '' : 'none';
        if (visibleItemCount > 0) visibleGroupCount++;
    });

    if (emptyState) emptyState.style.display = visibleGroupCount === 0 ? 'block' : 'none';
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('input[name="permissions[]"]').forEach(cb => {
        cb.addEventListener('change', updateCheckedCounter);
    });

    const search = document.getElementById('permissionSearch');
    if (search) {
        search.addEventListener('input', function () {
            filterPermissions(this.value);
        });
    }

    updateCheckedCounter();
});
</script>
