<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\{Student, Attendance, RecitationSession, Warning};
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        \App\Support\MissedRecitationSync::run();

        try {
            $supervisorId = Auth::id();

            $students = Student::where('supervisor_id', $supervisorId)->with('user', 'level')->get();

            $todayAttendance = Attendance::where('supervisor_id', $supervisorId)
                ->where('date', today())
                ->get();

            $stats = [
                'total_students' => $students->count(),
                'today_present' => $todayAttendance->where('status', 'present')->count(),
                'today_absent' => $todayAttendance->where('status', 'absent')->count(),
                'today_late' => $todayAttendance->where('status', 'late')->count(),
                'today_sessions' => RecitationSession::whereHas('student', fn($q) => $q->where('supervisor_id', $supervisorId))
                    ->whereDate('session_date', today())->count(),
                'this_week_sessions' => RecitationSession::whereHas('student', fn($q) => $q->where('supervisor_id', $supervisorId))
                    ->whereBetween('session_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count(),
                'attendance_recorded' => $todayAttendance->count() > 0,
            ];

            $recentSessions = RecitationSession::whereHas('student', fn($q) => $q->where('supervisor_id', $supervisorId))
                ->with(['student.user', 'part'])
                ->latest()
                ->take(8)
                ->get();

            $recentWarnings = Warning::where('issued_by', $supervisorId)->latest()->take(5)->get();

            // Present but not recited today (only on required recitation days).
            $today = Carbon::today();
            $isRequiredDay = in_array($today->dayOfWeek, array_map('intval', (array) config('recitation.required_days', [])), true);
            $presentNotRecited = collect();

            if ($isRequiredDay) {
                $presentNotRecited = Attendance::where('supervisor_id', $supervisorId)
                    ->where('date', $today->toDateString())
                    ->whereIn('status', ['present', 'late'])
                    ->whereDoesntHave('student.recitationSessions', fn ($q) => $q->whereDate('session_date', $today->toDateString()))
                    ->with('student.user')
                    ->get();
            }

            return view('supervisor.dashboard.index', compact(
                'stats', 'students', 'recentSessions', 'recentWarnings', 'isRequiredDay', 'presentNotRecited'
            ));
        } catch (\Exception $e) {
            \Log::error('Dashboard load failed: ' . $e->getMessage());
            return view('supervisor.dashboard.index', [
                'stats' => ['total_students' => 0, 'today_present' => 0, 'today_absent' => 0, 'today_late' => 0, 'today_sessions' => 0, 'this_week_sessions' => 0, 'attendance_recorded' => false],
                'students' => collect(),
                'recentSessions' => collect(),
                'recentWarnings' => collect(),
                'isRequiredDay' => false,
                'presentNotRecited' => collect(),
            ])->with('error', 'حدث خطأ أثناء تحميل البيانات');
        }
    }
}
