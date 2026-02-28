<?php
namespace App\Http\Controllers\ParentPortal;
use App\Http\Controllers\Controller;
use App\Models\{RecitationSession, Attendance};
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReportController extends Controller {
    public function index() {
        $parent = Auth::user()->parentProfile()->with('students.user', 'students.level', 'students.supervisor')->first();
        $students = $parent->students;

        // Date range filtering
        $fromDate = request('from_date') ? Carbon::parse(request('from_date')) : Carbon::now()->startOfMonth();
        $toDate = request('to_date') ? Carbon::parse(request('to_date')) : Carbon::now()->endOfDay();

        $studentIds = $students->pluck('id');

        // Get sessions statistics
        $sessions = RecitationSession::whereIn('student_id', $studentIds)
            ->whereBetween('session_date', [$fromDate, $toDate])
            ->get();

        $totalSessions = $sessions->count();
        $passedSessions = $sessions->where('status', 'passed')->count();
        $failedSessions = $sessions->whereIn('status', ['failed', 'failed_continued'])->count();

        // Get attendance statistics
        $attendances = Attendance::whereIn('student_id', $studentIds)
            ->whereBetween('date', [$fromDate, $toDate])
            ->get();

        $totalAttendances = $attendances->count();
        $presentCount = $attendances->where('status', 'present')->count();
        $absentCount = $attendances->where('status', 'absent')->count();
        $lateCount = $attendances->where('status', 'late')->count();
        $attendanceRate = $totalAttendances > 0 ? round(($presentCount + $lateCount) / $totalAttendances * 100) : 0;

        // Per-student statistics
        $studentsData = [];
        foreach ($students as $student) {
            $studentSessions = $sessions->where('student_id', $student->id);
            $studentAttendances = $attendances->where('student_id', $student->id);

            $studentsData[] = [
                'student' => $student,
                'sessions' => [
                    'total' => $studentSessions->count(),
                    'passed' => $studentSessions->where('status', 'passed')->count(),
                    'failed' => $studentSessions->whereIn('status', ['failed', 'failed_continued'])->count(),
                ],
                'attendance' => [
                    'total' => $studentAttendances->count(),
                    'present' => $studentAttendances->where('status', 'present')->count(),
                    'absent' => $studentAttendances->where('status', 'absent')->count(),
                    'late' => $studentAttendances->where('status', 'late')->count(),
                    'rate' => $studentAttendances->count() > 0
                        ? round(($studentAttendances->whereIn('status', ['present', 'late'])->count()) / $studentAttendances->count() * 100)
                        : 100,
                ],
            ];
        }

        return view('parent.reports.index', compact(
            'parent', 'students', 'studentsData',
            'fromDate', 'toDate',
            'totalSessions', 'passedSessions', 'failedSessions',
            'totalAttendances', 'presentCount', 'absentCount', 'lateCount', 'attendanceRate'
        ));
    }
}
