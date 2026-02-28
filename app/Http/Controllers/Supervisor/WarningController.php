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
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
        ]);

        $warning = Warning::create([
            'student_id' => $request->student_id,
            'issued_by' => Auth::id(),
            'warning_number' => Warning::generateWarningNumber(),
            'type' => $request->type,
            'title' => $request->title,
            'content' => $request->content,
            'issue_date' => $request->issue_date,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'absent_days' => $request->absent_days,
            'late_days' => $request->late_days,
            'total_days' => $request->total_days,
            'failed_sessions' => $request->failed_sessions,
            'total_sessions' => $request->total_sessions,
            'additional_notes' => $request->additional_notes,
        ]);

        // Notify parents
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

        return redirect()->route('supervisor.warnings.show', $warning)
            ->with('success', 'تم إصدار الإنذار بنجاح');
    }

    public function show(Warning $warning)
    {
        $warning->load(['student.user', 'issuedBy']);
        return view('supervisor.warnings.show', compact('warning'));
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

        // Header
        $pdf->SetFontSize(20);
        $pdf->SetTextColor(26, 82, 118);
        $pdf->Cell(0, 8, 'جمعية المحافظة على القرآن الكريم', 0, true, 'C', 0);
        $pdf->SetFontSize(16);
        $pdf->SetTextColor(201, 162, 39);
        $pdf->Cell(0, 8, 'إنذار رسمي', 0, true, 'C', 0);
        $pdf->SetFontSize(10);
        $pdf->SetTextColor(102, 102, 102);
        $pdf->Cell(0, 5, 'تعمل هذه الجمعية على المحافظة على كتاب الله وتعليم حفاظه', 0, true, 'C', 0);
        $pdf->Ln(5);

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
        $pdf->Cell(0, 5, 'جمعية المحافظة على القرآن الكريم | نظام التسميع الإلكتروني | ' . $warning->issue_date->format('Y'), 0, 1, 'C', 0);

        // Output PDF
        return response()->make($pdf->Output('warning.pdf', 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="إنذار-' . $warning->warning_number . '.pdf"',
        ]);
    }
}
