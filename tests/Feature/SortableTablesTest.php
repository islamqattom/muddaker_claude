<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;
use Tests\CreatesApplication;

class SortableTablesTest extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        User::firstOrCreate(
            ['username' => 'sortable_admin'],
            [
                'name' => 'Admin', 'email' => 'sortable_admin@test.local',
                'password' => Hash::make('secret'), 'role' => 'admin',
            ]
        );
    }

    public function test_layout_injects_global_sortable_script(): void
    {
        $response = $this->actingAs(User::where('username', 'sortable_admin')->first())
            ->get('/admin/students');

        $response->assertStatus(200);
        $response->assertSee('SortableTables', false);
        $response->assertSee('data-sortable', false);
    }
}

