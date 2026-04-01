<?php

namespace Tests\Feature\Student;

use App\Models\LiveRecitationError;
use App\Models\LiveRecitationSession;
use App\Models\Student;
use App\Models\User;
use App\Services\AssemblyAIRealtimeService;
use App\Services\QuranApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Tests\TestCase;

class LiveRecitationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->student()->create();
        $this->student = Student::factory()->create(['user_id' => $this->user->id]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_token_creates_new_token(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/student/live-recitation/token');

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'token']);
        $this->assertNotEmpty($response->json('token'));
    }

    public function test_get_token_requires_authentication(): void
    {
        $response = $this->getJson('/student/live-recitation/token');

        $response->assertStatus(401);
    }

    public function test_start_validates_mode_required(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/student/live-recitation/start', [], [
            'Authorization' => 'Bearer ' . $this->user->currentAccessToken()->token,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['mode']);
    }

    public function test_start_validates_mode_in_valid_values(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/student/live-recitation/start', [
            'mode' => 'invalid',
            'start_page' => 1,
        ], [
            'Authorization' => 'Bearer ' . $this->user->currentAccessToken()->token,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['mode']);
    }

    public function test_start_validates_page_range_for_page_mode(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/student/live-recitation/start', [
            'mode' => 'page',
        ], [
            'Authorization' => 'Bearer ' . $this->user->currentAccessToken()->token,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['start_page']);
    }

    public function test_start_validates_end_page_greater_than_start_page(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/student/live-recitation/start', [
            'mode' => 'range',
            'start_page' => 10,
            'end_page' => 5,
        ], [
            'Authorization' => 'Bearer ' . $this->user->currentAccessToken()->token,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['end_page']);
    }

    public function test_start_validates_surah_required_for_surah_mode(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/student/live-recitation/start', [
            'mode' => 'surah',
        ], [
            'Authorization' => 'Bearer ' . $this->user->currentAccessToken()->token,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['surah', 'verse_start', 'verse_end']);
    }

    public function test_end_session_returns_404_for_nonexistent(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/student/live-recitation/session/99999/end', [], [
            'Authorization' => 'Bearer ' . $this->user->currentAccessToken()->token,
        ]);

        $response->assertStatus(404);
    }

    public function test_end_session_returns_404_for_other_student_session(): void
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->student()->create();
        $otherStudent = Student::factory()->create(['user_id' => $otherUser->id]);
        $session = LiveRecitationSession::factory()->create(['student_id' => $otherStudent->id]);

        $response = $this->postJson("/api/student/live-recitation/session/{$session->id}/end", [], [
            'Authorization' => 'Bearer ' . $this->user->currentAccessToken()->token,
        ]);

        $response->assertStatus(404);
    }

    public function test_cancel_session_marks_as_cancelled(): void
    {
        Sanctum::actingAs($this->user);

        $session = LiveRecitationSession::factory()->active()->create([
            'student_id' => $this->student->id,
        ]);

        $response = $this->deleteJson("/api/student/live-recitation/session/{$session->id}", [], [
            'Authorization' => 'Bearer ' . $this->user->currentAccessToken()->token,
        ]);

        $response->assertStatus(200);

        $session->refresh();
        $this->assertEquals('cancelled', $session->status);
    }

    public function test_cancel_session_requires_ownership(): void
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->student()->create();
        $otherStudent = Student::factory()->create(['user_id' => $otherUser->id]);
        $session = LiveRecitationSession::factory()->create(['student_id' => $otherStudent->id]);

        $response = $this->deleteJson("/api/student/live-recitation/session/{$session->id}", [], [
            'Authorization' => 'Bearer ' . $this->user->currentAccessToken()->token,
        ]);

        $response->assertStatus(404);
    }

    public function test_record_error_validates_required_fields(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/student/live-recitation/error', [], [
            'Authorization' => 'Bearer ' . $this->user->currentAccessToken()->token,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'session_id',
            'surah',
            'verse_number',
            'verse_index',
            'expected_text',
            'detected_text',
            'similarity',
            'error_type',
        ]);
    }

    public function test_record_error_validates_error_type_enum(): void
    {
        Sanctum::actingAs($this->user);

        $session = LiveRecitationSession::factory()->create(['student_id' => $this->student->id]);

        $response = $this->postJson('/api/student/live-recitation/error', [
            'session_id' => $session->id,
            'surah' => 1,
            'verse_number' => 1,
            'verse_index' => 0,
            'expected_text' => 'بِسْمِ اللَّهِ',
            'detected_text' => 'بسم الله',
            'similarity' => 75.0,
            'error_type' => 'invalid_type',
        ], [
            'Authorization' => 'Bearer ' . $this->user->currentAccessToken()->token,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['error_type']);
    }

    public function test_record_error_creates_error_record(): void
    {
        Sanctum::actingAs($this->user);

        $session = LiveRecitationSession::factory()->create(['student_id' => $this->student->id]);

        $response = $this->postJson('/api/student/live-recitation/error', [
            'session_id' => $session->id,
            'surah' => 1,
            'verse_number' => 1,
            'verse_index' => 0,
            'expected_text' => 'بِسْمِ اللَّهِ',
            'detected_text' => 'بسم الله',
            'similarity' => 75.0,
            'error_type' => 'mismatch',
        ], [
            'Authorization' => 'Bearer ' . $this->user->currentAccessToken()->token,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data' => ['error_id', 'total_errors']]);
        $this->assertDatabaseHas('live_recitation_errors', [
            'live_session_id' => $session->id,
            'surah' => 1,
            'error_type' => 'mismatch',
        ]);
    }

    public function test_record_error_requires_session_ownership(): void
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->student()->create();
        $otherStudent = Student::factory()->create(['user_id' => $otherUser->id]);
        $session = LiveRecitationSession::factory()->create(['student_id' => $otherStudent->id]);

        $response = $this->postJson('/api/student/live-recitation/error', [
            'session_id' => $session->id,
            'surah' => 1,
            'verse_number' => 1,
            'verse_index' => 0,
            'expected_text' => 'بِسْمِ اللَّهِ',
            'detected_text' => 'بسم الله',
            'similarity' => 75.0,
            'error_type' => 'mismatch',
        ], [
            'Authorization' => 'Bearer ' . $this->user->currentAccessToken()->token,
        ]);

        $response->assertStatus(404);
    }

    public function test_update_progress_validates_required_fields(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/student/live-recitation/progress', [], [
            'Authorization' => 'Bearer ' . $this->user->currentAccessToken()->token,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['session_id', 'verse_index', 'similarity']);
    }

    public function test_update_progress_validates_similarity_range(): void
    {
        Sanctum::actingAs($this->user);

        $session = LiveRecitationSession::factory()->create(['student_id' => $this->student->id]);

        $response = $this->postJson('/api/student/live-recitation/progress', [
            'session_id' => $session->id,
            'verse_index' => 0,
            'similarity' => 150,
        ], [
            'Authorization' => 'Bearer ' . $this->user->currentAccessToken()->token,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['similarity']);
    }

    public function test_update_progress_updates_session(): void
    {
        Sanctum::actingAs($this->user);

        $session = LiveRecitationSession::factory()->create([
            'student_id' => $this->student->id,
            'verses_completed' => 0,
            'average_similarity' => 0,
        ]);

        $response = $this->postJson('/api/student/live-recitation/progress', [
            'session_id' => $session->id,
            'verse_index' => 0,
            'similarity' => 85.0,
        ], [
            'Authorization' => 'Bearer ' . $this->user->currentAccessToken()->token,
        ]);

        $response->assertStatus(200);

        $session->refresh();
        $this->assertEquals(1, $session->verses_completed);
        $this->assertEquals(85.0, $session->average_similarity);
    }

    public function test_update_progress_requires_ownership(): void
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->student()->create();
        $otherStudent = Student::factory()->create(['user_id' => $otherUser->id]);
        $session = LiveRecitationSession::factory()->create(['student_id' => $otherStudent->id]);

        $response = $this->postJson('/api/student/live-recitation/progress', [
            'session_id' => $session->id,
            'verse_index' => 0,
            'similarity' => 85.0,
        ], [
            'Authorization' => 'Bearer ' . $this->user->currentAccessToken()->token,
        ]);

        $response->assertStatus(404);
    }

    public function test_get_report_returns_session_data(): void
    {
        Sanctum::actingAs($this->user);

        $session = LiveRecitationSession::factory()->completed()->create([
            'student_id' => $this->student->id,
        ]);

        $response = $this->getJson("/api/student/live-recitation/session/{$session->id}/report", [
            'Authorization' => 'Bearer ' . $this->user->currentAccessToken()->token,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'session',
                'errors',
                'summary',
            ],
        ]);
    }

    public function test_get_report_returns_errors_array(): void
    {
        Sanctum::actingAs($this->user);

        $session = LiveRecitationSession::factory()->create(['student_id' => $this->student->id]);
        LiveRecitationError::factory()->count(2)->create(['live_session_id' => $session->id]);

        $response = $this->getJson("/api/student/live-recitation/session/{$session->id}/report", [
            'Authorization' => 'Bearer ' . $this->user->currentAccessToken()->token,
        ]);

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data.errors');
    }

    public function test_get_report_requires_ownership(): void
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->student()->create();
        $otherStudent = Student::factory()->create(['user_id' => $otherUser->id]);
        $session = LiveRecitationSession::factory()->create(['student_id' => $otherStudent->id]);

        $response = $this->getJson("/api/student/live-recitation/session/{$session->id}/report", [
            'Authorization' => 'Bearer ' . $this->user->currentAccessToken()->token,
        ]);

        $response->assertStatus(404);
    }

    public function test_live_recitation_page_loads(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->get('/student/live-recitation');

        $response->assertStatus(200);
    }

    public function test_start_creates_session_with_verses(): void
    {
        Sanctum::actingAs($this->user);

        $mockService = Mockery::mock(\App\Services\LiveRecitationService::class);
        $mockService->shouldReceive('startSession')
            ->once()
            ->andReturn([
                'success' => true,
                'session' => LiveRecitationSession::factory()->make([
                    'id' => 1,
                    'student_id' => $this->student->id,
                ]),
                'verses' => [
                    ['surah' => 1, 'verse_number' => 1, 'text' => 'بِسْمِ اللَّهِ'],
                ],
                'audio_urls' => ['1:1' => 'https://audio.url/1.mp3'],
                'websocket_token' => [
                    'token' => 'test-token',
                    'websocket_url' => 'wss://api.assemblyai.com/v2/realtime/stream?token=test-token',
                ],
            ]);

        $this->app->instance(\App\Services\LiveRecitationService::class, $mockService);

        $response = $this->postJson('/api/student/live-recitation/start', [
            'mode' => 'page',
            'start_page' => 1,
        ], [
            'Authorization' => 'Bearer ' . $this->user->currentAccessToken()->token,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'session_id',
                'verses',
                'audio_urls',
                'websocket_token',
                'websocket_url',
            ],
        ]);
    }

    public function test_start_returns_websocket_url(): void
    {
        Sanctum::actingAs($this->user);

        $mockService = Mockery::mock(\App\Services\LiveRecitationService::class);
        $mockService->shouldReceive('startSession')
            ->once()
            ->andReturn([
                'success' => true,
                'session' => LiveRecitationSession::factory()->make([
                    'id' => 1,
                    'student_id' => $this->student->id,
                ]),
                'verses' => [],
                'audio_urls' => [],
                'websocket_token' => [
                    'token' => 'abc-token',
                    'websocket_url' => 'wss://api.assemblyai.com/v2/realtime/stream?token=abc-token',
                ],
            ]);

        $this->app->instance(\App\Services\LiveRecitationService::class, $mockService);

        $response = $this->postJson('/api/student/live-recitation/start', [
            'mode' => 'page',
            'start_page' => 1,
        ], [
            'Authorization' => 'Bearer ' . $this->user->currentAccessToken()->token,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.websocket_token', 'abc-token');
        $response->assertJsonPath('data.websocket_url', 'wss://api.assemblyai.com/v2/realtime/stream?token=abc-token');
    }

    public function test_start_returns_404_when_student_profile_not_found(): void
    {
        $userWithoutStudent = User::factory()->student()->create();
        Sanctum::actingAs($userWithoutStudent);

        $response = $this->postJson('/api/student/live-recitation/start', [
            'mode' => 'page',
            'start_page' => 1,
        ], [
            'Authorization' => 'Bearer ' . $userWithoutStudent->currentAccessToken()->token,
        ]);

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'error' => 'Student profile not found',
        ]);
    }

    public function test_start_returns_error_when_service_fails(): void
    {
        Sanctum::actingAs($this->user);

        $mockService = Mockery::mock(\App\Services\LiveRecitationService::class);
        $mockService->shouldReceive('startSession')
            ->once()
            ->andReturn([
                'success' => false,
                'error' => 'No verses found for the specified range',
            ]);

        $this->app->instance(\App\Services\LiveRecitationService::class, $mockService);

        $response = $this->postJson('/api/student/live-recitation/start', [
            'mode' => 'page',
            'start_page' => 1,
        ], [
            'Authorization' => 'Bearer ' . $this->user->currentAccessToken()->token,
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'error' => 'No verses found for the specified range',
        ]);
    }

    public function test_start_validates_page_max_boundary(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/student/live-recitation/start', [
            'mode' => 'page',
            'start_page' => 605,
        ], [
            'Authorization' => 'Bearer ' . $this->user->currentAccessToken()->token,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['start_page']);
    }

    public function test_start_validates_surah_max_boundary(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/student/live-recitation/start', [
            'mode' => 'surah',
            'surah' => 115,
            'verse_start' => 1,
            'verse_end' => 5,
        ], [
            'Authorization' => 'Bearer ' . $this->user->currentAccessToken()->token,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['surah']);
    }
}
