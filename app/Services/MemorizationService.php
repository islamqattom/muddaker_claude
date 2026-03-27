<?php

namespace App\Services;

use App\Models\Student;
use App\Models\MemorizationGoal;
use App\Models\DailyProgress;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MemorizationService
{
    public function getOrCreateGoal(Student $student): MemorizationGoal
    {
        $goal = MemorizationGoal::where('student_id', $student->id)->active()->first();

        if (!$goal) {
            $goal = MemorizationGoal::create([
                'student_id' => $student->id,
                'period' => 'daily',
                'target_pages' => 2,
                'is_active' => true,
            ]);
        }

        return $goal;
    }

    public function updateGoal(Student $student, array $data): MemorizationGoal
    {
        $goal = $this->getOrCreateGoal($student);
        $goal->update($data);
        return $goal->fresh();
    }

    public function recordProgress(
        Student $student,
        int $pagesMemorized = 0,
        int $pagesReviewed = 0,
        int $minutes = 0,
        int $verses = 0
    ): DailyProgress {
        $today = Carbon::today();

        $progress = DailyProgress::firstOrCreate(
            ['student_id' => $student->id, 'date' => $today],
            ['pages_memorized' => 0, 'pages_reviewed' => 0, 'minutes_practiced' => 0, 'verses_recited' => 0]
        );

        $progress->pages_memorized += $pagesMemorized;
        $progress->pages_reviewed += $pagesReviewed;
        $progress->minutes_practiced += $minutes;
        $progress->verses_recited += $verses;

        $goal = $this->getOrCreateGoal($student);
        if ($goal->period === 'daily' && $progress->pages_memorized >= $goal->target_pages) {
            $progress->goal_completed = true;
        }

        $progress->save();

        $this->checkStreak($student);

        return $progress;
    }

    public function checkStreak(Student $student): int
    {
        return DB::transaction(function () use ($student) {
            // Lock the student row for update to prevent race conditions
            $lockedStudent = Student::where('id', $student->id)->lockForUpdate()->first();

            $today = Carbon::today();
            $yesterday = $today->copy()->subDay();

            $lastMemorization = $lockedStudent->last_memorization_date
                ? Carbon::parse($lockedStudent->last_memorization_date)
                : null;

            if (!$lastMemorization) {
                $lockedStudent->current_streak = 1;
                $lockedStudent->longest_streak = max(1, $lockedStudent->longest_streak);
            } elseif ($lastMemorization->isSameDay($yesterday) || $lastMemorization->isSameDay($today)) {
                if (!$lastMemorization->isSameDay($today)) {
                    $lockedStudent->current_streak++;
                }
                $lockedStudent->longest_streak = max($lockedStudent->current_streak, $lockedStudent->longest_streak);
            } else {
                $lockedStudent->current_streak = 1;
            }

            $lockedStudent->last_memorization_date = $today;
            $lockedStudent->save();

            // Update the original student object
            $student->current_streak = $lockedStudent->current_streak;
            $student->longest_streak = $lockedStudent->longest_streak;
            $student->last_memorization_date = $lockedStudent->last_memorization_date;

            return $lockedStudent->current_streak;
        });
    }

    public function getWeeklyProgress(Student $student): array
    {
        $startOfWeek = Carbon::now()->startOfWeek();

        $progress = DailyProgress::where('student_id', $student->id)
            ->where('date', '>=', $startOfWeek->toDateString())
            ->orderBy('date')
            ->get();

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $startOfWeek->copy()->addDays($i);
            $dayProgress = $progress->firstWhere('date', $day->toDateString());
            $days[] = [
                'date' => $day->format('Y-m-d'),
                'day_name' => $day->locale('ar')->dayName,
                'pages_memorized' => $dayProgress?->pages_memorized ?? 0,
                'pages_reviewed' => $dayProgress?->pages_reviewed ?? 0,
                'minutes_practiced' => $dayProgress?->minutes_practiced ?? 0,
                'goal_completed' => $dayProgress?->goal_completed ?? false,
            ];
        }

        return $days;
    }

    public function getMonthlyProgress(Student $student): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();

        $progress = DailyProgress::where('student_id', $student->id)
            ->where('date', '>=', $startOfMonth->toDateString())
            ->orderBy('date')
            ->get();

        $weeks = [];
        $currentWeek = $startOfMonth->copy()->startOfWeek();
        $now = Carbon::now();

        while ($currentWeek->lte($now)) {
            $weekStart = $currentWeek->copy();
            $weekEnd = $currentWeek->copy()->endOfWeek();

            $weekData = $progress->filter(function ($p) use ($weekStart, $weekEnd) {
                $date = Carbon::parse($p->date);
                return $date->gte($weekStart) && $date->lte($weekEnd);
            });

            $weeks[] = [
                'week_start' => $weekStart->format('Y-m-d'),
                'week_label' => $weekStart->locale('ar')->isoFormat('MMM Do'),
                'pages_memorized' => $weekData->sum('pages_memorized'),
                'pages_reviewed' => $weekData->sum('pages_reviewed'),
                'minutes_practiced' => $weekData->sum('minutes_practiced'),
                'days_active' => $weekData->count(),
            ];

            $currentWeek->addWeek();
        }

        return $weeks;
    }

    public function getTodayProgress(Student $student): ?DailyProgress
    {
        return DailyProgress::where('student_id', $student->id)
            ->where('date', Carbon::today())
            ->first();
    }
}
