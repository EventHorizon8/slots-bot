<?php

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'parsedSites' => [
        //look at \app\enum\CountryIso::enum
        'PT' => [
            'url' => env('PT_URL', ''),
            'strategy' => \app\services\PTStrategy::class,
        ],
    ]
];
