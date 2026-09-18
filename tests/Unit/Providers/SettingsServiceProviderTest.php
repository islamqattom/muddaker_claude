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

    public function test_apply_settings_to_config_loads_known_keys(): void
    {
        $now = now();

        DB::table('settings')->insert([
            ['key' => 'center_name', 'value' => 'مركز الاختبار', 'group' => 'general', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'enable_notifications', 'value' => 'false', 'group' => 'general', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'recitation_days', 'value' => 'sunday,tuesday', 'group' => 'general', 'created_at' => $now, 'updated_at' => $now],
        ]);

        SettingsServiceProvider::applySettingsToConfig();

        $this->assertSame('مركز الاختبار', config('app.center_name'));
        $this->assertFalse(config('app.enable_notifications'));
        $this->assertSame([0, 2], config('recitation.required_days'));
    }
}
