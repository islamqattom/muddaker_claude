<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Student, RecitationSession};
use App\Services\ReportExporter;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $reports,
        private ReportExporter $exporter,
    ) {
    }

    public function index()
    {
        $stats = [
            'total_students' => Student::count(),
            'total_sessions' => RecitationSession::count(),
            'passed_sessions' => RecitationSession::where('status', 'passed')->count(),
            'failed_sessions' => RecitationSession::whereIn('status', ['failed', 'failed_continued'])->count(),
            'avg_score' => round(RecitationSession::whereNotNull('final_score')->avg('final_score') ?? 0, 1),
        ];

        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $monthSessions = RecitationSession::whereBetween('session_date', [$monthStart, $monthEnd])->get();
        $monthStats = $this->reports->recitationStats($monthSessions);
        $monthTrend = $this->reports->recitationDailyTrend($monthSessions);
        $topStudents = $this->reports->topStudents($monthSessions, 5);

        return view('admin.reports.index', compact('stats', 'monthStats', 'monthTrend', 'topStudents'));
    }

    public function recitation(Request $request)
    {
        $query = $this->reports->recitationQuery($request);
        $sessions = (clone $query)->latest('session_date')->paginate(20)->withQueryString();

        $allSessions = (clone $query)->get();
        $stats = $this->reports->recitationStats($allSessions);
        $trend  = $this->reports->recitationDailyTrend($allSessions);
        $top    = $this->reports->topStudents($allSessions, 5);

        [$fromDate, $toDate, $period] = $this->reports->resolvePeriod($request);
        $dropdowns = $this->reports->dropdownData($request);

        if ($export = $request->export) {
            return $this->exporter->exportRecitation($export, $allSessions, $stats, $fromDate, $toDate, $period);
        }

        return view('admin.reports.recitation', array_merge(
            compact('sessions', 'stats', 'trend', 'top', 'fromDate', 'toDate', 'period'),
            $dropdowns
        ));
    }

    public function attendance(Request $request)
    {
        $query = $this->reports->attendanceQuery($request);
        $attendances = (clone $query)->latest('date')->paginate(20)->withQueryString();

        $allAttendances = (clone $query)->get();
        $stats = $this->reports->attendanceStats($allAttendances);
        $trend = $this->reports->attendanceDailyTrend($allAttendances);

        [$fromDate, $toDate, $period] = $this->reports->resolvePeriod($request);
        $dropdowns = $this->reports->dropdownData($request);

        if ($export = $request->export) {
            return $this->exporter->exportAttendance($export, $allAttendances, $stats, $fromDate, $toDate, $period);
        }

        return view('admin.reports.attendance', array_merge(
            compact('attendances', 'stats', 'trend', 'fromDate', 'toDate', 'period'),
            $dropdowns
        ));
    }

    public function quarter(Request $request)
    {
        $year    = (int) ($request->year ?? Carbon::now()->year);
        $quarter = (int) ($request->quarter ?? ReportService::quarterOf());
        $request->merge(['period' => 'quarter', 'year' => $year, 'quarter' => $quarter]);

        [$fromDate, $toDate] = ReportService::quarterRange($year, $quarter);
        $payload = $this->aggregateReportData($request, $fromDate, $toDate);
        $dropdowns = $this->reports->dropdownData($request);

        if ($export = $request->export) {
            return $this->exporter->exportAggregate($export, $payload, [
                'title'    => 'التقرير الربعي — الربع ' . $this->arabicOrdinal($quarter) . ' ' . $year,
                'subtitle' => 'الفترة: ' . $fromDate->format('Y/m/d') . ' - ' . $toDate->format('Y/m/d'),
                'filename' => 'quarterly-report-' . $year . '-Q' . $quarter,
            ]);
        }

        return view('admin.reports.quarter', array_merge(
            $payload,
            $dropdowns,
            ['year' => $year, 'quarter' => $quarter]
        ));
    }

    public function half(Request $request)
    {
        $year = (int) ($request->year ?? Carbon::now()->year);
        $half = (int) ($request->half ?? ReportService::halfOf());
        $request->merge(['period' => 'half', 'year' => $year, 'half' => $half]);

        [$fromDate, $toDate] = ReportService::halfRange($year, $half);
        $payload = $this->aggregateReportData($request, $fromDate, $toDate);
        $dropdowns = $this->reports->dropdownData($request);

        if ($export = $request->export) {
            return $this->exporter->exportAggregate($export, $payload, [
                'title'    => 'التقرير نصف السنوي — ' . $this->arabicOrdinal($half) . ' ' . $year,
                'subtitle' => 'الفترة: ' . $fromDate->format('Y/m/d') . ' - ' . $toDate->format('Y/m/d'),
                'filename' => 'half-yearly-report-' . $year . '-H' . $half,
            ]);
        }

        return view('admin.reports.half', array_merge(
            $payload,
            $dropdowns,
            ['year' => $year, 'half' => $half]
        ));
    }

    public function year(Request $request)
    {
        $year = (int) ($request->year ?? Carbon::now()->year);
        $request->merge(['period' => 'year', 'year' => $year]);

        [$fromDate, $toDate] = ReportService::yearRange($year);
        $payload = $this->aggregateReportData($request, $fromDate, $toDate);
        $dropdowns = $this->reports->dropdownData($request);

        if ($export = $request->export) {
            return $this->exporter->exportAggregate($export, $payload, [
                'title'    => 'التقرير السنوي — ' . $year,
                'subtitle' => 'الفترة: ' . $fromDate->format('Y/m/d') . ' - ' . $toDate->format('Y/m/d'),
                'filename' => 'annual-report-' . $year,
            ]);
        }

        return view('admin.reports.year', array_merge(
            $payload,
            $dropdowns,
            ['year' => $year]
        ));
    }

    private function aggregateReportData(Request $request, $fromDate, $toDate): array
    {
        $recQuery = $this->reports->recitationQuery($request);
        $attQuery = $this->reports->attendanceQuery($request);

        $allSessions    = (clone $recQuery)->orderBy('session_date')->get();
        $allAttendances = (clone $attQuery)->orderBy('date')->get();

        $recSessions    = (clone $recQuery)->orderBy('session_date', 'desc')->paginate(20)->withQueryString();
        $attAttendances = (clone $attQuery)->orderBy('date', 'desc')->paginate(20)->withQueryString();

        return [
            'recSessions'    => $recSessions,
            'attAttendances' => $attAttendances,
            'allSessions'    => $allSessions,
            'allAttendances' => $allAttendances,
            'recStats'       => $this->reports->recitationStats($allSessions),
            'attStats'       => $this->reports->attendanceStats($allAttendances),
            'recTrend'       => $this->reports->recitationMonthlyTrend($allSessions),
            'attTrend'       => $this->reports->attendanceMonthlyTrend($allAttendances),
            'top'            => $this->reports->topStudents($allSessions, 5),
            'fromDate'       => $fromDate,
            'toDate'         => $toDate,
        ];
    }

    private function arabicOrdinal(int $n): string
    {
        return [1 => 'الأول', 2 => 'الثاني', 3 => 'الثالث', 4 => 'الرابع'][$n] ?? (string) $n;
    }
}
