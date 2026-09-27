<?php

namespace Tests\Feature;

use App\Models\Level;
use App\Models\QuranPage;
use App\Models\QuranPart;
use App\Models\RecitationPage;
use App\Models\RecitationSession;
use App\Models\Student;
use App\Models\SupervisorAlert;
use App\Models\User;
use App\Services\RecitationAlertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupervisorRecitationAlertsTest extends TestCase
{
    use RefreshDatabase;

    private User $supervisor;
    private Student $student;
    private Level $level;

    protected function setUp(): void
    {
        parent::setUp();

        // Tests that do not wrap themselves in transactions (e.g. the report
        // breakdown tests) may leave seeded parts behind. Free the part numbers
        // this suite uses so the unique constraint does not collide.
        foreach (QuranPart::whereIn('part_number', [1, 2, 3, 4])->get() as $stalePart) {
            RecitationSession::where('part_id', $stalePart->id)->update(['part_id' => null]);
            QuranPage::where('part_id', $stalePart->id)->delete();
            $stalePart->delete();
        }

        $this->supervisor = User::factory()->supervisor()->create();

        $this->level = Level::create([
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
            'level_id' => $this->level->id,
            'student_number' => 'STU-' . uniqid(),
            'memorized_parts' => 0,
            'current_part' => 1,
            'current_page' => 1,
        ]);
    }

    private function makePart(int $number): QuranPart
    {
        $start = ($number - 1) * 20 + 1;
        $end = $number * 20;
        $part = QuranPart::create([
            'part_number' => $number,
            'name' => 'الجزء ' . $number,
            'pages_count' => 20,
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

    private function memorizePart(QuranPart $part): RecitationSession
    {
        $session = RecitationSession::create([
            'student_id' => $this->student->id,
            'student_name' => $this->student->name,
            'supervisor_id' => $this->supervisor->id,
            'type' => RecitationSession::TYPE_NEW_MEMORIZATION,
            'part_id' => $part->id,
            'session_date' => now()->toDateString(),
            'status' => RecitationSession::STATUS_PASSED,
            'start_page' => $part->start_page,
            'end_page' => $part->end_page,
            'total_pages' => (int) $part->end_page - (int) $part->start_page + 1,
            'total_errors' => 0,
            'total_hesitations' => 0,
        ]);

        for ($page = (int) $part->start_page; $page <= (int) $part->end_page; $page++) {
            RecitationPage::create([
                'session_id' => $session->id,
                'page_number' => $page,
                'errors' => 0,
                'hesitations' => 0,
                'color_status' => 'green',
                'is_passed' => true,
            ]);
        }

        return $session;
    }

    private function sardPart(QuranPart $part, string $status = RecitationSession::STATUS_PASSED): RecitationSession
    {
        $session = RecitationSession::create([
            'student_id' => $this->student->id,
            'student_name' => $this->student->name,
            'supervisor_id' => $this->supervisor->id,
            'type' => RecitationSession::TYPE_RECITATION,
            'part_id' => $part->id,
            'session_date' => now()->toDateString(),
            'status' => $status,
            'start_page' => $part->start_page,
            'end_page' => $part->end_page,
            'total_pages' => (int) $part->end_page - (int) $part->start_page + 1,
            'total_errors' => 0,
            'total_hesitations' => 0,
            'final_score' => 95,
        ]);

        for ($page = (int) $part->start_page; $page <= (int) $part->end_page; $page++) {
            RecitationPage::create([
                'session_id' => $session->id,
                'page_number' => $page,
                'errors' => 0,
                'hesitations' => 0,
                'color_status' => 'green',
                'is_passed' => true,
            ]);
        }

        return $session;
    }

    public function test_saving_new_memorization_creates_part_alert_and_shows_it_on_student_selection(): void
    {
        $part = $this->makePart(1);

        $pages = [];
        for ($page = 1; $page <= 20; $page++) {
            $pages[] = ['page_number' => $page, 'errors' => 0, 'hesitations' => 0];
        }

        $this->actingAs($this->supervisor)
            ->postJson(route('supervisor.recitation.save'), [
                'student_id' => $this->student->id,
                'type' => 'new_memorization',
                'part_id' => $part->id,
                'session_date' => now()->toDateString(),
                'pages' => $pages,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('supervisor_alerts', [
            'student_id' => $this->student->id,
            'type' => SupervisorAlert::TYPE_PART_MEMORIZED,
            'reference' => 'part_1',
            'part_number' => 1,
            'read_at' => null,
        ]);

        // Opening the new-session page must not preload or show any popup;
        // pending ids only ever come from the per-student endpoint.
        $index = $this->actingAs($this->supervisor)
            ->get(route('supervisor.recitation.index'))
            ->assertOk();
        $index->assertSee('let pendingMemorizationAlertIds = [];', false);

        // The alert is only returned once the supervisor picks the student.
        $this->actingAs($this->supervisor)
            ->getJson(route('supervisor.alerts.student', $this->student))
            ->assertOk()
            ->assertJsonPath('student_name', $this->student->name)
            ->assertJsonCount(1, 'alerts')
            ->assertJsonPath('alerts.0.part_number', 1);

        $alert = SupervisorAlert::firstOrFail();
        $this->actingAs($this->supervisor)
            ->postJson(route('supervisor.alerts.acknowledge'), ['ids' => [$alert->id]])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertNotNull($alert->fresh()->read_at);

        $this->actingAs($this->supervisor)
            ->getJson(route('supervisor.alerts.student', $this->student))
            ->assertOk()
            ->assertJsonCount(0, 'alerts');
    }

    public function test_student_alerts_are_not_exposed_to_another_supervisor(): void
    {
        $part = $this->makePart(1);
        $session = $this->memorizePart($part);
        app(RecitationAlertService::class)->alertCompletedMemorization($session);

        $otherSupervisor = User::factory()->supervisor()->create();

        $this->actingAs($otherSupervisor)
            ->getJson(route('supervisor.alerts.student', $this->student))
            ->assertForbidden();
    }

    public function test_part_alert_is_not_recreated_for_the_same_part(): void
    {
        $part = $this->makePart(1);

        $first = $this->memorizePart($part);
        $this->sardPart($part);

        $service = app(RecitationAlertService::class);
        $service->alertCompletedMemorization($first);

        $this->assertSame(1, SupervisorAlert::where('type', SupervisorAlert::TYPE_PART_MEMORIZED)->count());

        // Re-saving another new-memorization session over the same part must
        // not create a duplicate alert.
        $again = $this->memorizePart($part);
        $created = $service->alertCompletedMemorization($again);

        $this->assertTrue($created->isEmpty());
        $this->assertSame(1, SupervisorAlert::where('type', SupervisorAlert::TYPE_PART_MEMORIZED)->count());
    }

    public function test_mafsil_alert_fires_at_station_three_and_not_for_the_fourth_part(): void
    {
        $service = app(RecitationAlertService::class);

        $parts = [];
        for ($number = 1; $number <= 4; $number++) {
            $part = $this->makePart($number);
            $this->memorizePart($part);
            $parts[$number] = $part;
        }

        $this->sardPart($parts[1]);
        $this->sardPart($parts[2]);
        $thirdSard = $this->sardPart($parts[3]);

        $created = $service->alertMafsilStations($thirdSard);

        $this->assertCount(1, $created);
        $this->assertSame(3, $created->first()->station);
        $this->assertDatabaseHas('supervisor_alerts', [
            'student_id' => $this->student->id,
            'type' => SupervisorAlert::TYPE_MAFSIL_REQUIRED,
            'reference' => 'station_3',
            'station' => 3,
        ]);

        // The fourth recited part has no station of its own and must not
        // re-trigger the previous one.
        $fourthSard = $this->sardPart($parts[4]);
        $again = $service->alertMafsilStations($fourthSard);

        $this->assertTrue($again->isEmpty());
        $this->assertSame(1, SupervisorAlert::where('type', SupervisorAlert::TYPE_MAFSIL_REQUIRED)->count());
    }

    public function test_repeating_a_previous_sard_does_not_duplicate_the_mafsil_alert(): void
    {
        $service = app(RecitationAlertService::class);
        $parts = [];
        for ($number = 1; $number <= 3; $number++) {
            $part = $this->makePart($number);
            $this->memorizePart($part);
            $parts[$number] = $part;
        }

        $this->sardPart($parts[1]);
        $this->sardPart($parts[2]);
        $service->alertMafsilStations($this->sardPart($parts[3]));

        $this->assertSame(1, SupervisorAlert::where('type', SupervisorAlert::TYPE_MAFSIL_REQUIRED)->count());

        // Re-reciting an earlier part must not create another alert.
        $repeat = $this->sardPart($parts[1]);
        $created = $service->alertMafsilStations($repeat);

        $this->assertTrue($created->isEmpty());
        $this->assertSame(1, SupervisorAlert::where('type', SupervisorAlert::TYPE_MAFSIL_REQUIRED)->count());
    }

    public function test_sard_save_response_returns_mafsil_alert(): void
    {
        $service = app(RecitationAlertService::class);
        for ($number = 1; $number <= 2; $number++) {
            $part = $this->makePart($number);
            $this->memorizePart($part);
            $this->sardPart($part);
        }

        $thirdPart = $this->makePart(3);
        $this->memorizePart($thirdPart);

        $pages = [];
        for ($page = (int) $thirdPart->start_page; $page <= (int) $thirdPart->end_page; $page++) {
            $pages[] = ['page_number' => $page, 'errors' => 0, 'hesitations' => 0];
        }

        $response = $this->actingAs($this->supervisor)
            ->postJson(route('supervisor.recitation.save'), [
                'student_id' => $this->student->id,
                'type' => 'recitation',
                'part_id' => $thirdPart->id,
                'session_date' => now()->toDateString(),
                'pages' => $pages,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $response->assertJsonPath('mafsil_alerts.0.station', 3);
        $this->assertSame(3, $response->json('mafsil_alerts_count'));
        $this->assertSame($this->student->name, $response->json('mafsil_alerts_student'));
    }

    public function test_deleting_a_session_removes_its_alerts_without_creating_new_ones(): void
    {
        $part = $this->makePart(1);
        $session = $this->memorizePart($part);

        app(RecitationAlertService::class)->alertCompletedMemorization($session);
        $this->assertSame(1, SupervisorAlert::count());

        $this->actingAs($this->supervisor)
            ->delete(route('supervisor.recitation.destroy', $session))
            ->assertRedirect();

        $this->assertSame(0, SupervisorAlert::count());
        $this->assertDatabaseMissing('recitation_sessions', ['id' => $session->id]);
    }

    public function test_failed_sard_does_not_trigger_a_mafsil_alert(): void
    {
        $service = app(RecitationAlertService::class);
        $parts = [];
        for ($number = 1; $number <= 3; $number++) {
            $part = $this->makePart($number);
            $this->memorizePart($part);
            $parts[$number] = $part;
        }

        $this->sardPart($parts[1], RecitationSession::STATUS_FAILED);
        $this->sardPart($parts[2], RecitationSession::STATUS_FAILED);
        $failed = $this->sardPart($parts[3], RecitationSession::STATUS_FAILED);

        $this->assertTrue($service->alertMafsilStations($failed)->isEmpty());
        $this->assertSame(0, SupervisorAlert::where('type', SupervisorAlert::TYPE_MAFSIL_REQUIRED)->count());
    }

    /**
     * Create a session of the given type whose pages $from..$to include the
     * listed failed page numbers.
     */
    private function makeTypedSession(QuranPart $part, string $type, array $failedPages, int $from, int $to, ?string $date = null): RecitationSession
    {
        $session = RecitationSession::create([
            'student_id' => $this->student->id,
            'student_name' => $this->student->name,
            'supervisor_id' => $this->supervisor->id,
            'type' => $type,
            'part_id' => $part->id,
            'session_date' => $date ?? now()->toDateString(),
            'status' => RecitationSession::STATUS_PASSED,
            'start_page' => $from,
            'end_page' => $to,
            'total_pages' => $to - $from + 1,
            'total_errors' => count($failedPages) * 3,
            'total_hesitations' => 0,
        ]);

        for ($page = $from; $page <= $to; $page++) {
            $failed = in_array($page, $failedPages, true);
            RecitationPage::create([
                'session_id' => $session->id,
                'page_number' => $page,
                'errors' => $failed ? 3 : 0,
                'hesitations' => 0,
                'color_status' => $failed ? 'black' : 'green',
                'is_passed' => ! $failed,
            ]);
        }

        return $session;
    }

    public function test_failed_pages_alert_lists_failed_pages_of_last_new_memorization_session(): void
    {
        $part = $this->makePart(1);
        QuranPage::where('part_id', $part->id)->where('page_number', 2)->update(['surah_name' => 'البقرة']);

        $this->makeTypedSession($part, RecitationSession::TYPE_NEW_MEMORIZATION, [2], 1, 3);

        $response = $this->actingAs($this->supervisor)
            ->getJson(route('supervisor.alerts.failed-pages', [
                'student' => $this->student,
                'type' => 'new_memorization',
            ]))
            ->assertOk();

        $response->assertJsonCount(1, 'data.pages');
        $response->assertJsonPath('data.pages.0.page_number', 2);
        $response->assertJsonPath('data.pages.0.surah_name', 'البقرة');
        $response->assertJsonPath('data.type', 'new_memorization');
    }

    public function test_failed_pages_alert_matches_the_selected_session_type(): void
    {
        $part = $this->makePart(1);
        // Last new-memorization session has no failed pages.
        $this->memorizePart($part);
        // A review session has a failed page.
        $this->makeTypedSession($part, RecitationSession::TYPE_REVIEW, [3], 1, 20);

        // New memorization: nothing to show.
        $this->actingAs($this->supervisor)
            ->getJson(route('supervisor.alerts.failed-pages', ['student' => $this->student, 'type' => 'new_memorization']))
            ->assertOk()
            ->assertJsonPath('data', null);

        // Review: the failed page is reported.
        $this->actingAs($this->supervisor)
            ->getJson(route('supervisor.alerts.failed-pages', ['student' => $this->student, 'type' => 'review']))
            ->assertOk()
            ->assertJsonCount(1, 'data.pages')
            ->assertJsonPath('data.pages.0.page_number', 3);
    }

    public function test_failed_pages_alert_uses_only_the_most_recent_session(): void
    {
        $part = $this->makePart(1);
        // Older session with a failed page.
        $this->makeTypedSession($part, RecitationSession::TYPE_NEW_MEMORIZATION, [2], 1, 3, now()->subWeek()->toDateString());
        // More recent session with no failed pages.
        $this->makeTypedSession($part, RecitationSession::TYPE_NEW_MEMORIZATION, [], 1, 3, now()->toDateString());

        $this->actingAs($this->supervisor)
            ->getJson(route('supervisor.alerts.failed-pages', ['student' => $this->student, 'type' => 'new_memorization']))
            ->assertOk()
            ->assertJsonPath('data', null);
    }

    public function test_failed_pages_alert_is_empty_without_previous_sessions(): void
    {
        $this->actingAs($this->supervisor)
            ->getJson(route('supervisor.alerts.failed-pages', ['student' => $this->student, 'type' => 'review']))
            ->assertOk()
            ->assertJsonPath('data', null);
    }

    public function test_failed_pages_alert_is_forbidden_for_another_supervisor(): void
    {
        $otherSupervisor = User::factory()->supervisor()->create();

        $this->actingAs($otherSupervisor)
            ->getJson(route('supervisor.alerts.failed-pages', ['student' => $this->student, 'type' => 'review']))
            ->assertForbidden();
    }

    public function test_repeated_pages_appear_first_in_new_memorization_session(): void
    {
        $part = $this->makePart(1);

        $repeated = json_encode([
            ['part_id' => $part->id, 'page_number' => 4],
        ]);

        $response = $this->actingAs($this->supervisor)
            ->get(route('supervisor.recitation.new-memorization', [
                'student_id' => $this->student->id,
                'part_id' => $part->id,
                'page_number' => 5,
                'repeated_pages' => $repeated,
            ]))
            ->assertOk();

        // Repeated page (4) comes first, then the session pages 5, 6.
        $response->assertSee('name="pages[0][page_number]" value="4"', false);
        $response->assertSee('name="pages[1][page_number]" value="5"', false);
        $response->assertSee('name="pages[2][page_number]" value="6"', false);
    }
}
