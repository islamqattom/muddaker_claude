<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;
use Tests\CreatesApplication;

class AuthLogoutReloginTest extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        // Create admin and supervisor users in the test DB
        if (!User::where('email', 'admin@test.local')->exists()) {
            User::create([
                'id' => 1,
                'name' => 'مدير النظام',
                'username' => 'admin',
                'email' => 'admin@test.local',
                'password' => Hash::make('Test1234'),
                'role' => 'admin',
                'is_active' => true,
            ]);
        }
        if (!User::where('email', 'sup@test.local')->exists()) {
            User::create([
                'id' => 2,
                'name' => 'مشرف تجريبي',
                'username' => 'sup',
                'email' => 'sup@test.local',
                'password' => Hash::make('Test1234'),
                'role' => 'supervisor',
                'is_active' => true,
            ]);
        }
    }

    public function test_admin_can_login_view_dashboard_logout_then_supervisor_can_login(): void
    {
        $admin = User::where('email', 'admin@test.local')->first();
        $sup = User::where('email', 'sup@test.local')->first();

        // 1. Login as admin
        $resp = $this->post('/login', [
            'login' => 'admin@test.local',
            'password' => 'Test1234',
        ]);
        $resp->assertStatus(302);
        $resp->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);

        // 2. Admin can view dashboard
        $resp = $this->get('/admin/dashboard');
        $resp->assertStatus(200);

        // 3. Logout
        $resp = $this->post('/logout');
        $resp->assertStatus(302);
        $resp->assertRedirect(route('login'));
        $this->assertGuest();

        // 4. GET /login after logout - should be 200
        $resp = $this->get('/login');
        $resp->assertStatus(200);

        // 5. Login as supervisor (different user) - should succeed without issues
        $resp = $this->post('/login', [
            'login' => 'sup@test.local',
            'password' => 'Test1234',
        ]);
        $resp->assertStatus(302);
        $resp->assertRedirect(route('supervisor.dashboard'));
        $this->assertAuthenticatedAs($sup);

        // 6. Supervisor can view their dashboard
        $resp = $this->get('/supervisor/dashboard');
        $resp->assertStatus(200);
    }

    public function test_logout_via_get_route_works(): void
    {
        $admin = User::where('email', 'admin@test.local')->first();
        $this->actingAs($admin);
        $resp = $this->get('/logout');
        $resp->assertStatus(302);
        $this->assertGuest();
    }

    public function test_throttle_does_not_block_logout_then_login_quickly(): void
    {
        $admin = User::where('email', 'admin@test.local')->first();
        $sup = User::where('email', 'sup@test.local')->first();

        // Simulate rapid login → logout → login cycle, repeating 5 times to stress throttle
        for ($i = 0; $i < 4; $i++) {
            $this->post('/login', ['login' => 'admin@test.local', 'password' => 'Test1234']);
            $this->post('/logout');
            $this->post('/login', ['login' => 'sup@test.local', 'password' => 'Test1234']);
            $this->post('/logout');
        }
        // Final login as supervisor should still work
        $resp = $this->post('/login', ['login' => 'sup@test.local', 'password' => 'Test1234']);
        $resp->assertStatus(302);
        $resp->assertRedirect(route('supervisor.dashboard'));
        $this->assertAuthenticatedAs($sup);
    }
}
