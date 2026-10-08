<?php

namespace App\Services;

use App\Models\Student;
use App\Models\User;
use App\Models\Attendance;
use App\Models\RecitationSession;
use App\Models\RecitationPage;
use App\DTO\StudentProgressData;
use App\DTO\SupervisorPerformanceData;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function getStudentProgressVelocity(int $studentId, int $weeksBack = 12): array
    {
        $startDate = Carbon::now()->subWeeks($weeksBack)->startOfWeek();

        $sessions = RecitationSession::where('student_id', $studentId)
            ->where('type', 'new_memorization')
            ->where('session_date', '>=', $startDate->toDateString())
            ->with('pages')
            ->get();

        $velocity = [];
        for ($i = $weeksBack - 1; $i >= 0; $i--) {
            $weekStart = Carbon::now()->subWeeks($i)->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();
            $monthKey = $weekStart->format('Y-W');

            $pagesThisWeek = $sessions
                ->filter(function ($session) use ($weekStart, $weekEnd) {
                    $sessionDate = Carbon::parse($session->session_date);
                    return $sessionDate->gte($weekStart) && $sessionDate->lte($weekEnd);
                })
                ->sum(function ($session) {
                    return $session->pages->count();
                });

            $velocity[$monthKey] = $pagesThisWeek;
        }

        return $velocity;
    }

    public function getPageDifficultyHeatmap(int $studentId): array
    {
        $pages = RecitationPage::join('recitation_sessions', 'recitation_pages.session_id', '=', 'recitation_sessions.id')
            ->where('recitation_sessions.student_id', $studentId)
            ->select('recitation_pages.page_number')
            ->selectRaw('SUM(recitation_pages.errors) as total_errors, COUNT(*) as total_reviews')
            ->groupBy('recitation_pages.page_number')
            ->get()
            ->keyBy('page_number');

        $heatmap = [];
        for ($i = 1; $i <= 604; $i++) {
            $pageData = $pages->get($i);
            if ($pageData && $pageData->total_reviews > 0) {
                $heatmap[$i] = $pageData->total_errors / ($pageData->total_reviews * 2);
                $heatmap[$i] = min(1.0, max(0.0, $heatmap[$i]));
            } else {
                $heatmap[$i] = null;
            }
        }

        return $heatmap;
    }

    public function getStudentProgressData(int $studentId): StudentProgressData
    {
        $student = Student::with('level')->findOrFail($studentId);
        $level = $student->level;

        $velocity = $this->getStudentProgressVelocity($studentId);
        $heatmap = $this->getPageDifficultyHeatmap($studentId);

        $recentSessions = RecitationSession::where('student_id', $studentId)
            ->with('part')
            ->orderBy('session_date', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'type' => $s->type,
                'score' => $s->final_score,
                'date' => $s->session_date->toDateString(),
                'part_id' => $s->part_id,
            ])
            ->toArray();

        $pagesDone = $student->current_page ?? 0;
        $pagesTotal = $level ? ($level->new_memorization_pages ?? 604) : 604;
        $completionPercent = $pagesTotal > 0 ? round(($pagesDone / $pagesTotal) * 100, 1) : 0;

        return StudentProgressData::fromArray([
            'velocityWeeks' => $velocity,
            'pageDifficulty' => $heatmap,
            'currentLevelPagesTotal' => $pagesTotal,
            'currentLevelPagesDone' => $pagesDone,
            'completionPercent' => $completionPercent,
            'recentSessions' => $recentSessions,
        ]);
    }

    public function getSupervisorPerformance(int $supervisorId): SupervisorPerformanceData
    {
        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();

        $studentsCount = Student::where('supervisor_id', $supervisorId)->active()->count();

        $sessionsThisMonth = RecitationSession::where('supervisor_id', $supervisorId)
            ->where('session_date', '>=', $monthStart->toDateString())
            ->get();

        $evaluationsThisMonth = $sessionsThisMonth->count();
        $avgScoreThisMonth = $sessionsThisMonth->avg('final_score') ?? 0.0;

        $passedCount = $sessionsThisMonth->where('status', 'passed')->count();
        $failedCount = $sessionsThisMonth->where('status', 'failed')->count();
        $decidedCount = $passedCount + $failedCount;
        $passRate = $decidedCount > 0 ? round(($passedCount / $decidedCount) * 100, 1) : 0.0;

        $studentIds = Student::where('supervisor_id', $supervisorId)->active()->pluck('id');
        $attendanceRows = Attendance::whereIn('student_id', $studentIds)
            ->where('date', '>=', $monthStart->toDateString())
            ->get();
        $attendanceTotal = $attendanceRows->count();
        $attendancePresent = $attendanceRows->whereIn('status', ['present', 'late'])->count();
        $attendanceRate = $attendanceTotal > 0
            ? round(($attendancePresent / $attendanceTotal) * 100, 1)
            : 0.0;

        $evaluationsPerStudent = $studentsCount > 0
            ? round($evaluationsThisMonth / $studentsCount, 1)
            : 0.0;

        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $mStart = $now->copy()->subMonths($i)->startOfMonth();
            $mEnd = $mStart->copy()->endOfMonth();
            $monthKey = $mStart->format('Y-m');
            $count = RecitationSession::where('supervisor_id', $supervisorId)
                ->whereBetween('session_date', [$mStart->toDateString(), $mEnd->toDateString()])
                ->count();
            $monthlyTrend[$monthKey] = $count;
        }

        $weeklyTrend = ['labels' => [], 'evaluations' => [], 'avgScore' => []];
        for ($i = 3; $i >= 0; $i--) {
            $wStart = $now->copy()->subWeeks($i)->startOfWeek();
            $wEnd = $wStart->copy()->endOfWeek();
            $label = $wStart->format('d/m');
            $weeklyTrend['labels'][] = $label;
            $weeklyTrend['evaluations'][] = RecitationSession::where('supervisor_id', $supervisorId)
                ->whereBetween('session_date', [$wStart->toDateString(), $wEnd->toDateString()])
                ->count();
            $weeklyTrend['avgScore'][] = round((float) (RecitationSession::where('supervisor_id', $supervisorId)
                ->whereBetween('session_date', [$wStart->toDateString(), $wEnd->toDateString()])
                ->avg('final_score') ?? 0), 1);
        }

        return SupervisorPerformanceData::fromArray([
            'studentsCount' => $studentsCount,
            'evaluationsThisMonth' => $evaluationsThisMonth,
            'avgScoreThisMonth' => round($avgScoreThisMonth, 1),
            'monthlyTrend' => $monthlyTrend,
            'passRate' => $passRate,
            'attendanceRate' => $attendanceRate,
            'evaluationsPerStudent' => $evaluationsPerStudent,
            'passedCount' => $passedCount,
            'failedCount' => $failedCount,
            'weeklyTrend' => $weeklyTrend,
        ]);
    }

    public function getAllSupervisorsPerformance(): array
    {
        $supervisors = User::where('role', 'supervisor')->where('is_active', true)->get();

        $cards = $supervisors->map(function ($s) {
            $data = $this->getSupervisorPerformance($s->id);

            $performanceScore = round(
                ($data->avgScoreThisMonth * 0.4) +
                ($data->passRate * 0.3) +
                ($data->attendanceRate * 0.2) +
                (min($data->evaluationsPerStudent, 5) * 4 * 0.1),
                1
            );

            $level = match (true) {
                $performanceScore >= 80 => 'excellent',
                $performanceScore >= 60 => 'good',
                $performanceScore >= 40 => 'average',
                default => 'needs_improvement',
            };

            return [
                'supervisor_id' => $s->id,
                'name' => $s->name,
                'data' => SupervisorPerformanceData::fromArray([
                    'studentsCount' => $data->studentsCount,
                    'evaluationsThisMonth' => $data->evaluationsThisMonth,
                    'avgScoreThisMonth' => $data->avgScoreThisMonth,
                    'monthlyTrend' => $data->monthlyTrend,
                    'passRate' => $data->passRate,
                    'attendanceRate' => $data->attendanceRate,
                    'evaluationsPerStudent' => $data->evaluationsPerStudent,
                    'passedCount' => $data->passedCount,
                    'failedCount' => $data->failedCount,
                    'weeklyTrend' => $data->weeklyTrend,
                    'performanceScore' => $performanceScore,
                    'performanceLevel' => $level,
                ]),
            ];
        })->toArray();

        $sorted = collect($cards)->sortByDesc(fn ($c) => $c['data']->performanceScore)->values();
        $total = $sorted->count();
        $ranked = [];
        foreach ($sorted as $idx => $card) {
            $rank = $idx + 1;
            $ranked[] = [
                'supervisor_id' => $card['supervisor_id'],
                'name' => $card['name'],
                'data' => SupervisorPerformanceData::fromArray([
                    'studentsCount' => $card['data']->studentsCount,
                    'evaluationsThisMonth' => $card['data']->evaluationsThisMonth,
                    'avgScoreThisMonth' => $card['data']->avgScoreThisMonth,
                    'monthlyTrend' => $card['data']->monthlyTrend,
                    'passRate' => $card['data']->passRate,
                    'attendanceRate' => $card['data']->attendanceRate,
                    'evaluationsPerStudent' => $card['data']->evaluationsPerStudent,
                    'passedCount' => $card['data']->passedCount,
                    'failedCount' => $card['data']->failedCount,
                    'weeklyTrend' => $card['data']->weeklyTrend,
                    'performanceScore' => $card['data']->performanceScore,
                    'performanceLevel' => $card['data']->performanceLevel,
                    'rank' => $rank,
                    'totalSupervisors' => $total,
                ]),
            ];
        }

        return $ranked;
    }
}
