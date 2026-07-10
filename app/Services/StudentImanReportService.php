<?php

namespace App\Services;

use App\Models\ImanDailyEntry;
use App\Models\ImanProgram;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StudentImanReportService
{
    public function build(Student $student, Request $request): array
    {
        $student->loadMissing(['user', 'supervisor', 'level']);

        $programs = ImanProgram::with(['criteria', 'creator'])
            ->where('owner_id', $student->id)
            ->where('owner_type', $student->getMorphClass())
            ->orderByDesc('is_active')
            ->orderByDesc('id')
            ->get();

        $fromDate = $request->from_date ? Carbon::parse($request->from_date) : Carbon::now()->subDays(30);
        $toDate = $request->to_date ? Carbon::parse($request->to_date) : Carbon::now();

        $entriesByProgram = [];
        $criteriaStats = [];

        foreach ($programs as $program) {
            $entries = ImanDailyEntry::where('iman_program_id', $program->id)
                ->whereBetween('entry_date', [$fromDate->toDateString(), $toDate->toDateString()])
                ->get();

            $entriesByProgram[$program->id] = $entries;

            foreach ($program->criteria as $criterion) {
                $criterionEntries = $entries->where('iman_criterion_id', $criterion->id);
                $total = $criterionEntries->count();
                $checked = 0;
                $valueSum = 0;

                foreach ($criterionEntries as $entry) {
                    if ($criterion->isCheckType()) {
                        if ($entry->is_checked) {
                            $checked++;
                        }
                    } else {
                        $valueSum += (int) ($entry->value ?? 0);
                    }
                }

                $expected = max(1, $fromDate->diffInDays($toDate) + 1);
                $completion = $criterion->isCheckType()
                    ? min(100, round(($checked / $expected) * 100))
                    : ($criterion->target_value ? min(100, round(($valueSum / max(1, $criterion->target_value * $expected)) * 100)) : 0);

                $criteriaStats[$program->id][$criterion->id] = [
                    'name' => $criterion->name,
                    'type' => $criterion->type,
                    'target' => $criterion->target_value,
                    'total' => $total,
                    'checked' => $checked,
                    'value_sum' => $valueSum,
                    'completion' => $completion,
                ];
            }
        }

        $overallStats = [
            'programs_count' => $programs->count(),
            'active_programs' => $programs->where('is_active', true)->count(),
            'total_criteria' => $programs->sum(fn ($p) => $p->criteria->count()),
            'total_entries' => ImanDailyEntry::whereIn('iman_program_id', $programs->pluck('id'))
                ->whereBetween('entry_date', [$fromDate->toDateString(), $toDate->toDateString()])
                ->count(),
        ];

        $dailyCompletion = $this->buildDailyCompletion($programs, $fromDate, $toDate);
        $weekData = collect($dailyCompletion)->take(-7)->values()->all();
        $monthData = $dailyCompletion;

        $availableWeeks = $this->buildAvailableWeeks();
        $requestedWeeks = $this->resolveRequestedWeeks($request, $availableWeeks);
        $weekComparison = $this->buildWeekComparison($programs, $requestedWeeks);

        $allCriteria = $programs->flatMap->criteria->sortBy('sort_order')->values();
        $validCriterionIds = $allCriteria->pluck('id')->all();
        $requestedCriteria = $this->resolveRequestedCriteria($request, $validCriterionIds);

        $criteriaRange = $this->resolveCriteriaRange($fromDate, $toDate, $requestedWeeks);
        $criteriaDayLabels = $this->buildCriteriaDayLabels($criteriaRange['from'], $criteriaRange['to']);
        $criteriaComparison = $this->buildCriteriaComparison(
            $programs,
            $requestedCriteria,
            $allCriteria,
            $criteriaDayLabels,
            $criteriaRange['from'],
            $criteriaRange['to']
        );

        return [
            'student' => $student,
            'programs' => $programs,
            'entriesByProgram' => $entriesByProgram,
            'criteriaStats' => $criteriaStats,
            'overallStats' => $overallStats,
            'dailyCompletion' => $dailyCompletion,
            'weekData' => $weekData,
            'monthData' => $monthData,
            'availableWeeks' => $availableWeeks,
            'requestedWeeks' => $requestedWeeks,
            'weekComparison' => $weekComparison,
            'allCriteria' => $allCriteria,
            'requestedCriteria' => $requestedCriteria,
            'criteriaComparison' => $criteriaComparison,
            'criteriaDayLabels' => $criteriaDayLabels,
            'criteriaRangeSource' => $criteriaRange['source'],
            'criteriaFrom' => $criteriaRange['from'],
            'criteriaTo' => $criteriaRange['to'],
            'fromDate' => $fromDate,
            'toDate' => $toDate,
        ];
    }

    protected function buildDailyCompletion($programs, Carbon $fromDate, Carbon $toDate): array
    {
        $dailyCompletion = [];
        $period = Carbon::parse($fromDate)->startOfDay();
        $end = Carbon::parse($toDate)->endOfDay();
        $allProgramIds = $programs->pluck('id');
        $criteriaById = $programs->flatMap->criteria->keyBy('id');
        $expectedPerDay = max(1, $programs->sum(fn ($p) => $p->criteria->count()));

        while ($period->lte($end)) {
            $dateStr = $period->toDateString();
            $dayEntries = ImanDailyEntry::whereIn('iman_program_id', $allProgramIds)
                ->where('entry_date', $dateStr)
                ->get();

            $actual = 0;
            foreach ($dayEntries as $entry) {
                $criterion = $criteriaById[$entry->iman_criterion_id] ?? null;
                if (! $criterion) {
                    continue;
                }
                if ($criterion->isCheckType()) {
                    if ($entry->is_checked) {
                        $actual += 1;
                    }
                } else {
                    $target = (int) ($criterion->target_value ?? 0);
                    if ($target > 0) {
                        $actual += min(1, ((int) ($entry->value ?? 0)) / $target);
                    }
                }
            }

            $dailyCompletion[] = [
                'date' => $dateStr,
                'label' => $period->format('m/d'),
                'points' => round($actual, 2),
                'completion' => $expectedPerDay > 0 ? min(100, round(($actual / $expectedPerDay) * 100)) : 0,
            ];

            $period->addDay();
        }

        return $dailyCompletion;
    }

    protected function buildAvailableWeeks(): array
    {
        $weeks = [];
        $cursor = Carbon::today()->startOfWeek(Carbon::SATURDAY);
        for ($i = 0; $i < 8; $i++) {
            $weekStart = $cursor->copy()->subWeeks($i);
            $weekEnd = $weekStart->copy()->addDays(6);
            $weeks[] = [
                'start' => $weekStart->toDateString(),
                'end' => $weekEnd->toDateString(),
                'label' => $weekStart->format('Y/m/d') . ' - ' . $weekEnd->format('Y/m/d'),
                'short' => $weekStart->format('m/d') . ' - ' . $weekEnd->format('m/d'),
            ];
        }
        return $weeks;
    }

    protected function resolveRequestedWeeks(Request $request, array $availableWeeks): array
    {
        $requestedWeeks = $request->input('compare_weeks', []);
        if (! is_array($requestedWeeks)) {
            $requestedWeeks = [];
        }
        $requestedWeeks = array_values(array_filter($requestedWeeks, fn ($w) => ! empty($w)));
        if ($request->boolean('compare_last_4')) {
            $requestedWeeks = array_slice(array_column($availableWeeks, 'start'), 0, 4);
        }
        $validStarts = array_column($availableWeeks, 'start');
        $requestedWeeks = array_values(array_filter($requestedWeeks, fn ($w) => in_array($w, $validStarts, true)));
        return array_slice($requestedWeeks, 0, 4);
    }

    protected function buildWeekComparison($programs, array $requestedWeeks): array
    {
        if (empty($requestedWeeks) || $programs->isEmpty()) {
            return [];
        }

        $allProgramIds = $programs->pluck('id');
        $criteriaById = $programs->flatMap->criteria->keyBy('id');
        $expectedPerDay = max(1, $programs->sum(fn ($p) => $p->criteria->count()));
        $dayLabels = ['السبت', 'الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة'];
        $weekComparison = [];

        foreach ($requestedWeeks as $weekStartStr) {
            $weekStart = Carbon::parse($weekStartStr)->startOfDay();
            $weekEnd = $weekStart->copy()->addDays(6);

            $rows = ImanDailyEntry::whereIn('iman_program_id', $allProgramIds)
                ->whereBetween('entry_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->get(['iman_criterion_id', 'entry_date', 'is_checked', 'value']);

            $pointsByDay = array_fill(0, 7, 0);
            foreach ($rows as $r) {
                $criterion = $criteriaById[$r->iman_criterion_id] ?? null;
                if (! $criterion) {
                    continue;
                }
                $dow = $r->entry_date->dayOfWeek;
                $idx = ($dow - Carbon::SATURDAY + 7) % 7;
                if ($criterion->isCheckType()) {
                    if ($r->is_checked) {
                        $pointsByDay[$idx] += 1;
                    }
                } else {
                    $target = (int) ($criterion->target_value ?? 0);
                    if ($target > 0) {
                        $pointsByDay[$idx] += min(1, ((int) ($r->value ?? 0)) / $target);
                    }
                }
            }

            $completionByDay = array_map(fn ($p) => min(100, round(($p / $expectedPerDay) * 100)), $pointsByDay);
            $pointsByDay = array_map(fn ($p) => round($p, 2), $pointsByDay);

            $weekComparison[] = [
                'start' => $weekStart->toDateString(),
                'end' => $weekEnd->toDateString(),
                'short' => $weekStart->format('m/d') . ' - ' . $weekEnd->format('m/d'),
                'labels' => $dayLabels,
                'points' => $pointsByDay,
                'completion' => $completionByDay,
            ];
        }

        return $weekComparison;
    }

    protected function resolveRequestedCriteria(Request $request, array $validCriterionIds): array
    {
        $requestedCriteria = $request->input('criteria_ids', []);
        if (! is_array($requestedCriteria)) {
            $requestedCriteria = [];
        }
        $requestedCriteria = array_values(array_filter(
            array_map('intval', $requestedCriteria),
            fn ($id) => in_array($id, $validCriterionIds, true)
        ));
        $requestedCriteria = array_slice(array_unique($requestedCriteria), 0, 8);
        if (empty($requestedCriteria)) {
            $requestedCriteria = array_slice($validCriterionIds, 0, min(5, count($validCriterionIds)));
        }
        return $requestedCriteria;
    }

    protected function resolveCriteriaRange(Carbon $fromDate, Carbon $toDate, array $requestedWeeks): array
    {
        $criteriaFrom = Carbon::parse($fromDate)->startOfDay();
        $criteriaTo = Carbon::parse($toDate)->startOfDay();
        $source = 'period';
        if (! empty($requestedWeeks)) {
            $weekStarts = array_map(fn ($w) => Carbon::parse($w)->startOfDay(), $requestedWeeks);
            $weekEnds = array_map(fn ($w) => Carbon::parse($w)->startOfDay()->addDays(6), $requestedWeeks);
            $criteriaFrom = collect($weekStarts)->min()->startOfDay();
            $criteriaTo = collect($weekEnds)->max()->startOfDay();
            $source = 'weeks';
        }
        return ['from' => $criteriaFrom, 'to' => $criteriaTo, 'source' => $source];
    }

    protected function buildCriteriaDayLabels(Carbon $from, Carbon $to): array
    {
        $labels = [];
        $cursor = $from->copy();
        while ($cursor->lte($to)) {
            $labels[] = [
                'date' => $cursor->toDateString(),
                'label' => $cursor->format('m/d'),
                'week_start' => $cursor->copy()->startOfWeek(Carbon::SATURDAY)->toDateString(),
            ];
            $cursor->addDay();
        }
        return $labels;
    }

    protected function buildCriteriaComparison($programs, array $requestedCriteria, $allCriteria, array $criteriaDayLabels, Carbon $from, Carbon $to): array
    {
        if (empty($requestedCriteria) || $programs->isEmpty()) {
            return [];
        }

        $rows = ImanDailyEntry::whereIn('iman_program_id', $programs->pluck('id'))
            ->whereIn('iman_criterion_id', $requestedCriteria)
            ->whereBetween('entry_date', [$from->toDateString(), $to->toDateString()])
            ->get(['iman_criterion_id', 'entry_date', 'is_checked', 'value']);

        $byCriterionDay = [];
        foreach ($rows as $r) {
            $cid = (int) $r->iman_criterion_id;
            $date = $r->entry_date->toDateString();
            if (! isset($byCriterionDay[$cid][$date])) {
                $byCriterionDay[$cid][$date] = ['check' => 0, 'value' => 0];
            }
            if ($r->is_checked) {
                $byCriterionDay[$cid][$date]['check'] = 1;
            }
            if ((int) ($r->value ?? 0) > 0) {
                $byCriterionDay[$cid][$date]['value'] = max(
                    $byCriterionDay[$cid][$date]['value'],
                    (int) $r->value
                );
            }
        }

        $comparison = [];
        foreach ($requestedCriteria as $cid) {
            $criterion = $allCriteria->firstWhere('id', $cid);
            if (! $criterion) {
                continue;
            }
            $isCheck = $criterion->isCheckType();
            $data = [];
            $totalVal = 0;
            $hits = 0;
            foreach ($criteriaDayLabels as $day) {
                $cell = $byCriterionDay[$cid][$day['date']] ?? null;
                if ($isCheck) {
                    $val = $cell ? 1 : 0;
                } else {
                    $val = $cell ? (int) $cell['value'] : 0;
                }
                $data[] = $val;
                $totalVal += $val;
                if ($val > 0) {
                    $hits++;
                }
            }

            $comparison[] = [
                'id' => (int) $criterion->id,
                'name' => $criterion->name,
                'type' => $criterion->type,
                'target' => $criterion->target_value,
                'program_name' => $criterion->program->name ?? '',
                'data' => $data,
                'total' => $totalVal,
                'hits' => $hits,
                'avg' => count($data) ? round($totalVal / count($data), 1) : 0,
            ];
        }

        return $comparison;
    }
}
