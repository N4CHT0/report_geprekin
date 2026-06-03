<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapping User ke Outlet</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        body {
            background: #f4f7fb;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .page-wrap {
            padding: 28px 0 40px;
        }

        .page-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 20px;
            box-shadow: 0 12px 35px rgba(15, 23, 42, .06);
            overflow: hidden;
        }

        .page-header {
            padding: 24px 24px 10px;
        }

        .page-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 6px;
        }

        .page-subtitle {
            color: #6b7280;
            margin-bottom: 0;
        }

        .toolbar {
            padding: 0 24px 20px;
        }

        .summary-box {
            background: #eef5fb;
            border: 1px solid #d9e2ec;
            border-radius: 16px;
            padding: 16px 18px;
            height: 100%;
        }

        .summary-label {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 6px;
        }

        .summary-value {
            font-size: 24px;
            font-weight: 700;
            color: #163a5a;
        }

        .table-wrap {
            padding: 0 24px 24px;
        }

        .table thead th {
            background: #f8fafc;
            color: #374151;
            vertical-align: middle;
            white-space: nowrap;
        }

        .user-name {
            font-weight: 600;
            color: #111827;
        }

        .suggested-box {
            min-width: 220px;
        }

        .suggested-name {
            font-weight: 700;
            color: #166534;
        }

        .suggested-score {
            font-size: 12px;
            color: #6b7280;
        }

        .badge-status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }

        .badge-auto {
            background: #dcfce7;
            color: #166534;
        }

        .badge-manual {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge-none {
            background: #fef3c7;
            color: #92400e;
        }

        .btn-soft {
            border-radius: 10px;
        }

        .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--single {
            height: 38px;
            border: 1px solid #ced4da;
            border-radius: 10px;
            padding: 4px 8px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 28px;
        }

        .sticky-actions {
            position: sticky;
            bottom: 0;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(6px);
            border-top: 1px solid #e5e7eb;
            padding: 16px 24px;
        }

        .small-muted {
            color: #6b7280;
            font-size: 12px;
        }

        @media (max-width: 767.98px) {
            .page-title {
                font-size: 1.25rem;
            }

            .table-wrap {
                padding: 0 16px 20px;
            }

            .page-header,
            .toolbar,
            .sticky-actions {
                padding-left: 16px;
                padding-right: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid page-wrap">
        <div class="container-xxl">
            <div class="page-card">
                <div class="page-header">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                        <div>
                            <h1 class="page-title">Mapping User ke Outlet</h1>
                            <p class="page-subtitle">
                                Sistem akan mencoba mencocokkan nama user dengan nama outlet secara otomatis.
                            </p>
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="btn btn-outline-secondary btn-soft" id="btnRefreshSuggest">
                                Refresh Suggestion
                            </button>
                            <button type="button" class="btn btn-primary btn-soft" id="btnAutoSaveAll">
                                Auto Save Semua Suggestion
                            </button>
                        </div>
                    </div>
                </div>

                <div class="toolbar">
                    @if(session('success'))
                        <div class="alert alert-success mb-3">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger mb-3">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="summary-box">
                                <div class="summary-label">Total User Belum Mapping</div>
                                <div class="summary-value" id="totalUsers">{{ count($users) }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="summary-box">
                                <div class="summary-label">Suggestion Ditemukan</div>
                                <div class="summary-value" id="totalSuggested">0</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="summary-box">
                                <div class="summary-label">Belum Ada Suggestion</div>
                                <div class="summary-value" id="totalNoSuggestion">0</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-wrap">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" id="mappingTable">
                            <thead>
                                <tr>
                                    <th style="width:60px;">No</th>
                                    <th>User</th>
                                    <th style="width:220px;">Email</th>
                                    <th style="width:260px;">Suggestion Outlet</th>
                                    <th style="width:280px;">Pilih Outlet Manual</th>
                                    <th style="width:120px;">Status</th>
                                    <th style="width:130px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $index => $user)
                                    <tr data-user-id="{{ $user->id }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <div class="user-name">{{ $user->name }}</div>
                                        </td>
                                        <td>
                                            <div>{{ $user->email ?? '-' }}</div>
                                        </td>
                                        <td class="suggested-box">
                                            <div class="suggested-name">-</div>
                                            <div class="suggested-score">Belum dicocokkan</div>
                                        </td>
                                        <td>
                                            <select class="form-select outlet-select">
                                                <option value="">-- Pilih Outlet --</option>
                                                @foreach($outlets as $outlet)
                                                    <option value="{{ $outlet->id }}">
                                                        {{ $outlet->nama_outlet }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <span class="badge-status badge-none status-label">Belum match</span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-success btn-soft btn-save-row">
                                                Save
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">
                                            Semua user sudah punya outlet atau tidak ada data.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="sticky-actions">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                        <div class="small-muted">
                            Suggestion dibuat dari kemiripan nama user dengan nama outlet. Tetap cek dulu sebelum simpan massal.
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="btn btn-outline-secondary btn-soft" id="btnSelectOnlyHighScore">
                                Pilih Hanya Match Kuat
                            </button>
                            <button type="button" class="btn btn-primary btn-soft" id="btnAutoSaveAllBottom">
                                Auto Save Semua Suggestion
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.outletsData = @json($outlets);
        window.updateOutletUrl = "{{ route('user.updateOutlet') }}";
        window.csrfToken = "{{ csrf_token() }}";
    </script>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fuse.js@6.6.2"></script>

    <script>
        $(document).ready(function () {
            $('.outlet-select').select2({
                placeholder: '-- Pilih Outlet --',
                allowClear: true,
                width: '100%'
            });

            const outlets = window.outletsData || [];
            const threshold = 0.25;

            const fuse = new Fuse(outlets, {
                keys: ['nama_outlet'],
                includeScore: true,
                threshold: threshold,
                ignoreLocation: true,
                minMatchCharLength: 3
            });

            function normalizeText(text) {
                return String(text || '')
                    .toLowerCase()
                    .replace(/g\.\s*express/gi, '')
                    .replace(/gg\s*express/gi, '')
                    .replace(/g\.\s*/gi, '')
                    .replace(/gg\s*/gi, '')
                    .replace(/express/gi, '')
                    .replace(/outlet/gi, '')
                    .replace(/geprek/gi, '')
                    .replace(/[^a-z0-9\s]/g, ' ')
                    .replace(/\s+/g, ' ')
                    .trim();
            }

            function getStatusBadge(type) {
                if (type === 'auto') {
                    return '<span class="badge-status badge-auto status-label">Auto match</span>';
                }
                if (type === 'manual') {
                    return '<span class="badge-status badge-manual status-label">Manual</span>';
                }
                return '<span class="badge-status badge-none status-label">Belum match</span>';
            }

            function suggestAllRows() {
                let suggestedCount = 0;
                let noSuggestionCount = 0;

                $('#mappingTable tbody tr').each(function () {
                    const row = $(this);
                    const userName = row.find('.user-name').text();
                    const cleanedName = normalizeText(userName);
                    const result = fuse.search(cleanedName);

                    row.removeAttr('data-auto-outlet-id');
                    row.removeAttr('data-auto-score');

                    if (result.length > 0) {
                        const best = result[0];
                        const bestOutlet = best.item;
                        const rawScore = typeof best.score === 'number' ? best.score : 1;
                        const matchPercent = Math.round((1 - rawScore) * 100);

                        row.attr('data-auto-outlet-id', bestOutlet.id);
                        row.attr('data-auto-score', matchPercent);

                        row.find('.suggested-name').text(bestOutlet.nama_outlet);
                        row.find('.suggested-score').text('Skor kecocokan: ' + matchPercent + '%');

                        if (!row.find('.outlet-select').val()) {
                            row.find('.outlet-select').val(String(bestOutlet.id)).trigger('change');
                            row.find('td:eq(5)').html(getStatusBadge('auto'));
                        }

                        suggestedCount++;
                    } else {
                        row.find('.suggested-name').text('-');
                        row.find('.suggested-score').text('Tidak ditemukan suggestion');
                        row.find('td:eq(5)').html(getStatusBadge('none'));
                        noSuggestionCount++;
                    }
                });

                $('#totalSuggested').text(suggestedCount);
                $('#totalNoSuggestion').text(noSuggestionCount);
            }

            suggestAllRows();

            $('#btnRefreshSuggest').on('click', function () {
                suggestAllRows();
            });

            $('#mappingTable').on('change', '.outlet-select', function () {
                const row = $(this).closest('tr');
                const autoOutletId = String(row.attr('data-auto-outlet-id') || '');
                const selectedOutletId = String($(this).val() || '');

                if (!selectedOutletId) {
                    row.find('td:eq(5)').html(getStatusBadge('none'));
                    return;
                }

                if (autoOutletId && selectedOutletId === autoOutletId) {
                    row.find('td:eq(5)').html(getStatusBadge('auto'));
                } else {
                    row.find('td:eq(5)').html(getStatusBadge('manual'));
                }
            });

            async function saveRow(row) {
                const userId = row.data('user-id');
                const outletId = row.find('.outlet-select').val();

                if (!outletId) {
                    alert('Pilih outlet dulu untuk user: ' + row.find('.user-name').text());
                    return false;
                }

                const button = row.find('.btn-save-row');
                const oldText = button.text();

                button.prop('disabled', true).text('Saving...');

                try {
                    const response = await fetch(window.updateOutletUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': window.csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            user_id: userId,
                            outlet_id: outletId
                        })
                    });

                    if (!response.ok) {
                        throw new Error('Gagal update outlet user');
                    }

                    const data = await response.json();

                    if (!data || data.status !== 'ok') {
                        throw new Error(data.message || 'Response tidak valid');
                    }

                    row.fadeOut(250, function () {
                        $(this).remove();
                        $('#totalUsers').text($('#mappingTable tbody tr').length);
                    });

                    return true;
                } catch (error) {
                    alert(error.message || 'Terjadi kesalahan saat menyimpan.');
                    button.prop('disabled', false).text(oldText);
                    return false;
                }
            }

            $('#mappingTable').on('click', '.btn-save-row', async function () {
                const row = $(this).closest('tr');
                await saveRow(row);
            });

            async function autoSaveAll(highScoreOnly = false) {
                const rows = $('#mappingTable tbody tr');
                if (!rows.length) {
                    alert('Tidak ada data untuk disimpan.');
                    return;
                }

                let successCount = 0;

                for (let i = 0; i < rows.length; i++) {
                    const row = $(rows[i]);
                    const selectedOutletId = row.find('.outlet-select').val();
                    const autoScore = parseInt(row.attr('data-auto-score') || '0', 10);

                    if (!selectedOutletId) {
                        continue;
                    }

                    if (highScoreOnly && autoScore < 70) {
                        continue;
                    }

                    const ok = await saveRow(row);
                    if (ok) {
                        successCount++;
                    }
                }

                alert('Selesai. Berhasil simpan ' + successCount + ' data.');
            }

            $('#btnAutoSaveAll, #btnAutoSaveAllBottom').on('click', async function () {
                if (!confirm('Yakin auto save semua suggestion yang terpilih?')) {
                    return;
                }
                await autoSaveAll(false);
            });

            $('#btnSelectOnlyHighScore').on('click', function () {
                $('#mappingTable tbody tr').each(function () {
                    const row = $(this);
                    const autoOutletId = row.attr('data-auto-outlet-id');
                    const autoScore = parseInt(row.attr('data-auto-score') || '0', 10);

                    if (autoOutletId && autoScore >= 70) {
                        row.find('.outlet-select').val(String(autoOutletId)).trigger('change');
                        row.find('td:eq(5)').html(getStatusBadge('auto'));
                    } else {
                        row.find('.outlet-select').val('').trigger('change');
                        row.find('td:eq(5)').html(getStatusBadge('none'));
                    }
                });

                alert('Hanya suggestion dengan skor >= 70% yang dipilih.');
            });
        });
    </script>
</body>
</html>