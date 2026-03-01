<?php
namespace App\Http\Controllers\Student;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\RecitationSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller {
    public function index() {
        $student = Auth::user()->student;

        $totalSessions = RecitationSession::where('student_id', $student->id)->count();
        $attendanceRate = $student->attendance_rate;
        $passRate = $student->pass_rate;
        $memorizedParts = $this->getSavedPartsCount($student->id);

        $recentSessions = RecitationSession::where('student_id', $student->id)
            ->with(['part', 'pages'])
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

    private function getSavedPartsCount(int $studentId): int
    {
        // Get distinct part numbers from mafsil sessions (page_number stores part number 1-30)
        $mafselParts = DB::table('recitation_sessions')
            ->join('recitation_pages', 'recitation_sessions.id', '=', 'recitation_pages.session_id')
            ->where('recitation_sessions.student_id', $studentId)
            ->where('recitation_sessions.type', 'mafsil')
            ->distinct()
            ->pluck('recitation_pages.page_number')
            ->filter()
            ->unique()
            ->values();

        // Get distinct part numbers from recitation sessions
        $recitationParts = DB::table('recitation_sessions')
            ->join('recitation_pages', 'recitation_sessions.id', '=', 'recitation_pages.session_id')
            ->where('recitation_sessions.student_id', $studentId)
            ->where('recitation_sessions.type', 'recitation')
            ->distinct()
            ->pluck('recitation_pages.page_number')
            ->filter()
            ->unique()
            ->values();

        // Return: mafsil parts + recitation parts (excluding those in mafsil)
        return $mafselParts->count() + $recitationParts->diff($mafselParts)->count();
    }
}
