<?php
namespace App\Http\Controllers\Student;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\RecitationSession;
use App\Services\ReportExporter;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller {
    public function __construct(
        private ReportService $reports,
        private ReportExporter $exporter,
    ) {
    }

    public function index(Request $request) {
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

        // التقرير المكتوب للفترة المختارة (بنفس منطق تقارير المشرف/المدير).
        $request->merge(['period' => $request->period ?? 'year']);
        [$fromDate, $toDate, $period] = $this->reports->resolvePeriod($request);

        $sessionsQuery = RecitationSession::where('student_id', $student->id)
            ->with(['student.user', 'part', 'pages']);
        $attendanceQuery = Attendance::where('student_id', $student->id);

        if ($fromDate && $toDate) {
            $sessionsQuery->whereBetween('session_date', [$fromDate->toDateString(), $toDate->toDateString()]);
            $attendanceQuery->whereBetween('date', [$fromDate->toDateString(), $toDate->toDateString()]);
        }

        $breakdown = $this->reports->studentBreakdown(
            $sessionsQuery->get(),
            $attendanceQuery->get(),
            $toDate
        );
        $periodLabel = $this->periodLabel($period);

        if ($request->export === 'pdf') {
            return $this->exporter->exportWrittenReportPdf($student, $breakdown, $periodLabel, $fromDate, $toDate);
        }

        return view('student.reports.index', compact(
            'student', 'totalSessions', 'attendanceRate', 'passRate', 'memorizedParts', 'recentSessions', 'recentAttendances',
            'breakdown', 'periodLabel', 'period', 'fromDate', 'toDate'
        ));
    }

    private function periodLabel(?string $period): string
    {
        return [
            'today'   => 'اليوم',
            'week'    => 'هذا الأسبوع',
            'month'   => 'هذا الشهر',
            'quarter' => 'هذا الربع',
            'half'    => 'هذا النصف',
            'year'    => 'هذه السنة',
            'all'     => 'كل الفترات',
            'custom'  => 'فترة مخصصة',
        ][$period] ?? 'هذه السنة';
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

        // Get distinct part_ids from recitation sessions:
        // 1. From part_id field in recitation_sessions table
        $recitationFromPartId = DB::table('recitation_sessions')
            ->where('student_id', $studentId)
            ->where('type', 'recitation')
            ->whereNotNull('part_id')
            ->distinct()
            ->pluck('part_id')
            ->filter()
            ->unique()
            ->values();

        // 2. From recitation_pages with page_id joined to quran_pages
        $recitationFromPages = DB::table('recitation_sessions')
            ->join('recitation_pages', 'recitation_sessions.id', '=', 'recitation_pages.session_id')
            ->join('quran_pages', 'recitation_pages.page_id', '=', 'quran_pages.id')
            ->where('recitation_sessions.student_id', $studentId)
            ->where('recitation_sessions.type', 'recitation')
            ->distinct()
            ->pluck('quran_pages.part_id')
            ->filter()
            ->unique()
            ->values();

        // Combine both sources for recitation parts
        $recitationParts = $recitationFromPartId->merge($recitationFromPages)->unique()->values();

        // Return: mafsil parts + recitation parts (excluding those in mafsil)
        return $mafselParts->count() + $recitationParts->diff($mafselParts)->count();
    }
}
