<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Pagination\Paginator;
use Carbon\Carbon;
use App\Services\NotificationService;
use Minishlink\WebPush\WebPush;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(NotificationService::class, function ($app) {
            $webPush = null;
            try {
                $webPush = new WebPush([
                    'VAPID' => [
                        'publicKey' => config('web-push.vapid.public_key'),
                        'privateKey' => config('web-push.vapid.private_key'),
                        'subject' => config('web-push.vapid.subject'),
                    ],
                ]);
            } catch (\Exception $e) {
                // WebPush not configured — skip
            }

            return new NotificationService($webPush);
        });
    }

    public function boot(): void
    {
        // Set Arabic locale for Carbon
        Carbon::setLocale('ar');
        setlocale(LC_ALL, 'ar_SA.UTF-8');

        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Use Bootstrap 5 pagination
        Paginator::useBootstrapFive();
    }
}
