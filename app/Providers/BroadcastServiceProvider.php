<?php

namespace App\Providers;

use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Contracts\Broadcasting\Broadcaster;
use Illuminate\Contracts\Broadcasting\Factory as BroadcastingFactory;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(BroadcastManager::class, fn ($app) => new BroadcastManager($app));

        $this->app->singleton(Broadcaster::class, function ($app) {
            return $app->make(BroadcastManager::class)->connection();
        });

        $this->app->alias(
            BroadcastManager::class,
            BroadcastingFactory::class
        );
    }

    public function boot(): void
    {
        Broadcast::routes();

        require base_path('routes/channels.php');
    }
}
