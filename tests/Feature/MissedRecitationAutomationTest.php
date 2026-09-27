<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Level;
use App\Models\RecitationSession;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MissedRecitationAutomationTest extends TestCase
{
    use RefreshDatabase;

    private User $supervisor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->supervisor = User::factory()->supervisor()->create();
    }

    private function student(): Student
    {
        $level = Level::create([
            'name' => 'مستوى', 'level_number' => 1,
            'new_memorization_pages' => 3, 'review_pages' => 6, 'description' => 'x',
        ]);

        return Student::create([
            'user_id' => User::factory()->student()->create()->id,
            'supervisor_id' => $this->supervisor->id,
            'level_id' => $level->id,
            'student_number' => 'STU-' . uniqid(),
            'memorized_parts' => 0,
            'current_part' => 1,
            'current_page' => 1,
        ]);
    }

    private function attendance(Student $student, string $date, string $status = 'present'): Attendance
    {
        return Attendance::factory()->create([
            'student_id' => $student->id,
            'supervisor_id' => $this->supervisor->id,
            'date' => $date,
            'status' => $status,
        ]);
    }

    public function test_supervisor_dashboard_creates_missing_session_after_required_day_ends(): void
    {
        $student = $this->student();
        // 2026-01-03 is a Saturday (required); scan runs the next day.
        $this->attendance($student, '2026-01-03');

        Carbon::setTestNow(Carbon::parse('2026-01-04 09:00:00'));
        try {
            $this->actingAs($this->supervisor)
                ->get(route('supervisor.dashboard'))
                ->assertOk();
        } finally {
            Carbon::setTestNow();
        }

        $this->assertDatabaseHas('recitation_sessions', [
            'student_id' => $student->id,
            'type' => RecitationSession::TYPE_NOT_RECITE,
            'session_date' => '2026-01-03',
            'auto_generated' => true,
        ]);
    }

    public function test_second_scan_same_day_does_not_duplicate(): void
    {
        $student = $this->student();
        $this->attendance($student, '2026-01-03');

        Carbon::setTestNow(Carbon::parse('2026-01-04 09:00:00'));
        try {
            $this->actingAs($this->supervisor)->get(route('supervisor.dashboard'))->assertOk();
            $this->actingAs($this->supervisor)->get(route('supervisor.attendance.index'))->assertOk();
        } finally {
            Carbon::setTestNow();
        }

        $this->assertSame(1, RecitationSession::where('auto_generated', true)->count());
    }

    public function test_cron_url_rejects_wrong_key(): void
    {
        config(['recitation.cron_key' => 'secret123']);

        $this->get('/cron/missed-recitation?key=wrong')->assertForbidden();
    }

    public function test_cron_url_runs_with_correct_key(): void
    {
        config(['recitation.cron_key' => 'secret123']);
        $student = $this->student();
        $this->attendance($student, '2026-01-03');

        Carbon::setTestNow(Carbon::parse('2026-01-04 09:00:00'));
        try {
            $this->get('/cron/missed-recitation?key=secret123')
                ->assertOk()
                ->assertJsonPath('success', true)
                ->assertJsonPath('created', 1);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_auto_scan_respects_the_auto_lookback_window(): void
    {
        config(['recitation.auto_lookback_days' => 7]);
        $student = $this->student();

        // 20 days ago is outside the 7-day auto window.
        $old = now()->subDays(20);
        // Keep it on a required day so the only reason it is skipped is the window.
        while (! in_array($old->dayOfWeek, [6, 2, 4], true)) {
            $old->subDay();
        }
        $this->attendance($student, $old->toDateString());

        $result = app(\App\Services\MissedRecitationService::class)->run(now());

        $this->assertSame(0, $result['created']);
        $this->assertSame(0, RecitationSession::where('auto_generated', true)->count());
    }

    public function test_backfill_preview_does_not_create_and_run_range_creates(): void
    {
        $student = $this->student();
        $from = now()->subDays(40);
        while (! in_array($from->dayOfWeek, [6, 2, 4], true)) {
            $from->subDay();
        }
        $this->attendance($student, $from->toDateString());

        $service = app(\App\Services\MissedRecitationService::class);
        $count = $service->previewRange(now(), $from->copy(), $from->copy());

        $this->assertSame(1, $count);
        $this->assertSame(0, RecitationSession::where('auto_generated', true)->count());

        $service->runRange(now(), $from->copy(), $from->copy());
        $this->assertSame(1, RecitationSession::where('auto_generated', true)->count());
    }

    public function test_saving_real_session_removes_stale_auto_not_recite_but_keeps_manual(): void
    {
        $student = $this->student();
        $date = now()->toDateString();

        // An auto "لم يسمع" and an unrelated manual "لم يسمع" for another day.
        $auto = RecitationSession::create([
            'student_id' => $student->id, 'supervisor_id' => $this->supervisor->id,
            'type' => RecitationSession::TYPE_NOT_RECITE, 'status' => RecitationSession::STATUS_NOT_RECITE,
            'session_date' => $date, 'auto_generated' => true,
        ]);
        $manualOtherDay = RecitationSession::create([
            'student_id' => $student->id, 'supervisor_id' => $this->supervisor->id,
            'type' => RecitationSession::TYPE_NOT_RECITE, 'status' => RecitationSession::STATUS_NOT_RECITE,
            'session_date' => now()->subDay()->toDateString(), 'auto_generated' => false,
        ]);

        // Save a real review session for the same day.
        $part = \App\Models\QuranPart::firstOrCreate(
            ['part_number' => 1],
            ['name' => 'الجزء 1', 'pages_count' => 20, 'start_page' => 1, 'end_page' => 20]
        );
        \App\Models\QuranPage::firstOrCreate(['page_number' => 1], [
            'part_id' => $part->id, 'page_in_part' => 1,
        ]);

        $this->actingAs($this->supervisor)
            ->postJson(route('supervisor.recitation.save'), [
                'student_id' => $student->id,
                'type' => 'review',
                'part_id' => $part->id,
                'session_date' => $date,
                'pages' => [['page_number' => 1, 'errors' => 0, 'hesitations' => 0]],
            ])
            ->assertOk();

        $this->assertDatabaseMissing('recitation_sessions', ['id' => $auto->id]);
        $this->assertDatabaseHas('recitation_sessions', ['id' => $manualOtherDay->id]);
    }

    public function test_auto_not_recite_does_not_notify_parent(): void
    {
        $student = $this->student();
        $this->attendance($student, '2026-01-03');

        Carbon::setTestNow(Carbon::parse('2026-01-04 09:00:00'));
        try {
            $this->actingAs($this->supervisor)->get(route('supervisor.dashboard'))->assertOk();
        } finally {
            Carbon::setTestNow();
        }

        $this->assertSame(0, \App\Models\Notification::where('type', 'recitation_session')->count());
    }

    public function test_diagnostics_page_is_admin_only(): void
    {
        $this->actingAs($this->supervisor)
            ->get(route('admin.recitation-diagnostics.index'))
            ->assertForbidden();

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)
            ->get(route('admin.recitation-diagnostics.index'))
            ->assertOk();
    }
}
