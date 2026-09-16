<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Level;
use App\Models\ParentProfile;
use App\Models\QuranPart;
use App\Models\RecitationPage;
use App\Models\RecitationSession;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\CreatesApplication;

class PortalWrittenReportTest extends BaseTestCase
{
    use CreatesApplication;

    private int $supervisorId;
    private int $seq = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $sup = User::firstWhere('role', 'supervisor');
        if (!$sup) {
            $this->seq++;
            $sup = User::create([
                'name' => 'مشرف', 'username' => 'sup_' . $this->seq,
                'email' => 'sup_' . $this->seq . '@test.local',
                'password' => Hash::make('secret'), 'role' => 'supervisor', 'is_active' => true,
            ]);
        }
        $this->supervisorId = $sup->id;

        Model::unguard();
        Level::firstOrCreate(['id' => 1], [
            'name' => 'مستوى 1', 'level_number' => 1,
            'new_memorization_pages' => 3, 'review_pages' => 6, 'description' => 'تجريبي',
        ]);
        $stale = QuranPart::where('part_number', 1)->where('id', '!=', 1)->first();
        if ($stale) {
            RecitationSession::where('part_id', $stale->id)->update(['part_id' => null]);
            $stale->delete();
        }
        QuranPart::firstOrCreate(['id' => 1], [
            'part_number' => 1, 'name' => 'الجزء الأول', 'start_page' => 1, 'end_page' => 20,
        ]);
        Model::reguard();
    }

    private function makeStudentWithActivity(string $name): Student
    {
        $uniq = uniqid('stu_', true);
        $user = User::create([
            'name' => $name, 'username' => 'stu_' . $uniq,
            'email' => $uniq . '@test.local',
            'password' => Hash::make('secret'), 'role' => 'student', 'is_active' => true,
        ]);
        $student = Student::create([
            'user_id' => $user->id, 'supervisor_id' => $this->supervisorId, 'level_id' => 1,
            'student_number' => 'STU-' . strtoupper(str_replace('.', '', $uniq)),
        ]);

        $session = RecitationSession::create([
            'student_id' => $student->id, 'student_name' => $name,
            'supervisor_id' => $this->supervisorId, 'type' => 'new_memorization', 'part_id' => 1,
            'session_date' => now()->toDateString(), 'status' => 'passed',
            'start_page' => 1, 'end_page' => 3, 'total_pages' => 3,
            'total_errors' => 0, 'total_hesitations' => 0,
        ]);
        for ($p = 1; $p <= 3; $p++) {
            RecitationPage::create([
                'session_id' => $session->id, 'page_number' => $p,
                'errors' => 0, 'hesitations' => 0, 'color_status' => 'green', 'is_passed' => true,
            ]);
        }

        Attendance::create([
            'student_id' => $student->id, 'supervisor_id' => $this->supervisorId,
            'date' => now()->toDateString(), 'status' => 'present',
        ]);

        return $student;
    }

    private function makeParentFor(Student $student): User
    {
        $uniq = uniqid('par_', true);
        $user = User::create([
            'name' => 'ولي أمر', 'username' => 'par_' . $uniq,
            'email' => $uniq . '@test.local',
            'password' => Hash::make('secret'), 'role' => 'parent', 'is_active' => true,
        ]);

        $profile = ParentProfile::create([
            'user_id' => $user->id,
            'national_id' => '999' . random_int(1000000, 9999999),
            'phone' => '05' . random_int(10000000, 99999999),
            'relationship' => 'father',
        ]);

        DB::table('parent_student')->insert([
            'parent_id' => $profile->id,
            'student_id' => $student->id,
        ]);

        return $user;
    }

    public function test_student_portal_shows_written_report(): void
    {
        $student = $this->makeStudentWithActivity('طالب البوابة');

        $resp = $this->actingAs($student->user)->get('/student/reports?period=year');

        $resp->assertStatus(200);
        $body = $resp->getContent();
        $this->assertStringContainsString('تقرير مكتوب', $body);
        $this->assertStringContainsString('طالب البوابة', $body);
    }

    public function test_student_portal_written_report_pdf_has_arabic_filename(): void
    {
        $student = $this->makeStudentWithActivity('طالب PDF');

        $resp = $this->actingAs($student->user)->get('/student/reports?period=year&export=pdf');

        $resp->assertStatus(200);
        $this->assertSame('application/pdf', $resp->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $resp->getContent());

        $disposition = (string) $resp->headers->get('Content-Disposition');
        $this->assertStringContainsString(rawurlencode('التقرير-المكتوب'), $disposition);
        $this->assertStringContainsString(
            rawurlencode(str_replace(' ', '-', $student->user->name)),
            $disposition
        );
    }

    public function test_parent_portal_shows_written_report_for_child(): void
    {
        $student = $this->makeStudentWithActivity('ابن ولي الأمر');
        $parent = $this->makeParentFor($student);

        $resp = $this->actingAs($parent)->get('/parent/reports?period=year');

        $resp->assertStatus(200);
        $body = $resp->getContent();
        $this->assertStringContainsString('تقرير مكتوب', $body);
        $this->assertStringContainsString('ابن ولي الأمر', $body);
    }

    public function test_parent_portal_written_report_pdf_has_arabic_filename(): void
    {
        $student = $this->makeStudentWithActivity('ابن PDF');
        $parent = $this->makeParentFor($student);

        $resp = $this->actingAs($parent)->get(
            '/parent/reports?period=year&student_id=' . $student->id . '&export=pdf'
        );

        $resp->assertStatus(200);
        $this->assertSame('application/pdf', $resp->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $resp->getContent());

        $disposition = (string) $resp->headers->get('Content-Disposition');
        $this->assertStringContainsString(rawurlencode('التقرير-المكتوب'), $disposition);
        $this->assertStringContainsString(
            rawurlencode(str_replace(' ', '-', $student->user->name)),
            $disposition
        );
    }
}
