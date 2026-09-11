<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\{Student, Attendance, RecitationSession};
use App\Services\ReportExporter;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $reports,
        private ReportExporter $exporter,
    ) {
    }

    public function index()
    {
        $supervisorId = Auth::id();
        $students = Student::where('supervisor_id', $supervisorId)->with('user')->get();

        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $monthSessions = RecitationSession::whereHas('student', fn ($q) => $q->where('supervisor_id', $supervisorId))
            ->whereBetween('session_date', [$monthStart, $monthEnd])->get();
        $monthStats = $this->reports->recitationStats($monthSessions);
        $monthTrend = $this->reports->recitationDailyTrend($monthSessions);
        $topStudents = $this->reports->topStudents($monthSessions, 5);

        $monthAttendances = Attendance::where('supervisor_id', $supervisorId)
            ->whereBetween('date', [$monthStart, $monthEnd])->get();
        $attendanceStats = $this->reports->attendanceStats($monthAttendances);
        $attendanceTrend = $this->reports->attendanceDailyTrend($monthAttendances);

        $stats = [
            'total_students' => $students->count(),
            'total_sessions' => RecitationSession::whereHas('student', fn ($q) => $q->where('supervisor_id', $supervisorId))->count(),
            'passed_sessions' => RecitationSession::whereHas('student', fn ($q) => $q->where('supervisor_id', $supervisorId))->where('status', 'passed')->count(),
            'failed_sessions' => RecitationSession::whereHas('student', fn ($q) => $q->where('supervisor_id', $supervisorId))->whereIn('status', ['failed', 'failed_continued'])->count(),
            'avg_score' => round((float) RecitationSession::whereHas('student', fn ($q) => $q->where('supervisor_id', $supervisorId))->whereNotNull('final_score')->avg('final_score') ?? 0, 1),
        ];

        return view('supervisor.reports.index', compact(
            'students', 'stats', 'monthStats', 'monthTrend', 'topStudents', 'attendanceStats', 'attendanceTrend'
        ));
    }

    public function recitationReport(Request $request)
    {
        $supervisorId = Auth::id();
        $request->validate([
            'student_id' => 'nullable|exists:students,id',
            'type' => 'nullable|in:new_memorization,review,unmastered,recitation,mafsil,all',
        ]);

        $query = $this->reports->recitationQuery($request, $supervisorId);
        $sessions = (clone $query)->orderBy('session_date', 'desc')->paginate(20)->withQueryString();

        $allSessions = (clone $query)->get();
        $stats = $this->reports->recitationStats($allSessions);
        $trend = $this->reports->recitationDailyTrend($allSessions);
        $top   = $this->reports->topStudents($allSessions, 5);

        [$fromDate, $toDate, $period] = $this->reports->resolvePeriod($request);
        $dropdowns = $this->reports->dropdownData($request, $supervisorId);

        if ($export = $request->export) {
            return $this->exportRecitation($export, $allSessions, $stats, $fromDate, $toDate, $period);
        }

        return view('supervisor.reports.recitation', array_merge(
            compact('sessions', 'stats', 'trend', 'top', 'fromDate', 'toDate', 'period'),
            $dropdowns
        ));
    }

    public function attendanceReport(Request $request)
    {
        $supervisorId = Auth::id();
        $request->validate([
            'student_id' => 'nullable|exists:students,id',
        ]);

        $query = $this->reports->attendanceQuery($request, $supervisorId);
        $attendances = (clone $query)->orderBy('date', 'desc')->paginate(20)->withQueryString();

        $allAttendances = (clone $query)->get();
        $stats = $this->reports->attendanceStats($allAttendances);
        $trend = $this->reports->attendanceDailyTrend($allAttendances);

        [$fromDate, $toDate, $period] = $this->reports->resolvePeriod($request);
        $dropdowns = $this->reports->dropdownData($request, $supervisorId);

        if ($export = $request->export) {
            return $this->exportAttendance($export, $allAttendances, $stats, $fromDate, $toDate, $period);
        }

        return view('supervisor.reports.attendance', array_merge(
            compact('attendances', 'stats', 'trend', 'fromDate', 'toDate', 'period'),
            $dropdowns
        ));
    }

    public function quarterReport(Request $request)
    {
        $supervisorId = Auth::id();
        $year    = (int) ($request->year ?? Carbon::now()->year);
        $quarter = (int) ($request->quarter ?? ReportService::quarterOf());
        $request->merge(['period' => 'quarter', 'year' => $year, 'quarter' => $quarter]);

        [$fromDate, $toDate] = ReportService::quarterRange($year, $quarter);
        $payload = $this->aggregateReportData($request, $fromDate, $toDate, $supervisorId);
        $dropdowns = $this->reports->dropdownData($request, $supervisorId);

        if ($export = $request->export) {
            return $this->exporter->exportAggregate($export, $payload, [
                'title'    => 'التقرير الربعي — الربع ' . $this->arabicOrdinal($quarter) . ' ' . $year,
                'subtitle' => 'الفترة: ' . $fromDate->format('Y/m/d') . ' - ' . $toDate->format('Y/m/d'),
                'filename' => 'supervisor-quarterly-' . $year . '-Q' . $quarter,
            ]);
        }

        return view('supervisor.reports.quarter', array_merge(
            $payload,
            $dropdowns,
            ['year' => $year, 'quarter' => $quarter]
        ));
    }

    public function halfReport(Request $request)
    {
        $supervisorId = Auth::id();
        $year = (int) ($request->year ?? Carbon::now()->year);
        $half = (int) ($request->half ?? ReportService::halfOf());
        $request->merge(['period' => 'half', 'year' => $year, 'half' => $half]);

        [$fromDate, $toDate] = ReportService::halfRange($year, $half);
        $payload = $this->aggregateReportData($request, $fromDate, $toDate, $supervisorId);
        $dropdowns = $this->reports->dropdownData($request, $supervisorId);

        if ($export = $request->export) {
            return $this->exporter->exportAggregate($export, $payload, [
                'title'    => 'التقرير نصف السنوي — ' . $this->arabicOrdinal($half) . ' ' . $year,
                'subtitle' => 'الفترة: ' . $fromDate->format('Y/m/d') . ' - ' . $toDate->format('Y/m/d'),
                'filename' => 'supervisor-half-yearly-' . $year . '-H' . $half,
            ]);
        }

        return view('supervisor.reports.half', array_merge(
            $payload,
            $dropdowns,
            ['year' => $year, 'half' => $half]
        ));
    }

    public function yearReport(Request $request)
    {
        $supervisorId = Auth::id();
        $year = (int) ($request->year ?? Carbon::now()->year);
        $request->merge(['period' => 'year', 'year' => $year]);

        [$fromDate, $toDate] = ReportService::yearRange($year);
        $payload = $this->aggregateReportData($request, $fromDate, $toDate, $supervisorId);
        $dropdowns = $this->reports->dropdownData($request, $supervisorId);

        if ($export = $request->export) {
            return $this->exporter->exportAggregate($export, $payload, [
                'title'    => 'التقرير السنوي — ' . $year,
                'subtitle' => 'الفترة: ' . $fromDate->format('Y/m/d') . ' - ' . $toDate->format('Y/m/d'),
                'filename' => 'supervisor-annual-' . $year,
            ]);
        }

        return view('supervisor.reports.year', array_merge(
            $payload,
            $dropdowns,
            ['year' => $year]
        ));
    }

    public function studentReport(Request $request, Student $student)
    {
        $supervisorId = Auth::id();
        if ($student->supervisor_id !== $supervisorId) {
            abort(403);
        }

        $fromDate = $request->from_date ? Carbon::parse($request->from_date) : Carbon::now()->startOfMonth();
        $toDate = $request->to_date ? Carbon::parse($request->to_date) : Carbon::now();

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

        $sessionStats = $this->reports->recitationStats($sessions);
        $attendanceStats = $this->reports->attendanceStats($attendances);

        if ($request->export === 'pdf') {
            return $this->exporter->exportStudentPdf($student, $sessions, $attendances, $sessionStats, $attendanceStats, $fromDate, $toDate);
        }

        return view('supervisor.reports.student', compact(
            'student', 'sessions', 'attendances', 'sessionStats', 'attendanceStats', 'fromDate', 'toDate'
        ));
    }

    private function aggregateReportData(Request $request, $fromDate, $toDate, int $supervisorId): array
    {
        $recQuery = $this->reports->recitationQuery($request, $supervisorId);
        $attQuery = $this->reports->attendanceQuery($request, $supervisorId);

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

    private function exportRecitation(string $format, $sessions, array $stats, $fromDate, $toDate, string $period)
    {
        $headers = ['#', 'الطالب', 'النوع', 'الجزء', 'الصفحات', 'الأخطاء', 'الترددات', 'الدرجة', 'الحالة', 'التاريخ'];
        $rows = $sessions->values()->map(function ($s, $i) {
            return [
                $i + 1,
                $s->student->user->name ?? '—',
                $s->type_label,
                $s->parts_label,
                $s->total_pages ?? '—',
                $s->total_errors ?? 0,
                $s->total_hesitations ?? 0,
                $s->final_score !== null ? number_format($s->final_score, 1) . '%' : '—',
                $s->status_label,
                $s->session_date?->format('Y/m/d') ?? '—',
            ];
        })->toArray();

        $subtitle = 'الفترة: ' . ($fromDate && $toDate ? $fromDate->format('Y/m/d') . ' - ' . $toDate->format('Y/m/d') : 'كل الفترات');
        $statsForExport = [
            'الإجمالي' => $stats['total'],
            'ناجح' => $stats['passed'],
            'راسب' => $stats['failed'],
            'نسبة النجاح' => $stats['pass_rate'] . '%',
        ];

        $filename = 'supervisor-recitation-' . now()->format('Ymd-His');

        return match ($format) {
            'excel', 'xlsx' => \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\RecitationReportExport('تقرير التسميع', $headers, $rows, $statsForExport, $subtitle),
                $filename . '.xlsx'
            ),
            'print' => response()->view('admin.reports.print', [
                'title' => 'تقرير التسميع',
                'headers' => $headers,
                'rows' => $rows,
                'stats' => $statsForExport,
                'subtitle' => $subtitle,
            ]),
            default => $this->exporter->exportRecitationPdf($sessions, $stats, $fromDate, $toDate),
        };
    }

    private function exportAttendance(string $format, $attendances, array $stats, $fromDate, $toDate, string $period)
    {
        $headers = ['#', 'الطالب', 'الحالة', 'وقت الحضور', 'ملاحظة', 'التاريخ'];
        $rows = $attendances->values()->map(function ($a, $i) {
            $statusLabel = match ($a->status) {
                'present' => 'حاضر', 'absent' => 'غائب', 'late' => 'متأخر', 'excused' => 'معذور', 'holiday' => 'عطلة', default => $a->status,
            };
            return [
                $i + 1,
                $a->student->user->name ?? '—',
                $statusLabel,
                $a->arrival_time ?? '—',
                $a->notes ?? '—',
                $a->date?->format('Y/m/d') ?? '—',
            ];
        })->toArray();

        $subtitle = 'الفترة: ' . ($fromDate && $toDate ? $fromDate->format('Y/m/d') . ' - ' . $toDate->format('Y/m/d') : 'كل الفترات');
        $statsForExport = [
            'الإجمالي' => $stats['total'],
            'حاضر' => $stats['present'],
            'غائب' => $stats['absent'],
            'متأخر' => $stats['late'],
            'بعذر' => $stats['excused'],
            'نسبة الحضور' => $stats['attendance_rate'] . '%',
        ];

        $filename = 'supervisor-attendance-' . now()->format('Ymd-His');

        return match ($format) {
            'excel', 'xlsx' => \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\AttendanceReportExport('تقرير الحضور والغياب', $headers, $rows, $statsForExport, $subtitle),
                $filename . '.xlsx'
            ),
            'print' => response()->view('admin.reports.print', [
                'title' => 'تقرير الحضور والغياب',
                'headers' => $headers,
                'rows' => $rows,
                'stats' => $statsForExport,
                'subtitle' => $subtitle,
            ]),
            default => $this->exporter->exportAttendancePdf($attendances, $stats, $fromDate, $toDate),
        };
    }
}
