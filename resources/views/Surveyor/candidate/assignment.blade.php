@section('title', 'Surveyor Assignment')
@section('breadcrumb', 'Surveyor / Assignment')

@include('Surveyor.layouts.header')

<style>
    :root {
        --ass-bg: #f4f7fb;
        --ass-card: #ffffff;
        --ass-text: #111827;
        --ass-muted: #64748b;
        --ass-border: #e2e8f0;
        --ass-primary: #3b82f6;
        --ass-primary-soft: #eff6ff;
        --ass-warning: #f59e0b;
        --ass-success: #10b981;
        --shadow-sm: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        --shadow-md: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
    }

    .ass-page {
        padding: 24px 30px 40px;
        background: var(--ass-bg);
        min-height: calc(100vh - 70px);
        color: var(--ass-text);
    }

    .ass-header {
        margin-bottom: 24px;
    }

    .ass-title {
        font-size: 28px;
        font-weight: 900;
        margin: 0;
        letter-spacing: -0.03em;
    }

    .ass-subtitle {
        color: var(--ass-muted);
        margin: 4px 0 0;
        font-size: 15px;
    }

    .ass-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: var(--ass-card);
        border: 1px solid var(--ass-border);
        border-radius: 20px;
        padding: 20px;
        box-shadow: var(--shadow-sm);
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .stat-icon {
        width: 54px;
        height: 54px;
        border-radius: 16px;
        display: grid;
        place-items: center;
        font-size: 24px;
    }

    .stat-icon.primary { background: var(--ass-primary-soft); color: var(--ass-primary); }
    .stat-icon.warning { background: #fef3c7; color: var(--ass-warning); }
    .stat-icon.success { background: #d1fae5; color: var(--ass-success); }

    .stat-info h4 {
        margin: 0;
        font-size: 13px;
        color: var(--ass-muted);
        text-transform: uppercase;
        font-weight: 800;
        letter-spacing: 0.05em;
    }

    .stat-info .stat-value {
        font-size: 28px;
        font-weight: 900;
        line-height: 1.2;
        margin-top: 2px;
    }

    .ass-panel {
        background: var(--ass-card);
        border: 1px solid var(--ass-border);
        border-radius: 24px;
        box-shadow: var(--shadow-md);
        overflow: hidden;
    }

    .ass-panel-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--ass-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .ass-panel-header h2 {
        margin: 0;
        font-size: 18px;
        font-weight: 900;
    }

    .ass-table-wrapper {
        padding: 0;
    }

    .ass-table {
        width: 100% !important;
        margin: 0 !important;
    }

    .ass-table thead th {
        background: #f8fafc;
        padding: 14px 20px !important;
        font-size: 12px;
        font-weight: 800;
        color: var(--ass-muted);
        text-transform: uppercase;
        border-bottom: 1px solid var(--ass-border) !important;
    }

    .ass-table tbody td {
        padding: 16px 20px !important;
        vertical-align: middle;
        border-bottom: 1px solid var(--ass-border);
    }

    .loc-kode {
        font-size: 12px;
        font-weight: 800;
        color: var(--ass-muted);
        background: #f1f5f9;
        padding: 4px 8px;
        border-radius: 6px;
        display: inline-block;
        margin-bottom: 4px;
    }

    .loc-nama {
        font-weight: 800;
        font-size: 15px;
        color: var(--ass-text);
        margin-bottom: 2px;
    }

    .loc-kota {
        font-size: 13px;
        color: var(--ass-muted);
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 99px;
        font-size: 12px;
        font-weight: 800;
    }

    .status-new { background: #fef3c7; color: #b45309; }
    .status-assigned { background: #d1fae5; color: #047857; }

    .assign-btn {
        background: var(--ass-primary);
        color: white;
        border: none;
        border-radius: 10px;
        padding: 8px 16px;
        font-weight: 800;
        font-size: 13px;
        transition: 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .assign-btn:hover {
        background: #2563eb;
        transform: translateY(-1px);
    }

    .surveyor-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--ass-primary-soft);
        color: var(--ass-primary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 14px;
    }

    .surveyor-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .surveyor-name {
        font-weight: 800;
        font-size: 14px;
    }

    .surveyor-date {
        font-size: 12px;
        color: var(--ass-muted);
    }

    /* Modal Styles */
    .modal-content {
        border: none;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    .modal-header {
        background: #f8fafc;
        border-bottom: 1px solid var(--ass-border);
        padding: 20px 24px;
    }

    .modal-title {
        font-weight: 900;
        font-size: 18px;
    }

    .modal-body {
        padding: 24px;
    }

    .form-label {
        font-weight: 800;
        color: var(--ass-text);
        font-size: 13px;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .form-control {
        border-radius: 12px;
        border: 2px solid var(--ass-border);
        padding: 12px 16px;
        font-weight: 600;
        transition: 0.2s;
    }

    .form-control:focus {
        border-color: var(--ass-primary);
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }

    .modal-footer {
        border-top: 1px solid var(--ass-border);
        padding: 16px 24px;
    }

    .btn-save {
        background: var(--ass-primary);
        color: white;
        border-radius: 12px;
        padding: 10px 20px;
        font-weight: 800;
        border: none;
    }
</style>

<div class="ass-page">
    <div class="ass-header">
        <h1 class="ass-title">Surveyor Assignment</h1>
        <p class="ass-subtitle">Pusat kendali penugasan tim lapangan untuk survei kandidat lokasi.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="border-radius: 16px; font-weight: 800; border: none; background: #d1fae5; color: #047857;">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="ass-stats">
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="bi bi-inbox"></i>
            </div>
            <div class="stat-info">
                <h4>Unassigned</h4>
                <div class="stat-value">{{ $stats->unassigned }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="bi bi-person-check"></i>
            </div>
            <div class="stat-info">
                <h4>Assigned</h4>
                <div class="stat-value">{{ $stats->assigned }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="bi bi-map"></i>
            </div>
            <div class="stat-info">
                <h4>Total Kandidat</h4>
                <div class="stat-value">{{ $stats->total }}</div>
            </div>
        </div>
    </div>

    <div class="ass-panel">
        <div class="ass-panel-header">
            <h2>Daftar Tugas Survei</h2>
        </div>
        
        <div class="table-responsive ass-table-wrapper">
            <table class="table ass-table" id="assignmentTable">
                <thead>
                    <tr>
                        <th class="text-nowrap">Lokasi</th>
                        <th class="text-nowrap">Prioritas</th>
                        <th class="text-nowrap">Status</th>
                        <th class="text-nowrap">Surveyor (Tim Lapangan)</th>
                        <th class="text-nowrap text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($locations as $loc)
                    <tr>
                        <td style="min-width: 250px;">
                            <div class="loc-kode">{{ $loc->kode_lokasi }}</div>
                            <div class="loc-nama">{{ $loc->nama_lokasi }}</div>
                            <div class="loc-kota"><i class="bi bi-geo-alt"></i> {{ $loc->kota ?? 'Kota tidak diketahui' }}</div>
                        </td>
                        <td class="text-nowrap">
                            @if($loc->priority == 'HIGH')
                                <span class="badge bg-danger">HIGH</span>
                            @elseif($loc->priority == 'LOW')
                                <span class="badge bg-success">LOW</span>
                            @else
                                <span class="badge bg-warning text-dark">MEDIUM</span>
                            @endif
                        </td>
                        <td class="text-nowrap">
                            @if($loc->status == 'NEW')
                                <span class="status-badge status-new"><i class="bi bi-clock"></i> Unassigned</span>
                            @else
                                <span class="status-badge status-assigned"><i class="bi bi-check-circle"></i> Assigned</span>
                            @endif
                        </td>
                        <td style="min-width: 200px;">
                            @if($loc->assigned_surveyor)
                                <div class="surveyor-info">
                                    <div class="surveyor-avatar">
                                        {{ substr($loc->assigned_surveyor, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="surveyor-name">{{ $loc->assigned_surveyor }}</div>
                                        <div class="surveyor-date">Ditugaskan pada: {{ \Carbon\Carbon::parse($loc->updated_at)->format('d M Y') }}</div>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted" style="font-size: 13px; font-weight: 600; font-style: italic;">Belum ada surveyor...</span>
                            @endif
                        </td>
                        <td class="text-nowrap text-end">
                            @if($loc->status != 'SURVEYED')
                                <a href="{{ route('investor.surveyor.site-score.create', ['candidate_id' => $loc->id]) }}" class="btn btn-sm btn-primary me-2" style="border-radius: 8px; font-weight: 700; padding: 8px 16px; background: #3b82f6; border: none;">
                                    <i class="bi bi-play-fill"></i> Mulai Survey
                                </a>
                            @endif
                            <button type="button" class="assign-btn" onclick="openAssignModal({{ $loc->id }}, '{{ $loc->nama_lokasi }}', '{{ $loc->assigned_surveyor }}')">
                                <i class="bi bi-person-plus"></i> {{ $loc->status == 'NEW' ? 'Assign' : 'Re-Assign' }}
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Assign Modal -->
<div class="modal fade" id="assignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus text-primary me-2"></i> Assign Surveyor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="assignForm" method="POST" action="">
                @csrf
                <div class="modal-body">
                    <p class="text-muted mb-4">Anda akan menugaskan surveyor untuk lokasi: <strong id="modalLocName" class="text-dark"></strong></p>
                    
                    <div class="mb-3">
                        <label class="form-label">Pilih / Ketik Nama Surveyor</label>
                        <input type="text" name="assigned_surveyor" id="inputSurveyor" class="form-control" placeholder="Contoh: Budi Santoso" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light" style="border-radius: 12px; font-weight: 800;" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-save"><i class="bi bi-send-check"></i> Kirim Penugasan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openAssignModal(id, nama, currentSurveyor) {
        document.getElementById('modalLocName').innerText = nama;
        document.getElementById('inputSurveyor').value = currentSurveyor && currentSurveyor !== 'null' ? currentSurveyor : '';
        
        // Update form action dynamically
        const form = document.getElementById('assignForm');
        const baseAction = "{{ route('investor.surveyor.candidate.assignment.store', 999999) }}";
        form.action = baseAction.replace('999999', id);
        
        const modal = new bootstrap.Modal(document.getElementById('assignModal'));
        modal.show();
    }

    $(document).ready(function() {
        $('#assignmentTable').DataTable({
            ordering: false,
            autoWidth: false,
            language: {
                search: "Cari data:",
                lengthMenu: "Tampilkan _MENU_ baris",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ tugas"
            }
        });
    });
</script>
@endpush

@include('Surveyor.layouts.footer')
