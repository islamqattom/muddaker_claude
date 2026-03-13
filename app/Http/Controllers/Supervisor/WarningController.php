<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\{Warning, Student, Attendance, RecitationSession, Notification};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use TCPDF;

class WarningController extends Controller
{
    public function index(Request $request)
    {
        $supervisorId = Auth::id();
        $warnings = Warning::where('issued_by', $supervisorId)
            ->with(['student.user'])
            ->when($request->student_id, function($query) use ($request) {
                return $query->where('student_id', $request->student_id);
            })
            ->when($request->type, function($query) use ($request) {
                return $query->where('type', $request->type);
            })
            ->latest()
            ->paginate(20);

        $students = Student::where('supervisor_id', $supervisorId)->with('user')->get();

        return view('supervisor.warnings.index', compact('warnings', 'students'));
    }

    public function create(Request $request)
    {
        $supervisorId = Auth::id();
        $students = Student::where('supervisor_id', $supervisorId)->with('user')->get();

        $student = null;
        $attendanceStats = null;
        $recitationStats = null;

        if ($request->student_id) {
            $student = Student::with('user')->findOrFail($request->student_id);
            $fromDate = $request->from_date ? Carbon::parse($request->from_date) : Carbon::now()->startOfMonth();
            $toDate = $request->to_date ? Carbon::parse($request->to_date) : Carbon::now();

            $attendanceStats = [
                'present' => Attendance::where('student_id', $student->id)->whereBetween('date', [$fromDate, $toDate])->where('status', 'present')->count(),
                'absent' => Attendance::where('student_id', $student->id)->whereBetween('date', [$fromDate, $toDate])->where('status', 'absent')->count(),
                'late' => Attendance::where('student_id', $student->id)->whereBetween('date', [$fromDate, $toDate])->where('status', 'late')->count(),
                'excused' => Attendance::where('student_id', $student->id)->whereBetween('date', [$fromDate, $toDate])->where('status', 'excused')->count(),
                'total' => Attendance::where('student_id', $student->id)->whereBetween('date', [$fromDate, $toDate])->count(),
            ];

            $recitationStats = [
                'total' => RecitationSession::where('student_id', $student->id)->whereBetween('session_date', [$fromDate, $toDate])->count(),
                'passed' => RecitationSession::where('student_id', $student->id)->whereBetween('session_date', [$fromDate, $toDate])->where('status', 'passed')->count(),
                'failed' => RecitationSession::where('student_id', $student->id)->whereBetween('session_date', [$fromDate, $toDate])->where('status', 'failed')->count(),
            ];
        }

        return view('supervisor.warnings.create', compact('students', 'student', 'attendanceStats', 'recitationStats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'type' => 'required|in:attendance,recitation,general',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'issue_date' => 'required|date',
        ]);

        // Verify student belongs to this supervisor
        $student = Student::findOrFail($request->student_id);
        if ($student->supervisor_id !== Auth::id()) {
            abort(403, 'Unauthorized to create warning for this student');
        }

        // Validate JSON data if provided
        $attendanceData = null;
        if ($request->attendance_data) {
            $decoded = json_decode($request->attendance_data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withErrors(['attendance_data' => 'Invalid JSON format in attendance data'])->withInput();
            }
            $attendanceData = $decoded;
        }

        $recitationData = null;
        if ($request->recitation_data) {
            $decoded = json_decode($request->recitation_data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withErrors(['recitation_data' => 'Invalid JSON format in recitation data'])->withInput();
            }
            $recitationData = $decoded;
        }

        $testData = null;
        if ($request->test_data) {
            $decoded = json_decode($request->test_data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withErrors(['test_data' => 'Invalid JSON format in test data'])->withInput();
            }
            $testData = $decoded;
        }

        $warning = Warning::create([
            'student_id' => $request->student_id,
            'issued_by' => Auth::id(),
            'warning_number' => Warning::generateWarningNumber(),
            'type' => $request->type,
            'title' => $request->title,
            'content' => $request->content,
            'issue_date' => $request->issue_date,
            'additional_notes' => $request->additional_notes,
            'attendance_data' => $attendanceData,
            'recitation_data' => $recitationData,
            'test_data' => $testData,
        ]);

        // Notify parents (if notifications are enabled)
        if (config('app.enable_notifications', true)) {
            $student = Student::with('parents.user')->findOrFail($request->student_id);
            foreach ($student->parents as $parent) {
                Notification::create([
                    'user_id' => $parent->user_id,
                    'title' => 'إنذار جديد',
                    'message' => "صدر إنذار جديد بحق ابنكم/ابنتكم {$student->user->name}",
                    'type' => 'warning',
                    'link' => route('parent.warnings.show', $warning->id),
                ]);
            }
        }

        return redirect()->route('supervisor.warnings.show', $warning)
            ->with('success', 'تم إصدار الإنذار بنجاح');
    }

    public function show(Warning $warning)
    {
        $warning->load(['student.user', 'issuedBy']);
        return view('supervisor.warnings.show', compact('warning'));
    }

    public function getAttendance(Request $request, Student $student)
    {
        // Verify student belongs to this supervisor
        if ($student->supervisor_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $fromDate = $request->from_date ?? date('Y-m-01');

        try {
            // Only fetch absent, late, and excused records (not present)
            $attendances = Attendance::where('student_id', $student->id)
                ->where('date', '>=', $fromDate)
                ->whereIn('status', ['absent', 'late', 'excused'])
                ->orderBy('date', 'desc')
                ->get();

            // Format date and time properly
            $attendances = $attendances->map(function($record) {
                return [
                    'id' => $record->id,
                    'day' => $record->day,
                    'date' => $record->date->toDateString(), // Use toDateString to avoid timezone issues
                    'status' => $record->status,
                    'arrival_time' => $record->arrival_time ? $this->convertTo12HourFormat($record->arrival_time) : null,
                    'notes' => $record->notes,
                ];
            });

            return response()->json($attendances);
        } catch (\Exception $e) {
            \Log::error('Failed to fetch attendance: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch attendance data'], 500);
        }
    }

    /**
     * Convert time from 24-hour to 12-hour format
     */
    private function convertTo12HourFormat($time)
    {
        if (!$time) return null;
        $dateTime = \Carbon\Carbon::createFromFormat('H:i:s', $time);
        if ($dateTime) {
            return $dateTime->format('h:i A');
        }
        // Try without seconds
        $dateTime = \Carbon\Carbon::createFromFormat('H:i', $time);
        if ($dateTime) {
            return $dateTime->format('h:i A');
        }
        return $time;
    }

    public function getRecitations(Request $request, Student $student)
    {
        // Verify student belongs to this supervisor
        if ($student->supervisor_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $fromDate = $request->from_date ?? date('Y-m-01');

        try {
            // Fetch failed and not_recitate recitations
            $recitations = RecitationSession::where('student_id', $student->id)
                ->where('session_date', '>=', $fromDate)
                ->whereIn('status', ['failed', 'not_recitate'])
                ->with(['pages' => function($query) {
                    $query->select('id', 'session_id', 'page_number', 'is_passed', 'color_status');
                }])
                ->orderBy('session_date', 'desc')
                ->get();

            // Transform to add computed fields
            $recitations = $recitations->map(function($session) {
                $passedPages = $session->pages->where('is_passed', true)->count();
                $failedPages = $session->pages->where('is_passed', false)->count();

                return [
                    'id' => $session->id,
                    'day' => $session->day,
                    'session_date' => $session->session_date->toDateString(), // Use toDateString to avoid timezone issues
                    'type' => $session->type,
                    'status' => $session->status,
                    'total_pages' => $session->total_pages,
                    'passed_pages' => $passedPages,
                    'failed_pages' => $failedPages,
                    'notes' => $session->notes,
                ];
            });

            return response()->json($recitations);
        } catch (\Exception $e) {
            \Log::error('Failed to fetch recitations: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch recitation data'], 500);
        }
    }

    public function destroy(Warning $warning)
    {
        // Ensure the warning was issued by this supervisor
        if ($warning->issued_by !== Auth::id()) {
            abort(403);
        }

        $warning->delete();

        return redirect()->route('supervisor.warnings.index')
            ->with('success', 'تم حذف الإنذار بنجاح');
    }

    public function print(Warning $warning)
    {
        $warning->load(['student.user', 'issuedBy', 'student.level', 'student.supervisor']);

        // Create new PDF document with RTL support
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->setRTL(true);

        // Set document information
        $pdf->SetCreator('Quran System');
        $pdf->SetAuthor('نظام تسميع القرآن الكريم');
        $pdf->SetTitle('إنذار - ' . $warning->warning_number);

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);

        // Add a page
        $pdf->AddPage();

        // Set font for Arabic
        $pdf->SetFont('aealarabiya', '', 12);

        // Header with Logo
        $logoPath = public_path('images/muddaker-logo-main-nobg.png');
        if (file_exists($logoPath)) {
            $pdf->Image($logoPath, 85, 5, 40, 40, 'PNG', '', '', false, 300, '', false, false, 0);
            $pdf->Ln(42);
        }

        $pdf->SetFontSize(18);
        $pdf->SetTextColor(26, 82, 118);
        $pdf->Cell(0, 6, 'مشروع مدكر القرآني', 0, true, 'C', 0);
        $pdf->SetFontSize(14);
        $pdf->SetTextColor(201, 162, 39);
        $pdf->Cell(0, 6, 'إنذار رسمي', 0, true, 'C', 0);
        $pdf->SetFontSize(9);
        $pdf->SetTextColor(102, 102, 102);
        $pdf->Cell(0, 4, 'تعمل هذه الجمعية على المحافظة على كتاب الله وتعليم حفاظه', 0, true, 'C', 0);
        $pdf->Ln(3);

        // Warning Meta
        $pdf->SetFillColor(248, 249, 250);
        $pdf->SetDrawColor(222, 226, 230);
        $pdf->Rect(10, $pdf->GetY(), 190, 25, 'F');

        $pdf->SetFontSize(10);
        $pdf->SetTextColor(26, 82, 118);
        $pdf->Cell(47.5, 8, 'رقم الإنذار: ' . $warning->warning_number, 0, 0, 'C', 0);
        $pdf->Cell(47.5, 8, 'تاريخ الإنذار: ' . $warning->issue_date->format('d/m/Y'), 0, 0, 'C', 0);
        $pdf->Cell(47.5, 8, 'نوع الإنذار: ' . $warning->type_label, 0, 0, 'C', 0);
        $pdf->Cell(47.5, 8, 'المشرف: ' . $warning->issuedBy->name, 0, 1, 'C', 0);
        $pdf->Ln(5);

        // Student Info Section
        $pdf->SetFontSize(14);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFillColor(26, 82, 118);
        $pdf->Cell(0, 8, 'معلومات الطالب', 0, 1, 'R', 1);
        $pdf->Ln(3);

        $pdf->SetFontSize(11);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(50, 7, 'اسم الطالب:', 0, 0, 'R', 0);
        $pdf->Cell(70, 7, $warning->student->user->name, 0, 0, 'R', 0);
        $pdf->Cell(50, 7, 'رقم الطالب:', 0, 1, 'R', 0);

        $pdf->Cell(50, 7, 'المستوى:', 0, 0, 'R', 0);
        $pdf->Cell(70, 7, $warning->student->level->name ?? '-', 0, 0, 'R', 0);
        $pdf->Cell(50, 7, 'المشرف المسؤول:', 0, 1, 'R', 0);

        if ($warning->from_date && $warning->to_date) {
            $pdf->Cell(0, 7, 'الفترة المعنية: من ' . $warning->from_date->format('d/m/Y') . ' إلى ' . $warning->to_date->format('d/m/Y'), 0, 1, 'R', 0);
        }
        $pdf->Ln(5);

        // Warning Content Section
        $pdf->SetFontSize(14);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFillColor(26, 82, 118);
        $pdf->Cell(0, 8, 'موضوع الإنذار: ' . $warning->title, 0, 1, 'R', 1);
        $pdf->Ln(3);

        $pdf->SetFontSize(12);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(255, 251, 240);
        $pdf->SetDrawColor(240, 230, 210);
        $pdf->Rect(10, $pdf->GetY(), 190, 50, 'F');
        $pdf->MultiCell(0, 7, $warning->content, 0, 'R', 0, 1, '', '', true, 0, true);
        $pdf->Ln(5);

        // Stats Section
        if ($warning->type === 'attendance' && $warning->total_days) {
            $pdf->SetFontSize(14);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFillColor(26, 82, 118);
            $pdf->Cell(0, 8, 'إحصائيات الحضور والغياب', 0, 1, 'R', 1);
            $pdf->Ln(3);

            $pdf->SetTextColor(0, 0, 0);
            $present = $warning->total_days - $warning->absent_days - $warning->late_days;
            $statsHtml = '
            <table border="1" cellpadding="5">
                <tr>
                    <td align="center" bgcolor="#d4edda"><b>أيام الحضور</b><br>' . $present . '</td>
                    <td align="center" bgcolor="#f8d7da"><b>أيام الغياب</b><br>' . $warning->absent_days . '</td>
                    <td align="center" bgcolor="#fff3cd"><b>أيام التأخير</b><br>' . $warning->late_days . '</td>
                    <td align="center" bgcolor="#e2e3e5"><b>الإجمالي</b><br>' . $warning->total_days . '</td>
                </tr>
            </table>';
            $pdf->writeHTML($statsHtml, true, false, false, false, 'R');
            $pdf->Ln(5);
        }

        if ($warning->type === 'recitation' && $warning->total_sessions) {
            $pdf->SetFontSize(14);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFillColor(26, 82, 118);
            $pdf->Cell(0, 8, 'إحصائيات التسميع', 0, 1, 'R', 1);
            $pdf->Ln(3);

            $pdf->SetTextColor(0, 0, 0);
            $passed = $warning->total_sessions - $warning->failed_sessions;
            $failRate = $warning->total_sessions > 0 ? round(($warning->failed_sessions / $warning->total_sessions) * 100) : 0;
            $statsHtml = '
            <table border="1" cellpadding="5">
                <tr>
                    <td align="center" bgcolor="#e2e3e5"><b>إجمالي الجلسات</b><br>' . $warning->total_sessions . '</td>
                    <td align="center" bgcolor="#d4edda"><b>جلسات ناجحة</b><br>' . $passed . '</td>
                    <td align="center" bgcolor="#f8d7da"><b>جلسات راسبة</b><br>' . $warning->failed_sessions . '</td>
                    <td align="center" bgcolor="#f8d7da"><b>نسبة الرسوب</b><br>' . $failRate . '%</td>
                </tr>
            </table>';
            $pdf->writeHTML($statsHtml, true, false, false, false, 'R');
            $pdf->Ln(5);
        }

        // Additional Notes
        if ($warning->additional_notes) {
            $pdf->SetFontSize(14);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFillColor(26, 82, 118);
            $pdf->Cell(0, 8, 'ملاحظات إضافية', 0, 1, 'R', 1);
            $pdf->Ln(3);

            $pdf->SetFontSize(11);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFillColor(248, 249, 250);
            $pdf->Rect(10, $pdf->GetY(), 190, 20, 'F');
            $pdf->MultiCell(0, 6, $warning->additional_notes, 0, 'R', 0, 1, '', '', true, 0, true);
            $pdf->Ln(10);
        }

        // Attendance Data Table
        if ($warning->attendance_data && is_array($warning->attendance_data) && count($warning->attendance_data) > 0) {
            $pdf->SetFontSize(14);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFillColor(26, 82, 118);
            $pdf->Cell(0, 8, 'سجل الحضور', 0, 1, 'R', 1);
            $pdf->Ln(3);

            $pdf->SetFontSize(9);
            $pdf->SetTextColor(0, 0, 0);

            $statusLabels = [
                'present' => 'حاضر',
                'absent' => 'غائب',
                'late' => 'متأخر',
                'excused' => 'غائب بعذر',
                'holiday' => 'عطلة'
            ];

            $attendanceHtml = '<table border="1" cellpadding="4">
                <tr style="background-color: #e8f4fd; font-weight: bold;">
                    <td align="center">اليوم</td>
                    <td align="center">التاريخ</td>
                    <td align="center">الحالة</td>
                    <td align="center">وقت الحضور</td>
                    <td align="center">الملاحظات</td>
                </tr>';

            foreach ($warning->attendance_data as $record) {
                $date = is_string($record['date']) ? explode('T', $record['date'])[0] : $record['date'];
                $status = $statusLabels[$record['status']] ?? $record['status'];
                $arrivalTime = $record['arrival_time'] ?? '-';
                $notes = $record['notes'] ?? '-';

                $attendanceHtml .= '<tr>
                    <td align="center">' . ($record['day'] ?? '-') . '</td>
                    <td align="center">' . $date . '</td>
                    <td align="center">' . $status . '</td>
                    <td align="center">' . $arrivalTime . '</td>
                    <td align="center">' . $notes . '</td>
                </tr>';
            }

            $attendanceHtml .= '</table>';
            $pdf->writeHTML($attendanceHtml, true, false, false, false, 'R');
            $pdf->Ln(5);
        }

        // Recitation Data Table
        if ($warning->recitation_data && is_array($warning->recitation_data) && count($warning->recitation_data) > 0) {
            $pdf->SetFontSize(14);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFillColor(26, 82, 118);
            $pdf->Cell(0, 8, 'سجل التسميع', 0, 1, 'R', 1);
            $pdf->Ln(3);

            $pdf->SetFontSize(9);
            $pdf->SetTextColor(0, 0, 0);

            $typeLabels = [
                'new_memorization' => 'حفظ جديد',
                'review' => 'مراجعة',
                'unmastered' => 'حفظ غير متقن',
                'recitation' => 'سرد',
                'mafsil' => 'مفصل',
                'not_recitate' => 'لم يسمع'
            ];

            $recitationHtml = '<table border="1" cellpadding="4">
                <tr style="background-color: #e8f4fd; font-weight: bold;">
                    <td align="center">اليوم</td>
                    <td align="center">التاريخ</td>
                    <td align="center">نوع التسميع</td>
                    <td align="center">المطلوب تسميعه</td>
                    <td align="center">الصفحات التي نجح بها</td>
                    <td align="center">الصفحات التي رسب بها</td>
                    <td align="center">الملاحظات</td>
                </tr>';

            foreach ($warning->recitation_data as $record) {
                $date = is_string($record['session_date']) ? explode('T', $record['session_date'])[0] : $record['session_date'];
                $type = $typeLabels[$record['type']] ?? $record['type'];
                $totalPages = $record['total_pages'] ?? 0;
                $passedPages = $record['passed_pages'] ?? 0;
                $failedPages = $record['failed_pages'] ?? 0;
                $notes = $record['notes'] ?? '-';

                $recitationHtml .= '<tr>
                    <td align="center">' . ($record['day'] ?? '-') . '</td>
                    <td align="center">' . $date . '</td>
                    <td align="center">' . $type . '</td>
                    <td align="center">' . $totalPages . '</td>
                    <td align="center">' . $passedPages . '</td>
                    <td align="center">' . $failedPages . '</td>
                    <td align="center">' . $notes . '</td>
                </tr>';
            }

            $recitationHtml .= '</table>';
            $pdf->writeHTML($recitationHtml, true, false, false, false, 'R');
            $pdf->Ln(5);
        }

        // Test Data Table
        if ($warning->test_data && is_array($warning->test_data) && count($warning->test_data) > 0) {
            $pdf->SetFontSize(14);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFillColor(26, 82, 118);
            $pdf->Cell(0, 8, 'الاختبارات', 0, 1, 'R', 1);
            $pdf->Ln(3);

            $pdf->SetFontSize(10);
            $pdf->SetTextColor(0, 0, 0);

            $testHtml = '<table border="1" cellpadding="5">
                <tr style="background-color: #e8f4fd; font-weight: bold;">
                    <td align="center" style="width: 50%;">الاختبار</td>
                    <td align="center" style="width: 50%;">ملاحظات</td>
                </tr>';

            foreach ($warning->test_data as $record) {
                $test = $record['test'] ?? '-';
                $notes = $record['notes'] ?? '-';

                $testHtml .= '<tr>
                    <td align="center">' . $test . '</td>
                    <td align="center">' . $notes . '</td>
                </tr>';
            }

            $testHtml .= '</table>';
            $pdf->writeHTML($testHtml, true, false, false, false, 'R');
            $pdf->Ln(5);
        }

        // Signature Section
        $pdf->Ln(15);
        $pdf->SetFontSize(11);
        $pdf->SetTextColor(0, 0, 0);

        $sigWidth = 55;
        $sigX = 10;
        $pdf->Cell($sigWidth, 5, 'توقيع المشرف', 0, 0, 'C', 0);
        $pdf->Cell($sigWidth, 5, '', 0, 0, 'C', 0);
        $pdf->Cell($sigWidth, 5, 'توقيع ولي الأمر', 0, 0, 'C', 0);
        $pdf->Cell($sigWidth, 5, 'توقيع إدارة الجمعية', 0, 1, 'C', 0);

        $pdf->Cell($sigWidth, 20, '', 0, 0, 'C', 0);
        $pdf->Cell($sigWidth, 20, '', 0, 0, 'C', 0);
        $pdf->Cell($sigWidth, 20, '', 0, 1, 'C', 0);

        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
        $pdf->Cell($sigWidth, 5, $warning->issuedBy->name, 0, 0, 'C', 0);
        $pdf->Cell($sigWidth, 5, '', 0, 0, 'C', 0);
        $pdf->Cell($sigWidth, 5, '', 0, 1, 'C', 0);

        // Footer
        $pdf->SetFontSize(9);
        $pdf->SetTextColor(102, 102, 102);
        $pdf->Cell(0, 5, 'مشروع مدكر القرآني | نظام التسميع الإلكتروني | ' . $warning->issue_date->format('Y'), 0, 1, 'C', 0);

        // Output PDF
        return response()->make($pdf->Output('warning.pdf', 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="إنذار-' . $warning->warning_number . '.pdf"',
        ]);
    }
}
