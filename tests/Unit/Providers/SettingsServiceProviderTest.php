<?php

namespace Tests\Unit\Providers;

use App\Providers\SettingsServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SettingsServiceProviderTest extends TestCase
{
    use RefreshDatabase;

    public function test_carbon_days_for_day_names_maps_names_and_sorts(): void
    {
        $this->assertSame([0, 2, 6], SettingsServiceProvider::carbonDaysForDayNames('sunday, tuesday ,saturday'));
        $this->assertSame([], SettingsServiceProvider::carbonDaysForDayNames(''));
        $this->assertSame([], SettingsServiceProvider::carbonDaysForDayNames('bogus,123'));
    }

    public function test_apply_recitation_days_sets_required_and_excluded(): void
    {
        SettingsServiceProvider::applyRecitationDays('sunday,tuesday');

        $this->assertSame([0, 2], config('recitation.required_days'));
        $this->assertSame([1, 3, 4, 5, 6], config('recitation.excluded_days'));
    }

    public function test_empty_recitation_days_make_no_day_required(): void
    {
        SettingsServiceProvider::applyRecitationDays('');

        $this->assertSame([], config('recitation.required_days'));
        $this->assertSame([0, 1, 2, 3, 4, 5, 6], config('recitation.excluded_days'));
    }

    public function test_default_work_days_cover_every_day(): void
    {
        $this->assertSame(
            [0, 1, 2, 3, 4, 5, 6],
            SettingsServiceProvider::carbonDaysForDayNames(SettingsServiceProvider::DEFAULT_WORK_DAYS)
        );
    }

    public function test_day_name_maps_stay_in_sync(): void
    {
        $numbers = array_keys(SettingsServiceProvider::DAY_NUMBERS);
        $labels = array_keys(SettingsServiceProvider::DAY_NAMES_AR);
        sort($numbers);
        sort($labels);

        $this->assertSame($numbers, $labels);
    }

    public function test_missing_recitation_setting_falls_back_to_default(): void
    {
        DB::table('settings')->where('key', 'recitation_days')->delete();

        SettingsServiceProvider::applySettingsToConfig();

        $this->assertSame([2, 6], config('recitation.required_days'));
        $this->assertSame([0, 1, 3, 4, 5], config('recitation.excluded_days'));
    }

    public function test_apply_settings_to_config_loads_known_keys(): void
    {
        $now = now();

        foreach ([
            ['key' => 'center_name', 'value' => 'مركز الاختبار'],
            ['key' => 'enable_notifications', 'value' => 'false'],
            ['key' => 'recitation_days', 'value' => 'sunday,tuesday'],
        ] as $row) {
            DB::table('settings')->updateOrInsert(
                ['key' => $row['key']],
                ['value' => $row['value'], 'group' => 'general', 'updated_at' => $now, 'created_at' => $now]
            );
        }

        SettingsServiceProvider::applySettingsToConfig();

        $this->assertSame('مركز الاختبار', config('app.center_name'));
        $this->assertFalse(config('app.enable_notifications'));
        $this->assertSame([0, 2], config('recitation.required_days'));
    }
}
