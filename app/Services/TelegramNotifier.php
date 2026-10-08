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

        $payload = [
            'chat_id' => $chatId,
            'text' => mb_substr($text, 0, 4096),
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ];

        if ($threadId) {
            $payload['message_thread_id'] = $threadId;
        } else {
            Log::warning('Telegram: thread_id kosong (config thread_error/review tidak terbaca) -> pesan masuk General');
        }

        try {
            $response = Http::timeout(5)
                ->post("https://api.telegram.org/bot{$token}/sendMessage", $payload);

            if (!$response->successful()) {
                Log::error('Telegram API HTTP '.$response->status().': '.mb_substr($response->body(), 0, 500));
            }

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('Telegram exception: '.$e->getMessage());
            return false;
        }
    }
}
