<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\{Student, Attendance, RecitationSession};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use TCPDF;

class ReportController extends Controller
{
    public function index()
    {
        $supervisorId = Auth::id();
        $students = Student::where('supervisor_id', $supervisorId)->with('user')->get();
        return view('supervisor.reports.index', compact('students'));
    }

    public function recitationReport(Request $request)
    {
        $request->validate([
            'student_id' => 'nullable|exists:students,id',
            'type' => 'nullable|in:new_memorization,review,unmastered,recitation,mafsil,all',
        ]);

        // Handle period filter
        $period = $request->period ?? 'month';
        switch ($period) {
            case 'today':
                $fromDate = Carbon::now()->startOfDay();
                $toDate = Carbon::now()->endOfDay();
                break;
            case 'week':
                $fromDate = Carbon::now()->startOfWeek();
                $toDate = Carbon::now()->endOfWeek();
                break;
            case 'month':
            default:
                $fromDate = Carbon::now()->startOfMonth();
                $toDate = Carbon::now()->endOfMonth();
                break;
            case 'custom':
                $fromDate = Carbon::parse($request->from_date ?? Carbon::now()->startOfMonth());
                $toDate = Carbon::parse($request->to_date ?? Carbon::now());
                break;
        }

        $supervisorId = Auth::id();

        $query = RecitationSession::with(['student.user', 'part', 'pages'])
            ->whereHas('student', fn($q) => $q->where('supervisor_id', $supervisorId))
            ->whereBetween('session_date', [$fromDate, $toDate]);

        if ($request->student_id) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->type && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        $sessions = $query->orderBy('session_date', 'desc')->get();

        $stats = [
            'total' => $sessions->count(),
            'passed' => $sessions->where('status', 'passed')->count(),
            'failed' => $sessions->where('status', 'failed')->count(),
            'total_errors' => $sessions->sum('total_errors'),
            'total_hesitations' => $sessions->sum('total_hesitations'),
            'total_pages' => $sessions->sum('total_pages'),
        ];

        $students = Student::where('supervisor_id', $supervisorId)->with('user')->get();

        if ($request->export === 'pdf') {
            return $this->generateRecitationPdf($sessions, $stats, $fromDate, $toDate);
        }

        return view('supervisor.reports.recitation', compact('sessions', 'stats', 'students', 'fromDate', 'toDate'));
    }

    private function generateRecitationPdf($sessions, $stats, $fromDate, $toDate)
    {
        // Create new PDF document with RTL support
        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->setRTL(true);

        // Set document information
        $pdf->SetCreator('Quran System');
        $pdf->SetAuthor('نظام تسميع القرآن الكريم');
        $pdf->SetTitle('تقرير التسميع');

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);

        // Add a page
        $pdf->AddPage();

        // Set font for Arabic
        $pdf->SetFont('aealarabiya', '', 12);

        // Title
        $pdf->SetFontSize(18);
        $pdf->Cell(0, 10, 'تقرير التسميع', 0, true, 'C', 0);
        $pdf->SetFontSize(12);
        $pdf->Cell(0, 8, 'الفترة: ' . $fromDate->format('Y/m/d') . ' - ' . $toDate->format('Y/m/d'), 0, true, 'C', 0);
        $pdf->Ln(5);

        // Stats row
        $statsHtml = '
        <table border="1" cellpadding="5" bgcolor="#f0f0f0">
            <tr>
                <td align="center"><b>إجمالي الجلسات</b><br>' . $stats['total'] . '</td>
                <td align="center" bgcolor="#28a745" color="white"><b>ناجح</b><br>' . $stats['passed'] . '</td>
                <td align="center" bgcolor="#dc3545" color="white"><b>راسب</b><br>' . $stats['failed'] . '</td>
                <td align="center" bgcolor="#ffc107"><b>إجمالي الأخطاء</b><br>' . $stats['total_errors'] . '</td>
                <td align="center" bgcolor="#17a2b8" color="white"><b>إجمالي الترددات</b><br>' . $stats['total_hesitations'] . '</td>
                <td align="center" bgcolor="#6c757d" color="white"><b>إجمالي الصفحات</b><br>' . $stats['total_pages'] . '</td>
            </tr>
        </table>';
        $pdf->writeHTML($statsHtml, true, false, false, false, 'C');
        $pdf->Ln(10);

        // Table header
        $html = '
        <table border="1" cellpadding="4">
            <tr bgcolor="#182B48" color="white">
                <th align="center">#</th>
                <th align="center">الطالب</th>
                <th align="center">النوع</th>
                <th align="center">الجزء</th>
                <th align="center">الصفحات</th>
                <th align="center">الأخطاء</th>
                <th align="center">الترددات</th>
                <th align="center">العلامة</th>
                <th align="center">الحالة</th>
                <th align="center">التاريخ</th>
            </tr>';

        $i = 1;
        foreach ($sessions as $session) {
            $part = $session->part ? 'الجزء ' . $session->part->part_number : ($session->mafsil_type ? 'مفصل (' . $session->mafsil_type . ')' : '-');
            $score = $session->final_score !== null ? number_format($session->final_score, 1) . '%' : '-';
            $statusColor = $session->status === 'passed' ? '#28a745' : '#dc3545';

            $html .= '
            <tr>
                <td align="center">' . $i . '</td>
                <td>' . $session->student->user->name . '</td>
                <td align="center">' . $session->type_label . '</td>
                <td align="center">' . $part . '</td>
                <td align="center">' . ($session->total_pages ?? '-') . '</td>
                <td align="center">' . ($session->total_errors ?? 0) . '</td>
                <td align="center">' . ($session->total_hesitations ?? 0) . '</td>
                <td align="center">' . $score . '</td>
                <td align="center" color="' . $statusColor . '"><b>' . $session->status_label . '</b></td>
                <td align="center">' . $session->session_date->format('Y/m/d') . '</td>
            </tr>';
            $i++;
        }

        $html .= '</table>';
        $pdf->writeHTML($html, true, false, false, false, '');

        // Footer
        $pdf->Ln(10);
        $pdf->SetFontSize(10);
        $pdf->Cell(0, 10, 'نظام تسميع القرآن الكريم - تم إنشاء التقرير في ' . now()->format('Y/m/d H:i'), 0, true, 'C', 0);

        // Output
        return $pdf->Output('تقرير-التسميع-' . $fromDate->format('Y-m-d') . '.pdf', 'D');
    }

    public function attendanceReport(Request $request)
    {
        $request->validate([
            'student_id' => 'nullable|exists:students,id',
        ]);

        // Handle period filter
        $period = $request->period ?? 'month';
        switch ($period) {
            case 'today':
                $fromDate = Carbon::now()->startOfDay();
                $toDate = Carbon::now()->endOfDay();
                break;
            case 'week':
                $fromDate = Carbon::now()->startOfWeek();
                $toDate = Carbon::now()->endOfWeek();
                break;
            case 'month':
            default:
                $fromDate = Carbon::now()->startOfMonth();
                $toDate = Carbon::now()->endOfMonth();
                break;
            case 'custom':
                $fromDate = Carbon::parse($request->from_date ?? Carbon::now()->startOfMonth());
                $toDate = Carbon::parse($request->to_date ?? Carbon::now());
                break;
        }

        $supervisorId = Auth::id();

        $query = Attendance::with(['student.user'])
            ->where('supervisor_id', $supervisorId)
            ->whereBetween('date', [$fromDate, $toDate]);

        if ($request->student_id) {
            $query->where('student_id', $request->student_id);
        }

        $attendances = $query->orderBy('date', 'desc')->get();

        $stats = [
            'total' => $attendances->count(),
            'present' => $attendances->where('status', 'present')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'excused' => $attendances->where('status', 'excused')->count(),
        ];

        $students = Student::where('supervisor_id', $supervisorId)->with('user')->get();

        if ($request->export === 'pdf') {
            return $this->generateAttendancePdf($attendances, $stats, $fromDate, $toDate);
        }

        return view('supervisor.reports.attendance', compact('attendances', 'stats', 'students', 'fromDate', 'toDate'));
    }

    private function generateAttendancePdf($attendances, $stats, $fromDate, $toDate)
    {
        $percentage = $stats['total'] > 0 ? round(($stats['present'] / $stats['total']) * 100) : 0;

        // Create new PDF document with RTL support
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->setRTL(true);

        $pdf->SetCreator('Quran System');
        $pdf->SetAuthor('نظام تسميع القرآن الكريم');
        $pdf->SetTitle('تقرير الحضور والغياب');

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->AddPage();

        $pdf->SetFont('aealarabiya', '', 12);

        // Title
        $pdf->SetFontSize(18);
        $pdf->Cell(0, 10, 'تقرير الحضور والغياب', 0, true, 'C', 0);
        $pdf->SetFontSize(12);
        $pdf->Cell(0, 8, 'الفترة: ' . $fromDate->format('Y/m/d') . ' - ' . $toDate->format('Y/m/d'), 0, true, 'C', 0);
        $pdf->Ln(5);

        // Stats
        $statsHtml = '
        <table border="1" cellpadding="5" bgcolor="#f0f0f0">
            <tr>
                <td align="center"><b>إجمالي السجلات</b><br>' . $stats['total'] . '</td>
                <td align="center" bgcolor="#28a745" color="white"><b>حاضر</b><br>' . $stats['present'] . '</td>
                <td align="center" bgcolor="#dc3545" color="white"><b>غائب</b><br>' . $stats['absent'] . '</td>
                <td align="center" bgcolor="#ffc107"><b>متأخر</b><br>' . $stats['late'] . '</td>
                <td align="center" bgcolor="#17a2b8" color="white"><b>بعذر</b><br>' . $stats['excused'] . '</td>
                <td align="center" bgcolor="#6c757d" color="white"><b>نسبة الحضور</b><br>' . $percentage . '%</td>
            </tr>
        </table>';
        $pdf->writeHTML($statsHtml, true, false, false, false, 'C');
        $pdf->Ln(10);

        // Table
        $html = '
        <table border="1" cellpadding="4">
            <tr bgcolor="#28a745" color="white">
                <th align="center">#</th>
                <th align="center">الطالب</th>
                <th align="center">الحالة</th>
                <th align="center">وقت الحضور</th>
                <th align="center">ملاحظة</th>
                <th align="center">التاريخ</th>
            </tr>';

        $i = 1;
        foreach ($attendances as $attendance) {
            $statusLabel = match($attendance->status) {
                'present' => 'حاضر',
                'absent' => 'غائب',
                'late' => 'متأخر',
                'excused' => 'بعذر',
                default => 'غير محدد'
            };
            $statusColor = match($attendance->status) {
                'present' => '#28a745',
                'absent' => '#dc3545',
                'late' => '#ffc107',
                'excused' => '#17a2b8',
                default => '#6c757d'
            };

            $html .= '
            <tr>
                <td align="center">' . $i . '</td>
                <td>' . $attendance->student->user->name . '</td>
                <td align="center" color="' . $statusColor . '"><b>' . $statusLabel . '</b></td>
                <td align="center">' . ($attendance->arrival_time ?? '-') . '</td>
                <td align="center">' . ($attendance->notes ?? '-') . '</td>
                <td align="center">' . $attendance->date->format('Y/m/d') . '</td>
            </tr>';
            $i++;
        }

        $html .= '</table>';
        $pdf->writeHTML($html, true, false, false, false, '');

        $pdf->Ln(10);
        $pdf->SetFontSize(10);
        $pdf->Cell(0, 10, 'نظام تسميع القرآن الكريم - تم إنشاء التقرير في ' . now()->format('Y/m/d H:i'), 0, true, 'C', 0);

        return $pdf->Output('تقرير-الحضور-' . $fromDate->format('Y-m-d') . '.pdf', 'D');
    }

    public function studentReport(Request $request, Student $student)
    {
        $fromDate = $request->from_date ? Carbon::parse($request->from_date) : Carbon::now()->startOfMonth();
        $toDate = $request->to_date ? Carbon::parse($request->to_date) : Carbon::now();

        $student->load(['user', 'level', 'supervisor']);

        $sessions = RecitationSession::where('student_id', $student->id)
            ->with(['part', 'pages'])
            ->whereBetween('session_date', [$fromDate, $toDate])
            ->orderBy('session_date', 'desc')
            ->get();

        $attendances = Attendance::where('student_id', $student->id)
            ->whereBetween('date', [$fromDate, $toDate])
            ->orderBy('date', 'desc')
            ->get();

        $attendanceStats = [
            'present' => $attendances->where('status', 'present')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'excused' => $attendances->where('status', 'excused')->count(),
            'total' => $attendances->count(),
        ];

        $sessionStats = [
            'total' => $sessions->count(),
            'passed' => $sessions->where('status', 'passed')->count(),
            'failed' => $sessions->where('status', 'failed')->count(),
            'by_type' => $sessions->groupBy('type')->map->count(),
        ];

        if ($request->export === 'pdf') {
            return $this->generateStudentPdf($student, $sessions, $attendances, $attendanceStats, $sessionStats, $fromDate, $toDate);
        }

        return view('supervisor.reports.student', compact(
            'student', 'sessions', 'attendances', 'attendanceStats', 'sessionStats', 'fromDate', 'toDate'
        ));
    }

    private function generateStudentPdf($student, $sessions, $attendances, $attendanceStats, $sessionStats, $fromDate, $toDate)
    {
        $attendancePercentage = $attendanceStats['total'] > 0 ? round(($attendanceStats['present'] / $attendanceStats['total']) * 100) : 0;
        $passRate = $sessionStats['total'] > 0 ? round(($sessionStats['passed'] / $sessionStats['total']) * 100) : 0;

        // Create new PDF document with RTL support
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->setRTL(true);

        $pdf->SetCreator('Quran System');
        $pdf->SetAuthor('نظام تسميع القرآن الكريم');
        $pdf->SetTitle('تقرير ' . $student->user->name);

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->AddPage();

        $pdf->SetFont('aealarabiya', '', 12);

        // Title
        $pdf->SetFontSize(18);
        $pdf->Cell(0, 10, 'تقرير طالب', 0, true, 'C', 0);
        $pdf->SetFontSize(12);
        $pdf->Cell(0, 8, 'الفترة: ' . $fromDate->format('Y/m/d') . ' - ' . $toDate->format('Y/m/d'), 0, true, 'C', 0);
        $pdf->Ln(5);

        // Student Info
        $studentInfo = '
        <table border="1" cellpadding="5" bgcolor="#f0f0f0">
            <tr>
                <td><b>الاسم:</b> ' . $student->user->name . '</td>
                <td><b>المستوى:</b> ' . ($student->level->name ?? '-') . '</td>
            </tr>
            <tr>
                <td><b>رقم الطالب:</b> ' . $student->student_number . '</td>
                <td><b>المشرف:</b> ' . ($student->supervisor->user->name ?? '-') . '</td>
            </tr>
        </table>';
        $pdf->writeHTML($studentInfo, true, false, false, false, '');
        $pdf->Ln(5);

        // Attendance Stats
        $pdf->SetFontSize(14);
        $pdf->Cell(0, 10, 'إحصائيات الحضور', 0, true, 'R', 0);
        $attendanceStatsHtml = '
        <table border="1" cellpadding="5">
            <tr>
                <td align="center" bgcolor="#28a745" color="white"><b>حاضر</b><br>' . $attendanceStats['present'] . '</td>
                <td align="center" bgcolor="#dc3545" color="white"><b>غائب</b><br>' . $attendanceStats['absent'] . '</td>
                <td align="center" bgcolor="#ffc107"><b>متأخر</b><br>' . $attendanceStats['late'] . '</td>
                <td align="center" bgcolor="#17a2b8" color="white"><b>بعذر</b><br>' . $attendanceStats['excused'] . '</td>
                <td align="center" bgcolor="#6c757d" color="white"><b>نسبة الحضور</b><br>' . $attendancePercentage . '%</td>
            </tr>
        </table>';
        $pdf->writeHTML($attendanceStatsHtml, true, false, false, false, 'R');
        $pdf->Ln(5);

        // Recitation Stats
        $pdf->SetFontSize(14);
        $pdf->Cell(0, 10, 'إحصائيات التسميع', 0, true, 'R', 0);
        $sessionStatsHtml = '
        <table border="1" cellpadding="5">
            <tr>
                <td align="center"><b>إجمالي الجلسات</b><br>' . $sessionStats['total'] . '</td>
                <td align="center" bgcolor="#28a745" color="white"><b>ناجح</b><br>' . $sessionStats['passed'] . '</td>
                <td align="center" bgcolor="#dc3545" color="white"><b>راسب</b><br>' . $sessionStats['failed'] . '</td>
                <td align="center" bgcolor="#ffc107"><b>نسبة النجاح</b><br>' . $passRate . '%</td>
            </tr>
        </table>';
        $pdf->writeHTML($sessionStatsHtml, true, false, false, false, 'R');
        $pdf->Ln(5);

        // Sessions Table
        if ($sessions->isNotEmpty()) {
            $pdf->SetFontSize(14);
            $pdf->Cell(0, 10, 'جلسات التسميع', 0, true, 'R', 0);
            $html = '
            <table border="1" cellpadding="4">
                <tr bgcolor="#182B48" color="white">
                    <th align="center">النوع</th>
                    <th align="center">الجزء</th>
                    <th align="center">الصفحات</th>
                    <th align="center">الأخطاء</th>
                    <th align="center">الترددات</th>
                    <th align="center">العلامة</th>
                    <th align="center">الحالة</th>
                    <th align="center">التاريخ</th>
                </tr>';

            foreach ($sessions as $session) {
                $part = $session->part ? 'الجزء ' . $session->part->part_number : ($session->mafsil_type ? 'مفصل' : '-');
                $score = $session->final_score !== null ? number_format($session->final_score, 1) . '%' : '-';
                $statusColor = $session->status === 'passed' ? '#28a745' : '#dc3545';

                $html .= '
                <tr>
                    <td align="center">' . $session->type_label . '</td>
                    <td align="center">' . $part . '</td>
                    <td align="center">' . ($session->total_pages ?? '-') . '</td>
                    <td align="center">' . ($session->total_errors ?? 0) . '</td>
                    <td align="center">' . ($session->total_hesitations ?? 0) . '</td>
                    <td align="center">' . $score . '</td>
                    <td align="center" color="' . $statusColor . '"><b>' . $session->status_label . '</b></td>
                    <td align="center">' . $session->session_date->format('Y/m/d') . '</td>
                </tr>';
            }
            $html .= '</table>';
            $pdf->writeHTML($html, true, false, false, false, '');
            $pdf->Ln(5);
        }

        // Attendance Records
        if ($attendances->isNotEmpty()) {
            $pdf->SetFontSize(14);
            $pdf->Cell(0, 10, 'سجلات الحضور', 0, true, 'R', 0);
            $html = '
            <table border="1" cellpadding="4">
                <tr bgcolor="#28a745" color="white">
                    <th align="center">الحالة</th>
                    <th align="center">وقت الحضور</th>
                    <th align="center">ملاحظة</th>
                    <th align="center">التاريخ</th>
                </tr>';

            foreach ($attendances as $attendance) {
                $statusLabel = match($attendance->status) {
                    'present' => 'حاضر',
                    'absent' => 'غائب',
                    'late' => 'متأخر',
                    'excused' => 'بعذر',
                    default => 'غير محدد'
                };
                $statusColor = match($attendance->status) {
                    'present' => '#28a745',
                    'absent' => '#dc3545',
                    'late' => '#ffc107',
                    'excused' => '#17a2b8',
                    default => '#6c757d'
                };

                $html .= '
                <tr>
                    <td align="center" color="' . $statusColor . '"><b>' . $statusLabel . '</b></td>
                    <td align="center">' . ($attendance->arrival_time ?? '-') . '</td>
                    <td align="center">' . ($attendance->notes ?? '-') . '</td>
                    <td align="center">' . $attendance->date->format('Y/m/d') . '</td>
                </tr>';
            }
            $html .= '</table>';
            $pdf->writeHTML($html, true, false, false, false, '');
        }

        $pdf->Ln(10);
        $pdf->SetFontSize(10);
        $pdf->Cell(0, 10, 'نظام تسميع القرآن الكريم - تم إنشاء التقرير في ' . now()->format('Y/m/d H:i'), 0, true, 'C', 0);

        return $pdf->Output('تقرير-' . $student->user->name . '-' . $fromDate->format('Y-m-d') . '.pdf', 'D');
    }
}
