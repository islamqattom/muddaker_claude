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
            ->with(['part', 'pages.page'])
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
        // Get distinct part_ids from mafsil sessions via pages (using join)
        $mafselPartIds = DB::table('recitation_sessions')
            ->join('recitation_pages', 'recitation_sessions.id', '=', 'recitation_pages.session_id')
            ->join('quran_pages', 'recitation_pages.page_id', '=', 'quran_pages.id')
            ->where('recitation_sessions.student_id', $studentId)
            ->where('recitation_sessions.type', 'mafsil')
            ->distinct()
            ->pluck('quran_pages.part_id')
            ->filter()
            ->unique()
            ->values();

        // Get distinct part_ids from recitation sessions via pages (using join)
        $recitationPartIds = DB::table('recitation_sessions')
            ->join('recitation_pages', 'recitation_sessions.id', '=', 'recitation_pages.session_id')
            ->join('quran_pages', 'recitation_pages.page_id', '=', 'quran_pages.id')
            ->where('recitation_sessions.student_id', $studentId)
            ->where('recitation_sessions.type', 'recitation')
            ->distinct()
            ->pluck('quran_pages.part_id')
            ->filter()
            ->unique()
            ->values();

        // Return: mafsil parts + recitation parts (excluding those in mafsil)
        return $mafselPartIds->count() + $recitationPartIds->diff($mafselPartIds)->count();
    }
}
