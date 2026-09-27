<?php
namespace App\Http\Controllers\Student;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\RecitationSession;
use App\Services\ReportExporter;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $memorizedParts = $this->reports->memorizedPartsCountForStudent($student->id);

        $recentSessions = RecitationSession::where('student_id', $student->id)
            ->with(['part', 'pages.mafsilPosition'])
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
            ->with(['student.user', 'part', 'pages.mafsilPosition']);
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
}
