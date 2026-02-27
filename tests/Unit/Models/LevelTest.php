<?php

namespace Tests\Unit\Models;

use App\Models\Level;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LevelTest extends TestCase
{
    use RefreshDatabase;

    public function test_level_has_required_fillable_attributes(): void
    {
        $level = new Level();
        $fillable = $level->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('level_number', $fillable);
        $this->assertContains('new_memorization_pages', $fillable);
        $this->assertContains('review_pages', $fillable);
        $this->assertContains('description', $fillable);
    }

    public function test_level_has_many_students(): void
    {
        $level = Level::factory()->create();
        Student::factory()->count(3)->create(['level_id' => $level->id]);

        $this->assertCount(3, $level->students);
    }

    public function test_level_can_have_zero_students(): void
    {
        $level = Level::factory()->create();

        $this->assertCount(0, $level->students);
    }
}
