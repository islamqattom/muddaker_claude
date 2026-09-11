<?php

namespace App\Services;

use App\Exports\AttendanceReportExport;
use App\Exports\RecitationReportExport;
use Maatwebsite\Excel\Facades\Excel;
use TCPDF;

class ReportExporter
{
    public function exportRecitation(string $format, $sessions, array $stats, $fromDate, $toDate, string $period)
    {
        $headers = ['#', 'الطالب', 'المشرف', 'الجزء', 'الصفحات', 'الدرجة', 'الحالة', 'التاريخ'];
        $rows = $sessions->values()->map(function ($s, $i) {
            return [
                $i + 1,
                $s->student->user->name ?? '—',
                $s->supervisor->name ?? '—',
                $s->parts_label,
                ($s->start_page ?? '—') . ' - ' . ($s->end_page ?? '—'),
                $s->final_score !== null ? number_format($s->final_score, 1) . '%' : '—',
                $s->status_label,
                $s->session_date?->format('Y/m/d') ?? '—',
            ];
        })->toArray();

        $subtitle = $this->dateRangeLabel($fromDate, $toDate, $period);
        $statsForExport = [
            'الإجمالي' => $stats['total'],
            'ناجح' => $stats['passed'],
            'راسب' => $stats['failed'],
            'نسبة النجاح' => $stats['pass_rate'] . '%',
        ];

        $filename = 'recitation-report-' . now()->format('Ymd-His');

        return match ($format) {
            'excel', 'xlsx' => Excel::download(
                new RecitationReportExport('تقرير التسميع', $headers, $rows, $statsForExport, $subtitle),
                $filename . '.xlsx'
            ),
            'print' => $this->printView('تقرير التسميع', $headers, $rows, $statsForExport, $subtitle),
            default => $this->recitationPdf($sessions, $stats, $fromDate, $toDate),
        };
    }

    public function exportAttendance(string $format, $attendances, array $stats, $fromDate, $toDate, string $period)
    {
        $headers = ['#', 'الطالب', 'المشرف', 'التاريخ', 'وقت الحضور', 'وقت الانصراف', 'الحالة'];
        $rows = $attendances->values()->map(function ($a, $i) {
            $statusLabel = $this->attendanceStatusLabel($a->status);
            return [
                $i + 1,
                $a->student->user->name ?? '—',
                $a->supervisor->name ?? '—',
                $a->date?->format('Y/m/d') ?? '—',
                $a->arrival_time ?? '—',
                $a->departure_time ?? '—',
                $statusLabel,
            ];
        })->toArray();

        $subtitle = $this->dateRangeLabel($fromDate, $toDate, $period);
        $statsForExport = [
            'الإجمالي' => $stats['total'],
            'حاضر' => $stats['present'],
            'غائب' => $stats['absent'],
            'متأخر' => $stats['late'],
            'بعذر' => $stats['excused'],
            'نسبة الحضور' => $stats['attendance_rate'] . '%',
        ];

        $filename = 'attendance-report-' . now()->format('Ymd-His');

        return match ($format) {
            'excel', 'xlsx' => Excel::download(
                new AttendanceReportExport('تقرير الحضور والغياب', $headers, $rows, $statsForExport, $subtitle),
                $filename . '.xlsx'
            ),
            'print' => $this->printView('تقرير الحضور والغياب', $headers, $rows, $statsForExport, $subtitle),
            default => $this->attendancePdf($attendances, $stats, $fromDate, $toDate),
        };
    }

    public function exportRecitationPdf($sessions, $stats, $fromDate, $toDate)
    {
        return $this->recitationPdf($sessions, $stats, $fromDate, $toDate);
    }

    public function exportAttendancePdf($attendances, $stats, $fromDate, $toDate)
    {
        return $this->attendancePdf($attendances, $stats, $fromDate, $toDate);
    }

    public function exportAggregate(string $format, array $payload, array $meta)
    {
        $headers = ['القسم', '#', 'الطالب', 'المشرف', 'التفاصيل', 'الدرجة/الحالة', 'التاريخ'];
        $rows = [];

        $i = 1;
        foreach ($payload['allSessions'] as $s) {
            $rows[] = [
                'تسميع', $i++,
                $s->student->user->name ?? '—',
                $s->supervisor->name ?? '—',
                $s->parts_label,
                ($s->final_score !== null ? number_format($s->final_score, 1) . '%' : '—') . ' / ' . $s->status_label,
                $s->session_date?->format('Y/m/d') ?? '—',
            ];
        }
        $i = 1;
        foreach ($payload['allAttendances'] as $a) {
            $rows[] = [
                'حضور', $i++,
                $a->student->user->name ?? '—',
                $a->supervisor->name ?? '—',
                'وقت الحضور: ' . ($a->arrival_time ?? '—'),
                $this->attendanceStatusLabel($a->status),
                $a->date?->format('Y/m/d') ?? '—',
            ];
        }

        $statsForExport = [
            'جلسات (إجمالي)' => $payload['recStats']['total'],
            'ناجح'           => $payload['recStats']['passed'],
            'راسب'           => $payload['recStats']['failed'],
            'نسبة النجاح'     => $payload['recStats']['pass_rate'] . '%',
            'متوسط الدرجة'    => $payload['recStats']['avg_score'] . '%',
            'حضور (إجمالي)'   => $payload['attStats']['total'],
            'حاضر'           => $payload['attStats']['present'],
            'غائب'           => $payload['attStats']['absent'],
            'نسبة الحضور'     => $payload['attStats']['attendance_rate'] . '%',
        ];

        $filename = $meta['filename'] . '-' . now()->format('Ymd-His');

        return match ($format) {
            'excel', 'xlsx' => Excel::download(
                new RecitationReportExport($meta['title'], $headers, $rows, $statsForExport, $meta['subtitle']),
                $filename . '.xlsx'
            ),
            'print' => $this->printView($meta['title'], $headers, $rows, $statsForExport, $meta['subtitle']),
            default => $this->aggregatePdf($payload, $meta),
        };
    }

    public function exportStudentPdf($student, $sessions, $attendances, $sessionStats, $attendanceStats, $fromDate, $toDate)
    {
        $attendancePct = $attendanceStats['attendance_rate'];
        $passRate = $sessionStats['pass_rate'];

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->setRTL(true);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->AddPage();
        $pdf->SetFont('aealarabiya', '', 12);

        $pdf->SetFontSize(18);
        $pdf->Cell(0, 10, 'تقرير طالب', 0, true, 'C', 0);
        $pdf->SetFontSize(12);
        $pdf->Cell(0, 8, 'الفترة: ' . $fromDate->format('Y/m/d') . ' - ' . $toDate->format('Y/m/d'), 0, true, 'C', 0);
        $pdf->Ln(5);

        $info = '<table border="1" cellpadding="5"><tr>
            <td><b>الاسم:</b> ' . $student->user->name . '</td>
            <td><b>المستوى:</b> ' . ($student->level->name ?? '-') . '</td>
        </tr><tr>
            <td><b>رقم الطالب:</b> ' . $student->student_number . '</td>
            <td><b>المشرف:</b> ' . ($student->supervisor->name ?? '-') . '</td>
        </tr></table>';
        $pdf->writeHTML($info, true, false, false, false, '');
        $pdf->Ln(5);

        $pdf->SetFontSize(14);
        $pdf->Cell(0, 10, 'إحصائيات الحضور', 0, true, 'R', 0);
        $pdf->writeHTML('<table border="1" cellpadding="5"><tr>
            <td align="center" bgcolor="#28a745" color="white"><b>حاضر</b><br>' . $attendanceStats['present'] . '</td>
            <td align="center" bgcolor="#dc3545" color="white"><b>غائب</b><br>' . $attendanceStats['absent'] . '</td>
            <td align="center" bgcolor="#ffc107"><b>متأخر</b><br>' . $attendanceStats['late'] . '</td>
            <td align="center" bgcolor="#17a2b8" color="white"><b>بعذر</b><br>' . $attendanceStats['excused'] . '</td>
            <td align="center" bgcolor="#6c757d" color="white"><b>نسبة الحضور</b><br>' . $attendancePct . '%</td>
        </tr></table>', true, false, false, false, 'R');
        $pdf->Ln(5);

        $pdf->SetFontSize(14);
        $pdf->Cell(0, 10, 'إحصائيات التسميع', 0, true, 'R', 0);
        $pdf->writeHTML('<table border="1" cellpadding="5"><tr>
            <td align="center"><b>الإجمالي</b><br>' . $sessionStats['total'] . '</td>
            <td align="center" bgcolor="#28a745" color="white"><b>ناجح</b><br>' . $sessionStats['passed'] . '</td>
            <td align="center" bgcolor="#dc3545" color="white"><b>راسب</b><br>' . $sessionStats['failed'] . '</td>
            <td align="center" bgcolor="#ffc107"><b>نسبة النجاح</b><br>' . $passRate . '%</td>
        </tr></table>', true, false, false, false, 'R');

        return $pdf->Output('تقرير-' . $student->user->name . '.pdf', 'D');
    }

    protected function printView(string $title, array $headers, array $rows, array $stats, ?string $subtitle)
    {
        return response()->view('admin.reports.print', compact('title', 'headers', 'rows', 'stats', 'subtitle'));
    }

    protected function recitationPdf($sessions, $stats, $fromDate, $toDate)
    {
        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->setRTL(true);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->AddPage();
        $pdf->SetFont('aealarabiya', '', 12);

        $centerName = config('app.center_name', 'مركز التسميع');
        $pdf->SetFontSize(16);
        $pdf->Cell(0, 8, $centerName, 0, true, 'C', 0);
        $pdf->SetFontSize(18);
        $pdf->Cell(0, 10, 'تقرير جلسات التسميع', 0, true, 'C', 0);
        $pdf->SetFontSize(12);
        $pdf->Cell(0, 8, 'الفترة: ' . ($fromDate ? $fromDate->format('Y/m/d') . ' - ' . $toDate->format('Y/m/d') : 'الكل'), 0, true, 'C', 0);
        $pdf->Ln(5);

        $pdf->writeHTML($this->pdfStatsTable($stats, '#182B48', ['#28a745', '#dc3545', '#ffc107']), true, false, false, false, 'C');
        $pdf->Ln(8);

        $html = '<table border="1" cellpadding="4"><tr bgcolor="#182B48" color="white">';
        foreach (['#', 'الطالب', 'المشرف', 'الجزء', 'الصفحات', 'الدرجة', 'الحالة', 'التاريخ'] as $h) {
            $html .= '<th align="center">' . $h . '</th>';
        }
        $html .= '</tr>';

        $i = 1;
        foreach ($sessions as $s) {
            $part = $s->parts_label;
            $pages = ($s->start_page && $s->end_page) ? ($s->start_page . ' - ' . $s->end_page) : '-';
            $score = $s->final_score !== null ? number_format($s->final_score, 1) . '%' : '-';
            $statusColor = match ($s->status) {
                'passed' => '#28a745',
                'failed', 'failed_continued' => '#dc3545',
                'in_progress' => '#ffc107',
                default => '#6c757d',
            };
            $html .= '<tr>
                <td align="center">' . $i++ . '</td>
                <td>' . ($s->student->user->name ?? '-') . '</td>
                <td>' . ($s->supervisor->name ?? '-') . '</td>
                <td align="center">' . $part . '</td>
                <td align="center">' . $pages . '</td>
                <td align="center">' . $score . '</td>
                <td align="center" color="' . $statusColor . '"><b>' . $s->status_label . '</b></td>
                <td align="center">' . ($s->session_date ? $s->session_date->format('Y/m/d') : '-') . '</td>
            </tr>';
        }
        $html .= '</table>';
        $pdf->writeHTML($html, true, false, false, false, '');

        $filename = $fromDate
            ? 'تقرير-التسميع-' . $fromDate->format('Y-m-d') . '.pdf'
            : 'تقرير-التسميع-الكل.pdf';
        return $pdf->Output($filename, 'D');
    }

    protected function attendancePdf($attendances, $stats, $fromDate, $toDate)
    {
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->setRTL(true);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->AddPage();
        $pdf->SetFont('aealarabiya', '', 12);

        $centerName = config('app.center_name', 'مركز التسميع');
        $pdf->SetFontSize(16);
        $pdf->Cell(0, 8, $centerName, 0, true, 'C', 0);
        $pdf->SetFontSize(18);
        $pdf->Cell(0, 10, 'تقرير الحضور والغياب', 0, true, 'C', 0);
        $pdf->SetFontSize(12);
        $pdf->Cell(0, 8, 'الفترة: ' . ($fromDate ? $fromDate->format('Y/m/d') . ' - ' . $toDate->format('Y/m/d') : 'الكل'), 0, true, 'C', 0);
        $pdf->Ln(5);

        $pdf->writeHTML($this->pdfStatsTable($stats, '#059669', ['#28a745', '#dc3545', '#ffc107', '#17a2b8']), true, false, false, false, 'C');
        $pdf->Ln(8);

        $html = '<table border="1" cellpadding="4"><tr bgcolor="#182B48" color="white">';
        foreach (['#', 'الطالب', 'المشرف', 'التاريخ', 'وقت الحضور', 'وقت الانصراف', 'الحالة'] as $h) {
            $html .= '<th align="center">' . $h . '</th>';
        }
        $html .= '</tr>';

        $i = 1;
        foreach ($attendances as $a) {
            $statusLabel = $this->attendanceStatusLabel($a->status);
            $statusColor = match ($a->status) {
                'present' => '#28a745', 'absent' => '#dc3545', 'late' => '#ffc107', 'excused' => '#17a2b8', default => '#6c757d',
            };
            $html .= '<tr>
                <td align="center">' . $i++ . '</td>
                <td>' . ($a->student->user->name ?? '-') . '</td>
                <td>' . ($a->supervisor->name ?? '-') . '</td>
                <td align="center">' . ($a->date?->format('Y/m/d') ?? '-') . '</td>
                <td align="center">' . ($a->arrival_time ?? '-') . '</td>
                <td align="center">' . ($a->departure_time ?? '-') . '</td>
                <td align="center" color="' . $statusColor . '"><b>' . $statusLabel . '</b></td>
            </tr>';
        }
        $html .= '</table>';
        $pdf->writeHTML($html, true, false, false, false, '');

        $filename = $fromDate
            ? 'تقرير-الحضور-' . $fromDate->format('Y-m-d') . '.pdf'
            : 'تقرير-الحضور-الكل.pdf';
        return $pdf->Output($filename, 'D');
    }

    protected function aggregatePdf(array $payload, array $meta)
    {
        $recStats = $payload['recStats'];
        $attStats = $payload['attStats'];
        $allSessions = $payload['allSessions'];
        $allAttendances = $payload['allAttendances'];

        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->setRTL(true);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->AddPage();
        $pdf->SetFont('aealarabiya', '', 12);

        $centerName = config('app.center_name', 'مركز التسميع');
        $pdf->SetFontSize(16);
        $pdf->Cell(0, 8, $centerName, 0, true, 'C', 0);
        $pdf->SetFontSize(18);
        $pdf->Cell(0, 10, $meta['title'], 0, true, 'C', 0);
        $pdf->SetFontSize(12);
        $pdf->Cell(0, 8, $meta['subtitle'], 0, true, 'C', 0);
        $pdf->Ln(5);

        $pdf->writeHTML('<table border="1" cellpadding="5"><tr>
            <td align="center"><b>إجمالي الجلسات</b><br>' . $recStats['total'] . '</td>
            <td align="center" bgcolor="#28a745" color="white"><b>ناجح</b><br>' . $recStats['passed'] . '</td>
            <td align="center" bgcolor="#dc3545" color="white"><b>راسب</b><br>' . $recStats['failed'] . '</td>
            <td align="center" bgcolor="#ffc107"><b>نسبة النجاح</b><br>' . $recStats['pass_rate'] . '%</td>
            <td align="center" bgcolor="#6c757d" color="white"><b>متوسط الدرجة</b><br>' . $recStats['avg_score'] . '%</td>
            <td align="center" bgcolor="#059669" color="white"><b>نسبة الحضور</b><br>' . $attStats['attendance_rate'] . '%</td>
        </tr></table>', true, false, false, false, 'C');
        $pdf->Ln(8);

        if ($allSessions->isNotEmpty()) {
            $html = '<table border="1" cellpadding="4"><tr bgcolor="#182B48" color="white">';
            foreach (['#', 'الطالب', 'المشرف', 'الجزء', 'الدرجة', 'الحالة', 'التاريخ'] as $h) {
                $html .= '<th align="center">' . $h . '</th>';
            }
            $html .= '</tr>';
            $i = 1;
            foreach ($allSessions as $s) {
                $statusColor = match ($s->status) {
                    'passed' => '#28a745', 'failed', 'failed_continued' => '#dc3545', 'in_progress' => '#ffc107', default => '#6c757d',
                };
                $html .= '<tr>
                    <td align="center">' . $i++ . '</td>
                    <td>' . ($s->student->user->name ?? '-') . '</td>
                    <td>' . ($s->supervisor->name ?? '-') . '</td>
                    <td align="center">' . $s->parts_label . '</td>
                    <td align="center">' . ($s->final_score !== null ? number_format($s->final_score, 1) . '%' : '-') . '</td>
                    <td align="center" color="' . $statusColor . '"><b>' . $s->status_label . '</b></td>
                    <td align="center">' . ($s->session_date ? $s->session_date->format('Y/m/d') : '-') . '</td>
                </tr>';
            }
            $html .= '</table>';
            $pdf->SetFontSize(14);
            $pdf->Cell(0, 8, 'جلسات التسميع', 0, true, 'R', 0);
            $pdf->writeHTML($html, true, false, false, false, '');
            $pdf->Ln(5);
        }

        if ($allAttendances->isNotEmpty()) {
            $html = '<table border="1" cellpadding="4"><tr bgcolor="#182B48" color="white">';
            foreach (['#', 'الطالب', 'المشرف', 'التاريخ', 'وقت الحضور', 'الحالة'] as $h) {
                $html .= '<th align="center">' . $h . '</th>';
            }
            $html .= '</tr>';
            $i = 1;
            foreach ($allAttendances as $a) {
                $statusLabel = $this->attendanceStatusLabel($a->status);
                $statusColor = match ($a->status) {
                    'present' => '#28a745', 'absent' => '#dc3545', 'late' => '#ffc107', 'excused' => '#17a2b8', default => '#6c757d',
                };
                $html .= '<tr>
                    <td align="center">' . $i++ . '</td>
                    <td>' . ($a->student->user->name ?? '-') . '</td>
                    <td>' . ($a->supervisor->name ?? '-') . '</td>
                    <td align="center">' . ($a->date?->format('Y/m/d') ?? '-') . '</td>
                    <td align="center">' . ($a->arrival_time ?? '-') . '</td>
                    <td align="center" color="' . $statusColor . '"><b>' . $statusLabel . '</b></td>
                </tr>';
            }
            $html .= '</table>';
            $pdf->SetFontSize(14);
            $pdf->Cell(0, 8, 'سجلات الحضور', 0, true, 'R', 0);
            $pdf->writeHTML($html, true, false, false, false, '');
        }

        return $pdf->Output($meta['filename'] . '.pdf', 'D');
    }

    protected function pdfStatsTable(array $stats, string $headerColor, array $palette): string
    {
        $cells = '';
        $i = 0;
        foreach ($stats as $label => $value) {
            $color = $palette[$i % count($palette)] ?? '#6c757d';
            $cells .= '<td align="center" bgcolor="' . $color . '" color="white"><b>' . $label . '</b><br>' . $value . '</td>';
            $i++;
        }
        return '<table border="1" cellpadding="5"><tr>' . $cells . '</tr></table>';
    }

    protected function dateRangeLabel($fromDate, $toDate, string $period): string
    {
        if (!$fromDate || !$toDate) {
            return 'كل الفترات';
        }
        return 'الفترة: ' . $fromDate->format('Y/m/d') . ' - ' . $toDate->format('Y/m/d');
    }

    protected function attendanceStatusLabel(string $status): string
    {
        return match ($status) {
            'present' => 'حاضر',
            'absent' => 'غائب',
            'late' => 'متأخر',
            'excused' => 'معذور',
            'holiday' => 'عطلة',
            default => $status,
        };
    }
}
