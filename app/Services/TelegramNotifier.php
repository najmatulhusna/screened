<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotifier
{
    /**
     * Kirim pesan notifikasi ke Telegram Supergroup / Topic.
     */
    public static function send(string $text, ?int $threadId = null): bool
    {
        $token = config('services.telegram.token');
        $chatId = config('services.telegram.chat_id');

        if (!$token || !$chatId) {
            Log::error('Telegram: TELEGRAM_BOT_TOKEN / TELEGRAM_CHAT_ID belum terbaca config services.telegram');
            return false;
        }

        // Pastikan thread ID valid (integer positif)
        $hasValidThread = !is_null($threadId) && $threadId > 0;

        if ($hasValidThread) {
            $text .= "\n\n<i>— thread {$threadId}</i>";
        } else {
            $text .= "\n\n<i>⚠ THREAD KOSONG: config services.telegram.thread_* tidak terbaca atau ber-nilai 0, pesan masuk General</i>";
            Log::warning('Telegram: thread_id kosong/invalid -> pesan masuk ke General');
        }

        $payload = [
            'chat_id' => $chatId,
            'text' => mb_substr($text, 0, 4096),
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ];

        // Hanya tambahkan message_thread_id jika bernilai positif
        if ($hasValidThread) {
            $payload['message_thread_id'] = $threadId;
        }

        try {
            $response = Http::timeout(5)
                ->post("https://api.telegram.org/bot{$token}/sendMessage", $payload);

            if (!$response->successful()) {
                Log::error('Telegram API HTTP '.$response->status().': '.mb_substr($response->body(), 0, 500));
                self::report($token, $chatId, $hasValidThread ? $threadId : null, $response->status(), $response->body());
            }

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('Telegram exception: '.$e->getMessage());
            return false;
        }
    }

    /**
     * Laporkan kegagalan kirim ke chat utama (tanpa message_thread_id) agar
     * error API Telegram langsung terlihat.
     */
    private static function report(string $token, string $chatId, ?int $threadId, int $status, string $body): void
    {
        $decoded = json_decode($body, true);
        $desc = is_array($decoded) ? ($decoded['description'] ?? $body) : $body;

        $text = "❌ <b>Kirim notifikasi Telegram gagal</b>\n\n"
            . "<b>HTTP:</b> {$status}\n"
            . '<b>Thread:</b> '.($threadId ?: '(kosong/invalid)')."\n"
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