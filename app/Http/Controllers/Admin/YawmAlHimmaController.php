<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\YawmAlHimmaSession;
use Illuminate\Http\Request;

class YawmAlHimmaController extends Controller
{
    public function index(Request $request)
    {
        $days = YawmAlHimmaSession::query()
            ->selectRaw('DATE(session_date) as day, COUNT(*) as sessions_count, SUM(total_errors) as total_errors, SUM(total_hesitations) as total_hesitations')
            ->orderByDesc('day')
            ->groupBy('day')
            ->get();

        $stats = [
            'total' => YawmAlHimmaSession::count(),
            'total_errors' => YawmAlHimmaSession::sum('total_errors'),
            'total_hesitations' => YawmAlHimmaSession::sum('total_hesitations'),
            'this_month' => YawmAlHimmaSession::whereMonth('session_date', now()->month)
                ->whereYear('session_date', now()->year)->count(),
        ];

        return view('admin.yawm-al-himma.index', compact('days', 'stats'));
    }

    public function show(Request $request, $date)
    {
        $query = YawmAlHimmaSession::with('student.user')
            ->whereDate('session_date', $date)
            ->orderByDesc('id');

        if ($request->filled('supervisor_name')) {
            $query->where('supervisor_name', 'like', '%' . $request->supervisor_name . '%');
        }
        if ($request->filled('student_name')) {
            $query->where('student_name', 'like', '%' . $request->student_name . '%');
        }

        $dayStats = [
            'sessions_count' => (clone $query)->count(),
            'total_errors' => (clone $query)->sum('total_errors'),
            'total_hesitations' => (clone $query)->sum('total_hesitations'),
        ];

        // Multi-line chart data: each student gets a cumulative line over parts in this day.
        $rawSessions = (clone $query)->get(['student_name', 'parts', 'pages_data']);

        $sessions = $query->paginate(50)->withQueryString();
        $day = \Carbon\Carbon::parse($date);
        $studentParts = [];
        $allPartNumbers = collect();
        foreach ($rawSessions as $s) {
            $parts = is_array($s->parts) ? $s->parts : [];
            foreach ($parts as $idx => $partNumber) {
                $partNumber = (int) $partNumber;
                $allPartNumbers->push($partNumber);
                $studentParts[$s->student_name][$partNumber] = true;
            }
        }
        $partLabels = $allPartNumbers->unique()->sort()->values()->map(fn($n) => 'جزء ' . $n);
        $datasets = [];
        $palette = [
            '#d4a030', '#182B48', '#e74c3c', '#27ae60', '#8e44ad',
            '#2980b9', '#f39c12', '#16a085', '#c0392b', '#2c3e50',
        ];
        $i = 0;
        foreach ($studentParts as $studentName => $partsMap) {
            $data = [];
            $cumulative = 0;
            foreach ($allPartNumbers->unique()->sort()->values() as $partNumber) {
                if (isset($partsMap[$partNumber])) {
                    $cumulative++;
                }
                $data[] = $cumulative;
            }
            $color = $palette[$i % count($palette)];
            $datasets[] = [
                'label' => $studentName,
                'data' => $data,
                'borderColor' => $color,
                'backgroundColor' => $color,
                'borderWidth' => 2,
                'pointRadius' => 4,
                'pointHoverRadius' => 6,
                'tension' => 0.2,
                'fill' => false,
            ];
            $i++;
        }
        $competitionChart = [
            'labels' => $partLabels,
            'datasets' => $datasets,
        ];

        $leaderboard = collect($studentParts)
            ->map(function ($partsMap, $studentName) {
                return ['name' => $studentName, 'parts' => count($partsMap)];
            })
            ->sortByDesc('parts')
            ->values();

        $barChart = [
            'labels' => $leaderboard->pluck('name')->values(),
            'data' => $leaderboard->pluck('parts')->values(),
        ];

        // Precision chart: most precise recitation = more parts with fewer mistakes.
        // Positive scoring formula per part:
        //   Base points per part = 10
        //   Quality bonus per part = max(0, 10 − errors_per_part) × 1
        //   Fluency bonus per part = max(0, 10 − hesitations_per_part) × 0.33
        //   Final score = sum over all parts
        // To compute per-part errors/hesitations, we spread session totals evenly across parts.
        $rawAll = (clone $query)->get(['student_name', 'parts', 'total_errors', 'total_hesitations']);
        $studentPrecision = [];
        foreach ($rawAll as $s) {
            $parts = is_array($s->parts) ? $s->parts : [];
            $partsCount = count($parts);
            if ($partsCount === 0) {
                continue;
            }

            $errorsPerPart = $s->total_errors / $partsCount;
            $hesitationsPerPart = $s->total_hesitations / $partsCount;

            $partsPoints = $partsCount * 10;
            $qualityBonus = $partsCount * (max(0, 10 - $errorsPerPart) * 1);
            $fluencyBonus = $partsCount * (max(0, 10 - $hesitationsPerPart) * 0.33);
            $score = $partsPoints + $qualityBonus + $fluencyBonus;

            $studentPrecision[$s->student_name] = [
                'parts' => ($studentPrecision[$s->student_name]['parts'] ?? 0) + $partsCount,
                'errors' => ($studentPrecision[$s->student_name]['errors'] ?? 0) + $s->total_errors,
                'hesitations' => ($studentPrecision[$s->student_name]['hesitations'] ?? 0) + $s->total_hesitations,
                'parts_points' => ($studentPrecision[$s->student_name]['parts_points'] ?? 0) + $partsPoints,
                'quality_bonus' => ($studentPrecision[$s->student_name]['quality_bonus'] ?? 0) + $qualityBonus,
                'fluency_bonus' => ($studentPrecision[$s->student_name]['fluency_bonus'] ?? 0) + $fluencyBonus,
                'score' => ($studentPrecision[$s->student_name]['score'] ?? 0) + $score,
            ];
        }
        $precisionList = collect($studentPrecision)
            ->map(function ($stats, $studentName) {
                return [
                    'name' => $studentName,
                    'parts' => $stats['parts'],
                    'errors' => $stats['errors'],
                    'hesitations' => $stats['hesitations'],
                    'parts_points' => round($stats['parts_points'], 2),
                    'quality_bonus' => round($stats['quality_bonus'], 2),
                    'fluency_bonus' => round($stats['fluency_bonus'], 2),
                    'score' => round($stats['score'], 2),
                ];
            })
            ->sortByDesc('score')
            ->values();

        $precisionChart = [
            'labels' => $precisionList->pluck('name')->values(),
            'data' => $precisionList->pluck('score')->values(),
        ];

        // Grouped precision comparison: compare students who recited the same number of parts.
        $groupedByParts = $precisionList->groupBy('parts')->sortKeys();
        $groupedPrecisionCharts = [];
        foreach ($groupedByParts as $partsCount => $students) {
            if ($students->count() < 2) {
                continue;
            }
            $sorted = $students->sortByDesc('score')->values();
            $groupedPrecisionCharts[$partsCount] = [
                'labels' => $sorted->pluck('name')->values(),
                'data' => $sorted->pluck('score')->values(),
                'students' => $sorted,
            ];
        }

        return view('admin.yawm-al-himma.show', compact('sessions', 'day', 'dayStats', 'competitionChart', 'leaderboard', 'barChart', 'precisionList', 'precisionChart', 'groupedPrecisionCharts'));
    }

    public function destroy(YawmAlHimmaSession $session)
    {
        $session->delete();
        return redirect()->route('admin.yawm-al-himma.index')
            ->with('success', 'تم حذف سجل يوم الهمة بنجاح');
    }
}
