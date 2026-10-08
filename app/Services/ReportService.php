<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\QuranPage;
use App\Models\QuranPart;
use App\Models\RecitationSession;
use App\Models\Student;
use App\Models\Supervisor;
use App\Models\User;
use App\Providers\SettingsServiceProvider;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ReportService
{
    public const PERIOD_TODAY   = 'today';
    public const PERIOD_WEEK    = 'week';
    public const PERIOD_MONTH   = 'month';
    public const PERIOD_QUARTER = 'quarter';
    public const PERIOD_HALF    = 'half';
    public const PERIOD_YEAR    = 'year';
    public const PERIOD_ALL     = 'all';
    public const PERIOD_CUSTOM  = 'custom';

    /**
     * Resolve the current calendar quarter (1-4) from a date (defaults to today).
     */
    public static function quarterOf(?Carbon $date = null): int
    {
        $date = $date ?? Carbon::now();
        return (int) ceil($date->month / 3);
    }

    /**
     * Resolve the current calendar half (1-2) from a date (defaults to today).
     */
    public static function halfOf(?Carbon $date = null): int
    {
        $date = $date ?? Carbon::now();
        return $date->month <= 6 ? 1 : 2;
    }

    /**
     * Return the start/end of a given (year, quarter) pair.
     */
    public static function quarterRange(int $year, int $quarter): array
    {
        $quarter = max(1, min(4, $quarter));
        $startMonth = ($quarter - 1) * 3 + 1;
        $start = Carbon::create($year, $startMonth, 1)->startOfMonth();
        $end   = $start->copy()->addMonths(2)->endOfMonth();
        return [$start, $end];
    }

    /**
     * Return the start/end of a given (year, half) pair.
     */
    public static function halfRange(int $year, int $half): array
    {
        $half = max(1, min(2, $half));
        $startMonth = ($half - 1) * 6 + 1;
        $start = Carbon::create($year, $startMonth, 1)->startOfMonth();
        $end   = $start->copy()->addMonths(5)->endOfMonth();
        return [$start, $end];
    }

    /**
     * Return the start/end of a given year.
     */
    public static function yearRange(int $year): array
    {
        $start = Carbon::create($year, 1, 1)->startOfYear();
        $end   = Carbon::create($year, 12, 31)->endOfYear();
        return [$start, $end];
    }

    public function resolvePeriod(Request $request): array
    {
        $period = $request->period ?? self::PERIOD_ALL;

        return match ($period) {
            self::PERIOD_TODAY => [Carbon::now()->startOfDay(),  Carbon::now()->endOfDay(),  $period],
            self::PERIOD_WEEK  => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek(),  $period],
            self::PERIOD_MONTH => [Carbon::now()->startOfMonth(),Carbon::now()->endOfMonth(),$period],
            self::PERIOD_QUARTER => [
                ...self::quarterRange(
                    (int) ($request->year ?? Carbon::now()->year),
                    (int) ($request->quarter ?? self::quarterOf())
                ),
                self::PERIOD_QUARTER,
            ],
            self::PERIOD_HALF => [
                ...self::halfRange(
                    (int) ($request->year ?? Carbon::now()->year),
                    (int) ($request->half ?? self::halfOf())
                ),
                self::PERIOD_HALF,
            ],
            self::PERIOD_YEAR => [
                ...self::yearRange((int) ($request->year ?? Carbon::now()->year)),
                self::PERIOD_YEAR,
            ],
            self::PERIOD_CUSTOM => [
                Carbon::parse($request->from_date ?? Carbon::now()->startOfMonth()->toDateString())->startOfDay(),
                Carbon::parse($request->to_date   ?? Carbon::now()->toDateString())->endOfDay(),
                $period,
            ],
            default => [null, null, $period],
        };
    }

    /**
     * Count the dates within a period that fall on the configured weekdays
     * (e.g. work_days / recitation_days settings). Returns null for an
     * open-ended (all-time) period so callers can omit the denominator.
     */
    public function configuredDayCount(?Carbon $fromDate, ?Carbon $toDate, string $settingKey): ?int
    {
        if (!$fromDate || !$toDate) {
            return null;
        }

        $dayNumbers = SettingsServiceProvider::carbonDaysForDayNames(
            $this->configuredDayNames($settingKey)
        );

        if (empty($dayNumbers)) {
            return 0;
        }

        $count  = 0;
        $cursor = $fromDate->copy()->startOfDay();
        $end    = $toDate->copy()->startOfDay();

        while ($cursor->lte($end)) {
            if (in_array((int) $cursor->dayOfWeek, $dayNumbers, true)) {
                $count++;
            }
            $cursor->addDay();
        }

        return $count;
    }

    /**
     * Resolve the configured day-name list for a settings key, tolerating
     * contexts where the config repository is not bound (e.g. pure unit
     * tests that do not boot the framework).
     */
    private function configuredDayNames(string $settingKey): string
    {
        $default = $settingKey === 'work_days'
            ? SettingsServiceProvider::DEFAULT_WORK_DAYS
            : SettingsServiceProvider::DEFAULT_RECITATION_DAYS;

        try {
            return (string) config('app.' . $settingKey, $default);
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * Total configured working days inside the period.
     */
    public function workDaysInRange(?Carbon $fromDate, ?Carbon $toDate): ?int
    {
        return $this->configuredDayCount($fromDate, $toDate, 'work_days');
    }

    /**
     * Total configured recitation days inside the period.
     */
    public function recitationDaysInRange(?Carbon $fromDate, ?Carbon $toDate): ?int
    {
        return $this->configuredDayCount($fromDate, $toDate, 'recitation_days');
    }

    public function recitationQuery(Request $request, ?int $supervisorScopeUserId = null, bool $excludeNotRecite = false): Builder
    {
        [$fromDate, $toDate, $period] = $this->resolvePeriod($request);

        $query = RecitationSession::with(['student.user', 'student.supervisor', 'supervisor', 'part', 'pages.mafsilPosition']);

        if ($excludeNotRecite) {
            $query->where('type', '!=', RecitationSession::TYPE_NOT_RECITE)
                  ->where('status', '!=', RecitationSession::STATUS_NOT_RECITE);
        }

        if ($supervisorScopeUserId) {
            $query->whereHas('student', fn ($q) => $q->where('supervisor_id', $supervisorScopeUserId));
        } elseif ($request->supervisor_id) {
            $query->whereHas('student', fn ($q) => $q->where('supervisor_id', $request->supervisor_id));
        }

        if ($request->student_id) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->type && in_array($request->type, ['new_memorization', 'review', 'unmastered', 'recitation', 'mafsil'], true)) {
            $query->where('type', $request->type);
        }

        if ($request->status && in_array($request->status, ['passed', 'failed', 'in_progress'], true)) {
            if ($request->status === 'failed') {
                // Not-completed (لم يكمل) sessions are shown with the failing ones.
                $query->whereIn('status', ['failed', 'failed_continued', 'incomplete']);
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($fromDate && $toDate) {
            $query->whereBetween('session_date', [$fromDate->toDateString(), $toDate->toDateString()]);
        }

        return $query;
    }

    public function attendanceQuery(Request $request, ?int $supervisorScopeUserId = null): Builder
    {
        [$fromDate, $toDate] = $this->resolvePeriod($request);

        $query = Attendance::with(['student.user', 'student.supervisor', 'supervisor']);

        if ($supervisorScopeUserId) {
            $query->where('supervisor_id', $supervisorScopeUserId);
        } elseif ($request->supervisor_id) {
            $query->whereHas('student', fn ($q) => $q->where('supervisor_id', $request->supervisor_id));
        }

        if ($request->student_id) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->status && in_array($request->status, ['present', 'absent', 'late', 'excused', 'holiday'], true)) {
            $query->where('status', $request->status);
        }

        if ($fromDate && $toDate) {
            $query->whereBetween('date', [$fromDate->toDateString(), $toDate->toDateString()]);
        }

        return $query;
    }

    /**
     * A "لم يسمع" marker: a session that records a recitation day the
     * student did not recite. It is not a real recitation session.
     */
    private function isNotRecite($session): bool
    {
        return ($session->type ?? null) === RecitationSession::TYPE_NOT_RECITE
            || ($session->status ?? null) === RecitationSession::STATUS_NOT_RECITE;
    }

    /**
     * Keep only genuine recitation sessions (drops "لم يسمع" markers).
     * In-progress sessions are kept.
     */
    public function actualRecitationSessions(Collection $sessions): Collection
    {
        return $sessions
            ->reject(fn ($s) => $this->isNotRecite($s))
            ->values();
    }

    public function recitationStats(Collection $sessions): array
    {
        // "لم يسمع" markers are not recitation sessions, so they are kept
        // out of the headline total. In-progress sessions still count.
        $sessions = $this->actualRecitationSessions($sessions);

        $total    = $sessions->count();
        $passedSessions = $sessions->where('status', 'passed');
        $passed   = $passedSessions->count();
        // Sessions that were not completed (لم يكمل) are reported with the
        // failing ones.
        $failedStatuses = ['failed', 'failed_continued', 'incomplete'];
        $failed   = $sessions->whereIn('status', $failedStatuses)->count();
        $decided  = $passed + $failed;
        $inProgress = $sessions->where('status', 'in_progress')->count();

        // Decided sessions (passed + failed) have complete error/hesitation
        // data; in-progress sessions only carry partial numbers.
        $decidedSessions = $sessions->whereIn('status', array_merge(['passed'], $failedStatuses));

        $avgScore = $decidedSessions->whereNotNull('final_score')->avg('final_score');
        $passRate = $decided > 0 ? round(($passed / $decided) * 100, 1) : 0.0;

        return [
            'total'        => $total,
            'passed'       => $passed,
            'failed'       => $failed,
            'in_progress'  => $inProgress,
            'avg_score'    => $avgScore ? round($avgScore, 1) : 0.0,
            'pass_rate'    => $passRate,
            'total_errors'      => (int) $decidedSessions->sum('total_errors'),
            'total_hesitations' => (int) $decidedSessions->sum('total_hesitations'),
            // Accomplished pages: only sessions the student actually passed.
            'total_pages'       => (int) $passedSessions->sum('total_pages'),
        ];
    }

    public function attendanceStats(Collection $attendances): array
    {
        $total   = $attendances->count();
        $present = $attendances->where('status', 'present')->count();
        $late    = $attendances->where('status', 'late')->count();
        $absent  = $attendances->where('status', 'absent')->count();
        $excused = $attendances->where('status', 'excused')->count();

        // Late arrivals are still attendance days. "attendance" is the
        // headline metric (present + late) used by the KPI card and the
        // rate numerator, while "present" stays the exclusive status used
        // by the breakdowns (donut, exports, written report) so those
        // partitions still add up to the total.
        $attendance = $present + $late;

        // Excused days are excluded from the rate denominator so they
        // neither reward nor penalize the student's attendance rate.
        $effective = $total - $excused;

        return [
            'total'           => $total,
            'present'         => $present,
            'attendance'      => $attendance,
            'late'            => $late,
            'absent'          => $absent,
            'excused'         => $excused,
            'attendance_rate' => $effective > 0 ? round(($attendance / $effective) * 100, 1) : 0.0,
        ];
    }

    public function recitationDailyTrend(Collection $sessions): array
    {
        return $sessions
            ->groupBy(fn ($s) => $s->session_date?->format('Y-m-d'))
            ->sortKeys()
            ->map(function ($group, $date) {
                $total = $group->count();
                $passed = $group->where('status', 'passed')->count();
                $decided = $passed + $group->whereIn('status', ['failed', 'failed_continued', 'incomplete'])->count();
                return [
                    'date' => $date,
                    'total' => $total,
                    'passed' => $passed,
                    'pass_rate' => $decided > 0 ? round(($passed / $decided) * 100, 1) : 0.0,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Aggregate by year-month for multi-month periods (e.g. quarterly view).
     */
    public function recitationMonthlyTrend(Collection $sessions): array
    {
        return $sessions
            ->groupBy(fn ($s) => $s->session_date?->format('Y-m'))
            ->sortKeys()
            ->map(function ($group, $month) {
                $total = $group->count();
                $passed = $group->where('status', 'passed')->count();
                $failed = $group->whereIn('status', ['failed', 'failed_continued', 'incomplete'])->count();
                $decided = $passed + $failed;
                $avgScore = $group->whereNotNull('final_score')->avg('final_score');
                return [
                    'month' => $month,
                    'total' => $total,
                    'passed' => $passed,
                    'failed' => $failed,
                    'pass_rate' => $decided > 0 ? round(($passed / $decided) * 100, 1) : 0.0,
                    'avg_score' => $avgScore ? round($avgScore, 1) : 0.0,
                ];
            })
            ->values()
            ->toArray();
    }

    public function attendanceDailyTrend(Collection $attendances): array
    {
        return $attendances
            ->groupBy(fn ($a) => $a->date?->format('Y-m-d'))
            ->sortKeys()
            ->map(function ($group, $date) {
                $total = $group->count();
                $present = $group->whereIn('status', ['present', 'late'])->count();
                $effective = $total - $group->where('status', 'excused')->count();
                return [
                    'date' => $date,
                    'total' => $total,
                    'present' => $present,
                    'rate' => $effective > 0 ? round(($present / $effective) * 100, 1) : 0.0,
                ];
            })
            ->values()
            ->toArray();
    }

    public function attendanceMonthlyTrend(Collection $attendances): array
    {
        return $attendances
            ->groupBy(fn ($a) => $a->date?->format('Y-m'))
            ->sortKeys()
            ->map(function ($group, $month) {
                $total = $group->count();
                $present = $group->where('status', 'present')->count();
                $absent = $group->where('status', 'absent')->count();
                $late = $group->where('status', 'late')->count();
                $excused = $group->where('status', 'excused')->count();
                return [
                    'month' => $month,
                    'total' => $total,
                    'present' => $present,
                    'absent' => $absent,
                    'late' => $late,
                    'excused' => $excused,
                    'rate' => ($total - $excused) > 0 ? round((($present + $late) / ($total - $excused)) * 100, 1) : 0.0,
                ];
            })
            ->values()
            ->toArray();
    }

    public function topStudents(Collection $sessions, int $limit = 5, int $minSessions = 3): array
    {
        $ranked = $sessions
            ->groupBy('student_id')
            ->map(function ($group) {
                $name = $group->first()?->student?->user?->name ?? '—';
                $total = $group->count();
                $passed = $group->where('status', 'passed')->count();
                $decided = $passed + $group->whereIn('status', ['failed', 'failed_continued', 'incomplete'])->count();
                $avg = $group->whereNotNull('final_score')->avg('final_score');
                return [
                    'name'      => $name,
                    'total'     => $total,
                    'passed'    => $passed,
                    'decided'   => $decided,
                    'pass_rate' => $decided > 0 ? round(($passed / $decided) * 100, 1) : 0.0,
                    'avg_score' => $avg ? round($avg, 1) : 0.0,
                ];
            });

        // Require a minimum number of decided sessions so a student with a
        // single high score does not outrank consistently performing ones.
        $qualified = $ranked->filter(fn ($row) => $row['decided'] >= $minSessions);
        if ($qualified->isEmpty()) {
            $qualified = $ranked;
        }

        return $qualified
            ->sortByDesc('avg_score')
            ->take($limit)
            ->values()
            ->toArray();
    }

    /**
     * Per-student textual breakdown of recitation sessions by type.
     * Used by the admin/supervisor quarter / half / year reports to render
     * a written summary: how many parts each student recited as mafsil,
     * how many new parts they memorized, how many review/recitation
     * sessions they had, etc.
     */
    public function studentBreakdown(Collection $sessions, ?Collection $attendances = null, ?Carbon $untilDate = null, ?Carbon $fromDate = null): array
    {
        $attendanceByStudent = $this->attendanceSummaryByStudent($attendances ?? collect());

        // Period denominators driven by the work_days / recitation_days
        // settings. Null for open-ended periods (no "X من Y" total then).
        $attDaysTotal = $this->workDaysInRange($fromDate, $untilDate);
        $recDaysTotal = $this->recitationDaysInRange($fromDate, $untilDate);

        // Load memorization + recitation sessions (with pages) up to the end
        // of the selected period for every student in the breakdown, in a
        // single batched query. Page mastery for "الأجزاء المحفوظة" must be
        // evaluated against the student's full history up to that date: the
        // new-memorization sessions that prove each page are usually spread
        // across multiple periods, and the qualifying سرد may also have been
        // passed in an earlier period. Capping at $untilDate keeps a past
        // period report from counting pages/parts completed later. When no
        // DB is available (e.g. isolated unit tests with plain objects), the
        // returned map is empty and callers fall back to the in-period
        // $group, preserving previous behavior.
        $historyByStudent = $this->loadAllTimeMemorizationHistory(
            $sessions->pluck('student_id')->filter()->unique()->values(),
            $untilDate
        );

        return $sessions
            ->groupBy('student_id')
            ->map(function ($group, $studentId) use ($attendanceByStudent, $historyByStudent, $attDaysTotal, $recDaysTotal) {
                $student = $group->first()?->student;

                // A session that was not completed (لم يكمل) is reported with
                // the failing sessions, so each type reconciles as
                // sessions = passed + failed. Only truly in-progress/not-heard
                // sessions are left out of the report.
                $failedStatuses = [
                    RecitationSession::STATUS_FAILED,
                    RecitationSession::STATUS_FAILED_CONTINUED,
                    RecitationSession::STATUS_INCOMPLETE,
                ];
                $decided = $group->whereIn('status', array_merge([RecitationSession::STATUS_PASSED], $failedStatuses));
                // Pages and parts are accomplishments: only sessions the
                // student actually passed are counted.
                $passed  = $group->where('status', 'passed');

                $review        = $decided->where('type', 'review');
                $newMemorize   = $decided->where('type', 'new_memorization');
                $recitation    = $decided->where('type', 'recitation');
                $unmastered    = $decided->where('type', 'unmastered');
                $mafsil        = $decided->where('type', 'mafsil');

                // Days the student did not recite (لم يسمع). These are not
                // "decided" sessions, so they are reported separately as a
                // distinct day count. They are still counted in the period's
                // grand total of sessions so days the student did not recite
                // are not silently dropped from إجمالي الجلسات.
                $notRecite = $group->filter(
                    fn ($s) => $s->type === RecitationSession::TYPE_NOT_RECITE
                        || $s->status === RecitationSession::STATUS_NOT_RECITE
                );
                $notReciteDays = $notRecite
                    ->map(fn ($s) => $this->sessionDate($s)?->toDateString())
                    ->filter()
                    ->unique()
                    ->count();

                // Days the student actually recited: distinct session dates
                // excluding the auto-generated "لم يسمع" markers.
                $reciteDays = $group
                    ->reject(fn ($s) => $s->type === RecitationSession::TYPE_NOT_RECITE
                        || $s->status === RecitationSession::STATUS_NOT_RECITE)
                    ->map(fn ($s) => $this->sessionDate($s)?->toDateString())
                    ->filter()
                    ->unique()
                    ->count();

                $attDays = (int) (($attendanceByStudent[$studentId]['att_days'] ?? 0));
                $attDaysRate = ($attDaysTotal !== null && $attDaysTotal > 0)
                    ? round(($attDays / $attDaysTotal) * 100, 1)
                    : null;
                $recDaysRate = ($recDaysTotal !== null && $recDaysTotal > 0)
                    ? round(($reciteDays / $recDaysTotal) * 100, 1)
                    : null;

                $recitationPassed  = $passed->where('type', 'recitation');
                $mafsilPassed      = $passed->where('type', 'mafsil');

                return array_merge([
                    'student_id'                  => $student?->id,
                    'name'                        => $student?->user?->name ?? '—',
                    'total_sessions'              => $decided->count() + $notReciteDays,
                    'sessions_without_not_recite' => $decided->count(),
                    'not_recite_days'             => $notReciteDays,
                    'att_days'                    => $attDays,
                    'att_days_total'              => $attDaysTotal,
                    'att_days_rate'               => $attDaysRate,
                    'rec_days'                    => $reciteDays,
                    'rec_days_total'              => $recDaysTotal,
                    'rec_days_rate'               => $recDaysRate,
                    'review_sessions'             => $review->count(),
                    'review_passed'               => $review->where('status', RecitationSession::STATUS_PASSED)->count(),
                    'review_failed'               => $review->whereIn('status', $failedStatuses)->count(),
                    'review_pages'                => $this->countPassedPages($review),
                    'review_failed_pages'         => $this->countFailedPages($review),
                    'review_parts'                => $this->countFullyReviewedParts($group),
                    'new_memorization_sessions'   => $newMemorize->count(),
                    'new_memorization_passed'     => $newMemorize->where('status', RecitationSession::STATUS_PASSED)->count(),
                    'new_memorization_failed'     => $newMemorize->whereIn('status', $failedStatuses)->count(),
                    'new_memorization_parts'      => $this->countFullyMemorizedParts($group, $historyByStudent[$studentId] ?? null),
                    'new_memorization_pages'      => $this->countPassedPages($newMemorize),
                    'new_memorization_failed_pages' => $this->countFailedPages($newMemorize),
                    'recitation_sessions'         => $recitation->count(),
                    'recitation_passed'           => $recitation->where('status', RecitationSession::STATUS_PASSED)->count(),
                    'recitation_failed'           => $recitation->whereIn('status', $failedStatuses)->count(),
                    'recitation_pages'            => $this->sumPages($recitationPassed),
                    'unmastered_sessions'         => $unmastered->count(),
                    'unmastered_passed'           => $unmastered->where('status', RecitationSession::STATUS_PASSED)->count(),
                    'unmastered_failed'           => $unmastered->whereIn('status', $failedStatuses)->count(),
                    'unmastered_pages'            => $this->countPassedPages($unmastered),
                    'unmastered_failed_pages'     => $this->countFailedPages($unmastered),
                    'unmastered_parts'            => $this->countFullyUnmasteredParts($group),
                    'mafsil_sessions'             => $mafsil->count(),
                    'mafsil_parts'                => $mafsilPassed->pluck('part_id')->filter()->unique()->count(),
                    'mafsil_pages'                => $this->sumPages($mafsilPassed),
                    'mafsil_details'              => $mafsil->map(function ($s) {
                        $mafsilType = $s->mafsil_type ?? null;
                        $label = $mafsilType ? ('مفصل ' . $mafsilType) : ($s->parts_label ?? null);
                        if (!$label || $label === '-' || $label === '—') {
                            $label = 'مفصل';
                        }

                        $statusLabel = $s->status_label
                            ?? ($s->status === RecitationSession::STATUS_PASSED ? 'ناجح' : 'راسب');

                        return [
                            'label'        => $label,
                            'status'       => $s->status,
                            'status_label' => $statusLabel,
                        ];
                    })->values()->toArray(),
                ], $this->emptyAttendanceSummary(), $attendanceByStudent[$studentId] ?? []);
            })
            ->sortBy('name')
            ->values()
            ->toArray();
    }

    /**
     * Sum pages across a session collection. Prefers the persisted
     * `total_pages` column and falls back to (end_page - start_page + 1)
     * when null. Treats missing start/end as 0.
     */
    private function sumPages(Collection $sessions): int
    {
        return (int) $sessions->sum(fn ($s) => $this->sessionPages($s));
    }

    private function sessionPages($s): int
    {
        if ($s->total_pages !== null) {
            return (int) $s->total_pages;
        }
        if ($s->start_page && $s->end_page) {
            return max(0, ((int) $s->end_page) - ((int) $s->start_page) + 1);
        }
        return 0;
    }

    /**
     * Normalise a session's date to Carbon (Eloquent casts it, plain test
     * objects may not). Returns null when the date is missing/unparseable so
     * callers can skip ordering checks.
     */
    private function sessionDate($session): ?Carbon
    {
        $date = $session->session_date ?? null;

        if (!$date) {
            return null;
        }

        if ($date instanceof Carbon) {
            return $date;
        }

        try {
            return Carbon::parse($date);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Safely resolve a session's pages as a collection, tolerating Eloquent
     * models (lazy/eager loaded relation) and plain objects without a pages
     * property (as used in unit tests).
     */
    private function pagesOf($session): Collection
    {
        $pages = $session->pages ?? null;

        if ($pages instanceof Collection) {
            return $pages;
        }

        if (is_array($pages)) {
            return collect($pages);
        }

        return collect();
    }

    /**
     * Count successfully memorized pages (recitation_pages.is_passed = true)
     * across the given sessions, regardless of the session's own status.
     * Falls back to the session page span when no page rows exist and the
     * session itself passed.
     */
    private function countPassedPages(Collection $sessions): int
    {
        return (int) $sessions->sum(function ($s) {
            $pages = $this->pagesOf($s);

            if ($pages->isEmpty()) {
                return $s->status === RecitationSession::STATUS_PASSED ? $this->sessionPages($s) : 0;
            }

            return $pages->where('is_passed', true)->count();
        });
    }

    /**
     * Count pages that were not mastered (recitation_pages.is_passed = false)
     * across the given sessions, regardless of the session's own status.
     */
    private function countFailedPages(Collection $sessions): int
    {
        return (int) $sessions->sum(function ($s) {
            $pages = $this->pagesOf($s);

            if ($pages->isEmpty()) {
                return 0;
            }

            return $pages->where('is_passed', false)->count();
        });
    }

    /**
     * Map every Quran page number to its part id (cached per request).
     */
    private function partIdByPageNumber(): array
    {
        static $map = null;

        if ($map !== null) {
            return $map;
        }

        try {
            $map = QuranPage::query()->pluck('part_id', 'page_number')->all();
        } catch (\Throwable $e) {
            // No database/table available (e.g. isolated unit tests): fall
            // back to the session's part_id via resolvePartId().
            $map = [];
        }

        return $map;
    }

    /**
     * Resolve the part a page record belongs to. Uses the accurate page→part
     * map when available and falls back to the session's part_id (e.g. in
     * tests where quran_pages is not seeded). This makes sessions that span
     * a part boundary attribute each page to its real part.
     */
    private function resolvePartId($session, int $pageNumber): ?int
    {
        return $this->partIdByPageNumber()[$pageNumber] ?? $session->part_id;
    }

    /**
     * Load new-memorization + recitation sessions (with their pages) for the
     * given student ids, grouped by student_id, optionally capped at
     * $untilDate (the end of the selected report period). Returns an array
     * [student_id => Collection] ready to be merged with any period-scoped
     * groups. Returns [] when no database is reachable (e.g. isolated unit
     * tests that pass plain objects) so callers can fall back to their
     * in-period session groups.
     */
    private function loadAllTimeMemorizationHistory(Collection $studentIds, ?Carbon $untilDate = null): array
    {
        if ($studentIds->isEmpty()) {
            return [];
        }

        try {
            return RecitationSession::whereIn('student_id', $studentIds)
                ->whereIn('type', [RecitationSession::TYPE_NEW_MEMORIZATION, RecitationSession::TYPE_RECITATION])
                ->when($untilDate, fn ($q) => $q->where('session_date', '<=', $untilDate->toDateString()))
                ->with('pages')
                ->get()
                ->groupBy('student_id')
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Count the parts a student has fully memorized and then passed a
     * recitation (سرد) for. A part qualifies only when:
     *  - every page in the part has at least one successful page record in a
     *    new-memorization session (a page passed only during سرد does not
     *    prove memorization), and
     *  - a passed recitation (سرد) session for that same part happened on or
     *    after the day the last of those pages was passed. A سرد that was
     *    passed before the part's pages were completed does NOT qualify.
     *
     * Evaluated against the student's history up to the end of the report
     * period when available, so this works regardless of which period holds
     * the memorization sessions or the qualifying سرد. The parts a سرد
     * qualifies are derived from its pages as well as its optional part_id,
     * because part_id is nullable and a سرد can span a part boundary.
     * Pass $history = null (e.g. when no DB is available) to fall back to
     * the in-period $group alone.
     */
    private function countFullyMemorizedParts(Collection $group, ?Collection $history = null): int
    {
        $source = $history ?? $group;

        // part_id => [page_number => earliest date that page was passed].
        $firstMasteredAt = [];
        // part_id => [dates of passed سرد sessions covering the part].
        $passedSardDates = [];

        foreach ($source as $session) {
            $type = $session->type;
            if (!in_array($type, [RecitationSession::TYPE_NEW_MEMORIZATION, RecitationSession::TYPE_RECITATION], true)) {
                continue;
            }

            $date = $this->sessionDate($session);
            $coveredParts = [];

            if ($session->part_id) {
                $coveredParts[(int) $session->part_id] = true;
            }

            foreach ($this->pagesOf($session) as $page) {
                $partId = $this->resolvePartId($session, (int) $page->page_number);
                if (!$partId) {
                    continue;
                }

                $partId = (int) $partId;
                $coveredParts[$partId] = true;

                // Memorization mastery must come from a new-memorization
                // session specifically: a page passed during سرد (recitation)
                // does not prove the page was memorized.
                if ($type !== RecitationSession::TYPE_NEW_MEMORIZATION || !$page->is_passed) {
                    continue;
                }

                $pageNumber = (int) $page->page_number;
                $prev = $firstMasteredAt[$partId][$pageNumber] ?? null;
                if ($prev === null || ($date && $date->lt($prev))) {
                    $firstMasteredAt[$partId][$pageNumber] = $date ?? $prev;
                }
            }

            if ($type === RecitationSession::TYPE_RECITATION && $session->status === RecitationSession::STATUS_PASSED) {
                foreach (array_keys($coveredParts) as $partId) {
                    $passedSardDates[$partId][] = $date;
                }
            }
        }

        if (empty($passedSardDates)) {
            return 0;
        }

        $count = 0;
        foreach (QuranPart::whereIn('id', array_keys($passedSardDates))->get() as $part) {
            $pages = $firstMasteredAt[$part->id] ?? [];
            $complete = true;
            $completedAt = null; // the day the last page was passed

            for ($page = (int) $part->start_page; $page <= (int) $part->end_page; $page++) {
                if (!isset($pages[$page])) {
                    $complete = false;
                    break;
                }
                $date = $pages[$page];
                if ($date && ($completedAt === null || $date->gt($completedAt))) {
                    $completedAt = $date;
                }
            }

            if (!$complete) {
                continue;
            }

            // The qualifying سرد must be on/after the part's completion.
            foreach ($passedSardDates[$part->id] as $sardDate) {
                if ($completedAt === null || $sardDate === null || $sardDate->gte($completedAt)) {
                    $count++;
                    break;
                }
            }
        }

        return $count;
    }

    /**
     * Count, against the student's full history, the parts they have
     * completely memorized and then successfully recited (سرد). Uses the same
     * rule as the written report's "عدد الأجزاء المحفوظة": every page of the
     * part must be passed in a new-memorization session and a passed سرد for
     * the part must follow. Used by the student dashboard/report stat cards.
     */
    public function memorizedPartsCountForStudent(int $studentId): int
    {
        $history = $this->loadAllTimeMemorizationHistory(collect([$studentId]))[$studentId] ?? collect();

        return $this->countFullyMemorizedParts($history, $history);
    }

    /**
     * Count the parts a student has reviewed: every page in the part has
     * at least one review page record. Passing is NOT required — a part counts
     * once all its pages have been reviewed, regardless of the session or
     * page result (no سرد condition).
     */
    private function countFullyReviewedParts(Collection $group): int
    {
        // part_id => [page_number => true] for every reviewed page.
        $reviewedByPart = [];
        foreach ($group->where('type', RecitationSession::TYPE_REVIEW) as $session) {
            foreach ($this->pagesOf($session) as $page) {
                $partId = $this->resolvePartId($session, (int) $page->page_number);
                if ($partId) {
                    $reviewedByPart[$partId][(int) $page->page_number] = true;
                }
            }
        }

        if (empty($reviewedByPart)) {
            return 0;
        }

        $count = 0;
        foreach (QuranPart::whereIn('id', array_keys($reviewedByPart))->get() as $part) {
            $pages = $reviewedByPart[$part->id] ?? [];
            $complete = true;

            for ($page = (int) $part->start_page; $page <= (int) $part->end_page; $page++) {
                if (!isset($pages[$page])) {
                    $complete = false;
                    break;
                }
            }

            if ($complete) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Count the parts a student reviewed as unmastered: every page in the
     * part has at least one unmastered page record. Passing is NOT required.
     */
    private function countFullyUnmasteredParts(Collection $group): int
    {
        // part_id => [page_number => true] for every unmastered page.
        $unmasteredByPart = [];
        foreach ($group->where('type', RecitationSession::TYPE_UNMASTERED) as $session) {
            foreach ($this->pagesOf($session) as $page) {
                $partId = $this->resolvePartId($session, (int) $page->page_number);
                if ($partId) {
                    $unmasteredByPart[$partId][(int) $page->page_number] = true;
                }
            }
        }

        if (empty($unmasteredByPart)) {
            return 0;
        }

        $count = 0;
        foreach (QuranPart::whereIn('id', array_keys($unmasteredByPart))->get() as $part) {
            $pages = $unmasteredByPart[$part->id] ?? [];
            $complete = true;

            for ($page = (int) $part->start_page; $page <= (int) $part->end_page; $page++) {
                if (!isset($pages[$page])) {
                    $complete = false;
                    break;
                }
            }

            if ($complete) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Return the immediately preceding period of the same length, used for
     * period-over-period comparison. Returns [null, null] for all-time.
     */
    public function previousPeriodRange($fromDate, $toDate): array
    {
        if (!$fromDate || !$toDate) {
            return [null, null];
        }

        $days   = $fromDate->copy()->startOfDay()->diffInDays($toDate->copy()->startOfDay()) + 1;
        $prevTo = $fromDate->copy()->subDay()->endOfDay();

        return [$prevTo->copy()->subDays($days - 1)->startOfDay(), $prevTo];
    }

    /**
     * Group decided-session final scores into fixed pass bands.
     */
    public function scoreDistribution(Collection $sessions): array
    {
        $bins = [
            ['label' => 'أقل من 60', 'min' => 0,  'max' => 59.999, 'count' => 0, 'color' => '#c0392b'],
            ['label' => '60 - 74',   'min' => 60, 'max' => 74.999, 'count' => 0, 'color' => '#e67e22'],
            ['label' => '75 - 89',   'min' => 75, 'max' => 89.999, 'count' => 0, 'color' => '#3498db'],
            ['label' => '90 - 100',  'min' => 90, 'max' => 100,    'count' => 0, 'color' => '#27ae60'],
        ];

        foreach ($sessions->whereNotNull('final_score')->pluck('final_score') as $score) {
            foreach ($bins as &$bin) {
                if ($score >= $bin['min'] && $score <= $bin['max']) {
                    $bin['count']++;
                    break;
                }
            }
            unset($bin);
        }

        return $bins;
    }

    /**
     * Count decided recitation sessions by type.
     */
    public function typeDistribution(Collection $sessions): array
    {
        $meta = [
            'new_memorization' => ['label' => 'حفظ جديد',      'color' => '#27ae60'],
            'review'           => ['label' => 'مراجعة',         'color' => '#2980b9'],
            'unmastered'       => ['label' => 'حفظ غير متقن',  'color' => '#c0392b'],
            'recitation'       => ['label' => 'سرد',            'color' => '#16a085'],
            'mafsil'           => ['label' => 'مفصل',           'color' => '#d4a030'],
        ];

        // Every genuine session (in-progress included) is represented in the
        // distribution; "لم يسمع" markers are excluded so the slices sum to
        // the headline session total.
        $actual = $this->actualRecitationSessions($sessions);

        $out = [];
        foreach ($meta as $key => $info) {
            $out[] = [
                'key'   => $key,
                'label' => $info['label'],
                'color' => $info['color'],
                'count' => $actual->where('type', $key)->count(),
            ];
        }

        return $out;
    }

    /**
     * Per-student aggregate of recitation and attendance activity. Accepts
     * either collection independently so the same table works on the
     * recitation, attendance and aggregate report pages.
     */
    public function studentSummary(Collection $sessions, ?Collection $attendances = null, ?Carbon $fromDate = null, ?Carbon $toDate = null): array
    {
        $attendances = $attendances ?? collect();
        $rows = [];

        $attDaysTotal = $this->workDaysInRange($fromDate, $toDate);

        // When no explicit period is selected ("all"), fall back to the span
        // of the reported sessions so the expected-session count and the
        // recitation-days rate still have a meaningful denominator.
        $rangeFrom = $fromDate;
        $rangeTo   = $toDate;

        if ((!$rangeFrom || !$rangeTo) && $sessions->isNotEmpty()) {
            $dates = $sessions
                ->map(fn ($s) => $this->sessionDate($s))
                ->filter()
                ->values();

            if ($dates->isNotEmpty()) {
                $sorted    = $dates->sortBy(fn ($d) => $d->getTimestamp())->values();
                $rangeFrom = $rangeFrom ?? $sorted->first();
                $rangeTo   = $rangeTo   ?? $sorted->last();
            }
        }

        $recDaysTotal = $this->recitationDaysInRange($rangeFrom, $rangeTo);

        foreach ($sessions->groupBy('student_id') as $studentId => $group) {
            // Not-completed (لم يكمل) sessions count with the failing ones.
            $decided = $group->whereIn('status', ['passed', 'failed', 'failed_continued', 'incomplete']);
            $passed  = $group->where('status', 'passed');
            $avg     = $group->whereNotNull('final_score')->avg('final_score');
            $total   = $decided->count();
            $passCnt = $passed->count();
            // Total sessions counts every genuine session (in-progress
            // included) but not the "لم يسمع" markers.
            $inProgress = $group->where('status', 'in_progress')->count();

            $reciteDays = $group
                ->reject(fn ($s) => $this->isNotRecite($s))
                ->map(fn ($s) => $this->sessionDate($s)?->toDateString())
                ->filter()
                ->unique()
                ->count();

            $rows[$studentId] = array_merge([
                'student_id' => $studentId,
                'name'       => $group->first()?->student?->user?->name ?? '—',
                'is_active'  => (bool) ($group->first()?->student?->user?->is_active ?? false),
                'sessions'   => $total + $inProgress,
                'passed'     => $passCnt,
                'incomplete' => $group->where('status', RecitationSession::STATUS_INCOMPLETE)->count(),
                // Distinct days on which the student failed (failed or
                // failed-and-continued); not-completed days stay separate.
                'fail_days'  => $group
                    ->whereIn('status', [RecitationSession::STATUS_FAILED, RecitationSession::STATUS_FAILED_CONTINUED])
                    ->map(fn ($s) => $this->sessionDate($s)?->toDateString())
                    ->filter()
                    ->unique()
                    ->count(),
                'pass_rate'  => $total > 0 ? round(($passCnt / $total) * 100, 1) : 0.0,
                'avg_score'  => $avg ? round($avg, 1) : 0.0,
                'pages'      => $this->sumPages($passed),
                'not_recite_days' => $group
                    ->filter(fn ($s) => $this->isNotRecite($s))
                    ->map(fn ($s) => $this->sessionDate($s)?->toDateString())
                    ->filter()
                    ->unique()
                    ->count(),
                'rec_days'       => $reciteDays,
                'rec_days_total' => $recDaysTotal,
                // Expected number of recitation sessions: configured
                // recitation days falling inside the filter period.
                'expected_sessions' => $recDaysTotal,
                'rec_days_rate'  => ($recDaysTotal !== null && $recDaysTotal > 0)
                    ? round(($reciteDays / $recDaysTotal) * 100, 1)
                    : null,
            ], $this->emptyAttendanceSummary());
        }

        foreach ($attendances->groupBy('student_id') as $studentId => $group) {
            if (!isset($rows[$studentId])) {
                $rows[$studentId] = array_merge([
                    'student_id' => $studentId,
                    'name'       => $group->first()?->student?->user?->name ?? '—',
                    'is_active'  => (bool) ($group->first()?->student?->user?->is_active ?? false),
                    'sessions'   => 0,
                    'passed'     => 0,
                    'incomplete' => 0,
                    'fail_days'  => 0,
                    'pass_rate'  => 0.0,
                    'avg_score'  => 0.0,
                    'pages'      => 0,
                    'expected_sessions' => $recDaysTotal,
                ], $this->emptyAttendanceSummary());
            }

            $total   = $group->count();
            $excused = $group->where('status', 'excused')->count();
            $present = $group->whereIn('status', ['present', 'late'])->count();
            $attDays = $group->whereIn('status', ['present', 'late'])
                ->map(fn ($a) => $this->dateKey($a))
                ->filter()
                ->unique()
                ->count();

            $rows[$studentId]['att_total']        = $total;
            // att_present stays the exclusive status so the summary row
            // (present + absent + late) still reconciles with att_total.
            // Late is already rewarded through $present in the rate below.
            $rows[$studentId]['att_present']      = $group->where('status', 'present')->count();
            $rows[$studentId]['att_absent']       = $group->where('status', 'absent')->count();
            $rows[$studentId]['att_late']         = $group->where('status', 'late')->count();
            $rows[$studentId]['att_excused']      = $excused;
            $rows[$studentId]['att_days']         = $attDays;
            $rows[$studentId]['att_days_total']   = $attDaysTotal;
            $rows[$studentId]['att_days_rate']    = ($attDaysTotal !== null && $attDaysTotal > 0)
                ? round(($attDays / $attDaysTotal) * 100, 1)
                : null;
            $rows[$studentId]['attendance_rate']  = ($total - $excused) > 0
                ? round(($present / ($total - $excused)) * 100, 1)
                : 0.0;
        }

        return collect($rows)->sortBy('name')->values()->toArray();
    }

    /**
     * Day-based metrics for a single student: distinct attended days
     * (present + late), distinct recited days, and the configured period
     * totals. Used by the individual student report pages.
     */
    public function studentDayStats(Collection $sessions, Collection $attendances, ?Carbon $fromDate = null, ?Carbon $toDate = null): array
    {
        $attDays = $attendances->whereIn('status', ['present', 'late'])
            ->map(fn ($a) => $this->dateKey($a))
            ->filter()
            ->unique()
            ->count();

        $recDays = $sessions
            ->reject(fn ($s) => $s->type === RecitationSession::TYPE_NOT_RECITE
                || $s->status === RecitationSession::STATUS_NOT_RECITE)
            ->map(fn ($s) => $this->sessionDate($s)?->toDateString())
            ->filter()
            ->unique()
            ->count();

        $attTotal = $this->workDaysInRange($fromDate, $toDate);
        $recTotal = $this->recitationDaysInRange($fromDate, $toDate);

        return [
            'att_days'       => $attDays,
            'att_days_total' => $attTotal,
            'att_days_rate'  => ($attTotal !== null && $attTotal > 0) ? round(($attDays / $attTotal) * 100, 1) : null,
            'rec_days'       => $recDays,
            'rec_days_total' => $recTotal,
            'rec_days_rate'  => ($recTotal !== null && $recTotal > 0) ? round(($recDays / $recTotal) * 100, 1) : null,
        ];
    }

    private function emptyAttendanceSummary(): array
    {
        return [
            'att_total'       => 0,
            'att_present'     => 0,
            'att_absent'      => 0,
            'att_late'        => 0,
            'att_excused'     => 0,
            'att_days'        => 0,
            'att_days_total'  => null,
            'att_days_rate'   => null,
            'attendance_rate' => null,
        ];
    }

    /**
     * Aggregate attendance counts per student id.
     * Returns [student_id => att_* summary].
     */
    private function attendanceSummaryByStudent(Collection $attendances): array
    {
        $out = [];

        foreach ($attendances->groupBy('student_id') as $studentId => $group) {
            $total   = $group->count();
            $excused = $group->where('status', 'excused')->count();
            $present = $group->whereIn('status', ['present', 'late'])->count();
            $attDays = $group->whereIn('status', ['present', 'late'])
                ->map(fn ($a) => $this->dateKey($a))
                ->filter()
                ->unique()
                ->count();

            $out[$studentId] = [
                'att_total'       => $total,
                // Exclusive status: the written report lists present /
                // absent / late as a numbered breakdown of att_total.
                // Late still rewards the rate through $present below.
                'att_present'     => $group->where('status', 'present')->count(),
                'att_absent'      => $group->where('status', 'absent')->count(),
                'att_late'        => $group->where('status', 'late')->count(),
                'att_excused'     => $excused,
                'att_days'        => $attDays,
                'attendance_rate' => ($total - $excused) > 0
                    ? round(($present / ($total - $excused)) * 100, 1)
                    : 0.0,
            ];
        }

        return $out;
    }

    /**
     * Normalise a record's date (attendance.date or session_date) to a
     * Y-m-d string, tolerating Carbon casts and plain test objects.
     */
    private function dateKey($model): ?string
    {
        $date = $model->date ?? $model->session_date ?? null;

        if (!$date) {
            return null;
        }

        if ($date instanceof Carbon) {
            return $date->toDateString();
        }

        try {
            return Carbon::parse($date)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function dropdownData(Request $request, ?int $supervisorScopeUserId = null): array
    {
        $supervisors = Supervisor::with('user')
            ->join('users', 'supervisors.user_id', '=', 'users.id')
            ->where('users.is_active', true)
            ->orderBy('users.name')
            ->select('supervisors.*')
            ->get();

        $studentsQuery = Student::active()->with('user')->orderedByName();
        if ($supervisorScopeUserId) {
            $studentsQuery->where('supervisor_id', $supervisorScopeUserId);
        } elseif ($request->supervisor_id) {
            $studentsQuery->where('supervisor_id', $request->supervisor_id);
        }
        $students = $studentsQuery->get();

        return [
            'supervisors' => $supervisors,
            'students'    => $students,
        ];
    }

    /**
     * Compute (date, supervisor) rows where the supervisor failed to register
     * a recitation session on a configured recitation day, or failed to
     * register any attendance on a configured working day.
     *
     * Returns rows + configured day-name arrays for the view banner.
     */
    public function supervisorMissingDays(?Carbon $from, ?Carbon $to, ?int $supervisorId = null): array
    {
        $requiredSessionDays = SettingsServiceProvider::carbonDaysForDayNames(
            config('app.recitation_days', SettingsServiceProvider::DEFAULT_RECITATION_DAYS)
        );
        $requiredAttendanceDays = SettingsServiceProvider::carbonDaysForDayNames(
            config('app.work_days', SettingsServiceProvider::DEFAULT_WORK_DAYS)
        );

        $supervisors = User::where('role', 'supervisor')
            ->where('is_active', true)
            ->when($supervisorId, fn ($q) => $q->where('id', $supervisorId))
            ->orderBy('name')
            ->get(['id', 'name']);

        $sessionDaysBySupervisor = RecitationSession::query()
            ->where('auto_generated', false)
            ->when($from, fn ($q) => $q->whereDate('session_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('session_date', '<=', $to))
            ->when($supervisorId, fn ($q) => $q->where('supervisor_id', $supervisorId))
            ->get(['supervisor_id', 'session_date'])
            ->groupBy(fn ($s) => $s->supervisor_id . '|' . $s->session_date->toDateString())
            ->keys()
            ->flip();

        $attendanceDaysBySupervisor = Attendance::query()
            ->when($from, fn ($q) => $q->whereDate('date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('date', '<=', $to))
            ->when($supervisorId, fn ($q) => $q->where('supervisor_id', $supervisorId))
            ->get(['supervisor_id', 'date'])
            ->groupBy(fn ($a) => $a->supervisor_id . '|' . $a->date->toDateString())
            ->keys()
            ->flip();

        // Days recorded as a holiday are off-days: no session or attendance is expected.
        $holidayDaysBySupervisor = Attendance::query()
            ->where('status', 'holiday')
            ->when($from, fn ($q) => $q->whereDate('date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('date', '<=', $to))
            ->when($supervisorId, fn ($q) => $q->where('supervisor_id', $supervisorId))
            ->get(['supervisor_id', 'date'])
            ->groupBy(fn ($a) => $a->supervisor_id . '|' . $a->date->toDateString())
            ->keys()
            ->flip();

        $rows = collect();

        $start = ($from ?? Carbon::now()->subMonths(1))->copy()->startOfDay();
        $end   = ($to   ?? Carbon::now())->copy()->endOfDay();
        if ($start->gt($end)) {
            [$start, $end] = [$end, $start];
        }

        $cur = $start->copy();
        while ($cur->lte($end)) {
            $sessionExpected    = in_array($cur->dayOfWeek, $requiredSessionDays, true);
            $attendanceExpected = in_array($cur->dayOfWeek, $requiredAttendanceDays, true);

            if ($sessionExpected || $attendanceExpected) {
                $dateKey = $cur->toDateString();
                foreach ($supervisors as $sup) {
                    $supKey = $sup->id . '|' . $dateKey;

                    // Holiday: skip entirely (no session/attendance expected).
                    if (isset($holidayDaysBySupervisor[$supKey])) {
                        continue;
                    }

                    $hasSession    = $sessionExpected    && isset($sessionDaysBySupervisor[$supKey]);
                    $hasAttendance = $attendanceExpected && isset($attendanceDaysBySupervisor[$supKey]);

                    if (($sessionExpected && ! $hasSession) || ($attendanceExpected && ! $hasAttendance)) {
                        $rows->push([
                            'date'                => $cur->copy(),
                            'day_name'            => SettingsServiceProvider::arabicDayName((int) $cur->dayOfWeek),
                            'supervisor_id'       => $sup->id,
                            'supervisor_name'     => $sup->name,
                            'session_expected'    => $sessionExpected,
                            'session_missing'     => $sessionExpected && ! $hasSession,
                            'attendance_expected' => $attendanceExpected,
                            'attendance_missing'  => $attendanceExpected && ! $hasAttendance,
                        ]);
                    }
                }
            }

            $cur->addDay();
        }

        $stats = [
            'rows_count'              => $rows->count(),
            'missing_sessions'        => $rows->where('session_missing', true)->count(),
            'missing_attendance'      => $rows->where('attendance_missing', true)->count(),
        ];

        return [
            'rows'                      => $rows,
            'stats'                     => $stats,
            'configured_session_days'  => array_map(
                [SettingsServiceProvider::class, 'arabicDayName'],
                $requiredSessionDays
            ),
            'configured_attendance_days'=> array_map(
                [SettingsServiceProvider::class, 'arabicDayName'],
                $requiredAttendanceDays
            ),
        ];
    }
}
