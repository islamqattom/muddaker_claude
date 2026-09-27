<?php

namespace App\Services;

use App\Models\QuranPage;
use App\Models\QuranPart;
use App\Models\RecitationSession;
use App\Models\Student;
use Illuminate\Support\Collection;

class RecitationStartSuggestionService
{
    private const TOTAL_PARTS = 30;

    public const DIRECTION_ASC = 'asc';
    public const DIRECTION_DESC = 'desc';

    private ?Collection $partsCache = null;
    private ?array $partNumberToIdCache = null;
    private ?array $pageToPartNumberCache = null;

    /**
     * Suggest where a student should start a session of the given type.
     *
     * @return array|null null when the type has no start field or no suggestion
     */
    public function suggest(Student $student, string $type): ?array
    {
        if (! in_array($type, RecitationSession::TYPES, true)) {
            return null;
        }

        return match ($type) {
            RecitationSession::TYPE_NEW_MEMORIZATION => $this->suggestNewMemorization($student),
            RecitationSession::TYPE_RECITATION => $this->suggestRecitation($student),
            RecitationSession::TYPE_REVIEW, RecitationSession::TYPE_UNMASTERED => $this->suggestReview($student, $type),
            default => null,
        };
    }

    /**
     * Resolve the student's memorization direction (part order).
     */
    public function direction(Student $student): string
    {
        $sessions = RecitationSession::where('student_id', $student->id)
            ->where('type', RecitationSession::TYPE_NEW_MEMORIZATION)
            ->with('part')
            ->orderBy('session_date')
            ->orderBy('id')
            ->get();

        $increases = 0;
        $decreases = 0;
        $previous = null;

        foreach ($sessions as $session) {
            $partNumber = $session->part?->part_number;
            if (! $partNumber) {
                continue;
            }

            $partNumber = (int) $partNumber;
            if ($previous !== null) {
                if ($partNumber > $previous) {
                    $increases++;
                } elseif ($partNumber < $previous) {
                    $decreases++;
                }
            }
            $previous = $partNumber;
        }

        if ($increases > $decreases) {
            return self::DIRECTION_ASC;
        }
        if ($decreases > $increases) {
            return self::DIRECTION_DESC;
        }

        return $this->fallbackDirection($student);
    }

    private function fallbackDirection(Student $student): string
    {
        $memorizedParts = (int) ($student->memorized_parts ?? 0);
        $currentPart = (int) ($student->current_part ?? 0);

        if ($currentPart <= 0) {
            return self::DIRECTION_ASC;
        }

        $distanceAsc = abs($currentPart - ($memorizedParts + 1));
        $distanceDesc = abs($currentPart - (self::TOTAL_PARTS - $memorizedParts));

        if ($distanceAsc < $distanceDesc) {
            return self::DIRECTION_ASC;
        }
        if ($distanceDesc < $distanceAsc) {
            return self::DIRECTION_DESC;
        }

        return self::DIRECTION_ASC;
    }

    private function suggestNewMemorization(Student $student): ?array
    {
        $direction = $this->direction($student);
        $preMemorized = $this->preMemorizedParts($student, $direction);

        if (count($preMemorized) >= self::TOTAL_PARTS) {
            return $this->completedSuggestion();
        }

        $memorized = $this->memorizedPageSet($student, $preMemorized);
        $completedParts = $this->completedPartNumbers($memorized);

        if (count($completedParts) >= self::TOTAL_PARTS) {
            return $this->completedSuggestion();
        }

        $sessions = $this->sessionsOfType($student, RecitationSession::TYPE_NEW_MEMORIZATION);

        if ($sessions->isEmpty()) {
            return $this->planStartSuggestion($student);
        }

        // Start from the end of the last session. Failed and not-completed
        // pages are handled separately as "repeated" pages.
        $latest = $sessions->last();
        $anchorPart = $latest->part?->part_number ? (int) $latest->part->part_number : null;
        $anchorEndPage = $this->sessionEndPage($latest);

        if ($anchorPart === null && $anchorEndPage !== null) {
            $anchorPart = $this->pageToPartNumber()[$anchorEndPage] ?? null;
        }

        $candidate = $this->nextUnmemorizedPageAfter($memorized, $anchorPart, $anchorEndPage, $direction);

        if ($candidate === null) {
            return $this->completedSuggestion();
        }

        $previousPage = $candidate - 1;
        $reason = $previousPage >= 1
            ? 'مقترح: استكمال بعد صفحة ' . $previousPage
            : 'مقترح: بداية الحفظ';

        return $this->pageSuggestion($candidate, $reason);
    }

    private function suggestReview(Student $student, string $type): ?array
    {
        $direction = $this->direction($student);
        $preMemorized = $this->preMemorizedParts($student, $direction);
        $memorized = $this->memorizedPageSet($student, $preMemorized);

        if (empty($memorized)) {
            return null;
        }

        $ordered = $this->orderedMemorizedPages($memorized, $direction);
        if (empty($ordered)) {
            return null;
        }

        $orderIndex = array_flip($ordered);
        $label = $this->typeLabel($type);
        $sessions = $this->sessionsOfType($student, $type);

        if ($sessions->isEmpty()) {
            return $this->pageSuggestion($ordered[0], 'مقترح: بداية دورة ' . $label . ' جديدة');
        }

        // Anchor on the end of the last session (failed and not-completed
        // pages are handled separately as "repeated" pages).
        $latest = $sessions->last();
        $anchorIndex = null;
        foreach ($latest->pages as $page) {
            $pageNumber = (int) $page->page_number;
            if (isset($orderIndex[$pageNumber])) {
                $anchorIndex = $anchorIndex === null ? $orderIndex[$pageNumber] : max($anchorIndex, $orderIndex[$pageNumber]);
            }
        }

        if ($anchorIndex === null) {
            // Fall back to the latest page of this type that is memorized.
            foreach ($sessions as $session) {
                foreach ($session->pages as $page) {
                    $pageNumber = (int) $page->page_number;
                    if ($page->is_passed && isset($orderIndex[$pageNumber])) {
                        $anchorIndex = $anchorIndex === null ? $orderIndex[$pageNumber] : max($anchorIndex, $orderIndex[$pageNumber]);
                    }
                }
            }
        }

        if ($anchorIndex === null) {
            return $this->pageSuggestion($ordered[0], 'مقترح: بداية دورة ' . $label . ' جديدة');
        }

        if ($anchorIndex + 1 < count($ordered)) {
            $anchorPage = $ordered[$anchorIndex];
            $nextPage = $ordered[$anchorIndex + 1];

            return $this->pageSuggestion($nextPage, 'مقترح: استكمال بعد صفحة ' . $anchorPage);
        }

        // Reached the end of the memorized pages: start a new cycle.
        return $this->pageSuggestion($ordered[0], 'مقترح: بداية دورة ' . $label . ' جديدة');
    }

    private function suggestRecitation(Student $student): ?array
    {
        $direction = $this->direction($student);
        $preMemorized = $this->preMemorizedParts($student, $direction);
        $memorized = $this->memorizedPageSet($student, $preMemorized);
        $memorizedParts = $this->completedPartNumbers($memorized);

        $orderedParts = array_values(array_filter(
            $this->partsInDirectionOrder($direction),
            fn ($partNumber) => in_array($partNumber, $memorizedParts, true)
        ));

        if (empty($orderedParts)) {
            return null;
        }

        $recitationSessions = $this->sessionsOfType($student, RecitationSession::TYPE_RECITATION);

        $recitedParts = [];
        foreach ($recitationSessions as $session) {
            if ($session->status !== RecitationSession::STATUS_PASSED) {
                continue;
            }
            $partNumber = $session->part?->part_number;
            if ($partNumber) {
                $recitedParts[(int) $partNumber] = true;
            }
        }

        $lastRecitedIndex = null;
        foreach ($orderedParts as $index => $partNumber) {
            if (isset($recitedParts[$partNumber])) {
                $lastRecitedIndex = $index;
            }
        }

        if ($lastRecitedIndex === null) {
            return $this->partSuggestion($orderedParts[0], 'مقترح: بداية دورة سرد جديدة');
        }

        if ($lastRecitedIndex + 1 < count($orderedParts)) {
            $lastRecitedPart = $orderedParts[$lastRecitedIndex];
            $nextPart = $orderedParts[$lastRecitedIndex + 1];

            return $this->partSuggestion($nextPart, 'مقترح: استكمال بعد الجزء ' . $lastRecitedPart);
        }

        return $this->partSuggestion($orderedParts[0], 'مقترح: بداية دورة سرد جديدة');
    }

    /**
     * @return array<int, int> part numbers memorized before joining the system
     */
    private function preMemorizedParts(Student $student, string $direction): array
    {
        $count = (int) ($student->memorized_parts ?? 0);
        $count = max(0, min(self::TOTAL_PARTS, $count));

        if ($count === 0) {
            return [];
        }

        if ($direction === self::DIRECTION_DESC) {
            return range(self::TOTAL_PARTS, self::TOTAL_PARTS - $count + 1, -1);
        }

        return range(1, $count);
    }

    /**
     * @return array<int, bool> page_number => true
     */
    private function memorizedPageSet(Student $student, array $preMemorizedParts): array
    {
        $pages = [];

        $sessions = RecitationSession::where('student_id', $student->id)
            ->where('type', RecitationSession::TYPE_NEW_MEMORIZATION)
            ->with('pages')
            ->get();

        foreach ($sessions as $session) {
            foreach ($session->pages as $page) {
                if ($page->is_passed) {
                    $pages[(int) $page->page_number] = true;
                }
            }
        }

        foreach ($preMemorizedParts as $partNumber) {
            $range = $this->partRange($partNumber);
            if ($range === null) {
                continue;
            }
            for ($page = $range['start']; $page <= $range['end']; $page++) {
                $pages[$page] = true;
            }
        }

        return $pages;
    }

    /**
     * @param array<int, bool> $memorized
     * @return array<int, int> part numbers fully memorized
     */
    private function completedPartNumbers(array $memorized): array
    {
        $complete = [];

        foreach ($this->partsCollection() as $part) {
            $partNumber = (int) $part->part_number;
            $start = (int) $part->start_page;
            $end = (int) $part->end_page;

            for ($page = $start; $page <= $end; $page++) {
                if (! isset($memorized[$page])) {
                    continue 2;
                }
            }

            $complete[] = $partNumber;
        }

        return $complete;
    }

    /**
     * @param array<int, bool> $memorized
     * @return array<int, int> memorized pages ordered by direction then ascending
     */
    private function orderedMemorizedPages(array $memorized, string $direction): array
    {
        $ordered = [];

        foreach ($this->partsCollection()->sortBy(
            fn ($part) => $direction === self::DIRECTION_DESC
                ? -1 * (int) $part->part_number
                : (int) $part->part_number
        ) as $part) {
            for ($page = (int) $part->start_page; $page <= (int) $part->end_page; $page++) {
                if (isset($memorized[$page])) {
                    $ordered[] = $page;
                }
            }
        }

        return $ordered;
    }

    private function sessionEndPage(RecitationSession $session): ?int
    {
        $maxPage = $session->pages->max('page_number');

        if ($maxPage !== null) {
            return (int) $maxPage;
        }

        return $session->end_page !== null ? (int) $session->end_page : null;
    }

    /**
     * First page after the anchor that is not memorized yet, walking parts in
     * the student's direction. Pages inside a part always ascend.
     *
     * @param array<int, bool> $memorized
     */
    private function nextUnmemorizedPageAfter(array $memorized, ?int $anchorPart, ?int $anchorEndPage, string $direction): ?int
    {
        $orderedParts = $this->partsInDirectionOrder($direction);
        if (empty($orderedParts)) {
            return null;
        }

        $startIndex = 0;
        if ($anchorPart !== null) {
            $position = array_search($anchorPart, $orderedParts, true);
            $startIndex = $position === false ? 0 : $position;
        }

        $total = count($orderedParts);
        for ($index = $startIndex; $index < $total; $index++) {
            $range = $this->partRange($orderedParts[$index]);
            if ($range === null) {
                continue;
            }

            $from = $range['start'];
            if ($index === $startIndex && $anchorPart !== null && $anchorEndPage !== null) {
                $from = max($range['start'], $anchorEndPage + 1);
            }

            for ($page = $from; $page <= $range['end']; $page++) {
                if (! isset($memorized[$page])) {
                    return $page;
                }
            }
        }

        return null;
    }

    /**
     * @return array<int, int>
     */
    private function partsInDirectionOrder(string $direction): array
    {
        $partNumbers = $this->partsCollection()->pluck('part_number')->map(fn ($n) => (int) $n)->all();
        $partNumbers = array_values(array_unique($partNumbers));

        if ($direction === self::DIRECTION_DESC) {
            rsort($partNumbers);
        } else {
            sort($partNumbers);
        }

        return $partNumbers;
    }

    /**
     * @return array{start: int, end: int}|null
     */
    private function partRange(int $partNumber): ?array
    {
        $part = $this->partsCollection()->firstWhere('part_number', $partNumber);
        if (! $part) {
            return null;
        }

        return ['start' => (int) $part->start_page, 'end' => (int) $part->end_page];
    }

    private function planStartSuggestion(Student $student): ?array
    {
        $partNumber = (int) ($student->current_part ?? 0);
        if ($partNumber <= 0) {
            $partNumber = 1;
        }

        $range = $this->partRange($partNumber);
        if ($range === null) {
            return null;
        }

        $page = (int) ($student->current_page ?? 0);
        if ($page < $range['start'] || $page > $range['end']) {
            $page = $range['start'];
        }

        return $this->pageSuggestion($page, 'مقترح: بداية خطة الطالب');
    }

    private function pageSuggestion(int $page, string $reason): array
    {
        $partNumber = $this->pageToPartNumber()[$page] ?? null;

        return [
            'has_fields' => true,
            'part_id' => $partNumber !== null ? ($this->partNumberToId()[$partNumber] ?? null) : null,
            'part_number' => $partNumber,
            'page_number' => $page,
            'reason' => $reason,
            'completed' => false,
        ];
    }

    private function partSuggestion(int $partNumber, string $reason): array
    {
        return [
            'has_fields' => true,
            'part_id' => $this->partNumberToId()[$partNumber] ?? null,
            'part_number' => $partNumber,
            'page_number' => null,
            'reason' => $reason,
            'completed' => false,
        ];
    }

    private function completedSuggestion(): array
    {
        return [
            'has_fields' => true,
            'part_id' => null,
            'part_number' => null,
            'page_number' => null,
            'reason' => 'أتمّ الطالب حفظ القرآن كاملاً',
            'completed' => true,
        ];
    }

    private function sessionsOfType(Student $student, string $type): Collection
    {
        return RecitationSession::where('student_id', $student->id)
            ->where('type', $type)
            ->with(['pages', 'part'])
            ->orderBy('session_date')
            ->orderBy('id')
            ->get();
    }

    private function typeLabel(string $type): string
    {
        return match ($type) {
            RecitationSession::TYPE_NEW_MEMORIZATION => 'حفظ جديد',
            RecitationSession::TYPE_REVIEW => 'مراجعة',
            RecitationSession::TYPE_UNMASTERED => 'غير متقن',
            RecitationSession::TYPE_RECITATION => 'سرد',
            default => $type,
        };
    }

    private function partsCollection(): Collection
    {
        return $this->partsCache ??= QuranPart::orderBy('part_number')->get();
    }

    /**
     * @return array<int, int> part_number => part_id
     */
    private function partNumberToId(): array
    {
        return $this->partNumberToIdCache ??= $this->partsCollection()->pluck('id', 'part_number')->all();
    }

    /**
     * @return array<int, int> page_number => part_number
     */
    private function pageToPartNumber(): array
    {
        if ($this->pageToPartNumberCache !== null) {
            return $this->pageToPartNumberCache;
        }

        $idToPartNumber = $this->partsCollection()->pluck('part_number', 'id')->all();
        $map = [];

        try {
            foreach (QuranPage::pluck('part_id', 'page_number') as $pageNumber => $partId) {
                if (isset($idToPartNumber[$partId])) {
                    $map[(int) $pageNumber] = (int) $idToPartNumber[$partId];
                }
            }
        } catch (\Throwable $e) {
            $map = [];
        }

        return $this->pageToPartNumberCache = $map;
    }
}
