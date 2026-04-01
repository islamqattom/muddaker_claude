<?php

namespace Tests\Unit\Models;

use App\Models\LiveRecitationError;
use App\Models\LiveRecitationSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveRecitationErrorTest extends TestCase
{
    use RefreshDatabase;

    protected LiveRecitationError $error;

    protected function setUp(): void
    {
        parent::setUp();
        $this->error = LiveRecitationError::factory()->create();
    }

    public function test_error_has_correct_fillable_attributes(): void
    {
        $fillable = (new LiveRecitationError())->getFillable();

        $this->assertContains('live_session_id', $fillable);
        $this->assertContains('surah', $fillable);
        $this->assertContains('verse_number', $fillable);
        $this->assertContains('verse_index', $fillable);
        $this->assertContains('expected_text', $fillable);
        $this->assertContains('detected_text', $fillable);
        $this->assertContains('similarity', $fillable);
        $this->assertContains('error_type', $fillable);
        $this->assertContains('word_level_errors', $fillable);
        $this->assertContains('occurred_at', $fillable);
    }

    public function test_error_has_correct_casts(): void
    {
        $casts = (new LiveRecitationError())->getCasts();

        $this->assertEquals('float', $casts['similarity']);
        $this->assertEquals('array', $casts['word_level_errors']);
        $this->assertEquals('datetime', $casts['occurred_at']);
    }

    public function test_error_belongs_to_session(): void
    {
        $session = LiveRecitationSession::factory()->create();
        $error = LiveRecitationError::factory()->create(['live_session_id' => $session->id]);

        $this->assertEquals($session->id, $error->session->id);
    }

    public function test_error_type_label_returns_arabic_missing(): void
    {
        $error = LiveRecitationError::factory()->create(['error_type' => 'missing']);

        $this->assertEquals('كلمة مفقودة', $error->error_type_label);
    }

    public function test_error_type_label_returns_arabic_mismatch(): void
    {
        $error = LiveRecitationError::factory()->create(['error_type' => 'mismatch']);

        $this->assertEquals('كلمة مختلفة', $error->error_type_label);
    }

    public function test_error_type_label_returns_arabic_extra(): void
    {
        $error = LiveRecitationError::factory()->create(['error_type' => 'extra']);

        $this->assertEquals('كلمة زائدة', $error->error_type_label);
    }

    public function test_error_type_label_returns_arabic_unclear(): void
    {
        $error = LiveRecitationError::factory()->create(['error_type' => 'unclear']);

        $this->assertEquals('غير واضح', $error->error_type_label);
    }

    public function test_word_level_errors_casts_to_array(): void
    {
        $wordErrors = [['word' => 'الله', 'position' => 2, 'type' => 'missing']];
        $error = LiveRecitationError::factory()->create(['word_level_errors' => $wordErrors]);

        $this->assertIsArray($error->word_level_errors);
        $this->assertEquals('الله', $error->word_level_errors[0]['word']);
    }
}
