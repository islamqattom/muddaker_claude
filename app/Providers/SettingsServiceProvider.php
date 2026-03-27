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
        // Only load known settings keys
        $knownSettings = [
            'center_name',
            'center_email',
            'center_phone',
            'work_start_time',
            'work_end_time',
            'work_days',
            'enable_notifications',
        ];

        try {
            $settings = \DB::table('settings')
                ->whereIn('key', $knownSettings)
                ->get()
                ->keyBy('key');

            foreach ($knownSettings as $key) {
                if (isset($settings[$key])) {
                    $value = $settings[$key]->value;

                    // Convert string booleans to actual booleans
                    if ($value === 'true') {
                        $value = true;
                    } elseif ($value === 'false') {
                        $value = false;
                    }

                    config(['app.' . $key => $value]);
                }
            }
        } catch (\Exception $e) {
            // Settings table may not exist during testing (before RefreshDatabase migrations run)
            // Silently skip loading settings in that case
        }
    }
}
