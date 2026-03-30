<?php

namespace App\Services;

use App\Models\Student;
use App\Models\WeakArea;
use App\Models\RecitationSession;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Weak Area Detection Service
 *
 * Analyzes recitation sessions to identify verses where
 * students consistently make errors or hesitations.
 */
class WeakAreaService
{
    /**
     * Error rate threshold for flagging as weak (30%)
     */
    public const ERROR_RATE_THRESHOLD = 0.3;

    /**
     * Hesitation rate threshold for flagging as weak (40%)
     */
    public const HESITATION_RATE_THRESHOLD = 0.4;

    /**
     * Minimum attempts before an area can be flagged as weak
     */
    public const MIN_ATTEMPTS = 2;

    /**
     * Detect weak areas for a student from their recitation sessions
     *
     * @param Student $student
     * @return Collection<WeakArea>
     */
    public function detectWeakAreas(Student $student): Collection
    {
        // Get all sessions with errors for this student
        $sessions = RecitationSession::where('student_id', $student->id)
            ->where(function ($query) {
                $query->where('total_errors', '>', 0)
                      ->orWhere('total_hesitations', '>', 0);
            })
            ->get();

        // Group by surah and verse range (use page as proxy)
        $pageStats = [];

        foreach ($sessions as $session) {
            if (!$session->start_page || !$session->end_page) {
                continue;
            }

            for ($page = $session->start_page; $page <= $session->end_page; $page++) {
                if (!isset($pageStats[$page])) {
                    $pageStats[$page] = [
                        'page' => $page,
                        'errors' => 0,
                        'hesitations' => 0,
                        'attempts' => 0,
                    ];
                }

                // Distribute errors/hesitations across pages
                $pagesInSession = $session->end_page - $session->start_page + 1;
                $pageErrors = (int) round($session->total_errors / $pagesInSession);
                $pageHesitations = (int) round($session->total_hesitations / $pagesInSession);

                $pageStats[$page]['errors'] += $pageErrors;
                $pageStats[$page]['hesitations'] += $pageHesitations;
                $pageStats[$page]['attempts']++;
            }
        }

        // Calculate rates and identify weak areas
        $weakAreas = collect();

        foreach ($pageStats as $page => $stats) {
            if ($stats['attempts'] < self::MIN_ATTEMPTS) {
                continue;
            }

            $errorRate = $stats['errors'] / $stats['attempts'];
            $hesitationRate = $stats['hesitations'] / $stats['attempts'];

            if ($errorRate >= self::ERROR_RATE_THRESHOLD || $hesitationRate >= self::HESITATION_RATE_THRESHOLD) {
                $priorityScore = ($errorRate * 0.6) + ($hesitationRate * 0.4);

                $weakAreas->push([
                    'student_id' => $student->id,
                    'surah' => $this->getSurahFromPage($page),
                    'verse_start' => $this->getVerseStartFromPage($page),
                    'verse_end' => $this->getVerseEndFromPage($page),
                    'page_number' => $page,
                    'error_count' => $stats['errors'],
                    'hesitation_count' => $stats['hesitations'],
                    'attempt_count' => $stats['attempts'],
                    'error_rate' => round($errorRate, 3),
                    'hesitation_rate' => round($hesitationRate, 3),
                    'priority_score' => round($priorityScore, 3),
                ]);
            }
        }

        // Sort by priority score descending
        return $weakAreas->sortByDesc('priority_score')->values();
    }

    /**
     * Sync detected weak areas to the database
     *
     * @param Student $student
     * @return int Number of weak areas identified
     */
    public function syncWeakAreas(Student $student): int
    {
        return DB::transaction(function () use ($student) {
            // Detect new weak areas
            $detected = $this->detectWeakAreas($student);

            if ($detected->isEmpty()) {
                // No weak areas found, mark all existing as remediated
                $student->weakAreas()->update(['is_remediated' => true]);
                return 0;
            }

            // Get all existing non-remediated weak areas in one query
            $existingAreas = $student->weakAreas()
                ->where('is_remediated', false)
                ->get()
                ->keyBy('page_number');

            foreach ($detected as $area) {
                $existing = $existingAreas->get($area['page_number']);

                if ($existing) {
                    // Update stats, keep is_remediated as false
                    $existing->update([
                        'error_count' => $area['error_count'],
                        'hesitation_count' => $area['hesitation_count'],
                        'attempt_count' => $area['attempt_count'],
                        'error_rate' => $area['error_rate'],
                        'hesitation_rate' => $area['hesitation_rate'],
                        'priority_score' => $area['priority_score'],
                    ]);
                } else {
                    // Create new weak area
                    WeakArea::create($area);
                }
            }

            // Mark detected page numbers to keep as not remediated
            $detectedPages = $detected->pluck('page_number')->toArray();
            $student->weakAreas()
                ->whereNotIn('page_number', $detectedPages)
                ->update(['is_remediated' => true]);

            return $detected->count();
        });
    }

    /**
     * Mark a weak area as remediated
     *
     * @param int $weakAreaId
     * @return bool True if remediated, false if not found
     * @throws \Exception if save fails
     */
    public function markAsRemediated(int $weakAreaId): bool
    {
        $weakArea = WeakArea::find($weakAreaId);

        if (!$weakArea) {
            return false;
        }

        $weakArea->is_remediated = true;

        if (!$weakArea->save()) {
            throw new \Exception('Failed to save weak area remediation');
        }

        return true;
    }

    /**
     * Get top weak areas for a student
     *
     * @param Student $student
     * @param int $limit
     * @return Collection
     */
    public function getTopWeakAreas(Student $student, int $limit = 3): Collection
    {
        return $student->weakAreas()
            ->where('is_remediated', false)
            ->orderByDesc('priority_score')
            ->limit($limit)
            ->get();
    }

    /**
     * Get weak area count for dashboard
     *
     * @param Student $student
     * @return int
     */
    public function getWeakAreaCount(Student $student): int
    {
        return $student->weakAreas()
            ->where('is_remediated', false)
            ->count();
    }

    /**
     * Get progress on remediating weak areas
     *
     * @param Student $student
     * @return array{total: int, remediated: int, percentage: float}
     */
    public function getRemediationProgress(Student $student): array
    {
        $total = $student->weakAreas()->count();
        $remediated = $student->weakAreas()->where('is_remediated', true)->count();

        return [
            'total' => $total,
            'remediated' => $remediated,
            'percentage' => $total > 0 ? round(($remediated / $total) * 100, 1) : 0,
        ];
    }

    /**
     * Approximate surah from page number
     * Uses a simple lookup table for common page ranges
     *
     * @param int $page
     * @return int
     */
    private function getSurahFromPage(int $page): int
    {
        // Page to surah mapping based on standard Mushaf al-Madina layout
        // Each entry is the starting page for that surah
        $pageToSurah = [
            1 => 1, 2 => 2, 50 => 3, 77 => 4, 103 => 5, 106 => 6, 114 => 7,
            125 => 8, 136 => 9, 150 => 10, 177 => 11, 187 => 12, 208 => 13,
            221 => 14, 235 => 15, 249 => 16, 255 => 17, 272 => 18, 282 => 19,
            295 => 20, 304 => 21, 312 => 22, 321 => 23, 332 => 24, 342 => 25,
            350 => 26, 359 => 27, 367 => 28, 377 => 29, 386 => 30, 396 => 31,
            404 => 32, 411 => 33, 419 => 34, 428 => 35, 434 => 36, 446 => 37,
            453 => 38, 458 => 39, 467 => 40, 477 => 41, 483 => 42, 489 => 43,
            496 => 44, 499 => 45, 502 => 46, 507 => 48, 511 => 49, 515 => 50,
            518 => 51, 520 => 52, 523 => 53, 526 => 54, 528 => 55, 531 => 56,
            534 => 57, 537 => 58, 542 => 59, 545 => 60, 548 => 61, 551 => 62,
            553 => 63, 556 => 64, 559 => 65, 561 => 66, 564 => 67, 566 => 68,
            568 => 69, 570 => 70, 572 => 71, 574 => 72, 576 => 73, 578 => 74,
            580 => 75, 582 => 76, 585 => 77, 587 => 78, 590 => 79, 592 => 80,
            594 => 81, 596 => 82, 598 => 83, 600 => 84, 602 => 85, 604 => 86,
            606 => 87, 608 => 88, 610 => 89, 612 => 90, 613 => 91, 614 => 92,
            615 => 93, 616 => 94, 617 => 95, 618 => 96, 620 => 97, 621 => 98,
            623 => 99, 624 => 100, 625 => 101, 626 => 102, 627 => 103, 628 => 104,
            629 => 105, 630 => 107, 631 => 109, 632 => 110,
            633 => 111, 634 => 113, 635 => 114,
        ];

        // Find the surah that starts at or before this page
        $surah = 1;
        foreach ($pageToSurah as $surahStartPage => $s) {
            if ($page >= $surahStartPage) {
                $surah = $s;
            } else {
                break;
            }
        }

        return $surah;
    }

    /**
     * Approximate verse start from page number
     *
     * Note: This is a rough approximation based on ~6 verses per page average.
     * The Quran API should be used when exact verse numbers are needed.
     *
     * @param int $page
     * @return int
     */
    private function getVerseStartFromPage(int $page): int
    {
        // Rough approximation: average ~6 verses per page
        // This is used when exact verse data from Quran API is not available
        return ($page - 1) * 6 + 1;
    }

    /**
     * Approximate verse end from page number
     *
     * Note: This is a rough approximation based on ~6 verses per page average.
     * The Quran API should be used when exact verse numbers are needed.
     *
     * @param int $page
     * @return int
     */
    private function getVerseEndFromPage(int $page): int
    {
        // Rough approximation: average ~6 verses per page
        return $page * 6;
    }
}
