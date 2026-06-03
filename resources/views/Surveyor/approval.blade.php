@section('title', 'Workflow Approval Board')
@section('breadcrumb', 'Manajemen / Workflow Approval')

@include('Surveyor.layouts.header')

<style>
    :root {
        --k-bg: #f3f4f6;
        --k-col: #e5e7eb;
        --k-card: #ffffff;
        --k-text: #1f2937;
        --k-muted: #6b7280;
    }
    /* PREMIUM AESTHETICS OVERHAUL */
    body {
        background: #f0f4f8;
        background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
        background-size: 24px 24px;
    }
    
    .kanban-board {
        display: flex;
        gap: 24px;
        align-items: flex-start;
        overflow-x: auto;
        padding-bottom: 24px;
        min-height: 70vh;
    }
    .kanban-col {
        flex: 0 0 320px;
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.5);
        border-radius: 16px;
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 16px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
    }
    .col-header {
        font-weight: 900;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding-bottom: 12px;
        border-bottom: 2px dashed #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #334155;
    }
    .col-count {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: white;
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 800;
        box-shadow: 0 2px 4px rgba(37, 99, 235, 0.3);
    }
    
    .k-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 16px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -2px rgba(0,0,0,0.02);
        border-left: 6px solid #cbd5e1;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    .k-card::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: linear-gradient(rgba(255,255,255,0), rgba(255,255,255,0.8));
        opacity: 0;
        transition: opacity 0.3s;
        pointer-events: none;
    }
    .k-card:hover {
        transform: translateY(-4px) scale(1.02);
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.05);
    }
    .k-card:hover::after {
        opacity: 0.1;
    }
    
    .k-card.status-PENDING { border-left-color: #f59e0b; }
    .k-card.status-APPROVED { border-left-color: #10b981; }
    .k-card.status-REVISION { border-left-color: #3b82f6; }
    .k-card.status-REJECTED { border-left-color: #ef4444; }
    
    .k-title { 
        font-weight: 800; 
        font-size: 16px; 
        margin-bottom: 6px; 
        color: #1e293b;
        line-height: 1.3;
    }
    .k-score { 
        font-size: 28px; 
        font-weight: 900; 
        line-height: 1; 
        margin: 12px 0;
        background: linear-gradient(135deg, #1e293b, #475569);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .k-info { 
        font-size: 12px; 
        color: #64748b; 
        display: flex; 
        gap: 12px; 
        align-items: center; 
        font-weight: 500;
    }
    .k-badge { 
        padding: 6px 10px; 
        border-radius: 8px; 
        font-size: 10px; 
        font-weight: 800; 
        letter-spacing: 0.5px;
        text-transform: uppercase;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .bg-rec-APPROVED { background: linear-gradient(135deg, #34d399, #10b981); color: white; }
    .bg-rec-CONSIDERATION { background: linear-gradient(135deg, #fbbf24, #f59e0b); color: white; }
    .bg-rec-REJECTED { background: linear-gradient(135deg, #f87171, #ef4444); color: white; }

    /* Modal Styling */
    .modal-content {
        border: none;
        border-radius: 24px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        overflow: hidden;
    }
    .modal-header {
        background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        border-bottom: 1px solid #e2e8f0;
        padding: 24px;
    }
    .modal-body {
        padding: 24px;
    }
    .modal-footer {
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        padding: 20px 24px;
    }
    .btn-primary {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        border: none;
        box-shadow: 0 4px 6px rgba(37, 99, 235, 0.2);
        transition: all 0.2s;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 8px rgba(37, 99, 235, 0.3);
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-black mb-1">Workflow Approval Board</h2>
        <p class="text-muted">Pusat persetujuan kelayakan lokasi bagi Area Manager / Manajemen.</p>
    </div>
</div>

<div class="kanban-board">
    @php
        $cols = ['PENDING', 'REVISION', 'APPROVED', 'REJECTED'];
        $titles = ['Menunggu Review', 'Perlu Revisi', 'Disetujui', 'Ditolak'];
    @endphp

    @foreach($cols as $idx => $status)
        @php $items = $scores->where('workflow_status', $status); @endphp
        <div class="kanban-col">
            <div class="col-header">
                {{ $titles[$idx] }} <span class="col-count">{{ $items->count() }}</span>
            </div>
            
            @foreach($items as $s)
                <div class="k-card status-{{ $status }}" onclick="openApprovalModal({{ json_encode($s) }})">
                    <div class="d-flex justify-content-between">
                        <span class="k-badge bg-rec-{{ $s->rekomendasi ?? 'REJECTED' }}">{{ $s->rekomendasi ?? 'N/A' }}</span>
                        <span style="font-size: 10px; color:#9ca3af;">{{ \Carbon\Carbon::parse($s->tanggal_survey)->format('d M') }}</span>
                    </div>
                    <div class="k-score">{{ number_format($s->final_percent ?? 0, 1) }}%</div>
                    <div class="k-title">{{ \Illuminate\Support\Str::limit($s->lokasi, 25) }}</div>
                    <div class="k-info">
                        <span><i class="bi bi-person"></i> {{ explode(' ', $s->surveyor)[0] ?? 'Sryvr' }}</span>
                        <span><i class="bi bi-geo-alt"></i> {{ $s->kota }}</span>
                    </div>
                </div>
            @endforeach
            
            @if($items->isEmpty())
                <div class="text-center text-muted mt-3" style="font-size:12px; font-weight:600;">Kosong</div>
            @endif
        </div>
    @endforeach
</div>

<!-- Modal Approval -->
<div class="modal fade" id="approvalModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header border-0 bg-light p-4 pb-0">
                <h5 class="modal-title fw-black" id="m-lokasi">Lokasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="approvalForm" method="POST">
                @csrf
                <div class="modal-body p-4 pt-3">
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                        <div>
                            <div class="text-muted" style="font-size:12px; font-weight:800; text-transform:uppercase;">Final Score</div>
                            <div id="m-score" style="font-size:32px; font-weight:900; letter-spacing:-1px;">0%</div>
                        </div>
                        <div class="text-end">
                            <div class="text-muted" style="font-size:12px; font-weight:800; text-transform:uppercase;">Rekomendasi Sistem</div>
                            <div id="m-rekomendasi" class="fw-bold mt-1">APPROVED</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="fw-bold mb-2">Ubah Status</label>
                        <select name="workflow_status" id="m-status" class="form-select form-select-lg" style="border-radius:12px; font-weight:700;">
                            <option value="PENDING">Kembalikan ke PENDING</option>
                            <option value="APPROVED">Setujui (APPROVED)</option>
                            <option value="REVISION">Minta Revisi (REVISION)</option>
                            <option value="REJECTED">Tolak (REJECTED)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold mb-2">Catatan Manajer</label>
                        <textarea name="approval_note" id="m-note" class="form-control" rows="3" style="border-radius:12px;" placeholder="Tulis instruksi revisi atau alasan penolakan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <a href="#" id="btn-detail" class="btn btn-light fw-bold" style="border-radius:12px; padding:12px 20px;">Lihat Rincian</a>
                    <button type="submit" class="btn btn-primary fw-bold ms-auto" style="border-radius:12px; padding:12px 30px;">Simpan Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
    
    function openApprovalModal(data) {
        document.getElementById('m-lokasi').innerText = data.lokasi;
        document.getElementById('m-score').innerText = parseFloat(data.final_percent).toFixed(1) + '%';
        document.getElementById('m-rekomendasi').innerText = data.rekomendasi;
        document.getElementById('m-status').value = data.workflow_status;
        document.getElementById('m-note').value = data.approval_note || '';
        
        let updateUrl = "{{ route('investor.surveyor.site-score.approval.update', 'DUMMY_ID') }}";
        document.getElementById('approvalForm').action = updateUrl.replace('DUMMY_ID', data.id);
        
        let detailUrl = "{{ route('investor.surveyor.site-score.detail', 'DUMMY_ID') }}";
        document.getElementById('btn-detail').href = detailUrl.replace('DUMMY_ID', data.id);
        
        modal.show();
    }
</script>
@endpush

@include('Surveyor.layouts.footer')
