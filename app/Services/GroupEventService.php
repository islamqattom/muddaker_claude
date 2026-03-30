<?php

namespace App\Services;

use App\Models\GroupEvent;
use App\Models\GroupEventParticipant;
use App\Models\GroupEventProgress;
use App\Models\Student;
use App\Models\RecitationSession;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Group Event Service
 *
 * Manages group memorization events where classes collectively
 * work on memorizing a juz.
 */
class GroupEventService
{
    private QuranApiService $quranApi;

    public function __construct(QuranApiService $quranApi)
    {
        $this->quranApi = $quranApi;
    }

    /**
     * Event statuses
     */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Create a new group event
     *
     * @param int $supervisorId
     * @param string $name
     * @param int $juzNumber
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return GroupEvent
     */
    public function createEvent(
        int $supervisorId,
        string $name,
        int $juzNumber,
        Carbon $startDate,
        Carbon $endDate
    ): GroupEvent {
        // Validate juz number (1-30 for Quran)
        if ($juzNumber < 1 || $juzNumber > 30) {
            throw new \InvalidArgumentException('Juz number must be between 1 and 30');
        }

        // Validate dates
        if ($endDate->lt($startDate)) {
            throw new \InvalidArgumentException('End date must be on or after start date');
        }

        return GroupEvent::create([
            'supervisor_id' => $supervisorId,
            'name' => $name,
            'juz_number' => $juzNumber,
            'target_pages' => $this->getJuzPageCount($juzNumber),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => self::STATUS_DRAFT,
        ]);
    }

    /**
     * Activate an event (open for participation)
     *
     * @param GroupEvent $event
     * @return GroupEvent
     */
    public function activate(GroupEvent $event): GroupEvent
    {
        if ($event->status !== self::STATUS_DRAFT) {
            throw new \LogicException('Can only activate a draft event');
        }

        $event->status = self::STATUS_ACTIVE;
        $event->save();
        return $event;
    }

    /**
     * Cancel an event
     *
     * @param GroupEvent $event
     * @return GroupEvent
     */
    public function cancel(GroupEvent $event): GroupEvent
    {
        if ($event->status === self::STATUS_COMPLETED) {
            throw new \LogicException('Cannot cancel a completed event');
        }

        $event->status = self::STATUS_CANCELLED;
        $event->save();
        return $event;
    }

    /**
     * Complete an event
     *
     * @param GroupEvent $event
     * @return GroupEvent
     */
    public function complete(GroupEvent $event): GroupEvent
    {
        if ($event->status !== self::STATUS_ACTIVE) {
            throw new \LogicException('Can only complete an active event');
        }

        $event->status = self::STATUS_COMPLETED;
        $event->save();

        // Generate final progress snapshot
        $this->recordProgress($event);

        return $event;
    }

    /**
     * Join a student to an event
     *
     * @param GroupEvent $event
     * @param Student $student
     * @return GroupEventParticipant
     */
    public function joinEvent(GroupEvent $event, Student $student): GroupEventParticipant
    {
        if ($event->status !== self::STATUS_ACTIVE) {
            throw new \LogicException('Can only join an active event');
        }

        return GroupEventParticipant::firstOrCreate([
            'event_id' => $event->id,
            'student_id' => $student->id,
        ], [
            'pages_memorized' => 0,
            'last_page_reached' => $this->getJuzStartPage($event->juz_number),
            'joined_at' => Carbon::now(),
        ]);
    }

    /**
     * Leave an event
     *
     * @param GroupEvent $event
     * @param Student $student
     * @return bool
     */
    public function leaveEvent(GroupEvent $event, Student $student): bool
    {
        if ($event->status === self::STATUS_COMPLETED) {
            throw new \LogicException('Cannot leave a completed event');
        }

        return (bool) GroupEventParticipant::where('event_id', $event->id)
            ->where('student_id', $student->id)
            ->delete();
    }

    /**
     * Update participant progress from a recitation session
     *
     * @param GroupEvent $event
     * @param Student $student
     * @param RecitationSession $session
     * @return bool True if progress was updated, false if skipped
     */
    public function updateProgress(GroupEvent $event, Student $student, RecitationSession $session): bool
    {
        // Only update progress for active events
        if ($event->status !== self::STATUS_ACTIVE) {
            Log::debug('GroupEventService: Skipping progress update - event not active', [
                'event_id' => $event->id,
                'status' => $event->status,
            ]);
            return false;
        }

        return DB::transaction(function () use ($event, $student, $session) {
            $participant = GroupEventParticipant::where('event_id', $event->id)
                ->where('student_id', $student->id)
                ->lockForUpdate()
                ->first();

            if (!$participant) {
                Log::info('GroupEventService: Skipping progress update - student not a participant', [
                    'event_id' => $event->id,
                    'student_id' => $student->id,
                ]);
                return false;
            }

            // Only count if session is in the event date range
            $sessionDate = Carbon::parse($session->session_date);
            if ($sessionDate->lt($event->start_date) || $sessionDate->gt($event->end_date)) {
                Log::debug('GroupEventService: Skipping progress update - session outside event dates', [
                    'event_id' => $event->id,
                    'session_date' => $sessionDate->toDateString(),
                    'event_start' => $event->start_date->toDateString(),
                    'event_end' => $event->end_date->toDateString(),
                ]);
                return false;
            }

            // Calculate pages memorized in this session
            if ($session->status === 'passed' && $session->start_page && $session->end_page) {
                $pagesMemorized = $session->end_page - $session->start_page + 1;

                // Cap pages_memorized at target_pages to prevent overflow
                $newTotal = $participant->pages_memorized + $pagesMemorized;
                $participant->pages_memorized = min($newTotal, $event->target_pages);
                $participant->last_page_reached = max($participant->last_page_reached, $session->end_page);
                $participant->save();

                // Record daily progress snapshot
                $this->recordProgress($event);

                Log::info('GroupEventService: Progress updated', [
                    'event_id' => $event->id,
                    'student_id' => $student->id,
                    'pages_added' => $pagesMemorized,
                    'total_pages' => $participant->pages_memorized,
                ]);

                return true;
            }

            return false;
        });
    }

    /**
     * Record a progress snapshot for the event
     *
     * @param GroupEvent $event
     * @return GroupEventProgress
     */
    public function recordProgress(GroupEvent $event): GroupEventProgress
    {
        $today = Carbon::today();

        $participants = $event->participants()->with('student')->get();
        $totalPages = $participants->sum('pages_memorized');
        $participatingCount = $participants->count();

        // Calculate completion percentage
        $maxPages = $event->target_pages * $participatingCount;
        $completionPercentage = $maxPages > 0
            ? min(100, round(($totalPages / $maxPages) * 100, 1))
            : 0;

        return GroupEventProgress::updateOrCreate(
            [
                'event_id' => $event->id,
                'date' => $today,
            ],
            [
                'total_pages' => $totalPages,
                'participating_students' => $participatingCount,
                'completion_percentage' => $completionPercentage,
            ]
        );
    }

    /**
     * Get event progress data for display
     *
     * @param GroupEvent $event
     * @return array
     */
    public function getEventProgress(GroupEvent $event): array
    {
        $participants = $event->participants()->with('student.user')->get();
        $latestProgress = $event->progress()->where('date', Carbon::today())->first();

        // Calculate collective progress
        $totalTarget = $event->target_pages * $participants->count();
        $totalCompleted = $participants->sum('pages_memorized');
        $completionPercentage = $totalTarget > 0 ? round(($totalCompleted / $totalTarget) * 100, 1) : 0;

        // Per-student breakdown
        $studentProgress = $participants->map(function ($p) use ($event) {
            $individualCompletion = $event->target_pages > 0
                ? round(($p->pages_memorized / $event->target_pages) * 100, 1)
                : 0;
            return [
                'student_id' => $p->student_id,
                'student_name' => $p->student->name,
                'pages_memorized' => $p->pages_memorized,
                'last_page_reached' => $p->last_page_reached,
                'completion_percentage' => $individualCompletion,
                'joined_at' => $p->joined_at,
            ];
        })->sortByDesc('completion_percentage');

        return [
            'event_id' => $event->id,
            'event_name' => $event->name,
            'juz_number' => $event->juz_number,
            'target_pages' => $event->target_pages,
            'total_target' => $totalTarget,
            'total_completed' => $totalCompleted,
            'completion_percentage' => $completionPercentage,
            'participating_count' => $participants->count(),
            'daily_progress' => $latestProgress,
            'student_progress' => $studentProgress->values(),
        ];
    }

    /**
     * Get daily progress history for charting
     *
     * @param GroupEvent $event
     * @return Collection
     */
    public function getProgressHistory(GroupEvent $event): Collection
    {
        return $event->progress()
            ->orderBy('date')
            ->get()
            ->map(function ($p) {
                return [
                    'date' => Carbon::parse($p->date)->format('Y-m-d'),
                    'total_pages' => $p->total_pages,
                    'participating_students' => $p->participating_students,
                    'completion_percentage' => $p->completion_percentage,
                ];
            });
    }

    /**
     * Get active events a student can join
     *
     * @param Student $student
     * @return Collection
     */
    public function getAvailableEvents(Student $student): Collection
    {
        $joinedEventIds = GroupEventParticipant::where('student_id', $student->id)
            ->pluck('event_id');

        return GroupEvent::where('status', self::STATUS_ACTIVE)
            ->where('end_date', '>=', Carbon::today())
            ->whereNotIn('id', $joinedEventIds)
            ->with('supervisor')
            ->orderBy('start_date')
            ->get();
    }

    /**
     * Get events a student is participating in
     *
     * @param Student $student
     * @return Collection
     */
    public function getMyEvents(Student $student): Collection
    {
        return GroupEvent::whereHas('participants', function ($query) use ($student) {
            $query->where('student_id', $student->id);
        })
            ->with('supervisor', 'participants')
            ->orderBy('start_date', 'desc')
            ->get();
    }

    /**
     * Check if student is participating in event
     *
     * @param GroupEvent $event
     * @param Student $student
     * @return bool
     */
    public function isParticipating(GroupEvent $event, Student $student): bool
    {
        return $event->participants()->where('student_id', $student->id)->exists();
    }

    /**
     * Get status label in Arabic
     *
     * @param string $status
     * @return string
     */
    public static function getStatusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_DRAFT => 'مسودة',
            self::STATUS_ACTIVE => 'نشط',
            self::STATUS_COMPLETED => 'مكتمل',
            self::STATUS_CANCELLED => 'ملغى',
            default => $status,
        };
    }

    /**
     * Get start page for a juz
     *
     * @param int $juzNumber
     * @return int
     */
    private function getJuzStartPage(int $juzNumber): int
    {
        return $this->quranApi->getJuzStartPage($juzNumber);
    }

    /**
     * Get page count for a juz
     *
     * @param int $juzNumber
     * @return int
     */
    private function getJuzPageCount(int $juzNumber): int
    {
        return $this->quranApi->getJuzPageCount($juzNumber);
    }
}
