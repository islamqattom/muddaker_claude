<?php

namespace Tests\Unit\Services;

use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class ReportServiceTest extends TestCase
{
    private ReportService $svc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->svc = new ReportService();
    }

    public function test_quarter_of_resolves_correctly_for_each_month(): void
    {
        $this->assertSame(1, ReportService::quarterOf(Carbon::create(2026, 1, 15)));
        $this->assertSame(1, ReportService::quarterOf(Carbon::create(2026, 3, 31)));
        $this->assertSame(2, ReportService::quarterOf(Carbon::create(2026, 4, 1)));
        $this->assertSame(2, ReportService::quarterOf(Carbon::create(2026, 6, 30)));
        $this->assertSame(3, ReportService::quarterOf(Carbon::create(2026, 7, 1)));
        $this->assertSame(3, ReportService::quarterOf(Carbon::create(2026, 9, 30)));
        $this->assertSame(4, ReportService::quarterOf(Carbon::create(2026, 10, 1)));
        $this->assertSame(4, ReportService::quarterOf(Carbon::create(2026, 12, 31)));
    }

    public function test_quarter_of_defaults_to_today(): void
    {
        $expected = (int) ceil(Carbon::now()->month / 3);
        $this->assertSame($expected, ReportService::quarterOf());
    }

    public function test_half_of_resolves_correctly(): void
    {
        $this->assertSame(1, ReportService::halfOf(Carbon::create(2026, 1, 15)));
        $this->assertSame(1, ReportService::halfOf(Carbon::create(2026, 6, 30)));
        $this->assertSame(2, ReportService::halfOf(Carbon::create(2026, 7, 1)));
        $this->assertSame(2, ReportService::halfOf(Carbon::create(2026, 12, 31)));
    }

    public function test_quarter_range_clamping(): void
    {
        [$start, $end] = ReportService::quarterRange(2026, 3);
        $this->assertSame('2026-07-01', $start->toDateString());
        $this->assertSame('2026-09-30', $end->toDateString());

        [$start, $end] = ReportService::quarterRange(2026, 0);
        $this->assertSame('2026-01-01', $start->toDateString());

        [$start, $end] = ReportService::quarterRange(2026, 99);
        $this->assertSame('2026-10-01', $start->toDateString());
    }

    public function test_half_range_clamping(): void
    {
        [$start, $end] = ReportService::halfRange(2026, 1);
        $this->assertSame('2026-01-01', $start->toDateString());
        $this->assertSame('2026-06-30', $end->toDateString());

        [$start, $end] = ReportService::halfRange(2026, 2);
        $this->assertSame('2026-07-01', $start->toDateString());
        $this->assertSame('2026-12-31', $end->toDateString());

        [$start, $end] = ReportService::halfRange(2026, 0);
        $this->assertSame('2026-01-01', $start->toDateString());
    }

    public function test_year_range(): void
    {
        [$start, $end] = ReportService::yearRange(2026);
        $this->assertSame('2026-01-01', $start->toDateString());
        $this->assertSame('2026-12-31', $end->toDateString());
    }

    public function test_resolve_period_quarter_returns_three_elements(): void
    {
        $req = Request::create('/', 'GET', ['period' => 'quarter', 'year' => 2026, 'quarter' => 2]);
        $result = $this->svc->resolvePeriod($req);

        $this->assertCount(3, $result);
        $this->assertSame('2026-04-01', $result[0]->toDateString());
        $this->assertSame('2026-06-30', $result[1]->toDateString());
        $this->assertSame('quarter', $result[2]);
    }

    public function test_resolve_period_half_returns_three_elements(): void
    {
        $req = Request::create('/', 'GET', ['period' => 'half', 'year' => 2026, 'half' => 2]);
        $result = $this->svc->resolvePeriod($req);

        $this->assertCount(3, $result);
        $this->assertSame('2026-07-01', $result[0]->toDateString());
        $this->assertSame('2026-12-31', $result[1]->toDateString());
        $this->assertSame('half', $result[2]);
    }

    public function test_resolve_period_year_returns_three_elements(): void
    {
        $req = Request::create('/', 'GET', ['period' => 'year', 'year' => 2025]);
        $result = $this->svc->resolvePeriod($req);

        $this->assertCount(3, $result);
        $this->assertSame('2025-01-01', $result[0]->toDateString());
        $this->assertSame('2025-12-31', $result[1]->toDateString());
        $this->assertSame('year', $result[2]);
    }

    public function test_resolve_period_today_returns_three_elements(): void
    {
        $req = Request::create('/', 'GET', ['period' => 'today']);
        $result = $this->svc->resolvePeriod($req);

        $this->assertCount(3, $result);
        $this->assertSame(Carbon::now()->toDateString(), $result[0]->toDateString());
        $this->assertSame('today', $result[2]);
    }

    public function test_resolve_period_all_returns_three_elements(): void
    {
        $req = Request::create('/', 'GET');
        $result = $this->svc->resolvePeriod($req);

        $this->assertCount(3, $result);
        $this->assertNull($result[0]);
        $this->assertNull($result[1]);
        $this->assertSame('all', $result[2]);
    }

    public function test_resolve_period_custom_returns_three_elements(): void
    {
        $req = Request::create('/', 'GET', ['period' => 'custom', 'from_date' => '2026-01-01', 'to_date' => '2026-01-31']);
        $result = $this->svc->resolvePeriod($req);

        $this->assertCount(3, $result);
        $this->assertSame('2026-01-01', $result[0]->toDateString());
        $this->assertSame('2026-01-31', $result[1]->toDateString());
        $this->assertSame('custom', $result[2]);
    }

    public function test_recitation_stats_counts_pages_from_passed_sessions_only(): void
    {
        $sessions = collect([
            $this->session(['status' => 'passed', 'total_pages' => 10, 'total_errors' => 2, 'total_hesitations' => 1]),
            $this->session(['status' => 'failed', 'total_pages' => 8, 'total_errors' => 5, 'total_hesitations' => 3]),
            $this->session(['status' => 'in_progress', 'total_pages' => 99, 'total_errors' => 99, 'total_hesitations' => 99]),
        ]);

        $stats = $this->svc->recitationStats($sessions);

        $this->assertSame(3, $stats['total']);
        $this->assertSame(1, $stats['in_progress']);
        $this->assertSame(50.0, $stats['pass_rate']);
        // Only accomplished (passed) pages are reported.
        $this->assertSame(10, $stats['total_pages']);
        // Errors/hesitations come from decided sessions, not in-progress ones.
        $this->assertSame(7, $stats['total_errors']);
        $this->assertSame(4, $stats['total_hesitations']);
    }

    public function test_attendance_stats_excludes_excused_from_rate_denominator(): void
    {
        $attendances = collect([
            $this->attendance('present'),
            $this->attendance('absent'),
            $this->attendance('excused'),
        ]);

        $stats = $this->svc->attendanceStats($attendances);

        $this->assertSame(3, $stats['total']);
        $this->assertSame(1, $stats['excused']);
        // 1 present out of 2 effective days (excused excluded) = 50%.
        $this->assertSame(50.0, $stats['attendance_rate']);
    }

    public function test_attendance_stats_counts_late_in_attendance_but_keeps_present_exclusive(): void
    {
        $attendances = collect([
            $this->attendance('present'),
            $this->attendance('present'),
            $this->attendance('late'),
            $this->attendance('absent'),
        ]);

        $stats = $this->svc->attendanceStats($attendances);

        $this->assertSame(4, $stats['total']);
        // Exclusive status used by the breakdowns/exports.
        $this->assertSame(2, $stats['present']);
        $this->assertSame(1, $stats['late']);
        // Headline metric counted by the KPI card: late is still attendance.
        $this->assertSame(3, $stats['attendance']);
        // 3 attended out of 4 (no excused) = 75%.
        $this->assertSame(75.0, $stats['attendance_rate']);
    }

    public function test_attendance_daily_trend_excludes_excused_from_rate(): void
    {
        $attendances = collect([
            $this->attendance('present'),
            $this->attendance('excused'),
        ]);

        $trend = $this->svc->attendanceDailyTrend($attendances);

        $this->assertSame(100.0, $trend[0]['rate']);
    }

    public function test_top_students_requires_minimum_decided_sessions(): void
    {
        $sessions = collect([
            // Student 1: a single perfect session.
            $this->session(['student_id' => 1, 'status' => 'passed', 'final_score' => 100]),
            // Student 2: three decided sessions with a lower average.
            $this->session(['student_id' => 2, 'status' => 'passed', 'final_score' => 90]),
            $this->session(['student_id' => 2, 'status' => 'passed', 'final_score' => 90]),
            $this->session(['student_id' => 2, 'status' => 'failed', 'final_score' => 90]),
        ]);

        $top = $this->svc->topStudents($sessions);

        $this->assertCount(1, $top);
        $this->assertSame(90.0, $top[0]['avg_score']);
    }

    public function test_top_students_falls_back_when_nobody_meets_minimum(): void
    {
        $sessions = collect([
            $this->session(['student_id' => 1, 'status' => 'passed', 'final_score' => 100]),
            $this->session(['student_id' => 2, 'status' => 'passed', 'final_score' => 80]),
        ]);

        $top = $this->svc->topStudents($sessions);

        $this->assertCount(2, $top);
        $this->assertSame(100.0, $top[0]['avg_score']);
    }

    public function test_student_breakdown_excludes_in_progress_and_adds_unmastered_pages(): void
    {
        $sessions = collect([
            $this->session(['student_id' => 1, 'type' => 'mafsil', 'status' => 'passed', 'part_id' => 5, 'total_pages' => 20]),
            $this->session(['student_id' => 1, 'type' => 'mafsil', 'status' => 'failed', 'part_id' => 6, 'total_pages' => 20]),
            $this->session(['student_id' => 1, 'type' => 'review', 'status' => 'in_progress', 'total_pages' => 15]),
            $this->session([
                'student_id' => 1, 'type' => 'unmastered', 'status' => 'failed',
                'pages' => collect([
                    (object) ['page_number' => 1, 'is_passed' => true],
                    (object) ['page_number' => 2, 'is_passed' => true],
                    (object) ['page_number' => 3, 'is_passed' => false],
                ]),
            ]),
        ]);

        $breakdown = $this->svc->studentBreakdown($sessions);

        $this->assertCount(1, $breakdown);
        $row = $breakdown[0];

        // In-progress session is excluded from the totals.
        $this->assertSame(3, $row['total_sessions']);
        $this->assertSame(0, $row['review_sessions']);
        $this->assertSame(0, $row['review_pages']);
        // Both decided mafsil sessions count, but only the passed one's
        // part and pages are reported as accomplished.
        $this->assertSame(2, $row['mafsil_sessions']);
        $this->assertSame(1, $row['mafsil_parts']);
        $this->assertSame(20, $row['mafsil_pages']);
        // Unmastered pages are counted from page records regardless of the
        // session result: 2 passed, 1 failed.
        $this->assertSame(1, $row['unmastered_sessions']);
        $this->assertSame(0, $row['unmastered_passed']);
        $this->assertSame(1, $row['unmastered_failed']);
        $this->assertSame(2, $row['unmastered_pages']);
        $this->assertSame(1, $row['unmastered_failed_pages']);
    }

    public function test_student_breakdown_includes_not_recited_days_in_total_sessions(): void
    {
        $sessions = collect([
            $this->session(['student_id' => 1, 'type' => 'new_memorization', 'status' => 'passed', 'part_id' => 1, 'total_pages' => 3]),
            $this->session(['student_id' => 1, 'type' => 'not_recitate', 'status' => 'not_recitate', 'session_date' => Carbon::create(2026, 1, 5)]),
            $this->session(['student_id' => 1, 'type' => 'not_recitate', 'status' => 'not_recitate', 'session_date' => Carbon::create(2026, 1, 6)]),
        ]);

        $breakdown = $this->svc->studentBreakdown($sessions);

        $this->assertCount(1, $breakdown);
        $row = $breakdown[0];

        $this->assertSame(2, $row['not_recite_days']);
        // The grand total counts the decided session plus the two days the
        // student did not recite.
        $this->assertSame(3, $row['total_sessions']);
        $this->assertSame(1, $row['sessions_without_not_recite']);
    }

    private function session(array $attrs = []): object
    {
        return (object) array_merge([
            'student_id' => 1,
            'student' => null,
            'type' => 'review',
            'status' => 'passed',
            'part_id' => null,
            'start_page' => null,
            'end_page' => null,
            'total_pages' => null,
            'total_errors' => 0,
            'total_hesitations' => 0,
            'final_score' => null,
            'unmastered_pages_count' => null,
            'session_date' => null,
        ], $attrs);
    }

    private function attendance(string $status): object
    {
        return (object) [
            'student_id' => 1,
            'status' => $status,
            'date' => Carbon::create(2026, 1, 10),
        ];
    }
}
