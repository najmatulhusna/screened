<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TelegramNotifier
{
    public static function send(string $text, ?int $threadId = null): bool
    {
        $token = config('services.telegram.token') ?? env('TELEGRAM_BOT_TOKEN');
        $chatId = config('services.telegram.chat_id') ?? env('TELEGRAM_CHAT_ID');

        if (!$token || !$chatId) {
            Log::error('Telegram Token atau Chat ID belum diset.');
            return false;
        }

        $payload = [
            'chat_id'       => $chatId,
            'text'          => mb_substr($text, 0, 4096),
            'parse_mode'    => 'HTML',
            'disable_web_page_preview' => true,
        ];

        if ($threadId) {
            $payload['message_thread_id'] = (int) $threadId;
        }

        try {
            $response = Http::timeout(5)
                ->post("https://api.telegram.org/bot{$token}/sendMessage", $payload);

            if (!$response->successful()) {
                Log::error('Gagal kirim Telegram: ' . $response->body());
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Exception Telegram: ' . $e->getMessage());
            return false;
        }
}
