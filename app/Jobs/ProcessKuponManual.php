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

class ProcessKuponManual implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    protected int $id;
    protected string $verifyMode; // 'auto' or 'manual'

    public function __construct(int $id, string $verifyMode = 'auto')
    {
        $this->id = $id;
        $this->verifyMode = $verifyMode;
    }

    public function handle(): void
    {
        $struk = DB::table('tbl_undian_struk')->where('id', $this->id)->first();

        if (! $struk) {
            Log::warning("[ProcessKuponManual] ID {$this->id} tidak ditemukan.");
            return;
        }

        $imagePath  = storage_path('app/public/' . $struk->foto_struk);
        $scriptPath = app_path('Scripts/ocr_kupon.py');
        $python     = env('PYTHON_BINARY', 'python3');

        if (! file_exists($imagePath)) {
            Log::error("[ProcessKuponManual] Gambar tidak ada: {$imagePath}");
            $this->markAs('need_review');
            return;
        }

        if (! file_exists($scriptPath)) {
            Log::error("[ProcessKuponManual] Script Python tidak ada: {$scriptPath}");
            $this->markAs('need_review');
            return;
        }

        $geminiKey = env('GEMINI_API_KEY', '');

        if (empty($geminiKey)) {
            Log::error("[ProcessKuponManual] GEMINI_API_KEY kosong!");
            $this->markAsNoDelete('need_review');
            return;
        }

        $command = sprintf(
            '%s %s %s %s %s 2>&1',
            escapeshellcmd($python),
            escapeshellarg($scriptPath),
            escapeshellarg($imagePath),
            escapeshellarg('0'),
            escapeshellarg($geminiKey)
        );

        Log::info("[ProcessKuponManual] Eksekusi ID {$this->id}");
        $output = shell_exec($command);
        Log::info("[ProcessKuponManual] Output Python ID {$this->id}: " . substr($output ?? 'NULL', 0, 800));

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
            Log::error("[ProcessKuponManual] OCR gagal ID {$this->id}: {$errMsg}");
            $this->markAsNoDelete('need_review');
            return;
        }

        $nomorStruk  = trim($result['nomor_struk'] ?? '');
        $namaLengkap = trim($result['nama_lengkap'] ?? '');
        $alamat      = trim($result['alamat'] ?? '');
        $noTelp      = trim($result['no_telp'] ?? '');
        $noKtp       = trim($result['no_ktp'] ?? '');

        Log::info("[ProcessKuponManual] OCR hasil ID {$this->id}: "
            . "nomor_struk={$nomorStruk}, nama={$namaLengkap}, "
            . "alamat={$alamat}, telp={$noTelp}, ktp={$noKtp}");

        if (empty($nomorStruk)) {
            $nomorStruk = 'MIGRASI_' . $this->id . '_' . time();
        }

        // Jika mode manual, kita selalu masukkan ke need_review
        if ($this->verifyMode === 'manual') {
            DB::table('tbl_undian_struk')->where('id', $this->id)->update([
                'nomor_struk'   => $nomorStruk,
                'nama_lengkap'  => $namaLengkap,
                'alamat'        => $alamat,
                'no_telp'       => $noTelp,
                'no_ktp'        => $noKtp,
                'status'        => 'need_review',
                'updated_at'    => now(),
            ]);
            Log::info("[ProcessKuponManual] ID {$this->id} diset need_review (mode manual).");
            return;
        }

        // Mode Auto - Generate nomor undian
        $nomorUndian = DB::transaction(function () use ($struk) {
            $maxAttempts = 20;
            for ($i = 0; $i < $maxAttempts; $i++) {
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
            return substr((string) (time() . random_int(10, 99)), -6);
        });

        $qrCode = url('/undian/validasi/' . $nomorUndian);

        DB::table('tbl_undian_struk')->where('id', $this->id)->update([
            'nomor_struk'   => $nomorStruk,
            'nama_lengkap'  => $namaLengkap,
            'alamat'        => $alamat,
            'no_telp'       => $noTelp,
            'no_ktp'        => $noKtp,
            'nomor_undian'  => $nomorUndian,
            'qr_code'       => $qrCode,
            'status'        => 'verified',
            'updated_at'    => now(),
        ]);

        $this->deleteImage($struk->foto_struk);

        Log::info("[ProcessKuponManual] ID {$this->id} verified -> undian #{$nomorUndian}");
    }

    private function deleteImage(?string $fotoPath): void
    {
        if (! $fotoPath) return;
        try {
            if (Storage::disk('public')->exists($fotoPath)) {
                Storage::disk('public')->delete($fotoPath);
                Log::info("[ProcessKuponManual] File dihapus: {$fotoPath}");
            }
        } catch (\Throwable $e) {
            Log::warning("[ProcessKuponManual] Gagal hapus file {$fotoPath}: " . $e->getMessage());
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
        Log::error("[ProcessKuponManual] Job ID {$this->id} final fail: " . $exception->getMessage());
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