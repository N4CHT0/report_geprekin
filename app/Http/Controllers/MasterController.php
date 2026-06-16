<?php

namespace App\Http\Controllers;

use App\Imports\OutletsImport;
use App\Imports\SalesImport;
use App\Imports\SalesPreviewImport;
use App\Imports\SalesImportQueued;
use App\Jobs\ImportSalesJob;
use App\Models\M_Outlet;
use App\Models\M_Sales;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessSalesImportJob;

class MasterController extends Controller
{
    public function dataOutlet()
    {
        $outlets = M_Outlet::orderBy('id', 'asc')->get();
        return view('Master.dataOutlet', compact('outlets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_outlet' => 'required|string|max:255',
            'status' => 'required|in:existing,new',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        DB::table('tbl_outlets')->insert([
            'nama_outlet' => $request->nama_outlet,
            'status'      => $request->status,
            'latitude'    => $request->latitude,
            'longitude'   => $request->longitude,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return redirect()->back()->with('success', 'Outlet berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_outlet' => 'required|string|max:255',
            'status'      => 'required|in:existing,go,tutup',
            'latitude'    => 'nullable|numeric',
            'longitude'   => 'nullable|numeric',
        ]);

        $outlet = M_Outlet::findOrFail($id);
        $outlet->update([
            'nama_outlet' => $request->nama_outlet,
            'status'      => $request->status,
            'latitude'    => $request->latitude,
            'longitude'   => $request->longitude,
        ]);

        return redirect()->route('outlet.master')->with('success', 'Outlet berhasil diupdate.');
    }

    public function destroy($id)
    {
        $outlet = M_Outlet::findOrFail($id);
        $outlet->delete();

        return redirect()->route('outlet.master')->with('success', 'Outlet berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            Excel::import(new OutletsImport, $request->file('file'));
            return redirect()->back()->with('success', 'Data outlet berhasil diimport!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    public function dataExisting()
    {
        return view('Master.dataExisting');
    }

    public function dataExistingData()
    {
        $sales = M_Sales::select(
            'tbl_transaksi_perhari.id',
            'tbl_transaksi_perhari.outlet_id',
            'tbl_transaksi_perhari.tr_metode',
            'tbl_transaksi_perhari.nomor',
            'tbl_transaksi_perhari.item_status',
            'tbl_transaksi_perhari.item_jumlah',
            'tbl_transaksi_perhari.item_sub_total',
            'tbl_transaksi_perhari.sesi_tanggal',
            'tbl_transaksi_perhari.tr_waktu',
            'tbl_outlets.nama_outlet',
            'tbl_outlets.status as outlet_status'
        )
            ->leftJoin('tbl_outlets', 'tbl_transaksi_perhari.outlet_id', '=', 'tbl_outlets.id')
            ->where('tbl_outlets.status', 'existing'); // hanya existing

        return DataTables::of($sales)
            ->addIndexColumn() // DT_RowIndex untuk nomor urut
            ->addColumn('nama_outlet', function ($row) {
                return $row->nama_outlet ?? 'ID: ' . $row->outlet_id;
            })
            ->addColumn('status', function ($row) {
                return $row->outlet_status ?? '-';
            })
            ->editColumn('sesi_tanggal', function ($row) {
                return $row->sesi_tanggal ? $row->sesi_tanggal->format('Y-m-d') : '-';
            })
            ->editColumn('tr_waktu', function ($row) {
                return $row->tr_waktu ? $row->tr_waktu->format('H:i:s') : '-';
            })
            ->editColumn('item_sub_total', function ($row) {
                return number_format($row->item_sub_total, 0, ',', '.');
            })
            ->editColumn('item_jumlah', function ($row) {
                return $row->item_jumlah ?? 0;
            })
            ->addColumn('action', function ($row) {
                return '
                <button class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Ubah</button>
                <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Hapus</button>
            ';
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    public function previewImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:102400|mimetypes:text/plain,text/csv,text/x-csv,application/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/octet-stream',
        ]);
    
        $file = $request->file('file');
    
        if (!$file) {
            return response()->json([
                'status' => 'error',
                'message' => 'File tidak ditemukan',
            ], 422);
        }
    
        try {
            $fileName = $file->getClientOriginalName();
            $storedPath = $file->store('imports/sales/preview', 'local');
            $previewKey = 'sales_preview_' . Str::uuid();
    
            Cache::put($previewKey, [
                'status' => 'queued',
                'message' => "File {$fileName} masuk antrian preview",
                'validRowsCount' => 0,
                'failedRows' => [],
                'progress' => 0,
            ], now()->addHours(6));
    
            ProcessSalesPreviewJob::dispatch(
                $storedPath,
                $fileName,
                $previewKey
            )->onQueue('imports');
    
            return response()->json([
                'status' => 'queued',
                'message' => "File {$fileName} masuk queue preview",
                'preview_key' => $previewKey,
            ], 202);
    
        } catch (\Throwable $e) {
            Log::error('Queue preview import sales gagal dibuat', [
                'message' => $e->getMessage(),
            ]);
    
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat proses preview',
            ], 500);
        }
    }
    
    public function dataSalesImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:102400',
        ]);
    
        try {
            $file = $request->file('file');
    
            if (!$file) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'File tidak ditemukan',
                ], 422);
            }
    
            $ext = strtolower($file->getClientOriginalExtension());
    
            if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Format file harus xlsx, xls, atau csv.',
                ], 422);
            }
    
            $fileName = $file->getClientOriginalName();
            $storedPath = $file->store('imports/sales', 'local');
            $importKey = 'sales_import_' . \Illuminate\Support\Str::uuid();
    
            \Illuminate\Support\Facades\Cache::put($importKey, [
                'status' => 'queued',
                'message' => "File {$fileName} masuk antrian",
                'fileName' => $fileName,
                'totalRows' => 0,
                'processedRows' => 0,
                'insertedRows' => 0,
                'skippedRows' => 0,
                'failedCount' => 0,
                'failedRows' => [],
                'progress' => 0,
                'totalItemSubTotal' => 0,
            ], now()->addHours(6));
    
            \Maatwebsite\Excel\Facades\Excel::queueImport(
                new \App\Imports\SalesImport($importKey, $fileName, $storedPath),
                $storedPath,
                'local'
            )->allOnQueue('imports');
    
            return response()->json([
                'status' => 'queued',
                'message' => "File {$fileName} masuk queue",
                'import_key' => $importKey,
            ], 202);
    
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Queue import sales gagal dibuat', [
                'message' => $e->getMessage(),
            ]);
    
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat proses import',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    public function dataSalesImportStatus($key)
    {
        $data = Cache::get($key);
    
        if (!$data) {
            return response()->json([
                'status' => 'error',
                'message' => 'Status import tidak ditemukan / expired',
            ], 404);
        }
    
        return response()->json($data);
    }
    
    public function importStatus($key)
    {
        $data = Cache::get($key);
    
        if (!$data) {
            return response()->json([
                'status' => 'error',
                'message' => 'Status import tidak ditemukan atau sudah expired',
            ], 404);
        }
    
        return response()->json($data);
}

    public function previewImportStatus($key)
    {
        $data = Cache::get($key);
    
        if (!$data) {
            return response()->json([
                'status' => 'error',
                'message' => 'Status preview tidak ditemukan / expired',
            ], 404);
        }
    
        return response()->json($data);
    }

    private function normalizeOutlet($str)
    {
        return strtolower(preg_replace('/\s+/', ' ', trim($str)));
    }

    public function commitImport(Request $request)
    {
        $request->validate([
            'filePath' => 'required|string',
        ]);

        try {
            $filePath = public_path($request->filePath);

            if (!File::exists($filePath)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'File tidak ditemukan di server. Silakan ulangi import.'
                ], 404);
            }

            // Import ke DB (gunakan Import class sesuai kebutuhan)
            Excel::import(new \App\Imports\OutletsImport, $filePath);

            // Hapus file sementara
            @unlink($filePath);

            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil diimport!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function dataGo()
    {
        return view('Master.dataGo');
    }

    public function dataGoData()
    {
        $sales = M_Sales::select(
            'tbl_transaksi_perhari.id',
            'tbl_transaksi_perhari.outlet_id',
            'tbl_transaksi_perhari.tr_metode',
            'tbl_transaksi_perhari.nomor',
            'tbl_transaksi_perhari.item_status',
            'tbl_transaksi_perhari.item_jumlah',
            'tbl_transaksi_perhari.item_sub_total',
            'tbl_transaksi_perhari.sesi_tanggal',
            'tbl_transaksi_perhari.tr_waktu',
            'tbl_outlets.nama_outlet',
            'tbl_outlets.status as outlet_status'
        )
            ->leftJoin('tbl_outlets', 'tbl_transaksi_perhari.outlet_id', '=', 'tbl_outlets.id')
            ->whereRaw("LOWER(TRIM(tbl_outlets.status)) = 'go'");
        // LOWER = biar tidak case sensitive
        // TRIM = biar spasi dihapus

        return DataTables::of($sales)
            ->addIndexColumn()
            ->addColumn('nama_outlet', function ($row) {
                return $row->nama_outlet ?? 'ID: ' . $row->outlet_id;
            })
            ->addColumn('status', function ($row) {
                return $row->outlet_status ?? '-';
            })
            ->editColumn('sesi_tanggal', function ($row) {
                return $row->sesi_tanggal ? \Carbon\Carbon::parse($row->sesi_tanggal)->format('Y-m-d') : '-';
            })
            ->editColumn('tr_waktu', function ($row) {
                return $row->tr_waktu ? \Carbon\Carbon::parse($row->tr_waktu)->format('H:i:s') : '-';
            })
            ->editColumn('item_sub_total', function ($row) {
                return number_format($row->item_sub_total, 0, ',', '.');
            })
            ->editColumn('item_jumlah', function ($row) {
                return $row->item_jumlah ?? 0;
            })
            ->addColumn('action', function ($row) {
                return '
                    <button class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Ubah</button>
                    <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Hapus</button>
                ';
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    public function dataEcom()
    {
        return view('Master.dataEcom');
    }
}
