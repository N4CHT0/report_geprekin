@extends('Surveyor.layout')

@section('content')
<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="bg-light rounded h-100 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-wallet2 me-2"></i> Database Master BOQ (RAB)</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Item BOQ
                    </button>
                </div>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="boqTable">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>Kategori</th>
                                <th>Slug ID (JS Target)</th>
                                <th>Nama Item</th>
                                <th>Harga Satuan (Rp)</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $i => $item)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td><span class="badge bg-secondary">{{ $item->kategori }}</span></td>
                                <td><code class="text-primary">{{ $item->slug_id }}</code></td>
                                <td class="fw-bold">{{ $item->nama_item }}</td>
                                <td>Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                                <td>
                                    @if($item->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-danger">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal{{$item->id}}"><i class="bi bi-pencil"></i></button>
                                    <form action="{{ route('master.boq.delete', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus item ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editModal{{$item->id}}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('master.boq.update', $item->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Item BOQ</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Kategori</label>
                                                    <select name="kategori" class="form-select" required>
                                                        <option value="Promosi" {{ $item->kategori == 'Promosi' ? 'selected' : '' }}>Promosi</option>
                                                        <option value="Listrik" {{ $item->kategori == 'Listrik' ? 'selected' : '' }}>Listrik</option>
                                                        <option value="Sanitasi" {{ $item->kategori == 'Sanitasi' ? 'selected' : '' }}>Air & Sanitasi</option>
                                                        <option value="Partisi" {{ $item->kategori == 'Partisi' ? 'selected' : '' }}>Partisi & Rak</option>
                                                        <option value="Sipil" {{ $item->kategori == 'Sipil' ? 'selected' : '' }}>Sipil & Cat</option>
                                                        <option value="Transport" {{ $item->kategori == 'Transport' ? 'selected' : '' }}>Transport</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Slug ID <small class="text-danger">(Wajib unik untuk pengikat rumus JS, awalan rab_)</small></label>
                                                    <input type="text" name="slug_id" class="form-control" value="{{ $item->slug_id }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Nama Item</label>
                                                    <input type="text" name="nama_item" class="form-control" value="{{ $item->nama_item }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Harga Satuan (Rp)</label>
                                                    <input type="number" step="0.01" name="harga_satuan" class="form-control" value="{{ $item->harga_satuan }}" required>
                                                </div>
                                                <div class="form-check form-switch mt-3">
                                                    <input class="form-check-input" type="checkbox" name="is_active" id="isActiveEdit{{$item->id}}" {{ $item->is_active ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="isActiveEdit{{$item->id}}">Item Aktif / Tampil di Surveyor</label>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('master.boq.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Item BOQ Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select name="kategori" class="form-select" required>
                            <option value="Promosi">Promosi</option>
                            <option value="Listrik">Listrik</option>
                            <option value="Sanitasi">Air & Sanitasi</option>
                            <option value="Partisi">Partisi & Rak</option>
                            <option value="Sipil">Sipil & Cat</option>
                            <option value="Transport">Transport</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slug ID <small class="text-danger">(Contoh: rab_item_baru)</small></label>
                        <input type="text" name="slug_id" class="form-control" required placeholder="rab_...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Item</label>
                        <input type="text" name="nama_item" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga Satuan (Rp)</label>
                        <input type="number" step="0.01" name="harga_satuan" class="form-control" required value="0">
                    </div>
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActiveAdd" checked>
                        <label class="form-check-label" for="isActiveAdd">Item Aktif / Tampil di Surveyor</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#boqTable').DataTable({
            "pageLength": 25,
            "order": [[ 1, "asc" ]]
        });
    });
</script>
@endpush
