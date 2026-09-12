<?php

namespace App\Services;

use App\Models\Attendance;
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

        if ($fromDate && $toDate) {
            $query->whereBetween('date', [$fromDate->toDateString(), $toDate->toDateString()]);
        }

        return $query;
    }

    public function recitationStats(Collection $sessions): array
    {
        $total    = $sessions->count();
        $passed   = $sessions->where('status', 'passed')->count();
        $failed   = $sessions->whereIn('status', ['failed', 'failed_continued'])->count();
        $decided  = $passed + $failed;
        $inProgress = $sessions->where('status', 'in_progress')->count();

        $avgScore = $sessions->whereNotNull('final_score')->avg('final_score');
        $passRate = $decided > 0 ? round(($passed / $decided) * 100, 1) : 0.0;

        return [
            'total'        => $total,
            'passed'       => $passed,
            'failed'       => $failed,
            'in_progress'  => $inProgress,
            'avg_score'    => $avgScore ? round($avgScore, 1) : 0.0,
            'pass_rate'    => $passRate,
            'total_errors'      => (int) $sessions->sum('total_errors'),
            'total_hesitations' => (int) $sessions->sum('total_hesitations'),
            'total_pages'       => (int) $sessions->sum('total_pages'),
        ];
    }

    public function attendanceStats(Collection $attendances): array
    {
        $total   = $attendances->count();
        $present = $attendances->whereIn('status', ['present', 'late'])->count();
        $absent  = $attendances->where('status', 'absent')->count();
        $late    = $attendances->where('status', 'late')->count();
        $excused = $attendances->where('status', 'excused')->count();

        return [
            'total'           => $total,
            'present'         => $attendances->where('status', 'present')->count(),
            'late'            => $late,
            'absent'          => $absent,
            'excused'         => $excused,
            'attendance_rate' => $total > 0 ? round(($present / $total) * 100, 1) : 0.0,
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
                return [
                    'date' => $date,
                    'total' => $total,
                    'present' => $present,
                    'rate' => $total > 0 ? round(($present / $total) * 100, 1) : 0.0,
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
                    'rate' => $total > 0 ? round((($present + $late) / $total) * 100, 1) : 0.0,
                ];
            })
            ->values()
            ->toArray();
    }

    public function topStudents(Collection $sessions, int $limit = 5): array
    {
        return $sessions
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
                    'pass_rate' => $decided > 0 ? round(($passed / $decided) * 100, 1) : 0.0,
                    'avg_score' => $avg ? round($avg, 1) : 0.0,
                ];
            })
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
    public function studentBreakdown(Collection $sessions): array
    {
        return $sessions
            ->groupBy('student_id')
            ->map(function ($group) {
                $student = $group->first()?->student;
                $review        = $group->where('type', 'review');
                $newMemorize   = $group->where('type', 'new_memorization');
                $recitation    = $group->where('type', 'recitation');
                $unmastered    = $group->where('type', 'unmastered');
                $mafsil        = $group->where('type', 'mafsil');

                return [
                    'student_id'                  => $student?->id,
                    'name'                        => $student?->user?->name ?? '—',
                    'total_sessions'              => $group->count(),
                    'review_sessions'             => $review->count(),
                    'review_pages'                => $this->sumPages($review),
                    'new_memorization_sessions'   => $newMemorize->count(),
                    'new_memorization_parts'      => $newMemorize->pluck('part_id')->filter()->unique()->count(),
                    'new_memorization_pages'      => $this->sumPages($newMemorize),
                    'recitation_sessions'         => $recitation->count(),
                    'recitation_pages'            => $this->sumPages($recitation),
                    'unmastered_sessions'         => $unmastered->count(),
                    'mafsil_sessions'             => $mafsil->count(),
                    'mafsil_parts'                => $mafsil->pluck('part_id')->filter()->unique()->count(),
                    'mafsil_pages'                => $this->sumPages($mafsil),
                ];
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
        return (int) $sessions->sum(function ($s) {
            if ($s->total_pages !== null) {
                return (int) $s->total_pages;
            }
            if ($s->start_page && $s->end_page) {
                return max(0, ((int) $s->end_page) - ((int) $s->start_page) + 1);
            }
            return 0;
        });
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
