<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load settings from database and merge into config
        $settings = \DB::table('settings')->get();

        foreach ($settings as $setting) {
            $value = $setting->value;

            // Convert string booleans to actual booleans
            if ($value === 'true') {
                $value = true;
            } elseif ($value === 'false') {
                $value = false;
            }

            config(['app.' . $setting->key => $value]);
        }
    }
}
