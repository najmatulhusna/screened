<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TelegramNotifier
{
    public static function send(string $text, ?int $threadId = null): bool
    {
        $token = config('services.telegram.token');
        $chatId = config('services.telegram.chat_id');

        if (!$token || !$chatId) {
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
        }

        try {
            return Http::timeout(5)
                ->post("https://api.telegram.org/bot{$token}/sendMessage", $payload)
                ->successful();
        } catch (\Throwable $e) {
            return false;
        }
    }
}
