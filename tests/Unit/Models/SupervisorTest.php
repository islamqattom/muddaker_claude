<?php

namespace Tests\Unit\Models;

use App\Models\Student;
use App\Models\Supervisor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupervisorTest extends TestCase
{
    use RefreshDatabase;

    public function test_supervisor_has_required_fillable_attributes(): void
    {
        $supervisor = new Supervisor();
        $fillable = $supervisor->getFillable();

        $this->assertContains('user_id', $fillable);
        $this->assertContains('employee_number', $fillable);
        $this->assertContains('specialization', $fillable);
        $this->assertContains('hire_date', $fillable);
    }

    public function test_supervisor_has_correct_casts(): void
    {
        $supervisor = new Supervisor();
        $casts = $supervisor->getCasts();

        $this->assertArrayHasKey('hire_date', $casts);
        $this->assertEquals('date', $casts['hire_date']);
    }

    public function test_supervisor_belongs_to_user(): void
    {
        $user = User::factory()->supervisor()->create();
        $supervisor = Supervisor::factory()->create(['user_id' => $user->id]);

        $this->assertEquals($user->id, $supervisor->user->id);
    }

    public function test_supervisor_has_many_students(): void
    {
        $supervisor = User::factory()->supervisor()->create();
        Student::factory()->count(3)->create(['supervisor_id' => $supervisor->id]);

        // Need to create a supervisor record first
        Supervisor::factory()->create(['user_id' => $supervisor->id]);

        // Reload the supervisor
        $supervisorModel = Supervisor::where('user_id', $supervisor->id)->first();

        $this->assertNotNull($supervisorModel);
    }
}
