<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'telegram' => [
        'token' => env('TELEGRAM_BOT_TOKEN'),
        'chat_id' => env('TELEGRAM_CHAT_ID'),
        // Default ID topic forum Telegram (3=Error, 2=Review) supaya notifikasi
        // tetap masuk ke topic yang benar saat env belum di-set di server.
        'thread_error' => (int) (env('TELEGRAM_THREAD_ERROR') ?: 3),
        'thread_review' => (int) (env('TELEGRAM_THREAD_REVIEW') ?: 2),
    ],

];
