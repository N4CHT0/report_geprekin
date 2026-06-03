{{-- resources/views/Investor/outlet_match_api.blade.php --}}
@section('title', 'Outlet Match API')
@section('breadcrumb', 'Master Data / Outlet Match API')

@include('Temp.Investor.header')

<style>
.page-wrap {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

/* CARD */
.aws-card {
    border: 1px solid rgba(15,23,42,.08);
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 12px 28px rgba(15,23,42,.05);
    overflow: hidden;
}

.aws-card-header {
    padding: 16px 18px;
    border-bottom: 1px solid rgba(15,23,42,.06);
    background: linear-gradient(90deg,#fff,#f8fafc);
}

.aws-card-title {
    font-weight: 900;
    font-size: 15px;
}

.aws-card-body {
    padding: 16px;
}

/* FILTER */
.aws-filter {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 10px;
}

.aws-input {
    height: 40px;
    border: 1px solid rgba(100,116,139,.3);
    border-radius: 10px;
    padding: 0 12px;
    font-size: 13px;
    font-weight: 600;
}

.aws-input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}

/* BUTTON */
.aws-btn {
    height: 40px;
    padding: 0 16px;
    border-radius: 10px;
    border: none;
    background: #2563eb;
    color: #fff;
    font-weight: 800;
    font-size: 13px;
}

/* TABLE */
.aws-table-wrap {
    overflow-x: auto;
}

.aws-table {
    width: 100%;
    min-width: 1100px;
    border-collapse: separate;
    border-spacing: 0;
}

.aws-table thead {
    background: #f8fafc;
}

.aws-table th {
    font-size: 12px;
    text-transform: uppercase;
    font-weight: 900;
    color: #64748b;
    padding: 12px;
    border-bottom: 1px solid rgba(15,23,42,.08);
}

.aws-table td {
    padding: 12px;
    font-size: 13px;
    border-bottom: 1px solid rgba(15,23,42,.05);
}

.aws-table tr:hover {
    background: #f9fbff;
}

/* CELL */
.cell-wrap {
    max-width: 220px;
    word-break: break-word;
}

/* ACTION */
.aws-btn-save {
    height: 34px;
    border-radius: 8px;
    background: #16a34a;
    color: #fff;
    border: none;
    font-weight: 800;
    width: 100%;
}

/* PAGINATION FIX */
.pagination {
    justify-content: flex-end;
    gap: 6px;
}

.pagination .page-link {
    border-radius: 8px;
    border: 1px solid rgba(15,23,42,.1);
    color: #334155;
    font-weight: 700;
}

.pagination .active .page-link {
    background: #2563eb;
    color: #fff;
    border-color: #2563eb;
}
</style>

<div class="page-wrap">

```
{{-- FILTER --}}
<div class="aws-card">
    <div class="aws-card-body">
        <form method="GET" action="{{ route('investor.outletMatchAPI.master') }}" class="aws-filter">
            <input type="text" name="keyword"
                   value="{{ request('keyword') }}"
                   class="aws-input"
                   placeholder="Cari kode / nama outlet / branch">

            <button class="aws-btn">
                <i class="bi bi-search"></i> Cari
            </button>
        </form>
    </div>
</div>

{{-- TABLE --}}
<div class="aws-card">
    <div class="aws-card-header">
        <div class="aws-card-title">Outlet Match API</div>
    </div>

    <div class="aws-card-body">

        @if(session('success'))
            <div style="margin-bottom:10px;color:#059669;font-weight:700;">
                {{ session('success') }}
            </div>
        @endif

        <div class="aws-table-wrap">
            <table class="aws-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Outlet</th>
                        <th>Credential</th>
                        <th>Branch</th>
                        <th>Referensi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($outlets as $key => $row)
                        @php
                            $branchRef = $branches->first(function ($b) use ($row) {
                                return $b->branch_code == $row->branch_code;
                            });
                        @endphp

                        <tr>
                            <td>{{ $outlets->firstItem() + $key }}</td>

                            <td class="cell-wrap">{{ $row->kode_outlet }}</td>
                            <td class="cell-wrap">{{ $row->nama_outlet }}</td>

                            <td>
                                <form method="POST" action="{{ route('investor.outletMatchAPIUpdate.master') }}">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $row->id }}">

                                    <select name="credential_id" class="aws-input select2">
                                        <option value="">-- credential --</option>
                                        @foreach($credentials as $c)
                                            <option value="{{ $c->id }}"
                                                {{ $row->credential_id == $c->id ? 'selected' : '' }}>
                                                {{ $c->credential_code }}
                                            </option>
                                        @endforeach
                                    </select>
                            </td>

                            <td>
                                <input type="text" name="branch_code"
                                       value="{{ $row->branch_code }}"
                                       class="aws-input">
                            </td>

                            <td class="cell-wrap">
                                @if($branchRef)
                                    {{ $branchRef->branch_name }}
                                @else
                                    <span style="color:#dc2626;font-weight:700;">Tidak ditemukan</span>
                                @endif
                            </td>

                            <td>
                                    <button class="aws-btn-save">Simpan</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;">Tidak ada data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:12px;">
            {{ $outlets->links() }}
        </div>

    </div>
</div>
```

</div>

@include('Temp.Investor.footer')

@push('scripts')

<script>
$(function() {
    $('.select2').select2({
        width: '100%'
    });
});
</script>

@endpush
