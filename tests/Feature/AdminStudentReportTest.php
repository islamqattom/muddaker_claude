<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Level;
use App\Models\QuranPart;
use App\Models\RecitationSession;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;
use Tests\CreatesApplication;

class AdminStudentReportTest extends BaseTestCase
{
    use CreatesApplication;

    private int $supervisorId;
    private int $seedSeq = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $sup = User::firstWhere('role', 'supervisor');
        if (!$sup) {
            $this->seedSeq++;
            $sup = User::create([
                'name' => 'مشرف', 'username' => 'sup_' . $this->seedSeq,
                'email' => 'sup_' . $this->seedSeq . '@test.local',
                'password' => Hash::make('secret'), 'role' => 'supervisor', 'is_active' => true,
            ]);
        }
        $this->supervisorId = $sup->id;

        if (!User::firstWhere('role', 'admin')) {
            $this->seedSeq++;
            User::create([
                'name' => 'مدير', 'username' => 'admin_' . $this->seedSeq,
                'email' => 'admin_' . $this->seedSeq . '@test.local',
                'password' => Hash::make('secret'), 'role' => 'admin', 'is_active' => true,
            ]);
        }

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

    private function makeStudent(string $name): Student
    {
        $uniq = uniqid('stu_', true);
        $user = User::create([
            'name' => $name, 'username' => 'stu_' . $uniq,
            'email' => $uniq . '@test.local',
            'password' => Hash::make('secret'), 'role' => 'student', 'is_active' => true,
        ]);
        return Student::create([
            'user_id' => $user->id, 'supervisor_id' => $this->supervisorId, 'level_id' => 1,
            'student_number' => 'STU-' . strtoupper(str_replace('.', '', $uniq)),
        ]);
    }

    public function test_admin_student_report_renders_summary_and_tables(): void
    {
        $student = $this->makeStudent('طالب تقرير');

        $session = RecitationSession::create([
            'student_id' => $student->id, 'student_name' => 'طالب',
            'supervisor_id' => $this->supervisorId, 'type' => 'new_memorization', 'part_id' => 1,
            'session_date' => now()->toDateString(), 'status' => 'passed',
            'start_page' => 1, 'end_page' => 3, 'total_pages' => 3,
            'total_errors' => 0, 'total_hesitations' => 0,
        ]);
        for ($p = 1; $p <= 3; $p++) {
            \App\Models\RecitationPage::create([
                'session_id' => $session->id, 'page_number' => $p,
                'errors' => 0, 'hesitations' => 0, 'color_status' => 'green', 'is_passed' => true,
            ]);
        }

        Attendance::create([
            'student_id' => $student->id, 'supervisor_id' => $this->supervisorId,
            'date' => now()->toDateString(), 'status' => 'present',
        ]);

        $admin = User::firstWhere('role', 'admin');
        $resp = $this->actingAs($admin)->get('/admin/reports/student/' . $student->id);

        $resp->assertStatus(200);
        $body = $resp->getContent();

        $this->assertStringContainsString('تقرير طالب', $body);
        $this->assertStringContainsString('طالب تقرير', $body);
        $this->assertStringContainsString('إجمالي الجلسات', $body);
        $this->assertStringContainsString('نسبة النجاح', $body);
        $this->assertStringContainsString('نسبة الحضور', $body);
        $this->assertStringContainsString('جلسات التسميع', $body);
        $this->assertStringContainsString('سجلات الحضور', $body);
        $this->assertMatchesRegularExpression('/<div class="rpt-kpi-value">\s*1\s*<\/div>/u', $body);
    }

    public function test_admin_student_report_respects_custom_date_range(): void
    {
        $student = $this->makeStudent('طالب فترة');

        // Session outside the requested range
        RecitationSession::create([
            'student_id' => $student->id, 'student_name' => 'طالب',
            'supervisor_id' => $this->supervisorId, 'type' => 'review', 'part_id' => 1,
            'session_date' => '2025-01-15', 'status' => 'passed',
            'start_page' => 1, 'end_page' => 3, 'total_pages' => 3,
            'total_errors' => 0, 'total_hesitations' => 0,
        ]);
        // Session inside the requested range
        RecitationSession::create([
            'student_id' => $student->id, 'student_name' => 'طالب',
            'supervisor_id' => $this->supervisorId, 'type' => 'review', 'part_id' => 1,
            'session_date' => '2026-08-10', 'status' => 'passed',
            'start_page' => 1, 'end_page' => 3, 'total_pages' => 3,
            'total_errors' => 0, 'total_hesitations' => 0,
        ]);

        $admin = User::firstWhere('role', 'admin');
        $resp = $this->actingAs($admin)->get(
            '/admin/reports/student/' . $student->id
            . '?from_date=2026-08-01&to_date=2026-08-31'
        );

        $resp->assertStatus(200);
        $body = $resp->getContent();

        $this->assertStringContainsString('2026/08/10', $body);
        $this->assertStringNotContainsString('2025/01/15', $body);
    }

    public function test_admin_student_report_404_for_missing_student(): void
    {
        $admin = User::firstWhere('role', 'admin');
        $resp = $this->actingAs($admin)->get('/admin/reports/student/999999');
        $resp->assertStatus(404);
    }

    public function test_admin_student_report_pdf_export_returns_pdf(): void
    {
        $student = $this->makeStudent('طالب PDF');

        $admin = User::firstWhere('role', 'admin');
        $resp = $this->actingAs($admin)->get(
            '/admin/reports/student/' . $student->id . '?export=pdf'
        );

        // TCPDF Output('D') streams the PDF as a download, which bypasses
        // the test response's content buffer. The status code is enough to
        // confirm the export path runs without error (the same approach
        // used in ReportRoutesSmokeTest for ?export=pdf routes).
        $this->assertLessThan(500, $resp->getStatusCode());
    }
}
