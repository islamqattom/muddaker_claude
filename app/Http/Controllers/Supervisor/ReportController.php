<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\{Student, Attendance, RecitationSession};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index()
    {
        $supervisorId = Auth::id();
        $students = Student::where('supervisor_id', $supervisorId)->with('user')->get();
        return view('supervisor.reports.index', compact('students'));
    }

    public function recitationReport(Request $request)
    {
        $request->validate([
            'student_id' => 'nullable|exists:students,id',
            'type' => 'nullable|in:new_memorization,review,unmastered,recitation,mafsil,all',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        $supervisorId = Auth::id();
        $fromDate = Carbon::parse($request->from_date);
        $toDate = Carbon::parse($request->to_date);

        $query = RecitationSession::with(['student.user', 'part', 'pages'])
            ->whereHas('student', fn($q) => $q->where('supervisor_id', $supervisorId))
            ->whereBetween('session_date', [$fromDate, $toDate]);

        if ($request->student_id) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->type && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        $sessions = $query->orderBy('session_date', 'desc')->get();

        $stats = [
            'total' => $sessions->count(),
            'passed' => $sessions->where('status', 'passed')->count(),
            'failed' => $sessions->where('status', 'failed')->count(),
            'total_errors' => $sessions->sum('total_errors'),
            'total_hesitations' => $sessions->sum('total_hesitations'),
            'total_pages' => $sessions->sum('total_pages'),
        ];

        $students = Student::where('supervisor_id', $supervisorId)->with('user')->get();

        if ($request->export === 'pdf') {
            $pdf = Pdf::loadView('supervisor.reports.recitation-pdf', compact('sessions', 'stats', 'fromDate', 'toDate', 'request'))
                ->setPaper('a4', 'landscape');
            return $pdf->download("تقرير-التسميع-{$fromDate->format('Y-m-d')}.pdf");
        }

        return view('supervisor.reports.recitation', compact('sessions', 'stats', 'students', 'fromDate', 'toDate'));
    }

    public function attendanceReport(Request $request)
    {
        $request->validate([
            'student_id' => 'nullable|exists:students,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        $supervisorId = Auth::id();
        $fromDate = Carbon::parse($request->from_date);
        $toDate = Carbon::parse($request->to_date);

        $query = Attendance::with(['student.user'])
            ->where('supervisor_id', $supervisorId)
            ->whereBetween('date', [$fromDate, $toDate]);

        if ($request->student_id) {
            $query->where('student_id', $request->student_id);
        }

        $attendances = $query->orderBy('date', 'desc')->get();

        $stats = [
            'total' => $attendances->count(),
            'present' => $attendances->where('status', 'present')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'excused' => $attendances->where('status', 'excused')->count(),
        ];

        $students = Student::where('supervisor_id', $supervisorId)->with('user')->get();

        if ($request->export === 'pdf') {
            $pdf = Pdf::loadView('supervisor.reports.attendance-pdf', compact('attendances', 'stats', 'fromDate', 'toDate'))
                ->setPaper('a4', 'portrait');
            return $pdf->download("تقرير-الحضور-{$fromDate->format('Y-m-d')}.pdf");
        }

        return view('supervisor.reports.attendance', compact('attendances', 'stats', 'students', 'fromDate', 'toDate'));
    }

    public function studentReport(Request $request, Student $student)
    {
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

        $attendanceStats = [
            'present' => $attendances->where('status', 'present')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'excused' => $attendances->where('status', 'excused')->count(),
            'total' => $attendances->count(),
        ];

        $sessionStats = [
            'total' => $sessions->count(),
            'passed' => $sessions->where('status', 'passed')->count(),
            'failed' => $sessions->where('status', 'failed')->count(),
            'by_type' => $sessions->groupBy('type')->map->count(),
        ];

        if ($request->export === 'pdf') {
            $pdf = Pdf::loadView('supervisor.reports.student-pdf', compact(
                'student', 'sessions', 'attendances', 'attendanceStats', 'sessionStats', 'fromDate', 'toDate'
            ))->setPaper('a4', 'portrait');
            return $pdf->download("تقرير-{$student->user->name}-{$fromDate->format('Y-m-d')}.pdf");
        }

        return view('supervisor.reports.student', compact(
            'student', 'sessions', 'attendances', 'attendanceStats', 'sessionStats', 'fromDate', 'toDate'
        ));
    }
}
