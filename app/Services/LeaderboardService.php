<?php

namespace App\Services;

use App\Models\Student;
use App\Models\LeaderboardEntry;
use App\Models\RecitationSession;
use App\Models\DailyProgress;
use App\Models\ReviewItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Leaderboard Service
 *
 * Calculates and caches student rankings across different metrics.
 */
class LeaderboardService
{
    /**
     * Cache TTL in seconds (1 hour)
     */
    public const CACHE_TTL = 3600;

    /**
     * Leaderboard types
     */
    public const TYPE_PAGES = 'pages';
    public const TYPE_ACCURACY = 'accuracy';
    public const TYPE_STREAK = 'streak';
    public const TYPE_REVIEWS = 'reviews';

    /**
     * Available periods
     */
    public const PERIOD_WEEKLY = 'weekly';
    public const PERIOD_MONTHLY = 'monthly';

    /**
     * Get cached leaderboard data or rebuild if stale
     *
     * @param string $period weekly or monthly
     * @param string $type pages, accuracy, streak, or reviews
     * @param int|null $limit Number of entries to return
     * @return Collection
     */
    public function getLeaderboard(string $period, string $type, ?int $limit = null): Collection
    {
        $cacheKey = "leaderboard_{$period}_{$type}";
        $limit ??= 100;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($period, $type) {
            return $this->buildLeaderboard($period, $type);
        })->take($limit);
    }

    /**
     * Build fresh leaderboard data
     *
     * @param string $period
     * @param string $type
     * @return Collection
     */
    public function buildLeaderboard(string $period, string $type): Collection
    {
        $students = Student::active()->with('user', 'level')->get()->keyBy('id');
        [$startDate, $endDate] = $this->getPeriodRange($period);

        $scores = match ($type) {
            self::TYPE_PAGES => $this->getPagesScores($startDate, $endDate),
            self::TYPE_ACCURACY => $this->getAccuracyScores($startDate, $endDate),
            self::TYPE_STREAK => $this->getStreakScores($students),
            self::TYPE_REVIEWS => $this->getReviewsScores($startDate, $endDate),
            default => collect(),
        };

        return $this->buildRankingsFromScores($students, $scores, $type);
    }

    /**
     * Get pages memorized scores using single aggregate query
     *
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    private function getPagesScores(string $startDate, string $endDate): Collection
    {
        return DailyProgress::whereBetween('date', [$startDate, $endDate])
            ->select('student_id', DB::raw('SUM(pages_memorized) as score'))
            ->groupBy('student_id')
            ->get()
            ->map(fn($row) => ['student_id' => $row->student_id, 'score' => (float) $row->score])
            ->keyBy('student_id');
    }

    /**
     * Get accuracy scores using single aggregate query
     *
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    private function getAccuracyScores(string $startDate, string $endDate): Collection
    {
        return RecitationSession::whereBetween('session_date', [$startDate, $endDate])
            ->whereIn('status', ['passed', 'failed'])
            ->select('student_id', DB::raw('AVG(final_score) as score'))
            ->groupBy('student_id')
            ->get()
            ->map(fn($row) => ['student_id' => $row->student_id, 'score' => round((float) $row->score, 1)])
            ->keyBy('student_id');
    }

    /**
     * Get streak scores from students collection
     *
     * @param Collection $students
     * @return Collection
     */
    private function getStreakScores(Collection $students): Collection
    {
        return $students->map(fn($s) => ['student_id' => $s->id, 'score' => (float) $s->current_streak])->keyBy('student_id');
    }

    /**
     * Get reviews completed scores using single aggregate query
     *
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    private function getReviewsScores(string $startDate, string $endDate): Collection
    {
        return DB::table('recitation_reviews')
            ->whereBetween('reviewed_at', [$startDate, $endDate])
            ->select('student_id', DB::raw('COUNT(*) as score'))
            ->groupBy('student_id')
            ->get()
            ->map(fn($row) => ['student_id' => $row->student_id, 'score' => (float) $row->score])
            ->keyBy('student_id');
    }

    /**
     * Build rankings from pre-computed scores
     *
     * @param Collection $students
     * @param Collection $scores
     * @param string $type
     * @return Collection
     */
    private function buildRankingsFromScores(Collection $students, Collection $scores, string $type): Collection
    {
        $rankings = $students->map(function ($student) use ($scores, $type) {
            $scoreData = $scores->get($student->id);
            return [
                'student_id' => $student->id,
                'student_name' => $student->name,
                'level_name' => $student->level?->name ?? '-',
                'score' => $scoreData['score'] ?? 0,
                'type' => $type,
            ];
        })
        ->sortByDesc('score')
        ->values()
        ->map(fn($item, $index) => array_merge($item, ['rank' => $index + 1]));

        return $rankings;
    }

    /**
     * Get period date range
     *
     * @param string $period
     * @return array{start: string, end: string}
     */
    private function getPeriodRange(string $period): array
    {
        $end = Carbon::today();

        $start = match ($period) {
            self::PERIOD_WEEKLY => $end->copy()->startOfWeek(),
            self::PERIOD_MONTHLY => $end->copy()->startOfMonth(),
            default => $end->copy()->startOfWeek(),
        };

        return [$start->toDateString(), $end->toDateString()];
    }

    /**
     * Get a student's rank for a specific period and type
     *
     * @param Student $student
     * @param string $period
     * @param string $type
     * @return array{rank: int, score: float}|null
     */
    public function getStudentRank(Student $student, string $period, string $type): ?array
    {
        $leaderboard = $this->getLeaderboard($period, $type);
        $entry = $leaderboard->firstWhere('student_id', $student->id);

        if ($entry) {
            return [
                'rank' => $entry['rank'],
                'score' => $entry['score'],
            ];
        }

        return null;
    }

    /**
     * Get surrounding students (3 above and below)
     *
     * @param Student $student
     * @param string $period
     * @param string $type
     * @return array{above: Collection, below: Collection}
     */
    public function getSurroundingStudents(Student $student, string $period, string $type): array
    {
        $leaderboard = $this->getLeaderboard($period, $type);
        $currentIndex = $leaderboard->search(fn($e) => $e['student_id'] === $student->id);

        if ($currentIndex === false) {
            return ['above' => collect(), 'below' => collect()];
        }

        return [
            'above' => $leaderboard->slice(max(0, $currentIndex - 3), 3),
            'below' => $leaderboard->slice($currentIndex + 1, 3),
        ];
    }

    /**
     * Refresh all leaderboard caches
     *
     * @return void
     */
    public function refreshAll(): void
    {
        foreach ($this->getAllCacheKeys() as $cacheKey) {
            Cache::forget($cacheKey);
            [$period, $type] = $this->parseCacheKey($cacheKey);
            $this->getLeaderboard($period, $type);
        }
    }

    /**
     * Clear all leaderboard caches
     *
     * @return void
     */
    public function clearCache(): void
    {
        foreach ($this->getAllCacheKeys() as $cacheKey) {
            Cache::forget($cacheKey);
        }
    }

    /**
     * Get all leaderboard cache keys
     *
     * @return array<string>
     */
    private function getAllCacheKeys(): array
    {
        $periods = [self::PERIOD_WEEKLY, self::PERIOD_MONTHLY];
        $types = [self::TYPE_PAGES, self::TYPE_ACCURACY, self::TYPE_STREAK, self::TYPE_REVIEWS];

        $keys = [];
        foreach ($periods as $period) {
            foreach ($types as $type) {
                $keys[] = "leaderboard_{$period}_{$type}";
            }
        }
        return $keys;
    }

    /**
     * Parse cache key into period and type
     *
     * @param string $cacheKey
     * @return array{0: string, 1: string}
     */
    private function parseCacheKey(string $cacheKey): array
    {
        if (!preg_match('/leaderboard_(.+)_(.+)/', $cacheKey, $matches)) {
            Log::warning('LeaderboardService: Failed to parse cache key', ['cacheKey' => $cacheKey]);
            return ['', ''];
        }
        return [$matches[1], $matches[2]];
    }

    /**
     * Get leaderboard type labels in Arabic
     *
     * @param string $type
     * @return string
     */
    public static function getTypeLabel(string $type): string
    {
        return match ($type) {
            self::TYPE_PAGES => 'الصفحات المحفوظة',
            self::TYPE_ACCURACY => 'معدل الدقة',
            self::TYPE_STREAK => 'السلسلة الحالية',
            self::TYPE_REVIEWS => 'المراجعات المكتملة',
            default => $type,
        };
    }

    /**
     * Get period labels in Arabic
     *
     * @param string $period
     * @return string
     */
    public static function getPeriodLabel(string $period): string
    {
        return match ($period) {
            self::PERIOD_WEEKLY => 'الأسبوع الحالي',
            self::PERIOD_MONTHLY => 'الشهر الحالي',
            default => $period,
        };
    }
}
