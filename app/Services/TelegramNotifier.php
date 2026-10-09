<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotifier
{
    public static function send(string $text, ?int $threadId = null): bool
    {
        $token = config('services.telegram.token');
        $chatId = config('services.telegram.chat_id');

        if (!$token || !$chatId) {
            Log::error('Telegram: TELEGRAM_BOT_TOKEN / TELEGRAM_CHAT_ID belum terbaca config services.telegram');
            return false;
        }

        if ($threadId) {
            $text .= "\n\n<i>— thread {$threadId}</i>";
        } else {
            $text .= "\n\n<i>⚠ THREAD KOSONG: config services.telegram.thread_* tidak terbaca, pesan masuk General</i>";
            Log::warning('Telegram: thread_id kosong (config thread_error/review tidak terbaca) -> pesan masuk General');
        }

        $payload = [
            'chat_id' => $chatId,
            'text' => mb_substr($text, 0, 4096),
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ];

        if ($threadId) {
            $payload['message_thread_id'] = $threadId;
        }

        try {
            $response = Http::timeout(5)
                ->post("https://api.telegram.org/bot{$token}/sendMessage", $payload);

            if (!$response->successful()) {
                Log::error('Telegram API HTTP '.$response->status().': '.mb_substr($response->body(), 0, 500));
                self::report($token, $chatId, $threadId, $response->status(), $response->body());
            }

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('Telegram exception: '.$e->getMessage());
            return false;
        }
    }

    /**
     * Laporkan kegagalan kirim ke chat (tanpa message_thread_id) supaya
     * error API Telegram langsung terlihat, bukan hanya di log yang
     * hilang tiap request di Wasmer.
     */
    private static function report(string $token, string $chatId, ?int $threadId, int $status, string $body): void
    {
        $decoded = json_decode($body, true);
        $desc = is_array($decoded) ? ($decoded['description'] ?? $body) : $body;

        $text = "❌ <b>Kirim notifikasi Telegram gagal</b>\n\n"
            . "<b>HTTP:</b> {$status}\n"
            . '<b>Thread:</b> '.($threadId ?: '(kosong)')."\n"
            . '<b>Alasan:</b> <pre>'.e(mb_substr((string) $desc, 0, 500)).'</pre>';

        try {
            Http::timeout(5)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => mb_substr($text, 0, 4096),
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ]);
        } catch (\Throwable $e) {
            Log::error('Telegram report gagal: '.$e->getMessage());
        }
    }
}
