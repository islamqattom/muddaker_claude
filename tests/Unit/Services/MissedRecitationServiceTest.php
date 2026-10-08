<?php

namespace Tests\Unit\Services;

use App\Models\Attendance;
use App\Models\RecitationSession;
use App\Models\Student;
use App\Services\MissedRecitationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MissedRecitationServiceTest extends TestCase
{
    use RefreshDatabase;

    private MissedRecitationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MissedRecitationService;
    }

    private function attendance(string $date, string $status, string $createdAt, ?Student $student = null): Attendance
    {
        $student ??= Student::factory()->create();

        return Attendance::factory()->create([
            'student_id' => $student->id,
            'supervisor_id' => $student->supervisor_id,
            'date' => $date,
            'status' => $status,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }

    private function runAt(string $now)
    {
        return $this->service->run(Carbon::parse($now));
    }

    public function test_creates_session_for_present_student_after_the_required_day_ends(): void
    {
        // 2026-01-03 is a Saturday; the following day has begun.
        $attendance = $this->attendance('2026-01-03', 'present', '2026-01-02 10:00:00');

        $result = $this->runAt('2026-01-04 00:05:00');

        $this->assertSame(1, $result['created']);
        $this->assertDatabaseHas('recitation_sessions', [
            'student_id' => $attendance->student_id,
            'supervisor_id' => $attendance->supervisor_id,
            'type' => RecitationSession::TYPE_NOT_RECITE,
            'status' => RecitationSession::STATUS_NOT_RECITE,
            'session_date' => '2026-01-03',
            'attendance_id' => $attendance->id,
            'auto_generated' => true,
        ]);
    }

    public function test_tuesday_is_a_required_day(): void
    {
        // 2026-01-06 is a Tuesday.
        $this->attendance('2026-01-06', 'present', '2026-01-05 10:00:00');

        $result = $this->runAt('2026-01-07 00:05:00');

        $this->assertSame(1, $result['created']);
    }

    public function test_friday_is_fully_excluded(): void
    {
        // 2026-01-02 is a Friday.
        $this->attendance('2026-01-02', 'present', '2026-01-01 10:00:00');

        $result = $this->runAt('2026-01-03 00:05:00');

        $this->assertSame(0, $result['created']);
        $this->assertSame(0, RecitationSession::count());
    }

    public function test_does_not_create_before_the_recitation_day_ends(): void
    {
        // Saturday, still the same day.
        $this->attendance('2026-01-03', 'present', '2026-01-03 07:00:00');

        $sameDay = $this->runAt('2026-01-03 23:59:00');
        $this->assertSame(0, $sameDay['created']);
        $this->assertSame(0, RecitationSession::count());

        // At the start of the following day the rule fires.
        $nextDay = $this->runAt('2026-01-04 00:01:00');
        $this->assertSame(1, $nextDay['created']);
    }

    public function test_does_not_create_when_a_session_already_exists_for_the_day(): void
    {
        $student = Student::factory()->create();
        $this->attendance('2026-01-03', 'present', '2026-01-02 10:00:00', $student);

        RecitationSession::factory()->create([
            'student_id' => $student->id,
            'session_date' => '2026-01-03',
            'type' => 'review',
            'status' => 'passed',
        ]);

        $result = $this->runAt('2026-01-04 00:05:00');

        $this->assertSame(0, $result['created']);
        $this->assertSame(1, RecitationSession::where('student_id', $student->id)->count());
    }

    public function test_is_idempotent_across_multiple_runs(): void
    {
        $this->attendance('2026-01-03', 'present', '2026-01-02 10:00:00');

        $this->runAt('2026-01-04 00:05:00');
        $this->runAt('2026-01-04 01:00:00');
        $this->runAt('2026-01-04 02:00:00');

        $this->assertSame(1, RecitationSession::where('auto_generated', true)->count());
    }

    public function test_does_not_create_for_excused_student(): void
    {
        $this->attendance('2026-01-03', 'excused', '2026-01-02 10:00:00');

        $result = $this->runAt('2026-01-04 00:05:00');

        $this->assertSame(0, $result['created']);
        $this->assertSame(0, RecitationSession::count());
    }

    public function test_late_is_treated_as_attended(): void
    {
        $this->attendance('2026-01-03', 'late', '2026-01-02 10:00:00');

        $result = $this->runAt('2026-01-04 00:05:00');

        $this->assertSame(1, $result['created']);
    }

    public function test_non_required_day_is_skipped(): void
    {
        // 2026-01-05 is a Monday (not a recitation day).
        $this->attendance('2026-01-05', 'present', '2026-01-04 10:00:00');

        $result = $this->runAt('2026-01-06 00:05:00');

        $this->assertSame(0, $result['created']);
    }

    public function test_backdated_attendance_still_triggers_after_the_day_ends(): void
    {
        // Attendance for a required day entered a few days later, still inside
        // the automatic 7-day window.
        $this->attendance('2026-01-03', 'present', '2026-01-07 09:00:00');

        $result = $this->runAt('2026-01-08 00:05:00');

        $this->assertSame(1, $result['created']);
    }

    public function test_auto_session_is_removed_when_attendance_becomes_excused(): void
    {
        $attendance = $this->attendance('2026-01-03', 'present', '2026-01-02 10:00:00');

        $this->runAt('2026-01-04 00:05:00');
        $this->assertSame(1, RecitationSession::where('auto_generated', true)->count());

        // The supervisor later excuses the student.
        $attendance->update(['status' => 'excused']);

        $result = $this->runAt('2026-01-04 01:00:00');

        $this->assertSame(1, $result['cleaned']);
        $this->assertSame(0, RecitationSession::where('auto_generated', true)->count());
    }

    public function test_system_actor_is_recorded_in_audit_log(): void
    {
        $this->attendance('2026-01-03', 'present', '2026-01-02 10:00:00');

        $this->runAt('2026-01-04 00:05:00');

        $this->assertDatabaseHas('audit_logs', [
            'role' => 'system',
            'action' => 'created',
            'model_type' => RecitationSession::class,
        ]);
    }

    public function test_command_runs_and_reports_created_sessions(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-01-04 00:30:00'));

        try {
            $this->attendance('2026-01-03', 'present', '2026-01-02 10:00:00');

            $this->artisan('recitation:register-missed')
                ->assertSuccessful();

            $this->assertSame(1, RecitationSession::where('auto_generated', true)->count());
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_run_once_per_day_scans_at_most_once_per_calendar_day(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-01-04 00:05:00'));

        try {
            $this->attendance('2026-01-03', 'present', '2026-01-02 10:00:00');

            $first = $this->service->runOncePerDay();
            $this->assertFalse($first['already_ran']);
            $this->assertSame(1, $first['created']);
            $this->assertSame(1, RecitationSession::where('auto_generated', true)->count());

            // Another eligible record (a required Tuesday) appears later the
            // same day, after the first scan already ran.
            $this->attendance('2025-12-30', 'present', '2025-12-30 10:00:00');

            $second = $this->service->runOncePerDay();
            $this->assertTrue($second['already_ran']);
            $this->assertSame(0, $second['created']);
            // Not processed until the next calendar day.
            $this->assertSame(1, RecitationSession::where('auto_generated', true)->count());

            Carbon::setTestNow(Carbon::parse('2026-01-05 00:05:00'));

            $third = $this->service->runOncePerDay();
            $this->assertFalse($third['already_ran']);
            $this->assertSame(1, $third['created']);
            $this->assertSame(2, RecitationSession::where('auto_generated', true)->count());
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_visit_trigger_middleware_runs_the_scan(): void
    {
        config(['recitation.scan_on_visits' => true]);
        Carbon::setTestNow(Carbon::parse('2026-01-04 00:05:00'));

        try {
            $this->attendance('2026-01-03', 'present', '2026-01-02 10:00:00');

            $middleware = app(\App\Http\Middleware\TriggerMissedRecitationScan::class);
            $middleware->terminate(request(), response(''));

            $this->assertSame(1, RecitationSession::where('auto_generated', true)->count());
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_preview_range_details_lists_only_days_with_new_sessions(): void
    {
        // Saturday (required): a present student with no session.
        $pending = Student::factory()->create();
        Attendance::factory()->create([
            'student_id' => $pending->id,
            'supervisor_id' => $pending->supervisor_id,
            'date' => '2026-01-03',
            'status' => 'present',
        ]);

        // Same required day, but this student already has a session.
        $recorded = Student::factory()->create();
        Attendance::factory()->create([
            'student_id' => $recorded->id,
            'supervisor_id' => $recorded->supervisor_id,
            'date' => '2026-01-03',
            'status' => 'present',
        ]);
        RecitationSession::factory()->create([
            'student_id' => $recorded->id,
            'session_date' => '2026-01-03',
            'type' => 'review',
            'status' => 'passed',
        ]);

        // Monday is not a required day and must not appear.
        $monday = Student::factory()->create();
        Attendance::factory()->create([
            'student_id' => $monday->id,
            'supervisor_id' => $monday->supervisor_id,
            'date' => '2026-01-05',
            'status' => 'present',
        ]);

        $now = Carbon::parse('2026-01-10 09:00:00');
        $from = Carbon::parse('2026-01-01');
        $to = Carbon::parse('2026-01-07');

        $details = $this->service->previewRangeDetails($now, $from, $to);

        $this->assertSame(1, $details['total']);
        $this->assertCount(1, $details['days']);
        $this->assertSame('2026-01-03', $details['days'][0]['date']);
        $this->assertSame(2, $details['days'][0]['present_count']);
        $this->assertSame(1, $details['days'][0]['already_has_session_count']);
        $this->assertSame(1, $details['days'][0]['will_create_count']);

        // The breakdown total must match the plain counter.
        $this->assertSame($details['total'], $this->service->previewRange($now, $from, $to));
    }

    public function test_manual_preview_and_range_include_the_same_day(): void
    {
        // Saturday, still the same day; the student is present with no session.
        $attendance = $this->attendance('2026-01-03', 'present', '2026-01-03 07:00:00');

        $now = Carbon::parse('2026-01-03 10:00:00');
        $from = Carbon::parse('2026-01-03');
        $to = Carbon::parse('2026-01-03');

        // Manual preview shows the same-day record.
        $details = $this->service->previewRangeDetails($now, $from, $to);
        $this->assertSame(1, $details['total']);
        $this->assertSame(1, $this->service->previewRange($now, $from, $to));

        // Manual backfill (admin approval) creates it on the same day.
        $result = $this->service->runRange($now, $from, $to);
        $this->assertSame(1, $result['created']);
        $this->assertDatabaseHas('recitation_sessions', [
            'student_id' => $attendance->student_id,
            'session_date' => '2026-01-03',
            'type' => RecitationSession::TYPE_NOT_RECITE,
            'attendance_id' => $attendance->id,
            'auto_generated' => true,
        ]);
    }
}
