<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class InventoryController extends Controller
{
    // ==========================================
    // 📊 DASHBOARD INVENTARIS
    // ==========================================
    public function dashboard()
    {
        $totalLaptops = DB::table('inventory_laptops')->count();
        
        $statusCounts = DB::table('inventory_laptops')
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')->toArray();

        $available = $statusCounts['Available'] ?? 0;
        $inUse = $statusCounts['In Use'] ?? 0;
        $maintenance = $statusCounts['Maintenance'] ?? 0;
        $damaged = $statusCounts['Damaged'] ?? 0;
        $missing = $statusCounts['Missing'] ?? 0;

        // Mendekati habis masa garansi (kurang dari 30 hari)
        $warrantyEndingSoon = DB::table('inventory_laptops')
            ->where('warranty_expired_at', '>=', Carbon::now())
            ->where('warranty_expired_at', '<=', Carbon::now()->addDays(30))
            ->get();

        return view('Room.inventory.dashboard', compact('totalLaptops', 'available', 'inUse', 'maintenance', 'damaged', 'missing', 'warrantyEndingSoon'));
    }

    // ==========================================
    // 💻 MASTER DATA LAPTOP
    // ==========================================
    public function master(Request $request)
    {
        $query = DB::table('inventory_laptops')
            ->leftJoin('users', 'inventory_laptops.assigned_user_id', '=', 'users.id')
            ->select('inventory_laptops.*', 'users.name as assigned_user_name')
            ->orderBy('inventory_laptops.created_at', 'desc');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('asset_code', 'like', "%{$search}%")
                  ->orWhere('brand_model', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%");
        }

        $laptops = $query->paginate(15);
        $users = DB::table('users')->orderBy('name')->get();

        return view('Room.inventory.master', compact('laptops', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'serial_number' => 'required|string|unique:inventory_laptops,serial_number',
            'brand_model' => 'required|string',
            'cpu' => 'nullable|string',
            'ram' => 'nullable|string',
            'ssd' => 'nullable|string',
            'warranty_expired_at' => 'nullable|date',
            'location' => 'nullable|string'
        ]);

        // Generate Asset Code e.g., LT-2024-0001
        $year = date('Y');
        $lastAsset = DB::table('inventory_laptops')->where('asset_code', 'like', "LT-{$year}-%")->orderBy('id', 'desc')->first();
        
        $nextNumber = 1;
        if ($lastAsset) {
            $parts = explode('-', $lastAsset->asset_code);
            $nextNumber = intval(end($parts)) + 1;
        }
        $assetCode = "LT-{$year}-" . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        $barcodeString = $assetCode;

        $laptopId = DB::table('inventory_laptops')->insertGetId([
            'asset_code' => $assetCode,
            'barcode' => $barcodeString,
            'serial_number' => $request->serial_number,
            'brand_model' => $request->brand_model,
            'cpu' => $request->cpu,
            'ram' => $request->ram,
            'ssd' => $request->ssd,
            'warranty_expired_at' => $request->warranty_expired_at,
            'status' => 'Available',
            'location' => $request->location ?? 'Gudang IT',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('inventory_logs')->insert([
            'laptop_id' => $laptopId,
            'action_type' => 'Added',
            'notes' => 'Laptop baru ditambahkan ke gudang',
            'performed_by' => auth()->id(),
            'created_at' => now()
        ]);

        return back()->with('success', 'Laptop berhasil ditambahkan dengan kode ' . $assetCode);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'brand_model' => 'required|string',
            'status' => 'required|string',
        ]);

        $laptop = DB::table('inventory_laptops')->where('id', $id)->first();

        DB::table('inventory_laptops')->where('id', $id)->update([
            'serial_number' => $request->serial_number,
            'brand_model' => $request->brand_model,
            'cpu' => $request->cpu,
            'ram' => $request->ram,
            'ssd' => $request->ssd,
            'warranty_expired_at' => $request->warranty_expired_at,
            'status' => $request->status,
            'location' => $request->location,
            'updated_at' => now()
        ]);

        if ($laptop->status != $request->status) {
            DB::table('inventory_logs')->insert([
                'laptop_id' => $id,
                'action_type' => 'Status Change',
                'notes' => "Status berubah dari {$laptop->status} menjadi {$request->status}",
                'performed_by' => auth()->id(),
                'created_at' => now()
            ]);
        }

        return back()->with('success', 'Data laptop berhasil diperbarui.');
    }

    public function destroy($id)
    {
        DB::table('inventory_laptops')->where('id', $id)->delete();
        DB::table('inventory_logs')->where('laptop_id', $id)->delete();
        return back()->with('success', 'Data laptop berhasil dihapus.');
    }

    // ==========================================
    // 🤝 SERAH TERIMA (ASSIGNMENT)
    // ==========================================
    public function assignView($id)
    {
        $laptop = DB::table('inventory_laptops')->where('id', $id)->first();
        if(!$laptop) return abort(404);
        
        $users = DB::table('users')->orderBy('name')->get();
        return view('Room.inventory.serah-terima', compact('laptop', 'users'));
    }

    public function assignProcess(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'digital_signature' => 'required|string',
            'admin_signature' => 'required|string'
        ]);

        // Simpan digital_signature ke dalam kolom current_signature dan admin_signature
        DB::table('inventory_laptops')->where('id', $id)->update([
            'assigned_user_id' => $request->user_id,
            'status' => 'In Use',
            'current_signature' => $request->digital_signature, 
            'admin_signature' => $request->admin_signature,
            'updated_at' => now()
        ]);

        $user = DB::table('users')->where('id', $request->user_id)->first();

        DB::table('inventory_logs')->insert([
            'laptop_id' => $id,
            'action_type' => 'Assigned',
            'notes' => "Diserahkan kepada {$user->name}. (Tanda Tangan Terlampir)",
            'performed_by' => auth()->id(),
            'created_at' => now()
        ]);

        return redirect()->route('inventory.master')->with('success', "Laptop berhasil diserahkan kepada {$user->name}");
    }

    public function returnLaptop($id)
    {
        $laptop = DB::table('inventory_laptops')->where('id', $id)->first();

        DB::table('inventory_laptops')->where('id', $id)->update([
            'assigned_user_id' => null,
            'status' => 'Available',
            'location' => 'Gudang IT',
            'current_signature' => null, // Reset signature user
            'admin_signature' => null, // Reset signature admin
            'updated_at' => now()
        ]);

        DB::table('inventory_logs')->insert([
            'laptop_id' => $id,
            'action_type' => 'Returned',
            'notes' => 'Dikembalikan ke gudang.',
            'performed_by' => auth()->id(),
            'created_at' => now()
        ]);

        return back()->with('success', 'Laptop berhasil dikembalikan ke Gudang IT.');
    }

    public function printBeritaAcara($id)
    {
        $laptop = DB::table('inventory_laptops')
            ->leftJoin('users', 'inventory_laptops.assigned_user_id', '=', 'users.id')
            ->select('inventory_laptops.*', 'users.name as assigned_user_name', 'users.email as assigned_user_email')
            ->where('inventory_laptops.id', $id)
            ->first();

        if (!$laptop || $laptop->status !== 'In Use') {
            return abort(404, 'Berita Acara tidak tersedia (Laptop tidak dalam status In Use).');
        }

        return view('Room.inventory.print', compact('laptop'));
    }

    public function history($id)
    {
        $laptop = DB::table('inventory_laptops')->where('id', $id)->first();
        if (!$laptop) return abort(404);

        $logs = DB::table('inventory_logs')
            ->leftJoin('users', 'inventory_logs.performed_by', '=', 'users.id')
            ->select('inventory_logs.*', 'users.name as admin_name')
            ->where('laptop_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('Room.inventory.history', compact('laptop', 'logs'));
    }

    // ==========================================
    // 🔍 AUDIT & SCANNER
    // ==========================================
    public function auditScanner()
    {
        return view('Room.inventory.scanner');
    }

    public function auditProcess(Request $request)
    {
        $barcode = $request->barcode;
        $laptop = DB::table('inventory_laptops')->where('barcode', $barcode)->orWhere('asset_code', $barcode)->first();

        if (!$laptop) {
            return response()->json(['success' => false, 'message' => 'Laptop tidak ditemukan dalam sistem.']);
        }

        DB::table('inventory_logs')->insert([
            'laptop_id' => $laptop->id,
            'action_type' => 'Audit',
            'notes' => "Audit fisik dilakukan. Barang ditemukan di lokasi aktual.",
            'performed_by' => auth()->id(),
            'created_at' => now()
        ]);

        return response()->json([
            'success' => true, 
            'message' => 'Audit berhasil dicatat.', 
            'laptop' => [
                'asset_code' => $laptop->asset_code,
                'brand_model' => $laptop->brand_model,
                'status' => $laptop->status
            ]
        ]);
    }

    // ==========================================
    // 🛠️ MAINTENANCE TICKETS (SERVIS)
    // ==========================================
    public function tickets()
    {
        $tickets = DB::table('inventory_tickets')
            ->join('inventory_laptops', 'inventory_tickets.laptop_id', '=', 'inventory_laptops.id')
            ->select('inventory_tickets.*', 'inventory_laptops.asset_code', 'inventory_laptops.brand_model', 'inventory_laptops.status as laptop_status')
            ->orderBy('inventory_tickets.created_at', 'desc')
            ->paginate(15);
            
        $laptops = DB::table('inventory_laptops')->where('status', '!=', 'Disposed')->get();
        
        return view('Room.inventory.tickets', compact('tickets', 'laptops'));
    }

    public function storeTicket(Request $request)
    {
        $request->validate([
            'laptop_id' => 'required|exists:inventory_laptops,id',
            'issue_description' => 'required|string'
        ]);

        DB::table('inventory_tickets')->insert([
            'laptop_id' => $request->laptop_id,
            'issue_description' => $request->issue_description,
            'status' => 'Open',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Opsional: Otomatis ubah status laptop menjadi "Maintenance"
        if ($request->has('set_maintenance')) {
            DB::table('inventory_laptops')->where('id', $request->laptop_id)->update([
                'status' => 'Maintenance',
                'updated_at' => now()
            ]);

            DB::table('inventory_logs')->insert([
                'laptop_id' => $request->laptop_id,
                'action_type' => 'Maintenance',
                'notes' => 'Laptop masuk masa servis/maintenance akibat laporan: ' . $request->issue_description,
                'performed_by' => auth()->id(),
                'created_at' => now()
            ]);
        }

        return back()->with('success', 'Tiket perbaikan berhasil dibuat.');
    }

    public function updateTicket(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Open,In Progress,Resolved'
        ]);

        $ticket = DB::table('inventory_tickets')->where('id', $id)->first();
        if(!$ticket) return abort(404);

        DB::table('inventory_tickets')->where('id', $id)->update([
            'status' => $request->status,
            'updated_at' => now()
        ]);

        // Jika resolved, kembalikan status laptop ke Available jika sebelumnya Maintenance
        if ($request->status === 'Resolved' && $request->has('return_available')) {
            DB::table('inventory_laptops')->where('id', $ticket->laptop_id)->update([
                'status' => 'Available',
                'updated_at' => now()
            ]);

            DB::table('inventory_logs')->insert([
                'laptop_id' => $ticket->laptop_id,
                'action_type' => 'Maintenance',
                'notes' => 'Servis selesai, laptop kembali Available.',
                'performed_by' => auth()->id(),
                'created_at' => now()
            ]);
        }

        return back()->with('success', 'Status tiket berhasil diperbarui.');
    }

    public function destroyTicket($id)
    {
        DB::table('inventory_tickets')->where('id', $id)->delete();
        return back()->with('success', 'Tiket berhasil dihapus.');
    }

    // ==========================================
    // 🗑️ DISPOSAL (PEMUSNAHAN ASET)
    // ==========================================
    public function disposals()
    {
        $disposals = DB::table('inventory_disposals')
            ->join('inventory_laptops', 'inventory_disposals.laptop_id', '=', 'inventory_laptops.id')
            ->join('users', 'inventory_disposals.requested_by', '=', 'users.id')
            ->select('inventory_disposals.*', 'inventory_laptops.asset_code', 'inventory_laptops.brand_model', 'users.name as requester_name')
            ->orderByRaw("FIELD(inventory_disposals.status, 'Pending', 'Approved', 'Rejected')")
            ->orderBy('inventory_disposals.created_at', 'desc')
            ->get();

        return view('Room.inventory.disposals', compact('disposals'));
    }

    public function requestDisposal(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $laptop = DB::table('inventory_laptops')->where('id', $id)->first();
        if (!$laptop || $laptop->status === 'In Use') {
            return back()->withErrors(['msg' => 'Laptop sedang digunakan. Tarik kembali ke Gudang IT terlebih dahulu.']);
        }

        DB::table('inventory_disposals')->insert([
            'laptop_id' => $id,
            'requested_by' => auth()->id(),
            'reason' => $request->reason,
            'status' => 'Pending',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('inventory_logs')->insert([
            'laptop_id' => $id,
            'action_type' => 'Disposal Requested',
            'notes' => 'Pengajuan pemusnahan aset: ' . $request->reason,
            'performed_by' => auth()->id(),
            'created_at' => now()
        ]);

        return back()->with('success', 'Pengajuan disposal berhasil dikirim. Menunggu persetujuan.');
    }

    public function processDisposal(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:Approve,Reject',
            'notes' => 'nullable|string'
        ]);

        $disposal = DB::table('inventory_disposals')->where('id', $id)->first();
        if (!$disposal) return back()->withErrors(['msg' => 'Data pengajuan tidak ditemukan.']);

        if ($request->action === 'Approve') {
            // Update Disposal
            DB::table('inventory_disposals')->where('id', $id)->update([
                'status' => 'Approved',
                'approved_by' => auth()->id(),
                'approval_notes' => $request->notes,
                'updated_at' => now()
            ]);

            // Update Laptop Status
            DB::table('inventory_laptops')->where('id', $disposal->laptop_id)->update([
                'status' => 'Disposed',
                'location' => 'Disposed (Dimusnahkan)',
                'updated_at' => now()
            ]);

            // Add Log
            DB::table('inventory_logs')->insert([
                'laptop_id' => $disposal->laptop_id,
                'action_type' => 'Disposed',
                'notes' => 'Pengajuan pemusnahan disetujui. Catatan: ' . $request->notes,
                'performed_by' => auth()->id(),
                'created_at' => now()
            ]);

            return back()->with('success', 'Pengajuan pemusnahan disetujui. Aset telah di-Disposed.');
        } else {
            // Reject
            DB::table('inventory_disposals')->where('id', $id)->update([
                'status' => 'Rejected',
                'approved_by' => auth()->id(),
                'approval_notes' => $request->notes,
                'updated_at' => now()
            ]);

            // Add Log
            DB::table('inventory_logs')->insert([
                'laptop_id' => $disposal->laptop_id,
                'action_type' => 'Disposal Rejected',
                'notes' => 'Pengajuan pemusnahan ditolak. Catatan: ' . $request->notes,
                'performed_by' => auth()->id(),
                'created_at' => now()
            ]);

            return back()->with('success', 'Pengajuan pemusnahan ditolak.');
        }
    }
}
