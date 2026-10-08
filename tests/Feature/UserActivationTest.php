<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Level;
use App\Models\Notification;
use App\Models\RecitationSession;
use App\Models\Student;
use App\Models\User;
use App\Services\AttendanceReminderService;
use App\Services\LeaderboardService;
use App\Services\MissedRecitationService;
use App\Services\NotificationService;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class UserActivationTest extends TestCase
{
    use RefreshDatabase;

    private function level(): Level
    {
        return Level::create([
            'name' => 'مستوى', 'level_number' => 1,
            'new_memorization_pages' => 3, 'review_pages' => 6, 'description' => 'x',
        ]);
    }

    private function student(bool $active = true, ?User $supervisor = null): Student
    {
        $user = User::factory()->student();
        if (! $active) {
            $user = $user->inactive();
        }

        return Student::create([
            'user_id' => $user->create()->id,
            'supervisor_id' => ($supervisor ?? User::factory()->supervisor()->create())->id,
            'level_id' => $this->level()->id,
            'student_number' => 'STU-'.uniqid(),
            'memorized_parts' => 0,
            'current_part' => 1,
            'current_page' => 1,
        ]);
    }

    // ---------------------------------------------------------------------
    // 1. Per-request active check
    // ---------------------------------------------------------------------

    public function test_inactive_user_is_logged_out_on_next_request(): void
    {
        $user = User::factory()->student()->inactive()->create();

        $response = $this->actingAs($user)->get(route('push.status'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_active_user_is_not_logged_out(): void
    {
        $user = User::factory()->student()->create();

        $this->actingAs($user)->get(route('push.status'))->assertOk();
        $this->assertAuthenticatedAs($user);
    }

    // ---------------------------------------------------------------------
    // 2. Toggle endpoints
    // ---------------------------------------------------------------------

    public function test_admin_can_toggle_student_activation(): void
    {
        $admin = User::factory()->admin()->create();
        $student = $this->student();

        $this->actingAs($admin)
            ->post(route('admin.students.toggleActive', $student->id))
            ->assertRedirect();

        $this->assertFalse($student->user->fresh()->is_active);

        $this->actingAs($admin)
            ->post(route('admin.students.toggleActive', $student->id))
            ->assertRedirect();

        $this->assertTrue($student->user->fresh()->is_active);
    }

    public function test_admin_can_toggle_supervisor_and_parent_activation(): void
    {
        $admin = User::factory()->admin()->create();
        $supervisor = User::factory()->supervisor()->create();
        $parent = User::factory()->parent()->create();

        $this->actingAs($admin)
            ->post(route('admin.supervisors.toggleActive', $supervisor->id))
            ->assertRedirect();
        $this->assertFalse($supervisor->fresh()->is_active);

        $this->actingAs($admin)
            ->post(route('admin.parents.toggleActive', $parent->id))
            ->assertRedirect();
        $this->assertFalse($parent->fresh()->is_active);
    }

    public function test_non_admin_cannot_toggle_activation(): void
    {
        $supervisor = User::factory()->supervisor()->create();
        $student = $this->student(true, $supervisor);

        $this->actingAs($supervisor)
            ->post(route('admin.students.toggleActive', $student->id))
            ->assertForbidden();

        $this->assertTrue($student->user->fresh()->is_active);
    }

    public function test_toggle_active_handles_student_without_user(): void
    {
        $admin = User::factory()->admin()->create();

        $orphan = new Student();

        session()->start();
        $this->actingAs($admin);
        $response = app(\App\Http\Controllers\Admin\StudentController::class)->toggleActive($orphan);

        $this->assertTrue($response->isRedirect());
        $this->assertSame('لا يوجد حساب مستخدم مرتبط بهذا الطالب.', session('error'));
    }

    // ---------------------------------------------------------------------
    // 3. Report / stat / dropdown exclusion
    // ---------------------------------------------------------------------

    public function test_inactive_student_excluded_from_report_dropdown(): void
    {
        $active = $this->student(true);
        $inactive = $this->student(false);

        $data = app(ReportService::class)->dropdownData(new Request(), null);

        $this->assertTrue($data['students']->contains('id', $active->id));
        $this->assertFalse($data['students']->contains('id', $inactive->id));
    }

    public function test_inactive_student_excluded_from_leaderboard(): void
    {
        $inactive = $this->student(false);
        $active = $this->student(true);

        $board = app(LeaderboardService::class)
            ->buildLeaderboard(LeaderboardService::PERIOD_WEEKLY, LeaderboardService::TYPE_PAGES);

        $studentIds = $board->pluck('student_id');
        $this->assertFalse($studentIds->contains($inactive->id));
        $this->assertTrue($studentIds->contains($active->id));
    }

    public function test_inactive_student_excluded_from_active_scope_and_dashboard_total(): void
    {
        $active = $this->student(true);
        $inactive = $this->student(false);

        $this->assertTrue(Student::active()->whereKey($active->id)->exists());
        $this->assertFalse(Student::active()->whereKey($inactive->id)->exists());

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    }

    public function test_historical_report_data_keeps_inactive_student(): void
    {
        $student = $this->student(true);
        $session = RecitationSession::factory()->create([
            'student_id' => $student->id,
            'part_id' => null,
            'session_date' => '2026-01-15',
            'type' => 'review',
            'status' => 'passed',
        ]);

        // Deactivate the student.
        $student->user->update(['is_active' => false]);

        $request = Request::create('/', 'GET', [
            'period' => 'custom',
            'from_date' => '2026-01-01',
            'to_date' => '2026-01-31',
        ]);

        $rows = app(ReportService::class)->recitationQuery($request, null, true)->get();

        $this->assertTrue($rows->contains('id', $session->id));
    }

    // ---------------------------------------------------------------------
    // 4. Automatic jobs skip inactive students
    // ---------------------------------------------------------------------

    public function test_missed_recitation_scan_skips_inactive_student(): void
    {
        $supervisor = User::factory()->supervisor()->create();
        $student = $this->student(false, $supervisor);

        Attendance::factory()->create([
            'student_id' => $student->id,
            'supervisor_id' => $supervisor->id,
            'date' => '2026-01-03',
            'status' => 'present',
        ]);

        $result = app(MissedRecitationService::class)->run(Carbon::parse('2026-01-04 01:00:00'));

        $this->assertSame(0, $result['created']);
        $this->assertDatabaseMissing('recitation_sessions', [
            'student_id' => $student->id,
            'auto_generated' => true,
        ]);
    }

    public function test_missed_recitation_scan_still_creates_for_active_student(): void
    {
        $supervisor = User::factory()->supervisor()->create();
        $student = $this->student(true, $supervisor);

        Attendance::factory()->create([
            'student_id' => $student->id,
            'supervisor_id' => $supervisor->id,
            'date' => '2026-01-03',
            'status' => 'present',
        ]);

        $result = app(MissedRecitationService::class)->run(Carbon::parse('2026-01-04 01:00:00'));

        $this->assertSame(1, $result['created']);
    }

    public function test_attendance_reminder_counts_only_active_students(): void
    {
        $supervisor = User::factory()->supervisor()->create();
        $this->student(true, $supervisor);
        $this->student(false, $supervisor);

        // Default work days cover every day; run after the work-day end time.
        $now = Carbon::parse('2026-01-03 20:30:00');

        $sent = app(AttendanceReminderService::class)->sendEndOfDayReminders($now);

        $this->assertGreaterThanOrEqual(1, $sent);

        $notification = Notification::where('user_id', $supervisor->id)
            ->where('type', 'attendance')
            ->first();

        $this->assertNotNull($notification);
        $this->assertStringContainsString('1 طالب', $notification->message);
    }

    // ---------------------------------------------------------------------
    // 5. Notification recipient guard
    // ---------------------------------------------------------------------

    public function test_notification_service_skips_inactive_recipient(): void
    {
        $inactive = User::factory()->parent()->inactive()->create();

        $result = app(NotificationService::class)
            ->sendToUser($inactive, 'test', 'عنوان', 'نص');

        $this->assertNull($result);
        $this->assertSame(0, Notification::where('user_id', $inactive->id)->count());
    }
}
