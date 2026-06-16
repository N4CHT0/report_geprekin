{{-- resources/views/Investor/Inventory/dscFormulirOmset.blade.php --}}
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>DSC - Form Omset</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        :root {
            --bg: #f4f6fb;
            --card: #ffffff;
            --text: #111827;
            --muted: #6b7280;
            --muted-2: #9ca3af;
            --border: #e5e7eb;
            --border-2: #d8dee8;
            --shadow: 0 12px 30px rgba(17, 24, 39, .06);
            --shadow-sm: 0 4px 14px rgba(17, 24, 39, .05);
            --radius: 18px;
            --radius-sm: 14px;
            --primary: #206bc4;
            --primary-soft: rgba(32, 107, 196, .10);
            --accent: #0f766e;
            --accent-soft: rgba(15, 118, 110, .10);
            --warn: #b45309;
            --warn-soft: rgba(180, 83, 9, .10);
            --danger: #dc2626;
            --danger-soft: rgba(220, 38, 38, .08);
            --soft: #f8fafc;
        }

        * {
            -webkit-tap-highlight-color: transparent;
        }

        body {
            background: var(--bg);
            color: var(--text);
        }

        .wrap {
            max-width: 1320px;
        }

        .shell {
            padding-top: 1rem;
            padding-bottom: 2rem;
        }

        .appbar,
        .cardx {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .appbar {
            padding: 16px 18px;
            background:
                radial-gradient(900px 280px at 0% 0%, rgba(32, 107, 196, .10), transparent 55%),
                radial-gradient(700px 220px at 100% 0%, rgba(15, 118, 110, .07), transparent 45%),
                linear-gradient(180deg, #ffffff 0%, #fbfcff 100%);
        }

        .appbar-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            flex-wrap: wrap;
        }

        .title-wrap {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .title-icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            background: var(--primary-soft);
            color: var(--primary);
            border: 1px solid rgba(32, 107, 196, .16);
            font-size: 1.05rem;
            flex: 0 0 auto;
        }

        .appbar h1 {
            margin: 0;
            font-weight: 800;
            letter-spacing: .2px;
            font-size: 1.16rem;
            color: #0f172a;
        }

        .sub {
            color: var(--muted);
            font-size: .88rem;
            margin-top: 4px;
        }

        .top-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn {
            border-radius: 12px;
            font-weight: 700;
        }

        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
        }

        .btn-accent {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }

        .btn-accent:hover,
        .btn-accent:focus,
        .btn-accent:active {
            filter: brightness(.96);
            color: #fff !important;
            background: var(--accent) !important;
            border-color: var(--accent) !important;
        }

        #btnSaveS1:hover,
        #btnSaveS1:focus,
        #btnSaveS1:active,
        #btnSaveS2:hover,
        #btnSaveS2:focus,
        #btnSaveS2:active {
            color: #fff !important;
            background: var(--accent) !important;
            border-color: var(--accent) !important;
            opacity: 1 !important;
            visibility: visible !important;
        }

        .btn-danger {
            box-shadow: none;
        }

        .form-label {
            font-weight: 700;
            font-size: .82rem;
            margin-bottom: .38rem;
            color: var(--muted);
        }

        .form-control,
        .form-select {
            border-radius: 12px;
            border-color: var(--border-2);
            height: 42px;
            font-weight: 700;
            box-shadow: none !important;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: rgba(32, 107, 196, .45);
            box-shadow: 0 0 0 .2rem rgba(32, 107, 196, .12) !important;
        }

        .mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        }

        .section-head {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            background: linear-gradient(180deg, #ffffff 0%, #fbfcff 100%);
            border-top-left-radius: var(--radius);
            border-top-right-radius: var(--radius);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .section-head .title {
            font-weight: 800;
            color: #0f172a;
        }

        .card-body-pad {
            padding: 16px;
        }

        .badge-wh {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 999px;
            border: 1px solid var(--border);
            background: #fff;
            font-weight: 700;
            color: var(--muted);
            font-size: .82rem;
            white-space: nowrap;
        }

        .status-ok {
            color: #15803d;
            background: rgba(34, 197, 94, .08);
            border-color: rgba(34, 197, 94, .16);
        }

        .status-bad {
            color: #b91c1c;
            background: rgba(239, 68, 68, .08);
            border-color: rgba(239, 68, 68, .16);
        }

        .grid2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        @media (max-width: 768px) {
            .grid2 {
                grid-template-columns: 1fr;
            }
        }

        .panel-soft {
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            background: #fff;
            box-shadow: var(--shadow-sm);
            padding: 14px;
            height: 100%;
        }

        .panel-soft-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            gap: 10px;
            flex-wrap: wrap;
        }

        .panel-soft-title {
            font-weight: 800;
            color: #0f172a;
        }

        .calc {
            background: var(--soft);
            border: 1px dashed var(--border-2);
            border-radius: 12px;
            padding: 10px 12px;
            font-weight: 700;
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: center;
        }

        .calc span {
            color: var(--muted);
            font-weight: 700;
            font-size: .84rem;
        }

        .calc b {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            color: #0f172a;
            font-size: .95rem;
        }

        .neg {
            color: var(--danger) !important;
        }

        .muted {
            color: var(--muted);
        }

        .minihelp {
            font-size: .82rem;
            color: var(--muted);
            line-height: 1.5;
        }

        .divider {
            height: 1px;
            background: var(--border);
            margin: 14px 0;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }

        @media (max-width: 992px) {
            .summary-grid {
                grid-template-columns: 1fr;
            }
        }

        .summary-box {
            background: linear-gradient(180deg, #ffffff 0%, #fbfcff 100%);
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: var(--shadow-sm);
            padding: 14px;
        }

        .summary-box .label {
            color: var(--muted);
            font-size: .8rem;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .summary-box .value {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-weight: 800;
            font-size: 1rem;
            color: #0f172a;
        }

        .cam-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .photo-wrap {
            border: 1px dashed var(--border-2);
            border-radius: 14px;
            padding: 12px;
            background: #fcfdff;
        }

        .photo-empty {
            color: var(--muted-2);
            font-size: .84rem;
        }

        .shift-locked .form-control,
        .shift-locked .form-select {
            background: #f3f4f6 !important;
            cursor: not-allowed;
        }

        .shift-locked .btn:not(.btn-outline-secondary) {
            pointer-events: none;
            opacity: .65;
        }

        .locked-note {
            color: #92400e;
            background: rgba(251, 191, 36, .16);
            border: 1px solid rgba(251, 191, 36, .32);
            border-radius: 999px;
            padding: 5px 10px;
            font-size: .78rem;
            font-weight: 800;
        }

        .photo-preview-img {
            max-height: 280px;
            width: auto;
            border-radius: 12px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            background: #fff;
        }

        .select2-container .select2-selection--single {
            height: 42px !important;
            border-radius: 12px !important;
            border: 1px solid var(--border-2) !important;
            display: flex !important;
            align-items: center !important;
            padding: 0 10px !important;
            background: #fff !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 40px !important;
            padding-left: 0 !important;
            color: var(--text) !important;
            font-weight: 700 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
        }

        .modal .modal-content {
            border-radius: 18px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .modal .modal-header {
            background: linear-gradient(180deg, #fff 0%, #fbfcff 100%);
            border-bottom: 1px solid var(--border);
        }

        .ratio.camera-box {
            border-radius: 14px;
            overflow: hidden;
            background: #111827;
            border: 1px solid rgba(255,255,255,.08);
        }

        @media (max-width: 768px) {
            .appbar,
            .cardx {
                border-radius: 16px;
            }

            .section-head,
            .card-body-pad {
                padding: 14px;
            }

            .top-actions {
                width: 100%;
            }

            .top-actions .btn,
            .top-actions form {
                width: 100%;
            }

            .top-actions form .btn {
                width: 100%;
            }
        }



        /* =========================================================
           MOBILE RESPONSIVE HARDENING - UI ONLY
           Tidak mengubah ID, name, route, endpoint, maupun JavaScript logic.
           Fokus: header tidak numpuk, form lebih rapi di HP, tidak ada overflow horizontal.
        ========================================================= */
        html,
        body {
            max-width: 100%;
            overflow-x: hidden;
        }

        .top-actions .btn,
        .top-actions form,
        .cam-actions .btn {
            min-width: 0;
        }

        .select2-container {
            max-width: 100% !important;
        }

        @media (max-width: 575.98px) {
            body {
                background: #eef2f7;
                font-size: 14px;
            }

            .shell {
                padding: 10px 8px 96px;
            }

            .wrap {
                width: 100%;
                max-width: 100%;
                padding-left: 8px;
                padding-right: 8px;
            }

            .appbar {
                padding: 12px;
                border-radius: 16px;
                overflow: hidden;
            }

            .appbar-top {
                display: grid;
                grid-template-columns: 1fr;
                gap: 12px;
                align-items: stretch;
            }

            .title-wrap {
                width: 100%;
                align-items: center;
                gap: 10px;
            }

            .title-icon {
                width: 38px;
                height: 38px;
                border-radius: 12px;
                font-size: 1rem;
            }

            .appbar h1 {
                font-size: 1rem;
                line-height: 1.18;
                word-break: normal;
            }

            .sub {
                font-size: .76rem;
                line-height: 1.35;
                margin-top: 2px;
            }

            .top-actions {
                width: 100%;
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 8px;
            }

            .top-actions .btn,
            .top-actions form,
            .top-actions form .btn {
                width: 100%;
                min-height: 42px;
                padding: 8px 8px;
                font-size: .78rem;
                line-height: 1.15;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 4px;
                white-space: normal;
                text-align: center;
            }

            .top-actions form {
                grid-column: 1 / -1;
            }

            .top-actions form .btn {
                max-width: 220px;
                margin-left: auto;
                margin-right: auto;
            }

            .cardx {
                border-radius: 16px;
                margin-top: 10px !important;
                overflow: hidden;
            }

            .section-head {
                padding: 12px;
                align-items: flex-start;
            }

            .section-head .title {
                font-size: .95rem;
            }

            .section-head .badge-wh {
                max-width: 100%;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .card-body-pad {
                padding: 12px;
            }

            .grid2,
            .summary-grid {
                grid-template-columns: 1fr !important;
                gap: 10px;
            }

            .row.g-3 {
                --bs-gutter-x: .75rem;
                --bs-gutter-y: .75rem;
            }

            .panel-soft {
                padding: 12px;
                border-radius: 15px;
                box-shadow: 0 6px 16px rgba(17, 24, 39, .045);
            }

            .panel-soft-head {
                gap: 8px;
                margin-bottom: 12px;
                padding-bottom: 8px;
                border-bottom: 1px solid var(--border);
            }

            .panel-soft-title {
                font-size: .95rem;
            }

            .badge-wh,
            .locked-note {
                padding: 5px 9px;
                font-size: .74rem;
                gap: 5px;
            }

            .form-label {
                font-size: .76rem;
                margin-bottom: .28rem;
            }

            .form-control,
            .form-select,
            .select2-container .select2-selection--single {
                height: 42px !important;
                min-height: 42px !important;
                border-radius: 12px !important;
                font-size: .9rem;
            }

            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 40px !important;
                font-size: .9rem;
            }

            .calc {
                padding: 9px 10px;
                border-radius: 12px;
                gap: 8px;
            }

            .calc span {
                font-size: .76rem;
            }

            .calc b {
                font-size: .88rem;
                text-align: right;
                word-break: break-word;
            }

            .summary-box {
                padding: 11px;
                border-radius: 13px;
            }

            .summary-box .label {
                font-size: .74rem;
                margin-bottom: 4px;
            }

            .summary-box .value {
                font-size: .95rem;
            }

            .minihelp {
                font-size: .75rem;
            }

            .cam-actions {
                display: grid;
                grid-template-columns: 1fr;
                gap: 8px;
            }

            .cam-actions .btn {
                width: 100%;
                min-height: 40px;
                font-size: .82rem;
                white-space: normal;
            }

            .photo-wrap {
                padding: 10px;
                border-radius: 13px;
            }

            .photo-preview-img {
                width: 100%;
                max-height: 220px;
                object-fit: contain;
            }

            .modal-dialog {
                margin: 10px;
            }

            .modal .modal-content {
                border-radius: 16px;
            }
        }

        @media (max-width: 390px) {
            .wrap {
                padding-left: 4px;
                padding-right: 4px;
            }

            .top-actions {
                grid-template-columns: 1fr;
            }

            .top-actions form .btn {
                max-width: none;
            }

            .appbar h1 {
                font-size: .96rem;
            }

            .title-icon {
                display: none;
            }
        }



        /* =========================================================
           MOBILE APP-LIKE POLISH - SAVE BUTTONS + SHIFT CARDS
           UI only. Tidak mengubah ID/name/route/endpoint/JavaScript.
           Fokus HP: tombol simpan rapat, panel omset/setoran tidak mencar.
        ========================================================= */
        @media (max-width: 575.98px) {
            .appbar {
                position: sticky;
                top: 8px;
                z-index: 30;
            }

            .top-actions {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                gap: 7px !important;
                align-items: stretch;
            }

            /* Tombol simpan selalu tampil paling atas dan rapi 2 kolom */
            #btnSaveS1,
            #btnSaveS2 {
                order: -20;
                min-height: 46px !important;
                font-size: .82rem !important;
                font-weight: 900 !important;
                border-radius: 14px !important;
                box-shadow: 0 8px 18px rgba(15, 118, 110, .16);
            }

            #btnLoad,
            #btnShowOmsetRules {
                order: -10;
            }

            .top-actions a.btn,
            .top-actions button.btn,
            .top-actions form .btn {
                min-width: 0 !important;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .top-actions form {
                grid-column: auto !important;
            }

            .top-actions form .btn {
                max-width: none !important;
            }

            /* Panel Shift 1/2 dibuat seperti kartu mobile yang rapat dan full-width */
            .row.g-3 > [class*="col-"] {
                width: 100%;
                flex: 0 0 100%;
            }

            #shiftPanelS1Omset,
            #shiftPanelS2Omset,
            #shiftPanelS1Setoran,
            #shiftPanelS2Setoran {
                margin: 0;
                border-radius: 16px;
                border-color: #dbe3ef;
                background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
            }

            #shiftPanelS1Omset .panel-soft-head,
            #shiftPanelS2Omset .panel-soft-head,
            #shiftPanelS1Setoran .panel-soft-head,
            #shiftPanelS2Setoran .panel-soft-head {
                display: grid;
                grid-template-columns: 1fr auto;
                align-items: center;
            }

            #shiftPanelS1Omset .locked-note,
            #shiftPanelS2Omset .locked-note,
            #shiftPanelS1Setoran .locked-note,
            #shiftPanelS2Setoran .locked-note {
                grid-column: 1 / -1;
                justify-self: start;
                margin-top: 4px;
            }

            .cardx + .cardx {
                margin-top: 12px !important;
            }

            .section-head {
                display: grid;
                grid-template-columns: 1fr auto;
                align-items: center;
            }

            .section-head .badge-wh {
                justify-self: end;
            }

            .calc {
                min-height: 42px;
            }
        }

        @media (max-width: 390px) {
            /* Di layar sangat kecil, save tetap 2 kolom; tombol lain boleh 1 kolom agar tidak pecah. */
            .top-actions {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }

            .top-actions .btn:not(#btnSaveS1):not(#btnSaveS2),
            .top-actions form {
                grid-column: 1 / -1;
            }

            #btnSaveS1,
            #btnSaveS2 {
                grid-column: auto !important;
                padding-left: 4px !important;
                padding-right: 4px !important;
                font-size: .78rem !important;
            }
        }


        /* =========================================================
           MOBILE SHIFT FLOW + STICKY ACTION BAR
           UI only. Tidak mengubah ID/name/route/endpoint/autosave logic.
           HP: Omset S1 -> Setoran S1 -> Omset S2 -> Setoran S2.
        ========================================================= */
        @media (max-width: 575.98px) {
            body {
                padding-bottom: 92px;
            }

            .appbar {
                position: relative !important;
                top: auto !important;
                z-index: 1 !important;
            }

            .top-actions {
                position: fixed !important;
                left: 10px;
                right: 10px;
                bottom: calc(10px + env(safe-area-inset-bottom));
                z-index: 1040;
                display: grid !important;
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
                gap: 7px !important;
                padding: 8px;
                border: 1px solid rgba(15, 23, 42, .10);
                border-radius: 18px;
                background: rgba(255, 255, 255, .96);
                box-shadow: 0 16px 42px rgba(15, 23, 42, .18);
                backdrop-filter: blur(12px);
            }

            .top-actions > a,
            .top-actions > form,
            .top-actions > #btnShowOmsetRules {
                display: none !important;
            }

            #btnLoad,
            #btnSaveS1,
            #btnSaveS2 {
                display: inline-flex !important;
                width: 100% !important;
                min-height: 44px !important;
                align-items: center;
                justify-content: center;
                border-radius: 14px !important;
                padding: 7px 5px !important;
                line-height: 1.1 !important;
                white-space: normal !important;
                font-size: .75rem !important;
            }

            #btnLoad { order: 1 !important; }
            #btnSaveS1 { order: 2 !important; }
            #btnSaveS2 { order: 3 !important; }

            .mobile-shift-title {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 8px;
                margin: 4px 0 8px;
                padding: 9px 11px;
                border-radius: 14px;
                border: 1px solid #dbe3ef;
                background: #f8fafc;
                color: #0f172a;
                font-weight: 900;
                font-size: .9rem;
            }

            .mobile-shift-title .badge-wh {
                font-size: .72rem;
                padding: 4px 8px;
            }

            .mobile-order-s1-omset { order: 10; }
            .mobile-order-s1-setoran { order: 20; }
            .mobile-order-s2-omset { order: 30; }
            .mobile-order-s2-setoran { order: 40; }
            .mobile-order-summary { order: 50; }
            .mobile-order-extra { order: 60; }

            #mobileShiftFlowRow > [class*="mobile-order-"] {
                margin-bottom: 10px;
            }

            #mobileShiftFlowRow .panel-soft {
                box-shadow: 0 8px 22px rgba(15, 23, 42, .055);
            }

            .mobile-empty-setoran-shell {
                display: none !important;
            }
        }

        @media (min-width: 576px) {
            .mobile-shift-title {
                display: none !important;
            }
        }




        /* =========================================================
           MOBILE NATIVE ACTION BAR FINAL FIX
           UI only. Tidak mengubah ID/name/route/endpoint/autosave logic.
           - Semua tombol tetap muncul di HP, tidak ada yang disembunyikan.
           - Load + Simpan tetap sticky di bawah.
           - Tombol sekunder tampil sebagai shortcut kecil agar tidak hilang.
        ========================================================= */
        @media (max-width: 575.98px) {
            body {
                padding-bottom: calc(178px + env(safe-area-inset-bottom)) !important;
            }

            .top-actions {
                position: fixed !important;
                left: 8px !important;
                right: 8px !important;
                bottom: calc(8px + env(safe-area-inset-bottom)) !important;
                z-index: 1050 !important;
                display: grid !important;
                grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
                gap: 7px !important;
                padding: 9px !important;
                border-radius: 20px !important;
                border: 1px solid rgba(148, 163, 184, .30) !important;
                background: rgba(15, 23, 42, .92) !important;
                box-shadow: 0 18px 48px rgba(0, 0, 0, .28) !important;
                backdrop-filter: blur(14px) !important;
            }

            .top-actions > a,
            .top-actions > button,
            .top-actions > form {
                display: block !important;
                width: 100% !important;
                margin: 0 !important;
                min-width: 0 !important;
            }

            .top-actions > form {
                grid-column: auto !important;
            }

            .top-actions .btn,
            .top-actions form .btn {
                width: 100% !important;
                max-width: none !important;
                min-width: 0 !important;
                margin: 0 !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 5px !important;
                overflow: hidden !important;
                text-align: center !important;
                white-space: normal !important;
                line-height: 1.08 !important;
                border-radius: 15px !important;
                box-shadow: none !important;
            }

            #btnLoad {
                grid-column: 1 / -1 !important;
                order: 1 !important;
                min-height: 48px !important;
                font-size: .88rem !important;
                font-weight: 900 !important;
            }

            #btnSaveS1 {
                grid-column: 1 / 3 !important;
                order: 2 !important;
                min-height: 50px !important;
                font-size: .84rem !important;
                font-weight: 900 !important;
            }

            #btnSaveS2 {
                grid-column: 3 / 5 !important;
                order: 3 !important;
                min-height: 50px !important;
                font-size: .84rem !important;
                font-weight: 900 !important;
            }

            .top-actions a[href*="dashboard"] { order: 4 !important; }
            .top-actions a[href*="dscFormulir"] { order: 5 !important; }
            #btnShowOmsetRules { order: 6 !important; }
            .top-actions a[href*="guidebook"] { order: 7 !important; }
            .top-actions form { order: 8 !important; }

            .top-actions a.btn,
            #btnShowOmsetRules,
            .top-actions form .btn {
                min-height: 36px !important;
                padding: 6px 4px !important;
                font-size: .64rem !important;
                font-weight: 850 !important;
            }

            .top-actions a.btn i,
            #btnShowOmsetRules i,
            .top-actions form .btn i {
                margin-right: 0 !important;
                font-size: .9rem !important;
            }

            .top-actions a.btn,
            #btnShowOmsetRules,
            .top-actions form .btn {
                flex-direction: column !important;
            }

            .top-actions .btn-danger {
                background: #dc2626 !important;
                border-color: #dc2626 !important;
                color: #fff !important;
            }

            .cardx,
            .appbar {
                overflow: visible !important;
            }

            .section-head {
                position: sticky;
                top: 0;
                z-index: 3;
            }

            #mobileShiftFlowRow {
                display: flex !important;
                flex-direction: column !important;
            }

            .mobile-order-s1-omset { order: 10 !important; }
            .mobile-order-s1-setoran { order: 20 !important; }
            .mobile-order-s2-omset { order: 30 !important; }
            .mobile-order-s2-setoran { order: 40 !important; }
            .mobile-order-summary { order: 50 !important; }
            .mobile-order-extra { order: 60 !important; }
        }

        @media (max-width: 390px) {
            .top-actions {
                left: 6px !important;
                right: 6px !important;
                gap: 6px !important;
                padding: 8px !important;
            }

            #btnLoad {
                min-height: 46px !important;
            }

            #btnSaveS1,
            #btnSaveS2 {
                min-height: 48px !important;
                font-size: .78rem !important;
            }

            .top-actions a.btn,
            #btnShowOmsetRules,
            .top-actions form .btn {
                font-size: .58rem !important;
                min-height: 34px !important;
            }
        }



        /* =========================================================
           MOBILE REFINED ACTION BAR + COMPACT FORM
           UI only. Logika JS, ID, name, route, endpoint tidak diubah.
           - Hanya Load + Simpan S1/S2 yang sticky di bawah.
           - Tombol lain kembali di top bar seperti semula, rapi di HP.
           - Form dipadatkan agar terasa seperti mobile, tidak jumbo.
        ========================================================= */
        @media (max-width: 575.98px) {
            body {
                padding-bottom: calc(126px + env(safe-area-inset-bottom)) !important;
            }

            .shell {
                padding: 8px 8px calc(126px + env(safe-area-inset-bottom)) !important;
            }

            .wrap {
                padding-left: 6px !important;
                padding-right: 6px !important;
            }

            .appbar {
                padding: 11px !important;
                border-radius: 15px !important;
                position: relative !important;
                top: auto !important;
                z-index: 1 !important;
                overflow: hidden !important;
            }

            .appbar-top {
                display: grid !important;
                grid-template-columns: 1fr !important;
                gap: 10px !important;
            }

            .title-wrap {
                align-items: center !important;
                gap: 9px !important;
            }

            .appbar h1 {
                font-size: .98rem !important;
                line-height: 1.18 !important;
            }

            .sub {
                font-size: .74rem !important;
                line-height: 1.35 !important;
            }

            /* Top bar tombol sekunder tetap di atas, bukan sticky bawah */
            .top-actions {
                position: static !important;
                left: auto !important;
                right: auto !important;
                bottom: auto !important;
                z-index: auto !important;
                display: grid !important;
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                gap: 7px !important;
                padding: 0 !important;
                border: 0 !important;
                border-radius: 0 !important;
                background: transparent !important;
                box-shadow: none !important;
                backdrop-filter: none !important;
            }

            .top-actions > a,
            .top-actions > button,
            .top-actions > form {
                display: block !important;
                width: 100% !important;
                margin: 0 !important;
                min-width: 0 !important;
                grid-column: auto !important;
            }

            .top-actions .btn,
            .top-actions form .btn {
                width: 100% !important;
                max-width: none !important;
                min-width: 0 !important;
                min-height: 38px !important;
                padding: 7px 8px !important;
                border-radius: 12px !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 5px !important;
                font-size: .74rem !important;
                font-weight: 800 !important;
                line-height: 1.12 !important;
                white-space: normal !important;
                text-align: center !important;
                box-shadow: none !important;
                flex-direction: row !important;
            }

            .top-actions a[href*="dashboard"] { order: 1 !important; }
            .top-actions a[href*="dscFormulir"] { order: 2 !important; }
            #btnShowOmsetRules { order: 3 !important; }
            .top-actions a[href*="guidebook"] { order: 4 !important; }
            .top-actions form { order: 5 !important; grid-column: 1 / -1 !important; }
            .top-actions form .btn { max-width: 190px !important; margin-inline: auto !important; }

            /* Hanya 3 tombol utama ini yang sticky di bawah */
            #btnLoad,
            #btnSaveS1,
            #btnSaveS2 {
                position: fixed !important;
                z-index: 1060 !important;
                margin: 0 !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 7px !important;
                border-radius: 15px !important;
                font-weight: 900 !important;
                box-shadow: 0 12px 28px rgba(0, 0, 0, .18) !important;
                white-space: normal !important;
                line-height: 1.08 !important;
            }

            #btnLoad {
                left: 12px !important;
                right: 12px !important;
                bottom: calc(70px + env(safe-area-inset-bottom)) !important;
                width: auto !important;
                min-height: 48px !important;
                font-size: .86rem !important;
                order: 98 !important;
            }

            #btnSaveS1 {
                left: 12px !important;
                right: calc(50% + 5px) !important;
                bottom: calc(14px + env(safe-area-inset-bottom)) !important;
                width: auto !important;
                min-height: 48px !important;
                font-size: .82rem !important;
                order: 99 !important;
            }

            #btnSaveS2 {
                left: calc(50% + 5px) !important;
                right: 12px !important;
                bottom: calc(14px + env(safe-area-inset-bottom)) !important;
                width: auto !important;
                min-height: 48px !important;
                font-size: .82rem !important;
                order: 100 !important;
            }

            .cardx {
                border-radius: 15px !important;
                margin-top: 10px !important;
                overflow: hidden !important;
            }

            .section-head {
                position: static !important;
                top: auto !important;
                z-index: auto !important;
                padding: 11px 12px !important;
                display: grid !important;
                grid-template-columns: 1fr auto !important;
                gap: 8px !important;
                align-items: center !important;
            }

            .section-head .title {
                font-size: .94rem !important;
                line-height: 1.2 !important;
            }

            .badge-wh,
            .locked-note {
                padding: 4px 8px !important;
                font-size: .70rem !important;
                gap: 5px !important;
            }

            .card-body-pad {
                padding: 11px !important;
            }

            .panel-soft {
                padding: 11px !important;
                border-radius: 14px !important;
                box-shadow: 0 6px 16px rgba(17, 24, 39, .04) !important;
            }

            .panel-soft-head {
                margin-bottom: 9px !important;
                padding-bottom: 7px !important;
                gap: 7px !important;
            }

            .panel-soft-title {
                font-size: .92rem !important;
            }

            .form-label {
                font-size: .73rem !important;
                margin-bottom: .24rem !important;
                line-height: 1.25 !important;
            }

            .form-control,
            .form-select,
            .select2-container .select2-selection--single {
                height: 40px !important;
                min-height: 40px !important;
                border-radius: 12px !important;
                font-size: .88rem !important;
                padding: 7px 10px !important;
            }

            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 38px !important;
                font-size: .88rem !important;
            }

            input[type="date"].form-control {
                min-height: 40px !important;
                height: 40px !important;
            }

            .mt-2 {
                margin-top: .45rem !important;
            }

            .calc {
                min-height: 38px !important;
                padding: 8px 10px !important;
                border-radius: 12px !important;
            }

            .calc span {
                font-size: .73rem !important;
            }

            .calc b {
                font-size: .88rem !important;
            }

            .summary-grid {
                gap: 9px !important;
            }

            .summary-box {
                padding: 10px !important;
                border-radius: 13px !important;
            }

            .summary-box .label {
                font-size: .72rem !important;
            }

            .summary-box .value {
                font-size: .9rem !important;
            }

            .minihelp {
                font-size: .73rem !important;
                line-height: 1.45 !important;
            }

            .mobile-shift-title {
                margin: 3px 0 7px !important;
                padding: 8px 10px !important;
                border-radius: 13px !important;
                font-size: .86rem !important;
            }

            #mobileShiftFlowRow > [class*="mobile-order-"] {
                margin-bottom: 9px !important;
            }
        }

        @media (max-width: 390px) {
            .top-actions .btn,
            .top-actions form .btn {
                min-height: 36px !important;
                font-size: .69rem !important;
                padding-left: 6px !important;
                padding-right: 6px !important;
            }

            #btnLoad {
                left: 10px !important;
                right: 10px !important;
                min-height: 46px !important;
                font-size: .82rem !important;
            }

            #btnSaveS1,
            #btnSaveS2 {
                min-height: 46px !important;
                font-size: .78rem !important;
            }

            #btnSaveS1 {
                left: 10px !important;
                right: calc(50% + 4px) !important;
            }

            #btnSaveS2 {
                left: calc(50% + 4px) !important;
                right: 10px !important;
            }
        }


        /* Hilangkan tombol up/down di Chrome, Safari, Edge */
        input[type=number]::-webkit-outer-spin-button,
        input[type=number]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* Hilangkan spinner di Firefox */
        input[type=number] {
            -moz-appearance: textfield;
        }


        /* =========================================================
           DSC OMSET UPDATE MODAL - STABLE BOOTSTRAP POPUP
           Tidak memakai Swal agar tidak ikut tertutup oleh Swal.close() dari proses load/autosave.
        ========================================================= */
        .omset-update-modal .modal-content {
            border: 0;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 28px 90px rgba(15, 23, 42, .32);
        }

        .omset-update-modal .modal-header {
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            border-bottom: 1px solid #e5e7eb;
            padding: 18px 20px;
        }

        .omset-update-title {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 0;
            font-size: 1.05rem;
            font-weight: 900;
            color: #0f172a;
        }

        .omset-update-icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #e0f2fe;
            color: #0369a1;
            flex: 0 0 auto;
        }

        .omset-update-modal .modal-body {
            background: #f8fafc;
            padding: 18px 20px;
        }

        .omset-update-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 14px;
        }

        .omset-update-card + .omset-update-card {
            margin-top: 12px;
        }

        .omset-update-card-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 900;
            color: #0f172a;
            margin-bottom: 8px;
        }

        .omset-update-list {
            margin: 0;
            padding-left: 1.15rem;
            color: #334155;
            font-size: .9rem;
            line-height: 1.55;
            font-weight: 650;
        }

        .omset-update-list li + li {
            margin-top: 5px;
        }

        .omset-update-note {
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            border-radius: 14px;
            padding: 11px 12px;
            color: #475569;
            font-size: .86rem;
            line-height: 1.5;
            font-weight: 700;
        }

        .omset-update-modal .modal-footer {
            background: #ffffff;
            border-top: 1px solid #e5e7eb;
            padding: 14px 20px;
        }

        @media (max-width: 575.98px) {
            .omset-update-modal .modal-dialog {
                margin: 10px;
            }
            .omset-update-title {
                font-size: .98rem;
            }
            .omset-update-modal .modal-header,
            .omset-update-modal .modal-body,
            .omset-update-modal .modal-footer {
                padding-left: 14px;
                padding-right: 14px;
            }
        }

        /* =========================================================
           MOBILE SIMPLE READABLE FLOW - Anak SMP bisa baca
           UI only: tidak mengubah ID, name, route, endpoint, atau logic JS save/autosave.
        ========================================================= */
        .step-pill {
            width: 24px;
            height: 24px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-soft);
            color: var(--primary);
            border: 1px solid rgba(32, 107, 196, .18);
            font-size: .78rem;
            font-weight: 900;
            margin-right: 6px;
        }

        @media (max-width: 575.98px) {
            .appbar h1 {
                font-size: 1.02rem !important;
                letter-spacing: 0 !important;
            }

            .sub {
                font-size: .78rem !important;
                max-width: 30ch;
            }

            .top-actions {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }

            .top-actions .btn:not(#btnLoad):not(#btnSaveS1):not(#btnSaveS2) {
                min-height: 38px !important;
                border-radius: 13px !important;
                font-size: .76rem !important;
                padding: 7px 8px !important;
            }

            #btnSaveS1,
            #btnSaveS2 {
                font-size: .86rem !important;
                min-height: 50px !important;
            }

            #btnSaveS1 .bi,
            #btnSaveS2 .bi,
            #btnLoad .bi {
                font-size: 1.02rem !important;
            }

            .section-head .title {
                font-size: 1rem !important;
                font-weight: 900 !important;
            }

            .panel-soft {
                padding: 12px !important;
                border-radius: 16px !important;
                margin-bottom: 10px !important;
            }

            .panel-soft-head {
                display: grid !important;
                grid-template-columns: 1fr auto !important;
                align-items: center !important;
                border-bottom: 1px solid var(--border) !important;
                padding-bottom: 9px !important;
                margin-bottom: 10px !important;
            }

            .panel-soft-title {
                display: flex !important;
                align-items: center !important;
                font-size: 1rem !important;
                line-height: 1.2 !important;
                font-weight: 900 !important;
                color: #f8fafc !important;
            }

            .step-pill {
                width: 26px !important;
                height: 26px !important;
                font-size: .82rem !important;
                margin-right: 7px !important;
            }

            .form-label {
                font-size: .82rem !important;
                font-weight: 900 !important;
                color: #cbd5e1 !important;
                margin-top: 2px !important;
                margin-bottom: 5px !important;
            }

            .form-control,
            .form-select,
            .select2-container .select2-selection--single {
                height: 44px !important;
                min-height: 44px !important;
                font-size: .98rem !important;
                border-radius: 14px !important;
                padding: 9px 12px !important;
            }

            input[type="date"].form-control {
                height: 44px !important;
                min-height: 44px !important;
            }

            .calc {
                min-height: 44px !important;
                padding: 9px 12px !important;
                border-radius: 14px !important;
            }

            .calc span {
                font-size: .82rem !important;
                font-weight: 900 !important;
            }

            .calc b {
                font-size: .98rem !important;
                font-weight: 900 !important;
            }

            .mobile-shift-title {
                margin: 10px 0 4px !important;
                padding: 10px 12px !important;
                border-radius: 16px !important;
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                background: rgba(32, 107, 196, .12) !important;
                border: 1px solid rgba(32, 107, 196, .22) !important;
                font-weight: 900 !important;
                color: #e5edff !important;
            }

            .mobile-title-s2 {
                background: rgba(15, 118, 110, .13) !important;
                border-color: rgba(15, 118, 110, .26) !important;
            }

            .photo-wrap {
                border-radius: 15px !important;
                padding: 10px !important;
            }

            .cam-actions {
                grid-template-columns: 1fr 1fr !important;
            }

            .cam-actions .btn {
                min-height: 40px !important;
                font-size: .8rem !important;
                border-radius: 13px !important;
            }
        }

    </style>
</head>

<body>
    <main class="container wrap shell">

        {{-- TOP BAR --}}
        <div class="appbar">
            <div class="appbar-top">
                <div class="title-wrap">
                    <div class="title-icon">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div>
                        <h1>DSC • Form Omset & Setoran</h1>
                        <div class="sub">Input omset harian Shift 1 & 2, setoran sales, dan bukti foto.</div>
                    </div>
                </div>

                <div class="top-actions">
                    <a href="{{ route('investor.sales.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-speedometer2 me-1"></i>Dashboard
                    </a>

                    <a href="{{ route('master.dscFormulir.index') ?? '#' }}" class="btn btn-outline-secondary">
                        <i class="bi bi-box-seam me-1"></i>Form SO
                    </a>

                    <button class="btn btn-primary" id="btnLoad" type="button">
                        <i class="bi bi-cloud-download me-1"></i>Load
                    </button>

                    <button class="btn btn-outline-primary" id="btnShowOmsetRules" type="button">
                        <i class="bi bi-info-circle me-1"></i>Aturan
                    </button>

                    <button class="btn btn-accent" id="btnSaveS1" type="button" onclick="saveOmsetShift(1)">
                        <i class="bi bi-save me-1"></i>Simpan S1
                    </button>

                    <button class="btn btn-accent" id="btnSaveS2" type="button" onclick="saveOmsetShift(2)">
                        <i class="bi bi-save me-1"></i>Simpan S2
                    </button>

                    <a href="{{ route('master.dscFormulirOmset.guidebook') }}"
                    class="btn btn-outline-info">
                        <i class="bi bi-journal-bookmark me-1"></i>
                        Guidebook
                    </a>

                    <form action="{{ route('auth.investor.logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button class="btn btn-danger" type="submit">
                            <i class="bi bi-box-arrow-right me-1"></i>Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- KONTEX --}}
        <div class="cardx mt-3">
            <div class="section-head">
                <div class="title">Konteks</div>
                <span class="badge-wh" id="statusBadge"><i class="bi bi-info-circle"></i> Belum load</span>
            </div>

            <div class="card-body-pad">
                <div class="grid2">
                    <div>
                        <label class="form-label">Outlet <span class="text-danger">*</span></label>
                        <select id="outlet_id" class="form-select" required>
                            <option value="">-- Pilih Outlet --</option>
                            @foreach ($outlets as $o)
                                <option value="{{ $o->id }}"
                                    {{ (string) $outletId === (string) $o->id ? 'selected' : '' }}>
                                    {{ $o->nama_outlet }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid2">
                        <div>
                            <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" id="tanggal" class="form-control" value="{{ $today }}">
                        </div>
                        <div>
                            <label class="form-label">PIC <span class="text-danger">*</span></label>
                            <input type="text" id="nama_petugas" class="form-control" placeholder="Nama...">
                        </div>
                    </div>
                </div>

                <div class="minihelp mt-2">
                    Klik <b>Load</b> untuk ambil data kalau sebelumnya sudah pernah disimpan.
                </div>
            </div>
        </div>

        {{-- OMSET --}}
        <div class="cardx mt-3">
            <div class="section-head">
                <div class="title">Isi Omset</div>
                <span class="badge-wh"><i class="bi bi-receipt"></i> Omset harian</span>
            </div>

            <div class="card-body-pad">
                <div class="row g-3">

                    {{-- SHIFT 1 --}}
                    <div class="col-lg-6 mobile-order-s1-omset">
                        <div class="panel-soft" id="shiftPanelS1Omset">
                            <div class="panel-soft-head">
                                <div class="panel-soft-title"><span class="step-pill">1</span> Shift 1 - Omset</div>
                                <span class="locked-note d-none" id="lockedNoteS1Omset">Terkunci</span>
                                <span class="badge-wh">Langkah 1/4</span>
                            </div>

                            <label class="form-label">Total Transaksi</label>
                            <input type="number" class="form-control mono om" id="tt_s1" step="1" value="0" min="0" oninput="scheduleAutoSaveShift(1)" onchange="scheduleAutoSaveShift(1)">

                            <div class="mt-2">
                                <label class="form-label">Diskon</label>
                                <input type="number" class="form-control mono om" id="diskon_s1" step="1" value="0" min="0" oninput="scheduleAutoSaveShift(1)" onchange="scheduleAutoSaveShift(1)">
                            </div>

                            <div class="mt-2">
                                <label class="form-label">Non Tunai</label>
                                <input type="number" class="form-control mono om" id="nontunai_s1" step="1" value="0" min="0" oninput="scheduleAutoSaveShift(1)" onchange="scheduleAutoSaveShift(1)">
                            </div>

                            <div class="mt-2">
                                <label class="form-label">Expense</label>
                                <input type="number" class="form-control mono om" id="expense_s1" step="1" value="0" min="0" oninput="scheduleAutoSaveShift(1)" onchange="scheduleAutoSaveShift(1)">
                            </div>

                            <div class="mt-2 calc">
                                <span>Total Omset</span>
                                <b id="total_s1">0</b>
                            </div>

                            <div class="mt-2">
                                <label class="form-label">Uang Fisik</label>
                                <input type="number" class="form-control mono om" id="uangfisik_s1" step="1" value="0" min="0" oninput="scheduleAutoSaveShift(1)" onchange="scheduleAutoSaveShift(1)">
                            </div>

                            <div class="mt-2 calc">
                                <span>Selisih Kas</span>
                                <b id="cashdiff_s1">0</b>
                            </div>
                        </div>
                    </div>

                    {{-- SHIFT 2 --}}
                    <div class="col-lg-6 mobile-order-s2-omset">
                        <div class="panel-soft" id="shiftPanelS2Omset">
                            <div class="panel-soft-head">
                                <div class="panel-soft-title"><span class="step-pill">3</span> Shift 2 - Omset</div>
                                <span class="locked-note d-none" id="lockedNoteS2Omset">Terkunci</span>
                                <span class="badge-wh">Langkah 3/4</span>
                            </div>

                            <label class="form-label">Total Transaksi</label>
                            <input type="number" class="form-control mono om" id="tt_s2" step="1" value="0" min="0" oninput="scheduleAutoSaveShift(2)" onchange="scheduleAutoSaveShift(2)">

                            <div class="mt-2">
                                <label class="form-label">Diskon</label>
                                <input type="number" class="form-control mono om" id="diskon_s2" step="1" value="0" min="0" oninput="scheduleAutoSaveShift(2)" onchange="scheduleAutoSaveShift(2)">
                            </div>

                            <div class="mt-2">
                                <label class="form-label">Non Tunai</label>
                                <input type="number" class="form-control mono om" id="nontunai_s2" step="1" value="0" min="0" oninput="scheduleAutoSaveShift(2)" onchange="scheduleAutoSaveShift(2)">
                            </div>

                            <div class="mt-2">
                                <label class="form-label">Expense</label>
                                <input type="number" class="form-control mono om" id="expense_s2" step="1" value="0" min="0" oninput="scheduleAutoSaveShift(2)" onchange="scheduleAutoSaveShift(2)">
                            </div>

                            <div class="mt-2 calc">
                                <span>Total Omset</span>
                                <b id="total_s2">0</b>
                            </div>

                            <div class="mt-2">
                                <label class="form-label">Uang Fisik</label>
                                <input type="number" class="form-control mono om" id="uangfisik_s2" step="1" value="0" min="0" oninput="scheduleAutoSaveShift(2)" onchange="scheduleAutoSaveShift(2)">
                            </div>

                            <div class="mt-2 calc">
                                <span>Selisih Kas</span>
                                <b id="cashdiff_s2">0</b>
                            </div>
                        </div>
                    </div>

                    {{-- Total Omset HARIAN --}}
                    <div class="col-12 mobile-order-summary">
                        <div class="panel-soft">
                            <div class="panel-soft-head">
                                <div class="panel-soft-title">Ringkasan Harian</div>
                                <span class="badge-wh"><i class="bi bi-calculator"></i> Auto</span>
                            </div>

                            <div class="summary-grid mt-2">
                                <div class="summary-box">
                                    <div class="label">Total Omset (S1+S2)</div>
                                    <div class="value" id="total_omset_harian">0</div>
                                </div>

                                <div class="summary-box">
                                    <div class="label">Uang Fisik (S1+S2)</div>
                                    <div class="value" id="uangfisik_harian">0</div>
                                </div>

                                <div class="summary-box">
                                    <div class="label">Cash Diff (S1+S2)</div>
                                    <div class="value" id="cashdiff_harian">0</div>
                                </div>
                            </div>

                            <div class="minihelp mt-2">
                                Rumus: <span class="mono">Total Omset = TT - Diskon - NonTunai - Expense</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- SETORAN SALES --}}
        <div class="cardx mt-3 mb-4">
            <div class="section-head">
                <div class="title">Isi Setoran</div>
                <span class="badge-wh"><i class="bi bi-cash-coin"></i> Setoran sales</span>
            </div>

            <div class="card-body-pad">
                <div class="row g-3">

                    {{-- SHIFT 1 SETORAN --}}
                    <div class="col-lg-6 mobile-order-s1-setoran">
                        <div class="panel-soft" id="shiftPanelS1Setoran">
                            <div class="panel-soft-head">
                                <div class="panel-soft-title"><span class="step-pill">2</span> Shift 1 - Setoran</div>
                                <span class="locked-note d-none" id="lockedNoteS1Setoran">Terkunci</span>
                                <span class="badge-wh">Langkah 2/4</span>
                            </div>

                            <div class="calc mb-2">
                                <span>Uang Fisik</span>
                                <b id="sf_uangfisik_s1">0</b>
                            </div>

                            <label class="form-label">Hanya Selisih (Minus)</label>
                            <input type="number" class="form-control mono set" id="hanyaselisih_s1" step="1" value="0" oninput="scheduleAutoSaveShift(1)" onchange="scheduleAutoSaveShift(1)">

                            <div class="mt-2 calc">
                                <span>Yang Harus Disetor</span>
                                <b id="harussetor_s1">0</b>
                            </div>

                            <div class="mt-2">
                                <label class="form-label">Tanggal Setor</label>
                                <input type="date" class="form-control set" id="tglsetor_s1" oninput="scheduleAutoSaveShift(1)" onchange="scheduleAutoSaveShift(1)">
                            </div>

                            <div class="mt-2">
                                <label class="form-label">Sudah Disetor</label>
                                <input type="number" class="form-control mono set" id="sudahsetor_s1" step="1" value="0" oninput="scheduleAutoSaveShift(1)" onchange="scheduleAutoSaveShift(1)">
                            </div>

                            <div class="mt-2">
                                <label class="form-label">Admin / Potongan Sales</label>
                                <input type="number" class="form-control mono set" id="admin_s1" step="1" value="0" oninput="scheduleAutoSaveShift(1)" onchange="scheduleAutoSaveShift(1)">
                            </div>

                            <div class="mt-2">
                                <label class="form-label">Adjustment</label>
                                <input type="number" class="form-control mono set" id="adjust_s1" step="1" value="0" oninput="scheduleAutoSaveShift(1)" onchange="scheduleAutoSaveShift(1)">
                            </div>

                            <div class="mt-2 calc">
                                <span>Total Disetor</span>
                                <b id="totaldisetor_s1">0</b>
                            </div>

                            <div class="mt-2 calc">
                                <span>Selisih</span>
                                <b id="selisih_s1">0</b>
                            </div>

                            <div class="divider"></div>

                            <div class="fw-bold mb-2"><i class="bi bi-camera me-1"></i>Foto Bukti Setoran S1</div>

                            <div class="photo-wrap">
                                <div class="cam-actions">
                                    <input type="file" id="bukti_foto_s1_fallback" class="d-none" accept="image/*" required>

                                    <button type="button" class="btn btn-outline-primary" id="btnCamS1">
                                        <i class="bi bi-camera me-1"></i>Ambil Foto
                                    </button>

                                    <button type="button" class="btn btn-outline-warning" id="btnUploadS1">
                                        <i class="bi bi-upload me-1"></i>Upload File
                                    </button>

                                    <button type="button" class="btn btn-outline-danger d-none" id="btnClearPhotoS1">
                                        <i class="bi bi-trash me-1"></i>Hapus Foto
                                    </button>
                                </div>

                                <div class="minihelp mt-2">
                                    Foto realtime lebih dipercaya. Upload file/galeri tetap boleh, tetapi otomatis masuk status review finance.
                                </div>

                                <div id="buktiS1PreviewWrap" class="mt-3 d-none">
                                    <div class="fw-bold mb-1" id="buktiS1PreviewText"></div>
                                    <a id="buktiS1PreviewLink" href="#" target="_blank">
                                        <img id="buktiS1PreviewImg" src="" class="img-fluid photo-preview-img">
                                    </a>
                                </div>

                                <div id="buktiS1Empty" class="minihelp mt-2 photo-empty">Belum ada foto</div>
                            </div>
                        </div>
                    </div>

                    {{-- SHIFT 2 SETORAN --}}
                    <div class="col-lg-6 mobile-order-s2-setoran">
                        <div class="panel-soft" id="shiftPanelS2Setoran">
                            <div class="panel-soft-head">
                                <div class="panel-soft-title"><span class="step-pill">4</span> Shift 2 - Setoran</div>
                                <span class="locked-note d-none" id="lockedNoteS2Setoran">Terkunci</span>
                                <span class="badge-wh">Langkah 4/4</span>
                            </div>

                            <div class="calc mb-2">
                                <span>Uang Fisik</span>
                                <b id="sf_uangfisik_s2">0</b>
                            </div>

                            <label class="form-label">Hanya Selisih (Minus)</label>
                            <input type="number" class="form-control mono set" id="hanyaselisih_s2" step="1" value="0" oninput="scheduleAutoSaveShift(2)" onchange="scheduleAutoSaveShift(2)">

                            <div class="mt-2 calc">
                                <span>Yang Harus Disetor</span>
                                <b id="harussetor_s2">0</b>
                            </div>

                            <div class="mt-2">
                                <label class="form-label">Tanggal Setor</label>
                                <input type="date" class="form-control set" id="tglsetor_s2" oninput="scheduleAutoSaveShift(2)" onchange="scheduleAutoSaveShift(2)">
                            </div>

                            <div class="mt-2">
                                <label class="form-label">Sudah Disetor</label>
                                <input type="number" class="form-control mono set" id="sudahsetor_s2" step="1" value="0" oninput="scheduleAutoSaveShift(2)" onchange="scheduleAutoSaveShift(2)">
                            </div>

                            <div class="mt-2">
                                <label class="form-label">Admin / Potongan Sales</label>
                                <input type="number" class="form-control mono set" id="admin_s2" step="1" value="0" oninput="scheduleAutoSaveShift(2)" onchange="scheduleAutoSaveShift(2)">
                            </div>

                            <div class="mt-2">
                                <label class="form-label">Adjustment</label>
                                <input type="number" class="form-control mono set" id="adjust_s2" step="1" value="0" oninput="scheduleAutoSaveShift(2)" onchange="scheduleAutoSaveShift(2)">
                            </div>

                            <div class="mt-2 calc">
                                <span>Total Disetor</span>
                                <b id="totaldisetor_s2">0</b>
                            </div>

                            <div class="mt-2 calc">
                                <span>Selisih</span>
                                <b id="selisih_s2">0</b>
                            </div>

                            <div class="divider"></div>

                            <div class="fw-bold mb-2"><i class="bi bi-camera me-1"></i>Foto Bukti Setoran S2</div>

                            <div class="photo-wrap">
                                <div class="cam-actions">
                                    <input type="file" id="bukti_foto_s2_fallback" class="d-none" accept="image/*" capture="environment" required>

                                    <button type="button" class="btn btn-outline-primary" id="btnCamS2">
                                        <i class="bi bi-camera me-1"></i>Ambil Foto
                                    </button>

                                    <button type="button" class="btn btn-outline-warning" id="btnUploadS2">
                                        <i class="bi bi-upload me-1"></i>Upload File
                                    </button>

                                    <button type="button" class="btn btn-outline-danger d-none" id="btnClearPhotoS2">
                                        <i class="bi bi-trash me-1"></i>Hapus Foto
                                    </button>
                                </div>

                                <div class="minihelp mt-2">
                                    Foto realtime lebih dipercaya. Upload file/galeri tetap boleh, tetapi otomatis masuk status review finance.
                                </div>

                                <div id="buktiS2PreviewWrap" class="mt-3 d-none">
                                    <div class="fw-bold mb-1" id="buktiS2PreviewText"></div>
                                    <a id="buktiS2PreviewLink" href="#" target="_blank">
                                        <img id="buktiS2PreviewImg" src="" class="img-fluid photo-preview-img">
                                    </a>
                                </div>

                                <div id="buktiS2Empty" class="minihelp mt-2 photo-empty">Belum ada foto</div>
                            </div>
                        </div>
                    </div>

                    {{-- MODAL CAMERA --}}
                    <div class="modal fade" id="cameraModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"><i class="bi bi-camera"></i> Ambil Foto</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <div class="modal-body">
                                    <div class="alert alert-warning py-2 d-none" id="camWarn"></div>

                                    <div class="ratio ratio-4x3 camera-box">
                                        <video id="camVideo" autoplay playsinline muted
                                            style="width:100%; height:100%; object-fit:cover;"></video>
                                    </div>

                                    <canvas id="camCanvas" class="d-none"></canvas>

                                    <div class="d-flex gap-2 flex-wrap mt-3">
                                        <button type="button" class="btn btn-outline-secondary" id="btnSwitchCam">
                                            <i class="bi bi-arrow-repeat me-1"></i>Switch Kamera
                                        </button>

                                        <button type="button" class="btn btn-primary" id="btnCaptureCam">
                                            <i class="bi bi-camera-fill me-1"></i>Capture
                                        </button>

                                        <button type="button" class="btn btn-outline-danger ms-auto"
                                            data-bs-dismiss="modal">
                                            <i class="bi bi-x-circle me-1"></i>Tutup
                                        </button>
                                    </div>

                                    <div class="minihelp mt-2">
                                        Kamera live butuh HTTPS atau localhost. Kalau browser menolak, otomatis fallback ke file picker.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- EXTRA --}}
                    <div class="col-12 mobile-order-extra">
                        <div class="panel-soft">
                            <div class="panel-soft-head">
                                <div class="panel-soft-title">Catatan Tambahan</div>
                                <span class="badge-wh"><i class="bi bi-journal-text"></i> Optional</span>
                            </div>

                            <div class="row g-2 mt-1">
                                <div class="col-md-6">
                                    <label class="form-label">Akumulasi Selisih</label>
                                    <input type="number" class="form-control mono set" id="akumulasi_selisih" step="1" value="0" oninput="scheduleAutoSaveShift(1); scheduleAutoSaveShift(2)" onchange="scheduleAutoSaveShift(1); scheduleAutoSaveShift(2)">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Kurang Setoran Bulan Lalu</label>
                                    <input type="number" class="form-control mono set" id="kekurangan_bulan_lalu" step="1" value="0" oninput="scheduleAutoSaveShift(1); scheduleAutoSaveShift(2)" onchange="scheduleAutoSaveShift(1); scheduleAutoSaveShift(2)">
                                </div>
                            </div>

                            <div class="minihelp mt-2">
                                Field ini opsional untuk catatan rekap per tanggal/outlet.
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>



        {{-- POPUP UPDATE & ATURAN OMSET - Bootstrap modal, tidak terganggu Swal.close() --}}
        <div class="modal fade omset-update-modal" id="omsetUpdateRulesModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title omset-update-title">
                            <span class="omset-update-icon"><i class="bi bi-megaphone"></i></span>
                            <span>Update Terbaru Form Omset & Setoran</span>
                        </h5>
                    </div>
                    <div class="modal-body">
                        <div class="omset-update-card">
                            <div class="omset-update-card-title">
                                <i class="bi bi-wifi"></i>
                                Sebelum mulai input
                            </div>
                            <ul class="omset-update-list">
                                <li>Pastikan jaringan internet stabil. Kalau sinyal jelek, tunggu sampai koneksi normal sebelum input banyak data.</li>
                                <li>Pilih outlet, tanggal, dan PIC dengan benar lalu tekan <b>Load</b> sebelum mengisi atau mengubah data.</li>
                                <li>Jangan membuka form omset yang sama di dua tab/perangkat sekaligus agar data tidak saling menimpa.</li>
                            </ul>
                        </div>

                        <div class="omset-update-card">
                            <div class="omset-update-card-title">
                                <i class="bi bi-arrow-repeat"></i>
                                Aturan autosave & simpan
                            </div>
                            <ul class="omset-update-list">
                                <li>Angka omset dan setoran memakai autosave, tetapi tetap tekan tombol <b>Simpan Shift 1</b> atau <b>Simpan Shift 2</b> setelah selesai mengisi.</li>
                                <li>Jangan pindah halaman, refresh, atau logout ketika status masih menyimpan atau saat upload foto bukti setoran.</li>
                                <li>Kalau muncul pesan gagal simpan, jangan isi ulang berkali-kali. Screenshot pesan error lalu laporkan ke admin/SPV.</li>
                            </ul>
                        </div>

                        <div class="omset-update-card">
                            <div class="omset-update-card-title">
                                <i class="bi bi-calculator"></i>
                                Interpretasi angka
                            </div>
                            <ul class="omset-update-list">
                                <li><b>Total</b> dihitung otomatis dari: Total Transaction - Diskon - Non Tunai - Expense.</li>
                                <li><b>Selisih Kas</b> adalah selisih antara uang fisik dan total omset tunai.</li>
                                <li>Foto bukti setoran wajib jelas. Foto realtime lebih disarankan; upload dari galeri tetap boleh tetapi bisa masuk review finance.</li>
                            </ul>
                        </div>

                        <div class="omset-update-note mt-3">
                            Jika ada kolom yang terkunci atau nominal sudah terisi, jangan dipaksa dari inspect/API. Hubungi SPV atau TM Manager untuk koreksi agar perubahan tetap tercatat rapi.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary px-4" id="btnUnderstandOmsetRules" data-bs-dismiss="modal">
                            <i class="bi bi-check2-circle me-1"></i>Saya paham, lanjut input
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        (function () {
            function showOmsetRulesModal(force = false) {
                const el = document.getElementById('omsetUpdateRulesModal');
                if (!el || typeof bootstrap === 'undefined') return;

                const modal = bootstrap.Modal.getOrCreateInstance(el, {
                    backdrop: 'static',
                    keyboard: false
                });

                if (force || !el.classList.contains('show')) {
                    modal.show();
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                const btn = document.getElementById('btnShowOmsetRules');
                if (btn) {
                    btn.addEventListener('click', function () {
                        showOmsetRulesModal(true);
                    });
                }

                // Delay kecil supaya tidak bentrok dengan init Select2/load awal.
                // Popup ini memakai Bootstrap, jadi tidak akan tertutup oleh Swal.close().
                window.setTimeout(function () {
                    showOmsetRulesModal(true);
                }, 650);
            });
        })();
    </script>


    <script>
        const URL_LOAD = `{{ route('master.dscOmset.load') }}`;
        const URL_SAVE = `{{ route('master.dscOmset.save') }}`;
        console.log('DSC Omset URL_SAVE:', URL_SAVE);

        // ========= SAVE GUARD =========
        // Mencegah klik Simpan berulang dan memberi pesan jelas saat CSRF token/session kadaluarsa.
        let manualSaveRunning = false;

        function setSavingUi(isSaving, message = 'Sistem sedang proses penyimpanan, harap tunggu...') {
            manualSaveRunning = !!isSaving;
            $('#btnLoad, #btnSaveS1, #btnSaveS2, #btnCamS1, #btnCamS2, #btnUploadS1, #btnUploadS2, #btnClearPhotoS1, #btnClearPhotoS2').prop('disabled', !!isSaving);
            if (isSaving) setStatus(message, 'loading');
            else refreshButtons();
        }

        window.addEventListener('beforeunload', function (e) {
            // FINAL FIX: jangan tampilkan warning browser karena autosave background.
            // Warning cukup saat user klik Simpan manual dan request masih berjalan.
            if (manualSaveRunning) {
                e.preventDefault();
                e.returnValue = 'Data masih dalam proses penyimpanan. Tunggu sampai proses selesai.';
            }
        });

        function pickSaveErrorMessage(res, json, raw) {
            if (res && res.status === 419) {
                return 'Token keamanan halaman sudah kadaluarsa / session berubah. Sistem belum bisa menyimpan. Jangan klik berulang; refresh halaman, load data terakhir, lalu lanjutkan.';
            }
            if (res && res.status === 0) {
                return 'Koneksi terputus. Harap tunggu sampai internet stabil, lalu coba simpan lagi.';
            }
            if (json) return json.message || json.error || `HTTP ${res.status}`;
            if (raw) return `Non-JSON response (HTTP ${res.status}): ${raw.slice(0, 400)}`;
            return `HTTP ${res?.status || 'unknown'}`;
        }

        function handleSaveError(title, res, json, raw, err) {
            const msg = err?.message || pickSaveErrorMessage(res, json, raw);
            if (res && res.status === 419) {
                return Swal.fire({
                    icon: 'warning',
                    title: 'Token Kadaluarsa',
                    html: 'Simpan gagal karena token halaman sudah tidak valid.<br><b>Harap tunggu, jangan klik berulang.</b><br>Refresh halaman, klik Load, lalu cek apakah data terakhir sudah masuk.',
                    confirmButtonText: 'Refresh Sekarang',
                    confirmButtonColor: '#0f172a',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then(() => window.location.reload());
            }
            return swError(title, msg);
        }

        const photoState = {
            s1: { file: null, serverUrl: null, serverPath: null, source: null, reviewStatus: null, reviewReason: null },
            s2: { file: null, serverUrl: null, serverPath: null, source: null, reviewStatus: null, reviewReason: null }
        };

        // ========= AUTO SAVE PER SHIFT =========
        // Field form sudah pakai oninput/onchange langsung:
        // oninput="scheduleAutoSaveShift(1)" atau oninput="scheduleAutoSaveShift(2)"
        const AUTO_SAVE_ENABLED = true;
        const AUTO_SAVE_DELAY = 1200;

        const autoSaveState = {
            s1: { timer: null, running: false, queued: false },
            s2: { timer: null, running: false, queued: false }
        };

        let isApplyingLoad = false;

        const shiftLockState = { s1: false, s2: false };

        function isShiftActuallySaved(shiftKey) {
            // Shift baru dikunci kalau data sudah FULL, bukan sekadar pernah auto-save.
            // Syarat full:
            // 1) foto sudah tersimpan di server dan preview server ada
            // 2) total transaction sudah diisi
            // 3) uang fisik sudah diisi
            // 4) tanggal setor sudah diisi
            // 5) sudah disetor sudah diisi
            // Field diskon/non tunai/expense/admin/adjustment/hanya selisih boleh 0.
            if (!photoState[shiftKey] || !photoState[shiftKey].serverUrl) return false;

            const totalTransaction = toNum($(`#tt_${shiftKey}`).val());
            const uangFisik = toNum($(`#uangfisik_${shiftKey}`).val());
            const tanggalSetor = ($(`#tglsetor_${shiftKey}`).val() || '').trim();
            const sudahSetor = toNum($(`#sudahsetor_${shiftKey}`).val());

            return totalTransaction > 0
                && uangFisik > 0
                && tanggalSetor !== ''
                && sudahSetor > 0;
        }

        function setShiftLocked(shiftKey, locked) {
            // Jangan pernah lock kalau belum ada bukti serverUrl.
            locked = false;

            shiftLockState[shiftKey] = !!locked;
            const suffix = shiftKey.toUpperCase();
            const shiftNumber = shiftKey === 's1' ? 1 : 2;

            $(`#shiftPanel${suffix}Omset, #shiftPanel${suffix}Setoran`).toggleClass('shift-locked', !!locked);
            $(`#lockedNote${suffix}Omset, #lockedNote${suffix}Setoran`).toggleClass('d-none', !locked);

            const selectors = [
                `#tt_${shiftKey}`,
                `#diskon_${shiftKey}`,
                `#nontunai_${shiftKey}`,
                `#expense_${shiftKey}`,
                `#uangfisik_${shiftKey}`,
                `#hanyaselisih_${shiftKey}`,
                `#tglsetor_${shiftKey}`,
                `#sudahsetor_${shiftKey}`,
                `#admin_${shiftKey}`,
                `#adjust_${shiftKey}`,
                `#bukti_foto_${shiftKey}_fallback`
            ].join(',');

            $(selectors).prop('disabled', !!locked);
            $(`#btnCam${suffix}, #btnUpload${suffix}, #btnClearPhoto${suffix}`).prop('disabled', !!locked);
            $(`#btnSaveS${shiftNumber}`).prop('disabled', !!locked || !validateSaveHeader());
        }

        function applyShiftLocks(locks = {}) {
            setShiftLocked('s1', !!locks.s1);
            setShiftLocked('s2', !!locks.s2);
            refreshButtons();
        }

        function apiHeaders() {
            return {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            };
        }

        function swLoading(title = 'Loading...') {
            return Swal.fire({
                title,
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => Swal.showLoading()
            });
        }

        function swSuccess(title, text, opts = {}) {
            return Swal.fire({ icon: 'success', title, text, ...opts });
        }

        function swError(title, text, opts = {}) {
            return Swal.fire({ icon: 'error', title, text, ...opts });
        }

        function swWarn(title, text, opts = {}) {
            return Swal.fire({ icon: 'warning', title, text, ...opts });
        }

        function toNum(v) {
            const s = (v ?? '').toString().trim();
            if (s === '') return 0;
            const n = parseFloat(s);
            return isNaN(n) ? 0 : n;
        }

        function fmt0(n) {
            n = Number(n || 0);
            if (!isFinite(n)) n = 0;
            return String(Math.round(n));
        }

        function setStatus(text, mode = 'info') {
            const icon = mode === 'ok' ? 'check-circle' : (mode === 'bad' ? 'x-circle' : 'info-circle');
            const extraClass = mode === 'ok' ? 'status-ok' : (mode === 'bad' ? 'status-bad' : '');
            $('#statusBadge').attr('class', `badge-wh ${extraClass}`).html(`<i class="bi bi-${icon}"></i> ${text}`);
        }

        function validateLoadHeader() {
            const outlet = $('#outlet_id').val();
            const tgl = $('#tanggal').val();
            return !!(outlet && tgl);
        }

        function validateSaveHeader() {
            const outlet = $('#outlet_id').val();
            const tgl = $('#tanggal').val();
            const pic = ($('#nama_petugas').val() || '').trim();
            return !!(outlet && tgl && pic);
        }

        function refreshButtons() {
            $('#btnLoad').prop('disabled', !validateLoadHeader());
            $('#btnSaveS1').prop('disabled', !validateSaveHeader() || shiftLockState.s1);
            $('#btnSaveS2').prop('disabled', !validateSaveHeader() || shiftLockState.s2);
        }

        function calcOmsetShift(shift) {
            const tt = toNum($(`#tt_${shift}`).val());
            const disk = toNum($(`#diskon_${shift}`).val());
            const nont = toNum($(`#nontunai_${shift}`).val());
            const exp = toNum($(`#expense_${shift}`).val());

            const total = tt - disk - nont - exp;
            const uangFisik = toNum($(`#uangfisik_${shift}`).val());
            const cashDiff = uangFisik - total;

            $(`#total_${shift}`).text(fmt0(total));
            $(`#cashdiff_${shift}`).text(fmt0(cashDiff));

            return { total, uangFisik, cashDiff };
        }

        function recalcSetoran(shift, uangFisik) {
            const hanyaSelisih = toNum($(`#hanyaselisih_${shift}`).val());
            const sudahSetor = toNum($(`#sudahsetor_${shift}`).val());
            const admin = toNum($(`#admin_${shift}`).val());
            const adjust = toNum($(`#adjust_${shift}`).val());

            const harusSetor = (uangFisik || 0) - (hanyaSelisih || 0);
            const totalDisetor = (sudahSetor || 0) + (admin || 0) + (adjust || 0);
            const selisih = totalDisetor - harusSetor;

            $(`#harussetor_${shift}`).text(fmt0(harusSetor));
            $(`#totaldisetor_${shift}`).text(fmt0(totalDisetor));

            const $sel = $(`#selisih_${shift}`);
            $sel.text(fmt0(selisih));
            $sel.toggleClass('neg', selisih < 0);

            return { harusSetor, totalDisetor, selisih };
        }

        function recalcAll() {
            const s1 = calcOmsetShift('s1');
            const s2 = calcOmsetShift('s2');

            $('#total_omset_harian').text(fmt0(s1.total + s2.total));
            $('#uangfisik_harian').text(fmt0(s1.uangFisik + s2.uangFisik));
            $('#cashdiff_harian').text(fmt0(s1.cashDiff + s2.cashDiff));

            $('#sf_uangfisik_s1').text(fmt0(s1.uangFisik));
            $('#sf_uangfisik_s2').text(fmt0(s2.uangFisik));

            recalcSetoran('s1', s1.uangFisik);
            recalcSetoran('s2', s2.uangFisik);
        }

        function bindRecalc() {
            $(document).on('input change', '.om', recalcAll);
            $(document).on('input change', '.set', recalcAll);
        }

        function setEmptyPhotoMessage(shiftKey, show) {
            const id = shiftKey === 's1' ? '#buktiS1Empty' : '#buktiS2Empty';
            if (show) $(id).removeClass('d-none');
            else $(id).addClass('d-none');
        }

        function hidePhotoPreview(shiftKey) {
            $(`#bukti${shiftKey.toUpperCase()}PreviewWrap`).addClass('d-none');
            $(`#bukti${shiftKey.toUpperCase()}PreviewImg`).attr('src','');
            $(`#bukti${shiftKey.toUpperCase()}PreviewLink`).attr('href','#');
            $(`#btnClearPhoto${shiftKey.toUpperCase()}`).addClass('d-none');
            $(`#bukti${shiftKey.toUpperCase()}Empty`).removeClass('d-none');
        }

        function setPhotoPreviewFromServer(shiftKey, url) {
            $(`#bukti${shiftKey.toUpperCase()}PreviewWrap`).removeClass('d-none');
            $(`#bukti${shiftKey.toUpperCase()}PreviewImg`).attr('src', url);
            $(`#bukti${shiftKey.toUpperCase()}PreviewLink`).attr('href', url);
            $(`#bukti${shiftKey.toUpperCase()}PreviewText`).text('Bukti tersimpan (server)');
            $(`#btnClearPhoto${shiftKey.toUpperCase()}`).removeClass('d-none');
            $(`#bukti${shiftKey.toUpperCase()}Empty`).addClass('d-none');
        }

        function setPhotoPreviewFromFile(shiftKey, file, label) {
            const url = URL.createObjectURL(file);
            $(`#bukti${shiftKey.toUpperCase()}PreviewWrap`).removeClass('d-none');
            $(`#bukti${shiftKey.toUpperCase()}PreviewImg`).attr('src', url);
            $(`#bukti${shiftKey.toUpperCase()}PreviewLink`).attr('href', url);
            $(`#bukti${shiftKey.toUpperCase()}PreviewText`).text(`Preview (${label}) - sedang disimpan / belum tersimpan server`);
            $(`#btnClearPhoto${shiftKey.toUpperCase()}`).removeClass('d-none');
            setEmptyPhotoMessage(shiftKey, false);
        }

        function clearPhoto(shiftKey) {
            photoState[shiftKey].file = null;
            photoState[shiftKey].source = null;
            photoState[shiftKey].serverUrl = null;
            photoState[shiftKey].serverPath = null;
            photoState[shiftKey].reviewStatus = null;
            photoState[shiftKey].reviewReason = null;

            $(`#bukti_foto_${shiftKey}_fallback`).val('');

            hidePhotoPreview(shiftKey);
            setShiftLocked(shiftKey, false);

            saveOmsetShift(
                shiftKey === 's1' ? 1 : 2,
                {
                    silent: true,
                    reloadAfterSave: false,
                    deletePhoto: true
                }
            );
        }

        function normalizeLoadData(d = {}) {
            if (d.omset?.s1 || d.setoran?.s1) return d;

            const om = (shift) => ({
                total_transaction: d[`${shift}_total_transaction`] ?? d[`${shift}_tt`] ?? 0,
                diskon: d[`${shift}_diskon`] ?? 0,
                non_tunai: d[`${shift}_non_tunai`] ?? d[`${shift}_nontunai`] ?? 0,
                expense: d[`${shift}_expense`] ?? 0,
                uang_fisik: d[`${shift}_uang_fisik`] ?? d[`${shift}_uangfisik`] ?? 0,
            });

            const st = (shift) => ({
                hanya_selisih: d[`${shift}_hanya_selisih`] ?? d[`${shift}_hanya_selisih_minus`] ?? 0,
                tanggal_setor: d[`${shift}_tanggal_setor`] ?? d[`${shift}_tglsetor`] ?? '',
                sudah_setor: d[`${shift}_sudah_setor`] ?? d[`${shift}_sudahsetor`] ?? 0,
                admin: d[`${shift}_admin`] ?? 0,
                adjustment: d[`${shift}_adjustment`] ?? d[`${shift}_adjust`] ?? 0,
                bukti_url: d[`${shift}_bukti_url`] ?? d[`${shift}_foto_url`] ?? null,
                bukti_foto: d[`${shift}_bukti_foto`] ?? d[`${shift}_foto_path`] ?? null,
                bukti_source: d[`${shift}_bukti_source`] ?? null,
                review_status: d[`${shift}_review_status`] ?? null,
                review_reason: d[`${shift}_review_reason`] ?? null,
            });

            return {
                omset: { s1: om('s1'), s2: om('s2') },
                setoran: { s1: st('s1'), s2: st('s2') },
                extra: {
                    akumulasi_selisih: d.akumulasi_selisih ?? 0,
                    kekurangan_bulan_lalu: d.kekurangan_bulan_lalu ?? 0
                }
            };
        }

        async function loadOmset(options = {}) {
            const silentNoData = !!options.silentNoData;
            const suppressSuccess = !!options.suppressSuccess;

            if (!validateLoadHeader()) {
                if (silentNoData) return false;
                return swWarn('Lengkapi data', 'Outlet / Tanggal wajib diisi sebelum Load.');
            }

            try {
                isApplyingLoad = true;
                setStatus('Loading...', 'info');
                if (!silentNoData) {
                    swLoading('Loading data...');
                }

                const qs = new URLSearchParams({
                    outlet_id: $('#outlet_id').val(),
                    tanggal: $('#tanggal').val()
                });

                const res = await fetch(URL_LOAD + '?' + qs.toString(), {
                    headers: apiHeaders()
                });

                const ct = (res.headers.get('content-type') || '').toLowerCase();
                const json = ct.includes('application/json')
                    ? await res.json()
                    : { ok: false, message: await res.text() };

                if (!res.ok || !json.ok) throw new Error(json.message || 'Load gagal');

                if (json.meta?.mode === 'NO_DATA') {
                    resetFormToEmpty();
                    applyShiftLocks({ s1: false, s2: false });

                    // Jangan hapus PIC. User boleh langsung isi data baru lalu auto-save.
                    if (!silentNoData) {
                        Swal.close();
                    }

                    setStatus('Siap isi data baru', 'info');
                    return false;
                }

                let d = json.data || {};
                d = normalizeLoadData(d);

                $('#nama_petugas').val(d.pic ?? '');

                $('#tt_s1').val(d.omset?.s1?.total_transaction ?? 0);
                $('#diskon_s1').val(d.omset?.s1?.diskon ?? 0);
                $('#nontunai_s1').val(d.omset?.s1?.non_tunai ?? 0);
                $('#expense_s1').val(d.omset?.s1?.expense ?? 0);
                $('#uangfisik_s1').val(d.omset?.s1?.uang_fisik ?? 0);

                $('#tt_s2').val(d.omset?.s2?.total_transaction ?? 0);
                $('#diskon_s2').val(d.omset?.s2?.diskon ?? 0);
                $('#nontunai_s2').val(d.omset?.s2?.non_tunai ?? 0);
                $('#expense_s2').val(d.omset?.s2?.expense ?? 0);
                $('#uangfisik_s2').val(d.omset?.s2?.uang_fisik ?? 0);

                $('#hanyaselisih_s1').val(d.setoran?.s1?.hanya_selisih ?? 0);
                $('#tglsetor_s1').val(d.setoran?.s1?.tanggal_setor ?? '');
                $('#sudahsetor_s1').val(d.setoran?.s1?.sudah_setor ?? 0);
                $('#admin_s1').val(d.setoran?.s1?.admin ?? 0);
                $('#adjust_s1').val(d.setoran?.s1?.adjustment ?? 0);

                $('#hanyaselisih_s2').val(d.setoran?.s2?.hanya_selisih ?? 0);
                $('#tglsetor_s2').val(d.setoran?.s2?.tanggal_setor ?? '');
                $('#sudahsetor_s2').val(d.setoran?.s2?.sudah_setor ?? 0);
                $('#admin_s2').val(d.setoran?.s2?.admin ?? 0);
                $('#adjust_s2').val(d.setoran?.s2?.adjustment ?? 0);

                $('#akumulasi_selisih').val(d.extra?.akumulasi_selisih ?? 0);
                $('#kekurangan_bulan_lalu').val(d.extra?.kekurangan_bulan_lalu ?? 0);

                photoState.s1.serverUrl = d.setoran?.s1?.bukti_url ?? null;
                photoState.s1.serverPath = d.setoran?.s1?.bukti_foto ?? null;
                photoState.s1.source = d.setoran?.s1?.bukti_source ?? null;
                photoState.s1.reviewStatus = d.setoran?.s1?.review_status ?? null;
                photoState.s1.reviewReason = d.setoran?.s1?.review_reason ?? null;

                photoState.s2.serverUrl = d.setoran?.s2?.bukti_url ?? null;
                photoState.s2.serverPath = d.setoran?.s2?.bukti_foto ?? null;
                photoState.s2.source = d.setoran?.s2?.bukti_source ?? null;
                photoState.s2.reviewStatus = d.setoran?.s2?.review_status ?? null;
                photoState.s2.reviewReason = d.setoran?.s2?.review_reason ?? null;

                photoState.s1.file = null;
                photoState.s2.file = null;
                $('#bukti_foto_s1_fallback,#bukti_foto_s2_fallback').val('');

                if (photoState.s1.serverUrl) setPhotoPreviewFromServer('s1', photoState.s1.serverUrl);
                else hidePhotoPreview('s1');

                if (photoState.s2.serverUrl) setPhotoPreviewFromServer('s2', photoState.s2.serverUrl);
                else hidePhotoPreview('s2');

                recalcAll();
                applyShiftLocks({
                    s1: false,
                    s2: false
                });

                Swal.close();
                setStatus('Loaded', 'ok');

                if (!suppressSuccess && !silentNoData) {
                    await swSuccess('Loaded', 'Data berhasil di-load.', {
                        timer: 900,
                        timerProgressBar: true
                    });
                }

                return true;

            } catch (e) {
                Swal.close();
                console.error(e);
                setStatus('Gagal load', 'bad');

                if (!silentNoData) {
                    await swError('Gagal Load', e.message);
                }

                return false;
            } finally {
                isApplyingLoad = false;
                if (!silentNoData) {
                    setSavingUi(false);
                } else {
                    refreshButtons();
                }
            }
        }

        function scheduleAutoSaveShift(shiftNumber) {
            if (!AUTO_SAVE_ENABLED) return;
            if (isApplyingLoad) return;

            // Auto-save silent jangan munculkan warning kalau PIC belum diisi.
            if (!validateSaveHeader()) {
                refreshButtons();
                return;
            }

            const key = shiftNumber === 1 ? 's1' : 's2';
            if (shiftLockState[key]) return;
            const state = autoSaveState[key];

            clearTimeout(state.timer);

            state.timer = setTimeout(function () {
                saveOmsetShift(shiftNumber, {
                    silent: true,
                    reloadAfterSave: false
                });
            }, AUTO_SAVE_DELAY);
        }

        window.scheduleAutoSaveShift = scheduleAutoSaveShift;

        async function saveOmsetShift(shiftNumber, options = {}) {
            const silent = !!options.silent;

            if (!silent && manualSaveRunning) {
                return swWarn('Harap tunggu', 'Sistem masih proses penyimpanan. Jangan klik Simpan berulang.');
            }
            const reloadAfterSave = options.reloadAfterSave !== false;
            const deletePhoto = options.deletePhoto === true;

            const key = shiftNumber === 1 ? 's1' : 's2';
            const state = autoSaveState[key];

            if (shiftLockState[key]) {
                if (!silent) {
                    return swWarn('Shift terkunci', `Shift ${shiftNumber} sudah pernah disimpan. Data bisa dilihat, tapi tidak bisa diubah.`);
                }
                return;
            }

            if (state.running) {
                state.queued = true;
                return;
            }

            if (!validateSaveHeader()) {
                if (silent) return;
                return swWarn('Lengkapi data', 'Outlet / Tanggal / PIC wajib diisi sebelum Simpan.');
            }

            recalcAll();

            const fd = new FormData();

            fd.append('outlet_id', $('#outlet_id').val());
            fd.append('tanggal', $('#tanggal').val());
            fd.append('pic', ($('#nama_petugas').val() || '').trim());
            fd.append('shift', shiftNumber);

            fd.append('total_transaction', toNum($(`#tt_${key}`).val()));
            fd.append('diskon', toNum($(`#diskon_${key}`).val()));
            fd.append('non_tunai', toNum($(`#nontunai_${key}`).val()));
            fd.append('expense', toNum($(`#expense_${key}`).val()));
            fd.append('uang_fisik', toNum($(`#uangfisik_${key}`).val()));

            fd.append('hanya_selisih_minus', toNum($(`#hanyaselisih_${key}`).val()));
            fd.append('tanggal_setor', $(`#tglsetor_${key}`).val() || '');
            fd.append('sudah_disetor', toNum($(`#sudahsetor_${key}`).val()));
            fd.append('admin_pot_sales', toNum($(`#admin_${key}`).val()));
            fd.append('adjustment', toNum($(`#adjust_${key}`).val()));

            fd.append('akumulasi_selisih', toNum($('#akumulasi_selisih').val()));
            fd.append('kekurangan_bulan_lalu', toNum($('#kekurangan_bulan_lalu').val()));

            if (deletePhoto) {
                fd.append('hapus_bukti_foto', '1');
            } else if (photoState[key].file) {
                fd.append('bukti_foto', photoState[key].file);
                fd.append('bukti_source', photoState[key].source || 'upload');
            }

            try {
                state.running = true;

                setStatus(silent ? `Auto saving S${shiftNumber}...` : `Menyimpan Shift ${shiftNumber}...`, 'info');

                if (!silent) {
                    setSavingUi(true, `Menyimpan Shift ${shiftNumber}. Harap tunggu, sistem sedang proses penyimpanan...`);
                    swLoading(`Menyimpan Shift ${shiftNumber}. Harap tunggu...`);
                }

                const res = await fetch(URL_SAVE, {
                    method: 'POST',
                    headers: apiHeaders(),
                    body: fd
                });

                const ct = (res.headers.get('content-type') || '').toLowerCase();
                let raw = '';

                const json = ct.includes('application/json')
                    ? await res.json()
                    : await (async () => {
                        raw = await res.text().catch(() => '');
                        return {
                            ok: false,
                            message: pickSaveErrorMessage(res, null, raw)
                        };
                    })();

                if (!res.ok || !json.ok) {
                    if (res.status === 409) {
                        setShiftLocked(key, false);
                    }
                    const err = new Error(pickSaveErrorMessage(res, json, raw));
                    err.res = res;
                    err.json = json;
                    err.raw = raw;
                    throw err;
                }

                const out = json.data || {};

                if (deletePhoto) {
                    photoState[key].file = null;
                    photoState[key].serverUrl = null;
                    photoState[key].serverPath = null;
                    photoState[key].source = null;
                    photoState[key].reviewStatus = null;
                    photoState[key].reviewReason = null;

                    $(`#bukti_foto_${key}_fallback`).val('');
                    hidePhotoPreview(key);
                    setShiftLocked(key, false);
                    setStatus(`Foto S${shiftNumber} berhasil dihapus`, 'ok');
                } else if (out.bukti_url) {
                    photoState[key].serverUrl = out.bukti_url;
                    photoState[key].serverPath = out.bukti_foto || null;
                    photoState[key].reviewStatus = out.review_status || null;
                    photoState[key].reviewReason = out.review_reason || null;

                    setPhotoPreviewFromServer(key, out.bukti_url);

                    photoState[key].file = null;
                    $(`#bukti_foto_${key}_fallback`).val('');

                    setShiftLocked(key, isShiftActuallySaved(key));

                    setStatus(
                        silent
                            ? (isShiftActuallySaved(key) ? `Auto saved S${shiftNumber} dan terkunci` : `Auto saved S${shiftNumber}, belum full`)
                            : (isShiftActuallySaved(key) ? `Shift ${shiftNumber} tersimpan dan terkunci` : `Shift ${shiftNumber} tersimpan, belum full`),
                        'ok'
                    );
                } else {
                    setShiftLocked(key, false);
                    setStatus(silent ? `Auto save S${shiftNumber} belum lengkap` : `Shift ${shiftNumber} belum terkunci`, 'info');
                }

                if (!silent) {
                    Swal.close();
                    await swSuccess('Tersimpan', `Shift ${shiftNumber} berhasil disimpan.`, {
                        timer: 1000,
                        timerProgressBar: true
                    });
                }

                if (reloadAfterSave && !deletePhoto) {
                    await loadOmset({ silentNoData: true, suppressSuccess: true });
                }

            } catch (e) {
                if (!silent) {
                    Swal.close();
                }

                console.error(e);
                const saveErrMsg = e?.message || 'Error tidak diketahui';
                setStatus(silent ? `Auto save S${shiftNumber} gagal: ${saveErrMsg}` : 'Simpan gagal', 'bad');

                if (!silent) {
                    await handleSaveError(`Gagal Simpan Shift ${shiftNumber}`, e.res || null, e.json || null, e.raw || null, e);
                }

            } finally {
                state.running = false;

                if (state.queued) {
                    state.queued = false;
                    scheduleAutoSaveShift(shiftNumber);
                }

                if (!silent) {
                    setSavingUi(false);
                } else {
                    refreshButtons();
                }
            }
        }

        window.saveOmsetShift = saveOmsetShift;

        window.saveOmsetShift = saveOmsetShift;
        window.loadOmset = loadOmset;


        function setupMobileShiftFlow() {
            const mq = window.matchMedia('(max-width: 575.98px)');
            const omsetRow = $('#shiftPanelS1Omset').closest('.row');
            const setoranCard = $('#shiftPanelS1Setoran').closest('.cardx');
            const setoranRow = $('#shiftPanelS1Setoran').closest('.row');

            const nodes = {
                s1Omset: $('.mobile-order-s1-omset'),
                s2Omset: $('.mobile-order-s2-omset'),
                summary: $('.mobile-order-summary'),
                s1Setoran: $('.mobile-order-s1-setoran'),
                s2Setoran: $('.mobile-order-s2-setoran'),
                extra: $('.mobile-order-extra')
            };

            if (!omsetRow.length || !setoranRow.length || !nodes.s1Setoran.length || !nodes.s2Setoran.length) {
                return;
            }

            omsetRow.attr('id', 'mobileShiftFlowRow');

            const placeholders = {
                s1Setoran: $('<span data-mobile-placeholder="s1-setoran" class="d-none"></span>'),
                s2Setoran: $('<span data-mobile-placeholder="s2-setoran" class="d-none"></span>'),
                extra: $('<span data-mobile-placeholder="extra" class="d-none"></span>')
            };

            placeholders.s1Setoran.insertBefore(nodes.s1Setoran);
            placeholders.s2Setoran.insertBefore(nodes.s2Setoran);
            placeholders.extra.insertBefore(nodes.extra);

            const titleS1 = $('<div class="col-12 mobile-shift-title mobile-title-s1"><span><i class="bi bi-1-circle me-1"></i>Shift 1</span><span class="badge-wh">Omset lalu Setoran</span></div>');
            const titleS2 = $('<div class="col-12 mobile-shift-title mobile-title-s2"><span><i class="bi bi-2-circle me-1"></i>Shift 2</span><span class="badge-wh">Omset lalu Setoran</span></div>');

            let applied = false;

            function applyMobile() {
                if (applied) return;
                applied = true;

                titleS1.insertBefore(nodes.s1Omset);
                nodes.s1Setoran.insertAfter(nodes.s1Omset);
                titleS2.insertBefore(nodes.s2Omset);
                nodes.s2Setoran.insertAfter(nodes.s2Omset);
                nodes.extra.insertAfter(nodes.summary);

                setoranCard.addClass('mobile-empty-setoran-shell');
            }

            function restoreDesktop() {
                if (!applied) return;
                applied = false;

                titleS1.detach();
                titleS2.detach();
                nodes.s1Setoran.insertAfter(placeholders.s1Setoran);
                nodes.s2Setoran.insertAfter(placeholders.s2Setoran);
                nodes.extra.insertAfter(placeholders.extra);

                setoranCard.removeClass('mobile-empty-setoran-shell');
            }

            function sync() {
                if (mq.matches) {
                    applyMobile();
                } else {
                    restoreDesktop();
                }
            }

            sync();
            if (mq.addEventListener) {
                mq.addEventListener('change', sync);
            } else if (mq.addListener) {
                mq.addListener(sync);
            }
        }

        $(document).ready(function() {
            $('#outlet_id').select2({
                width: '100%',
                placeholder: '-- Pilih Outlet --'
            });

            setupMobileShiftFlow();

            $('#outlet_id,#tanggal,#nama_petugas').on('change input', function () {
                refreshButtons();

                // Kalau user sudah isi angka dulu lalu baru isi PIC,
                // auto-save langsung jalan untuk kedua shift.
                if (this.id === 'nama_petugas' && validateSaveHeader()) {
                    scheduleAutoSaveShift(1);
                    scheduleAutoSaveShift(2);
                }
            });

            $('#outlet_id,#tanggal').on('change', function () {
                // Auto cek data lama tanpa popup "Data kosong".
                // Kalau belum ada data, user tetap bisa langsung isi lalu Simpan Shift.
                loadOmset({ silentNoData: true, suppressSuccess: true });
            });

            refreshButtons();

            bindRecalc();
            recalcAll();

            bindPhotoButtons();

            $('#akumulasi_selisih,#kekurangan_bulan_lalu').on('input change', function () {
                scheduleAutoSaveShift(1);
                scheduleAutoSaveShift(2);
            });

            $('#btnLoad').off('click.manualLoad').on('click.manualLoad', function () {
                loadOmset({ silentNoData: false, suppressSuccess: false });
            });

            setStatus('Siap', 'info');

            // Auto-load silent saat halaman pertama dibuka.
            // Kalau data belum ada, tidak muncul popup.
            if ($('#outlet_id').val() && $('#tanggal').val()) {
                loadOmset({ silentNoData: true, suppressSuccess: true });
            }
        });
    </script>

    <script>
        let camModal, camStream = null;
        let camShiftKey = null;
        let camFacing = 'environment';
        const $camVideo = () => document.getElementById('camVideo');
        const $camCanvas = () => document.getElementById('camCanvas');

        function showCamWarn(msg) {
            const el = document.getElementById('camWarn');
            el.textContent = msg;
            el.classList.remove('d-none');
        }

        function hideCamWarn() {
            const el = document.getElementById('camWarn');
            el.classList.add('d-none');
            el.textContent = '';
        }

        async function stopCameraStream() {
            try {
                if (camStream) {
                    camStream.getTracks().forEach(t => t.stop());
                    camStream = null;
                }
                const v = $camVideo();
                if (v) v.srcObject = null;
            } catch (e) {
                console.warn('stopCameraStream error', e);
            }
        }

        async function startCameraStream() {
            hideCamWarn();

            if (!navigator.mediaDevices?.getUserMedia) {
                throw new Error('Browser tidak support kamera live.');
            }

            await stopCameraStream();

            const constraints = {
                video: {
                    facingMode: camFacing,
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                },
                audio: false
            };

            camStream = await navigator.mediaDevices.getUserMedia(constraints);
            $camVideo().srcObject = camStream;
        }

        function dataUrlToFile(dataUrl, filename) {
            const arr = dataUrl.split(',');
            const mime = arr[0].match(/:(.*?);/)[1];
            const bstr = atob(arr[1]);
            let n = bstr.length;
            const u8arr = new Uint8Array(n);
            while (n--) u8arr[n] = bstr.charCodeAt(n);
            return new File([u8arr], filename, { type: mime });
        }

        async function openCameraForShift(shiftKey) {
            camShiftKey = shiftKey;

            if (!camModal) camModal = new bootstrap.Modal(document.getElementById('cameraModal'));

            try {
                camFacing = 'environment';
                await startCameraStream();
                camModal.show();
            } catch (e) {
                console.warn('Kamera live gagal, fallback file picker:', e);
                $(`#bukti_foto_${shiftKey}_fallback`).click();
            }
        }

        async function switchCamera() {
            camFacing = (camFacing === 'environment') ? 'user' : 'environment';
            try {
                await startCameraStream();
            } catch (e) {
                console.warn(e);
                showCamWarn('Tidak bisa switch kamera di device/browser ini. Coba ulang atau pakai fallback.');
            }
        }

        function captureCameraFrame() {
            const video = $camVideo();
            const canvas = $camCanvas();
            if (!video || !canvas) return;

            const w = video.videoWidth || 1280;
            const h = video.videoHeight || 720;

            canvas.width = w;
            canvas.height = h;

            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, w, h);

            const dataUrl = canvas.toDataURL('image/jpeg', 0.9);
            const filename = `bukti_${camShiftKey}_${Date.now()}.jpg`;
            const file = dataUrlToFile(dataUrl, filename);

            photoState[camShiftKey].file = file;
            photoState[camShiftKey].source = 'camera';
            setPhotoPreviewFromFile(camShiftKey, file, (camFacing === 'user' ? 'Kamera Depan Realtime' : 'Kamera Belakang Realtime'));
            saveOmsetShift(camShiftKey === 's1' ? 1 : 2, {
                silent: true,
                reloadAfterSave: true
            });

            camModal.hide();
        }

        function bindPhotoFallback(shiftKey) {
            $(`#bukti_foto_${shiftKey}_fallback`).on('change', function() {
                const f = this.files?.[0] || null;
                if (!f) return;

                photoState[shiftKey].file = f;
                photoState[shiftKey].source = 'upload';
                setPhotoPreviewFromFile(shiftKey, f, 'Upload File / Galeri');

                // Upload foto harus langsung save. Lock baru terjadi setelah server mengembalikan bukti_url.
                saveOmsetShift(shiftKey === 's1' ? 1 : 2, {
                    silent: true,
                    reloadAfterSave: true
                });
            });
        }

        function bindPhotoButtons() {
            $('#btnCamS1').on('click', () => openCameraForShift('s1'));
            $('#btnCamS2').on('click', () => openCameraForShift('s2'));

            $('#btnUploadS1').on('click', () => $('#bukti_foto_s1_fallback').click());
            $('#btnUploadS2').on('click', () => $('#bukti_foto_s2_fallback').click());

            $('#btnClearPhotoS1').on('click', () => clearPhoto('s1'));
            $('#btnClearPhotoS2').on('click', () => clearPhoto('s2'));

            bindPhotoFallback('s1');
            bindPhotoFallback('s2');

            $('#btnSwitchCam').on('click', switchCamera);
            $('#btnCaptureCam').on('click', captureCameraFrame);

            document.getElementById('cameraModal').addEventListener('hidden.bs.modal', () => {
                stopCameraStream();
            });
        }
    </script>

    <script>
        function resetFormToEmpty() {
            $('#tt_s1').val(0);
            $('#diskon_s1').val(0);
            $('#nontunai_s1').val(0);
            $('#expense_s1').val(0);
            $('#uangfisik_s1').val(0);

            $('#tt_s2').val(0);
            $('#diskon_s2').val(0);
            $('#nontunai_s2').val(0);
            $('#expense_s2').val(0);
            $('#uangfisik_s2').val(0);

            $('#hanyaselisih_s1').val(0);
            $('#tglsetor_s1').val('');
            $('#sudahsetor_s1').val(0);
            $('#admin_s1').val(0);
            $('#adjust_s1').val(0);

            $('#hanyaselisih_s2').val(0);
            $('#tglsetor_s2').val('');
            $('#sudahsetor_s2').val(0);
            $('#admin_s2').val(0);
            $('#adjust_s2').val(0);

            $('#akumulasi_selisih').val(0);
            $('#kekurangan_bulan_lalu').val(0);

            photoState.s1 = { file: null, serverUrl: null, serverPath: null, source: null, reviewStatus: null, reviewReason: null };
            photoState.s2 = { file: null, serverUrl: null, serverPath: null, source: null, reviewStatus: null, reviewReason: null };

            $('#bukti_foto_s1_fallback,#bukti_foto_s2_fallback').val('');

            hidePhotoPreview('s1');
            hidePhotoPreview('s2');

            recalcAll();
            applyShiftLocks({ s1: false, s2: false });
        }
    </script>

    <script>
        // Disable scroll change pada input number
        $(document).on('wheel', 'input[type=number]', function (e) {
            $(this).blur();
        });

        // Disable arrow up/down
        $(document).on('keydown', 'input[type=number]', function (e) {
            if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
                e.preventDefault();
            }
        });
    </script>

</body>
</html>