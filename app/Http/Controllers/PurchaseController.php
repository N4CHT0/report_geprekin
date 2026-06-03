<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\EsbPurchaseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use App\Jobs\PushToEsb;
use App\Jobs\SyncSimpleSalesJob;
use App\Jobs\SyncSimplePurchaseJob;
use App\Jobs\PushSimpleTransferJob;
use App\Jobs\PushSimpleSalesJob;
use App\Jobs\PushSimplePurchaseJob;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PurchaseController extends Controller
{
    protected $service;

    public function __construct(EsbPurchaseService $service)
    {
        $this->service = $service;
    }

    public function outletDashboard()
    {

        $total_po = DB::table('tbl_po')->count();

        $menunggu = DB::table('tbl_po')
            ->where('status', 'Waiting')
            ->count();

        $disetujui = DB::table('tbl_po')
            ->where('status', 'Approved')
            ->count();

        $ditolak = DB::table('tbl_po')
            ->where('status', 'Rejected')
            ->count();

        $outlets = DB::table('tbl_outlets')->get();
        $bahans = DB::table('tbl_bahan_scm')
            ->leftJoin('tbl_bahan_unit', function ($join) {
                $join->on('tbl_bahan_scm.id', '=', 'tbl_bahan_unit.bahan_id')
                    ->where('tbl_bahan_unit.is_base_unit', '=', 1);
            })
            ->select(
                'tbl_bahan_scm.*',
                'tbl_bahan_unit.unit_id as base_unit_id' // <--- KITA KASIH NAMA INI
            )
            ->get();

        // Ambil data PO untuk ditampilkan di tabel dashboard
        $dataPO = DB::table('tbl_po')
            ->join('tbl_outlets', 'tbl_po.outlet_id', '=', 'tbl_outlets.id')
            ->select('tbl_po.*', 'tbl_outlets.nama_outlet')
            ->get()
            ->map(function ($po) {
                // Ambil detail barang untuk setiap PO
                $po->items = DB::table('tbl_po_detail')
                    ->join('tbl_bahan_scm', 'tbl_po_detail.bahan_id', '=', 'tbl_bahan_scm.id')
                    ->leftJoin('tbl_units', 'tbl_po_detail.unit_id', '=', 'tbl_units.id')
                    ->where('tbl_po_detail.po_id', $po->id)
                    ->select(
                        'tbl_po_detail.*',
                        'tbl_bahan_scm.nama_bahan',
                        'tbl_bahan_scm.sumber_barang', // ← tambah ini
                        'tbl_units.nama_unit as satuan'
                    )
                    ->get();

                return $po; // <--- WAJIB ADA INI AGAR DATA TIDAK NULL
            });

        // dd($outlets);
        return view('Purchasing.dashboardOutlet', [
            'outlets' => $outlets,
            'bahans' => $bahans,
            'dataPO' => $dataPO,
            'total_po' => $total_po,
            'menunggu' => $menunggu,
            'disetujui' => $disetujui,
            'ditolak' => $ditolak
        ]);
    }

    public function outletFormPO()
    {
        // Ambil user yang sedang login
        $user = Auth::user(); // Ambil user yang sedang login (si Crew)
        $myOutlet = null;

        // Ambil ulang data outlet berdasarkan outlet_id si user
        if ($user && !empty($user->outlet_id)) {
            $myOutlet = DB::table('tbl_outlets')
                ->where('id', $user->outlet_id)
                ->first();
        }

        $myOutletId = auth()->user()->outlet_id;

        $baseQuery = DB::table('tbl_po')->where('outlet_id', $myOutletId);

        $total_po = (clone $baseQuery)->count();
        $menunggu = (clone $baseQuery)->where('status', 'Waiting')->count();
        $disetujui = (clone $baseQuery)->where('status', 'Approved')->count();
        $ditolak = (clone $baseQuery)->where('status', 'Rejected')->count();

        $outlets = DB::table('tbl_outlets')->get();
        // --- CARI BAGIAN INI DI CONTROLLER KAMU DAN GANTI DENGAN KODE DI BAWAH ---
        $bahans = DB::table('tbl_bahan_scm')
            // 1. Join ke tabel jembatan (tbl_bahan_unit) ambil yang is_purchase_unit = 1
            ->leftJoin('tbl_bahan_unit', function ($join) {
                $join->on('tbl_bahan_scm.id', '=', 'tbl_bahan_unit.bahan_id')
                    ->where('tbl_bahan_unit.is_purchase_unit', '=', 1);
            })
            // 2. Join ke master tabel unit (tbl_units) untuk mengambil nama teks unitnya
            ->leftJoin('tbl_units', 'tbl_bahan_unit.unit_id', '=', 'tbl_units.id')
            ->select(
                'tbl_bahan_scm.*',
                'tbl_bahan_unit.unit_id as purchase_unit_id', // Kita simpan ID unitnya
                'tbl_units.nama_unit as nama_purchase_unit'   // Kita ambil nama aslinya dari tbl_units (e.g. PACK @50PCS)
            )
            ->get();

        // Ambil data PO untuk ditampilkan di tabel dashboard
        $dataPO = DB::table('tbl_po')
            ->join('tbl_outlets', 'tbl_po.outlet_id', '=', 'tbl_outlets.id')
            // Jika $myOutletId ada, maka filter. Jika null (admin), tampilkan semua.
            ->when($myOutletId, function ($query, $myOutletId) {
                return $query->where('tbl_po.outlet_id', $myOutletId);
            })
            ->select('tbl_po.*', 'tbl_outlets.nama_outlet')
            ->get()
            ->map(function ($po) {
                // Ambil detail barang untuk setiap PO
                $po->items = DB::table('tbl_po_detail')
                    ->join('tbl_bahan_scm', 'tbl_po_detail.bahan_id', '=', 'tbl_bahan_scm.id')
                    ->leftJoin('tbl_units', 'tbl_po_detail.unit_id', '=', 'tbl_units.id')
                    ->where('tbl_po_detail.po_id', $po->id)
                    ->select(
                        'tbl_po_detail.*',
                        'tbl_bahan_scm.nama_bahan',
                        'tbl_bahan_scm.sumber_barang', // ← tambah ini
                        'tbl_units.nama_unit as satuan'
                    )
                    ->get();

                return $po; // <--- WAJIB ADA INI AGAR DATA TIDAK NULL
            });

        return view('Purchasing.Operasional.purchaseRequestForm', [
            'myOutlet' => $myOutlet,
            'outlets' => $outlets,
            'bahans' => $bahans,
            'dataPO' => $dataPO,
            'total_po' => $total_po,
            'menunggu' => $menunggu,
            'disetujui' => $disetujui,
            'ditolak' => $ditolak
        ]);
    }

    //     public function outletDashboard()
    // {
    //     $userId = Auth::id();

    //     // 1. Ambil DAFTAR ID OUTLET yang boleh diakses user ini dari tabel relasi
    //     $allowedOutletIds = DB::table('tbl_users_outlet')
    //         ->where('user_id', $userId)
    //         ->pluck('outlet_id'); // Menghasilkan array, misal: [1, 2]

    //     // Jika user tidak punya akses ke outlet manapun, kasih data kosong agar tidak error
    //     if ($allowedOutletIds->isEmpty()) {
    //         return view('Purchasing.dashboardOutlet', [
    //             'outlets' => collect(), 'bahans' => collect(), 'dataPO' => collect(),
    //             'total_po' => 0, 'menunggu' => 0, 'disetujui' => 0, 'ditolak' => 0
    //         ]);
    //     }

    //     // 2. Filter Statistik hanya untuk outlet milik user tersebut
    //     $total_po = DB::table('tbl_po')->whereIn('outlet_id', $allowedOutletIds)->count();

    //     $menunggu = DB::table('tbl_po')
    //         ->whereIn('outlet_id', $allowedOutletIds)
    //         ->where('status', 'Waiting')
    //         ->count();

    //     $disetujui = DB::table('tbl_po')
    //         ->whereIn('outlet_id', $allowedOutletIds)
    //         ->where('status', 'Approved')
    //         ->count();

    //     $ditolak = DB::table('tbl_po')
    //         ->whereIn('outlet_id', $allowedOutletIds)
    //         ->where('status', 'Rejected')
    //         ->count();

    //     // 3. Filter daftar outlet untuk dropdown (hanya yang boleh diakses)
    //     $outlets = DB::table('tbl_outlets')->whereIn('id', $allowedOutletIds)->get();

    //     $bahans = DB::table('tbl_bahan_scm')
    //         ->leftJoin('tbl_bahan_unit', function ($join) {
    //             $join->on('tbl_bahan_scm.id', '=', 'tbl_bahan_unit.bahan_id')
    //                 ->where('tbl_bahan_unit.is_base_unit', '=', 1);
    //         })
    //         ->select('tbl_bahan_scm.*', 'tbl_bahan_unit.unit_id as base_unit_id')
    //         ->get();

    //     // 4. Ambil data PO (Filter hanya milik outlet si user)
    //     $dataPO = DB::table('tbl_po')
    //         ->join('tbl_outlets', 'tbl_po.outlet_id', '=', 'tbl_outlets.id')
    //         ->whereIn('tbl_po.outlet_id', $allowedOutletIds) // <--- FILTER KRUSIAL
    //         ->select('tbl_po.*', 'tbl_outlets.nama_outlet')
    //         ->get()
    //         ->map(function ($po) {
    //             $po->items = DB::table('tbl_po_detail')
    //                 ->join('tbl_bahan_scm', 'tbl_po_detail.bahan_id', '=', 'tbl_bahan_scm.id')
    //                 ->leftJoin('tbl_units', 'tbl_po_detail.unit_id', '=', 'tbl_units.id')
    //                 ->where('tbl_po_detail.po_id', $po->id)
    //                 ->select('tbl_po_detail.*', 'tbl_bahan_scm.nama_bahan', 'tbl_units.nama_unit as satuan')
    //                 ->get();
    //             return $po;
    //         });

    //     return view('Purchasing.dashboardOutlet', [
    //         'outlets' => $outlets,
    //         'bahans'  => $bahans,
    //         'dataPO' => $dataPO,
    //         'total_po' => $total_po,
    //         'menunggu' => $menunggu,
    //         'disetujui' => $disetujui,
    //         'ditolak' => $ditolak
    //     ]);
    // }

    public function storePO(Request $request)
    {
        // PERBAIKAN: Validasi array dirancang lebih spesifik untuk tipe data kargo SCM
        $request->validate([
            'outlet_id' => 'required',
            'nama_pemesan' => 'required|string|max:255',
            'tgl_permintaan' => 'required|date',
            'bahan_id' => 'required|array|min:1',
            'bahan_id.*' => 'required', // Memastikan setiap baris bahan terisi
            'jumlah' => 'required|array|min:1',
            'jumlah.*' => 'required|numeric|min:0.01', // Validasi angka qty order
            'unit_id' => 'required|array|min:1',
            'unit_id.*' => 'required', // Memastikan unit purchase terikat sempurna
        ], [
            // Custom pesan error bahasa Indonesia agar user outlet tidak bingung
            'bahan_id.*.required' => 'Ada produk/bahan baku yang belum dipilih.',
            'jumlah.*.min' => 'Kuantitas pesanan minimal bernilai 0.01.',
            'unit_id.*.required' => 'Gagal memuat sistem satuan pembelian. Pastikan master data unit aman.',
        ]);

        // 1. Cari dulu DC mana yang melayani outlet ini
        $mapping = DB::table('tbl_mapping_dc')
            ->where('outlet_id', $request->outlet_id)
            ->first();

        if (!$mapping) {
            return redirect()->back()->with('error', 'Outlet ini belum di-mapping ke DC manapun.');
        }

        $warehouseId = $mapping->warehouse_id;

        DB::beginTransaction();

        try {
            // A. Insert ke tbl_po
            $poId = DB::table('tbl_po')->insertGetId([
                'no_po' => 'PO-' . date('YmdHis'),
                'outlet_id' => $request->outlet_id,
                'warehouse_id' => $warehouseId,
                'nama_pemesan' => $request->nama_pemesan,
                'tgl_permintaan' => $request->tgl_permintaan,
                'catatan' => $request->catatan,
                'status' => 'Waiting',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // B. Insert ke tbl_po_detail 
            foreach ($request->bahan_id as $index => $bahanId) {
                $jumlah = $request->jumlah[$index];
                $unitId = $request->unit_id[$index];

                // Pengaman tambahan di tingkat query loop jika ada data corrupt lolos validasi
                if (empty($unitId) || empty($bahanId)) {
                    continue;
                }

                // Insert detail PO
                DB::table('tbl_po_detail')->insert([
                    'po_id' => $poId,
                    'bahan_id' => $bahanId,
                    'unit_id' => $unitId,
                    'jumlah' => $jumlah,
                    'created_at' => now(),
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'PO berhasil dibuat dan dikirim ke backoffice!');
        } catch (\Exception $e) {
            DB::rollBack();
            // Disarankan menggunakan log error agar history development tercatat rapi
            \Log::error('Gagal Create PO SCM: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memproses data: ' . $e->getMessage())->withInput();
        }
    }

    public function destroyPO($id)
    {

        // 1. Ambil data untuk mendapatkan path foto sebelum data di database dihapus
        $data = DB::table('tbl_po')->where('id', $id)->first();

        // Hapus record dari database
        DB::table('tbl_po')->where('id', $id)->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus!');
    }

    // public function getDetailPO($id)
    // {
    //     try {
    //         // 1. Ambil data Header & Credential ID Outlet
    //         $header = DB::table('tbl_po')
    //             ->join('tbl_outlets', 'tbl_po.outlet_id', '=', 'tbl_outlets.id')
    //             ->where('tbl_po.id', $id)
    //             ->select('tbl_po.*', 'tbl_outlets.nama_outlet', 'tbl_outlets.credential_id') // Ambil credential_id
    //             ->first();

    //         if (!$header) {
    //             return response()->json(['error' => 'Data PO tidak ditemukan'], 404);
    //         }

    //         // 2. Ambil semua detail PO + Kolom sumber_barang
    //         $details = DB::table('tbl_po_detail as pd')
    //             ->join('tbl_bahan_scm as b', 'pd.bahan_id', '=', 'b.id')
    //             ->leftJoin('tbl_units as u', 'pd.unit_id', '=', 'u.id')
    //             ->leftJoin('tbl_bahan_unit as bu', function ($join) {
    //                 $join->on('pd.bahan_id', '=', 'bu.bahan_id')
    //                     ->on('pd.unit_id', '=', 'bu.unit_id');
    //             })
    //             ->select(
    //                 'pd.*',
    //                 'b.nama_bahan',
    //                 'b.sumber_barang', // WAJIB ADA untuk logika JS
    //                 'u.nama_unit as satuan',
    //                 'bu.conversion_factor'
    //             )
    //             ->where('pd.po_id', $id)
    //             ->get();

    //         // 3. Tambahkan info "Sudah Diterima"
    //         foreach ($details as $item) {
    //             $sudahDiterima = DB::table('tbl_po_receive_detail')
    //                 ->where('po_id', $id)
    //                 ->where('bahan_id', $item->bahan_id)
    //                 ->sum('qty_terima');

    //             $item->total_diterima_sebelumnya = $sudahDiterima ?? 0;
    //         }

    //         // 4. Ambil Daftar Supplier berdasarkan Credential ID Outlet
    //         $suppliers = DB::table('tbl_suppliers')
    //             ->where('credential_id', $header->credential_id)
    //             // Abaikan semua supplier yang namanya diawali dengan 'SCM'
    //             ->where('supplier_name', 'NOT LIKE', 'SCM%')
    //             ->select('id', 'supplier_name')
    //             ->get();

    //         return response()->json([
    //             'header' => $header,
    //             'details' => $details,
    //             'available_suppliers' => $suppliers // Kirim ini agar JS bisa looping
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }

    public function getDetailPO($id)
    {
        try {
            // 1. Header PO + credential outlet
            $header = DB::table('tbl_po')
                ->join('tbl_outlets', 'tbl_po.outlet_id', '=', 'tbl_outlets.id')
                ->where('tbl_po.id', $id)
                ->select('tbl_po.*', 'tbl_outlets.nama_outlet', 'tbl_outlets.credential_id')
                ->first();

            if (!$header) {
                return response()->json(['error' => 'Data PO tidak ditemukan'], 404);
            }

            // 2. Detail PO + info bahan + split mapping + supplier
            $details = DB::table('tbl_po_detail as pd')
                ->join('tbl_bahan_scm as b', 'pd.bahan_id', '=', 'b.id')
                ->leftJoin('tbl_units as u', 'pd.unit_id', '=', 'u.id')
                ->leftJoin('tbl_bahan_unit as bu', function ($join) {
                    $join->on('pd.bahan_id', '=', 'bu.bahan_id')
                        ->on('pd.unit_id', '=', 'bu.unit_id');
                })
                ->leftJoin('tbl_suppliers as sup', 'pd.supplier_id', '=', 'sup.id')
                ->select(
                    'pd.*',
                    'b.nama_bahan',
                    'b.sumber_barang',
                    'b.split_bahan_besar_id',
                    'b.split_bahan_kecil_id',
                    'u.nama_unit as satuan',
                    'bu.conversion_factor',
                    'bu.base_price as harga_satuan',
                    'sup.supplier_name' // nama supplier sudah fix dari approval SCM
                )
                ->where('pd.po_id', $id)
                ->get();

            // 3. Enrichment per item
            foreach ($details as $item) {
                $isAyam = !is_null($item->split_bahan_besar_id)
                    && !is_null($item->split_bahan_kecil_id);

                // Flag untuk JS agar tahu section mana yang dipakai
                $item->is_split_ayam = $isAyam;

                if ($isAyam) {
                    // Hitung total sudah diterima dari AYAM BESAR + AYAM KECIL
                    $totalBesar = DB::table('tbl_po_receive_detail')
                        ->where('po_id', $id)
                        ->where('bahan_id', $item->split_bahan_besar_id)
                        ->sum('qty_terima');

                    $totalKecil = DB::table('tbl_po_receive_detail')
                        ->where('po_id', $id)
                        ->where('bahan_id', $item->split_bahan_kecil_id)
                        ->sum('qty_terima');

                    // Total ekor yang sudah diterima
                    $item->total_diterima_sebelumnya = $totalBesar + $totalKecil;

                    // Sisa yang belum diterima
                    $item->sisa = max(0, $item->jumlah - $item->total_diterima_sebelumnya);
                } else {
                    // Bahan umum — cek per bahan_id
                    $sudahDiterima = DB::table('tbl_po_receive_detail')
                        ->where('po_id', $id)
                        ->where('bahan_id', $item->bahan_id)
                        ->sum('qty_terima');

                    $item->total_diterima_sebelumnya = $sudahDiterima ?? 0;
                    $item->sisa = max(0, $item->jumlah - $item->total_diterima_sebelumnya);
                }
            }

            // 4. Filter: hanya tampilkan item yang masih punya sisa
            // (item yang sudah 100% diterima tidak perlu muncul lagi di form)
            $details = $details->filter(fn($item) => $item->sisa > 0)->values();

            // 5. Supplier berdasarkan credential outlet
            $suppliers = DB::table('tbl_suppliers')
                ->where('credential_id', $header->credential_id)
                ->where('supplier_name', 'NOT LIKE', 'SCM%')
                ->select('id', 'supplier_name')
                ->get();

            return response()->json([
                'header' => $header,
                'details' => $details,
                'available_suppliers' => $suppliers,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function insertStockTransaction(
        int $warehouseId,
        int $bahanId,
        int $unitId,
        string $tipe,          // 'MASUK' | 'KELUAR' | 'ADJUSTMENT' | 'WASTE'
        float $jumlah,
        string $referenceType, // 'GD' | 'GR' | 'manual' dll
        int $referenceId,
        float $hargaSatuan = 0,
        string $keterangan = ''
    ): void {
        // Hitung stok sebelum dari total MASUK - KELUAR warehouse + bahan ini
        $masuk = DB::table('tbl_stock_transactions')
            ->where('warehouse_id', $warehouseId)
            ->where('bahan_id', $bahanId)
            ->where('unit_id', $unitId)
            ->whereIn('tipe', ['MASUK', 'ADJUSTMENT'])
            ->whereNull('deleted_at')
            ->sum('jumlah');

        $keluar = DB::table('tbl_stock_transactions')
            ->where('warehouse_id', $warehouseId)
            ->where('bahan_id', $bahanId)
            ->where('unit_id', $unitId)
            ->whereIn('tipe', ['KELUAR', 'WASTE'])
            ->whereNull('deleted_at')
            ->sum('jumlah');

        $stokSebelum = max(0, $masuk - $keluar);

        $stokSesudah = match ($tipe) {
            'MASUK', 'ADJUSTMENT' => $stokSebelum + $jumlah,
            'KELUAR', 'WASTE' => max(0, $stokSebelum - $jumlah),
            default => $stokSebelum,
        };

        DB::table('tbl_stock_transactions')->insert([
            'bahan_id' => $bahanId,
            'unit_id' => $unitId,
            'warehouse_id' => $warehouseId,
            'jumlah' => $jumlah,
            'stok_sebelum' => $stokSebelum,
            'stok_sesudah' => $stokSesudah,
            'tipe' => $tipe,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'harga_satuan' => $hargaSatuan,
            'total_nilai' => $jumlah * $hargaSatuan,
            'keterangan' => $keterangan,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function storeReceive(Request $request)
    {
        DB::beginTransaction();
        try {
            // ── 1. Ambil data PO, outlet, warehouse ──────────────────────
            $poHeader = DB::table('tbl_po')->where('id', $request->po_id)->first();
            $noPo = $poHeader->no_po ?? '-';

            $outlet = DB::table('tbl_outlets')
                ->join('tbl_api_credentials', 'tbl_outlets.credential_id', '=', 'tbl_api_credentials.id')
                ->where('tbl_outlets.id', $poHeader->outlet_id)
                ->select('tbl_outlets.*', 'tbl_api_credentials.credential_code')
                ->first();

            $origin = DB::table('tbl_warehouse')
                ->join('tbl_outlets', 'tbl_warehouse.branch_id', '=', 'tbl_outlets.id')
                ->where('tbl_warehouse.id', $poHeader->warehouse_id)
                ->select('tbl_outlets.*', 'tbl_warehouse.nama_warehouse')
                ->first();

            $destination = DB::table('tbl_outlets')
                ->where('id', $poHeader->outlet_id)
                ->first();

            // Ambil supplier_id dari tbl_po_detail (sudah diset SCM saat approve)
            // Supplier berbeda per bahan, ambil berdasarkan per-item saat staging
            $supplierId = null;
            $supplierName = 'SUPPLIER';

            $firstPoSupplier = DB::table('tbl_po_detail as pd')
                ->join('tbl_bahan_scm as b', 'pd.bahan_id', '=', 'b.id')
                ->join('tbl_suppliers as s', 'pd.supplier_id', '=', 's.id')
                ->where('pd.po_id', $request->po_id)
                ->where('b.sumber_barang', 'SUPPLIER')
                ->whereNotNull('pd.supplier_id')
                ->select('pd.supplier_id', 's.supplier_name')
                ->first();

            if ($firstPoSupplier) {
                $supplierId = $firstPoSupplier->supplier_id;
                $supplierName = $firstPoSupplier->supplier_name;
            }

            // ── 2. Simpan header tbl_po_receive ──────────────────────────
            $receiveId = DB::table('tbl_po_receive')->insertGetId([
                'po_id' => $request->po_id,
                'gd_id' => $request->gd_id ?? null,
                'no_po' => $request->no_po,
                'outlet_id' => $poHeader->outlet_id,
                'tgl_terima' => now(),
                'created_at' => now(),
            ]);

            // ── 3. Proses tiap item ───────────────────────────────────────
            $itemTerinput = 0;
            $adaGudang = false;
            $adaSupplier = false;
            $grItems = [];
            $itemsForStaging = [];

            foreach ($request->items as $item) {
                $bahanId = $item['bahan_id'];
                $unitId = $item['unit_id'];
                $qtyPo = $item['qty_po'] ?? 0;
                $konversi = $item['konversi'] ?? 1;

                $bahan = DB::table('tbl_bahan_scm')->where('id', $bahanId)->first();
                $sumber = strtoupper($bahan->sumber_barang ?? 'GUDANG');
                $isAyam = !is_null($bahan->split_bahan_besar_id ?? null)
                    && !is_null($bahan->split_bahan_kecil_id ?? null);

                $unitData = DB::table('tbl_bahan_unit')
                    ->where('bahan_id', $bahanId)
                    ->where('unit_id', $unitId)
                    ->first();
                $hargaFinal = $unitData->base_price ?? 0;

                if ($isAyam) {
                    // ── AYAM: split jadi 2 baris receive_detail ──────────
                    $qtyBesar = intval($item['qty_besar'] ?? 0);
                    $qtyKecil = intval($item['qty_kecil'] ?? 0);
                    $qtyTerima = $qtyBesar + $qtyKecil;
                    $qtyPack = intval($item['qty_pack'] ?? 0);

                    if ($qtyTerima <= 0)
                        continue; // Skip jika diisi 0

                    $unitBesar = DB::table('tbl_bahan_unit')->where('bahan_id', $bahan->split_bahan_besar_id)->where('is_base_unit', 1)->value('unit_id') ?? $unitId;
                    $unitKecil = DB::table('tbl_bahan_unit')->where('bahan_id', $bahan->split_bahan_kecil_id)->where('is_base_unit', 1)->value('unit_id') ?? $unitId;

                    $hargaBesar = DB::table('tbl_bahan_unit')->where('bahan_id', $bahan->split_bahan_besar_id)->where('unit_id', $unitBesar)->value('base_price') ?? 0;
                    $hargaKecil = DB::table('tbl_bahan_unit')->where('bahan_id', $bahan->split_bahan_kecil_id)->where('unit_id', $unitKecil)->value('base_price') ?? 0;

                    // Simpan baris AYAM BESAR
                    if ($qtyBesar > 0) {
                        DB::table('tbl_po_receive_detail')->insert([
                            'receive_id' => $receiveId,
                            'po_id' => $request->po_id,
                            'bahan_id' => $bahan->split_bahan_besar_id,
                            'unit_id' => $unitBesar,
                            'qty_po' => $qtyPo,
                            'qty_terima' => $qtyBesar,
                            'qty_besar' => $qtyBesar,
                            'qty_kecil' => 0,
                            'qty_kurang' => 0,
                            'qty_pack' => $qtyPack,
                            'harga_satuan_aktual' => $hargaBesar,
                            'total_gramase' => $qtyBesar * $konversi,
                            'created_at' => now(),
                        ]);

                        $itemsForStaging[] = array_merge($item, [
                            'bahan_id' => $bahan->split_bahan_besar_id,
                            'unit_id' => $unitBesar,
                            'qty_terima' => $qtyBesar,
                            'harga' => $hargaBesar,
                            'sumber' => $sumber,
                        ]);

                        if ($sumber === 'SUPPLIER') {
                            $grItems[] = [
                                'bahan_id' => $bahan->split_bahan_besar_id,
                                'unit_id' => $unitBesar,
                                'qty' => $qtyBesar,
                                'price' => $hargaBesar,
                            ];
                        }
                    }

                    // Simpan baris AYAM KECIL
                    if ($qtyKecil > 0) {
                        DB::table('tbl_po_receive_detail')->insert([
                            'receive_id' => $receiveId,
                            'po_id' => $request->po_id,
                            'bahan_id' => $bahan->split_bahan_kecil_id,
                            'unit_id' => $unitKecil,
                            'qty_po' => $qtyPo,
                            'qty_terima' => $qtyKecil,
                            'qty_besar' => 0,
                            'qty_kecil' => $qtyKecil,
                            'qty_kurang' => 0,
                            'qty_pack' => $qtyPack,
                            'harga_satuan_aktual' => $hargaKecil,
                            'total_gramase' => $qtyKecil * $konversi,
                            'created_at' => now(),
                        ]);

                        $itemsForStaging[] = array_merge($item, [
                            'bahan_id' => $bahan->split_bahan_kecil_id,
                            'unit_id' => $unitKecil,
                            'qty_terima' => $qtyKecil,
                            'harga' => $hargaKecil,
                            'sumber' => $sumber,
                        ]);

                        if ($sumber === 'SUPPLIER') {
                            $grItems[] = [
                                'bahan_id' => $bahan->split_bahan_kecil_id,
                                'unit_id' => $unitKecil,
                                'qty' => $qtyKecil,
                                'price' => $hargaKecil,
                            ];
                        }
                    }
                    $itemTerinput++;
                } else {
                    // ── BAHAN UMUM ────────────────────────────────────────
                    $qtyTerima = floatval($item['qty_terima'] ?? 0);
                    $qtyKurang = max(0, $qtyPo - $qtyTerima);

                    if ($qtyTerima <= 0)
                        continue; // Skip jika diisi 0

                    DB::table('tbl_po_receive_detail')->insert([
                        'receive_id' => $receiveId,
                        'po_id' => $request->po_id,
                        'bahan_id' => $bahanId,
                        'unit_id' => $unitId,
                        'qty_po' => $qtyPo,
                        'qty_terima' => $qtyTerima,
                        'qty_besar' => 0,
                        'qty_kecil' => 0,
                        'qty_kurang' => $qtyKurang,
                        'qty_pack' => null,
                        'harga_satuan_aktual' => $hargaFinal,
                        'total_gramase' => $qtyTerima * $konversi,
                        'created_at' => now(),
                    ]);

                    $itemsForStaging[] = array_merge($item, [
                        'qty_terima' => $qtyTerima,
                        'harga' => $hargaFinal,
                        'sumber' => $sumber,
                    ]);

                    if ($sumber === 'SUPPLIER') {
                        $grItems[] = [
                            'bahan_id' => $bahanId,
                            'unit_id' => $unitId,
                            'qty' => $qtyTerima,
                            'price' => $hargaFinal,
                        ];
                    }
                }

                if ($sumber === 'SUPPLIER')
                    $adaSupplier = true;
                else
                    $adaGudang = true;
            }

            // ── 4. Staging: simple transfer / simple sales+purchase ──────
            if ($itemTerinput > 0) {
                if ($outlet->credential_code === 'OKNHO') {
                    $prefix = 'STF' . date('Ymd');
                    $lastTransfer = DB::table('tbl_simple_transfer')->where('transfer_num', 'LIKE', $prefix . '%')->orderByDesc('transfer_num')->lockForUpdate()->first();
                    $nextNum = $lastTransfer ? str_pad((int) substr($lastTransfer->transfer_num, -4) + 1, 4, '0', STR_PAD_LEFT) : '0001';
                    $transferNum = $prefix . $nextNum;
                    $this->insertSimpleTransfer($transferNum, $origin, $destination, $itemsForStaging, $noPo, $supplierName);
                } else {
                    $prefixSales = 'SS' . date('Ymd');
                    $lastSales = DB::table('tbl_simple_sales')->where('sales_num', 'LIKE', $prefixSales . '%')->orderByDesc('sales_num')->lockForUpdate()->first();
                    $salesNum = $prefixSales . ($lastSales ? str_pad((int) substr($lastSales->sales_num, -4) + 1, 4, '0', STR_PAD_LEFT) : '0001');
                    $this->insertSimpleSales($salesNum, $outlet, $itemsForStaging, $noPo, $supplierName);

                    $prefixPurchase = 'CP' . date('Ymd');
                    $lastPurchase = DB::table('tbl_simple_purchases')->where('purchase_num', 'LIKE', $prefixPurchase . '%')->orderByDesc('purchase_num')->lockForUpdate()->first();
                    $purchaseNum = $prefixPurchase . ($lastPurchase ? str_pad((int) substr($lastPurchase->purchase_num, -4) + 1, 4, '0', STR_PAD_LEFT) : '0001');
                    $this->insertSimplePurchase($purchaseNum, $outlet, $itemsForStaging, $noPo, $supplierId, $supplierName, $salesNum);
                }
            }

            // ── 5. INTEGRASI GUDANG: auto-complete GD di SCM ─────────────
            if ($adaGudang) {
                $gdId = $request->gd_id ?? null;
                if (!$gdId) {
                    $gdRow = DB::table('tbl_goods_deliveries')->where('outlet_po_id', $request->po_id)->where('status', 'IN_TRANSIT')->first();
                    $gdId = $gdRow->id ?? null;
                }

                if ($gdId) {
                    $gd = DB::table('tbl_goods_deliveries')->where('id', $gdId)->first();
                    if ($gd && $gd->status === 'IN_TRANSIT') {
                        $receiveMap = [];
                        foreach ($itemsForStaging as $staged) {
                            $receiveMap[$staged['bahan_id']] = $staged['qty_terima'];
                        }

                        $gdDetails = DB::table('tbl_goods_delivery_details')->where('goods_delivery_id', $gdId)->get();
                        foreach ($gdDetails as $gdd) {
                            $actualQty = $receiveMap[$gdd->product_id] ?? $gdd->qty_ordered;
                            DB::table('tbl_goods_delivery_details')->where('id', $gdd->id)->update([
                                'qty_delivered' => $actualQty,
                                'subtotal' => $actualQty * $gdd->price,
                                'updated_at' => now(),
                            ]);

                            $this->insertStockTransaction(warehouseId: $gd->warehouse_id, bahanId: $gdd->product_id, unitId: $gdd->unit_id, tipe: 'KELUAR', jumlah: $actualQty, referenceType: 'GD', referenceId: $gdId, hargaSatuan: $gdd->price, keterangan: 'GD Delivered - outlet terima ' . $noPo);
                        }

                        $newTotal = DB::table('tbl_goods_delivery_details')->where('goods_delivery_id', $gdId)->sum('subtotal');
                        DB::table('tbl_goods_deliveries')->where('id', $gdId)->update([
                            'status' => 'DELIVERED',
                            'actual_arrival' => now(),
                            'total_amount' => $newTotal,
                            'updated_at' => now(),
                        ]);

                        DB::table('tbl_po_receive')->where('id', $receiveId)->update(['gd_id' => $gdId]);

                        if ($gd->sales_order_id) {
                            DB::table('tbl_sales_orders')->where('id', $gd->sales_order_id)->update(['status' => 'DELIVERED', 'updated_at' => now()]);
                        }
                    }
                }
            }

            // ── 6. INTEGRASI SUPPLIER: auto-create GR di SCM ─────────────
            if ($adaSupplier && count($grItems) > 0) {
                $today = now()->format('Ymd');
                $lastGr = DB::table('tbl_goods_receipts')->where('gr_number', 'like', "GR{$today}%")->lockForUpdate()->orderByDesc('gr_number')->value('gr_number');
                $seq = $lastGr ? (intval(substr($lastGr, -4)) + 1) : 1;
                $grNumber = 'GR' . $today . str_pad($seq, 4, '0', STR_PAD_LEFT);

                $scmPo = DB::table('tbl_purchase_orders')->where('reference_number', $noPo)->first();

                $grId = DB::table('tbl_goods_receipts')->insertGetId([
                    'gr_number' => $grNumber,
                    'purchase_order_id' => $scmPo->id ?? null,
                    'supplier_id' => $supplierId,
                    'warehouse_id' => $poHeader->warehouse_id,
                    'receipt_date' => now()->toDateString(),
                    'status' => 'RECEIVED',
                    'notes' => 'Auto GR - Supplier langsung ke outlet ' . $noPo,
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($grItems as $gi) {
                    // PROTEKSI GUDANG VS SUPPLIER
                    $cekBahanGr = DB::table('tbl_bahan_scm')->where('id', $gi['bahan_id'])->first();
                    if (strtoupper($cekBahanGr->sumber_barang ?? 'GUDANG') !== 'SUPPLIER') {
                        continue; // Skip jika bukan item milik supplier
                    }

                    $poDetailId = $scmPo ? DB::table('tbl_purchase_order_details')->where('purchase_order_id', $scmPo->id)->where('product_id', $gi['bahan_id'])->value('id') : null;

                    DB::table('tbl_goods_receipt_details')->insert([
                        'goods_receipt_id' => $grId,
                        'purchase_order_detail_id' => $poDetailId,
                        'product_id' => $gi['bahan_id'],
                        'unit_id' => $gi['unit_id'],
                        'qty_ordered' => $gi['qty'],
                        'qty_received' => $gi['qty'],
                        'price' => $gi['price'],
                        'subtotal' => $gi['qty'] * $gi['price'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $this->insertStockTransaction(warehouseId: $poHeader->warehouse_id, bahanId: $gi['bahan_id'], unitId: $gi['unit_id'], tipe: 'MASUK', jumlah: $gi['qty'], referenceType: 'GR', referenceId: $grId, hargaSatuan: $gi['price'], keterangan: 'Auto GR IN - Supplier langsung ' . $noPo);
                    $this->insertStockTransaction(warehouseId: $poHeader->warehouse_id, bahanId: $gi['bahan_id'], unitId: $gi['unit_id'], tipe: 'KELUAR', jumlah: $gi['qty'], referenceType: 'GR', referenceId: $grId, hargaSatuan: $gi['price'], keterangan: 'Auto GR OUT - Supplier langsung ke outlet ' . $noPo);
                }
            }

            // ── 7. Cek status PO Dinamis (Mendukung Split 0 untuk demo) ──
            $poDetails = DB::table('tbl_po_detail')->where('po_id', $request->po_id)->get();
            $totalQtyPO = 0;
            $totalQtyAktual = 0;

            foreach ($poDetails as $pd) {
                $totalQtyPO += $pd->jumlah;
                $b = DB::table('tbl_bahan_scm')->where('id', $pd->bahan_id)->first();
                $isAyam = !is_null($b->split_bahan_besar_id ?? null);

                if ($isAyam) {
                    $terimaItem = DB::table('tbl_po_receive_detail')->where('po_id', $request->po_id)->whereIn('bahan_id', [$b->split_bahan_besar_id, $b->split_bahan_kecil_id])->sum('qty_terima');
                } else {
                    $terimaItem = DB::table('tbl_po_receive_detail')->where('po_id', $request->po_id)->where('bahan_id', $pd->bahan_id)->sum('qty_terima');
                }
                $totalQtyAktual += $terimaItem;
            }

            if ($totalQtyAktual >= $totalQtyPO) {
                $statusPO = 'Received';
            } elseif ($totalQtyAktual > 0) {
                $statusPO = 'Sebagian Dikirim';
            } else {
                $statusPO = 'Belum Dikirim';
            }
            DB::table('tbl_po')->where('id', $request->po_id)->update(['status' => $statusPO]);

            // ── 8. Simpan foto ────────────────────────────────────────────
            if ($request->foto_barang_base64) {
                DB::table('tbl_po_receive_images')->insert(['receive_id' => $receiveId, 'jenis_foto' => 'barang', 'foto_base64' => $request->foto_barang_base64, 'created_at' => now()]);
            }
            if ($request->foto_supir_base64) {
                DB::table('tbl_po_receive_images')->insert(['receive_id' => $receiveId, 'jenis_foto' => 'supir', 'foto_base64' => $request->foto_supir_base64, 'created_at' => now()]);
            }

            DB::commit();

            $msgIntegrasi = '';
            if ($adaGudang)
                $msgIntegrasi .= ' GD SCM otomatis DELIVERED.';
            if ($adaSupplier)
                $msgIntegrasi .= ' GR supplier otomatis dibuat.';

            return response()->json([
                'status' => 'success',
                'message' => 'Penerimaan berhasil! Status PO: ' . $statusPO . '.' . $msgIntegrasi,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('storeReceive error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan: ' . $e->getMessage() . ' di baris ' . $e->getLine(),
            ], 500);
        }
    }

    // --- PRIVATE METHODS UNTUK STAGING ---
    private function insertSimpleTransfer($transferNum, $origin, $destination, $items, $noPo, $supplierName = null)
    {
        $info = $supplierName
            ? "COBA Supplier: " . $supplierName . " (PO: " . $noPo . ")"
            : "Checking PO: " . $noPo;

        // 1. Insert Header (Hanya 1 kali)
        DB::table('tbl_simple_transfer')->insert([
            'transfer_num' => $transferNum,
            'transfer_date' => now()->format('Y-m-d'),
            'origin_location_id' => $origin->esb_location_id, // Ambil dari parameter
            'origin_location_name' => $origin->nama_outlet,
            'destination_location_id' => $destination->esb_location_id, // Ambil dari parameter
            'destination_location_name' => $destination->nama_outlet,
            'status_name' => 'Authorized',
            'cost_center_id' => null,
            'additional_info' => $info,
            'created_by' => auth()->user()->name,
            'created_date' => now(),
        ]);

        // 2. Loop Detail Barang
        foreach ($items as $item) {
            $bahan = DB::table('tbl_bahan_scm')->where('id', $item['bahan_id'])->first();
            if (!$bahan)
                continue;

            if (stripos($bahan->nama_bahan, 'Ayam') !== false) {
                $isBesar = stripos($bahan->nama_bahan, 'Besar') !== false;
                $namaPasangan = $isBesar ? str_replace('Besar', 'Kecil', $bahan->nama_bahan) : str_replace('Kecil', 'Besar', $bahan->nama_bahan);
                $pasangan = DB::table('tbl_bahan_scm')->where('nama_bahan', $namaPasangan)->first();

                $configs = [
                    ['qty' => $item['qty_besar'] ?? 0, 'id' => $isBesar ? $bahan->id : ($pasangan->id ?? null)],
                    ['qty' => $item['qty_kecil'] ?? 0, 'id' => !$isBesar ? $bahan->id : ($pasangan->id ?? null)]
                ];

                foreach ($configs as $c) {
                    if ($c['qty'] > 0 && $c['id']) {
                        $pId = $this->getEsbProductByType($c['id'], 'is_transfer_unit');
                        // Kirim pId ke parameter terakhir
                        $this->insertDetailTransfer($transferNum, $c['id'], $item['unit_id'], $c['qty'], $pId);
                    }
                }
            } else {
                $qty = $item['qty_terima'] ?? 0;
                if ($qty > 0) {
                    $pId = $this->getEsbProductByType($item['bahan_id'], 'is_transfer_unit');
                    $this->insertDetailTransfer($transferNum, $item['bahan_id'], $item['unit_id'], $qty, $pId);
                }
            }
        }
    }

    private function insertDetailTransfer($num, $bahanId, $unitId, $qty, $productDetailId)
    {
        $mapping = DB::table('tbl_bahan_unit')
            ->join('tbl_units', 'tbl_bahan_unit.unit_id', '=', 'tbl_units.id')
            ->join('tbl_bahan_scm', 'tbl_bahan_unit.bahan_id', '=', 'tbl_bahan_scm.id')
            ->where('tbl_bahan_unit.product_detail_id', $productDetailId)
            ->select('tbl_units.nama_unit', 'tbl_bahan_scm.nama_bahan')
            ->first();

        if (!$mapping) {
            throw new \Exception("Mapping Gagal! Bahan ID: $bahanId tidak ditemukan untuk Product Detail ID: $productDetailId");
        }

        // Gunakan updateOrInsert agar aman dari Duplicate Entry 'transfer_num-product_detail_id'
        DB::table('tbl_simple_transfer_detail')->updateOrInsert(
            [
                'transfer_num' => $num,
                'product_detail_id' => $productDetailId,
            ],
            [
                'product_name' => $mapping->nama_bahan,
                'uom_name' => $mapping->nama_unit,
                // Menggunakan DB::raw untuk menambahkan qty jika data sudah ada
                'qty' => DB::raw("qty + $qty"),
                'stock_qty' => DB::raw("stock_qty + $qty"),
                'available_qty' => DB::raw("available_qty + $qty"),
                'is_asset' => 0,
                'created_at' => now(),
            ]
        );
    }

    private function insertSimpleSales($salesNum, $outlet, $items, $noPo, $supplierName)
    {
        // 1. INSERT HEADER DULU (Gunakan grandTotal 0 atau null sementara)
        DB::table('tbl_simple_sales')->insert([
            'sales_num' => $salesNum,
            'sales_date' => now(),
            'branch_id' => 459,
            'branch_name' => 'DC SUPPLIER',
            'location_id' => 1281,
            'customer_id' => $outlet->esb_customer_id,
            'customer_name' => $outlet->nama_outlet,
            'total_amount' => 0, // Akan di-update setelah loop selesai
            'status_name' => 'Authorized',
            'notes' => 'Checking PO: ' . $noPo . ' - Supplier: ' . $supplierName,
            'created_at' => now(),
        ]);

        $grandTotal = 0;

        // 2. BARU LOOP DETAIL
        foreach ($items as $item) {
            $bahan = DB::table('tbl_bahan_scm')->where('id', $item['bahan_id'])->first();
            if (!$bahan)
                continue;

            if (stripos($bahan->nama_bahan, 'Ayam') !== false) {
                $isBesar = stripos($bahan->nama_bahan, 'Besar') !== false;
                $namaPasangan = $isBesar ? str_replace('Besar', 'Kecil', $bahan->nama_bahan) : str_replace('Kecil', 'Besar', $bahan->nama_bahan);
                $pasangan = DB::table('tbl_bahan_scm')->where('nama_bahan', $namaPasangan)->first();

                $configs = [
                    ['qty' => $item['qty_besar'] ?? 0, 'id' => $isBesar ? $bahan->id : ($pasangan->id ?? null)],
                    ['qty' => $item['qty_kecil'] ?? 0, 'id' => !$isBesar ? $bahan->id : ($pasangan->id ?? null)]
                ];

                foreach ($configs as $c) {
                    if ($c['qty'] > 0 && $c['id']) {
                        $pId = $this->getEsbProductByType($c['id'], 'is_sales_unit');
                        $grandTotal += $this->insertDetailSales($salesNum, $c['id'], $c['qty'], $pId);
                    }
                }
            } else {
                $qty = $item['qty_terima'] ?? 0;
                if ($qty > 0) {
                    $pId = $this->getEsbProductByType($item['bahan_id'], 'is_sales_unit');
                    $grandTotal += $this->insertDetailSales($salesNum, $item['bahan_id'], $qty, $pId);
                }
            }
        }

        // 3. UPDATE TOTAL_AMOUNT DI HEADER
        if ($grandTotal > 0) {
            DB::table('tbl_simple_sales')
                ->where('sales_num', $salesNum)
                ->update(['total_amount' => $grandTotal]);
        } else {
            // Jika ternyata tidak ada item, opsional: hapus headernya lagi
            DB::table('tbl_simple_sales')->where('sales_num', $salesNum)->delete();
        }
    }

    // --- HELPER KULI UNTUK INSERT DETAIL SALES ---
    private function insertDetailSales($num, $bahanId, $qty, $productDetailId)
    {
        // Ambil detail mapping (harga, nama, unit) berdasarkan productDetailId yang terpilih
        $mapping = DB::table('tbl_bahan_unit')
            ->join('tbl_units', 'tbl_bahan_unit.unit_id', '=', 'tbl_units.id')
            ->join('tbl_bahan_scm', 'tbl_bahan_unit.bahan_id', '=', 'tbl_bahan_scm.id')
            ->where('tbl_bahan_unit.product_detail_id', $productDetailId)
            ->select(
                'tbl_bahan_unit.base_price',
                'tbl_units.nama_unit',
                'tbl_bahan_scm.nama_bahan'
            )
            ->first();

        if ($mapping) {
            $price = $mapping->base_price ?? 0;
            $totalLine = $qty * $price;

            // Gunakan updateOrInsert agar aman jika ada Ayam Besar & Kecil dengan ID yang sama
            DB::table('tbl_simple_sales_detail')->updateOrInsert(
                [
                    'sales_num' => $num,
                    'product_detail_id' => $productDetailId,
                ],
                [
                    'product_id' => $productDetailId,
                    'product_name' => $mapping->nama_bahan,
                    'uom_name' => $mapping->nama_unit,
                    'price' => $price,
                    // Gunakan DB::raw untuk qty dan total agar bisa menjumlahkan jika terjadi update
                    'qty' => DB::raw("qty + $qty"),
                    'total_line' => DB::raw("total_line + $totalLine"),
                    'hpp' => 0,
                    'updated_at' => now(),
                    'created_at' => now(), // Laravel otomatis mengabaikan created_at jika update
                ]
            );

            return $totalLine;
        }
        return 0;
    }

    // Tambahkan $salesNum di akhir parameter
    private function insertSimplePurchase($purchaseNum, $outlet, $items, $noPo, $supplierId, $supplierName, $salesNum)
    {
        // 1. INSERT HEADER DULU agar purchase_num terdaftar secara resmi
        DB::table('tbl_simple_purchases')->insert([
            'credential_id' => $outlet->credential_id,
            'purchase_num' => $purchaseNum,
            'purchase_date' => now(),
            'purchase_type_name' => 'Goods',
            'branch_id' => $outlet->esb_branch_id,
            'branch_name' => $outlet->nama_outlet,
            'location_id' => $outlet->esb_location_id,
            'location_name' => $outlet->nama_outlet,
            'supplier_id' => $supplierId,
            'supplier_name' => $supplierName,
            'currency_sign' => 'IDR',
            'total_amount' => 0, // Update nanti setelah loop
            'status_name' => 'Authorized',
            'additional_info' => 'Ref Sales: ' . $salesNum,
            'created_by' => auth()->user()->name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $purchaseGrandTotal = 0;

        // 2. LOOP DETAIL (Sekarang aman karena induk sudah ada)
        foreach ($items as $item) {
            $bahan = DB::table('tbl_bahan_scm')->where('id', $item['bahan_id'])->first();
            if (!$bahan)
                continue;

            if (stripos($bahan->nama_bahan, 'Ayam') !== false) {
                $isBesar = stripos($bahan->nama_bahan, 'Besar') !== false;
                $namaPasangan = $isBesar ? str_replace('Besar', 'Kecil', $bahan->nama_bahan) : str_replace('Kecil', 'Besar', $bahan->nama_bahan);
                $pasangan = DB::table('tbl_bahan_scm')->where('nama_bahan', $namaPasangan)->first();

                $configs = [
                    ['qty' => $item['qty_besar'] ?? 0, 'id' => $isBesar ? $bahan->id : ($pasangan->id ?? null)],
                    ['qty' => $item['qty_kecil'] ?? 0, 'id' => !$isBesar ? $bahan->id : ($pasangan->id ?? null)]
                ];

                foreach ($configs as $c) {
                    if ($c['qty'] > 0 && $c['id']) {
                        $pId = $this->getEsbProductByType($c['id'], 'is_purchase_unit');
                        $purchaseGrandTotal += $this->insertDetailPurchase($purchaseNum, $outlet->credential_id, $c['id'], $c['qty'], $noPo, $pId);
                    }
                }
            } else {
                $qty = $item['qty_terima'] ?? 0;
                if ($qty > 0) {
                    $pId = $this->getEsbProductByType($item['bahan_id'], 'is_purchase_unit');
                    $purchaseGrandTotal += $this->insertDetailPurchase($purchaseNum, $outlet->credential_id, $item['bahan_id'], $qty, $noPo, $pId);
                }
            }
        }

        // 3. UPDATE TOTAL_AMOUNT DI HEADER
        if ($purchaseGrandTotal > 0) {
            DB::table('tbl_simple_purchases')
                ->where('purchase_num', $purchaseNum)
                ->update(['total_amount' => $purchaseGrandTotal]);
        } else {
            // Opsional: Hapus header jika ternyata tidak ada item yang masuk
            DB::table('tbl_simple_purchases')->where('purchase_num', $purchaseNum)->delete();
        }
    }

    // --- HELPER KULI UNTUK INSERT DETAIL PURCHASE ---
    private function insertDetailPurchase($num, $credentialId, $bahanId, $qty, $noPo, $productDetailId)
    {
        // Ambil detail mapping berdasarkan productDetailId yang sudah spesifik untuk Purchase
        $mapping = DB::table('tbl_bahan_unit')
            ->join('tbl_units', 'tbl_bahan_unit.unit_id', '=', 'tbl_units.id')
            ->join('tbl_bahan_scm', 'tbl_bahan_unit.bahan_id', '=', 'tbl_bahan_scm.id')
            ->where('tbl_bahan_unit.product_detail_id', $productDetailId)
            ->select(
                'tbl_units.nama_unit',
                'tbl_bahan_scm.nama_bahan'
            )
            ->first();

        if ($mapping) {
            $priceBeli = 1000; // Harga dummy sesuai kodingan awalmu
            $subtotal = $qty * $priceBeli;

            // Gunakan updateOrInsert agar aman jika Ayam Besar & Kecil punya ID ESB yang sama
            DB::table('tbl_simple_purchase_details')->updateOrInsert(
                [
                    'purchase_num' => $num,
                    'product_detail_id' => $productDetailId,
                    'credential_id' => $credentialId,
                ],
                [

                    'product_name' => $mapping->nama_bahan,
                    'uom_name' => $mapping->nama_unit,
                    'price' => $priceBeli,
                    // Gunakan DB::raw untuk akumulasi qty dan subtotal
                    'qty' => DB::raw("qty + $qty"),
                    'subtotal' => DB::raw("subtotal + $subtotal"),
                    'notes' => 'Checking PO: ' . $noPo,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            return $subtotal;
        }
        return 0;
    }

    private function getEsbProductByType($bahanId, $typeColumn)
    {
        // Cari product_detail_id berdasarkan bahan_id dan tipe kolom (transfer/sales/purchase)
        // Kita sengaja tidak pakai unit_id karena data di PO dan Master sering berbeda
        $productDetailId = DB::table('tbl_bahan_unit')
            ->where('bahan_id', $bahanId)
            ->where($typeColumn, 1) // Mencari unit yang memang di-set untuk tipe ini
            ->value('product_detail_id');

        // Jika tipe spesifik tidak ditemukan, gunakan is_base_unit sebagai pengaman (fallback)
        if (is_null($productDetailId)) {
            $productDetailId = DB::table('tbl_bahan_unit')
                ->where('bahan_id', $bahanId)
                ->where('is_base_unit', 1)
                ->value('product_detail_id');
        }

        // Jika tetap null, baru lempar exception agar user tahu master data mana yang kurang
        if (is_null($productDetailId)) {
            $namaBahan = DB::table('tbl_bahan_scm')->where('id', $bahanId)->value('nama_bahan');
            throw new \Exception("Data Gagal Disimpan: Bahan [$namaBahan] tidak memiliki Product Detail ID yang valid untuk kategori [$typeColumn].");
        }

        return $productDetailId;
    }

    // private function createStagingTransfer($receiveId, $item, $qty, $outlet)
    // {
    //     $originEsbId = DB::table('tbl_master_location')
    //         ->where('slug', 'gudang-pusat') // Ganti 'gudang-pusat' sesuai slug DC kamu
    //         ->value('esb_id');

    //     // 2. Cari ESB ID untuk Outlet Penerima (Destination) berdasarkan slug outlet
    //     // Kita asumsikan di tbl_outlets kamu punya kolom 'slug' yang sama dengan tbl_master_location
    //     $destEsbId = DB::table('tbl_master_location')
    //         ->where('slug', $outlet->slug)
    //         ->value('esb_id');

    //     // Cari id staging header atau buat baru jika belum ada
    //     $stagingId = DB::table('staging_simple_transfers')->updateOrInsert(
    //         ['receive_id' => $receiveId],
    //         [
    //             'transfer_num' => 'TRF-' . time(),
    //             'origin_location_id' => $originEsbId, // Contoh ID DC Supplier di ESB
    //             'destination_location_id' => $destEsbId,
    //             'status_api' => 'pending',
    //             'created_at' => now()
    //         ]
    //     );

    //     $header = DB::table('staging_simple_transfers')->where('receive_id', $receiveId)->first();

    //     // Simpan rinciannya
    //     DB::table('staging_simple_transfer_details')->insert([
    //         'staging_transfer_id' => $header->id,
    //         'bahan_id' => $item['bahan_id'],
    //         'esb_product_id' => $this->getEsbProductId($item['bahan_id']),
    //         'unit_id' => $item['unit_id'],
    //         'qty' => $qty
    //     ]);
    // }

    // private function createStagingSalesPurchase($receiveId, $item, $qty, $price, $outlet)
    // {
    //     // 1. Ambil ID Customer SCM
    //     $customerId = $outlet->esb_customer_id;

    //     if (!$customerId) {
    //         \Log::warning("Outlet {$outlet->nama_outlet} belum memiliki esb_customer_id!");
    //         return;
    //     }

    //     // 2. Ambil product_detail_id dari Base Unit (Otomatis)
    //     $esbIdToPost = $this->getEsbProductId($item['bahan_id']);

    //     // --- A. INSERT STAGING SALES (Sisi DC) ---
    //     DB::table('staging_simple_sales')->updateOrInsert(
    //         ['receive_id' => $receiveId],
    //         [
    //             'sales_num'    => 'SS-' . $receiveId . '-' . time(),
    //             'branch_id'    => 459,
    //             'customer_id'  => $customerId,
    //             'total_amount' => DB::raw("total_amount + " . ($qty * $price)),
    //             'status_api'   => 'pending',
    //             'created_at'   => now()
    //         ]
    //     );

    //     $salesHeader = DB::table('staging_simple_sales')->where('receive_id', $receiveId)->first();

    //     DB::table('staging_simple_sales_details')->insert([
    //         'staging_sales_id' => $salesHeader->id,
    //         'bahan_id'         => $item['bahan_id'],
    //         'esb_product_id'   => $esbIdToPost, // Menggunakan ID dari Base Unit
    //         'unit_id'          => $item['unit_id'],
    //         'qty'              => $qty,
    //         'price'            => $price,
    //         'subtotal'         => $qty * $price,
    //         'created_at'       => now()
    //     ]);

    //     // 3. STAGING SIMPLE PURCHASE (Sisi Mitra/Pembelian)
    //     DB::table('staging_simple_purchases')->updateOrInsert(
    //         ['receive_id' => $receiveId],
    //         [
    //             'purchase_num' => 'SP-' . $receiveId . '-' . time(),
    //             'branch_id'    => $outlet->esb_branch_id, // ID Cabang Mitra
    //             'supplier_id'  => 459, // DC bertindak sebagai Supplier
    //             'total_amount' => DB::raw("total_amount + " . ($qty * $price)),
    //             'status_api'   => 'pending',
    //             'created_at'   => now()
    //         ]
    //     );

    //     // 2. Simpan Header Purchase (Sisi Mitra)
    //     // DB::table('staging_simple_purchases_details')->updateOrInsert(
    //     //     ['receive_id' => $receiveId],
    //     //     [
    //     //         'branch_id' => $outlet->esb_branch_id,
    //     //         'supplier_id' => 459,
    //     //         'total_amount' => DB::raw("total_amount + " . ($qty * $price)),
    //     //         'status_api' => 'pending'
    //     //     ]
    //     // );
    // }

    // Helper untuk ambil ID ESB
    private function getEsbProductId($bahanId)
    {
        $productDetailId = DB::table('tbl_bahan_unit')
            ->where('bahan_id', $bahanId)
            ->where('is_base_unit', 1)
            ->value('product_detail_id');

        // Validasi agar tidak terjadi error "cannot be null" lagi
        if (is_null($productDetailId)) {
            // Ambil nama bahan untuk pesan error yang jelas
            $namaBahan = DB::table('tbl_bahan_scm')->where('id', $bahanId)->value('nama_bahan');
            throw new \Exception("Data Gagal Disimpan: Bahan [$namaBahan] tidak memiliki Base Unit atau product_detail_id belum diisi di tabel master unit.");
        }

        return $productDetailId;
    }

    public function getReceiveDetail($id)
    {
        $allDetails = DB::table('tbl_po_receive_detail as rd')
            ->leftJoin('tbl_bahan_scm as b', 'rd.bahan_id', '=', 'b.id')
            ->where('rd.po_id', $id) // Pastikan ID 15 ini benar-benar ada di tabel tbl_po_receive_detail
            ->select('rd.*', 'b.nama_bahan')
            ->get();

        dd($allDetails);

        // FILTER: Hanya ambil yang qty_datang < qty_po
        $filtered = $allDetails->filter(function ($item) {
            return floatval($item->qty_datang) < floatval($item->qty_po);
        })->values(); // values() untuk mereset index array agar jadi 0, 1, 2...

        return response()->json([
            'success' => true,
            'details' => $filtered
        ]);
    }

    public function storeReturn(Request $request)
    {
        DB::beginTransaction();
        try {
            $itemsRetur = []; // Untuk menampung nama bahan buat catatan history

            foreach ($request->returns as $ret) {
                $qty = $ret['qty_return'] ?? 0;

                if ($qty > 0) {
                    // 1. Ambil nama bahan untuk catatan (opsional, agar history lebih informatif)
                    $bahan = DB::table('tbl_bahan_scm')->where('id', $ret['bahan_id'])->first();
                    $itemsRetur[] = ($bahan->nama_bahan ?? 'Bahan') . " ({$qty})";

                    // 2. UPDATE QTY RETUR DI DETAIL PO RECEIVE
                    DB::table('tbl_po_receive_detail')
                        ->where('po_id', $request->po_id)
                        ->where('bahan_id', $ret['bahan_id'])
                        ->increment('qty_retur', $qty);

                    // 3. SIMPAN DETAIL KE TABEL BARU (tbl_retur)
                    DB::table('tbl_retur')->insert([
                        'po_id' => $request->po_id,
                        'bahan_id' => $ret['bahan_id'],
                        'qty_retur' => $qty,
                        'alasan' => $ret['alasan'] ?? '-',
                        // 'id_user'    => auth()->id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            if (!empty($itemsRetur)) {
                // 4. UPDATE STATUS PO UTAMA
                DB::table('tbl_po')
                    ->where('id', $request->po_id)
                    ->update(['status' => 'Retur Requested']);

                // 5. ISI TABEL HISTORY PO (Sesuai kolom yang kamu punya)
                DB::table('tbl_po_history')->insert([
                    'po_id' => $request->po_id,
                    'status_po' => 'Retur Requested',
                    'catatan' => 'Barang diretur: ' . implode(', ', $itemsRetur),
                    'created_at' => now(),
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Retur berhasil diajukan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    //============== SUPPLY CHAIN MANAGEMENT ===============
    public function scmDashboard()
    {
        // 1. Cek role dan set hak akses (DC vs Superadmin)
        $userRole = Auth::user()->role;
        $isDcUser = $userRole !== 'superadmin';

        $allowedOutlets = [];
        $namaDc = null;

        // Jika yang login adalah DC, ambil nama DC dan daftar outlet_id yang diizinkan
        if ($isDcUser) {
            $userWarehouseId = Auth::user()->warehouse_id;

            // Ambil nama DC untuk ditampilkan di dashboard
            // (Sesuaikan 'tbl_warehouse' dan 'nama_warehouse' dengan database kamu)
            $warehouse = DB::table('tbl_warehouse')->where('id', $userWarehouseId)->first();
            if ($warehouse) {
                $namaDc = $warehouse->nama_warehouse;
            }

            // Ambil mapping outlet_id milik DC ini
            $allowedOutlets = DB::table('tbl_mapping_dc')
                ->where('warehouse_id', $userWarehouseId)
                ->pluck('outlet_id')
                ->toArray();
        }

        // 2. Buat Base Query PO untuk menghindari penulisan filter berulang
        $basePoQuery = DB::table('tbl_po')
            ->when($isDcUser, function ($query) use ($allowedOutlets) {
                return $query->whereIn('outlet_id', $allowedOutlets);
            });

        // 3. Gunakan 'clone' agar base query bisa dipakai ulang untuk menghitung status
        $total_po = (clone $basePoQuery)->count();
        $menunggu = (clone $basePoQuery)->where('status', 'Waiting')->count();
        $disetujui = (clone $basePoQuery)->where('status', 'Approved')->count();
        $ditolak = (clone $basePoQuery)->where('status', 'Rejected')->count();

        // 4. Filter data master yang dikirim ke view
        $outlets = DB::table('tbl_outlets')
            ->when($isDcUser, function ($query) use ($allowedOutlets) {
                return $query->whereIn('id', $allowedOutlets);
            })
            ->get();

        $bahans = DB::table('tbl_bahan_scm')->get();

        // 5. Terapkan filter pada List PO beserta detail barangnya
        $dataPO = DB::table('tbl_po')
            ->join('tbl_outlets', 'tbl_po.outlet_id', '=', 'tbl_outlets.id')
            ->select('tbl_po.*', 'tbl_outlets.nama_outlet')
            ->when($isDcUser, function ($query) use ($allowedOutlets) {
                return $query->whereIn('tbl_po.outlet_id', $allowedOutlets);
            })
            ->get()
            ->map(function ($po) {
                $po->items = DB::table('tbl_po_detail')
                    ->join('tbl_bahan_scm', 'tbl_po_detail.bahan_id', '=', 'tbl_bahan_scm.id')
                    ->leftJoin('tbl_units', 'tbl_po_detail.unit_id', '=', 'tbl_units.id')
                    ->where('tbl_po_detail.po_id', $po->id)
                    ->select(
                        'tbl_po_detail.*',
                        'tbl_bahan_scm.nama_bahan',
                        'tbl_bahan_scm.sumber_barang', // ← tambah ini
                        'tbl_units.nama_unit as satuan'
                    )
                    ->get();

                return $po;
            });

        return view('Purchasing.dashboardSCM', [
            'outlets' => $outlets,
            'bahans' => $bahans,
            'dataPO' => $dataPO,
            'total_po' => $total_po,
            'menunggu' => $menunggu,
            'disetujui' => $disetujui,
            'ditolak' => $ditolak,
            'namaDc' => $namaDc
        ]);
    }

    public function historyPO()
    {
        $histories = DB::table('tbl_po_history')
            ->join('tbl_po', 'tbl_po_history.po_id', '=', 'tbl_po.id') // Gabungkan tabel
            ->select('tbl_po_history.*', 'tbl_po.no_po')          // Pilih kolom no_po dari tabel po
            ->get();

        return view('Purchasing.historyPO', compact('histories'));
    }

    public function updateStatusPO(Request $request)
    {
        DB::beginTransaction();
        try {
            // Update status PO
            $affected = DB::table('tbl_po')
                ->where('id', $request->id)
                ->update(['status' => $request->status]);

            // Jika Approved dan ada data supplier per detail → simpan
            if ($request->status === 'Approved' && $request->supplier_data) {
                foreach ($request->supplier_data as $data) {
                    if (!empty($data['detail_id']) && !empty($data['supplier_id'])) {
                        DB::table('tbl_po_detail')
                            ->where('id', $data['detail_id'])
                            ->update(['supplier_id' => $data['supplier_id']]);
                    }
                }
            }

            DB::commit();

            if ($affected) {
                return response()->json(['success' => true]);
            }
            return response()->json(['success' => false], 400);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ── Ambil bahan SUPPLIER dari PO untuk dropdown di modal approve ──
    public function getPoSupplierItems($poId)
    {
        $po = DB::table('tbl_po')->where('id', $poId)->first();
        if (!$po) {
            return response()->json(['items' => [], 'suppliers' => []]);
        }

        // Ambil credential_id langsung dari tbl_outlets
        $outlet = DB::table('tbl_outlets')
            ->where('id', $po->outlet_id)
            ->select('id', 'credential_id')
            ->first();

        $credentialId = $outlet->credential_id ?? null;

        // Bahan yang sumber_barang = SUPPLIER dari PO ini
        $items = DB::table('tbl_po_detail as pd')
            ->join('tbl_bahan_scm as b', 'pd.bahan_id', '=', 'b.id')
            ->where('pd.po_id', $poId)
            ->where('b.sumber_barang', 'SUPPLIER')
            ->select('pd.id as detail_id', 'b.nama_bahan', 'pd.supplier_id')
            ->get();

        // Supplier dari credential mitra yang sama dengan outlet
        $suppliers = DB::table('tbl_suppliers')
            ->where('credential_id', $credentialId)
            ->where('flag_active', 1)
            ->select('id', 'supplier_name', 'supplier_code')
            ->orderBy('supplier_name')
            ->get();

        return response()->json([
            'items' => $items,
            'suppliers' => $suppliers,
        ]);
    }

    public function setupIndex()
    {
        // Ambil semua produk
        $products = DB::table('tbl_bahan_scm')->get();

        $warehouses = DB::table('tbl_warehouse')->get();

        // Ambil data distribusi yang sudah ada agar checkbox tercentang otomatis
        $distributions = DB::table('tbl_distribusi_produk')->get();

        return view('Purchasing.setupDistribution', compact('products', 'warehouses', 'distributions'));
    }

    public function save(Request $request)
    {
        // 1. Hapus semua data lama (Reset total)
        // Atau bisa pakai logik hapus berdasarkan produk yang di-edit saja
        DB::table('tbl_distribusi_produk')->truncate();

        // 2. Jika ada checkbox yang dicentang
        if ($request->has('dist')) {
            $dataToInsert = [];
            foreach ($request->dist as $productId => $warehouses) {
                foreach ($warehouses as $dcId => $value) {
                    $dataToInsert[] = [
                        'bahan_id' => $productId,
                        'warehouse_id' => $dcId
                    ];
                }
            }
            // 3. Insert sekaligus (Batch Insert lebih cepat daripada loop)
            DB::table('tbl_distribusi_produk')->insert($dataToInsert);
        }

        return back()->with('success', 'Distribusi berhasil diperbarui!');
    }


    public function controlStock(Request $request)
    {
        // =====================================================================
        // Migrasi: sebelumnya pakai tbl_stock (date-based snapshot) dan
        // tbl_stok_transaksi (ledger lama). Sekarang pakai tbl_stock_transactions
        // (ledger baru) — stok aktual = stok_sesudah dari baris terakhir per bahan
        // per warehouse.
        // =====================================================================

        // 1. Ambil data Warehouse (Distribution Center)
        $warehouses = DB::table('tbl_warehouse')->get();

        // 2. Ambil data Master Bahan SCM dengan satuan dan harga
        $products = DB::table('tbl_bahan_scm')
            ->leftJoin('tbl_bahan_unit', function ($join) {
                $join->on('tbl_bahan_scm.id', '=', 'tbl_bahan_unit.bahan_id')
                    ->where('tbl_bahan_unit.is_base_unit', '=', 1);
            })
            ->leftJoin('tbl_units', 'tbl_bahan_unit.unit_id', '=', 'tbl_units.id')
            ->select(
                'tbl_bahan_scm.*',
                'tbl_bahan_unit.base_price as harga_satuan',
                'tbl_units.nama_unit as nama_satuan'
            )
            ->get();

        // 3. Ambil stok aktual per bahan per warehouse dari tbl_stock_transactions
        // Cara: ambil stok_sesudah dari baris terakhir (id terbesar) per kombinasi
        // bahan_id + warehouse_id. Ini adalah stok current yang paling akurat.
        //
        // Struktur hasil: $stokData[bahan_id][warehouse_id] = stok_sesudah
        $latestStok = DB::table('tbl_stock_transactions as st')
            ->join(
                DB::raw('(SELECT MAX(id) as max_id
                  FROM tbl_stock_transactions
                  WHERE deleted_at IS NULL
                  GROUP BY bahan_id, warehouse_id) as latest'),
                'st.id',
                '=',
                'latest.max_id' // Cukup cocokkan ID-nya saja
            )
            ->select('st.bahan_id', 'st.warehouse_id', 'st.stok_sesudah')
            ->get();

        $stokData = [];
        $totalPerBahan = [];
        foreach ($latestStok as $s) {
            $stokData[$s->bahan_id][$s->warehouse_id] = $s->stok_sesudah;
            $totalPerBahan[$s->bahan_id] = ($totalPerBahan[$s->bahan_id] ?? 0) + $s->stok_sesudah;
        }

        // 4. Hitung Widget Kritis/Menipis
        $kritisCount = 0;
        $menipisCount = 0;
        foreach ($products as $p) {
            $stokTotal = $totalPerBahan[$p->id] ?? 0;
            if ($stokTotal <= 0)
                $kritisCount++;
            elseif ($stokTotal <= ($p->stok_minimal ?? 15))
                $menipisCount++;
        }

        // 5. Hitung Total Nilai Aset (stok × harga satuan)
        $totalAset = 0;
        foreach ($products as $p) {
            $total = $totalPerBahan[$p->id] ?? 0;
            $totalAset += $total * ($p->harga_satuan ?? 0);
        }

        foreach ($products as $p) {
            $p->stok_total = $totalPerBahan[$p->id] ?? 0;
        }

        // 6. History 10 transaksi terakhir dari tbl_stock_transactions
        $histories = DB::table('tbl_stock_transactions as t')
            ->join('tbl_bahan_scm as b', 't.bahan_id', '=', 'b.id')
            ->join('tbl_warehouse as w', 't.warehouse_id', '=', 'w.id')
            ->whereNull('t.deleted_at')
            ->select(
                't.id',
                't.tipe',
                't.jumlah',
                't.stok_sebelum',
                't.stok_sesudah',
                't.keterangan',
                't.created_at',
                'b.nama_bahan',
                'w.nama_warehouse'
            )
            ->orderBy('t.created_at', 'desc')
            ->limit(10)
            ->get();

        // 7. Fast Moving: 5 bahan dengan total KELUAR terbanyak 30 hari terakhir
        $fastMoving = DB::table('tbl_stock_transactions')
            ->join('tbl_bahan_scm', 'tbl_stock_transactions.bahan_id', '=', 'tbl_bahan_scm.id')
            ->where('tbl_stock_transactions.tipe', 'KELUAR')
            ->where('tbl_stock_transactions.created_at', '>=', now()->subDays(30))
            ->whereNull('tbl_stock_transactions.deleted_at')
            ->select(
                'tbl_bahan_scm.nama_bahan',
                DB::raw('SUM(tbl_stock_transactions.jumlah) as total_keluar')
            )
            ->groupBy('tbl_bahan_scm.id', 'tbl_bahan_scm.nama_bahan')
            ->orderBy('total_keluar', 'desc')
            ->limit(5)
            ->get();

        // selectedDate tidak lagi dipakai untuk filter stok
        // tapi tetap dikirim agar filter UI tidak error
        $selectedDate = now()->toDateString();

        return view('Purchasing.stockControl', compact(
            'products',
            'warehouses',
            'stokData',
            'histories',
            'kritisCount',
            'menipisCount',
            'fastMoving',
            'totalAset',
            'selectedDate'
        ));
    }

    private function generateNomorOpname(): string
    {
        $today = Carbon::now()->format('Ymd');
        $prefix = "OPN-{$today}-";

        $last = DB::table('tbl_stock_opname')
            ->where('nomor_opname', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->value('nomor_opname');

        $seq = $last ? ((int) substr($last, -3)) + 1 : 1;

        return $prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }

    // =========================================================================
    // STORE — dipanggil saat klik tombol "Save" (status = confirmed)
    // POST /purchasing/stock-opname/store
    // =========================================================================
    public function storeStockOpname(Request $request)
    {
        // --- Validasi input ---
        $validator = Validator::make($request->all(), [
            'warehouse_id' => 'required|integer|exists:tbl_warehouse,id',
            'adjustment_date' => 'required|date',
            'adjustment_type' => 'required|in:in,out',
            'notes' => 'required|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:tbl_bahan_scm,id',
            'items.*.current_stock' => 'required|numeric|min:0',
            'items.*.adj_qty' => 'required|numeric|min:0',
            'items.*.final_stock' => 'required|numeric',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $adjType = $request->adjustment_type; // 'in' atau 'out'
            $tipeDb = 'ADJUSTMENT';               // tipe di tbl_stock_transactions
            $warehouseId = $request->warehouse_id;
            $tanggal = $request->adjustment_date;
            $items = $request->items;

            // Hitung total nilai seluruh item
            $totalNilai = collect($items)->sum(function ($item) {
                return ($item['adj_qty'] ?? 0) * ($item['unit_cost'] ?? 0);
            });

            // ----------------------------------------------------------------
            // 1. Insert header ke tbl_stock_opname
            // ----------------------------------------------------------------
            $opnameId = DB::table('tbl_stock_opname')->insertGetId([
                'nomor_opname' => $this->generateNomorOpname(),
                'warehouse_id' => $warehouseId,
                'tanggal_opname' => $tanggal,
                'status' => 'confirmed',
                'jenis_adjustment' => $adjType,
                'keterangan' => $request->notes,
                'additional_info' => $request->additional_info ?? null,
                'total_nilai' => $totalNilai,
                'created_by' => null, // TODO: ganti auth()->id() saat sudah ada login
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // ----------------------------------------------------------------
            // 2. Loop item — insert ke opname_items & stock_transactions
            // ----------------------------------------------------------------
            foreach ($items as $item) {
                $bahanId = $item['product_id'];
                $currentStock = (float) $item['current_stock'];
                $adjQty = (float) $item['adj_qty'];
                $finalStock = (float) $item['final_stock'];
                $unitCost = (float) $item['unit_cost'];
                $physicalStock = (float) ($item['physical_stock'] ?? $currentStock);
                $totalNilaiItem = $adjQty * $unitCost;

                // Ambil unit_id base dari tbl_bahan_unit
                $unitId = DB::table('tbl_bahan_unit')
                    ->where('bahan_id', $bahanId)
                    ->where('is_base_unit', 1)
                    ->value('unit_id');

                // -- 2a. Insert ke tbl_stock_opname_items --
                DB::table('tbl_stock_opname_items')->insert([
                    'opname_id' => $opnameId,
                    'bahan_id' => $bahanId,
                    'unit_id' => $unitId,
                    'current_stock' => $currentStock,
                    'physical_stock' => $physicalStock,
                    'adj_qty' => $adjQty,
                    'final_stock' => $finalStock,
                    'unit_cost' => $unitCost,
                    'total_nilai' => $totalNilaiItem,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // -- 2b. Insert ke tbl_stock_transactions (ledger resmi) --
                // Stok sebelum = ambil stok_sesudah dari baris terakhir bahan ini
                $stokSebelum = DB::table('tbl_stock_transactions')
                    ->where('bahan_id', $bahanId)
                    ->where('warehouse_id', $warehouseId)
                    ->whereNull('deleted_at')
                    ->orderBy('id', 'desc')
                    ->value('stok_sesudah') ?? 0;

                // Jumlah yang dicatat di ledger selalu positif,
                // arah (tambah/kurang) ditentukan oleh tipe ADJUSTMENT
                // dan kita simpan final_stock sebagai stok_sesudah.
                // Jika adj_qty = 0, skip (tidak ada perubahan stok)
                if ($adjQty > 0) {
                    DB::table('tbl_stock_transactions')->insert([
                        'bahan_id' => $bahanId,
                        'unit_id' => $unitId ?? 0,
                        'warehouse_id' => $warehouseId,
                        'jumlah' => $adjQty,
                        'stok_sebelum' => $stokSebelum,
                        'stok_sesudah' => $finalStock,
                        'tipe' => $tipeDb,
                        'reference_type' => 'stock_opname',
                        'reference_id' => $opnameId,
                        'harga_satuan' => $unitCost,
                        'total_nilai' => $totalNilaiItem,
                        'keterangan' => "Adjustment #{$this->generateNomorOpname()} — {$request->notes}",
                        'created_by' => null, // TODO: auth()->id()
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Inventory adjustment berhasil disimpan!',
                'nomor_opname' => DB::table('tbl_stock_opname')->where('id', $opnameId)->value('nomor_opname'),
                'opname_id' => $opnameId,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================================
    // SAVE AS DRAFT — status = draft, TIDAK insert ke tbl_stock_transactions
    // POST /purchasing/stock-opname/draft
    // =========================================================================
    public function draftStockOpname(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'warehouse_id' => 'required|integer|exists:tbl_warehouse,id',
            'adjustment_date' => 'required|date',
            'adjustment_type' => 'required|in:in,out',
            'items' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $totalNilai = collect($request->items)->sum(function ($item) {
                return ($item['adj_qty'] ?? 0) * ($item['unit_cost'] ?? 0);
            });

            // Cek apakah sudah ada draft sebelumnya untuk warehouse + tanggal yang sama
            // Kalau ada, update saja (upsert behaviour)
            $existing = DB::table('tbl_stock_opname')
                ->where('warehouse_id', $request->warehouse_id)
                ->where('tanggal_opname', $request->adjustment_date)
                ->where('status', 'draft')
                ->whereNull('deleted_at')
                ->first();

            if ($existing) {
                // Update header
                DB::table('tbl_stock_opname')->where('id', $existing->id)->update([
                    'jenis_adjustment' => $request->adjustment_type,
                    'keterangan' => $request->notes ?? null,
                    'additional_info' => $request->additional_info ?? null,
                    'total_nilai' => $totalNilai,
                    'updated_at' => now(),
                ]);
                $opnameId = $existing->id;

                // Hapus items lama, ganti dengan yang baru
                DB::table('tbl_stock_opname_items')->where('opname_id', $opnameId)->delete();
            } else {
                $opnameId = DB::table('tbl_stock_opname')->insertGetId([
                    'nomor_opname' => $this->generateNomorOpname(),
                    'warehouse_id' => $request->warehouse_id,
                    'tanggal_opname' => $request->adjustment_date,
                    'status' => 'draft',
                    'jenis_adjustment' => $request->adjustment_type,
                    'keterangan' => $request->notes ?? null,
                    'additional_info' => $request->additional_info ?? null,
                    'total_nilai' => $totalNilai,
                    'created_by' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Insert items
            foreach ($request->items as $item) {
                $unitId = DB::table('tbl_bahan_unit')
                    ->where('bahan_id', $item['product_id'])
                    ->where('is_base_unit', 1)
                    ->value('unit_id');

                DB::table('tbl_stock_opname_items')->insert([
                    'opname_id' => $opnameId,
                    'bahan_id' => $item['product_id'],
                    'unit_id' => $unitId,
                    'current_stock' => $item['current_stock'] ?? 0,
                    'physical_stock' => $item['physical_stock'] ?? 0,
                    'adj_qty' => $item['adj_qty'] ?? 0,
                    'final_stock' => $item['final_stock'] ?? 0,
                    'unit_cost' => $item['unit_cost'] ?? 0,
                    'total_nilai' => (($item['adj_qty'] ?? 0) * ($item['unit_cost'] ?? 0)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Draft berhasil disimpan!',
                'opname_id' => $opnameId,
                'nomor_opname' => DB::table('tbl_stock_opname')->where('id', $opnameId)->value('nomor_opname'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function showDC($id)
    {
        // 1. Ambil info warehouse/DC
        $warehouse = DB::table('tbl_warehouse')->where('id', $id)->first();

        // 2. Ambil produk dan hitung rata-rata pengiriman keluar dari DC ini (7 hari terakhir)
        $products = DB::table('tbl_bahan_scm as b')
            ->leftJoin('tbl_stok_transaksi as t', function ($join) use ($id) {
                $join->on('b.id', '=', 't.bahan_id')
                    ->where('t.warehouse_id', '=', $id)
                    ->where('t.tipe', '=', 'keluar') // Hanya yang keluar dari DC ke Outlet
                    ->where('t.created_at', '>=', now()->subDays(7));
            })
            ->leftJoin('tbl_bahan_unit as bu', function ($join) {
                $join->on('b.id', '=', 'bu.bahan_id')->where('bu.is_base_unit', 1);
            })
            ->leftJoin('tbl_units as u', 'bu.unit_id', '=', 'u.id')
            ->select(
                'b.id',
                'b.nama_bahan',
                'b.stok_minimal', // Ini Safety Stock
                'b.lead_time',
                'b.rop_level',
                'u.nama_unit',
                DB::raw('ABS(SUM(IFNULL(t.jumlah, 0))) as total_keluar_7_hari')
            )
            ->groupBy('b.id', 'b.nama_bahan', 'b.stok_minimal', 'b.lead_time', 'b.rop_level', 'u.nama_unit')
            ->get();

        // 3. Ambil Stok Aktual saat ini di DC tersebut
        $stokAktual = DB::table('tbl_stok_transaksi')
            ->where('warehouse_id', $id)
            ->select('bahan_id', DB::raw('SUM(jumlah) as total'))
            ->groupBy('bahan_id')
            ->pluck('total', 'bahan_id');

        // 3. Ambil daftar outlet yang mapping ke DC ini dengan JOIN
        $outlets = DB::table('tbl_mapping_dc')
            ->join('tbl_outlets', 'tbl_mapping_dc.outlet_id', '=', 'tbl_outlets.id')
            ->where('tbl_mapping_dc.warehouse_id', $id)
            ->select('tbl_outlets.nama_outlet', 'tbl_outlets.alamat') // Sesuaikan nama kolomnya
            ->get();

        // dd($stokAktual);

        return view('Purchasing.detailDC', compact('warehouse', 'products', 'outlets', 'stokAktual'));
        // return "Halo, ini bukan Blade. Kalau tulisan ini muncul, berarti storage atau layout kamu bermasalah.";
    }

    public function stockTransfer(Request $request)
    {
        $request->validate([
            'id_bahan' => 'required',
            'dari_warehouse' => 'required',
            'tujuan_id' => 'required',
            'tipe_tujuan' => 'required|in:warehouse,outlet',
            'jumlah' => 'required|numeric|min:1',
            'jenis_armada' => 'required'
        ]);

        // Ambil berat per unit
        $bahan = DB::table('tbl_bahan_scm')->where('id', $request->id_bahan)->first();
        $totalKg = $request->jumlah * ($bahan->berat_per_unit ?? 0);
        $totalTon = $totalKg / 1000;

        DB::transaction(function () use ($request, $totalKg, $totalTon) {
            // 1. Kurangi Stok Asal
            $idOut = DB::table('tbl_stok_transaksi')->insertGetId([
                'bahan_id' => $request->id_bahan,
                'warehouse_id' => $request->dari_warehouse,
                'jumlah' => -$request->jumlah,
                'tipe' => 'keluar',
                'created_at' => now()
            ]);

            // 2. Simpan ke tabel logistik (tbl_pengiriman)
            DB::table('tbl_pengiriman')->insert([
                'transaksi_id' => $idOut,
                'dari_warehouse_id' => $request->dari_warehouse,
                'tujuan_id' => $request->tujuan_id,
                'tipe_tujuan' => $request->tipe_tujuan,
                'total_tonase' => $totalTon,
                'jenis_armada' => $request->jenis_armada,
                'status' => 'proses',
                'created_at' => now()
            ]);
        });

        return back()->with('success', 'Pengiriman diproses. Estimasi beban: ' . $totalTon . ' Ton.');
    }

    public function storeStock(Request $request)
    {
        $request->validate([
            'bahan_id' => 'required',
            'warehouse_id' => 'required',
            'jumlah' => 'required|numeric|min:1',
            'tipe' => 'required|in:masuk,keluar'
        ]);

        // Jika 'keluar', kita jadikan jumlahnya negatif agar otomatis mengurangi total
        $jumlah = ($request->tipe === 'keluar') ? -$request->jumlah : $request->jumlah;

        DB::table('tbl_stok_transaksi')->insert([
            'bahan_id' => $request->bahan_id,
            'warehouse_id' => $request->warehouse_id,
            'jumlah' => $jumlah,
            'tipe' => $request->tipe,
            'keterangan' => $request->keterangan ?? 'Transaksi manual SCM',
            'created_at' => now()
        ]);

        return back()->with('success', 'Transaksi ' . $request->tipe . ' berhasil disimpan!');
    }
    public function distributorList()
    {
        // ambil semua pertanyaan dari tabel
        $distributors = DB::table('tbl_warehouse')->get();

        return view('Purchasing.listDistributor', compact('distributors'));
    }

    public function storeDC(Request $request)
    {
        DB::beginTransaction();
        try {
            DB::table('tbl_warehouse')->insert([
                'nama_warehouse' => $request->nama_warehouse,
            ]);
            DB::commit();
            return back()->with('success', 'DC berhasil ditambah');
        } catch (\Exception $e) {
            DB::rollback();
            // PAKSA ERROR MUNCUL DI LAYAR
            dd([
                'pesan_error' => $e->getMessage(),
                'baris_berapa' => $e->getLine(),
                'data_input' => $request->all()
            ]);
        }
    }

    public function updateDC(Request $request)
    {
        DB::table('tbl_warehouse')->where('id', $request->id)->update([
            'nama_warehouse' => $request->nama_warehouse,
        ]);
        return back()->with('success', 'DC berhasil diupdate');
    }

    public function destroyDC($id)
    {
        DB::table('tbl_warehouse')->where('id', $id)->delete();
        return back()->with('success', 'DC berhasil dihapus');
    }

    public function unitList()
    {
        // ambil semua pertanyaan dari tabel
        $units = DB::table('tbl_units')->get();

        return view('Purchasing.unitList', compact('units'));
    }

    public function destroyUnit($id)
    {
        // Hapus record dari database
        DB::table('tbl_units')->where('id', $id)->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus!');
    }

    public function storeUnit(Request $request)
    {
        DB::beginTransaction();
        try {
            DB::table('tbl_units')->insert([
                'nama_unit' => $request->nama_unit,
            ]);
            DB::commit();
            return back()->with('success', 'Unit berhasil ditambah');
        } catch (\Exception $e) {
            DB::rollback();
            // PAKSA ERROR MUNCUL DI LAYAR
            dd([
                'pesan_error' => $e->getMessage(),
                'baris_berapa' => $e->getLine(),
                'data_input' => $request->all()
            ]);
        }
    }

    public function updateUnit(Request $request)
    {
        DB::table('tbl_units')->where('id', $request->id)->update([
            'nama_unit' => $request->nama_unit,
        ]);
        return back()->with('success', 'Unit berhasil diupdate');
    }


    public function armadaList()
    {
        // ambil semua pertanyaan dari tabel
        $armada = DB::table('tbl_armada')->get();

        return view('Purchasing.armadaList', compact('armada'));
    }

    public function destroyArmada($id)
    {
        // 1. Ambil data untuk mendapatkan path foto sebelum data di database dihapus
        $data = DB::table('tbl_armada')->where('id', $id)->first();

        // Hapus record dari database
        DB::table('tbl_armada')->where('id', $id)->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus!');
    }

    public function storeArmada(Request $request)
    {
        DB::beginTransaction();
        try {
            DB::table('tbl_armada')->insert([
                'nama_armada' => $request->nama_armada,
                'no_pol' => $request->no_pol,
                'kapasitas_kg' => $request->kapasitas_kg
            ]);
            DB::commit();
            return back()->with('success', 'Armada berhasil ditambah');
        } catch (\Exception $e) {
            DB::rollback();
            // PAKSA ERROR MUNCUL DI LAYAR
            dd([
                'pesan_error' => $e->getMessage(),
                'baris_berapa' => $e->getLine(),
                'data_input' => $request->all()
            ]);
        }
    }

    public function updateArmada(Request $request)
    {
        DB::table('tbl_armada')->where('id', $request->id)->update([
            'nama_armada' => $request->nama_armada,
            'no_pol' => $request->no_pol,
            'kapasitas_kg' => $request->kapasitas_kg,
        ]);
        return back()->with('success', 'Armada berhasil diupdate');
    }

    public function driverList()
    {
        // ambil semua pertanyaan dari tabel
        $driver = DB::table('tbl_supir')->get();

        return view('Purchasing.driverList', compact('driver'));
    }

    public function destroyDriver($id)
    {

        // Hapus record dari database
        DB::table('tbl_supir')->where('id', $id)->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus!');
    }

    public function storeDriver(Request $request)
    {
        DB::table('tbl_supir')->insert([
            'nama_supir' => $request->nama_supir,
            'no_telp' => $request->no_telp,
            'alamat' => $request->alamat
        ]);
        return back()->with('success', 'Data berhasil ditambah');
    }

    public function updateDriver(Request $request)
    {
        DB::table('tbl_supir')->where('id', $request->id)->update([
            'nama_supir' => $request->nama_supir,
            'no_telp' => $request->no_telp,
            'alamat' => $request->alamat
        ]);
        return back()->with('success', 'Data berhasil diupdate');
    }

    public function setupTonaseView()
    {
        // Ambil semua bahan
        $products = DB::table('tbl_bahan_scm')->get();
        // Ambil data berat yang sudah ada (biar muncul angkanya)
        $berat_list = DB::table('tbl_berat_bahan')->pluck('berat_per_unit', 'bahan_id');

        return view('Purchasing.setupTonase', compact('products', 'berat_list'));
    }

    public function saveTonase(Request $request)
    {
        // 1. Validasi untuk array
        $request->validate([
            'berat' => 'required|array',
            'berat.*' => 'required|numeric' // Memastikan setiap nilai dalam array adalah angka
        ]);

        // 2. Loop melalui array berat yang dikirim
        foreach ($request->berat as $bahan_id => $berat_per_unit) {

            DB::table('tbl_berat_bahan')->updateOrInsert(
                ['bahan_id' => $bahan_id],
                [
                    'berat_per_unit' => $berat_per_unit,
                    'updated_at' => now()
                ]
            );
        }

        return back()->with('success', 'Semua data berat bahan berhasil disimpan!');
    }

    //====REKAP TONASE
    public function rekapTonase(Request $request)
    {
        // Ambil ID Warehouse yang sedang dibuka oleh admin
        $warehouseId = $request->input('warehouse_id', 1);

        // 1. Ambil List PO yang statusnya 'approved' dan milik Warehouse tersebut
        $dataPO = DB::table('tbl_po')
            ->join('tbl_outlets', 'tbl_po.outlet_id', '=', 'tbl_outlets.id')
            ->select('tbl_po.*', 'tbl_outlets.nama_outlet')
            // Tambahkan subquery untuk menghitung tonase tanpa harus group by tabel PO
            ->addSelect([
                'total_kg' => DB::table('tbl_po_detail')
                    ->join('tbl_berat_bahan', 'tbl_po_detail.bahan_id', '=', 'tbl_berat_bahan.bahan_id')
                    ->selectRaw('SUM(tbl_po_detail.jumlah * COALESCE(tbl_berat_bahan.berat_per_unit, 0))')
                    ->whereColumn('tbl_po_detail.po_id', 'tbl_po.id')
            ])
            ->where('tbl_po.warehouse_id', '=', $warehouseId)
            ->where('tbl_po.status', '=', 'approved')
            ->get();

        // 2. Hitung Total Tonase (KG)
        $totalTonaseKg = DB::table('tbl_po')
            ->join('tbl_po_detail', 'tbl_po.id', '=', 'tbl_po_detail.po_id')
            ->leftJoin('tbl_berat_bahan', 'tbl_po_detail.bahan_id', '=', 'tbl_berat_bahan.bahan_id')
            ->where('tbl_po.warehouse_id', '=', $warehouseId) // Filter langsung pakai kolom ini
            ->where('tbl_po.status', '=', 'Approved')
            ->sum(DB::raw('tbl_po_detail.jumlah * COALESCE(tbl_berat_bahan.berat_per_unit, 0)'));

        // 3. Ambil Armada yang mangkal di Warehouse tersebut
        $listArmada = DB::table('tbl_armada')
            ->where('warehouse_id', '=', $warehouseId)
            ->get();

        return view('Purchasing.orderList', compact('dataPO', 'totalTonaseKg', 'listArmada', 'warehouseId'));
    }

    public function simpanPengiriman(Request $request)
    {
        $warehouseId = $request->warehouse_id;

        // 1. Ambil PO yang statusnya approved & ada di warehouse tersebut
        $poBelumDikirim = DB::table('tbl_po')
            ->join('tbl_po_detail', 'tbl_po.id', '=', 'tbl_po_detail.po_id')
            ->join('tbl_distribusi_produk', function ($join) use ($warehouseId) {
                $join->on('tbl_po_detail.bahan_id', '=', 'tbl_distribusi_produk.bahan_id')
                    ->where('tbl_distribusi_produk.warehouse_id', '=', $warehouseId);
            })
            ->where('tbl_po.status', 'approved')
            ->select('tbl_po.*')
            ->distinct()
            ->get();

        // 2. Loop simpan ke tbl_pengiriman
        foreach ($poBelumDikirim as $po) {
            DB::table('tbl_pengiriman')->insert([
                'transaksi_id' => $po->id,
                'armada_id' => $request->armada_id,
                'warehouse_id' => $warehouseId, // PENTING: Catat dari DC mana ini dikirim
                'total_tonase' => $request->total_tonase_kg,
                'status' => 'proses',
                'created_at' => now()
            ]);

            // Update status PO
            DB::table('tbl_po')->where('id', $po->id)->update(['status' => 'Processed']);
        }

        return redirect()->route('admin.rekap-armada')->with('success', 'Armada berhasil dipasangkan!');
    }

    public function simpanArmada(Request $request)
    {
        // Update tbl_po yang statusnya 'approved' dan belum ada armadanya
        DB::table('tbl_po')
            ->where('status', 'approved')
            ->whereNull('id_armada')
            ->update([
                'id_armada' => $request->id_armada,
                'status' => 'siap_kirim', // Opsional: ganti status agar tidak muncul di rekap lagi
                'updated_at' => now()
            ]);

        return redirect()->back()->with('success', 'Armada berhasil ditentukan untuk semua pesanan!');
    }

    //====== Outlet Mapping DC dan Supplier=====
    public function indexMapping()
    {
        // 1. Mengambil semua outlet kecuali DC dan Head Office
        // Ditambahkan select() agar query lebih ringan hanya mengambil kolom yang dibutuhkan
        $outlets = DB::table('tbl_outlets')
            ->select('id', 'nama_outlet')
            ->where('nama_outlet', 'NOT LIKE', 'DC%')
            ->where('nama_outlet', 'NOT LIKE', 'HEAD OFFICE%') // Ditambah % antisipasi jika ada spasi/karakter tambahan
            ->orderBy('nama_outlet', 'asc') // Opsional: Diurutkan abjad agar rapi di tabel
            ->get();

        // 2. Mengambil data warehouse untuk pilihan dropdown
        $warehouses = DB::table('tbl_warehouse')
            ->select('id', 'nama_warehouse')
            ->orderBy('nama_warehouse', 'asc')
            ->get();

        // 3. Mengambil data mapping yang sudah ada
        // Menggunakan keyBy('outlet_id') sudah sangat tepat untuk mempercepat pencarian di View
        $mappingData = DB::table('tbl_mapping_dc')
            ->get()
            ->keyBy('outlet_id');

        // Pastikan nama path view sesuai dengan struktur folder Anda (huruf kecil/besar berpengaruh di Linux/Hosting)
        return view('Purchasing.outletMapping', compact('outlets', 'warehouses', 'mappingData'));
    }

    public function simpanMapping(Request $request)
    {
        // Simpan/Update data mapping
        foreach ($request->mapping as $outletId => $warehouseId) {
            if ($warehouseId) { // Hanya simpan jika warehouse dipilih
                DB::table('tbl_mapping_dc')->updateOrInsert(
                    ['outlet_id' => $outletId],
                    ['warehouse_id' => $warehouseId, 'created_at' => now()]
                );
            }
        }
        return back()->with('success', 'Mapping berhasil disimpan!');
    }

    public function indexMappingSupplier()
    {
        // 1. Ambil data outlet + daftar supplier yang sudah ter-mapping
        $outlets = DB::table('tbl_outlets as o')
            ->leftJoin('tbl_mapping_supplier as m', 'o.id', '=', 'm.outlet_id')
            ->leftJoin('tbl_suppliers as s', 'm.supplier_id', '=', 's.id')
            ->select(
                'o.id',
                'o.nama_outlet',
                'o.alamat',
                DB::raw('GROUP_CONCAT(s.supplier_name SEPARATOR ", ") as daftar_supplier')
            )
            // --- TAMBAHKAN FILTER DI SINI ---
            ->where('o.nama_outlet', 'NOT LIKE', '%DC%')
            ->where('o.nama_outlet', 'NOT LIKE', '%HEAD OFFICE%')
            ->groupBy('o.id', 'o.nama_outlet', 'o.alamat')
            ->get();

        // 2. Ambil master supplier untuk isi dropdown di Modal Bulk
        $allSuppliers = DB::table('tbl_suppliers')->get();

        // Kirim keduanya ke view
        return view('Purchasing.mappingSupplier', compact('outlets', 'allSuppliers'));
    }

    public function simpanMappingSupplier(Request $request)
    {
        // Sekarang menerima ARRAY outlet_ids dan supplier_ids
        $outletIds = $request->outlet_ids; // Array ID Outlet
        $supplierIds = $request->supplier_ids; // Array ID Supplier yang dipilih di Select2
        $mode = $request->mode; // Pastikan <input type="hidden" name="mode"> ada di modal

        try {
            DB::transaction(function () use ($outletIds, $supplierIds, $mode) {
                foreach ($outletIds as $oid) {

                    // --- KUNCI PERBAIKAN DI SINI ---
                    // Jika sedang MODE EDIT, kita harus "bersihkan" dulu data lama 
                    // supaya kalau ada yang dihapus di modal, di DB juga hilang.
                    if ($mode == 'edit') {
                        DB::table('tbl_mapping_supplier')
                            ->where('outlet_id', $oid)
                            ->delete();
                    }

                    // Setelah bersih (atau jika mode bulk), kita masukkan supplier yang terpilih
                    if (!empty($supplierIds)) {
                        $dataInsert = [];
                        foreach ($supplierIds as $sid) {
                            $dataInsert[] = [
                                'outlet_id' => $oid,
                                'supplier_id' => $sid,
                                'created_at' => now()
                            ];
                        }
                        // Gunakan insert untuk memasukkan semua yang dipilih
                        DB::table('tbl_mapping_supplier')->insert($dataInsert);
                    }
                }
            });

            return response()->json(['status' => 'success', 'msg' => 'Data mapping berhasil diperbarui!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'msg' => $e->getMessage()], 500);
        }
    }

    public function editMappingSupplier($outlet_id)
    {
        // 1. Ambil data outlet-nya
        $outlet = DB::table('tbl_outlets')->where('id', $outlet_id)->first();

        // 2. Ambil ID supplier yang sudah terpilih untuk outlet ini
        $selectedSupplierIds = DB::table('tbl_mapping_supplier')
            ->where('outlet_id', $outlet_id)
            ->pluck('supplier_id')
            ->toArray();

        return response()->json([
            'outlet' => $outlet,
            'selected_ids' => $selectedSupplierIds
        ]);
    }

    public function monitoringPeta()
    {
        $daftarTitik = DB::table('tbl_surat_jalan as sj')
            ->join('tbl_po as po', 'sj.id', '=', 'po.sj_id')
            ->join('tbl_outlet as ot', 'po.id_outlet', '=', 'ot.id')
            ->select(
                'sj.no_surat_jalan',
                'sj.id as sj_id',
                'po.no_po',
                'ot.nama_outlet',
                'ot.latitude',
                'ot.longitude',
                'ot.alamat'
            )
            // Misal hanya yang belum selesai atau sesuai filter tertentu
            ->whereNotNull('ot.latitude')
            ->whereNotNull('ot.longitude')
            ->get();

        return view('nama_view_kamu', compact('daftarTitik'));
    }


    // ======== DATA SUPPLIER =======
    public function indexSupplier(Request $request)
    {
        // Ambil semua mitra untuk filter dropdown
        $credentials = DB::table('tbl_api_credentials')->where('is_active', 1)->get();

        // Ambil supplier berdasarkan filter (jika ada)
        $selectedMitra = $request->get('credential_id');

        $suppliers = DB::table('tbl_suppliers')
            ->join('tbl_api_credentials', 'tbl_suppliers.credential_id', '=', 'tbl_api_credentials.id')
            ->select(
                'tbl_suppliers.*',
                'tbl_api_credentials.credential_code'
            )
            ->when($selectedMitra, function ($query, $selectedMitra) {
                // Tambahkan nama tabel di depan 'credential_id'
                return $query->where('tbl_suppliers.credential_id', $selectedMitra);
            }, function ($query) {
                // Tambahkan nama tabel di depan 'id' biar nggak ambigu
                return $query->where('tbl_suppliers.id', 0);
            })
            ->get();

        return view('Purchasing.supplierList', compact('suppliers', 'credentials', 'selectedMitra'));
    }

    public function storeSupplier(Request $request)
    {
        DB::table('tbl_supplier')->insert([
            'nama_supplier' => $request->nama_supplier,
            'alamat' => $request->alamat,
            'created_at' => now()
        ]);
        return back()->with('success', 'Supplier berhasil ditambah');
    }

    public function updateSupplier(Request $request)
    {
        DB::table('tbl_supplier')->where('id', $request->id)->update([
            'nama_supplier' => $request->nama_supplier,
            'alamat' => $request->alamat,
        ]);
        return back()->with('success', 'Supplier berhasil diupdate');
    }

    public function destroySupplier($id)
    {
        DB::table('tbl_supplier')->where('id', $id)->delete();
        return back()->with('success', 'Supplier berhasil dihapus');
    }

    // ======= ALL ABOUT PRODUK =====
    public function indexBahan()
    {
        // Mengambil semua data produk untuk ditampilkan di tabel
        $listBahan = DB::table('tbl_bahan_scm')
            ->leftJoin('tbl_bahan_unit', function ($join) {
                $join->on('tbl_bahan_scm.id', '=', 'tbl_bahan_unit.bahan_id')
                    ->where('tbl_bahan_unit.is_stock_unit', '=', 1); // Kita ambil satuan stok sebagai default tampilan
            })
            ->leftJoin('tbl_units', 'tbl_bahan_unit.unit_id', '=', 'tbl_units.id')
            ->select(
                'tbl_bahan_scm.*',
                'tbl_units.nama_unit as satuan_tampil',
                'tbl_bahan_unit.base_price',
                'tbl_bahan_unit.weight'
            )
            ->get();

        return view('Purchasing.setupProduct', compact('listBahan'));
    }

    public function createBahan()
    {
        // Ambil semua master unit untuk dropdown di tabel dinamis
        $units = DB::table('tbl_units')->get();

        // Ambil kategori untuk dropdown kategori produk
        // $categories = DB::table('tbl_kategori_bahan')->get();

        return view('Purchasing.createProduct', compact('units'));
    }

    public function storeBahan(Request $request)
    {
        // Gunakan dd untuk melihat data yang dikirim jika masih gagal
        // dd($request->all());

        DB::beginTransaction();

        try {
            // 1. Simpan ke tbl_bahan_scm (Sesuaikan dengan image_72dd41.png)
            $bahanId = DB::table('tbl_bahan_scm')->insertGetId([
                'nama_bahan' => $request->nama_bahan, // Pastikan name di HTML adalah 'product_name'
                'product_code' => $request->product_code,
                'sumber_barang' => $request->sumber_barang,
                'stok_minimal' => $request->stok_minimal ?? 0,
                'created_at' => now()
            ]);

            // 2. Simpan ke tbl_bahan_unit
            if ($request->has('units')) {
                foreach ($request->units as $unit) {
                    DB::table('tbl_bahan_unit')->insert([
                        'bahan_id' => $bahanId,
                        'unit_id' => $unit['unit_id'],
                        'conversion_factor' => $unit['factor'] ?? 1,
                        'base_price' => $unit['price'] ?? 0,
                        'weight' => $unit['weight'] ?? 0,
                        'is_base_unit' => isset($unit['is_base']) ? 1 : 0,
                        'is_stock_unit' => isset($unit['is_stock']) ? 1 : 0,
                        'is_purchase_unit' => isset($unit['is_purchase']) ? 1 : 0,
                        'is_transfer_unit' => isset($unit['is_transfer']) ? 1 : 0,
                        // Tambahkan is_sales_unit jika ada di tabel unit kamu (image_72c6df)
                        'is_sales_unit' => isset($unit['is_sales']) ? 1 : 0,
                        // 'created_at'        => now()
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('scm.index-bahan')->with('success', 'Produk Berhasil Disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            // Aktifkan DD ini untuk melihat pesan error spesifik jika gagal
            dd("Error baris: " . $e->getLine() . " | Pesan: " . $e->getMessage());
        }
    }

    public function showBahan($id)
    {
        // 1. Ambil data utama produk
        $product = DB::table('tbl_bahan_scm')
            ->where('id', $id)
            ->first();

        // Jika produk tidak ditemukan, balikkan ke index
        if (!$product) {
            return redirect()->route('scm.index-bahan')->with('error', 'Produk tidak ditemukan');
        }

        // 2. Ambil detail unit dan join dengan tabel master unit untuk dapat nama unitnya
        // Asumsi tabel master unit kamu bernama 'tbl_units' atau sejenisnya
        $details = DB::table('tbl_bahan_unit')
            ->join('tbl_units', 'tbl_bahan_unit.unit_id', '=', 'tbl_units.id')
            ->where('tbl_bahan_unit.bahan_id', $id)
            ->select('tbl_bahan_unit.*', 'tbl_units.nama_unit')
            ->get();

        // 3. Cari nama base unit untuk ditampilkan di kolom "Base Unit" pada tabel
        // Kita cari baris yang is_base_unit = 1
        $baseUnit = $details->where('is_base_unit', 1)->first();
        $product->base_unit_name = $baseUnit ? $baseUnit->nama_unit : '-';

        // 4. Tempelkan details ke objek product agar mudah dipanggil di view
        $product->details = $details;

        return view('Purchasing.viewProduct', compact('product'));
    }

    public function importBahan(Request $request)
    {
        // 1. Ambil array datanya
        $data = Excel::toArray([], $request->file('file'));

        // 2. Data ada di index [0] (sheet pertama)
        foreach ($data[0] as $index => $row) {
            // Skip baris pertama kalau baris pertama itu header
            if ($index == 0)
                continue;

            // 3. Masukkan ke database via Query Builder
            DB::table('tbl_bahan_scm')->insert([
                'product_name' => $row[0], // index 0 = kolom A
                'unit_display' => $row[1], // index 1 = kolom B
                'berat_per_unit' => $row[2], // dst...
                'price' => $row[3],
                'expire_date' => $row[4],
            ]);
        }

        return back()->with('success', 'Data berhasil diimport!');
    }

    public function editBahan($id)
    {
        $product = DB::table('tbl_bahan_scm')->where('id', $id)->first();

        // Cegah error kalau ID tidak ada di database
        if (!$product) {
            return redirect()->route('scm.index-bahan')->with('error', 'Produk tidak ditemukan');
        }

        $details = DB::table('tbl_bahan_unit')
            ->join('tbl_units', 'tbl_bahan_unit.unit_id', '=', 'tbl_units.id')
            ->where('tbl_bahan_unit.bahan_id', $id)
            ->select('tbl_bahan_unit.*', 'tbl_units.nama_unit')
            ->get();

        // 3. Cari nama base unit untuk ditampilkan di kolom "Base Unit" pada tabel
        // Kita cari baris yang is_base_unit = 1
        $baseUnit = $details->where('is_base_unit', 1)->first();
        $product->base_unit_name = $baseUnit ? $baseUnit->nama_unit : '-';

        // 4. Tempelkan details ke objek product agar mudah dipanggil di view
        $product->details = $details;

        $units = DB::table('tbl_units')->get();
        return view('Purchasing.editProduct', compact('product', 'units', 'details'));
    }

    public function updateBahan(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            // 1. Update data utama di tbl_bahan_scm
            DB::table('tbl_bahan_scm')->where('id', $id)->update([
                'nama_bahan' => $request->nama_bahan,
                'product_code' => $request->product_code,
                'sumber_barang' => $request->sumber_barang,
                'stok_minimal' => $request->stok_minimal ?? 0,
                'updated_at' => now()
            ]);

            // 2. Hapus total unit lama milik produk ini agar bersih dan tidak duplikat
            DB::table('tbl_bahan_unit')->where('bahan_id', $id)->delete();

            // 3. Insert ulang unit-unit yang dikirim dari form edit
            if ($request->has('units')) {
                foreach ($request->units as $unit) {
                    DB::table('tbl_bahan_unit')->insert([
                        'bahan_id' => $id,
                        'unit_id' => $unit['unit_id'],
                        'conversion_factor' => $unit['factor'] ?? 1,
                        'base_price' => $unit['price'] ?? 0,
                        'weight' => $unit['weight'] ?? 0,

                        // Ganti isset dengan logika array_key_exists atau langsung mengecek nilainya
                        'is_base_unit' => (isset($unit['is_base']) && $unit['is_base'] == 1) ? 1 : 0,
                        'is_stock_unit' => (isset($unit['is_stock']) && $unit['is_stock'] == 1) ? 1 : 0,
                        'is_purchase_unit' => (isset($unit['is_purchase']) && $unit['is_purchase'] == 1) ? 1 : 0,
                        'is_transfer_unit' => (isset($unit['is_transfer']) && $unit['is_transfer'] == 1) ? 1 : 0,
                        'is_sales_unit' => (isset($unit['is_sales']) && $unit['is_sales'] == 1) ? 1 : 0,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('scm.index-bahan')->with('success', 'Produk Berhasil Diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            dd("Error baris: " . $e->getLine() . " | Pesan: " . $e->getMessage());
        }
    }

    public function deleteBahan($id)
    {
        DB::table('tbl_bahan_scm')->where('id', $id)->delete();
        return back()->with('success', 'Data berhasil dihapus!');
    }

    public function indexPricelist()
    {
        // 1. Ambil data pricelist menggunakan JOIN
        $pricelists = DB::table('tbl_pricelist_scm as p')
            ->join('tbl_bahan_unit as bu', 'p.bahan_unit_id', '=', 'bu.id')
            ->join('tbl_bahan_scm as b', 'bu.bahan_id', '=', 'b.id')
            ->join('tbl_units as u', 'bu.unit_id', '=', 'u.id')
            ->select(
                'p.id',
                'p.harga_ho',
                'p.harga_mitra',
                'b.nama_bahan',
                'u.nama_unit'
            )
            ->orderBy('p.id', 'desc')
            ->get();

        // 2. Ambil bahan yang is_purchase_unit = 1 DAN belum ada di tbl_pricelist
        $availableItems = DB::table('tbl_bahan_unit as bu')
            ->join('tbl_bahan_scm as b', 'bu.bahan_id', '=', 'b.id')
            ->join('tbl_units as u', 'bu.unit_id', '=', 'u.id')
            ->leftJoin('tbl_pricelist_scm as p', 'bu.id', '=', 'p.bahan_unit_id')
            ->where('bu.is_purchase_unit', 1)
            ->whereNull('p.id') // Filter: Yang id pricelist-nya masih kosong (belum didaftarkan)
            ->select(
                'bu.id as bahan_unit_id',
                'b.nama_bahan',
                'u.nama_unit'
            )
            ->get();

        return view('Purchasing.pricelist', compact('pricelists', 'availableItems'));
    }

    public function storePricelist(Request $request)
    {
        // Validasi tetap bisa menggunakan tabel
        $request->validate([
            'bahan_unit_id' => 'required|exists:tbl_bahan_unit,id|unique:tbl_pricelist_scm,bahan_unit_id',
            'harga_ho'      => 'required|numeric|min:0',
            'harga_mitra'   => 'required|numeric|min:0',
        ]);

        // Insert menggunakan Query Builder
        DB::table('tbl_pricelist_scm')->insert([
            'bahan_unit_id' => $request->bahan_unit_id,
            'harga_ho'      => $request->harga_ho,
            'harga_mitra'   => $request->harga_mitra,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return response()->json(['status' => 'success', 'msg' => 'Pricelist berhasil ditambahkan!']);
    }

    public function updatePricelist(Request $request, $id)
    {
        $request->validate([
            'harga_ho'    => 'required|numeric|min:0',
            'harga_mitra' => 'required|numeric|min:0',
        ]);

        // Update menggunakan Query Builder
        DB::table('tbl_pricelist_scm')
            ->where('id', $id)
            ->update([
                'harga_ho'    => $request->harga_ho,
                'harga_mitra' => $request->harga_mitra,
                'updated_at'  => now(),
            ]);

        return response()->json(['status' => 'success', 'msg' => 'Pricelist berhasil diperbarui!']);
    }

    public function importPricelist(Request $request)
    {
        $data = $request->input('data');

        if (!$data || !is_array($data)) {
            return response()->json(['status' => 'error', 'message' => 'Data tidak valid atau kosong.'], 400);
        }

        DB::beginTransaction();
        try {
            $berhasil = 0;
            $gagalDitemukan = 0;

            foreach ($data as $row) {
                // Ambil dari JS yang sudah dibersihkan
                $namaProduk = $row['nama_produk'] ?? '';
                $hargaHo = isset($row['harga_ho']) ? (float) $row['harga_ho'] : 0;
                $hargaMitra = isset($row['harga_mitra']) ? (float) $row['harga_mitra'] : 0;

                if (empty($namaProduk)) continue;

                // Cari ID berdasarkan Nama Bahan dan pastikan is_purchase_unit = 1
                $bahanUnit = DB::table('tbl_bahan_unit as bu')
                    ->join('tbl_bahan_scm as b', 'bu.bahan_id', '=', 'b.id')
                    ->where('b.nama_bahan', $namaProduk)
                    ->where('bu.is_purchase_unit', 1)
                    ->select('bu.id')
                    ->first();

                if ($bahanUnit) {
                    $bahanUnitId = $bahanUnit->id;
                    $exists = DB::table('tbl_pricelist_scm')->where('bahan_unit_id', $bahanUnitId)->first();

                    if ($exists) {
                        // Update
                        DB::table('tbl_pricelist_scm')
                            ->where('id', $exists->id)
                            ->update([
                                'harga_ho' => $hargaHo,
                                'harga_mitra' => $hargaMitra,
                                'updated_at' => now(),
                            ]);
                    } else {
                        // Insert Baru
                        DB::table('tbl_pricelist_scm')->insert([
                            'bahan_unit_id' => $bahanUnitId,
                            'harga_ho' => $hargaHo,
                            'harga_mitra' => $hargaMitra,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                    $berhasil++;
                } else {
                    $gagalDitemukan++; // Nama barang tidak cocok/tidak ada di database
                }
            }

            DB::commit();
            
            $msg = "Berhasil update/insert $berhasil produk.";
            if ($gagalDitemukan > 0) {
                $msg .= " Namun ada $gagalDitemukan produk yang dilewati karena nama tidak sesuai dengan database SCM.";
            }

            return response()->json(['status' => 'success', 'msg' => $msg]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()], 500);
        }
    }


    public function indexPendingDelivery()
    {
        // 1. Buat base query yang berlaku untuk SEMUA user (Backoffice & DC)
        $query = DB::table('tbl_po')
            ->join('tbl_outlets', 'tbl_po.outlet_id', '=', 'tbl_outlets.id')
            ->select('tbl_po.*', 'tbl_outlets.nama_outlet as outlet_name')
            ->where('tbl_po.status', 'Approved')
            ->whereNull('tbl_po.sj_id');

        // 2. Cek apakah user yang login adalah Backoffice atau bukan.
        // Asumsi: ada kolom 'role' di tabel users untuk membedakannya. 
        $userRole = Auth::user()->role;

        // 3. Jika BUKAN backoffice, maka tambahkan join dan filter mapping DC-nya
        if ($userRole !== 'superadmin') {
            $userWarehouseId = Auth::user()->warehouse_id;

            $query->join('tbl_mapping_dc', 'tbl_po.outlet_id', '=', 'tbl_mapping_dc.outlet_id')
                ->where('tbl_mapping_dc.warehouse_id', $userWarehouseId);
        }

        // 4. Eksekusi query
        $listPO = $query->get();

        // 5. Kembalikan ke blade yang sama
        return view('Purchasing.approvedPO', compact('listPO'));
    }

    public function buatSJ(Request $request)
    {
        $poIds = $request->input('po_ids');

        if (!$poIds || !is_array($poIds) || count($poIds) === 0) {
            return redirect()->route('scm.pengiriman.index')
                ->with('error', 'Silahkan pilih minimal satu PO terlebih dahulu!');
        }

        // Rekap barang GUDANG
        $rekapGudang = DB::table('tbl_po_detail')
            ->join('tbl_bahan_scm', 'tbl_po_detail.bahan_id', '=', 'tbl_bahan_scm.id')
            ->leftJoin('tbl_units', 'tbl_po_detail.unit_id', '=', 'tbl_units.id')
            ->leftJoin('tbl_bahan_unit', function ($join) {
                $join->on('tbl_po_detail.bahan_id', '=', 'tbl_bahan_unit.bahan_id')
                    ->on('tbl_po_detail.unit_id', '=', 'tbl_bahan_unit.unit_id');
            })
            ->whereIn('tbl_po_detail.po_id', $poIds)
            ->where('tbl_bahan_scm.sumber_barang', 'GUDANG')
            ->select(
                'tbl_bahan_scm.id as bahan_id',
                'tbl_bahan_scm.nama_bahan',
                'tbl_units.nama_unit as satuan',
                'tbl_bahan_unit.weight as berat_per_unit',
                DB::raw('SUM(tbl_po_detail.jumlah) as total_qty')
            )
            ->groupBy(
                'tbl_bahan_scm.id',
                'tbl_bahan_scm.nama_bahan',
                'tbl_units.nama_unit',
                'tbl_bahan_unit.weight'
            )
            ->get();

        // Rekap barang SUPPLIER
        $rekapSupplier = DB::table('tbl_po_detail')
            ->join('tbl_bahan_scm', 'tbl_po_detail.bahan_id', '=', 'tbl_bahan_scm.id')
            ->leftJoin('tbl_units', 'tbl_po_detail.unit_id', '=', 'tbl_units.id')
            ->leftJoin('tbl_bahan_unit', function ($join) {
                $join->on('tbl_po_detail.bahan_id', '=', 'tbl_bahan_unit.bahan_id')
                    ->on('tbl_po_detail.unit_id', '=', 'tbl_bahan_unit.unit_id');
            })
            ->whereIn('tbl_po_detail.po_id', $poIds)
            ->where('tbl_bahan_scm.sumber_barang', 'SUPPLIER')
            ->select(
                'tbl_bahan_scm.id as bahan_id',
                'tbl_bahan_scm.nama_bahan',
                'tbl_units.nama_unit as satuan',
                'tbl_bahan_unit.weight as berat_per_unit',
                DB::raw('SUM(tbl_po_detail.jumlah) as total_qty')
            )
            ->groupBy(
                'tbl_bahan_scm.id',
                'tbl_bahan_scm.nama_bahan',
                'tbl_units.nama_unit',
                'tbl_bahan_unit.weight'
            )
            ->get();

        $armadaDaftar = DB::table('tbl_armada')->get();
        $driverDaftar = DB::table('tbl_supir')->get();

        // Untuk rekap blade lama (backward compat)
        $rekapBarang = $rekapGudang;

        return view('Purchasing.recapListPO', compact(
            'rekapBarang',
            'rekapGudang',
            'rekapSupplier',
            'poIds',
            'armadaDaftar',
            'driverDaftar',
        ));
    }

    // -------------------------------------------------------
    // finalisasiPengiriman() — buat SJ + SO + GD + GR otomatis
    // -------------------------------------------------------
    public function finalisasiPengiriman(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'po_ids' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            $poIds = $request->po_ids;
            $yearMonth = date('Ym');

            // Ambil info warehouse dari user yang login (khusus dipakai di SECTION GUDANG)
            $warehouseId = Auth::user()->warehouse_id ?? 1;
            $warehouse = DB::table('tbl_warehouse')->where('id', $warehouseId)->first();

            // Ambil branch_id dari tabel warehouse (jika tidak ada, fallback ke $warehouseId atau 1)
            $branchIdGudang = $warehouse ? $warehouse->branch_id : ($warehouseId ?? 1); // <--- DIUBAH

            // ── SECTION GUDANG ────────────────────────────────────────────
            $adaGudang = DB::table('tbl_po_detail')
                ->join('tbl_bahan_scm', 'tbl_po_detail.bahan_id', '=', 'tbl_bahan_scm.id')
                ->whereIn('tbl_po_detail.po_id', $poIds)
                ->where('tbl_bahan_scm.sumber_barang', 'GUDANG')
                ->exists();

            if ($adaGudang) {
                if (!$request->driver_id || !$request->armada_id) {
                    return back()->with('error', 'Driver dan Armada wajib diisi untuk pengiriman dari Gudang.');
                }

                $supir = DB::table('tbl_supir')->where('id', $request->driver_id)->first();
                $armada = DB::table('tbl_armada')->where('id', $request->armada_id)->first();

                $noSjGudang = $this->generateNoSJ("SJ-DC/{$yearMonth}/");
                $sjIdGudang = DB::table('tbl_surat_jalan')->insertGetId([
                    'no_sj' => $noSjGudang,
                    'driver_name' => $supir->nama_supir,
                    'armada_nopol' => $armada->no_pol,
                    'tipe_sj' => 'GUDANG',
                    'status' => 'In Transit',
                    'created_at' => now(),
                ]);

                foreach ($poIds as $poId) {
                    $po = DB::table('tbl_po')
                        ->join('tbl_outlets', 'tbl_po.outlet_id', '=', 'tbl_outlets.id')
                        ->where('tbl_po.id', $poId)
                        ->select('tbl_po.*', 'tbl_outlets.nama_outlet')
                        ->first();

                    $poAdaGudang = DB::table('tbl_po_detail')
                        ->join('tbl_bahan_scm', 'tbl_po_detail.bahan_id', '=', 'tbl_bahan_scm.id')
                        ->where('tbl_po_detail.po_id', $poId)
                        ->where('tbl_bahan_scm.sumber_barang', 'GUDANG')
                        ->exists();

                    if (!$poAdaGudang)
                        continue;

                    $customer = DB::table('tbl_customers')->where('outlet_id', $po->outlet_id)->first();

                    $today = now()->format('Ymd');
                    $lastSo = DB::table('tbl_sales_orders')
                        ->where('so_number', 'like', "SL{$today}%")
                        ->lockForUpdate()->orderByDesc('so_number')
                        ->value('so_number');
                    $soSeq = $lastSo ? (intval(substr($lastSo, -4)) + 1) : 1;
                    $soNumber = 'SL' . $today . str_pad($soSeq, 4, '0', STR_PAD_LEFT);

                    $totalSo = DB::table('tbl_po_detail')
                        ->join('tbl_bahan_scm', 'tbl_po_detail.bahan_id', '=', 'tbl_bahan_scm.id')
                        ->join('tbl_bahan_unit', function ($j) {
                            $j->on('tbl_po_detail.bahan_id', '=', 'tbl_bahan_unit.bahan_id')
                                ->on('tbl_po_detail.unit_id', '=', 'tbl_bahan_unit.unit_id');
                        })
                        ->where('tbl_po_detail.po_id', $poId)
                        ->where('tbl_bahan_scm.sumber_barang', 'GUDANG')
                        ->sum(DB::raw('tbl_po_detail.jumlah * tbl_bahan_unit.base_price'));

                    $soId = DB::table('tbl_sales_orders')->insertGetId([
                        'so_number' => $soNumber,
                        'outlet_po_id' => $poId,
                        'customer_id' => $customer->outlet_id ?? null,
                        'branch_id' => $branchIdGudang, // <--- DIUBAH (Sekarang pakai branch_id asli dari warehouse)
                        'sales_date' => now()->toDateString(),
                        'required_date' => now()->addDays(1)->toDateString(),
                        'status' => 'NEW',
                        'currency' => 'IDR',
                        'rate' => 1,
                        'reference_number' => $po->no_po,
                        'total_amount' => $totalSo,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $itemsGudang = DB::table('tbl_po_detail')
                        ->join('tbl_bahan_scm', 'tbl_po_detail.bahan_id', '=', 'tbl_bahan_scm.id')
                        ->leftJoin('tbl_bahan_unit', function ($j) {
                            $j->on('tbl_po_detail.bahan_id', '=', 'tbl_bahan_unit.bahan_id')
                                ->on('tbl_po_detail.unit_id', '=', 'tbl_bahan_unit.unit_id');
                        })
                        ->where('tbl_po_detail.po_id', $poId)
                        ->where('tbl_bahan_scm.sumber_barang', 'GUDANG')
                        ->select('tbl_po_detail.bahan_id', 'tbl_po_detail.unit_id', 'tbl_po_detail.jumlah', 'tbl_bahan_unit.base_price as price', 'tbl_bahan_unit.unit_id as bu_unit_id')
                        ->get();

                    foreach ($itemsGudang as $item) {
                        DB::table('tbl_sales_order_details')->insert([
                            'sales_order_id' => $soId,
                            'product_id' => $item->bahan_id,
                            'unit_id' => $item->unit_id ?? $item->bu_unit_id,
                            'qty' => $item->jumlah,
                            'price' => $item->price ?? 0,
                            'subtotal' => $item->jumlah * ($item->price ?? 0),
                        ]);
                    }

                    $lastGd = DB::table('tbl_goods_deliveries')
                        ->where('gd_number', 'like', "GD{$today}%")
                        ->lockForUpdate()->orderByDesc('gd_number')
                        ->value('gd_number');
                    $gdSeq = $lastGd ? (intval(substr($lastGd, -4)) + 1) : 1;
                    $gdNumber = 'GD' . $today . str_pad($gdSeq, 4, '0', STR_PAD_LEFT);

                    $gdId = DB::table('tbl_goods_deliveries')->insertGetId([
                        'gd_number' => $gdNumber,
                        'sales_order_id' => $soId,
                        'sj_id' => $sjIdGudang,
                        'outlet_po_id' => $poId,
                        'customer_id' => $customer->outlet_id ?? 0,
                        'warehouse_id' => $warehouseId, // Tetap warehouse_id karena kolomnya emang minta ID Warehouse
                        'delivery_address' => $po->nama_outlet ?? null,
                        'delivery_date' => now()->toDateString(),
                        'estimated_arrival' => now()->addDays(1)->toDateString(),
                        'driver_name' => $supir->nama_supir,
                        'vehicle_plate' => $armada->no_pol,
                        'status' => 'IN_TRANSIT',
                        'total_amount' => $totalSo,
                        'created_by' => Auth::id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $soDetails = DB::table('tbl_sales_order_details')->where('sales_order_id', $soId)->get();
                    foreach ($soDetails as $sod) {
                        DB::table('tbl_goods_delivery_details')->insert([
                            'goods_delivery_id' => $gdId,
                            'sales_order_detail_id' => $sod->id,
                            'product_id' => $sod->product_id,
                            'unit_id' => $sod->unit_id,
                            'qty_ordered' => $sod->qty,
                            'qty_delivered' => $sod->qty,
                            'price' => $sod->price,
                            'subtotal' => $sod->subtotal,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        $stokSebelum = $this->getStokSekarang($warehouseId, $sod->product_id, $sod->unit_id);
                        DB::table('tbl_stock_transactions')->insert([
                            'bahan_id' => $sod->product_id,
                            'unit_id' => $sod->unit_id,
                            'warehouse_id' => $warehouseId,
                            'jumlah' => $sod->qty,
                            'stok_sebelum' => $stokSebelum,
                            'stok_sesudah' => max(0, $stokSebelum - $sod->qty),
                            'tipe' => 'KELUAR',
                            'reference_type' => 'GD',
                            'reference_id' => $gdId,
                            'harga_satuan' => $sod->price,
                            'total_nilai' => $sod->qty * $sod->price,
                            'keterangan' => 'GD In Transit - SJ: ' . $noSjGudang . ' PO: ' . $po->no_po,
                            'created_by' => Auth::id(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    DB::table('tbl_surat_jalan')->where('id', $sjIdGudang)->update(['gd_id' => $gdId]);
                }

                DB::table('tbl_po')->whereIn('id', $poIds)->update([
                    'sj_id' => $sjIdGudang,
                    'status' => 'In Transit',
                    'updated_at' => now(),
                ]);
            }

            // ── SECTION SUPPLIER ──────────────────────────────────────────
            $itemsSupplierGrouped = DB::table('tbl_po_detail')
                ->join('tbl_bahan_scm', 'tbl_po_detail.bahan_id', '=', 'tbl_bahan_scm.id')
                ->whereIn('tbl_po_detail.po_id', $poIds)
                ->where('tbl_bahan_scm.sumber_barang', 'SUPPLIER')
                ->whereNotNull('tbl_po_detail.supplier_id')
                ->get()
                ->groupBy('supplier_id');

            $adaSupplier = $itemsSupplierGrouped->isNotEmpty();

            if ($adaSupplier) {
                $today = now()->format('Ymd');

                // --- AMBIL ID "DC SUPPLIER" OTOMATIS DARI DATABASE ---
                $dcSupplier = DB::table('tbl_warehouse')
                    ->where('nama_warehouse', 'LIKE', '%Supplier%')
                    ->first();

                // Ambil branch_id dari warehouse supplier, jika tidak ketemu fallback ke branch milik user login
                $branchIdSupplier = $dcSupplier ? $dcSupplier->branch_id : $branchIdGudang; // <--- DIUBAH

                // 1. Buat Surat Jalan SUPPLIER Utama
                $noSjSup = $this->generateNoSJ("SJ-SUP/{$yearMonth}/");
                $sjIdSup = DB::table('tbl_surat_jalan')->insertGetId([
                    'no_sj' => $noSjSup,
                    'driver_name' => 'DRIVER SUPPLIER',
                    'armada_nopol' => '-',
                    'tipe_sj' => 'SUPPLIER',
                    'status' => 'In Transit',
                    'created_at' => now(),
                ]);

                // 2. Looping per-Supplier hasil grouping (1 Supplier = 1 PO Baru)
                foreach ($itemsSupplierGrouped as $supplierId => $items) {

                    // Ambil nomor PO asal + nama outlet penyeru pesanan secara join
                    $firstItem = $items->first();
                    $poAsal = DB::table('tbl_po')
                        ->join('tbl_outlets', 'tbl_po.outlet_id', '=', 'tbl_outlets.id')
                        ->where('tbl_po.id', $firstItem->po_id)
                        ->select('tbl_po.*', 'tbl_outlets.nama_outlet')
                        ->first();

                    // Generate PO SCM number baru khusus untuk supplier ini
                    $lastPo = DB::table('tbl_purchase_orders')
                        ->where('po_number', 'like', "PO{$today}%")
                        ->lockForUpdate()->orderByDesc('po_number')
                        ->value('po_number');
                    $poSeq = $lastPo ? (intval(substr($lastPo, -4)) + 1) : 1;
                    $poNumber = 'PO' . $today . str_pad($poSeq, 4, '0', STR_PAD_LEFT);

                    // Hitung subtotal kargo komoditas supplier ini
                    $subtotal = 0;
                    foreach ($items as $item) {
                        $hargaBahan = DB::table('tbl_bahan_unit')
                            ->where('bahan_id', $item->bahan_id)
                            ->where('unit_id', $item->unit_id)
                            ->value('base_price') ?? 0;

                        $subtotal += ($item->jumlah * $hargaBahan);
                    }

                    $vat = $subtotal * 0.11;
                    $total = $subtotal + $vat;

                    // Insert dokumen PO SCM
                    $scmPoId = DB::table('tbl_purchase_orders')->insertGetId([
                        'po_number' => $poNumber,
                        'supplier_id' => $supplierId,
                        'branch_id' => $branchIdSupplier, // <--- DIUBAH (Sekarang pakai branch_id asli dari warehouse supplier)
                        'request_date' => now()->toDateString(),
                        'required_date' => now()->addDays(2)->toDateString(),
                        'status' => 'APPROVED',
                        'reference_number' => $poAsal->no_po,
                        'currency' => 'IDR',
                        'rate' => 1,
                        'subtotal' => $subtotal,
                        'discount' => 0,
                        'vat_amount' => $vat,
                        'total_amount' => $total,
                        'notes' => 'Auto PO SCM berdasarkan PO Outlet : ' . ($poAsal->nama_outlet ?? 'Unknown'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Insert detail barang khusus milik supplier ini ke tbl_purchase_order_details
                    foreach ($items as $item) {
                        $hargaBahan = DB::table('tbl_bahan_unit')
                            ->where('bahan_id', $item->bahan_id)
                            ->where('unit_id', $item->unit_id)
                            ->value('base_price') ?? 0;

                        DB::table('tbl_purchase_order_details')->insert([
                            'purchase_order_id' => $scmPoId,
                            'product_id' => $item->bahan_id,
                            'po_qty' => $item->jumlah,
                            'price' => $hargaBahan,
                            'subtotal' => $item->jumlah * $hargaBahan,
                        ]);
                    }
                }

                // Update status PO outlet asal ke In Transit
                DB::table('tbl_po')->whereIn('id', $poIds)->update([
                    'status' => 'In Transit',
                    'updated_at' => now(),
                ]);
            }

            // Jika murni SUPPLIER (tidak ada item kargo gudang), update link sj_id di PO
            if (!$adaGudang && $adaSupplier) {
                DB::table('tbl_po')->whereIn('id', $poIds)->update([
                    'sj_id' => $sjIdSup,
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            $pesan = [];
            if ($adaGudang)
                $pesan[] = 'SJ Gudang (' . ($noSjGudang ?? '-') . ') + SO + GD otomatis dibuat';
            if ($adaSupplier)
                $pesan[] = 'SJ Supplier (' . ($noSjSup ?? '-') . ') + Sejumlah PO SCM Per Supplier otomatis diterbitkan';

            return redirect()->route('scm.pengiriman.index')->with('success', implode(' | ', $pesan));

        } catch (\Exception $e) {
            DB::rollBack();
            dd([
                'Pesan Error' => $e->getMessage(),
                'File' => $e->getFile(),
                'Baris Ke' => $e->getLine()
            ]);
        }
    }

    // -------------------------------------------------------
    // HELPER: hitung stok sekarang untuk stok_sebelum
    // -------------------------------------------------------
    private function getStokSekarang(int $warehouseId, int $bahanId, int $unitId): float
    {
        $masuk = DB::table('tbl_stock_transactions')
            ->where('warehouse_id', $warehouseId)
            ->where('bahan_id', $bahanId)
            ->where('unit_id', $unitId)
            ->whereIn('tipe', ['MASUK', 'ADJUSTMENT'])
            ->whereNull('deleted_at')
            ->sum('jumlah');

        $keluar = DB::table('tbl_stock_transactions')
            ->where('warehouse_id', $warehouseId)
            ->where('bahan_id', $bahanId)
            ->where('unit_id', $unitId)
            ->whereIn('tipe', ['KELUAR', 'WASTE'])
            ->whereNull('deleted_at')
            ->sum('jumlah');

        return max(0, $masuk - $keluar);
    }

    // Helper function untuk generate nomor agar kode tidak duplikat
    private function generateNoSJ($prefix)
    {
        $lastSJ = DB::table('tbl_surat_jalan')->where('no_sj', 'like', $prefix . "%")->latest('id')->first();
        $number = $lastSJ ? (intval(substr($lastSJ->no_sj, -4)) + 1) : 1;
        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    public function indexSuratJalan()
    {
        // Ambil semua SJ
        $listSJ = DB::table('tbl_surat_jalan as sj')
            ->orderByDesc('sj.created_at')
            ->get();

        $sjIds = $listSJ->pluck('id')->toArray();

        // Jumlah PO per SJ — subquery terpisah agar tidak kena MySQL strict GROUP BY
        $jumlahPo = DB::table('tbl_po')
            ->whereIn('sj_id', $sjIds)
            ->select('sj_id', DB::raw('COUNT(id) as jumlah_po'))
            ->groupBy('sj_id')
            ->pluck('jumlah_po', 'sj_id');

        // Outlet names per SJ
        $outletNames = DB::table('tbl_po as po')
            ->join('tbl_outlets as o', 'po.outlet_id', '=', 'o.id')
            ->whereIn('po.sj_id', $sjIds)
            ->select(
                'po.sj_id',
                DB::raw('GROUP_CONCAT(DISTINCT o.nama_outlet ORDER BY o.nama_outlet SEPARATOR ", ") as outlet_names')
            )
            ->groupBy('po.sj_id')
            ->pluck('outlet_names', 'sj_id');

        // Gabungkan ke collection SJ
        $listSJ = $listSJ->map(function ($sj) use ($jumlahPo, $outletNames) {
            $sj->jumlah_po = $jumlahPo[$sj->id] ?? 0;
            $sj->outlet_names = $outletNames[$sj->id] ?? null;
            return $sj;
        });

        return view('Purchasing.daftarSuratJalan', compact('listSJ'));
    }

    // -------------------------------------------------------
    // printSuratJalan() — cetak SJ per PO (cetakSuratJalan.blade)
    // Route: GET /scm/surat-jalan/{id}/print
    // -------------------------------------------------------
    public function printSuratJalan($sjId)
    {
        $sj = DB::table('tbl_surat_jalan')->where('id', $sjId)->first();

        if (!$sj)
            abort(404, 'Surat Jalan tidak ditemukan.');

        // Ambil semua PO yang linked ke SJ ini
        $pos = DB::table('tbl_po as po')
            ->join('tbl_outlets as o', 'po.outlet_id', '=', 'o.id')
            ->where('po.sj_id', $sjId)
            ->select('po.*', 'o.nama_outlet as outlet_name')
            ->get();

        // Ambil detail barang per PO, filter sesuai tipe SJ
        // (GUDANG → sumber_barang=GUDANG, SUPPLIER → sumber_barang=SUPPLIER)
        $detailsPerPo = [];
        foreach ($pos as $po) {
            $detailsPerPo[$po->id] = DB::table('tbl_po_detail as d')
                ->join('tbl_bahan_scm as b', 'd.bahan_id', '=', 'b.id')
                ->leftJoin('tbl_units as u', 'd.unit_id', '=', 'u.id')
                ->leftJoin('tbl_bahan_unit as bu', function ($j) {
                    $j->on('d.bahan_id', '=', 'bu.bahan_id')
                        ->on('d.unit_id', '=', 'bu.unit_id');
                })
                ->where('d.po_id', $po->id)
                ->where('b.sumber_barang', $sj->tipe_sj)
                ->select(
                    'b.nama_bahan',
                    'u.nama_unit as satuan',
                    'd.jumlah',
                    'bu.base_price as harga_satuan',
                    'bu.weight as berat_per_unit'
                )
                ->get();
        }

        return view('Purchasing.cetakSuratJalan', compact('sj', 'pos', 'detailsPerPo'));
    }

    // -------------------------------------------------------
    // printPackingList() — cetak rekap packing list (cetakRekap.blade)
    // Route: GET /scm/surat-jalan/{id}/packing-list
    // -------------------------------------------------------
    public function printPackingList($sjId)
    {
        $sj = DB::table('tbl_surat_jalan')->where('id', $sjId)->first();

        if (!$sj)
            abort(404, 'Surat Jalan tidak ditemukan.');

        // PO yang linked
        $pos = DB::table('tbl_po as po')
            ->join('tbl_outlets as o', 'po.outlet_id', '=', 'o.id')
            ->where('po.sj_id', $sjId)
            ->select('po.*', 'o.nama_outlet as outlet_name')
            ->get();

        $poIds = $pos->pluck('id')->toArray();

        // Rekap semua barang (sum per bahan, filter sesuai tipe SJ)
        $poDetails = DB::table('tbl_po_detail as d')
            ->join('tbl_bahan_scm as b', 'd.bahan_id', '=', 'b.id')
            ->leftJoin('tbl_units as u', 'd.unit_id', '=', 'u.id')
            ->leftJoin('tbl_bahan_unit as bu', function ($j) {
                $j->on('d.bahan_id', '=', 'bu.bahan_id')
                    ->on('d.unit_id', '=', 'bu.unit_id');
            })
            ->whereIn('d.po_id', $poIds)
            ->where('b.sumber_barang', $sj->tipe_sj)
            ->select(
                'b.nama_bahan',
                'u.nama_unit as satuan',
                'bu.base_price as harga_satuan',
                'bu.weight as berat_per_unit',
                DB::raw('SUM(d.jumlah) as total_qty')
            )
            ->groupBy(
                'b.nama_bahan',
                'u.nama_unit',
                'bu.base_price',
                'bu.weight'
            )
            ->orderBy('b.nama_bahan')
            ->get();

        $totalTonase = $poDetails->sum(
            fn($i) => $i->total_qty * (($i->berat_per_unit ?? 0) / 1000)
        );

        return view('Purchasing.cetakRekap', compact(
            'sj',
            'pos',
            'poDetails',
            'totalTonase'
        ));
    }

    // -------------------------------------------------------
    // cancelSuratJalan() — batalkan SJ (hanya status Packing)
    // Route: POST /scm/surat-jalan/{id}/cancel
    // -------------------------------------------------------
    public function cancelSuratJalan($id)
    {
        $sj = DB::table('tbl_surat_jalan')->where('id', $id)->first();

        if (!$sj) {
            return response()->json(['status' => 'error', 'message' => 'SJ tidak ditemukan.'], 404);
        }

        if (($sj->status ?? 'Packing') !== 'Packing') {
            return response()->json([
                'status' => 'error',
                'message' => "SJ dengan status '{$sj->status}' tidak bisa dibatalkan.",
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Reset status PO yang linked ke SJ ini
            DB::table('tbl_po')
                ->where('sj_id', $id)
                ->update([
                    'status' => 'Approved',
                    'sj_id' => null,
                    'updated_at' => now(),
                ]);

            // Batalkan SJ
            DB::table('tbl_surat_jalan')->where('id', $id)->update([
                'status' => 'Cancelled',
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "SJ {$sj->no_sj} berhasil dibatalkan. PO dikembalikan ke status Approved.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }


    public function printSJ($id)
    {
        // 1. Ambil data Surat Jalan
        $sj = DB::table('tbl_surat_jalan')->where('id', $id)->first();

        if (!$sj) {
            return redirect()->back()->with('error', 'Surat Jalan tidak ditemukan.');
        }

        // 2. Cari PO yang memiliki barang sesuai tipe_sj dan status In Transit
        $pos = DB::table('tbl_po')
            ->join('tbl_outlets', 'tbl_po.outlet_id', '=', 'tbl_outlets.id')
            ->select('tbl_po.*', 'tbl_outlets.nama_outlet')
            ->whereIn('tbl_po.id', function ($query) use ($sj) {
                $query->select('po_id')
                    ->from('tbl_po_detail')
                    ->join('tbl_bahan_scm', 'tbl_po_detail.bahan_id', '=', 'tbl_bahan_scm.id')
                    ->where('tbl_bahan_scm.sumber_barang', $sj->tipe_sj);
            })
            ->where('tbl_po.status', 'In Transit')
            // Opsional: Jika SJ punya kolom armada_id/supir_id, bisa difilter di sini agar lebih spesifik
            ->get()
            ->map(function ($po) use ($sj) {
                // 3. Ambil detail barang UNTUK SETIAP PO
                $po->items = DB::table('tbl_po_detail')
                    ->join('tbl_bahan_scm', 'tbl_po_detail.bahan_id', '=', 'tbl_bahan_scm.id')
                    ->leftJoin('tbl_units', 'tbl_po_detail.unit_id', '=', 'tbl_units.id')
                    // Join ke tbl_bahan_unit untuk ambil harga sesuai unit yang dipilih
                    ->leftJoin('tbl_bahan_unit', function ($join) {
                    $join->on('tbl_po_detail.bahan_id', '=', 'tbl_bahan_unit.bahan_id')
                        ->on('tbl_po_detail.unit_id', '=', 'tbl_bahan_unit.unit_id');
                })
                    ->where('tbl_po_detail.po_id', $po->id)
                    ->where('tbl_bahan_scm.sumber_barang', $sj->tipe_sj)
                    ->select(
                        'tbl_po_detail.*',
                        'tbl_bahan_scm.nama_bahan',
                        'tbl_units.nama_unit as satuan',
                        'tbl_bahan_unit.base_price as harga_satuan' // Pengganti harga_bahan
                    )
                    ->get();
                return $po;
            });

        return view('Purchasing.cetakSuratJalan', compact('sj', 'pos'));
    }

    public function printPackList($id)
    {
        // 1. Ambil data Surat Jalan
        $sj = DB::table('tbl_surat_jalan')->where('id', $id)->first();

        if (!$sj) {
            return redirect()->back()->with('error', 'Surat Jalan tidak ditemukan.');
        }

        // 2. Query Packing List: Gabungkan semua barang yang sama
        $poDetails = DB::table('tbl_po_detail')
            ->join('tbl_bahan_scm', 'tbl_po_detail.bahan_id', '=', 'tbl_bahan_scm.id')
            ->join('tbl_po', 'tbl_po_detail.po_id', '=', 'tbl_po.id') // Join ke PO untuk filter status
            ->leftJoin('tbl_units', 'tbl_po_detail.unit_id', '=', 'tbl_units.id') // Join ke Unit baru
            ->where('tbl_po.status', 'In Transit')
            ->where('tbl_bahan_scm.sumber_barang', $sj->tipe_sj)
            // Tambahkan filter armada/supir jika SJ punya kolom tersebut agar lebih akurat
            ->select(
                'tbl_bahan_scm.nama_bahan',
                'tbl_units.nama_unit as satuan', // Ambil satuan dari tabel units
                DB::raw('SUM(tbl_po_detail.jumlah) as total_qty')
            )
            ->groupBy('tbl_bahan_scm.nama_bahan', 'tbl_units.nama_unit') // Group by unit name
            ->get();

        return view('Purchasing.cetakRekap', compact('sj', 'poDetails'));
    }

    public function daftarSJ()
    {
        // Mengambil semua data produk untuk ditampilkan di tabel
        $listSJ = DB::table('tbl_surat_jalan')->get();

        return view('Purchasing.daftarSuratJalan', compact('listSJ'));
    }

    public function indexReport()
    {
        // Mengambil semua data dari tbl_po tanpa model
        $dataPO = DB::table('tbl_po')
            ->join('tbl_outlets', 'tbl_po.outlet_id', '=', 'tbl_outlets.id')
            ->select('tbl_po.*', 'tbl_outlets.nama_outlet') // Ambil semua dari PO + nama dari tabel outlet
            ->get();

        return view('Purchasing.reportYa', compact('dataPO'));
    }

    public function indexRute()
    {
        // Ambil semua rute beserta jadwalnya
        $rutes = DB::table('tbl_rute')->get();
        return view('Purchasing.setupRute', compact('rutes'));
    }

    public function storeRute(Request $request)
    {
        // 1. Validasi input wajib
        $request->validate([
            'nama_rute' => 'required|string|max:255',
            'hari' => 'required|array|min:1', // Pastikan minimal pilih 1 hari
        ]);

        // 2. Ambil warehouse dari session atau default ke 1
        $warehouseId = session('warehouse_id', 1);

        // 3. Insert ke tbl_rute
        $ruteId = DB::table('tbl_rute')->insertGetId([
            'nama_rute' => $request->nama_rute,
            'warehouse_id' => $warehouseId
        ]);

        // 4. Insert ke tbl_rute_jadwal
        // Kita gunakan $request->hari karena sudah tervalidasi sebagai array
        foreach ($request->hari as $h) {
            DB::table('tbl_rute_jadwal')->insert([
                'rute_id' => $ruteId,
                'hari' => $h
            ]);
        }

        return back()->with('success', 'Rute & Jadwal berhasil disimpan!');
    }

    public function indexRuteMapping()
    {
        // Ambil semua rute
        $rutes = DB::table('tbl_rute')->get();

        // Ambil semua outlet yang sudah punya DC
        $outlets = DB::table('tbl_mapping_dc')
            ->join('tbl_outlets', 'tbl_mapping_dc.outlet_id', '=', 'tbl_outlets.id')
            ->select('tbl_outlets.id', 'tbl_outlets.nama_outlet', 'tbl_mapping_dc.warehouse_id', 'tbl_mapping_dc.rute_id')
            ->get();

        return view('Purchasing.ruteMapping', compact('outlets', 'rutes'));
    }

    public function simpanRuteMapping(Request $request)
    {
        // $request->selected_outlets berisi array [rute_id => [outlet_id1, outlet_id2]]
        if ($request->has('selected_outlets')) {
            foreach ($request->selected_outlets as $ruteId => $outletIds) {
                foreach ($outletIds as $outletId) {
                    DB::table('tbl_mapping_dc')
                        ->where('outlet_id', $outletId)
                        ->update(['rute_id' => $ruteId]);
                }
            }
        }
        return back()->with('success', 'Berhasil update rute massal!');
    }


    // ===== RUTE DAN JADWAL =====
    public function getSaranOutlet($ruteId)
    {
        // 1. Ambil data Warehouse berdasarkan Rute yang dipilih
        $rute = DB::table('tbl_rute')
            ->join('tbl_warehouse', 'tbl_rute.warehouse_id', '=', 'tbl_warehouse.id')
            ->where('tbl_rute.id', $ruteId)
            ->select('tbl_warehouse.lat_dc', 'tbl_warehouse.long_dc')
            ->first();

        // 2. Query outlet dengan jarak terdekat dari Warehouse
        // Tambahkan kondisi where('warehouse_id', ...) supaya hanya outlet di DC tersebut yang muncul
        $saran = DB::table('tbl_outlets')
            ->join('tbl_mapping_dc', 'tbl_outlets.id', '=', 'tbl_mapping_dc.outlet_id')
            ->selectRaw(
                "tbl_outlets.id, tbl_outlets.nama_outlet, 
            (6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(long) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance",
                [$rute->lat_dc, $rute->long_dc, $rute->lat_dc]
            )
            ->where('tbl_mapping_dc.warehouse_id', $rute->warehouse_id) // Filter DC
            ->orderBy('distance', 'asc') // Urut dari yang paling dekat dengan gudang
            ->get();

        return response()->json($saran);
    }

    // ==== COBA QR CODE ====
    public function cetakSJ($id)
    {
        // Ambil data PO berdasarkan ID
        $header = DB::table('tbl_po')
            ->join('tbl_outlets', 'tbl_po.outlet_id', '=', 'tbl_outlets.id')
            ->where('tbl_po.id', $id)
            ->select('tbl_po.*', 'tbl_outlets.nama_outlet')
            ->first();

        if (!$header) {
            return "Data PO Tidak Ditemukan!";
        }

        // Buat Link otomatis yang nanti dimasukkan ke QR Code
        // Contoh: https://webkamu.com/konfirmasi-penerimaan/PO-2024-001
        $urlKonfirmasi = route('outlet.scan', ['no_po' => $header->no_po]);

        // Kirim data ke view
        return view('purchasing.cobaGenerateCode', compact('header', 'urlKonfirmasi'));
    }
    // Fungsi untuk menampilkan halaman konfirmasi saat QR di-scan
    public function halamanKonfirmasiScan($no_po)
    {
        $po = DB::table('tbl_po')
            ->join('tbl_outlets', 'tbl_po.outlet_id', '=', 'tbl_outlets.id')
            ->where('tbl_po.no_po', $no_po)
            ->select('tbl_po.*', 'tbl_outlets.nama_outlet')
            ->first();

        if (!$po) {
            return "Maaf, Data PO tidak valid.";
        }

        return view('purchasing.scan_confirm', compact('po'));
    }

    // Fungsi untuk proses "Terima Semua" otomatis
    public function terimaSemuaBarang(Request $request)
    {
        DB::transaction(function () use ($request) {
            // 1. Ambil semua item yang dipesan di PO ini
            $items = DB::table('tbl_po_detail')
                ->where('po_id', $request->po_id)
                ->get();

            foreach ($items as $item) {
                // 2. Masukkan ke tabel penerimaan (Otomatis qty_datang = jumlah pesanan)
                DB::table('tbl_po_receive_detail')->insert([
                    'po_id' => $item->po_id,
                    'bahan_id' => $item->bahan_id,
                    'qty_po' => $item->jumlah,
                    'qty_datang' => $item->jumlah, // SCM senang, Outlet tidak repot
                    'qty_netto' => $item->jumlah,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // 3. Update status PO Utama menjadi 'All Checked'
            DB::table('tbl_po')->where('id', $request->po_id)->update([
                'status' => 'All Checked',
                'updated_at' => now()
            ]);
        });

        return "<h4>Penerimaan Berhasil!</h4><p>Data stok outlet telah diperbarui otomatis.</p>";
    }

    // === SIMPLE PURCHASE ====

    public function indexSimPurchase(Request $request) // 1. Tambahkan Request $request
    {
        // 2. Mulai query dengan Join agar filter bisa diterapkan langsung
        $query = DB::table('tbl_simple_purchases as sp')
            ->join('tbl_api_credentials as ac', 'sp.credential_id', '=', 'ac.id')
            ->select('sp.*', 'ac.credential_code');

        // 3. Logika Filter Tanggal
        if (!$request->filled('start_date') && !$request->filled('end_date')) {
            // Jika tidak ada filter, tampilkan data hari ini saja (biar enteng)
            $today = date('Y-m-d');
            $query->whereDate('sp.purchase_date', $today); // Pastikan nama kolom benar (sp.purchase_date)
        } else {
            if ($request->filled('start_date')) {
                $query->where('sp.purchase_date', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->where('sp.purchase_date', '<=', $request->end_date);
            }
        }

        // 4. Ambil datanya dari variabel $query yang sudah difilter tadi
        $purchases = $query->orderBy('sp.created_at', 'desc')->get();

        return view('Purchasing.Simples.indexSimPurchase', compact('purchases'));
    }

    public function createSimPurchase()
    {
        return view('Purchasing.Simples.simplePurchase');
    }

    public function destroySimPurchase($id)
    {
        try {
            $purchase = DB::table('simple_purchases')->where('id', $id)->first();

            if (!$purchase) {
                return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan.'], 404);
            }

            // CEK STATUS: Jangan hapus kalau sudah di-sync ke ESB
            if ($purchase->is_pushed == 1) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data tidak boleh dihapus karena sudah sinkron dengan ESB!'
                ], 403);
            }

            DB::table('simple_purchases')->where('id', $id)->delete();

            return response()->json(['status' => 'success', 'message' => 'Data berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // public function syncNow(Request $request)
    // {
    //     $date = $request->date ?? date('Y-m-d');
    //     $syncKey = 'sync_' . uniqid();

    //     // 1. Ambil SEMUA credential
    //     $credentials = DB::table('tbl_api_credentials')->get();

    //     if ($credentials->isEmpty()) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Tidak ada credential'
    //         ], 404);
    //     }

    //     // 2. Hitung total semua branch dari semua credential
    //     $totalBranchCount = DB::table('tbl_api_credential_branches')
    //         ->whereIn('credential_id', $credentials->pluck('id'))
    //         ->count();

    //     // 3. Init cache
    //     $cacheKey = "purchase_sync:$syncKey";

    //     Cache::put($cacheKey, [
    //         'status' => 'processing',
    //         'total_jobs' => $totalBranchCount,
    //         'processed_jobs' => 0,
    //         'inserted' => 0,
    //         'percentage' => 0,
    //         'branches_done' => [],
    //         'errors' => []
    //     ], now()->addHours(2));

    //     // 4. Loop semua credential → dispatch masing-masing
    //     foreach ($credentials as $cred) {
    //         $this->service->dispatchSync($cred->credential_code, $date, $syncKey);
    //     }

    //     return response()->json([
    //         'status' => 'success',
    //         'sync_key' => $syncKey
    //     ]);
    // }

    // public function checkStatus($syncKey)
    // {
    //     $data = Cache::get("purchase_sync:{$syncKey}");

    //     if (!$data) {
    //         return response()->json([
    //             'status' => 'processing',
    //             'message' => 'Menunggu antrean dimulai...',
    //             'branch' => '-'
    //         ]);
    //     }

    //     // Opsi Tambahan: Jika mau otomatis set 'done' saat semua job selesai
    //     // Kamu bisa bandingkan total_jobs dengan processed_jobs jika kamu menambah hitungannya di Job

    //     return response()->json($data);
    // }
    // public function syncSP(Request $request)
    // {
    //     // 1. Validasi input range tanggal
    //     $request->validate([
    //         'date_from' => 'required|date',
    //         'date_to'   => 'required|date',
    //     ]);

    //     // 2. Ambil semua credential_id yang memiliki sesi aktif
    //     $sessions = DB::table('tbl_api_sessions')
    //         ->select('credential_id')
    //         ->whereNotNull('bearer_token')
    //         ->get();

    //     if ($sessions->isEmpty()) {
    //         return response()->json([
    //             'status'  => 'error',
    //             'message' => 'Tidak ada sesi API aktif ditemukan. Pastikan sudah login ke ESB.'
    //         ], 422);
    //     }

    //     // 3. Loop setiap credential dan masukkan ke antrean sync-purchase
    //     foreach ($sessions as $session) {
    //         SyncSimplePurchaseJob::dispatch(
    //             $request->date_from,
    //             $request->date_to,
    //             $session->credential_id
    //         )->onQueue('sync-purchase');
    //     }

    //     return response()->json([
    //         'status'  => 'success',
    //         'message' => 'Sinkronisasi dimulai untuk ' . $sessions->count() . ' kredensial mitra.'
    //     ]);
    // }

    public function syncSP(Request $request)
    {
        // 1. Validasi input range tanggal
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date',
        ]);

        Log::info("=== INITIATING SYNC REQUEST ===", [
            'from' => $request->date_from,
            'to' => $request->date_to
        ]);

        // 2. Ambil semua credential_id yang memiliki sesi aktif
        $sessions = DB::table('tbl_api_sessions')
            ->select('credential_id')
            ->whereNotNull('bearer_token')
            ->get();

        if ($sessions->isEmpty()) {
            Log::warning("Sync canceled: No active API sessions found.");
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak ada sesi API aktif ditemukan. Pastikan sudah login ke ESB.'
            ], 422);
        }

        // 3. Loop setiap credential dan masukkan ke antrean sync-purchase
        foreach ($sessions as $session) {
            Log::info("Dispatching Job for Credential ID: " . $session->credential_id);

            SyncSimplePurchaseJob::dispatch(
                $request->date_from,
                $request->date_to,
                $session->credential_id
            )->onQueue('sync-purchase');
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Sinkronisasi dimulai untuk ' . $sessions->count() . ' kredensial mitra.'
        ]);
    }

    public function syncNow(Request $request)
    {
        // 1. Ambil input dari request
        $dateFrom = $request->date_from ?? $request->date ?? date('Y-m-d');
        $dateTo = $request->date_to ?? $dateFrom; // Bisa null
        $cpNum = $request->cash_purchase_num; // Bisa null

        $syncKey = 'sync_' . uniqid();

        // 2. Ambil SEMUA credential
        $credentials = DB::table('tbl_api_credentials')->get();

        if ($credentials->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak ada credential ditemukan'
            ], 404);
        }

        // 3. Hitung total branch untuk progress bar
        $totalBranchCount = DB::table('tbl_api_credential_branches')
            ->whereIn('credential_id', $credentials->pluck('id'))
            ->count();

        // 4. Init cache untuk tracking progress
        $cacheKey = "purchase_sync:$syncKey";
        Cache::put($cacheKey, [
            'status' => 'processing',
            'total_jobs' => $totalBranchCount,
            'processed_jobs' => 0,
            'inserted' => 0,
            'percentage' => 0,
            'branches_done' => [],
            'errors' => []
        ], now()->addHours(2));

        // 5. Loop credential & jalankan sync dengan parameter baru
        foreach ($credentials as $cred) {
            $this->service->dispatchSync(
                $cred->credential_code,
                $dateFrom,
                $syncKey,
                $dateTo,
                $cpNum
            );
        }

        return response()->json([
            'status' => 'success',
            'sync_key' => $syncKey,
            'message' => $cpNum ? "Sync nomor $cpNum sedang berjalan" : "Sync tanggal $dateFrom s/d $dateTo sedang berjalan"
        ]);
    }

    public function checkStatus($syncKey)
    {
        $data = Cache::get("purchase_sync:{$syncKey}");

        if (!$data) {
            return response()->json([
                'status' => 'processing',
                'message' => 'Menunggu antrean dimulai...',
                'branch' => '-'
            ]);
        }

        return response()->json($data);
    }



    public function storeSimPurchase(Request $request)
    {
        $newSequence = null;
        try {
            DB::transaction(function () use ($request) {
                $today = now()->format('Ymd');
                $prefix = 'CP' . $today;

                // Cari transaksi terakhir hari ini untuk menentukan nomor urut
                $lastTransaction = DB::table('simple_purchases')
                    ->where('sequence', 'like', $prefix . '%')
                    ->orderBy('sequence', 'desc')
                    ->first();

                if (!$lastTransaction) {
                    $newSequence = $prefix . '0001';
                } else {
                    // Ambil 4 digit terakhir, tambah 1, lalu balikin jadi 4 digit (pad)
                    $lastNumber = substr($lastTransaction->sequence, -4);
                    $nextNumber = str_pad((int) $lastNumber + 1, 4, '0', STR_PAD_LEFT);
                    $newSequence = $prefix . $nextNumber;
                }

                // 1. Simpan Header
                $purchaseId = DB::table('simple_purchases')->insertGetId([
                    'sequence' => $newSequence,
                    'supplier_name' => $request->supplier,
                    'purchase_date' => $request->date,
                    'branch' => $request->branch,
                    'location' => $request->location,
                    'payment_method' => $request->payment_method,
                    'cost_total' => $request->cost_total_val,
                    'purchase_total' => $request->purchase_total_val,
                    'grand_total' => $request->purchase_total_val + $request->cost_total_val,
                    'created_at' => now()
                ]);

                // 2. Simpan Barang (Purchase Detail)
                if ($request->items) {
                    foreach ($request->items as $item) {
                        DB::table('simple_purchase_items')->insert([
                            'purchase_id' => $purchaseId,
                            'product_name' => $item['name'],
                            'qty' => $item['qty'],
                            'price' => $item['price'],
                            'total_line' => $item['qty'] * $item['price']
                        ]);
                    }
                }

                // 3. Simpan Biaya (Purchase Cost)
                if ($request->costs) {
                    foreach ($request->costs as $cost) {
                        DB::table('simple_purchase_costs')->insert([
                            'purchase_id' => $purchaseId,
                            'account_name' => $cost['account'],
                            'amount' => $cost['amount']
                        ]);
                    }
                }
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil simpan data dengan nomor: ' . $newSequence
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function showSimPurchase($purchase_num, $credential_id)
    {
        // 1. Ambil data Header
        $purchase = DB::table('tbl_simple_purchases')
            ->where('purchase_num', $purchase_num)
            ->where('credential_id', $credential_id)
            ->first();

        // Proteksi jika data tidak ditemukan
        if (!$purchase) {
            return redirect()->back()->with('error', 'Data Transaksi tidak ditemukan atau Anda tidak memiliki akses.');
        }

        // 2. Ambil data Detail Barang
        // Karena sudah buat index composite, query ini akan sangat cepat
        $items = DB::table('tbl_simple_purchase_details')
            ->where('purchase_num', $purchase_num)
            ->where('credential_id', $credential_id)
            ->get();

        // 3. Ambil data Biaya (jika ada)
        $costs = DB::table('tbl_simple_purchase_costs')
            ->where('purchase_num', $purchase_num)
            ->where('credential_id', $credential_id)
            ->get();

        // Kirim semua variabel ke view menggunakan compact
        return view('Purchasing.Simples.viewSimPurchase', compact('purchase', 'items', 'costs'));
    }

    public function pushPurchase(Request $request)
    {
        // 1. Validasi input
        if (!$request->filled('ids')) {
            return response()->json(['status' => false, 'message' => 'Tidak ada data Purchase yang dipilih'], 400);
        }

        // 2. Pecah string ID menjadi array
        $ids = explode(",", $request->ids);

        // 3. Ambil purchase_num berdasarkan ID
        $purchases = DB::table('tbl_simple_purchases')
            ->whereIn('id', $ids)
            ->select('purchase_num')
            ->get();

        if ($purchases->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'Data Purchase tidak ditemukan'], 404);
        }

        // 4. Dispatch Job satu per satu
        foreach ($purchases as $row) {
            \App\Jobs\PushSimplePurchaseJob::dispatch($row->purchase_num);
        }

        return response()->json([
            'status' => true,
            'message' => count($purchases) . ' data Purchase telah dimasukkan ke antrean push ESB.'
        ]);
    }

    // ==== SIMPLE TRANSFER ====
    public function indexSimTransfer(Request $request)
    {
        // Mengambil data purchase terbaru
        $query = DB::table('tbl_simple_transfer'); // Ganti dengan nama tabel asli

        // Filter Tanggal Awal
        if (!$request->filled('start_date') && !$request->filled('end_date')) {
            $today = date('Y-m-d');
            $query->whereDate('transfer_date', $today);
        } else {
            // Jika ada filter, jalankan filter seperti biasa
            if ($request->filled('start_date')) {
                $query->where('transfer_date', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->where('transfer_date', '<=', $request->end_date);
            }
        }

        // Eksekusi query
        // Gunakan paginate(100) jika ingin load lebih enteng lagi
        $transfers = $query->orderBy('transfer_date', 'desc')->get();

        return view('Purchasing.Simples.indexSimTransfer', compact('transfers'));
    }

    public function createSimTransfer()
    {
        $outlets = DB::table('tbl_outlets')->get();
        $products = DB::table('tbl_bahan_scm')->get();
        // dd($outlets);

        return view('Purchasing.Simples.simpleTransfer', compact('outlets', 'products'));
    }

    public function showTransfer($transfer_num)
    {
        // 1. Ambil data Header sesuai struktur image_50deae.png
        $transfer = DB::table('tbl_simple_transfer') // Ganti dengan nama tabel header
            ->where('transfer_num', $transfer_num)
            ->first();

        if (!$transfer) {
            return redirect()->back()->with('error', "Data $transfer_num tidak ditemukan.");
        }

        // 2. Ambil data Detail sesuai struktur image_50de8c.png
        // Kita join menggunakan transfer_num sesuai kolom di gambar kedua
        $items = DB::table('tbl_simple_transfer_detail') // Ganti dengan nama tabel detail
            ->where('transfer_num', $transfer->transfer_num)
            ->get();

        // 3. Tempelkan ke objek transfer
        $transfer->items = $items;

        return view('Purchasing.Simples.viewSimTransfer', compact('transfer'));
    }

    public function editSimTransfer($transfer_num)
    {
        $transfer = DB::table('tbl_simple_transfer')
            ->where('transfer_num', $transfer_num)
            ->first();

        if (!$transfer) {
            return redirect()->back()->with('error', 'Data tidak ditemukan di database lokal.');
        }

        // Ambil detailnya
        $transfer->items = DB::table('tbl_simple_transfer_detail')
            ->where('transfer_num', $transfer_num)
            ->get();

        // dd($transfer->items);

        // 3. Ambil data produk untuk dropdown (Filter: is_transfer_unit = 1)
        $products = DB::table('tbl_bahan_unit as bu')
            ->join('tbl_bahan_scm as p', 'bu.bahan_id', '=', 'p.id')
            ->join('tbl_units as u', 'bu.unit_id', '=', 'u.id')
            ->select(
                'bu.product_detail_id', // Ini ID yang dikirim ke database transaksi (20, 21, 22)
                'p.nama_bahan',          // AYAM BESAR
                'u.nama_unit'            // PCS / KG
            )
            ->where('bu.is_transfer_unit', 1)
            ->orderBy('p.nama_bahan', 'asc')
            ->get();

        // dd($products);

        // 3. Tampilkan ke halaman edit
        return view('Purchasing.Simples.editSimTransfer', compact('transfer', 'products'));
    }

    public function updateSimTransfer(Request $request, $transfer_num)
    {
        DB::beginTransaction();
        try {
            // 1. Update Header
            DB::table('tbl_simple_transfer')
                ->where('transfer_num', $transfer_num)
                ->update([
                    'transfer_date' => $request->transfer_date,
                    'from_outlet' => $request->from_outlet,
                    'to_outlet' => $request->to_outlet,
                    'remark' => $request->remark,
                    'updated_at' => now(),
                ]);

            // 2. Update Detail (Cara termudah: hapus detail lama, masukkan yang baru)
            DB::table('tbl_simple_transfer_detail')->where('transfer_num', $transfer_num)->delete();

            foreach ($request->items as $item) {
                DB::table('tbl_simple_transfer_detail')->insert([
                    'transfer_num' => $transfer_num,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return redirect()->route('indexSimTransfer')->with('success', 'Data berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    // public function updateSimTransfer(Request $request, $id)
    // {
    //     // 1. Validasi Input Dasar
    //     $request->validate([
    //         'transfer_date' => 'required|date',
    //         'items' => 'required|array',
    //         'items.*.qty' => 'required|numeric|min:0',
    //     ]);

    //     try {
    //         // 2. Ambil Token OKNHO yang valid
    //         $token = DB::table('tbl_api_sessions as s')
    //             ->join('tbl_api_credentials as c', 's.api_credential_id', '=', 'c.id')
    //             ->where('c.code', 'OKNHO')
    //             ->orderBy('s.id', 'desc')
    //             ->value('s.bearer_token');

    //         if (!$token) {
    //             return redirect()->back()->with('error', 'Token API tidak ditemukan. Silakan sinkronisasi ulang.');
    //         }

    //         // 3. Susun Payload untuk ESB sesuai format JSON yang Anda berikan
    //         $payload = [
    //             "simpleTransferDate"    => $request->transfer_date,
    //             "originLocationID"      => (int)$request->origin_id,
    //             "destinationLocationID" => (int)$request->destination_id,
    //             "costCenterID"          => 1, // Sesuai contoh JSON Anda
    //             "projectID"             => null,
    //             "additionalInfo"        => $request->notes ?? '',
    //             "simpleTransferDetails" => collect($request->items)->map(function ($item) {
    //                 return [
    //                     "productDetailID" => (int)$item['productDetailID'],
    //                     "qty"             => (float)$item['qty']
    //                 ];
    //             })->values()->all(),
    //             "assetIDs"              => [] // Kosongkan jika tidak ada asset
    //         ];

    //         // 4. Kirim ke API ESB (Gunakan PUT untuk Update)
    //         $response = Http::withToken(trim($token))
    //             ->withoutVerifying()
    //             ->withHeaders(['company-code' => 'OKNHO'])
    //             ->put("https://services.esb.co.id/core/inventory/simple-transfer/{$request->transfer_num}", $payload);

    //         if ($response->successful()) {
    //             // 5. JIKA API SUKSES, UPDATE DATABASE LOKAL
    //             DB::beginTransaction();
    //             try {
    //                 // Update Header
    //                 DB::table('tbl_simple_transfer')->where('id', $id)->update([
    //                     'transfer_date'   => $request->transfer_date,
    //                     'additional_info' => $request->notes,
    //                     'updated_at'      => now(),
    //                 ]);

    //                 // Update Detail (Hapus lama, masukkan yang baru dari request)
    //                 DB::table('tbl_simple_transfer_detail')->where('transfer_id', $id)->delete();

    //                 foreach ($request->items as $item) {
    //                     DB::table('tbl_simple_transfer_detail')->insert([
    //                         'transfer_id'       => $id,
    //                         'product_detail_id' => $item['productDetailID'],
    //                         'qty'               => $item['qty'],
    //                         'created_at'        => now(),
    //                         'updated_at'        => now(),
    //                     ]);
    //                 }

    //                 DB::commit();
    //                 return redirect()->route('simple-transfer.index')->with('success', 'Transfer berhasil diperbarui di ESB dan Lokal.');
    //             } catch (\Exception $e) {
    //                 DB::rollBack();
    //                 Log::error("Gagal update DB Lokal setelah ESB Sukses: " . $e->getMessage());
    //                 return redirect()->back()->with('error', 'ESB Berhasil, tapi database lokal gagal update.');
    //             }
    //         }

    //         // Jika API ESB mengembalikan error (misal stok tidak cukup)
    //         $errorMessage = $response->json('message') ?? 'Terjadi kesalahan pada API ESB.';
    //         return redirect()->back()->with('error', 'Gagal update ESB: ' . $errorMessage);
    //     } catch (\Exception $e) {
    //         Log::error("SimpleTransferUpdate Error: " . $e->getMessage());
    //         return redirect()->back()->with('error', 'Kesalahan Sistem: ' . $e->getMessage());
    //     }
    // }

    public function destroySimTransfer($transfer_num)
    {
        try {
            // 1. Ambil Token
            $token = DB::table('tbl_api_sessions as s')
                ->join('tbl_api_credentials as c', 's.api_credential_id', '=', 'c.id')
                ->where('c.code', 'OKNHO')
                ->value('s.bearer_token');

            // 2. Hit API DELETE ESB
            // Cek dokumentasi ESB apakah endpoint delete-nya sama
            $response = Http::withToken(trim($token))
                ->withoutVerifying()
                ->withHeaders(['company-code' => 'OKNHO'])
                ->delete("https://services.esb.co.id/core/inventory/simple-transfer/{$transfer_num}");

            if ($response->successful()) {
                DB::beginTransaction();
                try {
                    // Cari header untuk hapus detailnya dulu
                    $header = DB::table('tbl_simple_transfer')->where('transfer_num', $transfer_num)->first();

                    if ($header) {
                        // Hapus Detail & Header di lokal
                        DB::table('tbl_simple_transfer_detail')->where('transfer_id', $header->id)->delete();
                        DB::table('tbl_simple_transfer')->where('id', $header->id)->delete();
                    }

                    DB::commit();
                    return redirect()->route('simple-transfer.index')->with('success', 'Dokumen Transfer berhasil dihapus.');
                } catch (\Exception $e) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Gagal hapus data lokal: ' . $e->getMessage());
                }
            }

            return redirect()->back()->with('error', 'ESB menolak penghapusan: ' . $response->json('message'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Sistem Error: ' . $e->getMessage());
        }
    }

    public function startSyncSTF(Request $request)
    {
        $start = $request->input('start_date');
        $end = $request->input('end_date');
        $limit = 10;

        // 🔒 Lock: Biar user nggak spam klik
        if (cache()->has('stf_running')) {
            return response()->json(['message' => 'Sync sedang berjalan, mohon tunggu...'], 429);
        }

        // Inisialisasi awal
        cache()->put('stf_running', true, 1200);
        cache()->put('stf_progress', 0, 1200);
        cache()->put('stf_total', 0, 1200);

        // 🚀 Dispatch Job Pertama
        \App\Jobs\SyncSimpleTransferJob::dispatch(1, $limit, $start, $end)
            ->onQueue('transfer-sync');

        return response()->json([
            'message' => "Proses sinkronisasi dimulai...",
        ]);
    }

    public function pushTransfer(Request $request)
    {
        // 1. Validasi input
        if (!$request->filled('ids')) {
            return response()->json(['status' => false, 'message' => 'Tidak ada data yang dipilih'], 400);
        }

        // 2. Pecah string ID menjadi array [1, 2, 3]
        $ids = explode(",", $request->ids);

        // 3. Ambil transfer_num berdasarkan ID yang dicentang
        // Kita hanya mengambil yang belum dipush atau yang gagal sebelumnya (opsional)
        $transfers = DB::table('tbl_simple_transfer')
            ->whereIn('id', $ids)
            ->select('transfer_num')
            ->get();

        if ($transfers->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        // 4. Dispatch ke Queue (Job) satu per satu
        foreach ($transfers as $row) {
            // Memanggil Job yang kamu buat
            PushSimpleTransferJob::dispatch($row->transfer_num);
        }

        return response()->json([
            'status' => true,
            'message' => count($transfers) . ' transaksi telah dimasukkan ke antrean push ESB.'
        ]);
    }

    public function getProgress()
    {
        return response()->json([
            'progress' => (int) cache()->get('stf_progress', 0),
            'total' => (int) cache()->get('stf_total', 0),
            'running' => cache()->has('stf_running')
        ]);
    }

    // public function pushSingle($id)
    // {
    //     // Lempar tugas ke antrean (Queue)
    //     PushToEsb::dispatch($id);

    //     // Langsung kembalikan user ke halaman sebelumnya
    //     return back()->with('success', 'Data sedang diproses di latar belakang untuk di-push ke ESB.');
    // }

    public function pushSingle($id, \App\Services\SimpleTransferService $service)
    {
        // Kita panggil langsung tanpa Job
        $result = $service->pushToApi($id);

        if ($result) {
            return "BERHASIL!";
        } else {
            return "GAGAL. Coba cek file storage/logs/laravel.log sekarang, pasti ada pesan error barunya di sana.";
        }
    }

    public function storeSimTransfer(Request $request)
    {
        $newSequence = null;
        try {
            DB::transaction(function () use ($request, &$newSequence) {
                $today = now()->format('Ymd');
                $prefix = 'ST' . $today;

                // 1. Generate Nomor Urut Otomatis
                $lastTransaction = DB::table('tbl_simple_transfer')
                    ->where('transfer_num', 'like', $prefix . '%')
                    ->orderBy('transfer_num', 'desc')
                    ->first();

                if (!$lastTransaction) {
                    $newSequence = $prefix . '0001';
                } else {
                    $lastNumber = substr($lastTransaction->transfer_num, -4);
                    $nextNumber = str_pad((int) $lastNumber + 1, 4, '0', STR_PAD_LEFT);
                    $newSequence = $prefix . $nextNumber;
                }

                // 2. Cari Nama Outlet Asal (Origin)
                $originName = null;
                if ($request->origin_location && $request->origin_location !== 'HO') {
                    $originName = DB::table('tbl_outlets') // Sesuaikan nama tabel outlet Anda
                        ->where('id', $request->origin_location)
                        ->value('nama_outlet');
                } else {
                    $originName = 'Tidak Teridentifikasi';
                }

                // 3. Cari Nama Outlet Tujuan (Destination)
                $destinationName = null;
                if ($request->destination_id && $request->destination_id !== 'HO') {
                    $destinationName = DB::table('tbl_outlets') // Sesuaikan nama tabel outlet Anda
                        ->where('id', $request->destination_id)
                        ->value('nama_outlet');
                } else {
                    $destinationName = 'Tidak Teridentifikasi';
                }

                // 4. Simpan Header (tbl_simple_transfer)
                DB::table('tbl_simple_transfer')->insert([
                    'transfer_num' => $newSequence,
                    'transfer_date' => $request->date,
                    'origin_location_name' => $originName,
                    'destination_location_name' => $destinationName, // Menyimpan teks nama tujuan
                    'status_name' => 'Authorized',
                    'additional_info' => $request->notes,
                ]);

                // 5. Simpan Detail Barang (tbl_simple_transfer_detail)
                if ($request->items && is_array($request->items)) {
                    foreach ($request->items as $item) {
                        DB::table('tbl_simple_transfer_detail')->insert([
                            'transfer_num' => $newSequence,
                            'product_detail_id' => $item['product_id'],
                            'product_name' => $item['name'],
                            'uom_name' => $item['uom'],
                            'qty' => $item['qty'],
                            'is_asset' => $item['is_asset'] ?? 0,
                        ]);
                    }
                }
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil simpan transfer dengan nomor: ' . $newSequence
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal simpan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function indexStagSTF()
    {
        $stagingData = DB::table('stf_header_staging')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('Purchasing.Simples.stagingSimTransfer', compact('stagingData'));
    }

    //SIMPLE SALES
    public function indexSimSales(Request $request)
    {

        $query = DB::table('tbl_simple_sales'); // Ganti dengan nama tabel asli

        // Filter Tanggal Awal
        if (!$request->filled('start_date') && !$request->filled('end_date')) {
            $today = date('Y-m-d');
            $query->whereDate('sales_date', $today);
        } else {
            // Jika ada filter, jalankan filter seperti biasa
            if ($request->filled('start_date')) {
                $query->where('sales_date', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->where('sales_date', '<=', $request->end_date);
            }
        }
        // Mengambil data purchase terbaru
        $sales = $query->orderBy('sales_date', 'desc')->get();

        return view('Purchasing.Simples.indexSimSales', compact('sales'));
    }

    public function createSimSales()
    {
        return view('Purchasing.Simples.simpleSales');
    }

    public function syncSS(Request $request)
    {
        $start = $request->date_from; // Ambil dari input date web
        $end = $request->date_to;

        \Log::info("Memicu sync untuk tanggal: " . $request->date_from);

        SyncSimpleSalesJob::dispatch(1, $start, $end)->onQueue('sim-sales-sync');

        return response()->json([
            'status' => 'success',
            'message' => 'Sinkronisasi data Sales telah dimulai.'
        ]);
    }

    // public function syncSS(Request $request)
    // {
    //     $start = \Carbon\Carbon::parse($request->date_from);
    //     $end = \Carbon\Carbon::parse($request->date_to);

    //     // Hitung berapa hari yang dipilih
    //     $diffInDays = $start->diffInDays($end);

    //     // Proteksi jika user pilih rentang terlalu jauh (misal 1 tahun)
    //     if ($diffInDays > 31) {
    //         return response()->json(['status' => 'error', 'message' => 'Maksimal rentang adalah 31 hari.']);
    //     }

    //     Log::info("User memicu sync dari {$request->date_from} sampai {$request->date_to}");

    //     // Loop per hari agar Job terbagi rata
    //     while ($start->lte($end)) {
    //         $currentDate = $start->format('Y-m-d');

    //         Log::info("Menambahkan antrean Job untuk tanggal: $currentDate");

    //         // Kirim Job KHUSUS untuk tanggal tersebut saja
    //         SyncSimpleSalesJob::dispatch(1, $currentDate, $currentDate)
    //             ->onQueue('sim-sales-sync');

    //         $start->addDay();
    //     }

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Sinkronisasi untuk ' . ($diffInDays + 1) . ' hari telah dijadwalkan.'
    //     ]);
    // }

    public function showSimSales($sales_num)
    {
        // Mengambil data header berdasarkan ID
        $header = DB::table('tbl_simple_sales')
            ->where('sales_num', $sales_num)
            ->first();

        // Jika data tidak ditemukan, kembalikan dengan pesan error
        if (!$header) {
            return redirect()->back()->with('error', 'Data transaksi tidak ditemukan.');
        }

        // Mengambil data detail item berdasarkan sales_num dari header
        $details = DB::table('tbl_simple_sales_detail')
            ->where('sales_num', $header->sales_num)
            ->get();

        // Mengirim data ke view detail
        return view('Purchasing.Simples.viewSimSales', compact('header', 'details'));
    }

    public function pushSales(Request $request)
    {
        // 1. Validasi input
        if (!$request->filled('ids')) {
            return response()->json(['status' => false, 'message' => 'Tidak ada data Sales yang dipilih'], 400);
        }

        // 2. Pecah string ID menjadi array
        $ids = explode(",", $request->ids);

        // 3. Ambil sales_num berdasarkan ID
        $sales = DB::table('tbl_simple_sales')
            ->whereIn('id', $ids)
            ->select('sales_num')
            ->get();

        if ($sales->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'Data Sales tidak ditemukan'], 404);
        }

        // 4. Dispatch Job satu per satu
        foreach ($sales as $row) {
            \App\Jobs\PushSimpleSalesJob::dispatch($row->sales_num);
        }

        return response()->json([
            'status' => true,
            'message' => count($sales) . ' data Sales telah dimasukkan ke antrean push ESB.'
        ]);
    }

    // public function editSimSales($sales_num)
    // {
    //     // 1. Ambil data Header Sales berdasarkan sales_num
    //     $header = DB::table('tr_simple_sales as ts')
    //         ->join('ms_customers as mc', 'ts.customer_id', '=', mc.id')
    //         ->join('ms_branches as mb', 'ts.branch_id', '=', mb.id')
    //         ->select('ts.*', 'mc.customer_name', 'mb.branch_name')
    //         ->where('ts.sales_num', $sales_num) // Pakai sales_num
    //         ->first();

    //     if (!$header) {
    //         return redirect()->route('simple-sales.index')->with('error', 'Data penjualan tidak ditemukan.');
    //     }

    //     // 2. Ambil data detail item (Gunakan sales_num sebagai pengikat relasi)
    //     $details = DB::table('tr_simple_sales_detail')
    //         ->where('sales_num', $sales_num) // Pakai sales_num
    //         ->select('*', DB::raw('(qty * price) as total_line'))
    //         ->get();

    //     // 3. Ambil data master produk untuk isi dropdown select
    //     $products = DB::table('ms_products')
    //         ->where('is_active', 1)
    //         ->select('id', 'nama_bahan')
    //         ->get();

    //     return view('Purchasing.Simples.editSimpleSales', compact('header', 'details', 'products'));
    // }

    public function editSimSales($sales_num)
    {
        // 1. Ambil header simple sales
        $header = DB::table('tbl_simple_sales')
            ->where('sales_num', $sales_num)
            ->first();

        if (!$header) {
            return redirect()->route('simple-sales.index')
                ->with('error', 'Data penjualan tidak ditemukan.');
        }

        // Proteksi: jangan bisa edit kalau sudah di-push ke ESB
        if ($header->is_pushed ?? false) {
            return redirect()->route('simple-sales.index')
                ->with('error', 'Data yang sudah di-push ke ESB tidak bisa diedit.');
        }

        // 2. Ambil detail item
        $details = DB::table('tbl_simple_sales_detail')
            ->where('sales_num', $sales_num)
            ->select('*', DB::raw('(qty * price) as total_line'))
            ->get();

        // 3. Ambil master produk untuk dropdown
        $products = DB::table('tbl_bahan_scm')
            ->select('id', 'nama_bahan')
            ->orderBy('nama_bahan')
            ->get();

        return view('Purchasing.Simples.editSimSales', compact('header', 'details', 'products'));
    }

    // ── UPDATE SIMPLE SALES ──────────────────────────────────────
    public function updateSimSales(Request $request, $sales_num)
    {
        $request->validate([
            'sales_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($request, $sales_num) {

                // 1. Hitung grand total baru
                $grandTotal = collect($request->items)->sum(fn($i) => $i['qty'] * $i['price']);

                // 2. Update header
                DB::table('tbl_simple_sales')
                    ->where('sales_num', $sales_num)
                    ->update([
                        'sales_date' => $request->sales_date,
                        'notes' => $request->notes ?? null,
                        'total_amount' => $grandTotal,
                        'updated_at' => now(),
                    ]);

                // 3. Hapus detail lama, insert ulang yang baru
                DB::table('tbl_simple_sales_detail')
                    ->where('sales_num', $sales_num)
                    ->delete();

                foreach ($request->items as $item) {
                    // Ambil nama produk dari master
                    $bahan = DB::table('tbl_bahan_scm')
                        ->where('id', $item['product_id'])
                        ->select('nama_bahan')
                        ->first();

                    // Ambil unit dari tbl_bahan_unit (is_sales_unit)
                    $unitMapping = DB::table('tbl_bahan_unit')
                        ->join('tbl_units', 'tbl_bahan_unit.unit_id', '=', 'tbl_units.id')
                        ->where('tbl_bahan_unit.bahan_id', $item['product_id'])
                        ->where('tbl_bahan_unit.is_sales_unit', 1)
                        ->select('tbl_bahan_unit.product_detail_id', 'tbl_units.nama_unit')
                        ->first();

                    DB::table('tbl_simple_sales_detail')->insert([
                        'sales_num' => $sales_num,
                        'product_id' => $item['product_id'],
                        'product_detail_id' => $unitMapping->product_detail_id ?? null,
                        'product_name' => $bahan->nama_bahan ?? 'Unknown',
                        'uom_name' => $unitMapping->nama_unit ?? '-',
                        'qty' => $item['qty'],
                        'price' => $item['price'],
                        'total_line' => $item['qty'] * $item['price'],
                        'hpp' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });

            return redirect()->route('simple-sales.index')
                ->with('success', "Simple Sales {$sales_num} berhasil diperbarui.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    // ── EDIT SIMPLE PURCHASE ─────────────────────────────────────
    public function editSimPurchase($purchase_num)
    {
        // 1. Ambil header
        $header = DB::table('tbl_simple_purchases as sp')
            ->leftJoin('tbl_api_credentials as ac', 'sp.credential_id', '=', 'ac.id')
            ->where('sp.purchase_num', $purchase_num)
            ->select('sp.*', 'ac.credential_code')
            ->first();

        if (!$header) {
            return redirect()->route('simple-purchase.index')
                ->with('error', 'Data pembelian tidak ditemukan.');
        }

        // Proteksi: jangan bisa edit kalau sudah di-push ke ESB
        if ($header->is_pushed ?? false) {
            return redirect()->route('simple-purchase.index')
                ->with('error', 'Data yang sudah di-push ke ESB tidak bisa diedit.');
        }

        // 2. Ambil detail item
        $details = DB::table('tbl_simple_purchase_details')
            ->where('purchase_num', $purchase_num)
            ->select('*', DB::raw('(qty * price) as total_line'))
            ->get();

        // 3. Ambil biaya (costs)
        $costs = DB::table('tbl_simple_purchase_costs')
            ->where('purchase_num', $purchase_num)
            ->get();

        // 4. Ambil master produk untuk dropdown
        $products = DB::table('tbl_bahan_scm')
            ->select('id', 'nama_bahan')
            ->orderBy('nama_bahan')
            ->get();

        // 5. Ambil master supplier untuk dropdown
        $suppliers = DB::table('tbl_suppliers')
            ->select('id', 'supplier_name')
            ->orderBy('supplier_name')
            ->get();

        return view('Purchasing.Simples.editSimPurchase', compact(
            'header',
            'details',
            'costs',
            'products',
            'suppliers'
        ));
    }

    // ── UPDATE SIMPLE PURCHASE ───────────────────────────────────
    public function updateSimPurchase(Request $request, $purchase_num)
    {
        $request->validate([
            'purchase_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($request, $purchase_num) {

                // Hitung totals
                $purchaseTotal = collect($request->items)->sum(fn($i) => $i['qty'] * $i['price']);
                $costTotal = collect($request->costs ?? [])->sum(fn($c) => $c['amount'] ?? 0);
                $grandTotal = $purchaseTotal + $costTotal;

                // 1. Update header
                DB::table('tbl_simple_purchases')
                    ->where('purchase_num', $purchase_num)
                    ->update([
                        'purchase_date' => $request->purchase_date,
                        'supplier_name' => $request->supplier_name ?? null,
                        'notes' => $request->notes ?? null,
                        'total_amount' => $grandTotal,
                        'updated_at' => now(),
                    ]);

                // 2. Hapus detail lama, insert ulang
                DB::table('tbl_simple_purchase_details')
                    ->where('purchase_num', $purchase_num)
                    ->delete();

                $credentialId = DB::table('tbl_simple_purchases')
                    ->where('purchase_num', $purchase_num)
                    ->value('credential_id');

                foreach ($request->items as $item) {
                    $bahan = DB::table('tbl_bahan_scm')
                        ->where('id', $item['product_id'])
                        ->select('nama_bahan')
                        ->first();

                    $unitMapping = DB::table('tbl_bahan_unit')
                        ->join('tbl_units', 'tbl_bahan_unit.unit_id', '=', 'tbl_units.id')
                        ->where('tbl_bahan_unit.bahan_id', $item['product_id'])
                        ->where('tbl_bahan_unit.is_purchase_unit', 1)
                        ->select('tbl_bahan_unit.product_detail_id', 'tbl_units.nama_unit')
                        ->first();

                    DB::table('tbl_simple_purchase_details')->insert([
                        'purchase_num' => $purchase_num,
                        'credential_id' => $credentialId,
                        'product_id' => $item['product_id'],
                        'product_detail_id' => $unitMapping->product_detail_id ?? null,
                        'product_name' => $bahan->nama_bahan ?? 'Unknown',
                        'uom_name' => $unitMapping->nama_unit ?? '-',
                        'qty' => $item['qty'],
                        'price' => $item['price'],
                        'total_line' => $item['qty'] * $item['price'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // 3. Hapus biaya lama, insert ulang
                DB::table('tbl_simple_purchase_costs')
                    ->where('purchase_num', $purchase_num)
                    ->delete();

                foreach ($request->costs ?? [] as $cost) {
                    if (empty($cost['account_name']) || empty($cost['amount']))
                        continue;
                    DB::table('tbl_simple_purchase_costs')->insert([
                        'purchase_num' => $purchase_num,
                        'credential_id' => $credentialId,
                        'account_name' => $cost['account_name'],
                        'amount' => $cost['amount'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });

            return redirect()->route('simple-purchase.index')
                ->with('success', "Simple Purchase {$purchase_num} berhasil diperbarui.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }
}