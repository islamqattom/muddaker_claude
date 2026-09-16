<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\BuildsReportData;
use App\Http\Controllers\Controller;
use App\Models\{Attendance, RecitationSession, Student};
use App\Services\ReportExporter;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    use BuildsReportData;

    public function __construct(
        private ReportService $reports,
        private ReportExporter $exporter,
    ) {
    }

    public function index(Request $request)
    {
        // Cumulative headline numbers (not tied to the selected period).
        $totals = [
            'total_students' => Student::count(),
        ];

        // Selected period (default: current month), resolved through the
        // same service the dedicated report pages use so the metrics are
        // identical across the dashboard and the detailed reports.
        $request->merge(['period' => $request->period ?? ReportService::PERIOD_MONTH]);
        [$fromDate, $toDate, $period] = $this->reports->resolvePeriod($request);

        $sessions    = $this->reports->recitationQuery($request)->get();
        $attendances = $this->reports->attendanceQuery($request)->get();

        $stats       = $this->reports->recitationStats($sessions);
        $attStats    = $this->reports->attendanceStats($attendances);
        $trend       = $this->reports->recitationDailyTrend($sessions);
        $topStudents = $this->reports->topStudents($sessions, 5);

        $students = Student::with('user')->orderBy('id')->limit(500)->get()
            ->sortBy(fn ($s) => $s->user->name ?? '')
            ->values();

        return view('admin.reports.index', compact(
            'totals', 'stats', 'attStats', 'trend', 'topStudents', 'students',
            'fromDate', 'toDate', 'period'
        ));
    }

    public function recitation(Request $request)
    {
        $data = $this->buildRecitationData($request, null);

        if ($export = $request->export) {
            return $this->exporter->exportRecitation(
                $export, $data['allSessions'], $data['stats'], $data['fromDate'], $data['toDate'], $data['period'], $data['summary']
            );
        }

        return view('reports.recitation', $data);
    }

    public function attendance(Request $request)
    {
        $data = $this->buildAttendanceData($request, null);

        if ($export = $request->export) {
            return $this->exporter->exportAttendance(
                $export, $data['allAttendances'], $data['stats'], $data['fromDate'], $data['toDate'], $data['period'], $data['summary']
            );
        }

        return view('reports.attendance', $data);
    }

    public function quarter(Request $request)
    {
        return $this->aggregateResponse($request, 'quarter', null, false, 'admin-');
    }

    public function half(Request $request)
    {
        return $this->aggregateResponse($request, 'half', null, false, 'admin-');
    }

    public function year(Request $request)
    {
        return $this->aggregateResponse($request, 'year', null, false, 'admin-');
    }

    /**
     * Student reports (تقرير الطلاب): pick a student and a period, then show
     * the written breakdown plus recitation/attendance KPIs. Mirrors the
     * supervisor's students report, but the admin picks from every student.
     */
    public function studentsReport(Request $request)
    {
        $allStudents = Student::with('user', 'level')
            ->get()
            ->sortBy(fn ($s) => $s->user->name ?? '')
            ->values();

        $request->merge(['period' => $request->period ?? 'year']);
        [$fromDate, $toDate, $period] = $this->reports->resolvePeriod($request);

        $periodLabels = [
            'today'   => 'اليوم',
            'week'    => 'هذا الأسبوع',
            'month'   => 'هذا الشهر',
            'quarter' => 'هذا الربع',
            'half'    => 'هذا النصف',
            'year'    => 'هذه السنة',
            'all'     => 'كل الفترات',
            'custom'  => 'فترة مخصصة',
        ];
        $periodLabel = $periodLabels[$period] ?? 'هذه السنة';

        $selectedStudent = $request->student_id
            ? $allStudents->firstWhere('id', (int) $request->student_id)
            : null;

        $breakdown = [];
        $recStats  = null;
        $attStats  = null;

        if ($selectedStudent) {
            $sessionsQuery = RecitationSession::where('student_id', $selectedStudent->id)
                ->with(['student.user', 'student.supervisor', 'supervisor', 'part', 'pages']);
            $attendanceQuery = Attendance::where('student_id', $selectedStudent->id);

            if ($fromDate && $toDate) {
                $sessionsQuery->whereBetween('session_date', [$fromDate->toDateString(), $toDate->toDateString()]);
                $attendanceQuery->whereBetween('date', [$fromDate->toDateString(), $toDate->toDateString()]);
            }

            $sessions    = $sessionsQuery->get();
            $attendances = $attendanceQuery->get();

            $breakdown = $this->reports->studentBreakdown($sessions, $attendances, $toDate);
            $recStats  = $this->reports->recitationStats($sessions);
            $attStats  = $this->reports->attendanceStats($attendances);
        }

        if ($request->export === 'pdf') {
            if ($selectedStudent) {
                return $this->exporter->exportWrittenReportPdf(
                    $selectedStudent, $breakdown, $periodLabel, $fromDate, $toDate
                );
            }

            return redirect()->route('admin.reports.students');
        }

        return view('admin.reports.students', compact(
            'allStudents', 'selectedStudent', 'breakdown', 'periodLabel', 'period', 'recStats', 'attStats', 'fromDate', 'toDate'
        ));
    }

    /**
     * Per-student report (تقرير طالب): recitation sessions + attendance
     * for one student in a chosen date range. Mirrors the supervisor
     * student report but is not bound to a single supervisor.
     */
    public function studentReport(Request $request, Student $student)
    {
        $fromDate = $request->from_date ? Carbon::parse($request->from_date) : Carbon::now()->startOfMonth();
        $toDate   = $request->to_date   ? Carbon::parse($request->to_date)   : Carbon::now();

        $student->load(['user', 'level', 'supervisor']);

        $sessions = RecitationSession::where('student_id', $student->id)
            ->with(['part', 'pages'])
            ->whereBetween('session_date', [$fromDate, $toDate])
            ->orderBy('session_date', 'desc')
            ->get();

        $attendances = Attendance::where('student_id', $student->id)
            ->whereBetween('date', [$fromDate, $toDate])
            ->orderBy('date', 'desc')
            ->get();

        $sessionStats    = $this->reports->recitationStats($sessions);
        $attendanceStats = $this->reports->attendanceStats($attendances);

        if ($request->export === 'pdf') {
            return $this->exporter->exportStudentPdf($student, $sessions, $attendances, $sessionStats, $attendanceStats, $fromDate, $toDate);
        }

        return view('admin.reports.student', compact(
            'student', 'sessions', 'attendances', 'sessionStats', 'attendanceStats', 'fromDate', 'toDate'
        ));
    }
}

