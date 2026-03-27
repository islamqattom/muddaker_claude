<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\{RecitationSession, Attendance, Warning, Notification};
use App\Services\AnalyticsService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $parentProfile = $user->parentProfile()->with('students.user', 'students.level')->firstOrFail();
        $students = $parentProfile->students;

        $studentsData = [];
        foreach ($students as $student) {
            $thisMonthAttendance = Attendance::where('student_id', $student->id)
                ->whereMonth('date', now()->month)->get();

            $thisMonthSessions = RecitationSession::where('student_id', $student->id)
                ->whereMonth('session_date', now()->month)->get();

            $studentsData[] = [
                'student' => $student,
                'attendance' => [
                    'present' => $thisMonthAttendance->where('status', 'present')->count(),
                    'absent' => $thisMonthAttendance->where('status', 'absent')->count(),
                    'late' => $thisMonthAttendance->where('status', 'late')->count(),
                    'excused' => $thisMonthAttendance->where('status', 'excused')->count(),
                    'total' => $thisMonthAttendance->count(),
                ],
                'sessions' => [
                    'total' => $thisMonthSessions->count(),
                    'passed' => $thisMonthSessions->where('status', 'passed')->count(),
                    'failed' => $thisMonthSessions->where('status', 'failed')->count(),
                ],
                'latest_session' => $thisMonthSessions->sortByDesc('session_date')->first(),
                'recent_warnings' => Warning::where('student_id', $student->id)->latest()->take(3)->get(),
            ];
        }

        // Mark notifications as read
        Notification::where('user_id', $user->id)->where('is_read', false)->update(['is_read' => true]);

        return view('parent.dashboard.index', compact('studentsData', 'parentProfile'));
    }

    public function studentDetail($studentId)
    {
        $user = Auth::user();
        $parentProfile = $user->parentProfile;

        // Verify this parent has access to this student
        $student = $parentProfile->students()->with(['user', 'level', 'supervisor'])->findOrFail($studentId);

        $fromDate = request('from_date') ? Carbon::parse(request('from_date')) : Carbon::now()->startOfMonth();
        $toDate = request('to_date') ? Carbon::parse(request('to_date')) : Carbon::now();

        $sessions = RecitationSession::where('student_id', $student->id)
            ->with('part', 'pages')
            ->whereBetween('session_date', [$fromDate, $toDate])
            ->orderBy('session_date', 'desc')
            ->get();

        $attendances = Attendance::where('student_id', $student->id)
            ->whereBetween('date', [$fromDate, $toDate])
            ->orderBy('date', 'desc')
            ->get();

        $warnings = Warning::where('student_id', $student->id)->latest()->get();
        $warnings->each(fn($w) => $w->update(['is_read_by_parent' => true]));

        $analyticsService = app(AnalyticsService::class);
        $progressData = $analyticsService->getStudentProgressData($student->id);

        return view('parent.dashboard.student-detail', array_merge(
            compact('student', 'sessions', 'attendances', 'warnings', 'fromDate', 'toDate'),
            [
                'velocityData' => $progressData->velocityWeeks,
                'heatmapData' => $progressData->pageDifficulty,
                'recentSessions' => $progressData->recentSessions,
                'completionPercent' => $progressData->completionPercent,
                'levelPagesTotal' => $progressData->currentLevelPagesTotal,
                'levelPagesDone' => $progressData->currentLevelPagesDone,
            ]
        ));
    }
}
