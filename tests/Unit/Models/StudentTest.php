<?php

namespace Tests\Unit\Models;

use App\Models\Attendance;
use App\Models\Level;
use App\Models\RecitationSession;
use App\Models\Student;
use App\Models\User;
use App\Models\Warning;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentTest extends TestCase
{
    use RefreshDatabase;

    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->student = Student::factory()->create();
    }

    public function test_student_has_required_fillable_attributes(): void
    {
        $student = new Student();
        $fillable = $student->getFillable();

        $this->assertContains('user_id', $fillable);
        $this->assertContains('supervisor_id', $fillable);
        $this->assertContains('level_id', $fillable);
        $this->assertContains('student_number', $fillable);
        $this->assertContains('national_id', $fillable);
        $this->assertContains('birth_date', $fillable);
        $this->assertContains('phone', $fillable);
        $this->assertContains('address', $fillable);
        $this->assertContains('memorized_parts', $fillable);
        $this->assertContains('current_part', $fillable);
        $this->assertContains('current_page', $fillable);
        $this->assertContains('enrollment_date', $fillable);
        $this->assertContains('notes', $fillable);
    }

    public function test_student_has_correct_casts(): void
    {
        $student = new Student();
        $casts = $student->getCasts();

        $this->assertArrayHasKey('birth_date', $casts);
        $this->assertEquals('date', $casts['birth_date']);
        $this->assertArrayHasKey('enrollment_date', $casts);
        $this->assertEquals('date', $casts['enrollment_date']);
    }

    public function test_student_belongs_to_user(): void
    {
        $user = User::factory()->student()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);

        $this->assertEquals($user->id, $student->user->id);
    }

    public function test_student_belongs_to_supervisor(): void
    {
        $supervisor = User::factory()->supervisor()->create();
        $student = Student::factory()->create(['supervisor_id' => $supervisor->id]);

        $this->assertEquals($supervisor->id, $student->supervisor->id);
    }

    public function test_student_belongs_to_level(): void
    {
        $level = Level::factory()->create();
        $student = Student::factory()->create(['level_id' => $level->id]);

        $this->assertEquals($level->id, $student->level->id);
    }

    public function test_student_has_many_attendances(): void
    {
        $student = Student::factory()->create();
        Attendance::factory()->count(3)->create(['student_id' => $student->id]);

        $this->assertCount(3, $student->attendances);
    }

    public function test_student_has_many_recitation_sessions(): void
    {
        $student = Student::factory()->create();
        RecitationSession::factory()->count(3)->create(['student_id' => $student->id]);

        $this->assertCount(3, $student->recitationSessions);
    }

    public function test_student_has_many_warnings(): void
    {
        $student = Student::factory()->create();
        Warning::factory()->count(2)->create(['student_id' => $student->id]);

        $this->assertCount(2, $student->warnings);
    }

    public function test_student_get_name_attribute_returns_user_name(): void
    {
        $user = User::factory()->create(['name' => 'أحمد محمد']);
        $student = Student::factory()->create(['user_id' => $user->id]);

        $this->assertEquals('أحمد محمد', $student->name);
    }

    public function test_student_get_attendance_rate_attribute_returns_100_when_no_attendances(): void
    {
        $student = Student::factory()->create();

        $this->assertEquals(100, $student->attendance_rate);
    }

    public function test_student_get_attendance_rate_attribute_calculates_correctly(): void
    {
        $student = Student::factory()->create();

        // 3 present, 1 absent
        Attendance::factory()->present()->create(['student_id' => $student->id]);
        Attendance::factory()->present()->create(['student_id' => $student->id]);
        Attendance::factory()->present()->create(['student_id' => $student->id]);
        Attendance::factory()->absent()->create(['student_id' => $student->id]);

        // 3/4 = 75%
        $this->assertEquals(75, $student->attendance_rate);
    }

    public function test_student_get_attendance_rate_includes_late_as_present(): void
    {
        $student = Student::factory()->create();

        // 2 present, 1 late, 1 absent
        Attendance::factory()->present()->create(['student_id' => $student->id]);
        Attendance::factory()->present()->create(['student_id' => $student->id]);
        Attendance::factory()->late()->create(['student_id' => $student->id]);
        Attendance::factory()->absent()->create(['student_id' => $student->id]);

        // 3/4 = 75%
        $this->assertEquals(75, $student->attendance_rate);
    }

    public function test_student_get_pass_rate_attribute_returns_100_when_no_sessions(): void
    {
        $student = Student::factory()->create();

        $this->assertEquals(100, $student->pass_rate);
    }

    public function test_student_get_pass_rate_attribute_calculates_correctly(): void
    {
        $student = Student::factory()->create();

        // 2 passed, 1 failed
        RecitationSession::factory()->passed()->create(['student_id' => $student->id]);
        RecitationSession::factory()->passed()->create(['student_id' => $student->id]);
        RecitationSession::factory()->failed()->create(['student_id' => $student->id]);

        // 2/3 = 66.67%
        $this->assertEquals(66.7, $student->pass_rate);
    }

    public function test_student_get_today_attendance_attribute_returns_null_when_no_attendance(): void
    {
        $student = Student::factory()->create();

        $this->assertNull($student->today_attendance);
    }

    public function test_student_get_today_attendance_attribute_returns_today_attendance(): void
    {
        $student = Student::factory()->create();
        $todayAttendance = Attendance::factory()->create([
            'student_id' => $student->id,
            'date' => now()->toDateString()
        ]);

        $this->assertEquals($todayAttendance->id, $student->today_attendance->id);
    }
}
