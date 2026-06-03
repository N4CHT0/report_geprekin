@section('title', 'Data Responses')
@section('breadcrumb', 'Master / Data Responses')

@include('Temp.Audit.header')

    <div style="margin-bottom: 24px; padding: 0 4px;">
        <h2 style="font-size: 24px; font-weight: 700; margin: 0; color: var(--text-primary);">Data Responses</h2>
        <p style="font-size: 13.5px; color: var(--text-muted); margin-top: 4px;">Monitoring data audit harian berdasarkan periode, PIC, dan outlet.</p>
    </div>

    <livewire:audit.master-data-responses />

@include('Temp.Audit.footer')