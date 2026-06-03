<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Jobs\SyncBahanJob;
use App\Services\BahanService;
use Illuminate\Support\Facades\Log;
use App\Jobs\SyncCustomerJob;
use App\Jobs\SyncSupplierJob;
use App\Jobs\SyncLocationJob;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StockMovementExport;


class SCMController extends Controller
{
    public function indexSCM()
    {
        $user = Auth::user();

        // Pastikan user login dan rolenya benar
        if (!$user || $user->role !== 'admindc') {
            abort(403, 'Hanya Admin DC yang dapat mengakses halaman ini.');
        }

        // Ambil ID Warehouse dari user login sebagai patokan utama
        $id = $user->warehouse_id;

        if (empty($id)) {
            abort(403, 'User Anda belum dikaitkan dengan Warehouse manapun.');
        }

        // 1. Ambil info warehouse
        $warehouse = DB::table('tbl_warehouse')
            ->select('id', 'nama_warehouse')
            ->where('id', $id)
            ->first();

        // 2. Query Produk dan Total Keluar 7 Hari
        $products = DB::table('tbl_bahan_scm as b')
            ->leftJoin('tbl_stok_transaksi as t', function ($join) use ($id) {
                $join->on('b.id', '=', 't.bahan_id')
                    ->where('t.warehouse_id', '=', $id)
                    ->where('t.tipe', '=', 'keluar')
                    ->where('t.created_at', '>=', now()->subDays(7));
            })
            ->leftJoin('tbl_bahan_unit as bu', function ($join) {
                $join->on('b.id', '=', 'bu.bahan_id')->where('bu.is_base_unit', 1);
            })
            ->leftJoin('tbl_units as u', 'bu.unit_id', '=', 'u.id')
            ->select(
                'b.id',
                'b.nama_bahan',
                'b.stok_minimal',
                'b.lead_time',
                'b.rop_level',
                'u.nama_unit',
                DB::raw('ABS(SUM(IFNULL(t.jumlah, 0))) as total_keluar_7_hari')
            )
            ->groupBy('b.id', 'b.nama_bahan', 'b.stok_minimal', 'b.lead_time', 'b.rop_level', 'u.nama_unit')
            ->get();

        // 3. Ambil Stok Aktual di DC tersebut
        $stokAktual = DB::table('tbl_stok_transaksi')
            ->where('warehouse_id', $id)
            ->select('bahan_id', DB::raw('SUM(jumlah) as total'))
            ->groupBy('bahan_id')
            ->pluck('total', 'bahan_id');

        // 4. Ambil daftar outlet yang mapping ke DC ini
        $outlets = DB::table('tbl_mapping_dc')
            ->join('tbl_outlets', 'tbl_mapping_dc.outlet_id', '=', 'tbl_outlets.id')
            ->where('tbl_mapping_dc.warehouse_id', $id)
            ->select('tbl_outlets.nama_outlet', 'tbl_outlets.alamat')
            ->get();

        // Perhatikan kurung tutup di compact()
        return view('Purchasing.SCM.dashboardAdminDC', compact('user', 'warehouse', 'products', 'outlets', 'stokAktual'));
    }

    public function userSCM()
    {
        $data = DB::table('users as u')
            // Ambil nama DC dari tbl_warehouse berdasarkan warehouse_id user
            ->leftJoin('tbl_warehouse as w', 'u.warehouse_id', '=', 'w.id')
            ->select(
                'u.id',
                'u.name',
                'u.email',
                'u.role',
                'w.nama_warehouse',
                'u.created_at'
            )
            ->whereIn('u.role', ['admindc'])
            ->orderBy('u.name', 'asc')
            ->get();

        $outlets = DB::table('tbl_outlets')
            ->select('id', 'nama_outlet')
            ->orderBy('nama_outlet', 'asc')
            ->get();

        $warehouse = DB::table('tbl_warehouse')
            ->select('id', 'nama_warehouse')
            ->orderBy('nama_warehouse', 'asc')
            ->get();

        return view('Purchasing.SCM.userAccountSCM', compact('data', 'outlets', 'warehouse'));
    }

    public function storeUserSCM(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:admindc',
            'warehouse_id' => 'required', // Pastikan alokasi gudang terisi
        ]);

        DB::beginTransaction();
        try {
            DB::table('users')->insert([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'outlet_id' => null,
                'warehouse_id' => $request->warehouse_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            return redirect()->route('user.account.scm')
                ->with('success', 'User SCM berhasil didaftarkan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('user.account.scm')
                ->with('error', 'Gagal menambahkan user SCM: ' . $e->getMessage());
        }
    }

    public function updateUserSCM(Request $request, $id)
    {
        // FIX VALIDASI: Email mengecualikan ID user ini, password dibuat nullable (opsional)
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6',
            'role' => 'required|in:admindc',
            'warehouse_id' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $payload = [
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role,
                'outlet_id' => null,
                'warehouse_id' => $request->warehouse_id,
                'updated_at' => now(),
            ];

            // Masukkan password ke payload HANYA jika kolomnya diisi oleh admin
            if ($request->filled('password')) {
                $payload['password'] = Hash::make($request->password);
            }

            DB::table('users')->where('id', $id)->update($payload);

            DB::commit();
            return redirect()->route('user.account.scm')
                ->with('success', 'Akun user SCM berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('user.account.scm')
                ->with('error', 'Gagal update user SCM: ' . $e->getMessage());
        }
    }

    public function destroyUserSCM($id)
    {
        $user = DB::table('users')->where('id', $id)->first();

        if (!$user) {
            return redirect()->route('user.account.scm')
                ->with('error', 'User tidak ditemukan.');
        }

        // FIX VALIDASI ROLE: Dipastikan mendeteksi role admindc agar tidak memicu salah blokir
        if (!in_array($user->role, ['admindc'])) {
            return redirect()->route('user.account.scm')
                ->with('error', 'User ini bukan bagian dari otoritas operasional SCM.');
        }

        DB::table('users')->where('id', $id)->delete();

        return redirect()->route('user.account.scm')
            ->with('success', 'User SCM berhasil dihapus secara permanen.');
    }

    // ── PRINT SALES INVOICE ──────────────────────────────────────
    public function printSalesInvoice($id)
    {
        $si = DB::table('tbl_sales_invoices as si')
            ->join('tbl_goods_deliveries as gd', 'si.goods_delivery_id', '=', 'gd.id')
            ->join('tbl_sales_orders as so', 'gd.sales_order_id', '=', 'so.id')
            ->join('tbl_outlets as o', 'si.customer_id', '=', 'o.id')
            ->where('si.id', $id)
            ->select(
                'si.*',
                'gd.gd_number',
                'so.so_number',
                'o.nama_outlet as customer_name',
                'o.alamat as customer_address'
            )
            ->first();

        if (!$si) {
            abort(404, 'Sales Invoice tidak ditemukan.');
        }

        $details = DB::table('tbl_sales_invoice_details as sid')
            ->join('tbl_bahan_scm as b', 'sid.product_id', '=', 'b.id')
            ->leftJoin('tbl_units as u', 'sid.unit_id', '=', 'u.id')
            ->where('sid.sales_invoice_id', $id)
            ->select('sid.*', 'b.nama_bahan', 'u.nama_unit')
            ->get();

        // Render halaman print tanpa layout header/footer aplikasi
        return view('Purchasing.SCM.printSalesInvoice', compact('si', 'details'));
    }

    // ── PRINT PURCHASE INVOICE ───────────────────────────────────
    public function printPurchaseInvoice($id)
    {
        $pi = DB::table('tbl_purchase_invoices as pi')
            ->join('tbl_suppliers as s', 'pi.supplier_id', '=', 's.id')
            ->join('tbl_goods_receipts as gr', 'pi.goods_receipt_id', '=', 'gr.id')
            ->join('tbl_purchase_orders as po', 'gr.purchase_order_id', '=', 'po.id')
            ->where('pi.id', $id)
            ->select(
                'pi.*',
                's.supplier_name',
                'gr.gr_number',
                'po.po_number'
            )
            ->first();

        if (!$pi) {
            abort(404, 'Purchase Invoice tidak ditemukan.');
        }

        $details = DB::table('tbl_purchase_invoice_details as pid')
            ->join('tbl_bahan_scm as b', 'pid.product_id', '=', 'b.id')
            ->leftJoin('tbl_units as u', 'pid.unit_id', '=', 'u.id')
            ->where('pid.purchase_invoice_id', $id)
            ->select('pid.*', 'b.nama_bahan', 'u.nama_unit')
            ->get();

        return view('Purchasing.SCM.printPurchaseInvoice', compact('pi', 'details'));
    }

    public function printGoodsDelivery($id)
    {
        $gd = DB::table('tbl_goods_deliveries as gd')
            ->join('tbl_sales_orders as so', 'gd.sales_order_id', '=', 'so.id')
            ->join('tbl_outlets as o', 'gd.customer_id', '=', 'o.id')
            ->leftJoin('tbl_warehouse as w', 'gd.warehouse_id', '=', 'w.id')
            ->where('gd.id', $id)
            ->select(
                'gd.*',
                'so.so_number',
                'o.nama_outlet as customer_name',
                'o.alamat as customer_address',
                'w.nama_warehouse as warehouse_name'
            )
            ->first();

        if (!$gd) {
            abort(404, 'Goods Delivery tidak ditemukan.');
        }

        $details = DB::table('tbl_goods_delivery_details as gdd')
            ->join('tbl_bahan_scm as b', 'gdd.product_id', '=', 'b.id')
            ->leftJoin('tbl_units as u', 'gdd.unit_id', '=', 'u.id')
            ->where('gdd.goods_delivery_id', $id)
            ->select('gdd.*', 'b.nama_bahan', 'u.nama_unit')
            ->get();

        return view('Purchasing.SCM.printGoodsDelivery', compact('gd', 'details'));
    }

    // ── PRINT GOODS RECEIPT ──────────────────────────────────────
    public function printGoodsReceipt($id)
    {
        $gr = DB::table('tbl_goods_receipts as gr')
            ->join('tbl_purchase_orders as po', 'gr.purchase_order_id', '=', 'po.id')
            ->join('tbl_suppliers as s', 'gr.supplier_id', '=', 's.id')
            ->leftJoin('tbl_warehouse as w', 'gr.warehouse_id', '=', 'w.id')
            ->where('gr.id', $id)
            ->select(
                'gr.*',
                'po.po_number',
                's.supplier_name',
                'w.nama_warehouse as warehouse_name'
            )
            ->first();

        if (!$gr) {
            abort(404, 'Goods Receipt tidak ditemukan.');
        }

        $details = DB::table('tbl_goods_receipt_details as grd')
            ->join('tbl_bahan_scm as b', 'grd.product_id', '=', 'b.id')
            ->leftJoin('tbl_units as u', 'grd.unit_id', '=', 'u.id')
            ->where('grd.goods_receipt_id', $id)
            ->select(
                'grd.*',
                'b.nama_bahan',
                'u.nama_unit'
            )
            ->get();

        return view('Purchasing.SCM.printGoodsReceipt', compact('gr', 'details'));
    }

    // === PURCHASE ORDER ===
    public function indexPurchaseOrder(Request $request)
    {
        // 1. Filter Tanggal
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // 2. Query Utama untuk Tabel List PO
        $query = DB::table('tbl_purchase_orders')
            ->join('tbl_suppliers', 'tbl_purchase_orders.supplier_id', '=', 'tbl_suppliers.id')
            ->select(
                'tbl_purchase_orders.*',
                'tbl_suppliers.supplier_name'
            );

        if ($startDate && $endDate) {
            $query->whereBetween('tbl_purchase_orders.request_date', [$startDate, $endDate]);
        }

        $purchase_orders = $query->orderBy('tbl_purchase_orders.created_at', 'desc')->get();

        // 3. Data Master untuk Modal Create PO

        // Ambil semua branch (Outlets)
        $branches = DB::table('tbl_warehouse as w')
            ->join('tbl_outlets as o', 'w.branch_id', '=', 'o.id')
            ->select(
                'w.id',                  // ID Warehouse/DC untuk value form select
                'o.nama_outlet',         // Nama DC yang diambil dari master outlet
                'o.credential_id'        // Tetap aman membawa credential_id asli milik outlet DC tersebut
            )
            ->orderBy('o.nama_outlet', 'asc')
            ->get();

        // Untuk Supplier, kita bisa ambil semua dulu atau biarkan kosong 
        // agar diisi oleh AJAX setelah user pilih Branch. 
        // Di sini saya ambil semua sebagai default awal.
        $suppliers = DB::table('tbl_suppliers')->select('id', 'supplier_name', 'credential_id')->get();

        // Ambil data bahan baku SCM
        // Ambil data bahan baku lengkap dengan satuan pembeliannya
        $products = DB::table('tbl_bahan_scm')
            ->join('tbl_bahan_unit', 'tbl_bahan_scm.id', '=', 'tbl_bahan_unit.bahan_id')
            ->join('tbl_units', 'tbl_bahan_unit.unit_id', '=', 'tbl_units.id')
            ->where('tbl_bahan_unit.is_purchase_unit', 1) // KUNCI UTAMA: Hanya ambil satuan beli
            ->select(
                'tbl_bahan_scm.id',
                'tbl_bahan_scm.nama_bahan',
                'tbl_bahan_scm.product_code',
                'tbl_bahan_scm.stok_minimal',
                'tbl_bahan_unit.unit_id',
                'tbl_bahan_unit.base_price', // Gunakan base_price sebagai harga awal
                'tbl_units.nama_unit as satuan'
            )
            ->get();

        // 4. Kirim ke View
        return view('Purchasing.SCM.purchaseOrderList', compact(
            'purchase_orders',
            'suppliers',
            'branches',
            'products'
        ));
    }

    public function getSuppliersByBranch($outletId)
    {
        // Ambil credential_id dari outlet terpilih
        $outlet = DB::table('tbl_outlets')->where('id', $outletId)->first();

        if (!$outlet) {
            return response()->json([]);
        }

        // Ambil supplier yang credential_id nya sama dengan outlet tersebut
        $suppliers = DB::table('tbl_suppliers')
            ->where('credential_id', $outlet->credential_id)
            ->select('id', 'supplier_name')
            ->get();

        return response()->json($suppliers);
    }

    public function storePurchaseOrder(Request $request)
    {
        // 1. Validasi Input
        $validator = Validator::make($request->all(), [
            'branch_id' => 'required',
            'supplier_id' => 'required',
            'request_date' => 'required|date',
            'required_date' => 'required|date',
            'items' => 'required|array|min:1', // Pastikan ada produk
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        // Jalankan Transaction
        DB::beginTransaction();

        try {
            // 2. Generate Nomor PO Otomatis (Contoh: PO-20231027-0001)
            // $poNumber = 'PO' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $today = Carbon::now()->format('Ymd'); // Hasil: 20260513

            // 2. Cari nomor PO terakhir di database pada hari ini
            // Ganti 'purchase_orders' dengan nama tabel kamu yang sebenarnya
            $lastPo = DB::table('tbl_purchase_orders')
                ->where('po_number', 'LIKE', 'PO' . $today . '%')
                ->orderBy('po_number', 'desc')
                ->lockForUpdate() // Cegah race condition saat 2 user simpan PO bersamaan
                ->value('po_number');

            if ($lastPo) {
                // Ambil 4 digit terakhir, tambah 1, lalu balikin ke format 0000
                $lastNumber = substr($lastPo, -4);
                $nextNumber = str_pad((int) $lastNumber + 1, 4, '0', STR_PAD_LEFT);
            } else {
                // Jika ganti tanggal atau data kosong, reset ke 0001
                $nextNumber = '0001';
            }

            // 3. Gabungkan jadi hasil akhir
            $poNumber = 'PO' . $today . $nextNumber;

            // 3. Hitung Kalkulasi (Pastikan angka bersih dari format ribuan/currency jika ada)
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += ($item['po_qty'] * $item['price']);
            }

            $discount = 0; // Bisa dikembangkan jika ada input discount
            $dpp = $subtotal - $discount;
            $vat = $request->has('apply_vat') ? ($dpp * 0.11) : 0;
            $total = $dpp + $vat;

            // 4. Simpan Header ke purchase_orders
            $poId = DB::table('tbl_purchase_orders')->insertGetId([
                'po_number' => $poNumber,
                'branch_id' => $request->branch_id,
                'request_date' => $request->request_date,
                'required_date' => $request->required_date,
                'supplier_id' => $request->supplier_id,
                'currency' => $request->currency,
                'rate' => $request->rate ?? 1,
                'request_number' => $request->request_number,
                'notes' => $request->notes,
                'footnote' => $request->footnote,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'vat_amount' => $vat,
                'total_amount' => $total,
                'status' => 'PENDING',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 5. Simpan Detail ke purchase_order_details
            foreach ($request->items as $item) {
                DB::table('tbl_purchase_order_details')->insert([
                    'purchase_order_id' => $poId,
                    'product_id' => $item['product_id'],
                    'po_qty' => $item['po_qty'],
                    'price' => $item['price'],
                    'subtotal' => $item['po_qty'] * $item['price'],
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Purchase Order ' . $poNumber . ' berhasil dibuat!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function showPurchaseOrder($id)
    {
        $po = DB::table('tbl_purchase_orders as po')
            ->join('tbl_suppliers as s', 'po.supplier_id', '=', 's.id')
            ->leftJoin('tbl_outlets as o', 'po.branch_id', '=', 'o.id')
            ->where('po.id', $id)
            ->select(
                'po.*',
                's.supplier_name',
                'o.nama_outlet as branch_name'
            )
            ->first();

        if (!$po) {
            return response()->json([
                'status' => 'error',
                'message' => 'Purchase Order tidak ditemukan.',
            ], 404);
        }

        $details = DB::table('tbl_purchase_order_details as d')
            ->join('tbl_bahan_scm as b', 'd.product_id', '=', 'b.id')
            ->leftJoin('tbl_bahan_unit as bu', function ($join) {
                $join->on('b.id', '=', 'bu.bahan_id')->where('bu.is_purchase_unit', 1);
            })
            ->leftJoin('tbl_units as u', 'bu.unit_id', '=', 'u.id')
            ->where('d.purchase_order_id', $id)
            ->select(
                'd.*',
                'b.nama_bahan',
                'b.product_code',
                'bu.unit_id',
                'u.nama_unit'
            )
            ->get();

        return response()->json([
            'status' => 'success',
            'po' => $po,
            'details' => $details,
        ]);
    }

    public function editPurchaseOrder($id)
    {
        $po = DB::table('tbl_purchase_orders as po')
            ->join('tbl_suppliers as s', 'po.supplier_id', '=', 's.id')
            ->leftJoin('tbl_outlets as o', 'po.branch_id', '=', 'o.id')
            ->where('po.id', $id)
            ->select('po.*', 's.supplier_name', 'o.nama_outlet as branch_name')
            ->first();

        if (!$po) {
            return response()->json([
                'status' => 'error',
                'message' => 'Purchase Order tidak ditemukan.',
            ], 404);
        }

        // Hanya PO berstatus PENDING yang boleh diedit
        if ($po->status !== 'PENDING') {
            return response()->json([
                'status' => 'error',
                'message' => "PO dengan status '{$po->status}' tidak dapat diedit.",
            ], 422);
        }

        $details = DB::table('tbl_purchase_order_details as d')
            ->join('tbl_bahan_scm as b', 'd.product_id', '=', 'b.id')
            ->leftJoin('tbl_bahan_unit as bu', function ($join) {
                $join->on('b.id', '=', 'bu.bahan_id')->where('bu.is_purchase_unit', 1);
            })
            ->leftJoin('tbl_units as u', 'bu.unit_id', '=', 'u.id')
            ->where('d.purchase_order_id', $id)
            ->select(
                'd.*',
                'b.nama_bahan',
                'b.product_code',
                'bu.unit_id',
                'u.nama_unit'
            )
            ->get();

        return response()->json([
            'status' => 'success',
            'po' => $po,
            'details' => $details,
        ]);
    }

    public function updatePurchaseOrder(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'required',
            'supplier_id' => 'required',
            'request_date' => 'required|date',
            'required_date' => 'required|date',
            'items' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $po = DB::table('tbl_purchase_orders')->where('id', $id)->first();

        if (!$po) {
            return response()->json([
                'status' => 'error',
                'message' => 'Purchase Order tidak ditemukan.',
            ], 404);
        }

        if ($po->status !== 'PENDING') {
            return response()->json([
                'status' => 'error',
                'message' => "PO dengan status '{$po->status}' tidak dapat diubah.",
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Hitung ulang total
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += ($item['po_qty'] * $item['price']);
            }

            $discount = 0;
            $dpp = $subtotal - $discount;
            $vat = $request->has('apply_vat') ? ($dpp * 0.11) : 0;
            $total = $dpp + $vat;

            // Update header PO
            DB::table('tbl_purchase_orders')->where('id', $id)->update([
                'branch_id' => $request->branch_id,
                'supplier_id' => $request->supplier_id,
                'request_date' => $request->request_date,
                'required_date' => $request->required_date,
                'currency' => $request->currency ?? 'IDR',
                'rate' => $request->rate ?? 1,
                'request_number' => $request->request_number,
                'notes' => $request->notes,
                'footnote' => $request->footnote,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'vat_amount' => $vat,
                'total_amount' => $total,
                'updated_at' => now(),
            ]);

            // Hapus semua detail lama lalu insert ulang
            DB::table('tbl_purchase_order_details')
                ->where('purchase_order_id', $id)
                ->delete();

            foreach ($request->items as $item) {
                DB::table('tbl_purchase_order_details')->insert([
                    'purchase_order_id' => $id,
                    'product_id' => $item['product_id'],
                    'po_qty' => $item['po_qty'],
                    'price' => $item['price'],
                    'subtotal' => $item['po_qty'] * $item['price'],
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Purchase Order {$po->po_number} berhasil diperbarui.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PO update failed: ' . $e->getMessage(), ['po_id' => $id]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui PO: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroyPurchaseOrder($id)
    {
        $po = DB::table('tbl_purchase_orders')->where('id', $id)->first();

        if (!$po) {
            return response()->json([
                'status' => 'error',
                'message' => 'Purchase Order tidak ditemukan.',
            ], 404);
        }

        // Hanya PO PENDING yang boleh dihapus
        if ($po->status !== 'PENDING') {
            return response()->json([
                'status' => 'error',
                'message' => "PO dengan status '{$po->status}' tidak dapat dihapus.",
            ], 422);
        }

        DB::beginTransaction();

        try {
            DB::table('tbl_purchase_order_details')
                ->where('purchase_order_id', $id)
                ->delete();

            DB::table('tbl_purchase_orders')
                ->where('id', $id)
                ->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Purchase Order {$po->po_number} berhasil dihapus.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PO delete failed: ' . $e->getMessage(), ['po_id' => $id]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus PO: ' . $e->getMessage(),
            ], 500);
        }
    }


    // === SALES ORDER ===
    public function indexSalesOrder()
    {
        // 1. Ambil data Header Sales Order untuk Tabel Utama
        $sales_orders = DB::table('tbl_sales_orders')
            ->join('tbl_outlets', 'tbl_sales_orders.customer_id', '=', 'tbl_outlets.id')
            ->select('tbl_sales_orders.*', 'tbl_outlets.nama_outlet as customer_name')
            ->orderBy('tbl_sales_orders.created_at', 'desc')
            ->get();

        $branches = DB::table('tbl_outlets')->select('id', 'nama_outlet', 'credential_id')->get();

        // 2. Data untuk Modal: List Customer (diambil dari tbl_outlets agar konsisten
        //    dengan query list yang join ke tbl_outlets.id = customer_id)
        // FIX: Sebelumnya pakai tbl_customers.customerID tapi list query join ke tbl_outlets.id
        //      sehingga customer_name selalu null di tabel. Sekarang konsisten pakai tbl_outlets.
        $customers = DB::table('tbl_outlets')->select('id as customerID', 'nama_outlet as customerName')->get();

        // 3. Data untuk Modal: List Sales Representative
        // Ganti 'tbl_users' sesuai tabel karyawan kamu
        // $sales_reps = DB::table('tbl_users')
        //     ->where('role', 'sales')
        //     ->select('id', 'name')
        //     ->get();

        // 4. Data untuk Modal: Produk/Bahan Baku (Sama seperti logic PO kamu)
        $products = DB::table('tbl_bahan_scm')
            ->join('tbl_bahan_unit', 'tbl_bahan_scm.id', '=', 'tbl_bahan_unit.bahan_id')
            ->join('tbl_units', 'tbl_bahan_unit.unit_id', '=', 'tbl_units.id')
            ->where('tbl_bahan_unit.is_purchase_unit', 1)
            ->select(
                'tbl_bahan_scm.id',
                'tbl_bahan_scm.nama_bahan',
                'tbl_bahan_scm.product_code',
                'tbl_bahan_scm.stok_minimal',
                'tbl_bahan_unit.unit_id',
                'tbl_bahan_unit.base_price',
                'tbl_units.nama_unit as satuan'
            )
            ->get();

        return view('Purchasing.SCM.salesOrderList', compact('sales_orders', 'customers', 'products', 'branches'));
    }

    public function storeSalesOrder(Request $request)
    {
        // 1. Validasi Input
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required',
            'sales_date' => 'required|date',
            'required_date' => 'required|date',
            'items' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        DB::beginTransaction();

        try {
            // 2. Generate Nomor SL (Contoh: SL202605130001)
            $today = Carbon::now()->format('Ymd');
            $lastSo = DB::table('tbl_sales_orders')
                ->where('so_number', 'LIKE', 'SL' . $today . '%')
                ->orderBy('so_number', 'desc')
                ->lockForUpdate()
                ->value('so_number');

            if ($lastSo) {
                $lastNumber = substr($lastSo, -4);
                $nextNumber = str_pad((int) $lastNumber + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $nextNumber = '0001';
            }

            $soNumber = 'SL' . $today . $nextNumber;

            // 3. Hitung Total (Sama seperti logic PO)
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += ($item['qty'] * $item['price']);
            }

            $total = $subtotal; // Tambahkan logic VAT/Discount jika perlu

            // 4. Simpan Header SO
            // reference_number diisi Nomor PO jika otomatis, atau manual input
            $soId = DB::table('tbl_sales_orders')->insertGetId([
                'so_number' => $soNumber,
                'po_id' => $request->po_id ?? null,
                'branch_id' => $request->branch_id ?? null, // FIX: branch_id dari form
                'customer_id' => $request->customer_id,
                'sales_date' => $request->sales_date,
                'required_date' => $request->required_date,
                'currency' => $request->currency ?? 'IDR',
                'rate' => $request->rate ?? 1,
                'reference_number' => $request->reference_number,
                'address' => $request->address,
                'total_amount' => $total,
                'status' => 'NEW',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 5. Simpan Detail SO
            foreach ($request->items as $item) {
                DB::table('tbl_sales_order_details')->insert([
                    'sales_order_id' => $soId,
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'subtotal' => $item['qty'] * $item['price'],
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Sales Order ' . $soNumber . ' berhasil dibuat!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal simpan SO: ' . $e->getMessage()
            ], 500);
        }
    }

    public function showSalesOrder($id)
    {
        $so = DB::table('tbl_sales_orders as so')
            // FIX: join ke tbl_outlets (bukan tbl_customers) agar konsisten
            ->leftJoin('tbl_outlets as c', 'so.customer_id', '=', 'c.id')
            ->leftJoin('tbl_outlets as o', 'so.branch_id', '=', 'o.id')
            ->where('so.id', $id)
            ->select(
                'so.*',
                'c.nama_outlet as customer_name',
                'o.nama_outlet as branch_name'
            )
            ->first();

        if (!$so) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sales Order tidak ditemukan.',
            ], 404);
        }

        $details = DB::table('tbl_sales_order_details as d')
            ->join('tbl_bahan_scm as b', 'd.product_id', '=', 'b.id')
            ->leftJoin('tbl_units as u', 'd.unit_id', '=', 'u.id')
            ->where('d.sales_order_id', $id)
            ->select(
                'd.*',
                'b.nama_bahan',
                'b.product_code',
                'u.nama_unit'
            )
            ->get();

        return response()->json([
            'status' => 'success',
            'so' => $so,
            'details' => $details,
        ]);
    }

    public function editSalesOrder($id)
    {
        $so = DB::table('tbl_sales_orders as so')
            // FIX: join ke tbl_outlets (bukan tbl_customers) agar konsisten
            ->leftJoin('tbl_outlets as c', 'so.customer_id', '=', 'c.id')
            ->leftJoin('tbl_outlets as o', 'so.branch_id', '=', 'o.id')
            ->where('so.id', $id)
            ->select(
                'so.*',
                'c.nama_outlet as customer_name',
                'o.nama_outlet as branch_name'
            )
            ->first();

        if (!$so) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sales Order tidak ditemukan.',
            ], 404);
        }

        // Hanya SO berstatus NEW yang boleh diedit
        if ($so->status !== 'NEW') {
            return response()->json([
                'status' => 'error',
                'message' => "SO dengan status '{$so->status}' tidak dapat diedit.",
            ], 422);
        }

        $details = DB::table('tbl_sales_order_details as d')
            ->join('tbl_bahan_scm as b', 'd.product_id', '=', 'b.id')
            ->leftJoin('tbl_units as u', 'd.unit_id', '=', 'u.id')
            ->where('d.sales_order_id', $id)
            ->select(
                'd.*',
                'b.nama_bahan',
                'b.product_code',
                'u.nama_unit'
            )
            ->get();

        return response()->json([
            'status' => 'success',
            'so' => $so,
            'details' => $details,
        ]);
    }

    public function updateSalesOrder(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required',
            'sales_date' => 'required|date',
            'required_date' => 'required|date',
            'items' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $so = DB::table('tbl_sales_orders')->where('id', $id)->first();

        if (!$so) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sales Order tidak ditemukan.',
            ], 404);
        }

        if ($so->status !== 'NEW') {
            return response()->json([
                'status' => 'error',
                'message' => "SO dengan status '{$so->status}' tidak dapat diubah.",
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Hitung ulang total
            $total = 0;
            foreach ($request->items as $item) {
                $total += ($item['qty'] * $item['price']);
            }

            // Update header SO
            DB::table('tbl_sales_orders')->where('id', $id)->update([
                'customer_id' => $request->customer_id,
                'branch_id' => $request->branch_id ?? $so->branch_id,
                'sales_date' => $request->sales_date,
                'required_date' => $request->required_date,
                'currency' => $request->currency ?? 'IDR',
                'rate' => $request->rate ?? 1,
                'reference_number' => $request->reference_number,
                'address' => $request->address,
                'total_amount' => $total,
                'updated_at' => now(),
            ]);

            // Hapus detail lama lalu insert ulang
            DB::table('tbl_sales_order_details')
                ->where('sales_order_id', $id)
                ->delete();

            foreach ($request->items as $item) {
                DB::table('tbl_sales_order_details')->insert([
                    'sales_order_id' => $id,
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'subtotal' => $item['qty'] * $item['price'],
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Sales Order {$so->so_number} berhasil diperbarui.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SO update failed: ' . $e->getMessage(), ['so_id' => $id]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui SO: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroySalesOrder($id)
    {
        $so = DB::table('tbl_sales_orders')->where('id', $id)->first();

        if (!$so) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sales Order tidak ditemukan.',
            ], 404);
        }

        // Hanya SO berstatus NEW yang boleh dihapus
        if ($so->status !== 'NEW') {
            return response()->json([
                'status' => 'error',
                'message' => "SO dengan status '{$so->status}' tidak dapat dihapus.",
            ], 422);
        }

        DB::beginTransaction();

        try {
            DB::table('tbl_sales_order_details')
                ->where('sales_order_id', $id)
                ->delete();

            DB::table('tbl_sales_orders')
                ->where('id', $id)
                ->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Sales Order {$so->so_number} berhasil dihapus.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SO delete failed: ' . $e->getMessage(), ['so_id' => $id]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus SO: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function approveSalesOrder($id)
    {
        $so = DB::table('tbl_sales_orders')->where('id', $id)->first();

        if (!$so) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sales Order tidak ditemukan.',
            ], 404);
        }

        if ($so->status !== 'NEW') {
            return response()->json([
                'status' => 'error',
                'message' => "Hanya SO berstatus NEW yang bisa di-approve. Status sekarang: {$so->status}",
            ], 422);
        }

        DB::table('tbl_sales_orders')->where('id', $id)->update([
            'status' => 'AUTHORIZED',
            'updated_at' => now(),
        ]);

        Log::info("SO {$so->so_number} di-AUTHORIZE oleh user " . Auth::id());

        return response()->json([
            'status' => 'success',
            'message' => "Sales Order {$so->so_number} berhasil di-AUTHORIZE. Siap untuk Goods Delivery.",
        ]);
    }

    // === GOODS DELIVERY ===
    public function indexGoodsDelivery()
    {
        // Ambil semua GD + info SO + nama outlet (customer)
        $goodsDeliveries = DB::table('tbl_goods_deliveries as gd')
            ->join('tbl_sales_orders as so', 'gd.sales_order_id', '=', 'so.id')
            ->join('tbl_outlets as o', 'gd.customer_id', '=', 'o.id')
            ->select(
                'gd.id',
                'gd.gd_number',
                'gd.delivery_date',
                'gd.actual_arrival',
                'gd.status',
                'gd.total_amount',
                'gd.driver_name',
                'gd.vehicle_plate',
                'gd.delivery_address',
                'gd.created_at',
                'so.so_number',
                'o.nama_outlet as customer_name',
            )
            ->orderBy('gd.created_at', 'desc')
            ->get();

        // Hitung jumlah item per GD untuk kolom "Items"
        foreach ($goodsDeliveries as $gd) {
            $gd->item_count = DB::table('tbl_goods_delivery_details')
                ->where('goods_delivery_id', $gd->id)
                ->count();
        }

        // Ambil SO yang statusnya siap dikirim (NEW atau APPROVED)
        // Dipakai untuk dropdown di modal "Create Delivery"
        $readySOs = DB::table('tbl_sales_orders as so')
            ->join('tbl_outlets as o', 'so.customer_id', '=', 'o.id')
            ->whereIn('so.status', ['AUTHORIZED']) // Hanya SO yg sudah AUTHORIZED bisa dibuatkan GD
            ->select(
                'so.id',
                'so.so_number',
                'so.required_date',
                'so.total_amount',
                'so.customer_id',
                'so.address',
                'o.nama_outlet as customer_name',
            )
            ->orderBy('so.created_at', 'desc')
            ->get();

        // Widget ringkasan
        $summary = [
            'total' => DB::table('tbl_goods_deliveries')->count(),
            'draft' => DB::table('tbl_goods_deliveries')->where('status', 'DRAFT')->count(),
            'in_transit' => DB::table('tbl_goods_deliveries')->where('status', 'IN_TRANSIT')->count(),
            'delivered' => DB::table('tbl_goods_deliveries')->where('status', 'DELIVERED')->count(),
        ];

        $outletPOs = DB::table('tbl_po as p')
            ->join('tbl_outlets as o', 'p.outlet_id', '=', 'o.id')
            ->whereIn('p.status', ['Approved', 'In Transit', 'Processing'])
            ->select('p.id', 'p.no_po', 'p.status', 'o.nama_outlet')
            ->orderByDesc('p.created_at')
            ->get();

        // Surat jalan yang belum punya GD
        $suratJalans = DB::table('tbl_surat_jalan')
            ->whereNull('gd_id')
            ->whereIn('status', ['Packing', 'In Transit'])
            ->select('id', 'no_sj', 'driver_name', 'armada_nopol', 'status')
            ->orderByDesc('created_at')
            ->get();

        return view('Purchasing.SCM.goodsDelivery', compact(
            'goodsDeliveries',
            'readySOs',
            'summary',
            'outletPOs',
            'suratJalans'
        ));
    }

    public function getSoDetails($soId)
    {
        $so = DB::table('tbl_sales_orders as so')
            ->join('tbl_outlets as o', 'so.customer_id', '=', 'o.id')
            ->where('so.id', $soId)
            ->select(
                'so.*',
                'o.nama_outlet as customer_name',
                'o.alamat as customer_address',
            )
            ->first();

        if (!$so) {
            return response()->json(['status' => 'error', 'message' => 'SO tidak ditemukan.'], 404);
        }

        // Ambil detail item SO
        $items = DB::table('tbl_sales_order_details as sod')
            ->join('tbl_bahan_scm as b', 'sod.product_id', '=', 'b.id')
            ->leftJoin('tbl_units as u', 'sod.unit_id', '=', 'u.id')
            ->where('sod.sales_order_id', $soId)
            ->select(
                'sod.id as sod_id',
                'sod.product_id',
                'sod.qty',
                'sod.price',
                'sod.subtotal',
                'sod.unit_id',
                'b.nama_bahan',
                'u.nama_unit',
            )
            ->get();

        // Hitung qty yang sudah dikirim di GD sebelumnya (partial delivery)
        foreach ($items as $item) {
            $alreadyDelivered = DB::table('tbl_goods_delivery_details as gdd')
                ->join('tbl_goods_deliveries as gd', 'gdd.goods_delivery_id', '=', 'gd.id')
                ->where('gdd.sales_order_detail_id', $item->sod_id)
                ->whereIn('gd.status', ['IN_TRANSIT', 'DELIVERED'])
                ->sum('gdd.qty_delivered');

            $item->qty_already_delivered = (float) $alreadyDelivered;
            $item->qty_remaining = max(0, $item->qty - $alreadyDelivered);
        }

        return response()->json([
            'status' => 'success',
            'so' => $so,
            'items' => $items,
        ]);
    }

    public function storeGoodsDelivery(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sales_order_id' => 'required|integer|exists:tbl_sales_orders,id',
            'delivery_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.sales_order_detail_id' => 'required|integer',
            'items.*.product_id' => 'required|integer',
            'items.*.unit_id' => 'required|integer',
            'items.*.qty_ordered' => 'required|numeric|min:0',
            'items.*.qty_delivered' => 'required|numeric|min:0',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // Minimal 1 item harus punya qty_delivered > 0
        $adaItemDikirim = collect($request->items)->some(fn($i) => ($i['qty_delivered'] ?? 0) > 0);
        if (!$adaItemDikirim) {
            return response()->json([
                'status' => 'error',
                'message' => 'Minimal satu item harus memiliki qty dikirim lebih dari 0.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // --- 1. 3-way match: qty_delivered tidak boleh melebihi sisa SO ---
            foreach ($request->items as $item) {
                $qtyDelivered = (float) ($item['qty_delivered'] ?? 0);
                if ($qtyDelivered <= 0)
                    continue;

                $sodQty = DB::table('tbl_sales_order_details')
                    ->where('id', $item['sales_order_detail_id'])
                    ->value('qty');

                $alreadyDelivered = DB::table('tbl_goods_delivery_details as gdd')
                    ->join('tbl_goods_deliveries as gd', 'gdd.goods_delivery_id', '=', 'gd.id')
                    ->where('gdd.sales_order_detail_id', $item['sales_order_detail_id'])
                    ->whereIn('gd.status', ['IN_TRANSIT', 'DELIVERED'])
                    ->sum('gdd.qty_delivered');

                $maxBisaDikirim = $sodQty - $alreadyDelivered;

                if ($qtyDelivered > $maxBisaDikirim) {
                    DB::rollBack();
                    $bahan = DB::table('tbl_bahan_scm')->where('id', $item['product_id'])->value('nama_bahan');
                    return response()->json([
                        'status' => 'error',
                        'message' => "Item '{$bahan}': qty dikirim ({$qtyDelivered}) melebihi sisa SO ({$maxBisaDikirim}).",
                    ], 422);
                }
            }

            // --- 2. Validasi stok tersedia sebelum buat GD ---
            // Stok keluar baru dicatat saat DELIVERED, tapi cek dulu agar tidak
            // membuat GD yang tidak bisa dikonfirmasi karena stok kurang
            $warehouseId = auth()->user()->warehouse_id ?? $request->warehouse_id;
            foreach ($request->items as $item) {
                $qtyDelivered = (float) ($item['qty_delivered'] ?? 0);
                if ($qtyDelivered <= 0)
                    continue;

                $stokAktual = DB::table('tbl_stock_transactions')
                    ->where('bahan_id', $item['product_id'])
                    ->where('warehouse_id', $warehouseId)
                    ->orderByDesc('id')
                    ->value('stok_sesudah') ?? 0;

                if ($stokAktual < $qtyDelivered) {
                    DB::rollBack();
                    $bahan = DB::table('tbl_bahan_scm')->where('id', $item['product_id'])->value('nama_bahan');
                    return response()->json([
                        'status' => 'error',
                        'message' => "Stok '{$bahan}' tidak cukup. Stok tersedia: {$stokAktual}, dibutuhkan: {$qtyDelivered}.",
                    ], 422);
                }
            }

            // --- 3. Generate GD number (dengan lockForUpdate) ---
            $today = Carbon::now()->format('Ymd');
            $prefix = 'GD' . $today;

            $lastGd = DB::table('tbl_goods_deliveries')
                ->where('gd_number', 'LIKE', $prefix . '%')
                ->orderBy('gd_number', 'desc')
                ->lockForUpdate()
                ->value('gd_number');

            $nextNum = $lastGd
                ? str_pad((int) substr($lastGd, -4) + 1, 4, '0', STR_PAD_LEFT)
                : '0001';
            $gdNumber = $prefix . $nextNum;

            // --- 4. Ambil info SO untuk denormalisasi ---
            $so = DB::table('tbl_sales_orders')->where('id', $request->sales_order_id)->first();

            // --- 5. Hitung total amount ---
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += (float) ($item['qty_delivered'] ?? 0) * (float) ($item['price'] ?? 0);
            }

            // --- 6. Simpan Header GD ---
            $gdId = DB::table('tbl_goods_deliveries')->insertGetId([
                'gd_number' => $gdNumber,
                'sales_order_id' => $request->sales_order_id,
                'customer_id' => $so->customer_id,
                'delivery_address' => $request->delivery_address ?? $so->address,
                'warehouse_id' => $warehouseId,
                'delivery_date' => $request->delivery_date,
                'estimated_arrival' => $request->estimated_arrival,
                'driver_name' => $request->driver_name,
                'vehicle_plate' => $request->vehicle_plate,
                'status' => 'DRAFT',
                'total_amount' => $totalAmount,
                'notes' => $request->notes,
                'outlet_po_id' => $request->outlet_po_id ?? null,
                'sj_id' => $request->sj_id ?? null,
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($request->outlet_po_id) {
                DB::table('tbl_po')
                    ->where('id', $request->outlet_po_id)
                    ->update(['status' => 'Processing', 'updated_at' => now()]);
            }

            if ($request->sj_id) {
                DB::table('tbl_surat_jalan')
                    ->where('id', $request->sj_id)
                    ->update(['gd_id' => $gdId, 'updated_at' => now()]);
            }


            // --- 7. Simpan Detail GD ---
            foreach ($request->items as $item) {
                $qtyDelivered = (float) ($item['qty_delivered'] ?? 0);
                if ($qtyDelivered <= 0)
                    continue;

                $price = (float) ($item['price'] ?? 0);

                DB::table('tbl_goods_delivery_details')->insert([
                    'goods_delivery_id' => $gdId,
                    'sales_order_detail_id' => $item['sales_order_detail_id'],
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'],
                    'qty_ordered' => $item['qty_ordered'],
                    'qty_delivered' => $qtyDelivered,
                    'price' => $price,
                    'subtotal' => $qtyDelivered * $price,
                    'notes' => $item['notes'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            Log::info("GD {$gdNumber} dibuat oleh user " . Auth::id());

            return response()->json([
                'status' => 'success',
                'message' => "Goods Delivery {$gdNumber} berhasil dibuat sebagai DRAFT. Klik 'Dispatch' untuk mulai pengiriman.",
                'gd_number' => $gdNumber,
                'gd_id' => $gdId,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('GoodsDelivery store failed: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan GD: ' . $e->getMessage(),
            ], 500);
        }
    }

    // public function dispatchGoodsDelivery($id)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $gd = DB::table('tbl_goods_deliveries')->where('id', $id)->first();

    //         if (!$gd) {
    //             return response()->json(['status' => 'error', 'message' => 'GD tidak ditemukan.'], 404);
    //         }

    //         if ($gd->status !== 'DRAFT') {
    //             return response()->json([
    //                 'status'  => 'error',
    //                 'message' => "Hanya GD berstatus DRAFT yang bisa di-dispatch. Status sekarang: {$gd->status}",
    //             ], 422);
    //         }

    //         DB::table('tbl_goods_deliveries')->where('id', $id)->update([
    //             'status'     => 'IN_TRANSIT',
    //             'updated_at' => now(),
    //         ]);

    //         // Update status SO menjadi IN_DELIVERY
    //         DB::table('tbl_sales_orders')
    //             ->where('id', $gd->sales_order_id)
    //             ->update(['status' => 'IN_DELIVERY', 'updated_at' => now()]);

    //         DB::commit();

    //         Log::info("GD {$gd->gd_number} di-dispatch oleh user " . Auth::id());

    //         return response()->json([
    //             'status'  => 'success',
    //             'message' => "GD {$gd->gd_number} sekarang IN_TRANSIT. Stok akan keluar saat dikonfirmasi DELIVERED.",
    //         ]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error('GoodsDelivery dispatch failed: ' . $e->getMessage(), ['gd_id' => $id]);
    //         return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    //     }
    // }

    public function dispatchGoodsDelivery(Request $request, $id)
    {
        $gd = DB::table('tbl_goods_deliveries')->where('id', $id)->first();

        if (!$gd) {
            return response()->json(['status' => 'error', 'message' => 'GD tidak ditemukan.'], 404);
        }

        if ($gd->status !== 'DRAFT') {
            return response()->json(['status' => 'error', 'message' => "GD status '{$gd->status}' tidak bisa di-dispatch."], 422);
        }

        DB::beginTransaction();
        try {
            // 1. Update GD → IN_TRANSIT
            DB::table('tbl_goods_deliveries')->where('id', $id)->update([
                'status' => 'IN_TRANSIT',
                'updated_at' => now(),
            ]);

            // 2. Jika ada outlet_po_id → update tbl_po status ke "In Transit"
            if ($gd->outlet_po_id) {
                DB::table('tbl_po')
                    ->where('id', $gd->outlet_po_id)
                    ->update([
                        'status' => 'In Transit',
                        'updated_at' => now(),
                    ]);
            }

            // 3. Jika ada sj_id → update tbl_surat_jalan status ke "In Transit"
            if ($gd->sj_id) {
                DB::table('tbl_surat_jalan')
                    ->where('id', $gd->sj_id)
                    ->update([
                        'status' => 'In Transit',
                        'updated_at' => now(),
                    ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "GD {$gd->gd_number} di-dispatch. Status outlet PO diperbarui ke In Transit.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('GD dispatch failed: ' . $e->getMessage(), ['gd_id' => $id]);
            return response()->json(['status' => 'error', 'message' => 'Gagal dispatch: ' . $e->getMessage()], 500);
        }
    }

    // public function storePoReceive(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'po_id'    => 'required|exists:tbl_po,id',
    //         'outlet_id' => 'required',
    //         'items'    => 'required|array|min:1',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
    //     }

    //     $po = DB::table('tbl_po')->where('id', $request->po_id)->first();

    //     if (!$po) {
    //         return response()->json(['status' => 'error', 'message' => 'PO tidak ditemukan.'], 404);
    //     }

    //     DB::beginTransaction();
    //     try {

    //         // ── 1. Simpan header tbl_po_receive ──────────────────────
    //         $receiveId = DB::table('tbl_po_receive')->insertGetId([
    //             'po_id'             => $request->po_id,
    //             'no_po'             => $po->no_po,
    //             'outlet_id'         => $request->outlet_id,
    //             'tgl_terima'        => now(),
    //             'keterangan_global' => $request->keterangan_global,
    //             'gd_id'             => $request->gd_id ?? null, // link ke GD jika ada
    //             'created_by'        => auth()->id(),
    //             'created_at'        => now(),
    //         ]);

    //         // ── 2. Simpan detail tbl_po_receive_detail ────────────────
    //         foreach ($request->items as $item) {
    //             $qtyPo    = $item['qty_po']    ?? 0;
    //             $qtyTerima = $item['qty_terima'] ?? 0;
    //             $qtykurang = max(0, $qtyPo - $qtyTerima);

    //             DB::table('tbl_po_receive_detail')->insert([
    //                 'receive_id'           => $receiveId,
    //                 'po_id'                => $request->po_id,
    //                 'bahan_id'             => $item['bahan_id'],
    //                 'unit_id'              => $item['unit_id'],
    //                 'qty_po'               => $qtyPo,
    //                 'qty_terima'           => $qtyTerima,
    //                 'qty_kurang'           => $qtykurang,
    //                 'alasan_kurang'        => $item['alasan_kurang'] ?? null,
    //                 'harga_satuan_aktual'  => $item['harga_satuan_aktual'] ?? null,
    //                 'qty_besar'            => $item['qty_besar'] ?? 0,
    //                 'qty_kecil'            => $item['qty_kecil'] ?? 0,
    //                 'qty_pack'             => $item['qty_pack'] ?? null,
    //                 'konversi'             => $item['konversi'] ?? 1,
    //                 'total_gramase'        => ($item['qty_terima'] ?? 0) * ($item['konversi'] ?? 1),
    //                 'created_at'           => now(),
    //             ]);
    //         }

    //         // ── 3. Update tbl_po status → "Recieved" ─────────────────
    //         DB::table('tbl_po')->where('id', $request->po_id)->update([
    //             'status'     => 'Recieved',
    //             'updated_at' => now(),
    //         ]);

    //         // ── 4. INTEGRASI: jika ada gd_id → konfirmasi GD di SCM ──
    //         $gdId = $request->gd_id;

    //         // Coba cari GD dari outlet_po_id jika gd_id tidak dikirim
    //         if (!$gdId) {
    //             $gd = DB::table('tbl_goods_deliveries')
    //                 ->where('outlet_po_id', $request->po_id)
    //                 ->where('status', 'IN_TRANSIT')
    //                 ->first();
    //             $gdId = $gd->id ?? null;
    //         }

    //         if ($gdId) {
    //             $gd = DB::table('tbl_goods_deliveries')->where('id', $gdId)->first();

    //             if ($gd && $gd->status === 'IN_TRANSIT') {

    //                 // Update GD → DELIVERED
    //                 DB::table('tbl_goods_deliveries')->where('id', $gdId)->update([
    //                     'status'         => 'DELIVERED',
    //                     'actual_arrival' => now(),
    //                     'updated_at'     => now(),
    //                 ]);

    //                 // Update tbl_po_receive dengan gd_id (jika ditemukan otomatis)
    //                 DB::table('tbl_po_receive')->where('id', $receiveId)->update([
    //                     'gd_id' => $gdId,
    //                 ]);

    //                 // Kurangi stok (stock OUT) berdasarkan qty_delivered di GD details
    //                 // Ambil detail GD untuk update stok transaksi
    //                 $gdDetails = DB::table('tbl_goods_delivery_details')
    //                     ->where('goods_delivery_id', $gdId)
    //                     ->get();

    //                 // Juga update qty_delivered di GD details berdasarkan qty_terima outlet
    //                 // Map bahan_id → qty_terima dari outlet receive
    //                 $receiveMap = [];
    //                 foreach ($request->items as $item) {
    //                     $receiveMap[$item['bahan_id']] = $item['qty_terima'] ?? 0;
    //                 }

    //                 foreach ($gdDetails as $gdd) {
    //                     // Update qty_delivered di GD detail sesuai qty yang benar-benar diterima outlet
    //                     $actualQty = $receiveMap[$gdd->product_id] ?? $gdd->qty_ordered;

    //                     DB::table('tbl_goods_delivery_details')
    //                         ->where('id', $gdd->id)
    //                         ->update([
    //                             'qty_delivered' => $actualQty,
    //                             'subtotal'      => $actualQty * $gdd->price,
    //                             'updated_at'    => now(),
    //                         ]);

    //                     // Stock OUT dari warehouse
    //                     DB::table('tbl_stok_transaksi')->insert([
    //                         'warehouse_id'   => $gd->warehouse_id,
    //                         'bahan_id'       => $gdd->product_id,
    //                         'unit_id'        => $gdd->unit_id,
    //                         'tipe_transaksi' => 'OUT',
    //                         'qty'            => $actualQty,
    //                         'referensi'      => $gd->gd_number,
    //                         'keterangan'     => 'GD Delivered - outlet confirmed receive',
    //                         'created_at'     => now(),
    //                     ]);
    //                 }

    //                 // Update total_amount GD sesuai qty aktual
    //                 $newTotal = DB::table('tbl_goods_delivery_details')
    //                     ->where('goods_delivery_id', $gdId)
    //                     ->sum('subtotal');

    //                 DB::table('tbl_goods_deliveries')->where('id', $gdId)->update([
    //                     'total_amount' => $newTotal,
    //                 ]);

    //                 // Update SO status → DELIVERED
    //                 if ($gd->sales_order_id) {
    //                     DB::table('tbl_sales_orders')
    //                         ->where('id', $gd->sales_order_id)
    //                         ->update(['status' => 'DELIVERED', 'updated_at' => now()]);
    //                 }
    //             }
    //         }

    //         // ── 5. Simpan foto jika ada (base64) ─────────────────────
    //         if ($request->filled('foto_barang_base64')) {
    //             DB::table('tbl_po_receive_images')->insert([
    //                 'receive_id' => $receiveId,
    //                 'image_data' => $request->foto_barang_base64,
    //                 'created_at' => now(),
    //             ]);
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'status'  => 'success',
    //             'message' => 'Penerimaan barang berhasil dicatat.' .
    //                 ($gdId ? ' GD di SCM otomatis dikonfirmasi DELIVERED.' : ''),
    //         ]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error('PO Receive store failed: ' . $e->getMessage(), ['po_id' => $request->po_id]);
    //         return response()->json(['status' => 'error', 'message' => 'Gagal simpan: ' . $e->getMessage()], 500);
    //     }
    // }

    public function storePoReceive(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'po_id' => 'required|exists:tbl_po,id',
            'outlet_id' => 'required',
            'items' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $po = DB::table('tbl_po')->where('id', $request->po_id)->first();

        if (!$po) {
            return response()->json(['status' => 'error', 'message' => 'PO tidak ditemukan.'], 404);
        }

        DB::beginTransaction();
        try {

            // ── 1. Simpan header tbl_po_receive ──────────────────────
            $receiveId = DB::table('tbl_po_receive')->insertGetId([
                'po_id' => $request->po_id,
                'no_po' => $po->no_po,
                'outlet_id' => $request->outlet_id,
                'tgl_terima' => now(),
                'keterangan_global' => $request->keterangan_global,
                'gd_id' => $request->gd_id ?? null, // link ke GD jika ada
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);

            // ── 2. Simpan detail tbl_po_receive_detail ────────────────
            foreach ($request->items as $item) {
                $qtyPo = $item['qty_po'] ?? 0;
                $qtyTerima = $item['qty_terima'] ?? 0;
                $qtykurang = max(0, $qtyPo - $qtyTerima);

                DB::table('tbl_po_receive_detail')->insert([
                    'receive_id' => $receiveId,
                    'po_id' => $request->po_id,
                    'bahan_id' => $item['bahan_id'],
                    'unit_id' => $item['unit_id'],
                    'qty_po' => $qtyPo,
                    'qty_terima' => $qtyTerima,
                    'qty_kurang' => $qtykurang,
                    'alasan_kurang' => $item['alasan_kurang'] ?? null,
                    'harga_satuan_aktual' => $item['harga_satuan_aktual'] ?? null,
                    'qty_besar' => $item['qty_besar'] ?? 0,
                    'qty_kecil' => $item['qty_kecil'] ?? 0,
                    'qty_pack' => $item['qty_pack'] ?? null,
                    'konversi' => $item['konversi'] ?? 1,
                    'total_gramase' => ($item['qty_terima'] ?? 0) * ($item['konversi'] ?? 1),
                    'created_at' => now(),
                ]);
            }

            // ── 3. Update tbl_po status → "Recieved" ─────────────────
            DB::table('tbl_po')->where('id', $request->po_id)->update([
                'status' => 'Recieved',
                'updated_at' => now(),
            ]);

            // ── 4. INTEGRASI: jika ada gd_id → konfirmasi GD di SCM ──
            $gdId = $request->gd_id;

            // Coba cari GD dari outlet_po_id jika gd_id tidak dikirim
            if (!$gdId) {
                $gd = DB::table('tbl_goods_deliveries')
                    ->where('outlet_po_id', $request->po_id)
                    ->where('status', 'IN_TRANSIT')
                    ->first();
                $gdId = $gd->id ?? null;
            }

            if ($gdId) {
                $gd = DB::table('tbl_goods_deliveries')->where('id', $gdId)->first();

                if ($gd && $gd->status === 'IN_TRANSIT') {

                    // Update GD → DELIVERED
                    DB::table('tbl_goods_deliveries')->where('id', $gdId)->update([
                        'status' => 'DELIVERED',
                        'actual_arrival' => now(),
                        'updated_at' => now(),
                    ]);

                    // Update tbl_po_receive dengan gd_id (jika ditemukan otomatis)
                    DB::table('tbl_po_receive')->where('id', $receiveId)->update([
                        'gd_id' => $gdId,
                    ]);

                    // Kurangi stok (stock OUT) berdasarkan qty_delivered di GD details
                    // Ambil detail GD untuk update stok transaksi
                    $gdDetails = DB::table('tbl_goods_delivery_details')
                        ->where('goods_delivery_id', $gdId)
                        ->get();

                    // Juga update qty_delivered di GD details berdasarkan qty_terima outlet
                    // Map bahan_id → qty_terima dari outlet receive
                    $receiveMap = [];
                    foreach ($request->items as $item) {
                        $receiveMap[$item['bahan_id']] = $item['qty_terima'] ?? 0;
                    }

                    foreach ($gdDetails as $gdd) {
                        // Update qty_delivered di GD detail sesuai qty yang benar-benar diterima outlet
                        $actualQty = $receiveMap[$gdd->product_id] ?? $gdd->qty_ordered;

                        DB::table('tbl_goods_delivery_details')
                            ->where('id', $gdd->id)
                            ->update([
                                'qty_delivered' => $actualQty,
                                'subtotal' => $actualQty * $gdd->price,
                                'updated_at' => now(),
                            ]);

                        // Stock OUT dari warehouse
                        DB::table('tbl_stok_transaksi')->insert([
                            'warehouse_id' => $gd->warehouse_id,
                            'bahan_id' => $gdd->product_id,
                            'unit_id' => $gdd->unit_id,
                            'tipe_transaksi' => 'OUT',
                            'qty' => $actualQty,
                            'referensi' => $gd->gd_number,
                            'keterangan' => 'GD Delivered - outlet confirmed receive',
                            'created_at' => now(),
                        ]);
                    }

                    // Update total_amount GD sesuai qty aktual
                    $newTotal = DB::table('tbl_goods_delivery_details')
                        ->where('goods_delivery_id', $gdId)
                        ->sum('subtotal');

                    DB::table('tbl_goods_deliveries')->where('id', $gdId)->update([
                        'total_amount' => $newTotal,
                    ]);

                    // Update SO status → DELIVERED
                    if ($gd->sales_order_id) {
                        DB::table('tbl_sales_orders')
                            ->where('id', $gd->sales_order_id)
                            ->update(['status' => 'DELIVERED', 'updated_at' => now()]);
                    }
                }
            }

            // ── 5. Simpan foto jika ada (base64) ─────────────────────
            if ($request->filled('foto_barang_base64')) {
                DB::table('tbl_po_receive_images')->insert([
                    'receive_id' => $receiveId,
                    'image_data' => $request->foto_barang_base64,
                    'created_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Penerimaan barang berhasil dicatat.' .
                    ($gdId ? ' GD di SCM otomatis dikonfirmasi DELIVERED.' : ''),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PO Receive store failed: ' . $e->getMessage(), ['po_id' => $request->po_id]);
            return response()->json(['status' => 'error', 'message' => 'Gagal simpan: ' . $e->getMessage()], 500);
        }
    }

    // -------------------------------------------------------
    // HOOK 3: Saat SCM buat GD baru, link ke outlet PO
    // Tambahkan logika ini di dalam storeGoodsDelivery() yang ada
    // Cukup tambahkan outlet_po_id ke array insert GD
    //
    // Di storeGoodsDelivery(), saat insert tbl_goods_deliveries:
    // -------------------------------------------------------
    // CONTOH TAMBAHAN di storeGoodsDelivery():
    //
    // $gdId = DB::table('tbl_goods_deliveries')->insertGetId([
    //     'gd_number'       => $gdNumber,
    //     'sales_order_id'  => $request->sales_order_id,
    //     'customer_id'     => $request->customer_id,
    //     'outlet_po_id'    => $request->outlet_po_id ?? null,  // ← TAMBAH INI
    //     'sj_id'           => $request->sj_id ?? null,         // ← TAMBAH INI
    //     ...
    // ]);
    //
    // Setelah insert, jika outlet_po_id ada:
    // DB::table('tbl_po')->where('id', $request->outlet_po_id)
    //     ->update(['status' => 'Processing', 'updated_at' => now()]);

    // -------------------------------------------------------
    // HOOK 4: API untuk outlet ambil GD yang sedang IN_TRANSIT
    // Outlet pakai ini untuk tahu gd_id mana yang harus diisi
    // Route: GET /dashboard-outlet/active-gd/{po_id}
    // -------------------------------------------------------
    public function getActiveGdForOutlet($poId)
    {
        $gd = DB::table('tbl_goods_deliveries as gd')
            ->leftJoin('tbl_goods_delivery_details as gdd', 'gdd.goods_delivery_id', '=', 'gd.id')
            ->leftJoin('tbl_bahan_scm as b', 'gdd.product_id', '=', 'b.id')
            ->leftJoin('tbl_units as u', 'gdd.unit_id', '=', 'u.id')
            ->where('gd.outlet_po_id', $poId)
            ->whereIn('gd.status', ['DRAFT', 'IN_TRANSIT'])
            ->select(
                'gd.id as gd_id',
                'gd.gd_number',
                'gd.status',
                'gd.delivery_date',
                'gd.driver_name',
                'gd.vehicle_plate',
                'gdd.product_id',
                'b.nama_bahan',
                'gdd.unit_id',
                'u.nama_unit',
                'gdd.qty_ordered',
                'gdd.qty_delivered',
                'gdd.price'
            )
            ->get();

        if ($gd->isEmpty()) {
            return response()->json([
                'status' => 'not_found',
                'gd_id' => null,
                'items' => [],
            ]);
        }

        // Group by GD header
        $header = $gd->first();

        return response()->json([
            'status' => 'success',
            'gd_id' => $header->gd_id,
            'gd_number' => $header->gd_number,
            'gd_status' => $header->status,
            'delivery_date' => $header->delivery_date,
            'driver_name' => $header->driver_name,
            'vehicle_plate' => $header->vehicle_plate,
            'items' => $gd->map(fn($r) => [
                'product_id' => $r->product_id,
                'nama_bahan' => $r->nama_bahan,
                'unit_id' => $r->unit_id,
                'nama_unit' => $r->nama_unit,
                'qty_ordered' => $r->qty_ordered,
                'qty_delivered' => $r->qty_delivered,
                'price' => $r->price,
            ])->values(),
        ]);
    }

    public function confirmDelivered($id)
    {
        DB::beginTransaction();
        try {
            $gd = DB::table('tbl_goods_deliveries')->where('id', $id)->first();

            if (!$gd) {
                return response()->json(['status' => 'error', 'message' => 'GD tidak ditemukan.'], 404);
            }

            // Hanya IN_TRANSIT atau DRAFT yang boleh di-deliver
            if (!in_array($gd->status, ['IN_TRANSIT', 'DRAFT'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => "GD dengan status '{$gd->status}' tidak bisa dikonfirmasi delivered.",
                ], 422);
            }

            $details = DB::table('tbl_goods_delivery_details')
                ->where('goods_delivery_id', $id)
                ->get();

            $totalItemDikirim = 0;

            // --- Loop per item: catat stock OUT ke tbl_stock_transactions ---
            foreach ($details as $detail) {
                if ($detail->qty_delivered <= 0)
                    continue;

                // Cek stok cukup sebelum kurangi (double-check saat konfirmasi)
                $stokSebelum = DB::table('tbl_stock_transactions')
                    ->where('bahan_id', $detail->product_id)
                    ->where('warehouse_id', $gd->warehouse_id)
                    ->lockForUpdate()   // ← WAJIB: hindari race condition
                    ->orderByDesc('id')
                    ->value('stok_sesudah') ?? 0;

                if ($stokSebelum < $detail->qty_delivered) {
                    DB::rollBack();
                    $bahan = DB::table('tbl_bahan_scm')->where('id', $detail->product_id)->value('nama_bahan');
                    return response()->json([
                        'status' => 'error',
                        'message' => "Stok '{$bahan}' tidak cukup saat konfirmasi. Tersedia: {$stokSebelum}, dibutuhkan: {$detail->qty_delivered}.",
                    ], 422);
                }

                $stokSesudah = $stokSebelum - $detail->qty_delivered;

                // Catat transaksi stok KELUAR
                DB::table('tbl_stock_transactions')->insert([
                    'bahan_id' => $detail->product_id,
                    'unit_id' => $detail->unit_id,
                    'warehouse_id' => $gd->warehouse_id,
                    'jumlah' => $detail->qty_delivered,
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $stokSesudah,
                    'tipe' => 'KELUAR',
                    'reference_type' => 'goods_delivery',
                    'reference_id' => $gd->id,
                    'harga_satuan' => $detail->price,
                    'total_nilai' => $detail->qty_delivered * $detail->price,
                    'keterangan' => 'Pengiriman barang GD: ' . $gd->gd_number,
                    'created_by' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $totalItemDikirim++;
            }

            // --- Cek apakah semua SO detail sudah ter-deliver ---
            $soDetails = DB::table('tbl_sales_order_details')
                ->where('sales_order_id', $gd->sales_order_id)
                ->get();

            $semuaTerkirim = true;
            foreach ($soDetails as $sod) {
                $totalDelivered = DB::table('tbl_goods_delivery_details as gdd')
                    ->join('tbl_goods_deliveries as gd2', 'gdd.goods_delivery_id', '=', 'gd2.id')
                    ->where('gdd.sales_order_detail_id', $sod->id)
                    ->where(function ($q) use ($id) {
                        $q->whereIn('gd2.status', ['DELIVERED'])
                            ->orWhere('gd2.id', $id); // termasuk GD ini yang baru di-deliver
                    })
                    ->sum('gdd.qty_delivered');

                if ($totalDelivered < $sod->qty) {
                    $semuaTerkirim = false;
                    break;
                }
            }

            // --- Update status GD ---
            DB::table('tbl_goods_deliveries')->where('id', $id)->update([
                'status' => 'DELIVERED',
                'actual_arrival' => now()->toDateString(),
                'delivered_by' => Auth::id(),
                'updated_at' => now(),
            ]);

            // --- Update status SO ---
            $statusSO = $semuaTerkirim ? 'DELIVERED' : 'PARTIAL_DELIVERED';
            DB::table('tbl_sales_orders')
                ->where('id', $gd->sales_order_id)
                ->update(['status' => $statusSO, 'updated_at' => now()]);

            DB::commit();

            Log::info("GD {$gd->gd_number} DELIVERED. SO status: {$statusSO}. Stok keluar: {$totalItemDikirim} item.");

            return response()->json([
                'status' => 'success',
                'message' => "GD {$gd->gd_number} berhasil dikonfirmasi DELIVERED! Stok sudah berkurang.",
                'gd_status' => 'DELIVERED',
                'so_status' => $statusSO,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('GoodsDelivery confirmDelivered failed: ' . $e->getMessage(), [
                'gd_id' => $id,
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal konfirmasi delivered: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function showGoodsDelivery($id)
    {
        $gd = DB::table('tbl_goods_deliveries as gd')
            ->join('tbl_sales_orders as so', 'gd.sales_order_id', '=', 'so.id')
            ->join('tbl_outlets as o', 'gd.customer_id', '=', 'o.id')
            ->where('gd.id', $id)
            ->select('gd.*', 'so.so_number', 'o.nama_outlet as customer_name')
            ->first();

        if (!$gd) {
            return response()->json(['status' => 'error', 'message' => 'GD tidak ditemukan.'], 404);
        }

        $details = DB::table('tbl_goods_delivery_details as gdd')
            ->join('tbl_bahan_scm as b', 'gdd.product_id', '=', 'b.id')
            ->leftJoin('tbl_units as u', 'gdd.unit_id', '=', 'u.id')
            ->where('gdd.goods_delivery_id', $id)
            ->select('gdd.*', 'b.nama_bahan', 'u.nama_unit')
            ->get();

        return response()->json([
            'status' => 'success',
            'gd' => $gd,
            'details' => $details,
        ]);
    }

    // === GOODS RECEIPT ===
    public function indexGoodsReceipt()
    {
        // $user = Auth::user();

        // Ambil semua GR beserta info PO dan supplier (JOIN)
        $goodsReceipts = DB::table('tbl_goods_receipts as gr')
            ->join('tbl_purchase_orders as po', 'gr.purchase_order_id', '=', 'po.id')
            ->join('tbl_suppliers as s', 'gr.supplier_id', '=', 's.id')
            ->select(
                'gr.id',
                'gr.gr_number',
                'gr.receipt_date',
                'gr.status',
                'gr.qc_status',
                'gr.total_amount',
                'gr.supplier_do_number',
                'gr.driver_name',
                'gr.notes',
                'gr.created_at',
                'po.po_number',
                's.supplier_name',
            )
            ->orderBy('gr.created_at', 'desc')
            ->get();

        // Ambil daftar PO yang statusnya APPROVED (siap di-GR)
        // Dipakai untuk dropdown di modal "Receive Goods"
        $approvedPOs = DB::table('tbl_purchase_orders as po')
            ->join('tbl_suppliers as s', 'po.supplier_id', '=', 's.id')
            ->where('po.status', 'APPROVED')
            ->select(
                'po.id',
                'po.po_number',
                'po.required_date',
                'po.total_amount',
                's.supplier_name',
                's.id as supplier_id',
            )
            ->orderBy('po.created_at', 'desc')
            ->get();

        // Ringkasan widget atas
        $summary = [
            'total' => DB::table('tbl_goods_receipts')->count(),
            'draft' => DB::table('tbl_goods_receipts')->where('status', 'DRAFT')->count(),
            'partial' => DB::table('tbl_goods_receipts')->where('status', 'PARTIAL')->count(),
            'received' => DB::table('tbl_goods_receipts')->where('status', 'RECEIVED')->count(),
        ];

        return view('Purchasing.SCM.goodsReceipt', compact(
            'goodsReceipts',
            'approvedPOs',
            'summary'
        ));
    }

    public function getPoDetails($poId)
    {
        $po = DB::table('tbl_purchase_orders as po')
            ->join('tbl_suppliers as s', 'po.supplier_id', '=', 's.id')
            ->where('po.id', $poId)
            ->select(
                'po.*',
                's.supplier_name',
                's.id as supplier_id',
            )
            ->first();

        if (!$po) {
            return response()->json(['status' => 'error', 'message' => 'PO tidak ditemukan.'], 404);
        }

        // --- FIX: QUERY AMBIL DETAIL ITEM PO YANG SUDAH DIPERBAIKI KOLOM UNITNYA ---
        $items = DB::table('tbl_purchase_order_details as pod')
            ->join('tbl_bahan_scm as b', 'pod.product_id', '=', 'b.id')
            // Hubungkan ke tbl_bahan_unit untuk mencari unit purchase yang is_purchase_unit = 1
            ->leftJoin('tbl_bahan_unit as bu', function ($join) {
                $join->on('pod.product_id', '=', 'bu.bahan_id')
                    ->where('bu.is_purchase_unit', '=', 1);
            })
            // Hubungkan ke master tbl_units untuk mengambil nama aslinya (e.g., PACK @50PCS)
            ->leftJoin('tbl_units as u', 'bu.unit_id', '=', 'u.id')
            ->where('pod.purchase_order_id', $poId)
            ->select(
                'pod.id as pod_id',
                'pod.product_id',
                'pod.po_qty',
                'pod.price',
                'pod.subtotal',
                'bu.unit_id as unit_id', // <--- AMBIL DARI TABLE JEMBATAN (Bukan pod.unit_id lagi)
                'b.nama_bahan',
                'u.nama_unit'
            )
            ->get();

        // Hitung qty yang SUDAH diterima sebelumnya (untuk GR partial) - AMAN TETAP UTUH
        foreach ($items as $item) {
            $alreadyReceived = DB::table('tbl_goods_receipt_details as grd')
                ->join('tbl_goods_receipts as gr', 'grd.goods_receipt_id', '=', 'gr.id')
                ->where('grd.purchase_order_detail_id', $item->pod_id)
                ->whereIn('gr.status', ['PARTIAL', 'RECEIVED'])
                ->sum('grd.qty_received');

            $item->qty_already_received = (float) $alreadyReceived;
            $item->qty_remaining = max(0, $item->po_qty - $alreadyReceived);
        }

        return response()->json([
            'status' => 'success',
            'po' => $po,
            'items' => $items,
        ]);
    }

    public function storeGoodsReceipt(Request $request)
    {
        // --- Validasi Input ---
        $validator = Validator::make($request->all(), [
            'purchase_order_id' => 'required|integer|exists:tbl_purchase_orders,id',
            'receipt_date' => 'required|date',
            'warehouse_id' => 'required|integer',
            'items' => 'required|array|min:1',
            'items.*.purchase_order_detail_id' => 'required|integer',
            'items.*.product_id' => 'required|integer',
            'items.*.unit_id' => 'required|integer',
            'items.*.qty_ordered' => 'required|numeric|min:0',
            'items.*.qty_received' => 'required|numeric|min:0',
            'items.*.qty_rejected' => 'nullable|numeric|min:0',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // Pastikan ada minimal 1 item yang qty_received > 0
        $adaItemDiterima = collect($request->items)->some(fn($item) => ($item['qty_received'] ?? 0) > 0);
        if (!$adaItemDiterima) {
            return response()->json([
                'status' => 'error',
                'message' => 'Minimal satu item harus memiliki qty diterima lebih dari 0.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // --- 1. Validasi 3-way match: qty_received tidak boleh melebihi qty PO ---
            foreach ($request->items as $item) {
                $qtyReceived = (float) ($item['qty_received'] ?? 0);
                if ($qtyReceived <= 0)
                    continue;

                // Ambil qty PO dari tabel purchase_order_details
                $podQty = DB::table('tbl_purchase_order_details')
                    ->where('id', $item['purchase_order_detail_id'])
                    ->value('po_qty');

                // Hitung total yang SUDAH diterima sebelumnya (GR sebelumnya)
                $alreadyReceived = DB::table('tbl_goods_receipt_details as grd')
                    ->join('tbl_goods_receipts as gr', 'grd.goods_receipt_id', '=', 'gr.id')
                    ->where('grd.purchase_order_detail_id', $item['purchase_order_detail_id'])
                    ->whereIn('gr.status', ['PARTIAL', 'RECEIVED'])
                    ->sum('grd.qty_received');

                $maxBisaDiterima = $podQty - $alreadyReceived;

                if ($qtyReceived > $maxBisaDiterima) {
                    DB::rollBack();
                    $bahan = DB::table('tbl_bahan_scm')->where('id', $item['product_id'])->value('nama_bahan');
                    return response()->json([
                        'status' => 'error',
                        'message' => "Item '{$bahan}': qty diterima ({$qtyReceived}) melebihi sisa PO ({$maxBisaDiterima}).",
                    ], 422);
                }
            }

            // --- 2. Generate Nomor GR (dengan lockForUpdate untuk hindari race condition) ---
            $today = Carbon::now()->format('Ymd');
            $prefix = 'GR' . $today;

            $lastGr = DB::table('tbl_goods_receipts')
                ->where('gr_number', 'LIKE', $prefix . '%')
                ->orderBy('gr_number', 'desc')
                ->lockForUpdate()
                ->value('gr_number');

            $nextNum = $lastGr
                ? str_pad((int) substr($lastGr, -4) + 1, 4, '0', STR_PAD_LEFT)
                : '0001';
            $grNumber = $prefix . $nextNum;

            // --- 3. Hitung total amount dari item yang diterima ---
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $qtyReceived = (float) ($item['qty_received'] ?? 0);
                $price = (float) ($item['price'] ?? 0);
                $totalAmount += $qtyReceived * $price;
            }

            // --- 4. Simpan Header GR ---
            $grId = DB::table('tbl_goods_receipts')->insertGetId([
                'gr_number' => $grNumber,
                'purchase_order_id' => $request->purchase_order_id,
                'supplier_id' => $request->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'receipt_date' => $request->receipt_date,
                'supplier_do_number' => $request->supplier_do_number,
                'driver_name' => $request->driver_name,
                'vehicle_plate' => $request->vehicle_plate,
                'status' => 'DRAFT',   // mulai DRAFT dulu, konfirmasi manual
                'qc_status' => 'PENDING',
                'total_amount' => $totalAmount,
                'notes' => $request->notes,
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // --- 5. Simpan Detail GR (satu baris per item) ---
            foreach ($request->items as $item) {
                $qtyReceived = (float) ($item['qty_received'] ?? 0);
                $qtyRejected = (float) ($item['qty_rejected'] ?? 0);
                $price = (float) ($item['price'] ?? 0);

                // Hanya simpan item yang ada qty-nya (diterima atau ditolak)
                if ($qtyReceived <= 0 && $qtyRejected <= 0)
                    continue;

                DB::table('tbl_goods_receipt_details')->insert([
                    'goods_receipt_id' => $grId,
                    'purchase_order_detail_id' => $item['purchase_order_detail_id'],
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'],
                    'qty_ordered' => $item['qty_ordered'],
                    'qty_received' => $qtyReceived,
                    'qty_rejected' => $qtyRejected,
                    'price' => $price,
                    'subtotal' => $qtyReceived * $price,
                    'batch_number' => $item['batch_number'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                    'production_date' => $item['production_date'] ?? null,
                    'reject_reason' => $item['reject_reason'] ?? null,
                    'notes' => $item['notes'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            Log::info("GR {$grNumber} dibuat oleh user " . Auth::id());

            return response()->json([
                'status' => 'success',
                'message' => "Goods Receipt {$grNumber} berhasil dibuat! Silakan konfirmasi untuk update stok.",
                'gr_number' => $grNumber,
                'gr_id' => $grId,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('GoodsReceipt store failed: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan GR: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function confirm($id)
    {
        DB::beginTransaction();
        try {
            // Ambil header GR
            $gr = DB::table('tbl_goods_receipts')->where('id', $id)->first();

            if (!$gr) {
                return response()->json(['status' => 'error', 'message' => 'GR tidak ditemukan.'], 404);
            }

            // Hanya GR berstatus DRAFT yang boleh dikonfirmasi
            if (!in_array($gr->status, ['DRAFT'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => "GR dengan status '{$gr->status}' tidak bisa dikonfirmasi.",
                ], 422);
            }

            // Ambil semua detail item GR
            $details = DB::table('tbl_goods_receipt_details')
                ->where('goods_receipt_id', $id)
                ->get();

            $totalItemDiterima = 0;

            // --- Loop per item: catat stock IN ke tbl_stock_transactions ---
            foreach ($details as $detail) {
                if ($detail->qty_received <= 0)
                    continue;

                // Hitung stok sebelum (KUNCI RACE CONDITION: pakai lockForUpdate)
                $stokSebelum = DB::table('tbl_stock_transactions')
                    ->where('bahan_id', $detail->product_id)
                    ->where('warehouse_id', $gr->warehouse_id)
                    ->lockForUpdate()
                    ->orderByDesc('id')
                    ->value('stok_sesudah') ?? 0;

                $stokSesudah = $stokSebelum + $detail->qty_received;

                // Catat transaksi stok MASUK
                DB::table('tbl_stock_transactions')->insert([
                    'bahan_id' => $detail->product_id,
                    'unit_id' => $detail->unit_id,
                    'warehouse_id' => $gr->warehouse_id,
                    'jumlah' => $detail->qty_received,
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $stokSesudah,
                    'tipe' => 'MASUK',
                    'reference_type' => 'goods_receipt',
                    'reference_id' => $gr->id,
                    'batch_number' => $detail->batch_number,
                    'expiry_date' => $detail->expiry_date,
                    'harga_satuan' => $detail->price,
                    'total_nilai' => $detail->qty_received * $detail->price,
                    'keterangan' => 'Penerimaan barang dari GR: ' . $gr->gr_number,
                    'created_by' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $totalItemDiterima++;
            }

            // --- Tentukan status GR: RECEIVED atau PARTIAL ---
            // Cek apakah total qty diterima sudah cover semua qty PO
            $poDetails = DB::table('tbl_purchase_order_details')
                ->where('purchase_order_id', $gr->purchase_order_id)
                ->get();

            $semuaDiterima = true;
            foreach ($poDetails as $pod) {
                $totalDiterima = DB::table('tbl_goods_receipt_details as grd')
                    ->join('tbl_goods_receipts as gr2', 'grd.goods_receipt_id', '=', 'gr2.id')
                    ->where('grd.purchase_order_detail_id', $pod->id)
                    ->whereIn('gr2.status', ['PARTIAL', 'RECEIVED'])
                    ->sum('grd.qty_received');

                // Tambah yang baru saja dikonfirmasi (belum berubah status)
                $totalDiterima += DB::table('tbl_goods_receipt_details')
                    ->where('goods_receipt_id', $id)
                    ->where('purchase_order_detail_id', $pod->id)
                    ->value('qty_received') ?? 0;

                if ($totalDiterima < $pod->po_qty) {
                    $semuaDiterima = false;
                    break;
                }
            }

            $statusGR = $semuaDiterima ? 'RECEIVED' : 'PARTIAL';

            // --- Update status GR ---
            DB::table('tbl_goods_receipts')->where('id', $id)->update([
                'status' => $statusGR,
                'received_by' => Auth::id(),
                'updated_at' => now(),
            ]);

            // --- Update status PO ---
            $statusPO = $semuaDiterima ? 'RECEIVED' : 'PARTIAL_RECEIVED';
            DB::table('tbl_purchase_orders')
                ->where('id', $gr->purchase_order_id)
                ->update([
                    'status' => $statusPO,
                    'updated_at' => now(),
                ]);

            DB::commit();

            Log::info("GR {$gr->gr_number} dikonfirmasi. Status: {$statusGR}. Stok masuk: {$totalItemDiterima} item.");

            return response()->json([
                'status' => 'success',
                'message' => "GR {$gr->gr_number} berhasil dikonfirmasi! Stok sudah diperbarui. Status: {$statusGR}.",
                'gr_status' => $statusGR,
                'po_status' => $statusPO,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('GoodsReceipt confirm failed: ' . $e->getMessage(), [
                'gr_id' => $id,
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal konfirmasi GR: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function showGoodsReceipt($id)
    {
        $gr = DB::table('tbl_goods_receipts as gr')
            ->join('tbl_purchase_orders as po', 'gr.purchase_order_id', '=', 'po.id')
            ->join('tbl_suppliers as s', 'gr.supplier_id', '=', 's.id')
            ->where('gr.id', $id)
            ->select('gr.*', 'po.po_number', 's.supplier_name')
            ->first();

        if (!$gr) {
            return response()->json(['status' => 'error', 'message' => 'GR tidak ditemukan.'], 404);
        }

        $details = DB::table('tbl_goods_receipt_details as grd')
            ->join('tbl_bahan_scm as b', 'grd.product_id', '=', 'b.id')
            ->leftJoin('tbl_units as u', 'grd.unit_id', '=', 'u.id')
            ->where('grd.goods_receipt_id', $id)
            ->select(
                'grd.*',
                'b.nama_bahan',
                'u.nama_unit',
            )
            ->get();

        return response()->json([
            'status' => 'success',
            'gr' => $gr,
            'details' => $details,
        ]);
    }

    public function updateQc(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'qc_status' => 'required|in:PASSED,PARTIAL_REJECTED,REJECTED',
            'qc_notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $gr = DB::table('tbl_goods_receipts')->where('id', $id)->first();
        if (!$gr) {
            return response()->json(['status' => 'error', 'message' => 'GR tidak ditemukan.'], 404);
        }

        DB::table('tbl_goods_receipts')->where('id', $id)->update([
            'qc_status' => $request->qc_status,
            'qc_notes' => $request->qc_notes,
            'qc_by' => Auth::id(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Status QC berhasil diperbarui menjadi ' . $request->qc_status,
        ]);
    }

    // === SALES INVOICE ===
    public function indexSalesInvoice()
    {
        $invoices = DB::table('tbl_sales_invoices as si')
            ->join('tbl_goods_deliveries as gd', 'si.goods_delivery_id', '=', 'gd.id')
            ->join('tbl_sales_orders as so', 'gd.sales_order_id', '=', 'so.id')
            ->join('tbl_outlets as o', 'si.customer_id', '=', 'o.id')
            ->select(
                'si.id',
                'si.invoice_number',
                'si.invoice_date',
                'si.due_date',
                'si.subtotal',
                'si.dpp',
                'si.vat_amount',
                'si.total_amount',
                'si.paid_amount',
                'si.status',
                'si.billing_address',
                'si.created_at',
                'gd.gd_number',
                'so.so_number',
                'o.nama_outlet as customer_name',
            )
            ->whereNull('si.deleted_at')
            ->orderBy('si.created_at', 'desc')
            ->get();

        // Hitung outstanding dan flag overdue
        foreach ($invoices as $inv) {
            $inv->outstanding = $inv->total_amount - $inv->paid_amount;
            $inv->is_overdue = in_array($inv->status, ['ISSUED', 'PARTIAL_PAID'])
                && Carbon::parse($inv->due_date)->isPast();
        }

        // GD yang siap di-invoice:
        // - Status DELIVERED atau PARTIAL_DELIVERED (sudah ada barang terkirim)
        // - Belum punya SI yang aktif (bukan DRAFT dan bukan CANCELLED)
        // - leftJoin SO agar GD tanpa SO tetap muncul
        $readyGDs = DB::table('tbl_goods_deliveries as gd')
            ->leftJoin('tbl_sales_orders as so', 'gd.sales_order_id', '=', 'so.id')
            ->join('tbl_outlets as o', 'gd.customer_id', '=', 'o.id')
            ->whereIn('gd.status', ['DELIVERED', 'PARTIAL_DELIVERED'])
            ->whereNotExists(function ($q) {
                // Blokir hanya jika sudah ada SI aktif (ISSUED/PARTIAL_PAID/PAID)
                // SI berstatus DRAFT masih boleh dibuat ulang
                $q->select(DB::raw(1))
                    ->from('tbl_sales_invoices as si2')
                    ->whereColumn('si2.goods_delivery_id', 'gd.id')
                    ->whereNotIn('si2.status', ['DRAFT', 'CANCELLED']);
            })
            ->select(
                'gd.id',
                'gd.gd_number',
                'gd.delivery_date',
                'gd.actual_arrival',
                'gd.total_amount',
                'gd.customer_id',
                'gd.delivery_address',
                'gd.status as gd_status',
                DB::raw("COALESCE(so.so_number, '-') as so_number"),
                'o.nama_outlet as customer_name',
                'o.alamat as customer_address',
            )
            ->orderBy('gd.created_at', 'desc')
            ->get();

        // Widget ringkasan
        $summary = [
            'total' => DB::table('tbl_sales_invoices')->whereNull('deleted_at')->count(),
            'draft' => DB::table('tbl_sales_invoices')->whereIn('status', ['DRAFT', 'ISSUED'])->count(),
            'paid' => DB::table('tbl_sales_invoices')->where('status', 'PAID')->count(),
            'outstanding' => DB::table('tbl_sales_invoices')
                ->whereIn('status', ['ISSUED', 'PARTIAL_PAID', 'OVERDUE'])
                ->whereNull('deleted_at')
                ->sum(DB::raw('total_amount - paid_amount')),
        ];

        return view('Purchasing.SCM.salesInvoice', compact(
            'invoices',
            'readyGDs',
            'summary'
        ));
    }

    public function getGdDetails($gdId)
    {
        $gd = DB::table('tbl_goods_deliveries as gd')
            ->join('tbl_sales_orders as so', 'gd.sales_order_id', '=', 'so.id')
            ->join('tbl_outlets as o', 'gd.customer_id', '=', 'o.id')
            ->where('gd.id', $gdId)
            ->select(
                'gd.*',
                'so.so_number',
                'so.payment_terms_days',
                'o.nama_outlet as customer_name',
                'o.alamat as customer_address',
            )
            ->first();

        if (!$gd) {
            return response()->json(['status' => 'error', 'message' => 'GD tidak ditemukan.'], 404);
        }

        if ($gd->status !== 'DELIVERED') {
            return response()->json([
                'status' => 'error',
                'message' => "GD {$gd->gd_number} belum berstatus DELIVERED. Konfirmasi pengiriman terlebih dahulu.",
            ], 422);
        }

        // Ambil detail item GD beserta referensi SO untuk 3-way match
        $items = DB::table('tbl_goods_delivery_details as gdd')
            ->join('tbl_bahan_scm as b', 'gdd.product_id', '=', 'b.id')
            ->leftJoin('tbl_units as u', 'gdd.unit_id', '=', 'u.id')
            ->join('tbl_sales_order_details as sod', 'gdd.sales_order_detail_id', '=', 'sod.id')
            ->where('gdd.goods_delivery_id', $gdId)
            ->where('gdd.qty_delivered', '>', 0)
            ->select(
                'gdd.id as gdd_id',
                'gdd.product_id',
                'gdd.unit_id',
                'gdd.qty_delivered',        // ceiling untuk 3-way match
                'gdd.price as gd_price',
                'gdd.subtotal as gd_subtotal',
                'sod.qty as so_qty',
                'sod.price as so_price',    // harga di SO (untuk deteksi perbedaan harga)
                'b.nama_bahan',
                'u.nama_unit',
            )
            ->get();

        // Hitung qty yang SUDAH di-invoice di SI lain untuk GD ini
        foreach ($items as $item) {
            $alreadyInvoiced = DB::table('tbl_sales_invoice_details as sid')
                ->join('tbl_sales_invoices as si', 'sid.sales_invoice_id', '=', 'si.id')
                ->where('sid.goods_delivery_detail_id', $item->gdd_id)
                ->whereNotIn('si.status', ['CANCELLED'])
                ->sum('sid.qty_invoiced');

            $item->qty_already_invoiced = (float) $alreadyInvoiced;
            $item->qty_max_invoice = max(0, $item->qty_delivered - $alreadyInvoiced);

            // Deteksi perbedaan harga SO vs GD
            $item->price_diff = round($item->gd_price - $item->so_price, 2);
            $item->has_price_diff = abs($item->price_diff) > 0.01;
        }

        return response()->json([
            'status' => 'success',
            'gd' => $gd,
            'items' => $items,
        ]);
    }

    public function storeSalesInvoice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'goods_delivery_id' => 'required|integer|exists:tbl_goods_deliveries,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'items' => 'required|array|min:1',
            'items.*.gdd_id' => 'required|integer',
            'items.*.product_id' => 'required|integer',
            'items.*.unit_id' => 'required|integer',
            'items.*.qty_delivered' => 'required|numeric|min:0',
            'items.*.qty_invoiced' => 'required|numeric|min:0',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $adaItem = collect($request->items)->some(fn($i) => ($i['qty_invoiced'] ?? 0) > 0);
        if (!$adaItem) {
            return response()->json([
                'status' => 'error',
                'message' => 'Minimal satu item harus memiliki qty invoice lebih dari 0.',
            ], 422);
        }

        DB::beginTransaction();
        try {

            // =================================================================
            // 3-WAY MATCH VALIDATION — SO <-> GD <-> Sales Invoice
            // Rule: qty_invoiced TIDAK BOLEH melebihi qty_delivered di GD
            // Kita tidak boleh menagih outlet lebih dari yang sudah dikirim.
            // =================================================================
            foreach ($request->items as $item) {
                $qtyInvoiced = (float) ($item['qty_invoiced'] ?? 0);
                if ($qtyInvoiced <= 0)
                    continue;

                // Ambil qty_delivered dari baris GD (hard ceiling)
                $qtyDelivered = DB::table('tbl_goods_delivery_details')
                    ->where('id', $item['gdd_id'])
                    ->value('qty_delivered');

                // Hitung sudah berapa yang di-invoice di SI lain
                $alreadyInvoiced = DB::table('tbl_sales_invoice_details as sid')
                    ->join('tbl_sales_invoices as si', 'sid.sales_invoice_id', '=', 'si.id')
                    ->where('sid.goods_delivery_detail_id', $item['gdd_id'])
                    ->whereNotIn('si.status', ['CANCELLED'])
                    ->sum('sid.qty_invoiced');

                $maxBolehDiInvoice = $qtyDelivered - $alreadyInvoiced;

                if ($qtyInvoiced > $maxBolehDiInvoice) {
                    DB::rollBack();
                    $bahan = DB::table('tbl_bahan_scm')
                        ->where('id', $item['product_id'])
                        ->value('nama_bahan');

                    return response()->json([
                        'status' => 'error',
                        'message' => "3-way match GAGAL untuk '{$bahan}': "
                            . "qty invoice ({$qtyInvoiced}) melebihi qty yang sudah dikirim ({$maxBolehDiInvoice}). "
                            . "Outlet tidak bisa ditagih lebih dari barang yang sudah diterima.",
                    ], 422);
                }
            }

            // =================================================================
            // Generate nomor SI (dengan lockForUpdate — anti race condition)
            // =================================================================
            $today = Carbon::now()->format('Ymd');
            $prefix = 'SI' . $today;

            $lastSi = DB::table('tbl_sales_invoices')
                ->where('invoice_number', 'LIKE', $prefix . '%')
                ->orderBy('invoice_number', 'desc')
                ->lockForUpdate()
                ->value('invoice_number');

            $nextNum = $lastSi
                ? str_pad((int) substr($lastSi, -4) + 1, 4, '0', STR_PAD_LEFT)
                : '0001';
            $siNumber = $prefix . $nextNum;

            // =================================================================
            // Kalkulasi finansial
            // =================================================================
            $subtotal = 0;
            foreach ($request->items as $item) {
                $qty = (float) ($item['qty_invoiced'] ?? 0);
                $price = (float) ($item['price'] ?? 0);
                $discount = (float) ($item['discount'] ?? 0);
                $subtotal += ($qty * $price) - $discount;
            }

            $discountHeader = (float) ($request->discount ?? 0);
            $dpp = $subtotal - $discountHeader;
            $vatPct = (float) ($request->vat_percent ?? 11);
            $vatAmount = round($dpp * $vatPct / 100, 2);
            $totalAmount = $dpp + $vatAmount;

            // Ambil info GD untuk denormalisasi customer
            $gd = DB::table('tbl_goods_deliveries')->where('id', $request->goods_delivery_id)->first();

            // =================================================================
            // Simpan Header SI
            // =================================================================
            $siId = DB::table('tbl_sales_invoices')->insertGetId([
                'invoice_number' => $siNumber,
                'goods_delivery_id' => $request->goods_delivery_id,
                'customer_id' => $gd->customer_id,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'payment_terms_days' => $request->payment_terms_days ?? 30,
                'subtotal' => $subtotal,
                'discount' => $discountHeader,
                'dpp' => $dpp,
                'vat_amount' => $vatAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'status' => 'ISSUED',  // langsung ISSUED saat dibuat
                'currency' => $request->currency ?? 'IDR',
                'rate' => $request->rate ?? 1,
                'notes' => $request->notes,
                'billing_address' => $request->billing_address ?? $gd->delivery_address,
                'created_by' => Auth::id(),
                'issued_by' => Auth::id(),
                'issued_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // =================================================================
            // Simpan Detail SI (satu baris per item)
            // =================================================================
            foreach ($request->items as $item) {
                $qtyInvoiced = (float) ($item['qty_invoiced'] ?? 0);
                if ($qtyInvoiced <= 0)
                    continue;

                $price = (float) ($item['price'] ?? 0);
                $discount = (float) ($item['discount'] ?? 0);

                DB::table('tbl_sales_invoice_details')->insert([
                    'sales_invoice_id' => $siId,
                    'goods_delivery_detail_id' => $item['gdd_id'],
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'],
                    'qty_delivered' => $item['qty_delivered'],   // snapshot dari GD
                    'qty_invoiced' => $qtyInvoiced,
                    'price' => $price,
                    'subtotal' => ($qtyInvoiced * $price) - $discount,
                    'notes' => $item['notes'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Update SO status → INVOICED
            DB::table('tbl_sales_orders')
                ->where('id', $gd->sales_order_id)
                ->update(['status' => 'INVOICED', 'updated_at' => now()]);

            DB::commit();

            Log::info("SI {$siNumber} dibuat oleh user " . Auth::id() . " untuk GD #{$request->goods_delivery_id}");

            return response()->json([
                'status' => 'success',
                'message' => "Sales Invoice {$siNumber} berhasil dibuat dan diterbitkan ke outlet.",
                'si_number' => $siNumber,
                'si_id' => $siId,
                'total' => $totalAmount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SalesInvoice store failed: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan SI: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function recordPaymentSalesInvoice(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        DB::beginTransaction();
        try {
            $si = DB::table('tbl_sales_invoices')
                ->where('id', $id)
                ->lockForUpdate()
                ->first();

            if (!$si) {
                return response()->json(['status' => 'error', 'message' => 'Invoice tidak ditemukan.'], 404);
            }

            if (!in_array($si->status, ['ISSUED', 'PARTIAL_PAID', 'OVERDUE'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Invoice harus berstatus ISSUED, PARTIAL_PAID, atau OVERDUE untuk dicatat pembayarannya.",
                ], 422);
            }

            $paymentAmount = (float) $request->payment_amount;
            $outstanding = $si->total_amount - $si->paid_amount;

            if ($paymentAmount > $outstanding) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Jumlah bayar (Rp " . number_format($paymentAmount, 0, ',', '.') . ") "
                        . "melebihi outstanding (Rp " . number_format($outstanding, 0, ',', '.') . ").",
                ], 422);
            }

            $newPaidAmount = $si->paid_amount + $paymentAmount;
            $newStatus = $newPaidAmount >= $si->total_amount ? 'PAID' : 'PARTIAL_PAID';

            DB::table('tbl_sales_invoices')->where('id', $id)->update([
                'paid_amount' => $newPaidAmount,
                'status' => $newStatus,
                'updated_at' => now(),
            ]);

            DB::commit();

            Log::info("SI {$si->invoice_number} pembayaran Rp {$paymentAmount}. Status: {$newStatus}");

            return response()->json([
                'status' => 'success',
                'message' => "Pembayaran Rp " . number_format($paymentAmount, 0, ',', '.')
                    . " berhasil dicatat. Status: {$newStatus}.",
                'new_status' => $newStatus,
                'outstanding' => $si->total_amount - $newPaidAmount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SI recordPayment failed: ' . $e->getMessage(), ['si_id' => $id]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function showSalesInvoice($id)
    {
        $si = DB::table('tbl_sales_invoices as si')
            ->join('tbl_goods_deliveries as gd', 'si.goods_delivery_id', '=', 'gd.id')
            ->join('tbl_sales_orders as so', 'gd.sales_order_id', '=', 'so.id')
            ->join('tbl_outlets as o', 'si.customer_id', '=', 'o.id')
            ->where('si.id', $id)
            ->select('si.*', 'gd.gd_number', 'so.so_number', 'o.nama_outlet as customer_name')
            ->first();

        if (!$si) {
            return response()->json(['status' => 'error', 'message' => 'Invoice tidak ditemukan.'], 404);
        }

        $details = DB::table('tbl_sales_invoice_details as sid')
            ->join('tbl_bahan_scm as b', 'sid.product_id', '=', 'b.id')
            ->leftJoin('tbl_units as u', 'sid.unit_id', '=', 'u.id')
            ->where('sid.sales_invoice_id', $id)
            ->select('sid.*', 'b.nama_bahan', 'u.nama_unit')
            ->get();

        return response()->json([
            'status' => 'success',
            'si' => $si,
            'details' => $details,
        ]);
    }

    // === PURCHASE INVOICE ===
    public function indexPurchaseInvoice()
    {
        $invoices = DB::table('tbl_purchase_invoices as pi')
            ->join('tbl_suppliers as s', 'pi.supplier_id', '=', 's.id')
            ->join('tbl_goods_receipts as gr', 'pi.goods_receipt_id', '=', 'gr.id')
            ->join('tbl_purchase_orders as po', 'gr.purchase_order_id', '=', 'po.id')
            ->select(
                'pi.id',
                'pi.invoice_number',
                'pi.supplier_invoice_number',
                'pi.invoice_date',
                'pi.due_date',
                'pi.subtotal',
                'pi.dpp',
                'pi.vat_amount',
                'pi.total_amount',
                'pi.paid_amount',
                'pi.status',
                'pi.currency',
                'pi.created_at',
                's.supplier_name',
                'gr.gr_number',
                'po.po_number',
            )
            ->whereNull('pi.deleted_at')
            ->orderBy('pi.created_at', 'desc')
            ->get();

        // Hitung outstanding (belum bayar) per invoice
        foreach ($invoices as $inv) {
            $inv->outstanding = $inv->total_amount - $inv->paid_amount;
            $inv->is_overdue = $inv->status === 'APPROVED'
                && Carbon::parse($inv->due_date)->isPast();
        }

        // GR yang siap di-invoice:
        // - Status RECEIVED atau PARTIAL (sudah ada barang masuk)
        // - Belum punya PI yang aktif (PENDING/APPROVED/PARTIAL_PAID/PAID)
        // - Pakai leftJoin ke PO agar GR tanpa PO tetap muncul
        $readyGRs = DB::table('tbl_goods_receipts as gr')
            ->leftJoin('tbl_purchase_orders as po', 'gr.purchase_order_id', '=', 'po.id')
            ->join('tbl_suppliers as s', 'gr.supplier_id', '=', 's.id')
            ->whereIn('gr.status', ['RECEIVED', 'PARTIAL'])
            ->whereNotExists(function ($q) {
                // Blokir hanya jika sudah ada PI aktif (bukan DRAFT dan bukan CANCELLED)
                // PI berstatus DRAFT masih boleh dibuat ulang
                $q->select(DB::raw(1))
                    ->from('tbl_purchase_invoices as pi2')
                    ->whereColumn('pi2.goods_receipt_id', 'gr.id')
                    ->whereNotIn('pi2.status', ['DRAFT', 'CANCELLED']);
            })
            ->select(
                'gr.id',
                'gr.gr_number',
                'gr.receipt_date',
                'gr.total_amount',
                'gr.supplier_id',
                'gr.status as gr_status',
                DB::raw("COALESCE(po.po_number, '-') as po_number"),
                's.supplier_name',
            )
            ->orderBy('gr.created_at', 'desc')
            ->get();

        // Widget ringkasan
        $summary = [
            'total' => DB::table('tbl_purchase_invoices')->whereNull('deleted_at')->count(),
            'pending' => DB::table('tbl_purchase_invoices')->whereIn('status', ['DRAFT', 'PENDING'])->count(),
            'approved' => DB::table('tbl_purchase_invoices')->where('status', 'APPROVED')->count(),
            'outstanding' => DB::table('tbl_purchase_invoices')
                ->whereIn('status', ['APPROVED', 'PARTIAL_PAID'])
                ->whereNull('deleted_at')
                ->sum(DB::raw('total_amount - paid_amount')),
        ];

        return view('Purchasing.SCM.purchaseInvoice', compact(
            'invoices',
            'readyGRs',
            'summary'
        ));
    }

    public function getGrDetails($grId)
    {
        $gr = DB::table('tbl_goods_receipts as gr')
            ->join('tbl_purchase_orders as po', 'gr.purchase_order_id', '=', 'po.id')
            ->join('tbl_suppliers as s', 'gr.supplier_id', '=', 's.id')
            ->where('gr.id', $grId)
            ->select('gr.*', 'po.po_number', 'po.discount', 's.supplier_name')
            ->first();

        if (!$gr) {
            return response()->json(['status' => 'error', 'message' => 'GR tidak ditemukan.'], 404);
        }

        if ($gr->status !== 'RECEIVED') {
            return response()->json([
                'status' => 'error',
                'message' => "GR {$gr->gr_number} belum berstatus RECEIVED. Konfirmasi GR terlebih dahulu.",
            ], 422);
        }

        // Ambil detail item GR dengan info PO asalnya (untuk 3-way match display)
        $items = DB::table('tbl_goods_receipt_details as grd')
            ->join('tbl_bahan_scm as b', 'grd.product_id', '=', 'b.id')
            ->leftJoin('tbl_units as u', 'grd.unit_id', '=', 'u.id')
            ->join('tbl_purchase_order_details as pod', 'grd.purchase_order_detail_id', '=', 'pod.id')
            ->where('grd.goods_receipt_id', $grId)
            ->where('grd.qty_received', '>', 0)
            ->select(
                'grd.id as grd_id',
                'grd.product_id',
                'grd.unit_id',
                'grd.qty_received',          // max qty yang boleh di-invoice (3-way match)
                'grd.price as gr_price',      // harga aktual saat terima
                'grd.subtotal as gr_subtotal',
                'pod.po_qty',
                'pod.price as po_price',      // harga di PO (untuk deteksi perbedaan harga)
                'b.nama_bahan',
                'u.nama_unit',
            )
            ->get();

        // Hitung qty yang SUDAH di-invoice sebelumnya (kalau ada PI lain untuk GR ini)
        foreach ($items as $item) {
            $alreadyInvoiced = DB::table('tbl_purchase_invoice_details as pid')
                ->join('tbl_purchase_invoices as pi', 'pid.purchase_invoice_id', '=', 'pi.id')
                ->where('pid.goods_receipt_detail_id', $item->grd_id)
                ->whereNotIn('pi.status', ['CANCELLED'])
                ->sum('pid.qty_invoiced');

            $item->qty_already_invoiced = (float) $alreadyInvoiced;
            $item->qty_max_invoice = max(0, $item->qty_received - $alreadyInvoiced);

            // Flag: apakah ada perbedaan harga PO vs GR? (warning ke user)
            $item->price_diff = round($item->gr_price - $item->po_price, 2);
            $item->has_price_diff = abs($item->price_diff) > 0.01;
        }

        return response()->json([
            'status' => 'success',
            'gr' => $gr,
            'items' => $items,
        ]);
    }

    public function storePurchaseInvoice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'goods_receipt_id' => 'required|integer|exists:tbl_goods_receipts,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'items' => 'required|array|min:1',
            'items.*.grd_id' => 'required|integer',
            'items.*.product_id' => 'required|integer',
            'items.*.unit_id' => 'required|integer',
            'items.*.qty_received' => 'required|numeric|min:0',
            'items.*.qty_invoiced' => 'required|numeric|min:0',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $adaItem = collect($request->items)->some(fn($i) => ($i['qty_invoiced'] ?? 0) > 0);
        if (!$adaItem) {
            return response()->json([
                'status' => 'error',
                'message' => 'Minimal satu item harus memiliki qty invoice lebih dari 0.',
            ], 422);
        }

        DB::beginTransaction();
        try {

            // =================================================================
            // 3-WAY MATCH VALIDATION
            // Rule: qty_invoiced TIDAK BOLEH melebihi qty_received di GR
            // Ini adalah inti dari 3-way match: PO <-> GR <-> Invoice
            // =================================================================
            foreach ($request->items as $item) {
                $qtyInvoiced = (float) ($item['qty_invoiced'] ?? 0);
                if ($qtyInvoiced <= 0)
                    continue;

                // Ambil qty_received dari baris GR ini (hard ceiling)
                $qtyReceived = DB::table('tbl_goods_receipt_details')
                    ->where('id', $item['grd_id'])
                    ->value('qty_received');

                // Hitung sudah berapa yang di-invoice di PI sebelumnya
                $alreadyInvoiced = DB::table('tbl_purchase_invoice_details as pid')
                    ->join('tbl_purchase_invoices as pi', 'pid.purchase_invoice_id', '=', 'pi.id')
                    ->where('pid.goods_receipt_detail_id', $item['grd_id'])
                    ->whereNotIn('pi.status', ['CANCELLED'])
                    ->sum('pid.qty_invoiced');

                $maxBolehDiInvoice = $qtyReceived - $alreadyInvoiced;

                // REJECT jika melebihi batas
                if ($qtyInvoiced > $maxBolehDiInvoice) {
                    DB::rollBack();
                    $bahan = DB::table('tbl_bahan_scm')
                        ->where('id', $item['product_id'])
                        ->value('nama_bahan');

                    return response()->json([
                        'status' => 'error',
                        'message' => "3-way match GAGAL untuk '{$bahan}': "
                            . "qty invoice ({$qtyInvoiced}) melebihi qty diterima yang bisa di-invoice ({$maxBolehDiInvoice}). "
                            . "Supplier tidak boleh menagih lebih dari yang sudah diterima.",
                    ], 422);
                }
            }

            // =================================================================
            // Generate nomor PI dengan lockForUpdate (anti race condition)
            // =================================================================
            $today = Carbon::now()->format('Ymd');
            $prefix = 'PI' . $today;

            $lastPi = DB::table('tbl_purchase_invoices')
                ->where('invoice_number', 'LIKE', $prefix . '%')
                ->orderBy('invoice_number', 'desc')
                ->lockForUpdate()
                ->value('invoice_number');

            $nextNum = $lastPi
                ? str_pad((int) substr($lastPi, -4) + 1, 4, '0', STR_PAD_LEFT)
                : '0001';
            $piNumber = $prefix . $nextNum;

            // =================================================================
            // Kalkulasi finansial
            // =================================================================
            $subtotal = 0;
            foreach ($request->items as $item) {
                $qty = (float) ($item['qty_invoiced'] ?? 0);
                $price = (float) ($item['price'] ?? 0);
                $discount = (float) ($item['discount'] ?? 0);
                $subtotal += ($qty * $price) - $discount;
            }

            $discountHeader = (float) ($request->discount ?? 0);
            $dpp = $subtotal - $discountHeader;
            $vatPct = (float) ($request->vat_percent ?? 11);
            $vatAmount = round($dpp * $vatPct / 100, 2);
            $totalAmount = $dpp + $vatAmount;

            // Ambil info GR untuk denormalisasi supplier
            $gr = DB::table('tbl_goods_receipts')->where('id', $request->goods_receipt_id)->first();

            // =================================================================
            // Simpan Header PI
            // =================================================================
            $piId = DB::table('tbl_purchase_invoices')->insertGetId([
                'invoice_number' => $piNumber,
                'supplier_invoice_number' => $request->supplier_invoice_number,
                'goods_receipt_id' => $request->goods_receipt_id,
                'supplier_id' => $gr->supplier_id,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'received_date' => $request->received_date ?? now()->toDateString(),
                'subtotal' => $subtotal,
                'discount' => $discountHeader,
                'dpp' => $dpp,
                'vat_amount' => $vatAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'status' => 'PENDING',
                'currency' => $request->currency ?? 'IDR',
                'rate' => $request->rate ?? 1,
                'notes' => $request->notes,
                'attachment_path' => null,
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // =================================================================
            // Simpan Detail PI (satu baris per item)
            // =================================================================
            foreach ($request->items as $item) {
                $qtyInvoiced = (float) ($item['qty_invoiced'] ?? 0);
                if ($qtyInvoiced <= 0)
                    continue;

                $price = (float) ($item['price'] ?? 0);
                $discount = (float) ($item['discount'] ?? 0);

                DB::table('tbl_purchase_invoice_details')->insert([
                    'purchase_invoice_id' => $piId,
                    'goods_receipt_detail_id' => $item['grd_id'],
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'],
                    'qty_received' => $item['qty_received'],  // snapshot dari GR
                    'qty_invoiced' => $qtyInvoiced,
                    'price' => $price,
                    'subtotal' => ($qtyInvoiced * $price) - $discount,
                    'notes' => $item['notes'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            Log::info("PI {$piNumber} dibuat oleh user " . Auth::id() . " untuk GR #{$request->goods_receipt_id}");

            return response()->json([
                'status' => 'success',
                'message' => "Purchase Invoice {$piNumber} berhasil dibuat.",
                'pi_number' => $piNumber,
                'pi_id' => $piId,
                'total' => $totalAmount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PurchaseInvoice store failed: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan PI: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function approvePurchaseInvoice($id)
    {
        $pi = DB::table('tbl_purchase_invoices')->where('id', $id)->first();

        if (!$pi) {
            return response()->json(['status' => 'error', 'message' => 'Invoice tidak ditemukan.'], 404);
        }

        if ($pi->status !== 'PENDING') {
            return response()->json([
                'status' => 'error',
                'message' => "Hanya invoice berstatus PENDING yang bisa di-approve. Status sekarang: {$pi->status}",
            ], 422);
        }

        DB::table('tbl_purchase_invoices')->where('id', $id)->update([
            'status' => 'APPROVED',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info("PI {$pi->invoice_number} di-approve oleh user " . Auth::id());

        return response()->json([
            'status' => 'success',
            'message' => "Invoice {$pi->invoice_number} berhasil di-approve. Siap untuk pembayaran.",
        ]);
    }

    public function recordPayment(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        DB::beginTransaction();
        try {
            $pi = DB::table('tbl_purchase_invoices')
                ->where('id', $id)
                ->lockForUpdate()
                ->first();

            if (!$pi) {
                return response()->json(['status' => 'error', 'message' => 'Invoice tidak ditemukan.'], 404);
            }

            if (!in_array($pi->status, ['APPROVED', 'PARTIAL_PAID'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Invoice harus berstatus APPROVED atau PARTIAL_PAID untuk dicatat pembayarannya.",
                ], 422);
            }

            $paymentAmount = (float) $request->payment_amount;
            $outstanding = $pi->total_amount - $pi->paid_amount;

            if ($paymentAmount > $outstanding) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Jumlah bayar (Rp " . number_format($paymentAmount, 0, ',', '.') . ") "
                        . "melebihi outstanding (Rp " . number_format($outstanding, 0, ',', '.') . ").",
                ], 422);
            }

            $newPaidAmount = $pi->paid_amount + $paymentAmount;
            $newStatus = $newPaidAmount >= $pi->total_amount ? 'PAID' : 'PARTIAL_PAID';

            DB::table('tbl_purchase_invoices')->where('id', $id)->update([
                'paid_amount' => $newPaidAmount,
                'status' => $newStatus,
                'updated_at' => now(),
            ]);

            DB::commit();

            Log::info("PI {$pi->invoice_number} pembayaran Rp {$paymentAmount} dicatat. Status: {$newStatus}");

            return response()->json([
                'status' => 'success',
                'message' => "Pembayaran Rp " . number_format($paymentAmount, 0, ',', '.')
                    . " berhasil dicatat. Status invoice: {$newStatus}.",
                'new_status' => $newStatus,
                'outstanding' => $pi->total_amount - $newPaidAmount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PI recordPayment failed: ' . $e->getMessage(), ['pi_id' => $id]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function showPurchaseInvoice($id)
    {
        $pi = DB::table('tbl_purchase_invoices as pi')
            ->join('tbl_suppliers as s', 'pi.supplier_id', '=', 's.id')
            ->join('tbl_goods_receipts as gr', 'pi.goods_receipt_id', '=', 'gr.id')
            ->join('tbl_purchase_orders as po', 'gr.purchase_order_id', '=', 'po.id')
            ->where('pi.id', $id)
            ->select('pi.*', 's.supplier_name', 'gr.gr_number', 'po.po_number')
            ->first();

        if (!$pi) {
            return response()->json(['status' => 'error', 'message' => 'Invoice tidak ditemukan.'], 404);
        }

        $details = DB::table('tbl_purchase_invoice_details as pid')
            ->join('tbl_bahan_scm as b', 'pid.product_id', '=', 'b.id')
            ->leftJoin('tbl_units as u', 'pid.unit_id', '=', 'u.id')
            ->where('pid.purchase_invoice_id', $id)
            ->select('pid.*', 'b.nama_bahan', 'u.nama_unit')
            ->get();

        return response()->json([
            'status' => 'success',
            'pi' => $pi,
            'details' => $details,
        ]);
    }

    public function indexTransfer()
    {
        $transfers = DB::table('staging_simple_transfers')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('Purchasing.Staging.SimTransfer', compact('transfers'));
    }

    public function indexSales()
    {
        $sales = DB::table('staging_simple_sales')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('Purchasing.Staging.SimSales', compact('sales'));
    }

    public function indexPurchase()
    {
        $purchases = DB::table('staging_simple_purchases')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('Purchasing.Staging.SimPurchase', compact('purchases'));
    }

    public function detailSales($id)
    {
        // 1. Ambil Header dengan Join ke Branch/Outlet dan Customer
        $sales = DB::table('staging_simple_sales as s')
            ->leftJoin('tbl_outlets as o', 's.branch_id', '=', 'o.esb_branch_id')
            ->leftJoin('tbl_customers as c', 's.customer_id', '=', 'c.customerID')
            ->select('s.*', 'o.nama_outlet', 'c.customerName') // Ambil nama-namanya
            ->where('s.id', $id)
            ->first();

        if (!$sales) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        // 2. Ambil Detail dengan Join ke tabel Bahan/Produk
        $details = DB::table('staging_simple_sales_details as d')
            ->leftJoin('tbl_bahan_scm as b', 'd.bahan_id', '=', 'b.id')
            ->select('d.*', 'b.nama_bahan') // Ambil nama bahannya
            ->where('d.staging_sales_id', $id)
            ->get();

        return view('Purchasing.Staging.detailSales', compact('sales', 'details'));
    }

    /**
     * Detail Simple Transfer
     */
    public function detailTransfer($id)
    {
        // Mengambil data header transfer (sesuaikan nama tabel dengan database Anda)
        // Jika tabel Anda bernama tbl_transfers atau staging_transfers
        $transfer = DB::table('staging_simple_transfers')->where('id', $id)->first();

        if (!$transfer) {
            return redirect()->back()->with('error', 'Data Transfer tidak ditemukan.');
        }

        // Mengambil detail item transfer menggunakan receive_id atau id header
        $details = DB::table('staging_simple_transfer_details')
            ->where('staging_transfer_id', $id)
            ->get();

        return view('Purchasing.Staging.detailTransfer', compact('transfer', 'details'));
    }

    /**
     * Detail Simple Purchase
     */
    public function detailPurchase($id)
    {
        // 1. Ambil Header dengan Join ke tabel Supplier
        $purchase = DB::table('staging_simple_purchases as p')
            ->leftJoin('tbl_suppliers as s', 'p.supplier_id', '=', 's.supplier_id') // Sesuaikan kolom joinnya
            ->select('p.*', 's.supplier_name')
            ->where('p.id', $id)
            ->first();

        if (!$purchase) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        // 2. Ambil Detail dengan Join ke tabel Bahan dan Satuan/Unit
        $details = DB::table('staging_simple_purchase_details as d')
            ->leftJoin('tbl_bahan_scm as b', 'd.bahan_id', '=', 'b.id')
            ->leftJoin('tbl_units as u', 'd.unit_id', '=', 'u.id') // Join ke master satuan
            ->select('d.*', 'b.nama_bahan', 'u.nama_unit')
            ->where('d.staging_purchase_id', $id)
            ->get();

        return view('Purchasing.Staging.detailPurchase', compact('purchase', 'details'));
    }


    public function syncBahan(Request $request)
    {
        // 1. Fungsi untuk MEMULAI Sinkronisasi (Trigger Job)
        if ($request->ajax() && $request->isMethod('post')) {
            try {
                // Reset status progres di cache ke awal
                \Cache::put('sync_product_progress', [
                    'percentage' => 0,
                    'processed' => 0,
                    'total' => 0,
                    'status' => 'running'
                ], 600);

                // Picu Job halaman pertama ke antrean
                \App\Jobs\SyncBahanJob::dispatch(1)->onQueue('bahan');

                return response()->json(['status' => 'started']);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
            }
        }

        // 2. Fungsi untuk MENGECEK Progres (Polling)
        if ($request->ajax() && $request->isMethod('get')) {
            $progress = \Cache::get('sync_product_progress', [
                'percentage' => 0,
                'status' => 'waiting'
            ]);
            return response()->json($progress);
        }

        return back()->with('error', 'Metode sinkronisasi tidak valid.');
    }

    protected $bahanService;

    // WAJIB: Masukkan service lewat constructor
    public function __construct(BahanService $bahanService)
    {
        $this->bahanService = $bahanService;
    }

    public function syncUnits(Request $request)
    {
        $page = $request->get('page', 1);
        $result = $this->bahanService->syncUnitsFromApi($page);

        return response()->json($result);
    }

    public function indexCustomer()
    {
        // Ambil data dari tabel customers langsung
        $customers = DB::table('tbl_customers')
            ->orderBy('customerID', 'desc')
            ->get();

        return view('Purchasing.customerList', compact('customers'));
    }

    public function syncCustomer()
    {
        // Masukkan proses ke dalam antrean (Queue)
        SyncCustomerJob::dispatch();

        return redirect()->back()->with('status', 'Sinkronisasi sedang diproses di background. Silakan cek berkala.');
    }

    public function syncSupplier()
    {
        // 1. Ambil semua mitra yang aktif (Hasilnya adalah Collection)
        $credentials = DB::table('tbl_api_credentials')
            ->where('is_active', 1)
            ->get();

        if ($credentials->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada kredensial aktif.');
        }

        // 2. Lakukan looping untuk mengakses ID setiap item
        foreach ($credentials as $cred) {
            // Ambil token dari session jika ada
            $session = DB::table('tbl_api_sessions')
                ->where('credential_id', $cred->id) // Sekarang $cred->id bisa diakses
                ->first();

            $token = $session ? $session->bearer_token : null;

            // 3. Dispatch masing-masing mitra ke Job
            SyncSupplierJob::dispatch($token, $cred->id);
        }

        return redirect()->back()->with('status', 'Proses sinkronisasi untuk ' . $credentials->count() . ' mitra telah masuk antrean.');
    }

    public function syncLoc()
    {
        // 1. Ambil semua mitra yang aktif
        $credentials = DB::table('tbl_api_credentials')
            ->where('is_active', 1)
            ->get();

        if ($credentials->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada kredensial aktif.');
        }

        // 2. Looping setiap mitra
        foreach ($credentials as $cred) {
            // Ambil token dari session milik mitra tersebut
            $session = DB::table('tbl_api_sessions')
                ->where('credential_id', $cred->id)
                ->first();

            // Kita kirim ID mitranya saja ke Job. 
            // Biar Job yang urus soal token (termasuk refresh token kalau expired).
            SyncLocationJob::dispatch($cred->id);
        }

        return redirect()->back()->with('status', 'Proses sinkronisasi lokasi untuk ' . $credentials->count() . ' mitra telah masuk antrean.');
    }


    // ==== REPORT ====
    private function defaultDates(Request $request): array
    {
        return [
            'start' => $request->get('start_date', Carbon::now()->subDays(30)->toDateString()),
            'end' => $request->get('end_date', Carbon::now()->toDateString()),
        ];
    }

    // =========================================================================
    // 1. STOCK MOVEMENT REPORT
    //    Sumber: tbl_stock_transactions
    //    Filter: tanggal, bahan, warehouse, tipe (MASUK/KELUAR/ADJUSTMENT/WASTE)
    // =========================================================================
    public function stockMovement(Request $request)
    {
        ['start' => $start, 'end' => $end] = $this->defaultDates($request);

        $query = DB::table('tbl_stock_transactions as st')
            ->join('tbl_bahan_scm as b', 'st.bahan_id', '=', 'b.id')
            ->join('tbl_warehouse as w', 'st.warehouse_id', '=', 'w.id')
            ->leftJoin('tbl_units as u', 'st.unit_id', '=', 'u.id')
            ->whereNull('st.deleted_at')
            ->whereBetween(DB::raw('DATE(st.created_at)'), [$start, $end]);

        // Filter opsional
        if ($request->filled('bahan_id')) {
            $query->where('st.bahan_id', $request->bahan_id);
        }
        if ($request->filled('warehouse_id')) {
            $query->where('st.warehouse_id', $request->warehouse_id);
        }
        if ($request->filled('tipe')) {
            $query->where('st.tipe', $request->tipe);
        }
        if ($request->filled('search')) {
            $query->where('b.nama_bahan', 'like', '%' . $request->search . '%');
        }

        $movements = $query
            ->select(
                'st.id',
                'st.created_at',
                'st.tipe',
                'st.jumlah',
                'st.stok_sebelum',
                'st.stok_sesudah',
                'st.harga_satuan',
                'st.total_nilai',
                'st.keterangan',
                'st.reference_type',
                'st.reference_id',
                'b.nama_bahan',
                'b.product_code',
                'w.nama_warehouse',
                'u.nama_unit'
            )
            ->orderBy('st.created_at', 'desc')
            ->paginate(50)
            ->withQueryString();

        // Jika user klik export Excel
        if ($request->export === 'excel') {
            $dataExport = $query->get();

            // 1. Ambil tanggal dari filter
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            // 2. Ambil waktu download (tanggal sekarang)
            $waktuSekarang = Carbon::now()->format('Ymd_His'); // Contoh hasil: 20260522_082722

            // 3. Susun nama file
            $fileName = 'Stock_Movement_Report';

            // Jika filter dari & sampai tanggal diisi, tambahkan ke nama file
            if ($startDate && $endDate) {
                $fileName .= "_Periode_{$startDate}_sd_{$endDate}";
            } elseif ($startDate) {
                $fileName .= "_Sejak_{$startDate}";
            }

            // Tambahkan tanggal cetak/sekarang di akhir beserta ekstensinya
            $fileName .= "_Cetak_{$waktuSekarang}.xlsx";

            // 4. Download dengan nama file dinamis
            return Excel::download(new StockMovementExport($dataExport), $fileName);
        }

        // Summary cards
        $summaryQuery = DB::table('tbl_stock_transactions as st')
            ->join('tbl_bahan_scm as b', 'st.bahan_id', '=', 'b.id')
            ->whereNull('st.deleted_at')
            ->whereBetween(DB::raw('DATE(st.created_at)'), [$start, $end]);

        if ($request->filled('bahan_id'))
            $summaryQuery->where('st.bahan_id', $request->bahan_id);
        if ($request->filled('warehouse_id'))
            $summaryQuery->where('st.warehouse_id', $request->warehouse_id);

        $summary = [
            'total_masuk' => (clone $summaryQuery)->where('st.tipe', 'MASUK')->sum('st.jumlah'),
            'total_keluar' => (clone $summaryQuery)->where('st.tipe', 'KELUAR')->sum('st.jumlah'),
            'total_adjustment' => (clone $summaryQuery)->where('st.tipe', 'ADJUSTMENT')->count(),
            'total_nilai' => (clone $summaryQuery)->sum('st.total_nilai'),
        ];

        // Chart data: gerakan per hari (7 hari terakhir dalam range)
        $chartData = DB::table('tbl_stock_transactions as st')
            ->whereNull('st.deleted_at')
            ->whereBetween(DB::raw('DATE(st.created_at)'), [$start, $end])
            ->whereIn('st.tipe', ['MASUK', 'KELUAR'])
            ->select(
                DB::raw('DATE(st.created_at) as tgl'),
                'st.tipe',
                DB::raw('SUM(st.jumlah) as total')
            )
            ->groupBy('tgl', 'st.tipe')
            ->orderBy('tgl')
            ->get()
            ->groupBy('tgl');

        // Master filter data
        $allProducts = DB::table('tbl_bahan_scm')->select('id', 'nama_bahan')->orderBy('nama_bahan')->get();
        $allWarehouses = DB::table('tbl_warehouse')->select('id', 'nama_warehouse')->orderBy('nama_warehouse')->get();

        return view('Purchasing.SCM.stockMovement', compact(
            'movements',
            'summary',
            'chartData',
            'allProducts',
            'allWarehouses',
            'start',
            'end'
        ));
    }

    // =========================================================================
    // 2. STOCK OPNAME REPORT
    //    Sumber: tbl_stock_opname + tbl_stock_opname_items
    //    Filter: tanggal, warehouse, status (draft/confirmed)
    // =========================================================================
    public function stockOpname(Request $request)
    {
        ['start' => $start, 'end' => $end] = $this->defaultDates($request);

        $query = DB::table('tbl_stock_opname as so')
            ->join('tbl_warehouse as w', 'so.warehouse_id', '=', 'w.id')
            ->whereBetween(DB::raw('DATE(so.created_at)'), [$start, $end])
            ->whereNull('so.deleted_at');

        if ($request->filled('warehouse_id'))
            $query->where('so.warehouse_id', $request->warehouse_id);
        if ($request->filled('status'))
            $query->where('so.status', $request->status);

        $opnames = $query
            ->select(
                'so.id',
                'so.nomor_opname',
                'so.tanggal_opname',
                'so.status',
                'so.jenis_adjustment',
                'so.keterangan',
                'so.total_nilai',
                'so.created_at',
                'w.nama_warehouse'
            )
            ->orderBy('so.created_at', 'desc')
            ->paginate(30)
            ->withQueryString();

        // Items per opname (untuk expand row)
        $opnameIds = $opnames->pluck('id');
        $allItems = DB::table('tbl_stock_opname_items as soi')
            ->join('tbl_bahan_scm as b', 'soi.bahan_id', '=', 'b.id')
            ->leftJoin('tbl_units as u', 'soi.unit_id', '=', 'u.id')
            ->whereIn('soi.opname_id', $opnameIds)
            ->select('soi.*', 'b.nama_bahan', 'b.product_code', 'u.nama_unit')
            ->get()
            ->groupBy('opname_id');

        // Summary
        $summaryBase = DB::table('tbl_stock_opname')
            ->whereNull('deleted_at')
            ->whereBetween(DB::raw('DATE(created_at)'), [$start, $end]);

        $summary = [
            'total' => (clone $summaryBase)->count(),
            'confirmed' => (clone $summaryBase)->where('status', 'confirmed')->count(),
            'draft' => (clone $summaryBase)->where('status', 'draft')->count(),
            'total_nilai' => (clone $summaryBase)->where('status', 'confirmed')->sum('total_nilai'),
        ];

        $allWarehouses = DB::table('tbl_warehouse')->select('id', 'nama_warehouse')->orderBy('nama_warehouse')->get();

        return view('Purchasing.SCM.stockOpname', compact(
            'opnames',
            'allItems',
            'summary',
            'allWarehouses',
            'start',
            'end'
        ));
    }

    // =========================================================================
    // 3. GOODS RECEIPT RECAPITULATION
    //    Sumber: tbl_goods_receipts + tbl_goods_receipt_details
    //    Filter: tanggal, supplier, status, qc_status, bahan
    // =========================================================================
    public function goodsReceiptRecap(Request $request)
    {
        ['start' => $start, 'end' => $end] = $this->defaultDates($request);

        $query = DB::table('tbl_goods_receipts as gr')
            ->join('tbl_purchase_orders as po', 'gr.purchase_order_id', '=', 'po.id')
            ->join('tbl_suppliers as s', 'gr.supplier_id', '=', 's.id')
            ->leftJoin('tbl_warehouse as w', 'gr.warehouse_id', '=', 'w.id')
            ->whereBetween(DB::raw('DATE(gr.receipt_date)'), [$start, $end]);

        if ($request->filled('supplier_id'))
            $query->where('gr.supplier_id', $request->supplier_id);
        if ($request->filled('status'))
            $query->where('gr.status', $request->status);
        if ($request->filled('qc_status'))
            $query->where('gr.qc_status', $request->qc_status);
        if ($request->filled('warehouse_id'))
            $query->where('gr.warehouse_id', $request->warehouse_id);
        if ($request->filled('search'))
            $query->where('s.supplier_name', 'like', '%' . $request->search . '%');

        $receipts = $query->select(
            'gr.id',
            'gr.gr_number',
            'gr.receipt_date',
            'gr.status',
            'gr.qc_status',
            'gr.total_amount',
            'gr.supplier_do_number',
            'gr.driver_name',
            'gr.notes',
            'gr.created_at',
            'po.po_number',
            's.supplier_name',
            'w.nama_warehouse'
        )
            ->orderBy('gr.receipt_date', 'desc')
            ->paginate(30)
            ->withQueryString();

        // Detail items untuk tiap GR
        $grIds = $receipts->pluck('id');
        $grItems = DB::table('tbl_goods_receipt_details as grd')
            ->join('tbl_bahan_scm as b', 'grd.product_id', '=', 'b.id')
            ->leftJoin('tbl_units as u', 'grd.unit_id', '=', 'u.id')
            ->whereIn('grd.goods_receipt_id', $grIds)
            ->select('grd.*', 'b.nama_bahan', 'b.product_code', 'u.nama_unit')
            ->get()
            ->groupBy('goods_receipt_id');

        // Summary cards
        $baseSum = DB::table('tbl_goods_receipts')
            ->whereBetween(DB::raw('DATE(receipt_date)'), [$start, $end]);

        $summary = [
            'total' => (clone $baseSum)->count(),
            'received' => (clone $baseSum)->where('status', 'RECEIVED')->count(),
            'partial' => (clone $baseSum)->where('status', 'PARTIAL')->count(),
            'qc_passed' => (clone $baseSum)->where('qc_status', 'PASSED')->count(),
            'qc_rejected' => (clone $baseSum)->whereIn('qc_status', ['REJECTED', 'PARTIAL_REJECTED'])->count(),
            'total_nilai' => (clone $baseSum)->sum('total_amount'),
        ];

        // Top 5 supplier by nilai
        $topSuppliers = DB::table('tbl_goods_receipts as gr')
            ->join('tbl_suppliers as s', 'gr.supplier_id', '=', 's.id')
            ->whereBetween(DB::raw('DATE(gr.receipt_date)'), [$start, $end])
            ->select('s.supplier_name', DB::raw('SUM(gr.total_amount) as total'), DB::raw('COUNT(gr.id) as jumlah'))
            ->groupBy('s.id', 's.supplier_name')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        $allSuppliers = DB::table('tbl_suppliers')->select('id', 'supplier_name')->orderBy('supplier_name')->get();
        $allWarehouses = DB::table('tbl_warehouse')->select('id', 'nama_warehouse')->orderBy('nama_warehouse')->get();

        return view('Purchasing.SCM.goodsReceiptRecap', compact(
            'receipts',
            'grItems',
            'summary',
            'topSuppliers',
            'allSuppliers',
            'allWarehouses',
            'start',
            'end'
        ));
    }

    // =========================================================================
    // 4. GOODS DELIVERY RECAPITULATION
    //    Sumber: tbl_goods_deliveries + tbl_goods_delivery_details
    //    Filter: tanggal, outlet/customer, warehouse, status, driver
    // =========================================================================
    public function goodsDeliveryRecap(Request $request)
    {
        ['start' => $start, 'end' => $end] = $this->defaultDates($request);

        $query = DB::table('tbl_goods_deliveries as gd')
            ->join('tbl_sales_orders as so', 'gd.sales_order_id', '=', 'so.id')
            ->join('tbl_outlets as o', 'gd.customer_id', '=', 'o.id')
            ->leftJoin('tbl_warehouse as w', 'gd.warehouse_id', '=', 'w.id')
            ->whereBetween(DB::raw('DATE(gd.delivery_date)'), [$start, $end]);

        if ($request->filled('customer_id'))
            $query->where('gd.customer_id', $request->customer_id);
        if ($request->filled('warehouse_id'))
            $query->where('gd.warehouse_id', $request->warehouse_id);
        if ($request->filled('status'))
            $query->where('gd.status', $request->status);
        if ($request->filled('search'))
            $query->where('o.nama_outlet', 'like', '%' . $request->search . '%');

        $deliveries = $query->select(
            'gd.id',
            'gd.gd_number',
            'gd.delivery_date',
            'gd.actual_arrival',
            'gd.status',
            'gd.total_amount',
            'gd.driver_name',
            'gd.vehicle_plate',
            'gd.delivery_address',
            'gd.notes',
            'gd.created_at',
            'so.so_number',
            'o.nama_outlet as customer_name',
            'w.nama_warehouse'
        )
            ->orderBy('gd.delivery_date', 'desc')
            ->paginate(30)
            ->withQueryString();

        // Detail items per GD
        $gdIds = $deliveries->pluck('id');
        $gdItems = DB::table('tbl_goods_delivery_details as gdd')
            ->join('tbl_bahan_scm as b', 'gdd.product_id', '=', 'b.id')
            ->leftJoin('tbl_units as u', 'gdd.unit_id', '=', 'u.id')
            ->whereIn('gdd.goods_delivery_id', $gdIds)
            ->select('gdd.*', 'b.nama_bahan', 'b.product_code', 'u.nama_unit')
            ->get()
            ->groupBy('goods_delivery_id');

        // Summary cards
        $baseSum = DB::table('tbl_goods_deliveries')
            ->whereBetween(DB::raw('DATE(delivery_date)'), [$start, $end]);

        $summary = [
            'total' => (clone $baseSum)->count(),
            'delivered' => (clone $baseSum)->where('status', 'DELIVERED')->count(),
            'in_transit' => (clone $baseSum)->where('status', 'IN_TRANSIT')->count(),
            'draft' => (clone $baseSum)->where('status', 'DRAFT')->count(),
            'total_nilai' => (clone $baseSum)->sum('total_amount'),
        ];

        // Top 5 outlet by nilai pengiriman
        $topOutlets = DB::table('tbl_goods_deliveries as gd')
            ->join('tbl_outlets as o', 'gd.customer_id', '=', 'o.id')
            ->whereBetween(DB::raw('DATE(gd.delivery_date)'), [$start, $end])
            ->select('o.nama_outlet', DB::raw('SUM(gd.total_amount) as total'), DB::raw('COUNT(gd.id) as jumlah'))
            ->groupBy('o.id', 'o.nama_outlet')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        $allOutlets = DB::table('tbl_outlets')->select('id', 'nama_outlet')->orderBy('nama_outlet')->get();
        $allWarehouses = DB::table('tbl_warehouse')->select('id', 'nama_warehouse')->orderBy('nama_warehouse')->get();

        return view('Purchasing.SCM.goodsDeliveryRecap', compact(
            'deliveries',
            'gdItems',
            'summary',
            'topOutlets',
            'allOutlets',
            'allWarehouses',
            'start',
            'end'
        ));
    }
}