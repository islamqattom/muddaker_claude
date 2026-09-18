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
 * recitation session must have a session recorded by their supervisor before
 * that day ends. Once the recitation day is over and no session exists, the
 * system registers a "لم يسمع" (not recited) session automatically.
 *
 * The set of required/excluded days is configurable via config/recitation.php
 * (and, in turn, by the "recitation_days" setting).
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

        $lookbackDays     = (int) config('recitation.lookback_days', 30);
        $attendedStatuses = (array) config('recitation.attendance_statuses', ['present', 'late']);

        // Auto sessions whose attendance is no longer "attended" (for example
        // the supervisor later changed the record to excused/absent) are
        // removed so the report stays truthful.
        $cleaned = $this->cleanupInvalid($attendedStatuses);

        // Scan attendance by its date (not its creation time) so backdated
        // records are handled too. Records from the last lookback window are
        // considered; whether a given day has actually ended is checked per
        // record below.
        $candidates = Attendance::query()
            ->whereIn('status', $attendedStatuses)
            ->where('date', '>=', $now->copy()->subDays($lookbackDays)->toDateString())
            ->where('date', '<=', $now->copy()->toDateString())
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
        $today = $now->toDateString();
        $key = 'recitation_last_scan_date';

        if (DB::table('settings')->where('key', $key)->value('value') === $today) {
            return $this->alreadyRanResult();
        }

        // Reserve today's run atomically: only one request can flip the value
        // from a different value to today's date.
        $reserved = DB::table('settings')
            ->where('key', $key)
            ->where(fn ($query) => $query->whereNull('value')->orWhere('value', '!=', $today))
            ->update(['value' => $today, 'updated_at' => $now]);

        if ($reserved === 0) {
            // No marker row yet: create one (the unique key guards the race).
            try {
                DB::table('settings')->insert([
                    'key'        => $key,
                    'value'      => $today,
                    'group'      => 'recitation',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } catch (UniqueConstraintViolationException $e) {
                return $this->alreadyRanResult();
            }
        }

        try {
            return $this->run($now) + ['already_ran' => false];
        } catch (\Throwable $e) {
            // Release the marker so the next visit can retry.
            DB::table('settings')->where('key', $key)->update(['value' => null, 'updated_at' => $now]);
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
