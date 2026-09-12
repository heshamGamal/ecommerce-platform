<?php

return [
    'postmark' => ['key' => env('POSTMARK_API_KEY')],
    'resend' => ['key' => env('RESEND_API_KEY')],
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
    'paymob' => [
        'enabled' => (bool) env('PAYMOB_ENABLED', false),
        'base_url' => env('PAYMOB_BASE_URL', 'https://accept.paymob.com'),
        'secret_key' => env('PAYMOB_SECRET_KEY'),
        'public_key' => env('PAYMOB_PUBLIC_KEY'),
        'hmac_secret' => env('PAYMOB_HMAC_SECRET'),
        'integration_ids' => array_values(array_filter(array_map('intval', explode(',', (string) env('PAYMOB_INTEGRATION_IDS', ''))))),
        'notification_url' => env('PAYMOB_NOTIFICATION_URL'),
        'redirection_url' => env('PAYMOB_REDIRECTION_URL'),
        'timeout' => (int) env('PAYMOB_TIMEOUT', 15),
    ],
];
