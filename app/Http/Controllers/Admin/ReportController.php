<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{Student, Supervisor, Attendance, RecitationSession, User};
use Illuminate\Http\Request;
use Carbon\Carbon;
use TCPDF;

class ReportController extends Controller {
    public function index() {
        $stats = [
            'total_students' => Student::count(),
            'total_sessions' => RecitationSession::count(),
            'passed_sessions' => RecitationSession::where('status','passed')->count(),
            'failed_sessions' => RecitationSession::where('status','failed')->count(),
        ];
        return view('admin.reports.index', compact('stats'));
    }
    public function recitation(Request $request) {
        $period = $request->period ?? 'all';
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
                $fromDate = Carbon::now()->startOfMonth();
                $toDate = Carbon::now()->endOfMonth();
                break;
            case 'custom':
                $fromDate = Carbon::parse($request->from_date ?? Carbon::now()->startOfMonth());
                $toDate = Carbon::parse($request->to_date ?? Carbon::now());
                break;
            default:
                $fromDate = null;
                $toDate = null;
                break;
        }

        $query = RecitationSession::with(['student.user','supervisor','part']);

        // Filter by supervisor
        if ($request->supervisor_id) {
            $query->whereHas('student', fn($q) => $q->where('supervisor_id', $request->supervisor_id));
        }

        // Filter by student
        if ($request->student_id) {
            $query->where('student_id', $request->student_id);
        }

        // Filter by date period
        if ($fromDate && $toDate) {
            $query->whereBetween('session_date', [$fromDate, $toDate]);
        }

        $sessions = $query->latest()->paginate(30);

        // Get data for dropdowns
        $supervisors = Supervisor::with('user')->get();
        $students = Student::with('user')->get();
        if ($request->export === 'pdf') {
            // Get all sessions for PDF (not paginated) - apply same filters
            $pdfQuery = RecitationSession::with(['student.user','supervisor','part']);

            // Apply same filters as main query
            if ($request->supervisor_id) {
                $pdfQuery->whereHas('student', fn($q) => $q->where('supervisor_id', $request->supervisor_id));
            }
            if ($request->student_id) {
                $pdfQuery->where('student_id', $request->student_id);
            }
            if ($fromDate && $toDate) {
                $pdfQuery->whereBetween('session_date', [$fromDate, $toDate]);
            }

            $allSessions = $pdfQuery->orderBy('session_date', 'desc')->get();

            $stats = [
                'total' => $allSessions->count(),
                'passed' => $allSessions->where('status', 'passed')->count(),
                'failed' => $allSessions->whereIn('status', ['failed', 'failed_continued'])->count(),
                'in_progress' => $allSessions->where('status', 'in_progress')->count(),
            ];

            return $this->generateRecitationPdf($allSessions, $stats, $fromDate, $toDate);
        }
        return view('admin.reports.recitation', compact('sessions','supervisors','students','fromDate','toDate'));
    }

    private function generateRecitationPdf($sessions, $stats, $fromDate, $toDate)
    {
        // Create new PDF document with RTL support
        $pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);
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

        // Center info
        $centerName = config('app.center_name', 'مركز التسميع');
        $centerPhone = config('app.center_phone', '');
        $centerEmail = config('app.center_email', '');

        $pdf->SetFontSize(16);
        $pdf->Cell(0, 8, $centerName, 0, true, 'C', 0);
        $pdf->SetFontSize(10);
        $contactInfo = [];
        if ($centerPhone) $contactInfo[] = '📞 ' . $centerPhone;
        if ($centerEmail) $contactInfo[] = '✉️ ' . $centerEmail;
        if (!empty($contactInfo)) {
            $pdf->Cell(0, 6, implode('  |  ', $contactInfo), 0, true, 'C', 0);
        }
        $pdf->Ln(3);

        // Title
        $pdf->SetFontSize(18);
        $pdf->Cell(0, 10, 'تقرير جلسات التسميع', 0, true, 'C', 0);
        $pdf->SetFontSize(12);
        $dateRange = ($fromDate && $toDate)
            ? $fromDate->format('Y/m/d') . ' - ' . $toDate->format('Y/m/d')
            : 'الكل';
        $pdf->Cell(0, 8, 'الفترة: ' . $dateRange, 0, true, 'C', 0);
        $pdf->Ln(5);

        // Stats row
        $statsHtml = '
        <table border="1" cellpadding="5" bgcolor="#f0f0f0">
            <tr>
                <td align="center"><b>إجمالي الجلسات</b><br>' . $stats['total'] . '</td>
                <td align="center" bgcolor="#28a745" color="white"><b>ناجح</b><br>' . $stats['passed'] . '</td>
                <td align="center" bgcolor="#dc3545" color="white"><b>راسب</b><br>' . $stats['failed'] . '</td>
                <td align="center" bgcolor="#ffc107"><b>جارٍ</b><br>' . $stats['in_progress'] . '</td>
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
                <th align="center">المشرف</th>
                <th align="center">الجزء</th>
                <th align="center">الصفحات</th>
                <th align="center">الحالة</th>
                <th align="center">التاريخ</th>
            </tr>';

        $i = 1;
        foreach ($sessions as $session) {
            $partName = $session->part ? ($session->part->part_number ? 'الجزء ' . $session->part->part_number : $session->part->name) : '-';
            $pages = ($session->start_page && $session->end_page) ? ($session->start_page . ' - ' . $session->end_page) : '-';

            // Handle all possible status values
            $statusLabel = match($session->status) {
                'passed' => 'ناجح',
                'failed' => 'راسب',
                'failed_continued' => 'راسب ومكمل',
                'in_progress' => 'جارٍ',
                default => $session->status,
            };
            $statusColor = match($session->status) {
                'passed' => '#28a745',
                'failed', 'failed_continued' => '#dc3545',
                'in_progress' => '#ffc107',
                default => '#6c757d',
            };

            $html .= '
            <tr>
                <td align="center">' . $i . '</td>
                <td>' . ($session->student->user->name ?? '-') . '</td>
                <td>' . ($session->supervisor->name ?? '-') . '</td>
                <td align="center">' . $partName . '</td>
                <td align="center">' . $pages . '</td>
                <td align="center" color="' . $statusColor . '"><b>' . $statusLabel . '</b></td>
                <td align="center">' . ($session->session_date ? $session->session_date->format('Y/m/d') : '-') . '</td>
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
        $filename = $fromDate
            ? 'تقرير-التسميع-' . $fromDate->format('Y-m-d') . '.pdf'
            : 'تقرير-التسميع-الكل.pdf';
        return $pdf->Output($filename, 'D');
    }
    public function attendance(Request $request) {
        $period = $request->period ?? 'all';
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
                $fromDate = Carbon::now()->startOfMonth();
                $toDate = Carbon::now()->endOfMonth();
                break;
            case 'custom':
                $fromDate = Carbon::parse($request->from_date ?? Carbon::now()->startOfMonth());
                $toDate = Carbon::parse($request->to_date ?? Carbon::now());
                break;
            default:
                $fromDate = null;
                $toDate = null;
                break;
        }

        $query = Attendance::with(['student.user','supervisor']);

        // Filter by supervisor
        if ($request->supervisor_id) {
            $query->whereHas('student', fn($q) => $q->where('supervisor_id', $request->supervisor_id));
        }

        // Filter by student
        if ($request->student_id) {
            $query->where('student_id', $request->student_id);
        }

        // Filter by date period
        if ($fromDate && $toDate) {
            $query->whereBetween('date', [$fromDate, $toDate]);
        }

        $attendances = $query->latest('date')->paginate(30);

        // Get data for dropdowns
        $supervisors = Supervisor::with('user')->get();
        $students = Student::with('user')->get();

        if ($request->export === 'pdf') {
            // Get all attendances for PDF (not paginated) - apply same filters
            $pdfQuery = Attendance::with(['student.user','supervisor']);

            // Apply same filters as main query
            if ($request->supervisor_id) {
                $pdfQuery->whereHas('student', fn($q) => $q->where('supervisor_id', $request->supervisor_id));
            }
            if ($request->student_id) {
                $pdfQuery->where('student_id', $request->student_id);
            }
            if ($fromDate && $toDate) {
                $pdfQuery->whereBetween('date', [$fromDate, $toDate]);
            }

            $allAttendances = $pdfQuery->orderBy('date', 'desc')->get();

            $stats = [
                'total' => $allAttendances->count(),
                'present' => $allAttendances->where('status', 'present')->count(),
                'absent' => $allAttendances->where('status', 'absent')->count(),
                'late' => $allAttendances->where('status', 'late')->count(),
                'excused' => $allAttendances->where('status', 'excused')->count(),
            ];

            return $this->generateAttendancePdf($allAttendances, $stats, $fromDate, $toDate);
        }

        return view('admin.reports.attendance', compact('attendances','supervisors','students','fromDate','toDate'));
    }

    private function generateAttendancePdf($attendances, $stats, $fromDate, $toDate)
    {
        $percentage = $stats['total'] > 0 ? round(($stats['present'] / $stats['total']) * 100) : 0;

        // Create new PDF document with RTL support
        $pdf = new TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
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

        // Center info
        $centerName = config('app.center_name', 'مركز التسميع');
        $centerPhone = config('app.center_phone', '');
        $centerEmail = config('app.center_email', '');

        $pdf->SetFontSize(16);
        $pdf->Cell(0, 8, $centerName, 0, true, 'C', 0);
        $pdf->SetFontSize(10);
        $contactInfo = [];
        if ($centerPhone) $contactInfo[] = '📞 ' . $centerPhone;
        if ($centerEmail) $contactInfo[] = '✉️ ' . $centerEmail;
        if (!empty($contactInfo)) {
            $pdf->Cell(0, 6, implode('  |  ', $contactInfo), 0, true, 'C', 0);
        }
        $pdf->Ln(3);

        // Title
        $pdf->SetFontSize(18);
        $pdf->Cell(0, 10, 'تقرير الحضور والغياب', 0, true, 'C', 0);
        $pdf->SetFontSize(12);
        $dateRange = ($fromDate && $toDate)
            ? $fromDate->format('Y/m/d') . ' - ' . $toDate->format('Y/m/d')
            : 'الكل';
        $pdf->Cell(0, 8, 'الفترة: ' . $dateRange, 0, true, 'C', 0);
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
            <tr bgcolor="#182B48" color="white">
                <th align="center">#</th>
                <th align="center">اسم الطالب</th>
                <th align="center">المشرف</th>
                <th align="center">التاريخ</th>
                <th align="center">وقت الحضور</th>
                <th align="center">وقت الانصراف</th>
                <th align="center">الحالة</th>
            </tr>';

        $i = 1;
        foreach ($attendances as $attendance) {
            $statusLabel = match($attendance->status) {
                'present' => 'حاضر',
                'absent' => 'غائب',
                'late' => 'متأخر',
                'excused' => 'معذور',
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
                <td>' . ($attendance->student->user->name ?? '-') . '</td>
                <td>' . ($attendance->supervisor->name ?? '-') . '</td>
                <td align="center">' . ($attendance->date ?? '-') . '</td>
                <td align="center">' . ($attendance->arrival_time ?? '-') . '</td>
                <td align="center">' . ($attendance->departure_time ?? '-') . '</td>
                <td align="center" color="' . $statusColor . '"><b>' . $statusLabel . '</b></td>
            </tr>';
            $i++;
        }

        $html .= '</table>';
        $pdf->writeHTML($html, true, false, false, false, '');

        $pdf->Ln(10);
        $pdf->SetFontSize(10);
        $pdf->Cell(0, 10, 'نظام تسميع القرآن الكريم - تم إنشاء التقرير في ' . now()->format('Y/m/d H:i'), 0, true, 'C', 0);

        $filename = $fromDate
            ? 'تقرير-الحضور-' . $fromDate->format('Y-m-d') . '.pdf'
            : 'تقرير-الحضور-الكل.pdf';
        return $pdf->Output($filename, 'D');
    }
}
