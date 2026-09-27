<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\RecitationSession;
use App\Services\Concerns\ReservesDailyRun;
use Carbon\CarbonInterface;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Automated business rule: a student who is present on a day that requires a
 * recitation session must have a session recorded by their supervisor before
 * that day ends. Once the recitation day is over and no session exists, the
 * system registers a "لم يسمع" (not recited) session automatically.
 *
 * The set of required/excluded days is configurable via config/recitation.php
 * (and, in turn, by the "recitation_days" setting).
 */
class MissedRecitationService
{
    use ReservesDailyRun;

    /**
     * Process all eligible attendance records and return run statistics.
     *
     * @return array{created:int, skipped:int, cleaned:int}
     */
    public function run(?CarbonInterface $now = null): array
    {
        $now = $now ?? now();

        // The automatic scan only covers a short, recent window so a first
        // successful run after a long outage cannot backfill the whole
        // history at once. Older periods are created explicitly via
        // runRange() from the admin diagnostics page.
        $lookbackDays = (int) config('recitation.auto_lookback_days', 7);

        return $this->runWindow($now, $now->copy()->subDays($lookbackDays), $now);
    }

    /**
     * Process eligible attendance records within an explicit date range.
     * Used by the admin backfill (preview + confirm) for older periods.
     *
     * @return array{created:int, skipped:int, cleaned:int}
     */
    public function runRange(CarbonInterface $now, CarbonInterface $from, CarbonInterface $to): array
    {
        return $this->runWindow($now, $from, $to);
    }

    /**
     * @return array{created:int, skipped:int, cleaned:int}
     */
    private function runWindow(CarbonInterface $now, CarbonInterface $from, CarbonInterface $to): array
    {
        $attendedStatuses = (array) config('recitation.attendance_statuses', ['present', 'late']);

        // Auto sessions whose attendance is no longer "attended" (for example
        // the supervisor later changed the record to excused/absent) are
        // removed so the report stays truthful.
        $cleaned = $this->cleanupInvalid($attendedStatuses);

        // Also remove stale auto sessions once a real session exists for the
        // same student and day (e.g. a backdated real session was saved).
        $cleaned += $this->cleanupSuperseded();

        // Scan attendance by its date (not its creation time) so backdated
        // records are handled too. Whether a given day has actually ended is
        // checked per record below.
        $candidates = Attendance::query()
            ->whereIn('status', $attendedStatuses)
            ->where('date', '>=', $from->toDateString())
            ->where('date', '<=', $to->toDateString())
            ->get();

        $created = 0;
        $skipped = 0;

        foreach ($candidates as $attendance) {
            if (! $this->shouldCreateForAttendance($attendance, $now)) {
                $skipped++;
                continue;
            }

            if ($this->createForAttendance($attendance)) {
                $created++;
            } else {
                $skipped++;
            }
        }

        return ['created' => $created, 'skipped' => $skipped, 'cleaned' => $cleaned];
    }

    /**
     * Count how many sessions the given range would create without creating
     * any (used for the admin backfill preview).
     */
    public function previewRange(CarbonInterface $now, CarbonInterface $from, CarbonInterface $to): int
    {
        $attendedStatuses = (array) config('recitation.attendance_statuses', ['present', 'late']);

        return Attendance::query()
            ->whereIn('status', $attendedStatuses)
            ->where('date', '>=', $from->toDateString())
            ->where('date', '<=', $to->toDateString())
            ->get()
            ->filter(fn (Attendance $attendance) => $this->shouldCreateForAttendance($attendance, $now))
            ->count();
    }

    /**
     * Run the scan at most once per calendar day. This lets the site trigger
     * the rule on ordinary web visits, so it works even without a cron entry
     * for "schedule:run". Returns the run statistics, or a zeroed result with
     * "already_ran" = true when today's scan already happened.
     *
     * The "last run" marker is stored in the settings table (rather than the
     * cache) so it works regardless of the cache store configuration.
     *
     * @return array{created:int, skipped:int, cleaned:int, already_ran:bool}
     */
    public function runOncePerDay(?CarbonInterface $now = null): array
    {
        $now = $now ?? now();
        $key = 'recitation_last_scan_date';

        if (! $this->reserveDailyRun($key, $now)) {
            return $this->alreadyRanResult();
        }

        try {
            return $this->run($now) + ['already_ran' => false];
        } catch (\Throwable $e) {
            $this->releaseDailyRun($key, $now);
            throw $e;
        }
    }

    /**
     * @return array{created:int, skipped:int, cleaned:int, already_ran:bool}
     */
    private function alreadyRanResult(): array
    {
        return ['created' => 0, 'skipped' => 0, 'cleaned' => 0, 'already_ran' => true];
    }

    /**
     * Whether the attendance record satisfies all rule conditions.
     */
    public function shouldCreateForAttendance(Attendance $attendance, CarbonInterface $now): bool
    {
        if (! $this->isRequiredAttendanceDay($attendance)) {
            return false;
        }

        if (! $this->hasAttendanceDayEnded($attendance, $now)) {
            return false;
        }

        return ! $this->hasExistingSession($attendance);
    }

    /**
     * Whether the attendance date falls on a required day and is not excluded.
     */
    public function isRequiredAttendanceDay(Attendance $attendance): bool
    {
        if (! $attendance->date) {
            return false;
        }

        $day = (int) $attendance->date->dayOfWeek;

        $required = array_map('intval', (array) config('recitation.required_days', []));
        $excluded = array_map('intval', (array) config('recitation.excluded_days', []));

        if (in_array($day, $excluded, true)) {
            return false;
        }

        return in_array($day, $required, true);
    }

    /**
     * Whether the day the student was required to recite has already ended.
     * The rule fires at the start of the following calendar day.
     */
    public function hasAttendanceDayEnded(Attendance $attendance, CarbonInterface $now): bool
    {
        if (! $attendance->date) {
            return false;
        }

        return $now->greaterThanOrEqualTo($attendance->date->copy()->addDay()->startOfDay());
    }

    /**
     * Whether any recitation session already exists for the student on that day.
     */
    public function hasExistingSession(Attendance $attendance): bool
    {
        return RecitationSession::withoutGlobalScope('exclude_visitor_mafsil')
            ->where('student_id', $attendance->student_id)
            ->whereDate('session_date', $attendance->date)
            ->exists();
    }

    /**
     * Create the automatic "not recited" session for the attendance record.
     * Returns true only when a new session was persisted.
     */
    public function createForAttendance(Attendance $attendance): bool
    {
        // Fast idempotency guard.
        $alreadyLinked = RecitationSession::withoutGlobalScope('exclude_visitor_mafsil')
            ->where('attendance_id', $attendance->id)
            ->exists();

        if ($alreadyLinked) {
            return false;
        }

        try {
            DB::transaction(function () use ($attendance): void {
                // Re-check inside the transaction to avoid a race with a
                // supervisor saving a session just before the day ends.
                if ($this->hasExistingSession($attendance)) {
                    return;
                }

                RecitationSession::create([
                    'student_id'      => $attendance->student_id,
                    'supervisor_id'   => $attendance->supervisor_id,
                    'type'            => config('recitation.type', 'not_recitate'),
                    'status'          => config('recitation.status', 'not_recitate'),
                    'session_date'    => $attendance->date,
                    'attendance_id'   => $attendance->id,
                    'auto_generated'  => true,
                    'notes'           => 'تسميع تلقائي - انتهى يوم التسميع دون تسجيل تسميع',
                ]);
            });
        } catch (UniqueConstraintViolationException $e) {
            // Another concurrent run already created it.
            return false;
        } catch (\Throwable $e) {
            Log::error('Failed to auto-register a missed recitation session', [
                'attendance_id' => $attendance->id,
                'error'         => $e->getMessage(),
            ]);

            return false;
        }

        return true;
    }

    /**
     * Remove auto sessions whose attendance is no longer attended.
     */
    protected function cleanupInvalid(array $attendedStatuses): int
    {
        return RecitationSession::withoutGlobalScope('exclude_visitor_mafsil')
            ->where('auto_generated', true)
            ->whereHas('attendance', fn ($query) => $query->whereNotIn('status', $attendedStatuses))
            ->delete();
    }

    /**
     * Remove auto-generated "لم يسمع" sessions when a real (non not_recitate)
     * session exists for the same student and date. Manual sessions are never
     * touched.
     */
    protected function cleanupSuperseded(): int
    {
        $autoSessions = RecitationSession::withoutGlobalScope('exclude_visitor_mafsil')
            ->where('auto_generated', true)
            ->where('type', RecitationSession::TYPE_NOT_RECITE)
            ->get();

        if ($autoSessions->isEmpty()) {
            return 0;
        }

        $studentIds = $autoSessions->pluck('student_id')->unique()->all();

        $realSessions = RecitationSession::withoutGlobalScope('exclude_visitor_mafsil')
            ->whereIn('student_id', $studentIds)
            ->where('type', '!=', RecitationSession::TYPE_NOT_RECITE)
            ->get(['student_id', 'session_date'])
            ->map(fn ($s) => $s->student_id . '|' . (optional($s->session_date)->toDateString() ?? $s->session_date))
            ->flip();

        $idsToDelete = $autoSessions
            ->filter(function ($s) use ($realSessions) {
                $key = $s->student_id . '|' . (optional($s->session_date)->toDateString() ?? $s->session_date);

                return $realSessions->has($key);
            })
            ->pluck('id')
            ->all();

        if (empty($idsToDelete)) {
            return 0;
        }

        return RecitationSession::withoutGlobalScope('exclude_visitor_mafsil')
            ->whereIn('id', $idsToDelete)
            ->delete();
    }
}
