<?php

namespace Tests\Unit\Models;

use App\Models\Notification;
use App\Models\ParentProfile;
use App\Models\Student;
use App\Models\Supervisor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_has_required_fillable_attributes(): void
    {
        $user = new User();
        $fillable = $user->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('phone', $fillable);
        $this->assertContains('password', $fillable);
        $this->assertContains('role', $fillable);
        $this->assertContains('avatar', $fillable);
        $this->assertContains('is_active', $fillable);
    }

    public function test_user_has_correct_hidden_attributes(): void
    {
        $user = new User();
        $hidden = $user->getHidden();

        $this->assertContains('password', $hidden);
        $this->assertContains('remember_token', $hidden);
    }

    public function test_user_has_correct_casts(): void
    {
        $user = new User();
        $casts = $user->getCasts();

        $this->assertArrayHasKey('email_verified_at', $casts);
        $this->assertEquals('datetime', $casts['email_verified_at']);
        $this->assertArrayHasKey('password', $casts);
        $this->assertEquals('hashed', $casts['password']);
        $this->assertArrayHasKey('is_active', $casts);
        $this->assertEquals('boolean', $casts['is_active']);
    }

    public function test_is_admin_returns_true_for_admin_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->assertTrue($admin->isAdmin());
    }

    public function test_is_admin_returns_false_for_non_admin_role(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $this->assertFalse($user->isAdmin());
    }

    public function test_is_supervisor_returns_true_for_supervisor_role(): void
    {
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $this->assertTrue($supervisor->isSupervisor());
    }

    public function test_is_supervisor_returns_false_for_non_supervisor_role(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $this->assertFalse($user->isSupervisor());
    }

    public function test_is_student_returns_true_for_student_role(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $this->assertTrue($student->isStudent());
    }

    public function test_is_student_returns_false_for_non_student_role(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->assertFalse($user->isStudent());
    }

    public function test_is_parent_returns_true_for_parent_role(): void
    {
        $parent = User::factory()->create(['role' => 'parent']);
        $this->assertTrue($parent->isParent());
    }

    public function test_is_parent_returns_false_for_non_parent_role(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->assertFalse($user->isParent());
    }

    public function test_user_has_student_relationship(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->student()->exists());
        $this->assertEquals($student->id, $user->student->id);
    }

    public function test_user_has_supervisor_relationship(): void
    {
        $user = User::factory()->create();
        $supervisor = Supervisor::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->supervisor()->exists());
        $this->assertEquals($supervisor->id, $user->supervisor->id);
    }

    public function test_user_has_parent_profile_relationship(): void
    {
        $user = User::factory()->create();
        $parentProfile = ParentProfile::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->parentProfile()->exists());
        $this->assertEquals($parentProfile->id, $user->parentProfile->id);
    }

    public function test_user_has_supervised_students_relationship(): void
    {
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $student1 = Student::factory()->create(['supervisor_id' => $supervisor->id]);
        $student2 = Student::factory()->create(['supervisor_id' => $supervisor->id]);

        $supervisedStudents = $supervisor->supervisedStudents;

        $this->assertCount(2, $supervisedStudents);
        $this->assertTrue($supervisedStudents->contains($student1));
        $this->assertTrue($supervisedStudents->contains($student2));
    }

    public function test_user_has_notifications_relationship(): void
    {
        $user = User::factory()->create();
        Notification::factory()->create(['user_id' => $user->id]);
        Notification::factory()->create(['user_id' => $user->id]);

        $this->assertCount(2, $user->notifications);
    }

    public function test_avatar_url_returns_asset_path_when_avatar_exists(): void
    {
        $user = User::factory()->create(['avatar' => 'avatars/user.png']);

        $this->assertStringContainsString('storage/avatars/user.png', $user->avatar_url);
    }

    public function test_avatar_url_returns_default_when_no_avatar(): void
    {
        $user = User::factory()->create(['avatar' => null]);

        $this->assertStringContainsString('default-avatar.png', $user->avatar_url);
    }

    public function test_unread_notifications_count_returns_correct_count(): void
    {
        $user = User::factory()->create();
        Notification::factory()->create(['user_id' => $user->id, 'is_read' => false]);
        Notification::factory()->create(['user_id' => $user->id, 'is_read' => false]);
        Notification::factory()->create(['user_id' => $user->id, 'is_read' => true]);

        $this->assertEquals(2, $user->unreadNotificationsCount());
    }
}
