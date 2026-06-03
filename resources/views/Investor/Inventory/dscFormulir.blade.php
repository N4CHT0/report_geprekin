<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DSC - Warehouse Input</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        :root {
            --bg: #f3f4f6;
            --card: #fff;
            --text: #111827;
            --muted: #6b7280;
            --border: #d1d5db;
            --shadow: 0 10px 24px rgba(0, 0, 0, .07);
            --radius: 14px;

            --primary: #111827;
            --accent: #0f766e;
            --warn: #64748b;
            --danger: #b91c1c;
            --soft: #f9fafb;
        }

        body {
            background: var(--bg);
            color: var(--text);
        }

        .wrap {
            max-width: 1600px;
        }

        /* TOP APP BAR */
        .appbar {
            background: linear-gradient(180deg, #ffffff 0%, #fbfbfb 100%);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 12px 14px;
        }

        .appbar h1 {
            margin: 0;
            font-weight: 900;
            letter-spacing: .2px;
            font-size: 1.1rem;
        }

        .appbar .sub {
            color: var(--muted);
            font-size: .88rem;
            margin-top: 4px;
        }

        /* HORIZONTAL CONTEXT HEADER */
        .ctx {
            margin-top: 12px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            position: static;
            top: 12px;
            z-index: 50;
        }

        .ctx-head {
            padding: 10px 12px;
            background: var(--soft);
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .ctx-title {
            font-weight: 900;
        }

        .hint {
            color: var(--muted);
            font-size: .86rem;
        }

        .badge-wh {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            border-radius: 999px;
            border: 1px solid var(--border);
            background: #fff;
            font-weight: 800;
            color: var(--muted);
            font-size: .85rem;
            white-space: nowrap;
        }

        .dot {
            width: 8px;
            height: 8px;
            border-radius: 99px;
            background: #9ca3af;
            display: inline-block;
        }

        .dot.ok {
            background: #16a34a;
        }

        .dot.bad {
            background: #dc2626;
        }

        .dot.loading {
            background: #2563eb;
        }

        .mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        }

        .btn {
            border-radius: 10px;
            font-weight: 900;
        }

        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            filter: brightness(.95);
        }

        .btn-accent {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }

        .btn-accent:hover {
            filter: brightness(.95);
            color: #fff;
        }

        .form-label {
            font-weight: 900;
            font-size: .88rem;
            margin-bottom: .25rem;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            border-color: var(--border);
            height: 42px;
            font-weight: 800;
        }

        /* Select2 height match */
        .select2-container .select2-selection--single {
            height: 42px;
            border-radius: 10px;
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 10px;
            background: #fff;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 42px;
            padding-left: 0;
            color: var(--text);
            font-weight: 800;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 42px;
        }

        /* CTX v2 */
        .ctx-v2 {
            overflow: hidden;
        }

        .ctx-head-v2 {
            padding: 12px 14px;
        }

        .ctx-body-v2 {
            padding: 14px;
        }

        .ctx-grid-v2 {
            display: grid;
            grid-template-columns: 1.2fr .9fr;
            gap: 14px;
            align-items: start;
        }

        .right-stack {
            display: grid;
            gap: 14px;
        }

        .box {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 12px;
        }

        .box-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border);
        }

        .box-head h6 {
            font-size: .82rem;
            letter-spacing: .35px;
            text-transform: uppercase;
            color: #374151;
            font-weight: 900;
            margin: 0;
        }

        .help-mini {
            color: var(--muted);
            font-size: .83rem;
            font-weight: 700;
        }

        .pill-req,
        .pill-tip,
        .pill-safe {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid var(--border);
            background: #fff;
            padding: 4px 10px;
            border-radius: 999px;
            font-weight: 900;
            font-size: .78rem;
            color: #374151;
            white-space: nowrap;
        }

        .pill-req i {
            color: #dc2626;
        }

        .pill-tip i {
            color: #0f766e;
        }

        .pill-safe i {
            color: #16a34a;
        }

        .input-group-v2 .input-group-text {
            border-radius: 10px 0 0 10px;
            border-color: var(--border);
            background: #fff;
            color: var(--muted);
        }

        .input-group-v2 .form-control {
            border-radius: 0 10px 10px 0;
        }

        .btn-lg {
            height: 48px;
            font-size: 1rem;
        }

        .scanbar {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .scanbar .form-control {
            height: 46px;
            font-size: 1rem;
        }

        .scanicon {
            width: 46px;
            height: 46px;
            border: 1px solid var(--border);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            color: var(--muted);
        }

        /* MAIN TABLE CARD */
        .maincard {
            margin-top: 12px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .main-head {
            padding: 12px;
            border-bottom: 1px solid var(--border);
            background: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .main-head .title {
            font-weight: 900;
        }

        .main-head .sub {
            color: var(--muted);
            font-size: .86rem;
        }

        .dt-wrap {
            width: 100%;
            overflow: auto;
            -webkit-overflow-scrolling: touch;
        }

        #tblDSC {
            width: 100%;
            min-width: 1400px;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 0;
        }

        #tblDSC thead th {
            position: sticky;
            top: 0;
            z-index: 20;
            background: var(--soft) !important;
            border-bottom: 1px solid var(--border) !important;
            font-weight: 900;
            font-size: .8rem;
            text-transform: uppercase;
            letter-spacing: .35px;
            white-space: nowrap;
            padding: 10px;
        }

        #tblDSC tbody td {
            white-space: nowrap;
            vertical-align: middle !important;
            padding: 8px 10px;
            font-size: .92rem;
            border-color: var(--border);
            background: #fff;
        }

        #tblDSC tbody tr:nth-child(even) td {
            background: #fcfcfd;
        }

        .num-read {
            color: var(--muted);
            font-weight: 900;
        }

        .neg {
            color: var(--danger);
            font-weight: 900;
        }

        .input-mini {
            width: 100%;
            min-width: 0;
            height: 40px;
            border-radius: 10px;
        }

        .note-mini {
            width: 100%;
            min-width: 0;
            height: 40px;
            border-radius: 10px;
        }

        th.col-no,
        td.col-no {
            width: 70px;
        }

        th.col-nama,
        td.col-nama {
            width: 320px;
        }

        #tblDSC thead th.col-no,
        #tblDSC tbody td.col-no {
            position: sticky;
            left: 0;
            z-index: 30;
            background: #fff;
        }

        #tblDSC thead th.col-nama,
        #tblDSC tbody td.col-nama {
            position: sticky;
            left: 70px;
            z-index: 30;
            background: #fff;
            box-shadow: 8px 0 0 rgba(0, 0, 0, .04);
        }

        #tblDSC thead th.col-no,
        #tblDSC thead th.col-nama {
            z-index: 40;
            background: var(--soft) !important;
        }

        /* FOOTER ACTIONS */
        .wh-footer {
            position: sticky;
            bottom: 10px;
            z-index: 30;
            margin: 12px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: rgba(255, 255, 255, .96);
            backdrop-filter: blur(10px);
            box-shadow: 0 14px 28px rgba(0, 0, 0, .12);
            padding: 10px 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .wh-footer .left {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .wh-footer .right {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        #warnEmpty {
            display: none;
            color: var(--warn);
            font-weight: 900;
        }

        @media (max-width: 1200px) {
            .ctx-grid-v2 {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width:575.98px) {
            .wh-footer {
                position: fixed;
                left: 12px;
                right: 12px;
                bottom: 12px;
                margin: 0;
            }

            main {
                padding-bottom: 120px;
            }

            .wh-footer .right .btn {
                flex: 1 1 auto;
            }
        }

        /* MOBILE CARDS */
        .card-list {
            display: grid;
            gap: 10px;
        }

        .bcard {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: var(--shadow);
            padding: 12px;
        }

        .bcard .name {
            font-weight: 900;
            margin: 0;
        }

        .bcard .meta {
            color: var(--muted);
            font-size: .88rem;
            font-weight: 800;
        }

        .summary {
            border: 1px dashed var(--border);
            border-radius: 14px;
            padding: 10px;
            background: #fafafa;
            margin-top: 10px;
        }

        .kv {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            font-size: .9rem;
            margin: 4px 0;
        }

        .kv b {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        }

        .grid2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-top: 10px;
        }

        .grid3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 8px;
            margin-top: 10px;
        }

        @media (max-width: 575.98px) {

            .grid2,
            .grid3 {
                grid-template-columns: 1fr;
            }
        }



        /* MOBILE OPTIMIZED: 1 item per screen, compact header, no long vertical list */
        .mobile-pager {
            display: none;
        }

        @media (max-width: 767.98px) {
            body {
                background: #f8fafc;
            }

            .wrap {
                padding-left: 8px !important;
                padding-right: 8px !important;
            }

            .appbar,
            .ctx,
            .maincard {
                border-radius: 12px;
                box-shadow: 0 6px 16px rgba(0, 0, 0, .06);
            }

            .appbar {
                padding: 10px;
            }

            .appbar h1 {
                font-size: 1rem;
            }

            .appbar .sub {
                display: none;
            }

            .appbar .d-flex.gap-2.flex-wrap {
                width: 100%;
                display: grid !important;
                grid-template-columns: 1fr 1fr;
                gap: 6px !important;
            }

            .appbar .btn {
                width: 100%;
                padding: 7px 8px;
                font-size: .82rem;
            }

            .ctx {
                margin-top: 8px;
            }

            .ctx-head-v2 {
                padding: 8px 10px;
            }

            .ctx-title {
                font-size: .92rem;
            }

            .hint {
                font-size: .75rem;
            }

            .badge-wh {
                padding: 4px 8px;
                font-size: .74rem;
                gap: 5px;
            }

            .ctx-body-v2 {
                padding: 10px;
            }

            .box {
                padding: 10px;
                border-radius: 12px;
            }

            .box-head {
                padding-bottom: 7px;
            }

            .box-head h6 {
                font-size: .75rem;
            }

            .form-label {
                font-size: .78rem;
            }

            .help-mini {
                display: none;
            }

            .form-control,
            .form-select,
            .select2-container .select2-selection--single {
                height: 38px;
                font-size: .9rem;
                border-radius: 9px;
            }

            .select2-container--default .select2-selection--single .select2-selection__rendered,
            .select2-container--default .select2-selection--single .select2-selection__arrow {
                line-height: 38px;
                height: 38px;
            }

            .btn-lg {
                height: 42px;
                font-size: .92rem;
            }

            .right-stack {
                gap: 8px;
            }

            .scanbar {
                gap: 7px;
            }

            .scanbar .form-control,
            .scanicon {
                height: 40px;
            }

            .scanicon {
                width: 40px;
            }

            .maincard {
                margin-top: 8px;
            }

            .main-head {
                padding: 9px 10px;
            }

            .main-head .title {
                font-size: .92rem;
            }

            .main-head .sub {
                font-size: .72rem;
            }

            .mobile-pager {
                display: grid;
                grid-template-columns: 42px 1fr 42px;
                gap: 8px;
                align-items: center;
                position: sticky;
                top: 0;
                z-index: 25;
                padding: 8px;
                margin: -8px -8px 8px;
                background: rgba(248, 250, 252, .97);
                backdrop-filter: blur(8px);
                border-bottom: 1px solid var(--border);
            }

            .mobile-pager .btn {
                height: 38px;
                padding: 0;
            }

            .mobile-jump-wrap {
                min-width: 0;
            }

            .mobile-progress {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 8px;
                font-size: .75rem;
                font-weight: 900;
                line-height: 1;
                margin-bottom: 4px;
                color: var(--muted);
            }

            #mobJump {
                height: 34px;
                min-height: 34px;
                padding: 4px 28px 4px 8px;
                font-size: .78rem;
                font-weight: 900;
                border-radius: 9px;
            }

            #cards.card-list {
                gap: 0;
            }

            #cards .bcard {
                display: none;
                box-shadow: none;
                padding: 10px;
                border-radius: 12px;
            }

            #cards .bcard.active-card {
                display: block;
            }

            .bcard .name {
                font-size: .95rem;
            }

            .bcard .meta {
                font-size: .75rem;
            }

            .summary {
                margin-top: 8px;
                padding: 8px;
                border-radius: 10px;
            }

            .kv {
                font-size: .8rem;
                margin: 2px 0;
            }

            .grid2,
            .grid3 {
                grid-template-columns: 1fr 1fr;
                gap: 7px;
                margin-top: 8px;
            }

            .grid3 > div:first-child {
                grid-column: span 2;
            }

            .wh-footer {
                left: 8px;
                right: 8px;
                bottom: 8px;
                padding: 8px;
                border-radius: 12px;
            }

            .wh-footer .left {
                width: 100%;
                font-size: .75rem;
            }

            .wh-footer .right {
                width: 100%;
                display: grid;
                grid-template-columns: 1fr 1fr 1fr;
                gap: 6px;
            }

            .wh-footer .right .btn {
                width: 100%;
                padding: 8px 6px;
                font-size: .82rem;
            }

            main {
                padding-bottom: 105px !important;
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


        /* ==========================================================
           MOBILE NEAT LAYOUT PATCH
           Fokus: header rapi, form lebih compact, card input informatif.
           ========================================================== */
        @media (max-width: 767.98px) {
            html, body {
                max-width: 100%;
                overflow-x: hidden;
            }

            body {
                background: #eef3ee;
                font-size: 13px;
            }

            .container.wrap {
                width: 100%;
                max-width: 390px;
                padding: 10px 8px 112px !important;
                margin-left: auto;
                margin-right: auto;
            }

            .appbar {
                padding: 10px;
                border-radius: 14px;
            }

            .appbar > .d-flex {
                display: grid !important;
                grid-template-columns: 1fr;
                gap: 8px !important;
            }

            .appbar h1 {
                font-size: 1rem;
                line-height: 1.05;
                max-width: 180px;
            }

            .appbar .d-flex.gap-2.flex-wrap {
                width: 100%;
                display: grid !important;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 6px !important;
            }

            .appbar .btn,
            .appbar a.btn,
            .appbar form .btn {
                min-height: 42px;
                padding: 6px 4px;
                font-size: .76rem;
                line-height: 1.05;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 3px;
                white-space: normal;
            }

            .appbar form {
                grid-column: span 3;
            }

            .appbar form .btn {
                min-height: 36px;
            }

            .ctx-head-v2 {
                display: grid;
                grid-template-columns: 1fr;
                gap: 7px;
                padding: 9px 10px;
            }

            .ctx-head-v2 > .d-flex {
                display: flex !important;
                gap: 5px !important;
                overflow-x: auto;
                flex-wrap: nowrap !important;
                padding-bottom: 1px;
                scrollbar-width: none;
            }

            .ctx-head-v2 > .d-flex::-webkit-scrollbar {
                display: none;
            }

            .badge-wh,
            #shiftAlert {
                flex: 0 0 auto;
                max-width: 100%;
                white-space: nowrap;
            }

            .ctx-body-v2 {
                padding: 8px;
            }

            .ctx-grid-v2,
            .right-stack {
                gap: 8px;
            }

            .box {
                padding: 9px;
                border-radius: 13px;
            }

            .box-head {
                padding-bottom: 7px;
                margin-bottom: 2px;
            }

            .row.g-2 {
                --bs-gutter-x: .45rem;
                --bs-gutter-y: .48rem;
            }

            .form-label {
                font-size: .76rem;
                margin-bottom: .18rem;
            }

            .input-group-v2 .input-group-text {
                padding-left: 9px;
                padding-right: 9px;
            }

            #btnLoadCta {
                height: 43px;
                margin-top: 2px;
            }

            .right-stack .box:first-child {
                padding: 8px;
            }

            .scanbar {
                gap: 6px;
            }

            .scanicon {
                width: 38px;
                height: 38px;
                flex: 0 0 38px;
            }

            .scanbar .form-control {
                height: 38px;
            }

            .maincard {
                border-radius: 14px;
                overflow: visible;
            }

            .main-head {
                padding: 9px 10px 7px;
                display: grid;
                grid-template-columns: 1fr auto;
                align-items: start;
            }

            .main-head .title {
                line-height: 1.05;
            }

            .main-head .sub {
                margin-top: 2px;
                line-height: 1.15;
                max-width: 235px;
            }

            .main-head .badge-wh {
                padding: 4px 7px;
                font-size: .7rem;
            }

            .p-2.p-sm-3.d-md-none {
                padding: 7px !important;
            }

            .mobile-pager {
                grid-template-columns: 38px minmax(0, 1fr) 38px;
                gap: 6px;
                padding: 7px;
                margin: -7px -7px 7px;
                border-radius: 0;
            }

            .mobile-pager .btn {
                height: 37px;
                border-radius: 11px;
            }

            .mobile-progress {
                font-size: .72rem;
                margin-bottom: 4px;
            }

            #mobItemName {
                max-width: 165px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                text-align: right;
            }

            #mobJump {
                height: 36px;
                min-height: 36px;
                font-size: .8rem;
                border-radius: 10px;
                background-color: #fff;
            }

            #cards .bcard {
                padding: 10px;
                border-radius: 13px;
                border-color: #d9dee7;
                box-shadow: 0 6px 16px rgba(15, 23, 42, .05);
            }

            .bcard > .d-flex:first-child {
                padding-bottom: 7px;
                border-bottom: 1px solid #eef0f3;
                margin-bottom: 8px;
            }

            .bcard .name {
                font-size: .98rem;
                line-height: 1.15;
                letter-spacing: -.1px;
            }

            .bcard .meta {
                font-size: .73rem;
                line-height: 1.15;
            }

            .summary {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 6px;
                padding: 0;
                border: 0;
                background: transparent;
                margin-top: 0;
            }

            .summary .kv {
                display: grid;
                grid-template-columns: 1fr;
                gap: 2px;
                margin: 0;
                padding: 7px 8px;
                border: 1px solid #e5e7eb;
                border-radius: 11px;
                background: #fafafa;
                min-height: 48px;
            }

            .summary .kv span {
                color: #6b7280;
                font-size: .66rem;
                font-weight: 900;
                letter-spacing: .25px;
            }

            .summary .kv b {
                font-size: .84rem;
                text-align: right;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .summary .kv:nth-child(3) {
                grid-column: span 2;
                background: #fff;
            }

            .grid2,
            .grid3 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 7px;
                margin-top: 9px;
            }

            .grid3 > div:first-child {
                grid-column: span 2;
            }

            .bcard .form-control {
                height: 39px;
                font-size: .92rem;
                font-weight: 900;
                border-radius: 10px;
            }

            .bcard .mt-2 {
                margin-top: .52rem !important;
            }

            .wh-footer {
                max-width: 374px;
                left: 50%;
                right: auto;
                transform: translateX(-50%);
                width: calc(100% - 16px);
                bottom: 8px;
                padding: 7px;
                border-radius: 14px;
                box-shadow: 0 14px 28px rgba(15, 23, 42, .16);
            }

            .wh-footer .left {
                font-size: .72rem;
                line-height: 1.15;
            }

            .wh-footer .right {
                grid-template-columns: 1fr 1fr 1fr;
                gap: 6px;
            }

            .wh-footer .right .btn {
                min-height: 38px;
                border-radius: 10px;
                font-size: .8rem;
                font-weight: 900;
            }
        }

        @media (max-width: 360px) {
            .container.wrap {
                max-width: 100%;
                padding-left: 6px !important;
                padding-right: 6px !important;
            }

            .appbar .d-flex.gap-2.flex-wrap {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .appbar form {
                grid-column: span 2;
            }

            .summary .kv b {
                font-size: .78rem;
            }

            .grid2,
            .grid3 {
                gap: 6px;
            }
        }


        /* ==========================================================
           FINAL MOBILE RESPONSIVE PATCH
           Fix: top buttons tidak tumpuk, spacing lebih rapi di layar kecil.
           ========================================================== */
        @media (max-width: 767.98px) {
            .container.wrap {
                max-width: 420px;
                padding: 8px 10px 118px !important;
            }

            .appbar {
                padding: 10px !important;
                overflow: hidden;
            }

            .appbar > .d-flex {
                display: block !important;
            }

            .appbar > .d-flex > div:first-child {
                display: block;
                width: 100%;
                margin-bottom: 8px;
            }

            .appbar h1 {
                max-width: none !important;
                width: 100%;
                font-size: 1.02rem !important;
                line-height: 1.12 !important;
                margin: 0;
            }

            .appbar .sub {
                display: none !important;
            }

            .appbar > .d-flex > .d-flex.gap-2.flex-wrap {
                width: 100%;
                display: grid !important;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 7px !important;
                align-items: stretch;
            }

            .appbar > .d-flex > .d-flex.gap-2.flex-wrap > * {
                min-width: 0;
                width: 100%;
            }

            .appbar .btn,
            .appbar a.btn,
            .appbar form .btn {
                width: 100%;
                min-height: 38px !important;
                height: 38px;
                padding: 6px 8px !important;
                font-size: .78rem !important;
                line-height: 1.05 !important;
                border-radius: 10px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 4px !important;
                white-space: nowrap !important;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .appbar .btn i,
            .appbar a.btn i,
            .appbar form .btn i {
                margin-right: 0 !important;
                flex: 0 0 auto;
            }

            .appbar form {
                grid-column: 1 / -1 !important;
                margin: 0 !important;
            }

            .appbar form .btn {
                max-width: 170px;
                margin-left: auto;
                margin-right: auto;
            }

            .ctx {
                margin-top: 8px;
                border-radius: 14px;
            }

            .ctx-title {
                font-size: .9rem;
                line-height: 1.15;
            }

            .hint {
                font-size: .73rem;
                line-height: 1.2;
            }

            .ctx-head-v2 > .d-flex {
                gap: 6px !important;
            }

            .badge-wh,
            #shiftAlert {
                padding: 5px 8px !important;
                font-size: .7rem !important;
                line-height: 1.1;
            }

            .box-head h6 {
                font-size: .72rem;
            }

            .pill-req,
            .pill-tip,
            .pill-safe {
                padding: 4px 8px;
                font-size: .7rem;
            }

            .form-control,
            .form-select,
            .select2-container .select2-selection--single {
                height: 39px !important;
                min-height: 39px !important;
                font-size: .88rem;
            }

            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 39px !important;
                font-size: .88rem;
            }

            #btnLoadCta {
                height: 42px !important;
                min-height: 42px !important;
                font-size: .88rem !important;
                border-radius: 11px !important;
            }

            #btnLoadCta span {
                font-size: .66rem !important;
                opacity: .9 !important;
            }

            .maincard {
                margin-top: 8px;
            }

            .main-head {
                grid-template-columns: 1fr;
                gap: 5px;
            }

            .main-head .sub {
                max-width: none;
                font-size: .72rem;
            }

            .main-head .badge-wh {
                width: fit-content;
            }

            .mobile-pager {
                position: sticky;
                top: 0;
                z-index: 20;
                background: #fff;
                border-bottom: 1px solid #e5e7eb;
            }

            #mobItemName {
                max-width: 100%;
                text-align: left;
            }

            .mobile-progress {
                display: flex;
                justify-content: space-between;
                gap: 8px;
                align-items: center;
            }

            .summary {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .summary .kv {
                min-height: 45px;
                padding: 7px;
            }

            .grid2,
            .grid3 {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }

            .grid3 > div:first-child {
                grid-column: span 2;
            }

            .wh-footer {
                max-width: 404px;
                width: calc(100% - 20px);
            }
        }

        @media (max-width: 340px) {
            .container.wrap {
                padding-left: 7px !important;
                padding-right: 7px !important;
            }

            .appbar > .d-flex > .d-flex.gap-2.flex-wrap {
                grid-template-columns: 1fr;
            }

            .appbar form {
                grid-column: auto !important;
            }

            .appbar form .btn {
                max-width: none;
            }

            .summary,
            .grid2,
            .grid3 {
                grid-template-columns: 1fr !important;
            }

            .grid3 > div:first-child,
            .summary .kv:nth-child(3) {
                grid-column: auto;
            }
        }


        /* ==========================================================
           HARD MOBILE RESPONSIVE FIX - APPBAR & CONTEXT
           Target class baru supaya tidak bentrok dengan Bootstrap .d-flex.
           ========================================================== */
        @media (max-width: 767.98px) {
            html, body { max-width: 100%; overflow-x: hidden; }

            main.container.wrap {
                width: 100% !important;
                max-width: 430px !important;
                padding: 8px 8px 112px !important;
                margin-left: auto !important;
                margin-right: auto !important;
            }

            .appbar {
                padding: 10px !important;
                border-radius: 14px !important;
                overflow: hidden !important;
            }

            .appbar-inner {
                display: grid !important;
                grid-template-columns: 1fr !important;
                gap: 10px !important;
                align-items: stretch !important;
            }

            .appbar-inner > div:first-child {
                width: 100% !important;
                min-width: 0 !important;
            }

            .appbar h1 {
                display: block !important;
                width: 100% !important;
                max-width: none !important;
                font-size: 1rem !important;
                line-height: 1.12 !important;
                margin: 0 !important;
                overflow-wrap: anywhere !important;
            }

            .appbar .sub { display: none !important; }

            .appbar-actions {
                width: 100% !important;
                min-width: 0 !important;
                display: grid !important;
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                gap: 8px !important;
            }

            .appbar-actions > *,
            .appbar-actions form {
                width: 100% !important;
                min-width: 0 !important;
                margin: 0 !important;
            }

            .appbar-actions .btn,
            .appbar-actions a.btn,
            .appbar-actions form .btn {
                width: 100% !important;
                min-width: 0 !important;
                height: 42px !important;
                min-height: 42px !important;
                padding: 6px 8px !important;
                border-radius: 11px !important;
                font-size: .78rem !important;
                line-height: 1.05 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 5px !important;
                white-space: nowrap !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
            }

            .appbar-actions .btn i,
            .appbar-actions a.btn i,
            .appbar-actions form .btn i {
                margin-right: 0 !important;
                flex: 0 0 auto !important;
            }

            .appbar-actions form {
                grid-column: 1 / -1 !important;
            }

            .appbar-actions form .btn {
                max-width: 190px !important;
                margin-left: auto !important;
                margin-right: auto !important;
            }

            .ctx-head,
            .ctx-head-v2 {
                padding: 10px !important;
                display: grid !important;
                grid-template-columns: 1fr !important;
                gap: 8px !important;
            }

            .ctx-head-v2 > .d-flex,
            .ctx-head > .d-flex {
                width: 100% !important;
                display: flex !important;
                flex-wrap: nowrap !important;
                gap: 6px !important;
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch !important;
                padding-bottom: 2px !important;
            }

            .badge-wh {
                max-width: 100% !important;
                font-size: .72rem !important;
                padding: 5px 8px !important;
                flex: 0 0 auto !important;
            }

            .box { padding: 10px !important; border-radius: 14px !important; }
            .box-head { padding-bottom: 8px !important; }
            .form-control, .form-select { height: 39px !important; font-size: .86rem !important; }
            .btn-lg { height: 44px !important; min-height: 44px !important; font-size: .88rem !important; }
        }

        @media (max-width: 360px) {
            .appbar-actions { grid-template-columns: 1fr !important; }
            .appbar-actions form .btn { max-width: none !important; }
        }


        /* ==========================================================
           DSC HISTORY MODAL - CLEAN AUDIT STYLE (NO ORANGE)
           ========================================================== */
        .btn-history {
            background: #0f172a;
            border-color: #0f172a;
            color: #fff;
        }
        .btn-history:hover,
        .btn-history:focus {
            background: #1e293b;
            border-color: #1e293b;
            color: #fff;
        }

        .dsc-history-modal .modal-dialog {
            max-width: min(1380px, calc(100vw - 28px));
        }

        .dsc-history-modal .modal-content {
            border: 0;
            border-radius: 18px;
            overflow: hidden;
            background: #f8fafc;
            box-shadow: 0 28px 90px rgba(15, 23, 42, .34);
        }

        .dsc-history-modal .modal-header {
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            border-bottom: 1px solid #e5e7eb;
            padding: 18px 20px;
        }

        .dsc-history-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.08rem;
            font-weight: 950;
            color: #0f172a;
            margin: 0;
        }

        .dsc-history-title .title-icon {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #e2e8f0;
            color: #0f172a;
        }

        .dsc-history-subtitle {
            margin-top: 6px;
            color: #64748b;
            font-size: .88rem;
            font-weight: 800;
        }

        .dsc-history-modal .modal-body {
            background: #f8fafc;
            padding: 16px 18px 0;
        }

        .history-filter-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 14px;
            box-shadow: 0 10px 22px rgba(15, 23, 42, .04);
        }

        .history-filter-grid {
            display: grid;
            grid-template-columns: 1.35fr .8fr .9fr .9fr auto;
            gap: 12px;
            align-items: end;
        }

        .history-filter-label {
            font-size: .76rem;
            font-weight: 950;
            color: #0f172a;
            margin-bottom: 6px;
        }

        .history-filter-card .form-control,
        .history-filter-card .form-select {
            height: 42px;
            border-radius: 12px;
            border-color: #cbd5e1;
            color: #0f172a;
            font-weight: 850;
        }

        .history-search-row {
            margin-top: 12px;
            display: grid;
            grid-template-columns: minmax(260px, 1fr) auto;
            gap: 12px;
            align-items: center;
        }

        .history-search-wrap {
            position: relative;
        }

        .history-search-wrap i {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            pointer-events: none;
        }

        .history-search-wrap input {
            padding-left: 40px;
        }

        .history-layout {
            margin-top: 14px;
            display: grid;
            grid-template-columns: minmax(0, 1fr) 340px;
            gap: 14px;
            align-items: stretch;
        }

        .history-table-shell,
        .history-detail-panel {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 22px rgba(15, 23, 42, .035);
        }

        .history-table-scroll {
            max-height: min(58vh, 630px);
            overflow: auto;
            -webkit-overflow-scrolling: touch;
        }

        #tblHistoryDSC {
            min-width: 900px;
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        #tblHistoryDSC thead th {
            position: sticky;
            top: 0;
            z-index: 5;
            background: #0f172a !important;
            color: #fff;
            border-color: rgba(255,255,255,.14) !important;
            font-size: .78rem;
            font-weight: 950;
            letter-spacing: .12px;
            white-space: nowrap;
            padding: 12px;
        }

        #tblHistoryDSC tbody td {
            padding: 12px;
            vertical-align: middle;
            border-color: #e5e7eb;
            color: #0f172a;
            font-size: .88rem;
            background: #fff;
        }

        #tblHistoryDSC tbody tr:nth-child(even) td {
            background: #fbfdff;
        }

        #tblHistoryDSC tbody tr.history-clickable {
            cursor: pointer;
        }

        #tblHistoryDSC tbody tr.history-clickable:hover td,
        #tblHistoryDSC tbody tr.history-active td {
            background: #eef2ff !important;
        }

        .history-time {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-weight: 850;
            color: #334155;
            white-space: nowrap;
        }

        .history-user-main {
            font-weight: 950;
            color: #0f172a;
            line-height: 1.15;
        }

        .history-user-sub {
            margin-top: 4px;
            display: inline-flex;
            align-items: center;
            max-width: 170px;
            padding: 3px 7px;
            border-radius: 999px;
            background: #f1f5f9;
            color: #475569;
            font-size: .68rem;
            font-weight: 950;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .history-change-title {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            font-weight: 950;
            color: #0f172a;
        }

        .history-change-meta {
            margin-top: 4px;
            color: #64748b;
            font-size: .78rem;
            font-weight: 800;
        }

        .history-diff {
            margin-top: 7px;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .history-old-value,
        .history-new-value {
            display: inline-block;
            max-width: 220px;
            padding: 5px 8px;
            border-radius: 10px;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-weight: 900;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .history-old-value {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .history-new-value {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #bbf7d0;
        }

        .history-arrow {
            color: #64748b;
            font-weight: 950;
        }

        .history-status-badge {
            display: inline-flex;
            align-items: center;
            padding: 5px 10px;
            border-radius: 999px;
            font-weight: 950;
            font-size: .72rem;
            line-height: 1;
            white-space: nowrap;
        }

        .history-status-badge.draft {
            background: #dbeafe;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }
        .history-status-badge.final {
            background: #dcfce7;
            color: #047857;
            border: 1px solid #bbf7d0;
        }
        .history-status-badge.other {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .history-detail-btn {
            border-color: #cbd5e1;
            color: #0f172a;
            background: #fff;
            white-space: nowrap;
        }

        .history-detail-panel {
            padding: 14px;
            max-height: min(58vh, 630px);
            overflow: auto;
        }

        .history-detail-title {
            font-weight: 950;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .history-detail-sub {
            color: #64748b;
            font-weight: 800;
            font-size: .82rem;
            margin-bottom: 12px;
        }

        .history-detail-empty {
            color: #64748b;
            font-weight: 850;
            padding: 22px 4px;
            text-align: center;
        }

        .history-kv {
            display: grid;
            grid-template-columns: 108px 1fr;
            gap: 10px;
            padding: 9px 0;
            border-bottom: 1px solid #eef2f7;
            font-size: .84rem;
        }

        .history-kv span:first-child {
            color: #64748b;
            font-weight: 900;
        }

        .history-kv span:last-child {
            color: #0f172a;
            font-weight: 850;
            word-break: break-word;
        }

        .history-device-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 9px;
            border-radius: 999px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #334155;
            font-weight: 950;
            font-size: .76rem;
        }

        .history-mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        }

        .history-footbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 12px 4px 16px;
            color: #64748b;
            font-weight: 850;
            font-size: .86rem;
        }

        .history-pagination {
            display: flex;
            gap: 6px;
            align-items: center;
            flex-wrap: wrap;
        }

        .history-pagination .btn {
            min-width: 36px;
            height: 34px;
            padding: 5px 9px;
            border-radius: 10px;
            font-weight: 950;
        }

        .history-pagination .btn.active,
        .history-pagination .btn-primary {
            background: #0f172a;
            border-color: #0f172a;
            color: #fff;
        }

        .history-empty {
            padding: 38px 12px !important;
            color: #64748b !important;
            text-align: center;
            font-weight: 850;
        }

        .history-action-btn {
            background: #0f172a;
            border-color: #0f172a;
            color: #fff;
        }
        .history-action-btn:hover,
        .history-action-btn:focus {
            background: #1e293b;
            border-color: #1e293b;
            color: #fff;
        }

        @media (max-width: 1100px) {
            .history-layout {
                grid-template-columns: 1fr;
            }
            .history-detail-panel {
                max-height: none;
            }
        }

        @media (max-width: 991.98px) {
            .history-filter-grid {
                grid-template-columns: 1fr 1fr;
            }
            .history-filter-grid .history-refresh-wrap {
                grid-column: span 2;
            }
            .history-search-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 575.98px) {
            .dsc-history-modal .modal-dialog {
                max-width: none;
                width: 100%;
                height: 100%;
                margin: 0;
            }
            .dsc-history-modal .modal-content {
                min-height: 100vh;
                border-radius: 0;
            }
            .history-filter-grid {
                grid-template-columns: 1fr;
            }
            .history-filter-grid .history-refresh-wrap {
                grid-column: auto;
            }
            .history-table-scroll {
                max-height: 55vh;
            }
            .history-footbar {
                align-items: flex-start;
                flex-direction: column;
            }
        }

    </style>
</head>

<body>
    <main class="container py-3 wrap">

        <!-- APP BAR (TOP BAR) -->
        <div class="appbar">
            <div class="appbar-inner d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h1>DSC • Warehouse Input</h1>
                    <div class="sub">
                        Rumus:
                        <span class="mono">Total = Open + Purchase + MutIn - MutOut + Adjustment</span>,
                        <span class="mono">Used = Total - Ending</span>,
                        <span class="mono">Actual Tepung = Used - (WasteProd+WasteBahan)</span>
                    </div>
                </div>

                <div class="appbar-actions d-flex gap-2 flex-wrap">
                    <button class="btn btn-outline-secondary" id="btnReset" type="button">
                        <i class="bi bi-eraser me-1"></i>Reset
                    </button>

                    <a href="{{ route('investor.sales.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-speedometer2 me-1"></i>Dashboard
                    </a>

                    <a href="{{ route('master.dscFormulirOmset.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-currency-dollar me-1"></i>Form Omset
                    </a>

                    <a href="{{ route('master.dscGuidebook.formulir') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-journal-richtext me-1"></i>Guidebook
                    </a>

                    <!-- IMPORTANT: UNIQUE ID -->
                    <button class="btn btn-primary" id="btnLoadNav" type="button">
                        <i class="bi bi-cloud-download me-1"></i>Load
                    </button>

                    <button class="btn btn-history" id="btnHistoryNav" type="button">
                        <i class="bi bi-clock-history me-1"></i>History
                    </button>

                    <form action="{{ route('auth.investor.logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button class="btn btn-danger" type="submit">
                            <i class="bi bi-box-arrow-right me-1"></i>Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- HORIZONTAL CONTEXT (TOP) -->
        <section class="ctx ctx-v2">
            <div class="ctx-head ctx-head-v2">
                <div>
                    <div class="ctx-title d-flex align-items-center gap-2">
                        <i class="bi bi-sliders2-vertical"></i>
                        Konteks Input
                    </div>
                    <div class="hint" id="infoText">Pilih outlet/tanggal/shift/petugas lalu Load.</div>
                </div>

                <div class="d-flex gap-2 flex-wrap align-items-center">
                    <span class="badge-wh" id="countBadge"><i class="bi bi-box-seam"></i> 0</span>
                    <span class="badge-wh" title="Auto dihitung dari baris 'Tepung Breader'">
                        <i class="bi bi-flower1"></i>
                        Actual Tepung: <span class="mono" id="actualTepungLabel">0.00</span>
                    </span>
                    <span class="badge-wh" id="statusBadge"><span class="dot"></span> Belum load</span>
                    <span class="badge-wh" id="lastSavedBadge"><i class="bi bi-cloud-check"></i> Belum tersimpan</span>
                    <span class="badge-wh" id="shiftAlert" style="display:none"></span>
                </div>
            </div>

            <div class="ctx-body ctx-body-v2">
                <div class="ctx-grid-v2">

                    <!-- PANEL: FORM KONTEX -->
                    <div class="box">
                        <div class="box-head">
                            <h6>1) Isi Konteks</h6>
                            <span class="pill-req"><i class="bi bi-asterisk"></i> wajib</span>
                        </div>

                        <div class="row g-2 mt-1">
                            <div class="col-12">
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
                                <div class="help-mini">Pilih outlet terlebih dulu agar data load sesuai lokasi.</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" id="tanggal" class="form-control" value="{{ $today }}">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Shift <span class="text-danger">*</span></label>
                                <select id="shift" class="form-select" required>
                                    <option value="1" {{ (string) $shift === '1' ? 'selected' : '' }}>Shift 1
                                    </option>
                                    <option value="2" {{ (string) $shift === '2' ? 'selected' : '' }}>Shift 2
                                    </option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Petugas <span class="text-danger">*</span></label>
                                <div class="input-group input-group-v2">
                                    <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                    <input type="text" id="nama_petugas" class="form-control"
                                        placeholder="Nama petugas..." value="{{ request('pic') ?? '' }}">
                                </div>
                                <div class="help-mini">Wajib diisi agar <b>Load</b> & <b>Simpan</b> aktif.</div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Uang Plus Shift Ini</label>
                                <div class="input-group input-group-v2">
                                    <span class="input-group-text"><i class="bi bi-cash-coin"></i></span>
                                    <input type="number" id="uang_plus_shift" class="form-control mono" step="0.01" value="0">
                                </div>
                                <div class="help-mini">Cukup isi sekali untuk shift aktif. Nilai ini otomatis disimpan pada satu baris agar report tidak dobel.</div>
                            </div>
                        </div>

                        <!-- CTA Load: UNIQUE ID -->
                        <div class="mt-3">
                            <button class="btn btn-primary btn-lg w-100" id="btnLoadCta" type="button">
                                <i class="bi bi-cloud-download me-1"></i> Load Data
                                <span class="ms-2" style="font-size:.78rem; font-weight:800; opacity:.8;">Ambil
                                    bahan & stok awal</span>
                            </button>
                        </div>
                    </div>

                    <!-- PANEL: SEARCH + QUICK ACTIONS -->
                    <div class="right-stack">

                        <!-- SEARCH -->
                        <div class="box">
                            <div class="box-head">
                                <h6>2) Scan / Search</h6>
                                <span class="pill-tip"><i class="bi bi-lightbulb"></i> tip</span>
                            </div>

                            <div class="scanbar mt-2">
                                <div class="scanicon"><i class="bi bi-upc-scan"></i></div>
                                <input id="searchAny" class="form-control" placeholder="Scan / cari bahan...">
                            </div>
                            <div class="help-mini mt-2">
                                Gunakan search seperti scanner untuk lompat ke input <b>Ending</b> lebih cepat.
                            </div>
                        </div>

                        <!-- QUICK ACTIONS -->
                        <div class="box">
                            <div class="box-head">
                                <h6>3) Aksi Cepat</h6>
                                <span class="pill-safe"><i class="bi bi-shield-check"></i> aman</span>
                            </div>

                            <div class="row g-2 mt-2">
                                <div class="col-12 col-md-6">
                                    <button class="btn btn-outline-secondary w-100" id="btnNextZeroDesk"
                                        type="button">
                                        <i class="bi bi-arrow-right-circle me-1"></i> Next (isi 0)
                                    </button>
                                </div>
                                <div class="col-12 col-md-6">
                                    <button class="btn btn-outline-primary w-100" id="btnSaveDraftDesk"
                                        type="button">
                                        <i class="bi bi-pencil-square me-1"></i> Save Draft
                                    </button>
                                </div>
                                <div class="col-12">
                                    <!-- FINAL: UNIQUE ID -->
                                    <button class="btn btn-accent btn-lg w-100" id="btnSaveFinalDesk" type="button">
                                        <i class="bi bi-save me-1"></i> Simpan (FINAL)
                                        <span class="ms-2"
                                            style="font-size:.78rem; font-weight:800;">Shift 1+2</span>
                                    </button>
                                </div>
                            </div>

                            <div class="help-mini mt-2">Shift 1 = Draft. Shift 2 = Draft / Final.</div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- MAIN LIST (FULL WIDTH) -->
        <section class="maincard">
            <div class="main-head">
                <div>
                    <div class="title">Daftar Bahan</div>
                    <div class="sub">NO & NAMA fixed. Kolom OPEN/Total/Used/Waste/Actual auto hitung.</div>
                </div>
                <span class="badge-wh"><i class="bi bi-table"></i> Input Mode</span>
            </div>

            <!-- DESKTOP TABLE -->
            <div class="p-0 d-none d-md-block">
                <div class="dt-wrap">
                    <table id="tblDSC" class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th class="col-no">No</th>
                                <th class="col-nama">Nama</th>
                                <th style="width:80px;">Sat</th>

                                <th style="width:120px;" title="OPEN = stok awal otomatis.">Open</th>
                                <th style="width:140px;" title="PURCHASE IN = barang masuk dari pembelian.">Purchase
                                    In</th>
                                <th style="width:140px;" title="MUTASI IN = perpindahan stok masuk.">Mutasi In</th>
                                <th style="width:140px;" title="MUTASI OUT = perpindahan stok keluar.">Mutasi Out</th>

                                <th style="width:130px;" title="TOTAL = Open + Purchase + MutIn - MutOut">Total</th>

                                <th style="width:140px;" title="ENDING = stok akhir fisik.">Ending</th>
                                <th style="width:140px;" title="ACTUAL USED = Total - Ending">Actual Used</th>

                                <th style="width:140px;">Waste Prod</th>
                                <th style="width:140px;">Waste Bahan</th>
                                <th style="width:140px;" title="Waste Tepung = WasteProd + WasteBahan">Waste Tepung
                                </th>

                                <th style="width:140px;" title="Khusus 'Tepung Breader'">Actual Tepung</th>

                                <th style="width:280px;">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="15" class="text-center text-muted p-4">Klik <b>Load</b> dulu.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="wh-footer">
                    <div class="left">
                        <span id="warnEmpty"><i class="bi bi-exclamation-triangle me-1"></i> Ending masih ada yang
                            kosong.</span>
                    </div>
                    <div class="right">
                        <button class="btn btn-outline-secondary" id="btnNextZeroFoot" type="button">
                            <i class="bi bi-arrow-right-circle me-1"></i>Next (isi 0)
                        </button>
                        <button class="btn btn-history" id="btnHistoryFoot" type="button">
                            <i class="bi bi-clock-history me-1"></i>History
                        </button>
                        <button class="btn btn-outline-primary" id="btnSaveDraftFoot" type="button">
                            <i class="bi bi-pencil-square me-1"></i>Save Draft
                        </button>
                        <!-- FINAL: UNIQUE ID -->
                        <button class="btn btn-accent" id="btnSaveFinalFoot" type="button">
                            <i class="bi bi-save me-1"></i>FINAL
                        </button>
                    </div>
                </div>
            </div>

            <!-- MOBILE CARDS -->
            <div class="p-2 p-sm-3 d-md-none">
                <div class="mobile-pager" id="mobilePager">
                    <button class="btn btn-outline-secondary" id="btnMobPrev" type="button" title="Sebelumnya">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <div class="mobile-jump-wrap">
                        <div class="mobile-progress">
                            <span id="mobProgress">0 / 0</span>
                            <span id="mobItemName">Belum load</span>
                        </div>
                        <select id="mobJump" class="form-select" disabled>
                            <option value="">Load dulu</option>
                        </select>
                    </div>
                    <button class="btn btn-outline-secondary" id="btnMobNext" type="button" title="Berikutnya">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>

                <div id="cards" class="card-list">
                    <div class="text-center text-muted p-4">Klik <b>Load</b> dulu.</div>
                </div>

                <div class="wh-footer">
                    <div class="left">
                        <span id="warnEmptyM"><i class="bi bi-exclamation-triangle me-1"></i> Ending masih ada yang
                            kosong.</span>
                    </div>
                    <div class="right">
                        <button class="btn btn-outline-secondary" id="btnNextZeroMob" type="button">Next</button>
                        <button class="btn btn-history" id="btnHistoryMob" type="button">History</button>
                        <button class="btn btn-outline-primary" id="btnSaveDraftM" type="button">Draft</button>
                        <!-- FINAL: UNIQUE ID -->
                        <button class="btn btn-accent" id="btnSaveFinalMob" type="button">FINAL</button>
                    </div>
                </div>
            </div>

        </section>


        <!-- MODAL HISTORY DSC - clean audit style -->
        <div class="modal fade dsc-history-modal" id="historyModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="dsc-history-title">
                                <span class="title-icon"><i class="bi bi-clock-history"></i></span>
                                History Perubahan DSC
                            </h5>
                            <div class="dsc-history-subtitle" id="historySummary">
                                Pilih outlet/tanggal lalu klik Refresh.
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="history-filter-card">
                            <div class="history-filter-grid">
                                <div>
                                    <div class="history-filter-label">Filter Bahan</div>
                                    <select id="historyBahan" class="form-select">
                                        <option value="">Semua bahan</option>
                                    </select>
                                </div>
                                <div>
                                    <div class="history-filter-label">Shift</div>
                                    <select id="historyShift" class="form-select">
                                        <option value="">Semua shift</option>
                                        <option value="1">Shift 1</option>
                                        <option value="2">Shift 2</option>
                                    </select>
                                </div>
                                <div>
                                    <div class="history-filter-label">Dari Tanggal</div>
                                    <input type="date" id="historyDateFrom" class="form-control">
                                </div>
                                <div>
                                    <div class="history-filter-label">Sampai Tanggal</div>
                                    <input type="date" id="historyDateTo" class="form-control">
                                </div>
                                <div class="history-refresh-wrap">
                                    <button type="button" class="btn history-action-btn w-100" id="btnHistoryRefresh">
                                        <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                                    </button>
                                </div>
                            </div>

                            <div class="history-search-row">
                                <div class="history-search-wrap">
                                    <i class="bi bi-search"></i>
                                    <input type="search" id="historySearch" class="form-control"
                                        placeholder="Cari petugas, bahan, kolom, nilai, IP, device...">
                                </div>
                                <button type="button" class="btn btn-outline-secondary" id="btnHistoryReset">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Filter
                                </button>
                            </div>
                        </div>

                        <div class="history-layout">
                            <div class="history-table-shell">
                                <div class="history-table-scroll">
                                    <table class="table table-bordered align-middle" id="tblHistoryDSC">
                                        <thead>
                                            <tr>
                                                <th style="width:58px">No</th>
                                                <th style="width:150px">Jam</th>
                                                <th style="width:190px">Petugas / User</th>
                                                <th>Perubahan</th>
                                                <th style="width:100px">Status</th>
                                                <th style="width:100px">Detail</th>
                                            </tr>
                                        </thead>
                                        <tbody id="historyBody">
                                            <tr>
                                                <td colspan="6" class="history-empty">Belum ada data history.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <aside class="history-detail-panel" id="historyDetailPanel">
                                <div class="history-detail-title">Detail audit</div>
                                <div class="history-detail-sub">Klik salah satu baris history untuk melihat IP, device, sumber data, dan user agent.</div>
                                <div class="history-detail-empty">
                                    <i class="bi bi-cursor-fill d-block mb-2"></i>
                                    Belum ada baris dipilih.
                                </div>
                            </aside>
                        </div>

                        <div class="history-footbar">
                            <div id="historyInfo">Menampilkan 0 data</div>
                            <div class="d-flex gap-2 align-items-center flex-wrap">
                                <select id="historyPerPage" class="form-select" style="width:150px">
                                    <option value="10">10 per halaman</option>
                                    <option value="25">25 per halaman</option>
                                    <option value="50">50 per halaman</option>
                                    <option value="100">100 per halaman</option>
                                </select>
                                <div class="history-pagination" id="historyPagination"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-white">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // ========= ENDPOINTS =========
        const BASE_URL = `{{ url('') }}`;

        // VIEW PAGES (GET)
        const URL_STOCK_FORM = `{{ route('master.dscFormulir.index') }}`; // /master/dsc/formulir
        const URL_OMSET_FORM = `{{ route('master.dscFormulirOmset.index') }}`; // /master/dsc/formulir/omset

        // API (AJAX)
        const URL_LOAD = `{{ route('load') }}`; // /load
        const URL_SAVE = `{{ route('saveSo') }}`; // /save-so  (FINAL -> tbl_stock)
        const URL_SAVE_DRAFT = `{{ route('dsc.save-draft') }}`; // /save-draft (draft)
        const URL_HISTORY = `{{ route('dsc.history') }}`; // JSON history DSC

        // ========= BUTTON GROUPS (UNIQUE IDs) =========
        const BTN = {
            load: '#btnLoadNav, #btnLoadCta',
            draft: '#btnSaveDraftDesk, #btnSaveDraftFoot, #btnSaveDraftM',
            final: '#btnSaveFinalDesk, #btnSaveFinalFoot, #btnSaveFinalMob',
            next0: '#btnNextZeroDesk, #btnNextZeroFoot, #btnNextZeroMob',
            history: '#btnHistoryNav, #btnHistoryFoot, #btnHistoryMob'
        };

        // ========= STATE =========
        const st = {}; // per bahan_id
        let items = []; // list dari BE
        let loaded = false;
        let kasirClosed = false;
        let actualTepungMeta = 0;
        let gateMeta = {}; // meta untuk shift gate
        let mobileIndex = 0; // mobile: tampilkan 1 bahan aktif agar tidak scroll panjang
        let lastEditedBahanId = null; // bahan terakhir yang user ubah, supaya History tidak salah bahan

        // ========= AUTO SAVE SILENT =========
        // Mode baru: setiap user isi/edit form, sistem otomatis simpan ke DRAFT.
        // Tidak mengubah logic Save Draft manual dan FINAL.
        let autoSaveTimer = null;
        let autoSaveRunning = false;
        let autoSaveQueued = false;

        const AUTO_SAVE_ENABLED = true;
        const AUTO_SAVE_DELAY = 8000; // 8 detik: lebih aman untuk banyak outlet, tetap otomatis simpan draft

        // touched tracker (yang user pernah ubah)
        const touched = {}; // { bahan_id: true }
        let uangPlusDirty = false; // true kalau input Uang Plus Shift Ini diubah user
        let lastAutoSaveHash = null;
        let lastAutoSaveAt = null;

        // ========= UTILS =========
        function apiHeaders() {
            return {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            };
        }

        async function readJsonOrText(res) {
            const ct = (res.headers.get('content-type') || '').toLowerCase();
            if (ct.includes('application/json')) {
                try {
                    return {
                        json: await res.json(),
                        raw: ''
                    };
                } catch (e) {
                    const raw = await res.text().catch(() => '');
                    return {
                        json: null,
                        raw
                    };
                }
            } else {
                const raw = await res.text().catch(() => '');
                return {
                    json: null,
                    raw
                };
            }
        }

        function pickErrorMessage(res, json, raw) {
            if (json) return json.error || json.message || `HTTP ${res.status}`;
            if (raw) return `Non-JSON response (HTTP ${res.status}): ${raw.slice(0, 400)}`;
            return `HTTP ${res.status}`;
        }

        function esc(s) {
            return (s ?? '').toString()
                .replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;').replaceAll("'", "&#039;");
        }

        function toNum(v) {
            const s = (v ?? '').toString().trim();
            if (s === '') return 0;
            const n = parseFloat(s);
            return isNaN(n) ? 0 : n;
        }

        function fmt(n) {
            n = Number(n || 0);
            return n.toFixed(2);
        }

        function setStatus(mode, text) {
            let dotClass = '';
            if (mode === 'ok') dotClass = 'ok';
            if (mode === 'bad') dotClass = 'bad';
            if (mode === 'loading') dotClass = 'loading';
            $('#statusBadge').html(`<span class="dot ${dotClass}"></span> ${esc(text)}`);
        }

        function isShift1() {
            return String($('#shift').val()) === '1';
        }

        function isShift2() {
            return String($('#shift').val()) === '2';
        }

        function validateHeader() {
            const outlet = $('#outlet_id').val();
            const tgl = $('#tanggal').val();
            const shift = $('#shift').val();
            const pic = ($('#nama_petugas').val() || '').trim();
            return !!(outlet && tgl && shift && pic);
        }

        // ========= SHIFT BADGE + GATE =========
        function setShiftBadge(type, text) {
            const $b = $('#shiftAlert');
            if (!text) {
                $b.hide();
                return;
            }

            let icon = 'bi-info-circle';
            if (type === 'warn') icon = 'bi-exclamation-triangle-fill';
            if (type === 'ok') icon = 'bi-check-circle-fill';

            $b.html(`<i class="bi ${icon}"></i> ${esc(text)}`).show();
        }

        function updateShiftGate(meta = {}) {
            // support 2 versi meta:
            const hasDraftS1 = !!meta.has_draft_s1;
            const hasFinalS1 = !!meta.has_final_s1;
        
            // fallback lama: has_shift_1 = 1 artinya ada (draft/final)
            const hasS1 = (meta.has_shift_1 === 1 || meta.has_shift_1 === '1' || meta.has_shift_1 === true);
        
            const okS1 = hasDraftS1 || hasFinalS1 || hasS1;
        
            if (isShift1()) {
                setShiftBadge('warn', 'Shift 1: hanya boleh Save Draft (Tombol Simpan Disabled)');
                return;
            }
        
            if (!okS1) {
                setShiftBadge('bad', 'Shift 1 belum ada — FINAL Shift 2 dikunci');
                return;
            }
        
            // kalau cuma tahu has_shift_1, kamu bisa tampilkan generic “siap”
            if (hasDraftS1 && !hasFinalS1) {
                setShiftBadge('warn', 'Shift 1 masih draft — akan difinalkan bersama Shift 2');
                return;
            }
        
            setShiftBadge('ok', 'Shift 1 sudah siap');
        }

        function refreshButtons() {
            const ok = validateHeader();
        
            $(BTN.load).prop('disabled', !ok || kasirClosed);
            $(BTN.draft).prop('disabled', !ok || kasirClosed || !loaded);
        
            let canFinal = ok && loaded && !kasirClosed && isShift2();
        
            if (isShift2()) {
                const hasDraftS1 = !!gateMeta.has_draft_s1;
                const hasFinalS1 = !!gateMeta.has_final_s1;
                const hasS1 = !!gateMeta.has_shift_1;

                if (!hasDraftS1 && !hasFinalS1 && !hasS1) {
                    canFinal = false;
                }
            }
        
            $(BTN.final).prop('disabled', !canFinal);
        }

        // ========= LOGIC (match BE) =========
        function ensure(id, seed = null) {
            if (!st[id]) {
                st[id] = {
                    id: Number(id),
                    nama: seed?.nama_bahan || '',
                    sat: seed?.satuan || '',
                    open: Number(seed?.open || 0),

                    pin: Number(seed?.purchase_in || 0),
                    mi: Number(seed?.mutasi_in || 0),
                    mo: Number(seed?.mutasi_out || 0),

                    adj: Number(seed?.adjustment_qty || 0),

                    ending: (seed?.ending_stock === null || typeof seed?.ending_stock === 'undefined') ? 0 : Number(seed
                        ?.ending_stock || 0),

                    wProd: Number(seed?.waste_product || 0),
                    wBahan: Number(seed?.waste_bahan || 0),

                    uang: Number(seed?.uang_plus || 0),
                    ket: (seed?.keterangan || '')
                };
            } else if (seed) {
                st[id].nama = seed?.nama_bahan ?? st[id].nama;
                st[id].sat = seed?.satuan ?? st[id].sat;
                st[id].open = Number(seed?.open ?? st[id].open);
            }
            return st[id];
        }

        function calc(x) {
            const total = (x.open || 0) + (x.pin || 0) + (x.mi || 0) - (x.mo || 0) + (x.adj || 0);
            const actualUsed = total - (x.ending || 0);
            const wasteTepung = (x.wProd || 0) + (x.wBahan || 0);
            return {
                total,
                actualUsed,
                wasteTepung
            };
        }

        function isTepungName(nama) {
            return (nama || '').trim().toLowerCase() === 'tepung breader';
        }

        function normalizeUangPlusShiftState() {
            let totalUangPlus = 0;

            items.forEach(it => {
                const x = ensure(it.id, it);
                totalUangPlus += Number(x.uang || 0);
                x.uang = 0;
            });

            if (items.length) {
                ensure(items[0].id).uang = totalUangPlus;
            }

            $('#uang_plus_shift').val(totalUangPlus);
            uangPlusDirty = false;
        }

        function getUangPlusShift() {
            return toNum($('#uang_plus_shift').val());
        }

        // ========= RENDER =========
        function rowHtml(i, it) {
            const x = ensure(it.id, it);
            const c = calc(x);

            const isTepung = isTepungName(x.nama);
            const actualTepung = isTepung ? (c.actualUsed - c.wasteTepung) : 0;
            const negClass = c.actualUsed < 0 ? 'neg' : '';

            return `
        <tr data-id="${it.id}">
          <td class="col-no text-center text-muted">${i + 1}</td>
          <td class="col-nama"><div style="font-weight:900;">${esc(x.nama)}</div></td>
          <td class="text-center text-muted">${esc(x.sat || '-')}</td>

          <td class="mono num-read text-end">${fmt(x.open)}</td>

          <td><input class="form-control input-mini pin" type="number" step="0.01" value="${x.pin}"></td>
          <td><input class="form-control input-mini mi" type="number" step="0.01" value="${x.mi}"></td>
          <td><input class="form-control input-mini mo" type="number" step="0.01" value="${x.mo}"></td>

          <td class="mono num-read text-end total">${fmt(c.total)}</td>

          <td><input class="form-control input-mini ending" type="number" step="0.01" value="${x.ending}"></td>

          <td class="mono text-end actual ${negClass}">${fmt(c.actualUsed)}</td>

          <td><input class="form-control input-mini wprod" type="number" step="0.01" value="${x.wProd}"></td>
          <td><input class="form-control input-mini wbahan" type="number" step="0.01" value="${x.wBahan}"></td>

          <td class="mono num-read text-end wt">${fmt(c.wasteTepung)}</td>

          <td class="mono num-read text-end at">${isTepung ? fmt(actualTepung) : '0.00'}</td>

          <td><input class="form-control note-mini ket" type="text" value="${esc(x.ket)}" placeholder="opsional..."></td>
        </tr>
      `;
        }

        function cardHtml(i, it) {
            const x = ensure(it.id, it);
            const c = calc(x);

            const isTepung = isTepungName(x.nama);
            const actualTepung = isTepung ? (c.actualUsed - c.wasteTepung) : 0;
            const negClass = c.actualUsed < 0 ? 'neg' : '';

            return `
        <div class="bcard" data-id="${it.id}">
          <div class="d-flex justify-content-between gap-2 flex-wrap">
            <div>
              <p class="name mb-1">${i + 1}. ${esc(x.nama)}</p>
              <div class="meta">Sat: ${esc(x.sat || '-')} • ID: ${it.id}</div>
            </div>
            ${isTepung ? `<span class="badge-wh"><i class="bi bi-flower1"></i> Tepung</span>` : ``}
          </div>

          <div class="summary">
            <div class="kv"><span>OPEN</span><b>${fmt(x.open)}</b></div>
            <div class="kv"><span>TOTAL</span><b>${fmt(c.total)}</b></div>
            <div class="kv ${negClass}"><span>ACTUAL USED</span><b>${fmt(c.actualUsed)}</b></div>
            <div class="kv"><span>WASTE TEPUNG</span><b>${fmt(c.wasteTepung)}</b></div>
            <div class="kv"><span>ACTUAL TEPUNG</span><b>${fmt(actualTepung)}</b></div>
          </div>

          <div class="grid3">
            <div>
              <label class="form-label small mb-1">Purchase IN</label>
              <input class="form-control pin" type="number" step="0.01" value="${x.pin}">
            </div>
            <div>
              <label class="form-label small mb-1">Mutasi IN</label>
              <input class="form-control mi" type="number" step="0.01" value="${x.mi}">
            </div>
            <div>
              <label class="form-label small mb-1">Mutasi OUT</label>
              <input class="form-control mo" type="number" step="0.01" value="${x.mo}">
            </div>
          </div>

          <div class="mt-2">
            <label class="form-label small mb-1">Ending <span class="text-danger">*</span></label>
            <input class="form-control ending" type="number" step="0.01" value="${x.ending}">
          </div>

          <div class="grid2">
            <div>
              <label class="form-label small mb-1">Waste Product</label>
              <input class="form-control wprod" type="number" step="0.01" value="${x.wProd}">
            </div>
            <div>
              <label class="form-label small mb-1">Waste Bahan</label>
              <input class="form-control wbahan" type="number" step="0.01" value="${x.wBahan}">
            </div>
          </div>

          <div class="mt-2">
            <label class="form-label small mb-1">Keterangan</label>
            <input class="form-control ket" type="text" value="${esc(x.ket)}" placeholder="opsional...">
          </div>
        </div>
      `;
        }

        function renderAll() {
            const $tb = $('#tblDSC tbody').empty();
            if (!items.length) {
                $tb.html(`<tr><td colspan="15" class="text-center text-muted p-4">Tidak ada bahan.</td></tr>`);
            } else {
                let html = '';
                items.forEach((it, i) => html += rowHtml(i, it));
                $tb.html(html);
            }

            const $cards = $('#cards').empty();
            if (!items.length) {
                $cards.html(`<div class="text-center text-muted p-4">Tidak ada bahan.</div>`);
            } else {
                let chtml = '';
                items.forEach((it, i) => chtml += cardHtml(i, it));
                $cards.html(chtml);
            }

            $('#countBadge').html(`<i class="bi bi-box-seam"></i> ${items.length}`);
            syncActualTepungLabel();
            rebuildMobileJump();
            setMobileIndex(0);
        }

        function syncActualTepungLabel() {
            let v = Number(actualTepungMeta || 0);
            for (const id in st) {
                const x = st[id];
                if (isTepungName(x.nama)) {
                    const c = calc(x);
                    v = (c.actualUsed - c.wasteTepung);
                    break;
                }
            }
            $('#actualTepungLabel').text(fmt(v));
        }

        function markTouched(id) {
            const bahanId = Number(id);
            touched[bahanId] = true;
            if (bahanId) {
                lastEditedBahanId = bahanId;
            }
        }

        function isTouched(id) {
            return !!touched[Number(id)];
        }

        function applySearch() {
            const q = ($('#searchAny').val() || '').toLowerCase().trim();

            $('#tblDSC tbody tr').each(function() {
                const id = Number($(this).data('id'));
                const x = st[id];
                const hit = !q || (x?.nama || '').toLowerCase().includes(q) || (x?.sat || '').toLowerCase()
                    .includes(q);
                $(this).toggle(hit);
            });

            $('#cards .bcard').each(function() {
                const id = Number($(this).data('id'));
                const x = st[id];
                const hit = !q || (x?.nama || '').toLowerCase().includes(q) || (x?.sat || '').toLowerCase()
                    .includes(q);
                $(this).toggle(hit);
            });

            setMobileIndex(0);
        }

        function visibleMobileCards() {
            return $('#cards .bcard').filter(function() {
                return $(this).css('display') !== 'none' || $(this).hasClass('active-card');
            });
        }

        function matchedMobileCards() {
            const q = ($('#searchAny').val() || '').toLowerCase().trim();
            return $('#cards .bcard').filter(function() {
                const id = Number($(this).data('id'));
                const x = st[id];
                return !q || (x?.nama || '').toLowerCase().includes(q) || (x?.sat || '').toLowerCase().includes(q);
            });
        }

        function rebuildMobileJump() {
            const $jump = $('#mobJump');
            if (!$jump.length) return;

            if (!items.length) {
                $jump.html('<option value="">Load dulu</option>').prop('disabled', true);
                return;
            }

            let html = '';
            items.forEach((it, i) => {
                const x = ensure(it.id, it);
                const label = `${i + 1}. ${x.nama || '-'}${x.sat ? ' (' + x.sat + ')' : ''}`;
                html += `<option value="${it.id}">${esc(label)}</option>`;
            });
            $jump.html(html).prop('disabled', false);
        }

        function jumpMobileToBahanId(bahanId) {
            const id = Number(bahanId);
            if (!id) return;

            const qNow = ($('#searchAny').val() || '').trim();
            const $all = $('#cards .bcard');
            let $matched = matchedMobileCards();

            let targetIndex = $matched.toArray().findIndex(el => Number($(el).data('id')) === id);

            // Kalau item tidak ada di hasil search aktif, kosongkan search supaya bisa langsung lompat.
            if (targetIndex < 0 && qNow) {
                $('#searchAny').val('');
                $matched = $all;
                targetIndex = $matched.toArray().findIndex(el => Number($(el).data('id')) === id);
            }

            if (targetIndex >= 0) {
                setMobileIndex(targetIndex);
                $('#cards .bcard.active-card input.ending').focus();
            }
        }

        function setMobileIndex(idx) {
            const $all = $('#cards .bcard');
            if (!$all.length) {
                mobileIndex = 0;
                refreshMobilePager();
                return;
            }

            let $matched = matchedMobileCards();

            if (!$matched.length) {
                $all.removeClass('active-card').hide();
                mobileIndex = 0;
                refreshMobilePager();
                return;
            }

            mobileIndex = Math.max(0, Math.min(idx, $matched.length - 1));
            $all.removeClass('active-card').hide();
            $matched.eq(mobileIndex).addClass('active-card').show();
            refreshMobilePager($matched);
        }

        function refreshMobilePager($matched = null) {
            const $all = $('#cards .bcard');
            if (!$matched) {
                const q = ($('#searchAny').val() || '').toLowerCase().trim();
                $matched = $all.filter(function() {
                    const id = Number($(this).data('id'));
                    const x = st[id];
                    return !q || (x?.nama || '').toLowerCase().includes(q) || (x?.sat || '').toLowerCase().includes(q);
                });
            }

            const total = $matched.length;
            const $active = $matched.eq(mobileIndex);
            const id = Number($active.data('id'));
            const name = id && st[id] ? st[id].nama : (total ? '-' : 'Tidak ada hasil');

            $('#mobProgress').text(total ? `${mobileIndex + 1} / ${total}` : '0 / 0');
            $('#mobItemName').text(name || '-');
            $('#btnMobPrev').prop('disabled', !total || mobileIndex <= 0);
            $('#btnMobNext').prop('disabled', !total || mobileIndex >= total - 1);
            $('#mobJump').prop('disabled', !items.length).val(id || '');
        }

        function getEndingInputValue(bahanId) {
            const $tr = $('#tblDSC tbody tr[data-id="' + bahanId + '"]');
            if ($tr.length) return $tr.find('input.ending').val();
            const $c = $('#cards .bcard[data-id="' + bahanId + '"]');
            if ($c.length) return $c.find('input.ending').val();
            return null;
        }

        function validateEndingEmptyForTouchedOnly() {
            let emptyCount = 0;
            items.forEach(it => {
                if (!touched[it.id]) return;
                const v = getEndingInputValue(it.id);
                if (v === '' || v === null || typeof v === 'undefined') emptyCount++;
            });
            $('#warnEmpty').toggle(emptyCount > 0);
            $('#warnEmptyM').toggle(emptyCount > 0);
            return emptyCount === 0;
        }

        // ========= INPUT BIND =========
        function bindInputs() {
            // Desktop table
            $(document).off('input.dsc change.dsc blur.dsc').on('input.dsc change.dsc blur.dsc', '#tblDSC tbody input', function() {
                const $tr = $(this).closest('tr');
                const id = Number($tr.data('id'));
                const x = ensure(id);

                markTouched(id);

                x.pin = toNum($tr.find('input.pin').val());
                x.mi = toNum($tr.find('input.mi').val());
                x.mo = toNum($tr.find('input.mo').val());
                x.ending = toNum($tr.find('input.ending').val());
                x.wProd = toNum($tr.find('input.wprod').val());
                x.wBahan = toNum($tr.find('input.wbahan').val());
                x.ket = ($tr.find('input.ket').val() || '');

                const c = calc(x);
                $tr.find('td.total').text(fmt(c.total));
                $tr.find('td.actual').text(fmt(c.actualUsed)).toggleClass('neg', c.actualUsed < 0);
                $tr.find('td.wt').text(fmt(c.wasteTepung));

                const isTep = isTepungName(x.nama);
                $tr.find('td.at').text(isTep ? fmt(c.actualUsed - c.wasteTepung) : '0.00');

                // sync to cards
                const $card = $('#cards .bcard[data-id="' + id + '"]');
                if ($card.length) {
                    $card.find('input.pin').val(x.pin);
                    $card.find('input.mi').val(x.mi);
                    $card.find('input.mo').val(x.mo);
                    $card.find('input.ending').val(x.ending);
                    $card.find('input.wprod').val(x.wProd);
                    $card.find('input.wbahan').val(x.wBahan);
                    $card.find('input.ket').val(x.ket);
                }

                syncActualTepungLabel();
                validateEndingEmptyForTouchedOnly();
                scheduleAutoSave();
            });

            // Mobile cards
            $(document).off('input.dscm change.dscm blur.dscm').on('input.dscm change.dscm blur.dscm', '#cards input', function() {
                const $card = $(this).closest('.bcard');
                const id = Number($card.data('id'));
                const x = ensure(id);

                markTouched(id);

                x.pin = toNum($card.find('input.pin').val());
                x.mi = toNum($card.find('input.mi').val());
                x.mo = toNum($card.find('input.mo').val());
                x.ending = toNum($card.find('input.ending').val());
                x.wProd = toNum($card.find('input.wprod').val());
                x.wBahan = toNum($card.find('input.wbahan').val());
                x.ket = ($card.find('input.ket').val() || '');

                const $tr = $('#tblDSC tbody tr[data-id="' + id + '"]');
                if ($tr.length) {
                    $tr.find('input.pin').val(x.pin);
                    $tr.find('input.mi').val(x.mi);
                    $tr.find('input.mo').val(x.mo);
                    $tr.find('input.ending').val(x.ending);
                    $tr.find('input.wprod').val(x.wProd);
                    $tr.find('input.wbahan').val(x.wBahan);
                    $tr.find('input.ket').val(x.ket);

                    const c = calc(x);
                    $tr.find('td.total').text(fmt(c.total));
                    $tr.find('td.actual').text(fmt(c.actualUsed)).toggleClass('neg', c.actualUsed < 0);
                    $tr.find('td.wt').text(fmt(c.wasteTepung));
                    const isTep = isTepungName(x.nama);
                    $tr.find('td.at').text(isTep ? fmt(c.actualUsed - c.wasteTepung) : '0.00');
                }

                syncActualTepungLabel();
                validateEndingEmptyForTouchedOnly();
                refreshMobilePager();
                scheduleAutoSave();
            });
        }

        // ========= LOCK UI =========
        function lockUI(isLock, meta = null) {
            kasirClosed = !!isLock;

            const $targets = $(
                '#outlet_id, #tanggal, #shift, #nama_petugas, #uang_plus_shift,' +
                '#searchAny,' +
                '#tblDSC input, #cards input,' +
                '#btnReset,' +
                BTN.load + ',' + BTN.draft + ',' + BTN.final + ',' + BTN.next0
            );
            $targets.prop('disabled', kasirClosed);

            if (kasirClosed) {
                setStatus('bad', 'FINAL (LOCK)');
                $('#infoText').text(`Kasir sudah ditutup${meta?.closed_at ? ' • ' + meta.closed_at : ''}`);
            }

            refreshButtons();
        }

        // ========= API: LOAD =========
        async function loadData() {
            if (!validateHeader()) {
                await swWarn('Lengkapi data', 'Outlet / Tanggal / Shift / Petugas wajib diisi sebelum Load.');
                return;
            }

            $('#infoText').text('Loading data...');
            setStatus('loading', 'Loading...');

            try {
                const qs = new URLSearchParams({
                    outlet_id: $('#outlet_id').val(),
                    tanggal: $('#tanggal').val(),
                    shift: $('#shift').val(),
                    pic: ($('#nama_petugas').val() || '').trim(),
                });

                const res = await fetch(URL_LOAD + '?' + qs.toString(), {
                    headers: apiHeaders()
                });
                const {
                    json,
                    raw
                } = await readJsonOrText(res);
                if (!res.ok || !json || !json.ok) throw new Error(pickErrorMessage(res, json, raw));

                // reset state
                items = [];
                loaded = false;
                actualTepungMeta = 0;
                gateMeta = {};
                uangPlusDirty = false;
                lastAutoSaveHash = null;
                updateLastSavedBadge('pending');
                $('#uang_plus_shift').val(0);
                for (const k in st) delete st[k];
                for (const k in touched) delete touched[k];
                lastEditedBahanId = null;

                items = json.data?.items || [];
                actualTepungMeta = Number(json.data?.meta?.actual_tepung || 0);
                gateMeta = json.data?.meta || {};

                items.forEach(it => ensure(it.id, it));
                normalizeUangPlusShiftState();
                loaded = true;

                renderAll();
                bindInputs();

                $('#infoText').text(`Loaded: ${items.length} bahan`);
                setStatus('ok', 'Siap input');

                // lock kasir
                const kasir = json.data?.lock || {
                    is_closed: false
                };
                lockUI(!!kasir.is_closed, kasir);

                updateShiftGate(gateMeta);
                validateEndingEmptyForTouchedOnly();
            } catch (e) {
                console.error(e);
                setStatus('bad', 'Gagal');
                $('#infoText').text('Gagal load.');
                await swError('Gagal Load', e.message);
            } finally {
                refreshButtons();
            }
        }

        // ========= PAYLOAD BUILDER =========
        function buildPayloadDraft() {
            const payload = {
                outlet_id: $('#outlet_id').val(),
                tanggal: $('#tanggal').val(),
                shift: $('#shift').val(),
                nama_petugas: ($('#nama_petugas').val() || '').trim(),
                rows: []
            };

            let selectedItems = items.filter(it => isTouched(it.id));

            // Kalau Uang Plus Shift diubah, kirim semua baris agar uang_plus lama per bahan
            // ikut direset menjadi 0 dan tidak dobel di report.
            if (uangPlusDirty) {
                selectedItems = [...items];
            }

            const uangPlusShift = getUangPlusShift();

            selectedItems.forEach((it, i) => {
                const x = ensure(it.id);
                const endingRaw = getEndingInputValue(it.id);

                payload.rows.push({
                    bahan_id: it.id,
                    ending_stock: (endingRaw === '' || endingRaw === null || typeof endingRaw === 'undefined') ? null : Number(x.ending || 0),
                    purchase_in: Number(x.pin || 0),
                    mutasi_in: Number(x.mi || 0),
                    mutasi_out: Number(x.mo || 0),
                    adjustment_qty: Number(x.adj || 0),
                    waste_product: Number(x.wProd || 0),
                    waste_bahan: Number(x.wBahan || 0),
                    uang_plus: i === 0 ? uangPlusShift : 0,
                    keterangan: x.ket || ''
                });
            });

            return payload;
        }

        function buildPayloadFinal() {
            const outletId = $('#outlet_id').val();
            const tanggal = $('#tanggal').val();
            const picRaw = ($('#nama_petugas').val() || '').trim();

            return {
                outlet_id: outletId,
                tanggal,
                shift: 2,
                nama_petugas: picRaw,

                // OPTIONAL: kalau backend kamu pakai finalize both
                finalize_both: 1,

                // FINAL: kirim semua rows biar tbl_stock lengkap
                rows: items.map((it, i) => {
                    const x = ensure(it.id);
                    const uangPlusShift = getUangPlusShift();
                    return {
                        bahan_id: it.id,
                        ending_stock: Number(x.ending || 0),
                        purchase_in: Number(x.pin || 0),
                        mutasi_in: Number(x.mi || 0),
                        mutasi_out: Number(x.mo || 0),
                        adjustment_qty: Number(x.adj || 0),
                        waste_product: Number(x.wProd || 0),
                        waste_bahan: Number(x.wBahan || 0),
                        uang_plus: i === 0 ? uangPlusShift : 0,
                        keterangan: x.ket || ''
                    };
                })
            };
        }

        // ========= LAST SAVED INDICATOR =========
        function updateLastSavedBadge(mode, message = '') {
            const $b = $('#lastSavedBadge');
            if (!$b.length) return;

            if (mode === 'pending') {
                $b.html('<i class="bi bi-hourglass-split"></i> Menunggu autosave...');
                return;
            }

            if (mode === 'saving') {
                $b.html('<i class="bi bi-cloud-arrow-up"></i> Menyimpan...');
                return;
            }

            if (mode === 'ok') {
                const now = new Date();
                const jam = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                lastAutoSaveAt = jam;
                $b.html(`<i class="bi bi-cloud-check"></i> Tersimpan ${jam}`);
                return;
            }

            if (mode === 'error') {
                $b.html(`<i class="bi bi-cloud-slash"></i> Gagal simpan${message ? ': ' + esc(message).slice(0, 60) : ''}`);
                return;
            }
        }

        // ========= AUTO SAVE DRAFT SILENT =========
        function scheduleAutoSave() {
            if (!AUTO_SAVE_ENABLED) return;
            if (kasirClosed || !loaded) return;
            if (!validateHeader()) return;

            clearTimeout(autoSaveTimer);
            updateLastSavedBadge('pending');

            autoSaveTimer = setTimeout(function () {
                autoSaveDraftSilent();
            }, AUTO_SAVE_DELAY);
        }

        async function autoSaveDraftSilent() {
            if (autoSaveRunning) {
                autoSaveQueued = true;
                return;
            }

            const payload = buildPayloadDraft();

            // Auto-save hanya menyimpan baris yang sudah pernah diubah user.
            // Jadi tidak akan mengganggu data lain.
            if (!payload.rows.length) return;

            const payloadHash = JSON.stringify(payload);
            if (payloadHash === lastAutoSaveHash) {
                updateLastSavedBadge('ok');
                return;
            }

            autoSaveRunning = true;

            try {
                setStatus('loading', 'Auto saving...');
                updateLastSavedBadge('saving');

                const res = await fetch(URL_SAVE_DRAFT, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        ...apiHeaders()
                    },
                    body: JSON.stringify(payload)
                });

                const { json, raw } = await readJsonOrText(res);

                if (!res.ok || !json || !json.ok) {
                    throw new Error(pickErrorMessage(res, json, raw));
                }

                lastAutoSaveHash = payloadHash;
                setStatus('ok', 'Auto saved');
                updateLastSavedBadge('ok');
                $('#infoText').text('Perubahan otomatis tersimpan sebagai draft.');
            } catch (e) {
                console.error(e);
                setStatus('bad', 'Auto save gagal');
                updateLastSavedBadge('error', e.message);
                $('#infoText').text('Auto save gagal. Tombol Save Draft manual masih bisa digunakan.');
            } finally {
                autoSaveRunning = false;

                // Kalau user mengetik lagi saat request masih berjalan,
                // jalankan auto-save berikutnya setelah request pertama selesai.
                if (autoSaveQueued) {
                    autoSaveQueued = false;
                    scheduleAutoSave();
                }

                refreshButtons();
            }
        }

        // ========= DRAFT =========
        async function saveDraft() {
            if (kasirClosed) return swWarn('Sudah FINAL', 'Kasir sudah ditutup. Tidak bisa simpan/edit.');
            if (!loaded) return swWarn('Belum load', 'Klik Load dulu.');
            if (!validateHeader()) return swWarn('Data belum lengkap', 'Lengkapi Outlet/Tanggal/Shift/Nama Petugas.');

            const payload = buildPayloadDraft();
            if (!payload.rows.length) {
                return swWarn('Tidak ada perubahan', 'Belum ada baris yang diubah/diisi. Tidak ada yang disimpan.');
            }

            try {
                swLoading('Menyimpan draft...');
                const res = await fetch(URL_SAVE_DRAFT, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        ...apiHeaders()
                    },
                    body: JSON.stringify(payload)
                });

                const { json, raw } = await readJsonOrText(res);
                if (!res.ok || !json || !json.ok) throw new Error(pickErrorMessage(res, json, raw));

                Swal.close();
                lastAutoSaveHash = JSON.stringify(buildPayloadDraft());
                updateLastSavedBadge('ok');

                // kalau shift 1, tawarkan lanjut ke shift 2
                if (String($('#shift').val()) === '1') {
                    const ans = await Swal.fire({
                        icon: 'question',
                        title: 'Draft Shift 1 tersimpan',
                        text: 'Apakah ingin lanjut ke Shift 2 sekarang?',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, lanjut Shift 2',
                        cancelButtonText: 'Tidak',
                        reverseButtons: true
                    });

                    if (ans.isConfirmed) {
                        $('#shift').val('2').trigger('change');

                        // kalau pakai select2, pastikan tampilannya ikut berubah
                        if ($('#shift').hasClass('select2-hidden-accessible')) {
                            $('#shift').trigger('change.select2');
                        }

                        await loadData();
                        await swSuccess('Shift 2 siap', 'Data Shift 2 berhasil dimuat.');
                    } else {
                        await swSuccess('Draft tersimpan', json.message || 'Draft Shift 1 tersimpan.', {
                            timer: 1200,
                            timerProgressBar: true
                        });
                    }
                } else {
                    await swSuccess('Draft tersimpan', json.message || 'Draft tersimpan.', {
                        timer: 1200,
                        timerProgressBar: true
                    });
                }

                return json;
            } catch (e) {
                Swal.close();
                console.error(e);
                await swError('Draft gagal', e.message);
                throw e;
            } finally {
                refreshButtons();
            }
        }

        // ========= FINAL =========
        async function saveFinal() {
            if (kasirClosed) return swWarn('Sudah FINAL', 'Kasir sudah ditutup. Tidak bisa simpan/edit.');
            if (!loaded) return swWarn('Belum load', 'Klik Load dulu.');
            if (!validateHeader()) return swWarn('Data belum lengkap', 'Lengkapi Outlet/Tanggal/Shift/Nama Petugas.');

            // SHIFT 1: FINAL selalu ditolak
            if (isShift1()) {
                return swWarn('Shift 1', 'Shift 1 hanya boleh Save Draft. Pindah ke Shift 2 untuk Simpan (FINAL).');
            }

            // SHIFT 2 gate: shift1 harus ada draft/final (kalau meta tersedia)
            if (isShift2()) {
                const hasDraftS1 = !!gateMeta.has_draft_s1;
                const hasFinalS1 = !!gateMeta.has_final_s1;
                const hasS1 = !!gateMeta.has_shift_1;

                if (!hasDraftS1 && !hasFinalS1 && !hasS1) {
                    return swWarn('Shift 1 belum ada', 'Final Shift 2 dikunci. Simpan Draft Shift 1 terlebih dahulu.');
                }
            }

            const outletId = $('#outlet_id').val();
            const tanggal = $('#tanggal').val();
            const picEnc = encodeURIComponent(($('#nama_petugas').val() || '').trim());

            const payload = buildPayloadFinal();

            try {
                swLoading('Menyimpan FINAL SO...');
                const res = await fetch(URL_SAVE, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        ...apiHeaders()
                    },
                    body: JSON.stringify(payload)
                });

                const {
                    json,
                    raw
                } = await readJsonOrText(res);
                if (!res.ok || !json || !json.ok) throw new Error(pickErrorMessage(res, json, raw));

                Swal.close();

                // ✅ MODAL: mau lanjut isi omset/setor?
                const ans = await Swal.fire({
                    icon: 'question',
                    title: 'Lanjut isi Omset/Setor hari ini?',
                    text: 'Apakah Anda ingin mengisi data omset hari ini dan setor uang?',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, lanjut',
                    cancelButtonText: 'Tidak',
                    reverseButtons: true
                });

                if (ans.isConfirmed) {
                    window.location.href =
                        `${URL_OMSET_FORM}?tanggal=${tanggal}&outlet_id=${outletId}&shift=2&pic=${picEnc}`;
                } else {
                    window.location.reload();
                }
            } catch (e) {
                Swal.close();
                console.error(e);
                await swError('FINAL SO gagal', e.message);
            }
        }

        // ========= NEXT ZERO (isi ending 0 untuk baris berikutnya yang belum disentuh) =========
        function nextZero() {
            if (!loaded) return;

            // Mobile: isi 0 pada kartu aktif kalau kosong, auto-save, lalu pindah kartu berikutnya.
            if (window.matchMedia('(max-width: 767.98px)').matches) {
                const $active = $('#cards .bcard.active-card');
                if ($active.length) {
                    const $ending = $active.find('input.ending');
                    if ($ending.length && ($ending.val() === '' || $ending.val() === null)) {
                        $ending.val('0').trigger('input');
                    } else {
                        scheduleAutoSave();
                    }
                    setMobileIndex(mobileIndex + 1);
                    const $nextEnding = $('#cards .bcard.active-card input.ending');
                    if ($nextEnding.length) $nextEnding.focus();
                    return;
                }
            }

            // Desktop: cari baris visible pertama yang ending kosong.
            const $rows = $('#tblDSC tbody tr:visible');
            for (let i = 0; i < $rows.length; i++) {
                const $tr = $($rows[i]);
                const $ending = $tr.find('input.ending');
                if ($ending.length && ($ending.val() === '' || $ending.val() === null)) {
                    $ending.val('0').trigger('input');
                    $ending.focus();
                    return;
                }
            }

            scheduleAutoSave();
        }

        // ========= SWEETALERT HELPERS =========
        function swLoading(title = 'Loading...') {
            return Swal.fire({
                title,
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => Swal.showLoading()
            });
        }

        function swSuccess(title, text, opts = {}) {
            return Swal.fire({
                icon: 'success',
                title,
                text,
                ...opts
            });
        }

        function swError(title, text, opts = {}) {
            return Swal.fire({
                icon: 'error',
                title,
                text,
                ...opts
            });
        }

        function swWarn(title, text, opts = {}) {
            return Swal.fire({
                icon: 'warning',
                title,
                text,
                ...opts
            });
        }



        // ========= HISTORY DSC MODAL - CLEAN, CLICKABLE, SEARCH + SCROLL + PAGINATION =========
        let historyRawRows = [];
        let historyFilteredRows = [];
        let historyPage = 1;
        let historySelectedIndex = null;

        function historySafeText(value) {
            if (value === null || typeof value === 'undefined' || value === '') return '-';
            return String(value);
        }

        function historyFormatTime(value) {
            const raw = historySafeText(value);
            if (raw === '-') return '-';

            // Support beberapa format dari controller:
            // - created_at: 2026-05-29 16:20:46
            // - jam: 29/05/2026 16:20:46
            // - timestamp ISO: 2026-05-29T16:20:46.000000Z
            return raw
                .replace('T', ' ')
                .replace('.000000Z', '')
                .replace('Z', '')
                .slice(0, 19);
        }

        function historyRowTime(row) {
            return historyFormatTime(
                row.jam ||
                row.created_at ||
                row.createdAt ||
                row.created ||
                row.updated_at ||
                row.updatedAt ||
                ''
            );
        }

        function historyStatusKind(row) {
            const src = String(row.source || row.table_name || '').toLowerCase();
            const act = String(row.action || '').toLowerCase();
            if (src.includes('draft') || act.includes('draft')) return 'draft';
            if (src.includes('tbl_stock') || act.includes('final')) return 'final';
            return 'other';
        }

        function historyStatusBadge(row) {
            const kind = historyStatusKind(row);
            if (kind === 'draft') return '<span class="history-status-badge draft">DRAFT</span>';
            if (kind === 'final') return '<span class="history-status-badge final">FINAL</span>';
            return '<span class="history-status-badge other">LOG</span>';
        }

        function historyIsSignificant(row) {
            const oldVal = parseFloat(String(row.old_value ?? '').replace(',', '.'));
            const newVal = parseFloat(String(row.new_value ?? '').replace(',', '.'));
            if (Number.isNaN(oldVal) || Number.isNaN(newVal)) return false;
            const diff = Math.abs(newVal - oldVal);
            const base = Math.max(Math.abs(oldVal), 1);
            return diff >= 50 || (diff / base) >= 0.3;
        }

        function historySignificantBadge(row) {
            return historyIsSignificant(row)
                ? '<span class="history-status-badge other" title="Selisih besar">Perubahan signifikan</span>'
                : '';
        }

        function historyFieldLabel(field) {
            const map = {
                opening_stock: 'Opening Stock',
                purchase_in: 'Purchase In',
                mutasi_in: 'Mutasi In',
                mutasi_out: 'Mutasi Out',
                adjustment_qty: 'Adjustment',
                used_qty: 'Used Qty',
                waste_product: 'Waste Product',
                waste_bahan: 'Waste Bahan',
                waste_tepung: 'Waste Tepung',
                ending_stock: 'Ending Stock / AB',
                actual_tepung: 'Actual Tepung',
                uang_plus: 'Uang Plus',
                keterangan: 'Keterangan',
                nama_petugas: 'Petugas',
                pic: 'Petugas',
                'Purchase In': 'Purchase In',
                'Petugas': 'Petugas',
                'Keterangan': 'Keterangan'
            };
            const key = String(field || '').trim();
            return map[key] || key || '-';
        }

        function getHistoryBahanIdFromRow() {
            // Prioritas: bahan terakhir yang benar-benar diubah user.
            // Ini mencegah modal History tetap nyangkut di filter bahan lama
            // seperti AYAM BESAR padahal yang baru diedit AYAM CUT 14.
            if (lastEditedBahanId) return lastEditedBahanId;

            const focusedRowId = $('#tblDSC tbody tr:has(:focus)').data('id');
            if (focusedRowId) return focusedRowId;

            const activeCardId = $('#cards .bcard.active-card').data('id');
            if (activeCardId) return activeCardId;

            return '';
        }

        function fillHistoryBahanOptions() {
            const $sel = $('#historyBahan');
            if (!$sel.length) return;

            const current = String($sel.val() || '');
            $sel.empty().append('<option value="">Semua bahan</option>');

            const seen = {};
            items.forEach(function(it) {
                const id = String(it.id || it.bahan_id || '');
                if (!id || seen[id]) return;
                seen[id] = true;
                const nama = it.nama_bahan || it.nama || ('Bahan #' + id);
                $sel.append(`<option value="${esc(id)}">${esc(nama)}</option>`);
            });

            if (current && $sel.find(`option[value="${current}"]`).length) {
                $sel.val(current);
            }
        }

        async function openHistoryModal(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }

            const outletId = $('#outlet_id').val();
            const tanggal = $('#tanggal').val();

            if (!outletId || !tanggal) {
                return swWarn('Filter belum lengkap', 'Pilih outlet dan tanggal dulu, lalu klik History lagi.');
            }

            if (!document.getElementById('historyModal')) {
                return swError('Modal history tidak ditemukan', 'Elemen #historyModal belum ada di file blade.');
            }

            fillHistoryBahanOptions();
            $('#historyDateFrom').val($('#historyDateFrom').val() || tanggal);
            $('#historyDateTo').val($('#historyDateTo').val() || tanggal);

            const activeBahan = getHistoryBahanIdFromRow();
            if (activeBahan && $('#historyBahan').find(`option[value="${activeBahan}"]`).length) {
                $('#historyBahan').val(activeBahan);
            }

            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('historyModal'));
            modal.show();

            await loadHistoryData();
        }

        async function loadHistoryData() {
            const outletId = $('#outlet_id').val();
            const tanggal = $('#tanggal').val();
            const shift = $('#historyShift').val() || $('#shift').val() || '';
            const bahanId = $('#historyBahan').val() || '';
            const dateFrom = $('#historyDateFrom').val() || tanggal || '';
            const dateTo = $('#historyDateTo').val() || tanggal || '';

            if (!outletId || !tanggal) return;

            $('#historySummary').text('Memuat history...');
            $('#historyBody').html('<tr><td colspan="6" class="history-empty">Memuat data history...</td></tr>');
            $('#historyPagination').html('');
            $('#historyDetailPanel').html(historyEmptyDetailHtml());

            try {
                const qs = new URLSearchParams();
                qs.set('outlet_id', outletId);
                qs.set('tanggal', tanggal);
                if (shift) qs.set('shift', shift);
                if (bahanId) qs.set('bahan_id', bahanId);
                if (dateFrom) qs.set('date_from', dateFrom);
                if (dateTo) qs.set('date_to', dateTo);

                const res = await fetch(URL_HISTORY + '?' + qs.toString(), {
                    method: 'GET',
                    headers: apiHeaders()
                });

                const { json, raw } = await readJsonOrText(res);
                if (!res.ok || !json || !(json.ok || json.success)) {
                    throw new Error(pickErrorMessage(res, json, raw));
                }

                let rows = [];
                if (Array.isArray(json.data)) rows = json.data;
                else if (Array.isArray(json.data?.rows)) rows = json.data.rows;
                else if (Array.isArray(json.history)) rows = json.history;

                historyRawRows = rows;
                historyPage = 1;
                historySelectedIndex = null;
                applyHistorySearch();
            } catch (err) {
                console.error(err);
                $('#historySummary').text('Gagal memuat history');
                $('#historyBody').html(`<tr><td colspan="6" class="text-danger py-4 px-3">Gagal memuat history: ${esc(err.message)}</td></tr>`);
                $('#historyInfo').text('Gagal memuat data');
            }
        }

        function historyParseDevice(userAgent) {
            const ua = String(userAgent || '').trim();
            if (!ua) return { label: '-', icon: 'bi-question-circle', detail: '-' };

            let os = 'Unknown OS';
            if (/android/i.test(ua)) os = 'Android';
            else if (/iphone|ipad|ipod/i.test(ua)) os = 'iPhone/iPad';
            else if (/windows/i.test(ua)) os = 'Windows';
            else if (/mac os|macintosh/i.test(ua)) os = 'Mac';
            else if (/linux/i.test(ua)) os = 'Linux';

            let browser = 'Browser';
            if (/edg/i.test(ua)) browser = 'Edge';
            else if (/opr|opera/i.test(ua)) browser = 'Opera';
            else if (/firefox/i.test(ua)) browser = 'Firefox';
            else if (/crios/i.test(ua)) browser = 'Chrome iOS';
            else if (/chrome/i.test(ua)) browser = 'Chrome';
            else if (/safari/i.test(ua)) browser = 'Safari';

            let icon = 'bi-laptop';
            if (/android|iphone|ipad|ipod|mobile/i.test(ua)) icon = 'bi-phone';
            if (/tablet|ipad/i.test(ua)) icon = 'bi-tablet';

            return { label: `${os} • ${browser}`, icon, detail: ua };
        }

        function historyEmptyDetailHtml() {
            return `
                <div class="history-detail-title">Detail audit</div>
                <div class="history-detail-sub">Klik salah satu baris history untuk melihat IP, device, sumber data, dan user agent.</div>
                <div class="history-detail-empty">
                    <i class="bi bi-cursor-fill d-block mb-2"></i>
                    Belum ada baris dipilih.
                </div>`;
        }

        function historyDetailHtml(row) {
            const device = historyParseDevice(row.user_agent || row.device || '');
            const petugas = row.pic || row.nama_petugas || '-';
            const user = row.user_name || row.user || '-';
            const bahan = row.nama_bahan || row.bahan || (row.bahan_id ? `Bahan #${row.bahan_id}` : '-');
            const source = row.table_name || row.source || '-';
            const action = row.action || '-';
            const ip = row.ip_address || '-';

            return `
                <div class="history-detail-title">Detail audit</div>
                <div class="history-detail-sub">${esc(historyRowTime(row))}</div>
                <div class="history-kv"><span>Petugas</span><span>${esc(petugas)}</span></div>
                <div class="history-kv"><span>User Login</span><span>${esc(user)}</span></div>
                <div class="history-kv"><span>Bahan</span><span>${esc(bahan)}</span></div>
                <div class="history-kv"><span>Shift</span><span>${row.shift ? 'Shift ' + esc(row.shift) : '-'}</span></div>
                <div class="history-kv"><span>Kolom</span><span>${esc(historyFieldLabel(row.field_name))}</span></div>
                <div class="history-kv"><span>Sebelum</span><span class="history-mono">${esc(historySafeText(row.old_value))}</span></div>
                <div class="history-kv"><span>Sesudah</span><span class="history-mono">${esc(historySafeText(row.new_value))}</span></div>
                <div class="history-kv"><span>Status</span><span>${historyStatusBadge(row)}</span></div>
                <div class="history-kv"><span>Sumber</span><span>${esc(source)}</span></div>
                <div class="history-kv"><span>Action</span><span>${esc(action)}</span></div>
                <div class="history-kv"><span>IP Address</span><span class="history-mono">${esc(ip)}</span></div>
                <div class="history-kv"><span>Device</span><span><span class="history-device-pill"><i class="bi ${esc(device.icon)}"></i>${esc(device.label)}</span></span></div>
                <div class="history-kv"><span>User Agent</span><span class="history-mono">${esc(device.detail)}</span></div>`;
        }

        function applyHistorySearch() {
            const q = ($('#historySearch').val() || '').trim().toLowerCase();

            if (!q) {
                historyFilteredRows = historyRawRows.slice();
            } else {
                historyFilteredRows = historyRawRows.filter(function(row) {
                    const haystack = [
                        row.jam, row.created_at, row.updated_at, row.pic, row.user_name, row.nama_petugas, row.shift,
                        row.bahan_id, row.nama_bahan, row.bahan, row.field_name,
                        historyFieldLabel(row.field_name), row.old_value, row.new_value,
                        row.action, row.table_name, row.source, row.ip_address, row.user_agent, row.device
                    ].map(historySafeText).join(' ').toLowerCase();
                    return haystack.includes(q);
                });
            }

            historyPage = 1;
            historySelectedIndex = null;
            $('#historyDetailPanel').html(historyEmptyDetailHtml());
            renderHistoryPage();
        }

        function renderHistoryPage() {
            const perPage = Math.max(1, Number($('#historyPerPage').val() || 10));
            const total = historyFilteredRows.length;
            const totalPage = Math.max(1, Math.ceil(total / perPage));
            historyPage = Math.min(Math.max(1, historyPage), totalPage);

            const start = (historyPage - 1) * perPage;
            const pageRows = historyFilteredRows.slice(start, start + perPage);

            const fromDate = $('#historyDateFrom').val() || $('#tanggal').val() || '-';
            const toDate = $('#historyDateTo').val() || fromDate;
            const dateText = toDate && toDate !== fromDate ? `${fromDate} s/d ${toDate}` : fromDate;
            $('#historySummary').text(`Total ${total} perubahan • Outlet ${$('#outlet_id').val() || '-'} • ${dateText}`);
            $('#historyInfo').text(total ? `Menampilkan ${start + 1} sampai ${Math.min(start + perPage, total)} dari ${total} data` : 'Menampilkan 0 data');

            if (!pageRows.length) {
                $('#historyBody').html('<tr><td colspan="6" class="history-empty">Belum ada history untuk filter ini.</td></tr>');
                renderHistoryPagination(totalPage);
                return;
            }

            const html = pageRows.map(function(row, idx) {
                const no = start + idx + 1;
                const globalIndex = start + idx;
                const petugas = row.pic || row.nama_petugas || '-';
                const user = row.user_name || row.user || '';
                const bahan = row.nama_bahan || row.bahan || (row.bahan_id ? `Bahan #${row.bahan_id}` : '-');
                const field = historyFieldLabel(row.field_name);
                const shift = row.shift ? `Shift ${row.shift}` : '-';
                const source = row.table_name || row.source || '-';

                const userHtml = `
                    <div class="history-user-main">${esc(petugas || user || '-')}</div>
                    ${user && user !== petugas ? `<div class="history-user-sub" title="${esc(user)}">${esc(user)}</div>` : ''}`;

                const changeHtml = `
                    <div class="history-change-title">
                        <span>${esc(field)}</span>
                        ${historySignificantBadge(row)}
                    </div>
                    <div class="history-change-meta">${esc(bahan)} • ${esc(shift)} • ${esc(source)}</div>
                    <div class="history-diff">
                        <span class="history-old-value" title="${esc(historySafeText(row.old_value))}">${esc(historySafeText(row.old_value))}</span>
                        <span class="history-arrow">→</span>
                        <span class="history-new-value" title="${esc(historySafeText(row.new_value))}">${esc(historySafeText(row.new_value))}</span>
                    </div>`;

                return `
                    <tr class="history-clickable ${historySelectedIndex === globalIndex ? 'history-active' : ''}" data-history-index="${globalIndex}">
                        <td class="text-center text-muted">${no}</td>
                        <td><div class="history-time">${esc(historyRowTime(row))}</div></td>
                        <td>${userHtml}</td>
                        <td>${changeHtml}</td>
                        <td>${historyStatusBadge(row)}</td>
                        <td><button type="button" class="btn btn-sm history-detail-btn" data-history-index="${globalIndex}">Detail</button></td>
                    </tr>`;
            }).join('');

            $('#historyBody').html(html);
            renderHistoryPagination(totalPage);
        }

        function selectHistoryRow(index) {
            index = Number(index);
            if (Number.isNaN(index) || !historyFilteredRows[index]) return;
            historySelectedIndex = index;
            $('#historyDetailPanel').html(historyDetailHtml(historyFilteredRows[index]));
            $('#tblHistoryDSC tbody tr').removeClass('history-active');
            $(`#tblHistoryDSC tbody tr[data-history-index="${index}"]`).addClass('history-active');
        }

        function renderHistoryPagination(totalPage) {
            const $p = $('#historyPagination');
            if (!$p.length) return;
            if (totalPage <= 1) {
                $p.html('');
                return;
            }

            const btn = (page, label, disabled = false, active = false) => {
                return `<button type="button" class="btn btn-sm ${active ? 'btn-primary active' : 'btn-outline-secondary'}" data-history-page="${page}" ${disabled ? 'disabled' : ''}>${label}</button>`;
            };

            let html = '';
            html += btn(1, '&laquo;', historyPage <= 1);
            html += btn(historyPage - 1, '&lsaquo;', historyPage <= 1);

            const from = Math.max(1, historyPage - 2);
            const to = Math.min(totalPage, historyPage + 2);
            for (let p = from; p <= to; p++) html += btn(p, p, false, p === historyPage);

            html += btn(historyPage + 1, '&rsaquo;', historyPage >= totalPage);
            html += btn(totalPage, '&raquo;', historyPage >= totalPage);
            $p.html(html);
        }

        function changeHistoryPage(page) {
            page = Number(page || 1);
            if (!page || page < 1) return;
            historyPage = page;
            historySelectedIndex = null;
            $('#historyDetailPanel').html(historyEmptyDetailHtml());
            renderHistoryPage();
        }

        function bindHistoryButtonClick() {
            $(document).off('click.dschistory', BTN.history).on('click.dschistory', BTN.history, openHistoryModal);
        }


        function dscShowOpeningNotice() {
            if (typeof Swal === 'undefined') return;

            const isOnline = navigator.onLine;
            const networkText = isOnline
                ? 'Status browser: online. Pastikan sinyal tetap stabil sampai muncul status tersimpan.'
                : 'Status browser: offline. Jangan lanjut input dulu sebelum koneksi kembali normal.';

            Swal.fire({
                icon: isOnline ? 'info' : 'warning',
                title: 'Update Terbaru DSC',
                html: `
                    <div style="text-align:left;line-height:1.5;font-size:.92rem">
                        <div style="font-weight:800;margin-bottom:8px;color:#0f172a">Sebelum mulai input, perhatikan aturan terbaru berikut:</div>
                        <ol style="padding-left:18px;margin:0 0 10px 0">
                            <li><b>Pastikan jaringan internet stabil.</b> DSC memakai autosave, tetapi data dianggap aman setelah muncul status <b>Tersimpan</b>.</li>
                            <li><b>Jangan membuka DSC outlet/tanggal/shift yang sama di 2 tab atau 2 device</b>, karena bisa saling menimpa data terakhir.</li>
                            <li><b>Ending Stock adalah hasil hitung fisik/manual.</b> Angka ending akan menjadi dasar opening shift berikutnya atau tanggal berikutnya.</li>
                            <li><b>Role Crew</b> hanya boleh mengisi kolom yang masih kosong/0. Jika nominal sudah lebih dari 0, perubahan hanya bisa dilakukan oleh <b>SPV</b> atau <b>TM Manager</b>.</li>
                            <li>Semua perubahan tersimpan di <b>History</b> lengkap dengan jam, petugas, IP, dan device.</li>
                        </ol>
                        <div style="padding:10px 12px;border:1px solid ${isOnline ? '#bbf7d0' : '#fecaca'};border-radius:12px;background:${isOnline ? '#ecfdf5' : '#fef2f2'};color:${isOnline ? '#065f46' : '#991b1b'};font-weight:800">
                            ${networkText}
                        </div>
                    </div>
                `,
                confirmButtonText: 'Saya Mengerti',
                confirmButtonColor: '#0f172a',
                allowOutsideClick: false,
                allowEscapeKey: false,
                width: 620
            });
        }

        function dscBindNetworkAlert() {
            window.addEventListener('offline', function() {
                if (typeof Swal === 'undefined') return;
                Swal.fire({
                    icon: 'warning',
                    title: 'Koneksi Internet Terputus',
                    text: 'Jangan lanjut input dulu. Tunggu koneksi normal agar autosave tidak gagal.',
                    confirmButtonText: 'Oke',
                    confirmButtonColor: '#0f172a'
                });
            });

            window.addEventListener('online', function() {
                if (typeof Swal === 'undefined') return;
                Swal.fire({
                    icon: 'success',
                    title: 'Koneksi Kembali Normal',
                    text: 'Silakan cek status tersimpan atau klik Save Draft untuk memastikan data aman.',
                    timer: 2200,
                    timerProgressBar: true,
                    showConfirmButton: false
                });
            });
        }

        // ========= DOC READY =========
        $(document).ready(function() {
            dscBindNetworkAlert();
            setTimeout(dscShowOpeningNotice, 450);

            // select2
            $('#outlet_id').select2({
                width: '100%',
                placeholder: '-- Pilih Outlet --'
            });
            $('#shift').select2({
                width: '100%',
                minimumResultsForSearch: Infinity
            });

            // header changes
            $('#outlet_id,#tanggal,#shift,#nama_petugas').on('change input', function() {
                updateShiftGate(gateMeta);
                refreshButtons();
            });

            $('#uang_plus_shift').on('input change', function() {
                uangPlusDirty = true;
                if (items.length) {
                    markTouched(items[0].id);
                }
                refreshButtons();
                scheduleAutoSave();
            });

            // load (nav + cta)
            $(document).on('click', BTN.load, loadData);

            // draft (desk + foot + mobile)
            $(document).on('click', BTN.draft, saveDraft);

            // final (desk + foot + mobile)
            $(document).on('click', BTN.final, saveFinal);

            // next 0
            $(document).on('click', BTN.next0, nextZero);

            // reset
            $('#btnReset').on('click', function() {
                // reset input saja, tidak menyentuh DB
                $('#searchAny').val('');
                applySearch();
                for (const k in touched) delete touched[k];
                validateEndingEmptyForTouchedOnly();
                swSuccess('Reset', 'Reset hanya pada tampilan. Data di server tidak berubah.', {
                    timer: 1200,
                    timerProgressBar: true
                });
            });

            // mobile pager
            $('#btnMobPrev').on('click', function() {
                setMobileIndex(mobileIndex - 1);
                $('#cards .bcard.active-card input.ending').focus();
            });
            $('#btnMobNext').on('click', function() {
                scheduleAutoSave();
                setMobileIndex(mobileIndex + 1);
                $('#cards .bcard.active-card input.ending').focus();
            });

            $('#mobJump').on('change', function() {
                scheduleAutoSave();
                jumpMobileToBahanId($(this).val());
            });

            // search
            $('#searchAny').on('input', applySearch);


            // history modal
            bindHistoryButtonClick();
            $('#btnHistoryRefresh').on('click', loadHistoryData);
            $('#historyBahan,#historyShift,#historyDateFrom,#historyDateTo').on('change', loadHistoryData);
            $('#historySearch').on('input', applyHistorySearch);
            $('#historyPerPage').on('change', function() {
                historyPage = 1;
                renderHistoryPage();
            });
            $('#historyPagination').on('click', '[data-history-page]', function() {
                changeHistoryPage($(this).data('history-page'));
            });
            $('#historyBody').on('click', 'tr.history-clickable, .history-detail-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                selectHistoryRow($(this).data('history-index') || $(this).closest('tr').data('history-index'));
            });
            $('#btnHistoryReset').on('click', function() {
                $('#historySearch').val('');
                $('#historyBahan').val('');
                $('#historyShift').val('');
                $('#historyDateFrom').val($('#tanggal').val() || '');
                $('#historyDateTo').val($('#tanggal').val() || '');
                loadHistoryData();
            });

            // initial UI
            gateMeta = {};
            updateShiftGate(gateMeta);
            refreshButtons();

            // auto-load kalau outlet & petugas ada
            if ($('#outlet_id').val() && ($('#nama_petugas').val() || '').trim()) {
                loadData();
            }
        });
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
