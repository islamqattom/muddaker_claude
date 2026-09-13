<?php

namespace Tests\Feature;

use App\Models\Level;
use App\Models\QuranPart;
use App\Models\RecitationSession;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;
use Tests\CreatesApplication;

class ReportsBreakdownTest extends BaseTestCase
{
    use CreatesApplication;

    private int $seedSeq = 0;
    private int $supervisorId;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a supervisor (no hard-coded id so auto-increment doesn't collide
        // across RefreshDatabase-rollback-then-rerun cycles)
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
            User::create([
                'name' => 'Admin', 'username' => 'admin_' . $this->seedSeq,
                'email' => 'admin_' . $this->seedSeq . '@test.local',
                'password' => Hash::make('secret'), 'role' => 'admin', 'is_active' => true,
            ]);
        }

        // This class does not wrap tests in transactions, so make the seed
        // resilient to ids already consumed by earlier factory-based tests
        // (RefreshDatabase rollbacks do not reset AUTO_INCREMENT).
        Model::unguard();

        Level::firstOrCreate(['id' => 1], [
            'name' => 'مستوى 1', 'level_number' => 1,
            'new_memorization_pages' => 3, 'review_pages' => 6, 'description' => 'تجريبي',
        ]);

        // part_number is unique: free it from any stale row before seeding id=1.
        $stalePart = QuranPart::where('part_number', 1)->where('id', '!=', 1)->first();
        if ($stalePart) {
            RecitationSession::where('part_id', $stalePart->id)->update(['part_id' => null]);
            $stalePart->delete();
        }
        QuranPart::firstOrCreate(['id' => 1], [
            'part_number' => 1, 'name' => 'الجزء الأول', 'start_page' => 1, 'end_page' => 20,
        ]);

        Model::reguard();
    }

    private function makeStudent(string $name, string $suffix): Student
    {
        $uniq = $suffix . '_' . uniqid('', true);
        $user = User::create([
            'name' => $name, 'username' => 'stu_' . $uniq,
            'email' => 'stu_' . $uniq . '@test.local',
            'password' => Hash::make('secret'), 'role' => 'student', 'is_active' => true,
        ]);
        return Student::create([
            'user_id' => $user->id, 'supervisor_id' => $this->supervisorId, 'level_id' => 1,
            'student_number' => 'STU-' . strtoupper(str_replace('.', '', $uniq)),
        ]);
    }

    private function makeSession(int $studentId, string $type, ?int $partId = null, int $totalPages = 3, string $status = 'passed'): void
    {
        RecitationSession::create([
            'student_id' => $studentId, 'student_name' => 'طالب',
            'supervisor_id' => $this->supervisorId, 'type' => $type, 'part_id' => $partId,
            'session_date' => now()->toDateString(),
            'total_errors' => 0, 'total_hesitations' => 0, 'status' => $status,
            'total_pages' => $totalPages,
            'start_page' => 1, 'end_page' => $totalPages,
            'final_score' => 85.0,
        ]);
    }

    public function test_quarter_report_renders_written_breakdown_when_students_have_sessions(): void
    {
        $student = $this->makeStudent('طالب اختبار', 'written');
        $this->makeSession($student->id, 'mafsil');
        $this->makeSession($student->id, 'new_memorization', 1);
        $this->makeSession($student->id, 'review', 1);
        $this->makeSession($student->id, 'recitation', 1);
        $this->makeSession($student->id, 'unmastered', 1);

        $admin = User::firstWhere('role', 'admin');
        $resp = $this->actingAs($admin)->get('/admin/reports/quarter');
        $resp->assertStatus(200);

        $body = $resp->getContent();
        $this->assertStringContainsString('تقرير مكتوب', $body,
            'Page should render the written report section heading');
        $this->assertStringContainsString('المفصل:', $body);
        $this->assertStringContainsString('الحفظ الجديد:', $body);
        $this->assertStringContainsString('عدد جلسات الحفظ الجديد:', $body);
        $this->assertStringContainsString('عدد الجلسات النّاجحة:', $body);
        $this->assertStringContainsString('عدد الجلسات الرّاسبة:', $body);
        $this->assertStringContainsString('عدد صفحات الحفظ الجديد النّاجحة:', $body);
        $this->assertStringContainsString('عدد صفحات الحفظ الجديد الرّاسبة:', $body);
        $this->assertStringContainsString('عدد الأجزاء المحفوظة:', $body);
        $this->assertStringContainsString('المراجعة:', $body);
        $this->assertStringContainsString('السرد:', $body);
        $this->assertStringContainsString('الحفظ غير المتقن:', $body);
        $this->assertStringContainsString('إجمالي جلسات الربع', $body);
        $this->assertStringContainsString('طالب اختبار', $body,
            'Student name should appear in the breakdown');
    }

    public function test_breakdown_section_is_omitted_when_no_sessions(): void
    {
        $admin = User::firstWhere('role', 'admin');
        $resp = $this->actingAs($admin)->get('/admin/reports/quarter?year=1990&quarter=1');
        $resp->assertStatus(200);
        $body = $resp->getContent();
        $this->assertStringNotContainsString('تقرير مكتوب — ملخص النشاط', $body,
            'Written report section should not render when no sessions exist');
    }

    public function test_breakdown_counts_match_actual_sessions(): void
    {
        $student = $this->makeStudent('طالب عدّ', 'count');
        // 2 mafsil sessions of 4 pages each
        $this->makeSession($student->id, 'mafsil', null, 4);
        $this->makeSession($student->id, 'mafsil', null, 4);
        // 3 review sessions of 5 pages each = 15 review pages
        for ($i = 0; $i < 3; $i++) {
            $this->makeSession($student->id, 'review', 1, 5);
        }

        $admin = User::firstWhere('role', 'admin');
        $resp = $this->actingAs($admin)->get('/admin/reports/quarter?student_id=' . $student->id);
        $resp->assertStatus(200);

        $body = $resp->getContent();

        // Locate the per-student breakdown row
        $rowPattern = '/<div class="rpt-written-row"[^>]*>.*?طالب عدّ.*?إجمالي جلسات الربع.*?<\/div>\s*<\/div>/s';
        $this->assertTrue((bool) preg_match($rowPattern, $body, $matches),
            'Breakdown row for student should exist');
        $row = $matches[0];

        // Mafsil: two presented sessions, both passed.
        preg_match('/المفصل:<\/strong>\s*<\/div>\s*<div class="rpt-written-lines"[^>]*>(.*?)<\/div>\s*<\/div>/s', $row, $mafsilBlock);
        $this->assertNotEmpty($mafsilBlock, 'mafsil block should be extracted');
        $this->assertSame(2, substr_count($mafsilBlock[1], 'المفصل الذي تقدم له'), 'Should list 2 mafsil sessions');
        $this->assertSame(2, substr_count($mafsilBlock[1], 'ناجح'), 'Both mafsil sessions should be passed');

        // Review: 3 passed sessions, 15 successful pages, 0 failed pages
        preg_match('/المراجعة:<\/strong>\s*<\/div>\s*<div class="rpt-written-lines"[^>]*>(.*?)<\/div>\s*<\/div>/s', $row, $reviewBlock);
        $this->assertNotEmpty($reviewBlock, 'review block should be extracted');
        $this->assertMatchesRegularExpression('/عدد جلسات المراجعة:\s*<strong[^>]*>3<\/strong>/u', $reviewBlock[1]);
        $this->assertMatchesRegularExpression('/عدد الجلسات النّاجحة:\s*<strong[^>]*>3<\/strong>/u', $reviewBlock[1]);
        $this->assertMatchesRegularExpression('/عدد الجلسات الرّاسبة:\s*<strong[^>]*>0<\/strong>/u', $reviewBlock[1]);
        $this->assertMatchesRegularExpression('/عدد صفحات المراجعة النّاجحة:\s*<strong[^>]*>15<\/strong>/u', $reviewBlock[1]);
        $this->assertMatchesRegularExpression('/عدد صفحات المراجعة الرّاسبة:\s*<strong[^>]*>0<\/strong>/u', $reviewBlock[1]);
    }

    public function test_breakdown_includes_pages_count_for_new_memorization(): void
    {
        $student = $this->makeStudent('طالب حفظ', 'memorize');
        // 2 new memorization sessions of 6 pages each = 12 pages, on the same part_id (1)
        // so we test the pages total rather than parts count
        $this->makeSession($student->id, 'new_memorization', 1, 6);
        $this->makeSession($student->id, 'new_memorization', 1, 6);

        $admin = User::firstWhere('role', 'admin');
        $resp = $this->actingAs($admin)->get('/admin/reports/quarter?student_id=' . $student->id);
        $resp->assertStatus(200);
        $body = $resp->getContent();

        $this->assertMatchesRegularExpression('/عدد جلسات الحفظ الجديد:\s*<strong[^>]*>2<\/strong>/u', $body);
        $this->assertMatchesRegularExpression('/عدد الجلسات النّاجحة:\s*<strong[^>]*>2<\/strong>/u', $body);
        $this->assertMatchesRegularExpression('/عدد الجلسات الرّاسبة:\s*<strong[^>]*>0<\/strong>/u', $body);
        $this->assertMatchesRegularExpression('/عدد صفحات الحفظ الجديد النّاجحة:\s*<strong[^>]*>12<\/strong>/u', $body);
        $this->assertMatchesRegularExpression('/عدد صفحات الحفظ الجديد الرّاسبة:\s*<strong[^>]*>0<\/strong>/u', $body);
        $this->assertMatchesRegularExpression('/عدد الأجزاء المحفوظة:\s*<strong[^>]*>0<\/strong>/u', $body);
    }

    public function test_part_counts_when_sard_passed_covering_all_pages_even_with_failed_new_mem_page(): void
    {
        $student = $this->makeStudent('طالب سرد', 'sard');

        // New-memorization over pages 1-20 with page 5 failed.
        $mem = RecitationSession::create([
            'student_id' => $student->id, 'student_name' => 'طالب',
            'supervisor_id' => $this->supervisorId, 'type' => 'new_memorization', 'part_id' => 1,
            'session_date' => now()->toDateString(), 'status' => 'failed',
            'start_page' => 1, 'end_page' => 20, 'total_pages' => 20,
            'total_errors' => 3, 'total_hesitations' => 0,
        ]);
        for ($p = 1; $p <= 20; $p++) {
            \App\Models\RecitationPage::create([
                'session_id' => $mem->id, 'page_number' => $p,
                'errors' => $p === 5 ? 3 : 0, 'hesitations' => 0,
                'color_status' => $p === 5 ? 'black' : 'green', 'is_passed' => $p !== 5,
            ]);
        }

        // Passed سرد covering pages 1-20 (page 5 passes here).
        $sard = RecitationSession::create([
            'student_id' => $student->id, 'student_name' => 'طالب',
            'supervisor_id' => $this->supervisorId, 'type' => 'recitation', 'part_id' => 1,
            'session_date' => now()->toDateString(), 'status' => 'passed',
            'start_page' => 1, 'end_page' => 20, 'total_pages' => 20,
            'total_errors' => 0, 'total_hesitations' => 0, 'final_score' => 95,
        ]);
        for ($p = 1; $p <= 20; $p++) {
            \App\Models\RecitationPage::create([
                'session_id' => $sard->id, 'page_number' => $p,
                'errors' => 0, 'hesitations' => 0, 'color_status' => 'green', 'is_passed' => true,
            ]);
        }

        $admin = User::firstWhere('role', 'admin');
        $resp = $this->actingAs($admin)->get('/admin/reports/quarter?student_id=' . $student->id);
        $resp->assertStatus(200);

        $this->assertMatchesRegularExpression(
            '/عدد الأجزاء المحفوظة:\s*<strong[^>]*>1<\/strong>/u',
            $resp->getContent(),
            'A part whose سرد passed over all pages should count even if a new-memorization page failed'
        );
    }

    public function test_review_part_counts_when_all_pages_reviewed_even_if_a_page_failed(): void
    {
        $student = $this->makeStudent('طالب مراجعة', 'revpart');

        // Review covering every page 1-20; page 5 fails, but the part is
        // still considered reviewed (no pass/سرد requirement).
        $session = RecitationSession::create([
            'student_id' => $student->id, 'student_name' => 'طالب',
            'supervisor_id' => $this->supervisorId, 'type' => 'review', 'part_id' => 1,
            'session_date' => now()->toDateString(), 'status' => 'failed',
            'start_page' => 1, 'end_page' => 20, 'total_pages' => 20,
            'total_errors' => 3, 'total_hesitations' => 0,
        ]);
        for ($p = 1; $p <= 20; $p++) {
            \App\Models\RecitationPage::create([
                'session_id' => $session->id, 'page_number' => $p,
                'errors' => $p === 5 ? 3 : 0, 'hesitations' => 0,
                'color_status' => $p === 5 ? 'black' : 'green', 'is_passed' => $p !== 5,
            ]);
        }

        $admin = User::firstWhere('role', 'admin');
        $resp = $this->actingAs($admin)->get('/admin/reports/quarter?student_id=' . $student->id);
        $resp->assertStatus(200);
        $body = $resp->getContent();

        $this->assertMatchesRegularExpression('/عدد جلسات المراجعة:\s*<strong[^>]*>1<\/strong>/u', $body);
        $this->assertMatchesRegularExpression('/عدد صفحات المراجعة النّاجحة:\s*<strong[^>]*>19<\/strong>/u', $body);
        $this->assertMatchesRegularExpression('/عدد صفحات المراجعة الرّاسبة:\s*<strong[^>]*>1<\/strong>/u', $body);
        $this->assertMatchesRegularExpression('/عدد الأجزاء المراجعة:\s*<strong[^>]*>1<\/strong>/u', $body);
    }

    public function test_unmastered_section_hidden_without_sessions_and_shows_details_with_them(): void
    {
        $admin = User::firstWhere('role', 'admin');

        // Student without any unmastered session -> section hidden.
        $plain = $this->makeStudent('طالب بدون غير متقن', 'nounm');
        $this->makeSession($plain->id, 'new_memorization', 1);
        $resp = $this->actingAs($admin)->get('/admin/reports/quarter?student_id=' . $plain->id);
        $resp->assertStatus(200);
        $this->assertStringNotContainsString('عدد جلسات الحفظ غير المتقن', $resp->getContent());

        // Student with an unmastered session -> section shown with details.
        $student = $this->makeStudent('طالب غير متقن', 'unm');
        $session = RecitationSession::create([
            'student_id' => $student->id, 'student_name' => 'طالب',
            'supervisor_id' => $this->supervisorId, 'type' => 'unmastered', 'part_id' => 1,
            'session_date' => now()->toDateString(), 'status' => 'failed',
            'start_page' => 1, 'end_page' => 20, 'total_pages' => 20,
            'total_errors' => 3, 'total_hesitations' => 0,
        ]);
        for ($p = 1; $p <= 20; $p++) {
            \App\Models\RecitationPage::create([
                'session_id' => $session->id, 'page_number' => $p,
                'errors' => $p === 5 ? 3 : 0, 'hesitations' => 0,
                'color_status' => $p === 5 ? 'black' : 'green', 'is_passed' => $p !== 5,
            ]);
        }

        $resp = $this->actingAs($admin)->get('/admin/reports/quarter?student_id=' . $student->id);
        $resp->assertStatus(200);
        $body = $resp->getContent();

        $this->assertStringContainsString('الحفظ غير المتقن:', $body);
        $this->assertMatchesRegularExpression('/عدد جلسات الحفظ غير المتقن:\s*<strong[^>]*>1<\/strong>/u', $body);
        $this->assertMatchesRegularExpression('/عدد الجلسات الرّاسبة:\s*<strong[^>]*>1<\/strong>/u', $body);
        $this->assertMatchesRegularExpression('/عدد صفحات الحفظ غير المتقن النّاجحة:\s*<strong[^>]*>19<\/strong>/u', $body);
        $this->assertMatchesRegularExpression('/عدد صفحات الحفظ غير المتقن الرّاسبة:\s*<strong[^>]*>1<\/strong>/u', $body);
        $this->assertMatchesRegularExpression('/عدد الأجزاء غير المتقنة:\s*<strong[^>]*>1<\/strong>/u', $body);
    }
}
