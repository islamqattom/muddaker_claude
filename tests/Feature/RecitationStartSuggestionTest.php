<?php

namespace Tests\Feature;

use App\Models\Level;
use App\Models\QuranPage;
use App\Models\QuranPart;
use App\Models\RecitationPage;
use App\Models\RecitationSession;
use App\Models\Student;
use App\Models\User;
use App\Services\RecitationStartSuggestionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecitationStartSuggestionTest extends TestCase
{
    use RefreshDatabase;

    private User $supervisor;
    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (QuranPart::all() as $stalePart) {
            RecitationSession::where('part_id', $stalePart->id)->update(['part_id' => null]);
            QuranPage::where('part_id', $stalePart->id)->delete();
            $stalePart->delete();
        }

        $this->supervisor = User::factory()->supervisor()->create();

        $level = Level::create([
            'name' => 'المستوى الأول',
            'level_number' => 1,
            'new_memorization_pages' => 3,
            'review_pages' => 6,
            'description' => 'اختبار',
        ]);

        $studentUser = User::factory()->student()->create();

        $this->student = Student::create([
            'user_id' => $studentUser->id,
            'supervisor_id' => $this->supervisor->id,
            'level_id' => $level->id,
            'student_number' => 'STU-' . uniqid(),
            'memorized_parts' => 0,
            'current_part' => 1,
            'current_page' => 1,
        ]);
    }

    private function makePart(int $number, int $start, int $end): QuranPart
    {
        $part = QuranPart::create([
            'part_number' => $number,
            'name' => 'الجزء ' . $number,
            'pages_count' => $end - $start + 1,
            'start_page' => $start,
            'end_page' => $end,
        ]);

        for ($page = $start; $page <= $end; $page++) {
            QuranPage::create([
                'part_id' => $part->id,
                'page_number' => $page,
                'page_in_part' => $page - $start + 1,
                'surah_name' => null,
            ]);
        }

        return $part;
    }

    private function createSession(
        string $type,
        QuranPart $part,
        array $passedPages,
        array $failedPages = [],
        ?string $date = null,
        string $status = RecitationSession::STATUS_PASSED,
        array $notCompletedPages = []
    ): RecitationSession {
        $allPages = array_merge($passedPages, $failedPages, $notCompletedPages);
        sort($allPages);

        $session = RecitationSession::create([
            'student_id' => $this->student->id,
            'student_name' => $this->student->name,
            'supervisor_id' => $this->supervisor->id,
            'type' => $type,
            'part_id' => $part->id,
            'session_date' => $date ?? now()->toDateString(),
            'status' => $status,
            'start_page' => $allPages ? min($allPages) : null,
            'end_page' => $allPages ? max($allPages) : null,
            'total_pages' => count($allPages),
            'total_errors' => count($failedPages) * 3,
            'total_hesitations' => 0,
            'final_score' => 90,
        ]);

        foreach ($allPages as $pageNumber) {
            $failed = in_array($pageNumber, $failedPages, true);
            $notCompleted = in_array($pageNumber, $notCompletedPages, true);
            RecitationPage::create([
                'session_id' => $session->id,
                'page_number' => $pageNumber,
                'errors' => $failed ? 3 : 0,
                'hesitations' => 0,
                'color_status' => ($failed || $notCompleted) ? 'black' : 'green',
                'is_passed' => ! $failed && ! $notCompleted,
                'is_completed' => ! $notCompleted,
            ]);
        }

        return $session;
    }

    private function suggest(string $type)
    {
        return $this->actingAs($this->supervisor)->getJson(route('supervisor.alerts.suggestion', [
            'student' => $this->student,
            'type' => $type,
        ]))->assertOk();
    }

    public function test_new_memorization_suggests_next_page_after_last_pass(): void
    {
        $part = $this->makePart(1, 1, 20);
        $this->createSession(RecitationSession::TYPE_NEW_MEMORIZATION, $part, range(1, 5));

        $response = $this->suggest('new_memorization');

        $response->assertJsonPath('data.page_number', 6);
        $response->assertJsonPath('data.part_number', 1);
        $this->assertStringContainsString('استكمال بعد صفحة 5', $response->json('data.reason'));
    }

    public function test_new_memorization_continues_after_last_session_when_there_is_a_failed_page(): void
    {
        $part = $this->makePart(1, 1, 20);
        $this->createSession(RecitationSession::TYPE_NEW_MEMORIZATION, $part, [1, 2, 4, 5], [3]);

        $response = $this->suggest('new_memorization');

        // Start from the end of the last session (page 6), not the failed page.
        $response->assertJsonPath('data.page_number', 6);
        $this->assertStringContainsString('استكمال بعد صفحة 5', $response->json('data.reason'));
    }

    public function test_failed_pages_of_last_session_are_reported_with_part_info(): void
    {
        $part = $this->makePart(1, 1, 20);
        $this->createSession(RecitationSession::TYPE_NEW_MEMORIZATION, $part, [1, 2, 4, 5], [3]);

        $response = $this->actingAs($this->supervisor)->getJson(route('supervisor.alerts.failed-pages', [
            'student' => $this->student,
            'type' => 'new_memorization',
        ]))->assertOk();

        $response->assertJsonCount(1, 'data.pages');
        $response->assertJsonPath('data.pages.0.page_number', 3);
        $response->assertJsonPath('data.pages.0.part_number', 1);
    }

    public function test_review_continues_after_last_session_end_not_failed_page(): void
    {
        $part = $this->makePart(1, 1, 20);
        $this->createSession(RecitationSession::TYPE_NEW_MEMORIZATION, $part, range(1, 20));
        $this->createSession(RecitationSession::TYPE_REVIEW, $part, [1, 2, 4, 5], [3]);

        $response = $this->suggest('review');

        $response->assertJsonPath('data.page_number', 6);
        $this->assertStringContainsString('استكمال بعد صفحة 5', $response->json('data.reason'));
    }

    public function test_new_memorization_moves_to_next_part(): void
    {
        $part1 = $this->makePart(1, 1, 20);
        $this->makePart(2, 21, 40);
        $this->createSession(RecitationSession::TYPE_NEW_MEMORIZATION, $part1, range(1, 20));

        $response = $this->suggest('new_memorization');

        $response->assertJsonPath('data.page_number', 21);
        $response->assertJsonPath('data.part_number', 2);
    }

    public function test_new_memorization_descending_finishes_part_30_then_goes_to_part_29(): void
    {
        $part29 = $this->makePart(29, 561, 580);
        $part30 = $this->makePart(30, 581, 604);

        // A single session in part 30 is not enough to infer the trend, so the
        // fallback uses current_part (30) and memorized_parts (0) => descending.
        $this->student->update(['current_part' => 30]);
        $this->createSession(RecitationSession::TYPE_NEW_MEMORIZATION, $part30, range(581, 604));

        $response = $this->suggest('new_memorization');

        $response->assertJsonPath('data.part_number', 29);
        $response->assertJsonPath('data.page_number', 561);
        $response->assertJsonPath('data.completed', false);
    }

    public function test_pre_memorized_parts_are_skipped(): void
    {
        $part27 = $this->makePart(27, 521, 540);
        $this->makePart(28, 541, 560);
        $this->makePart(29, 561, 580);
        $this->makePart(30, 581, 604);

        $this->student->update(['memorized_parts' => 3, 'current_part' => 30]);

        // The student memorized parts 30, 29, 28 before joining; new sessions
        // are in part 27, so the suggestion must be part 27.
        $this->createSession(RecitationSession::TYPE_NEW_MEMORIZATION, $part27, [521, 522, 523]);

        $response = $this->suggest('new_memorization');

        $response->assertJsonPath('data.part_number', 27);
    }

    public function test_direction_fallback_ascending_when_current_part_is_n_plus_one(): void
    {
        $this->student->update(['memorized_parts' => 21, 'current_part' => 22]);

        $direction = app(RecitationStartSuggestionService::class)->direction($this->student);

        $this->assertSame(RecitationStartSuggestionService::DIRECTION_ASC, $direction);
    }

    public function test_direction_inferred_descending_from_session_sequence(): void
    {
        $part28 = $this->makePart(28, 541, 560);
        $part29 = $this->makePart(29, 561, 580);
        $part30 = $this->makePart(30, 581, 604);

        $this->createSession(RecitationSession::TYPE_NEW_MEMORIZATION, $part30, [581], [], now()->subDays(3)->toDateString());
        $this->createSession(RecitationSession::TYPE_NEW_MEMORIZATION, $part29, [561], [], now()->subDays(2)->toDateString());
        $this->createSession(RecitationSession::TYPE_NEW_MEMORIZATION, $part28, [541], [], now()->subDay()->toDateString());

        $direction = app(RecitationStartSuggestionService::class)->direction($this->student);

        $this->assertSame(RecitationStartSuggestionService::DIRECTION_DESC, $direction);
    }

    public function test_new_memorization_completed_when_all_parts_memorized(): void
    {
        $this->student->update(['memorized_parts' => 30]);

        $response = $this->suggest('new_memorization');

        $response->assertJsonPath('data.completed', true);
        $response->assertJsonPath('data.page_number', null);
        $this->assertStringContainsString('أتمّ', $response->json('data.reason'));
    }

    public function test_review_stays_within_memorized_and_wraps(): void
    {
        $part = $this->makePart(1, 1, 20);
        $this->createSession(RecitationSession::TYPE_NEW_MEMORIZATION, $part, range(1, 20));

        // No review sessions yet => start of a new review cycle.
        $first = $this->suggest('review');
        $first->assertJsonPath('data.page_number', 1);
        $this->assertStringContainsString('بداية دورة', $first->json('data.reason'));

        // Fully reviewed => wraps back to the first memorized page.
        $this->createSession(RecitationSession::TYPE_REVIEW, $part, range(1, 20));
        $wrapped = $this->suggest('review');
        $wrapped->assertJsonPath('data.page_number', 1);
        $this->assertStringContainsString('بداية دورة', $wrapped->json('data.reason'));
    }

    public function test_review_does_not_exceed_memorized_pages(): void
    {
        $part = $this->makePart(1, 1, 20);
        $this->createSession(RecitationSession::TYPE_NEW_MEMORIZATION, $part, range(1, 20));
        $this->createSession(RecitationSession::TYPE_REVIEW, $part, range(1, 18));

        $response = $this->suggest('review');

        $response->assertJsonPath('data.page_number', 19);
        $this->assertStringContainsString('استكمال بعد صفحة 18', $response->json('data.reason'));
    }

    public function test_suggestion_changes_with_the_selected_type(): void
    {
        $part1 = $this->makePart(1, 1, 20);
        $this->makePart(2, 21, 40);
        $this->createSession(RecitationSession::TYPE_NEW_MEMORIZATION, $part1, range(1, 20));

        $newMem = $this->suggest('new_memorization');
        $newMem->assertJsonPath('data.page_number', 21);
        $newMem->assertJsonPath('data.part_number', 2);

        $review = $this->suggest('review');
        $review->assertJsonPath('data.page_number', 1);
        $review->assertJsonPath('data.part_number', 1);
    }

    public function test_recitation_suggests_next_memorized_part(): void
    {
        $part1 = $this->makePart(1, 1, 20);
        $part2 = $this->makePart(2, 21, 40);
        $this->createSession(RecitationSession::TYPE_NEW_MEMORIZATION, $part1, range(1, 20));
        $this->createSession(RecitationSession::TYPE_NEW_MEMORIZATION, $part2, range(21, 40));
        $this->createSession(RecitationSession::TYPE_RECITATION, $part1, range(1, 20));

        $response = $this->suggest('recitation');

        $response->assertJsonPath('data.part_number', 2);
        $this->assertStringContainsString('استكمال بعد الجزء 1', $response->json('data.reason'));
    }

    public function test_no_suggestion_without_sessions_or_memorized_pages(): void
    {
        $response = $this->suggest('review');

        $response->assertJsonPath('data', null);
    }

    public function test_suggestion_is_forbidden_for_another_supervisor(): void
    {
        $otherSupervisor = User::factory()->supervisor()->create();

        $this->actingAs($otherSupervisor)
            ->getJson(route('supervisor.alerts.suggestion', ['student' => $this->student, 'type' => 'review']))
            ->assertForbidden();
    }

    public function test_not_completed_pages_do_not_block_progression(): void
    {
        $part = $this->makePart(1, 1, 20);

        // Session 10-14; the student completed 10,11,12 but not 13,14.
        $this->createSession(RecitationSession::TYPE_NEW_MEMORIZATION, $part, [10, 11, 12], [], null, RecitationSession::STATUS_INCOMPLETE, [13, 14]);

        $response = $this->suggest('new_memorization');

        // Not-completed pages behave like failed repeated pages: the session
        // advances past the previous session end (14) to 15.
        $response->assertJsonPath('data.page_number', 15);
        $response->assertJsonPath('data.part_number', 1);
        $this->assertStringContainsString('استكمال بعد صفحة 14', $response->json('data.reason'));
    }

    public function test_failed_pages_endpoint_includes_not_completed_pages_with_label(): void
    {
        $part = $this->makePart(1, 1, 20);

        // Page 13 was recited but failed; page 14 was never completed.
        $this->createSession(RecitationSession::TYPE_NEW_MEMORIZATION, $part, [10, 11, 12], [13], null, RecitationSession::STATUS_INCOMPLETE, [14]);

        $response = $this->actingAs($this->supervisor)->getJson(route('supervisor.alerts.failed-pages', [
            'student' => $this->student,
            'type' => 'new_memorization',
        ]))->assertOk();

        $response->assertJsonCount(2, 'data.pages');
        $response->assertJsonPath('data.pages.0.page_number', 13);
        $response->assertJsonPath('data.pages.0.is_completed', true);
        $response->assertJsonPath('data.pages.0.status_label', 'راسبة');
        $response->assertJsonPath('data.pages.1.page_number', 14);
        $response->assertJsonPath('data.pages.1.is_completed', false);
        $response->assertJsonPath('data.pages.1.status_label', 'لم يكمل');
    }

    public function test_saving_with_not_completed_page_marks_session_incomplete(): void
    {
        $part = $this->makePart(1, 1, 20);

        $this->actingAs($this->supervisor)
            ->postJson(route('supervisor.recitation.save'), [
                'student_id' => $this->student->id,
                'type' => 'new_memorization',
                'part_id' => $part->id,
                'session_date' => now()->toDateString(),
                'pages' => [
                    ['page_number' => 10, 'errors' => 0, 'hesitations' => 0, 'is_completed' => 1],
                    ['page_number' => 11, 'errors' => 0, 'hesitations' => 0, 'is_completed' => 0],
                ],
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $session = RecitationSession::latest('id')->first();
        $this->assertSame(RecitationSession::STATUS_INCOMPLETE, $session->status);

        $this->assertDatabaseHas('recitation_pages', [
            'session_id' => $session->id,
            'page_number' => 11,
            'is_completed' => 0,
            'is_passed' => 0,
        ]);
        $this->assertDatabaseHas('recitation_pages', [
            'session_id' => $session->id,
            'page_number' => 10,
            'is_completed' => 1,
            'is_passed' => 1,
        ]);

        $notCompleted = $session->pages()->where('page_number', 11)->first();
        $this->assertSame('لم يكمل', $notCompleted->color_label);
    }

    public function test_session_level_incomplete_marks_all_pages_not_completed(): void
    {
        $part = $this->makePart(1, 1, 20);

        $this->actingAs($this->supervisor)
            ->postJson(route('supervisor.recitation.saveIncomplete'), [
                'student_id' => $this->student->id,
                'type' => 'new_memorization',
                'part_id' => $part->id,
                'session_date' => now()->toDateString(),
                'pages' => [
                    ['page_number' => 10, 'errors' => 0, 'hesitations' => 0, 'is_completed' => 1],
                    ['page_number' => 11, 'errors' => 0, 'hesitations' => 0, 'is_completed' => 1],
                ],
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $session = RecitationSession::latest('id')->first();
        $this->assertSame(RecitationSession::STATUS_INCOMPLETE, $session->status);
        $this->assertSame(0, $session->pages()->where('is_completed', true)->count());
        $this->assertSame(0, $session->pages()->where('is_passed', true)->count());
    }
}
