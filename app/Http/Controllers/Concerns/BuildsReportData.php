<?php

namespace App\Http\Controllers\Concerns;

use Carbon\Carbon;
use Illuminate\Http\Request;

trait BuildsReportData
{
    /**
     * Role-aware context consumed by the shared report views.
     */
    protected function reportContext(bool $isSupervisor): array
    {
        return $isSupervisor ? [
            'sidebar'               => 'supervisor.partials.sidebar',
            'roleLabel'             => 'مشرف',
            'dashboardRoute'        => 'supervisor.dashboard',
            'reportsIndexRoute'     => 'supervisor.reports.index',
            'routePrefix'           => 'supervisor.reports',
            'studentRouteName'      => 'supervisor.students.show',
            'showSupervisorFilter'  => false,
        ] : [
            'sidebar'               => 'admin.partials.sidebar',
            'roleLabel'             => 'مدير',
            'dashboardRoute'        => 'admin.dashboard',
            'reportsIndexRoute'     => 'admin.reports.index',
            'routePrefix'           => 'admin.reports',
            'studentRouteName'      => 'admin.students.show',
            'showSupervisorFilter'  => true,
        ];
    }

    protected function buildRecitationData(Request $request, ?int $supervisorId): array
    {
        // Full set keeps the "لم يسمع" markers so the student summary can
        // count the days a student did not recite, and the results table can
        // list them next to the genuine sessions.
        $fullQuery    = $this->reports->recitationQuery($request, $supervisorId);
        $fullSessions = (clone $fullQuery)->get();

        // Genuine recitation sessions only, used by the KPI, charts and
        // exports so every aggregate "session" number agrees.
        $actualQuery = $this->reports->recitationQuery($request, $supervisorId, true);
        $allSessions = (clone $actualQuery)->get();

        // The results table lists every recorded session, including the
        // "لم يسمع" markers, so nothing is hidden from the report.
        $sessions = (clone $fullQuery)->latest('session_date')->paginate(20)->withQueryString();
        [$fromDate, $toDate, $period] = $this->reports->resolvePeriod($request);

        return array_merge([
            'sessions'          => $sessions,
            'allSessions'       => $allSessions,
            'fullSessions'      => $fullSessions,
            'stats'             => $this->reports->recitationStats($allSessions),
            'trend'             => $this->reports->recitationDailyTrend($allSessions),
            'top'               => $this->reports->topStudents($allSessions, 5),
            'typeDistribution'  => $this->reports->typeDistribution($allSessions),
            'summary'           => $this->reports->studentSummary($fullSessions, null, $fromDate, $toDate),
            'prevStats'         => $this->previousStats($request, $fromDate, $toDate, $supervisorId, 'recitation'),
            'fromDate'          => $fromDate,
            'toDate'            => $toDate,
            'period'            => $period,
        ], $this->reports->dropdownData($request, $supervisorId), $this->reportContext($supervisorId !== null));
    }

    protected function buildAttendanceData(Request $request, ?int $supervisorId): array
    {
        $query          = $this->reports->attendanceQuery($request, $supervisorId);
        $attendances    = (clone $query)->latest('date')->paginate(20)->withQueryString();
        $allAttendances = (clone $query)->get();
        [$fromDate, $toDate, $period] = $this->reports->resolvePeriod($request);

        return array_merge([
            'attendances'        => $attendances,
            'allAttendances'     => $allAttendances,
            'stats'              => $this->reports->attendanceStats($allAttendances),
            'trend'              => $this->reports->attendanceDailyTrend($allAttendances),
            'statusDistribution' => $this->attendanceStatusDistribution($allAttendances),
            'summary'            => $this->reports->studentSummary(collect(), $allAttendances, $fromDate, $toDate),
            'prevStats'          => $this->previousStats($request, $fromDate, $toDate, $supervisorId, 'attendance'),
            'fromDate'           => $fromDate,
            'toDate'             => $toDate,
            'period'             => $period,
        ], $this->reports->dropdownData($request, $supervisorId), $this->reportContext($supervisorId !== null));
    }

    /**
     * Resolve the selected period (quarter/half/year) and its metadata.
     */
    protected function aggregateMeta(Request $request, string $type): array
    {
        $year          = (int) ($request->year ?? Carbon::now()->year);
        $selectorValue = null;

        if ($type === 'quarter') {
            $selectorValue = (int) ($request->quarter ?? \App\Services\ReportService::quarterOf());
            $request->merge(['period' => 'quarter', 'year' => $year, 'quarter' => $selectorValue]);
            [$fromDate, $toDate] = \App\Services\ReportService::quarterRange($year, $selectorValue);
            $periodTitle = 'الربع ' . $this->arabicOrdinal($selectorValue) . ' ' . $year;
        } elseif ($type === 'half') {
            $selectorValue = (int) ($request->half ?? \App\Services\ReportService::halfOf());
            $request->merge(['period' => 'half', 'year' => $year, 'half' => $selectorValue]);
            [$fromDate, $toDate] = \App\Services\ReportService::halfRange($year, $selectorValue);
            $periodTitle = 'النصف ' . ($selectorValue === 1 ? 'الأول' : 'الثاني') . ' ' . $year;
        } else {
            $request->merge(['period' => 'year', 'year' => $year]);
            [$fromDate, $toDate] = \App\Services\ReportService::yearRange($year);
            $periodTitle = 'السنة ' . $year;
        }

        return compact('year', 'selectorValue', 'fromDate', 'toDate', 'periodTitle');
    }

    protected function buildAggregateData(Request $request, $fromDate, $toDate, ?int $supervisorId): array
    {
        $recQuery = $this->reports->recitationQuery($request, $supervisorId);
        $attQuery = $this->reports->attendanceQuery($request, $supervisorId);

        $fullSessions   = (clone $recQuery)->orderBy('session_date')->get();
        $allAttendances = (clone $attQuery)->orderBy('date')->get();

        // Genuine sessions only for the KPIs/charts/exports; the written
        // breakdown and summary still use the full set so "لم يسمع" days are
        // reported separately.
        $allSessions = $this->reports->actualRecitationSessions($fullSessions);

        return [
            'allSessions'        => $allSessions,
            'allAttendances'     => $allAttendances,
            'recStats'           => $this->reports->recitationStats($allSessions),
            'attStats'           => $this->reports->attendanceStats($allAttendances),
            'recTrend'           => $this->reports->recitationMonthlyTrend($allSessions),
            'attTrend'           => $this->reports->attendanceMonthlyTrend($allAttendances),
            'top'                => $this->reports->topStudents($allSessions, 5),
            'breakdown'          => $this->reports->studentBreakdown($fullSessions, $allAttendances, $toDate, $fromDate),
            'typeDistribution'   => $this->reports->typeDistribution($allSessions),
            'statusDistribution' => $this->attendanceStatusDistribution($allAttendances),
            'summary'            => $this->reports->studentSummary($fullSessions, $allAttendances, $fromDate, $toDate),
            'prevStats'          => [
                'rec' => $this->previousStats($request, $fromDate, $toDate, $supervisorId, 'recitation'),
                'att' => $this->previousStats($request, $fromDate, $toDate, $supervisorId, 'attendance'),
            ],
            'fromDate'           => $fromDate,
            'toDate'             => $toDate,
        ];
    }

    protected function aggregateResponse(Request $request, string $type, ?int $supervisorId, bool $isSupervisor, string $filenameBase)
    {
        $meta    = $this->aggregateMeta($request, $type);
        $payload = $this->buildAggregateData($request, $meta['fromDate'], $meta['toDate'], $supervisorId);
        $context = $this->reportContext($isSupervisor);

        if ($export = $request->export) {
            $title = match ($type) {
                'quarter' => 'التقرير الربعي — ' . $meta['periodTitle'],
                'half'    => 'التقرير النصف سنوي — ' . $meta['periodTitle'],
                default   => 'التقرير السنوي — ' . $meta['year'],
            };

            return $this->exporter->exportAggregate($export, $payload, [
                'title'    => $title,
                'subtitle' => 'الفترة: ' . $meta['fromDate']->format('Y/m/d') . ' - ' . $meta['toDate']->format('Y/m/d'),
                'filename' => $filenameBase . $type . '-' . $meta['year'] . ($meta['selectorValue'] ? '-S' . $meta['selectorValue'] : ''),
            ]);
        }

        return view('reports.aggregate', array_merge(
            $payload,
            $this->reports->dropdownData($request, $supervisorId),
            $context,
            [
                'periodType'    => $type,
                'year'          => $meta['year'],
                'selectorValue' => $meta['selectorValue'],
                'periodTitle'   => $meta['periodTitle'],
            ]
        ));
    }

    /**
     * Statistics for the immediately preceding period, mirroring the current
     * request's filters. Returns null for all-time periods.
     */
    private function previousStats(Request $request, $fromDate, $toDate, ?int $supervisorId, string $kind): ?array
    {
        [$prevFrom, $prevTo] = $this->reports->previousPeriodRange($fromDate, $toDate);
        if (!$prevFrom || !$prevTo) {
            return null;
        }

        $prevRequest = Request::create('/', 'GET', array_filter([
            'period'        => 'custom',
            'from_date'     => $prevFrom->toDateString(),
            'to_date'       => $prevTo->toDateString(),
            'supervisor_id' => $supervisorId ? null : $request->supervisor_id,
            'student_id'    => $request->student_id,
            'type'          => $request->type,
            'status'        => $request->status,
        ], fn ($v) => $v !== null && $v !== ''));

        if ($kind === 'recitation') {
            return $this->reports->recitationStats($this->reports->recitationQuery($prevRequest, $supervisorId)->get());
        }

        return $this->reports->attendanceStats($this->reports->attendanceQuery($prevRequest, $supervisorId)->get());
    }

    private function attendanceStatusDistribution($attendances): array
    {
        return [
            ['key' => 'present', 'label' => 'حاضر', 'color' => '#059669', 'count' => $attendances->where('status', 'present')->count()],
            ['key' => 'late',    'label' => 'متأخر', 'color' => '#f39c12', 'count' => $attendances->where('status', 'late')->count()],
            ['key' => 'absent',  'label' => 'غائب',  'color' => '#c0392b', 'count' => $attendances->where('status', 'absent')->count()],
            ['key' => 'excused', 'label' => 'بعذر',  'color' => '#3498db', 'count' => $attendances->where('status', 'excused')->count()],
        ];
    }

    private function arabicOrdinal(int $n): string
    {
        return [1 => 'الأول', 2 => 'الثاني', 3 => 'الثالث', 4 => 'الرابع'][$n] ?? (string) $n;
    }
}
