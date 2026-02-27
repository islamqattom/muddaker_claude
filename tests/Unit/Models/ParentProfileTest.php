<?php

namespace Tests\Unit\Models;

use App\Models\ParentProfile;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParentProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_profile_has_required_fillable_attributes(): void
    {
        $parent = new ParentProfile();
        $fillable = $parent->getFillable();

        $this->assertContains('user_id', $fillable);
        $this->assertContains('national_id', $fillable);
        $this->assertContains('phone', $fillable);
        $this->assertContains('relationship', $fillable);
    }

    public function test_parent_profile_uses_parents_table(): void
    {
        $parent = new ParentProfile();

        $this->assertEquals('parents', $parent->getTable());
    }

    public function test_parent_profile_belongs_to_user(): void
    {
        $user = User::factory()->parent()->create();
        $parent = ParentProfile::factory()->create(['user_id' => $user->id]);

        $this->assertEquals($user->id, $parent->user->id);
    }

    public function test_parent_profile_has_many_students(): void
    {
        $parent = ParentProfile::factory()->create();
        Student::factory()->count(2)->create();

        $this->assertCount(0, $parent->students);
    }

    public function test_parent_profile_relationship_label_returns_arabic_label(): void
    {
        $parent = ParentProfile::factory()->create(['relationship' => 'father']);
        $this->assertEquals('الأب', $parent->relationship_label);

        $parent = ParentProfile::factory()->create(['relationship' => 'mother']);
        $this->assertEquals('الأم', $parent->relationship_label);

        $parent = ParentProfile::factory()->create(['relationship' => 'guardian']);
        $this->assertEquals('ولي الأمر', $parent->relationship_label);
    }

    public function test_parent_profile_relationship_label_returns_relationship_for_unknown(): void
    {
        $parent = ParentProfile::factory()->create(['relationship' => 'other']);

        $this->assertEquals('other', $parent->relationship_label);
    }
}
