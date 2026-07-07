<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Event;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Relations\Relation;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Student;
use App\Services\NotificationService;
use App\Listeners\AuthenticationListener;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Minishlink\WebPush\WebPush;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(NotificationService::class, function ($app) {
            $webPush = null;
            $publicKey  = (string) config('web-push.vapid.public_key');
            $privateKey = (string) config('web-push.vapid.private_key');
            $subject    = (string) config('web-push.vapid.subject');

            if ($publicKey === '' || $privateKey === '') {
                \Illuminate\Support\Facades\Log::warning(
                    'WebPush is not configured. Set VAPID_PUBLIC_KEY and VAPID_PRIVATE_KEY in .env to enable push notifications.'
                );
            } else {
                try {
                    $webPush = new WebPush([
                        'VAPID' => [
                            'publicKey'  => $publicKey,
                            'privateKey' => $privateKey,
                            'subject'    => $subject,
                        ],
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to initialise WebPush: ' . $e->getMessage());
                }
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

        Relation::morphMap([
            'user' => User::class,
            'student' => Student::class,
        ]);

        // Register authentication audit listeners
        Event::listen(Login::class, [AuthenticationListener::class, 'handleLogin']);
        Event::listen(Logout::class, [AuthenticationListener::class, 'handleLogout']);
        Event::listen(Failed::class, [AuthenticationListener::class, 'handleFailed']);
    }
}
