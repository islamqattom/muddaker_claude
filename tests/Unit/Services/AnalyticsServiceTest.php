<?php

namespace Tests\Unit\Services;

use App\Models\Level;
use App\Models\RecitationSession;
use App\Models\Student;
use App\Models\User;
use App\Models\RecitationPage;
use App\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AnalyticsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AnalyticsService();
    }

    public function test_get_student_progress_velocity_returns_array(): void
    {
        $student = Student::factory()->create();
        $result = $this->service->getStudentProgressVelocity($student->id, 12);

        $this->assertIsArray($result);
        $this->assertCount(12, $result);
    }

    public function test_get_page_difficulty_heatmap_returns_array_with_604_pages(): void
    {
        $student = Student::factory()->create();
        $result = $this->service->getPageDifficultyHeatmap($student->id);

        $this->assertIsArray($result);
        $this->assertCount(604, $result);
    }

    public function test_get_student_progress_data_returns_student_progress_data(): void
    {
        $level = Level::factory()->create(['new_memorization_pages' => 100]);
        $student = Student::factory()->create(['level_id' => $level->id, 'current_page' => 25]);
        $result = $this->service->getStudentProgressData($student->id);

        $this->assertInstanceOf(\App\DTO\StudentProgressData::class, $result);
        $this->assertEquals(100, $result->currentLevelPagesTotal);
        $this->assertEquals(25, $result->currentLevelPagesDone);
        $this->assertEquals(25.0, $result->completionPercent);
    }

    public function test_get_supervisor_performance_returns_supervisor_performance_data(): void
    {
        $supervisor = User::factory()->supervisor()->create();
        $result = $this->service->getSupervisorPerformance($supervisor->id);

        $this->assertInstanceOf(\App\DTO\SupervisorPerformanceData::class, $result);
        $this->assertArrayHasKey('studentsCount', (array) $result);
        $this->assertArrayHasKey('evaluationsThisMonth', (array) $result);
        $this->assertArrayHasKey('pendingWarningDrafts', (array) $result);
    }

    public function test_get_all_supervisors_performance_returns_array(): void
    {
        User::factory()->supervisor()->create();
        User::factory()->supervisor()->create();
        $result = $this->service->getAllSupervisorsPerformance();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    public function test_velocity_includes_session_pages(): void
    {
        $student = Student::factory()->create();
        $supervisor = User::factory()->supervisor()->create();
        $session = RecitationSession::factory()->create([
            'student_id' => $student->id,
            'supervisor_id' => $supervisor->id,
            'type' => 'new_memorization',
            'session_date' => now()->toDateString(),
        ]);
        RecitationPage::factory()->count(5)->create(['session_id' => $session->id]);

        $result = $this->service->getStudentProgressVelocity($student->id, 12);

        $currentWeekKey = now()->format('Y-W');
        $this->assertArrayHasKey($currentWeekKey, $result);
    }

    public function test_heatmap_normalizes_error_rate_between_zero_and_one(): void
    {
        $student = Student::factory()->create();
        $supervisor = User::factory()->supervisor()->create();
        $session = RecitationSession::factory()->create([
            'student_id' => $student->id,
            'supervisor_id' => $supervisor->id,
            'session_date' => now()->toDateString(),
        ]);
        RecitationPage::factory()->create([
            'session_id' => $session->id,
            'page_number' => 10,
            'errors' => 4,
        ]);

        $result = $this->service->getPageDifficultyHeatmap($student->id);

        $this->assertNotNull($result[10]);
        $this->assertGreaterThanOrEqual(0, $result[10]);
        $this->assertLessThanOrEqual(1, $result[10]);
    }
}
