<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Settings keys that are loaded from the database into config('app.*').
     */
    public const KNOWN_SETTINGS = [
        'center_name',
        'center_email',
        'center_phone',
        'work_start_time',
        'work_end_time',
        'work_days',
        'recitation_days',
        'enable_notifications',
        'billing_monthly_fee_per_student',
        'billing_reminder_frequency_days',
        'billing_reminder_enabled',
    ];

    /**
     * Default list of actual work days, used by every place that needs a
     * fallback so the value can never drift between files. The site may be
     * opened at any time and on any day, so the default covers every day.
     */
    public const DEFAULT_WORK_DAYS = 'saturday,sunday,monday,tuesday,wednesday,thursday,friday';

    /**
     * Default list of recitation days. Kept independent from the work days.
     */
    public const DEFAULT_RECITATION_DAYS = 'saturday,tuesday';

    /**
     * Arabic labels for the English day names stored in the settings.
     */
    public const DAY_NAMES_AR = [
        'saturday' => 'السبت',
        'sunday' => 'الأحد',
        'monday' => 'الاثنين',
        'tuesday' => 'الثلاثاء',
        'wednesday' => 'الأربعاء',
        'thursday' => 'الخميس',
        'friday' => 'الجمعة',
    ];

    /**
     * Map English day names (as stored in the "work_days" setting) to their
     * Carbon day numbers (0 = Sunday ... 6 = Saturday).
     */
    public const DAY_NUMBERS = [
        'sunday' => 0,
        'monday' => 1,
        'tuesday' => 2,
        'wednesday' => 3,
        'thursday' => 4,
        'friday' => 5,
        'saturday' => 6,
    ];

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
        try {
            self::applySettingsToConfig();
        } catch (\Exception $e) {
            // Settings table may not exist during testing (before RefreshDatabase migrations run)
            // Silently skip loading settings in that case
        }
    }

    /**
     * Load the persisted settings and merge them into the runtime config.
     *
     * Known settings are exposed as config('app.<key>'). The recitation days
     * also drive the missed-recitation automation: they become the days on
     * which a present student is required to have a recitation session, and
     * every other day is excluded.
     */
    public static function applySettingsToConfig(): void
    {
        $settings = \DB::table('settings')
            ->whereIn('key', self::KNOWN_SETTINGS)
            ->get()
            ->keyBy('key');

        foreach (self::KNOWN_SETTINGS as $key) {
            if (! isset($settings[$key])) {
                continue;
            }

            $value = $settings[$key]->value;

            // Convert string booleans to actual booleans
            if ($value === 'true') {
                $value = true;
            } elseif ($value === 'false') {
                $value = false;
            }

            config(['app.'.$key => $value]);
        }

        self::applyRecitationDays(
            isset($settings['recitation_days'])
                ? (string) $settings['recitation_days']->value
                : self::DEFAULT_RECITATION_DAYS
        );
    }

    /**
     * Translate a comma-separated list of day names into the recitation
     * automation's required/excluded day numbers.
     */
    public static function applyRecitationDays(string $recitationDays): void
    {
        $required = self::carbonDaysForDayNames($recitationDays);

        config([
            'recitation.required_days' => $required,
            'recitation.excluded_days' => array_values(array_diff(range(0, 6), $required)),
        ]);
    }

    /**
     * Convert a comma-separated list of day names to sorted Carbon day numbers.
     */
    public static function carbonDaysForDayNames(string $dayNames): array
    {
        $days = [];

        foreach (explode(',', $dayNames) as $name) {
            $name = strtolower(trim($name));

            if (isset(self::DAY_NUMBERS[$name])) {
                $days[self::DAY_NUMBERS[$name]] = true;
            }
        }

        $days = array_keys($days);
        sort($days);

        return $days;
    }

    /**
     * Arabic label for a Carbon day number (0 = Sunday ... 6 = Saturday).
     */
    public static function arabicDayName(int $carbonDay): string
    {
        $english = array_search($carbonDay, self::DAY_NUMBERS, true);

        if ($english === false) {
            return (string) $carbonDay;
        }

        return self::DAY_NAMES_AR[$english] ?? $english;
    }
}
