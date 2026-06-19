{{-- FIX TEPUNG BREADER FULL BOM PIPELINE --}}
{{-- FIX TEPUNG BREADER TAMPIL DI TABEL QCR --}}
{{-- FIX KOLOM TEPUNG QCR UTAMA --}}
{{-- FIX KOLOM TEPUNG QCR UTAMA V2 SAFE --}}
@section('title', 'Quality Cost Report')
@section('breadcrumb', 'Inventory / QCR')

@include('Temp.Investor.header')

<style>
  :root{
    --qcr-bg:#f7f8fa;
    --qcr-card:#ffffff;
    --qcr-border:#d5dbdb;
    --qcr-border-soft:#e9ebed;
    --qcr-text:#16191f;
    --qcr-muted:#5f6b7a;
    --qcr-primary:#0972d3;
    --qcr-primary-hover:#033160;
    --qcr-success:#037f0c;
    --qcr-warning:#b7791f;
    --qcr-danger:#d13212;
    --qcr-excel:#1d6f42;
    --qcr-radius:8px;
    --qcr-focus:0 0 0 3px rgba(9,114,211,.18);
  }

  .qcr-page{
    display:flex;
    flex-direction:column;
    gap:16px;
  }

  .neg{ color:var(--qcr-danger)!important; font-weight:700; }
  .pos{ color:var(--qcr-success)!important; font-weight:700; }

  .qcr-page-header{
    background:#fff;
    border:1px solid var(--qcr-border);
    border-radius:var(--qcr-radius);
    padding:14px;
    box-shadow:0 1px 2px rgba(15,23,42,.05);
  }

  .qcr-title{
    font-size:1.38rem;
    font-weight:700;
    color:var(--qcr-text);
    margin:0;
    display:flex;
    align-items:center;
    gap:.55rem;
    letter-spacing:-.02em;
  }

  .qcr-title i{
    width:34px;
    height:34px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    border-radius:8px;
    background:#f1f8ff;
    color:var(--qcr-primary);
    font-size:1rem;
  }


  .qcr-title-wrap{
    display:flex;
    align-items:center;
    gap:.6rem;
    flex-wrap:wrap;
  }

  .qcr-notif-btn{
    position:relative;
    width:36px;
    height:36px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    border:1px solid #b6d7f5;
    border-radius:10px;
    background:#f1f8ff;
    color:var(--qcr-primary);
    transition:.15s ease;
  }

  .qcr-notif-btn:hover{
    background:#e6f2ff;
    border-color:var(--qcr-primary);
    color:var(--qcr-primary-hover);
  }

  .qcr-notif-count{
    position:absolute;
    top:-7px;
    right:-7px;
    min-width:20px;
    height:20px;
    padding:0 5px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    border-radius:999px;
    background:var(--qcr-danger);
    color:#fff;
    font-size:.68rem;
    font-weight:800;
    border:2px solid #fff;
    line-height:1;
  }

  .qcr-notif-modal .modal-dialog{
    max-width:760px;
  }

  .qcr-notif-total{
    background:#fff7ed;
    border:1px solid #fdba74;
    color:#9a3412;
    border-radius:10px;
    padding:.75rem .9rem;
    font-weight:800;
  }

  .qcr-info-strip{
    display:flex;
    gap:9px;
    align-items:flex-start;
    margin-top:10px;
    padding:10px 12px;
    border:1px solid #b6d7f5;
    border-radius:var(--qcr-radius);
    background:#f1f8ff;
    color:#033160;
    font-size:.86rem;
    font-weight:600;
  }

  .breadcrumb{ margin:0; }
  .breadcrumb-item a{
    color:var(--qcr-primary);
    text-decoration:none;
    font-weight:600;
  }

  .qcr-filter-card{
    background:#f8f9fa;
    border:1px solid var(--qcr-border-soft);
    border-radius:var(--qcr-radius);
    padding:13px;
  }

  .qcr-filter-card .form-label{
    font-size:.78rem;
    font-weight:700;
    color:var(--qcr-text);
    margin-bottom:.35rem;
  }

  .form-control,
  .form-select{
    border-radius:8px;
    border:1px solid var(--qcr-border);
    min-height:38px;
    box-shadow:none!important;
    font-size:.9rem;
    color:var(--qcr-text);
  }

  .form-control:focus,
  .form-select:focus{
    border-color:var(--qcr-primary);
    box-shadow:var(--qcr-focus)!important;
  }

  .btn{
    border-radius:8px;
    font-weight:700;
    box-shadow:none!important;
  }

  .btn-primary{
    background:var(--qcr-primary);
    border-color:var(--qcr-primary);
  }

  .btn-primary:hover{
    background:var(--qcr-primary-hover);
    border-color:var(--qcr-primary-hover);
  }

  .btn-excel{
    background:var(--qcr-excel);
    border-color:var(--qcr-excel);
    color:#fff;
  }

  .btn-excel:hover{
    background:#175935;
    border-color:#175935;
    color:#fff;
  }

  .btn-uang-plus{
    background:#fff7ed;
    border-color:#fdba74;
    color:#c2410c;
  }

  .btn-uang-plus:hover{
    background:#ffedd5;
    border-color:#fb923c;
    color:#9a3412;
  }

  .qcr-stats{
    display:grid;
    grid-template-columns:repeat(12,minmax(0,1fr));
    gap:12px;
    align-items:start;
  }

  .qcr-stat{
    background:#fff;
    border:1px solid var(--qcr-border);
    border-radius:var(--qcr-radius);
    box-shadow:0 1px 2px rgba(15,23,42,.05);
    padding:14px;
    height:auto;
    min-height:122px;
    display:flex;
    flex-direction:column;
  }

  .qcr-stat--compact{ grid-column:span 3; }
  .qcr-stat--compare{ grid-column:span 6; }

  .qcr-stat-label{
    color:var(--qcr-muted);
    font-size:.78rem;
    font-weight:700;
    margin-bottom:.35rem;
  }

  .qcr-stat-value{
    font-size:1.12rem;
    font-weight:800;
    line-height:1.2;
    color:var(--qcr-text);
    margin-bottom:.5rem;
    word-break:break-word;
  }


  .qcr-summary-compare{
    display:grid;
    grid-template-columns:1fr;
    gap:8px;
    margin-top:6px;
  }

  .qcr-stat--compare .qcr-summary-compare{
    grid-template-columns:repeat(2,minmax(0,1fr));
  }

  .qcr-stat--compare .qcr-summary-compare.qcr-summary-compare-three{
    grid-template-columns:repeat(3,minmax(0,1fr));
  }

  .qcr-summary-line{
    padding:8px 9px;
    border:1px solid var(--qcr-border-soft);
    border-radius:8px;
    background:#fbfbfb;
  }

  .qcr-summary-line.after-hide{
    background:#f1f8ff;
    border-color:#b6d7f5;
  }

  .qcr-summary-line-label{
    color:var(--qcr-muted);
    font-size:.72rem;
    font-weight:800;
    margin-bottom:3px;
    line-height:1.25;
  }

  .qcr-summary-line-value{
    color:var(--qcr-text);
    font-size:.95rem;
    font-weight:800;
    line-height:1.25;
  }

  .qcr-badge{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:.32rem .54rem;
    border-radius:999px;
    font-size:.72rem;
    font-weight:800;
  }

  .qcr-badge.success{ background:#ecfdf3; color:var(--qcr-success); }
  .qcr-badge.warning{ background:#fff7ed; color:var(--qcr-warning); }
  .qcr-badge.info{ background:#f1f8ff; color:var(--qcr-primary); }
  .qcr-badge.danger{ background:#fff1f0; color:var(--qcr-danger); }
  .qcr-badge.secondary{ background:#f2f3f3; color:#414d5c; }
  .qcr-badge.primary{ background:#f1f8ff; color:var(--qcr-primary); }

  .qcr-card{
    background:#fff;
    border:1px solid var(--qcr-border);
    border-radius:var(--qcr-radius);
    box-shadow:0 1px 2px rgba(15,23,42,.05);
    overflow:hidden;
  }

  .qcr-card .card-header{
    background:#fbfbfb;
    border-bottom:1px solid var(--qcr-border);
    padding:13px 14px;
  }

  .qcr-card .card-body{
    padding:14px;
  }

  .qcr-section-title{
    font-size:1rem;
    font-weight:700;
    margin:0;
    color:var(--qcr-text);
  }

  .qcr-toolbar{
    display:flex;
    align-items:center;
    gap:.75rem;
    flex-wrap:wrap;
  }

  .qcr-search{
    width:280px;
    max-width:100%;
  }

  .qcr-actions{
    display:flex;
    align-items:center;
    gap:.5rem;
    flex-wrap:wrap;
  }

  .qcr-table-wrap{
    overflow:auto;
    -webkit-overflow-scrolling:touch;
  }

  .qcr-table{
    margin:0!important;
    width:100%;
    color:var(--qcr-text);
    vertical-align:middle;
    font-size:.86rem;
  }

  .qcr-table thead th{
    position:relative;
    background:#f8f9fa!important;
    color:#414d5c;
    font-size:.73rem;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:.045em;
    border-bottom:1px solid var(--qcr-border)!important;
    border-color:var(--qcr-border)!important;
    padding:.74rem .7rem;
    white-space:nowrap;
  }

  .qcr-table tbody td,
  .qcr-table tbody th{
    border-color:var(--qcr-border-soft)!important;
    padding:.72rem .7rem;
    font-size:.84rem;
    white-space:nowrap;
  }

  .qcr-table tbody tr:hover{
    background:#f2f8fd;
  }

  .qcr-highlight{
    background-color:#fff7ed!important;
  }

  .qcr-sticky-col{
    position:sticky;
    left:0;
    background:#fff!important;
    z-index:4;
  }

  .qcr-sticky-head{
    position:sticky!important;
    left:0;
    background:#f8f9fa!important;
    z-index:6!important;
  }

  .qcr-warning-row th,
  .qcr-warning-row td{
    background:#fff8db!important;
  }

  .qcr-range-badge{
    display:inline-flex;
    align-items:center;
    gap:.5rem;
    flex-wrap:wrap;
    padding:.52rem .75rem;
    background:#f1f8ff;
    color:var(--qcr-primary);
    border-radius:999px;
    font-size:.76rem;
    font-weight:800;
    line-height:1.35;
    text-align:left;
  }

  .dataTables_wrapper .dataTables_scroll div.dataTables_scrollBody{
    border-bottom:none;
  }

  table.dataTable > thead > tr > th,
  table.dataTable > thead > tr > td,
  table.dataTable > tbody > tr > th,
  table.dataTable > tbody > tr > td{
    border-bottom-color:var(--qcr-border-soft)!important;
  }

  .select2-container{ width:100%!important; }

  .select2-container--default .select2-selection--single{
    border:1px solid var(--qcr-border)!important;
    border-radius:8px!important;
    min-height:38px;
    display:flex!important;
    align-items:center;
    padding:0 .35rem;
  }

  .select2-container .select2-selection--single .select2-selection__rendered{
    line-height:36px!important;
    padding-left:.45rem!important;
    font-weight:600;
  }

  .select2-container .select2-selection--single .select2-selection__arrow{
    height:36px!important;
  }

  .uplus-modal .modal-dialog,
  .qcr-hide-modal .modal-dialog{
    max-width:1100px;
  }

  .uplus-modal .modal-content,
  .qcr-hide-modal .modal-content{
    border:1px solid var(--qcr-border);
    border-radius:10px;
    overflow:hidden;
    box-shadow:0 24px 64px rgba(15,23,42,.18);
  }

  .uplus-modal .modal-header,
  .qcr-hide-modal .modal-header{
    background:#fbfbfb;
    border-bottom:1px solid var(--qcr-border);
    padding:1rem 1.1rem;
  }

  .uplus-modal .modal-title,
  .qcr-hide-modal .modal-title{
    font-size:1.05rem;
    font-weight:700;
    color:var(--qcr-text);
  }

  .uplus-modal .modal-body,
  .qcr-hide-modal .modal-body{
    background:#f8f9fa;
    padding:1rem;
    max-height:75vh;
    overflow:auto;
  }

  .uplus-card,
  .qcr-hide-card{
    background:#fff;
    border:1px solid var(--qcr-border);
    border-radius:var(--qcr-radius);
    box-shadow:0 1px 2px rgba(15,23,42,.05);
    padding:1rem;
    height:100%;
  }

  .uplus-label,
  .qcr-hide-label{
    font-size:.78rem;
    font-weight:800;
    color:var(--qcr-muted);
    margin-bottom:.35rem;
  }

  .uplus-balance{
    font-size:1.55rem;
    font-weight:800;
    color:var(--qcr-text);
    line-height:1.2;
  }

  .qcr-hide-value{
    font-size:1.25rem;
    font-weight:800;
    color:var(--qcr-text);
    line-height:1.2;
  }

  .uplus-badge,
  .qcr-hide-badge{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:.36rem .62rem;
    border-radius:999px;
    font-size:.76rem;
    font-weight:800;
  }

  .uplus-badge.ok,
  .qcr-hide-badge.visible{
    background:#ecfdf3;
    color:var(--qcr-success);
  }

  .uplus-badge.warn,
  .qcr-hide-badge.hidden{
    background:#fff7ed;
    color:var(--qcr-warning);
  }

  .uplus-summary-list{
    display:grid;
    gap:.65rem;
  }

  .uplus-summary-item{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:1rem;
    font-size:.9rem;
  }

  .uplus-summary-item strong{
    color:var(--qcr-text);
  }

  .uplus-table-wrap,
  .qcr-hide-table-wrap{
    overflow:auto;
    max-height:420px;
    border:1px solid var(--qcr-border);
    border-radius:var(--qcr-radius);
  }

  .uplus-table,
  .qcr-hide-table{
    margin:0!important;
    width:100%;
    min-width:720px;
  }

  .qcr-hide-table{
    min-width:900px;
  }

  .uplus-table thead th,
  .qcr-hide-table thead th{
    background:#f8f9fa!important;
    position:sticky;
    top:0;
    z-index:2;
    font-size:.76rem;
    font-weight:800;
    color:#414d5c;
    white-space:nowrap;
    text-transform:uppercase;
    letter-spacing:.04em;
  }

  .uplus-empty,
  .qcr-hide-empty{
    padding:1rem;
    text-align:center;
    color:var(--qcr-muted);
    font-size:.9rem;
    font-weight:600;
  }


  @media (max-width:1199.98px){
    .qcr-stat--compact,
    .qcr-stat--compare{ grid-column:span 6; }
  }

  @media (max-width:991.98px){
    .qcr-title{ font-size:1.12rem; }
    .qcr-toolbar{ align-items:stretch; }
    .qcr-search{ width:100%; }
    .qcr-range-badge{ width:100%; border-radius:8px; }
    .qcr-actions{ width:100%; }
    .qcr-actions .btn{ flex:1 1 auto; }
    .uplus-modal .modal-dialog,
    .qcr-hide-modal .modal-dialog{
      max-width:95vw;
      margin:.75rem auto;
    }
    .uplus-balance{ font-size:1.32rem; }
  }

  @media (max-width:575.98px){
    .qcr-stat{ padding:.9rem; }
    .qcr-stat-value{ font-size:1rem; }
    .qcr-card .card-header,
    .qcr-card .card-body{ padding:.9rem; }
    .qcr-table thead th,
    .qcr-table tbody td,
    .qcr-table tbody th{
      padding:.65rem .6rem;
      font-size:.78rem;
    }
  }

  /* Mobile QCR Fix */
  @media (max-width: 767.98px){
    .qcr-page{
      gap:12px;
    }

    .qcr-page-header{
      padding:12px;
    }

    .qcr-page-header .row{
      --bs-gutter-y: .75rem;
    }

    .qcr-title{
      font-size:1.02rem;
      align-items:flex-start;
    }

    .qcr-title i{
      width:30px;
      height:30px;
      min-width:30px;
      border-radius:8px;
    }

    .qcr-info-strip{
      padding:9px 10px;
      font-size:.78rem;
      line-height:1.45;
    }

    .qcr-stats{
      grid-template-columns:1fr;
      gap:10px;
    }

    .qcr-stat,
    .qcr-stat--compact,
    .qcr-stat--compare{
      grid-column:1 / -1;
    }

    .qcr-stat{
      padding:12px;
      min-height:auto;
    }

    .qcr-stat--compare .qcr-summary-compare{
      grid-template-columns:1fr;
    }

    .qcr-card .card-header{
      padding:12px;
    }

    .qcr-toolbar{
      display:grid;
      grid-template-columns:1fr;
      gap:9px;
      width:100%;
    }

    .qcr-section-title{
      font-size:.95rem;
    }

    .qcr-actions{
      display:grid;
      grid-template-columns:1fr 1fr;
      gap:8px;
      width:100%;
    }

    .qcr-actions .qcr-search{
      grid-column:1 / -1;
      width:100%;
      order:-1;
    }

    .qcr-actions .btn-excel{
      grid-column:1 / -1;
      width:100%;
    }

    .qcr-actions .btn-uang-plus{
      grid-column:1 / -1;
      width:100%;
    }

    .qcr-actions .btn-outline-warning,
    .qcr-actions .btn-outline-danger{
      width:100%;
      min-height:38px;
      display:inline-flex;
      align-items:center;
      justify-content:center;
    }

    .qcr-table-wrap{
      border-top:1px solid var(--qcr-border-soft);
      overflow-x:auto;
      overflow-y:hidden;
      position:relative;
      width:100%;
      -webkit-overflow-scrolling:touch;
    }

    #laporanTable{
      min-width:980px!important;
      width:980px!important;
      font-size:.76rem;
    }

    #laporanTable thead th,
    #laporanTable tbody td{
      padding:.56rem .52rem;
      font-size:.72rem;
      white-space:nowrap;
    }

    #laporanTable th:nth-child(1),
    #laporanTable td:nth-child(1){
      width:48px!important;
      min-width:48px!important;
      max-width:48px!important;
      text-align:center;
    }

    #laporanTable th:nth-child(2),
    #laporanTable td:nth-child(2){
      width:160px!important;
      min-width:160px!important;
      max-width:160px!important;
      white-space:normal!important;
      line-height:1.25;
    }

    #laporanTable th:nth-child(3),
    #laporanTable td:nth-child(3){
      width:110px!important;
      min-width:110px!important;
      max-width:110px!important;
      text-align:center;
    }

    .qcr-badge{
      padding:.24rem .42rem;
      font-size:.64rem;
      max-width:100%;
      white-space:nowrap;
    }

    .dataTables_scrollHead,
    .dataTables_scrollHeadInner,
    .dataTables_scrollBody{
      width:100%!important;
    }

    .dataTables_scrollBody{
      max-height:none!important;
      height:auto!important;
      overflow:auto!important;
      -webkit-overflow-scrolling:touch;
    }

    .DTFC_LeftWrapper,
    .DTFC_RightWrapper,
    .dtfc-fixed-left,
    .dtfc-fixed-right{
      display:none!important;
    }

    table.dataTable thead .sorting:before,
    table.dataTable thead .sorting:after,
    table.dataTable thead .sorting_asc:before,
    table.dataTable thead .sorting_asc:after,
    table.dataTable thead .sorting_desc:before,
    table.dataTable thead .sorting_desc:after{
      display:none!important;
    }

    .qcr-sticky-col,
    .qcr-sticky-head{
      position:static!important;
      left:auto!important;
      z-index:auto!important;
    }

    .qcr-range-badge{
      display:flex;
      align-items:flex-start;
      border-radius:8px;
      font-size:.72rem;
      gap:.35rem;
    }

    .uplus-modal .modal-body,
    .qcr-hide-modal .modal-body{
      max-height:78vh;
      padding:.75rem;
    }

    .uplus-card,
    .qcr-hide-card{
      padding:.8rem;
    }
  }

  @media (max-width: 420px){
    #page-content{
      padding:10px!important;
    }

    .qcr-card{
      border-radius:8px;
    }

    .qcr-actions{
      grid-template-columns:1fr 1fr;
    }

    .qcr-actions .btn{
      font-size:.78rem;
      padding:.42rem .55rem;
    }

    #laporanTable{
      min-width:900px!important;
      width:900px!important;
    }

    #laporanTable th:nth-child(2),
    #laporanTable td:nth-child(2){
      width:145px!important;
      min-width:145px!important;
      max-width:145px!important;
    }
  }


  /* QCR Non-BOM / Operasional */
  .qcr-nonbom-summary{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(160px,1fr));
    gap:10px;
    margin-bottom:12px;
  }

  .qcr-nonbom-mini{
    border:1px solid var(--qcr-border-soft);
    border-radius:8px;
    background:#fbfbfb;
    padding:10px 12px;
  }

  .qcr-nonbom-mini-label{
    font-size:.72rem;
    font-weight:800;
    color:var(--qcr-muted);
    text-transform:uppercase;
    letter-spacing:.04em;
  }

  .qcr-nonbom-mini-value{
    font-size:1rem;
    font-weight:800;
    color:var(--qcr-text);
    margin-top:3px;
  }

  .qcr-status-pill{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-width:74px;
    padding:.28rem .52rem;
    border-radius:999px;
    font-size:.7rem;
    font-weight:800;
    text-transform:uppercase;
  }

  .qcr-status-pill.minus{ background:#fff1f0; color:var(--qcr-danger); }
  .qcr-status-pill.plus{ background:#fff7ed; color:var(--qcr-warning); }
  .qcr-status-pill.normal{ background:#ecfdf3; color:var(--qcr-success); }
  .qcr-status-pill.opsional{ background:#f1f8ff; color:var(--qcr-primary); }

  .qcr-status-pill.company{ background:#fff7ed; color:var(--qcr-warning); }
  .qcr-status-pill.employee{ background:#fff1f0; color:var(--qcr-danger); }

  /* Non-BOM desktop: satu wrapper scroll saja, jangan DataTables scroll */
  .qcr-nonbom-table-desktop{
    display:block;
  }

  .qcr-nonbom-cards-mobile{
    display:none;
  }

  .qcr-nonbom-scroll{
    width:100%;
    max-width:100%;
    overflow:auto!important;
    -webkit-overflow-scrolling:touch;
    max-height:62vh;
    border-top:1px solid var(--qcr-border-soft);
    background:#fff;
  }

  .qcr-nonbom-scroll table{
    min-width:1380px!important;
    width:1380px!important;
    table-layout:fixed;
    border-collapse:separate;
    border-spacing:0;
  }

  #nonBomTable thead th{
    position:sticky!important;
    top:0;
    z-index:5;
    background:#f8f9fa!important;
  }

  #nonBomTable th:nth-child(1),
  #nonBomTable td:nth-child(1){ width:62px!important; }

  #nonBomTable th:nth-child(2),
  #nonBomTable td:nth-child(2){
    width:250px!important;
    white-space:normal!important;
    line-height:1.25;
  }

  #nonBomTable th:nth-child(3),
  #nonBomTable td:nth-child(3){ width:90px!important; }

  #nonBomTable th:nth-child(4),
  #nonBomTable td:nth-child(4),
  #nonBomTable th:nth-child(5),
  #nonBomTable td:nth-child(5),
  #nonBomTable th:nth-child(6),
  #nonBomTable td:nth-child(6),
  #nonBomTable th:nth-child(7),
  #nonBomTable td:nth-child(7){ width:118px!important; }

  #nonBomTable th:nth-child(8),
  #nonBomTable td:nth-child(8),
  #nonBomTable th:nth-child(9),
  #nonBomTable td:nth-child(9),
  #nonBomTable th:nth-child(10),
  #nonBomTable td:nth-child(10),
  #nonBomTable th:nth-child(11),
  #nonBomTable td:nth-child(11){ width:125px!important; }

  #nonBomTable th:nth-child(12),
  #nonBomTable td:nth-child(12){ width:135px!important; }

  .qcr-nonbom-mobile-card{
    border:1px solid var(--qcr-border-soft);
    border-radius:12px;
    background:#fff;
    padding:12px;
    box-shadow:0 1px 2px rgba(15,23,42,.04);
  }

  .qcr-nonbom-mobile-card + .qcr-nonbom-mobile-card{
    margin-top:10px;
  }

  .qcr-nonbom-mobile-title{
    font-weight:800;
    color:var(--qcr-text);
    line-height:1.25;
    margin-bottom:4px;
  }

  .qcr-nonbom-mobile-meta{
    color:var(--qcr-muted);
    font-size:.76rem;
    font-weight:700;
    margin-bottom:10px;
  }

  .qcr-nonbom-mobile-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:8px;
  }

  .qcr-nonbom-mobile-kv{
    border:1px solid var(--qcr-border-soft);
    border-radius:10px;
    background:#fbfbfb;
    padding:8px;
  }

  .qcr-nonbom-mobile-label{
    color:var(--qcr-muted);
    font-size:.68rem;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:.03em;
    margin-bottom:3px;
  }

  .qcr-nonbom-mobile-value{
    font-size:.86rem;
    font-weight:800;
    color:var(--qcr-text);
    word-break:break-word;
  }

  @media (max-width: 767.98px){
    .qcr-nonbom-summary{
      grid-template-columns:1fr 1fr;
    }

    .qcr-nonbom-table-desktop{
      display:none!important;
    }

    .qcr-nonbom-cards-mobile{
      display:block!important;
      padding:12px;
      background:#f8f9fa;
    }

    .qcr-nonbom-mobile-grid{
      grid-template-columns:1fr 1fr;
    }
  }

  @media (max-width: 420px){
    .qcr-nonbom-summary{
      grid-template-columns:1fr;
    }

    .qcr-nonbom-mobile-grid{
      grid-template-columns:1fr;
    }
  }


  /* QCR Non-BOM / Operasional - disamakan dengan tabel QCR lain */
  .qcr-card:has(#nonBomTable){
    border:1px solid var(--qcr-border);
    border-radius:var(--qcr-radius);
    box-shadow:0 1px 2px rgba(15,23,42,.05);
    overflow:hidden;
  }

  .qcr-card:has(#nonBomTable) > .card-header{
    background:#fbfbfb;
    border-bottom:1px solid var(--qcr-border);
    padding:13px 14px;
  }

  .qcr-card:has(#nonBomTable) .qcr-section-title{
    font-size:1rem;
    font-weight:700;
    color:var(--qcr-text);
  }

  .qcr-card:has(#nonBomTable) .small.text-muted{
    color:var(--qcr-muted)!important;
  }

  .qcr-card:has(#nonBomTable) .qcr-range-badge{
    border-radius:999px;
    background:#f1f8ff;
    border:0;
    color:var(--qcr-primary);
    padding:.52rem .75rem;
    box-shadow:none;
  }

  .qcr-card:has(#nonBomTable) > .card-body:not(.p-0){
    padding:14px;
    background:#fff;
  }

  .qcr-card:has(#nonBomTable) > .card-body.p-0{
    padding:0!important;
    background:#fff;
  }

  .qcr-nonbom-summary{
    display:grid;
    grid-template-columns:repeat(7,minmax(150px,1fr));
    gap:12px;
    margin-bottom:0;
  }

  .qcr-nonbom-mini{
    border:1px solid var(--qcr-border);
    border-radius:var(--qcr-radius);
    background:#fff;
    padding:12px 14px;
    box-shadow:0 1px 2px rgba(15,23,42,.05);
  }

  .qcr-nonbom-mini:before{
    display:none;
  }

  .qcr-nonbom-mini-label{
    font-size:.72rem;
    font-weight:800;
    color:var(--qcr-muted);
    text-transform:uppercase;
    letter-spacing:.04em;
  }

  .qcr-nonbom-mini-value{
    margin-top:4px;
    font-size:1.08rem;
    font-weight:800;
    line-height:1.15;
    color:var(--qcr-text);
  }

  .qcr-nonbom-table-desktop{
    border-top:1px solid var(--qcr-border-soft);
    border-radius:0;
    overflow:hidden;
    background:#fff;
    box-shadow:none;
  }

  .qcr-nonbom-scroll{
    width:100%;
    max-width:100%;
    max-height:62vh;
    border:0;
    background:#fff;
    overflow:auto!important;
    -webkit-overflow-scrolling:touch;
  }

  .qcr-nonbom-scroll table{
    min-width:1380px!important;
    width:1380px!important;
    table-layout:fixed;
    border-collapse:separate!important;
    border-spacing:0!important;
  }

  #nonBomTable{
    margin:0!important;
    border:0!important;
    font-size:.86rem;
  }

  #nonBomTable thead th{
    position:sticky!important;
    top:0;
    z-index:5;
    background:#f8f9fa!important;
    color:#414d5c;
    border-bottom:1px solid var(--qcr-border)!important;
    border-color:var(--qcr-border)!important;
    padding:.74rem .7rem!important;
    font-size:.73rem;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:.045em;
    white-space:nowrap;
  }

  #nonBomTable tbody td{
    border-color:var(--qcr-border-soft)!important;
    border-bottom:1px solid var(--qcr-border-soft)!important;
    padding:.72rem .7rem!important;
    font-size:.84rem;
    color:var(--qcr-text);
    vertical-align:middle;
    background:#fff;
    white-space:nowrap;
  }

  #nonBomTable tbody tr:hover td{
    background:#f2f8fd!important;
  }

  #nonBomTable th:nth-child(1),
  #nonBomTable td:nth-child(1){
    width:62px!important;
    min-width:62px!important;
    color:var(--qcr-muted);
    font-weight:800;
  }

  #nonBomTable th:nth-child(2),
  #nonBomTable td:nth-child(2){
    position:static!important;
    left:auto!important;
    z-index:auto!important;
    width:250px!important;
    min-width:250px!important;
    max-width:250px!important;
    white-space:normal!important;
    line-height:1.25;
    box-shadow:none!important;
  }

  #nonBomTable tbody td:nth-child(2){
    font-weight:800!important;
  }

  #nonBomTable th:nth-child(3),
  #nonBomTable td:nth-child(3){ width:90px!important; }

  #nonBomTable th:nth-child(4),
  #nonBomTable td:nth-child(4),
  #nonBomTable th:nth-child(5),
  #nonBomTable td:nth-child(5),
  #nonBomTable th:nth-child(6),
  #nonBomTable td:nth-child(6),
  #nonBomTable th:nth-child(7),
  #nonBomTable td:nth-child(7){ width:118px!important; }

  #nonBomTable th:nth-child(8),
  #nonBomTable td:nth-child(8),
  #nonBomTable th:nth-child(9),
  #nonBomTable td:nth-child(9),
  #nonBomTable th:nth-child(10),
  #nonBomTable td:nth-child(10),
  #nonBomTable th:nth-child(11),
  #nonBomTable td:nth-child(11){ width:125px!important; }

  #nonBomTable th:nth-child(12),
  #nonBomTable td:nth-child(12){ width:135px!important; }

  #nonBomTable td:nth-child(4),
  #nonBomTable td:nth-child(5),
  #nonBomTable td:nth-child(6),
  #nonBomTable td:nth-child(7),
  #nonBomTable td:nth-child(8),
  #nonBomTable td:nth-child(9),
  #nonBomTable td:nth-child(10),
  #nonBomTable td:nth-child(11){
    font-variant-numeric:tabular-nums;
  }

  #nonBomTable .text-warning,
  #nonBomTable .text-warning.fw-bold{
    color:var(--qcr-warning)!important;
  }

  #nonBomTable .text-danger,
  #nonBomTable .text-danger.fw-bold{
    color:var(--qcr-danger)!important;
  }

  #nonBomTable .text-muted{
    color:#94a3b8!important;
  }

  .qcr-status-pill{
    min-width:86px;
    padding:.32rem .6rem;
    border-radius:999px;
    font-size:.68rem;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:.03em;
    border:1px solid transparent;
  }

  .qcr-status-pill.plus{
    background:#fff7ed;
    border-color:#fdba74;
    color:var(--qcr-warning);
  }

  .qcr-status-pill.opsional{
    background:#f1f8ff;
    border-color:#b6d7f5;
    color:var(--qcr-primary);
  }

  .qcr-status-pill.minus,
  .qcr-status-pill.employee{
    background:#fff1f0;
    border-color:#fecaca;
    color:var(--qcr-danger);
  }

  .qcr-status-pill.normal{
    background:#ecfdf3;
    border-color:#bbf7d0;
    color:var(--qcr-success);
  }

  @media (max-width:1199.98px){
    .qcr-nonbom-summary{
      grid-template-columns:repeat(3,minmax(0,1fr));
    }
  }

  @media (max-width:767.98px){
    .qcr-card:has(#nonBomTable){
      border-radius:var(--qcr-radius);
    }

    .qcr-card:has(#nonBomTable) > .card-header{
      padding:12px;
    }

    .qcr-card:has(#nonBomTable) > .card-body:not(.p-0){
      padding:12px;
    }

    .qcr-card:has(#nonBomTable) > .card-body.p-0{
      padding:0!important;
    }

    .qcr-nonbom-summary{
      grid-template-columns:1fr 1fr;
      gap:10px;
    }

    .qcr-nonbom-mini{
      padding:11px 12px;
      border-radius:var(--qcr-radius);
    }

    .qcr-nonbom-cards-mobile{
      padding:12px!important;
      background:#f8f9fa;
    }
  }



  /* ==========================================================
     RESPONSIVE POLISH - QCR Summary + Non-BOM
     Tujuan: tampilan flat, serasi dengan tabel lain, tidak patah di desktop/tablet/mobile.
     ========================================================== */
  .qcr-summary-card,
  .qcr-nonbom-card{
    border:1px solid var(--qcr-border)!important;
    border-radius:var(--qcr-radius)!important;
    box-shadow:0 1px 2px rgba(15,23,42,.05)!important;
    overflow:hidden!important;
    background:#fff!important;
  }

  .qcr-summary-card > .card-header,
  .qcr-nonbom-card > .card-header{
    background:#fbfbfb!important;
    border-bottom:1px solid var(--qcr-border)!important;
    padding:13px 14px!important;
  }

  .qcr-summary-card > .card-body,
  .qcr-nonbom-card > .card-body{
    background:#fff!important;
  }

  .qcr-summary-card .qcr-range-badge,
  .qcr-nonbom-card .qcr-range-badge{
    max-width:100%;
    border-radius:999px!important;
    background:#f1f8ff!important;
    border:1px solid #d8eaff!important;
    color:var(--qcr-primary)!important;
    box-shadow:none!important;
  }

  .qcr-summary-card .qcr-table-wrap,
  .qcr-nonbom-scroll{
    width:100%;
    max-width:100%;
    overflow:auto!important;
    -webkit-overflow-scrolling:touch;
    scrollbar-width:thin;
    scrollbar-color:#9aa7b8 #eef2f7;
  }

  .qcr-summary-card .qcr-table-wrap::-webkit-scrollbar,
  .qcr-nonbom-scroll::-webkit-scrollbar{ height:9px; width:9px; }
  .qcr-summary-card .qcr-table-wrap::-webkit-scrollbar-track,
  .qcr-nonbom-scroll::-webkit-scrollbar-track{ background:#eef2f7; }
  .qcr-summary-card .qcr-table-wrap::-webkit-scrollbar-thumb,
  .qcr-nonbom-scroll::-webkit-scrollbar-thumb{ background:#9aa7b8; border-radius:999px; border:2px solid #eef2f7; }

  /* Summary table: compact, readable, first column stays visible */
  #summaryTable{
    width:max-content!important;
    min-width:100%!important;
    table-layout:auto!important;
    border-collapse:separate!important;
    border-spacing:0!important;
  }

  #summaryTable thead th{
    position:sticky!important;
    top:0;
    z-index:5;
    background:#f8f9fa!important;
  }

  #summaryTable th.qcr-sticky-head,
  #summaryTable th.qcr-sticky-col{
    position:sticky!important;
    left:0;
    z-index:8!important;
    width:220px!important;
    min-width:220px!important;
    max-width:220px!important;
    background:#fff!important;
    box-shadow:8px 0 14px -14px rgba(15,23,42,.35);
  }

  #summaryTable thead th.qcr-sticky-head{
    z-index:12!important;
    background:#f8f9fa!important;
  }

  #summaryTable th,
  #summaryTable td{
    padding:.68rem .7rem!important;
    vertical-align:middle!important;
  }

  #summaryTable thead th:not(.qcr-sticky-head){
    min-width:118px;
    max-width:150px;
    white-space:normal!important;
    line-height:1.25;
  }

  #summaryTable td{
    min-width:118px;
    font-variant-numeric:tabular-nums;
  }

  /* Non-BOM summary cards: no more oversized dashboard look */
  .qcr-nonbom-card .card-body:not(.p-0){
    padding:14px!important;
  }

  .qcr-nonbom-summary{
    display:grid!important;
    grid-template-columns:repeat(auto-fit,minmax(170px,1fr))!important;
    gap:12px!important;
    margin:0!important;
  }

  .qcr-nonbom-mini{
    position:relative;
    border:1px solid var(--qcr-border)!important;
    border-radius:var(--qcr-radius)!important;
    background:#fff!important;
    padding:12px 14px!important;
    min-height:66px;
    box-shadow:0 1px 2px rgba(15,23,42,.05)!important;
    overflow:hidden;
  }

  .qcr-nonbom-mini:before{ display:none!important; }

  .qcr-nonbom-mini-label{
    font-size:.72rem!important;
    font-weight:800!important;
    color:var(--qcr-muted)!important;
    text-transform:uppercase!important;
    letter-spacing:.04em!important;
    line-height:1.2;
  }

  .qcr-nonbom-mini-value{
    margin-top:5px!important;
    font-size:1.05rem!important;
    line-height:1.15!important;
    font-weight:800!important;
    color:var(--qcr-text);
  }

  .qcr-nonbom-card > .card-body.p-0,
  .qcr-nonbom-card > .card-body.p-0.pt-0{
    padding:0!important;
  }

  .qcr-nonbom-table-desktop{
    display:block;
    border-top:1px solid var(--qcr-border-soft)!important;
    border-radius:0!important;
    box-shadow:none!important;
    background:#fff!important;
    overflow:hidden!important;
  }

  .qcr-nonbom-scroll{
    max-height:540px!important;
    border:0!important;
    background:#fff!important;
  }

  .qcr-nonbom-scroll table,
  #nonBomTable{
    width:100%!important;
    min-width:1120px!important;
    table-layout:fixed!important;
    border-collapse:separate!important;
    border-spacing:0!important;
    margin:0!important;
  }

  #nonBomTable thead th{
    position:sticky!important;
    top:0!important;
    z-index:5!important;
    background:#f8f9fa!important;
    color:#414d5c!important;
    border:0!important;
    border-bottom:1px solid var(--qcr-border)!important;
    padding:.72rem .65rem!important;
    font-size:.71rem!important;
    font-weight:800!important;
    text-transform:uppercase!important;
    letter-spacing:.04em!important;
    white-space:nowrap!important;
  }

  #nonBomTable tbody td{
    border:0!important;
    border-bottom:1px solid var(--qcr-border-soft)!important;
    padding:.68rem .65rem!important;
    font-size:.82rem!important;
    color:var(--qcr-text)!important;
    vertical-align:middle!important;
    background:#fff!important;
    white-space:nowrap!important;
    font-variant-numeric:tabular-nums;
  }

  #nonBomTable tbody tr:hover td{ background:#f2f8fd!important; }

  #nonBomTable th:nth-child(1), #nonBomTable td:nth-child(1){ width:52px!important; min-width:52px!important; text-align:center; }
  #nonBomTable th:nth-child(2), #nonBomTable td:nth-child(2){
    position:sticky!important;
    left:0!important;
    z-index:4!important;
    width:230px!important;
    min-width:230px!important;
    max-width:230px!important;
    white-space:normal!important;
    line-height:1.25!important;
    background:#fff!important;
    box-shadow:8px 0 14px -14px rgba(15,23,42,.35)!important;
  }
  #nonBomTable thead th:nth-child(2){ z-index:9!important; background:#f8f9fa!important; }
  #nonBomTable tbody td:nth-child(2){ font-weight:800!important; }
  #nonBomTable th:nth-child(3), #nonBomTable td:nth-child(3){ width:78px!important; }
  #nonBomTable th:nth-child(4), #nonBomTable td:nth-child(4),
  #nonBomTable th:nth-child(5), #nonBomTable td:nth-child(5),
  #nonBomTable th:nth-child(6), #nonBomTable td:nth-child(6),
  #nonBomTable th:nth-child(7), #nonBomTable td:nth-child(7){ width:98px!important; }
  #nonBomTable th:nth-child(8), #nonBomTable td:nth-child(8),
  #nonBomTable th:nth-child(9), #nonBomTable td:nth-child(9),
  #nonBomTable th:nth-child(10), #nonBomTable td:nth-child(10),
  #nonBomTable th:nth-child(11), #nonBomTable td:nth-child(11){ width:110px!important; }
  #nonBomTable th:nth-child(12), #nonBomTable td:nth-child(12){ width:118px!important; }

  .qcr-status-pill{
    min-width:82px!important;
    padding:.3rem .55rem!important;
    border-radius:999px!important;
    font-size:.66rem!important;
    line-height:1!important;
    font-weight:800!important;
    letter-spacing:.03em!important;
    text-transform:uppercase!important;
    border:1px solid transparent;
  }

  @media (max-width:1199.98px){
    .qcr-summary-card .card-header .d-flex,
    .qcr-nonbom-card .card-header .d-flex{
      align-items:stretch!important;
    }

    .qcr-summary-card .qcr-range-badge,
    .qcr-nonbom-card .qcr-range-badge{
      width:100%;
      border-radius:8px!important;
      justify-content:flex-start;
    }

    .qcr-nonbom-summary{
      grid-template-columns:repeat(3,minmax(0,1fr))!important;
    }
  }

  @media (max-width:767.98px){
    .qcr-summary-card > .card-header,
    .qcr-nonbom-card > .card-header{
      padding:12px!important;
    }

    .qcr-summary-card .qcr-range-badge,
    .qcr-nonbom-card .qcr-range-badge{
      display:flex!important;
      flex-wrap:wrap!important;
      gap:.35rem!important;
      font-size:.72rem!important;
      line-height:1.35!important;
      padding:.55rem .65rem!important;
    }

    .qcr-nonbom-summary{
      grid-template-columns:1fr 1fr!important;
      gap:10px!important;
    }

    .qcr-nonbom-mini{
      min-height:auto;
      padding:10px 11px!important;
    }

    .qcr-nonbom-mini-label{ font-size:.66rem!important; }
    .qcr-nonbom-mini-value{ font-size:.95rem!important; }

    /* Non-BOM mobile pakai card list, bukan tabel lebar */
    .qcr-nonbom-table-desktop{ display:none!important; }
    .qcr-nonbom-cards-mobile{
      display:block!important;
      padding:12px!important;
      background:#f8f9fa!important;
    }

    .qcr-nonbom-mobile-card{
      border:1px solid var(--qcr-border)!important;
      border-radius:var(--qcr-radius)!important;
      background:#fff!important;
      box-shadow:0 1px 2px rgba(15,23,42,.05)!important;
    }

    #summaryTable{
      min-width:980px!important;
      font-size:.76rem!important;
    }

    #summaryTable th.qcr-sticky-head,
    #summaryTable th.qcr-sticky-col{
      width:175px!important;
      min-width:175px!important;
      max-width:175px!important;
    }

    #summaryTable thead th:not(.qcr-sticky-head),
    #summaryTable td{
      min-width:105px!important;
    }

    #summaryTable th,
    #summaryTable td{
      padding:.58rem .55rem!important;
      font-size:.72rem!important;
    }
  }

  @media (max-width:420px){
    .qcr-nonbom-summary{ grid-template-columns:1fr!important; }
    .qcr-nonbom-mobile-grid{ grid-template-columns:1fr!important; }

    #summaryTable{
      min-width:860px!important;
    }

    #summaryTable th.qcr-sticky-head,
    #summaryTable th.qcr-sticky-col{
      width:155px!important;
      min-width:155px!important;
      max-width:155px!important;
    }
  }



  /* ==========================================================
     MOBILE TABLE MODE - QCR/BOM & NON-BOM
     Request: di HP tetap berbentuk tabel, bukan card.
     Freeze/sticky dimatikan khusus mobile supaya scroll tidak mengganggu.
     ========================================================== */
  @media (max-width: 767.98px){
    .qcr-table-wrap,
    .qcr-nonbom-scroll,
    .uplus-table-wrap,
    .qcr-hide-table-wrap{
      width:100%!important;
      max-width:100%!important;
      overflow-x:auto!important;
      overflow-y:auto!important;
      -webkit-overflow-scrolling:touch!important;
      touch-action:pan-x pan-y!important;
    }

    /* QCR utama tetap tabel compact */
    #laporanTable{
      width:1080px!important;
      min-width:1080px!important;
      table-layout:fixed!important;
      border-collapse:separate!important;
      border-spacing:0!important;
      white-space:nowrap!important;
      margin:0!important;
    }

    #laporanTable thead th,
    #laporanTable tbody td,
    #laporanTable tbody th{
      position:static!important;
      left:auto!important;
      right:auto!important;
      z-index:auto!important;
      box-shadow:none!important;
      padding:.62rem .55rem!important;
      font-size:.72rem!important;
      line-height:1.25!important;
      vertical-align:middle!important;
      white-space:nowrap!important;
      background:inherit;
    }

    #laporanTable thead th{
      background:#f8f9fa!important;
    }

    #laporanTable th:nth-child(1),
    #laporanTable td:nth-child(1){
      width:52px!important;
      min-width:52px!important;
      max-width:52px!important;
      text-align:center!important;
    }

    #laporanTable th:nth-child(2),
    #laporanTable td:nth-child(2){
      width:190px!important;
      min-width:190px!important;
      max-width:190px!important;
      white-space:normal!important;
      word-break:break-word!important;
      font-weight:800!important;
    }

    #laporanTable th:nth-child(3),
    #laporanTable td:nth-child(3){
      width:120px!important;
      min-width:120px!important;
      max-width:120px!important;
      text-align:center!important;
    }

    #laporanTable th:nth-child(4),
    #laporanTable td:nth-child(4),
    #laporanTable th:nth-child(5),
    #laporanTable td:nth-child(5),
    #laporanTable th:nth-child(6),
    #laporanTable td:nth-child(6){
      width:105px!important;
      min-width:105px!important;
      max-width:105px!important;
      text-align:center!important;
    }

    #laporanTable th:nth-child(n+7),
    #laporanTable td:nth-child(n+7){
      width:105px!important;
      min-width:105px!important;
      max-width:105px!important;
      text-align:center!important;
    }

    /* Summary tetap tabel, tapi freeze kiri dimatikan di HP */
    #summaryTable{
      width:1000px!important;
      min-width:1000px!important;
      table-layout:fixed!important;
    }

    #summaryTable th.qcr-sticky-head,
    #summaryTable th.qcr-sticky-col,
    #summaryTable thead th,
    #summaryTable tbody th,
    #summaryTable tbody td{
      position:static!important;
      left:auto!important;
      z-index:auto!important;
      box-shadow:none!important;
    }

    #summaryTable th.qcr-sticky-head,
    #summaryTable th.qcr-sticky-col{
      width:190px!important;
      min-width:190px!important;
      max-width:190px!important;
      background:#f8f9fa!important;
    }

    /* Non-BOM di HP juga tetap tabel, bukan card */
    .qcr-nonbom-table-desktop{
      display:block!important;
    }

    .qcr-nonbom-cards-mobile{
      display:none!important;
    }

    .qcr-nonbom-scroll{
      max-height:none!important;
      border-top:1px solid var(--qcr-border-soft)!important;
    }

    .qcr-nonbom-scroll table,
    #nonBomTable{
      width:1120px!important;
      min-width:1120px!important;
      table-layout:fixed!important;
    }

    #nonBomTable thead th,
    #nonBomTable tbody td,
    #nonBomTable tbody th{
      position:static!important;
      left:auto!important;
      z-index:auto!important;
      box-shadow:none!important;
      padding:.6rem .52rem!important;
      font-size:.72rem!important;
      line-height:1.25!important;
      white-space:nowrap!important;
    }

    #nonBomTable thead th{
      background:#f8f9fa!important;
    }

    #nonBomTable th:nth-child(1),
    #nonBomTable td:nth-child(1){ width:52px!important; min-width:52px!important; max-width:52px!important; }

    #nonBomTable th:nth-child(2),
    #nonBomTable td:nth-child(2){
      width:180px!important;
      min-width:180px!important;
      max-width:180px!important;
      white-space:normal!important;
      word-break:break-word!important;
      font-weight:800!important;
    }

    #nonBomTable th:nth-child(3),
    #nonBomTable td:nth-child(3){ width:78px!important; min-width:78px!important; max-width:78px!important; }

    #nonBomTable th:nth-child(n+4),
    #nonBomTable td:nth-child(n+4){
      width:98px!important;
      min-width:98px!important;
      max-width:98px!important;
      text-align:center!important;
    }

    .dataTables_scrollHead,
    .dataTables_scrollHeadInner,
    .dataTables_scrollBody{
      width:100%!important;
    }

    .dataTables_scrollBody{
      overflow:auto!important;
      max-height:none!important;
      height:auto!important;
    }

    .DTFC_LeftWrapper,
    .DTFC_RightWrapper,
    .dtfc-fixed-left,
    .dtfc-fixed-right{
      display:none!important;
    }
  }

  @media (max-width:420px){
    #laporanTable{ width:1020px!important; min-width:1020px!important; }
    #summaryTable{ width:900px!important; min-width:900px!important; }
    #nonBomTable{ width:1040px!important; min-width:1040px!important; }
  }


  /* ==========================================================
     MOBILE FINAL FIX - tabel tetap mirip desktop + freeze kolom penting
     - Summary: kolom Keterangan freeze kiri
     - Tabel QCR/BOM: kolom No + Nama Menu freeze kiri
     - Header bahan tidak dibuat vertikal; tabel diperlebar agar dibaca dengan scroll horizontal
     ========================================================== */
  @media (max-width: 767.98px){
    .qcr-table-wrap{
      overflow-x:auto!important;
      overflow-y:auto!important;
      max-width:100%!important;
      -webkit-overflow-scrolling:touch!important;
      position:relative!important;
    }

    /* TABEL QCR / BOM */
    #laporanTable{
      width:max-content!important;
      min-width:1320px!important;
      table-layout:fixed!important;
      border-collapse:separate!important;
      border-spacing:0!important;
    }

    #laporanTable thead th,
    #laporanTable tbody td{
      white-space:nowrap!important;
      word-break:normal!important;
      overflow:visible!important;
      line-height:1.25!important;
      vertical-align:middle!important;
    }

    #laporanTable th:nth-child(1),
    #laporanTable td:nth-child(1){
      position:sticky!important;
      left:0!important;
      z-index:20!important;
      width:52px!important;
      min-width:52px!important;
      max-width:52px!important;
      text-align:center!important;
      background:var(--qcr-card,#fff)!important;
    }

    #laporanTable thead th:nth-child(1){
      z-index:35!important;
      background:#f8f9fa!important;
    }

    #laporanTable th:nth-child(2),
    #laporanTable td:nth-child(2){
      position:sticky!important;
      left:52px!important;
      z-index:19!important;
      width:230px!important;
      min-width:230px!important;
      max-width:230px!important;
      white-space:normal!important;
      word-break:break-word!important;
      font-weight:800!important;
      background:var(--qcr-card,#fff)!important;
      box-shadow:10px 0 12px -12px rgba(0,0,0,.55)!important;
    }

    #laporanTable thead th:nth-child(2){
      z-index:34!important;
      background:#f8f9fa!important;
    }

    #laporanTable th:nth-child(3),
    #laporanTable td:nth-child(3){
      width:135px!important;
      min-width:135px!important;
      max-width:135px!important;
      text-align:center!important;
    }

    #laporanTable th:nth-child(4),
    #laporanTable td:nth-child(4),
    #laporanTable th:nth-child(5),
    #laporanTable td:nth-child(5),
    #laporanTable th:nth-child(6),
    #laporanTable td:nth-child(6){
      width:110px!important;
      min-width:110px!important;
      max-width:110px!important;
      text-align:center!important;
    }

    #laporanTable th:nth-child(n+7),
    #laporanTable td:nth-child(n+7){
      width:132px!important;
      min-width:132px!important;
      max-width:132px!important;
      text-align:center!important;
    }

    #laporanTable th:nth-child(n+7){
      white-space:normal!important;
      word-break:normal!important;
      overflow-wrap:anywhere!important;
      line-height:1.2!important;
    }

    /* SUMMARY PEMAKAIAN BAHAN */
    #summaryTable{
      width:max-content!important;
      min-width:1280px!important;
      table-layout:fixed!important;
      border-collapse:separate!important;
      border-spacing:0!important;
    }

    #summaryTable thead th,
    #summaryTable tbody td{
      width:138px!important;
      min-width:138px!important;
      max-width:138px!important;
      white-space:normal!important;
      word-break:normal!important;
      overflow-wrap:anywhere!important;
      line-height:1.22!important;
      text-align:center!important;
    }

    #summaryTable th.qcr-sticky-head,
    #summaryTable th.qcr-sticky-col,
    #summaryTable tbody th:first-child,
    #summaryTable thead th:first-child{
      position:sticky!important;
      left:0!important;
      z-index:25!important;
      width:185px!important;
      min-width:185px!important;
      max-width:185px!important;
      text-align:left!important;
      white-space:normal!important;
      word-break:break-word!important;
      background:var(--qcr-card,#fff)!important;
      box-shadow:10px 0 12px -12px rgba(0,0,0,.55)!important;
    }

    #summaryTable thead th:first-child,
    #summaryTable th.qcr-sticky-head{
      z-index:40!important;
      background:#f8f9fa!important;
    }

    #summaryTable thead th:not(:first-child){
      font-size:.68rem!important;
      min-height:72px!important;
    }

    /* Non-BOM tetap tabel, tapi kolom nama bahan juga freeze supaya sama feel-nya */
    #nonBomTable th:nth-child(1),
    #nonBomTable td:nth-child(1){
      position:sticky!important;
      left:0!important;
      z-index:20!important;
      background:var(--qcr-card,#fff)!important;
    }

    #nonBomTable thead th:nth-child(1){
      z-index:35!important;
      background:#f8f9fa!important;
    }

    #nonBomTable th:nth-child(2),
    #nonBomTable td:nth-child(2){
      position:sticky!important;
      left:52px!important;
      z-index:19!important;
      background:var(--qcr-card,#fff)!important;
      box-shadow:10px 0 12px -12px rgba(0,0,0,.55)!important;
    }

    #nonBomTable thead th:nth-child(2){
      z-index:34!important;
      background:#f8f9fa!important;
    }
  }

  @media (max-width:420px){
    #laporanTable{ min-width:1280px!important; }
    #summaryTable{ min-width:1220px!important; }
  }



  /* ==========================================================
     FINAL MOBILE PATCH V2
     - Selisih Persediaan & Quality Cost tidak dipaksa 3 kolom di HP kecil
     - Tabel QCR tetap tabel, tetapi freeze hanya Nama Menu agar tidak menutup kolom lain
     - Summary tetap tabel dan kolom Keterangan freeze dengan lebar lebih proporsional
     ========================================================== */
  @media (max-width: 767.98px){
    .qcr-stat--compare{
      overflow:hidden!important;
    }

    .qcr-stat--compare .qcr-summary-compare,
    .qcr-stat--compare .qcr-summary-compare.qcr-summary-compare-three{
      grid-template-columns:1fr!important;
      gap:10px!important;
    }

    .qcr-summary-line{
      min-width:0!important;
      padding:10px 11px!important;
    }

    .qcr-summary-line-label{
      font-size:.72rem!important;
      line-height:1.25!important;
      word-break:normal!important;
      overflow-wrap:anywhere!important;
    }

    .qcr-summary-line-value{
      font-size:1rem!important;
      line-height:1.25!important;
      word-break:break-word!important;
    }

    .qcr-stat--compare .qcr-badge{
      width:auto!important;
      max-width:100%!important;
      white-space:normal!important;
    }

    .qcr-table-wrap,
    .dataTables_scrollBody{
      overflow-x:auto!important;
      overflow-y:auto!important;
      -webkit-overflow-scrolling:touch!important;
      overscroll-behavior-x:contain!important;
    }

    #laporanTable{
      width:max-content!important;
      min-width:1260px!important;
      table-layout:fixed!important;
      border-collapse:separate!important;
      border-spacing:0!important;
    }

    /* Reset freeze lama supaya No tidak ikut menutup ruang layar */
    #laporanTable th:nth-child(1),
    #laporanTable td:nth-child(1){
      position:static!important;
      left:auto!important;
      z-index:auto!important;
      width:48px!important;
      min-width:48px!important;
      max-width:48px!important;
      background:inherit!important;
      box-shadow:none!important;
      text-align:center!important;
    }

    /* Freeze hanya Nama Menu */
    #laporanTable th:nth-child(2),
    #laporanTable td:nth-child(2){
      position:sticky!important;
      left:0!important;
      z-index:28!important;
      width:168px!important;
      min-width:168px!important;
      max-width:168px!important;
      white-space:normal!important;
      word-break:break-word!important;
      overflow:hidden!important;
      background:var(--qcr-card,#fff)!important;
      box-shadow:8px 0 10px -10px rgba(0,0,0,.65)!important;
    }

    #laporanTable thead th:nth-child(2){
      z-index:42!important;
      background:#f8f9fa!important;
    }

    #laporanTable th:nth-child(3),
    #laporanTable td:nth-child(3){
      width:118px!important;
      min-width:118px!important;
      max-width:118px!important;
    }

    #laporanTable th:nth-child(4),
    #laporanTable td:nth-child(4),
    #laporanTable th:nth-child(5),
    #laporanTable td:nth-child(5),
    #laporanTable th:nth-child(6),
    #laporanTable td:nth-child(6){
      width:102px!important;
      min-width:102px!important;
      max-width:102px!important;
    }

    #laporanTable th:nth-child(n+7),
    #laporanTable td:nth-child(n+7){
      width:124px!important;
      min-width:124px!important;
      max-width:124px!important;
    }

    /* Summary: header bahan lebih lebar supaya tidak pecah huruf per huruf */
    #summaryTable{
      width:max-content!important;
      min-width:1180px!important;
      table-layout:fixed!important;
      border-collapse:separate!important;
      border-spacing:0!important;
    }

    #summaryTable thead th:not(:first-child),
    #summaryTable tbody td{
      width:132px!important;
      min-width:132px!important;
      max-width:132px!important;
      white-space:normal!important;
      word-break:normal!important;
      overflow-wrap:break-word!important;
      line-height:1.22!important;
      text-align:center!important;
    }

    #summaryTable th.qcr-sticky-head,
    #summaryTable th.qcr-sticky-col,
    #summaryTable tbody th:first-child,
    #summaryTable thead th:first-child{
      position:sticky!important;
      left:0!important;
      z-index:30!important;
      width:155px!important;
      min-width:155px!important;
      max-width:155px!important;
      white-space:normal!important;
      word-break:break-word!important;
      text-align:left!important;
      background:var(--qcr-card,#fff)!important;
      box-shadow:8px 0 10px -10px rgba(0,0,0,.65)!important;
    }

    #summaryTable thead th:first-child,
    #summaryTable th.qcr-sticky-head{
      z-index:45!important;
      background:#f8f9fa!important;
    }
  }

  @media (min-width: 421px) and (max-width: 767.98px){
    .qcr-stat--compare .qcr-summary-compare.qcr-summary-compare-three{
      grid-template-columns:1fr 1fr!important;
    }

    .qcr-stat--compare .qcr-summary-compare.qcr-summary-compare-three .qcr-summary-line:first-child{
      grid-column:1 / -1!important;
    }
  }

  @media (max-width: 420px){
    #laporanTable{ min-width:1180px!important; }
    #summaryTable{ min-width:1080px!important; }

    #laporanTable th:nth-child(2),
    #laporanTable td:nth-child(2){
      width:150px!important;
      min-width:150px!important;
      max-width:150px!important;
    }

    #summaryTable th.qcr-sticky-head,
    #summaryTable th.qcr-sticky-col,
    #summaryTable tbody th:first-child,
    #summaryTable thead th:first-child{
      width:140px!important;
      min-width:140px!important;
      max-width:140px!important;
    }
  }



  /* ==========================================================
     FINAL DESKTOP STATS FULL WIDTH PATCH
     Tujuan:
     - Baris pertama tetap 4 kartu: Total Sales, HPP, Gross Profit, Waste
     - Card compare seperti Selisih Persediaan / Quality Cost dibuat full width
     - Menghilangkan space kosong kanan pada desktop
     - Aman untuk mobile karena aturan mobile lama tetap override layout kecil
     ========================================================== */
  @media (min-width: 1200px){
    .qcr-stats{
      grid-template-columns: repeat(12, minmax(0, 1fr)) !important;
      align-items: stretch !important;
    }

    .qcr-stat{
      min-height: auto !important;
    }

    .qcr-stat--compact{
      grid-column: span 3 !important;
      min-height: 122px !important;
    }

    .qcr-stat--compare{
      grid-column: 1 / -1 !important;
      min-height: auto !important;
    }

    .qcr-stat--compare .qcr-summary-compare{
      width: 100% !important;
    }

    .qcr-stat--compare .qcr-summary-compare.qcr-summary-compare-three{
      grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    }

    .qcr-stat--compare .qcr-summary-line{
      min-height: 84px !important;
      display: flex !important;
      flex-direction: column !important;
      justify-content: center !important;
    }
  }

  @media (min-width: 768px) and (max-width: 1199.98px){
    .qcr-stat--compact{
      grid-column: span 6 !important;
    }

    .qcr-stat--compare{
      grid-column: 1 / -1 !important;
      min-height: auto !important;
    }

    .qcr-stat--compare .qcr-summary-compare.qcr-summary-compare-three{
      grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    }
  }

</style>

<div class="qcr-page">

      {{-- Header --}}
      <div class="qcr-page-header">
        <div class="row g-3 align-items-center">
          <div class="col-12 col-xl-5">
            <div class="d-flex flex-column gap-2">
              <div class="qcr-title-wrap">
                <h3 class="qcr-title">
                  <i class="bi bi-graph-up-arrow"></i>
                  <span>Quality Cost Report</span>
                </h3>

                <button
                  type="button"
                  class="qcr-notif-btn"
                  data-bs-toggle="modal"
                  data-bs-target="#qcrMinusNotifModal"
                  title="Notif bahan minus"
                  aria-label="Notif bahan minus"
                >
                  <i class="bi bi-bell-fill"></i>
                  @if (($qcrMinusNotifCount ?? 0) > 0)
                    <span class="qcr-notif-count">{{ $qcrMinusNotifCount }}</span>
                  @endif
                </button>
              </div>
              <div class="qcr-info-strip">
                <i class="bi bi-info-circle"></i>
                <div>
                  Harga <strong>AYAM BESAR</strong>, <strong>AYAM KECIL</strong>,
                  <strong>AYAM UTUH</strong>, dan <strong>BERAS</strong>
                  bersifat <strong>per outlet</strong>. Pilih outlet terlebih dahulu untuk melihat dan mengubah harga.
                </div>
              </div>

              <nav>
                <ol class="breadcrumb small text-muted">
                  <li class="breadcrumb-item">
                    <a href="#">
                      <i class="bi bi-house-door me-1"></i>Dashboard
                    </a>
                  </li>
                  <li class="breadcrumb-item active">QCR</li>
                </ol>
              </nav>
            </div>
          </div>

          <div class="col-12 col-xl-7">
            <form method="GET" action="{{ route('master.qcr.index') }}">
              <div class="qcr-filter-card">
                <div class="row g-3 align-items-end">
                  <div class="col-12 col-md-6 col-xl-4">
                    <label for="outletInput" class="form-label">
                      <i class="bi bi-shop me-1 text-primary"></i> Outlet
                    </label>
                      <select name="outlet_id" id="outletInput" class="form-select select2" required>
                          <option value="" {{ empty($outletId) ? 'selected' : '' }} disabled>
                              Wajib pilih outlet dulu
                          </option>

                          @foreach ($outletGroups as $groupKey => $group)
                              <option value="{{ $groupKey }}" {{ $outletId == $groupKey ? 'selected' : '' }}>
                                  {{ $group['label'] }}
                              </option>
                          @endforeach
                      </select>
                  </div>

                  <div class="col-6 col-md-3 col-xl-3">
                    <label for="startDateInput" class="form-label">
                      <i class="bi bi-calendar-date me-1 text-primary"></i> Start
                    </label>
                    <input type="date" name="start_date" id="startDateInput" class="form-control" value="{{ $start_date }}" required>
                  </div>

                  <div class="col-6 col-md-3 col-xl-2">
                    <label for="endDateInput" class="form-label">
                      <i class="bi bi-calendar-date me-1 text-primary"></i> End
                    </label>
                    <input type="date" name="end_date" id="endDateInput" class="form-control" value="{{ $end_date }}" required>
                  </div>

                  <div class="col-6 col-md-3 col-xl-2">
                    <label for="shiftFilterInput" class="form-label">
                      <i class="bi bi-clock-history me-1 text-primary"></i> Shift
                    </label>
                    <select name="shift_filter" id="shiftFilterInput" class="form-select">
                      <option value="all" {{ ($shiftFilter ?? 'all') === 'all' ? 'selected' : '' }}>Semua Shift</option>
                      <option value="1" {{ ($shiftFilter ?? 'all') === '1' ? 'selected' : '' }}>Shift 1 (05:00 - 12:59)</option>
                      <option value="2" {{ ($shiftFilter ?? 'all') === '2' ? 'selected' : '' }}>Shift 2 (13:00 - Tutup)</option>
                    </select>
                  </div>

                  <div class="col-12 col-xl-2 d-grid">
                    <button class="btn btn-primary">
                      <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>

      {{-- Ringkasan --}}
      <div class="qcr-stats mb-4">
        <div class="qcr-stat qcr-stat--compact">
          <div class="qcr-stat-label">Total Sales</div>
          <div class="qcr-stat-value">Rp {{ number_format($summary['sales'], 0, ',', '.') }}</div>
          <span class="qcr-badge success">100%</span>
        </div>

        <div class="qcr-stat qcr-stat--compact">
          <div class="qcr-stat-label">HPP</div>
          <div class="qcr-stat-value">Rp {{ number_format($summary['hpp'], 0, ',', '.') }}</div>
          <span class="qcr-badge warning">{{ $summary['hpp_percent'] }}%</span>
        </div>

        <div class="qcr-stat qcr-stat--compact">
          <div class="qcr-stat-label">Gross Profit</div>
          <div class="qcr-stat-value">Rp {{ number_format($summary['profit'], 0, ',', '.') }}</div>
          <span class="qcr-badge info">{{ $summary['profit_percent'] }}%</span>
        </div>

        <div class="qcr-stat qcr-stat--compact">
          <div class="qcr-stat-label">Waste</div>
          <div class="qcr-stat-value">Rp {{ number_format($summary['waste'], 0, ',', '.') }}</div>
          <span class="qcr-badge danger">{{ $summary['waste_percent'] }}%</span>
        </div>

        <div class="qcr-stat qcr-stat--compare">
          <div class="qcr-stat-label">Selisih Persediaan (Loss)</div>

          @php
            $qcrInitialMinusAfter = collect($qcrMinusItems ?? [])->sum('nominal_raw');
            $qcrInitialPlusAfter = collect($qcrPlusItems ?? [])->sum('nominal_raw');
            $qcrInitialMinusAfterPercent = (($summaryNormal['sales'] ?? ($summary['sales'] ?? 0)) > 0)
                ? ($qcrInitialMinusAfter / ($summaryNormal['sales'] ?? ($summary['sales'] ?? 1)) * 100)
                : 0;
            $qcrInitialPlusAfterPercent = (($summaryNormal['sales'] ?? ($summary['sales'] ?? 0)) > 0)
                ? ($qcrInitialPlusAfter / ($summaryNormal['sales'] ?? ($summary['sales'] ?? 1)) * 100)
                : 0;
          @endphp

          <div class="qcr-summary-compare qcr-summary-compare-three">
            <div class="qcr-summary-line">
              <div class="qcr-summary-line-label">Data Normal</div>
              <div class="qcr-summary-line-value">
                Rp {{ number_format($summaryNormal['selisih_loss'] ?? 0, 0, ',', '.') }}
              </div>
              <span class="qcr-badge secondary mt-1">
                {{ $summaryNormal['selisih_percent'] ?? 0 }}%
              </span>
            </div>

            <div class="qcr-summary-line after-hide">
              <div class="qcr-summary-line-label">Bahan Baku Minus</div>
              <div class="qcr-summary-line-value neg" id="qcrAfterMinusSelisihValue">
                -Rp {{ number_format($qcrInitialMinusAfter, 0, ',', '.') }}
              </div>
              <span class="qcr-badge danger mt-1" id="qcrAfterMinusSelisihPercent">
                -{{ number_format($qcrInitialMinusAfterPercent, 1, ',', '.') }}%
              </span>
            </div>

            <div class="qcr-summary-line after-hide">
              <div class="qcr-summary-line-label">Bahan Baku Plus</div>
              <div class="qcr-summary-line-value pos" id="qcrAfterPlusSelisihValue">
                Rp {{ number_format($qcrInitialPlusAfter, 0, ',', '.') }}
              </div>
              <span class="qcr-badge success mt-1" id="qcrAfterPlusSelisihPercent">
                {{ number_format($qcrInitialPlusAfterPercent, 1, ',', '.') }}%
              </span>
            </div>
          </div>
        </div>

        {{-- Quality Cost after-minus/after-plus sementara di-hide karena rumus belum final. --}}
        <div class="qcr-stat qcr-stat--compare d-none">

          <div class="qcr-stat-label">Quality Cost</div>

          @php
            $qcrInitialWaste = $summaryNormal['waste'] ?? ($summary['waste'] ?? 0);
            $qcrInitialMinusQuality = $qcrInitialWaste + $qcrInitialMinusAfter;
            $qcrInitialPlusQuality = $qcrInitialWaste + $qcrInitialPlusAfter;
            $qcrInitialMinusQualityPercent = (($summaryNormal['sales'] ?? ($summary['sales'] ?? 0)) > 0)
                ? ($qcrInitialMinusQuality / ($summaryNormal['sales'] ?? ($summary['sales'] ?? 1)) * 100)
                : 0;
            $qcrInitialPlusQualityPercent = (($summaryNormal['sales'] ?? ($summary['sales'] ?? 0)) > 0)
                ? ($qcrInitialPlusQuality / ($summaryNormal['sales'] ?? ($summary['sales'] ?? 1)) * 100)
                : 0;
          @endphp

          <div class="qcr-summary-compare qcr-summary-compare-three">
            <div class="qcr-summary-line">
              <div class="qcr-summary-line-label">Data Normal</div>
              <div class="qcr-summary-line-value">
                Rp {{ number_format($summaryNormal['quality_cost'] ?? 0, 0, ',', '.') }}
              </div>
              <span class="qcr-badge secondary mt-1">
                {{ $summaryNormal['quality_cost_percent'] ?? 0 }}%
              </span>
            </div>

            <div class="qcr-summary-line after-hide">
              <div class="qcr-summary-line-label">Quality Cost Setelah Hapus Minus</div>
              <div class="qcr-summary-line-value" id="qcrAfterMinusQualityCostValue">
                Rp {{ number_format($qcrInitialMinusQuality, 0, ',', '.') }}
              </div>
              <span class="qcr-badge danger mt-1" id="qcrAfterMinusQualityCostPercent">
                {{ number_format($qcrInitialMinusQualityPercent, 1, ',', '.') }}%
              </span>
            </div>

            <div class="qcr-summary-line after-hide">
              <div class="qcr-summary-line-label">Quality Cost Setelah Hapus Plus</div>
              <div class="qcr-summary-line-value" id="qcrAfterPlusQualityCostValue">
                Rp {{ number_format($qcrInitialPlusQuality, 0, ',', '.') }}
              </div>
              <span class="qcr-badge success mt-1" id="qcrAfterPlusQualityCostPercent">
                {{ number_format($qcrInitialPlusQualityPercent, 1, ',', '.') }}%
              </span>
            </div>
          </div>
        </div>
      </div>

      {{-- Tabel QCR --}}
      <div class="card qcr-card mb-4">
        <div class="card-header">
          <div class="qcr-toolbar w-100 justify-content-between">
            <h5 class="qcr-section-title">Tabel QCR</h5>

            <div class="qcr-actions">
              <input type="text" id="customSearch" class="form-control qcr-search" placeholder="Cari data...">

                <button
                    type="button"
                    class="btn btn-excel"
                    data-bs-toggle="modal"
                    data-bs-target="#modalExportExcel"
                    id="btnOpenExportExcel"
                >
                    <i class="bi bi-file-earmark-excel me-1"></i>
                    Export Excel
                </button>
                
                <div class="modal fade" id="modalExportExcel" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    Export Excel QCR
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                
                            <div class="modal-body">
                                <div class="alert alert-info mb-3">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Export dijalankan di background supaya halaman tidak berat saat dipakai banyak user.
                                </div>

                                <div class="small text-muted mb-2">
                                    Filter export mengikuti filter QCR yang sedang dibuka.
                                </div>

                                <div class="border rounded p-3 bg-light">
                                    <div class="d-flex justify-content-between gap-2 flex-wrap mb-2">
                                        <div>
                                            <div class="fw-bold">Status Export</div>
                                            <div class="small text-muted" id="qcrExportStatusText">Belum mulai.</div>
                                        </div>
                                        <span class="badge bg-secondary align-self-start" id="qcrExportBadge">Idle</span>
                                    </div>

                                    <div class="progress" style="height: 10px;">
                                        <div
                                            class="progress-bar"
                                            id="qcrExportProgressBar"
                                            role="progressbar"
                                            style="width: 0%;"
                                            aria-valuenow="0"
                                            aria-valuemin="0"
                                            aria-valuemax="100"
                                        ></div>
                                    </div>

                                    <div class="small text-muted mt-2" id="qcrExportProgressText">0%</div>
                                </div>
                            </div>
                
                            <div class="modal-footer">
                                <button
                                    type="button"
                                    class="btn btn-secondary"
                                    data-bs-dismiss="modal"
                                >
                                    Tutup
                                </button>

                                <a
                                    href="#"
                                    class="btn btn-success d-none"
                                    id="btnDownloadQcrExport"
                                >
                                    <i class="bi bi-download me-1"></i>
                                    Download
                                </a>

                                <button
                                    type="button"
                                    class="btn btn-excel"
                                    id="btnStartQcrExport"
                                >
                                    <i class="bi bi-play-circle me-1"></i>
                                    Mulai Export
                                </button>
            
                            </div>
                
                        </div>
                    </div>
                </div>

              <button type="button" class="btn btn-uang-plus" id="btnManageUangPlus">
                <i class="bi bi-cash-coin me-1"></i> Membelanjakan Uang Plus
              </button>

              <button type="button" class="btn btn-outline-warning" id="btnHapusPlus" title="Hapus data PLUS">
                <i class="bi bi-plus-circle"></i>
              </button>

              <button type="button" class="btn btn-outline-danger" id="btnHapusMinus" title="Hapus data MINUS">
                <i class="bi bi-dash-circle"></i>
              </button>
            </div>
          </div>
        </div>

        @php
          /*
           |--------------------------------------------------------------------------
           | FIX TEPUNG BREADER TAMPIL DI TABEL QCR UTAMA
           |--------------------------------------------------------------------------
           | Penyebab lama:
           | - Kolom QCR utama dibuat ulang dari $bahanPrice.
           | - Di blok lama, bahan dengan nama tepung breader / breader di-return false.
           | - Akibatnya data muncul di Summary, tapi tidak muncul sebagai kolom QCR utama.
           |
           | Fix:
           | - Jangan reject Tepung Breader.
           | - Bahan dianggap tampil jika dipakai di BOM/menuData.
           | - Tepung Breader tetap dipaksa tampil dari bahanPrice atau bahanSummary.
           | - Lookup nama bahan dibuat normalisasi supaya aman dari beda spasi/huruf besar-kecil.
           */

          $normalizeBahanQcrName = function ($name) {
              $name = strtolower(trim((string) $name));
              return preg_replace('/\s+/', ' ', $name);
          };

          $isTepungBreaderQcr = function ($name) use ($normalizeBahanQcrName) {
              $nama = $normalizeBahanQcrName($name);
              return $nama === 'tepung breader' || str_contains($nama, 'breader');
          };

          $findBahanSummaryRow = function ($namaBahan) use ($bahanSummary, $normalizeBahanQcrName) {
              if (isset($bahanSummary[$namaBahan])) {
                  return $bahanSummary[$namaBahan];
              }

              $target = $normalizeBahanQcrName($namaBahan);

              foreach (($bahanSummary ?? []) as $summaryName => $summaryRow) {
                  if ($normalizeBahanQcrName($summaryName) === $target) {
                      return $summaryRow;
                  }
              }

              return [];
          };

          $getMenuBahanValue = function ($bahanMenu, $namaBahan) use ($normalizeBahanQcrName) {
              if (isset($bahanMenu[$namaBahan])) {
                  return (float) $bahanMenu[$namaBahan];
              }

              $target = $normalizeBahanQcrName($namaBahan);

              foreach (($bahanMenu ?? []) as $menuBahanName => $value) {
                  if ($normalizeBahanQcrName($menuBahanName) === $target) {
                      return (float) $value;
                  }
              }

              return 0.0;
          };

          $bahanTerpakai = collect($bahanPrice ?? [])->filter(function ($b) use ($menuData, $bahanSummary, $normalizeBahanQcrName, $isTepungBreaderQcr) {
              $namaBahan = (string) ($b->nama_bahan ?? '');
              $namaNorm = $normalizeBahanQcrName($namaBahan);

              if ($namaNorm === '') {
                  return false;
              }

              // Khusus Tepung Breader: jangan pernah dibuang dari kolom QCR utama.
              if ($isTepungBreaderQcr($namaBahan)) {
                  return true;
              }

              foreach (($menuData ?? []) as $menu) {
                  foreach (($menu['bahan'] ?? []) as $menuBahanName => $qty) {
                      if ($normalizeBahanQcrName($menuBahanName) === $namaNorm && (float) $qty > 0) {
                          return true;
                      }
                  }
              }

              foreach (($bahanSummary ?? []) as $summaryName => $summaryRow) {
                  if ($normalizeBahanQcrName($summaryName) !== $namaNorm) {
                      continue;
                  }

                  $qtyResep = (float) ($summaryRow['qty_resep'] ?? 0);
                  $qtyStock = (float) ($summaryRow['qty_stock'] ?? 0);
                  $hpp = (float) ($summaryRow['hpp'] ?? 0);

                  if ($qtyResep != 0 || $qtyStock != 0 || $hpp != 0) {
                      return true;
                  }
              }

              return false;
          });

          // Fallback dari Summary: jika Tepung Breader ada di Summary tapi tidak ada di bahanPrice,
          // tetap buat object kolom agar muncul di tabel utama.
          foreach (($bahanSummary ?? []) as $summaryName => $summaryRow) {
              if (! $isTepungBreaderQcr($summaryName)) {
                  continue;
              }

              $exists = $bahanTerpakai->contains(function ($b) use ($summaryName, $normalizeBahanQcrName) {
                  return $normalizeBahanQcrName($b->nama_bahan ?? '') === $normalizeBahanQcrName($summaryName);
              });

              if (! $exists) {
                  $bahanTerpakai->push((object) [
                      'nama_bahan'  => $summaryName,
                      'satuan'      => $summaryRow['satuan'] ?? 'gram',
                      'harga_bahan' => $summaryRow['harga_bahan'] ?? ($summaryRow['harga'] ?? 0),
                  ]);
              }
          }

          $bahanTerpakai = $bahanTerpakai
              ->filter(fn ($b) => trim((string) ($b->nama_bahan ?? '')) !== '')
              ->unique(fn ($b) => $normalizeBahanQcrName($b->nama_bahan ?? ''))
              ->values();

          $jumlahKolomLaporan = 6 + $bahanTerpakai->count();
        @endphp

        <div class="card-body p-0">
          <div class="qcr-table-wrap">
            <table id="laporanTable" class="table table-bordered table-hover align-middle qcr-table"
              style="min-width:1400px; white-space:nowrap;">
              <thead>
                <tr>
                  <th style="width:60px;">No</th>
                  <th style="width:260px;">Nama Menu</th>
                  <th style="width:160px;" class="text-center">Tipe</th>
                  <th class="text-center">Unit Sold</th>
                  <th class="text-center">Harga</th>
                  <th class="text-center">Total</th>

                  @foreach ($bahanTerpakai as $b)
                    @php
                      $summaryRowHeader = $findBahanSummaryRow($b->nama_bahan);
                      $usageHeader = $summaryRowHeader['qty_resep'] ?? null;
                      $satHeader = $summaryRowHeader['satuan'] ?? ($b->satuan ?? '-');
                    @endphp
                    <th class="text-center" style="font-weight:600;">
                      <small class="text-muted d-block">
                        {{ $usageHeader !== null ? number_format($usageHeader, 0, ',', '.') : '0' }} ({{ $satHeader }})
                      </small>
                      {{ $b->nama_bahan }}
                    </th>
                  @endforeach
                </tr>
              </thead>

              <tbody>
                @php $no = 1; @endphp
                @forelse ($menuData as $key => $data)
                  @php
                    $namaMenu = $data['nama_menu'] ?? (is_string($key) ? $key : '-');
                    $tipeMenu = $data['tipe'] ?? 'Regular';
                    $unitSold = (float) ($data['unit_sold'] ?? 0);
                    $harga    = (float) ($data['harga'] ?? 0);
                    $total    = (float) ($data['total_sales'] ?? ($unitSold * $harga));
                  @endphp
                  <tr>
                    <td class="text-center">{{ $no++ }}</td>

                    <td class="text-start ps-2 fw-semibold">
                      {{ $namaMenu }}
                    </td>

                    <td class="text-center">
                      <span class="qcr-badge primary">{{ $tipeMenu }}</span>
                    </td>

                    <td class="text-center">
                      {{ number_format($unitSold, 0, ',', '.') }}
                    </td>

                    <td class="text-center">
                      {{ number_format($harga, 0, ',', '.') }}
                    </td>

                    <td class="text-center">
                      {{ number_format($total, 0, ',', '.') }}
                    </td>

                    @foreach ($bahanTerpakai as $b)
                      @php
                        $val = $getMenuBahanValue($data['bahan'] ?? [], $b->nama_bahan);
                        $bg = $val > 0 ? 'qcr-highlight' : '';
                      @endphp
                      <td class="text-center {{ $bg }}">
                        {{ number_format($val, 0, ',', '.') }}
                      </td>
                    @endforeach
                  </tr>
                @empty
                  <tr>
                    <td class="text-center"></td>
                    <td class="text-center text-muted">Data menu belum tersedia</td>
                    <td class="text-center"></td>
                    <td class="text-center"></td>
                    <td class="text-center"></td>
                    <td class="text-center"></td>

                    @foreach ($bahanTerpakai as $b)
                      <td class="text-center"></td>
                    @endforeach
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      {{-- Summary Pemakaian Bahan --}}
      <div class="card qcr-card qcr-summary-card mb-4">
        <div class="card-header">
          <div class="d-flex flex-column flex-xl-row gap-3 justify-content-between align-items-xl-center">
            <h5 class="qcr-section-title mb-0">Summary Pemakaian Bahan</h5>

            @php
              $rangeDaysLocal    = $rangeDays ?? 1;
              $forecastDaysLocal = $forecastDays ?? $rangeDaysLocal;
              $forecastFromLocal = $forecastFrom ?? \Carbon\Carbon::parse($end_date)->addDay();
              $forecastToLocal   = $forecastTo ?? \Carbon\Carbon::parse($end_date)->addDays($forecastDaysLocal);

              $integerUnits = ['pcs','pc','cup','sachet','botol','bottle','pack','pax','box','buah','lembar'];
              $isIntegerUnit = function($satuan) use ($integerUnits) {
                  return in_array(strtolower(trim((string)$satuan)), $integerUnits, true);
              };
            @endphp

            <span class="qcr-range-badge">
              <span>
                Range: {{ \Carbon\Carbon::parse($start_date)->format('d-m-Y') }}
                s/d {{ \Carbon\Carbon::parse($end_date)->format('d-m-Y') }}
                ({{ $rangeDaysLocal }} hari)
              </span>
              <span>•</span>
              <span>
                Prediksi Hari Berikutnya: {{ $forecastDaysLocal }} hari
                ({{ \Carbon\Carbon::parse($forecastFromLocal)->format('d-m-Y') }}
                s/d {{ \Carbon\Carbon::parse($forecastToLocal)->format('d-m-Y') }})
              </span>
            </span>
          </div>
        </div>

        <div class="card-body p-0">
          <div class="qcr-table-wrap">
            @php
              /*
               |--------------------------------------------------------------------------
               | FIX TEPUNG BREADER FULL BOM PIPELINE
               |--------------------------------------------------------------------------
               | $bahanTerpakai sekarang harus sudah membawa Tepung Breader dari BOM.
               | Tambahan fallback dari $bahanPrice dipakai kalau data lama belum memasukkan
               | Tepung ke bahanTerpakai.
               */
              $tepungFallback = collect($bahanPrice ?? [])->filter(function ($b) {
                  $nama = strtolower(trim((string) ($b->nama_bahan ?? '')));
                  return str_contains($nama, 'tepung breader') || str_contains($nama, 'breader');
              });

              $visibleBahan = collect($bahanTerpakai ?? [])
                  ->concat($tepungFallback)
                  ->unique(fn ($b) => strtolower(trim((string) ($b->nama_bahan ?? ''))))
                  ->values();

              $integerUnits = ['pcs','pc','cup','sachet','botol','bottle','pack','pax','box','buah','lembar'];
              $isIntegerUnit = function($satuan) use ($integerUnits) {
                  return in_array(strtolower(trim((string)$satuan)), $integerUnits, true);
              };
            @endphp

            <table id="summaryTable" class="table table-bordered table-hover align-middle qcr-table"
              style="table-layout:auto; min-width:1000px; white-space:nowrap;">
              <thead>
                <tr>
                  <th class="qcr-sticky-head" style="width:220px;">Keterangan</th>
                  @foreach ($visibleBahan as $b)
                    @php
                      $satuanCol = $bahanSummary[$b->nama_bahan]['satuan'] ?? ($b->satuan ?? '-');
                    @endphp
                    <th class="text-center">
                      {{ $b->nama_bahan }}
                      <div class="small text-muted">({{ $satuanCol }})</div>
                    </th>
                  @endforeach
                </tr>
              </thead>

              <tbody>
                <tr>
                  <th class="qcr-sticky-col">Price / Unit</th>
                  @foreach ($visibleBahan as $b)
                    @php
                      // FIX DISPLAY SAJA: harga outlet di QCR jangan dibulatkan 0 desimal.
                      // Contoh 4956.71 harus tampil Rp 4.956,71, bukan Rp 4.957.
                      // Logika perhitungan/HPP/usage tidak diubah.
                      $priceUnit = (float) ($b->harga_bahan ?? 0);
                      $priceUnitText = number_format($priceUnit, 2, ',', '.');

                      if (str_contains($priceUnitText, ',')) {
                          $priceUnitText = rtrim(rtrim($priceUnitText, '0'), ',');
                      }
                    @endphp
                    <td class="text-center">
                      Rp {{ $priceUnitText }}
                    </td>
                  @endforeach
                </tr>

                <tr>
                  <th class="qcr-sticky-col">HPP</th>
                  @foreach ($visibleBahan as $b)
                    <td class="text-center">
                      Rp {{ number_format($bahanSummary[$b->nama_bahan]['hpp'] ?? 0, 0, ',', '.') }}
                    </td>
                  @endforeach
                </tr>

                <tr>
                  <th class="qcr-sticky-col">Stock (Available)</th>
                  @foreach ($visibleBahan as $b)
                    @php
                      $row = $bahanSummary[$b->nama_bahan] ?? [];
                      $satuan = $row['satuan'] ?? ($b->satuan ?? '');
                      $dec = $isIntegerUnit($satuan) ? 0 : 2;
                      $val = (float)($bahanSummary[$b->nama_bahan]['stock'] ?? 0);
                    @endphp
                    <td class="text-center {{ $val < 0 ? 'text-danger fw-bold' : '' }}">
                      {{ number_format($val, $dec, ',', '.') }}
                    </td>
                  @endforeach
                </tr>

                <tr>
                  <th class="qcr-sticky-col">Usage POS</th>
                  @foreach ($visibleBahan as $b)
                    @php
                      $row = $bahanSummary[$b->nama_bahan] ?? [];
                      $satuan = $row['satuan'] ?? ($b->satuan ?? '');
                      $dec = $isIntegerUnit($satuan) ? 0 : 2;
                      $posVal = (float) ($row['qty_resep'] ?? 0);
                    @endphp

                    <td class="text-center">
                      <span class="{{ $posVal < 0 ? 'text-danger fw-bold' : '' }}">
                        {{ number_format($posVal, $dec, ',', '.') }}
                      </span>
                    </td>
                  @endforeach
                </tr>

                <tr>
                  <th class="qcr-sticky-col">Usage DSC</th>
                  @foreach ($visibleBahan as $b)
                    @php
                      $row = $bahanSummary[$b->nama_bahan] ?? [];
                      $satuan = $row['satuan'] ?? ($b->satuan ?? '');
                      $dec = $isIntegerUnit($satuan) ? 0 : 2;
                      $val = $row['qty_stock'] ?? null;
                    @endphp

                    <td class="text-center">
                      @if ($val === null)
                        <span class="text-muted">-</span>
                      @else
                        <span class="{{ (float)$val < 0 ? 'text-danger fw-bold' : '' }}">
                          {{ number_format((float)$val, $dec, ',', '.') }}
                        </span>
                      @endif
                    </td>
                  @endforeach
                </tr>

                <tr>
                  <th class="qcr-sticky-col">Waste Product Qty</th>
                  @foreach ($visibleBahan as $b)
                    @php
                      $row = $bahanSummary[$b->nama_bahan] ?? [];
                      $satuan = $row['satuan'] ?? ($b->satuan ?? '');
                      $dec = $isIntegerUnit($satuan) ? 0 : 2;
                      $val = (float) ($row['waste_product_qty'] ?? 0);
                    @endphp
                    <td class="text-center {{ $val > 0 ? 'text-danger fw-bold' : '' }}">
                      {{ number_format($val, $dec, ',', '.') }}
                    </td>
                  @endforeach
                </tr>

                <tr>
                  <th class="qcr-sticky-col">Waste Bahan Qty</th>
                  @foreach ($visibleBahan as $b)
                    @php
                      $row = $bahanSummary[$b->nama_bahan] ?? [];
                      $satuan = $row['satuan'] ?? ($b->satuan ?? '');
                      $dec = $isIntegerUnit($satuan) ? 0 : 2;
                      $val = (float) ($row['waste_bahan_qty'] ?? 0);
                    @endphp
                    <td class="text-center {{ $val > 0 ? 'text-danger fw-bold' : '' }}">
                      {{ number_format($val, $dec, ',', '.') }}
                    </td>
                  @endforeach
                </tr>
                <tr>
                  <th class="qcr-sticky-col">
                    Waste Tepung Qty
                    <small class="text-muted d-block">Waste khusus tepung</small>
                  </th>
                  @foreach ($visibleBahan as $b)
                    @php
                      $row = $bahanSummary[$b->nama_bahan] ?? [];
                      $satuan = $row['satuan'] ?? ($b->satuan ?? '');
                      $dec = $isIntegerUnit($satuan) ? 0 : 2;
                      $val = (float) ($row['waste_tepung_qty'] ?? 0);
                    @endphp
                    <td class="text-center {{ $val > 0 ? 'text-warning fw-bold' : '' }}">
                      {{ number_format($val, $dec, ',', '.') }}
                    </td>
                  @endforeach
                </tr>

                <tr>
                  <th class="qcr-sticky-col">Waste Rp</th>
                  @foreach ($visibleBahan as $b)
                    @php
                      $row = $bahanSummary[$b->nama_bahan] ?? [];
                      $val = (float) ($row['waste_rp'] ?? 0);
                    @endphp
                    <td class="text-center {{ $val > 0 ? 'text-danger fw-bold' : '' }}">
                      Rp {{ number_format($val, 0, ',', '.') }}
                    </td>
                  @endforeach
                </tr>
                

                <tr>
                  <th class="qcr-sticky-col">Difference (Visible)</th>
                  @foreach ($visibleBahan as $b)
                    @php
                      $row = $bahanSummary[$b->nama_bahan] ?? [];
                      $satuan = $row['satuan'] ?? ($b->satuan ?? '');
                      $dec = $isIntegerUnit($satuan) ? 0 : 2;

                      $rawQty = (float) ($row['diff_raw_qty'] ?? 0);
                      $visibleQty = (float) ($row['diff_visible_qty'] ?? 0);

                      $sign = $rawQty > 0 ? '-' : ($rawQty < 0 ? '+' : '');
                      $colorClass = $rawQty > 0 ? 'text-danger fw-bold'
                                  : ($rawQty < 0 ? 'text-warning fw-bold' : '');
                    @endphp

                    <td class="text-center {{ $colorClass }}">
                      {{ $sign }}{{ number_format($visibleQty, $dec, ',', '.') }}
                    </td>
                  @endforeach
                </tr>

                <tr class="qcr-warning-row">
                  <th class="qcr-sticky-col" style="background:#fff8db!important;">
                    Avg Usage / Hari ({{ $rangeDaysLocal }} hari)
                  </th>
                  @foreach ($visibleBahan as $b)
                    @php
                      $satuan = $bahanSummary[$b->nama_bahan]['satuan'] ?? ($b->satuan ?? '');
                      $avg = (float)($bahanSummary[$b->nama_bahan]['avg_per_day'] ?? 0);
                      $dec = $isIntegerUnit($satuan) ? 0 : 2;
                    @endphp
                    <td class="text-center">
                      {{ number_format($avg, $dec, ',', '.') }}
                    </td>
                  @endforeach
                </tr>

                <tr class="qcr-warning-row">
                  <th class="qcr-sticky-col" style="background:#fff8db!important;">
                    Prediksi Usage {{ $forecastDaysLocal }} Hari (Hari Berikutnya)
                    <small class="text-muted d-block">
                      ({{ \Carbon\Carbon::parse($forecastFromLocal)->format('d-m-Y') }}
                      s/d {{ \Carbon\Carbon::parse($forecastToLocal)->format('d-m-Y') }})
                    </small>
                  </th>
                  @foreach ($visibleBahan as $b)
                    @php
                      $satuan = $bahanSummary[$b->nama_bahan]['satuan'] ?? ($b->satuan ?? '');
                      $dec = $isIntegerUnit($satuan) ? 0 : 2;
                      $fqty = $bahanSummary[$b->nama_bahan]['forecast_qty'] ?? 0;
                    @endphp
                    <td class="text-center">
                      {{ number_format($fqty, $dec, ',', '.') }}
                    </td>
                  @endforeach
                </tr>

                <tr class="qcr-warning-row">
                  <th class="qcr-sticky-col" style="background:#fff8db!important;">
                    Prediksi Stock Setelah {{ $forecastDaysLocal }} Hari
                    <small class="text-muted d-block">(Stock - Prediksi Usage)</small>
                  </th>
                  @foreach ($visibleBahan as $b)
                    @php
                      $satuan = $bahanSummary[$b->nama_bahan]['satuan'] ?? ($b->satuan ?? '');
                      $dec = $isIntegerUnit($satuan) ? 0 : 2;
                      $pstock = (float)($bahanSummary[$b->nama_bahan]['forecast_stock'] ?? 0);
                    @endphp
                    <td class="text-center {{ $pstock < 0 ? 'text-danger fw-bold' : '' }}">
                      {{ number_format($pstock, $dec, ',', '.') }}
                    </td>
                  @endforeach
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>


      {{-- QCR Bahan Non-BOM / Operasional --}}
      @php
        /*
         | Variance Non-BOM / Operasional
         | - Minus 0 sampai 0,5% dari Total Sales = aman / beban perusahaan, disembunyikan dari tabel utama.
         | - Minus di atas 0,5% = beban. Nilai beban dibulatkan ke atas per 1% sales.
         |   Contoh: sales Rp 500.000 dan variance 0,51% => charge 1% x sales = Rp 5.000.
         */
        $totalSalesForVariance = max(0, (float) ($summary['sales'] ?? 0));
        $varianceTolerancePercent = 0.5;

        $nonBomRawRows = collect($bahanNonBomRows ?? []);

        $nonBomRows = $nonBomRawRows->map(function ($row) use ($totalSalesForVariance, $varianceTolerancePercent) {
            $status = (string) ($row['status'] ?? 'Normal');
            $selisihRp = (float) ($row['selisih_rp'] ?? 0);

            $minusLoss = strtolower($status) === 'minus' ? abs($selisihRp) : 0;
            $variancePercent = ($totalSalesForVariance > 0 && $minusLoss > 0)
                ? (($minusLoss / $totalSalesForVariance) * 100)
                : 0;

            $isCompanyBurden = strtolower($status) === 'minus'
                && $variancePercent > 0
                && $variancePercent <= $varianceTolerancePercent;

            $isEmployeeBurden = strtolower($status) === 'minus'
                && $variancePercent > $varianceTolerancePercent;

            $chargePercent = $isEmployeeBurden ? (float) ceil($variancePercent) : 0;
            $chargeRp = $isEmployeeBurden ? (($totalSalesForVariance * $chargePercent) / 100) : 0;

            $row['variance_percent'] = $variancePercent;
            $row['is_company_burden'] = $isCompanyBurden;
            $row['is_employee_burden'] = $isEmployeeBurden;
            $row['charge_percent'] = $chargePercent;
            $row['charge_rp'] = $chargeRp;

            return $row;
        });

        // $nonBomDisplayRows = $nonBomRows->reject(fn ($row) => (bool) ($row['is_company_burden'] ?? false))->values();
        $nonBomDisplayRows = $nonBomRows->values();

        $nonBomMinusCount = $nonBomDisplayRows->where('status', 'Minus')->count();
        $nonBomPlusCount = $nonBomDisplayRows->where('status', 'Plus')->count();
        $nonBomOpsionalCount = $nonBomDisplayRows->where('status', 'Opsional')->count();
        $nonBomCompanyBurdenCount = $nonBomRows->where('is_company_burden', true)->count();
        $nonBomEmployeeBurdenCount = $nonBomRows->where('is_employee_burden', true)->count();
        $nonBomTotalLoss = (float) $nonBomRows->where('is_employee_burden', true)->sum('charge_rp');
      @endphp

      <div class="card qcr-card qcr-nonbom-card mb-4">
        <div class="card-header">
          <div class="d-flex flex-column flex-xl-row gap-2 justify-content-between align-items-xl-center">
            <div>
              <h5 class="qcr-section-title mb-1">
                <i class="bi bi-box-seam me-1 text-primary"></i>
                QCR Bahan Non-BOM / Operasional
              </h5>
              <div class="small text-muted fw-semibold">
                Bahan yang tidak masuk resep menu/BOM, tetapi tetap dimonitor dari stock DSC.
              </div>
            </div>

            <span class="qcr-range-badge">
              <span>{{ number_format($nonBomDisplayRows->count(), 0, ',', '.') }} tampil / {{ number_format($nonBomRows->count(), 0, ',', '.') }} bahan</span>
              <span>•</span>
              <span>Beban : Rp {{ number_format($nonBomTotalLoss, 0, ',', '.') }}</span>
            </span>
          </div>
        </div>

        <div class="card-body">
          <div class="qcr-nonbom-summary">
            <div class="qcr-nonbom-mini">
              <div class="qcr-nonbom-mini-label">Total Bahan</div>
              <div class="qcr-nonbom-mini-value">{{ number_format($nonBomRows->count(), 0, ',', '.') }}</div>
            </div>
            <div class="qcr-nonbom-mini">
              <div class="qcr-nonbom-mini-label">Minus</div>
              <div class="qcr-nonbom-mini-value text-danger">{{ number_format($nonBomMinusCount, 0, ',', '.') }}</div>
            </div>
            <div class="qcr-nonbom-mini">
              <div class="qcr-nonbom-mini-label">Plus</div>
              <div class="qcr-nonbom-mini-value text-warning">{{ number_format($nonBomPlusCount, 0, ',', '.') }}</div>
            </div>
            <div class="qcr-nonbom-mini">
              <div class="qcr-nonbom-mini-label">Opsional</div>
              <div class="qcr-nonbom-mini-value text-primary">{{ number_format($nonBomOpsionalCount, 0, ',', '.') }}</div>
            </div>
            <div class="qcr-nonbom-mini">
              <div class="qcr-nonbom-mini-label">Aman ≤ 0,5%</div>
              <div class="qcr-nonbom-mini-value text-warning">{{ number_format($nonBomCompanyBurdenCount, 0, ',', '.') }}</div>
            </div>
            <div class="qcr-nonbom-mini">
              <div class="qcr-nonbom-mini-label">Beban > 0,5%</div>
              <div class="qcr-nonbom-mini-value text-danger">{{ number_format($nonBomEmployeeBurdenCount, 0, ',', '.') }}</div>
            </div>
            <div class="qcr-nonbom-mini">
              <div class="qcr-nonbom-mini-label">Total Beban</div>
              <div class="qcr-nonbom-mini-value text-danger">Rp {{ number_format($nonBomTotalLoss, 0, ',', '.') }}</div>
            </div>
          </div>
        </div>

        <div class="card-body p-0 pt-0">
          <div class="qcr-nonbom-table-desktop">
            <div class="qcr-nonbom-scroll">
            <table id="nonBomTable" class="table table-hover align-middle qcr-table mb-0">
              <thead>
                <tr>
                  <th style="width:60px;" class="text-center">No</th>
                  <th>Nama Bahan</th>
                  <th style="width:90px;" class="text-center">Satuan</th>
                  <th class="text-center">Stock</th>
                  <th class="text-center">Usage DSC</th>
                  <th class="text-center">Waste</th>
                  <th class="text-center">Selisih Qty</th>
                  <th class="text-center">Harga</th>
                  <th class="text-center">Selisih Rp</th>
                  <th class="text-center">Variance %</th>
                  <th class="text-center">Beban</th>
                  <th style="width:120px;" class="text-center">Status</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($nonBomDisplayRows as $idx => $row)
                  @php
                    $status = (string) ($row['status'] ?? 'Normal');
                    $statusClass = strtolower($status);
                    $satuan = (string) ($row['satuan'] ?? '-');
                    $dec = $isIntegerUnit($satuan) ? 0 : 2;
                    $selisihQty = (float) ($row['selisih_qty'] ?? 0);
                    $selisihRp = (float) ($row['selisih_rp'] ?? 0);
                    $harga = (float) ($row['harga'] ?? 0);
                    $wasteQty = (float) ($row['waste_qty'] ?? 0);
                    $variancePercent = (float) ($row['variance_percent'] ?? 0);
                    $isEmployeeBurden = (bool) ($row['is_employee_burden'] ?? false);
                    $chargePercent = (float) ($row['charge_percent'] ?? 0);
                    $chargeRp = (float) ($row['charge_rp'] ?? 0);
                    $burdenClass = $isEmployeeBurden ? 'employee' : (strtolower($status) === 'plus' ? 'plus' : $statusClass);
                  @endphp
                  <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="fw-semibold text-start">{{ $row['nama_bahan'] ?? '-' }}</td>
                    <td class="text-center">{{ $satuan }}</td>
                    <td class="text-center">{{ number_format((float) ($row['stock'] ?? 0), $dec, ',', '.') }}</td>
                    <td class="text-center">{{ number_format((float) ($row['usage_dsc'] ?? 0), $dec, ',', '.') }}</td>
                    <td class="text-center {{ $wasteQty > 0 ? 'text-danger fw-bold' : '' }}">
                      {{ number_format($wasteQty, $dec, ',', '.') }}
                    </td>
                    <td class="text-center {{ $selisihQty > 0 ? 'text-danger fw-bold' : ($selisihQty < 0 ? 'text-warning fw-bold' : '') }}">
                      {{ number_format($selisihQty, $dec, ',', '.') }}
                    </td>
                    <td class="text-center">Rp {{ number_format($harga, 0, ',', '.') }}</td>
                    <td class="text-center {{ $status === 'Minus' ? 'text-danger fw-bold' : ($status === 'Plus' ? 'text-warning fw-bold' : '') }}">
                      {{ $status === 'Minus' ? '-Rp ' : 'Rp ' }}{{ number_format(abs($selisihRp), 0, ',', '.') }}
                    </td>
                    <td class="text-center {{ $isEmployeeBurden ? 'text-danger fw-bold' : '' }}">
                      {{ $isEmployeeBurden ? '-' : '' }}{{ number_format($variancePercent, 2, ',', '.') }}%
                    </td>
                    <td class="text-center {{ $isEmployeeBurden ? 'text-danger fw-bold' : 'text-muted' }}">
                      @if ($isEmployeeBurden)
                        -Rp {{ number_format($chargeRp, 0, ',', '.') }}
                        <small class="d-block text-muted">{{ number_format($chargePercent, 0, ',', '.') }}% sales</small>
                      @else
                        -
                      @endif
                    </td>
                    <td class="text-center">
                      <span class="qcr-status-pill {{ $burdenClass }}">
                        {{ $isEmployeeBurden ? 'Karyawan' : $status }}
                      </span>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td class="text-center text-muted">-</td>
                    <td class="text-center text-muted fw-semibold py-4">
                      Tidak ada bahan Non-BOM / Operasional pada filter ini.
                    </td>
                    <td class="text-center text-muted">-</td>
                    <td class="text-center text-muted">-</td>
                    <td class="text-center text-muted">-</td>
                    <td class="text-center text-muted">-</td>
                    <td class="text-center text-muted">-</td>
                    <td class="text-center text-muted">-</td>
                    <td class="text-center text-muted">-</td>
                    <td class="text-center text-muted">-</td>
                    <td class="text-center text-muted">-</td>
                    <td class="text-center">
                      <span class="qcr-status-pill opsional">Kosong</span>
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
            </div>
          </div>

          <div class="qcr-nonbom-cards-mobile">
            @forelse ($nonBomDisplayRows as $idx => $row)
              @php
                $status = (string) ($row['status'] ?? 'Normal');
                $statusClass = strtolower($status);
                $satuan = (string) ($row['satuan'] ?? '-');
                $dec = $isIntegerUnit($satuan) ? 0 : 2;
                $selisihQty = (float) ($row['selisih_qty'] ?? 0);
                $selisihRp = (float) ($row['selisih_rp'] ?? 0);
                $harga = (float) ($row['harga'] ?? 0);
                $wasteQty = (float) ($row['waste_qty'] ?? 0);
                $variancePercent = (float) ($row['variance_percent'] ?? 0);
                $isEmployeeBurden = (bool) ($row['is_employee_burden'] ?? false);
                $chargePercent = (float) ($row['charge_percent'] ?? 0);
                $chargeRp = (float) ($row['charge_rp'] ?? 0);
                $burdenClass = $isEmployeeBurden ? 'employee' : (strtolower($status) === 'plus' ? 'plus' : $statusClass);
              @endphp
              <div class="qcr-nonbom-mobile-card">
                <div class="d-flex justify-content-between align-items-start gap-2">
                  <div>
                    <div class="qcr-nonbom-mobile-title">{{ $idx + 1 }}. {{ $row['nama_bahan'] ?? '-' }}</div>
                    <div class="qcr-nonbom-mobile-meta">Satuan: {{ $satuan }}</div>
                  </div>
                  <span class="qcr-status-pill {{ $burdenClass }}">
                    {{ $isEmployeeBurden ? 'Karyawan' : $status }}
                  </span>
                </div>

                <div class="qcr-nonbom-mobile-grid">
                  <div class="qcr-nonbom-mobile-kv">
                    <div class="qcr-nonbom-mobile-label">Stock</div>
                    <div class="qcr-nonbom-mobile-value">{{ number_format((float) ($row['stock'] ?? 0), $dec, ',', '.') }}</div>
                  </div>
                  <div class="qcr-nonbom-mobile-kv">
                    <div class="qcr-nonbom-mobile-label">Usage DSC</div>
                    <div class="qcr-nonbom-mobile-value">{{ number_format((float) ($row['usage_dsc'] ?? 0), $dec, ',', '.') }}</div>
                  </div>
                  <div class="qcr-nonbom-mobile-kv">
                    <div class="qcr-nonbom-mobile-label">Waste</div>
                    <div class="qcr-nonbom-mobile-value {{ $wasteQty > 0 ? 'text-danger' : '' }}">{{ number_format($wasteQty, $dec, ',', '.') }}</div>
                  </div>
                  <div class="qcr-nonbom-mobile-kv">
                    <div class="qcr-nonbom-mobile-label">Selisih Qty</div>
                    <div class="qcr-nonbom-mobile-value {{ $selisihQty > 0 ? 'text-danger' : ($selisihQty < 0 ? 'text-warning' : '') }}">{{ number_format($selisihQty, $dec, ',', '.') }}</div>
                  </div>
                  <div class="qcr-nonbom-mobile-kv">
                    <div class="qcr-nonbom-mobile-label">Harga</div>
                    <div class="qcr-nonbom-mobile-value">Rp {{ number_format($harga, 0, ',', '.') }}</div>
                  </div>
                  <div class="qcr-nonbom-mobile-kv">
                    <div class="qcr-nonbom-mobile-label">Selisih Rp</div>
                    <div class="qcr-nonbom-mobile-value {{ $status === 'Minus' ? 'text-danger' : ($status === 'Plus' ? 'text-warning' : '') }}">{{ $status === 'Minus' ? '-Rp ' : 'Rp ' }}{{ number_format(abs($selisihRp), 0, ',', '.') }}</div>
                  </div>
                  <div class="qcr-nonbom-mobile-kv">
                    <div class="qcr-nonbom-mobile-label">Variance</div>
                    <div class="qcr-nonbom-mobile-value {{ $isEmployeeBurden ? 'text-danger' : '' }}">{{ $isEmployeeBurden ? '-' : '' }}{{ number_format($variancePercent, 2, ',', '.') }}%</div>
                  </div>
                  <div class="qcr-nonbom-mobile-kv">
                    <div class="qcr-nonbom-mobile-label">Beban</div>
                    <div class="qcr-nonbom-mobile-value {{ $isEmployeeBurden ? 'text-danger' : 'text-muted' }}">
                      @if ($isEmployeeBurden)
                        -Rp {{ number_format($chargeRp, 0, ',', '.') }}
                        <small class="d-block text-muted">{{ number_format($chargePercent, 0, ',', '.') }}% sales</small>
                      @else
                        -
                      @endif
                    </div>
                  </div>
                </div>
              </div>
            @empty
              <div class="text-center text-muted fw-semibold py-4">
                Tidak ada bahan Non-BOM / Operasional pada filter ini.
              </div>
            @endforelse
          </div>
        </div>
      </div>

      <div class="modal fade uplus-modal" id="uangPlusModal" tabindex="-1" aria-labelledby="uangPlusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
          <div class="modal-content">
            <div class="modal-header">
              <div>
                <h5 class="modal-title" id="uangPlusModalLabel">
                  <i class="bi bi-cash-coin me-2 text-primary"></i>Manage Uang Plus
                </h5>
                <div class="small text-muted">
                  Tukarkan saldo uang plus menjadi menu pembelian manual untuk mengurangi saldo.
                </div>
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
              <div class="row g-3">
                <div class="col-12 col-lg-4">
                  <div class="uplus-card">
                    <div class="uplus-label">Saldo Uang Plus</div>
                    <div class="uplus-balance" id="uplusSaldoText">Rp 0</div>
                    <div class="mt-2">
                      <span class="uplus-badge ok" id="uplusStatusBadge">Saldo tersedia</span>
                    </div>

                    <hr>

                    <div class="uplus-summary-list">
                      <div class="uplus-summary-item">
                        <span>Total penukaran</span>
                        <strong id="uplusDipakaiText">Rp 0</strong>
                      </div>
                      <div class="uplus-summary-item">
                        <span>Sisa saldo</span>
                        <strong id="uplusSisaText">Rp 0</strong>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-12 col-lg-8">
                  <div class="uplus-card">
                    <div class="row g-3">
                      <div class="col-12 col-md-7">
                        <label class="uplus-label">Pilih Menu</label>
                        <select id="uplusMenuSelect" class="form-select">
                          <option value="">-- Pilih menu --</option>
                        </select>
                      </div>

                      <div class="col-6 col-md-2">
                        <label class="uplus-label">Qty</label>
                        <input type="number" min="1" step="1" id="uplusQty" class="form-control" value="1">
                      </div>

                      <div class="col-6 col-md-3">
                        <label class="uplus-label">Harga</label>
                        <input type="text" id="uplusHargaText" class="form-control" value="Rp 0" readonly>
                      </div>

                      <div class="col-12">
                        <div class="d-flex flex-wrap gap-2">
                          <button type="button" class="btn btn-primary" id="btnTambahUangPlusItem">
                            <i class="bi bi-plus-circle me-1"></i>Tambah Item
                          </button>

                          <button type="button" class="btn btn-outline-secondary" id="btnResetUangPlusDraft">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Draft
                          </button>
                        </div>
                      </div>
                    </div>

                    <hr>

                    <div class="uplus-label">Daftar Penukaran</div>
                    <div class="uplus-table-wrap">
                      <table class="table table-bordered table-hover align-middle uplus-table" id="uplusDraftTable">
                        <thead>
                          <tr>
                            <th style="width:60px;">No</th>
                            <th>Nama Menu / Jenis Transaksi</th>
                            <th style="width:120px;">Qty</th>
                            <th style="width:160px;">Harga</th>
                            <th style="width:180px;">Subtotal</th>
                            <th style="width:100px;">Aksi</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td colspan="6" class="uplus-empty">Belum ada item penukaran.</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>

                    <div class="mt-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                      <div class="small text-muted">
                        Item di bawah ini adalah draft penukaran saldo uang plus.
                      </div>

                      <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                          Tutup
                        </button>
                        <button type="button" class="btn btn-success" id="btnSimpanUangPlus">
                          <i class="bi bi-save me-1"></i>Simpan Penukaran
                        </button>
                      </div>
                    </div>
                  </div>
                </div>


              </div>
            </div>
            
            <div class="uplus-card mt-3">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                  <div class="uplus-label mb-0">History Pembelian Uang Plus</div>
                  <div class="small text-muted">Riwayat penukaran uang plus sesuai filter aktif.</div>
                </div>
              </div>
            
              <div class="uplus-table-wrap">
                <table class="table table-bordered table-hover align-middle uplus-table mb-0">
                  <thead>
                    <tr>
                      <th style="width:60px;">No</th>
                      <th>Waktu</th>
                      <th>Outlet</th>
                      <th>Nama Menu</th>
                      <th class="text-end">Saldo Awal</th>
                      <th class="text-end">Belanja</th>
                      <th class="text-end">Sisa</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse (($qcrUangPlusHistory ?? collect()) as $i => $h)
                      @php
                        $tanggalHistory = $h->sesi_tanggal ?? $h->created_at ?? null;
                        $jamHistory = $h->tr_waktu ?? null;
                        $waktuHistory = $tanggalHistory
                            ? \Carbon\Carbon::parse($tanggalHistory)->format('d-m-Y') . ($jamHistory ? ' ' . substr((string) $jamHistory, 0, 5) : '')
                            : '-';
                      @endphp
                      <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>{{ $waktuHistory }}</td>
                        <td>{{ $h->nama_outlet ?? ('Outlet ID '.$h->outlet_id) }}</td>
                        <td>{{ $h->item_nama ?? '-' }}</td>
                        <td class="text-end">Rp {{ number_format($h->saldo_awal ?? 0, 0, ',', '.') }}</td>
                        <td class="text-end fw-bold text-warning">Rp {{ number_format($h->total_belanja ?? 0, 0, ',', '.') }}</td>
                        <td class="text-end fw-bold">Rp {{ number_format($h->saldo_sisa ?? 0, 0, ',', '.') }}</td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="7" class="uplus-empty">
                          Belum ada history pembelian uang plus pada filter ini.
                        </td>
                      </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>


      {{-- Modal Notif Bahan Minus --}}
      <div class="modal fade qcr-notif-modal" id="qcrMinusNotifModal" tabindex="-1" aria-labelledby="qcrMinusNotifModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <div>
                <h5 class="modal-title" id="qcrMinusNotifModalLabel">
                  <i class="bi bi-bell-fill me-2 text-warning"></i>Notif Bahan Minus
                </h5>
                <div class="small text-muted">
                  Warning menampilkan nominal harga, bukan qty. Rumus: qty minus yang masih tampil x harga bahan.
                </div>
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
              <div class="d-flex flex-column gap-3">
                <div class="qcr-notif-total d-flex flex-column flex-md-row justify-content-between gap-2">
                  <div>
                    <div class="small text-uppercase">Total Warning Bahan Minus</div>
                    <div class="fs-5">Rp {{ number_format($qcrMinusNotifTotal ?? 0, 0, ',', '.') }}</div>
                  </div>
                  <div class="text-md-end">
                    <div class="small text-uppercase">Jumlah Item</div>
                    <div class="fs-5">{{ number_format($qcrMinusNotifCount ?? 0, 0, ',', '.') }}</div>
                  </div>
                </div>

                <div class="table-responsive">
                  <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light">
                      <tr>
                        <th style="width:60px;">No</th>
                        <th>Nama Outlet</th>
                        <th>Nama Bahan</th>
                        <th class="text-end">Harga Warning</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse (($qcrMinusNotifItems ?? []) as $i => $item)
                        <tr>
                          <td class="text-center">{{ $i + 1 }}</td>
                          <td class="fw-semibold">{{ $item['outlet_label'] ?? ($selectedOutletLabel ?? '-') }}</td>
                          <td>{{ $item['reference_name'] ?? '-' }}</td>
                          <td class="text-end fw-bold text-danger">
                            Rp {{ number_format($item['nominal_warning'] ?? $item['nominal'] ?? 0, 0, ',', '.') }}
                          </td>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="4" class="text-center text-muted py-4">
                            Tidak ada bahan minus pada filter aktif.
                          </td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>

                <div class="small text-muted">
                  Catatan: nominal ikut berubah jika data minus di modal Hapus - disembunyikan lalu disimpan.
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade qcr-hide-modal" id="qcrHideModal" tabindex="-1" aria-labelledby="qcrHideModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
          <div class="modal-content">
            <div class="modal-header">
              <div>
                <h5 class="modal-title" id="qcrHideModalLabel">
                  <i class="bi bi-eye-slash me-2 text-primary"></i><span id="qcrHideModalTitle">Hapus +</span>
                </h5>
                <div class="small text-muted" id="qcrHideModalSubtitle">
                  Item tidak dihapus permanen. Data normal tetap tersimpan, sedangkan summary setelah dihapus memakai qty yang masih tampil.
                </div>
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
              <div class="d-flex flex-column gap-3">

                {{-- Summary top --}}
                <div class="qcr-hide-card">
                  <div class="row g-3">
                    <div class="col-12 col-md-3">
                      <div class="qcr-hide-label">Jenis Data</div>
                      <div class="qcr-hide-value" id="qcrHideJenisText">PLUS</div>
                    </div>

                    <div class="col-12 col-md-3">
                      <div class="qcr-hide-label">Total Aktif</div>
                      <div class="qcr-hide-value" id="qcrHideTotalVisible">Rp 0</div>
                    </div>

                    <div class="col-12 col-md-3">
                      <div class="qcr-hide-label">Total Hidden</div>
                      <div class="qcr-hide-value" id="qcrHideTotalHidden">Rp 0</div>
                    </div>

                    <div class="col-12 col-md-3">
                      <div class="qcr-hide-label">Jumlah Item</div>
                      <div class="qcr-hide-value" id="qcrHideItemCount">0</div>
                    </div>
                  </div>
                </div>

                {{-- Toolbar --}}
                <div class="qcr-hide-card">
                  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                      <div class="qcr-hide-label mb-1">Daftar Item</div>
                      <div class="small text-muted">
                        Isi Qty Hidden untuk menyembunyikan sebagian qty dari perhitungan.
                      </div>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                      <button type="button" class="btn btn-outline-secondary btn-sm" id="btnHideShowAllVisible">
                        Hide Semua
                      </button>
                      <button type="button" class="btn btn-outline-primary btn-sm" id="btnHideShowAllHidden">
                        Tampilkan Semua
                      </button>
                    </div>
                  </div>
                </div>

                {{-- Table full width --}}
                <div class="qcr-hide-card">
                  <div class="qcr-hide-table-wrap">
                    <table class="table table-bordered table-hover align-middle qcr-hide-table" id="qcrHideTable">
                      <thead>
                        <tr>
                          <th style="width:60px;">No</th>
                          <th>Nama Item</th>
                          <th style="width:120px;">Qty Tampil</th>
                          <th style="width:100px;">Sat</th>
                          <th style="width:160px;">Nominal</th>
                          <th style="width:140px;">Qty Hidden</th>
                          <th style="width:120px;">Status</th>
                          <th style="width:120px;">Aksi</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td colspan="8" class="qcr-hide-empty">Belum ada data.</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                {{-- Footer note + action --}}
                <div class="qcr-hide-card">
                  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="small text-muted">
                      Preview UI saja. Perubahan belum disimpan ke database.
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                      <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Tutup
                      </button>
                      <button type="button" class="btn btn-primary" id="btnSimpanHidePreview">
                        Simpan Perubahan
                      </button>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- Assets --}}
      


</div>

{{-- Datatables --}}
<link rel="stylesheet" href="https://cdn.datatables.net/fixedcolumns/4.3.0/css/fixedColumns.dataTables.min.css">

@push('scripts')
<script>
  window.qcrToast = function(icon, title) {
    if (window.Swal) {
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: icon,
        title: title,
        timer: 1800,
        showConfirmButton: false
      });
    } else {
      alert(title);
    }
  };
</script>

<script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script>
<script>
        $(document).ready(function() {
          const $table = $('#laporanTable');
          const isQcrMobile = window.matchMedia('(max-width: 767.98px)').matches;

          if ($.fn.DataTable.isDataTable('#laporanTable')) {
            $table.DataTable().destroy();
          }

          if (isQcrMobile) {
            // Mobile: tetap tabel biasa, tanpa DataTables scroll/freeze.
            // Ini mencegah header/body pecah dan fixed column menutup isi.
            $('#customSearch').off('keyup.qcrMobile').on('keyup.qcrMobile', function() {
              const q = (this.value || '').toLowerCase();
              $('#laporanTable tbody tr').each(function() {
                const text = ($(this).text() || '').toLowerCase();
                $(this).toggle(text.includes(q));
              });
            });
            return;
          }

          let table = $table.DataTable({
            destroy: true,
            paging: false,
            ordering: true,
            searching: true,
            info: false,
            scrollX: true,
            scrollY: "500px",
            scrollCollapse: true,
            autoWidth: false,
            dom: 'rt',
            fixedColumns: {
              left: 3,
              right: 0
            }
          });

          setTimeout(function(){
            table.columns.adjust().draw(false);
          }, 250);

          $('#customSearch').off('keyup.qcrDesktop').on('keyup.qcrDesktop', function() {
            table.search(this.value).draw();
          });

          let resizeTimer = null;
          $(window).on('resize.qcrTable', function(){
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function(){
              if ($.fn.DataTable.isDataTable('#laporanTable')) {
                table.columns.adjust().draw(false);
              }
            }, 180);
          });
        });

          // Non-BOM tidak pakai DataTables.
          // Desktop dan mobile sama-sama tabel scroll manual agar bentuknya konsisten.
      </script>
<script>
        const containers = document.querySelectorAll('.qcr-table-wrap');
        containers.forEach(container => {
          let isDown = false, startX = 0, scrollLeft = 0;

          container.addEventListener('mousedown', e => {
            isDown = true;
            startX = e.pageX - container.offsetLeft;
            scrollLeft = container.scrollLeft;
          });

          container.addEventListener('mouseleave', () => isDown = false);
          container.addEventListener('mouseup', () => isDown = false);

          container.addEventListener('mousemove', e => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - container.offsetLeft;
            container.scrollLeft = scrollLeft - (x - startX) * 1.5;
          });

          container.addEventListener('touchstart', e => {
            startX = e.touches[0].pageX;
            scrollLeft = container.scrollLeft;
          }, { passive: true });

          container.addEventListener('touchmove', e => {
            const x = e.touches[0].pageX;
            container.scrollLeft = scrollLeft - (x - startX) * 1.5;
          }, { passive: true });
        });
      </script>


<script>
  $(document).ready(function() {
    $('#outletInput').select2({
      placeholder: "Pilih Outlet",
      allowClear: true,
      width: '100%'
    });
  });
</script>
<script>
  /*
   |--------------------------------------------------------------------------
   | DATA MENU UANG PLUS
   |--------------------------------------------------------------------------
   | Source dari controller:
   | $qcrUangPlusMenuOptions
   |
   | Format option:
   | Nama Menu | Harga | Jenis Transaksi
   |--------------------------------------------------------------------------
   */
  window.qcrUangPlusMenuOptions = @json(collect($qcrUangPlusMenuOptions ?? [])->values());
</script>

<script>
  $(document).ready(function() {
    const uangPlusModalEl = document.getElementById('uangPlusModal');
    const uangPlusModal = new bootstrap.Modal(uangPlusModalEl);

    let uplusSaldo = 0;
    let uplusDraftItems = [];

    function formatRupiah(value) {
      value = Number(value || 0);
      return 'Rp ' + value.toLocaleString('id-ID');
    }

    function hitungSaldoAwal() {
      return Number(@json((float) ($totalUangPlus ?? 0)));
    }

    function hitungDipakai() {
      return uplusDraftItems.reduce((sum, item) => sum + Number(item.subtotal || 0), 0);
    }

    function renderSummaryUangPlus() {
      const dipakai = hitungDipakai();
      const sisa = uplusSaldo - dipakai;

      $('#uplusSaldoText').text(formatRupiah(uplusSaldo));
      $('#uplusDipakaiText').text(formatRupiah(dipakai));
      $('#uplusSisaText').text(formatRupiah(sisa));

      if (sisa < 0) {
        $('#uplusStatusBadge')
          .removeClass('ok')
          .addClass('warn')
          .text('Saldo minus');
      } else {
        $('#uplusStatusBadge')
          .removeClass('warn')
          .addClass('ok')
          .text('Saldo tersedia');
      }
    }

    function renderDraftTable() {
      const $tbody = $('#uplusDraftTable tbody');

      if (!uplusDraftItems.length) {
        $tbody.html(`
          <tr>
            <td colspan="6" class="uplus-empty">Belum ada item penukaran.</td>
          </tr>
        `);
        renderSummaryUangPlus();
        return;
      }

      let html = '';
      uplusDraftItems.forEach((item, index) => {
        html += `
          <tr>
            <td class="text-center">${index + 1}</td>
            <td>
              <div class="fw-semibold">${item.nama_menu}</div>
              <div class="small text-muted">${item.tipe}</div>
            </td>
            <td class="text-center">${item.qty}</td>
            <td class="text-end">${formatRupiah(item.harga)}</td>
            <td class="text-end">${formatRupiah(item.subtotal)}</td>
            <td class="text-center">
              <button type="button" class="btn btn-sm btn-outline-danger btn-remove-uplus-item" data-index="${index}">
                <i class="bi bi-trash"></i>
              </button>
            </td>
          </tr>
        `;
      });

      $tbody.html(html);
      renderSummaryUangPlus();
    }

    function isiSelectMenu() {
      const $select = $('#uplusMenuSelect');
      $select.empty().append('<option value="">-- Pilih menu --</option>');

      const menus = Array.isArray(window.qcrUangPlusMenuOptions)
        ? window.qcrUangPlusMenuOptions
        : [];

      menus.forEach(function(menu) {
        const name = menu.nama_menu || '';
        const type = menu.jenis_transaksi || menu.tipe || 'Regular';
        const price = Number(menu.harga || 0);
        const category = menu.kategori || '';
        const key = menu.key || `${name}||${type}||${price}`;

        if (!name) {
          return;
        }

        $select.append(`
          <option
            value="${key}"
            data-name="${name}"
            data-type="${type}"
            data-price="${price}"
            data-category="${category}"
          >
            ${name} | ${formatRupiah(price)} | ${type}
          </option>
        `);
      });
    }

    function resetFormUplus() {
      $('#uplusMenuSelect').val('').trigger('change');
      $('#uplusQty').val(1);
      $('#uplusHargaText').val(formatRupiah(0));
    }

    $('#btnManageUangPlus').on('click', function() {
      const outletAktif = @json($outletId);
      if (!outletAktif || outletAktif === 'all') {
        qcrToast('warning', 'Pilih satu outlet / satu grup outlet dulu. Penukaran uang plus tidak bisa dari mode Semua Outlet.');
        return;
      }

      isiSelectMenu();

      if ($('#uplusMenuSelect').hasClass('select2-hidden-accessible')) {
        $('#uplusMenuSelect').select2('destroy');
      }

      $('#uplusMenuSelect').select2({
        dropdownParent: $('#uangPlusModal'),
        width: '100%',
        placeholder: '-- Pilih menu --'
      });

      uplusSaldo = hitungSaldoAwal();
      uplusDraftItems = [];

      renderDraftTable();
      resetFormUplus();
      uangPlusModal.show();
    });

    $('#uplusMenuSelect').on('change', function() {
      const selected = $(this).find(':selected');
      const price = Number(selected.data('price') || 0);
      $('#uplusHargaText').val(formatRupiah(price));
    });

    $('#btnTambahUangPlusItem').on('click', function() {
      const selected = $('#uplusMenuSelect').find(':selected');
      const namaMenu = selected.data('name') || '';
      const tipe = selected.data('type') || 'Regular';
      const harga = Number(selected.data('price') || 0);
      const qty = Number($('#uplusQty').val() || 0);

      if (!namaMenu) {
        qcrToast('warning', 'Pilih menu dulu.');
        return;
      }

      if (qty <= 0) {
        qcrToast('warning', 'Qty harus lebih dari 0.');
        return;
      }

      if (uplusSaldo <= 0) {
        qcrToast('warning', 'Uang plus anda masih kosong.');
        return;
      }

      const subtotal = harga * qty;
      const totalDraft = hitungDipakai();

      if ((totalDraft + subtotal) > uplusSaldo) {
        qcrToast('warning', 'Uang plus tidak cukup untuk item ini.');
        return;
      }

      uplusDraftItems.push({
        nama_menu: namaMenu,
        tipe: tipe,
        harga: harga,
        qty: qty,
        subtotal: subtotal
      });

      renderDraftTable();
      resetFormUplus();
    });

    $(document).on('click', '.btn-remove-uplus-item', function() {
      const index = Number($(this).data('index'));
      uplusDraftItems.splice(index, 1);
      renderDraftTable();
    });

    $('#btnResetUangPlusDraft').on('click', function() {
      uplusDraftItems = [];
      renderDraftTable();
      resetFormUplus();
    });

    $('#btnSimpanUangPlus').on('click', async function() {
      const $btn = $(this);
      if ($btn.prop('disabled')) return;
      try {
        $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Menyimpan...');
        const totalBelanja = hitungDipakai();

        if (uplusSaldo <= 0) {
          qcrToast('warning', 'Uang plus tidak tersedia.');
          return;
        }

        if (!uplusDraftItems.length) {
          qcrToast('warning', 'Belum ada item penukaran.');
          return;
        }

        if (totalBelanja <= 0) {
          qcrToast('warning', 'Total belanja harus lebih dari 0.');
          return;
        }

        if (totalBelanja > uplusSaldo) {
          qcrToast('warning', 'Uang plus tidak mencukupi.');
          return;
        }

        const payload = {
          outlet_id: @json($outletId),
          start_date: @json($start_date),
          end_date: @json($end_date),
          uang_plus: uplusSaldo,
          idempotency_key: 'UPLUS-' + Date.now() + '-' + Math.random().toString(16).slice(2),
          items: uplusDraftItems.map(item => ({
            nama_menu: item.nama_menu,
            tipe: item.tipe,
            harga: Number(item.harga || 0),
            qty: Number(item.qty || 0)
          }))
        };

        const res = await fetch(@json(route('master.qcr.uangplus.save')), {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': @json(csrf_token()),
            'Accept': 'application/json'
          },
          body: JSON.stringify(payload)
        });

        const json = await res.json().catch(() => ({}));

        if (!res.ok || !json.ok) {
          throw new Error(json.message || 'Gagal menyimpan penukaran uang plus.');
        }

        qcrToast('success', json.message || 'Belanja uang plus berhasil disimpan.');
        location.reload();
      } catch (err) {
        qcrToast('error', err.message || 'Terjadi kesalahan.');
      } finally {
        $btn.prop('disabled', false).html('<i class="bi bi-check2-circle me-1"></i>Simpan Penukaran');
      }
    });
  });
</script>
<script>
  $(document).ready(function() {
    const qcrHideModalEl = document.getElementById('qcrHideModal');
    const qcrHideModal = new bootstrap.Modal(qcrHideModalEl);

    const plusSource = @json($qcrPlusItems);
    const minusSource = @json($qcrMinusItems);

    const qcrBaseSales = Number(@json($summaryNormal['sales'] ?? ($summary['sales'] ?? 0))) || 0;
    const qcrBaseWaste = Number(@json($summaryNormal['waste'] ?? ($summary['waste'] ?? 0))) || 0;

    function getRawNominalTotal(items) {
      return (items || []).reduce((sum, item) => sum + Number(item.nominal_raw || 0), 0);
    }

    function getVisibleNominalTotal(items) {
      return (items || []).reduce((sum, item) => {
        const rawAbs = Number(item.qty_abs || 0);
        const hiddenQty = Math.min(Math.max(Number(item.qty_hidden || 0), 0), rawAbs);
        const pricePerUnit = rawAbs > 0 ? (Number(item.nominal_raw || 0) / rawAbs) : 0;
        return sum + Math.max(0, rawAbs - hiddenQty) * pricePerUnit;
      }, 0);
    }

    const qcrRawMinusTotal = getRawNominalTotal(minusSource);
    const qcrRawPlusTotal = getRawNominalTotal(plusSource);

    function formatPercent(value, useMinus = false) {
      const rounded = Math.round(Number(value || 0) * 10) / 10;
      const text = rounded.toLocaleString('id-ID') + '%';
      return useMinus && rounded > 0 ? '-' + text : text;
    }

    function updateComparisonCardsPreview() {
      const visibleMinus = currentJenis === 'minus' ? getVisibleNominalTotal(workingItems) : getVisibleNominalTotal(minusSource);
      const visiblePlus = currentJenis === 'plus' ? getVisibleNominalTotal(workingItems) : getVisibleNominalTotal(plusSource);

      const minusPercent = qcrBaseSales !== 0 ? ((visibleMinus / qcrBaseSales) * 100) : 0;
      const plusPercent = qcrBaseSales !== 0 ? ((visiblePlus / qcrBaseSales) * 100) : 0;

      const minusQualityCost = qcrBaseWaste + visibleMinus;
      const plusQualityCost = qcrBaseWaste + visiblePlus;
      const minusQualityCostPercent = qcrBaseSales !== 0 ? ((minusQualityCost / qcrBaseSales) * 100) : 0;
      const plusQualityCostPercent = qcrBaseSales !== 0 ? ((plusQualityCost / qcrBaseSales) * 100) : 0;

      $('#qcrAfterMinusSelisihValue').text('-' + formatRupiah(visibleMinus));
      $('#qcrAfterMinusSelisihPercent').text(formatPercent(minusPercent, true));

      $('#qcrAfterPlusSelisihValue').text(formatRupiah(visiblePlus));
      $('#qcrAfterPlusSelisihPercent').text(formatPercent(plusPercent));

      $('#qcrAfterMinusQualityCostValue').text(formatRupiah(minusQualityCost));
      $('#qcrAfterMinusQualityCostPercent').text(formatPercent(minusQualityCostPercent));

      $('#qcrAfterPlusQualityCostValue').text(formatRupiah(plusQualityCost));
      $('#qcrAfterPlusQualityCostPercent').text(formatPercent(plusQualityCostPercent));
    }

    let currentJenis = 'plus';
    let workingItems = [];

    function formatRupiah(value) {
      value = Number(value || 0);
      return 'Rp ' + value.toLocaleString('id-ID');
    }

    function cloneItems(items) {
      return JSON.parse(JSON.stringify(items || []));
    }

    function normalizeItem(item) {
      const rawAbs = Number(item.qty_abs || 0);
      let hiddenQty = Number(item.qty_hidden || 0);

      if (hiddenQty < 0) hiddenQty = 0;
      if (hiddenQty > rawAbs) hiddenQty = rawAbs;

      const pricePerUnit = rawAbs > 0 ? (Number(item.nominal_raw || 0) / rawAbs) : 0;
      const visibleAbs = Math.max(0, rawAbs - hiddenQty);

      item.qty_hidden = hiddenQty;
      item.qty_visible = visibleAbs;
      item.qty_visible_abs = visibleAbs;
      item.nominal = visibleAbs * pricePerUnit;
      item.status = visibleAbs > 0 ? 'visible' : 'hidden';

      return item;
    }

    function normalizeAllItems() {
      workingItems = workingItems.map(item => normalizeItem(item));
    }

    function updateHideSummary() {
      normalizeAllItems();

      let totalVisible = 0;
      let totalHidden = 0;

      workingItems.forEach(item => {
        const rawAbs = Number(item.qty_abs || 0);
        const hiddenQty = Number(item.qty_hidden || 0);
        const visibleAbs = Number(item.qty_visible_abs || item.qty_visible || 0);
        const pricePerUnit = rawAbs > 0 ? (Number(item.nominal_raw || 0) / rawAbs) : 0;

        totalVisible += visibleAbs * pricePerUnit;
        totalHidden += hiddenQty * pricePerUnit;
      });

      $('#qcrHideTotalVisible').text(formatRupiah(totalVisible));
      $('#qcrHideTotalHidden').text(formatRupiah(totalHidden));
      $('#qcrHideItemCount').text(workingItems.length);
      updateComparisonCardsPreview();
    }

    function renderHideTable() {
      normalizeAllItems();

      const $tbody = $('#qcrHideTable tbody');

      if (!workingItems.length) {
        $tbody.html(`
          <tr>
            <td colspan="8" class="qcr-hide-empty">Tidak ada item.</td>
          </tr>
        `);
        updateHideSummary();
        return;
      }

      let html = '';

      workingItems.forEach((item, index) => {
        const badgeClass = item.status === 'hidden' ? 'hidden' : 'visible';
        const badgeText = item.status === 'hidden' ? 'Hidden' : 'Visible';

        html += `
          <tr>
            <td class="text-center">${index + 1}</td>
            <td class="fw-semibold">${item.reference_name}</td>
            <td class="text-end">${Number(item.qty_visible_abs || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 })}</td>
            <td class="text-center">${item.satuan || '-'}</td>
            <td class="text-end">${formatRupiah(item.nominal || 0)}</td>
            <td class="text-center">
              <input
                type="number"
                min="0"
                step="0.01"
                class="form-control form-control-sm qcr-hidden-qty-input"
                data-index="${index}"
                value="${Number(item.qty_hidden || 0)}"
              >
            </td>
            <td class="text-center">
              <span class="qcr-hide-badge ${badgeClass}">${badgeText}</span>
            </td>
            <td class="text-center">
              <button
                type="button"
                class="btn btn-sm btn-outline-secondary btn-reset-hide-item"
                data-index="${index}"
              >
                Reset
              </button>
            </td>
          </tr>
        `;
      });

      $tbody.html(html);
      updateHideSummary();
    }

    function openHideModal(jenis) {
      currentJenis = jenis;
      workingItems = cloneItems(jenis === 'plus' ? plusSource : minusSource);
      normalizeAllItems();

      $('#qcrHideJenisText').text(jenis === 'plus' ? 'PLUS' : 'MINUS');
      $('#qcrHideModalTitle').text(jenis === 'plus' ? 'Hapus +' : 'Hapus -');

      $('#qcrHideModalSubtitle').text(
        jenis === 'plus'
          ? 'Item plus tidak dihapus permanen. Hanya disembunyikan dari perhitungan.'
          : 'Item minus tidak dihapus permanen. Hanya disembunyikan dari perhitungan.'
      );

      renderHideTable();
      qcrHideModal.show();
    }

    $('#btnHapusPlus').off('click.qcrhide').on('click.qcrhide', function() {
      openHideModal('plus');
    });

    $('#btnHapusMinus').off('click.qcrhide').on('click.qcrhide', function() {
      openHideModal('minus');
    });

    $('#btnHideShowAllVisible').off('click.qcrhide').on('click.qcrhide', function() {
      // Hide Semua
      workingItems.forEach(item => {
        item.qty_hidden = Number(item.qty_abs || 0);
        normalizeItem(item);
      });

      renderHideTable();
    });

    $('#btnHideShowAllHidden').off('click.qcrhide').on('click.qcrhide', function() {
      // Tampilkan Semua
      workingItems.forEach(item => {
        item.qty_hidden = 0;
        normalizeItem(item);
      });

      renderHideTable();
    });

    $(document).off('input.qcrhide change.qcrhide', '.qcr-hidden-qty-input')
      .on('input.qcrhide change.qcrhide', '.qcr-hidden-qty-input', function() {
        const index = Number($(this).data('index'));
        if (!workingItems[index]) return;

        workingItems[index].qty_hidden = Number($(this).val() || 0);
        normalizeItem(workingItems[index]);
        renderHideTable();
      });

    $(document).off('click.qcrhide', '.btn-reset-hide-item')
      .on('click.qcrhide', '.btn-reset-hide-item', function() {
        const index = Number($(this).data('index'));
        if (!workingItems[index]) return;

        workingItems[index].qty_hidden = 0;
        normalizeItem(workingItems[index]);
        renderHideTable();
      });

    $('#btnSimpanHidePreview').off('click.qcrhide').on('click.qcrhide', async function() {
      try {
        normalizeAllItems();

        const payload = {
          outlet_key: @json((string) $outletId),
          start_date: @json($start_date),
          end_date: @json($end_date),
          jenis: currentJenis,
          items: workingItems.map(item => ({
            reference_key: item.reference_key,
            reference_name: item.reference_name,
            qty_abs: Number(item.qty_abs || 0),
            qty_hidden: Number(item.qty_hidden || 0),
            satuan: item.satuan || ''
          }))
        };

        const res = await fetch(@json(route('master.qcr.hide.save')), {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': @json(csrf_token()),
            'Accept': 'application/json'
          },
          body: JSON.stringify(payload)
        });

        const json = await res.json().catch(() => ({}));
        if (!res.ok || !json.ok) {
          throw new Error(json.message || 'Gagal menyimpan perubahan.');
        }

        qcrToast('success', json.message || 'Perubahan berhasil disimpan.');
        setTimeout(() => location.reload(), 350);
      } catch (err) {
        qcrToast('error', err.message || 'Terjadi kesalahan.');
      }
    });
  });
</script>

<script>
  /* ==========================================================
     QCR EXPORT QUEUE - ringan untuk banyak user
     - Tidak generate Excel di request halaman.
     - Request hanya membuat job, lalu status dipolling.
     - File baru didownload saat job selesai.
     ========================================================== */
  document.addEventListener('DOMContentLoaded', function () {
    const startBtn = document.getElementById('btnStartQcrExport');
    const downloadBtn = document.getElementById('btnDownloadQcrExport');
    const statusText = document.getElementById('qcrExportStatusText');
    const badge = document.getElementById('qcrExportBadge');
    const progressBar = document.getElementById('qcrExportProgressBar');
    const progressText = document.getElementById('qcrExportProgressText');

    if (!startBtn) return;

    const exportGenerateUrl = @json(route('master.qcr.export.generate'));
    const exportStatusBaseUrl = @json(url('/master/qcr/export/status'));

    let pollingTimer = null;

    function setExportUi(status, message, progress, downloadUrl) {
      const pct = Math.max(0, Math.min(100, Number(progress || 0)));

      if (statusText) statusText.textContent = message || '-';
      if (progressText) progressText.textContent = pct.toFixed(pct % 1 ? 1 : 0) + '%';
      if (progressBar) {
        progressBar.style.width = pct + '%';
        progressBar.setAttribute('aria-valuenow', String(pct));
      }

      if (badge) {
        badge.className = 'badge align-self-start';
        if (status === 'done') badge.classList.add('bg-success');
        else if (status === 'failed') badge.classList.add('bg-danger');
        else if (status === 'processing') badge.classList.add('bg-primary');
        else badge.classList.add('bg-secondary');
        badge.textContent = status || 'Idle';
      }

      if (downloadBtn) {
        if (downloadUrl) {
          downloadBtn.href = downloadUrl;
          downloadBtn.classList.remove('d-none');
        } else {
          downloadBtn.href = '#';
          downloadBtn.classList.add('d-none');
        }
      }
    }

    async function readJson(res) {
      try { return await res.json(); } catch (e) { return {}; }
    }

    async function pollExportStatus(exportId) {
      clearTimeout(pollingTimer);

      try {
        const res = await fetch(exportStatusBaseUrl + '/' + encodeURIComponent(exportId), {
          headers: { 'Accept': 'application/json' }
        });
        const json = await readJson(res);

        if (!res.ok || !json.ok) {
          throw new Error(json.message || 'Gagal membaca status export.');
        }

        const data = json.data || {};
        const status = data.status || 'pending';
        const progress = Number(data.progress || 0);

        if (status === 'done') {
          setExportUi('done', 'Export selesai. Silakan download file.', 100, data.download_url);
          startBtn.disabled = false;
          startBtn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Export Lagi';
          return;
        }

        if (status === 'failed') {
          setExportUi('failed', data.error_message || 'Export gagal.', progress, null);
          startBtn.disabled = false;
          startBtn.innerHTML = '<i class="bi bi-play-circle me-1"></i> Mulai Export';
          return;
        }

        setExportUi(
          status,
          'Export sedang diproses: ' + Number(data.processed_outlet || 0) + ' / ' + Number(data.total_outlet || 0) + ' outlet.',
          progress,
          null
        );

        pollingTimer = setTimeout(function () {
          pollExportStatus(exportId);
        }, 2500);
      } catch (err) {
        setExportUi('failed', err.message || 'Gagal membaca status export.', 0, null);
        startBtn.disabled = false;
      }
    }

    startBtn.addEventListener('click', async function () {
      clearTimeout(pollingTimer);

      const payload = new FormData();
      payload.append('outlet_id', @json((string) $outletId));
      payload.append('start_date', @json((string) $start_date));
      payload.append('end_date', @json((string) $end_date));

      startBtn.disabled = true;
      startBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Membuat job...';
      setExportUi('pending', 'Membuat antrian export...', 0, null);

      try {
        const res = await fetch(exportGenerateUrl, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': @json(csrf_token()),
            'Accept': 'application/json'
          },
          body: payload
        });
        const json = await readJson(res);

        if (!res.ok || !json.ok) {
          throw new Error(json.message || 'Gagal membuat job export.');
        }

        startBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Diproses...';
        setExportUi('processing', json.message || 'Export sedang diproses.', 2, null);
        pollExportStatus(json.export_id);
      } catch (err) {
        setExportUi('failed', err.message || 'Gagal membuat job export.', 0, null);
        startBtn.disabled = false;
        startBtn.innerHTML = '<i class="bi bi-play-circle me-1"></i> Mulai Export';
      }
    });
  });
</script>

@endpush

@include('Temp.Investor.footer')