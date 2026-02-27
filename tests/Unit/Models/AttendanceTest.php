<?php

namespace Tests\Unit\Models;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_has_required_fillable_attributes(): void
    {
        $attendance = new Attendance();
        $fillable = $attendance->getFillable();

        $this->assertContains('student_id', $fillable);
        $this->assertContains('supervisor_id', $fillable);
        $this->assertContains('date', $fillable);
        $this->assertContains('status', $fillable);
        $this->assertContains('arrival_time', $fillable);
        $this->assertContains('scheduled_time', $fillable);
        $this->assertContains('notes', $fillable);
    }

    public function test_attendance_has_correct_casts(): void
    {
        $attendance = new Attendance();
        $casts = $attendance->getCasts();

        $this->assertArrayHasKey('date', $casts);
        $this->assertEquals('date', $casts['date']);
    }

    public function test_attendance_belongs_to_student(): void
    {
        $student = Student::factory()->create();
        $attendance = Attendance::factory()->create(['student_id' => $student->id]);

        $this->assertEquals($student->id, $attendance->student->id);
    }

    public function test_attendance_belongs_to_supervisor(): void
    {
        $supervisor = User::factory()->supervisor()->create();
        $attendance = Attendance::factory()->create(['supervisor_id' => $supervisor->id]);

        $this->assertEquals($supervisor->id, $attendance->supervisor->id);
    }

    public function test_attendance_status_label_returns_arabic_label_for_present(): void
    {
        $attendance = Attendance::factory()->create(['status' => 'present']);

        $this->assertEquals('حاضر', $attendance->status_label);
    }

    public function test_attendance_status_label_returns_arabic_label_for_absent(): void
    {
        $attendance = Attendance::factory()->create(['status' => 'absent']);

        $this->assertEquals('غائب', $attendance->status_label);
    }

    public function test_attendance_status_label_returns_arabic_label_for_late(): void
    {
        $attendance = Attendance::factory()->create(['status' => 'late']);

        $this->assertEquals('متأخر', $attendance->status_label);
    }

    public function test_attendance_status_label_returns_arabic_label_for_excused(): void
    {
        $attendance = Attendance::factory()->create(['status' => 'excused']);

        $this->assertEquals('غائب بعذر', $attendance->status_label);
    }

    public function test_attendance_status_label_returns_default_for_unknown_status(): void
    {
        // Test directly without database since SQLite has CHECK constraints
        $attendance = new Attendance(['status' => 'unknown']);

        $this->assertEquals('غير محدد', $attendance->status_label);
    }

    public function test_attendance_status_color_returns_success_for_present(): void
    {
        $attendance = Attendance::factory()->create(['status' => 'present']);

        $this->assertEquals('success', $attendance->status_color);
    }

    public function test_attendance_status_color_returns_danger_for_absent(): void
    {
        $attendance = Attendance::factory()->create(['status' => 'absent']);

        $this->assertEquals('danger', $attendance->status_color);
    }

    public function test_attendance_status_color_returns_warning_for_late(): void
    {
        $attendance = Attendance::factory()->create(['status' => 'late']);

        $this->assertEquals('warning', $attendance->status_color);
    }

    public function test_attendance_status_color_returns_info_for_excused(): void
    {
        $attendance = Attendance::factory()->create(['status' => 'excused']);

        $this->assertEquals('info', $attendance->status_color);
    }

    public function test_attendance_status_color_returns_secondary_for_unknown(): void
    {
        // Test directly without database since SQLite has CHECK constraints
        $attendance = new Attendance(['status' => 'unknown']);

        $this->assertEquals('secondary', $attendance->status_color);
    }
}
