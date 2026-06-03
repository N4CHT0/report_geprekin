<?php

namespace App\Http\Controllers;

use App\Imports\OutletsImport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Illuminate\Support\Str;
use App\Jobs\SyncMissingOutletsFromEsbJob;
use App\Services\EsbBranchService;
use App\Jobs\SyncEsbBranchesAndOutletsAllJob;
use Illuminate\Support\Facades\Cache;
use App\Jobs\DispatchSyncEsbAllCredentialsJob;
use App\Jobs\SyncSalesByBranchJob;
use App\Jobs\DispatchSyncSalesAllBranchesJob;
use Illuminate\Support\Facades\Redis;
use App\Services\EsbSalesService;
use Illuminate\Support\Facades\Log;
use App\Jobs\SyncSalesSelectedOutletsJob;
use App\Jobs\GenerateQcrExportJob;
use App\Jobs\SyncSalesAllCredentialsSequentialJob;

class MasterInvestorController extends Controller
{
    public function investor()
    {
        $data = DB::table('tbl_mitra')
            ->leftJoin('tbl_investor', 'tbl_mitra.investor_id', '=', 'tbl_investor.id')
            ->select(
                'tbl_mitra.id',
                'tbl_mitra.kode_mitra',
                'tbl_mitra.nama_mitra',
                'tbl_mitra.investor_id',
                'tbl_investor.nama_investor'
            )
            ->get();

        $investors = DB::table('tbl_investor')->select('id', 'nama_investor')->get();

        return view('Investor.Master.investor', compact('data', 'investors'));
    }

    public function storeMitra(Request $request)
    {
        // dd('Store Mitra terpanggil!', $request->all());

        $request->validate([
            'investor_id' => 'required|exists:tbl_investor,id',
            'kode_mitra' => 'required|string|max:50|unique:tbl_mitra,kode_mitra',
            'nama_mitra' => 'required|string|max:255',
        ]);

        DB::table('tbl_mitra')->insert([
            'investor_id' => $request->investor_id,
            'kode_mitra' => $request->kode_mitra,
            'nama_mitra' => $request->nama_mitra,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Mitra berhasil ditambahkan');
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'kode_mitra' => 'required|string|max:50',
            'nama_mitra' => 'required|string|max:255',
            'investor_id' => 'nullable|integer',
        ]);

        DB::table('tbl_mitra')->where('id', $request->id)->update([
            'kode_mitra' => $request->kode_mitra,
            'nama_mitra' => $request->nama_mitra,
            'investor_id' => $request->investor_id,
            'updated_at' => now(),
        ]);

        return redirect()->route('investor.master')->with('success', 'Data berhasil diperbarui');
    }

    public function destroy($id)
    {
        DB::table('tbl_mitra')->where('id', $id)->delete();

        return redirect()->route('investor.master')->with('success', 'Data berhasil dihapus');
    }

    public function outlet(Request $request)
    {
        $data = DB::table('tbl_outlets')
            ->leftJoin('tbl_mitra', 'tbl_outlets.mitra_id', '=', 'tbl_mitra.id')
            ->select(
                'tbl_outlets.id',
                'tbl_outlets.kode_outlet',
                'tbl_outlets.nama_outlet',
                'tbl_outlets.kota',
                'tbl_outlets.alamat',
                'tbl_outlets.status',
                'tbl_outlets.mitra_id',
                'tbl_outlets.area_id',
                'tbl_mitra.nama_mitra'
            )
            ->orderByRaw("FIELD(tbl_outlets.status, 'existing', 'go', 'tutup')")
            ->orderBy('tbl_outlets.nama_outlet', 'asc')
            ->get();

        $outlets = DB::table('tbl_outlets')
            ->select('id', 'nama_outlet')
            ->whereNotNull('credential_id')
            ->whereNotNull('esb_branch_id')
            ->orderBy('nama_outlet', 'asc')
            ->get();

        $mitra = DB::table('tbl_mitra')
            ->select('id', 'nama_mitra')
            ->orderBy('nama_mitra')
            ->get();

        $areas = DB::table('tbl_outlets')
            ->select('area_id')
            ->whereNotNull('area_id')
            ->groupBy('area_id')
            ->orderBy('area_id')
            ->pluck('area_id')
            ->toArray();

        return view('Investor.Master.outlet', compact('data', 'mitra', 'areas', 'outlets'));
    }

    public function storeOutlet(Request $request)
    {
        $request->validate([
            'kode_outlet' => 'required|string|max:255',
            'area_id'     => 'nullable|integer',
            'mitra_id'    => 'nullable|exists:tbl_mitra,id',
            'nama_outlet' => 'required|string|max:255',
            'kota'        => 'nullable|string|max:100',   // ✅
            'alamat'      => 'nullable|string',            // ✅
            'status'      => 'required|in:existing,go,tutup',
        ]);
    
        try {
            $dup = DB::table('tbl_outlets')
                ->where('kode_outlet', $request->kode_outlet)
                ->exists();
    
            if ($dup) {
                return back()->with('duplicate', 'Kode outlet sudah ada.');
            }
    
            DB::table('tbl_outlets')->insert([
                'kode_outlet' => $request->kode_outlet,
                'area_id'     => $request->area_id ?: null,
                'mitra_id'    => $request->mitra_id ?: null,
                'nama_outlet' => $request->nama_outlet,
                'kota'        => $request->kota ?: null,     // ✅
                'alamat'      => $request->alamat ?: null,   // ✅
                'status'      => $request->status,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
    
            return back()->with('success', 'Outlet berhasil ditambahkan.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal tambah outlet: '.$e->getMessage());
        }
    }

    public function updateOutlet(Request $request)
    {
        $request->validate([
            'id'          => 'required|exists:tbl_outlets,id',
            'area_id'     => 'nullable|integer',
            'mitra_id'    => 'nullable|exists:tbl_mitra,id',
            'kode_outlet' => 'required|string|max:50',
            'nama_outlet' => 'required|string|max:100',
            'kota'        => 'nullable|string|max:100', // ✅
            'alamat'      => 'nullable|string',          // ✅
            'status'      => 'required|in:existing,go,tutup',
        ]);
    
        try {
            $dup = DB::table('tbl_outlets')
                ->where('kode_outlet', $request->kode_outlet)
                ->where('id', '!=', $request->id)
                ->exists();
    
            if ($dup) {
                return back()->with('duplicate', 'Kode outlet sudah dipakai outlet lain.');
            }
    
            DB::table('tbl_outlets')
                ->where('id', $request->id)
                ->update([
                    'area_id'     => $request->area_id ?: null,
                    'mitra_id'    => $request->mitra_id ?: null,
                    'kode_outlet' => $request->kode_outlet,
                    'nama_outlet' => $request->nama_outlet,
                    'kota'        => $request->kota ?: null,   // ✅
                    'alamat'      => $request->alamat ?: null, // ✅
                    'status'      => $request->status,
                    'updated_at'  => now(),
                ]);
    
            return back()->with('success', 'Outlet berhasil diperbarui.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal update outlet: '.$e->getMessage());
        }
    }

    public function destroyOutlet($id)
    {
        try {
            DB::table('tbl_outlets')->where('id', $id)->delete();
    
            return redirect()
                ->route('investor.outlet.master')
                ->with('success', 'Outlet berhasil dihapus.');
        } catch (\Throwable $e) {
            return redirect()
                ->route('investor.outlet.master')
                ->with('error', 'Gagal menghapus outlet: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $filePath = public_path('tempImport/template import.csv');

        if (! file_exists($filePath)) {
            abort(404, 'File template tidak ditemukan.');
        }

        return response()->download($filePath, 'template_import_outlet.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function outletMatchAPI(Request $request)
    {
        $keyword = trim($request->keyword);

        $credentials = DB::table('tbl_api_credentials')
            ->select('id', 'credential_code', 'credential_name')
            ->where('is_active', 1)
            ->orderBy('credential_code')
            ->get();

        $branches = DB::table('tbl_api_credential_branches as b')
            ->leftJoin('tbl_api_credentials as c', 'c.id', '=', 'b.credential_id')
            ->select(
                'b.id',
                'b.credential_id',
                'b.branch_id',
                'b.branch_code',
                'b.branch_name',
                'c.credential_code',
                'c.credential_name'
            )
            ->orderBy('c.credential_code')
            ->orderBy('b.branch_code')
            ->get();

        $outlets = DB::table('tbl_outlets as o')
            ->leftJoin('tbl_api_credentials as c', 'c.id', '=', 'o.credential_id')
            ->select(
                'o.id',
                'o.branch_code',
                'o.credential_id',
                'o.kode_outlet',
                'o.nama_outlet',
                'c.credential_code',
                'c.credential_name'
            )
            ->when($keyword, function ($q) use ($keyword) {
                $q->where(function ($qq) use ($keyword) {
                    $qq->where('o.kode_outlet', 'like', "%{$keyword}%")
                        ->orWhere('o.nama_outlet', 'like', "%{$keyword}%")
                        ->orWhere('o.branch_code', 'like', "%{$keyword}%");
                });
            })
            ->orderBy('o.nama_outlet')
            ->paginate(25)
            ->withQueryString();

        return view('Investor.Master.outletMatchAPI', compact(
            'outlets',
            'credentials',
            'branches',
            'keyword'
        ));
    }

    public function outletMatchAPIUpdate(Request $request)
    {
        $request->validate([
            'id' => 'required|numeric',
            'branch_code' => 'nullable|string|max:20',
            'credential_id' => 'nullable|numeric',
        ]);

        DB::table('tbl_outlets')
            ->where('id', $request->id)
            ->update([
                'branch_code'   => $request->branch_code ?: null,
                'credential_id' => $request->credential_id ?: null,
                'updated_at'    => now(),
            ]);

        return redirect()
            ->route('investor.outletMatchAPI.master', $request->only('keyword', 'page'))
            ->with('success', 'Outlet berhasil diupdate.');
    }

    public function showSummaryDetailTransaksi()
    {
        return view('Investor.Master.summaryDetailTransaksi');
    }

    public function SummaryDetailTransaksi(Request $request, EsbSalesService $service)
    {
        $request->validate([
            'tanggal_awal'  => ['required', 'date'],
            'tanggal_akhir' => ['required', 'date', 'after_or_equal:tanggal_awal'],
        ]);

        $tanggalAwal = Carbon::parse($request->tanggal_awal)->startOfDay();
        $tanggalAkhir = Carbon::parse($request->tanggal_akhir)->startOfDay();

        $processed = [];

        try {
            $current = $tanggalAwal->copy();

            while ($current->lte($tanggalAkhir)) {
                $salesDate = $current->format('Y-m-d');

                $affectedBulanan = $service->syncDailySummaryToLaporanBulanan($salesDate);

                $processed[] = [
                    'tanggal' => $salesDate,
                    'affected_bulanan' => $affectedBulanan,
                ];

                $current->addDay();
            }

            Log::info('SUMMARY DETAIL TRANSAKSI RANGE DONE', [
                'tanggal_awal' => $request->tanggal_awal,
                'tanggal_akhir' => $request->tanggal_akhir,
                'processed' => $processed,
            ]);

            return redirect()
                ->route('investor.SummaryDetailTransaksi.form')
                ->with('success', 'Summary berhasil diproses dari ' . $request->tanggal_awal . ' sampai ' . $request->tanggal_akhir);
        } catch (\Throwable $e) {
            Log::error('SUMMARY DETAIL TRANSAKSI RANGE FAILED', [
                'tanggal_awal' => $request->tanggal_awal,
                'tanggal_akhir' => $request->tanggal_akhir,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal memproses summary: ' . $e->getMessage());
        }
    }
    
    // store khusus investor
    public function storeInvestor(Request $request)
    {
        $request->validate([
            'nama_investor' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
        ]);

        DB::table('tbl_investor')->insert([
            'user_id' => $request->user_id,
            'nama_investor' => $request->nama_investor,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('investor.user.master')->with('success', 'Investor baru berhasil ditambahkan.');
    }

    public function userInvestor()
    {
        $data = DB::table('tbl_investor as i')
            ->join('users as u', 'u.id', '=', 'i.user_id')
            ->select(
                'i.id',
                'i.nama_investor',
                'i.user_id',
                'u.name as nama_user',
                'u.email',
                'i.created_at'
            )
            ->orderBy('i.nama_investor', 'asc')
            ->get();

        $users = DB::table('users')
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return view('Investor.Master.userInvestor', compact('data', 'users'));
    }

    // store khusus users
    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        DB::table('users')->insert([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('investor.user.master')->with('success', 'User baru berhasil ditambahkan.');
    }

    public function updateInvestor(Request $request, $id)
    {
        $request->validate([
            'nama_investor' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$request->user_id,
            'password' => 'nullable|min:6',
        ]);

        // Update users
        $dataUser = [
            'name' => $request->name,
            'email' => $request->email,
            'updated_at' => now(),
        ];
        if ($request->filled('password')) {
            $dataUser['password'] = Hash::make($request->password);
        }
        DB::table('users')->where('id', $request->user_id)->update($dataUser);

        // Update investor
        DB::table('tbl_investor')->where('id', $id)->update([
            'nama_investor' => $request->nama_investor,
            'updated_at' => now(),
        ]);

        return redirect()->route('investor.user.master')->with('success', 'Investor berhasil diperbarui.');
    }

    public function destroyInvestor($id)
    {
        $investor = DB::table('tbl_investor')->where('id', $id)->first();

        if ($investor) {
            DB::table('tbl_investor')->where('id', $id)->delete();
            DB::table('users')->where('id', $investor->user_id)->delete();
        }

        return redirect()->route('Investor.Master.userInvestor')->with('success', 'Investor berhasil dihapus.');
    }
    
    // Master Users Operasional (multi outlet)
    public function userOperasional()
    {
        $data = DB::table('users as u')
            ->leftJoin('tbl_users_outlet as uo', 'uo.user_id', '=', 'u.id')
            ->leftJoin('tbl_outlets as o', 'o.id', '=', 'uo.outlet_id')
            ->select(
                'u.id',
                'u.name',
                'u.email',
                'u.role',
                'u.created_at',
                DB::raw("GROUP_CONCAT(o.nama_outlet ORDER BY o.nama_outlet SEPARATOR ', ') as nama_outlets"),
                DB::raw("GROUP_CONCAT(uo.outlet_id ORDER BY uo.outlet_id SEPARATOR ',') as outlet_ids")
            )
            ->whereIn('u.role', ['crew', 'leader', 'spv', 'tm_manager'])
            ->groupBy('u.id', 'u.name', 'u.email', 'u.role', 'u.created_at')
            ->orderBy('u.name', 'asc')
            ->get();
    
        $outlets = DB::table('tbl_outlets')
            ->select('id', 'nama_outlet')
            ->orderBy('nama_outlet', 'asc')
            ->get();
    
        return view('Investor.Master.userOperasional', compact('data', 'outlets'));
    }

    public function storeUserOperasional(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'password'     => 'required|min:6',
            'role'         => 'required|in:crew,leader,spv,tm_manager',
            'outlet_ids'   => 'required|array|min:1',
            'outlet_ids.*' => 'exists:tbl_outlets,id',
        ]);
    
        DB::beginTransaction();
    
        try {
            $outletIds = array_values(array_unique($request->outlet_ids ?? []));
    
            $userId = DB::table('users')->insertGetId([
                'name'       => $request->name,
                'email'      => $request->email,
                'password'   => Hash::make($request->password),
                'role'       => $request->role,
                'outlet_id'  => $outletIds[0] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
    
            $rows = [];
            foreach ($outletIds as $i => $oid) {
                $rows[] = [
                    'user_id'    => $userId,
                    'outlet_id'  => $oid,
                    'is_primary' => $i === 0 ? 1 : 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
    
            DB::table('tbl_users_outlet')->insert($rows);
    
            DB::commit();
    
            return redirect()->route('investor.user.operasional')
                ->with('success', 'User operasional berhasil ditambahkan.');
        } catch (\Throwable $e) {
            DB::rollBack();
    
            return redirect()->route('investor.user.operasional')
                ->with('error', 'Gagal menambahkan user operasional: ' . $e->getMessage());
        }
    }

    public function updateUserOperasional(Request $request, $id)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email,' . $id,
            'password'     => 'nullable|min:6',
            'role'         => 'required|in:crew,leader,spv,tm_manager',
            'outlet_ids'   => 'required|array|min:1',
            'outlet_ids.*' => 'exists:tbl_outlets,id',
        ]);
    
        DB::beginTransaction();
    
        try {
            $outletIds = array_values(array_unique($request->outlet_ids ?? []));
    
            $payload = [
                'name'       => $request->name,
                'email'      => $request->email,
                'role'       => $request->role,
                'outlet_id'  => $outletIds[0] ?? null,
                'updated_at' => now(),
            ];
    
            if ($request->filled('password')) {
                $payload['password'] = Hash::make($request->password);
            }
    
            DB::table('users')->where('id', $id)->update($payload);
    
            DB::table('tbl_users_outlet')->where('user_id', $id)->delete();
    
            $rows = [];
            foreach ($outletIds as $i => $oid) {
                $rows[] = [
                    'user_id'    => $id,
                    'outlet_id'  => $oid,
                    'is_primary' => $i === 0 ? 1 : 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
    
            DB::table('tbl_users_outlet')->insert($rows);
    
            DB::commit();
    
            return redirect()->route('investor.user.operasional')
                ->with('success', 'User operasional berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
    
            return redirect()->route('investor.user.operasional')
                ->with('error', 'Gagal update user operasional: ' . $e->getMessage());
        }
    }

    public function destroyUserOperasional($id)
    {
        $user = DB::table('users')->where('id', $id)->first();
    
        if (!$user) {
            return redirect()->route('investor.user.operasional')
                ->with('error', 'User tidak ditemukan.');
        }
    
        if (!in_array($user->role, ['crew', 'leader', 'spv', 'tm_manager'])) {
            return redirect()->route('investor.user.operasional')
                ->with('error', 'User bukan kategori operasional.');
        }
    
        DB::table('users')->where('id', $id)->delete();
    
        return redirect()->route('investor.user.operasional')
            ->with('success', 'User operasional berhasil dihapus.');
    }

    // Import file Excel
    public function importOutlet(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            Excel::import(new OutletsImport, $request->file('file'));

            return back()->with('success', 'Data outlet berhasil diimport!');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat import: '.$e->getMessage());
        }
    }

    public function dataArea(Request $request)
    {
        $user = auth()->user();

        // filter
        $areaId = $request->get('area_id', 'all');
        $mitraId = $request->get('mitra_id', 'all');
        $status = $request->get('status', 'all');
        $keyword = trim($request->get('q', ''));

        // list area + mitra untuk dropdown
        $areas = DB::table('tbl_area_outlet')->select('id', 'nama_area')->orderBy('nama_area')->get();

        // mitra: kalau investor, hanya mitra dia
        if ($user->role === 'superadmin') {
            $mitra = DB::table('tbl_mitra')->select('id', 'nama_mitra')->orderBy('nama_mitra')->get();
        } else {
            $investorId = DB::table('tbl_investor')->where('user_id', $user->id)->value('id');
            if (! $investorId) {
                abort(403, 'Investor tidak ditemukan.');
            }

            $mitra = DB::table('tbl_mitra')
                ->select('id', 'nama_mitra')
                ->where('investor_id', $investorId)
                ->orderBy('nama_mitra')
                ->get();
        }

        // query data table
        $q = DB::table('tbl_outlets')
            ->leftJoin('tbl_mitra', 'tbl_outlets.mitra_id', '=', 'tbl_mitra.id')
            ->leftJoin('tbl_area_outlet', 'tbl_outlets.area_id', '=', 'tbl_area_outlet.id')
            ->select(
                'tbl_outlets.id',
                'tbl_outlets.kode_outlet',
                'tbl_outlets.nama_outlet',
                'tbl_outlets.status',
                'tbl_outlets.mitra_id',
                'tbl_outlets.area_id as outlet_area_id',
                'tbl_mitra.nama_mitra',
                'tbl_area_outlet.nama_area'
            )
            ->orderBy('tbl_outlets.nama_outlet', 'asc');

        // scope akses investor
        if ($user->role !== 'superadmin') {
            $investorId = DB::table('tbl_investor')->where('user_id', $user->id)->value('id');
            $mitraIds = DB::table('tbl_mitra')->where('investor_id', $investorId)->pluck('id')->toArray();
            $q->whereIn('tbl_outlets.mitra_id', $mitraIds);
        }

        // apply filter dropdown
        if ($areaId !== 'all') {
            $q->where('tbl_outlets.area_id', $areaId);
        }
        if ($mitraId !== 'all') {
            $q->where('tbl_outlets.mitra_id', $mitraId);
        }
        if ($status !== 'all') {
            $q->where('tbl_outlets.status', $status);
        }

        // keyword search
        if ($keyword !== '') {
            $q->where(function ($w) use ($keyword) {
                $w->where('tbl_outlets.nama_outlet', 'like', "%{$keyword}%")
                    ->orWhere('tbl_outlets.kode_outlet', 'like', "%{$keyword}%")
                    ->orWhere('tbl_mitra.nama_mitra', 'like', "%{$keyword}%")
                    ->orWhere('tbl_area_outlet.nama_area', 'like', "%{$keyword}%");
            });
        }

        $data = $q->get();

        return view('Investor.Master.dataArea', compact(
            'data', 'areas', 'mitra',
            'areaId', 'mitraId', 'status', 'keyword'
        ));
    }

    public function updateArea(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'id' => 'required|integer',
            'kode_outlet' => 'required|string|max:50',
            'nama_outlet' => 'required|string|max:255',
            'mitra_id' => 'required|integer',
            'area_id' => 'required|integer',
            'status' => 'required|in:existing,go,tutup',
        ]);

        // ambil outlet
        $outlet = DB::table('tbl_outlets')->where('id', $validated['id'])->first();
        if (! $outlet) {
            return back()->with('error', 'Outlet tidak ditemukan.');
        }

        // cek akses investor (biar gak update outlet orang)
        if ($user->role !== 'superadmin') {
            $investorId = DB::table('tbl_investor')->where('user_id', $user->id)->value('id');
            if (! $investorId) {
                abort(403, 'Investor tidak ditemukan.');
            }

            $allowedMitraIds = DB::table('tbl_mitra')->where('investor_id', $investorId)->pluck('id')->toArray();

            // outlet lama harus milik investor ini
            if (! in_array((int) $outlet->mitra_id, $allowedMitraIds, true)) {
                abort(403, 'Tidak punya akses ke outlet ini.');
            }

            // mitra baru juga harus milik investor ini
            if (! in_array((int) $validated['mitra_id'], $allowedMitraIds, true)) {
                abort(403, 'Tidak boleh pindah outlet ke mitra lain.');
            }
        }

        // cek referensi area & mitra valid
        $areaExists = DB::table('tbl_area_outlet')->where('id', $validated['area_id'])->exists();
        if (! $areaExists) {
            return back()->with('error', 'Area tidak valid.');
        }

        $mitraExists = DB::table('tbl_mitra')->where('id', $validated['mitra_id'])->exists();
        if (! $mitraExists) {
            return back()->with('error', 'Mitra tidak valid.');
        }

        DB::table('tbl_outlets')
            ->where('id', $validated['id'])
            ->update([
                'kode_outlet' => $validated['kode_outlet'],
                'nama_outlet' => $validated['nama_outlet'],
                'mitra_id' => $validated['mitra_id'],
                'area_id' => $validated['area_id'],
                'status' => $validated['status'],
                'updated_at' => now(),
            ]);

        return redirect()->route('investor.outlet.master')->with('success', 'Outlet berhasil diupdate.');
    }

    public function deleteArea($id)
    {
        $user = auth()->user();

        $outlet = DB::table('tbl_outlets')->where('id', $id)->first();
        if (! $outlet) {
            return back()->with('error', 'Outlet tidak ditemukan.');
        }

        // cek akses investor
        if ($user->role !== 'superadmin') {
            $investorId = DB::table('tbl_investor')->where('user_id', $user->id)->value('id');
            if (! $investorId) {
                abort(403, 'Investor tidak ditemukan.');
            }

            $allowedMitraIds = DB::table('tbl_mitra')->where('investor_id', $investorId)->pluck('id')->toArray();

            if (! in_array((int) $outlet->mitra_id, $allowedMitraIds, true)) {
                abort(403, 'Tidak punya akses untuk hapus outlet ini.');
            }
        }

        DB::table('tbl_outlets')->where('id', $id)->delete();

        return redirect()->route('investor.area.master')->with('success', 'Outlet berhasil dihapus.');
    }

    // OPTIONAL biar lengkap dengan FE yang sudah ada
    public function storeArea(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'kode_outlet' => 'required|string|max:50',
            'nama_outlet' => 'required|string|max:255',
            'mitra_id' => 'required|integer',
            'area_id' => 'required|integer',
            'status' => 'required|in:existing,go,tutup',
        ]);

        // investor hanya boleh tambah untuk mitra dia
        if ($user->role !== 'superadmin') {
            $investorId = DB::table('tbl_investor')->where('user_id', $user->id)->value('id');
            if (! $investorId) {
                abort(403, 'Investor tidak ditemukan.');
            }

            $allowed = DB::table('tbl_mitra')
                ->where('investor_id', $investorId)
                ->where('id', $validated['mitra_id'])
                ->exists();

            if (! $allowed) {
                abort(403, 'Tidak boleh tambah outlet untuk mitra lain.');
            }
        }

        $areaExists = DB::table('tbl_area_outlet')->where('id', $validated['area_id'])->exists();
        if (! $areaExists) {
            return back()->with('error', 'Area tidak valid.');
        }

        DB::table('tbl_outlets')->insert([
            'kode_outlet' => $validated['kode_outlet'],
            'nama_outlet' => $validated['nama_outlet'],
            'mitra_id' => $validated['mitra_id'],
            'area_id' => $validated['area_id'],
            'status' => $validated['status'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('investor.outlet.master')->with('success', 'Outlet berhasil ditambahkan.');
    }

    // DATA INTERNAL AUDIT
    public function dataInternalAudit(Request $request)
    {
        $q = DB::table('tbl_audit_reports as r')
            ->join('tbl_outlets as o', 'o.id', '=', 'r.outlet_id')
            ->select('r.*', 'o.kode_outlet', 'o.nama_outlet');

        $hasFilter = $request->filled('period_ym') || $request->filled('outlet_id');

        $audits = collect();

        if ($hasFilter) {

            // ✅ FILTER OUTLET
            if ($request->filled('outlet_id')) {
                $q->where('r.outlet_id', $request->outlet_id);
            }

            // ✅ FILTER PERIODE (STABIL)
            if ($request->filled('period_ym')) {
                $ym = $request->period_ym;           // "YYYY-MM"
                $ym01 = $ym . '-01';                 // "YYYY-MM-01"

                $q->where(function ($w) use ($ym, $ym01) {
                    // kemungkinan 1: period_ym disimpan "YYYY-MM"
                    $w->where('r.period_ym', $ym)

                    // kemungkinan 2: period_ym disimpan "YYYY-MM-01"
                    ->orWhere('r.period_ym', $ym01)

                    // kemungkinan 3: period_ym kosong/beda, tapi audit_date ada
                    ->orWhereRaw("DATE_FORMAT(r.audit_date, '%Y-%m') = ?", [$ym]);
                });
            }

            $audits = $q->orderByDesc('r.audit_date')->get();
        }

        // dd($request->all(), $q->toSql(), $q->getBindings());

        // ===== hitung skor di PHP (punyamu) =====
        $audits = $audits->map(function ($row) {
            $avg = function (array $vals) {
                $nums = array_values(array_filter($vals, fn ($v) => $v !== null && $v !== ''));
                if (!count($nums)) return null;
                return array_sum($nums) / count($nums);
            };

            $avgCompliance = $avg([$row->compliance_1, $row->compliance_2, $row->compliance_3]);
            $avg5r         = $avg([$row->r5_ringkas, $row->r5_rapi, $row->r5_resik]);
            $avgStock      = $avg([$row->stock_1, $row->stock_2, $row->stock_3]);
            $avgCash       = $avg([$row->cash_1, $row->cash_2, $row->cash_3]);
            $avgQcp        = $avg([$row->qcp_1, $row->qcp_2, $row->qcp_3]);

            $groups = array_values(array_filter([$avgCompliance, $avg5r, $avgStock, $avgCash, $avgQcp], fn ($v) => $v !== null));
            $pencapaian = count($groups) ? round(array_sum($groups) / count($groups), 2) : null;

            $target = $row->target ?? 80;
            $gap    = $pencapaian === null ? null : round($pencapaian - (float)$target, 2);

            $row->pencapaian = $pencapaian;
            $row->gap        = $gap;
            $row->hasil      = $pencapaian === null ? 'BELUM' : ($pencapaian >= (float)$target ? 'LULUS' : 'TIDAK');

            return $row;
        });

        $outlets = DB::table('tbl_outlets')->select('id', 'nama_outlet')->orderBy('nama_outlet')->get();

        return view('Investor.Master.dataInternalAudit', compact('audits', 'outlets'));
    }

    public function storeInternalAudit(Request $request)
    {
        $data = $this->validated($request);

        if (empty($data['period_ym'])) {
            $data['period_ym'] = date('Y-m', strtotime($data['audit_date']));
        }

        DB::table('tbl_audit_reports')->insert($data);

        return redirect()->back()->with('success', 'Audit berhasil ditambahkan.');
    }

    public function updateInternalAudit(Request $request, $id)
    {
        $data = $this->validated($request);

        if (empty($data['period_ym'])) {
            $data['period_ym'] = date('Y-m', strtotime($data['audit_date']));
        }

        DB::table('tbl_audit_reports')->where('id', $id)->update($data);

        return redirect()->back()->with('success', 'Audit berhasil diubah.');
    }

    private function validatedInternalAudit(Request $request): array
    {
        $num = ['nullable', 'numeric', 'min:0', 'max:100'];

        return $request->validate([
            'outlet_id' => ['required', 'integer', 'exists:tbl_outlets,id'],
            'auditor' => ['nullable', 'string', 'max:100'],
            'audit_date' => ['required', 'date'],
            'period_ym' => ['nullable', 'regex:/^\d{4}-\d{2}$/'],

            'compliance_1' => $num, 'compliance_2' => $num, 'compliance_3' => $num,
            'r5_ringkas' => $num, 'r5_rapi' => $num, 'r5_resik' => $num,
            'stock_1' => $num, 'stock_2' => $num, 'stock_3' => $num,
            'cash_1' => $num, 'cash_2' => $num, 'cash_3' => $num,
            'qcp_1' => $num, 'qcp_2' => $num, 'qcp_3' => $num,

            'target' => ['required', 'numeric', 'min:0', 'max:100'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);
    }

    public function importInternalAudit(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:5048', 'mimes:csv,txt,xlsx'],
            'skip_if_exists' => ['nullable'],
        ]);

        $skipIfExists = $request->boolean('skip_if_exists');
        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());

        $allowed = [
            'outlet_id', 'kode_outlet', 'nama_outlet',
            'audit_date', 'period_ym', 'auditor', 'target', 'keterangan',
            'compliance_1', 'compliance_2', 'compliance_3',
            'r5_ringkas', 'r5_rapi', 'r5_resik',
            'stock_1', 'stock_2', 'stock_3',
            'cash_1', 'cash_2', 'cash_3',
            'qcp_1', 'qcp_2', 'qcp_3',
        ];

        $toNum = function ($v) {
            if ($v === null) {
                return null;
            }
            $v = trim((string) $v);
            if ($v === '' || $v === '-') {
                return null;
            }
            $v = str_replace(['%', ' '], '', $v);
            $v = str_replace(',', '.', $v);

            return is_numeric($v) ? (float) $v : null;
        };

        $toDate = function ($v) {
            if ($v === null) {
                return null;
            }

            if (is_numeric($v)) { // excel serial
                $ts = ((int) $v - 25569) * 86400;

                return gmdate('Y-m-d', $ts);
            }

            $v = trim((string) $v);
            if ($v === '') {
                return null;
            }

            $raw = str_replace(['\\', '-', '.'], '/', $v);

            if (preg_match('/^\d{4}\/\d{1,2}\/\d{1,2}$/', $raw)) {
                $p = explode('/', $raw);

                return sprintf('%04d-%02d-%02d', (int) $p[0], (int) $p[1], (int) $p[2]);
            }

            if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $raw)) {
                $p = explode('/', $raw);
                $a = (int) $p[0];
                $b = (int) $p[1];
                $Y = (int) $p[2];

                if ($a > 12) {
                    $d = $a;
                    $m = $b;
                } elseif ($b > 12) {
                    $m = $a;
                    $d = $b;
                } else {
                    $d = $a;
                    $m = $b;
                } // default ID dd/mm

                return sprintf('%04d-%02d-%02d', $Y, $m, $d);
            }

            $ts = strtotime($v);

            return $ts ? date('Y-m-d', $ts) : null;
        };

        // ===== read rows =====
        $rows = [];
        if ($ext === 'csv' || $ext === 'txt') {
            $handle = fopen($file->getRealPath(), 'r');
            if (! $handle) {
                return back()->withErrors(['file' => 'File tidak bisa dibaca.']);
            }
            while (($r = fgetcsv($handle, 0, ',')) !== false) {
                $rows[] = $r;
            }
            fclose($handle);
        } else {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
        }

        if (count($rows) < 2) {
            return back()->withErrors(['file' => 'File kosong atau tidak ada data.']);
        }

        // header normalize
        $header = [];
        foreach (($rows[0] ?: []) as $h) {
            $h = trim((string) $h);
            $lower = strtolower($h);
            if ($lower === 'outlet') {
                $h = 'nama_outlet';
            }
            $header[] = $h;
        }

        if (! in_array('audit_date', $header, true)) {
            return back()->withErrors(['file' => 'File wajib punya kolom audit_date.']);
        }

        if (
            ! in_array('outlet_id', $header, true) &&
            ! in_array('kode_outlet', $header, true) &&
            ! in_array('nama_outlet', $header, true)
        ) {
            return back()->withErrors(['file' => 'File wajib punya kolom outlet_id atau kode_outlet atau nama_outlet.']);
        }

        // cache outlets
        $outlets = DB::table('tbl_outlets')->select('id', 'kode_outlet', 'nama_outlet')->get();
        if ($outlets->count() === 0) {
            return back()->withErrors(['file' => 'tbl_outlets kosong. Tidak bisa resolve outlet.']);
        }

        $inserted = 0;
        $skipped = 0;
        $errors = [];        // pesan ringkas
        $warnings = [];
        $failedRows = [];      // baris gagal utk diexport

        DB::beginTransaction();
        try {
            for ($idx = 1; $idx < count($rows); $idx++) {
                $rowNum = $idx + 1;
                $row = $rows[$idx];

                // skip empty row
                $nonEmpty = false;
                foreach ((array) $row as $x) {
                    if (trim((string) $x) !== '') {
                        $nonEmpty = true;
                        break;
                    }
                }
                if (! $nonEmpty) {
                    continue;
                }

                // map header -> data (hanya allowed)
                $data = [];
                foreach ($header as $i => $col) {
                    if (! in_array($col, $allowed, true)) {
                        continue;
                    }
                    $data[$col] = isset($row[$i]) ? $row[$i] : null;
                }

                // audit_date
                $auditDateRaw = isset($data['audit_date']) ? $data['audit_date'] : null;
                $auditDate = $toDate($auditDateRaw);
                if (! $auditDate) {
                    $msg = "Baris {$rowNum}: audit_date tidak valid ({$auditDateRaw})";
                    $errors[] = $msg;
                    $failedRows[] = $this->buildFailedRow($header, $row, $msg);

                    continue;
                }

                // resolve outlet_id
                $outletId = null;
                $matchScore = 0;
                $matchSrc = '';
                $matchMode = '';

                // 1) outlet_id
                if (! empty($data['outlet_id'])) {
                    $tryId = (int) $data['outlet_id'];
                    $ok = $outlets->firstWhere('id', $tryId);
                    if ($ok) {
                        $outletId = $tryId;
                        $matchScore = 100;
                        $matchSrc = (string) $ok->nama_outlet;
                        $matchMode = 'outlet_id';
                    }
                }

                // 2) kode_outlet exact
                if ($outletId === null && ! empty($data['kode_outlet'])) {
                    $val = trim((string) $data['kode_outlet']);
                    if ($val !== '') {
                        $found = $outlets->firstWhere('kode_outlet', $val);
                        if ($found) {
                            $outletId = (int) $found->id;
                            $matchScore = 100;
                            $matchSrc = (string) $found->nama_outlet;
                            $matchMode = 'kode_outlet';
                        }
                    }
                }

                // 3) fuzzy pakai nama_outlet dulu, kalau kosong pakai kode_outlet (anggap itu nama)
                if ($outletId === null) {
                    $srcText = '';
                    if (! empty($data['nama_outlet'])) {
                        $srcText = trim((string) $data['nama_outlet']);
                    }
                    if ($srcText === '' && ! empty($data['kode_outlet'])) {
                        $srcText = trim((string) $data['kode_outlet']);
                    }

                    if ($srcText !== '') {
                        $res = $this->resolveOutletIdFuzzyStrict($srcText, $outlets); // strict: boleh gagal kalau score terlalu rendah
                        if ($res['id'] !== null) {
                            $outletId = (int) $res['id'];
                            $matchScore = (float) $res['score'];
                            $matchSrc = (string) $res['src'];
                            $matchMode = 'fuzzy';
                        } else {
                            $msg = "Baris {$rowNum}: outlet '{$srcText}' tidak yakin (score {$res['score']}). Kandidat: ".implode(' | ', $res['candidates']);
                            $errors[] = $msg;
                            $failedRows[] = $this->buildFailedRow($header, $row, $msg);

                            continue;
                        }
                    }
                }

                if ($outletId === null) {
                    $msg = "Baris {$rowNum}: outlet kosong (isi outlet_id/kode_outlet/nama_outlet)";
                    $errors[] = $msg;
                    $failedRows[] = $this->buildFailedRow($header, $row, $msg);

                    continue;
                }

                // skip duplicate
                if ($skipIfExists) {
                    $exists = DB::table('tbl_audit_reports')
                        ->where('outlet_id', $outletId)
                        ->where('audit_date', $auditDate)
                        ->exists();

                    if ($exists) {
                        $skipped++;

                        continue;
                    }
                }

                // period_ym
                $periodYm = isset($data['period_ym']) ? trim((string) $data['period_ym']) : '';
                if ($periodYm === '') {
                    $periodYm = date('Y-m', strtotime($auditDate));
                }

                // keterangan (append tag)
                $ket = isset($data['keterangan']) ? trim((string) $data['keterangan']) : '';
                $tag = "[match={$matchMode};score={$matchScore};src={$matchSrc}]";
                $keteranganFinal = ($ket !== '') ? ($ket.' '.$tag) : $tag;

                // optional warning
                if ($matchMode === 'fuzzy' && $matchScore < 60) {
                    $warnings[] = "Baris {$rowNum}: score fuzzy rendah ({$matchScore}) => {$matchSrc}";
                }

                DB::table('tbl_audit_reports')->insert([
                    'outlet_id' => $outletId,
                    'audit_date' => $auditDate,
                    'period_ym' => $periodYm,
                    'auditor' => (isset($data['auditor']) && trim((string) $data['auditor']) !== '') ? trim((string) $data['auditor']) : null,
                    'target' => ($toNum(isset($data['target']) ? $data['target'] : 80) !== null) ? $toNum(isset($data['target']) ? $data['target'] : 80) : 80,
                    'keterangan' => $keteranganFinal,

                    'compliance_1' => $toNum(isset($data['compliance_1']) ? $data['compliance_1'] : null),
                    'compliance_2' => $toNum(isset($data['compliance_2']) ? $data['compliance_2'] : null),
                    'compliance_3' => $toNum(isset($data['compliance_3']) ? $data['compliance_3'] : null),

                    'r5_ringkas' => $toNum(isset($data['r5_ringkas']) ? $data['r5_ringkas'] : null),
                    'r5_rapi' => $toNum(isset($data['r5_rapi']) ? $data['r5_rapi'] : null),
                    'r5_resik' => $toNum(isset($data['r5_resik']) ? $data['r5_resik'] : null),

                    'stock_1' => $toNum(isset($data['stock_1']) ? $data['stock_1'] : null),
                    'stock_2' => $toNum(isset($data['stock_2']) ? $data['stock_2'] : null),
                    'stock_3' => $toNum(isset($data['stock_3']) ? $data['stock_3'] : null),

                    'cash_1' => $toNum(isset($data['cash_1']) ? $data['cash_1'] : null),
                    'cash_2' => $toNum(isset($data['cash_2']) ? $data['cash_2'] : null),
                    'cash_3' => $toNum(isset($data['cash_3']) ? $data['cash_3'] : null),

                    'qcp_1' => $toNum(isset($data['qcp_1']) ? $data['qcp_1'] : null),
                    'qcp_2' => $toNum(isset($data['qcp_2']) ? $data['qcp_2'] : null),
                    'qcp_3' => $toNum(isset($data['qcp_3']) ? $data['qcp_3'] : null),

                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $inserted++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withErrors(['file' => 'Import gagal: '.$e->getMessage()]);
        }

        // ===== generate excel failed rows (kalau ada) =====
        $failedUrl = null;
        if (count($failedRows) > 0) {
            $failedUrl = $this->exportFailedRowsToExcel($header, $failedRows);
        }

        $msg = "Import selesai. Berhasil: {$inserted}. Dilewati: {$skipped}. Gagal: ".count($errors).'. Warning: '.count($warnings).'.';

        $resp = back()->with('success', $msg);
        if (count($errors)) {
            $resp = $resp->with('import_errors', array_slice($errors, 0, 30));
        }
        if (count($warnings)) {
            $resp = $resp->with('import_warnings', array_slice($warnings, 0, 30));
        }
        if ($failedUrl) {
            $resp = $resp->with('failed_export_url', $failedUrl);
        }

        return $resp;
    }

    private function resolveOutletIdSmart(string $input, $outletCache): array
    {
        $normalize = function ($s) {
            $s = mb_strtolower(trim((string) $s));

            // normalisasi istilah umum
            $s = str_replace(['(express)', 'express', ' exp ', 'exp'], ' ', $s);
            $s = str_replace(['gerai', 'g.', 'g '], ' ', $s);

            // singkatan lokasi umum
            $s = str_replace(['jaktim', 'jak tim'], 'jakarta timur', $s);

            // typo/varian umum
            $s = str_replace(['brigjend', 'brigjen'], 'brigjen', $s);
            $s = str_replace(['gatotsubroto', 'gatot subroto'], 'gatot subroto', $s);

            // hapus tanda baca
            $s = preg_replace('/[^\pL\pN\s]+/u', ' ', $s);

            // rapikan spasi
            $s = preg_replace('/\s+/u', ' ', $s);

            return trim($s);
        };

        $stripSpaces = fn ($s) => str_replace(' ', '', $s);

        // stopwords lokasi (biar fokus ke kata inti)
        $stop = ['sidoarjo', 'lamongan', 'jember', 'lumajang', 'banyumas', 'purwokerto', 'karang', 'kab', 'kota', 'jakarta', 'timur', 'barat', 'utara', 'selatan'];

        $coreTokens = function ($s) use ($stop) {
            $toks = array_values(array_filter(explode(' ', $s)));
            $toks = array_values(array_filter($toks, fn ($x) => mb_strlen($x) >= 4 && ! in_array($x, $stop, true)));

            return array_values(array_unique($toks));
        };

        $tokenScore = function (array $a, array $b) {
            if (! $a || ! $b) {
                return 0;
            }
            $inter = count(array_intersect($a, $b));
            $union = count(array_unique(array_merge($a, $b)));

            return $union ? ($inter / $union) * 100 : 0;
        };

        $inNorm = $normalize($input);
        if ($inNorm === '') {
            return ['id' => null, 'score' => 0, 'ambiguous' => false, 'candidates' => []];
        }

        $inTokens = $coreTokens($inNorm);
        $inNoSpace = $stripSpaces($inNorm);

        $cands = [];
        foreach ($outletCache as $o) {
            $raw = (string) ($o->nama_outlet ?? '');
            $nm = $normalize($raw);
            if ($nm === '') {
                continue;
            }

            $nmTokens = $coreTokens($nm);

            // 1) similar_text normal
            similar_text($inNorm, $nm, $pct1);

            // 2) token similarity (kata inti)
            $pct2 = $tokenScore($inTokens, $nmTokens);

            // 3) no-space compare (KEBON AGUNG vs KEBONAGUNG)
            $nmNoSpace = $stripSpaces($nm);
            similar_text($inNoSpace, $nmNoSpace, $pct3);

            // gabung skor (token dibuat lebih penting)
            $score = (0.30 * $pct1) + (0.50 * $pct2) + (0.20 * $pct3);

            // bonus kalau ada token unik yang persis match
            $uniqHit = 0;
            foreach ($inTokens as $t) {
                if (in_array($t, $nmTokens, true)) {
                    $uniqHit++;
                }
            }
            if ($uniqHit >= 1) {
                $score += 3;
            }
            if ($uniqHit >= 2) {
                $score += 4;
            }

            $cands[] = [
                'id' => (int) $o->id,
                'name' => $raw,
                'score' => round($score, 2),
                'uniqHit' => $uniqHit,
            ];
        }

        usort($cands, fn ($a, $b) => $b['score'] <=> $a['score']);
        $top = array_slice($cands, 0, 3);

        $best = $top[0] ?? null;
        $second = $top[1] ?? null;

        if (! $best) {
            return ['id' => null, 'score' => 0, 'ambiguous' => false, 'candidates' => []];
        }

        $bestScore = $best['score'];
        $secondScore = $second['score'] ?? 0;
        $gap = $bestScore - $secondScore;

        // aturan baru:
        // - threshold turun jadi 55
        // - kalau uniqHit >= 1 dan best >= 55 => accept walau gap kecil
        // - ambiguous hanya kalau best >= 55 tapi uniqHit == 0 dan gap < 1.0
        $threshold = 55;

        if ($bestScore < $threshold) {
            return ['id' => null, 'score' => $bestScore, 'ambiguous' => false, 'candidates' => $top];
        }

        $ambiguous = ($best['uniqHit'] === 0) && ($gap < 1.0);

        return [
            'id' => $ambiguous ? null : (int) $best['id'],
            'score' => $bestScore,
            'ambiguous' => $ambiguous,
            'candidates' => $top,
        ];
    }

    /**
     * Fuzzy resolver outlet by name (best match).
     * Return: ['id'=>int|null,'score'=>float,'ambiguous'=>bool]
     */
    private function resolveOutletIdFuzzyAlways($input, $outlets)
    {
        $normalize = function ($s) {
            $s = mb_strtolower(trim((string) $s));

            $s = str_replace(['(express)', 'express'], ' ', $s);
            $s = preg_replace('/\bexp\b/u', ' ', $s);
            $s = preg_replace('/\bg\b\.?/u', ' ', $s);

            $s = preg_replace('/\bjaktim\b/u', 'jakarta timur', $s);
            $s = preg_replace('/\bjakbar\b/u', 'jakarta barat', $s);
            $s = preg_replace('/\bjaksel\b/u', 'jakarta selatan', $s);
            $s = preg_replace('/\bjakut\b/u', 'jakarta utara', $s);

            $s = preg_replace('/\bjln\b|\bjl\b/u', 'jalan', $s);

            $s = preg_replace('/[^\pL\pN\s,]+/u', ' ', $s);
            $s = preg_replace('/\s+/u', ' ', $s);

            return trim($s);
        };

        $stripSpaces = function ($s) {
            return str_replace(' ', '', $s);
        };

        $tokenize = function ($s) {
            $t = array_values(array_filter(explode(' ', $s)));
            $out = [];
            foreach ($t as $x) {
                if (mb_strlen($x) >= 3) {
                    $out[] = $x;
                }
            }

            return $out;
        };

        $jaccard = function ($a, $b) {
            if (! $a || ! $b) {
                return 0.0;
            }
            $a = array_values(array_unique($a));
            $b = array_values(array_unique($b));
            $inter = count(array_intersect($a, $b));
            $union = count(array_unique(array_merge($a, $b)));

            return $union ? ($inter / $union) * 100.0 : 0.0;
        };

        $diceBigram = function ($a, $b) {
            $a = preg_replace('/\s+/u', '', $a);
            $b = preg_replace('/\s+/u', '', $b);

            if (mb_strlen($a) < 2 || mb_strlen($b) < 2) {
                return 0.0;
            }

            $bigrams = function ($s) {
                $len = mb_strlen($s);
                $arr = [];
                for ($i = 0; $i < $len - 1; $i++) {
                    $arr[] = mb_substr($s, $i, 2);
                }

                return $arr;
            };

            $A = $bigrams($a);
            $B = $bigrams($b);

            $freqA = array_count_values($A);
            $freqB = array_count_values($B);

            $inter = 0;
            foreach ($freqA as $bg => $cnt) {
                if (isset($freqB[$bg])) {
                    $inter += min($cnt, $freqB[$bg]);
                }
            }

            $countA = count($A);
            $countB = count($B);

            return ($countA + $countB) ? (2.0 * $inter / ($countA + $countB)) * 100.0 : 0.0;
        };

        $inRaw = trim((string) $input);
        $in = $normalize($inRaw);

        if ($in === '') {
            $first = $outlets->first();

            return ['id' => (int) $first->id, 'score' => 0.0, 'src' => (string) $first->nama_outlet];
        }

        // lokasi setelah koma
        $locPart = null;
        if (strpos($inRaw, ',') !== false) {
            $parts = array_map('trim', explode(',', $inRaw));
            $last = end($parts);
            $loc = $normalize($last ? $last : '');
            if ($loc !== '') {
                $locPart = $loc;
            }
        }

        $inTokens = $tokenize($in);
        $inNoSpace = $stripSpaces($in);

        $bestId = null;
        $bestScore = -1;
        $bestName = null;

        foreach ($outlets as $o) {
            $nameRaw = (string) $o->nama_outlet;
            $nm = $normalize($nameRaw);
            if ($nm === '') {
                continue;
            }

            similar_text($in, $nm, $pct1);
            $pct2 = $jaccard($inTokens, $tokenize($nm));
            similar_text($inNoSpace, $stripSpaces($nm), $pct3);
            $pct4 = $diceBigram($inNoSpace, $stripSpaces($nm));

            $bonusLoc = 0.0;
            if ($locPart) {
                $locHit = $jaccard($tokenize($locPart), $tokenize($nm));
                $bonusLoc = 0.20 * $locHit; // max +20
            }

            $score = (0.15 * $pct1) + (0.30 * $pct2) + (0.15 * $pct3) + (0.40 * $pct4) + $bonusLoc;

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestId = (int) $o->id;
                $bestName = $nameRaw;
            }
        }

        if ($bestId === null) {
            $first = $outlets->first();

            return ['id' => (int) $first->id, 'score' => 0.0, 'src' => (string) $first->nama_outlet];
        }

        return ['id' => $bestId, 'score' => round($bestScore, 2), 'src' => (string) $bestName];
    }

    private function resolveOutletIdByName(string $input, $outletCache): array
    {
        $normalize = function ($s) {
            $s = mb_strtolower(trim((string) $s));

            // samakan istilah umum
            $s = str_replace(['(express)', 'express', ' exp ', 'exp'], ' ', $s);
            $s = str_replace(['gerai', 'g.', 'g '], ' ', $s);

            // alias singkatan yang sering
            $s = preg_replace('/\btb\b/u', 'tambak', $s);

            // hapus tanda baca
            $s = preg_replace('/[^\pL\pN\s]+/u', ' ', $s);

            // rapikan spasi
            $s = preg_replace('/\s+/u', ' ', $s);

            return trim($s);
        };

        $stripSpaces = fn ($s) => str_replace(' ', '', $s);

        $tokenScore = function (string $a, string $b) {
            $ta = array_values(array_filter(explode(' ', $a)));
            $tb = array_values(array_filter(explode(' ', $b)));
            if (! $ta || ! $tb) {
                return 0;
            }

            $sa = array_unique($ta);
            $sb = array_unique($tb);

            $inter = count(array_intersect($sa, $sb));
            $union = count(array_unique(array_merge($sa, $sb)));

            return $union ? ($inter / $union) * 100 : 0;
        };

        $in = $normalize($input);
        if ($in === '') {
            return ['id' => null, 'score' => 0, 'ambiguous' => false, 'candidates' => []];
        }

        $best = null;
        $bestScore = 0;
        $secondScore = 0;

        $cands = [];

        foreach ($outletCache as $o) {
            $nameRaw = (string) ($o->nama_outlet ?? '');
            $nameNorm = $normalize($nameRaw);
            if ($nameNorm === '') {
                continue;
            }

            // 1) similar_text normal
            similar_text($in, $nameNorm, $pct1);

            // 2) token score
            $pct2 = $tokenScore($in, $nameNorm);

            // 3) compare versi tanpa spasi (KEBON AGUNG vs KEBONAGUNG)
            $in2 = $stripSpaces($in);
            $nm2 = $stripSpaces($nameNorm);
            similar_text($in2, $nm2, $pct3);

            // gabung skor
            $score = (0.40 * $pct1) + (0.35 * $pct2) + (0.25 * $pct3);

            $cands[] = [
                'id' => (int) $o->id,
                'name' => $nameRaw,
                'score' => round($score, 2),
            ];

            if ($score > $bestScore) {
                $secondScore = $bestScore;
                $bestScore = $score;
                $best = $o;
            } elseif ($score > $secondScore) {
                $secondScore = $score;
            }
        }

        usort($cands, fn ($a, $b) => $b['score'] <=> $a['score']);
        $top = array_slice($cands, 0, 3);

        // threshold lebih rendah karena data kamu banyak variasi format
        $threshold = 60;

        if (! $best || $bestScore < $threshold) {
            return [
                'id' => null,
                'score' => round($bestScore, 2),
                'ambiguous' => false,
                'candidates' => $top,
            ];
        }

        // ambiguous kalau beda best vs runner-up kecil (biar aman)
        $ambiguous = ($bestScore - $secondScore) < 2.0;

        return [
            'id' => (int) $best->id,
            'score' => round($bestScore, 2),
            'ambiguous' => $ambiguous,
            'candidates' => $top,
        ];
    }

    private function resolveOutletIdByFuzzyName(string $namaOutlet): ?int
    {
        $namaOutlet = strtolower(trim($namaOutlet));

        $outlets = DB::table('tbl_outlets')->select('id', 'nama_outlet')->get();

        $best = null;
        $score = 0;

        foreach ($outlets as $o) {
            similar_text(strtolower($o->nama_outlet), $namaOutlet, $pct);
            if ($pct > $score) {
                $score = $pct;
                $best = $o;
            }
        }

        return ($best && $score >= 70) ? (int) $best->id : null;
    }

    private function buildFailedRow($header, $row, $errorMessage)
    {
        // bikin array sesuai urutan header file import
        $out = [];
        for ($i = 0; $i < count($header); $i++) {
            $out[] = isset($row[$i]) ? $row[$i] : null;
        }
        // tambah kolom error_message
        $out[] = $errorMessage;

        return $out;
    }

    private function exportFailedRowsToExcel($header, $failedRows)
    {
        // header excel = header import + error_message
        $excelHeader = $header;
        $excelHeader[] = 'error_message';

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        // tulis header
        for ($c = 0; $c < count($excelHeader); $c++) {
            $sheet->setCellValueByColumnAndRow($c + 1, 1, (string) $excelHeader[$c]);
        }

        // tulis rows
        $r = 2;
        foreach ($failedRows as $rowArr) {
            for ($c = 0; $c < count($rowArr); $c++) {
                $sheet->setCellValueByColumnAndRow($c + 1, $r, $rowArr[$c]);
            }
            $r++;
        }

        $filename = 'internal_audit_failed_'.date('Ymd_His').'.xlsx';
        $path = 'imports/failed/'.$filename;

        // simpan ke storage/app/public/...
        if (! Storage::disk('public')->exists('imports/failed')) {
            Storage::disk('public')->makeDirectory('imports/failed');
        }

        $fullPath = Storage::disk('public')->path($path);

        $writer = new Xlsx($spreadsheet);
        $writer->save($fullPath);

        // url download (butuh php artisan storage:link)
        return asset('storage/'.$path);
    }

    private function resolveOutletIdFuzzyStrict($input, $outlets)
    {
        $normalize = function ($s) {
            $s = mb_strtolower(trim((string) $s));
            $s = str_replace(['(express)', 'express'], ' ', $s);
            $s = preg_replace('/\bexp\b/u', ' ', $s);
            $s = preg_replace('/\bg\b\.?/u', ' ', $s);
            $s = preg_replace('/\bjln\b|\bjl\b/u', 'jalan', $s);
            $s = preg_replace('/[^\pL\pN\s,]+/u', ' ', $s);
            $s = preg_replace('/\s+/u', ' ', $s);

            return trim($s);
        };

        $stripSpaces = function ($s) {
            return str_replace(' ', '', $s);
        };

        $diceBigram = function ($a, $b) {
            $a = preg_replace('/\s+/u', '', $a);
            $b = preg_replace('/\s+/u', '', $b);
            if (mb_strlen($a) < 2 || mb_strlen($b) < 2) {
                return 0.0;
            }

            $bigrams = function ($s) {
                $len = mb_strlen($s);
                $arr = [];
                for ($i = 0; $i < $len - 1; $i++) {
                    $arr[] = mb_substr($s, $i, 2);
                }

                return $arr;
            };

            $A = $bigrams($a);
            $B = $bigrams($b);
            $fa = array_count_values($A);
            $fb = array_count_values($B);

            $inter = 0;
            foreach ($fa as $bg => $cnt) {
                if (isset($fb[$bg])) {
                    $inter += min($cnt, $fb[$bg]);
                }
            }
            $ca = count($A);
            $cb = count($B);

            return ($ca + $cb) ? (2.0 * $inter / ($ca + $cb)) * 100.0 : 0.0;
        };

        $inRaw = trim((string) $input);
        $in = $normalize($inRaw);
        if ($in === '') {
            return ['id' => null, 'score' => 0, 'src' => '', 'candidates' => []];
        }

        $inNo = $stripSpaces($in);

        $bestId = null;
        $bestScore = -1;
        $bestName = '';
        $cand = [];

        foreach ($outlets as $o) {
            $nmRaw = (string) $o->nama_outlet;
            $nm = $normalize($nmRaw);
            if ($nm === '') {
                continue;
            }

            $score = $diceBigram($inNo, $stripSpaces($nm)); // fokus bigram biar kuat untuk “tamanmini”, “kebonagung”, dll.

            $cand[] = ['name' => $nmRaw, 'score' => round($score, 2)];

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestId = (int) $o->id;
                $bestName = $nmRaw;
            }
        }

        usort($cand, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        $top = array_slice($cand, 0, 3);
        $topStr = [];
        foreach ($top as $t) {
            $topStr[] = $t['name'].' ('.$t['score'].'%)';
        }

        // threshold strict
        $threshold = 55;

        if ($bestScore < $threshold) {
            return ['id' => null, 'score' => round($bestScore, 2), 'src' => $bestName, 'candidates' => $topStr];
        }

        return ['id' => $bestId, 'score' => round($bestScore, 2), 'src' => $bestName, 'candidates' => $topStr];
    }

    // DATA GO / RTO
    public function dataRTO(Request $request)
    {
        return view('Investor.Master.dataRTO');
    }

    private function parseYm(?string $v): ?string
    {
        $v = trim((string)$v);
        if ($v === '') return null;
        if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $v)) return $v;
        return null;
    }

    private function toInt($v): int
    {
        if ($v === null || $v === '') return 0;
        return (int) preg_replace('/[^0-9\-]/', '', (string)$v);
    }

    private function toFloatOrNull($v): ?float
    {
        if ($v === null || $v === '') return null;
        // allow "12,5" -> 12.5
        $s = str_replace(',', '.', (string)$v);
        $s = preg_replace('/[^0-9\.\-]/', '', $s);
        if ($s === '' || $s === '.' || $s === '-' ) return null;
        return (float)$s;
    }

    private function monthNameToNumber(?string $bulan): ?int
    {
        $b = strtolower(trim((string)$bulan));
        if ($b === '') return null;

        $map = [
            'januari' => 1,
            'februari' => 2,
            'maret' => 3,
            'april' => 4,
            'mei' => 5,
            'juni' => 6,
            'juli' => 7,
            'agustus' => 8,
            'september' => 9,
            'oktober' => 10,
            'november' => 11,
            'desember' => 12,
        ];

        return $map[$b] ?? null;
    }

    private function parseNumberLoose($v): ?float
    {
        if ($v === null) return null;
        $s = trim((string)$v);
        if ($s === '') return null;

        $s = str_replace([' ', '%'], '', $s);
        $s = str_replace(',', '.', $s);
        $s = preg_replace('/[^0-9\.\-]/', '', $s);

        if ($s === '' || $s === '.' || $s === '-') return null;
        return (float)$s;
    }

    // =================== DATA RTO (READY to OPEN) ===================
    public function dataRTOData(Request $request)
    {
        $year = $this->parseYear($request->get('year', ''));
    
        if (!$year) {
            return response()->json([
                'draw' => (int) $request->get('draw', 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }
    
        // ambil agregasi per bulan + MAX(id) untuk kebutuhan edit/update
        $rows = DB::table('tbl_opening_progress as t')
            ->selectRaw("
                t.bulan as bulan,
                MAX(t.id) as id,
                SUM(t.target) as target,
                SUM(t.plan_opening) as plan_opening,
                SUM(t.done) as done,
                SUM(t.on_schedule) as on_schedule,
                SUM(t.hold) as hold,
                SUM(t.rejected) as rejected,
                SUM(t.backlog) as backlog,
                ROUND(AVG(t.lead_time), 2) as lead_time,
                ROUND(AVG(t.percentage), 2) as percentage
            ")
            ->where('t.bulan', 'like', $year . '-%')
            ->groupBy('t.bulan')
            ->orderBy('t.bulan')
            ->get();
    
        // map hasil query: key = YYYY-MM
        $map = [];
        foreach ($rows as $r) {
            $map[$r->bulan] = $r;
        }
    
        // bikin 12 bulan fixed (Jan–Des)
        $data = [];
        for ($m = 1; $m <= 12; $m++) {
            $ym = sprintf('%s-%02d', $year, $m);
    
            if (isset($map[$ym])) {
                $r = $map[$ym];
            } else {
                // bulan kosong -> tidak ada id -> tidak bisa update
                $r = (object) [
                    'id' => null,
                    'bulan' => $ym,
                    'target' => 0,
                    'plan_opening' => 0,
                    'done' => 0,
                    'on_schedule' => 0,
                    'hold' => 0,
                    'rejected' => 0,
                    'backlog' => 0,
                    'lead_time' => null,
                    'percentage' => null,
                ];
            }
    
            $data[] = $r;
        }
    
        return DataTables::of(collect($data))
            ->addIndexColumn()
            ->editColumn('percentage', function ($r) {
                if ($r->percentage === null || $r->percentage === '') return '-';
                $val = (float) $r->percentage;
                return rtrim(rtrim(number_format($val, 2, '.', ''), '0'), '.') . '%';
            })
            ->addColumn('aksi', function ($r) {
                // kalau bulan kosong (id null) -> tampilkan tombol tambah / atau "-"
                if (!$r->id) {
                    return '-';
                }
    
                return '
                    <button class="btn btn-sm btn-outline-primary btn-edit"
                        data-bs-toggle="modal" data-bs-target="#editModal"
                        data-id="' . e($r->id) . '"
                        data-bulan="' . e($r->bulan) . '"
                        data-target="' . e($r->target) . '"
                        data-plan_opening="' . e($r->plan_opening) . '"
                        data-done="' . e($r->done) . '"
                        data-on_schedule="' . e($r->on_schedule) . '"
                        data-hold="' . e($r->hold) . '"
                        data-rejected="' . e($r->rejected) . '"
                        data-backlog="' . e($r->backlog) . '"
                        data-lead_time="' . e($r->lead_time) . '"
                        data-percentage="' . e($r->percentage) . '"
                    >Ubah</button>
                ';
            })
            ->rawColumns(['aksi'])
            ->toJson();
    }

    private function parseYmRTO(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') return null;
    
        if (preg_match('/^(19|20)\d{2}-(0[1-9]|1[0-2])$/', $value)) return $value;
        return null;
    }
    
    private function parseYear(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') return null;
    
        if (preg_match('/^(19|20)\d{2}$/', $value)) return $value;
        return null;
    }

    public function downloadOpeningTemplate()
    {
        return response()->download(storage_path('app/template/template_opening_progress.xlsx'));
    }

    private function parseDateToYmd($v): ?string
    {
        if ($v === null || trim((string)$v) === '') return null;

        // kalau dari excel berupa angka serial
        if (is_numeric($v)) {
            try {
                $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($v);
                return Carbon::instance($dt)->toDateString();
            } catch (\Throwable $e) {
                return null;
            }
        }

        // kalau string: "01/01/2025" atau "2025-01-01"
        try {
            return Carbon::parse((string)$v)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    // =================== IMPORT ===================
    public function importOpeningProgress(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        $sheets = Excel::toArray([], $request->file('file'));
        $rows   = $sheets[0] ?? [];

        if (!$rows || count($rows) <= 1) {
            return back()->with('error', 'Sheet kosong. Gunakan template resmi.');
        }

        $inserts = [];

        foreach ($rows as $i => $r) {
            if ($i === 0) continue; // header
            if (empty(array_filter($r, fn($x) => $x !== null && trim((string)$x) !== ''))) continue;

            // Template:
            // Bulan | Tanggal | Target | Plan Opening | Done | On Schedule | Hold | Rejected | Backlog | Lead Time | %
            $r = array_pad($r, 11, null);

            [
                $bulanNama,
                $tanggal,
                $target,
                $plan,
                $done,
                $on,
                $hold,
                $rej,
                $backlog,
                $lead,
                $pct
            ] = $r;

            // convert bulan nama -> nomor bulan
            $m = $this->monthNameToNumber($bulanNama);
            if (!$m) continue;

            // parse tanggal
            $tanggalVal = $this->parseDateToYmd($tanggal);
            if (!$tanggalVal) continue; // kalau tanggal wajib, biar aman

            // ambil tahun dari tanggal excel
            $tahun = (int) Carbon::parse($tanggalVal)->format('Y');

            // simpan bulan format YYYY-MM
            $bulanYm = sprintf('%04d-%02d', $tahun, $m);

            // parse angka decimal (koma/persen)
            $targetF = $this->parseNumberLoose($target);
            $planF   = $this->parseNumberLoose($plan);
            $doneF   = $this->parseNumberLoose($done);
            $onF     = $this->parseNumberLoose($on);
            $holdF   = $this->parseNumberLoose($hold);
            $rejF    = $this->parseNumberLoose($rej);
            $backF   = $this->parseNumberLoose($backlog);
            $leadF   = $this->parseNumberLoose($lead);
            $pctF    = $this->parseNumberLoose($pct);

            $inserts[] = [
                'bulan'        => $bulanYm,        // contoh 2025-01
                'tanggal'      => $tanggalVal,     // contoh 2025-01-01
                'target'       => $targetF ?? 0,
                'plan_opening' => $planF ?? 0,
                'done'         => $doneF ?? 0,
                'on_schedule'  => $onF ?? 0,
                'hold'         => $holdF ?? 0,
                'rejected'     => $rejF ?? 0,
                'backlog'      => $backF ?? 0,
                'lead_time'    => $leadF,
                'percentage'   => $pctF,
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
        }

        if (!$inserts) {
            return back()->with('error', 'Tidak ada baris valid. Pastikan Bulan = Januari–Desember dan Tanggal valid.');
        }

        foreach (array_chunk($inserts, 500) as $chunk) {
            DB::table('tbl_opening_progress')->insert($chunk);
        }

        return back()->with('success', 'Import Opening Progress berhasil.');
    }

    // =================== STORE ===================
    public function storeOpeningProgress(Request $request)
    {
        $request->validate([
            'bulan' => ['required','regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'target' => ['nullable','integer'],
            'plan_opening' => ['nullable','integer'],
            'done' => ['nullable','integer'],
            'on_schedule' => ['nullable','integer'],
            'hold' => ['nullable','integer'],
            'rejected' => ['nullable','integer'],
            'backlog' => ['nullable','integer'],
            'lead_time' => ['nullable','numeric'],   // ✅ FIX
            'percentage' => ['nullable','numeric'],
        ]);
    
        DB::table('tbl_opening_progress')->insert([
            'bulan' => $request->bulan,
            'target' => (int)($request->target ?? 0),
            'plan_opening' => (int)($request->plan_opening ?? 0),
            'done' => (int)($request->done ?? 0),
            'on_schedule' => (int)($request->on_schedule ?? 0),
            'hold' => (int)($request->hold ?? 0),
            'rejected' => (int)($request->rejected ?? 0),
            'backlog' => (int)($request->backlog ?? 0),
    
            // ✅ jangan cast int (konsisten dengan AVG & input numeric)
            'lead_time' => ($request->lead_time === null || $request->lead_time === '') ? null : (float)$request->lead_time,
    
            'percentage' => ($request->percentage === null || $request->percentage === '') ? null : (float)$request->percentage,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    
        return back()->with('success', 'Data berhasil ditambah.');
    }

    // =================== UPDATE ===================
    public function updateOpeningProgress(Request $request, $id)
    {
        $request->validate([
            'bulan' => ['required','regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'target' => ['nullable','integer'],
            'plan_opening' => ['nullable','integer'],
            'done' => ['nullable','integer'],
            'on_schedule' => ['nullable','integer'],
            'hold' => ['nullable','integer'],
            'rejected' => ['nullable','integer'],
            'backlog' => ['nullable','integer'],
            'lead_time' => ['nullable','numeric'],   // ✅ FIX
            'percentage' => ['nullable','numeric'],
        ]);
    
        $affected = DB::table('tbl_opening_progress')
            ->where('id', $id)
            ->update([
                'bulan' => $request->bulan,
                'target' => (int)($request->target ?? 0),
                'plan_opening' => (int)($request->plan_opening ?? 0),
                'done' => (int)($request->done ?? 0),
                'on_schedule' => (int)($request->on_schedule ?? 0),
                'hold' => (int)($request->hold ?? 0),
                'rejected' => (int)($request->rejected ?? 0),
                'backlog' => (int)($request->backlog ?? 0),
    
                // kalau mau simpan decimal, jangan cast int
                'lead_time' => ($request->lead_time === null || $request->lead_time === '') ? null : (float)$request->lead_time,
    
                'percentage' => ($request->percentage === null || $request->percentage === '') ? null : (float)$request->percentage,
                'updated_at' => now(),
            ]);
    
        return $affected
            ? back()->with('success', 'Data berhasil diupdate.')
            : back()->with('error', 'Update gagal: data tidak ditemukan.');
    }

    // DATA INVESTOR (EBITDA)
    public function dataEbitda(Request $request)
    {
        $period = $request->get('period', now()->format('Y-m'));

        $outlets = DB::table('tbl_outlets')
            ->select('id', 'nama_outlet')
            ->orderBy('nama_outlet')
            ->get();

        return view('Investor.Master.dataEbitda', [
            'period' => $period,
            'outlets' => $outlets,
        ]);
    }

    private function calcHppQcr(int $outletId, string $tanggal): float
    {
        $bahan = DB::table('tbl_bahan')->get();

        $transaksi = DB::table('tbl_transaksi_perhari')
            ->where('outlet_id', $outletId)
            ->whereDate('sesi_tanggal', $tanggal)
            ->get();

        if ($transaksi->isEmpty()) {
            return 0;
        }

        $menuMapping = [
            'Extra Saos Korea'    => 'EXTRA SAOS GANGNAM',
            'PAKET GANGNAM HEMAT' => 'Paket Gangnam Hemat',
            'PAKET GANGNAM JUMBO' => 'Paket Gangnam Jumbo',
            'STRAWBERRY'          => 'Strawbery',
        ];

        $menuData = [];

        foreach ($transaksi as $t) {
            $itemNama = $menuMapping[$t->item_nama] ?? $t->item_nama;
            $qty = (float) $t->item_jumlah;

            $menu = DB::table('tbl_menu')
                ->where('item_produk', 'LIKE', "%{$itemNama}%")
                ->first();

            if (! $menu) continue;

            $bahanMenu = DB::table('tbl_menu_bahan as mb')
                ->join('tbl_bahan as b', 'b.id', '=', 'mb.bahan_id')
                ->select('b.nama_bahan','b.harga_bahan','b.isi_per_unit','b.konversi','b.satuan','mb.qty')
                ->where('mb.menu_id', $menu->id)
                ->get();

            foreach ($bahanMenu as $bhn) {
                $pakai = $bhn->qty * $qty;

                $gram = match (strtolower($bhn->satuan)) {
                    'kg' => $pakai * 1000,
                    'liter' => $pakai * 1000,
                    default => $pakai * ($bhn->konversi ?: 1),
                };

                $menuData[$bhn->nama_bahan] =
                    ($menuData[$bhn->nama_bahan] ?? 0) + $gram;
            }
        }

        $totalHpp = 0;

        foreach ($menuData as $namaBahan => $qtyGram) {
            $bhn = $bahan->firstWhere('nama_bahan', $namaBahan);
            if (! $bhn) continue;

            $hargaPerGram =
                ($bhn->harga_bahan / max($bhn->isi_per_unit,1))
                / max($bhn->konversi,1);

            $totalHpp += $hargaPerGram * $qtyGram;
        }

        return $totalHpp;
    }

    public function dataEbitdaData(Request $request)
    {
        // kalau belum klik cari, jangan hitung apa-apa (biar cepat)
        $hasFilter = (int) $request->get('hasFilter', 0) === 1;
        if (! $hasFilter) {
            return DataTables::of(collect([]))->toJson();
        }

        $period = $request->get('period', now()->format('Y-m'));
        $start  = Carbon::createFromFormat('Y-m', $period)->startOfMonth();
        $end    = Carbon::createFromFormat('Y-m', $period)->endOfMonth();
        $periodMonth = $start->toDateString(); // YYYY-MM-01

        $outletId = $request->get('outlet_id');

        // =========================
        // OUTLET SCOPE (join area+mitra)
        // =========================
        $outletsQ = DB::table('tbl_outlets as o')
            ->leftJoin('tbl_area_outlet as a', 'a.id', '=', 'o.area_id')
            ->leftJoin('tbl_mitra as m', 'm.id', '=', 'o.mitra_id')
            ->select(
                'o.id', 'o.nama_outlet', 'o.status',
                'a.nama_area', 'm.nama_mitra'
            );

        if ($outletId) {
            $outletsQ->where('o.id', $outletId);
        }

        $outlets = $outletsQ->orderBy('o.nama_outlet')->get();
        if ($outlets->isEmpty()) {
            return DataTables::of(collect([]))->toJson();
        }

        $outletIds = $outlets->pluck('id')->map(fn($v)=>(string)$v)->all();

        // =========================
        // SALES cepat (tbl_laporan_bulanan)
        // =========================
        $salesByOutlet = DB::table('tbl_laporan_bulanan')
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->whereIn('outlet_id', $outletIds)
            ->selectRaw('outlet_id, SUM(total_omset) as sales')
            ->groupBy('outlet_id')
            ->pluck('sales','outlet_id')
            ->map(fn($v)=>(float)$v)
            ->toArray();

        // =========================
        // FINANCE (OPEX + target) bulan itu
        // =========================
        $financeRows = DB::table('tbl_finance_outlet_monthly')
            ->whereIn('outlet_id', $outletIds)
            ->where('period_month', $periodMonth)
            ->get();

        $financeByOutlet = [];
        foreach ($financeRows as $f) {
            $opexFallback =
                (float)($f->beban_marketing ?? 0) +
                (float)($f->admin_shopee ?? 0) +
                (float)($f->penjualan ?? 0) +
                (float)($f->gaji ?? 0) +
                (float)($f->administrasi ?? 0);

            $opexUsed = ((float)($f->opex ?? 0) > 0) ? (float)$f->opex : $opexFallback;

            $financeByOutlet[(string)$f->outlet_id] = [
                'opex' => (float)$opexUsed,
                'target' => (float)($f->ebitda_target ?? 0),
            ];
        }

        // =========================
        // HPP pakai PARETO (cara BOD)
        // =========================
        $menuMapping = [
            'Extra Saos Korea'    => 'EXTRA SAOS GANGNAM',
            'PAKET GANGNAM HEMAT' => 'Paket Gangnam Hemat',
            'PAKET GANGNAM JUMBO' => 'Paket Gangnam Jumbo',
            'STRAWBERRY'          => 'Strawbery',
        ];

        $norm = function ($s) {
            $s = strtolower(trim((string) $s));
            $s = str_ireplace('express', '', $s);
            $s = preg_replace('/[^a-z0-9]+/i', ' ', $s);
            $s = preg_replace('/\s+/', ' ', $s);
            return trim($s);
        };

        // ambil pareto 1x saja (bulan ini)
        $pareto = DB::table('tbl_laporan_pareto')
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->whereIn('outlet_id', $outletIds)
            ->selectRaw('outlet_id, item_nama, SUM(total_jumlah) as qty_sold')
            ->groupBy('outlet_id', 'item_nama')
            ->get();

        // preload tbl_menu 1x
        $menus = DB::table('tbl_menu')->select('id','item_produk')->get();
        $menuNormToId = [];
        foreach ($menus as $m) {
            $k = $norm($m->item_produk);
            if ($k !== '' && !isset($menuNormToId[$k])) {
                $menuNormToId[$k] = (int)$m->id;
            }
        }

        $findMenuId = function (string $itemNama) use ($menuMapping, $norm, $menuNormToId) {
            $raw = trim($itemNama);
            $mapped = $menuMapping[$raw] ?? $raw;
            $key = $norm($mapped);

            // exact
            if ($key !== '' && isset($menuNormToId[$key])) {
                return $menuNormToId[$key];
            }

            // fallback contains (scan key yang mirip) -> masih jauh lebih ringan daripada query per row
            foreach ($menuNormToId as $k => $id) {
                if ($k !== '' && (str_contains($k, $key) || str_contains($key, $k))) {
                    return $id;
                }
            }

            return null;
        };

        // cache cost per menuId (query tbl_menu_bahan hanya sekali per menu)
        $menuCostById = [];

        $getMenuCost = function (int $menuId) use (&$menuCostById) {
            if (array_key_exists($menuId, $menuCostById)) return $menuCostById[$menuId];

            $rows = DB::table('tbl_menu_bahan as mb')
                ->join('tbl_bahan as b', 'b.id', '=', 'mb.bahan_id')
                ->where('mb.menu_id', $menuId)
                ->select('b.harga_bahan','b.isi_per_unit','b.konversi','mb.qty')
                ->get();

            if ($rows->isEmpty()) {
                $menuCostById[$menuId] = 0.0;
                return 0.0;
            }

            $cost = 0.0;
            foreach ($rows as $r) {
                $harga = (float)($r->harga_bahan ?? 0);
                $isi   = (float)($r->isi_per_unit ?? 1);
                $konv  = (float)($r->konversi ?? 1);
                $qty   = (float)($r->qty ?? 0);

                if ($isi <= 0)  $isi = 1;
                if ($konv <= 0) $konv = 1;

                $hargaPerUnitKecil = ($harga / $isi) / $konv;
                $cost += ($qty * $hargaPerUnitKecil);
            }

            $menuCostById[$menuId] = $cost;
            return $cost;
        };

        $hppByOutlet = [];
        foreach ($pareto as $p) {
            $oid = (string)$p->outlet_id;
            $qtySold = (float)($p->qty_sold ?? 0);
            if ($qtySold <= 0) continue;

            $menuId = $findMenuId((string)$p->item_nama);
            if (! $menuId) continue;

            $costPerMenu = $getMenuCost((int)$menuId);
            if ($costPerMenu <= 0) continue;

            $hppByOutlet[$oid] = ($hppByOutlet[$oid] ?? 0) + ($qtySold * $costPerMenu);
        }

        // =========================
        // BUILD ROWS
        // EBITDA = (Sales - HPP) - OPEX
        // =========================
        $rows = [];
        foreach ($outlets as $o) {
            $oid = (string)$o->id;

            $sales = (float)($salesByOutlet[$oid] ?? 0);
            $hpp   = (float)($hppByOutlet[$oid] ?? 0);

            $opex  = (float)($financeByOutlet[$oid]['opex'] ?? 0);
            $gross = $sales - $hpp;
            $ebitda = $gross - $opex;

            $margin = $sales > 0 ? round(($ebitda / $sales) * 100, 2) : 0;

            $rows[] = [
                'outlet_id'   => $o->id,
                'period_month'=> $start->translatedFormat('M Y'),
                'nama_area'   => $o->nama_area ?? '-',
                'nama_mitra'  => $o->nama_mitra ?? '-',
                'nama_outlet' => $o->nama_outlet,
                'sales'       => $sales,
                'hpp'         => $hpp,
                'ebitda'      => $ebitda,
                'margin'      => $margin,
                'status'      => $o->status ?? '-',
                'aksi'        => '-', // isi kalau ada tombol
            ];
        }

        return DataTables::of(collect($rows))
            ->addIndexColumn()
            ->editColumn('sales', fn($r)=>number_format($r['sales'],0,',','.'))
            ->editColumn('hpp', fn($r)=>number_format($r['hpp'],0,',','.'))
            ->editColumn('ebitda', fn($r)=>number_format($r['ebitda'],0,',','.'))
            ->editColumn('margin', fn($r)=>$r['margin'].'%') // simple (biar cepat)
            ->rawColumns(['margin'])
            ->toJson();
    }

    public function downloadEbitdaTemplate()
    {
        return response()->download(
            storage_path('app/template/template_import_finance_pnl_final.xlsx')
        );
    }

    public function importEbitda(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        $sheets = Excel::toArray([], $request->file('file'));

        // file kamu: [0]=README, [1]=Finance_Input, [2]=PnL_Detail
        $financeSheet = $sheets[1] ?? [];

        // ==========================
        // 1) PRELOAD OUTLET (CACHE)
        // ==========================
        $outlets = DB::table('tbl_outlets')->select('id', 'nama_outlet', 'kode_outlet')->get();

        $norm = function ($s) {
            $s = strtolower(trim((string) $s));
            $s = str_ireplace('express', '', $s);
            $s = preg_replace('/[^a-z0-9]+/i', ' ', $s);
            $s = preg_replace('/\s+/', ' ', $s);

            return trim($s);
        };

        $outletMap = [];
        foreach ($outlets as $o) {
            $k1 = $norm($o->nama_outlet);
            if ($k1 !== '' && ! isset($outletMap[$k1])) {
                $outletMap[$k1] = (int) $o->id;
            }

            if (! empty($o->kode_outlet)) {
                $outletMap[$norm($o->kode_outlet)] = (int) $o->id;
            }
        }

        $resolveOutletFast = function ($namaOutlet) use ($norm, $outletMap) {
            $k = $norm($namaOutlet);

            return $outletMap[$k] ?? null;
        };

        // ==========================
        // 2) PARSE PERIOD
        // ==========================
        $parsePeriodMonth = function ($period) {
            $period = is_string($period) ? trim($period) : $period;

            if (is_string($period) && preg_match('/^\d{4}-\d{2}$/', $period)) {
                return \Carbon\Carbon::createFromFormat('Y-m', $period)->startOfMonth()->toDateString();
            }

            if ($period instanceof \DateTimeInterface) {
                return \Carbon\Carbon::instance($period)->startOfMonth()->toDateString();
            }

            if (is_numeric($period)) {
                try {
                    $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($period);

                    return \Carbon\Carbon::instance($dt)->startOfMonth()->toDateString();
                } catch (\Throwable $e) {
                    return null;
                }
            }

            return null;
        };

        // ==========================
        // 3) READ & UPSERT
        // Finance_Input columns:
        // [0] period, [1] nama_outlet,
        // [2] beban_marketing, [3] admin_shopee, [4] penjualan,
        // [5] gaji, [6] administrasi, [7] depresiasi, [8] amortisasi,
        // [9] ebitda_target
        // ==========================
        DB::beginTransaction();
        try {
            $upserts = [];

            foreach ($financeSheet as $i => $row) {
                if ($i === 0) {
                    continue;
                } // header
                if (empty(array_filter($row, fn ($v) => $v !== null && $v !== ''))) {
                    continue;
                }

                [
                    $period,
                    $namaOutlet,
                    $bebanMarketing,
                    $adminShopee,
                    $penjualan,
                    $gaji,
                    $administrasi,
                    $depresiasi,
                    $amortisasi,
                    $ebitdaTarget
                ] = array_pad($row, 10, null);

                $periodMonth = $parsePeriodMonth($period);
                if (! $periodMonth) {
                    continue;
                }

                $outletId = $resolveOutletFast($namaOutlet);
                if (! $outletId) {
                    continue;
                }

                $bebanMarketing = (float) ($bebanMarketing ?? 0);
                $adminShopee = (float) ($adminShopee ?? 0);
                $penjualan = (float) ($penjualan ?? 0);
                $gaji = (float) ($gaji ?? 0);
                $administrasi = (float) ($administrasi ?? 0);
                $depresiasi = (float) ($depresiasi ?? 0);
                $amortisasi = (float) ($amortisasi ?? 0);

                // OPEX dihitung otomatis biar konsisten
                $opex = $bebanMarketing + $adminShopee + $penjualan + $gaji + $administrasi;

                $upserts[] = [
                    'outlet_id' => $outletId,
                    'period_month' => $periodMonth,

                    'beban_marketing' => $bebanMarketing,
                    'admin_shopee' => $adminShopee,
                    'penjualan' => $penjualan,
                    'gaji' => $gaji,
                    'administrasi' => $administrasi,

                    'opex' => $opex,
                    'depresiasi' => $depresiasi,
                    'amortisasi' => $amortisasi,

                    'ebitda_target' => ($ebitdaTarget !== null && $ebitdaTarget !== '') ? (float) $ebitdaTarget : null,

                    'source_profit' => 'manual',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            foreach (array_chunk($upserts, 500) as $chunk) {
                DB::table('tbl_finance_outlet_monthly')->upsert(
                    $chunk,
                    ['outlet_id', 'period_month'],
                    [
                        'beban_marketing',
                        'admin_shopee',
                        'penjualan',
                        'gaji',
                        'administrasi',
                        'opex',
                        'depresiasi',
                        'amortisasi',
                        'ebitda_target',
                        'source_profit',
                        'updated_at',
                    ]
                );
            }

            DB::commit();

            return back()->with('success', 'Import finance monthly berhasil (kolom baru).');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }
    
    public function startSyncAllEsbOutlets()
    {
        if (Cache::has('outlet_sync_multi_lock')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Masih ada sync yang sedang berjalan.',
            ], 409);
        }

        $syncKey = 'outlet-sync-' . Str::uuid()->toString();

        Cache::put('outlet_sync_multi_lock', 1, now()->addHours(4));

        Cache::put("outlet_sync_multi:{$syncKey}", [
            'status' => 'queued',
            'message' => 'Sync outlet semua credential masuk antrian.',
            'total_credentials' => 0,
            'processed_credentials' => 0,
            'success_credentials' => 0,
            'failed_credentials' => 0,
            'total_inserted' => 0,
            'total_updated' => 0,
            'total_skipped' => 0,
            'total_failed_rows' => 0,
            'progress' => 0,
            'logs' => [],
            'per_credential' => [],
            'updated_at' => now()->toDateTimeString(),
        ], now()->addHours(6));

        DispatchSyncEsbAllCredentialsJob::dispatch($syncKey)
            ->onConnection('redis')
            ->onQueue('esb-sync');

        return response()->json([
            'status' => 'queued',
            'message' => 'Sync outlet semua credential sedang diproses di background.',
            'sync_key' => $syncKey,
        ]);
    }
    
    public function syncAllEsbOutletsStatus(string $key)
    {
        $data = Cache::get("outlet_sync_multi:{$key}");
    
        if (!$data) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Status sync tidak ditemukan atau sudah expired.',
            ], 404);
        }
    
        if (($data['status'] ?? null) === 'done' || ($data['status'] ?? null) === 'failed') {
            Cache::forget('outlet_sync_multi_lock');
        }
    
        return response()->json($data);
    }
    
    public function login($credential)
    {
        $response = Http::post('https://services.esb.co.id/core/login', [
            'username' => $credential->username,
            'password' => $credential->password,
        ]);
    
        if (!$response->successful()) {
            throw new \Exception('Login gagal: ' . $credential->credential_code);
        }
    
        $data = $response->json();
    
        DB::table('tbl_api_sessions')->updateOrInsert(
            ['credential_id' => $credential->id],
            [
                'bearer_token' => $data['accessToken'] ?? null,
                'refresh_token' => $data['refreshToken'] ?? null,
                'company_code' => $credential->credential_code,
                'updated_at' => now(),
            ]
        );
    
        return $data['accessToken'] ?? null;
    }
    
    public function startSyncSalesEsbAll(Request $request)
    {
        $request->validate([
            'sales_date' => ['required', 'date'],
        ]);

        $startLock = Cache::lock('sales_sync_all_start_lock', 10);

        if (! $startLock->get()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Masih ada proses start sync sales all branch yang sedang berjalan.',
            ], 409);
        }

        try {
            $activeKey = Cache::get('sales_sync_all_active_key');

            if ($activeKey) {
                $existing = Cache::get("sales_sync_all:{$activeKey}");

                if ($existing && !in_array($existing['status'] ?? null, ['done', 'failed'])) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Masih ada sync sales yang sedang berjalan.',
                    ], 409);
                }

                if (! $existing) {
                    Cache::forget('sales_sync_all_active_key');
                }
            }

            $syncKey = 'sales-all-sync-' . Str::uuid()->toString();

            Cache::put('sales_sync_all_active_key', $syncKey, now()->addHours(12));

            Cache::put("sales_sync_all:{$syncKey}", [
                'status' => 'queued',
                'message' => 'Sync sales sequential all credential masuk antrian.',
                'sales_date' => $request->sales_date,

                'total_credentials' => 0,
                'processed_credentials' => 0,
                'success_credentials' => 0,
                'failed_credentials' => 0,

                'total_branches' => 0,
                'processed_branches' => 0,
                'success_branches' => 0,
                'failed_branches' => 0,

                'total_pages' => 0,
                'processed_pages' => 0,
                'success_pages' => 0,
                'failed_pages' => 0,

                'total_api_rows' => 0,
                'total_built_rows' => 0,
                'total_inserted_rows' => 0,

                'progress' => 0,
                'requested_at' => now()->toDateTimeString(),
                'started_at' => null,
                'finished_at' => null,
                'updated_at' => now()->toDateTimeString(),
                'logs' => [],
                'per_credential' => [],
                'per_branch' => [],
                'finalized' => false,
            ], now()->addHours(12));

            DispatchSyncSalesAllBranchesJob::dispatch($syncKey, $request->sales_date)
                ->onConnection('redis')
                ->onQueue('esb-sales');

            return response()->json([
                'status' => 'queued',
                'message' => 'Sync sales sequential all credential sedang diproses di background.',
                'sync_key' => $syncKey,
            ]);
        } finally {
            optional($startLock)->release();
        }
    }
    
    public function syncSalesEsbAllStatus(string $key)
    {
        $data = Cache::get("sales_sync_all:{$key}");
    
        if (! $data) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Status sync sales all branch tidak ditemukan / expired.',
            ], 404);
        }
    
        return response()->json($data);
    }
    
    // Testing Fuzzy Logic 
    public function index()
    {
        $users = DB::table('users')
            ->select('id', 'name', 'email', 'outlet_id', 'role')
            ->whereNull('outlet_id')
            ->where('role', 'crew') // 🔥 ini penting
            ->orderBy('name')
            ->get();

        $outlets = DB::table('tbl_outlets')
            ->select('id', 'nama_outlet')
            ->orderBy('nama_outlet', 'asc')
            ->get();

        return view('testing.testing', compact('users', 'outlets'));
    }

    public function save(Request $request)
    {
        $request->validate([
            'mappings' => 'required|array',
            'mappings.*.user_id' => 'required|integer|exists:users,id',
            'mappings.*.outlet_id' => 'nullable|integer|exists:tbl_outlets,id',
        ]);

        $updated = 0;

        foreach ($request->mappings as $mapping) {
            if (!empty($mapping['outlet_id'])) {
                DB::table('users')
                    ->where('id', $mapping['user_id'])
                    ->update([
                        'outlet_id' => $mapping['outlet_id'],
                        'updated_at' => now(),
                    ]);

                $updated++;
            }
        }

        return redirect()
            ->back()
            ->with('success', "Berhasil update {$updated} user.");
    }
    
    public function updateOutletTesting(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'outlet_id' => 'required|exists:tbl_outlets,id',
        ]);
    
        DB::table('users')
            ->where('id', $request->user_id)
            ->update([
                'outlet_id' => $request->outlet_id,
                'updated_at' => now(),
            ]);
    
        return response()->json([
            'status' => 'ok',
            'message' => 'Outlet user berhasil diupdate.'
        ]);
    }

    public function syncSalesSelectedOutlets(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'outlet_ids' => ['required', 'array', 'min:1', 'max:5'],
            'outlet_ids.*' => ['integer', 'exists:tbl_outlets,id'],
        ]);

        $syncKey = 'sales_selected_' . now()->format('YmdHis') . '_' . uniqid();

        SyncSalesSelectedOutletsJob::dispatch(
            $syncKey,
            $validated['outlet_ids'],
            $validated['start_date'],
            $validated['end_date']
        )->onConnection('redis')->onQueue('esb-sales');

        return response()->json([
            'status' => 'queued',
            'sync_key' => $syncKey,
            'message' => 'Job sync sales outlet pilihan berhasil dikirim.',
        ]);
    }

    public function syncSalesSelectedStatus(string $key)
    {
        return response()->json(
            Cache::store('redis')->get("sales_selected_sync:{$key}", [
                'status' => 'not_found',
                'message' => 'Status sync tidak ditemukan.',
                'progress' => 0,
            ])
        );
    }
}