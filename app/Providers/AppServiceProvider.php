<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Set Arabic locale for Carbon
        Carbon::setLocale('ar');
        setlocale(LC_ALL, 'ar_SA.UTF-8');

        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
