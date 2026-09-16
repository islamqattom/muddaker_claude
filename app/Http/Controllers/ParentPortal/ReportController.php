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

        // Get filter parameters
        $studentId = request('student_id');

        // Handle period filter
        $period = request('period', 'month');
        switch ($period) {
            case 'today':
                $fromDate = Carbon::now()->startOfDay();
                $toDate = Carbon::now()->endOfDay();
                break;
            case 'week':
                $fromDate = Carbon::now()->startOfWeek();
                $toDate = Carbon::now()->endOfWeek();
                break;
            case 'month':
                $fromDate = Carbon::now()->startOfMonth();
                $toDate = Carbon::now()->endOfMonth();
                break;
            case 'custom':
                $fromDate = Carbon::parse(request('from_date', Carbon::now()->startOfMonth()));
                $toDate = Carbon::parse(request('to_date', Carbon::now()));
                break;
            case 'all':
            default:
                $fromDate = null;
                $toDate = null;
                break;
        }

        $studentIds = $students->pluck('id');

        // Filter by student if selected
        $filteredStudentIds = $studentIds;
        if ($studentId && $students->contains('id', $studentId)) {
            $filteredStudentIds = collect([$studentId]);
        }

        // Get sessions statistics
        $sessionsQuery = RecitationSession::whereIn('student_id', $filteredStudentIds);
        if ($fromDate && $toDate) {
            $sessionsQuery->whereBetween('session_date', [$fromDate, $toDate]);
        }
        $sessions = $sessionsQuery->get();

        $totalSessions = $sessions->count();
        $passedSessions = $sessions->where('status', 'passed')->count();
        $failedSessions = $sessions->whereIn('status', ['failed', 'failed_continued', 'incomplete'])->count();

        // Get attendance statistics
        $attendancesQuery = Attendance::whereIn('student_id', $filteredStudentIds);
        if ($fromDate && $toDate) {
            $attendancesQuery->whereBetween('date', [$fromDate, $toDate]);
        }
        $attendances = $attendancesQuery->get();

        $totalAttendances = $attendances->count();
        $presentCount = $attendances->where('status', 'present')->count();
        $absentCount = $attendances->where('status', 'absent')->count();
        $lateCount = $attendances->where('status', 'late')->count();
        $excusedCount = $attendances->where('status', 'excused')->count();
        $holidayCount = $attendances->where('status', 'holiday')->count();
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
                    'failed' => $studentSessions->whereIn('status', ['failed', 'failed_continued', 'incomplete'])->count(),
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
            'parent', 'students', 'studentsData', 'studentId',
            'fromDate', 'toDate',
            'totalSessions', 'passedSessions', 'failedSessions',
            'totalAttendances', 'presentCount', 'absentCount', 'lateCount', 'excusedCount', 'holidayCount', 'attendanceRate'
        ));
    }
}
