<?php

namespace Tests\Feature;

use App\Models\Level;
use App\Models\ParentProfile;
use App\Models\Student;
use App\Models\User;
use App\Services\ParentActivationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParentAutoDeactivationTest extends TestCase
{
    use RefreshDatabase;

    private function level(): Level
    {
        return Level::create([
            'name' => 'مستوى', 'level_number' => 1,
            'new_memorization_pages' => 3, 'review_pages' => 6, 'description' => 'x',
        ]);
    }

    private function student(bool $active = true): Student
    {
        $user = User::factory()->student();
        if (! $active) {
            $user = $user->inactive();
        }

        return Student::create([
            'user_id' => $user->create()->id,
            'supervisor_id' => User::factory()->supervisor()->create()->id,
            'level_id' => $this->level()->id,
            'student_number' => 'STU-'.uniqid(),
            'memorized_parts' => 0,
            'current_part' => 1,
            'current_page' => 1,
        ]);
    }

    /**
     * @param  array<int, Student>  $students
     */
    private function parentProfile(array $students = []): ParentProfile
    {
        $parentUser = User::factory()->parent()->create();
        $profile = ParentProfile::factory()->create(['user_id' => $parentUser->id]);

        if (! empty($students)) {
            $profile->students()->attach(collect($students)->pluck('id')->all());
        }

        return $profile;
    }

    private function service(): ParentActivationService
    {
        return app(ParentActivationService::class);
    }

    // ---------------------------------------------------------------------
    // 1. Single child
    // ---------------------------------------------------------------------

    public function test_parent_with_one_child_follows_child_activation(): void
    {
        $admin = User::factory()->admin()->create();
        $student = $this->student(true);
        $parent = $this->parentProfile([$student]);

        $this->assertTrue($parent->user->is_active);

        $this->actingAs($admin)
            ->post(route('admin.students.toggleActive', $student->id))
            ->assertRedirect();

        $this->assertFalse($student->user->fresh()->is_active);
        $this->assertFalse($parent->user->fresh()->is_active);
        $this->assertTrue($parent->user->fresh()->deactivated_by_system);
        $this->assertStringContainsString('تم تعطيل حساب ولي الأمر', session('success'));

        $this->actingAs($admin)
            ->post(route('admin.students.toggleActive', $student->id))
            ->assertRedirect();

        $this->assertTrue($student->user->fresh()->is_active);
        $this->assertTrue($parent->user->fresh()->is_active);
        $this->assertFalse($parent->user->fresh()->deactivated_by_system);
    }

    // ---------------------------------------------------------------------
    // 2. Multiple children
    // ---------------------------------------------------------------------

    public function test_parent_with_two_children_only_deactivates_when_all_inactive(): void
    {
        $admin = User::factory()->admin()->create();
        $first = $this->student(true);
        $second = $this->student(true);
        $parent = $this->parentProfile([$first, $second]);

        // Deactivate one child: parent stays active.
        $this->actingAs($admin)->post(route('admin.students.toggleActive', $first->id));
        $this->assertFalse($first->user->fresh()->is_active);
        $this->assertTrue($parent->user->fresh()->is_active);

        // Deactivate both: parent deactivated.
        $this->actingAs($admin)->post(route('admin.students.toggleActive', $second->id));
        $this->assertFalse($parent->user->fresh()->is_active);
        $this->assertTrue($parent->user->fresh()->deactivated_by_system);

        // Reactivate one: parent reactivated.
        $this->actingAs($admin)->post(route('admin.students.toggleActive', $first->id));
        $this->assertTrue($first->user->fresh()->is_active);
        $this->assertTrue($parent->user->fresh()->is_active);
    }

    // ---------------------------------------------------------------------
    // 3. Manual admin deactivation is preserved
    // ---------------------------------------------------------------------

    public function test_manually_deactivated_parent_is_not_reactivated_by_child(): void
    {
        $admin = User::factory()->admin()->create();
        $student = $this->student(true);
        $parent = $this->parentProfile([$student]);

        // Admin deactivates the parent manually.
        $this->actingAs($admin)
            ->post(route('admin.parents.toggleActive', $parent->user->id));
        $this->assertFalse($parent->user->fresh()->is_active);
        $this->assertFalse($parent->user->fresh()->deactivated_by_system);

        // Child deactivated then reactivated by admin.
        $this->actingAs($admin)->post(route('admin.students.toggleActive', $student->id));
        $this->actingAs($admin)->post(route('admin.students.toggleActive', $student->id));

        $this->assertTrue($student->user->fresh()->is_active);
        $this->assertFalse($parent->user->fresh()->is_active);
    }

    // ---------------------------------------------------------------------
    // 4. Parent without children
    // ---------------------------------------------------------------------

    public function test_parent_without_children_is_never_touched(): void
    {
        $parent = $this->parentProfile();

        $this->assertNull($this->service()->syncParentStatus($parent));
        $this->assertTrue($parent->user->fresh()->is_active);
    }

    // ---------------------------------------------------------------------
    // 5. Admin manual activation wins
    // ---------------------------------------------------------------------

    public function test_admin_can_manually_activate_parent_whose_children_are_all_inactive(): void
    {
        $admin = User::factory()->admin()->create();
        $student = $this->student(true);
        $parent = $this->parentProfile([$student]);

        // System deactivates the parent first.
        $this->actingAs($admin)->post(route('admin.students.toggleActive', $student->id));
        $this->assertFalse($parent->user->fresh()->is_active);

        // Admin manually reactivates the parent while the child is inactive.
        $this->actingAs($admin)
            ->post(route('admin.parents.toggleActive', $parent->user->id));

        $this->assertTrue($parent->user->fresh()->is_active);
        $this->assertFalse($parent->user->fresh()->deactivated_by_system);
    }

    // ---------------------------------------------------------------------
    // 6. Pivot (link / unlink) changes
    // ---------------------------------------------------------------------

    public function test_linking_active_child_reactivates_auto_deactivated_parent(): void
    {
        $student = $this->student(true);
        $parent = $this->parentProfile();

        $parent->user->update(['is_active' => false, 'deactivated_by_system' => true]);

        $parent->students()->attach($student->id);
        $event = $this->service()->syncParentStatus($parent);

        $this->assertSame('reactivated', $event['action']);
        $this->assertTrue($parent->user->fresh()->is_active);
        $this->assertFalse($parent->user->fresh()->deactivated_by_system);
    }

    public function test_unlinking_last_active_child_deactivates_parent(): void
    {
        $active = $this->student(true);
        $inactive = $this->student(false);
        $parent = $this->parentProfile([$active, $inactive]);

        $this->assertTrue($parent->user->is_active);

        $parent->students()->detach($active->id);
        $event = $this->service()->syncParentStatus($parent);

        $this->assertSame('deactivated', $event['action']);
        $this->assertFalse($parent->user->fresh()->is_active);
        $this->assertTrue($parent->user->fresh()->deactivated_by_system);
    }

    public function test_unlinking_only_child_leaves_parent_unchanged(): void
    {
        $only = $this->student(false);
        $parent = $this->parentProfile([$only]);

        $parent->students()->detach($only->id);
        $event = $this->service()->syncParentStatus($parent);

        $this->assertNull($event);
        $this->assertTrue($parent->user->fresh()->is_active);
    }

    public function test_store_deactivates_new_parent_linked_only_to_inactive_children(): void
    {
        $admin = User::factory()->admin()->create();
        $inactive = $this->student(false);

        $this->actingAs($admin)->post(route('admin.parents.store'), [
            'name' => 'ولي أمر جديد',
            'username' => 'newparent'.uniqid(),
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'relationship' => 'father',
            'student_ids' => [$inactive->id],
        ])->assertRedirect(route('admin.parents.index'));

        $parentUser = User::where('role', 'parent')->latest('id')->first();
        $this->assertNotNull($parentUser);
        $this->assertFalse($parentUser->is_active);
        $this->assertTrue($parentUser->deactivated_by_system);
    }

    // ---------------------------------------------------------------------
    // 7. Backfill command
    // ---------------------------------------------------------------------

    public function test_backfill_dry_run_changes_nothing(): void
    {
        $student = $this->student(false);
        $parent = $this->parentProfile([$student]);

        $this->artisan('parents:deactivate-inactive-children', ['--dry-run' => true])
            ->assertSuccessful();

        $this->assertTrue($parent->user->fresh()->is_active);
    }

    public function test_backfill_applies_without_dry_run(): void
    {
        $student = $this->student(false);
        $parent = $this->parentProfile([$student]);

        $this->artisan('parents:deactivate-inactive-children', ['--force' => true])
            ->assertSuccessful();

        $this->assertFalse($parent->user->fresh()->is_active);
        $this->assertTrue($parent->user->fresh()->deactivated_by_system);
    }
}
