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
            'student_number' => 'STU-'.uniqid(),
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
        while (! in_array($old->dayOfWeek, [6, 2], true)) {
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
        while (! in_array($from->dayOfWeek, [6, 2], true)) {
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

    public function test_backfill_preview_lists_only_days_with_new_sessions_and_affected_students(): void
    {
        $admin = User::factory()->admin()->create();

        // Saturday (required): one present student without a session.
        $pending = $this->student();
        $this->attendance($pending, '2026-01-03');

        // Same required day, but this student already has a session.
        $recorded = $this->student();
        $this->attendance($recorded, '2026-01-03');
        RecitationSession::factory()->create([
            'student_id' => $recorded->id,
            'session_date' => '2026-01-03',
            'type' => 'review',
            'status' => 'passed',
        ]);

        // Monday is not a required day and must not appear.
        $monday = $this->student();
        $this->attendance($monday, '2026-01-05');

        Carbon::setTestNow(Carbon::parse('2026-01-10 09:00:00'));

        try {
            $resp = $this->actingAs($admin)->getJson(route('admin.recitation-diagnostics.preview', [
                'from' => '2026-01-01',
                'to' => '2026-01-07',
            ]));

            $resp->assertOk()
                ->assertJsonPath('success', true)
                ->assertJsonPath('count', 1);

            $days = $resp->json('days');

            $this->assertCount(1, $days);
            $this->assertSame('2026-01-03', $days[0]['date']);
            $this->assertSame('السبت', $days[0]['day_name']);
            $this->assertSame(2, $days[0]['present_count']);
            $this->assertSame(1, $days[0]['already_has_session_count']);
            $this->assertSame(1, $days[0]['will_create_count']);
            $this->assertCount(1, $days[0]['students']);
            $this->assertSame($pending->user->name, $days[0]['students'][0]['name']);

            // Previewing must not create anything.
            $this->assertSame(0, RecitationSession::where('auto_generated', true)->count());
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_auto_not_recitate_sessions_get_attendance_date_and_list_in_date_order(): void
    {
        $admin = User::factory()->admin()->create();

        // Two attendances on different past required days. The auto sessions
        // created by the backfill must keep the attendance date so the admin
        // sessions list shows them in their proper date position.
        $older = $this->student();
        $this->attendance($older, '2026-01-03');

        $newer = $this->student();
        $this->attendance($newer, '2026-01-06');

        Carbon::setTestNow(Carbon::parse('2026-01-10 09:00:00'));

        try {
            $resp = $this->actingAs($admin)->post(route('admin.recitation-diagnostics.backfill'), [
                'from' => '2026-01-01',
                'to' => '2026-01-07',
            ]);

            $resp->assertSessionHas('success');

            $auto = RecitationSession::where('auto_generated', true)
                ->orderBy('id')
                ->get();

            $this->assertCount(2, $auto);

            // Every auto-created session must carry the attendance date.
            foreach ($auto as $session) {
                $this->assertContains(
                    $session->session_date->toDateString(),
                    ['2026-01-03', '2026-01-06'],
                    'session_date must equal the attendance date, not the creation date'
                );
            }

            // The admin /admin/sessions endpoint must order by session_date
            // DESC first, then by id DESC — so the older date sits below the
            // newer date, not pinned to the top by created_at.
            $listing = $this->actingAs($admin)->get(route('admin.sessions.index'));
            $listing->assertOk();

            $rows = $listing->viewData('sessions');
            $dates = $rows->pluck('session_date')->map(fn ($d) => $d->toDateString())->all();

            $this->assertSame(
                ['2026-01-06', '2026-01-03'],
                array_slice($dates, 0, 2),
                'newest session_date must come first, then older dates in their own group'
            );
        } finally {
            Carbon::setTestNow();
        }
    }
}
