<?php

namespace Tests\Unit\Models;

use App\Models\QuranPart;
use App\Models\RecitationSession;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecitationSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_recitation_session_has_required_fillable_attributes(): void
    {
        $session = new RecitationSession();
        $fillable = $session->getFillable();

        $this->assertContains('student_id', $fillable);
        $this->assertContains('supervisor_id', $fillable);
        $this->assertContains('type', $fillable);
        $this->assertContains('part_id', $fillable);
        $this->assertContains('start_page', $fillable);
        $this->assertContains('end_page', $fillable);
        $this->assertContains('total_pages', $fillable);
        $this->assertContains('total_errors', $fillable);
        $this->assertContains('total_hesitations', $fillable);
        $this->assertContains('status', $fillable);
        $this->assertContains('mafsil_type', $fillable);
        $this->assertContains('max_score', $fillable);
        $this->assertContains('final_score', $fillable);
    }

    public function test_recitation_session_has_correct_casts(): void
    {
        $session = new RecitationSession();
        $casts = $session->getCasts();

        $this->assertArrayHasKey('session_date', $casts);
        $this->assertEquals('date', $casts['session_date']);
        $this->assertArrayHasKey('max_score', $casts);
        $this->assertEquals('float', $casts['max_score']);
        $this->assertArrayHasKey('final_score', $casts);
        $this->assertEquals('float', $casts['final_score']);
    }

    public function test_recitation_session_belongs_to_student(): void
    {
        $student = Student::factory()->create();
        $session = RecitationSession::factory()->create(['student_id' => $student->id]);

        $this->assertEquals($student->id, $session->student->id);
    }

    public function test_recitation_session_belongs_to_supervisor(): void
    {
        $supervisor = User::factory()->supervisor()->create();
        $session = RecitationSession::factory()->create(['supervisor_id' => $supervisor->id]);

        $this->assertEquals($supervisor->id, $session->supervisor->id);
    }

    public function test_recitation_session_belongs_to_part(): void
    {
        $part = QuranPart::factory()->create();
        $session = RecitationSession::factory()->create(['part_id' => $part->id]);

        $this->assertEquals($part->id, $session->part->id);
    }

    public function test_recitation_session_type_label_returns_arabic_label(): void
    {
        $session = RecitationSession::factory()->create(['type' => 'new_memorization']);
        $this->assertEquals('حفظ جديد', $session->type_label);

        $session = RecitationSession::factory()->create(['type' => 'review']);
        $this->assertEquals('مراجعة', $session->type_label);

        $session = RecitationSession::factory()->create(['type' => 'unmastered']);
        $this->assertEquals('حفظ غير متقن', $session->type_label);

        $session = RecitationSession::factory()->create(['type' => 'recitation']);
        $this->assertEquals('سرد', $session->type_label);

        $session = RecitationSession::factory()->create(['type' => 'mafsil']);
        $this->assertEquals('مفصل', $session->type_label);
    }

    public function test_recitation_session_status_label_returns_arabic_label(): void
    {
        $session = RecitationSession::factory()->create(['status' => 'passed']);
        $this->assertEquals('ناجح', $session->status_label);

        $session = RecitationSession::factory()->create(['status' => 'failed']);
        $this->assertEquals('راسب', $session->status_label);

        $session = RecitationSession::factory()->create(['status' => 'in_progress']);
        $this->assertEquals('جارٍ', $session->status_label);

        $session = RecitationSession::factory()->create(['status' => 'stopped']);
        $this->assertEquals('موقوف', $session->status_label);
    }

    public function test_recitation_session_status_color_returns_correct_colors(): void
    {
        $session = RecitationSession::factory()->create(['status' => 'passed']);
        $this->assertEquals('success', $session->status_color);

        $session = RecitationSession::factory()->create(['status' => 'failed']);
        $this->assertEquals('danger', $session->status_color);

        $session = RecitationSession::factory()->create(['status' => 'in_progress']);
        $this->assertEquals('warning', $session->status_color);

        $session = RecitationSession::factory()->create(['status' => 'stopped']);
        $this->assertEquals('secondary', $session->status_color);
    }

    public function test_calculate_page_color_returns_green_for_excellent(): void
    {
        // 0 errors, 0-2 hesitations = excellent (green)
        $result = RecitationSession::calculatePageColor(0, 0);
        $this->assertEquals('green', $result['color']);
        $this->assertTrue($result['passed']);
        $this->assertEquals('ممتاز', $result['label']);

        $result = RecitationSession::calculatePageColor(0, 2);
        $this->assertEquals('green', $result['color']);
        $this->assertTrue($result['passed']);
    }

    public function test_calculate_page_color_returns_orange_for_good(): void
    {
        // 1 error OR 3-5 hesitations = good (orange)
        $result = RecitationSession::calculatePageColor(1, 0);
        $this->assertEquals('orange', $result['color']);
        $this->assertTrue($result['passed']);
        $this->assertEquals('جيد', $result['label']);

        $result = RecitationSession::calculatePageColor(0, 4);
        $this->assertEquals('orange', $result['color']);
        $this->assertTrue($result['passed']);
    }

    public function test_calculate_page_color_returns_red_for_weak(): void
    {
        // 2 errors, 0 hesitations = weak (red)
        $result = RecitationSession::calculatePageColor(2, 0);
        $this->assertEquals('red', $result['color']);
        $this->assertTrue($result['passed']);
        $this->assertEquals('ضعيف', $result['label']);
    }

    public function test_calculate_page_color_returns_black_for_failed(): void
    {
        // 3+ errors OR 2 errors with any hesitations OR 6+ hesitations = failed (black)
        $result = RecitationSession::calculatePageColor(3, 0);
        $this->assertEquals('black', $result['color']);
        $this->assertFalse($result['passed']);
        $this->assertEquals('راسب', $result['label']);

        $result = RecitationSession::calculatePageColor(2, 1);
        $this->assertEquals('black', $result['color']);
        $this->assertFalse($result['passed']);

        $result = RecitationSession::calculatePageColor(0, 6);
        $this->assertEquals('black', $result['color']);
        $this->assertFalse($result['passed']);
    }

    public function test_calculate_mafsil_score_deducts_correctly(): void
    {
        // max_score = 90, errors = 3, hesitations = 6
        // deduction = (3 * 3) + (6/3 * 1) = 9 + 2 = 11
        // result = 90 - 11 = 79
        $result = RecitationSession::calculateMafsilScore(90, 3, 6);
        $this->assertEquals(79, $result);

        // No errors or hesitations
        $result = RecitationSession::calculateMafsilScore(90, 0, 0);
        $this->assertEquals(90, $result);

        // More errors than max score (should not go negative)
        $result = RecitationSession::calculateMafsilScore(10, 10, 0);
        $this->assertEquals(0, $result);
    }

    public function test_get_mafsil_max_score_returns_correct_values(): void
    {
        // Types 3 and 6 return 90
        $this->assertEquals(90, RecitationSession::getMafsilMaxScore(3));
        $this->assertEquals(90, RecitationSession::getMafsilMaxScore(6));

        // Types 10 and 15 return 80
        $this->assertEquals(80, RecitationSession::getMafsilMaxScore(10));
        $this->assertEquals(80, RecitationSession::getMafsilMaxScore(15));

        // Types 20, 25, 30 return 75
        $this->assertEquals(75, RecitationSession::getMafsilMaxScore(20));
        $this->assertEquals(75, RecitationSession::getMafsilMaxScore(25));
        $this->assertEquals(75, RecitationSession::getMafsilMaxScore(30));

        // Default case returns 90
        $this->assertEquals(90, RecitationSession::getMafsilMaxScore(1));
        $this->assertEquals(90, RecitationSession::getMafsilMaxScore(7));
    }
}
