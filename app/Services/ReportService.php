<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\QuranPage;
use App\Models\QuranPart;
use App\Models\RecitationSession;
use App\Models\Student;
use App\Models\Supervisor;
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

    public function recitationQuery(Request $request, ?int $supervisorScopeUserId = null): Builder
    {
        [$fromDate, $toDate, $period] = $this->resolvePeriod($request);

        $query = RecitationSession::with(['student.user', 'student.supervisor', 'supervisor', 'part', 'pages']);

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
                $query->whereIn('status', ['failed', 'failed_continued']);
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

    public function recitationStats(Collection $sessions): array
    {
        $total    = $sessions->count();
        $passedSessions = $sessions->where('status', 'passed');
        $passed   = $passedSessions->count();
        $failed   = $sessions->whereIn('status', ['failed', 'failed_continued'])->count();
        $decided  = $passed + $failed;
        $inProgress = $sessions->where('status', 'in_progress')->count();

        // Decided sessions (passed + failed) have complete error/hesitation
        // data; in-progress sessions only carry partial numbers.
        $decidedSessions = $sessions->whereIn('status', ['passed', 'failed', 'failed_continued']);

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
        $present = $attendances->whereIn('status', ['present', 'late'])->count();
        $absent  = $attendances->where('status', 'absent')->count();
        $late    = $attendances->where('status', 'late')->count();
        $excused = $attendances->where('status', 'excused')->count();

        // Excused days are excluded from the rate denominator so they
        // neither reward nor penalize the student's attendance rate.
        $effective = $total - $excused;

        return [
            'total'           => $total,
            'present'         => $attendances->where('status', 'present')->count(),
            'late'            => $late,
            'absent'          => $absent,
            'excused'         => $excused,
            'attendance_rate' => $effective > 0 ? round(($present / $effective) * 100, 1) : 0.0,
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
                $decided = $passed + $group->whereIn('status', ['failed', 'failed_continued'])->count();
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
                $failed = $group->whereIn('status', ['failed', 'failed_continued'])->count();
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
                $decided = $passed + $group->whereIn('status', ['failed', 'failed_continued'])->count();
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
    public function studentBreakdown(Collection $sessions, ?Collection $attendances = null): array
    {
        $attendanceByStudent = $this->attendanceSummaryByStudent($attendances ?? collect());

        // Load all-time memorization + recitation sessions (with pages) for
        // every student in the breakdown, in a single batched query. Page
        // mastery for "الأجزاء المحفوظة" must be evaluated against the
        // student's full history: the new-memorization sessions that prove
        // each page are usually spread across multiple periods (the سرد that
        // qualifies the part is itself period-scoped). When no DB is
        // available (e.g. isolated unit tests with plain objects), the
        // returned map is empty and callers fall back to the in-period
        // $group, preserving previous behavior.
        $historyByStudent = $this->loadAllTimeMemorizationHistory(
            $sessions->pluck('student_id')->filter()->unique()->values()
        );

        return $sessions
            ->groupBy('student_id')
            ->map(function ($group, $studentId) use ($attendanceByStudent, $historyByStudent) {
                $student = $group->first()?->student;

                // In-progress sessions are unfinished: exclude them from
                // session counts so the written report only reflects
                // completed activity.
                $decided = $group->whereIn('status', ['passed', 'failed', 'failed_continued']);
                // Pages and parts are accomplishments: only sessions the
                // student actually passed are counted.
                $passed  = $group->where('status', 'passed');

                $review        = $decided->where('type', 'review');
                $newMemorize   = $decided->where('type', 'new_memorization');
                $recitation    = $decided->where('type', 'recitation');
                $unmastered    = $decided->where('type', 'unmastered');
                $mafsil        = $decided->where('type', 'mafsil');

                $recitationPassed  = $passed->where('type', 'recitation');
                $mafsilPassed      = $passed->where('type', 'mafsil');

                return array_merge([
                    'student_id'                  => $student?->id,
                    'name'                        => $student?->user?->name ?? '—',
                    'total_sessions'              => $decided->count(),
                    'review_sessions'             => $review->count(),
                    'review_passed'               => $review->where('status', RecitationSession::STATUS_PASSED)->count(),
                    'review_failed'               => $review->whereIn('status', [RecitationSession::STATUS_FAILED, RecitationSession::STATUS_FAILED_CONTINUED])->count(),
                    'review_pages'                => $this->countPassedPages($review),
                    'review_failed_pages'         => $this->countFailedPages($review),
                    'review_parts'                => $this->countFullyReviewedParts($group),
                    'new_memorization_sessions'   => $newMemorize->count(),
                    'new_memorization_passed'     => $newMemorize->where('status', RecitationSession::STATUS_PASSED)->count(),
                    'new_memorization_failed'     => $newMemorize->whereIn('status', [RecitationSession::STATUS_FAILED, RecitationSession::STATUS_FAILED_CONTINUED])->count(),
                    'new_memorization_parts'      => $this->countFullyMemorizedParts($group, $historyByStudent[$studentId] ?? null),
                    'new_memorization_pages'      => $this->countPassedPages($newMemorize),
                    'new_memorization_failed_pages' => $this->countFailedPages($newMemorize),
                    'recitation_sessions'         => $recitation->count(),
                    'recitation_passed'           => $recitation->where('status', RecitationSession::STATUS_PASSED)->count(),
                    'recitation_failed'           => $recitation->whereIn('status', [RecitationSession::STATUS_FAILED, RecitationSession::STATUS_FAILED_CONTINUED])->count(),
                    'recitation_pages'            => $this->sumPages($recitationPassed),
                    'unmastered_sessions'         => $unmastered->count(),
                    'unmastered_passed'           => $unmastered->where('status', RecitationSession::STATUS_PASSED)->count(),
                    'unmastered_failed'           => $unmastered->whereIn('status', [RecitationSession::STATUS_FAILED, RecitationSession::STATUS_FAILED_CONTINUED])->count(),
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
     * Load all-time new-memorization + recitation sessions (with their
     * pages) for the given student ids, grouped by student_id. Returns an
     * array [student_id => Collection] ready to be merged with any
     * period-scoped groups. Returns [] when no database is reachable
     * (e.g. isolated unit tests that pass plain objects) so callers can
     * fall back to their in-period session groups.
     */
    private function loadAllTimeMemorizationHistory(Collection $studentIds): array
    {
        if ($studentIds->isEmpty()) {
            return [];
        }

        try {
            return RecitationSession::whereIn('student_id', $studentIds)
                ->whereIn('type', [RecitationSession::TYPE_NEW_MEMORIZATION, RecitationSession::TYPE_RECITATION])
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
     * recitation (سرد) for. A part qualifies only when every page in the
     * part has at least one successful page record (from a new-memorization
     * or recitation session) and there is a passed recitation session for
     * that same part.
     *
     * The passed-سَرد qualifying event is taken from the in-period
     * $group; the page-mastery check uses the student's full history so
     * that memorization sessions from previous periods still prove the
     * pages. Pass $history = null (e.g. when no DB is available) to fall
     * back to evaluating page mastery on $group alone.
     */
    private function countFullyMemorizedParts(Collection $group, ?Collection $history = null): int
    {
        $recitedPartIds = $group
            ->where('type', RecitationSession::TYPE_RECITATION)
            ->where('status', RecitationSession::STATUS_PASSED)
            ->pluck('part_id')
            ->filter()
            ->unique()
            ->values();

        if ($recitedPartIds->isEmpty()) {
            return 0;
        }

        // page_number => true for every page successfully recited/memorized.
        // A page is considered mastered if it passed in a new-memorization
        // session OR in a recitation session (a passed سرد proves the page).
        // Use the student's full history when available so memorization
        // sessions from previous periods still count toward mastery.
        $source = $history ?? $group;
        $masteredByPart = [];
        foreach ($source->whereIn('type', [RecitationSession::TYPE_NEW_MEMORIZATION, RecitationSession::TYPE_RECITATION]) as $session) {
            foreach ($this->pagesOf($session) as $page) {
                if (!$page->is_passed) {
                    continue;
                }
                $partId = $this->resolvePartId($session, (int) $page->page_number);
                if ($partId) {
                    $masteredByPart[$partId][(int) $page->page_number] = true;
                }
            }
        }

        $count = 0;
        foreach (QuranPart::whereIn('id', $recitedPartIds)->get() as $part) {
            $pages = $masteredByPart[$part->id] ?? [];
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
     * Count the parts a student has reviewed: every page in the part has at
     * least one review page record. Passing is NOT required — a part counts
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

        $decided = $sessions->whereIn('status', ['passed', 'failed', 'failed_continued']);

        $out = [];
        foreach ($meta as $key => $info) {
            $out[] = [
                'key'   => $key,
                'label' => $info['label'],
                'color' => $info['color'],
                'count' => $decided->where('type', $key)->count(),
            ];
        }

        return $out;
    }

    /**
     * Per-student aggregate of recitation and attendance activity. Accepts
     * either collection independently so the same table works on the
     * recitation, attendance and aggregate report pages.
     */
    public function studentSummary(Collection $sessions, ?Collection $attendances = null): array
    {
        $attendances = $attendances ?? collect();
        $rows = [];

        foreach ($sessions->groupBy('student_id') as $studentId => $group) {
            $decided = $group->whereIn('status', ['passed', 'failed', 'failed_continued']);
            $passed  = $group->where('status', 'passed');
            $avg     = $group->whereNotNull('final_score')->avg('final_score');
            $total   = $decided->count();
            $passCnt = $passed->count();

            $rows[$studentId] = array_merge([
                'student_id' => $studentId,
                'name'       => $group->first()?->student?->user?->name ?? '—',
                'sessions'   => $total,
                'passed'     => $passCnt,
                'pass_rate'  => $total > 0 ? round(($passCnt / $total) * 100, 1) : 0.0,
                'avg_score'  => $avg ? round($avg, 1) : 0.0,
                'pages'      => $this->sumPages($passed),
            ], $this->emptyAttendanceSummary());
        }

        foreach ($attendances->groupBy('student_id') as $studentId => $group) {
            if (!isset($rows[$studentId])) {
                $rows[$studentId] = array_merge([
                    'student_id' => $studentId,
                    'name'       => $group->first()?->student?->user?->name ?? '—',
                    'sessions'   => 0,
                    'passed'     => 0,
                    'pass_rate'  => 0.0,
                    'avg_score'  => 0.0,
                    'pages'      => 0,
                ], $this->emptyAttendanceSummary());
            }

            $total   = $group->count();
            $excused = $group->where('status', 'excused')->count();
            $present = $group->whereIn('status', ['present', 'late'])->count();

            $rows[$studentId]['att_total']        = $total;
            $rows[$studentId]['att_present']      = $group->where('status', 'present')->count();
            $rows[$studentId]['att_absent']       = $group->where('status', 'absent')->count();
            $rows[$studentId]['att_late']         = $group->where('status', 'late')->count();
            $rows[$studentId]['att_excused']      = $excused;
            $rows[$studentId]['attendance_rate']  = ($total - $excused) > 0
                ? round(($present / ($total - $excused)) * 100, 1)
                : 0.0;
        }

        return collect($rows)->sortBy('name')->values()->toArray();
    }

    private function emptyAttendanceSummary(): array
    {
        return [
            'att_total'       => 0,
            'att_present'     => 0,
            'att_absent'      => 0,
            'att_late'        => 0,
            'att_excused'     => 0,
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

            $out[$studentId] = [
                'att_total'       => $total,
                'att_present'     => $group->where('status', 'present')->count(),
                'att_absent'      => $group->where('status', 'absent')->count(),
                'att_late'        => $group->where('status', 'late')->count(),
                'att_excused'     => $excused,
                'attendance_rate' => ($total - $excused) > 0
                    ? round(($present / ($total - $excused)) * 100, 1)
                    : 0.0,
            ];
        }

        return $out;
    }

    public function dropdownData(Request $request, ?int $supervisorScopeUserId = null): array
    {
        $supervisors = Supervisor::with('user')->orderBy('employee_number')->get();

        $studentsQuery = Student::with('user')->orderBy('id');
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
}
