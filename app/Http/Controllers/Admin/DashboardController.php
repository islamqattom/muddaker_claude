<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Student, User, Attendance, RecitationSession, Warning};
use App\Services\AnalyticsService;
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

        // Supervisor performance
        $analyticsService = app(AnalyticsService::class);
        $supervisorsPerformance = $analyticsService->getAllSupervisorsPerformance();

        return view('admin.dashboard.index', compact(
            'stats', 'recentSessions', 'recentWarnings',
            'monthlyAttendance', 'weeklySessions',
            'supervisorsPerformance'
        ));
    }

    private function getMonthlyAttendanceData(): array
    {
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $rows = Attendance::whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw("date, status, COUNT(*) as count")
            ->groupBy('date', 'status')
            ->get();

        $byDate = [];
        foreach ($rows as $row) {
            if (empty($row->date)) {
                \Log::warning('Empty date in attendance data', ['row' => $row]);
                continue;
            }
            $byDate[Carbon::parse($row->date)->toDateString()][$row->status] = $row->count;
        }

        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateStr = $date->toDateString();
            $data[] = [
                'date' => $date->format('d/m'),
                'present' => $byDate[$dateStr]['present'] ?? 0,
                'absent' => $byDate[$dateStr]['absent'] ?? 0,
                'late' => $byDate[$dateStr]['late'] ?? 0,
            ];
        }
        return $data;
    }

    private function getWeeklySessionsData(): array
    {
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $rows = RecitationSession::whereBetween('session_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw("DATE(session_date) as date, status, COUNT(*) as count")
            ->groupBy('date', 'status')
            ->get();

        $byDate = [];
        foreach ($rows as $row) {
            $byDate[$row->date][$row->status] = $row->count;
        }

        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateStr = $date->toDateString();
            $data[] = [
                'date' => $date->format('d/m'),
                'sessions' => array_sum($byDate[$dateStr] ?? []),
                'passed' => $byDate[$dateStr]['passed'] ?? 0,
                'failed' => $byDate[$dateStr]['failed'] ?? 0,
            ];
        }
        return $data;
    }
}
