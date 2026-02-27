<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Student, User, Attendance, RecitationSession, Warning};
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_students' => Student::count(),
            'total_supervisors' => User::where('role', 'supervisor')->count(),
            'total_parents' => User::where('role', 'parent')->count(),
            'today_present' => Attendance::where('date', today())->where('status', 'present')->count(),
            'today_absent' => Attendance::where('date', today())->where('status', 'absent')->count(),
            'today_late' => Attendance::where('date', today())->where('status', 'late')->count(),
            'today_sessions' => RecitationSession::whereDate('session_date', today())->count(),
            'this_week_sessions' => RecitationSession::whereBetween('session_date', [
                Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()
            ])->count(),
            'active_warnings' => Warning::whereMonth('issue_date', now()->month)->count(),
            'new_memorization_today' => RecitationSession::whereDate('session_date', today())
                ->where('type', 'new_memorization')->count(),
        ];

        $recentSessions = RecitationSession::with(['student.user', 'supervisor'])
            ->latest()
            ->take(10)
            ->get();

        $recentWarnings = Warning::with(['student.user', 'issuedBy'])
            ->latest()
            ->take(5)
            ->get();

        // Monthly attendance chart data
        $monthlyAttendance = $this->getMonthlyAttendanceData();

        // Weekly sessions chart data
        $weeklySessions = $this->getWeeklySessionsData();

        return view('admin.dashboard.index', compact(
            'stats', 'recentSessions', 'recentWarnings',
            'monthlyAttendance', 'weeklySessions'
        ));
    }

    private function getMonthlyAttendanceData(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $data[] = [
                'date' => $date->format('d/m'),
                'present' => Attendance::where('date', $date->toDateString())->where('status', 'present')->count(),
                'absent' => Attendance::where('date', $date->toDateString())->where('status', 'absent')->count(),
                'late' => Attendance::where('date', $date->toDateString())->where('status', 'late')->count(),
            ];
        }
        return $data;
    }

    private function getWeeklySessionsData(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $data[] = [
                'date' => $date->format('d/m'),
                'sessions' => RecitationSession::whereDate('session_date', $date->toDateString())->count(),
                'passed' => RecitationSession::whereDate('session_date', $date->toDateString())->where('status', 'passed')->count(),
                'failed' => RecitationSession::whereDate('session_date', $date->toDateString())->where('status', 'failed')->count(),
            ];
        }
        return $data;
    }
}
