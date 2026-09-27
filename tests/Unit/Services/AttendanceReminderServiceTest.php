<?php

namespace Tests\Unit\Services;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use App\Services\AttendanceReminderService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AttendanceReminderServiceTest extends TestCase
{
    use RefreshDatabase;

    private AttendanceReminderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(AttendanceReminderService::class);
    }

    private function supervisor(): User
    {
        return User::create([
            'name' => 'مشرف', 'username' => 'sup_' . uniqid('', true),
            'email' => uniqid('', true) . '@test.local',
            'password' => Hash::make('secret'), 'role' => 'supervisor', 'is_active' => true,
        ]);
    }

    private function studentFor(User $supervisor): Student
    {
        return Student::factory()->create(['supervisor_id' => $supervisor->id]);
    }

    private function runAt(string $now): int
    {
        return $this->service->sendEndOfDayReminders(Carbon::parse($now));
    }

    public function test_reminds_supervisor_when_attendance_is_missing_after_work_hours(): void
    {
        config(['app.work_days' => 'saturday', 'app.work_end_time' => '20:00']);

        $supervisor = $this->supervisor();
        $this->studentFor($supervisor);
        $this->studentFor($supervisor);

        $sent = $this->runAt('2026-01-03 20:05:00'); // Saturday

        $this->assertSame(1, $sent);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $supervisor->id,
            'type'    => 'attendance',
        ]);
    }

    public function test_does_not_remind_before_the_work_day_ends(): void
    {
        config(['app.work_days' => 'saturday', 'app.work_end_time' => '20:00']);

        $supervisor = $this->supervisor();
        $this->studentFor($supervisor);

        $sent = $this->runAt('2026-01-03 12:00:00');

        $this->assertSame(0, $sent);
        $this->assertDatabaseMissing('notifications', ['type' => 'attendance']);
    }

    public function test_does_not_remind_on_a_non_work_day(): void
    {
        config(['app.work_days' => 'saturday', 'app.work_end_time' => '20:00']);

        $supervisor = $this->supervisor();
        $this->studentFor($supervisor);

        $sent = $this->runAt('2026-01-04 20:05:00'); // Sunday

        $this->assertSame(0, $sent);
        $this->assertDatabaseMissing('notifications', ['type' => 'attendance']);
    }

    public function test_does_not_remind_when_all_students_have_attendance(): void
    {
        config(['app.work_days' => 'saturday', 'app.work_end_time' => '20:00']);

        $supervisor = $this->supervisor();
        $student = $this->studentFor($supervisor);

        Attendance::factory()->create([
            'student_id'    => $student->id,
            'supervisor_id' => $supervisor->id,
            'date'          => '2026-01-03',
            'status'        => 'present',
        ]);

        $sent = $this->runAt('2026-01-03 20:05:00');

        $this->assertSame(0, $sent);
        $this->assertDatabaseMissing('notifications', ['type' => 'attendance']);
    }

    public function test_reminds_with_the_number_of_missing_students(): void
    {
        config(['app.work_days' => 'saturday', 'app.work_end_time' => '20:00']);

        $supervisor = $this->supervisor();
        $recorded = $this->studentFor($supervisor);
        $this->studentFor($supervisor);
        $this->studentFor($supervisor);

        Attendance::factory()->create([
            'student_id'    => $recorded->id,
            'supervisor_id' => $supervisor->id,
            'date'          => '2026-01-03',
            'status'        => 'present',
        ]);

        $sent = $this->runAt('2026-01-03 20:05:00');

        $this->assertSame(1, $sent);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $supervisor->id,
            'type'    => 'attendance',
        ]);
        $this->assertStringContainsString('2 طالب', (string) \DB::table('notifications')
            ->where('user_id', $supervisor->id)->value('message'));
    }

    public function test_only_reminds_once_per_calendar_day(): void
    {
        config(['app.work_days' => 'saturday', 'app.work_end_time' => '20:00']);

        $supervisor = $this->supervisor();
        $this->studentFor($supervisor);

        $first = $this->runAt('2026-01-03 20:05:00');
        $second = $this->runAt('2026-01-03 21:00:00');

        $this->assertSame(1, $first);
        $this->assertSame(0, $second);
        $this->assertSame(1, \DB::table('notifications')
            ->where('user_id', $supervisor->id)
            ->where('type', 'attendance')
            ->count());
    }

    public function test_does_not_remind_supervisor_without_students(): void
    {
        config(['app.work_days' => 'saturday', 'app.work_end_time' => '20:00']);

        $supervisor = $this->supervisor();

        $sent = $this->runAt('2026-01-03 20:05:00');

        $this->assertSame(0, $sent);
        $this->assertDatabaseMissing('notifications', ['type' => 'attendance']);
    }

    public function test_visit_trigger_middleware_sends_the_reminder(): void
    {
        config([
            'app.work_days' => 'saturday',
            'app.work_end_time' => '20:00',
            'attendance.reminder_on_visits' => true,
        ]);

        $supervisor = $this->supervisor();
        $this->studentFor($supervisor);

        Carbon::setTestNow(Carbon::parse('2026-01-03 20:05:00'));

        try {
            $middleware = app(\App\Http\Middleware\TriggerAttendanceReminder::class);
            $middleware->terminate(request(), response(''));

            $this->assertDatabaseHas('notifications', [
                'user_id' => $supervisor->id,
                'type'    => 'attendance',
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }
}
