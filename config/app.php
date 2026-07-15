<?php
return [
    'name' => env('APP_NAME', 'نظام تسميع القرآن الكريم'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => 'Asia/Riyadh',
    'locale' => 'ar',
    'fallback_locale' => 'en',
    'faker_locale' => 'ar_SA',
    'cipher' => 'AES-256-CBC',
    'key' => env('APP_KEY'),
    'maintenance' => ['driver' => 'file'],

    // Custom center settings
    'center_name' => env('CENTER_NAME', 'مركز التسميع'),
    'center_email' => env('CENTER_EMAIL', ''),
    'center_phone' => env('CENTER_PHONE', ''),
    'work_start_time' => env('WORK_START_TIME', '08:00'),
    'work_end_time' => env('WORK_END_TIME', '20:00'),
    'work_days' => env('WORK_DAYS', 'saturday,sunday,monday,tuesday,wednesday,thursday'),
    'enable_notifications' => env('ENABLE_NOTIFICATIONS', true),

    // Third-party API keys
    'assemblyai' => [
        'api_key' => env('ASSEMBLYAI_API_KEY', ''),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY', ''),
        'model' => env('OPENAI_MODEL', 'gpt-4o'),
    ],

    'providers' => [
        Illuminate\Auth\AuthServiceProvider::class,
        App\Providers\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        App\Providers\SettingsServiceProvider::class,
    ],
    'aliases' => Illuminate\Support\Facades\Facade::defaultAliases()->merge([
        'PDF' => Barryvdh\DomPDF\Facade\Pdf::class,
    ])->toArray(),
];

