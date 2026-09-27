<?php

namespace App\Services;

use App\Models\QuranPage;
use App\Models\QuranPart;
use App\Models\RecitationSession;
use App\Models\Student;
use App\Models\SupervisorAlert;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RecitationAlertService
{
    /**
     * Load the new-memorization and recitation sessions (with their pages)
     * for a student. These are the only types that prove memorization or a
     * full سرد.
     */
    private function historyFor(Student $student): Collection
    {
        return RecitationSession::where('student_id', $student->id)
            ->whereIn('type', [RecitationSession::TYPE_NEW_MEMORIZATION, RecitationSession::TYPE_RECITATION])
            ->with('pages')
            ->get();
    }

    /**
     * Map every Quran page number to its part id (looked up once per call).
     */
    private function partIdByPageNumber(): array
    {
        try {
            return QuranPage::query()->pluck('part_id', 'page_number')->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Resolve the part a page record belongs to, falling back to the
     * session's part_id when the page is not in the page map.
     */
    private function resolvePartId(?int $partId, int $pageNumber, array $pageMap): ?int
    {
        return $pageMap[$pageNumber] ?? $partId;
    }

    /**
     * Part ids where every page of the part has a passed page record in a
     * new-memorization session.
     *
     * @return array<int, int> part_id => part_number
     */
    public function memorizedParts(Student $student): array
    {
        $pageMap = $this->partIdByPageNumber();
        $masteredPages = []; // part_id => [page_number => true]

        foreach ($this->historyFor($student) as $session) {
            if ($session->type !== RecitationSession::TYPE_NEW_MEMORIZATION) {
                continue;
            }

            foreach ($session->pages as $page) {
                if (! $page->is_passed) {
                    continue;
                }

                $partId = $this->resolvePartId($session->part_id, (int) $page->page_number, $pageMap);
                if ($partId) {
                    $masteredPages[(int) $partId][(int) $page->page_number] = true;
                }
            }
        }

        return $this->completePartNumbers($masteredPages);
    }

    /**
     * Part ids memorized through new-memorization and then fully recited by a
     * passed سرد session on or after the day the last page was memorized.
     *
     * @return array<int, int> part_id => part_number
     */
    public function fullyRecitedParts(Student $student): array
    {
        $pageMap = $this->partIdByPageNumber();
        // part_id => [page_number => earliest date the page was passed in new memorization]
        $firstMasteredAt = [];
        // part_id => [dates of passed سرد sessions covering the part]
        $passedSardDates = [];

        foreach ($this->historyFor($student) as $session) {
            $type = $session->type;
            $date = $this->sessionDate($session);
            $coveredParts = [];

            if ($session->part_id) {
                $coveredParts[(int) $session->part_id] = true;
            }

            foreach ($session->pages as $page) {
                $partId = $this->resolvePartId($session->part_id, (int) $page->page_number, $pageMap);
                if (! $partId) {
                    continue;
                }

                $partId = (int) $partId;
                $coveredParts[$partId] = true;

                if ($type !== RecitationSession::TYPE_NEW_MEMORIZATION || ! $page->is_passed) {
                    continue;
                }

                $pageNumber = (int) $page->page_number;
                $prev = $firstMasteredAt[$partId][$pageNumber] ?? null;
                if ($prev === null || ($date && $date->lt($prev))) {
                    $firstMasteredAt[$partId][$pageNumber] = $date ?? $prev;
                }
            }

            if ($type === RecitationSession::TYPE_RECITATION && $session->status === RecitationSession::STATUS_PASSED) {
                foreach (array_keys($coveredParts) as $partId) {
                    $passedSardDates[$partId][] = $date;
                }
            }
        }

        if (empty($passedSardDates)) {
            return [];
        }

        $qualified = [];
        foreach (QuranPart::whereIn('id', array_keys($passedSardDates))->get() as $part) {
            $pages = $firstMasteredAt[$part->id] ?? [];
            $complete = true;
            $completedAt = null;

            for ($page = (int) $part->start_page; $page <= (int) $part->end_page; $page++) {
                if (! isset($pages[$page])) {
                    $complete = false;
                    break;
                }
                $date = $pages[$page];
                if ($date && ($completedAt === null || $date->gt($completedAt))) {
                    $completedAt = $date;
                }
            }

            if (! $complete) {
                continue;
            }

            foreach ($passedSardDates[$part->id] as $sardDate) {
                if ($completedAt === null || $sardDate === null || $sardDate->gte($completedAt)) {
                    $qualified[$part->id] = (int) $part->part_number;
                    break;
                }
            }
        }

        return $qualified;
    }

    /**
     * @param array<int, array<int, bool>> $masteredPages part_id => [page_number => true]
     * @return array<int, int> part_id => part_number
     */
    private function completePartNumbers(array $masteredPages): array
    {
        if (empty($masteredPages)) {
            return [];
        }

        $result = [];
        foreach (QuranPart::whereIn('id', array_keys($masteredPages))->get() as $part) {
            $pages = $masteredPages[$part->id] ?? [];

            for ($page = (int) $part->start_page; $page <= (int) $part->end_page; $page++) {
                if (! isset($pages[$page])) {
                    continue 2;
                }
            }

            $result[$part->id] = (int) $part->part_number;
        }

        return $result;
    }

    private function sessionDate($session): ?Carbon
    {
        $date = $session->session_date ?? null;

        if (! $date) {
            return null;
        }

        if ($date instanceof Carbon) {
            return $date;
        }

        try {
            return Carbon::parse($date);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Record a "part memorized" alert for every part covered by the saved
     * new-memorization session that is now fully memorized. Each part alerts
     * at most once.
     *
     * @return Collection<int, SupervisorAlert>
     */
    public function alertCompletedMemorization(RecitationSession $session): Collection
    {
        if ($session->type !== RecitationSession::TYPE_NEW_MEMORIZATION) {
            return collect();
        }

        $student = $session->student;
        if (! $student) {
            return collect();
        }

        $pageMap = $this->partIdByPageNumber();
        $coveredPartIds = [];
        foreach ($session->pages as $page) {
            $partId = $this->resolvePartId($session->part_id, (int) $page->page_number, $pageMap);
            if ($partId) {
                $coveredPartIds[(int) $partId] = true;
            }
        }

        if (empty($coveredPartIds)) {
            return collect();
        }

        $memorized = $this->memorizedParts($student);
        $created = collect();

        foreach (array_keys($coveredPartIds) as $partId) {
            if (! isset($memorized[$partId])) {
                continue;
            }

            $partNumber = $memorized[$partId];
            $alert = $this->firstOrCreateAlert(
                $student,
                SupervisorAlert::TYPE_PART_MEMORIZED,
                SupervisorAlert::partReference($partNumber),
                $partNumber,
                null,
                $session
            );

            if ($alert->wasRecentlyCreated) {
                $created->push($alert);
            }
        }

        return $created;
    }

    /**
     * Record a "mafsil required" alert for every station reached after a
     * passed سرد session. Stations already alerted are skipped, so a single
     * popup can report several stations reached at once.
     *
     * @return Collection<int, SupervisorAlert>
     */
    public function alertMafsilStations(RecitationSession $session): Collection
    {
        if ($session->type !== RecitationSession::TYPE_RECITATION || $session->status !== RecitationSession::STATUS_PASSED) {
            return collect();
        }

        $student = $session->student;
        if (! $student) {
            return collect();
        }

        $count = count($this->fullyRecitedParts($student));
        if ($count === 0) {
            return collect();
        }

        $created = collect();
        foreach (SupervisorAlert::MAFSIL_STATIONS as $station) {
            if ($count < $station) {
                continue;
            }

            $alert = $this->firstOrCreateAlert(
                $student,
                SupervisorAlert::TYPE_MAFSIL_REQUIRED,
                SupervisorAlert::stationReference($station),
                null,
                $station,
                $session
            );

            if ($alert->wasRecentlyCreated) {
                $created->push($alert);
            }
        }

        return $created;
    }

    private function firstOrCreateAlert(
        Student $student,
        string $type,
        string $reference,
        ?int $partNumber,
        ?int $station,
        RecitationSession $session
    ): SupervisorAlert {
        return SupervisorAlert::firstOrCreate(
            [
                'student_id' => $student->id,
                'type' => $type,
                'reference' => $reference,
            ],
            [
                'supervisor_id' => $session->supervisor_id ?? $student->supervisor_id,
                'part_number' => $partNumber,
                'station' => $station,
                'recitation_session_id' => $session->id,
            ]
        );
    }

    /**
     * Number of parts a student memorized (new memorization) and then fully
     * recited (سرد).
     */
    public function recitedPartsCount(Student $student): int
    {
        return count($this->fullyRecitedParts($student));
    }

    /**
     * Unread "part memorized" alerts for a single student, limited to the
     * supervisor responsible for that student.
     */
    public function unreadMemorizationAlertsForStudent(int $studentId, int $supervisorId): Collection
    {
        return SupervisorAlert::unread()
            ->where('type', SupervisorAlert::TYPE_PART_MEMORIZED)
            ->where('student_id', $studentId)
            ->whereHas('student', fn ($q) => $q->where('supervisor_id', $supervisorId))
            ->with('student.user')
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Mark the given alerts as read, but only when they belong to the
     * supervisor's own students.
     */
    public function acknowledge(array $ids, int $supervisorId): int
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (empty($ids)) {
            return 0;
        }

        return SupervisorAlert::whereIn('id', $ids)
            ->whereHas('student', fn ($q) => $q->where('supervisor_id', $supervisorId))
            ->unread()
            ->update(['read_at' => now()]);
    }

    /**
     * Failed pages (recitation_pages.is_passed = false) in the most recent
     * session of a given type for the student. Returns null when there is no
     * such session or when the last one has no failed pages.
     *
     * @return array|null
     */
    public function failedPagesForLastSession(Student $student, string $type): ?array
    {
        $session = RecitationSession::where('student_id', $student->id)
            ->where('type', $type)
            ->orderByDesc('session_date')
            ->orderByDesc('id')
            ->with('pages')
            ->first();

        if (! $session) {
            return null;
        }

        $failedPages = $session->pages
            ->filter(fn ($page) => ! $page->is_passed)
            ->sortBy('page_number')
            ->values();

        if ($failedPages->isEmpty()) {
            return null;
        }

        // Resolve a surah name and owning part per failed page, using the
        // mushaf page when the record provides one (e.g. مفصل stores the juz
        // in page_number).
        $lookupPages = $failedPages->mapWithKeys(fn ($page) => [
            (int) $page->page_number => (int) ($page->page_number_in_mushaf ?: $page->page_number),
        ]);

        $pageRecords = collect();
        $partNumbers = collect();
        try {
            $pageRecords = QuranPage::whereIn('page_number', $lookupPages->values()->unique()->all())
                ->get(['page_number', 'surah_name', 'part_id'])
                ->keyBy('page_number');
            $partNumbers = QuranPart::pluck('part_number', 'id');
        } catch (\Throwable $e) {
            $pageRecords = collect();
            $partNumbers = collect();
        }

        $pages = $failedPages->map(function ($page) use ($lookupPages, $pageRecords, $partNumbers) {
            $lookup = $lookupPages[(int) $page->page_number] ?? null;
            $record = $lookup !== null ? $pageRecords->get($lookup) : null;
            $partId = $record->part_id ?? null;

            return [
                'page_number' => (int) $page->page_number,
                'surah_name' => $record->surah_name ?? null,
                'part_id' => $partId,
                'part_number' => $partId !== null ? ($partNumbers[$partId] ?? null) : null,
                'is_completed' => (bool) $page->is_completed,
                'status_label' => $page->is_completed ? 'راسبة' : 'لم يكمل',
            ];
        })->values();

        return [
            'session_id' => $session->id,
            'session_date' => $session->session_date?->toDateString(),
            'type' => $session->type,
            'type_label' => $session->type_label,
            'pages' => $pages,
        ];
    }
}
