<?php

namespace App\Services;

use App\Exports\AttendanceReportExport;
use App\Exports\HimmaReportExport;
use App\Exports\RecitationReportExport;
use App\Exports\SupervisorMissingExport;
use Maatwebsite\Excel\Facades\Excel;
use TCPDF;

class ReportExporter
{
    public function exportRecitation(string $format, $sessions, array $stats, $fromDate, $toDate, string $period, array $summary = [])
    {
        // Order the detailed sessions chronologically by session date.
        $sessions = $sessions
            ->sortBy(fn ($s) => optional($s->session_date)->getTimestamp() ?? 0)
            ->values();

        $headers = ['#', 'الطالب', 'المشرف', 'النوع', 'الجزء', 'الصفحات', 'الأخطاء', 'الترددات', 'الدرجة', 'الحالة', 'التاريخ', 'اليوم'];
        $rows = $sessions->map(function ($s, $i) {
            return [
                $i + 1,
                $s->student->user->name ?? '—',
                $s->supervisor->name ?? '—',
                $s->type_label,
                $s->parts_label,
                $s->total_pages ?? '—',
                $s->total_errors ?? 0,
                $s->total_hesitations ?? 0,
                $s->final_score !== null ? number_format($s->final_score, 1) . '%' : '—',
                $s->status_label,
                $s->session_date?->format('Y/m/d') ?? '—',
                $s->day ?? '—',
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
                new RecitationReportExport('تقرير التسميع', $headers, $rows, $statsForExport, $subtitle, $summary, 'recitation'),
                $filename . '.xlsx'
            ),
            'print' => $this->printView('تقرير التسميع', $headers, $rows, $statsForExport, $subtitle),
            default => $this->recitationPdf($sessions, $stats, $fromDate, $toDate, $period, $summary),
        };
    }

    public function exportAttendance(string $format, $attendances, array $stats, $fromDate, $toDate, string $period, array $summary = [])
    {
        $headers = ['#', 'الطالب', 'المشرف', 'التاريخ', 'اليوم', 'وقت الحضور', 'وقت الانصراف', 'الحالة'];
        $rows = $attendances->values()->map(function ($a, $i) {
            $statusLabel = $this->attendanceStatusLabel($a->status);
            return [
                $i + 1,
                $a->student->user->name ?? '—',
                $a->supervisor->name ?? '—',
                $a->date?->format('Y/m/d') ?? '—',
                $a->day ?? '—',
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
                new AttendanceReportExport('تقرير الحضور والغياب', $headers, $rows, $statsForExport, $subtitle, $summary, 'attendance'),
                $filename . '.xlsx'
            ),
            'print' => $this->printView('تقرير الحضور والغياب', $headers, $rows, $statsForExport, $subtitle),
            default => $this->attendancePdf($attendances, $stats, $fromDate, $toDate, $period),
        };
    }

    public function exportRecitationPdf($sessions, $stats, $fromDate, $toDate, array $summary = [])
    {
        return $this->recitationPdf($sessions, $stats, $fromDate, $toDate, null, $summary);
    }

    public function exportAttendancePdf($attendances, $stats, $fromDate, $toDate)
    {
        return $this->attendancePdf($attendances, $stats, $fromDate, $toDate);
    }

    public function exportSupervisorMissing(string $format, $fromDate, $toDate, ?int $supervisorId)
    {
        /** @var \App\Services\ReportService $reports */
        $reports = app(\App\Services\ReportService::class);
        $payload = $reports->supervisorMissingDays($fromDate, $toDate, $supervisorId);
        $rows     = $payload['rows'];
        $stats    = $payload['stats'];

        $headers = ['#', 'التاريخ', 'اليوم', 'المشرف', 'الجلسة', 'الحضور'];

        $exportRows = $rows->values()->map(function ($r, $i) {
            return [
                $i + 1,
                $r['date']->format('Y/m/d'),
                $r['day_name'],
                $r['supervisor_name'],
                $this->missingCell($r['session_expected'], $r['session_missing']),
                $this->missingCell($r['attendance_expected'], $r['attendance_missing']),
            ];
        })->toArray();

        $subtitle = $this->dateRangeLabel($fromDate, $toDate, 'custom');
        $statsForExport = [
            'إجمالي النواقص' => $stats['rows_count'],
            'جلسات ناقصة'    => $stats['missing_sessions'],
            'حضور ناقص'      => $stats['missing_attendance'],
        ];

        $supervisorSegment = null;
        if ($supervisorId) {
            $supervisorSegment = $rows->pluck('supervisor_name')
                ->filter()
                ->unique()
                ->first() ?? 'غير محدد';
        }

        $filename = sprintf(
            'نواقص شهر %s %s%s',
            (string) ($fromDate?->month ?? now()->month),
            'المشرف ',
            $supervisorSegment ?? 'كل المشرفين'
        );

        return match ($format) {
            'excel', 'xlsx' => Excel::download(
                new SupervisorMissingExport('نواقص المشرفين', $headers, $exportRows, $statsForExport, $subtitle),
                $filename . '.xlsx'
            ),
            'print' => $this->printView('نواقص المشرفين', $headers, $exportRows, $statsForExport, $subtitle),
            'pdf'   => $this->supervisorMissingPdf($exportRows, $statsForExport, $fromDate, $toDate, $filename),
            default => $this->supervisorMissingPdf($exportRows, $statsForExport, $fromDate, $toDate, $filename),
        };
    }

    protected function missingCell(bool $expected, bool $missing): string
    {
        if (! $expected) {
            return '—';
        }
        return $missing ? '✗ ناقص' : '✓ مسجل';
    }

    protected function supervisorMissingPdf(array $rows, array $stats, $fromDate, $toDate, ?string $filename = null)
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
        $pdf->Cell(0, 10, 'نواقص المشرفين', 0, true, 'C', 0);
        $pdf->SetFontSize(12);
        $pdf->Cell(0, 8, 'الفترة: ' . ($fromDate ? $fromDate->format('Y/m/d') . ' - ' . $toDate->format('Y/m/d') : 'الكل'), 0, true, 'C', 0);
        $pdf->Ln(5);

        $pdf->writeHTML($this->pdfStatsTable($stats, '#182B48', ['#dc3545', '#ffc107', '#059669']), true, false, false, false, 'C');
        $pdf->Ln(8);

        $html = '<table border="1" cellpadding="4" cols="6" width="100%"><thead><tr bgcolor="#182B48" color="white">';
        foreach (['#', 'التاريخ', 'اليوم', 'المشرف', 'الجلسة', 'الحضور'] as $h) {
            $html .= '<th align="center">' . $h . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        foreach ($rows as $r) {
            $html .= '<tr>';
            foreach ($r as $i => $cell) {
                $align = $i === 3 ? ' align="right"' : ' align="center"';
                $color = '';
                if ($cell === '✗ ناقص') {
                    $color = ' color="#dc3545"';
                } elseif ($cell === '✓ مسجل') {
                    $color = ' color="#28a745"';
                }
                $html .= '<td' . $align . $color . '>' . $cell . '</td>';
            }
            $html .= '</tr>';
        }

        if (empty($rows)) {
            $html .= '<tr><td colspan="6" align="center">لا توجد نواقص في هذه الفترة</td></tr>';
        }

        $html .= '</tbody></table>';
        $pdf->writeHTML($html, true, false, false, false, '');

        $filename ??= $this->pdfFilename(
            'نواقص المشرفين',
            null,
            $this->periodSlug($fromDate, $toDate, 'custom')
        );

        return $this->pdfDownload($pdf, $filename);
    }

    public function exportAggregate(string $format, array $payload, array $meta)
    {
        $headers = ['القسم', '#', 'الطالب', 'المشرف', 'التفاصيل', 'الدرجة/الحالة', 'التاريخ', 'اليوم'];
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
                $s->day ?? '—',
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
                $a->day ?? '—',
            ];
        }

        $statsForExport = [
            'جلسات (إجمالي)' => $payload['recStats']['total'],
            'ناجح'           => $payload['recStats']['passed'],
            'راسب'           => $payload['recStats']['failed'],
            'نسبة النجاح'     => $payload['recStats']['pass_rate'] . '%',
            'حضور (إجمالي)'   => $payload['attStats']['total'],
            'حاضر'           => $payload['attStats']['present'],
            'غائب'           => $payload['attStats']['absent'],
            'نسبة الحضور'     => $payload['attStats']['attendance_rate'] . '%',
        ];

        $filename = $meta['filename'] . '-' . now()->format('Ymd-His');

        return match ($format) {
            'excel', 'xlsx' => Excel::download(
                new RecitationReportExport($meta['title'], $headers, $rows, $statsForExport, $meta['subtitle'], $payload['summary'] ?? [], 'both'),
                $filename . '.xlsx'
            ),
            'print' => $this->printView($meta['title'], $headers, $rows, $statsForExport, $meta['subtitle'], $payload['breakdown'] ?? []),
            default => $this->aggregatePdf($payload, $meta),
        };
    }

    /**
     * Export a "يوم الهمة" report (overview / day / student). The payload is
     * format-agnostic: a list of Arabic-labelled stats plus one or more tables.
     */
    public function exportHimma(string $format, array $payload, array $meta)
    {
        $title    = $meta['title'] ?? 'تقرير يوم الهمة';
        $subtitle = $meta['subtitle'] ?? null;
        $stats    = $payload['stats'] ?? [];
        $tables   = $payload['tables'] ?? [];
        $filename = ($meta['filename'] ?? 'himma-report') . '-' . now()->format('Ymd-His');

        return match ($format) {
            'excel', 'xlsx' => Excel::download(
                new HimmaReportExport($title, $stats, $tables, $subtitle),
                $filename . '.xlsx'
            ),
            default => $this->himmaPdf($title, $subtitle, $stats, $tables),
        };
    }

    protected function himmaPdf(string $title, ?string $subtitle, array $stats, array $tables)
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
        $pdf->Cell(0, 10, $title, 0, true, 'C', 0);
        if ($subtitle) {
            $pdf->SetFontSize(12);
            $pdf->Cell(0, 8, $subtitle, 0, true, 'C', 0);
        }
        $pdf->Ln(5);

        if (!empty($stats)) {
            $pdf->writeHTML(
                $this->pdfStatsTable($stats, '#182B48', ['#182B48', '#d4a030', '#27ae60', '#e74c3c', '#f39c12', '#2980b9']),
                true, false, false, false, 'C'
            );
            $pdf->Ln(8);
        }

        foreach ($tables as $table) {
            if (!empty($table['title'])) {
                $pdf->SetFontSize(14);
                $pdf->Cell(0, 8, $table['title'], 0, true, 'C', 0);
                $pdf->SetFontSize(12);
            }

            $html = '<table border="1" cellpadding="4"><tr bgcolor="#182B48" color="white">';
            foreach ($table['headers'] as $header) {
                $html .= '<th align="center">' . htmlspecialchars((string) $header, ENT_QUOTES, 'UTF-8') . '</th>';
            }
            $html .= '</tr>';

            foreach ($table['rows'] as $row) {
                $html .= '<tr>';
                foreach ($row as $cell) {
                    $html .= '<td align="center">' . htmlspecialchars((string) $cell, ENT_QUOTES, 'UTF-8') . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</table>';

            $pdf->writeHTML($html, true, false, false, false, '');
            $pdf->Ln(5);
        }

        return $this->pdfDownload($pdf, $this->pdfFilename($title, null, null));
    }

    public function exportStudentPdf($student, $sessions, $attendances, $sessionStats, $attendanceStats, $fromDate, $toDate, array $dayStats = [])
    {
        $attendancePct = $attendanceStats['attendance_rate'];
        $passRate = $sessionStats['pass_rate'];

        $dayText = function (string $daysKey, string $totalKey) use ($dayStats) {
            $days  = (int) ($dayStats[$daysKey] ?? 0);
            $total = $dayStats[$totalKey] ?? null;
            return $total === null ? (string) $days : $days . ' من ' . $total;
        };

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
            <td align="center" bgcolor="#059669" color="white"><b>أيام الحضور</b><br>' . $dayText('att_days', 'att_days_total') . '</td>
        </tr></table>', true, false, false, false, 'R');
        $pdf->Ln(5);

        $pdf->SetFontSize(14);
        $pdf->Cell(0, 10, 'إحصائيات التسميع', 0, true, 'R', 0);
        $pdf->writeHTML('<table border="1" cellpadding="5"><tr>
            <td align="center"><b>الإجمالي</b><br>' . $sessionStats['total'] . '</td>
            <td align="center" bgcolor="#28a745" color="white"><b>ناجح</b><br>' . $sessionStats['passed'] . '</td>
            <td align="center" bgcolor="#dc3545" color="white"><b>راسب</b><br>' . $sessionStats['failed'] . '</td>
            <td align="center" bgcolor="#ffc107"><b>نسبة النجاح</b><br>' . $passRate . '%</td>
            <td align="center" bgcolor="#16a085" color="white"><b>أيام التسميع</b><br>' . $dayText('rec_days', 'rec_days_total') . '</td>
        </tr></table>', true, false, false, false, 'R');

        $filename = $this->pdfFilename(
            'تقرير طالب',
            $student->user->name ?? null,
            $this->periodSlug($fromDate, $toDate)
        );

        return $this->pdfDownload($pdf, $filename);
    }

    /**
     * Export the written report (بطاقة تقرير الطالب) for a single student
     * as a PDF document.
     */
    public function exportWrittenReportPdf($student, array $breakdown, string $periodLabel, $fromDate, $toDate)
    {
        $row = $breakdown[0] ?? null;

        $filename = $this->pdfFilename(
            'التقرير المكتوب',
            $student->user->name ?? null,
            $this->periodSlug($fromDate, $toDate, $periodLabel)
        );

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->setRTL(true);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(12, 12, 12);
        $pdf->SetAutoPageBreak(true, 12);
        $pdf->AddPage();
        $pdf->SetFont('aealarabiya', '', 12);

        $centerName = config('app.center_name', 'مركز التسميع');
        $pdf->SetFontSize(16);
        $pdf->Cell(0, 8, $centerName, 0, true, 'C', 0);
        $pdf->SetFontSize(18);
        $pdf->Cell(0, 10, 'بطاقة تقرير الطالب', 0, true, 'C', 0);
        $pdf->SetFontSize(12);
        $pdf->Cell(0, 7, 'الطالب: ' . ($student->user->name ?? '-'), 0, true, 'C', 0);
        $pdf->Cell(0, 7, 'المستوى: ' . ($student->level->name ?? '-') . '   |   رقم الطالب: ' . ($student->student_number ?? '-'), 0, true, 'C', 0);
        $pdf->Cell(0, 7, 'الفترة: ' . ($fromDate && $toDate ? $fromDate->format('Y/m/d') . ' - ' . $toDate->format('Y/m/d') : 'كل الفترات'), 0, true, 'C', 0);
        $pdf->Ln(3);

        if (!$row) {
            $pdf->SetFontSize(12);
            $pdf->Cell(0, 12, 'لا توجد بيانات في هذه الفترة.', 0, true, 'C', 0);

            return $this->pdfDownload($pdf, $filename);
        }

        $v   = fn ($key) => $row[$key] ?? 0;
        $num = fn ($n) => number_format((float) $n);

        // Value palettes: green = passed, red = failed, navy = neutral.
        $ok   = ['text' => '#1e7e34', 'bg' => '#e8f6ec'];
        $bad  = ['text' => '#c0392b', 'bg' => '#fdecea'];
        $warn = ['text' => '#b45309', 'bg' => '#fef3c7'];
        $info = ['text' => '#1d4ed8', 'bg' => '#e8effd'];
        $base = ['text' => '#182B48', 'bg' => '#ffffff'];

        $table   = fn (string $rows) => '<table border="1" cellpadding="6" style="font-size:12px;">' . $rows . '</table><br/>';
        $section = fn (string $title, string $accent) =>
            '<tr><td colspan="2" bgcolor="' . $accent . '" color="#ffffff" align="center"><b>' . $title . '</b></td></tr>';
        $line = fn (string $label, $value, array $palette) =>
            '<tr><td width="62%" color="#475569">' . $label . '</td>'
            . '<td width="38%" align="center" bgcolor="' . $palette['bg'] . '" color="' . $palette['text'] . '"><b>' . $value . '</b></td></tr>';
        $dayLine = function (string $daysKey, string $totalKey) use ($row, $num) {
            $days  = (int) ($row[$daysKey] ?? 0);
            $total = $row[$totalKey] ?? null;
            return $total === null ? $num($days) : $num($days) . ' من ' . $num($total);
        };

        $html = '';

        // الحضور والغياب (green)
        $rows  = $section('الحضور والغياب', '#059669');
        $rows .= $line('1- العدد الكلي للجلسات', $num($v('att_total')), $base);
        $rows .= $line('2- عدد أيام الحضور', $dayLine('att_days', 'att_days_total'), $ok);
        $rows .= $line('3- عدد أيام الغياب', $num($v('att_absent')), $bad);
        $rows .= $line('4- عدد أيام التأخير', $num($v('att_late')), $warn);
        $rows .= $line('5- عدد أيام الغياب بعذر', $num($v('att_excused')), $info);
        $html .= $table($rows);

        // التسميع (teal) - separate section
        $rows  = $section('التسميع', '#16a085');
        $rows .= $line('1- عدد أيام التسميع', $dayLine('rec_days', 'rec_days_total'), $base);
        $rows .= $line('2- عدد الأيام التي لم يسمع بها', $num($v('not_recite_days')), $base);
        $html .= $table($rows);

        // الحفظ الجديد (navy)
        $rows  = $section('الحفظ الجديد', '#182B48');
        $rows .= $line('1- عدد جلسات الحفظ الجديد', $num($v('new_memorization_sessions')), $base);
        $rows .= $line('2- عدد الجلسات النّاجحة', $num($v('new_memorization_passed')), $ok);
        $rows .= $line('3- عدد الجلسات الرّاسبة', $num($v('new_memorization_failed')), $bad);
        $rows .= $line('4- عدد صفحات الحفظ الجديد النّاجحة', $num($v('new_memorization_pages')), $ok);
        $rows .= $line('5- عدد صفحات الحفظ الجديد الرّاسبة', $num($v('new_memorization_failed_pages')), $bad);
        $rows .= $line('6- عدد الأجزاء المحفوظة', $num($v('new_memorization_parts')), $base);
        $html .= $table($rows);

        // المراجعة (blue)
        $rows  = $section('المراجعة', '#2980b9');
        $rows .= $line('1- عدد جلسات المراجعة', $num($v('review_sessions')), $base);
        $rows .= $line('2- عدد الجلسات النّاجحة', $num($v('review_passed')), $ok);
        $rows .= $line('3- عدد الجلسات الرّاسبة', $num($v('review_failed')), $bad);
        $rows .= $line('4- عدد صفحات المراجعة النّاجحة', $num($v('review_pages')), $ok);
        $rows .= $line('5- عدد صفحات المراجعة الرّاسبة', $num($v('review_failed_pages')), $bad);
        $rows .= $line('6- عدد الأجزاء المراجعة', $num($v('review_parts')), $base);
        $html .= $table($rows);

        // السرد (teal)
        if ($v('recitation_sessions') > 0) {
            $rows  = $section('السرد', '#16a085');
            $rows .= $line('1- عدد الجلسات', $num($v('recitation_sessions')), $base);
            $rows .= $line('2- عدد الجلسات النّاجحة', $num($v('recitation_passed')), $ok);
            $rows .= $line('3- عدد الجلسات الرّاسبة', $num($v('recitation_failed')), $bad);
            $html .= $table($rows);
        }

        // الحفظ غير المتقن (purple)
        if ($v('unmastered_sessions') > 0) {
            $rows  = $section('الحفظ غير المتقن', '#7c3aed');
            $rows .= $line('1- عدد جلسات الحفظ غير المتقن', $num($v('unmastered_sessions')), $base);
            $rows .= $line('2- عدد الجلسات النّاجحة', $num($v('unmastered_passed')), $ok);
            $rows .= $line('3- عدد الجلسات الرّاسبة', $num($v('unmastered_failed')), $bad);
            $rows .= $line('4- عدد صفحات الحفظ غير المتقن النّاجحة', $num($v('unmastered_pages')), $ok);
            $rows .= $line('5- عدد صفحات الحفظ غير المتقن الرّاسبة', $num($v('unmastered_failed_pages')), $bad);
            $rows .= $line('6- عدد الأجزاء غير المتقنة', $num($v('unmastered_parts')), $base);
            $html .= $table($rows);
        }

        // المفصل (gold) — each presented mafsil colored by its result
        if (!empty($row['mafsil_details'])) {
            $rows = $section('المفصل', '#d4a030');
            foreach ($row['mafsil_details'] as $i => $m) {
                $passed = ($m['status'] ?? '') === 'passed';
                $rows .= $line(
                    ($i + 1) . '- المفصل الذي تقدم له: ' . ($m['label'] ?? ''),
                    $m['status_label'] ?? '',
                    $passed ? $ok : $bad
                );
            }
            $html .= $table($rows);
        }

        // الإجمالي
        $total  = '<tr><td width="62%" bgcolor="#f1f5f9" color="#182B48"><b>إجمالي جلسات ' . $periodLabel . '</b></td>';
        $total .= '<td width="38%" align="center" bgcolor="#182B48" color="#ffffff"><b>' . $num($v('total_sessions')) . '</b></td></tr>';
        $html .= $table($total);

        // مفتاح الألوان
        $html .= '<p style="font-size:10px;" color="#6b7280">'
            . '<font color="#1e7e34">■</font> ناجح&nbsp;&nbsp;'
            . '<font color="#c0392b">■</font> راسب&nbsp;&nbsp;'
            . '<font color="#182B48">■</font> إجمالي/محايد'
            . '</p>';

        $pdf->writeHTML($html, true, false, false, false, '');

        return $this->pdfDownload($pdf, $filename);
    }

    /**
     * Return a PDF as a downloadable Laravel response.
     */
    private function pdfDownload(TCPDF $pdf, string $filename)
    {
        if (!str_ends_with($filename, '.pdf')) {
            $filename .= '.pdf';
        }

        $content = $pdf->Output($filename, 'S');

        // RFC 5987: send a percent-encoded UTF-8 name for Arabic filenames,
        // plus a stripped ASCII fallback for clients that ignore filename*.
        $ascii = preg_replace('/[^\x20-\x7E]/', '', pathinfo($filename, PATHINFO_FILENAME));
        $ascii = trim(preg_replace('/[-_ ]+/', '-', (string) $ascii), '-');
        $fallback = $ascii !== '' ? $ascii . '.pdf' : 'report.pdf';

        return response($content, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fallback . '"; filename*=UTF-8\'\'' . rawurlencode($filename),
        ]);
    }

    /**
     * Build an Arabic report filename from the report type, the subject
     * (student name) and the period, stripping characters that are not
     * allowed in filenames.
     */
    protected function pdfFilename(string $type, ?string $subject, ?string $period): string
    {
        $parts = array_filter(
            [$type, $subject, $period],
            fn ($part) => $part !== null && trim((string) $part) !== ''
        );

        $name = str_replace([':', '*', '?', '"', '<', '>', '|', '/', '\\', '—', '–'], ' ', implode(' ', $parts));
        $name = preg_replace('/\s+/u', '-', $name);
        $name = preg_replace('/-+/u', '-', $name);

        return trim((string) $name, '-') ?: 'تقرير';
    }

    /**
     * Human-readable period slug: the explicit date range when available,
     * otherwise the supplied label.
     */
    protected function periodSlug($fromDate, $toDate, ?string $fallback = null): string
    {
        if ($fromDate && $toDate) {
            return $fromDate->format('Y-m-d') . '_' . $toDate->format('Y-m-d');
        }

        return $fallback ?: 'كل الفترات';
    }

    /**
     * Arabic label for a resolved period code.
     */
    protected function periodLabel(?string $period): string
    {
        return match ($period) {
            'today'   => 'اليوم',
            'week'    => 'هذا الأسبوع',
            'month'   => 'هذا الشهر',
            'quarter' => 'هذا الربع',
            'half'    => 'هذا النصف',
            'year'    => 'هذه السنة',
            'custom'  => 'فترة مخصصة',
            default   => 'كل الفترات',
        };
    }

    /**
     * The subject (student name) for a report: the single student the rows
     * belong to, or the fallback label for multi-student reports.
     */
    protected function reportSubject($records, string $fallback = 'جميع الطلاب'): string
    {
        if ($records instanceof \Illuminate\Support\Collection && $records->isNotEmpty()) {
            $name = $records->first()->student->user->name ?? null;

            if ($name && $records->pluck('student_id')->unique()->count() === 1) {
                return $name;
            }
        }

        return $fallback;
    }

    protected function printView(string $title, array $headers, array $rows, array $stats, ?string $subtitle, array $breakdown = [])
    {
        return response()->view('admin.reports.print', compact('title', 'headers', 'rows', 'stats', 'subtitle', 'breakdown'));
    }

    protected function recitationPdf($sessions, $stats, $fromDate, $toDate, ?string $period = null, array $summary = [])
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

        $pdf->writeHTML($this->pdfStatsTable($this->recitationStatsLabels($stats), '#182B48', ['#28a745', '#dc3545', '#ffc107', '#17a2b8', '#6f42c1']), true, false, false, false, 'C');
        $pdf->Ln(8);

        if (!empty($summary)) {
            $pdf->SetFontSize(14);
            $pdf->Cell(0, 8, 'ملخص الطلاب', 0, true, 'C', 0);
            $pdf->SetFontSize(12);
            $pdf->writeHTML($this->recitationSummaryTablePdf($summary), true, false, false, false, 'C');
            $pdf->Ln(8);
        }

        $pdf->SetFontSize(14);
        $pdf->Cell(0, 8, 'تفاصيل الجلسات', 0, true, 'C', 0);
        $pdf->SetFontSize(12);

        $html = '<table border="1" cellpadding="4"><tr bgcolor="#182B48" color="white">';
        foreach (['#', 'الطالب', 'المشرف', 'النوع', 'الجزء', 'الصفحات', 'الدرجة', 'الحالة', 'التاريخ', 'اليوم'] as $h) {
            $html .= '<th align="center">' . $h . '</th>';
        }
        $html .= '</tr>';

        $i = 1;
        $pdfRowLimit = 500;
        $truncated = $sessions->count() > $pdfRowLimit;
        foreach ($sessions->take($pdfRowLimit) as $s) {
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
                <td>' . htmlspecialchars((string) ($s->student->user->name ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>
                <td>' . htmlspecialchars((string) ($s->supervisor->name ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>
                <td align="center">' . htmlspecialchars((string) $s->type_label, ENT_QUOTES, 'UTF-8') . '</td>
                <td align="center">' . $part . '</td>
                <td align="center">' . $pages . '</td>
                <td align="center">' . $score . '</td>
                <td align="center" color="' . $statusColor . '"><b>' . $s->status_label . '</b></td>
                <td align="center">' . ($s->session_date ? $s->session_date->format('Y/m/d') : '-') . '</td>
                <td align="center">' . ($s->day ?? '-') . '</td>
            </tr>';
        }
        $html .= '</table>';
        $pdf->writeHTML($html, true, false, false, false, '');

        if ($truncated) {
            $pdf->SetFontSize(10);
            $pdf->Cell(0, 8, 'ملاحظة: تم عرض أول ' . $pdfRowLimit . ' سجل من أصل ' . $sessions->count() . '. استخدم تصدير Excel لعرض كل السجلات.', 0, true, 'C', 0);
        }

        $filename = $this->pdfFilename(
            'تقرير التسميع',
            $this->reportSubject($sessions),
            $this->periodSlug($fromDate, $toDate, $this->periodLabel($period))
        );

        return $this->pdfDownload($pdf, $filename);
    }

    protected function attendancePdf($attendances, $stats, $fromDate, $toDate, ?string $period = null)
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

        $pdf->writeHTML($this->pdfStatsTable($this->attendanceStatsLabels($stats), '#059669', ['#28a745', '#dc3545', '#ffc107', '#17a2b8']), true, false, false, false, 'C');
        $pdf->Ln(8);

        $html = '<table border="1" cellpadding="4"><tr bgcolor="#182B48" color="white">';
        foreach (['#', 'الطالب', 'المشرف', 'التاريخ', 'اليوم', 'وقت الحضور', 'وقت الانصراف', 'الحالة'] as $h) {
            $html .= '<th align="center">' . $h . '</th>';
        }
        $html .= '</tr>';

        $i = 1;
        $pdfRowLimit = 500;
        $truncated = $attendances->count() > $pdfRowLimit;
        foreach ($attendances->take($pdfRowLimit) as $a) {
            $statusLabel = $this->attendanceStatusLabel($a->status);
            $statusColor = match ($a->status) {
                'present' => '#28a745', 'absent' => '#dc3545', 'late' => '#ffc107', 'excused' => '#17a2b8', default => '#6c757d',
            };
            $html .= '<tr>
                <td align="center">' . $i++ . '</td>
                <td>' . ($a->student->user->name ?? '-') . '</td>
                <td>' . ($a->supervisor->name ?? '-') . '</td>
                <td align="center">' . ($a->date?->format('Y/m/d') ?? '-') . '</td>
                <td align="center">' . ($a->day ?? '-') . '</td>
                <td align="center">' . ($a->arrival_time ?? '-') . '</td>
                <td align="center">' . ($a->departure_time ?? '-') . '</td>
                <td align="center" color="' . $statusColor . '"><b>' . $statusLabel . '</b></td>
            </tr>';
        }
        $html .= '</table>';
        $pdf->writeHTML($html, true, false, false, false, '');

        if ($truncated) {
            $pdf->SetFontSize(10);
            $pdf->Cell(0, 8, 'ملاحظة: تم عرض أول ' . $pdfRowLimit . ' سجل من أصل ' . $attendances->count() . '. استخدم تصدير Excel لعرض كل السجلات.', 0, true, 'C', 0);
        }

        $filename = $this->pdfFilename(
            'تقرير الحضور',
            $this->reportSubject($attendances),
            $this->periodSlug($fromDate, $toDate, $this->periodLabel($period))
        );

        return $this->pdfDownload($pdf, $filename);
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
            <td align="center" bgcolor="#059669" color="white"><b>نسبة الحضور</b><br>' . $attStats['attendance_rate'] . '%</td>
        </tr></table>', true, false, false, false, 'C');
        $pdf->Ln(8);

        // التقرير المكتوب لكل طالب
        if (!empty($payload['breakdown'])) {
            $pdf->SetFontSize(14);
            $pdf->Cell(0, 8, 'التقرير المكتوب', 0, true, 'R', 0);
            $pdf->SetFontSize(11);
            $pdf->writeHTML($this->aggregateWrittenReportHtml($payload['breakdown']), true, false, false, false, '');
            $pdf->Ln(5);
        }

        if ($allSessions->isNotEmpty()) {
            $html = '<table border="1" cellpadding="4"><tr bgcolor="#182B48" color="white">';
            foreach (['#', 'الطالب', 'المشرف', 'الجزء', 'الدرجة', 'الحالة', 'التاريخ', 'اليوم'] as $h) {
                $html .= '<th align="center">' . $h . '</th>';
            }
            $html .= '</tr>';
            $i = 1;
            $pdfRowLimit = 500;
            $truncatedSessions = $allSessions->count() > $pdfRowLimit;
            foreach ($allSessions->take($pdfRowLimit) as $s) {
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
                    <td align="center">' . ($s->day ?? '-') . '</td>
                </tr>';
            }
            $html .= '</table>';
            $pdf->SetFontSize(14);
            $pdf->Cell(0, 8, 'جلسات التسميع', 0, true, 'R', 0);
            $pdf->writeHTML($html, true, false, false, false, '');
            if ($truncatedSessions) {
                $pdf->SetFontSize(10);
                $pdf->Cell(0, 8, 'ملاحظة: تم عرض أول ' . $pdfRowLimit . ' جلسة من أصل ' . $allSessions->count() . '. استخدم تصدير Excel لعرض كل الجلسات.', 0, true, 'C', 0);
            }
            $pdf->Ln(5);
        }

        if ($allAttendances->isNotEmpty()) {
            $html = '<table border="1" cellpadding="4"><tr bgcolor="#182B48" color="white">';
            foreach (['#', 'الطالب', 'المشرف', 'التاريخ', 'اليوم', 'وقت الحضور', 'الحالة'] as $h) {
                $html .= '<th align="center">' . $h . '</th>';
            }
            $html .= '</tr>';
            $i = 1;
            $pdfRowLimit = 500;
            $truncatedAtt = $allAttendances->count() > $pdfRowLimit;
            foreach ($allAttendances->take($pdfRowLimit) as $a) {
                $statusLabel = $this->attendanceStatusLabel($a->status);
                $statusColor = match ($a->status) {
                    'present' => '#28a745', 'absent' => '#dc3545', 'late' => '#ffc107', 'excused' => '#17a2b8', default => '#6c757d',
                };
                $html .= '<tr>
                    <td align="center">' . $i++ . '</td>
                    <td>' . ($a->student->user->name ?? '-') . '</td>
                    <td>' . ($a->supervisor->name ?? '-') . '</td>
                    <td align="center">' . ($a->date?->format('Y/m/d') ?? '-') . '</td>
                    <td align="center">' . ($a->day ?? '-') . '</td>
                    <td align="center">' . ($a->arrival_time ?? '-') . '</td>
                    <td align="center" color="' . $statusColor . '"><b>' . $statusLabel . '</b></td>
                </tr>';
            }
            $html .= '</table>';
            $pdf->SetFontSize(14);
            $pdf->Cell(0, 8, 'سجلات الحضور', 0, true, 'R', 0);
            $pdf->writeHTML($html, true, false, false, false, '');
            if ($truncatedAtt) {
                $pdf->SetFontSize(10);
                $pdf->Cell(0, 8, 'ملاحظة: تم عرض أول ' . $pdfRowLimit . ' سجل من أصل ' . $allAttendances->count() . '. استخدم تصدير Excel لعرض كل السجلات.', 0, true, 'C', 0);
            }
        }

        return $this->pdfDownload($pdf, $this->pdfFilename($meta['title'], 'جميع الطلاب', null));
    }

    /**
     * Build the written-report HTML (all students) for the aggregate PDF.
     */
    protected function aggregateWrittenReportHtml(array $breakdown): string
    {
        $ok   = ['text' => '#1e7e34', 'bg' => '#e8f6ec'];
        $bad  = ['text' => '#c0392b', 'bg' => '#fdecea'];
        $warn = ['text' => '#b45309', 'bg' => '#fef3c7'];
        $info = ['text' => '#1d4ed8', 'bg' => '#e8effd'];
        $base = ['text' => '#182B48', 'bg' => '#ffffff'];

        $table   = fn (string $rows) => '<table border="1" cellpadding="5" style="font-size:11px;">' . $rows . '</table>';
        $section = fn (string $title, string $accent) =>
            '<tr><td colspan="2" bgcolor="' . $accent . '" color="#ffffff" align="center"><b>' . $title . '</b></td></tr>';
        $line = fn (string $label, $value, array $palette) =>
            '<tr><td width="62%" color="#475569">' . $label . '</td>'
            . '<td width="38%" align="center" bgcolor="' . $palette['bg'] . '" color="' . $palette['text'] . '"><b>' . $value . '</b></td></tr>';

        $html = '';

        foreach ($breakdown as $row) {
            $v   = fn ($key) => $row[$key] ?? 0;
            $num = fn ($n) => number_format((float) $n);
            $dayLine = function (string $daysKey, string $totalKey) use ($row, $num) {
                $days  = (int) ($row[$daysKey] ?? 0);
                $total = $row[$totalKey] ?? null;
                return $total === null ? $num($days) : $num($days) . ' من ' . $num($total);
            };

            $html .= '<table border="1" cellpadding="6"><tr bgcolor="#2A4570" color="#ffffff">'
                . '<td align="center"><b>الطالب: ' . ($row['name'] ?? '-') . '</b></td></tr></table>';

            // الحضور والغياب
            $html .= $table(
                $section('الحضور والغياب', '#059669')
                . $line('1- العدد الكلي للجلسات', $num($v('att_total')), $base)
                . $line('2- عدد أيام الحضور', $dayLine('att_days', 'att_days_total'), $ok)
                . $line('3- عدد أيام الغياب', $num($v('att_absent')), $bad)
                . $line('4- عدد أيام التأخير', $num($v('att_late')), $warn)
                . $line('5- عدد أيام الغياب بعذر', $num($v('att_excused')), $info)
            );

            // التسميع (separate section)
            $html .= $table(
                $section('التسميع', '#16a085')
                . $line('1- عدد أيام التسميع', $dayLine('rec_days', 'rec_days_total'), $base)
                . $line('2- عدد الأيام التي لم يسمع بها', $num($v('not_recite_days')), $base)
            );

            // الحفظ الجديد
            $html .= $table(
                $section('الحفظ الجديد', '#182B48')
                . $line('1- عدد جلسات الحفظ الجديد', $num($v('new_memorization_sessions')), $base)
                . $line('2- عدد الجلسات النّاجحة', $num($v('new_memorization_passed')), $ok)
                . $line('3- عدد الجلسات الرّاسبة', $num($v('new_memorization_failed')), $bad)
                . $line('4- عدد صفحات الحفظ الجديد النّاجحة', $num($v('new_memorization_pages')), $ok)
                . $line('5- عدد صفحات الحفظ الجديد الرّاسبة', $num($v('new_memorization_failed_pages')), $bad)
                . $line('6- عدد الأجزاء المحفوظة', $num($v('new_memorization_parts')), $base)
            );

            // المراجعة
            $html .= $table(
                $section('المراجعة', '#2980b9')
                . $line('1- عدد جلسات المراجعة', $num($v('review_sessions')), $base)
                . $line('2- عدد الجلسات النّاجحة', $num($v('review_passed')), $ok)
                . $line('3- عدد الجلسات الرّاسبة', $num($v('review_failed')), $bad)
                . $line('4- عدد صفحات المراجعة النّاجحة', $num($v('review_pages')), $ok)
                . $line('5- عدد صفحات المراجعة الرّاسبة', $num($v('review_failed_pages')), $bad)
                . $line('6- عدد الأجزاء المراجعة', $num($v('review_parts')), $base)
            );

            // السرد
            if ($v('recitation_sessions') > 0) {
                $html .= $table(
                    $section('السرد', '#16a085')
                    . $line('1- عدد الجلسات', $num($v('recitation_sessions')), $base)
                    . $line('2- عدد الجلسات النّاجحة', $num($v('recitation_passed')), $ok)
                    . $line('3- عدد الجلسات الرّاسبة', $num($v('recitation_failed')), $bad)
                );
            }

            // الحفظ غير المتقن
            if ($v('unmastered_sessions') > 0) {
                $html .= $table(
                    $section('الحفظ غير المتقن', '#7c3aed')
                    . $line('1- عدد جلسات الحفظ غير المتقن', $num($v('unmastered_sessions')), $base)
                    . $line('2- عدد الجلسات النّاجحة', $num($v('unmastered_passed')), $ok)
                    . $line('3- عدد الجلسات الرّاسبة', $num($v('unmastered_failed')), $bad)
                    . $line('4- عدد صفحات الحفظ غير المتقن النّاجحة', $num($v('unmastered_pages')), $ok)
                    . $line('5- عدد صفحات الحفظ غير المتقن الرّاسبة', $num($v('unmastered_failed_pages')), $bad)
                    . $line('6- عدد الأجزاء غير المتقنة', $num($v('unmastered_parts')), $base)
                );
            }

            // المفصل
            if (!empty($row['mafsil_details'])) {
                $rows = $section('المفصل', '#d4a030');
                foreach ($row['mafsil_details'] as $i => $m) {
                    $passed = ($m['status'] ?? '') === 'passed';
                    $rows .= $line(
                        ($i + 1) . '- المفصل الذي تقدم له: ' . ($m['label'] ?? ''),
                        $m['status_label'] ?? '',
                        $passed ? $ok : $bad
                    );
                }
                $html .= $table($rows);
            }

            // الإجمالي
            $html .= $table(
                '<tr><td width="62%" bgcolor="#f1f5f9" color="#182B48"><b>إجمالي الجلسات</b></td>'
                . '<td width="38%" align="center" bgcolor="#182B48" color="#ffffff"><b>' . $num($v('total_sessions')) . '</b></td></tr>'
            );

            $html .= '<br/>';
        }

        return $html;
    }

    /**
     * Map raw recitation stats keys to Arabic labels for the PDF summary box.
     */
    protected function recitationStatsLabels(array $stats): array
    {
        return [
            'الإجمالي'    => $stats['total'] ?? 0,
            'ناجح'        => $stats['passed'] ?? 0,
            'راسب'        => $stats['failed'] ?? 0,
            'جارٍ'        => $stats['in_progress'] ?? 0,
            'نسبة النجاح' => ($stats['pass_rate'] ?? 0) . '%',
            'المتوسط'     => ($stats['avg_score'] ?? 0) . '%',
            'الأخطاء'     => $stats['total_errors'] ?? 0,
            'الترددات'    => $stats['total_hesitations'] ?? 0,
            'الصفحات'     => $stats['total_pages'] ?? 0,
        ];
    }

    /**
     * Map raw attendance stats keys to Arabic labels for the PDF summary box.
     */
    protected function attendanceStatsLabels(array $stats): array
    {
        return [
            'الإجمالي'    => $stats['total'] ?? 0,
            'حاضر'        => $stats['present'] ?? 0,
            'متأخر'       => $stats['late'] ?? 0,
            'غائب'        => $stats['absent'] ?? 0,
            'معذور'       => $stats['excused'] ?? 0,
            'نسبة الحضور' => ($stats['attendance_rate'] ?? 0) . '%',
        ];
    }

    /**
     * Arabic student-summary table for the recitation PDF, mirroring the
     * on-screen "ملخص الطلاب" table (including the not-recited days).
     */
    protected function recitationSummaryTablePdf(array $summary): string
    {
        $headers = [
            'الطالب', 'الجلسات', 'عدد الجلسات المفترض', 'نسبة النجاح', 'ناجح',
            'راسب', 'لم يكمل', 'أيام لم يسمع بها', 'الصفحات', 'نسبة أيام التسميع',
        ];

        $html = '<table border="1" cellpadding="3"><tr bgcolor="#182B48" color="white">';
        foreach ($headers as $header) {
            $html .= '<th align="center">' . $header . '</th>';
        }
        $html .= '</tr>';

        foreach ($summary as $row) {
            $rate = $row['rec_days_rate'] ?? null;
            $expected = $row['expected_sessions'] ?? null;

            $html .= '<tr>'
                . '<td align="right">' . htmlspecialchars((string) ($row['name'] ?? '—'), ENT_QUOTES, 'UTF-8') . '</td>'
                . '<td align="center">' . ($row['sessions'] ?? 0) . '</td>'
                . '<td align="center">' . (is_null($expected) ? '—' : $expected) . '</td>'
                . '<td align="center">' . ($row['pass_rate'] ?? 0) . '%</td>'
                . '<td align="center">' . ($row['passed'] ?? 0) . '</td>'
                . '<td align="center">' . ($row['fail_days'] ?? 0) . '</td>'
                . '<td align="center">' . ($row['incomplete'] ?? 0) . '</td>'
                . '<td align="center">' . ($row['not_recite_days'] ?? 0) . '</td>'
                . '<td align="center">' . ($row['pages'] ?? 0) . '</td>'
                . '<td align="center">' . (is_null($rate) ? '—' : $rate . '%') . '</td>'
                . '</tr>';
        }

        $html .= '</table>';

        return $html;
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
