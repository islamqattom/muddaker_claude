<?php

namespace Tests\Unit\Services;

use App\Models\RecitationSession;
use App\Models\Student;
use App\Models\User;
use App\Services\RecitationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecitationServiceTest extends TestCase
{
    use RefreshDatabase;

    private RecitationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RecitationService();
    }

    public function test_ensure_not_recitate_sessions_creates_session_for_present_student_with_no_recitation(): void
    {
        $student = Student::factory()->create();
        $date = '2026-05-11';

        $this->service->ensureNotRecitateSessions([$student->id], $student->supervisor_id, $date);

        $this->assertDatabaseHas('recitation_sessions', [
            'student_id' => $student->id,
            'supervisor_id' => $student->supervisor_id,
            'type' => RecitationSession::TYPE_NOT_RECITE,
            'status' => RecitationSession::STATUS_NOT_RECITE,
            'session_date' => $date,
        ]);
    }

    public function test_ensure_not_recitate_sessions_does_not_duplicate_when_session_exists(): void
    {
        $student = Student::factory()->create();
        $date = '2026-05-11';

        RecitationSession::factory()->create([
            'student_id' => $student->id,
            'session_date' => $date,
            'type' => 'review',
            'status' => 'passed',
        ]);

        $this->service->ensureNotRecitateSessions([$student->id], $student->supervisor_id, $date);

        $count = RecitationSession::where('student_id', $student->id)
            ->where('session_date', $date)
            ->count();
        $this->assertEquals(1, $count);
    }

    public function test_ensure_not_recitate_sessions_creates_for_multiple_students(): void
    {
        $student1 = Student::factory()->create();
        $student2 = Student::factory()->create();
        $date = '2026-05-11';

        $this->service->ensureNotRecitateSessions(
            [$student1->id, $student2->id],
            $student1->supervisor_id,
            $date
        );

        $this->assertDatabaseHas('recitation_sessions', [
            'student_id' => $student1->id,
            'session_date' => $date,
        ]);
        $this->assertDatabaseHas('recitation_sessions', [
            'student_id' => $student2->id,
            'session_date' => $date,
        ]);
    }

    public function test_ensure_not_recitate_sessions_skips_students_with_existing_session(): void
    {
        $student1 = Student::factory()->create();
        $student2 = Student::factory()->create();
        $date = '2026-05-11';

        RecitationSession::factory()->create([
            'student_id' => $student1->id,
            'session_date' => $date,
            'type' => 'recitation',
            'status' => 'passed',
        ]);

        $this->service->ensureNotRecitateSessions(
            [$student1->id, $student2->id],
            $student1->supervisor_id,
            $date
        );

        $count = RecitationSession::where('session_date', $date)->count();
        // student1's existing session + student2's new not_recitate session
        $this->assertEquals(2, $count);
        $this->assertDatabaseMissing('recitation_sessions', [
            'student_id' => $student1->id,
            'type' => RecitationSession::TYPE_NOT_RECITE,
        ]);
        $this->assertDatabaseHas('recitation_sessions', [
            'student_id' => $student2->id,
            'type' => RecitationSession::TYPE_NOT_RECITE,
            'status' => RecitationSession::STATUS_NOT_RECITE,
            'session_date' => $date,
        ]);
    }

    public function test_ensure_not_recitate_sessions_does_nothing_for_empty_array(): void
    {
        $student = Student::factory()->create();

        $this->service->ensureNotRecitateSessions([], $student->supervisor_id, '2026-05-11');

        $this->assertEquals(0, RecitationSession::count());
    }

    public function test_ensure_not_recitate_sessions_sets_correct_notes(): void
    {
        $student = Student::factory()->create();
        $date = '2026-05-11';

        $this->service->ensureNotRecitateSessions([$student->id], $student->supervisor_id, $date);

        $session = RecitationSession::where('student_id', $student->id)->first();
        $this->assertEquals('تسميع تلقائي - لم يتم تسجيل تسميع', $session->notes);
    }
}