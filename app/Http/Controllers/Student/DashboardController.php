<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\{RecitationSession, RecitationPage, Attendance, Warning};
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $student = $user->student()->with(['level', 'supervisor'])->firstOrFail();

        $recentSessions = RecitationSession::where('student_id', $student->id)
            ->with('part')
            ->latest('session_date')
            ->take(10)
            ->get();

        $thisMonthAttendance = Attendance::where('student_id', $student->id)
            ->whereMonth('date', now()->month)
            ->get();

        $attendanceStats = [
            'present' => $thisMonthAttendance->where('status', 'present')->count(),
            'absent' => $thisMonthAttendance->where('status', 'absent')->count(),
            'late' => $thisMonthAttendance->where('status', 'late')->count(),
            'total' => $thisMonthAttendance->count(),
        ];

        $thisMonthSessions = RecitationSession::where('student_id', $student->id)
            ->whereMonth('session_date', now()->month)
            ->get();

        $sessionStats = [
            'total' => $thisMonthSessions->count(),
            'passed' => $thisMonthSessions->where('status', 'passed')->count(),
            'failed' => $thisMonthSessions->where('status', 'failed')->count(),
            'saved_parts' => $this->getSavedPartsCount($student->id),
        ];

        $recentWarnings = Warning::where('student_id', $student->id)->latest()->take(3)->get();

        // Progress chart
        $weeklyProgress = $this->getWeeklyProgress($student->id);

        return view('student.dashboard.index', compact(
            'student', 'recentSessions', 'attendanceStats', 'sessionStats',
            'recentWarnings', 'weeklyProgress'
        ));
    }

    private function getWeeklyProgress(int $studentId): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $sessions = RecitationSession::where('student_id', $studentId)
                ->whereDate('session_date', $date->toDateString())
                ->get();
            $data[] = [
                'date' => $date->format('d/m'),
                'pages' => $sessions->sum('total_pages'),
                'sessions' => $sessions->count(),
            ];
        }
        return $data;
    }

    private function getSavedPartsCount(int $studentId): int
    {
        // Get distinct part_ids from mafsil sessions
        $mafselPartIds = RecitationSession::where('student_id', $studentId)
            ->where('type', 'mafsil')
            ->with('pages.page')
            ->get()
            ->pluck('pages')
            ->flatten()
            ->pluck('page.part_id')
            ->filter()
            ->unique()
            ->values();

        // Get distinct part_ids from recitation sessions
        $recitationPartIds = RecitationSession::where('student_id', $studentId)
            ->where('type', 'recitation')
            ->with('pages.page')
            ->get()
            ->pluck('pages')
            ->flatten()
            ->pluck('page.part_id')
            ->filter()
            ->unique()
            ->values();

        // Return: mafsil parts + recitation parts (excluding those in mafsil)
        return $mafselPartIds->count() + $recitationPartIds->diff($mafselPartIds)->count();
    }

    public function memorization()
    {
        return view('student.dashboard.memorization');
    }
}
