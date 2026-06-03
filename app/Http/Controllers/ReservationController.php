<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReservationController extends Controller
{
    // =========================================================================
    // 1. DASHBOARD
    // =========================================================================
    public function dashboard(Request $request)
    {
        // 1. Tangkap parameter tanggal dari URL, jika kosong gunakan hari ini
        $selectedDateStr = $request->input('date', Carbon::now()->toDateString());
        $selectedDate = Carbon::parse($selectedDateStr);

        $todayStr = Carbon::now()->toDateString();
        $currentTime = Carbon::now()->format('H:i:s');

        // 2. STATISTIK (Tetap menggunakan data keseluruhan/hari ini)
        $totalRooms = DB::table('rooms')->where('is_active', 1)->count();
        $todayReservations = DB::table('reservations')
            ->where('reservation_date', $selectedDateStr) // Berubah sesuai tanggal yang diklik
            ->where('status', 'Approved')
            ->count();
        $myPending = DB::table('reservations')
            ->where('user_id', Auth::id())
            ->where('status', 'Pending')
            ->count();

        // 3. KALENDER MINGGUAN DINAMIS
        $startOfWeek = $selectedDate->copy()->startOfWeek();
        $weekDates = [];
        $dayMap = ['Monday' => 'Sen', 'Tuesday' => 'Sel', 'Wednesday' => 'Rab', 'Thursday' => 'Kam', 'Friday' => 'Jum', 'Saturday' => 'Sab', 'Sunday' => 'Min'];

        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);

            $hasSchedule = DB::table('reservations')
                ->where('user_id', Auth::id())
                ->where('reservation_date', $date->toDateString())
                ->whereIn('status', ['Approved', 'Pending'])
                ->exists();

            $weekDates[] = [
                'day_name' => $dayMap[$date->format('l')],
                'date_num' => $date->format('d'),
                'full_date' => $date->toDateString(),
                'is_selected' => $date->toDateString() === $selectedDateStr, // Cek mana yang aktif
                'has_schedule' => $hasSchedule
            ];
        }

        // Tombol Prev & Next Week
        $prevWeek = $selectedDate->copy()->subWeek()->toDateString();
        $nextWeek = $selectedDate->copy()->addWeek()->toDateString();

        // 4. AGENDA (Berdasarkan Tanggal yang Dipilih)
        $rawAgenda = DB::table('reservations')
            ->join('rooms', 'reservations.room_id', '=', 'rooms.id')
            ->join('users', 'reservations.user_id', '=', 'users.id')
            ->join('reservation_time_slots', 'reservations.id', '=', 'reservation_time_slots.reservation_id')
            ->join('time_slots', 'reservation_time_slots.time_slot_id', '=', 'time_slots.id')
            ->where('reservations.reservation_date', $selectedDateStr) // <--- Filter Dinamis
            ->where('reservations.status', 'Approved')
            ->select(
                'reservations.id',
                'rooms.name as room_name',
                'users.name as user_name',
                'reservations.division',
                'time_slots.start_time',
                'time_slots.end_time'
            )
            ->orderBy('time_slots.start_time')
            ->get();

        $groupedAgenda = [];
        foreach ($rawAgenda as $item) {
            if (!isset($groupedAgenda[$item->id])) {
                $groupedAgenda[$item->id] = [
                    'room_name' => $item->room_name,
                    'user_name' => $item->user_name,
                    'division' => $item->division ?: 'UN', // Jika kosong beri nilai UM (Umum)
                    'start_time' => Carbon::parse($item->start_time)->format('H:i'),
                    'end_time' => Carbon::parse($item->end_time)->format('H:i'),
                ];
            } else {
                $groupedAgenda[$item->id]['end_time'] = Carbon::parse($item->end_time)->format('H:i');
            }
        }

        // 5. LIVE STATUS (Harus selalu Real-Time Hari Ini, bukan tanggal yang diklik)
        $rooms = DB::table('rooms')->where('is_active', 1)->get();
        $liveStatus = [];
        foreach ($rooms as $room) {
            $isUsedNow = DB::table('reservations')
                ->join('reservation_time_slots', 'reservations.id', '=', 'reservation_time_slots.reservation_id')
                ->join('time_slots', 'reservation_time_slots.time_slot_id', '=', 'time_slots.id')
                ->where('reservations.room_id', $room->id)
                ->where('reservations.reservation_date', $todayStr) // Selalu Hari ini
                ->where('reservations.status', 'Approved')
                ->where('time_slots.start_time', '<=', $currentTime)
                ->where('time_slots.end_time', '>=', $currentTime)
                ->exists();

            $liveStatus[] = [
                'name' => $room->name,
                'is_used' => $isUsedNow
            ];
        }

        return view('Room.dashboard', compact(
            'totalRooms',
            'todayReservations',
            'myPending',
            'weekDates',
            'groupedAgenda',
            'liveStatus',
            'selectedDateStr',
            'prevWeek',
            'nextWeek'
        ));
    }

    public function profile()
    {
        return view('Room.profile');
    }

   
    // ==========================================
    // 📅 FORM PEMINJAMAN RUANGAN (INDEX)
    // ==========================================
    public function bookingIndex(Request $request)
    {
        // 1. Ambil tanggal dari URL (jika ada), jika tidak gunakan hari ini
        $selectedDate = $request->input('date', \Carbon\Carbon::now()->toDateString());
        
        // 2. Ambil master data (Ruangan, Divisi, Waktu)
        $rooms = DB::table('rooms')->where('is_active', 1)->orderBy('name')->get();
        $divisions = DB::table('hospace_divisions')->orderBy('name')->get();
        $timeSlots = DB::table('time_slots')->orderBy('start_time')->get();

        // 3. Ambil data jam yang SUDAH DIBOOKING pada tanggal terpilih
        $bookedSlots = DB::table('reservations')
            ->join('reservation_time_slots', 'reservations.id', '=', 'reservation_time_slots.reservation_id')
            ->where('reservations.reservation_date', $selectedDate)
            ->whereIn('reservations.status', ['Approved', 'Pending'])
            ->select('reservations.room_id', 'reservation_time_slots.time_slot_id', 'reservations.status')
            ->get();

        // 4. Kelompokkan data booking ke dalam array matriks untuk dipakai di Blade
        // Format: $bookedMatrix[room_id][time_slot_id] = 'Status'
        $bookedMatrix = [];
        foreach ($bookedSlots as $booked) {
            $bookedMatrix[$booked->room_id][$booked->time_slot_id] = $booked->status;
        }

        return view('Room.staff.reservation', compact('rooms', 'divisions', 'timeSlots', 'selectedDate', 'bookedMatrix'));
    }

    // ==========================================
    // 💾 SIMPAN PEMINJAMAN (STORE)
    // ==========================================
    public function bookingStore(Request $request)
    {
        $request->validate([
            'room_id' => 'required|integer',
            'reservation_date' => 'required|date',
            'division' => 'required|string',
            'agenda' => 'required|string|max:255',
        ]);

        // Karena form di Blade mengirimkan array waktu berdasarkan ID ruangan
        $timeSlotIds = $request->input('slots_' . $request->room_id);

        if (empty($timeSlotIds) || !is_array($timeSlotIds)) {
            return back()->withInput()->withErrors(['Waktu' => 'Anda harus memilih minimal satu sesi waktu pada ruangan yang dipilih.']);
        }

        // Cek Bentrok (Conflict Checking Server-Side)
        $isConflict = DB::table('reservations')
            ->join('reservation_time_slots', 'reservations.id', '=', 'reservation_time_slots.reservation_id')
            ->where('reservations.room_id', $request->room_id)
            ->where('reservations.reservation_date', $request->reservation_date)
            ->whereIn('reservations.status', ['Approved', 'Pending']) 
            ->whereIn('reservation_time_slots.time_slot_id', $timeSlotIds)
            ->exists();

        if ($isConflict) {
            return back()->withInput()->withErrors(['Bentrok' => 'Maaf, waktu yang Anda pilih baru saja dipesan oleh divisi lain.']);
        }

        // Simpan Data Utama
        $reservationId = DB::table('reservations')->insertGetId([
            'user_id' => auth()->id(),
            'room_id' => $request->room_id,
            'division' => $request->division,
            'agenda' => $request->agenda,
            'reservation_date' => $request->reservation_date,
            'status' => 'Pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Simpan Relasi Waktu
        $timeSlotData = [];
        foreach ($timeSlotIds as $timeId) {
            $timeSlotData[] = [
                'reservation_id' => $reservationId,
                'time_slot_id' => $timeId,
            ];
        }
        DB::table('reservation_time_slots')->insert($timeSlotData);

        return redirect()->route('reservations.history')->with('success', 'Reservasi berhasil diajukan dan menunggu persetujuan Admin.');
    }

    public function approvalIndex()
    {
        // 1. Ambil data reservasi yang statusnya masih 'Pending'
        $approvals = DB::table('reservations')
            ->join('rooms', 'reservations.room_id', '=', 'rooms.id')
            ->join('users', 'reservations.user_id', '=', 'users.id')
            ->select('reservations.*', 'rooms.name as room_name', 'users.name as user_name')
            ->where('reservations.status', 'Pending')
            ->orderBy('reservations.created_at', 'asc')
            ->get();

        // 2. Tarik rentang waktu (jam) untuk masing-masing reservasi
        $approvalIds = $approvals->pluck('id')->toArray();
        if (!empty($approvalIds)) {
            $times = DB::table('reservation_time_slots')
                ->join('time_slots', 'reservation_time_slots.time_slot_id', '=', 'time_slots.id')
                ->whereIn('reservation_time_slots.reservation_id', $approvalIds)
                ->select(
                    'reservation_time_slots.reservation_id',
                    DB::raw('MIN(time_slots.start_time) as start_time'),
                    DB::raw('MAX(time_slots.end_time) as end_time')
                )
                ->groupBy('reservation_time_slots.reservation_id')
                ->get()
                ->keyBy('reservation_id');

            // 3. Sisipkan teks waktu ke dalam data approval
            foreach ($approvals as $approval) {
                if (isset($times[$approval->id])) {
                    $approval->time_range = \Carbon\Carbon::parse($times[$approval->id]->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($times[$approval->id]->end_time)->format('H:i');
                } else {
                    $approval->time_range = 'Waktu tidak valid';
                }
            }
        }

        // Pastikan nama view ini sama persis dengan yang ada di kodemu sebelumnya
        return view('Room.staff.approval', compact('approvals'));
    }

    // ==========================================
    // ✅ APPROVE RESERVASI
    // ==========================================
    public function approvalApprove($id)
    {
        // Update status jadi Approved
        DB::table('reservations')->where('id', $id)->update(['status' => 'Approved']);

        // Ambil data reservasi untuk dikirim ke notifikasi
        $reservation = DB::table('reservations')
            ->join('rooms', 'reservations.room_id', '=', 'rooms.id')
            ->select('reservations.*', 'rooms.name as room_name')
            ->where('reservations.id', $id)
            ->first();

        // Tembakkan Notifikasi ke User yang meminjam
        DB::table('hospace_notifications')->insert([
            'user_id' => $reservation->user_id,
            'title' => '✅ Reservasi Disetujui',
            'message' => "Pengajuan ruangan {$reservation->room_name} pada tanggal " . \Carbon\Carbon::parse($reservation->reservation_date)->format('d M Y') . " telah disetujui Admin.",
            'created_at' => now()
        ]);

        return back()->with('success', 'Reservasi berhasil disetujui!');
    }

    // ==========================================
    // ❌ REJECT RESERVASI (Dengan Alasan)
    // ==========================================
    public function approvalReject(Request $request, $id)
    {
        // Validasi input alasan penolakan
        $request->validate([
            'rejection_reason' => 'required|string'
        ]);

        // Update status jadi Rejected dan simpan alasannya
        DB::table('reservations')->where('id', $id)->update([
            'status' => 'Rejected',
            'rejection_reason' => $request->rejection_reason
        ]);

        $reservation = DB::table('reservations')
            ->join('rooms', 'reservations.room_id', '=', 'rooms.id')
            ->select('reservations.*', 'rooms.name as room_name')
            ->where('reservations.id', $id)
            ->first();

        // Tembakkan Notifikasi ke User
        DB::table('hospace_notifications')->insert([
            'user_id' => $reservation->user_id,
            'title' => '❌ Reservasi Ditolak',
            'message' => "Maaf, pengajuan {$reservation->room_name} pada " . \Carbon\Carbon::parse($reservation->reservation_date)->format('d M Y') . " ditolak. Alasan: " . $request->rejection_reason,
            'created_at' => now()
        ]);

        return back()->with('success', 'Reservasi berhasil ditolak beserta alasannya!');
    }

    public function historyIndex(Request $request)
    {
        // 1. Tangkap parameter dari URL (default 10 data per halaman)
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search');
        $status = $request->input('status', 'All');

        // 2. Buat dasar query
        $query = DB::table('reservations')
            ->join('rooms', 'reservations.room_id', '=', 'rooms.id')
            ->join('users', 'reservations.user_id', '=', 'users.id')
            ->select('reservations.*', 'rooms.name as room_name', 'users.name as user_name');

        // 3. Filter Hak Akses (User biasa hanya melihat miliknya)
        if (auth()->user()->role === 'userhospace') {
            $query->where('reservations.user_id', auth()->id());
        }

        // 4. Filter Pencarian (Search) Server-Side
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('rooms.name', 'like', "%{$search}%")
                  ->orWhere('reservations.agenda', 'like', "%{$search}%")
                  ->orWhere('users.name', 'like', "%{$search}%");
            });
        }

        // 5. Filter Status Dropdown
        if ($status !== 'All') {
            $query->where('reservations.status', $status);
        }

        // 6. Eksekusi query dengan Pagination
        $histories = $query->orderBy('reservations.reservation_date', 'desc')
                           ->orderBy('reservations.created_at', 'desc')
                           ->paginate($perPage);

        // 7. Ambil Rentang Jam (Waktu) untuk data yang ditampilkan saja agar ringan
        $historyIds = $histories->pluck('id')->toArray();
        if (!empty($historyIds)) {
            $times = DB::table('reservation_time_slots')
                ->join('time_slots', 'reservation_time_slots.time_slot_id', '=', 'time_slots.id')
                ->whereIn('reservation_time_slots.reservation_id', $historyIds)
                ->select(
                    'reservation_time_slots.reservation_id', 
                    DB::raw('MIN(time_slots.start_time) as start_time'), 
                    DB::raw('MAX(time_slots.end_time) as end_time')
                )
                ->groupBy('reservation_time_slots.reservation_id')
                ->get()
                ->keyBy('reservation_id');

            // Sisipkan waktu ke masing-masing objek history
            foreach ($histories as $history) {
                if (isset($times[$history->id])) {
                    $history->time_range = \Carbon\Carbon::parse($times[$history->id]->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($times[$history->id]->end_time)->format('H:i');
                } else {
                    $history->time_range = 'Waktu tidak valid';
                }
            }
        }

        return view('Room.staff.historyOrder', compact('histories'));
    }

    // ==========================================
    // 🚫 BATALKAN RESERVASI (KARYAWAN)
    // ==========================================
    public function cancelReservation($id)
    {
        $reservation = DB::table('reservations')->where('id', $id)->first();

        if (!$reservation) {
            return back()->withErrors(['Gagal' => 'Data reservasi tidak ditemukan.']);
        }

        // Pastikan karyawan hanya bisa membatalkan miliknya sendiri
        if (auth()->user()->role === 'userhospace' && $reservation->user_id !== auth()->id()) {
            return back()->withErrors(['Gagal' => 'Anda tidak memiliki akses untuk membatalkan reservasi ini.']);
        }

        // Cek agar tidak membatalkan reservasi yang sudah berlalu (kemarin/bulan lalu)
        $today = \Carbon\Carbon::now()->toDateString();
        if ($reservation->reservation_date < $today) {
            return back()->withErrors(['Gagal' => 'Tidak dapat membatalkan reservasi yang tanggalnya sudah lewat.']);
        }

        // Cek agar tidak membatalkan yang sudah ditolak atau dibatalkan sebelumnya
        if (in_array($reservation->status, ['Rejected', 'Cancelled'])) {
            return back()->withErrors(['Gagal' => 'Reservasi ini sudah berstatus tidak aktif.']);
        }

        // Update status menjadi Cancelled
        DB::table('reservations')->where('id', $id)->update([
            'status' => 'Cancelled',
            'updated_at' => now()
        ]);

        return back()->with('success', 'Reservasi berhasil dibatalkan. Slot waktu kini kembali tersedia.');
    }

    // ==========================================
    // 🔄 TAMPILKAN FORM RESCHEDULE
    // ==========================================
    public function reschedule(Request $request, $id)
    {
        $reservation = DB::table('reservations')->where('id', $id)->first();
        
        // Proteksi akses
        if (!$reservation || (auth()->user()->role === 'userhospace' && $reservation->user_id !== auth()->id())) {
            return redirect()->route('reservations.history')->withErrors(['Akses Ditolak' => 'Data tidak ditemukan.']);
        }

        // Ambil tanggal dari URL, jika kosong gunakan tanggal dari reservasi lama
        $selectedDate = $request->input('date', $reservation->reservation_date);

        $rooms = DB::table('rooms')->where('is_active', 1)->orderBy('name')->get();
        $divisions = DB::table('hospace_divisions')->orderBy('name')->get();
        $timeSlots = DB::table('time_slots')->orderBy('start_time')->get();

        // Matriks Ruangan (KECUALI reservasi ini sendiri)
        $bookedSlots = DB::table('reservations')
            ->join('reservation_time_slots', 'reservations.id', '=', 'reservation_time_slots.reservation_id')
            ->where('reservations.reservation_date', $selectedDate)
            ->whereIn('reservations.status', ['Approved', 'Pending'])
            ->where('reservations.id', '!=', $id) // <-- Abaikan agar jam lama bisa dipilih ulang
            ->select('reservations.room_id', 'reservation_time_slots.time_slot_id', 'reservations.status')
            ->get();

        $bookedMatrix = [];
        foreach ($bookedSlots as $booked) {
            $bookedMatrix[$booked->room_id][$booked->time_slot_id] = $booked->status;
        }

        // Ambil jam yang dulu dipilih (agar tercetak otomatis)
        $currentSlots = DB::table('reservation_time_slots')->where('reservation_id', $id)->pluck('time_slot_id')->toArray();

        return view('Room.staff.reschedule', compact('reservation', 'rooms', 'divisions', 'timeSlots', 'selectedDate', 'bookedMatrix', 'currentSlots'));
    }

    // ==========================================
    // 💾 SIMPAN HASIL RESCHEDULE
    // ==========================================
    public function processReschedule(Request $request, $id)
    {
        $request->validate([
            'room_id' => 'required|integer',
            'reservation_date' => 'required|date',
            'division' => 'required|string',
            'agenda' => 'required|string|max:255',
        ]);

        $timeSlotIds = $request->input('slots_' . $request->room_id);

        if (empty($timeSlotIds) || !is_array($timeSlotIds)) {
            return back()->withInput()->withErrors(['Waktu' => 'Pilih minimal satu sesi waktu.']);
        }

        // Cek Bentrok (Kecuali ID sendiri)
        $isConflict = DB::table('reservations')
            ->join('reservation_time_slots', 'reservations.id', '=', 'reservation_time_slots.reservation_id')
            ->where('reservations.room_id', $request->room_id)
            ->where('reservations.reservation_date', $request->reservation_date)
            ->whereIn('reservations.status', ['Approved', 'Pending'])
            ->where('reservations.id', '!=', $id)
            ->whereIn('reservation_time_slots.time_slot_id', $timeSlotIds)
            ->exists();

        if ($isConflict) {
            return back()->withInput()->withErrors(['Bentrok' => 'Maaf, waktu yang Anda pilih sudah dipesan divisi lain.']);
        }

        // Update Tabel Utama
        DB::table('reservations')->where('id', $id)->update([
            'room_id' => $request->room_id,
            'division' => $request->division,
            'agenda' => $request->agenda,
            'reservation_date' => $request->reservation_date,
            'status' => 'Pending', // Status di-reset ke Pending agar di-Acc ulang Admin
            'updated_at' => now(),
        ]);

        // Hapus slot lama, ganti yang baru
        DB::table('reservation_time_slots')->where('reservation_id', $id)->delete();
        $timeSlotData = [];
        foreach ($timeSlotIds as $timeId) {
            $timeSlotData[] = [
                'reservation_id' => $id,
                'time_slot_id' => $timeId,
            ];
        }
        DB::table('reservation_time_slots')->insert($timeSlotData);

        return redirect()->route('reservations.history')->with('success', 'Jadwal berhasil diubah dan sedang menunggu persetujuan ulang Admin.');
    }

    // =========================================================================
    // 🛠️ MASTER PEMBLOKIRAN (MAINTENANCE) LOGIC
    // =========================================================================
    public function maintenanceIndex()
    {
        // Tarik riwayat pemblokiran yang statusnya 'Maintenance'
        $maintenances = DB::table('reservations')
            ->join('rooms', 'reservations.room_id', '=', 'rooms.id')
            ->select('reservations.*', 'rooms.name as room_name')
            ->where('reservations.status', 'Maintenance')
            ->orderBy('reservations.reservation_date', 'desc')
            ->get();

        // Data untuk form modal (opsi pilihan ruangan dan jam)
        $rooms = DB::table('rooms')->where('is_active', 1)->get();
        $timeSlots = DB::table('time_slots')->orderBy('start_time', 'asc')->get();

        return view('Room.staff.disableTime', compact('maintenances', 'rooms', 'timeSlots'));
    }

    public function maintenanceStore(Request $request)
    {
        $request->validate([
            'room_id'          => 'required|exists:rooms,id',
            'reservation_date' => 'required|date',
            'time_slots'       => 'required|array', // Array dari jam yang dicentang
            'agenda'           => 'required|string|max:255' // Alasan penutupan (misal: Perbaikan AC)
        ]);

        DB::beginTransaction();
        try {
            // 1. Buat header reservasi dengan status Maintenance
            $reservationId = DB::table('reservations')->insertGetId([
                'user_id'          => auth()->id() ?? 1, // Admin yang memblokir
                'room_id'          => $request->room_id,
                'reservation_date' => $request->reservation_date,
                'division'         => 'Sistem Admin',
                'agenda'           => $request->agenda,
                'status'           => 'Maintenance', // 👈 Status spesial
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            // 2. Simpan jam apa saja yang diblokir ke tabel pivot
            foreach ($request->time_slots as $slotId) {
                DB::table('reservation_time_slots')->insert([
                    'reservation_id' => $reservationId,
                    'time_slot_id'   => $slotId,
                ]);
            }

            DB::commit();
            return back()->with('success', 'Jadwal ruangan berhasil diblokir/ditutup!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['Gagal memblokir ruangan: ' . $e->getMessage()]);
        }
    }

    public function maintenanceDestroy($id)
    {
        // Menghapus blokir agar ruangan bisa dipakai lagi
        DB::table('reservations')->where('id', $id)->where('status', 'Maintenance')->delete();
        return back()->with('success', 'Pemblokiran dibuka, ruangan kembali tersedia!');
    }

    // =========================================================================
    // 3. MASTER RUANGAN (CRUD)
    // =========================================================================
    public function roomIndex()
    {
        $rooms = DB::table('rooms')->orderBy('id', 'desc')->get();
        return view('Room.staff.rooms', compact('rooms'));
    }

    public function roomStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'is_active' => 'required|in:0,1'
        ]);

        DB::table('rooms')->insert([
            'name' => $request->name,
            'capacity' => $request->capacity,
            'is_active' => $request->is_active,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Ruangan berhasil ditambahkan!');
    }

    public function roomUpdate(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'is_active' => 'required|in:0,1'
        ]);

        DB::table('rooms')->where('id', $id)->update([
            'name' => $request->name,
            'capacity' => $request->capacity,
            'is_active' => $request->is_active,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Data ruangan berhasil diperbarui!');
    }

    public function roomDestroy($id)
    {
        DB::table('rooms')->where('id', $id)->delete();
        return back()->with('success', 'Ruangan berhasil dihapus!');
    }

    // =========================================================================
    // 4. MASTER WAKTU (CRUD)
    // =========================================================================
    public function timeSlotIndex()
    {
        $timeSlots = DB::table('time_slots')->orderBy('start_time', 'asc')->get();
        return view('Room.staff.timeSlots', compact('timeSlots'));
    }

    public function timeSlotStore(Request $request)
    {
        $request->validate([
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'label' => 'nullable|string|max:50'
        ]);

        DB::table('time_slots')->insert([
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'label' => $request->label
        ]);

        return back()->with('success', 'Sesi waktu berhasil ditambahkan!');
    }

    public function timeSlotUpdate(Request $request, $id)
    {
        $request->validate([
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'label' => 'nullable|string|max:50'
        ]);

        DB::table('time_slots')->where('id', $id)->update([
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'label' => $request->label
        ]);

        return back()->with('success', 'Sesi waktu berhasil diperbarui!');
    }

    public function timeSlotDestroy($id)
    {
        DB::table('time_slots')->where('id', $id)->delete();
        return back()->with('success', 'Sesi waktu berhasil dihapus!');
    }

    // =========================================================================
    // 🏢 MASTER DIVISI (CRUD)
    // =========================================================================
    public function divisionIndex()
    {
        $divisions = DB::table('hospace_divisions')->orderBy('id', 'desc')->get();
        return view('Room.staff.division', compact('divisions'));
    }

    public function divisionStore(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100|unique:hospace_divisions,name']);

        DB::table('hospace_divisions')->insert([
            'name' => $request->name,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return back()->with('success', 'Divisi baru berhasil ditambahkan!');
    }

    public function divisionUpdate(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:100']);

        DB::table('hospace_divisions')->where('id', $id)->update([
            'name' => $request->name,
            'updated_at' => now()
        ]);

        return back()->with('success', 'Nama divisi berhasil diperbarui!');
    }

    public function divisionDestroy($id)
    {
        DB::table('hospace_divisions')->where('id', $id)->delete();
        return back()->with('success', 'Divisi berhasil dihapus!');
    }
}
