<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramSiteScoreController extends Controller
{
    public function form()
    {
        $candidates = DB::table('surveyor_candidate_locations')
            ->orderByDesc('id')
            ->limit(300)
            ->get();

        return view('Surveyor.telegram.form', compact('candidates'));
    }

    public function formSubmit(Request $request)
    {
        $request->validate([
            'candidate_location_id' => ['nullable', 'integer'],
            'message_text' => ['required', 'string'],
        ]);

        $parsed = $this->parseMessage($request->message_text);
        $candidateId = $request->candidate_location_id ?: null;

        DB::table('surveyor_field_reports')->insert([
            'candidate_location_id' => $candidateId,
            'source' => 'FORM_BACKUP',
            'telegram_chat_id' => null,
            'telegram_message_id' => null,
            'raw_message' => $request->message_text,
            'parsed_payload' => json_encode($parsed),
            'status' => 'RECEIVED',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Laporan backup berhasil dicatat.');
    }

    public function webhook(Request $request)
    {
        $update = $request->all();
        $message = $update['message'] ?? null;

        if (!$message) {
            return response()->json(['ok' => true]);
        }

        $text = $message['text'] ?? $message['caption'] ?? '';
        $chatId = $message['chat']['id'] ?? null;
        $messageId = $message['message_id'] ?? null;

        if (!str_starts_with(trim($text), '/sitescore')) {
            return response()->json(['ok' => true]);
        }

        $parsed = $this->parseMessage($text);
        $kode = $parsed['kode'] ?? $parsed['kode_lokasi'] ?? null;
        $candidate = null;

        if ($kode) {
            $candidate = DB::table('surveyor_candidate_locations')
                ->where('kode_lokasi', $kode)
                ->first();
        }

        DB::table('surveyor_field_reports')->insert([
            'candidate_location_id' => $candidate->id ?? null,
            'source' => 'TELEGRAM',
            'telegram_chat_id' => $chatId,
            'telegram_message_id' => $messageId,
            'raw_message' => $text,
            'parsed_payload' => json_encode($parsed),
            'status' => $candidate ? 'MATCHED' : 'RECEIVED',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $reply = $candidate
            ? "Laporan diterima untuk {$candidate->kode_lokasi} - {$candidate->nama_lokasi}."
            : "Laporan diterima, tapi kode lokasi belum cocok dengan master kandidat.";

        $this->sendTelegramMessage($chatId, $reply);

        return response()->json(['ok' => true]);
    }

    private function parseMessage(string $text): array
    {
        $rows = preg_split('/\r\n|\r|\n/', $text);
        $data = [];

        foreach ($rows as $row) {
            $row = trim($row);

            if ($row === '' || str_starts_with($row, '/sitescore')) {
                continue;
            }

            if (!str_contains($row, ':')) {
                continue;
            }

            [$key, $value] = explode(':', $row, 2);
            $key = strtolower(trim($key));
            $key = str_replace([' ', '-'], '_', $key);
            $data[$key] = trim($value);
        }

        return $data;
    }

    private function sendTelegramMessage($chatId, string $text): void
    {
        $token = env('TELEGRAM_BOT_TOKEN');

        if (!$token || !$chatId) {
            return;
        }

        try {
            Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
            ]);
        } catch (\Throwable $e) {
            Log::error('Telegram send message failed: ' . $e->getMessage());
        }
    }
}
