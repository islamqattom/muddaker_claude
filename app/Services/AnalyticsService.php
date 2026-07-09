<?php

namespace App\Services;

use App\Models\Student;
use App\Models\User;
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

        $studentsCount = Student::where('supervisor_id', $supervisorId)->count();

        $sessionsThisMonth = RecitationSession::where('supervisor_id', $supervisorId)
            ->where('session_date', '>=', $monthStart->toDateString())
            ->get();

        $evaluationsThisMonth = $sessionsThisMonth->count();
        $avgScoreThisMonth = $sessionsThisMonth->avg('final_score') ?? 0.0;

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

        return SupervisorPerformanceData::fromArray([
            'studentsCount' => $studentsCount,
            'evaluationsThisMonth' => $evaluationsThisMonth,
            'avgScoreThisMonth' => round($avgScoreThisMonth, 1),
            'monthlyTrend' => $monthlyTrend,
        ]);
    }

    public function getAllSupervisorsPerformance(): array
    {
        $supervisors = User::where('role', 'supervisor')->where('is_active', true)->get();
        return $supervisors->map(fn($s) => [
            'supervisor_id' => $s->id,
            'name' => $s->name,
            'data' => $this->getSupervisorPerformance($s->id),
        ])->toArray();
    }
}
