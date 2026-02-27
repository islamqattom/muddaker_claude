<?php

return [
    'driver' => env('CACHE_DRIVER', 'file'),
    'store' => env('CACHE_STORE'),
    'prefix' => env('CACHE_PREFIX', 'quran_system_cache_'),
];
