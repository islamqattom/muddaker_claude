<?php

namespace Tests\Unit\Models;

use App\Models\Student;
use App\Models\User;
use App\Models\Warning;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarningTest extends TestCase
{
    use RefreshDatabase;

    public function test_warning_has_required_fillable_attributes(): void
    {
        $warning = new Warning();
        $fillable = $warning->getFillable();

        $this->assertContains('student_id', $fillable);
        $this->assertContains('issued_by', $fillable);
        $this->assertContains('warning_number', $fillable);
        $this->assertContains('type', $fillable);
        $this->assertContains('title', $fillable);
        $this->assertContains('content', $fillable);
        $this->assertContains('issue_date', $fillable);
        $this->assertContains('from_date', $fillable);
        $this->assertContains('to_date', $fillable);
        $this->assertContains('absent_days', $fillable);
        $this->assertContains('late_days', $fillable);
        $this->assertContains('total_days', $fillable);
        $this->assertContains('failed_sessions', $fillable);
        $this->assertContains('total_sessions', $fillable);
    }

    public function test_warning_has_correct_casts(): void
    {
        $warning = new Warning();
        $casts = $warning->getCasts();

        $this->assertArrayHasKey('issue_date', $casts);
        $this->assertEquals('date', $casts['issue_date']);
        $this->assertArrayHasKey('from_date', $casts);
        $this->assertEquals('date', $casts['from_date']);
        $this->assertArrayHasKey('to_date', $casts);
        $this->assertEquals('date', $casts['to_date']);
        $this->assertArrayHasKey('is_read_by_parent', $casts);
        $this->assertEquals('boolean', $casts['is_read_by_parent']);
    }

    public function test_warning_belongs_to_student(): void
    {
        $student = Student::factory()->create();
        $warning = Warning::factory()->create(['student_id' => $student->id]);

        $this->assertEquals($student->id, $warning->student->id);
    }

    public function test_warning_belongs_to_issued_by_user(): void
    {
        $user = User::factory()->supervisor()->create();
        $warning = Warning::factory()->create(['issued_by' => $user->id]);

        $this->assertEquals($user->id, $warning->issuedBy->id);
    }

    public function test_warning_type_label_returns_arabic_label_for_attendance(): void
    {
        $warning = Warning::factory()->attendance()->create();

        $this->assertEquals('إنذار حضور', $warning->type_label);
    }

    public function test_warning_type_label_returns_arabic_label_for_recitation(): void
    {
        $warning = Warning::factory()->recitation()->create();

        $this->assertEquals('إنذار تسميع', $warning->type_label);
    }

    public function test_warning_type_label_returns_arabic_label_for_general(): void
    {
        $warning = Warning::factory()->general()->create();

        $this->assertEquals('إنذار عام', $warning->type_label);
    }

    public function test_warning_type_label_returns_type_for_unknown(): void
    {
        // Test directly without database since SQLite has CHECK constraints
        $warning = new Warning(['type' => 'unknown']);

        $this->assertEquals('unknown', $warning->type_label);
    }

    public function test_generate_warning_number_creates_unique_format(): void
    {
        // Create some warnings first to increment counter
        Warning::factory()->count(5)->create();

        $warningNumber1 = Warning::generateWarningNumber();

        // Create another warning to increment the counter
        Warning::factory()->create();

        $warningNumber2 = Warning::generateWarningNumber();

        $this->assertStringStartsWith('WARN-' . date('Y') . '-', $warningNumber1);
        $this->assertStringStartsWith('WARN-' . date('Y') . '-', $warningNumber2);
        $this->assertNotEquals($warningNumber1, $warningNumber2);
    }
}
