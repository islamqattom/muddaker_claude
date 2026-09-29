<?php

namespace Tests\Feature;

use App\Models\User;
use App\Providers\SettingsServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'مدير', 'username' => 'admin_'.uniqid('', true),
            'email' => uniqid('', true).'@test.local',
            'password' => Hash::make('secret'), 'role' => 'admin', 'is_active' => true,
        ]);
    }

    public function test_admin_can_view_settings_page(): void
    {
        $resp = $this->actingAs($this->admin())->get('/admin/settings');

        $resp->assertStatus(200);
        $resp->assertSee('إعدادات النظام', false);
        $resp->assertSee('أيام التسميع', false);
        $resp->assertSee('recitation_days[]', false);
    }

    public function test_admin_dashboard_shows_work_days_in_arabic(): void
    {
        SettingsServiceProvider::applySettingsToConfig();

        $resp = $this->actingAs($this->admin())->get('/admin/dashboard');

        $resp->assertStatus(200);
        $resp->assertSee('السبت', false);
        $resp->assertSee('الجمعة', false);
    }

    public function test_admin_can_save_settings_and_values_persist(): void
    {
        $admin = $this->admin();

        $resp = $this->actingAs($admin)->put('/admin/settings', [
            'center_name' => 'مركز النور',
            'center_email' => 'info@noor.test',
            'center_phone' => '0500000000',
            'work_start_time' => '07:30',
            'work_end_time' => '18:00',
            'work_days' => ['saturday', 'monday'],
            'recitation_days' => ['saturday', 'tuesday'],
            'enable_notifications' => '1',
        ]);

        $resp->assertRedirect(route('admin.settings.index'));
        $resp->assertSessionHas('success');

        $this->assertDatabaseHas('settings', ['key' => 'center_name', 'value' => 'مركز النور']);
        $this->assertDatabaseHas('settings', ['key' => 'work_days', 'value' => 'saturday,monday']);
        $this->assertDatabaseHas('settings', ['key' => 'recitation_days', 'value' => 'saturday,tuesday']);

        // Simulate a fresh request: the provider reloads the persisted values.
        SettingsServiceProvider::applySettingsToConfig();

        $this->assertSame('مركز النور', config('app.center_name'));
        $this->actingAs($admin)->get('/admin/settings')->assertSee('مركز النور', false);
    }

    public function test_unchecking_all_work_days_is_persisted_as_empty(): void
    {
        $this->actingAs($this->admin())->put('/admin/settings', [
            'center_name' => 'مركز',
            'work_days_submitted' => '1',
            // work_days omitted on purpose: every checkbox unchecked.
        ])->assertRedirect(route('admin.settings.index'));

        $this->assertDatabaseHas('settings', ['key' => 'work_days', 'value' => '']);
    }

    public function test_unchecking_all_recitation_days_is_persisted_as_empty(): void
    {
        $this->actingAs($this->admin())->put('/admin/settings', [
            'center_name' => 'مركز',
            'recitation_days_submitted' => '1',
            // recitation_days omitted on purpose: every checkbox unchecked.
        ])->assertRedirect(route('admin.settings.index'));

        $this->assertDatabaseHas('settings', ['key' => 'recitation_days', 'value' => '']);
    }

    public function test_recitation_days_drive_recitation_required_days(): void
    {
        $this->actingAs($this->admin())->put('/admin/settings', [
            'center_name' => 'مركز',
            'recitation_days' => ['sunday', 'tuesday'],
        ])->assertRedirect(route('admin.settings.index'));

        SettingsServiceProvider::applySettingsToConfig();

        $this->assertSame([0, 2], config('recitation.required_days'));
        $this->assertSame([1, 3, 4, 5, 6], config('recitation.excluded_days'));
    }

    public function test_non_admin_cannot_access_settings(): void
    {
        $supervisor = User::create([
            'name' => 'مشرف', 'username' => 'sup_'.uniqid('', true),
            'email' => uniqid('', true).'@test.local',
            'password' => Hash::make('secret'), 'role' => 'supervisor', 'is_active' => true,
        ]);

        $this->actingAs($supervisor)->get('/admin/settings')->assertForbidden();
    }
}
