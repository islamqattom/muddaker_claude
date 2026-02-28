<?php
namespace App\Http\Controllers\Student;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\RecitationSession;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller {
    public function index() {
        $student = Auth::user()->student;

        $totalSessions = RecitationSession::where('student_id', $student->id)->count();
        $attendanceRate = $student->attendance_rate;
        $passRate = $student->pass_rate;
        $memorizedParts = $student->memorized_parts ?? 0;

        $recentSessions = RecitationSession::where('student_id', $student->id)
            ->with('part')
            ->latest()
            ->limit(5)
            ->get();

        $recentAttendances = Attendance::where('student_id', $student->id)
            ->latest()
            ->limit(5)
            ->get();

        return view('student.reports.index', compact(
            'student', 'totalSessions', 'attendanceRate', 'passRate', 'memorizedParts', 'recentSessions', 'recentAttendances'
        ));
    }
}
