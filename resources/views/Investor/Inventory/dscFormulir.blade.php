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
           Final MOBILE RESPONSIVE PATCH
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



        /* ==========================================================
           MOBILE SIMPLE READABLE PATCH - SMP FRIENDLY
           UI only: tidak mengubah ID, route, endpoint, atau logic JS.
           Fokus: 1 bahan per layar, istilah lebih mudah, tombol bawah tidak ribet.
           ========================================================== */
        @media (max-width: 767.98px) {
            body {
                background: #f3f6f3 !important;
                font-size: 14px !important;
            }

            main.container.wrap {
                max-width: 430px !important;
                padding: 10px 10px 112px !important;
            }

            .appbar,
            .ctx,
            .maincard,
            .box,
            #cards .bcard {
                border-radius: 16px !important;
                box-shadow: 0 8px 18px rgba(15, 23, 42, .06) !important;
            }

            .appbar h1 {
                font-size: 1.06rem !important;
                font-weight: 950 !important;
            }

            .appbar-actions {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                gap: 7px !important;
            }

            .appbar-actions .btn,
            .appbar-actions a.btn,
            .appbar-actions form .btn {
                height: 40px !important;
                min-height: 40px !important;
                font-size: .78rem !important;
                border-radius: 12px !important;
            }

            /* Load utama cukup di panel data awal, bukan terasa tombol kecil tersembunyi */
            #btnLoadCta {
                height: 46px !important;
                font-size: .94rem !important;
                border-radius: 13px !important;
            }

            #btnLoadCta span {
                display: inline !important;
                font-size: .72rem !important;
            }

            .ctx-head-v2,
            .main-head {
                padding: 11px 12px !important;
            }

            .ctx-title,
            .main-head .title {
                font-size: 1rem !important;
                font-weight: 950 !important;
            }

            .hint,
            .main-head .sub {
                display: block !important;
                font-size: .78rem !important;
                line-height: 1.35 !important;
                color: #6b7280 !important;
            }

            .ctx-body-v2 {
                padding: 10px !important;
            }

            .box-head h6 {
                font-size: .82rem !important;
                letter-spacing: .1px !important;
            }

            .form-label {
                font-size: .82rem !important;
                color: #374151 !important;
                margin-bottom: .24rem !important;
            }

            .form-control,
            .form-select,
            .select2-container .select2-selection--single,
            .bcard .form-control {
                height: 42px !important;
                min-height: 42px !important;
                font-size: .95rem !important;
                border-radius: 13px !important;
            }

            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 42px !important;
                font-size: .95rem !important;
            }

            .mobile-pager {
                grid-template-columns: 40px minmax(0, 1fr) 40px !important;
                padding: 8px !important;
                background: #fff !important;
                border-radius: 14px !important;
                margin: 0 0 8px 0 !important;
                border: 1px solid #e5e7eb !important;
            }

            .mobile-progress {
                font-size: .76rem !important;
                margin-bottom: 5px !important;
            }

            #mobJump {
                height: 38px !important;
                min-height: 38px !important;
                font-size: .84rem !important;
                border-radius: 11px !important;
            }

            #cards .bcard {
                padding: 12px !important;
                background: #fff !important;
            }

            .bcard .name {
                font-size: 1.05rem !important;
                line-height: 1.2 !important;
            }

            .bcard .meta {
                font-size: .78rem !important;
            }

            .summary {
                grid-template-columns: 1fr 1fr !important;
                gap: 8px !important;
                margin-top: 10px !important;
            }

            .summary .kv {
                min-height: 54px !important;
                padding: 8px 9px !important;
                border-radius: 13px !important;
                background: #f9fafb !important;
            }

            .summary .kv span {
                font-size: .7rem !important;
                color: #6b7280 !important;
            }

            .summary .kv b {
                font-size: .9rem !important;
            }

            .grid2,
            .grid3 {
                grid-template-columns: 1fr 1fr !important;
                gap: 9px !important;
            }

            .grid3 > div:first-child {
                grid-column: span 2 !important;
            }

            .bcard .mt-2 {
                margin-top: .7rem !important;
            }

            /* Bottom bar dibuat sederhana: Next, Draft, Final. History tetap ada di atas. */
            .p-2.p-sm-3.d-md-none .wh-footer {
                max-width: 410px !important;
                width: calc(100% - 20px) !important;
                left: 50% !important;
                right: auto !important;
                transform: translateX(-50%) !important;
                bottom: 10px !important;
                padding: 9px !important;
                border-radius: 18px !important;
                background: rgba(255,255,255,.96) !important;
                box-shadow: 0 16px 34px rgba(15, 23, 42, .18) !important;
            }

            .p-2.p-sm-3.d-md-none .wh-footer .left {
                display: none !important;
            }

            .p-2.p-sm-3.d-md-none .wh-footer .right {
                display: grid !important;
                grid-template-columns: .8fr 1fr 1fr !important;
                gap: 8px !important;
                width: 100% !important;
            }

            #btnHistoryMob {
                display: none !important;
            }

            .p-2.p-sm-3.d-md-none .wh-footer .right .btn {
                height: 46px !important;
                min-height: 46px !important;
                border-radius: 14px !important;
                font-size: .82rem !important;
                padding: 6px 5px !important;
                display: flex !important;
                flex-direction: column !important;
                gap: 2px !important;
                align-items: center !important;
                justify-content: center !important;
                line-height: 1.05 !important;
            }

            .p-2.p-sm-3.d-md-none .wh-footer .right .btn i {
                font-size: 1rem !important;
            }

            main {
                padding-bottom: 116px !important;
            }
        }

        @media (max-width: 350px) {
            .appbar-actions {
                grid-template-columns: 1fr !important;
            }

            .summary,
            .grid2,
            .grid3,
            .p-2.p-sm-3.d-md-none .wh-footer .right {
                grid-template-columns: 1fr !important;
            }

            .grid3 > div:first-child {
                grid-column: auto !important;
            }
        }

        /* ==========================================================
           PATCH MOBILE AUTO SUMMARY DSC
           Mobile card tetap 1 bahan per layar, tetapi angka otomatis
           ikut berubah saat user edit Ending, Purchase, Mutasi, Waste,
           dan tetap menampilkan data hasil filter/load.
           ========================================================== */
        @media (max-width: 767.98px) {
            #cards .summary {
                display: grid !important;
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                gap: 8px !important;
                margin-top: 10px !important;
            }

            #cards .summary .kv,
            #cards .summary .kv:nth-child(3) {
                grid-column: auto !important;
                display: grid !important;
                grid-template-columns: 1fr !important;
                gap: 3px !important;
                min-height: 54px !important;
                padding: 8px 9px !important;
                border: 1px solid #e5e7eb !important;
                border-radius: 13px !important;
                background: #f9fafb !important;
            }

            #cards .summary .kv span {
                color: #6b7280 !important;
                font-size: .68rem !important;
                font-weight: 950 !important;
                letter-spacing: .2px !important;
                text-transform: uppercase !important;
            }

            #cards .summary .kv b {
                font-size: .9rem !important;
                font-weight: 950 !important;
                text-align: right !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
                white-space: nowrap !important;
            }

            #cards .summary .kv-actual.neg {
                border-color: #fecaca !important;
                background: #fff5f5 !important;
            }

            #cards .summary .kv-actual.neg span,
            #cards .summary .kv-actual.neg b {
                color: #b91c1c !important;
            }
        }

        @media (max-width: 350px) {
            #cards .summary {
                grid-template-columns: 1fr !important;
            }
        }


        /* ==========================================================
           HISTORY FRIENDLY + WIB PATCH
           UI only: modal history lebih sederhana dan semua jam tampil WIB.
        ========================================================== */
        .dsc-history-modal .modal-dialog {
            max-width: min(1180px, calc(100vw - 24px));
        }
        .dsc-history-modal .modal-header {
            padding: 14px 18px !important;
        }
        .dsc-history-title {
            font-size: 1rem !important;
        }
        .dsc-history-subtitle {
            font-size: .8rem !important;
            margin-top: 4px !important;
        }
        .history-filter-card {
            padding: 12px !important;
            border-radius: 14px !important;
        }
        .history-filter-grid {
            grid-template-columns: 1.2fr .7fr .85fr .85fr auto !important;
            gap: 10px !important;
        }
        .history-search-row {
            margin-top: 10px !important;
            grid-template-columns: 1fr auto !important;
            gap: 10px !important;
        }
        .history-layout {
            grid-template-columns: minmax(0, 1fr) 300px !important;
            gap: 12px !important;
        }
        #tblHistoryDSC {
            min-width: 760px !important;
        }
        #tblHistoryDSC thead th {
            padding: 10px 12px !important;
            font-size: .76rem !important;
        }
        #tblHistoryDSC tbody td {
            padding: 10px 12px !important;
            font-size: .84rem !important;
        }
        .history-time {
            font-size: .82rem !important;
            line-height: 1.25 !important;
        }
        .history-user-main,
        .history-change-title {
            font-size: .88rem !important;
        }
        .history-user-sub,
        .history-change-meta {
            font-size: .72rem !important;
        }
        .history-diff {
            margin-top: 6px !important;
            gap: 6px !important;
        }
        .history-old-value,
        .history-new-value {
            max-width: 120px !important;
            padding: 4px 7px !important;
            font-size: .78rem !important;
        }
        .history-detail-panel {
            padding: 12px !important;
            border-radius: 14px !important;
        }
        .history-footbar {
            padding: 10px 2px 14px !important;
        }
        @media (max-width: 991.98px) {
            .history-layout {
                grid-template-columns: 1fr !important;
            }
            .history-detail-panel {
                max-height: none !important;
            }
        }
        @media (max-width: 575.98px) {
            .dsc-history-modal .modal-body {
                padding: 10px !important;
            }
            .history-filter-grid,
            .history-search-row {
                grid-template-columns: 1fr !important;
            }
            .history-table-scroll {
                max-height: 58vh !important;
            }
            #tblHistoryDSC {
                min-width: 680px !important;
            }
            .history-detail-panel {
                display: none;
            }
            .history-detail-panel.has-detail {
                display: block;
            }
        }



        /* PATCH SPV ADJUSTMENT VISIBILITY
           Kolom Adjustment hanya tampil untuk role SPV.
           Role lain tetap memakai nilai adjustment existing untuk rumus, tapi kolomnya disembunyikan dari UI.
        */
        .spv-only {
            display: none !important;
        }
        body.can-spv-adjust #tblDSC th.spv-only,
        body.can-spv-adjust #tblDSC td.spv-only {
            display: table-cell !important;
        }
        body.can-spv-adjust .spv-only-card {
            display: block !important;
        }

        /* PATCH SPV ADJUSTMENT FINAL LOCK */
        .spv-adjust-input {
            border-color: #f59e0b !important;
            background: #fff7ed !important;
        }
        .spv-adjust-input:disabled {
            background: #f3f4f6 !important;
            border-color: var(--border) !important;
        }

        .spv-correctable:not(:disabled) {
            border-color: #f59e0b !important;
            background: #fff7ed !important;
        }

        .spv-adjust-note {
            font-size: .78rem;
            font-weight: 900;
            color: #92400e;
        }


        /* PATCH: tampilkan tombol Koreksi Final di footer */
        #btnSaveSpvAdjustmentFoot,
        #btnSaveSpvAdjustmentMob {
            font-weight: 950;
        }

        @media (max-width: 767.98px) {
            .wh-footer .right {
                grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
            }
        }


        /* PATCH MOBILE FOOTER KOREKSI FINAL */
        @media (max-width: 767.98px) {
            .wh-footer .right {
                grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
                gap: 5px !important;
            }

            .wh-footer .right .btn {
                min-width: 0 !important;
                padding-left: 4px !important;
                padding-right: 4px !important;
                font-size: .72rem !important;
                line-height: 1.05 !important;
            }

            .wh-footer .right .btn span {
                display: inline-block;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                max-width: 100%;
            }
        }

    

        /* ==========================================================
           PATCH FOOTER ACTIONS FREEZE - DESKTOP + MOBILE
           Tujuan: tombol Next, Koreksi Final, Simpan Draft, dan Final
           tetap terlihat saat scroll. UI only: tidak mengubah ID, route,
           endpoint, payload, rumus, autosave, Simpan Draft, maupun Final.
           ========================================================== */
        .wh-footer {
            position: sticky !important;
            bottom: 10px !important;
            z-index: 999 !important;
            margin: 12px !important;
            border: 1px solid var(--border) !important;
            border-radius: 14px !important;
            background: rgba(255, 255, 255, .97) !important;
            backdrop-filter: blur(10px) !important;
            box-shadow: 0 8px 18px rgba(15, 23, 42, .10) !important;
        }

        .wh-footer .right {
            min-width: 0 !important;
        }

        .wh-footer .right .btn {
            white-space: nowrap !important;
        }

        @media (min-width: 768px) {
            .maincard {
                overflow: visible !important;
            }

            .dt-wrap {
                max-height: calc(100vh - 260px);
                overflow: auto !important;
                -webkit-overflow-scrolling: touch;
                overscroll-behavior: contain;
            }

            .wh-footer {
                left: 0 !important;
                right: 0 !important;
                transform: none !important;
            }
        }

        @media (max-width: 767.98px) {
            .wh-footer {
                position: fixed !important;
                left: 50% !important;
                right: auto !important;
                bottom: 10px !important;
                transform: translateX(-50%) !important;
                width: calc(100% - 20px) !important;
                max-width: 410px !important;
                z-index: 9999 !important;
                margin: 0 !important;
                padding: 9px !important;
                border-radius: 18px !important;
                background: rgba(255, 255, 255, .97) !important;
                box-shadow: 0 12px 28px rgba(15, 23, 42, .16) !important;
            }

            .wh-footer .left {
                display: none !important;
            }

            .wh-footer .right {
                width: 100% !important;
                display: grid !important;
                grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
                gap: 6px !important;
            }

            .wh-footer .right .btn {
                width: 100% !important;
                min-width: 0 !important;
                min-height: 44px !important;
                height: 44px !important;
                padding: 6px 4px !important;
                border-radius: 14px !important;
                font-size: .72rem !important;
                line-height: 1.05 !important;
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 2px !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
            }

            .wh-footer .right .btn i {
                font-size: 1rem !important;
                margin: 0 !important;
            }

            .wh-footer .right .btn span {
                display: inline-block !important;
                max-width: 100% !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
                white-space: nowrap !important;
            }

            main.container.wrap,
            main {
                padding-bottom: 132px !important;
            }
        }

        @media (max-width: 350px) {
            .wh-footer .right {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }

            main.container.wrap,
            main {
                padding-bottom: 190px !important;
            }
        }


        /* ==========================================================
           PATCH MOBILE OPENING NOTICE - CLEAN + NO FOOTER OVERLAY
           UI only: popup Update Terbaru DSC dibuat full mobile friendly.
           Tidak mengubah endpoint, payload, rumus, autosave, draft, atau final.
           ========================================================== */
        body.dsc-opening-notice-active .wh-footer {
            display: none !important;
        }

        body.dsc-opening-notice-active {
            overflow: hidden !important;
        }

        .dsc-start-popup {
            border-radius: 18px !important;
            overflow: hidden !important;
        }

        .dsc-start-title {
            font-size: 1.28rem !important;
            line-height: 1.15 !important;
            font-weight: 900 !important;
            color: #334155 !important;
            padding: 0 4px !important;
        }

        .dsc-start-html {
            margin: 0 !important;
            padding: 0 !important;
        }

        .dsc-start-box {
            text-align: left;
            line-height: 1.45;
            font-size: .92rem;
            color: #374151;
        }

        .dsc-start-box .intro {
            font-weight: 900;
            margin-bottom: 8px;
            color: #0f172a;
        }

        .dsc-start-box ol {
            padding-left: 18px;
            margin: 0 0 12px 0;
        }

        .dsc-start-box li {
            margin-bottom: 6px;
        }

        .dsc-start-net {
            padding: 10px 12px;
            border-radius: 13px;
            font-weight: 900;
        }

        @media (max-width: 575.98px) {
            .swal2-container.dsc-start-container {
                padding: 10px !important;
                align-items: center !important;
            }

            .swal2-popup.dsc-start-popup {
                width: calc(100vw - 20px) !important;
                max-width: 390px !important;
                max-height: calc(100dvh - 20px) !important;
                padding: 16px 14px 14px !important;
                border-radius: 18px !important;
            }

            .swal2-popup.dsc-start-popup .swal2-icon {
                width: 54px !important;
                height: 54px !important;
                margin: 4px auto 10px !important;
            }

            .swal2-popup.dsc-start-popup .swal2-icon-content {
                font-size: 2.05rem !important;
            }

            .swal2-popup.dsc-start-popup .swal2-title {
                margin: 0 0 10px !important;
                padding: 0 !important;
                font-size: 1.18rem !important;
                line-height: 1.15 !important;
            }

            .swal2-popup.dsc-start-popup .swal2-html-container {
                margin: 0 !important;
                padding: 0 2px !important;
                max-height: calc(100dvh - 190px) !important;
                overflow-y: auto !important;
                -webkit-overflow-scrolling: touch !important;
            }

            .dsc-start-box {
                font-size: .84rem !important;
                line-height: 1.38 !important;
            }

            .dsc-start-box .intro {
                font-size: .86rem !important;
                margin-bottom: 7px !important;
            }

            .dsc-start-box ol {
                padding-left: 18px !important;
                margin-bottom: 10px !important;
            }

            .dsc-start-box li {
                margin-bottom: 5px !important;
            }

            .dsc-start-net {
                padding: 9px 10px !important;
                border-radius: 12px !important;
                font-size: .82rem !important;
                line-height: 1.35 !important;
            }

            .swal2-popup.dsc-start-popup .swal2-actions {
                width: 100% !important;
                margin: 12px 0 0 !important;
            }

            .swal2-popup.dsc-start-popup .swal2-confirm {
                width: 100% !important;
                min-height: 46px !important;
                border-radius: 14px !important;
                font-size: .92rem !important;
                font-weight: 950 !important;
            }
        }



        /* ==========================================================
           FIX REQUEST: FOOTER TIDAK MENUTUP FORM + TOMBOL PER SHIFT
           - Desktop dan mobile diberi ruang bawah supaya footer tidak overlay input.
           - Tombol footer disembunyikan sesuai status:
             Shift 1 = Draft saja, Shift 2 = Draft + Final,
             Data sudah Final = Koreksi saja (jika role/meta mengizinkan).
           - UI only, tidak mengubah route, endpoint, payload, rumus, autosave,
             Simpan Draft, Final, atau Koreksi Final.
           ========================================================== */
        .dsc-action-hidden {
            display: none !important;
        }

        .dt-wrap {
            padding-bottom: 118px !important;
            scroll-padding-bottom: 140px !important;
        }

        #cards,
        .card-list {
            padding-bottom: 132px !important;
            scroll-padding-bottom: 150px !important;
        }

        #cards .bcard:last-child {
            margin-bottom: 132px !important;
        }

        .wh-footer {
            pointer-events: auto !important;
        }

        .wh-footer .right {
            align-items: stretch !important;
        }

        @media (min-width: 768px) {
            .maincard {
                padding-bottom: 4px !important;
            }

            .wh-footer {
                position: sticky !important;
                bottom: 12px !important;
            }

            .wh-footer .right {
                display: flex !important;
                justify-content: flex-end !important;
                flex-wrap: wrap !important;
            }
        }

        @media (max-width: 767.98px) {
            main.container.wrap,
            main {
                padding-bottom: calc(172px + env(safe-area-inset-bottom)) !important;
            }

            .wh-footer {
                bottom: calc(10px + env(safe-area-inset-bottom)) !important;
            }

            .wh-footer .right {
                display: grid !important;
                grid-template-columns: repeat(auto-fit, minmax(94px, 1fr)) !important;
                gap: 8px !important;
                width: 100% !important;
            }

            .wh-footer .right .btn:not(.dsc-action-hidden) {
                min-width: 0 !important;
                width: 100% !important;
            }
        }

        @media (max-width: 350px) {
            main.container.wrap,
            main {
                padding-bottom: calc(214px + env(safe-area-inset-bottom)) !important;
            }

            #cards,
            .card-list,
            #cards .bcard:last-child {
                padding-bottom: 180px !important;
                margin-bottom: 180px !important;
            }
        }



        /* ==========================================================
           FINAL FORCE FIX MOBILE FOOTER BUTTON VISIBILITY
           Masalah sebelumnya: .dsc-action-hidden kalah specificity oleh
           rule lama .wh-footer .right .btn { display:flex !important; }.
           Rule ini harus berada PALING BAWAH style agar tombol yang hidden
           benar-benar hilang di mobile dan desktop.
           ========================================================== */
        .wh-footer .right > .btn.dsc-action-hidden,
        .wh-footer .right > button.dsc-action-hidden,
        .wh-footer .right > .btn.d-none,
        .wh-footer .right > button.d-none,
        .wh-footer .right .btn.dsc-action-hidden,
        .wh-footer .right button.dsc-action-hidden,
        .wh-footer .right .btn.d-none,
        .wh-footer .right button.d-none {
            display: none !important;
            visibility: hidden !important;
            pointer-events: none !important;
        }

        body.dsc-shift-1:not(.dsc-final-lock) #btnSaveFinalFoot,
        body.dsc-shift-1:not(.dsc-final-lock) #btnSaveFinalMob,
        body.dsc-shift-1:not(.dsc-final-lock) #btnSaveSpvAdjustmentFoot,
        body.dsc-shift-1:not(.dsc-final-lock) #btnSaveSpvAdjustmentMob {
            display: none !important;
            visibility: hidden !important;
            pointer-events: none !important;
        }

        body.dsc-final-lock #btnSaveDraftFoot,
        body.dsc-final-lock #btnSaveDraftM,
        body.dsc-final-lock #btnSaveFinalFoot,
        body.dsc-final-lock #btnSaveFinalMob {
            display: none !important;
            visibility: hidden !important;
            pointer-events: none !important;
        }

        @media (max-width: 767.98px) {
            .wh-footer .right:has(.btn:not(.dsc-action-hidden):not(.d-none):nth-child(1)) {
                grid-template-columns: repeat(auto-fit, minmax(92px, 1fr)) !important;
            }
        }



        /* ==========================================================
           V4 FIX MOBILE FOOTER BUTTON RESPONSIVE
           Tombol bawah mobile dibuat adaptif sesuai jumlah tombol yang tampil:
           - 1 tombol: full width
           - 2 tombol: 2 kolom sama rata
           - 3 tombol: 3 kolom jika layar cukup, otomatis 2+1 di layar kecil
           - Koreksi/Draft/Final tetap mengikuti logic visibility JS.
           ========================================================== */
        @media (max-width: 767.98px) {
            .wh-footer {
                width: min(100% - 16px, 430px) !important;
                max-width: 430px !important;
                padding: 8px !important;
                border-radius: 18px !important;
            }

            .wh-footer .right {
                width: 100% !important;
                display: grid !important;
                gap: 7px !important;
                align-items: stretch !important;
            }

            .wh-footer .right.dsc-actions-count-1 {
                grid-template-columns: 1fr !important;
            }

            .wh-footer .right.dsc-actions-count-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }

            .wh-footer .right.dsc-actions-count-3,
            .wh-footer .right.dsc-actions-count-4 {
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            }

            .wh-footer .right > .btn:not(.dsc-action-hidden):not(.d-none) {
                width: 100% !important;
                min-width: 0 !important;
                height: 48px !important;
                min-height: 48px !important;
                padding: 6px 4px !important;
                border-radius: 15px !important;
                font-size: clamp(.68rem, 2.8vw, .82rem) !important;
                line-height: 1.05 !important;
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 3px !important;
                overflow: hidden !important;
                white-space: nowrap !important;
            }

            .wh-footer .right > .btn:not(.dsc-action-hidden):not(.d-none) i {
                font-size: 1.02rem !important;
                line-height: 1 !important;
                margin: 0 !important;
            }

            .wh-footer .right > .btn:not(.dsc-action-hidden):not(.d-none) span {
                display: block !important;
                max-width: 100% !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
                white-space: nowrap !important;
            }

            main.container.wrap,
            main {
                padding-bottom: calc(154px + env(safe-area-inset-bottom)) !important;
            }

            #cards,
            .card-list {
                padding-bottom: calc(132px + env(safe-area-inset-bottom)) !important;
            }
        }

        @media (max-width: 380px) {
            .wh-footer {
                width: calc(100% - 12px) !important;
                padding: 7px !important;
            }

            .wh-footer .right.dsc-actions-count-3,
            .wh-footer .right.dsc-actions-count-4 {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }

            .wh-footer .right.dsc-actions-count-3 > .btn:not(.dsc-action-hidden):not(.d-none):last-child {
                grid-column: 1 / -1 !important;
            }

            .wh-footer .right > .btn:not(.dsc-action-hidden):not(.d-none) {
                height: 46px !important;
                min-height: 46px !important;
                font-size: .72rem !important;
            }

            main.container.wrap,
            main {
                padding-bottom: calc(204px + env(safe-area-inset-bottom)) !important;
            }

            #cards,
            .card-list,
            #cards .bcard:last-child {
                padding-bottom: calc(184px + env(safe-area-inset-bottom)) !important;
                margin-bottom: calc(184px + env(safe-area-inset-bottom)) !important;
            }
        }



        /* ==========================================================
           V5 HARD FIX MOBILE FOOTER RESPONSIVE
           Tujuan: tombol Draft / Final / Koreksi tidak loncat posisi.
           Mobile footer memakai flex, bukan grid lama, supaya tombol yang
           disembunyikan tidak meninggalkan kolom kosong.
           ========================================================== */
        @media (max-width: 767.98px) {
            .wh-footer {
                width: min(calc(100vw - 16px), 430px) !important;
                max-width: 430px !important;
                left: 50% !important;
                right: auto !important;
                transform: translateX(-50%) !important;
                bottom: calc(10px + env(safe-area-inset-bottom)) !important;
                padding: 8px !important;
                border-radius: 18px !important;
                overflow: hidden !important;
            }

            .wh-footer .left {
                display: none !important;
            }

            .wh-footer .right {
                width: 100% !important;
                display: flex !important;
                flex-direction: row !important;
                flex-wrap: nowrap !important;
                gap: 8px !important;
                align-items: stretch !important;
                justify-content: center !important;
            }

            .wh-footer .right > .btn,
            .wh-footer .right > button {
                min-width: 0 !important;
                width: auto !important;
                max-width: none !important;
                flex: 1 1 0 !important;
                height: 50px !important;
                min-height: 50px !important;
                padding: 6px 4px !important;
                border-radius: 15px !important;
                font-size: clamp(.70rem, 2.9vw, .84rem) !important;
                line-height: 1.05 !important;
                flex-direction: column !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 3px !important;
                white-space: nowrap !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
            }

            .wh-footer .right > .btn i,
            .wh-footer .right > button i {
                font-size: 1.05rem !important;
                line-height: 1 !important;
                margin: 0 !important;
            }

            .wh-footer .right > .btn span,
            .wh-footer .right > button span {
                display: block !important;
                max-width: 100% !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
                white-space: nowrap !important;
            }

            .wh-footer .right.dsc-actions-count-0 {
                display: none !important;
            }

            .wh-footer .right.dsc-actions-count-1 > .btn:not(.dsc-action-hidden):not(.d-none),
            .wh-footer .right.dsc-actions-count-1 > button:not(.dsc-action-hidden):not(.d-none) {
                flex: 0 1 100% !important;
            }

            .wh-footer .right.dsc-actions-count-2 > .btn:not(.dsc-action-hidden):not(.d-none),
            .wh-footer .right.dsc-actions-count-2 > button:not(.dsc-action-hidden):not(.d-none) {
                flex: 1 1 0 !important;
            }

            .wh-footer .right.dsc-actions-count-3 {
                flex-wrap: nowrap !important;
            }

            main.container.wrap,
            main {
                padding-bottom: calc(165px + env(safe-area-inset-bottom)) !important;
            }

            #cards,
            .card-list,
            #cards .bcard:last-child {
                padding-bottom: calc(145px + env(safe-area-inset-bottom)) !important;
                margin-bottom: calc(40px + env(safe-area-inset-bottom)) !important;
            }
        }

        @media (max-width: 360px) {
            .wh-footer .right {
                gap: 6px !important;
            }

            .wh-footer .right > .btn,
            .wh-footer .right > button {
                height: 48px !important;
                min-height: 48px !important;
                font-size: .68rem !important;
            }

            .wh-footer .right.dsc-actions-count-3 {
                flex-wrap: wrap !important;
            }

            .wh-footer .right.dsc-actions-count-3 > .btn:not(.dsc-action-hidden):not(.d-none),
            .wh-footer .right.dsc-actions-count-3 > button:not(.dsc-action-hidden):not(.d-none) {
                flex: 1 1 calc(50% - 6px) !important;
            }
        }

        /* Hidden paling kuat: jangan biarkan rule lama display:flex membuat kolom kosong. */
        .wh-footer .right > .btn.dsc-action-hidden,
        .wh-footer .right > button.dsc-action-hidden,
        .wh-footer .right > .btn.d-none,
        .wh-footer .right > button.d-none,
        .wh-footer .right > .btn[hidden],
        .wh-footer .right > button[hidden] {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            pointer-events: none !important;
            flex: 0 0 0 !important;
            width: 0 !important;
            min-width: 0 !important;
            max-width: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
            border: 0 !important;
            overflow: hidden !important;
        }

    </style>
</head>

<body>
    <main class="container py-3 wrap">

        <!-- APP BAR (TOP BAR) -->
        <div class="appbar">
            <div class="appbar-inner d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h1>DSC • Input Stok</h1>
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
                    {{-- History sementara dimatikan untuk menurunkan CPU/I/O --}}

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
                        Data Awal
                    </div>
                    <div class="hint" id="infoText">Isi data awal lalu klik Load.</div>
                </div>

                <div class="d-flex gap-2 flex-wrap align-items-center">
                    <span class="badge-wh" id="countBadge"><i class="bi bi-box-seam"></i> 0</span>
                    <span class="badge-wh" title="Auto dihitung dari baris 'Tepung Breader'">
                        <i class="bi bi-flower1"></i>
                        Actual Tepung: <span class="mono" id="actualTepungLabel">0.00</span>
                    </span>
                    <span class="badge-wh" id="statusBadge"><span class="dot"></span> Belum load</span>
                    <span class="badge-wh" id="lastSavedBadge"><i class="bi bi-hdd"></i> Autosave OFF - lokal</span>
                    <span class="badge-wh" id="shiftAlert" style="display:none"></span>
                </div>

                <div class="w-100 mt-2 alert alert-warning py-2 px-3 mb-0 small fw-bold" id="autosaveOffNotice">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Autosave server sementara dimatikan untuk menurunkan beban CPU/I/O. Input tetap tersimpan lokal di HP/browser ini. Klik <b>Simpan Draft</b> untuk kirim ke server, lalu <b>Final</b> jika data sudah benar.
                </div>
            </div>

            <div class="ctx-body ctx-body-v2">
                <div class="ctx-grid-v2">

                    <!-- PANEL: FORM KONTEX -->
                    <div class="box">
                        <div class="box-head">
                            <h6>1) Data Awal</h6>
                            <span class="pill-req"><i class="bi bi-asterisk"></i> wajib</span>
                        </div>

                        <div class="row g-2 mt-1">
                            <div class="col-12">
                                <label class="form-label">Outlet <span class="text-danger">*</span></label>
                                <select id="outlet_id" class="form-select" required>
                                    <option value="">-- Pilih Outlet --</option>
                                    @php($selectedOutletForSelect = collect($outlets ?? [])->firstWhere('id', (int) ($outletId ?? 0)))
                                    @if(!empty($selectedOutletForSelect))
                                        <option value="{{ $selectedOutletForSelect->id }}" selected>{{ $selectedOutletForSelect->nama_outlet }}</option>
                                    @elseif(!empty($outletId))
                                        <option value="{{ $outletId }}" selected>Outlet terpilih</option>
                                    @endif
                                </select>
                                <div class="help-mini">Pilih outlet sesuai lokasi kerja.</div>
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
                                <label class="form-label">Uang Plus</label>
                                <div class="input-group input-group-v2">
                                    <span class="input-group-text"><i class="bi bi-cash-coin"></i></span>
                                    <input type="number" id="uang_plus_shift" class="form-control mono" step="0.01" value="0">
                                </div>
                                <div class="help-mini">Isi jika ada uang plus di shift ini.</div>
                            </div>
                        </div>

                        <!-- CTA Load: UNIQUE ID -->
                        <div class="mt-3">
                            <button class="btn btn-primary btn-lg w-100" id="btnLoadCta" type="button">
                                <i class="bi bi-cloud-download me-1"></i> Load Data
                                <span class="ms-2" style="font-size:.78rem; font-weight:800; opacity:.8;">Ambil bahan</span>
                            </button>
                        </div>
                    </div>

                    <!-- PANEL: SEARCH + QUICK ACTIONS -->
                    <div class="right-stack">

                        <!-- SEARCH -->
                        <div class="box">
                            <div class="box-head">
                                <h6>2) Cari Bahan</h6>
                                <span class="pill-tip"><i class="bi bi-lightbulb"></i> tip</span>
                            </div>

                            <div class="scanbar mt-2">
                                <div class="scanicon"><i class="bi bi-upc-scan"></i></div>
                                <input id="searchAny" class="form-control" placeholder="Cari nama bahan...">
                            </div>
                            <div class="help-mini mt-2">
                                Ketik nama bahan agar cepat ketemu.
                            </div>
                        </div>

                        <!-- QUICK ACTIONS -->
                        @php($hideSaveSection = true)
                        @if(!$hideSaveSection)
                        <div class="box">
                            <div class="box-head">
                                <h6>3) Simpan</h6>
                                <span class="pill-safe"><i class="bi bi-shield-check"></i> aman</span>
                            </div>

                            <div class="row g-2 mt-2">
                                <div class="col-12 col-md-6">
                                    <button class="btn btn-outline-secondary w-100" id="btnNextZeroDesk"
                                        type="button">
                                        <i class="bi bi-arrow-right-circle me-1"></i> Next
                                    </button>
                                </div>
                                <div class="col-12 col-md-6">
                                    <button class="btn btn-outline-primary w-100" id="btnSaveDraftDesk"
                                        type="button">
                                        <i class="bi bi-pencil-square me-1"></i> Simpan Draft
                                    </button>
                                </div>
                                <div class="col-12">
                                    <!-- Final: UNIQUE ID -->
                                    <button class="btn btn-accent btn-lg w-100" id="btnSaveFinalDesk" type="button">
                                        <i class="bi bi-save me-1"></i> Simpan Final
                                        <span class="ms-2"
                                            style="font-size:.78rem; font-weight:800;">Shift 1+2</span>
                                    </button>
                                </div>
                            </div>

                            <div class="help-mini mt-2">Shift 1 simpan draft. Shift 2 bisa final.</div>
                        </div>
                        @endif
                    </div>

                </div>
            </div>
        </section>

        <!-- MAIN LIST (FULL WIDTH) -->
        <section class="maincard">
            <div class="main-head">
                <div>
                    <div class="title">Input Bahan</div>
                    <div class="sub">Isi stok akhir dan waste. Angka lain otomatis.</div>
                </div>
                <span class="badge-wh"><i class="bi bi-table"></i> Mode Input</span>
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

                                <th style="width:120px;" title="Stok Awal = stok awal otomatis.">Open</th>
                                <th style="width:140px;" title="PURCHASE IN = barang masuk dari pembelian.">Purchase
                                    In</th>
                                <th style="width:140px;" title="MUTASI IN = perpindahan stok masuk.">Mutasi In</th>
                                <th style="width:140px;" title="MUTASI OUT = perpindahan stok keluar.">Mutasi Out</th>
                                <th class="spv-only" style="width:140px;" title="Koreksi Final = koreksi setelah final.">Adjustment</th>

                                <th style="width:130px;" title="Total Stok = Open + Purchase + MutIn - MutOut + Adjustment">Total</th>

                                <th style="width:140px;" title="ENDING = stok akhir fisik.">Ending</th>
                                <th style="width:140px;" title="Terpakai = Total - Ending">Actual Used</th>

                                <th style="width:140px;">Waste Prod</th>
                                <th style="width:140px;">Waste Bahan</th>
                                <th style="width:140px;" title="Waste Tepung = WasteProd + WasteBahan">Waste Tepung
                                </th>

                                <th style="width:140px;" title="Khusus 'Tepung Breader'">Actual Tepung</th>

                                <th style="width:280px;">Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="16" class="text-center text-muted p-4">Klik <b>Load</b> dulu.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="wh-footer">
                    <div class="left">
                        <span id="warnEmpty"><i class="bi bi-exclamation-triangle me-1"></i> Stok akhir masih kosong.</span>
                    </div>
                    <div class="right">
                        <button class="btn btn-outline-secondary" id="btnNextZeroFoot" type="button">
                            <i class="bi bi-arrow-right-circle me-1"></i>Next
                        </button>
                        {{-- History sementara dimatikan untuk menurunkan CPU/I/O --}}
                        <button class="btn btn-warning d-none" id="btnSaveSpvAdjustmentFoot" type="button">
                            <i class="bi bi-sliders me-1"></i>Koreksi Final
                        </button>
                        <button class="btn btn-outline-primary" id="btnSaveDraftFoot" type="button">
                            <i class="bi bi-pencil-square me-1"></i>Simpan Draft
                        </button>
                        <!-- Final: UNIQUE ID -->
                        <button class="btn btn-accent" id="btnSaveFinalFoot" type="button">
                            <i class="bi bi-save me-1"></i>Final
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
                        <span id="warnEmptyM"><i class="bi bi-exclamation-triangle me-1"></i> Stok akhir masih kosong.</span>
                    </div>
                    <div class="right">
                        <button class="btn btn-outline-secondary" id="btnNextZeroMob" type="button"><i class="bi bi-arrow-right-circle"></i><span>Next</span></button>
                        {{-- History sementara dimatikan untuk menurunkan CPU/I/O --}}
                        <button class="btn btn-warning d-none" id="btnSaveSpvAdjustmentMob" type="button"><i class="bi bi-sliders"></i><span>Koreksi</span></button>
                        <button class="btn btn-outline-primary" id="btnSaveDraftM" type="button"><i class="bi bi-pencil-square"></i><span>Draft</span></button>
                        <!-- Final: UNIQUE ID -->
                        <button class="btn btn-accent" id="btnSaveFinalMob" type="button"><i class="bi bi-check-circle"></i><span>Final</span></button>
                    </div>
                </div>
            </div>

        </section>

        {{-- MODAL HISTORY DSC dimatikan sementara untuk menurunkan CPU/I/O. --}}

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
        const URL_SAVE = `{{ route('saveSo') }}`; // /save-so  (Final -> tbl_stock)
        const URL_SAVE_DRAFT = `{{ route('dsc.save-draft') }}`; // /save-draft (draft)
        const URL_SAVE_SPV_ADJUSTMENT = `{{ url('/master/dsc/formulir/spv-adjustment') }}`; // PATCH: SPV adjustment after final
        const URL_HISTORY = `{{ route('dsc.history') }}`; // JSON history DSC

        // ========= BUTTON GROUPS (UNIQUE IDs) =========
        const BTN = {
            load: '#btnLoadNav, #btnLoadCta',
            draft: '#btnSaveDraftDesk, #btnSaveDraftFoot, #btnSaveDraftM',
            final: '#btnSaveFinalDesk, #btnSaveFinalFoot, #btnSaveFinalMob',
            next0: '#btnNextZeroDesk, #btnNextZeroFoot, #btnNextZeroMob',
            history: '#btnHistoryNav, #btnHistoryFoot, #btnHistoryMob',
            spvAdjust: '#btnSaveSpvAdjustmentFoot, #btnSaveSpvAdjustmentMob'
        };

        // ========= STATE =========
        const st = {}; // per bahan_id
        let items = []; // list dari BE
        let loaded = false;
        let kasirClosed = false;
        let actualTepungMeta = 0;
        let gateMeta = {}; // meta untuk shift gate
        let canSpvAdjust = false; // PATCH: SPV/TM Manager/Superadmin boleh koreksi field tertentu saat FINAL/LOCK
        const CURRENT_USER_ROLE = `{{ strtolower(auth()->user()->role ?? '') }}`;
        const CAN_FINAL_CORRECTION_ROLE =
            CURRENT_USER_ROLE.includes('spv') ||
            CURRENT_USER_ROLE === 'superadmin' ||
            CURRENT_USER_ROLE === 'tm_manager' ||
            CURRENT_USER_ROLE === 'tm manager' ||
            CURRENT_USER_ROLE === 'tm-manager' ||
            CURRENT_USER_ROLE.includes('tm_manager') ||
            CURRENT_USER_ROLE.includes('tm manager') ||
            CURRENT_USER_ROLE.includes('tm-manager');

        // Backward compatible: nama variable lama tetap dipakai oleh patch existing.
        const IS_SPV_USER = CAN_FINAL_CORRECTION_ROLE;
        let mobileIndex = 0; // mobile: tampilkan 1 bahan aktif agar tidak scroll panjang
        let lastEditedBahanId = null; // bahan terakhir yang user ubah, supaya History tidak salah bahan

        // ========= AUTO SAVE SILENT =========
        // Mode aman nasional: server autosave sementara dimatikan untuk mengurangi I/O.
        // Local draft tetap aktif. Simpan ke server hanya lewat tombol Simpan Draft / Final.
        // Tidak mengubah logic rumus, payload, Simpan Draft manual, dan Final.
        let autoSaveTimer = null;
        let autoSaveRunning = false;
        let autoSaveQueued = false;
        let localDraftSaveTimer = null;

        // OPTIMASI I/O:
        // Server autosave dimatikan agar tidak POST draft setiap user mengetik.
        // Data tetap aman di browser lewat localStorage yang disimpan debounce.
        // Kirim ke server hanya saat user klik Simpan Draft / Simpan Final.
        const AUTO_SAVE_ENABLED = false;
        const AUTO_SAVE_DELAY = 30000;
        const LOCAL_DRAFT_SAVE_DELAY = 1500;

        // touched tracker (yang user pernah ubah)
        const touched = {}; // { bahan_id: true }
        let uangPlusDirty = false; // true kalau input Uang Plus diubah user
        let lastAutoSaveHash = null;
        let lastAutoSaveAt = null;

        // ========= SAVE GUARD =========
        // Mencegah user klik berkali-kali / pindah halaman saat request simpan masih berjalan.
        // Ini juga memberi pesan jelas kalau token CSRF/session kadaluarsa (HTTP 419).
        let manualSaveRunning = false;

        function setSavingUi(isSaving, message = 'Sistem sedang proses penyimpanan, harap tunggu...') {
            manualSaveRunning = !!isSaving;
            $(BTN.load + ', ' + BTN.draft + ', ' + BTN.final + ', ' + BTN.next0 + ', ' + BTN.spvAdjust).prop('disabled', !!isSaving);

            if (isSaving) {
                setStatus('loading', message);
                $('#infoText').text(message);
            } else {
                refreshButtons();
            }
        }

        window.addEventListener('beforeunload', function (e) {
            // Final FIX: popup bawaan browser hanya muncul saat user menekan Simpan manual/final.
            // Autosave berjalan di background dan tidak perlu mengganggu tim ops.
            if (manualSaveRunning) {
                e.preventDefault();
                e.returnValue = 'Data masih dalam proses penyimpanan. Tunggu sampai proses selesai.';
            }
        });

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
            if (res && res.status === 419) {
                return 'Token keamanan halaman sudah kadaluarsa / session berubah. Sistem belum bisa menyimpan. Jangan input ulang dulu; refresh halaman, load data terakhir, lalu lanjutkan.';
            }
            if (res && res.status === 0) {
                return 'Koneksi terputus. Harap tunggu sampai internet stabil, lalu coba simpan lagi.';
            }
            if (json) return json.error || json.message || `HTTP ${res.status}`;
            if (raw) return `Non-JSON response (HTTP ${res.status}): ${raw.slice(0, 400)}`;
            return `HTTP ${res.status}`;
        }

        function handleSaveError(title, res, json, raw, err) {
            const msg = err?.message || pickErrorMessage(res, json, raw);
            if (res && res.status === 419) {
                return Swal.fire({
                    icon: 'warning',
                    title: 'Token Kadaluarsa',
                    html: 'Simpan gagal karena token halaman sudah tidak valid.<br><b>Harap tunggu, jangan klik berulang.</b><br>Refresh halaman, klik Load, lalu cek apakah draft/final terakhir sudah masuk.',
                    confirmButtonText: 'Refresh Sekarang',
                    confirmButtonColor: '#0f172a',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then(() => window.location.reload());
            }
            return swError(title, msg);
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

        // PATCH SAFE SELECT2 GROUP OUTLET:
        // Select2 AJAX bisa memilih value "group_xxx". Untuk endpoint save yang masih
        // validasi integer, ambil ID outlet pertama dari data Select2 kalau tersedia.
        // Kalau ID tidak tersedia, value asli tetap dikirim karena controller sudah punya
        // fallback normalisasi group_xxx. Ini tidak mengubah rumus/payload baris stok.
        function selectedOutletIdForServer() {
            const raw = ($('#outlet_id').val() || '').toString().trim();
            if (/^\d+$/.test(raw)) return raw;

            try {
                const selected = $('#outlet_id').select2('data');
                const item = selected && selected.length ? selected[0] : null;
                const ids = item ? (item.ids || item.merged_ids || item.alias_ids || null) : null;

                if (Array.isArray(ids) && ids.length) {
                    const id = parseInt(ids[0], 10);
                    if (id > 0) return String(id);
                }

                if (typeof ids === 'string') {
                    const m = ids.match(/\d+/);
                    if (m) return m[0];
                }
            } catch (e) {}

            const opt = $('#outlet_id option:selected').get(0);
            const optIds = opt && opt.dataset ? (opt.dataset.ids || '') : '';
            const optMatch = optIds.match(/\d+/);
            if (optMatch) return optMatch[0];

            const label = ($('#outlet_id option:selected').text() || '').toString();
            const m = label.match(/ID:\s*([0-9]+)/i) || label.match(/\b([0-9]{1,10})\b/);
            if (m && m[1]) return m[1];

            return raw;
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
                setShiftBadge('warn', 'Shift 1: hanya tampil tombol Simpan Draft.');
                return;
            }
        
            if (!okS1) {
                setShiftBadge('bad', 'Shift 1 belum ada — Final Shift 2 dikunci');
                return;
            }
        
            // kalau cuma tahu has_shift_1, kamu bisa tampilkan generic “siap”
            if (hasDraftS1 && !hasFinalS1) {
                setShiftBadge('warn', 'Shift 1 masih draft — akan difinalkan bersama Shift 2');
                return;
            }
        
            setShiftBadge('ok', 'Shift 1 sudah siap');
        }

        function setFooterActionVisible(selector, visible) {
            const $btn = $(selector);
            if (!$btn.length) return;

            $btn.each(function () {
                const el = this;

                if (visible) {
                    $(el)
                        .removeClass('d-none dsc-action-hidden')
                        .prop('hidden', false);

                    // Pakai !important supaya menang dari CSS lama .wh-footer .right .btn { display:flex !important; }.
                    el.style.setProperty('display', 'flex', 'important');
                    el.style.setProperty('visibility', 'visible', 'important');
                    el.style.setProperty('opacity', '1', 'important');
                    el.style.setProperty('pointer-events', 'auto', 'important');
                    el.style.removeProperty('width');
                    el.style.removeProperty('max-width');
                    el.style.removeProperty('min-width');
                    el.style.removeProperty('padding');
                    el.style.removeProperty('margin');
                    el.style.removeProperty('border');
                    el.style.removeProperty('flex');
                } else {
                    $(el)
                        .addClass('d-none dsc-action-hidden')
                        .prop('hidden', true)
                        .prop('disabled', true);

                    // Hide fisik, bukan cuma visibility, agar tidak ada kolom kosong di mobile.
                    el.style.setProperty('display', 'none', 'important');
                    el.style.setProperty('visibility', 'hidden', 'important');
                    el.style.setProperty('opacity', '0', 'important');
                    el.style.setProperty('pointer-events', 'none', 'important');
                    el.style.setProperty('flex', '0 0 0', 'important');
                    el.style.setProperty('width', '0', 'important');
                    el.style.setProperty('max-width', '0', 'important');
                    el.style.setProperty('min-width', '0', 'important');
                    el.style.setProperty('padding', '0', 'important');
                    el.style.setProperty('margin', '0', 'important');
                    el.style.setProperty('border', '0', 'important');
                }
            });
        }

        function canFinalByShiftGate() {
            if (!isShift2()) return false;

            const hasDraftS1 = !!gateMeta.has_draft_s1;
            const hasFinalS1 = !!gateMeta.has_final_s1;
            const hasS1 = !!gateMeta.has_shift_1;

            return hasDraftS1 || hasFinalS1 || hasS1;
        }

        function refreshFooterActionVisibility() {
            const ok = validateHeader();
            const ready = ok && loaded;
            const isFinalLocked = !!kasirClosed;
            const shiftValue = String($('#shift').val() || '');

            $('body')
                .toggleClass('dsc-shift-1', shiftValue === '1')
                .toggleClass('dsc-shift-2', shiftValue === '2')
                .toggleClass('dsc-ready', !!ready)
                .toggleClass('dsc-final-lock', !!isFinalLocked);

            // Default aman: semua action footer disembunyikan dulu.
            // Ini mencegah tombol Final/Koreksi sisa dari outlet/tanggal/shift sebelumnya
            // tetap kelihatan ketika user baru pindah ke Shift 1 atau belum Load ulang.
            let showDraft = false;
            let showFinal = false;
            let showSpvCorrection = false;

            if (ready) {
                if (isFinalLocked) {
                    // Data tanggal/shift ini sudah final: hanya Koreksi Final yang boleh muncul,
                    // dan hanya untuk role yang memang diizinkan oleh backend/meta.
                    showSpvCorrection = canSpvAdjust && CAN_FINAL_CORRECTION_ROLE;
                } else if (shiftValue === '1') {
                    // Shift 1 baru input: hanya Draft. Final dan Koreksi wajib hilang.
                    showDraft = true;
                    showFinal = false;
                    showSpvCorrection = false;
                } else if (shiftValue === '2') {
                    // Shift 2: Draft selalu boleh, Final hanya kalau Shift 1 sudah ada/siap.
                    showDraft = true;
                    showFinal = canFinalByShiftGate();
                    showSpvCorrection = false;
                }
            }

            // Sesuai request:
            // - Shift 1: hanya Draft.
            // - Shift 2: Draft dan Final jika gate Shift 1 sudah siap.
            // - Tanggal/data sudah Final: hanya Koreksi Final.
            // Tombol Next disembunyikan dari footer agar menu bawah tidak menutupi form.
            setFooterActionVisible(BTN.next0, false);
            setFooterActionVisible(BTN.draft, showDraft);
            setFooterActionVisible(BTN.final, showFinal);
            setFooterActionVisible(BTN.spvAdjust, showSpvCorrection);

            // Double safety khusus Shift 1: walaupun ada rule lama atau response final dari state lama,
            // Final/Koreksi tetap hilang sampai user benar-benar load data final untuk shift tersebut.
            if (shiftValue === '1' && !isFinalLocked) {
                setFooterActionVisible(BTN.final, false);
                setFooterActionVisible(BTN.spvAdjust, false);
            }

            refreshFooterActionLayout();
        }

        function refreshFooterActionLayout() {
            $('.wh-footer .right').each(function () {
                const $right = $(this);
                const count = $right.children('.btn').filter(function () {
                    const $btn = $(this);
                    return !$btn.hasClass('dsc-action-hidden') &&
                        !$btn.hasClass('d-none') &&
                        !$btn.prop('hidden') &&
                        $btn.css('display') !== 'none';
                }).length;

                $right.removeClass('dsc-actions-count-0 dsc-actions-count-1 dsc-actions-count-2 dsc-actions-count-3 dsc-actions-count-4');
                $right.addClass('dsc-actions-count-' + Math.min(count, 4));
            });
        }

        function resetLoadedStateBecauseHeaderChanged() {
            // Saat Outlet/Tanggal/Shift/Petugas berubah, data yang sedang tampil tidak boleh
            // dianggap sebagai status aktif lagi. Tanpa reset ini, kalau sebelumnya membuka
            // data FINAL lalu pindah ke Shift 1, tombol Koreksi bisa masih terlihat di mobile.
            loaded = false;
            kasirClosed = false;
            canSpvAdjust = false;
            gateMeta = {};
            updateShiftGate(gateMeta);
            setStatus('', 'Belum load');
            $('#infoText').text('Data awal berubah. Klik Load untuk mengambil data shift ini.');
            $(BTN.spvAdjust).addClass('d-none dsc-action-hidden').hide().prop('disabled', true);
            $(BTN.final).addClass('dsc-action-hidden').hide().prop('disabled', true);
            $(BTN.draft).addClass('dsc-action-hidden').hide().prop('disabled', true);
            refreshButtons();
        }

        function refreshButtons() {
            const ok = validateHeader();
        
            // PATCH LOCK UX:
            // Data Awal tetap bisa dipakai untuk cek outlet/tanggal/shift lain,
            // jadi tombol Load tidak boleh ikut disabled saat data sudah Final/LOCK.
            $(BTN.load).prop('disabled', !ok);

            const canDraft = ok && loaded && !kasirClosed && (isShift1() || isShift2());
            const canFinal = ok && loaded && !kasirClosed && isShift2() && canFinalByShiftGate();

            $(BTN.draft).prop('disabled', !canDraft);
            $(BTN.final).prop('disabled', !canFinal);

            // PATCH SPV ADJUSTMENT:
            // Tombol khusus hanya aktif jika data sudah FINAL/LOCK dan role SPV/TM Manager/Superadmin.
            const canSaveSpvAdjustment = ok && loaded && kasirClosed && canSpvAdjust && CAN_FINAL_CORRECTION_ROLE;
            $(BTN.spvAdjust).prop('disabled', !canSaveSpvAdjustment);

            refreshFooterActionVisibility();
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

          <td><input class="form-control input-mini pin spv-correctable" type="number" step="0.01" value="${x.pin}"></td>
          <td><input class="form-control input-mini mi spv-correctable" type="number" step="0.01" value="${x.mi}"></td>
          <td><input class="form-control input-mini mo spv-correctable" type="number" step="0.01" value="${x.mo}"></td>
          <td class="spv-only"><input class="form-control input-mini adj spv-adjust-input spv-correctable" type="number" step="0.01" value="${x.adj || 0}" title="Koreksi Final"></td>

          <td class="mono num-read text-end total">${fmt(c.total)}</td>

          <td><input class="form-control input-mini ending spv-correctable" type="number" step="0.01" value="${x.ending}"></td>

          <td class="mono text-end actual ${negClass}">${fmt(c.actualUsed)}</td>

          <td><input class="form-control input-mini wprod spv-correctable" type="number" step="0.01" value="${x.wProd}"></td>
          <td><input class="form-control input-mini wbahan spv-correctable" type="number" step="0.01" value="${x.wBahan}"></td>

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
            <div class="kv"><span>Stok Awal</span><b class="sum-open">${fmt(x.open)}</b></div>
            <div class="kv"><span>Purchase In</span><b class="sum-pin">${fmt(x.pin)}</b></div>
            <div class="kv"><span>Mutasi In</span><b class="sum-mi">${fmt(x.mi)}</b></div>
            <div class="kv"><span>Mutasi Out</span><b class="sum-mo">${fmt(x.mo)}</b></div>
            <div class="kv"><span>Adjustment</span><b class="sum-adj">${fmt(x.adj || 0)}</b></div>
            <div class="kv"><span>Total Stok</span><b class="sum-total">${fmt(c.total)}</b></div>
            <div class="kv"><span>Stok Akhir</span><b class="sum-ending">${fmt(x.ending)}</b></div>
            <div class="kv ${negClass} kv-actual"><span>Actual Used</span><b class="sum-actual">${fmt(c.actualUsed)}</b></div>
            <div class="kv"><span>Waste Produk</span><b class="sum-wprod">${fmt(x.wProd)}</b></div>
            <div class="kv"><span>Waste Bahan</span><b class="sum-wbahan">${fmt(x.wBahan)}</b></div>
            <div class="kv"><span>Waste Tepung</span><b class="sum-wt">${fmt(c.wasteTepung)}</b></div>
            <div class="kv"><span>Actual Tepung</span><b class="sum-at">${fmt(actualTepung)}</b></div>
          </div>

          <div class="grid3">
            <div>
              <label class="form-label small mb-1">Barang Masuk</label>
              <input class="form-control pin spv-correctable" type="number" step="0.01" value="${x.pin}">
            </div>
            <div>
              <label class="form-label small mb-1">Mutasi IN</label>
              <input class="form-control mi spv-correctable" type="number" step="0.01" value="${x.mi}">
            </div>
            <div>
              <label class="form-label small mb-1">Mutasi OUT</label>
              <input class="form-control mo spv-correctable" type="number" step="0.01" value="${x.mo}">
            </div>
          </div>

          <div class="mt-2 spv-only spv-only-card">
            <label class="form-label small mb-1">Adjustment <span class="spv-adjust-note">SPV</span></label>
            <input class="form-control adj spv-adjust-input spv-correctable" type="number" step="0.01" value="${x.adj || 0}">
          </div>

          <div class="mt-2">
            <label class="form-label small mb-1">Stok Akhir <span class="text-danger">*</span></label>
            <input class="form-control ending spv-correctable" type="number" step="0.01" value="${x.ending}">
          </div>

          <div class="grid2">
            <div>
              <label class="form-label small mb-1">Waste Produk</label>
              <input class="form-control wprod spv-correctable" type="number" step="0.01" value="${x.wProd}">
            </div>
            <div>
              <label class="form-label small mb-1">Waste Bahan</label>
              <input class="form-control wbahan spv-correctable" type="number" step="0.01" value="${x.wBahan}">
            </div>
          </div>

          <div class="mt-2">
            <label class="form-label small mb-1">Keterangan</label>
            <input class="form-control ket" type="text" value="${esc(x.ket)}" placeholder="opsional...">
          </div>
        </div>
      `;
        }

        function refreshMobileCard(id) {
            const x = ensure(id);
            const $card = $('#cards .bcard[data-id="' + id + '"]');
            if (!$card.length) return;

            const c = calc(x);
            const isTep = isTepungName(x.nama);
            const actualTepung = isTep ? (c.actualUsed - c.wasteTepung) : 0;

            $card.find('.sum-open').text(fmt(x.open));
            $card.find('.sum-pin').text(fmt(x.pin));
            $card.find('.sum-mi').text(fmt(x.mi));
            $card.find('.sum-mo').text(fmt(x.mo));
            $card.find('.sum-adj').text(fmt(x.adj || 0));
            $card.find('.sum-total').text(fmt(c.total));
            $card.find('.sum-ending').text(fmt(x.ending));
            $card.find('.sum-actual').text(fmt(c.actualUsed));
            $card.find('.sum-wprod').text(fmt(x.wProd));
            $card.find('.sum-wbahan').text(fmt(x.wBahan));
            $card.find('.sum-wt').text(fmt(c.wasteTepung));
            $card.find('.sum-at').text(fmt(actualTepung));
            $card.find('.kv-actual').toggleClass('neg', c.actualUsed < 0);
        }

        function refreshAllMobileCards() {
            items.forEach(it => refreshMobileCard(it.id));
        }

        function renderAll() {
            const $tb = $('#tblDSC tbody').empty();
            if (!items.length) {
                $tb.html(`<tr><td colspan="16" class="text-center text-muted p-4">Tidak ada bahan.</td></tr>`);
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

                /*
                 |--------------------------------------------------------------------------
                 | FIX MUTASI OUT MASUK KE ENDING, BUKAN ACTUAL USED
                 |--------------------------------------------------------------------------
                 | Mutasi Out adalah perpindahan stok keluar, bukan pemakaian.
                 | Jika user mengubah Mutasi Out, Ending otomatis dikurangi sebesar
                 | delta Mutasi Out supaya Actual Used tetap mengikuti pemakaian real.
                 |
                 | Contoh:
                 | Actual Used awal 45, Mutasi Out ditambah 100
                 | => Ending turun 100
                 | => Actual Used tetap 45
                 |--------------------------------------------------------------------------
                 */
                const isMutasiOutInput = $(this).hasClass('mo');
                const prevMo = Number(x.mo || 0);

                x.pin = toNum($tr.find('input.pin').val());
                x.mi = toNum($tr.find('input.mi').val());
                x.mo = toNum($tr.find('input.mo').val());
                x.adj = toNum($tr.find('input.adj').val());
                x.ending = toNum($tr.find('input.ending').val());

                if (isMutasiOutInput) {
                    const deltaMo = Number(x.mo || 0) - prevMo;
                    if (Math.abs(deltaMo) >= 0.00001) {
                        x.ending = Number(x.ending || 0) - deltaMo;
                        $tr.find('input.ending').val(x.ending);
                    }
                }

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
                    $card.find('input.adj').val(x.adj);
                    $card.find('input.ending').val(x.ending);
                    $card.find('input.wprod').val(x.wProd);
                    $card.find('input.wbahan').val(x.wBahan);
                    $card.find('input.ket').val(x.ket);
                    refreshMobileCard(id);
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

                /*
                 |--------------------------------------------------------------------------
                 | FIX MUTASI OUT MASUK KE ENDING, BUKAN ACTUAL USED - MOBILE
                 |--------------------------------------------------------------------------
                 | Sama seperti desktop: saat Mutasi OUT berubah, Ending otomatis
                 | turun/naik mengikuti delta Mutasi OUT agar Actual Used tidak berubah.
                 |--------------------------------------------------------------------------
                 */
                const isMutasiOutInput = $(this).hasClass('mo');
                const prevMo = Number(x.mo || 0);

                x.pin = toNum($card.find('input.pin').val());
                x.mi = toNum($card.find('input.mi').val());
                x.mo = toNum($card.find('input.mo').val());
                x.adj = toNum($card.find('input.adj').val());
                x.ending = toNum($card.find('input.ending').val());

                if (isMutasiOutInput) {
                    const deltaMo = Number(x.mo || 0) - prevMo;
                    if (Math.abs(deltaMo) >= 0.00001) {
                        x.ending = Number(x.ending || 0) - deltaMo;
                        $card.find('input.ending').val(x.ending);
                    }
                }

                x.wProd = toNum($card.find('input.wprod').val());
                x.wBahan = toNum($card.find('input.wbahan').val());
                x.ket = ($card.find('input.ket').val() || '');

                const $tr = $('#tblDSC tbody tr[data-id="' + id + '"]');
                if ($tr.length) {
                    $tr.find('input.pin').val(x.pin);
                    $tr.find('input.mi').val(x.mi);
                    $tr.find('input.mo').val(x.mo);
                    $tr.find('input.adj').val(x.adj);
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

                refreshMobileCard(id);
                syncActualTepungLabel();
                validateEndingEmptyForTouchedOnly();
                refreshMobilePager();
                scheduleAutoSave();
            });
        }

        // ========= LOCK UI =========
        let finalLockPopupShownKey = null;

        function lockUI(isLock, meta = null, options = {}) {
            kasirClosed = !!isLock;

            /*
             * PATCH LOCK UX:
             * Saat data sudah Final/LOCK, yang dikunci hanya area Input Bahan.
             * Data Awal tetap aktif supaya user bisa cek outlet/tanggal/shift lain lalu klik Load.
             *
             * Tetap aktif:
             * - Outlet
             * - Tanggal
             * - Shift
             * - Petugas
             * - Uang Plus
             * - Tombol Load
             *
             * Disabled saat Final:
             * - Cari Bahan
             * - Input table/card bahan
             * - Next
             * - Simpan Draft
             * - Simpan Final
             */
            const $inputBahanTargets = $(
                '#searchAny,' +
                '#tblDSC input, #cards input,' +
                BTN.draft + ',' + BTN.final + ',' + BTN.next0
            );

            $inputBahanTargets.prop('disabled', kasirClosed);

            // Jika FINAL/LOCK dan role SPV, hanya field Adjustment yang tetap bisa diedit.
            if (kasirClosed && canSpvAdjust && CAN_FINAL_CORRECTION_ROLE) {
                $('#tblDSC input.spv-correctable, #cards input.spv-correctable').prop('disabled', false);
                $(BTN.spvAdjust).removeClass('d-none').prop('disabled', false);
                $('#infoText').text('Data sudah final. SPV/TM Manager/Superadmin bisa koreksi Purchase/Mutasi/Adjustment/Ending/Waste/Uang Plus.');
            } else {
                $(BTN.spvAdjust).addClass('d-none').prop('disabled', true);
            }

            // Paksa Data Awal tetap aktif walaupun data final.
            $('#outlet_id, #tanggal, #shift, #nama_petugas, #uang_plus_shift').prop('disabled', false);

            // PATCH MOBILE:
            // Preview/Next bahan dan dropdown pilih bahan harus tetap aktif walaupun data final,
            // supaya user tetap bisa cek bahan lain di HP.
            $('#mobJump, #btnMobPrev, #btnMobNext').prop('disabled', false);

            $(BTN.load).prop('disabled', !validateHeader());

            if (kasirClosed) {
                setStatus('bad', 'Final (LOCK)');
                $('#infoText').text(`Anda sudah simpan final pada tanggal ini${meta?.closed_at ? ' • ' + meta.closed_at : ''}`);

                const popupKey = [
                    $('#outlet_id').val() || '',
                    $('#tanggal').val() || '',
                    meta?.closed_at || 'closed'
                ].join('|');

                if (options.showPopup && finalLockPopupShownKey !== popupKey) {
                    finalLockPopupShownKey = popupKey;

                    return Swal.fire({
                        icon: 'info',
                        title: 'Data Sudah Simpan Final',
                        html: `Data pada tanggal ini sudah disimpan final.<br><b>Tabel Input Bahan dikunci. SPV/TM Manager/Superadmin bisa koreksi field tertentu termasuk Uang Plus. Navigasi bahan tetap aktif.</b>${meta?.closed_at ? '<br><small>Waktu final: ' + esc(meta.closed_at) + '</small>' : ''}`,
                        confirmButtonText: 'Oke',
                        confirmButtonColor: '#0f172a'
                    }).then(function () {
                        refreshButtons();
                    });
                }
            }

            refreshButtons();
            return Promise.resolve();
        }

        // ========= API: LOAD =========
        let loadRequestRunning = false;

        async function loadData(showNotif = false) {
            if (loadRequestRunning) {
                $('#infoText').text('Load sedang berjalan, tunggu sampai selesai.');
                return;
            }

            if (!validateHeader()) {
                await swWarn('Lengkapi data', 'Outlet / Tanggal / Shift / Petugas wajib diisi sebelum Load.');
                return;
            }

            loadRequestRunning = true;
            // Selama Load berjalan, jangan tampilkan action sisa dari shift sebelumnya.
            loaded = false;
            kasirClosed = false;
            canSpvAdjust = false;
            refreshFooterActionVisibility();
            $(BTN.load).prop('disabled', true);
            $('#infoText').text('Loading data...');
            setStatus('loading', 'Loading...');

            try {
                const qs = new URLSearchParams({
                    outlet_id: selectedOutletIdForServer(),
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
                if (!res.ok || !json || !json.ok) { const err = new Error(pickErrorMessage(res, json, raw)); err.res = res; err.json = json; err.raw = raw; throw err; }

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
                const cleanedCarryOverCount = items.filter(it => Number(it.carryover_cleaned || 0) === 1).length;
                actualTepungMeta = Number(json.data?.meta?.actual_tepung || 0);
                gateMeta = json.data?.meta || {};
                canSpvAdjust = IS_SPV_USER && !!Number(gateMeta.can_spv_adjust || 0);

                items.forEach(it => ensure(it.id, it));
                normalizeUangPlusShiftState();
                loaded = true;

                renderAll();
                bindInputs();
                // FIX: Jangan munculkan popup localStorage otomatis setelah Load.
                // Popup ini menimpa data server dan mengganggu proses upsert.
                // Backup lokal tetap ada, tapi tidak dipulihkan otomatis.
                // restoreLocalDraftAfterLoad();

                if (cleanedCarryOverCount > 0) {
                    $('#infoText').text(`Loaded: ${items.length} bahan. ${cleanedCarryOverCount} baris purchase/mutasi Shift 1 otomatis tidak dibawa ke Shift 2.`);
                    setStatus('ok', 'Shift 2 bersih');
                } else {
                    $('#infoText').text(`Loaded: ${items.length} bahan`);
                    setStatus('ok', 'Siap input');
                }

                // lock kasir
                const kasir = json.data?.lock || {
                    is_closed: false
                };

                updateShiftGate(gateMeta);
                validateEndingEmptyForTouchedOnly();

                /*
                 * PATCH POPUP ORDER:
                 * 1. Kalau user klik tombol Load manual, tampilkan dulu: Data berhasil di load.
                 * 2. Setelah itu, kalau data sudah Final/LOCK, tampilkan popup final.
                 * 3. Disabled hanya tabel/card Input Bahan, bukan Data Awal.
                 */
                if (showNotif) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Data Berhasil Di Load',
                        text: 'Data berhasil di load.',
                        timer: 1200,
                        timerProgressBar: true,
                        showConfirmButton: false
                    });
                }

                await lockUI(!!kasir.is_closed, kasir, { showPopup: true });
            } catch (e) {
                console.error(e);
                setStatus('bad', 'Gagal');
                $('#infoText').text('Gagal load.');
                await swError('Gagal Load', e.message);
            } finally {
                loadRequestRunning = false;
                refreshButtons();
            }
        }

        // ========= PAYLOAD BUILDER =========

        /*
         |--------------------------------------------------------------------------
         | FIX MOBILE NEXT/PREVIOUS + OFFLINE SAFE
         |--------------------------------------------------------------------------
         | Di HP, user sering isi satu field lalu langsung tap Next/Previous/pindah Shift.
         | Pada beberapa browser mobile, event input/change/blur belum sempat mem-push
         | nilai terakhir ke object st sebelum card disembunyikan atau payload dibuat.
         |
         | Helper ini memaksa nilai DOM terakhir disalin dulu ke state st + touched,
         | sehingga Ayam Kecil tidak hilang setelah user pindah dari Ayam Besar,
         | pindah Shift, offline, atau langsung klik Simpan Draft.
         |--------------------------------------------------------------------------
         */
        function syncBahanFromDom(bahanId) {
            const id = Number(bahanId || 0);
            if (!id) return false;

            const x = ensure(id);
            let $scope = $('#cards .bcard[data-id="' + id + '"]');

            if (!$scope.length) {
                $scope = $('#tblDSC tbody tr[data-id="' + id + '"]');
            }

            if (!$scope.length) return false;

            markTouched(id);

            x.pin = toNum($scope.find('input.pin').val());
            x.mi = toNum($scope.find('input.mi').val());
            x.mo = toNum($scope.find('input.mo').val());
            x.adj = toNum($scope.find('input.adj').val());
            x.ending = toNum($scope.find('input.ending').val());
            x.wProd = toNum($scope.find('input.wprod').val());
            x.wBahan = toNum($scope.find('input.wbahan').val());
            x.ket = ($scope.find('input.ket').val() || '');

            return true;
        }

        function syncActiveBahanFromDom() {
            const $activeInput = $('#cards .bcard.active-card input:focus, #tblDSC tbody input:focus').first();

            if ($activeInput.length) {
                const $row = $activeInput.closest('.bcard, tr');
                const id = Number($row.data('id'));
                if (id) {
                    syncBahanFromDom(id);
                    return id;
                }
            }

            const $activeCard = $('#cards .bcard.active-card').first();
            if ($activeCard.length) {
                const id = Number($activeCard.data('id'));
                if (id) {
                    syncBahanFromDom(id);
                    return id;
                }
            }

            return null;
        }

        function syncAllTouchedFromDom() {
            syncActiveBahanFromDom();
            items.forEach(it => {
                const id = Number(it.id);
                if (touched[id]) {
                    syncBahanFromDom(id);
                }
            });
        }

        function buildPayloadDraft() {
            syncAllTouchedFromDom();

            const payload = {
                outlet_id: selectedOutletIdForServer(),
                tanggal: $('#tanggal').val(),
                shift: $('#shift').val(),
                nama_petugas: ($('#nama_petugas').val() || '').trim(),
                rows: []
            };

            // FIX UPSERT:
            // Simpan Draft manual wajib kirim semua baris, bukan hanya touched.
            // Kalau hanya touched, beberapa input mobile/restore/localStorage bisa dianggap
            // tidak berubah sehingga rows kosong / tidak lengkap dan upsert tidak menyimpan.
            let selectedItems = [...items];

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



        // ========= OFFLINE SAFE LOCAL DRAFT =========
        // Patch anti data hilang untuk HP/jaringan jelek:
        // - setiap input disimpan dulu ke localStorage HP
        // - server autosave sementara OFF; Simpan Draft manual tetap pakai tbl_stock_draft seperti logic lama
        // - kalau session habis / sinyal putus, data tetap bisa direstore setelah login/load ulang
        const DSC_LOCAL_DRAFT_VERSION = 2;
        const DSC_LOCAL_DRAFT_PREFIX = 'dsc_offline_stock_draft_v2';

        function localDraftKey() {
            return [
                DSC_LOCAL_DRAFT_PREFIX,
                $('#outlet_id').val() || 'outlet',
                $('#tanggal').val() || 'tanggal',
                $('#shift').val() || 'shift'
            ].join(':');
        }

        function collectFullLocalDraft(reason = 'input') {
            syncAllTouchedFromDom();

            const rows = items.map((it, i) => {
                const x = ensure(it.id, it);
                return {
                    bahan_id: Number(it.id),
                    nama_bahan: x.nama || it.nama_bahan || '',
                    satuan: x.sat || it.satuan || '',
                    opening_stock: Number(x.open || 0),
                    purchase_in: Number(x.pin || 0),
                    mutasi_in: Number(x.mi || 0),
                    mutasi_out: Number(x.mo || 0),
                    adjustment_qty: Number(x.adj || 0),
                    ending_stock: Number(x.ending || 0),
                    waste_product: Number(x.wProd || 0),
                    waste_bahan: Number(x.wBahan || 0),
                    waste_tepung: Number((x.wProd || 0) + (x.wBahan || 0)),
                    uang_plus: i === 0 ? getUangPlusShift() : Number(x.uang || 0),
                    keterangan: x.ket || '',
                    touched: !!touched[Number(it.id)]
                };
            });

            return {
                version: DSC_LOCAL_DRAFT_VERSION,
                reason,
                saved_at: new Date().toISOString(),
                saved_at_label: new Date().toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' }),
                outlet_id: selectedOutletIdForServer(),
                tanggal: $('#tanggal').val(),
                shift: $('#shift').val(),
                nama_petugas: ($('#nama_petugas').val() || '').trim(),
                uang_plus_shift: getUangPlusShift(),
                touched_ids: Object.keys(touched).map(Number),
                rows
            };
        }

        function scheduleLocalDraftSave(reason = 'input', immediate = false) {
            clearTimeout(localDraftSaveTimer);

            if (immediate) {
                saveLocalDraftNow(reason);
                return;
            }

            localDraftSaveTimer = setTimeout(function () {
                saveLocalDraftNow(reason);
            }, LOCAL_DRAFT_SAVE_DELAY);
        }

        function saveLocalDraftNow(reason = 'input') {
            // Jangan terlalu ketat validateHeader di local draft.
            // Kalau user sedang offline / baru pindah shift, simpan dulu di HP selama data utama ada.
            if (!loaded || !items.length) return;
            if (!($('#outlet_id').val() && $('#tanggal').val() && $('#shift').val())) return;

            syncAllTouchedFromDom();

            try {
                localStorage.setItem(localDraftKey(), JSON.stringify(collectFullLocalDraft(reason)));

                if (!navigator.onLine) {
                    setStatus('bad', 'Offline - aman di HP');
                    updateLastSavedBadge('pending');
                    $('#infoText').text('Offline: draft tetap tersimpan lokal di HP/browser ini. Saat internet normal, klik Simpan Draft untuk kirim ke server.');
                }
            } catch (e) {
                console.warn('Local draft gagal disimpan', e);
                $('#infoText').text('Penyimpanan lokal HP penuh/tidak tersedia. Segera klik Simpan Draft saat koneksi normal.');
            }
        }

        function clearLocalDraft() {
            try { localStorage.removeItem(localDraftKey()); } catch (e) {}
        }

        function applyLocalDraft(localDraft) {
            if (!localDraft || !Array.isArray(localDraft.rows)) return false;

            const byId = {};
            localDraft.rows.forEach(r => {
                const id = Number(r.bahan_id || 0);
                if (id) byId[id] = r;
            });

            items.forEach(it => {
                const id = Number(it.id);
                const r = byId[id];
                if (!r) return;

                const x = ensure(id, it);
                x.open = Number(r.opening_stock ?? x.open ?? 0);
                x.pin = Number(r.purchase_in ?? x.pin ?? 0);
                x.mi = Number(r.mutasi_in ?? x.mi ?? 0);
                x.mo = Number(r.mutasi_out ?? x.mo ?? 0);
                x.adj = Number(r.adjustment_qty ?? x.adj ?? 0);
                x.ending = Number(r.ending_stock ?? x.ending ?? 0);
                x.wProd = Number(r.waste_product ?? x.wProd ?? 0);
                x.wBahan = Number(r.waste_bahan ?? x.wBahan ?? 0);
                x.uang = Number(r.uang_plus ?? x.uang ?? 0);
                x.ket = (r.keterangan ?? x.ket ?? '').toString();

                if (r.touched || (localDraft.touched_ids || []).map(Number).includes(id)) {
                    touched[id] = true;
                    lastEditedBahanId = id;
                }
            });

            if (typeof localDraft.uang_plus_shift !== 'undefined') {
                $('#uang_plus_shift').val(Number(localDraft.uang_plus_shift || 0));
                uangPlusDirty = true;
            }

            renderAll();
            bindInputs();
            refreshAllMobileCards();
            syncActualTepungLabel();
            validateEndingEmptyForTouchedOnly();
            refreshButtons();
            setStatus('ok', 'Draft lokal dipulihkan');
            $('#infoText').text('Draft dari HP berhasil dipulihkan. Klik Simpan Draft saat koneksi normal.');
            return true;
        }

        async function restoreLocalDraftAfterLoad() {
            let raw = null;
            try { raw = localStorage.getItem(localDraftKey()); } catch (e) { raw = null; }
            if (!raw) return;

            let localDraft = null;
            try { localDraft = JSON.parse(raw); } catch (e) { return; }
            if (!localDraft || !Array.isArray(localDraft.rows) || !localDraft.rows.length) return;

            const touchedCount = (localDraft.touched_ids || []).length || localDraft.rows.filter(r => r.touched).length;
            const savedAt = localDraft.saved_at_label || '-';

            const ans = await Swal.fire({
                icon: 'warning',
                title: 'Ada draft belum tersinkron di HP ini',
                html: `Ditemukan backup lokal <b>${touchedCount || localDraft.rows.length} bahan</b><br>Terakhir: <b>${esc(savedAt)}</b><br><br>Pulihkan supaya input tidak hilang?`,
                showCancelButton: true,
                showDenyButton: true,
                confirmButtonText: 'Pulihkan',
                denyButtonText: 'Hapus backup',
                cancelButtonText: 'Nanti',
                reverseButtons: true,
                confirmButtonColor: '#0f172a'
            });

            if (ans.isConfirmed) {
                clearTimeout(localDraftSaveTimer);
                applyLocalDraft(localDraft);
                clearLocalDraft();
                isDirty = false;
            } else if (ans.isDenied) {
                clearTimeout(localDraftSaveTimer);
                clearLocalDraft();
                updateLastSavedBadge('pending');
                $('#infoText').text('Backup lokal HP dihapus.');
            }
        }

        function markLocalSynced() {
            clearLocalDraft();
        }
        function buildPayloadFinal() {
            const outletId = selectedOutletIdForServer();
            const tanggal = $('#tanggal').val();
            const picRaw = ($('#nama_petugas').val() || '').trim();

            return {
                outlet_id: outletId,
                tanggal,
                shift: 2,
                nama_petugas: picRaw,

                // OPTIONAL: kalau backend kamu pakai finalize both
                finalize_both: 1,

                // Final: kirim semua rows biar tbl_stock lengkap
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
                $b.html('<i class="bi bi-hdd"></i> Draft lokal tersimpan');
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
            if (kasirClosed || !loaded) return;
            if (!validateHeader()) return;

            // Simpan lokal dengan debounce supaya localStorage tidak ditulis setiap keypress.
            scheduleLocalDraftSave('typing', false);
            updateLastSavedBadge('pending');

            if (!AUTO_SAVE_ENABLED) {
                $('#infoText').text('Autosave server OFF. Draft aman tersimpan lokal di HP/browser ini. Klik Simpan Draft untuk kirim ke server.');
                return;
            }

            clearTimeout(autoSaveTimer);
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
                    const err = new Error(pickErrorMessage(res, json, raw));
                    err.res = res;
                    err.json = json;
                    err.raw = raw;
                    throw err;
                }

                lastAutoSaveHash = payloadHash;
                markLocalSynced();
                setStatus('ok', 'Auto saved');
                updateLastSavedBadge('ok');
                $('#infoText').text('Perubahan tersimpan sebagai draft server.');
            } catch (e) {
                console.error(e);
                saveLocalDraftNow('autosave_failed');
                setStatus('bad', navigator.onLine ? 'Backup di HP' : 'Offline - aman di HP');
                updateLastSavedBadge('error', e.message);
                $('#infoText').text('Server belum tersimpan, tapi draft aman lokal di HP/browser ini. Login ulang/online lalu Load untuk pulihkan.');
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
            if (manualSaveRunning) return swWarn('Harap tunggu', 'Sistem masih proses penyimpanan. Jangan klik berulang.');
            if (autoSaveRunning) return swWarn('Harap tunggu', 'Autosave masih berjalan. Tunggu sampai status selesai, lalu klik lagi.');
            if (kasirClosed) return swWarn('Sudah Final', 'Kasir sudah ditutup. Tidak bisa simpan/edit.');
            if (!loaded) return swWarn('Belum load', 'Klik Load dulu.');
            if (!validateHeader()) return swWarn('Data belum lengkap', 'Lengkapi Outlet/Tanggal/Shift/Nama Petugas.');

            saveLocalDraftNow('manual_draft_before_send');

            const payload = buildPayloadDraft();
            if (!payload.rows.length) {
                return swWarn('Tidak ada perubahan', 'Belum ada baris yang diubah/diisi. Tidak ada yang disimpan.');
            }

            try {
                setSavingUi(true, 'Menyimpan draft. Harap tunggu, sistem sedang proses penyimpanan...');
                swLoading('Menyimpan draft. Harap tunggu...');
                const res = await fetch(URL_SAVE_DRAFT, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        ...apiHeaders()
                    },
                    body: JSON.stringify(payload)
                });

                const { json, raw } = await readJsonOrText(res);
                if (!res.ok || !json || !json.ok) { const err = new Error(pickErrorMessage(res, json, raw)); err.res = res; err.json = json; err.raw = raw; throw err; }

                Swal.close();
                lastAutoSaveHash = JSON.stringify(buildPayloadDraft());
                clearTimeout(localDraftSaveTimer);
                markLocalSynced();
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
                await handleSaveError('Draft gagal', e.res || null, e.json || null, e.raw || null, e);
                throw e;
            } finally {
                setSavingUi(false);
            }
        }

        // ========= Final =========
        async function saveFinal() {
            syncAllTouchedFromDom();

            if (manualSaveRunning) return swWarn('Harap tunggu', 'Sistem masih proses penyimpanan. Jangan klik berulang.');
            if (autoSaveRunning) return swWarn('Harap tunggu', 'Autosave masih berjalan. Tunggu sampai status selesai, lalu klik lagi.');
            if (kasirClosed) return swWarn('Sudah Final', 'Kasir sudah ditutup. Tidak bisa simpan/edit.');
            if (!loaded) return swWarn('Belum load', 'Klik Load dulu.');
            if (!validateHeader()) return swWarn('Data belum lengkap', 'Lengkapi Outlet/Tanggal/Shift/Nama Petugas.');

            // SHIFT 1: Final selalu ditolak
            if (isShift1()) {
                return swWarn('Shift 1', 'Shift 1 hanya boleh Simpan Draft. Pindah ke Shift 2 untuk Simpan Final.');
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

            saveLocalDraftNow('final_before_send');

            const outletId = selectedOutletIdForServer();
            const tanggal = $('#tanggal').val();
            const picEnc = encodeURIComponent(($('#nama_petugas').val() || '').trim());

            const payload = buildPayloadFinal();

            try {
                setSavingUi(true, 'Menyimpan Final SO. Harap tunggu, sistem sedang proses penyimpanan...');
                swLoading('Menyimpan Final SO. Harap tunggu...');
                const res = await fetch(URL_SAVE, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        ...apiHeaders()
                    },
                    body: JSON.stringify(payload)
                });

                const { json, raw } = await readJsonOrText(res);
                if (!res.ok || !json || !json.ok) {
                    const err = new Error(pickErrorMessage(res, json, raw));
                    err.res = res;
                    err.json = json;
                    err.raw = raw;
                    throw err;
                }

                clearTimeout(localDraftSaveTimer);
                markLocalSynced();
                Swal.close();

                const lockMeta = json.data?.lock || {
                    is_closed: true,
                    closed_shift: 2
                };

                lockUI(true, lockMeta, { showPopup: false });

                await Swal.fire({
                    icon: 'success',
                    title: 'Final Berhasil',
                    html: 'Data Shift 1 dan Shift 2 sudah menjadi final.<br><b>Tanggal ini sudah dikunci dan tidak bisa diedit.</b>',
                    confirmButtonText: 'Oke',
                    confirmButtonColor: '#0f172a'
                });

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
                    await loadData();
                }
            } catch (e) {
                Swal.close();
                console.error(e);
                await handleSaveError('Final SO gagal', e.res || null, e.json || null, e.raw || null, e);
            } finally {
                setSavingUi(false);
            }
        }


        // ========= SPV ADJUSTMENT AFTER FINAL =========
        function buildPayloadSpvAdjustment() {
            // FIX KOREKSI FINAL BALIK LAGI:
            // Pastikan nilai terakhir dari input DOM masuk ke object st sebelum payload dibuat.
            // Tanpa ini, edit terakhir di HP/desktop bisa belum tersalin saat tombol Koreksi diklik.
            syncAllTouchedFromDom();

            return {
                // Pakai context yang sudah di-Load agar koreksi tidak nyasar shift/outlet lain.
                outlet_id: (typeof loadedOutletIdForSave !== 'undefined' && loadedOutletIdForSave) ? loadedOutletIdForSave : selectedOutletIdForServer(),
                tanggal: (typeof loadedTanggalForSave !== 'undefined' && loadedTanggalForSave) ? loadedTanggalForSave : $('#tanggal').val(),
                shift: (typeof loadedShiftForSave !== 'undefined' && loadedShiftForSave) ? loadedShiftForSave : $('#shift').val(),
                nama_petugas: ($('#nama_petugas').val() || '').trim(),

                /*
                 * SPV correction:
                 * Field dikirim eksplisit sesuai kolomnya, jadi BE tahu harus update ke kolom mana:
                 * - purchase_in
                 * - mutasi_in
                 * - mutasi_out
                 * - adjustment_qty
                 * - ending_stock
                 * - waste_product
                 * - waste_bahan
                 */
                /*
                 * PATCH UANG PLUS KOREKSI FINAL:
                 * Uang Plus adalah nilai per shift, bukan per bahan.
                 * Supaya tidak dobel, sama seperti draft/final:
                 * - baris bahan pertama membawa nilai uang_plus shift
                 * - baris lainnya dikirim 0 untuk mereset sisa nilai lama
                 */
                rows: items.map((it, i) => {
                    const x = ensure(it.id);
                    const uangPlusShift = getUangPlusShift();

                    return {
                        bahan_id: it.id,
                        purchase_in: Number(x.pin || 0),
                        mutasi_in: Number(x.mi || 0),
                        mutasi_out: Number(x.mo || 0),
                        adjustment_qty: Number(x.adj || 0),
                        ending_stock: Number(x.ending || 0),
                        waste_product: Number(x.wProd || 0),
                        waste_bahan: Number(x.wBahan || 0),
                        uang_plus: i === 0 ? uangPlusShift : 0
                    };
                })
            };
        }


        async function saveSpvAdjustment() {
            if (!CAN_FINAL_CORRECTION_ROLE || !canSpvAdjust) {
                return swWarn('Akses ditolak', 'Hanya SPV, TM Manager, atau Superadmin yang boleh simpan koreksi setelah final.');
            }
            if (!loaded) return swWarn('Belum load', 'Klik Load dulu.');
            if (!kasirClosed) return swWarn('Belum Final', 'Koreksi hanya untuk data yang sudah Final/LOCK.');
            if (!validateHeader()) return swWarn('Data belum lengkap', 'Lengkapi Outlet/Tanggal/Shift/Nama Petugas.');
            if (typeof validateLoadedContextForSave === 'function') {
                const loadedCtxSpv = validateLoadedContextForSave();
                if (!loadedCtxSpv.ok) return swWarn('Load ulang dulu', loadedCtxSpv.message);
            }

            const confirm = await Swal.fire({
                icon: 'question',
                title: 'Simpan Koreksi Final?',
                html: 'Status tetap <b>FINAL/LOCK</b>.<br>Yang disimpan hanya kolom koreksi SPV yang diizinkan.',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#0f172a',
                reverseButtons: true
            });

            if (!confirm.isConfirmed) return;

            try {
                setSavingUi(true, 'Menyimpan koreksi SPV...');
                swLoading('Menyimpan koreksi SPV...');

                const res = await fetch(URL_SAVE_SPV_ADJUSTMENT, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        ...apiHeaders()
                    },
                    body: JSON.stringify(buildPayloadSpvAdjustment())
                });

                const { json, raw } = await readJsonOrText(res);
                if (!res.ok || !json || !json.ok) {
                    const err = new Error(pickErrorMessage(res, json, raw));
                    err.res = res;
                    err.json = json;
                    err.raw = raw;
                    throw err;
                }

                Swal.close();

                await Swal.fire({
                    icon: 'success',
                    title: 'Koreksi Final Tersimpan',
                    html: 'Koreksi berhasil disimpan.<br><b>Status data tetap FINAL/LOCK.</b>',
                    timer: 1500,
                    timerProgressBar: true,
                    showConfirmButton: false
                });

                await loadData(false);
            } catch (e) {
                Swal.close();
                console.error(e);
                await handleSaveError('Koreksi Final gagal', e.res || null, e.json || null, e.raw || null, e);
            } finally {
                setSavingUi(false);
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

        // ========= HISTORY DSC MODAL DIMATIKAN SEMENTARA =========
        function bindHistoryButtonClick() {
            $(document).off('click.dschistory', BTN.history);
            $(BTN.history).addClass('d-none').prop('disabled', true);
        }

        function dscShowOpeningNotice() {
            if (typeof Swal === 'undefined') return;

            const isOnline = navigator.onLine;
            const networkText = isOnline
                ? 'Status browser online. Pastikan sinyal tetap stabil sampai muncul status tersimpan.'
                : 'Status browser offline. Jangan lanjut input dulu sebelum koneksi kembali normal.';

            Swal.fire({
                icon: isOnline ? 'info' : 'warning',
                title: 'Update Terbaru DSC',
                html: `
                    <div class="dsc-start-box">
                        <div class="intro">Sebelum mulai input, perhatikan aturan terbaru:</div>
                        <ol>
                            <li><b>Internet wajib stabil.</b> Draft disimpan lokal saat mengetik. Klik <b>Simpan Draft</b> untuk kirim ke server.</li>
                            <li><b>Jangan buka outlet/tanggal/shift yang sama di 2 tab atau 2 device</b>, karena data terakhir bisa saling menimpa.</li>
                            <li><b>Ending Stock hasil hitung fisik/manual.</b> Angka ending menjadi opening shift/tanggal berikutnya.</li>
                            <li><b>Crew</b> hanya mengisi kolom kosong/0. Jika sudah ada nominal, perubahan oleh <b>SPV</b> atau <b>TM Manager</b>.</li>
                            <li>History audit sementara disembunyikan agar halaman input lebih ringan.</li>
                        </ol>
                        <div class="dsc-start-net" style="border:1px solid ${isOnline ? '#bbf7d0' : '#fecaca'};background:${isOnline ? '#ecfdf5' : '#fef2f2'};color:${isOnline ? '#065f46' : '#991b1b'}">
                            ${networkText}
                        </div>
                    </div>
                `,
                confirmButtonText: 'Lanjutkan',
                confirmButtonColor: '#0f172a',
                allowOutsideClick: false,
                allowEscapeKey: false,
                width: 520,
                customClass: {
                    container: 'dsc-start-container',
                    popup: 'dsc-start-popup',
                    title: 'dsc-start-title',
                    htmlContainer: 'dsc-start-html'
                },
                didOpen: () => {
                    document.body.classList.add('dsc-opening-notice-active');
                    $('.wh-footer').attr('aria-hidden', 'true');
                },
                willClose: () => {
                    document.body.classList.remove('dsc-opening-notice-active');
                    $('.wh-footer').removeAttr('aria-hidden');
                }
            });
        }

        function dscBindNetworkAlert() {
            window.addEventListener('offline', function() {
                if (typeof Swal === 'undefined') return;
                Swal.fire({
                    icon: 'warning',
                    title: 'Koneksi Internet Terputus',
                    text: 'Jaringan tidak stabil. Input tetap aman lokal, tetapi kirim ke server hanya saat klik Simpan Draft / Final.',
                    confirmButtonText: 'Oke',
                    confirmButtonColor: '#0f172a'
                });
            });

            window.addEventListener('online', function() {
                if (typeof Swal === 'undefined') return;
                Swal.fire({
                    icon: 'success',
                    title: 'Koneksi Kembali Normal',
                    text: 'Silakan klik Simpan Draft untuk mengirim data lokal ke server.',
                    timer: 2200,
                    timerProgressBar: true,
                    showConfirmButton: false
                });
            });
        }

        // ========= DOC READY =========
        $(document).ready(function() {
            if (IS_SPV_USER) {
                $('body').addClass('can-spv-adjust');
            } else {
                $('body').removeClass('can-spv-adjust');
            }

            // PATCH ROLE VISIBILITY:
            // Tombol Koreksi Final hanya boleh terlihat untuk SPV, TM Manager, dan Superadmin.
            if (!CAN_FINAL_CORRECTION_ROLE) {
                $(BTN.spvAdjust).addClass('d-none').prop('disabled', true);
            }
            dscBindNetworkAlert();
            setTimeout(dscShowOpeningNotice, 450);

            // select2 outlet AJAX: tidak render ribuan option di HTML.
            $('#outlet_id').select2({
                width: '100%',
                placeholder: 'Ketik minimal 2 huruf outlet...',
                allowClear: true,
                minimumInputLength: 2,
                ajax: {
                    url: `{{ route('outlets') }}`,
                    dataType: 'json',
                    delay: 350,
                    data: params => ({ q: params.term || '', page: params.page || 1, limit: 25 }),
                    processResults: (data, params) => {
                        params.page = params.page || 1;
                        const rows = data.results || data.items || [];
                        return {
                            results: rows.map(item => ({
                                ...item,
                                id: item.id,
                                ids: item.ids || item.merged_ids || item.alias_ids || [],
                                text: (item.text || item.nama_outlet || item.label || '').toString()
                                    .replace(/\s*\[ID:\s*[^\]]*\]\s*/gi, '')
                                    .replace(/\s+/g, ' ')
                                    .trim()
                            })),
                            pagination: { more: !!(data.pagination && data.pagination.more) }
                        };
                    },
                    cache: true
                }
            }).on('select2:select', function (e) {
                const data = e.params && e.params.data ? e.params.data : {};
                const ids = data.ids || data.merged_ids || data.alias_ids || [];
                if (Array.isArray(ids) && ids.length) {
                    const option = this.options[this.selectedIndex];
                    if (option) option.dataset.ids = ids.join(',');
                }
            });
            $('#shift').select2({
                width: '100%',
                minimumResultsForSearch: Infinity
            });

            // header changes
            $('#outlet_id,#tanggal,#shift,#nama_petugas').on('focusin', function () {
                $(this).data('prev-value', $(this).val());
            });

            $('#outlet_id,#tanggal,#shift,#nama_petugas').on('change input', function() {
                // FIX MOBILE/OFFLINE:
                // Sebelum header berubah, paksa input bahan aktif masuk local draft.
                // Ini menjaga input terakhir kalau user isi Ayam Kecil lalu langsung pindah Shift.
                if (loaded && items.length) {
                    syncActiveBahanFromDom();
                    saveLocalDraftNow('header_change_before_reload');
                    updateLastSavedBadge('pending');
                }

                resetLoadedStateBecauseHeaderChanged();
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
            $(document).on('click', BTN.load, function() {
                loadData(true);
            });

            // draft (desk + foot + mobile)
            $(document).on('click', BTN.draft, saveDraft);

            // final (desk + foot + mobile)
            $(document).on('click', BTN.final, saveFinal);

            // PATCH SPV: simpan adjustment meskipun status tetap final
            $(document).on('click', BTN.spvAdjust, saveSpvAdjustment);

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
                // FIX: simpan nilai input aktif sebelum card disembunyikan.
                syncActiveBahanFromDom();
                saveLocalDraftNow('mobile_prev');
                scheduleAutoSave();

                setMobileIndex(mobileIndex - 1);
                $('#cards .bcard.active-card input.ending').focus();
            });
            $('#btnMobNext').on('click', function() {
                // FIX: simpan nilai input aktif sebelum card disembunyikan.
                syncActiveBahanFromDom();
                saveLocalDraftNow('mobile_next');
                scheduleAutoSave();

                setMobileIndex(mobileIndex + 1);
                $('#cards .bcard.active-card input.ending').focus();
            });

            $('#mobJump').on('change', function() {
                // FIX: simpan nilai input aktif sebelum lompat bahan.
                syncActiveBahanFromDom();
                saveLocalDraftNow('mobile_jump');
                scheduleAutoSave();

                jumpMobileToBahanId($(this).val());
            });

            // search debounce: jangan filter DOM setiap keypress langsung.
            let searchAnyTimer = null;
            $('#searchAny').on('input', function () {
                clearTimeout(searchAnyTimer);
                searchAnyTimer = setTimeout(applySearch, 300);
            });


            // history modal dimatikan sementara untuk menurunkan CPU/I/O
            bindHistoryButtonClick();


            // Status jaringan khusus outlet dengan sinyal jelek.
            window.addEventListener('offline', function () {
                if (loaded) saveLocalDraftNow('offline_event');
                setStatus('bad', 'Offline - aman di HP');
                updateLastSavedBadge('pending');
                $('#infoText').text('Internet putus. Input tetap disimpan di HP ini dan akan bisa dipulihkan.');
            });

            window.addEventListener('online', function () {
                if (loaded && validateHeader()) {
                    setStatus('loading', 'Online - sinkron...');
                    $('#infoText').text('Internet kembali. Autosave server tetap OFF; klik Simpan Draft untuk kirim ke server.');
                    scheduleAutoSave();
                }
            });

            // initial UI
            if (!AUTO_SAVE_ENABLED) {
                updateLastSavedBadge('pending');
                $('#infoText').text('Autosave server OFF. Input aman lokal; klik Simpan Draft untuk kirim ke server.');
            }
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