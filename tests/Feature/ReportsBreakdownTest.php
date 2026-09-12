<?php

namespace Tests\Feature;

use App\Models\Level;
use App\Models\QuranPart;
use App\Models\RecitationSession;
use App\Models\Student;
use App\Models\User;
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
        if (!Level::first()) {
            Level::create(['id' => 1, 'name' => 'مستوى 1', 'level_number' => 1, 'new_memorization_pages' => 3, 'review_pages' => 6, 'description' => 'تجريبي']);
        }
        if (!QuranPart::first()) {
            QuranPart::create(['id' => 1, 'part_number' => 1, 'name' => 'الجزء الأول', 'start_page' => 1, 'end_page' => 20]);
        }
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
        $this->assertStringContainsString('عدد الأجزاء المقدمة مفصل', $body);
        $this->assertStringContainsString('إجمالي الأجزاء الجديدة المحفوظة', $body);
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

        // Mafsil: 0 distinct parts (no part_id on mafsil), 2 sessions, 8 pages
        preg_match('/عدد الأجزاء المقدمة مفصل.*?<\/div>/s', $row, $mafsilBlock);
        $this->assertNotEmpty($mafsilBlock, 'mafsil block should be extracted');
        $this->assertStringContainsString('>0<', $mafsilBlock[0], 'Should show 0 mafsil parts');
        $this->assertStringContainsString('>2<', $mafsilBlock[0], 'Should show 2 mafsil sessions');
        $this->assertStringContainsString('8 صفحة', $mafsilBlock[0], 'Should show 8 mafsil pages');

        // Review: 3 sessions, 15 pages
        preg_match('/المراجعة:.*?<\/div>/s', $row, $reviewBlock);
        $this->assertNotEmpty($reviewBlock, 'review block should be extracted');
        $this->assertStringContainsString('>3<', $reviewBlock[0], 'Should show 3 review sessions');
        $this->assertStringContainsString('جلسات مراجعة', $reviewBlock[0]);
        $this->assertStringContainsString('15 صفحة', $reviewBlock[0], 'Should show 15 review pages');
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

        preg_match('/إجمالي الأجزاء الجديدة المحفوظة.*?<\/div>/s', $body, $block);
        $this->assertNotEmpty($block, 'new_memorization block should be extracted');
        $this->assertStringContainsString('>2<', $block[0], 'Should show 2 sessions');
        $this->assertStringContainsString('12 صفحة', $block[0], 'Should show 12 new memorization pages');
    }
}
