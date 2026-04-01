<?php

namespace Tests\Unit\Services;

use App\Models\LiveRecitationError;
use App\Models\LiveRecitationSession;
use App\Models\Student;
use App\Services\AssemblyAIRealtimeService;
use App\Services\LiveRecitationService;
use App\Services\QuranApiService;
use App\Services\TajweedEvaluatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class LiveRecitationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected LiveRecitationService $service;
    protected $mockQuranApi;
    protected $mockTajweedEvaluator;
    protected $mockAssemblyAI;
    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockQuranApi = Mockery::mock(QuranApiService::class);
        $this->mockTajweedEvaluator = Mockery::mock(TajweedEvaluatorService::class);
        $this->mockAssemblyAI = Mockery::mock(AssemblyAIRealtimeService::class);

        $this->service = new LiveRecitationService(
            $this->mockQuranApi,
            $this->mockTajweedEvaluator,
            $this->mockAssemblyAI
        );

        $this->student = Student::factory()->create();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_start_session_creates_session_with_page_mode(): void
    {
        $this->mockQuranApi->shouldReceive('getPage')
            ->andReturn([
                'ayahs' => [
                    ['surah' => ['number' => 1], 'numberInSurah' => 1, 'text' => 'بِسْمِ اللَّهِ', 'number' => 1],
                    ['surah' => ['number' => 1], 'numberInSurah' => 2, 'text' => 'الرَّحْمَٰنِ', 'number' => 2],
                ]
            ]);
        $this->mockQuranApi->shouldReceive('getAudioUrls')
            ->andReturn(['1:1' => 'https://audio.url/1.mp3', '1:2' => 'https://audio.url/2.mp3']);
        $this->mockAssemblyAI->shouldReceive('getWebSocketToken')
            ->andReturn([
                'success' => true,
                'token' => 'test-token',
                'websocket_url' => 'wss://api.assemblyai.com/v2/realtime',
            ]);

        $result = $this->service->startSession($this->student, [
            'mode' => 'page',
            'start_page' => 1,
            'end_page' => 1,
        ]);

        $this->assertTrue($result['success']);
        $this->assertInstanceOf(LiveRecitationSession::class, $result['session']);
        $this->assertEquals('active', $result['session']->status);
        $this->assertEquals(2, $result['session']->total_verses);
    }

    public function test_start_session_returns_error_when_no_verses(): void
    {
        $this->mockQuranApi->shouldReceive('getPage')
            ->andReturn(['ayahs' => []]);
        $this->mockQuranApi->shouldReceive('getAudioUrls')
            ->andReturn([]);

        $result = $this->service->startSession($this->student, [
            'mode' => 'page',
            'start_page' => 1,
            'end_page' => 1,
        ]);

        $this->assertFalse($result['success']);
        $this->assertEquals('No verses found for the specified range', $result['error']);
    }

    public function test_start_session_returns_error_when_websocket_fails(): void
    {
        $this->mockQuranApi->shouldReceive('getPage')
            ->andReturn([
                'ayahs' => [
                    ['surah' => ['number' => 1], 'numberInSurah' => 1, 'text' => 'بِسْمِ اللَّهِ', 'number' => 1],
                ]
            ]);
        $this->mockQuranApi->shouldReceive('getAudioUrls')
            ->andReturn(['1:1' => 'https://audio.url/1.mp3']);
        $this->mockAssemblyAI->shouldReceive('getWebSocketToken')
            ->andReturn(['success' => false, 'error' => 'API key not configured']);

        $result = $this->service->startSession($this->student, [
            'mode' => 'page',
            'start_page' => 1,
            'end_page' => 1,
        ]);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Failed to get WebSocket token', $result['error']);
    }

    public function test_update_progress_first_verse_sets_average(): void
    {
        $session = LiveRecitationSession::factory()->create([
            'student_id' => $this->student->id,
            'verses_completed' => 0,
            'average_similarity' => 0,
        ]);

        $this->service->updateProgress($session->id, 0, 85.0);

        $session->refresh();
        $this->assertEquals(85.0, $session->average_similarity);
        $this->assertEquals(1, $session->verses_completed);
    }

    public function test_update_progress_incremental_average_formula(): void
    {
        $session = LiveRecitationSession::factory()->create([
            'student_id' => $this->student->id,
            'verses_completed' => 2,
            'average_similarity' => 80.0, // After 2 verses with 80 and 80
        ]);

        // Third verse with similarity 90
        // new_avg = ((80 * 2) + 90) / 3 = 250 / 3 = 83.33
        $this->service->updateProgress($session->id, 2, 90.0);

        $session->refresh();
        $this->assertEqualsWithDelta(83.33, $session->average_similarity, 0.01);
        $this->assertEquals(3, $session->verses_completed);
    }

    public function test_update_progress_increments_verses_completed(): void
    {
        $session = LiveRecitationSession::factory()->create([
            'student_id' => $this->student->id,
            'verses_completed' => 0,
        ]);

        $this->service->updateProgress($session->id, 2, 85.0);

        $session->refresh();
        $this->assertEquals(3, $session->verses_completed);
    }

    public function test_update_progress_handles_nonexistent_session(): void
    {
        // Should not throw exception
        $this->service->updateProgress(99999, 0, 85.0);

        $this->assertTrue(true); // If we get here, no exception was thrown
    }

    public function test_record_error_creates_error_record(): void
    {
        $session = LiveRecitationSession::factory()->create([
            'student_id' => $this->student->id,
        ]);

        $errorData = [
            'surah' => 1,
            'verse_number' => 1,
            'verse_index' => 0,
            'expected_text' => 'بِسْمِ اللَّهِ',
            'detected_text' => 'بسم الله',
            'similarity' => 75.0,
            'error_type' => 'mismatch',
        ];

        $error = $this->service->recordError($session->id, $errorData);

        $this->assertInstanceOf(LiveRecitationError::class, $error);
        $this->assertEquals('بِسْمِ اللَّهِ', $error->expected_text);
        $this->assertEquals('بسم الله', $error->detected_text);
    }

    public function test_record_error_increments_session_total_errors(): void
    {
        $session = LiveRecitationSession::factory()->create([
            'student_id' => $this->student->id,
            'total_errors' => 2,
        ]);

        $errorData = [
            'surah' => 1,
            'verse_number' => 1,
            'verse_index' => 0,
            'expected_text' => 'بِسْمِ اللَّهِ',
            'detected_text' => 'بسم الله',
            'similarity' => 75.0,
            'error_type' => 'mismatch',
        ];

        $this->service->recordError($session->id, $errorData);

        $session->refresh();
        $this->assertEquals(3, $session->total_errors);
    }

    public function test_end_session_calculates_final_score_zero_errors(): void
    {
        $session = LiveRecitationSession::factory()->create([
            'student_id' => $this->student->id,
            'average_similarity' => 85.0,
            'total_errors' => 0,
        ]);

        $result = $this->service->endSession($session->id);

        $this->assertEquals(85.0, $result->final_score);
        $this->assertEquals('completed', $result->status);
    }

    public function test_end_session_calculates_final_score_with_errors(): void
    {
        $session = LiveRecitationSession::factory()->create([
            'student_id' => $this->student->id,
            'average_similarity' => 85.0,
            'total_errors' => 3,
        ]);

        // final_score = 85 - (3 * 3) = 76
        $result = $this->service->endSession($session->id);

        $this->assertEquals(76.0, $result->final_score);
    }

    public function test_end_session_final_score_cannot_go_negative(): void
    {
        $session = LiveRecitationSession::factory()->create([
            'student_id' => $this->student->id,
            'average_similarity' => 50.0,
            'total_errors' => 20,
        ]);

        // final_score = max(0, 50 - (20 * 3)) = max(0, -10) = 0
        $result = $this->service->endSession($session->id);

        $this->assertEquals(0, $result->final_score);
    }

    public function test_end_session_sets_status_completed(): void
    {
        $session = LiveRecitationSession::factory()->create([
            'student_id' => $this->student->id,
            'status' => 'active',
        ]);

        $result = $this->service->endSession($session->id);

        $this->assertEquals('completed', $result->status);
    }

    public function test_end_session_sets_ended_at(): void
    {
        $session = LiveRecitationSession::factory()->create([
            'student_id' => $this->student->id,
            'ended_at' => null,
        ]);

        $result = $this->service->endSession($session->id);

        $this->assertNotNull($result->ended_at);
    }

    public function test_cancel_session_sets_status_cancelled(): void
    {
        $session = LiveRecitationSession::factory()->create([
            'student_id' => $this->student->id,
            'status' => 'active',
        ]);

        $result = $this->service->cancelSession($session->id);

        $this->assertTrue($result);
        $session->refresh();
        $this->assertEquals('cancelled', $session->status);
    }

    public function test_cancel_session_sets_ended_at(): void
    {
        $session = LiveRecitationSession::factory()->create([
            'student_id' => $this->student->id,
            'ended_at' => null,
        ]);

        $result = $this->service->cancelSession($session->id);

        $this->assertTrue($result);
        $session->refresh();
        $this->assertNotNull($session->ended_at);
    }

    public function test_cancel_session_returns_false_for_nonexistent(): void
    {
        $result = $this->service->cancelSession(99999);

        $this->assertFalse($result);
    }

    public function test_generate_report_returns_session_data(): void
    {
        $session = LiveRecitationSession::factory()->completed()->create([
            'student_id' => $this->student->id,
        ]);

        $report = $this->service->generateReport($session->id);

        $this->assertArrayHasKey('session', $report);
        $this->assertArrayHasKey('errors', $report);
        $this->assertArrayHasKey('summary', $report);
    }

    public function test_generate_report_includes_errors(): void
    {
        $session = LiveRecitationSession::factory()->create([
            'student_id' => $this->student->id,
        ]);
        LiveRecitationError::factory()->count(2)->create([
            'live_session_id' => $session->id,
        ]);

        $report = $this->service->generateReport($session->id);

        $this->assertCount(2, $report['errors']);
    }

    public function test_generate_report_calculates_success_rate(): void
    {
        $session = LiveRecitationSession::factory()->create([
            'student_id' => $this->student->id,
            'total_verses' => 10,
            'total_errors' => 2,
        ]);

        $report = $this->service->generateReport($session->id);

        // success_rate = (10 - 2) / 10 * 100 = 80%
        $this->assertEquals(80.0, $report['summary']['success_rate']);
    }

    public function test_generate_report_finds_most_common_error_type(): void
    {
        $session = LiveRecitationSession::factory()->create([
            'student_id' => $this->student->id,
        ]);
        LiveRecitationError::factory()->mismatch()->create(['live_session_id' => $session->id]);
        LiveRecitationError::factory()->mismatch()->create(['live_session_id' => $session->id]);
        LiveRecitationError::factory()->missing()->create(['live_session_id' => $session->id]);

        $report = $this->service->generateReport($session->id);

        $this->assertEquals('mismatch', $report['summary']['most_common_error_type']);
    }

    public function test_generate_report_success_rate_zero_verses(): void
    {
        $session = LiveRecitationSession::factory()->create([
            'student_id' => $this->student->id,
            'total_verses' => 0,
        ]);

        $report = $this->service->generateReport($session->id);

        $this->assertEquals(0, $report['summary']['success_rate']);
    }

    public function test_get_verses_for_session_page_mode(): void
    {
        $this->mockQuranApi->shouldReceive('getPage')
            ->with(1)
            ->andReturn([
                'ayahs' => [
                    ['surah' => ['number' => 1], 'numberInSurah' => 1, 'text' => 'بِسْمِ اللَّهِ', 'number' => 1],
                    ['surah' => ['number' => 1], 'numberInSurah' => 2, 'text' => 'الرَّحْمَٰنِ', 'number' => 2],
                ]
            ]);
        $this->mockQuranApi->shouldReceive('getAudioUrls')
            ->with(1)
            ->andReturn(['1:1' => 'https://audio.url/1.mp3', '1:2' => 'https://audio.url/2.mp3']);

        $result = $this->service->getVersesForSession('page', 1, 1);

        $this->assertCount(2, $result['verses']);
        $this->assertEquals('بِسْمِ اللَّهِ', $result['verses'][0]['text']);
    }

    public function test_get_verses_for_session_surah_mode(): void
    {
        $this->mockQuranApi->shouldReceive('getVerses')
            ->with(1, 1, 3)
            ->andReturn([
                ['text' => 'بِسْمِ اللَّهِ', 'number' => 1],
                ['text' => 'الرَّحْمَٰنِ', 'number' => 2],
                ['text' => 'الرَّحِيمِ', 'number' => 3],
            ]);
        $this->mockQuranApi->shouldReceive('getVerseAudioUrl')
            ->andReturn('https://audio.url/verse.mp3');

        $result = $this->service->getVersesForSession('surah', 0, 0, 1, 1, 3);

        $this->assertCount(3, $result['verses']);
        $this->assertEquals(1, $result['verses'][0]['surah']);
        $this->assertEquals(1, $result['verses'][0]['verse_number']);
    }
}
