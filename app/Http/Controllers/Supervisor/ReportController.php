<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Concerns\BuildsReportData;
use App\Http\Controllers\Controller;
use App\Models\{Student, Attendance, RecitationSession};
use App\Services\ReportExporter;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    use BuildsReportData;

    public function __construct(
        private ReportService $reports,
        private ReportExporter $exporter,
    ) {
    }

    public function index()
    {
        \App\Support\MissedRecitationSync::run();

        $supervisorId = Auth::id();
        $students = Student::where('supervisor_id', $supervisorId)->with('user')->orderedByName()->get();

        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $monthSessions = $this->reports->actualRecitationSessions(
            RecitationSession::whereHas('student', fn ($q) => $q->where('supervisor_id', $supervisorId))
                ->whereBetween('session_date', [$monthStart, $monthEnd])->get()
        );
        $monthStats = $this->reports->recitationStats($monthSessions);
        $monthTrend = $this->reports->recitationDailyTrend($monthSessions);
        $topStudents = $this->reports->topStudents($monthSessions, 5);

        $monthAttendances = Attendance::where('supervisor_id', $supervisorId)
            ->whereBetween('date', [$monthStart, $monthEnd])->get();
        $attendanceStats = $this->reports->attendanceStats($monthAttendances);
        $attendanceTrend = $this->reports->attendanceDailyTrend($monthAttendances);

        $stats = [
            'total_students' => $students->count(),
            'total_sessions' => RecitationSession::whereHas('student', fn ($q) => $q->where('supervisor_id', $supervisorId))
                ->where('type', '!=', RecitationSession::TYPE_NOT_RECITE)
                ->where('status', '!=', RecitationSession::STATUS_NOT_RECITE)
                ->count(),
            'passed_sessions' => RecitationSession::whereHas('student', fn ($q) => $q->where('supervisor_id', $supervisorId))->where('status', 'passed')->count(),
            'failed_sessions' => RecitationSession::whereHas('student', fn ($q) => $q->where('supervisor_id', $supervisorId))->whereIn('status', ['failed', 'failed_continued', 'incomplete'])->count(),
            'avg_score' => round((float) RecitationSession::whereHas('student', fn ($q) => $q->where('supervisor_id', $supervisorId))->whereNotNull('final_score')->avg('final_score') ?? 0, 1),
        ];

        return view('supervisor.reports.index', compact(
            'students', 'stats', 'monthStats', 'monthTrend', 'topStudents', 'attendanceStats', 'attendanceTrend'
        ));
    }

    public function recitationReport(Request $request)
    {
        $request->validate([
            'student_id' => 'nullable|exists:students,id',
            'type' => 'nullable|in:new_memorization,review,unmastered,recitation,mafsil,all',
        ]);

        $data = $this->buildRecitationData($request, Auth::id());

        if ($export = $request->export) {
            return $this->exporter->exportRecitation(
                $export, $data['allSessions'], $data['stats'], $data['fromDate'], $data['toDate'], $data['period'], $data['summary']
            );
        }

        return view('reports.recitation', $data);
    }

    public function attendanceReport(Request $request)
    {
        $request->validate([
            'student_id' => 'nullable|exists:students,id',
        ]);

        $data = $this->buildAttendanceData($request, Auth::id());

        if ($export = $request->export) {
            return $this->exporter->exportAttendance(
                $export, $data['allAttendances'], $data['stats'], $data['fromDate'], $data['toDate'], $data['period'], $data['summary']
            );
        }

        return view('reports.attendance', $data);
    }

    public function quarterReport(Request $request)
    {
        return $this->aggregateResponse($request, 'quarter', Auth::id(), true, 'supervisor-');
    }

    public function halfReport(Request $request)
    {
        return $this->aggregateResponse($request, 'half', Auth::id(), true, 'supervisor-');
    }

    public function yearReport(Request $request)
    {
        return $this->aggregateResponse($request, 'year', Auth::id(), true, 'supervisor-');
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

        $sessions = $this->reports->actualRecitationSessions(
            RecitationSession::where('student_id', $student->id)
                ->with(['part', 'pages'])
                ->whereBetween('session_date', [$fromDate, $toDate])
                ->orderBy('session_date', 'desc')
                ->get()
        );

        $attendances = Attendance::where('student_id', $student->id)
            ->whereBetween('date', [$fromDate, $toDate])
            ->orderBy('date', 'desc')
            ->get();

        $sessionStats = $this->reports->recitationStats($sessions);
        $attendanceStats = $this->reports->attendanceStats($attendances);
        $dayStats = $this->reports->studentDayStats($sessions, $attendances, $fromDate, $toDate);

        if ($request->export === 'pdf') {
            return $this->exporter->exportStudentPdf($student, $sessions, $attendances, $sessionStats, $attendanceStats, $fromDate, $toDate, $dayStats);
        }

        return view('supervisor.reports.student', compact(
            'student', 'sessions', 'attendances', 'sessionStats', 'attendanceStats', 'dayStats', 'fromDate', 'toDate'
        ));
    }
}
