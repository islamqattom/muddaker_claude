<?php

namespace App\Services;

use App\Models\Student;
use App\Models\YawmAlHimmaSession;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Aggregation layer for the "يوم الهمة" reports (overview / per-day /
 * per-student). Keeps the precision scoring formula in a single place so
 * the report pages and the exports agree.
 */
class HimmaReportService
{
    public function __construct(private ReportService $reports)
    {
    }

    /**
     * Base query, optionally scoped to a supervisor (by their students or the
     * denormalized recorder name), mirroring Supervisor\YawmAlHimmaController.
     */
    public function scopedQuery(?int $supervisorUserId = null, ?string $supervisorName = null): Builder
    {
        $query = YawmAlHimmaSession::query()->with('student.user');

        if ($supervisorUserId || $supervisorName) {
            $query->where(function (Builder $q) use ($supervisorUserId, $supervisorName) {
                if ($supervisorUserId) {
                    $q->whereHas('student', fn ($sq) => $sq->where('supervisor_id', $supervisorUserId));
                }
                if ($supervisorName) {
                    $supervisorUserId
                        ? $q->orWhere('supervisor_name', $supervisorName)
                        : $q->where('supervisor_name', $supervisorName);
                }
            });
        }

        return $query;
    }

    /**
     * Apply the requested period and the optional student/supervisor-name
     * filters. Returns the resolved [fromDate, toDate, period].
     */
    public function applyFilters(Builder $query, Request $request): array
    {
        [$fromDate, $toDate, $period] = $this->reports->resolvePeriod($request);

        if ($fromDate && $toDate) {
            $query->whereBetween('session_date', [$fromDate->toDateString(), $toDate->toDateString()]);
        }

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->filled('supervisor_name')) {
            $query->where('supervisor_name', 'like', '%' . $request->supervisor_name . '%');
        }

        return [$fromDate, $toDate, $period];
    }

    public function overview(Request $request, ?int $supervisorUserId = null, ?string $supervisorName = null): array
    {
        $query = $this->scopedQuery($supervisorUserId, $supervisorName);
        [$fromDate, $toDate, $period] = $this->applyFilters($query, $request);

        $sessions = $query->orderByDesc('session_date')->orderByDesc('id')->get();

        return array_merge($this->aggregate($sessions), [
            'fromDate' => $fromDate,
            'toDate'   => $toDate,
            'period'   => $period,
            'studentOptions' => $this->studentOptions($supervisorUserId),
        ]);
    }

    public function day(Request $request, string $date, ?int $supervisorUserId = null, ?string $supervisorName = null): array
    {
        $query = $this->scopedQuery($supervisorUserId, $supervisorName)
            ->whereDate('session_date', $date);
        [$fromDate, $toDate, $period] = $this->applyFilters($query, $request);

        $sessions = $query->orderByDesc('id')->get();
        $day = Carbon::parse($date);

        return array_merge($this->aggregate($sessions), [
            'day'              => $day,
            'competitionChart' => $this->competitionChart($sessions),
            'fromDate'         => $fromDate,
            'toDate'           => $toDate,
            'period'           => $period,
        ]);
    }

    public function student(Request $request, Student $student, ?int $supervisorUserId = null, ?string $supervisorName = null): array
    {
        $query = $this->scopedQuery($supervisorUserId, $supervisorName)
            ->where('student_id', $student->id);
        [$fromDate, $toDate, $period] = $this->applyFilters($query, $request);

        $sessions = $query->orderBy('session_date')->orderBy('id')->get();

        return array_merge($this->aggregate($sessions), [
            'student'  => $student->loadMissing('user', 'level'),
            'fromDate' => $fromDate,
            'toDate'   => $toDate,
            'period'   => $period,
        ]);
    }

    /**
     * Core aggregation shared by all three report types.
     */
    public function aggregate(Collection $sessions): array
    {
        $students = [];
        $days = [];
        $totalParts = 0;
        $totalErrors = 0;
        $totalHesitations = 0;
        $totalScore = 0.0;

        foreach ($sessions as $session) {
            $precision = $this->sessionPrecision($session);
            $partsCount = count(is_array($session->parts) ? $session->parts : []);
            $totalParts += $partsCount;
            $totalErrors += (int) $session->total_errors;
            $totalHesitations += (int) $session->total_hesitations;
            $totalScore += $precision['score'];

            $key = $this->studentKey($session);
            $students[$key] ??= [
                'student_id'  => $session->student_id,
                'name'        => $session->student_name,
                'sessions'    => 0,
                'parts'       => 0,
                'errors'      => 0,
                'hesitations' => 0,
                'score'       => 0.0,
            ];
            $students[$key]['sessions']++;
            $students[$key]['parts']       += $partsCount;
            $students[$key]['errors']      += (int) $session->total_errors;
            $students[$key]['hesitations'] += (int) $session->total_hesitations;
            $students[$key]['score']       += $precision['score'];

            if ($date = $session->session_date?->toDateString()) {
                $days[$date] ??= [
                    'date'        => $date,
                    'sessions'    => 0,
                    'studentKeys' => [],
                    'parts'       => 0,
                    'errors'      => 0,
                    'hesitations' => 0,
                    'score'       => 0.0,
                ];
                $days[$date]['sessions']++;
                $days[$date]['studentKeys'][$key] = true;
                $days[$date]['parts']       += $partsCount;
                $days[$date]['errors']      += (int) $session->total_errors;
                $days[$date]['hesitations'] += (int) $session->total_hesitations;
                $days[$date]['score']       += $precision['score'];
            }
        }

        $studentRows = collect($students)
            ->map(fn ($row) => array_merge($row, ['score' => round($row['score'], 2)]))
            ->sortByDesc('score')
            ->values();

        $dayRows = collect($days)
            ->map(fn ($row) => [
                'date'        => $row['date'],
                'sessions'    => $row['sessions'],
                'students'    => count($row['studentKeys']),
                'parts'       => $row['parts'],
                'errors'      => $row['errors'],
                'hesitations' => $row['hesitations'],
                'score'       => round($row['score'], 2),
            ])
            ->sortBy('date')
            ->values();

        $sessionsCount = $sessions->count();

        $stats = [
            'total_sessions'    => $sessionsCount,
            'total_students'    => $studentRows->count(),
            'total_days'        => $dayRows->count(),
            'total_parts'       => $totalParts,
            'total_errors'      => $totalErrors,
            'total_hesitations' => $totalHesitations,
            'total_score'       => round($totalScore, 2),
            'avg_score'         => $sessionsCount > 0 ? round($totalScore / $sessionsCount, 2) : 0.0,
            'avg_parts'         => $sessionsCount > 0 ? round($totalParts / $sessionsCount, 2) : 0.0,
        ];

        return [
            'sessions' => $sessions,
            'students' => $studentRows,
            'days'     => $dayRows,
            'stats'    => $stats,
            'charts'   => [
                'days'        => [
                    'labels'   => $dayRows->map(fn ($d) => Carbon::parse($d['date'])->format('m/d'))->all(),
                    'sessions' => $dayRows->pluck('sessions')->all(),
                    'errors'   => $dayRows->pluck('errors')->all(),
                ],
                'leaderboard' => [
                    'labels' => $studentRows->take(10)->pluck('name')->all(),
                    'data'   => $studentRows->take(10)->pluck('parts')->all(),
                ],
                'precision' => [
                    'labels' => $studentRows->take(10)->pluck('name')->all(),
                    'data'   => $studentRows->take(10)->pluck('score')->all(),
                ],
            ],
        ];
    }

    /**
     * Per-student cumulative part lines for a single day's competition chart.
     */
    public function competitionChart(Collection $sessions): array
    {
        $studentParts = [];
        $allPartNumbers = collect();

        foreach ($sessions as $session) {
            $parts = is_array($session->parts) ? $session->parts : [];
            foreach ($parts as $partNumber) {
                $partNumber = (int) $partNumber;
                $allPartNumbers->push($partNumber);
                $studentParts[$session->student_name][$partNumber] = true;
            }
        }

        $sortedParts = $allPartNumbers->unique()->sort()->values();
        $labels = $sortedParts->map(fn ($n) => 'جزء ' . $n)->all();

        $palette = [
            '#d4a030', '#182B48', '#e74c3c', '#27ae60', '#8e44ad',
            '#2980b9', '#f39c12', '#16a085', '#c0392b', '#2c3e50',
        ];

        $datasets = [];
        $i = 0;
        foreach ($studentParts as $studentName => $partsMap) {
            $data = [];
            $cumulative = 0;
            foreach ($sortedParts as $partNumber) {
                if (isset($partsMap[$partNumber])) {
                    $cumulative++;
                }
                $data[] = $cumulative;
            }
            $color = $palette[$i % count($palette)];
            $datasets[] = [
                'label'           => $studentName,
                'data'            => $data,
                'borderColor'     => $color,
                'backgroundColor' => $color,
                'borderWidth'     => 2,
                'pointRadius'     => 4,
                'pointHoverRadius' => 6,
                'tension'         => 0.2,
                'fill'            => false,
            ];
            $i++;
        }

        return ['labels' => $labels, 'datasets' => $datasets];
    }

    /**
     * Precision scoring for one session. Mirrors the admin day page formula:
     *   base 10 per part + max(0, 10 − errors) + max(0, 10 − hesitations × 0.5).
     */
    public function sessionPrecision(YawmAlHimmaSession $session): array
    {
        $parts = is_array($session->parts) ? $session->parts : [];
        $pagesData = is_array($session->pages_data) ? $session->pages_data : [];

        $partStats = [];
        foreach ($parts as $partNumber) {
            $partStats[(int) $partNumber] = ['errors' => 0, 'hesitations' => 0, 'pages' => 0];
        }

        foreach ($pagesData as $page) {
            $pn = (int) ($page['part'] ?? $page['part_number'] ?? 0);
            if (!isset($partStats[$pn])) {
                continue;
            }
            $partStats[$pn]['errors'] += (int) ($page['errors'] ?? 0);
            $partStats[$pn]['hesitations'] += (int) ($page['hesitations'] ?? 0);
            $partStats[$pn]['pages']++;
        }

        $countedParts = 0;
        $partsPoints = 0;
        $qualityBonus = 0.0;
        $fluencyBonus = 0.0;

        foreach ($partStats as $st) {
            if ($st['pages'] === 0) {
                continue;
            }
            $countedParts++;
            $partsPoints += 10;
            $qualityBonus += max(0, 10 - $st['errors']);
            $fluencyBonus += max(0, 10 - ($st['hesitations'] * 0.5));
        }

        if ($countedParts === 0) {
            $countedParts = count($parts);
            $partsPoints = $countedParts * 10;
        }

        return [
            'parts'          => count($parts),
            'parts_points'   => $partsPoints,
            'quality_bonus'  => $qualityBonus,
            'fluency_bonus'  => $fluencyBonus,
            'score'          => $partsPoints + $qualityBonus + $fluencyBonus,
            'errors'         => (int) $session->total_errors,
            'hesitations'    => (int) $session->total_hesitations,
        ];
    }

    /**
     * Students that can be used as an overview filter (those with a linked id).
     */
    public function studentOptions(?int $supervisorUserId = null): Collection
    {
        $query = Student::with('user')->orderBy('id');

        if ($supervisorUserId) {
            $query->where('supervisor_id', $supervisorUserId);
        }

        return $query->get();
    }

    private function studentKey(YawmAlHimmaSession $session): string
    {
        return $session->student_id ? 'id:' . $session->student_id : 'name:' . $session->student_name;
    }
}
