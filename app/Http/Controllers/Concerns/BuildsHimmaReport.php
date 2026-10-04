<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Student;
use App\Services\HimmaReportService;
use App\Services\ReportExporter;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Shared response/export plumbing for the "يوم الهمة" report pages, used by
 * both the admin and the supervisor controllers.
 */
trait BuildsHimmaReport
{
    protected HimmaReportService $himma;
    protected ReportExporter $himmaExporter;

    /**
     * Role-aware layout context consumed by the shared Himma report views.
     */
    protected function himmaContext(bool $isSupervisor): array
    {
        return $isSupervisor ? [
            'sidebar'              => 'supervisor.partials.sidebar',
            'roleLabel'            => 'مشرف',
            'dashboardRoute'       => 'supervisor.dashboard',
            'reportsIndexRoute'    => 'supervisor.reports.index',
            'routePrefix'          => 'supervisor.reports',
            'showSupervisorFilter' => false,
            'himmaIndexRoute'      => 'supervisor.yawm-al-himma.index',
        ] : [
            'sidebar'              => 'admin.partials.sidebar',
            'roleLabel'            => 'مدير',
            'dashboardRoute'       => 'admin.dashboard',
            'reportsIndexRoute'    => 'admin.reports.index',
            'routePrefix'          => 'admin.reports',
            'showSupervisorFilter' => true,
            'himmaIndexRoute'      => 'admin.yawm-al-himma.index',
        ];
    }

    protected function himmaOverviewResponse(Request $request, bool $isSupervisor)
    {
        [$userId, $name] = $this->himmaScope($isSupervisor);
        $data = $this->himma->overview($request, $userId, $name);
        $data = array_merge($data, $this->himmaContext($isSupervisor));

        if ($export = $request->export) {
            return $this->himmaExporter->exportHimma($export, $this->himmaOverviewExport($data), [
                'title'    => 'تقرير يوم الهمة — نظرة شاملة',
                'subtitle' => $this->himmaPeriodLabel($data['fromDate'], $data['toDate'], $data['period']),
                'filename' => 'himma-overview',
            ]);
        }

        return view('reports.himma', $data);
    }

    protected function himmaDayResponse(Request $request, string $date, bool $isSupervisor)
    {
        [$userId, $name] = $this->himmaScope($isSupervisor);
        $data = $this->himma->day($request, $date, $userId, $name);
        $data = array_merge($data, $this->himmaContext($isSupervisor));

        if ($export = $request->export) {
            return $this->himmaExporter->exportHimma($export, $this->himmaDayExport($data), [
                'title'    => 'تقرير يوم الهمة — ' . $data['day']->format('Y/m/d'),
                'subtitle' => $this->himmaPeriodLabel(null, null, 'custom'),
                'filename' => 'himma-day-' . $data['day']->format('Y-m-d'),
            ]);
        }

        return view('reports.himma-day', $data);
    }

    protected function himmaStudentResponse(Request $request, Student $student, bool $isSupervisor)
    {
        [$userId, $name] = $this->himmaScope($isSupervisor);
        $data = $this->himma->student($request, $student, $userId, $name);
        $data = array_merge($data, $this->himmaContext($isSupervisor));

        if ($export = $request->export) {
            return $this->himmaExporter->exportHimma($export, $this->himmaStudentExport($data), [
                'title'    => 'تقرير يوم الهمة — ' . ($student->user->name ?? $student->name),
                'subtitle' => $this->himmaPeriodLabel($data['fromDate'], $data['toDate'], $data['period']),
                'filename' => 'himma-student-' . $student->id,
            ]);
        }

        return view('reports.himma-student', $data);
    }

    protected function himmaScope(bool $isSupervisor): array
    {
        return $isSupervisor
            ? [auth()->id(), auth()->user()->name]
            : [null, null];
    }

    protected function himmaOverviewExport(array $data): array
    {
        $dayRows = $data['days']->map(fn ($d) => [
            $d['date'],
            Carbon::parse($d['date'])->translatedFormat('l'),
            $d['sessions'],
            $d['students'],
            $d['parts'],
            $d['errors'],
            $d['hesitations'],
            $d['score'],
        ])->all();

        $studentRows = $data['students']->values()->map(fn ($s, $i) => [
            $i + 1,
            $s['name'],
            $s['sessions'],
            $s['parts'],
            $s['errors'],
            $s['hesitations'],
            $s['score'],
        ])->all();

        return [
            'stats'  => $this->himmaStatsLabels($data['stats']),
            'tables' => [
                [
                    'title'   => 'ملخص الأيام',
                    'headers' => ['التاريخ', 'اليوم', 'الجلسات', 'الطلاب', 'الأجزاء', 'الأخطاء', 'الترددات', 'النقاط'],
                    'rows'    => $dayRows,
                ],
                [
                    'title'   => 'ترتيب الطلاب',
                    'headers' => ['الترتيب', 'الطالب', 'الجلسات', 'الأجزاء', 'الأخطاء', 'الترددات', 'النقاط'],
                    'rows'    => $studentRows,
                ],
            ],
        ];
    }

    protected function himmaDayExport(array $data): array
    {
        $sessionRows = $data['sessions']->values()->map(function ($s, $i) {
            $precision = $this->himma->sessionPrecision($s);
            return [
                $i + 1,
                $s->student_name,
                $s->supervisor_name ?: '—',
                is_array($s->parts) ? implode('، ', $s->parts) : '—',
                (int) $s->total_errors,
                (int) $s->total_hesitations,
                round($precision['score'], 2),
            ];
        })->all();

        $studentRows = $data['students']->values()->map(fn ($s, $i) => [
            $i + 1,
            $s['name'],
            $s['sessions'],
            $s['parts'],
            $s['errors'],
            $s['hesitations'],
            $s['score'],
        ])->all();

        return [
            'stats'  => $this->himmaStatsLabels($data['stats']),
            'tables' => [
                [
                    'title'   => 'جلسات يوم ' . $data['day']->format('Y/m/d'),
                    'headers' => ['#', 'الطالب', 'المشرف', 'الأجزاء', 'الأخطاء', 'الترددات', 'النقاط'],
                    'rows'    => $sessionRows,
                ],
                [
                    'title'   => 'ترتيب طلاب اليوم',
                    'headers' => ['الترتيب', 'الطالب', 'الجلسات', 'الأجزاء', 'الأخطاء', 'الترددات', 'النقاط'],
                    'rows'    => $studentRows,
                ],
            ],
        ];
    }

    protected function himmaStudentExport(array $data): array
    {
        $sessionRows = $data['sessions']->values()->map(function ($s) {
            $precision = $this->himma->sessionPrecision($s);
            return [
                $s->session_date?->format('Y/m/d') ?? '—',
                $s->session_date?->translatedFormat('l') ?? '—',
                $s->supervisor_name ?: '—',
                is_array($s->parts) ? implode('، ', $s->parts) : '—',
                (int) $s->total_errors,
                (int) $s->total_hesitations,
                round($precision['score'], 2),
            ];
        })->all();

        return [
            'stats'  => $this->himmaStatsLabels($data['stats']),
            'tables' => [
                [
                    'title'   => 'جلسات الطالب',
                    'headers' => ['التاريخ', 'اليوم', 'المشرف', 'الأجزاء', 'الأخطاء', 'الترددات', 'النقاط'],
                    'rows'    => $sessionRows,
                ],
            ],
        ];
    }

    protected function himmaStatsLabels(array $stats): array
    {
        return [
            'إجمالي الجلسات'  => $stats['total_sessions'] ?? 0,
            'عدد الطلاب'      => $stats['total_students'] ?? 0,
            'عدد الأيام'      => $stats['total_days'] ?? 0,
            'إجمالي الأجزاء'  => $stats['total_parts'] ?? 0,
            'إجمالي الأخطاء'  => $stats['total_errors'] ?? 0,
            'إجمالي الترددات' => $stats['total_hesitations'] ?? 0,
            'متوسط النقاط'    => $stats['avg_score'] ?? 0,
        ];
    }

    protected function himmaPeriodLabel($fromDate, $toDate, ?string $period): string
    {
        if ($fromDate && $toDate) {
            return 'الفترة: ' . $fromDate->format('Y/m/d') . ' - ' . $toDate->format('Y/m/d');
        }

        return 'الفترة: كل الفترات';
    }
}
