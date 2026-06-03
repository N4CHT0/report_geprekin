{{-- Tambahkan link ini ke sidebar pada section Telegram & Backup atau Candidate & Assignment --}}

<a href="{{ route('investor.surveyor.field-report.index') }}"
   class="side-link {{ request()->routeIs('investor.surveyor.field-report.*') ? 'active' : '' }}">
    <i class="bi bi-clipboard-data"></i>
    <span>Field Report Draft</span>
</a>
