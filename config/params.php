<?php

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'parsedSites' => [
        //look at \app\enum\CountryIso::enum
        'PT' => [
            'url' => env('PT_URL', ''),
            'strategy' => \app\services\Strategy\PTStrategy::class,
        ],
    ],
    'telegramAdminId' => (string)env('TELEGRAM_ADMIN_ID', ''),
    'telegramToken' => env('TELEGRAM_BOT_TOKEN', ''),
    'telegramChatName' => env('TELEGRAM_BOT_NAME', ''),
];
