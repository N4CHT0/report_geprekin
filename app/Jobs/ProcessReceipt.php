<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessReceipt implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    protected int $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function handle(): void
    {
        $struk = DB::table('tbl_undian_struk')->where('id', $this->id)->first();

        if (! $struk) {
            Log::warning("[ProcessReceipt] ID {$this->id} tidak ditemukan.");
            return;
        }

        $imagePath  = storage_path('app/public/' . $struk->foto_struk);
        $scriptPath = app_path('Scripts/ocr_processor.py');
        $python     = env('PYTHON_BINARY', 'python3');

        if (! file_exists($imagePath)) {
            Log::error("[ProcessReceipt] Gambar tidak ada: {$imagePath}");
            $this->markAs('need_review');
            return;
        }

        if (! file_exists($scriptPath)) {
            Log::error("[ProcessReceipt] Script Python tidak ada: {$scriptPath}");
            $this->markAs('need_review');
            return;
        }

        $outlets = DB::table('tbl_outlets')
            ->select('id', 'nama_outlet')
            ->where('is_active', 1)
            ->get()
            ->toJson();

        $geminiKey = env('GEMINI_API_KEY', '');

        if (empty($geminiKey)) {
            Log::error("[ProcessReceipt] GEMINI_API_KEY kosong!");
            $this->markAsNoDelete('need_review');
            return;
        }

        $command = sprintf(
            '%s %s %s %s %s %s 2>&1',
            escapeshellcmd($python),
            escapeshellarg($scriptPath),
            escapeshellarg($imagePath),
            escapeshellarg($outlets),
            escapeshellarg('0'),
            escapeshellarg($geminiKey)
        );

        Log::info("[ProcessReceipt] Eksekusi ID {$this->id}");
        $output = shell_exec($command);
        Log::info("[ProcessReceipt] Output Python ID {$this->id}: " . substr($output ?? 'NULL', 0, 800));

        $result = null;
        if ($output) {
            foreach (explode("\n", trim($output)) as $line) {
                $line = trim($line);
                if (str_starts_with($line, '{')) {
                    $decoded = json_decode($line, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $result = $decoded;
                        break;
                    }
                }
            }
        }

        if (! $result || isset($result['error'])) {
            $errMsg = $result['error'] ?? 'Output Python kosong atau tidak valid';
            Log::error("[ProcessReceipt] OCR gagal ID {$this->id}: {$errMsg}");
            $this->markAsNoDelete('need_review');
            return;
        }

        $nomorStruk   = trim($result['nomor_struk'] ?? '');
        $totalBelanja = (int) ($result['total_belanja'] ?? 0);
        $outletId     = $result['outlet_id'] ?? $struk->outlet_id;
        $tanggalStruk = $result['tanggal_struk'] ?? null;
        $namaOutlet   = $result['nama_outlet'] ?? '';

        Log::info("[ProcessReceipt] OCR hasil ID {$this->id}: "
            . "nomor={$nomorStruk}, total={$totalBelanja}, "
            . "outlet_id={$outletId}, nama_outlet_ocr={$namaOutlet}");

        if (empty($nomorStruk)) {
            $nomorStruk = 'MANUAL_' . $this->id . '_' . time();
        }

        // Validasi minimal Rp 10.000
        if ($totalBelanja < 10_000) {
            Log::info("[ProcessReceipt] ID {$this->id} ditolak: Rp {$totalBelanja} < Rp 10.000");
            DB::table('tbl_undian_struk')->where('id', $this->id)->update([
                'status'        => 'failed_ocr',
                'total_belanja' => $totalBelanja,
                'nomor_struk'   => $nomorStruk,
                'outlet_id'     => $outletId ?: $struk->outlet_id,
                'updated_at'    => now(),
            ]);
            $this->deleteImage($struk->foto_struk);
            return;
        }

        // Anti-duplikat
        if (! str_starts_with($nomorStruk, 'MANUAL_')) {
            $isDuplicate = DB::table('tbl_undian_struk')
                ->where('nomor_struk', $nomorStruk)
                ->where('id', '!=', $this->id)
                ->where('status', 'verified')
                ->exists();

            if ($isDuplicate) {
                Log::warning("[ProcessReceipt] ID {$this->id} duplikat: {$nomorStruk}");
                DB::table('tbl_undian_struk')->where('id', $this->id)->update([
                    'status'      => 'failed_ocr',
                    'nomor_struk' => $nomorStruk,
                    'updated_at'  => now(),
                ]);
                $this->deleteImage($struk->foto_struk);
                return;
            }
        }

        // PHP fallback outlet matching
        if (! $outletId && ! empty($namaOutlet)) {
            $outletId = $this->matchOutletFallback($namaOutlet);
            if ($outletId) {
                Log::info("[ProcessReceipt] ID {$this->id} outlet matched via PHP fallback: {$outletId}");
            }
        }

        // Generate nomor undian — Random 6 digit unik, atomic
        $nomorUndian = DB::transaction(function () use ($struk) {
            $maxAttempts = 20;
            for ($i = 0; $i < $maxAttempts; $i++) {
                // Format: 6 digit random angka, e.g. 481920
                $candidate = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

                $exists = DB::table('tbl_undian_struk')
                    ->where('periode', $struk->periode)
                    ->where('nomor_undian', $candidate)
                    ->lockForUpdate()
                    ->exists();

                if (! $exists) {
                    return $candidate;
                }
            }
            // Fallback jika sangat penuh: timestamp-based unique
            return substr((string) (time() . random_int(10, 99)), -6);
        });

        DB::table('tbl_undian_struk')->where('id', $this->id)->update([
            'outlet_id'     => $outletId ?: $struk->outlet_id,
            'nomor_struk'   => $nomorStruk,
            'total_belanja' => $totalBelanja,
            'nomor_undian'  => $nomorUndian,
            'tanggal_struk' => $tanggalStruk ?: $struk->tanggal_struk,
            'status'        => 'verified',
            'updated_at'    => now(),
        ]);

        $this->deleteImage($struk->foto_struk);

        Log::info("[ProcessReceipt] ID {$this->id} verified -> undian #{$nomorUndian}");
    }

    private function matchOutletFallback(string $namaOutlet): ?int
    {
        $noise = [
            'geprek', 'ayam', 'warung', 'resto', 'rumah', 'makan',
            'cafe', 'kafe', 'dan', 'the', 'aja', 'kinaja', 'geprekin',
            'spesialis', 'spesial', 'and', 'by',
        ];

        $words    = preg_split('/\s+/', preg_replace('/[^A-Za-z0-9\s]/', ' ', $namaOutlet));
        $keywords = array_filter($words, fn($w) => strlen($w) >= 3 && ! in_array(strtolower($w), $noise));
        usort($keywords, fn($a, $b) => strlen($b) - strlen($a));

        foreach ($keywords as $kw) {
            $outlet = DB::table('tbl_outlets')
                ->where('is_active', 1)
                ->where('nama_outlet', 'LIKE', '%' . $kw . '%')
                ->select('id', 'nama_outlet')
                ->first();

            if ($outlet) {
                Log::info("[ProcessReceipt] Outlet matched via LIKE '%{$kw}%': {$outlet->nama_outlet} (id={$outlet->id})");
                return (int) $outlet->id;
            }
        }

        return null;
    }

    private function deleteImage(?string $fotoPath): void
    {
        if (! $fotoPath) return;
        try {
            if (Storage::disk('public')->exists($fotoPath)) {
                Storage::disk('public')->delete($fotoPath);
                Log::info("[ProcessReceipt] File dihapus: {$fotoPath}");
            }
        } catch (\Throwable $e) {
            Log::warning("[ProcessReceipt] Gagal hapus file {$fotoPath}: " . $e->getMessage());
        }
    }

    private function markAsNoDelete(string $status): void
    {
        DB::table('tbl_undian_struk')->where('id', $this->id)->update([
            'status'     => $status,
            'updated_at' => now(),
        ]);
    }

    private function markAs(string $status): void
    {
        $struk = DB::table('tbl_undian_struk')->where('id', $this->id)->first();
        DB::table('tbl_undian_struk')->where('id', $this->id)->update([
            'status'     => $status,
            'updated_at' => now(),
        ]);
        if ($struk) {
            $this->deleteImage($struk->foto_struk);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("[ProcessReceipt] Job ID {$this->id} final fail: " . $exception->getMessage());
        $struk = DB::table('tbl_undian_struk')->where('id', $this->id)->first();
        DB::table('tbl_undian_struk')->where('id', $this->id)->update([
            'status'     => 'need_review',
            'updated_at' => now(),
        ]);
        if ($struk) {
            $this->deleteImage($struk->foto_struk);
        }
    }
}