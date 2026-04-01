<?php

namespace Tests\Unit\Models;

use App\Models\LiveRecitationError;
use App\Models\LiveRecitationSession;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveRecitationSessionTest extends TestCase
{
    use RefreshDatabase;

    protected LiveRecitationSession $session;

    protected function setUp(): void
    {
        parent::setUp();
        $this->session = LiveRecitationSession::factory()->create();
    }

    public function test_session_has_correct_fillable_attributes(): void
    {
        $fillable = (new LiveRecitationSession())->getFillable();

        $this->assertContains('student_id', $fillable);
        $this->assertContains('mode', $fillable);
        $this->assertContains('start_page', $fillable);
        $this->assertContains('end_page', $fillable);
        $this->assertContains('surah', $fillable);
        $this->assertContains('verse_start', $fillable);
        $this->assertContains('verse_end', $fillable);
        $this->assertContains('total_verses', $fillable);
        $this->assertContains('verses_completed', $fillable);
        $this->assertContains('total_errors', $fillable);
        $this->assertContains('average_similarity', $fillable);
        $this->assertContains('final_score', $fillable);
        $this->assertContains('status', $fillable);
        $this->assertContains('started_at', $fillable);
        $this->assertContains('ended_at', $fillable);
    }

    public function test_session_has_correct_casts(): void
    {
        $casts = (new LiveRecitationSession())->getCasts();

        $this->assertEquals('float', $casts['average_similarity']);
        $this->assertEquals('float', $casts['final_score']);
        $this->assertEquals('datetime', $casts['started_at']);
        $this->assertEquals('datetime', $casts['ended_at']);
    }

    public function test_session_belongs_to_student(): void
    {
        $student = Student::factory()->create();
        $session = LiveRecitationSession::factory()->create(['student_id' => $student->id]);

        $this->assertEquals($student->id, $session->student->id);
    }

    public function test_session_has_many_errors(): void
    {
        $session = LiveRecitationSession::factory()->create();
        LiveRecitationError::factory()->count(3)->create(['live_session_id' => $session->id]);

        $this->assertCount(3, $session->errors);
    }

    public function test_status_label_returns_arabic_active(): void
    {
        $session = LiveRecitationSession::factory()->create(['status' => 'active']);

        $this->assertEquals('نشط', $session->status_label);
    }

    public function test_status_label_returns_arabic_completed(): void
    {
        $session = LiveRecitationSession::factory()->create(['status' => 'completed']);

        $this->assertEquals('مكتمل', $session->status_label);
    }

    public function test_status_label_returns_arabic_cancelled(): void
    {
        $session = LiveRecitationSession::factory()->create(['status' => 'cancelled']);

        $this->assertEquals('ملغى', $session->status_label);
    }

    public function test_status_label_returns_arabic_paused(): void
    {
        $session = LiveRecitationSession::factory()->create(['status' => 'paused']);

        $this->assertEquals('متوقف', $session->status_label);
    }

    public function test_mode_label_returns_arabic_page(): void
    {
        $session = LiveRecitationSession::factory()->create(['mode' => 'page']);

        $this->assertEquals('صفحة', $session->mode_label);
    }

    public function test_mode_label_returns_arabic_range(): void
    {
        $session = LiveRecitationSession::factory()->create(['mode' => 'range']);

        $this->assertEquals('نطاق', $session->mode_label);
    }

    public function test_mode_label_returns_arabic_surah(): void
    {
        $session = LiveRecitationSession::factory()->create(['mode' => 'surah']);

        $this->assertEquals('سورة', $session->mode_label);
    }
}
