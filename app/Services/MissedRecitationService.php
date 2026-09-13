<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\RecitationSession;
use Carbon\CarbonInterface;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Automated business rule: a student who is present on a day that requires a
 * recitation session must have a session recorded by their supervisor within
 * the configured grace period (24 hours by default). Otherwise the system
 * registers a "لم يسمع" (not recited) session automatically.
 *
 * Friday is always excluded, and the set of required/excluded days and the
 * grace period are configurable via config/recitation.php.
 */
class MissedRecitationService
{
    /**
     * Process all eligible attendance records and return run statistics.
     *
     * @return array{created:int, skipped:int, cleaned:int}
     */
    public function run(?CarbonInterface $now = null): array
    {
        $now = $now ?? now();

        $graceHours       = (int) config('recitation.grace_hours', 24);
        $lookbackDays     = (int) config('recitation.lookback_days', 30);
        $attendedStatuses = (array) config('recitation.attendance_statuses', ['present', 'late']);

        // Auto sessions whose attendance is no longer "attended" (for example
        // the supervisor later changed the record to excused/absent) are
        // removed so the report stays truthful.
        $cleaned = $this->cleanupInvalid($attendedStatuses);

        $deadline = $now->copy()->subHours($graceHours);

        $candidates = Attendance::query()
            ->whereIn('status', $attendedStatuses)
            ->where('created_at', '<=', $deadline)
            ->where('created_at', '>=', $now->copy()->subDays($lookbackDays))
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
     * Whether the attendance record satisfies all rule conditions.
     */
    public function shouldCreateForAttendance(Attendance $attendance, CarbonInterface $now): bool
    {
        if (! $this->isRequiredAttendanceDay($attendance)) {
            return false;
        }

        if (! $this->hasGraceElapsed($attendance, $now)) {
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
     * Whether the grace period has elapsed since the attendance was registered.
     */
    public function hasGraceElapsed(Attendance $attendance, CarbonInterface $now): bool
    {
        if (! $attendance->created_at) {
            return false;
        }

        $graceHours = (int) config('recitation.grace_hours', 24);

        return $now->greaterThanOrEqualTo($attendance->created_at->copy()->addHours($graceHours));
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
                // supervisor saving a session just before the deadline.
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
                    'notes'           => 'تسميع تلقائي - لم يتم تسجيل تسميع خلال '
                        . (int) config('recitation.grace_hours', 24) . ' ساعة',
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
     * Remove auto-generated sessions whose attendance is no longer attended.
     */
    protected function cleanupInvalid(array $attendedStatuses): int
    {
        return RecitationSession::withoutGlobalScope('exclude_visitor_mafsil')
            ->where('auto_generated', true)
            ->whereHas('attendance', fn ($query) => $query->whereNotIn('status', $attendedStatuses))
            ->delete();
    }
}
