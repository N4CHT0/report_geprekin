<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RollateController extends Controller
{
    public function index()
    {
        $periode = now()->format('Y-m');

        $participants = DB::table('tbl_undian_struk')
            ->select([
                DB::raw("CONCAT(nama_lengkap,' / ',no_telp) as username"),
                'nomor_undian as nomor',
                'nomor_struk'
            ])
            ->where('periode', $periode)
            ->where('status', 'verified')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('Rollate.spin', compact('participants'));
    }

    public function pendaftaran()
    {
        $outlets = DB::table('tbl_outlets')
            ->select('id', 'nama_outlet', 'kota')
            ->where('is_active', 1)
            ->orderBy('nama_outlet', 'asc')
            ->get();

        return view('Rollate.pendaftaran', compact('outlets'));
    }

    /**
     * Multi-upload: simpan beberapa struk sekaligus, dispatch job per struk.
     * Rate limit: 5 submission per IP per menit.
     */
    public function store(Request $request)
    {
        $key = 'undian_store_' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'success' => false,
                'message' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
            ], 429);
        }
        RateLimiter::hit($key, 60);

        $request->validate([
            'nama_lengkap'    => 'required|string|max:120',
            'no_telp'         => 'required|string|min:10|max:15',
            'foto_struk'      => 'required|array|min:1',
            'foto_struk.*'    => 'image|mimes:jpg,jpeg,png,webp|max:5120',
            'outlet_id'       => 'nullable|integer|exists:tbl_outlets,id',
            'tanggal_struk'   => 'nullable|date',
        ]);

        $periode = $request->tanggal_struk
            ? Carbon::parse($request->tanggal_struk)->format('Y-m')
            : now()->format('Y-m');

        $ids = [];

        try {
            foreach ($request->file('foto_struk') as $file) {
                $path = $file->store('undian/struk', 'public');

                $id = DB::table('tbl_undian_struk')->insertGetId([
                    'outlet_id'     => $request->outlet_id ?: null,
                    'nama_lengkap'  => trim($request->nama_lengkap),
                    'no_telp'       => preg_replace('/[^0-9]/', '', $request->no_telp),
                    'nomor_struk'   => 'PENDING_OCR_' . time() . '_' . rand(100, 999),
                    'total_belanja' => 0,
                    'foto_struk'    => $path,
                    'nomor_undian'  => 'PROSES',
                    'tanggal_struk' => $request->tanggal_struk ?: now()->toDateString(),
                    'periode'       => $periode,
                    'status'        => 'pending',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);

                \App\Jobs\ProcessReceipt::dispatch($id);
                $ids[] = $id;
            }

            return response()->json([
                'success' => true,
                'ids'     => $ids,
                'count'   => count($ids),
                'message' => count($ids) . ' struk berhasil diunggah dan sedang diproses.',
            ]);

        } catch (\Throwable $e) {
            Log::error('[RollateController::store] ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server. Silakan coba lagi.',
            ], 500);
        }
    }

    /**
     * Cek status:
     * - by IDs (array) → polling setelah submit multi-upload
     * - by nama_lengkap → pencarian
     * - by nomor_struk  → pencarian
     */
    public function checkStatus(Request $request)
    {
        $ids          = $request->ids;        // array id setelah submit
        $namaLengkap  = $request->nama_lengkap;
        $nomorStruk   = $request->nomor_struk;

        if (! $ids && ! $namaLengkap && ! $nomorStruk) {
            return response()->json([]);
        }

        $periode = now()->format('Y-m');

        $query = DB::table('tbl_undian_struk')
            ->select('id', 'nomor_struk', 'status', 'total_belanja', 'nomor_undian', 'nama_lengkap', 'created_at')
            ->where('periode', $periode);

        if ($ids && is_array($ids)) {
            $query->whereIn('id', $ids);
        } elseif ($nomorStruk) {
            // Cari by nomor_struk (exact & partial), exclude PENDING/MANUAL prefix
            $query->where(function ($q) use ($nomorStruk) {
                $q->where('nomor_struk', $nomorStruk)
                  ->orWhere('nomor_struk', 'LIKE', '%' . $nomorStruk . '%');
            })->where('nomor_struk', 'NOT LIKE', 'PENDING_OCR_%')
              ->orderBy('id', 'desc')
              ->limit(10);
        } elseif ($namaLengkap) {
            $query->where('nama_lengkap', 'LIKE', '%' . trim($namaLengkap) . '%')
                  ->orderBy('id', 'desc')
                  ->limit(10);
        }

        $struks = $query->get()->map(function ($item) {
            $item->status_label = match ($item->status) {
                'pending'     => 'Sedang diproses...',
                'verified'    => 'Berhasil Diverifikasi ✓',
                'failed_ocr'  => 'Struk Tidak Valid',
                'need_review' => 'Perlu Tinjauan Manual',
                default       => ucfirst($item->status),
            };

            $item->total_formatted = $item->total_belanja
                ? 'Rp ' . number_format($item->total_belanja, 0, ',', '.')
                : '-';

            return $item;
        });

        return response()->json($struks);
    }

    /**
     * Cetak kartu undian sebagai PDF.
     */
    public function cetakPDF($id)
    {
        $data = DB::table('tbl_undian_struk as us')
            ->leftJoin('tbl_outlets as o', 'o.id', '=', 'us.outlet_id')
            ->select(
                'us.*',
                DB::raw("COALESCE(o.nama_outlet, '-') as nama_outlet"),
                'o.kota as outlet_kota'
            )
            ->where('us.id', $id)
            ->where('us.status', 'verified')
            ->first();

        if (! $data) {
            abort(404, 'Data tidak ditemukan atau belum terverifikasi');
        }

        $pdf = Pdf::loadView('Rollate.cetak', ['data' => $data])
            ->setPaper([0, 0, 595, 250], 'portrait');

        return $pdf->download('Kartu-Undian-' . $data->nomor_undian . '.pdf');
    }
}