<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
// use Intervention\Image\Laravel\Facades\Image;
// use Intervention\Image\Facades\Image;
// use Intervention\Image\ImageManager;
// use Intervention\Image\Drivers\Gd\Driver;

class TicketController extends Controller
{
    private array $statuses = ['Open', 'Need Review', 'Confirmed', 'Process', 'Hold', 'Closed', 'Cancel'];

    public function dashboard(Request $request)
    {
        $user = Auth::user();
        abort_if(!$user, 403);

        $total = DB::table('tickets')->count();

        $summary = [
            'total' => $total,
            'open' => DB::table('tickets')->where('status', 'open')->count(),
            'confirmed' => DB::table('tickets')->where('status', 'confirmed')->count(),
            'process' => DB::table('tickets')->where('status', 'process')->count(),
            'hold' => DB::table('tickets')->where('status', 'hold')->count(),
            'closed' => DB::table('tickets')->where('status', 'closed')->count(),
            'cancel' => DB::table('tickets')->where('status', 'cancel')->count(),
            'urgent' => DB::table('tickets')
                ->where('priority', 'urgent')
                ->whereNotIn('status', ['closed', 'cancel'])
                ->count(),
        ];

        $overdue = DB::table('tickets')
            ->whereNotIn('status', ['closed', 'cancel'])
            ->whereNotNull('opened_at')
            ->whereNotNull('sla_hours')
            ->whereRaw('TIMESTAMPDIFF(HOUR, opened_at, NOW()) > sla_hours')
            ->count();

        $onSla = DB::table('tickets')
            ->whereNotIn('status', ['closed', 'cancel'])
            ->whereNotNull('opened_at')
            ->whereNotNull('sla_hours')
            ->whereRaw('TIMESTAMPDIFF(HOUR, opened_at, NOW()) <= sla_hours')
            ->count();

        $closed = $summary['closed'];

        $completionRate = $total > 0
            ? round(($closed / $total) * 100, 1)
            : 0;

        $slaTotal = $onSla + $overdue;

        $onSlaPercent = $slaTotal > 0
            ? round(($onSla / $slaTotal) * 100)
            : 0;

        $overduePercent = $slaTotal > 0
            ? round(($overdue / $slaTotal) * 100)
            : 0;

        $warningPercent = max(0, 100 - $onSlaPercent - $overduePercent);

        $trendRows = DB::table('tickets')
            ->selectRaw('DATE(created_at) as ticket_date, COUNT(*) as total')
            ->whereDate('created_at', '>=', now()->subDays(6)->toDateString())
            ->groupBy('ticket_date')
            ->orderBy('ticket_date')
            ->get()
            ->keyBy('ticket_date');

        $trend = collect(range(6, 0))->map(function ($i) use ($trendRows) {
            $date = now()->subDays($i)->toDateString();

            return [
                'label' => now()->subDays($i)->translatedFormat('D'),
                'date' => $date,
                'total' => (int) ($trendRows[$date]->total ?? 0),
            ];
        })->values();

        $maxTrend = max(1, $trend->max('total'));

        $byDivision = DB::table('tickets')
            ->selectRaw("
                COALESCE(NULLIF(TRIM(division), ''), 'Tidak ada divisi') as division_label,
                COUNT(*) as total
            ")
            ->groupBy('division_label')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->map(function ($row) {
                return (object) [
                    'division' => $row->division_label,
                    'total' => $row->total,
                ];
            });

        $byStatus = DB::table('tickets')
            ->selectRaw("
                LOWER(COALESCE(NULLIF(TRIM(status), ''), 'unknown')) as status,
                COUNT(*) as total
            ")
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        $byArea = DB::table('tickets')
            ->selectRaw("
                COALESCE(NULLIF(TRIM(area), ''), 'Tidak ada area') as area_label,
                COUNT(*) as total
            ")
            ->groupBy('area_label')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                return (object) [
                    'area' => $row->area_label,
                    'total' => $row->total,
                ];
            });

        $latest = DB::table('tickets')
            ->leftJoin('tbl_outlets', 'tbl_outlets.id', '=', 'tickets.outlet_id')
            ->leftJoin('users as pic', 'pic.id', '=', 'tickets.pic_user_id')
            ->select(
                'tickets.*',
                'tbl_outlets.nama_outlet',
                'tbl_outlets.kota',
                'pic.name as pic_name'
            )
            ->orderByDesc('tickets.id')
            ->limit(10)
            ->get();

        return view('Ticketing.dashboard.index', compact(
            'summary',
            'total',
            'closed',
            'completionRate',
            'overdue',
            'onSla',
            'onSlaPercent',
            'warningPercent',
            'overduePercent',
            'trend',
            'maxTrend',
            'byDivision',
            'byStatus',
            'byArea',
            'latest'
        ));
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        abort_if(!$user, 403);

        $role = $this->ticketRole($user);

        $mapColumn = collect([
            'google_maps_link',
            'link_maps',
            'maps_url',
            'google_maps',
            'google_maps_url',
            'map_url',
            'maps',
            'url_maps',
        ])->first(function ($column) {
            return Schema::hasColumn('tbl_outlets', $column);
        });

        $mapSelect = $mapColumn
            ? DB::raw('tbl_outlets.`' . $mapColumn . '` as maps_url')
            : DB::raw('NULL as maps_url');

        $query = DB::table('tickets')
            ->leftJoin('tbl_outlets', 'tbl_outlets.id', '=', 'tickets.outlet_id')
            ->leftJoin('users as pic', 'pic.id', '=', 'tickets.pic_user_id')
            ->leftJoin('users as vendor', 'vendor.id', '=', 'tickets.vendor_user_id')
            ->leftJoin('users as creator', 'creator.id', '=', 'tickets.created_by')
            ->select(
                'tickets.*',
                'tbl_outlets.nama_outlet',
                'tbl_outlets.kota',
                $mapSelect,
                'pic.name as pic_name',
                'vendor.name as vendor_name',
                'creator.name as creator_name'
            )
            ->orderByDesc('tickets.id');

        if ($role === 'pelapor') {
            $query->where('tickets.created_by', $user->id);
        } elseif ($role === 'pic') {
            $query->where('tickets.pic_user_id', $user->id);
        } elseif ($role === 'vendor') {
            $query->where('tickets.vendor_user_id', $user->id);
        } elseif ($role === 'maintenance') {
            $areaNames = $this->userAreaNames($user);

            if (empty($areaNames)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('tickets.area', $areaNames);
            }
        }

        foreach (['status', 'division', 'area', 'ticket_type', 'priority'] as $filter) {
            if ($request->filled($filter)) {
                $query->where('tickets.' . $filter, $request->input($filter));
            }
        }

        if ($request->filled('start_date')) {
            $query->whereDate('tickets.created_at', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('tickets.created_at', '<=', $request->input('end_date'));
        }

        if ($request->filled('q')) {
            $q = '%' . trim($request->q) . '%';

            $query->where(function ($w) use ($q) {
                $w->where('tickets.ticket_number', 'like', $q)
                    ->orWhere('tbl_outlets.nama_outlet', 'like', $q)
                    ->orWhere('tickets.description', 'like', $q)
                    ->orWhere('tickets.item', 'like', $q)
                    ->orWhere('tickets.area', 'like', $q)
                    ->orWhere('tickets.division', 'like', $q);
            });
        }

        $perPage = (int) $request->input('per_page', 15);
        $perPage = in_array($perPage, [10, 15, 25, 50, 100], true) ? $perPage : 15;

        $tickets = $query->paginate($perPage)->withQueryString();
        $lookups = $this->lookups();

        return view('Ticketing.tickets.index', compact('tickets', 'lookups', 'role'));
    }

    public function create()
    {
        $provinces = [
            'Jawa Timur',
            'Jawa Tengah',
            'Jawa Barat',
            'DKI Jakarta',
            'Daerah Istimewa Yogyakarta',
            'Banten',
            'Lampung',
        ];

        $outlets = DB::table('tbl_outlets')
            ->selectRaw('
                MIN(id) as id,
                TRIM(UPPER(nama_outlet)) as nama_outlet,
                MAX(kota) as kota
            ')
            ->where('is_active', 1)
            ->whereNotNull('nama_outlet')
            ->where('nama_outlet', '!=', '')
            ->groupBy(DB::raw('TRIM(UPPER(nama_outlet))'))
            ->orderBy('nama_outlet')
            ->get();

        $itemColumns = ['item', 'owner'];

        if (Schema::hasColumn('ticket_items', 'category')) {
            $itemColumns[] = 'category';
        }

        $itemsWithOwner = DB::table('ticket_items')
            ->where('is_active', 1)
            ->orderBy('item')
            ->get($itemColumns);

        return view('Ticketing.tickets.create', [
            'lookups' => $this->lookups(),
            'outlets' => $outlets,
            'provinces' => $provinces,
            'itemsWithOwner' => $itemsWithOwner,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        abort_if(!$user, 403);

        $data = $this->validateTicketRequest($request);

        $this->validateOutletExists($data['outlet_id']);

        $data['item'] = trim(
            $data['custom_item']
            ?? $data['item']
            ?? ''
        );

        if ($data['item'] === '') {
            return back()
                ->withInput()
                ->withErrors([
                    'item' => 'Item atau permintaan wajib diisi.'
                ]);
        }

        $uploadedPaths = $this->uploadOpenPhotos($request);

        // Kolom tickets.attachment_path hanya dipakai sebagai foto utama/thumbnail.
        // Semua foto tetap disimpan lengkap di tabel ticket_attachments.
        $attachment = $uploadedPaths[0]['path'] ?? null;

        $mapping = null;

        $status = 'Open';

        $lock = Cache::lock(
            'ticketing:create:' . $user->id,
            10
        );

        return $lock->block(5, function () use (
            $data,
            $uploadedPaths,
            $attachment,
            $mapping,
            $status,
            $user
        ) {

            return DB::transaction(function () use (
                $data,
                $uploadedPaths,
                $attachment,
                $mapping,
                $status,
                $user
            ) {

                $id = $this->createTicket(
                    $data,
                    $attachment,
                    $mapping,
                    $status,
                    $user
                );

                if (!empty($uploadedPaths)) {

                    $this->saveTicketAttachments(
                        $id,
                        $uploadedPaths,
                        $user->id
                    );
                }

                $this->log(
                    $id,
                    $user->id,
                    'CREATE',
                    null,
                    $status,
                    'Ticket berhasil dibuat.'
                );

                $this->clearDashboardCache();

                return redirect()
                    ->route('ticketing.show', $id)
                    ->with('success', 'Ticket berhasil dibuat.');
            });
        });
    }

    private function validateTicketRequest(Request $request): array
    {
        return $request->validate([
            'outlet_id'       => ['required', 'integer'],

            'province'        => ['required', 'string', 'max:100'],
            'city'            => ['required', 'string', 'max:100'],
            'area'            => ['required', 'string', 'max:100'],

            'division'        => ['required', 'string', 'max:100'],
            'ticket_type'     => ['required', 'string', 'max:100'],

            'item'            => ['nullable', 'string', 'max:150'],
            'custom_item'     => ['nullable', 'string', 'max:150'],

            'description'     => ['required', 'string'],

            'leader_name'     => ['nullable', 'string', 'max:150'],
            'reporter_phone'  => ['nullable', 'string', 'max:30'],
            'additional_notes'=> ['nullable', 'string'],

            'photos'          => ['nullable', 'array'],
            'photos.*'        => ['image', 'max:10240'],
        ]);
    }

    private function validateOutletExists(int $outletId): object
    {
        $outlet = DB::table('tbl_outlets')
            ->where('id', $outletId)
            ->first();

        abort_if(!$outlet, 404, 'Outlet tidak ditemukan.');

        return $outlet;
    }

    private function uploadOpenPhotos(Request $request): array
    {
        if (!$request->hasFile('photos')) {
            return [];
        }

        $files = $request->file('photos');

        if (!is_array($files)) {
            $files = [$files];
        }

        $uploaded = [];

        foreach ($files as $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');

            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
                $extension = 'jpg';
            }

            $filename = uniqid('ticket_', true) . '.' . $extension;

            $path = $file->storeAs(
                'ticketing/open',
                $filename,
                'public'
            );

            $uploaded[] = [
                'path' => $path,
                'name' => $file->getClientOriginalName(),
                'type' => $file->getMimeType() ?: 'image/' . $extension,
            ];
        }

        return $uploaded;
    }

    public function updateTicketDivision(Request $request, $ticket)
    {
        $role = strtolower(auth()->user()->role ?? '');

        if (!in_array($role, ['superadmin', 'admin', 'admin_ticketing', 'ticket_admin', 'superadmin_audit'])) {
            abort(403);
        }

        $request->validate([
            'division' => ['required', 'string', 'max:100'],
        ]);

        DB::table('tickets')
            ->where('id', $ticket)
            ->update([
                'division' => $request->division,
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Divisi ticket berhasil diperbarui.');
    }
    
    // private function buildTicketDescription(array $data): string
    // {
    //     $description = trim($data['description']);

    //     $meta = [];
    //     if (!empty($data['province'])) {
    //         $meta[] = 'Provinsi: ' . $data['province'];
    //     }
    //     if (!empty($data['city'])) {
    //         $meta[] = 'Kabupaten/Kota: ' . $data['city'];
    //     }
    //     if (!empty($data['leader_name'])) {
    //         $meta[] = 'Nama Leader/Pelapor: ' . $data['leader_name'];
    //     }
    //     if (!empty($data['reporter_phone'])) {
    //         $meta[] = 'No. HP Pelapor: ' . $data['reporter_phone'];
    //     }
    //     if (!empty($data['custom_item'])) {
    //         $meta[] = 'Permintaan Item Barang: ' . $data['custom_item'];
    //     }
    //     if (!empty($data['additional_notes'])) {
    //         $meta[] = 'Catatan Tambahan: ' . $data['additional_notes'];
    //     }

    //     if (!empty($meta)) {
    //         $description .= "\n\n" . implode("\n", $meta);
    //     }

    //     return $description;
    // }

    private function createTicket(
        array $data,
        ?string $attachment,
        ?object $mapping,
        string $status,
        object $user
    ): int {

        return DB::table('tickets')->insertGetId([

            'ticket_number' => $this->generateTicketNumber(),

            'created_by' => $user->id,

            'outlet_id' => $data['outlet_id'],

            'province' => $data['province'],
            'city' => $data['city'],
            'area' => $data['area'],

            'ticket_type' => $data['ticket_type'],
            'division' => $data['division'],
            'item' => $data['item'],

            'description' => $data['description'],

            'reporter_name' => $data['leader_name'] ?? null,

            'reporter_phone' => $data['reporter_phone'] ?? null,

            'extra_note' => $data['additional_notes'] ?? null,

            'priority' => null,

            'pic_user_id' => $mapping->pic_user_id ?? null,
            'vendor_user_id' => $mapping->vendor_user_id ?? null,

            'attachment_path' => $attachment,

            'is_duplicate_master_allowed' => 0,

            'opened_at' => now(),

            'status' => $status,

            'source' => 'Laravel Form',

            'sla_hours' => null,

            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function saveTicketAttachments(
        int $ticketId,
        array $uploadedPaths,
        int $userId
    ): void {
        if (empty($uploadedPaths)) {
            return;
        }

        $rows = [];

        foreach ($uploadedPaths as $file) {
            if (empty($file['path'])) {
                continue;
            }

            $rows[] = [
                'ticket_id' => $ticketId,
                'uploaded_by' => $userId,
                'file_path' => $file['path'],
                'file_name' => $file['name'] ?? basename($file['path']),
                'file_type' => $file['type'] ?? 'image',
                'created_at' => now(),
            ];
        }

        if (!empty($rows)) {
            DB::table('ticket_attachments')->insert($rows);
        }
    }
    
    public function show(int $ticket)
    {
        $user = Auth::user();
        abort_if(!$user, 403);

        $mapColumn = $this->mapColumn('tickets');
        $outletMapColumn = $this->mapColumn('tbl_outlets');

        $mapSelect = $mapColumn
            ? DB::raw('tickets.`' . $mapColumn . '` as maps_url')
            : ($outletMapColumn
                ? DB::raw('tbl_outlets.`' . $outletMapColumn . '` as maps_url')
                : DB::raw('NULL as maps_url'));

        $row = DB::table('tickets')
            ->leftJoin('tbl_outlets', 'tbl_outlets.id', '=', 'tickets.outlet_id')
            ->select(
                'tickets.*',
                'tbl_outlets.nama_outlet',
                'tbl_outlets.kota',
                $mapSelect
            )
            ->where('tickets.id', $ticket)
            ->first();

        abort_if(!$row, 404);

        $this->authorizeTicket($user, $row);

        $logs = DB::table('ticket_logs')
            ->leftJoin('users', 'users.id', '=', 'ticket_logs.user_id')
            ->where('ticket_id', $ticket)
            ->select(
                'ticket_logs.*',
                'users.name as user_name',
                'users.role as user_role'
            )
            ->orderBy('ticket_logs.id')
            ->get();

        $attachments = DB::table('ticket_attachments')
            ->where('ticket_id', $ticket)
            ->orderBy('id')
            ->get();

        if ($attachments->isEmpty() && !empty($row->attachment_path)) {
            $attachments = collect([
                (object) [
                    'file_path' => $row->attachment_path,
                    'file_name' => basename($row->attachment_path),
                    'file_type' => 'image',
                    'created_at' => $row->created_at,
                ],
            ]);
        }

        $users = DB::table('users')
            ->select('id', 'name', 'email', 'role')
            ->orderBy('name')
            ->get();

        $role = $this->ticketRole($user);

        $lookups = $this->lookups();

        return view('Ticketing.tickets.show', compact(
            'row',
            'logs',
            'attachments',
            'users',
            'role',
            'lookups'
        ));
    }

    public function updateTicketContent(Request $request, int $ticket)
    {
        $user = Auth::user();
        abort_if(!$user, 403);

        $data = $request->validate([
            'description' => ['required', 'string'],
            'extra_note' => ['nullable', 'string'],
            'maps_url' => ['nullable', 'url', 'max:1000'],
        ], [
            'description.required' => 'Deskripsi wajib diisi.',
            'maps_url.url' => 'Link Google Maps harus berupa URL yang valid.',
        ]);

        return $this->lockedUpdate($ticket, $user, function ($row) use ($data, $user) {
            $ticketUpdate = [
                'description' => trim($data['description']),
                'extra_note' => trim((string) ($data['extra_note'] ?? '')),
                'updated_at' => now(),
            ];

            $mapsUrl = trim((string) ($data['maps_url'] ?? ''));
            $ticketMapColumn = $this->mapColumn('tickets');
            $outletMapColumn = $this->mapColumn('tbl_outlets');

            if ($ticketMapColumn) {
                $ticketUpdate[$ticketMapColumn] = $mapsUrl !== '' ? $mapsUrl : null;
            }

            DB::table('tickets')
                ->where('id', $row->id)
                ->update($ticketUpdate);

            if (!$ticketMapColumn && $outletMapColumn && !empty($row->outlet_id)) {
                DB::table('tbl_outlets')
                    ->where('id', $row->outlet_id)
                    ->update([
                        $outletMapColumn => $mapsUrl !== '' ? $mapsUrl : null,
                        'updated_at' => now(),
                    ]);
            }

            $this->log(
                $row->id,
                $user->id,
                'UPDATE_DETAIL',
                $row->status,
                $row->status,
                'Deskripsi, catatan tambahan, dan link Google Maps diperbarui.'
            );

            $this->clearDashboardCache();

            return back()->with('success', 'Detail ticket berhasil diperbarui.');
        });
    }

    public function updateTicketPriority(Request $request, int $ticket)
    {
        $user = Auth::user();
        abort_if(!$this->isTicketAdmin($user), 403);

        $data = $request->validate([
            'priority' => ['required', 'string', 'max:50'],
        ]);

        $row = DB::table('tickets')->where('id', $ticket)->first();
        abort_if(!$row, 404);

        DB::table('tickets')
            ->where('id', $ticket)
            ->update([
                'priority' => strtolower($data['priority']),
                'sla_hours' => $this->slaHours($data['priority']),
                'updated_at' => now(),
            ]);

        $this->log(
            $ticket,
            $user->id,
            'UPDATE_PRIORITY',
            $row->priority,
            strtolower($data['priority']),
            'Prioritas ticket diupdate oleh admin.'
        );

        $this->clearDashboardCache();

        return back()->with('success', 'Prioritas berhasil diupdate.');
    }


    public function quickUpdateStatus(Request $request, int $ticket)
    {
        $user = Auth::user();
        abort_if(!$user, 403);
        abort_if(!$this->canExecute($user), 403);

        $allowedStatuses = $this->statuses;

        $data = $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', $allowedStatuses)],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $newStatus = $data['status'];
        $noteInput = trim((string) ($data['note'] ?? ''));

        if ($newStatus === 'Cancel') {
            abort_if(!$this->isTicketAdmin($user) && $this->ticketRole($user) !== 'pic', 403);
        }

        return $this->lockedUpdate($ticket, $user, function ($row) use ($user, $newStatus, $noteInput) {
            if (strtolower((string) $row->status) === strtolower($newStatus)) {
                return back()->with('success', 'Status ticket tidak berubah.');
            }

            $extra = [];
            $note = $noteInput !== ''
                ? $noteInput
                : 'Status ticket diubah cepat dari halaman daftar ticket.';

            switch ($newStatus) {
                case 'Open':
                    $extra['opened_at'] = $row->opened_at ?: now();
                    break;

                case 'Need Review':
                    if ($noteInput === '') {
                        $note = 'Ticket dikembalikan ke Need Review dari halaman daftar ticket.';
                    }
                    break;

                case 'Confirmed':
                    $extra['confirmed_at'] = now();
                    if ($noteInput === '') {
                        $note = 'Ticket dikonfirmasi dari halaman daftar ticket.';
                    }
                    break;

                case 'Process':
                    $extra['processed_at'] = now();
                    $extra['started_at'] = $row->started_at ?: now();
                    if ($noteInput === '') {
                        $note = 'Ticket diproses dari halaman daftar ticket.';
                    }
                    break;

                case 'Hold':
                    $extra['hold_reason'] = $noteInput !== ''
                        ? $noteInput
                        : ($row->hold_reason ?: 'Diubah cepat dari halaman daftar ticket.');
                    if ($noteInput === '') {
                        $note = 'Ticket di-hold dari halaman daftar ticket.';
                    }
                    break;

                case 'Closed':
                    $extra['closed_at'] = now();
                    $extra['completed_at'] = now();
                    $extra['lead_time_minutes'] = now()->diffInMinutes($row->opened_at ?: $row->created_at);
                    if ($noteInput === '') {
                        $note = 'Ticket ditutup dari halaman daftar ticket.';
                    }
                    break;

                case 'Cancel':
                    $extra['cancelled_at'] = now();
                    $extra['cancel_reason'] = $noteInput !== ''
                        ? $noteInput
                        : ($row->cancel_reason ?: 'Dibatalkan dari halaman daftar ticket.');
                    if ($noteInput === '') {
                        $note = 'Ticket dibatalkan dari halaman daftar ticket.';
                    }
                    break;
            }

            DB::table('tickets')
                ->where('id', $row->id)
                ->update(array_merge($extra, [
                    'status' => $newStatus,
                    'updated_at' => now(),
                ]));

            $this->log(
                $row->id,
                $user->id,
                'QUICK_STATUS_' . strtoupper(str_replace(' ', '_', $newStatus)),
                $row->status,
                $newStatus,
                $note
            );

            $this->clearDashboardCache();

            return back()->with('success', 'Status berhasil diupdate ke ' . $newStatus . '.');
        });
    }

    public function adminReview(Request $request, int $ticket)
    {
        $user = Auth::user();
        abort_if(!$this->isTicketAdmin($user), 403);

        $data = $request->validate([
            'pic_user_id' => ['required', 'integer'],
            'vendor_user_id' => ['nullable', 'integer'],
            'allow_duplicate_master' => ['nullable', 'boolean'],
            'note' => ['nullable', 'string'],
        ]);

        return $this->lockedUpdate($ticket, $user, function ($row) use ($data, $user) {
            DB::table('tickets')->where('id', $row->id)->update([
                'pic_user_id' => $data['pic_user_id'],
                'vendor_user_id' => $data['vendor_user_id'] ?? null,
                'is_duplicate_master_allowed' => (bool) ($data['allow_duplicate_master'] ?? false),
                'status' => 'Open',
                'updated_at' => now(),
            ]);
            $this->log($row->id, $user->id, 'ADMIN_REVIEW', $row->status, 'Open', $data['note'] ?? 'Review admin selesai, ticket menjadi Open.');
            $this->clearDashboardCache();
            return back()->with('success', 'Review admin tersimpan.');
        });
    }

    public function confirm(Request $request, int $ticket)
    {
        $user = Auth::user();
        abort_if(!$this->canExecute($user), 403);
        $data = $request->validate(['schedule_at' => ['nullable', 'date'], 'note' => ['nullable', 'string']]);
        return $this->changeStatus($ticket, $user, 'Confirmed', ['confirmed_at' => now(), 'schedule_at' => $data['schedule_at'] ?? null], $data['note'] ?? 'Ticket dikonfirmasi / dijadwalkan.');
    }

    public function process(Request $request, int $ticket)
    {
        $user = Auth::user();
        abort_if(!$this->canExecute($user), 403);
        $data = $request->validate(['start_at' => ['nullable', 'date'], 'note' => ['nullable', 'string']]);
        return $this->changeStatus($ticket, $user, 'Process', ['processed_at' => now(), 'started_at' => $data['start_at'] ?? now()], $data['note'] ?? 'Pekerjaan diproses.');
    }

    public function hold(Request $request, int $ticket)
    {
        $user = Auth::user();
        abort_if(!$this->canExecute($user), 403);
        $data = $request->validate(['reason' => ['required', 'string']]);
        return $this->changeStatus($ticket, $user, 'Hold', ['hold_reason' => $data['reason']], $data['reason']);
    }

    public function close(Request $request, int $ticket)
    {
        $user = Auth::user();
        abort_if(!$this->canExecute($user), 403);

        $data = $request->validate([
            'completed_at' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:4096'],
        ]);

        $extra = ['closed_at' => $data['completed_at'] ?? now(), 'completed_at' => $data['completed_at'] ?? now()];
        if ($request->hasFile('photo')) {
            $extra['completion_photo_path'] = $request->file('photo')->store('ticketing/close', 'public');
        }

        return $this->changeStatus($ticket, $user, 'Closed', $extra, $data['note'] ?? 'Pekerjaan selesai dan ticket closed.');
    }

    public function cancel(Request $request, int $ticket)
    {
        $user = Auth::user();
        abort_if(!$this->isTicketAdmin($user) && $this->ticketRole($user) !== 'pic', 403);
        $data = $request->validate(['reason' => ['required', 'string']]);
        return $this->changeStatus($ticket, $user, 'Cancel', ['cancelled_at' => now(), 'cancel_reason' => $data['reason']], $data['reason']);
    }

    public function comment(Request $request, int $ticket)
    {
        $user = Auth::user();
        abort_if(!$user, 403);

        $row = DB::table('tickets')->where('id', $ticket)->first();
        abort_if(!$row, 404);
        $this->authorizeTicket($user, $row);

        $data = $request->validate(['note' => ['required', 'string']]);
        $this->log($ticket, $user->id, 'COMMENT', $row->status, $row->status, $data['note']);

        return back()->with('success', 'Catatan ditambahkan.');
    }

    public function mappings()
    {
        $user = Auth::user();
        abort_if(!$this->isTicketAdmin($user), 403);

        $mappings = DB::table('ticket_mappings')
            ->leftJoin('users as pic', 'pic.id', '=', 'ticket_mappings.pic_user_id')
            ->leftJoin('users as vendor', 'vendor.id', '=', 'ticket_mappings.vendor_user_id')
            ->select('ticket_mappings.*', 'pic.name as pic_name', 'vendor.name as vendor_name')
            ->orderByDesc('ticket_mappings.id')
            ->get();

        $users = DB::table('users')->select('id', 'name', 'email', 'role')->orderBy('name')->get();

        return view('Ticketing.admin.mappings', compact('mappings', 'users'));
    }

    public function storeMapping(Request $request)
    {
        $user = Auth::user();
        abort_if(!$this->isTicketAdmin($user), 403);

        $data = $request->validate([
            'division' => ['required', 'max:100'],
            'ticket_type' => ['required', 'max:100'],
            'area' => ['required', 'max:100'],
            'item' => ['nullable', 'max:150'],
            'pic_user_id' => ['required', 'integer'],
            'vendor_user_id' => ['nullable', 'integer'],
        ]);

        DB::table('ticket_mappings')->insert(array_merge($data, ['created_at' => now(), 'updated_at' => now()]));
        Cache::forget('ticketing:lookups');

        return back()->with('success', 'Mapping berhasil dibuat.');
    }

    public function deleteMapping(int $mapping)
    {
        $user = Auth::user();
        abort_if(!$this->isTicketAdmin($user), 403);

        DB::table('ticket_mappings')->where('id', $mapping)->delete();
        Cache::forget('ticketing:lookups');

        return back()->with('success', 'Mapping dihapus.');
    }

public function exportCsv(): StreamedResponse
{
    Auth::user() ?: abort(403);

    return response()->streamDownload(function () {
        $out = fopen('php://output', 'w');

        $mapColumn = collect([
            'google_maps_link',
            'link_maps',
            'maps_url',
            'google_maps',
            'google_maps_url',
            'map_url',
            'maps',
            'url_maps',
        ])->first(function ($column) {
            return Schema::hasColumn('tbl_outlets', $column);
        });

        $mapSelect = $mapColumn
            ? DB::raw('tbl_outlets.`' . $mapColumn . '` as maps_url')
            : DB::raw('NULL as maps_url');

        fputcsv($out, [
            'No Ticket',
            'Outlet',
            'Kota',
            'Area',
            'Link Maps',
            'Divisi',
            'Jenis',
            'Item',
            'Priority',
            'Status',
            'Lead Time Menit',
            'Dibuat'
        ]);

        DB::table('tickets')
            ->leftJoin('tbl_outlets', 'tbl_outlets.id', '=', 'tickets.outlet_id')
            ->select(
                'tickets.*',
                'tbl_outlets.nama_outlet',
                'tbl_outlets.kota',
                $mapSelect
            )
            ->orderByDesc('tickets.id')
            ->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r->ticket_number,
                        $r->nama_outlet,
                        $r->kota,
                        $r->area,
                        $r->maps_url,
                        $r->division,
                        $r->ticket_type,
                        $r->item,
                        $r->priority,
                        $r->status,
                        $r->lead_time_minutes,
                        $r->created_at,
                    ]);
                }
            });

        fclose($out);
    }, 'rekap-ticketing-' . now()->format('Ymd') . '.csv');
}

public function print()
{
    Auth::user() ?: abort(403);

    $tickets = DB::table('tickets')
        ->leftJoin('tbl_outlets', 'tbl_outlets.id', '=', 'tickets.outlet_id')
        ->select(
            'tickets.*',
            'tbl_outlets.nama_outlet',
            'tbl_outlets.kota'
        )
        ->orderByDesc('tickets.id')
        ->limit(300)
        ->get();

    return view('Ticketing.dashboard.print', compact('tickets'));
}

    private function changeStatus(int $ticket, object $user, string $status, array $extra, string $note)
    {
        return $this->lockedUpdate($ticket, $user, function ($row) use ($user, $status, $extra, $note) {
            $update = array_merge($extra, ['status' => $status, 'updated_at' => now()]);
            if ($status === 'Closed') {
                $update['lead_time_minutes'] = now()->diffInMinutes($row->opened_at ?: $row->created_at);
            }
            DB::table('tickets')->where('id', $row->id)->update($update);
            $this->log($row->id, $user->id, 'STATUS_' . strtoupper($status), $row->status, $status, $note);
            $this->clearDashboardCache();
            return back()->with('success', 'Status berhasil diupdate ke ' . $status . '.');
        });
    }

    private function lockedUpdate(int $ticket, object $user, callable $callback)
    {
        $lock = Cache::lock('ticketing:update:' . $ticket, 15);

        return $lock->block(10, function () use ($ticket, $user, $callback) {
            return DB::transaction(function () use ($ticket, $user, $callback) {
                $row = DB::table('tickets')->where('id', $ticket)->lockForUpdate()->first();
                abort_if(!$row, 404);
                $this->authorizeTicket($user, $row);
                return $callback($row);
            });
        });
    }

    private function generateTicketNumber(): string
    {
        $date = now()->format('Ymd');

        $lastTicket = DB::table('tickets')
            ->whereDate('created_at', now()->toDateString())
            ->where('ticket_number', 'like', 'TCK-' . $date . '-%')
            ->orderByDesc('id')
            ->value('ticket_number');

        $nextNumber = 1;

        if ($lastTicket) {
            $lastNumber = (int) substr($lastTicket, -4);
            $nextNumber = $lastNumber + 1;
        }

        return 'TCK-' . $date . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    private function log(
        int $ticketId,
        ?int $userId,
        string $action,
        ?string $from,
        ?string $to,
        string $note
    ): void {
        DB::table('ticket_logs')->insert([
            'ticket_id' => $ticketId,
            'user_id' => $userId,
            'action' => $action,
            'note' => $note,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function findMapping(string $division, string $type, string $area, string $item): ?object
    {
        return DB::table('ticket_mappings')
            ->where('division', $division)
            ->where('ticket_type', $type)
            ->where('area', $area)
            ->where(function ($q) use ($item) {
                $q->where('item', $item)->orWhereNull('item');
            })
            ->orderByRaw('item IS NULL ASC')
            ->first();
    }

    private function slaHours(string $priority): int
    {
        if (Schema::hasTable('ticket_priorities')) {
            $sla = DB::table('ticket_priorities')
                ->whereRaw('LOWER(priority) = ?', [strtolower($priority)])
                ->where('is_active', 1)
                ->value('sla_hours');

            if ($sla) {
                return (int) $sla;
            }
        }

        return ['Low' => 72, 'Medium' => 48, 'High' => 24, 'Urgent' => 8][$priority] ?? 48;
    }

    private function mapColumn(string $table): ?string
    {
        foreach ([
            'google_maps_link',
            'link_maps',
            'maps_url',
            'google_maps',
            'google_maps_url',
            'map_url',
            'maps',
            'url_maps',
        ] as $column) {
            if (Schema::hasColumn($table, $column)) {
                return $column;
            }
        }

        return null;
    }

    private function lookups(): array
    {
        return Cache::remember('ticketing:lookups', 3600, function () {
            return [
                'divisions' => DB::table('ticket_divisions')
                    ->where('is_active', 1)
                    ->orderBy('division')
                    ->pluck('division')
                    ->toArray(),

                'areas' => DB::table('ticket_areas')
                    ->where('is_active', 1)
                    ->orderBy('area')
                    ->pluck('area')
                    ->toArray(),

                'types' => DB::table('ticket_types')
                    ->where('is_active', 1)
                    ->orderBy('ticket_type')
                    ->pluck('ticket_type')
                    ->toArray(),

                'items' => DB::table('ticket_items')
                    ->where('is_active', 1)
                    ->orderBy('item')
                    ->pluck('item')
                    ->toArray(),

                'items_with_owner' => DB::table('ticket_items')
                    ->where('is_active', 1)
                    ->orderBy('owner')
                    ->orderBy(Schema::hasColumn('ticket_items', 'category') ? 'category' : 'item')
                    ->orderBy('item')
                    ->get(array_values(array_filter([
                        'item',
                        Schema::hasColumn('ticket_items', 'owner') ? 'owner' : null,
                        Schema::hasColumn('ticket_items', 'category') ? 'category' : null,
                    ])))
                    ->toArray(),

                'priorities' => Schema::hasTable('ticket_priorities')
                    ? DB::table('ticket_priorities')
                        ->where('is_active', 1)
                        ->orderBy('id')
                        ->pluck('priority')
                        ->toArray()
                    : ['Low', 'Medium', 'High', 'Urgent'],

                'statuses' => $this->statuses,
            ];
        });
    }

    private function clearDashboardCache(): void
    {
        Cache::forget('ticketing:dashboard:summary');
        Cache::forget('ticketing:lookups');
    }

    private function authorizeTicket(object $user, object $row): void
    {
        $role = $this->ticketRole($user);

        if ($role === 'admin') return;

        if ($role === 'maintenance') {
            $areaNames = $this->userAreaNames($user);

            if (in_array(trim((string) $row->area), $areaNames, true)) {
                return;
            }

            abort(403, 'Tidak punya akses ke ticket area ini.');
        }

        if ($role === 'pelapor' && (int) $row->created_by === (int) $user->id) return;
        if ($role === 'pic' && (int) $row->pic_user_id === (int) $user->id) return;
        if ($role === 'vendor' && (int) $row->vendor_user_id === (int) $user->id) return;

        abort(403, 'Tidak punya akses ke ticket ini.');
    }

    private function ticketRole(?object $user): string
    {
        if (!$user) return 'guest';

        $role = strtolower((string) ($user->role ?? ''));

        if (in_array($role, ['superadmin', 'admin', 'admin_ticketing', 'ticket_admin', 'superadmin_audit'], true)) {
            return 'admin';
        }

        if ($role === 'maintenance') {
            return 'maintenance';
        }

        if (in_array($role, ['pic', 'ticket_pic', 'tm_manager', 'spv', 'leader'], true)) {
            return 'pic';
        }

        if (in_array($role, ['vendor', 'ticket_vendor', 'admindc', 'scm'], true)) {
            return 'vendor';
        }

        return 'pelapor';
    }

    private function isTicketAdmin(?object $user): bool
    {
        return $this->ticketRole($user) === 'admin';
    }

    private function canExecute(?object $user): bool
    {
        return in_array($this->ticketRole($user), ['admin', 'pic', 'vendor', 'maintenance'], true);
    }

    public function masterArea(Request $request)
    {
        $query = DB::table('ticket_areas')
            ->selectRaw(
                'id, TRIM(area) as area'
            )
            ->where('is_active', 1);

        if ($request->filled('q')) {
            $q = '%' . $request->q . '%';

            $query->where('area', 'like', $q);
        }

        $rows = $query
            ->orderBy('area')
            ->get();

        return view('Ticketing.master.area', compact('rows'));
    }

    public function masterItems(Request $request)
    {
        $hasCategoryColumn = Schema::hasColumn('ticket_items', 'category');

        $ownerLabels = [
            'SCM' => 'SCM',
            'HC' => 'HC',
            'Marketing' => 'Marketing',
            'BND' => 'BND',
        ];

        $itemCategories = [
            'SCM' => [
                'Equipment',
            ],
            'HC' => [
                'Jenis Seragam',
                'Jabatan Penerima',
                'Ukuran Seragam',
            ],
            'Marketing' => [
                'Jenis Pengajuan',
            ],
            'BND' => [
                'Building',
                'Equipment',
            ],
        ];

        $activeOwner = $request->input('owner', 'SCM');

        if (!array_key_exists($activeOwner, $ownerLabels)) {
            $activeOwner = 'SCM';
        }

        $activeCategory = $request->input(
            'category',
            $itemCategories[$activeOwner][0] ?? null
        );

        if (
            $activeCategory
            && !in_array($activeCategory, $itemCategories[$activeOwner] ?? [], true)
        ) {
            $activeCategory = $itemCategories[$activeOwner][0] ?? null;
        }

        $select = ['id', 'item', 'owner', 'is_active'];

        if ($hasCategoryColumn) {
            $select[] = 'category';
        }

        $query = DB::table('ticket_items')
            ->select($select)
            ->where('is_active', 1);

        if ($request->filled('owner')) {
            $query->where('owner', $activeOwner);
        }

        if ($hasCategoryColumn && $activeCategory) {
            $query->where('category', $activeCategory);
        }

        if ($request->filled('q')) {
            $query->where('item', 'like', '%' . trim($request->q) . '%');
        }

        $rows = $query
            ->orderBy('owner')
            ->when($hasCategoryColumn, function ($q) {
                $q->orderBy('category');
            })
            ->orderBy('item')
            ->get();

        return view('Ticketing.master.items', compact(
            'rows',
            'ownerLabels',
            'itemCategories',
            'activeOwner',
            'activeCategory',
            'hasCategoryColumn'
        ));
    }

    public function masterTypes(Request $request)
    {
        $query = DB::table('ticket_types')
            ->select('id', 'ticket_type', 'is_active')
            ->where('is_active', 1);

        if ($request->filled('q')) {
            $query->where('ticket_type', 'like', '%' . $request->q . '%');
        }

        $rows = $query->orderBy('ticket_type')->get();

        return view('Ticketing.master.types', compact('rows'));
    }

    public function masterDivisions(Request $request)
    {
        $query = DB::table('ticket_divisions')
            ->select('id', 'division', 'is_active')
            ->where('is_active', 1);

        if ($request->filled('q')) {
            $query->where('division', 'like', '%' . $request->q . '%');
        }

        $rows = $query->orderBy('division')->get();

        return view('Ticketing.master.divisions', compact('rows'));
    }

    public function storeArea(Request $request)
    {
        $data = $request->validate([
            'area' => ['required', 'string', 'max:100'],
        ]);

        DB::table('ticket_areas')->insert([
            'area' => trim($data['area']),
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('ticketing.master.area')->with('success', 'Area berhasil ditambahkan.');
    }

    public function updateArea(Request $request, int $id)
    {
        $data = $request->validate([
            'area' => ['required', 'string', 'max:100'],
        ]);

        $row = DB::table('ticket_areas')->where('id', $id)->first();
        abort_if(!$row, 404);

        DB::table('ticket_areas')
            ->where('id', $id)
            ->update([
                'area' => trim($data['area']),
                'updated_at' => now(),
            ]);

        return redirect()->route('ticketing.master.area')->with('success', 'Area berhasil diperbarui.');
    }

    public function deleteArea(int $id)
    {
        $row = DB::table('ticket_areas')->where('id', $id)->first();
        abort_if(!$row, 404);

        DB::table('ticket_areas')->where('id', $id)->delete();

        return redirect()->route('ticketing.master.area')->with('success', 'Area berhasil dihapus.');
    }

    public function storeItem(Request $request)
    {
        $hasCategoryColumn = Schema::hasColumn('ticket_items', 'category');

        $rules = [
            'item' => ['required', 'string', 'max:150'],
            'owner' => ['required', 'string', 'max:50'],
        ];

        if ($hasCategoryColumn) {
            $rules['category'] = ['required', 'string', 'max:100'];
        }

        $data = $request->validate($rules);

        $insert = [
            'item' => trim($data['item']),
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('ticket_items', 'owner')) {
            $insert['owner'] = trim($data['owner'] ?? '');
        }

        if ($hasCategoryColumn) {
            $insert['category'] = trim($data['category'] ?? '');
        }

        DB::table('ticket_items')->insert($insert);

        Cache::forget('ticketing:lookups');

        return redirect()
            ->route('ticketing.master.items', [
                'owner' => $insert['owner'] ?? null,
                'category' => $insert['category'] ?? null,
            ])
            ->with('success', 'Item berhasil ditambahkan.');
    }

    public function updateItem(Request $request, int $id)
    {
        $hasCategoryColumn = Schema::hasColumn('ticket_items', 'category');

        $rules = [
            'item' => ['required', 'string', 'max:150'],
            'owner' => ['required', 'string', 'max:50'],
        ];

        if ($hasCategoryColumn) {
            $rules['category'] = ['required', 'string', 'max:100'];
        }

        $data = $request->validate($rules);

        $row = DB::table('ticket_items')->where('id', $id)->first();

        abort_if(!$row, 404);

        $update = [
            'item' => trim($data['item']),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('ticket_items', 'owner')) {
            $update['owner'] = trim($data['owner'] ?? '');
        }

        if ($hasCategoryColumn) {
            $update['category'] = trim($data['category'] ?? '');
        }

        DB::table('ticket_items')
            ->where('id', $id)
            ->update($update);

        Cache::forget('ticketing:lookups');

        return redirect()
            ->route('ticketing.master.items', [
                'owner' => $update['owner'] ?? null,
                'category' => $update['category'] ?? null,
            ])
            ->with('success', 'Item berhasil diperbarui.');
    }

    public function deleteItem(int $id)
    {
        $row = DB::table('ticket_items')->where('id', $id)->first();
        abort_if(!$row, 404);

        DB::table('ticket_items')->where('id', $id)->delete();

        Cache::forget('ticketing:lookups');

        return redirect()->route('ticketing.master.items')->with('success', 'Item berhasil dihapus.');
    }

    public function storeType(Request $request)
    {
        $data = $request->validate([
            'ticket_type' => ['required', 'string', 'max:100'],
        ]);

        DB::table('ticket_types')->insert([
            'ticket_type' => trim($data['ticket_type']),
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Cache::forget('ticketing:lookups');

        return redirect()->route('ticketing.master.types')->with('success', 'Jenis tiket berhasil ditambahkan.');
    }

    public function updateType(Request $request, int $id)
    {
        $data = $request->validate([
            'ticket_type' => ['required', 'string', 'max:100'],
        ]);

        $row = DB::table('ticket_types')->where('id', $id)->first();
        abort_if(!$row, 404);

        DB::table('ticket_types')
            ->where('id', $id)
            ->update([
                'ticket_type' => trim($data['ticket_type']),
                'updated_at' => now(),
            ]);

        Cache::forget('ticketing:lookups');

        return redirect()->route('ticketing.master.types')->with('success', 'Jenis tiket berhasil diperbarui.');
    }

    public function deleteType(int $id)
    {
        $row = DB::table('ticket_types')->where('id', $id)->first();
        abort_if(!$row, 404);

        DB::table('ticket_types')->where('id', $id)->delete();

        Cache::forget('ticketing:lookups');

        return redirect()->route('ticketing.master.types')->with('success', 'Jenis tiket berhasil dihapus.');
    }

    public function storeDivision(Request $request)
    {
        $data = $request->validate([
            'division' => ['required', 'string', 'max:100'],
        ]);

        DB::table('ticket_divisions')->insert([
            'division' => trim($data['division']),
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Cache::forget('ticketing:lookups');

        return redirect()->route('ticketing.master.divisions')->with('success', 'Divisi berhasil ditambahkan.');
    }

    public function updateDivision(Request $request, int $id)
    {
        $data = $request->validate([
            'division' => ['required', 'string', 'max:100'],
        ]);

        $row = DB::table('ticket_divisions')->where('id', $id)->first();
        abort_if(!$row, 404);

        DB::table('ticket_divisions')
            ->where('id', $id)
            ->update([
                'division' => trim($data['division']),
                'updated_at' => now(),
            ]);

        Cache::forget('ticketing:lookups');

        return redirect()->route('ticketing.master.divisions')->with('success', 'Divisi berhasil diperbarui.');
    }

    public function deleteDivision(int $id)
    {
        $row = DB::table('ticket_divisions')->where('id', $id)->first();
        abort_if(!$row, 404);

        DB::table('ticket_divisions')->where('id', $id)->delete();

        Cache::forget('ticketing:lookups');

        return redirect()->route('ticketing.master.divisions')->with('success', 'Divisi berhasil dihapus.');
    }

    public function masterPriorities(Request $request)
    {
        $query = DB::table('ticket_priorities')
            ->select('id', 'priority', 'sla_hours', 'is_active')
            ->where('is_active', 1);

        if ($request->filled('q')) {
            $query->where('priority', 'like', '%' . $request->q . '%');
        }

        $rows = $query->orderBy('id')->get();

        return view('Ticketing.master.priorities', compact('rows'));
    }

    public function users(Request $request)
    {
        $query = DB::table('users')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.role',
                'users.area_id',
                'users.created_at',
                'users.updated_at'
            )
            ->where('users.role', 'maintenance');

        if ($request->filled('q')) {
            $q = '%' . $request->q . '%';

            $query->where(function ($w) use ($q) {
                $w->where('users.name', 'like', $q)
                    ->orWhere('users.email', 'like', $q);
            });
        }

        $rows = $query->orderBy('users.name')->get()
            ->map(function ($row) {

                $areaIds = json_decode($row->area_id ?? '[]', true);

                if (!is_array($areaIds)) {
                    $areaIds = [];
                }

                $row->areas = DB::table('ticket_areas')
                    ->whereIn('id', $areaIds)
                    ->pluck('area')
                    ->implode(', ');

                $row->created_at = $row->created_at
                    ? \Carbon\Carbon::parse($row->created_at)
                    : null;

                $row->updated_at = $row->updated_at
                    ? \Carbon\Carbon::parse($row->updated_at)
                    : null;

                return $row;
            });

        return view('Ticketing.master.users', compact('rows'));
    }

    private function userAreaNames(object $user): array
    {
        $areaIds = json_decode($user->area_id ?? '[]', true);

        if (!is_array($areaIds)) {
            $areaIds = [];
        }

        return DB::table('ticket_areas')
            ->whereIn('id', $areaIds)
            ->pluck('area')
            ->toArray();
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],

            'area_id' => ['required', 'array'],
            'area_id.*' => ['integer', 'exists:ticket_areas,id'],
        ]);

        $data['password'] = bcrypt($data['password']);

        $data['role'] = 'maintenance';

        $data['area_id'] = json_encode($data['area_id']);

        $data['created_at'] = now();
        $data['updated_at'] = now();

        DB::table('users')->insert($data);

        return redirect()
            ->route('ticketing.master.users')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function showEditUser(int $id)
    {
        $user = DB::table('users')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.role',
                'users.area_id',
                'users.created_at',
                'users.updated_at'
            )
            ->where('users.id', $id)
            ->where('users.role', 'maintenance')
            ->first();

        abort_if(!$user, 404);

        $user->selected_areas = json_decode($user->area_id ?? '[]', true);

        if (!is_array($user->selected_areas)) {
            $user->selected_areas = [];
        }

        $user->created_at = $user->created_at
            ? \Carbon\Carbon::parse($user->created_at)
            : null;

        $user->updated_at = $user->updated_at
            ? \Carbon\Carbon::parse($user->updated_at)
            : null;

        return view('Ticketing.master.users-edit', compact('user'));
    }

    public function updateUser(Request $request, int $id)
    {
        $user = DB::table('users')
            ->where('id', $id)
            ->where('role', 'maintenance')
            ->first();

        abort_if(!$user, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email,' . $id],
            'password' => ['nullable', 'string', 'min:8'],

            'area_id' => ['required', 'array'],
            'area_id.*' => ['integer', 'exists:ticket_areas,id'],
        ]);

        if ($request->filled('password')) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        $data['area_id'] = json_encode($data['area_id']);

        $data['updated_at'] = now();

        DB::table('users')
            ->where('id', $id)
            ->update($data);

        return redirect()
            ->route('ticketing.master.users')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function deleteUser(int $id)
    {
        $user = DB::table('users')->where('id', $id)->where('role', 'maintenance')->first();
        abort_if(!$user, 404);

        DB::table('users')->where('id', $id)->delete();

        return redirect()->route('ticketing.master.users')->with('success', 'User berhasil dihapus.');
    }

    public function storePriority(Request $request)
    {
        $data = $request->validate([
            'priority' => ['required', 'string', 'max:50'],
            'sla_hours' => ['required', 'integer', 'min:1'],
        ]);

        DB::table('ticket_priorities')->insert([
            'priority' => trim($data['priority']),
            'sla_hours' => $data['sla_hours'],
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Cache::forget('ticketing:lookups');

        return redirect()->route('ticketing.master.priorities')
            ->with('success', 'Prioritas berhasil ditambahkan.');
    }

    public function updatePriority(Request $request, int $id)
    {
        $data = $request->validate([
            'priority' => ['required', 'string', 'max:50'],
            'sla_hours' => ['required', 'integer', 'min:1'],
        ]);

        $row = DB::table('ticket_priorities')->where('id', $id)->first();
        abort_if(!$row, 404);

        DB::table('ticket_priorities')
            ->where('id', $id)
            ->update([
                'priority' => trim($data['priority']),
                'sla_hours' => $data['sla_hours'],
                'updated_at' => now(),
            ]);

        Cache::forget('ticketing:lookups');

        return redirect()->route('ticketing.master.priorities')
            ->with('success', 'Prioritas berhasil diperbarui.');
    }

    public function deletePriority(int $id)
    {
        $row = DB::table('ticket_priorities')->where('id', $id)->first();
        abort_if(!$row, 404);

        DB::table('ticket_priorities')->where('id', $id)->delete();

        Cache::forget('ticketing:lookups');

        return redirect()->route('ticketing.master.priorities')
            ->with('success', 'Prioritas berhasil dihapus.');
    }
}
