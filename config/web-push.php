<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Web Push VAPID Keys
    |--------------------------------------------------------------------------
    |
    | Generate keys using: php -r "require 'vendor/autoload.php'; use Minishlink\WebPush\VAPID; print_r(VAPID::createVapidKeys());"
    |
    */
    'vapid' => [
        'public_key' => env('VAPID_PUBLIC_KEY', ''),
        'private_key' => env('VAPID_PRIVATE_KEY', ''),
        'subject' => env('VAPID_SUBJECT', 'mailto:notifications@example.com'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Web Push TTL
    |--------------------------------------------------------------------------
    */
    'ttl' => env('WEB_PUSH_TTL', 2419200), // 4 weeks in seconds

    /*
    |--------------------------------------------------------------------------
    | Web Push urgency
    |--------------------------------------------------------------------------
    */
    'urgency' => env('WEB_PUSH_URGENCY', 'normal'),

    /*
    |--------------------------------------------------------------------------
    | Web Push topic (for replacing existing notifications)
    |--------------------------------------------------------------------------
    */
    'topic' => env('WEB_PUSH_TOPIC', 'QuranSystem'),
];
