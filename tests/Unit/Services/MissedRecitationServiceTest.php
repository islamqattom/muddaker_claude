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
        $this->service = new MissedRecitationService();
    }

    private function attendance(string $date, string $status, string $createdAt, ?Student $student = null): Attendance
    {
        $student ??= Student::factory()->create();

        return Attendance::factory()->create([
            'student_id'    => $student->id,
            'supervisor_id' => $student->supervisor_id,
            'date'          => $date,
            'status'        => $status,
            'created_at'    => $createdAt,
            'updated_at'    => $createdAt,
        ]);
    }

    private function runAt(string $now)
    {
        return $this->service->run(Carbon::parse($now));
    }

    public function test_creates_session_for_present_student_on_required_day_after_grace(): void
    {
        // 2026-01-03 is a Saturday; created 26h before "now".
        $attendance = $this->attendance('2026-01-03', 'present', '2026-01-02 10:00:00');

        $result = $this->runAt('2026-01-03 12:00:00');

        $this->assertSame(1, $result['created']);
        $this->assertDatabaseHas('recitation_sessions', [
            'student_id'     => $attendance->student_id,
            'supervisor_id'  => $attendance->supervisor_id,
            'type'           => RecitationSession::TYPE_NOT_RECITE,
            'status'         => RecitationSession::STATUS_NOT_RECITE,
            'session_date'   => '2026-01-03',
            'attendance_id'  => $attendance->id,
            'auto_generated' => true,
        ]);
    }

    public function test_tuesday_is_a_required_day(): void
    {
        // 2026-01-06 is a Tuesday.
        $this->attendance('2026-01-06', 'present', '2026-01-05 10:00:00');

        $result = $this->runAt('2026-01-06 12:00:00');

        $this->assertSame(1, $result['created']);
    }

    public function test_friday_is_fully_excluded(): void
    {
        // 2026-01-02 is a Friday.
        $this->attendance('2026-01-02', 'present', '2026-01-01 10:00:00');

        $result = $this->runAt('2026-01-04 12:00:00');

        $this->assertSame(0, $result['created']);
        $this->assertSame(0, RecitationSession::count());
    }

    public function test_does_not_create_before_grace_period_elapses(): void
    {
        // Saturday, but attendance was registered only 5 hours ago.
        $this->attendance('2026-01-03', 'present', '2026-01-03 07:00:00');

        $result = $this->runAt('2026-01-03 12:00:00');

        $this->assertSame(0, $result['created']);
        $this->assertSame(0, RecitationSession::count());
    }

    public function test_does_not_create_when_a_session_already_exists_for_the_day(): void
    {
        $student = Student::factory()->create();
        $this->attendance('2026-01-03', 'present', '2026-01-02 10:00:00', $student);

        RecitationSession::factory()->create([
            'student_id'   => $student->id,
            'session_date' => '2026-01-03',
            'type'         => 'review',
            'status'       => 'passed',
        ]);

        $result = $this->runAt('2026-01-03 12:00:00');

        $this->assertSame(0, $result['created']);
        $this->assertSame(1, RecitationSession::where('student_id', $student->id)->count());
    }

    public function test_is_idempotent_across_multiple_runs(): void
    {
        $this->attendance('2026-01-03', 'present', '2026-01-02 10:00:00');

        $this->runAt('2026-01-03 12:00:00');
        $this->runAt('2026-01-03 13:00:00');
        $this->runAt('2026-01-03 14:00:00');

        $this->assertSame(1, RecitationSession::where('auto_generated', true)->count());
    }

    public function test_does_not_create_for_excused_student(): void
    {
        $this->attendance('2026-01-03', 'excused', '2026-01-02 10:00:00');

        $result = $this->runAt('2026-01-03 12:00:00');

        $this->assertSame(0, $result['created']);
        $this->assertSame(0, RecitationSession::count());
    }

    public function test_late_is_treated_as_attended(): void
    {
        $this->attendance('2026-01-03', 'late', '2026-01-02 10:00:00');

        $result = $this->runAt('2026-01-03 12:00:00');

        $this->assertSame(1, $result['created']);
    }

    public function test_non_required_day_is_skipped(): void
    {
        // 2026-01-01 is a Thursday.
        $this->attendance('2026-01-01', 'present', '2025-12-31 10:00:00');

        $result = $this->runAt('2026-01-01 12:00:00');

        $this->assertSame(0, $result['created']);
    }

    public function test_auto_session_is_removed_when_attendance_becomes_excused(): void
    {
        $attendance = $this->attendance('2026-01-03', 'present', '2026-01-02 10:00:00');

        $this->runAt('2026-01-03 12:00:00');
        $this->assertSame(1, RecitationSession::where('auto_generated', true)->count());

        // The supervisor later excuses the student.
        $attendance->update(['status' => 'excused']);

        $result = $this->runAt('2026-01-03 13:00:00');

        $this->assertSame(1, $result['cleaned']);
        $this->assertSame(0, RecitationSession::where('auto_generated', true)->count());
    }

    public function test_system_actor_is_recorded_in_audit_log(): void
    {
        $this->attendance('2026-01-03', 'present', '2026-01-02 10:00:00');

        $this->runAt('2026-01-03 12:00:00');

        $this->assertDatabaseHas('audit_logs', [
            'role'       => 'system',
            'action'     => 'created',
            'model_type' => RecitationSession::class,
        ]);
    }

    public function test_command_runs_and_reports_created_sessions(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-01-03 12:00:00'));

        try {
            $this->attendance('2026-01-03', 'present', '2026-01-02 10:00:00');

            $this->artisan('recitation:register-missed')
                ->assertSuccessful();

            $this->assertSame(1, RecitationSession::where('auto_generated', true)->count());
        } finally {
            Carbon::setTestNow();
        }
    }
}
