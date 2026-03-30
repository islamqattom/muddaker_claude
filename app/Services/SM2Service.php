<?php

namespace App\Services;

/**
 * SM-2 Spaced Repetition Algorithm Service
 *
 * Implements the SuperMemo 2 algorithm for scheduling verse reviews.
 * Based on: https://www.supermemo.com/en/archives1990-2015/english/ol/sm2
 */
class SM2Service
{
    /**
     * Default ease factor for new items
     */
    public const DEFAULT_EASE_FACTOR = 2.5;

    /**
     * Minimum ease factor allowed
     */
    public const MIN_EASE_FACTOR = 1.3;

    /**
     * Maximum interval in days (10 years)
     */
    public const MAX_INTERVAL = 3650;

    /**
     * Quality ratings
     * 0 - Complete blackout
     * 1 - Incorrect, but upon seeing correct answer it was obvious
     * 2 - Incorrect, but correct answer seemed easy to recall
     * 3 - Correct with serious difficulty
     * 4 - Correct after hesitation
     * 5 - Perfect response
     */
    public const QUALITY_BLACKOUT = 0;
    public const QUALITY_INCORRECT_OBVIOUS = 1;
    public const QUALITY_INCORRECT_EASY = 2;
    public const QUALITY_DIFFICULT = 3;
    public const QUALITY_HESITATION = 4;
    public const QUALITY_PERFECT = 5;

    /**
     * Calculate the next review schedule based on SM-2 algorithm
     *
     * @param int $repetitions Number of successful repetitions
     * @param float $easeFactor Current ease factor (difficulty multiplier)
     * @param int $interval Current interval in days
     * @param int $quality Quality of the response (0-5)
     * @return array{repetitions: int, ease_factor: float, interval: int}
     */
    public function calculateNext(int $repetitions, float $easeFactor, int $interval, int $quality): array
    {
        // If quality < 3, start repetitions over (forgotten)
        if ($quality < 3) {
            return [
                'repetitions' => 0,
                'ease_factor' => max(self::MIN_EASE_FACTOR, $easeFactor - 0.2),
                'interval' => 1,
            ];
        }

        // Calculate new ease factor
        $newEaseFactor = $easeFactor + (0.1 - (5 - $quality) * (0.08 + (5 - $quality) * 0.02));
        $newEaseFactor = max(self::MIN_EASE_FACTOR, $newEaseFactor);

        // Calculate new interval
        if ($repetitions === 0) {
            $newInterval = 1;
        } elseif ($repetitions === 1) {
            $newInterval = 6;
        } else {
            $newInterval = (int) round($interval * $newEaseFactor);
            $newInterval = min(self::MAX_INTERVAL, $newInterval); // Cap interval to prevent overflow
        }

        return [
            'repetitions' => $repetitions + 1,
            'ease_factor' => $newEaseFactor,
            'interval' => $newInterval,
        ];
    }

    /**
     * Calculate the next review date based on interval
     *
     * @param int $interval Interval in days
     * @param string|null $fromDate Base date (defaults to today)
     * @return \Carbon\Carbon
     */
    public function calculateNextReviewDate(int $interval, ?string $fromDate = null): \Carbon\Carbon
    {
        $baseDate = $fromDate ? \Carbon\Carbon::parse($fromDate) : \Carbon\Carbon::today();
        return $baseDate->addDays($interval);
    }

    /**
     * Determine if a review item is due for review
     *
     * @param \Carbon\Carbon|null $nextReviewDate
     * @return bool
     */
    public function isDue(?\Carbon\Carbon $nextReviewDate): bool
    {
        return !$nextReviewDate || $nextReviewDate->lte(\Carbon\Carbon::today());
    }

    /**
     * Get urgency level for a review item
     *
     * @param \Carbon\Carbon|null $nextReviewDate
     * @return string 'overdue', 'due_today', 'upcoming', 'future'
     */
    public function getUrgency(?\Carbon\Carbon $nextReviewDate): string
    {
        if (!$nextReviewDate) {
            return 'overdue';
        }

        $diff = \Carbon\Carbon::today()->diffInDays($nextReviewDate, false);

        return match (true) {
            $diff < 0 => 'overdue',
            $diff === 0 => 'due_today',
            $diff <= 2 => 'upcoming',
            default => 'future',
        };
    }

    /**
     * Map a 1-5 simple rating to SM-2 quality
     *
     * @param int $rating User rating from 1-5
     * @return int SM-2 quality (0-5)
     */
    public function mapRatingToQuality(int $rating): int
    {
        if ($rating < 1 || $rating > 5) {
            throw new \InvalidArgumentException("Rating must be between 1 and 5, got {$rating}");
        }

        return match ($rating) {
            // Rating 1: Complete blackout - correct
            1 => self::QUALITY_BLACKOUT,
            // Rating 2: Very difficult (صعب جداً) - student DID recite correctly but with difficulty
            // SM-2 quality 3 = correct with serious difficulty
            2 => self::QUALITY_DIFFICULT,
            // Rating 3: Correct with difficulty - SM-2 quality 3
            3 => self::QUALITY_DIFFICULT,
            // Rating 4: Correct after hesitation - SM-2 quality 4
            4 => self::QUALITY_HESITATION,
            // Rating 5: Perfect - SM-2 quality 5
            5 => self::QUALITY_PERFECT,
            default => self::QUALITY_BLACKOUT,
        };
    }

    /**
     * Get human-readable quality label in Arabic
     *
     * @param int $quality
     * @return string
     */
    public function getQualityLabel(int $quality): string
    {
        return match ($quality) {
            self::QUALITY_BLACKOUT => 'لم أتذكر',
            self::QUALITY_INCORRECT_OBVIOUS => 'تذكرت بصعوبة',
            self::QUALITY_INCORRECT_EASY => 'أخطأت لكنه كان سهلاً',
            self::QUALITY_DIFFICULT => 'صحيح بصعوبة',
            self::QUALITY_HESITATION => 'صحيح مع تردد',
            self::QUALITY_PERFECT => 'ممتاز',
            default => 'غير معروف',
        };
    }
}
