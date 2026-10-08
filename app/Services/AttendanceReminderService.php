<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use App\Providers\SettingsServiceProvider;
use App\Services\Concerns\ReservesDailyRun;
use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * End-of-day reminder: when today is a work day and a supervisor has not
 * recorded attendance/absence for some of their students, notify them to do so.
 */
class AttendanceReminderService
{
    use ReservesDailyRun;

    public function __construct(
        private readonly NotificationService $notifications,
    ) {}

    /**
     * Send a reminder to every supervisor who still has students without an
     * attendance record for the work day that just ended.
     *
     * @return int Number of supervisors notified.
     */
    public function sendEndOfDayReminders(?CarbonInterface $now = null): int
    {
        $now = $now ?? now();

        if (! $this->isWorkDay($now) || ! $this->isEndOfWorkDay($now)) {
            return 0;
        }

        if (! $this->reserveDailyRun('attendance_reminder_last_sent_date', $now)) {
            return 0;
        }

        try {
            return $this->notifySupervisors($now);
        } catch (\Throwable $e) {
            $this->releaseDailyRun('attendance_reminder_last_sent_date', $now);
            throw $e;
        }
    }

    /**
     * Notify each supervisor whose students are missing today's attendance.
     */
    private function notifySupervisors(CarbonInterface $now): int
    {
        $today = $now->toDateString();
        $sent = 0;

        $supervisors = User::where('role', 'supervisor')
            ->where('is_active', true)
            ->get();

        foreach ($supervisors as $supervisor) {
            $studentIds = Student::where('supervisor_id', $supervisor->id)->active()->pluck('id');

            if ($studentIds->isEmpty()) {
                continue;
            }

            $recordedStudentIds = Attendance::where('supervisor_id', $supervisor->id)
                ->whereDate('date', $today)
                ->pluck('student_id')
                ->unique();

            $missing = $studentIds->diff($recordedStudentIds)->count();

            if ($missing === 0) {
                continue;
            }

            $this->notifications->sendToUser(
                $supervisor,
                'attendance',
                'تذكير بتسجيل الحضور والغياب',
                'لم يتم تسجيل الحضور والغياب لهذا اليوم لـ '.$missing.' طالب. يرجى تسجيله قبل نهاية اليوم.',
                route('supervisor.attendance.index')
            );

            $sent++;
        }

        return $sent;
    }

    /**
     * Whether the given day is a configured work day (from the settings).
     */
    public function isWorkDay(CarbonInterface $now): bool
    {
        $workDays = SettingsServiceProvider::carbonDaysForDayNames(
            (string) config('app.work_days', SettingsServiceProvider::DEFAULT_WORK_DAYS)
        );

        return in_array((int) $now->dayOfWeek, $workDays, true);
    }

    /**
     * Whether the configured work hours for the day have already ended.
     */
    public function isEndOfWorkDay(CarbonInterface $now): bool
    {
        $end = (string) config('app.work_end_time', '20:00');

        try {
            $endTime = Carbon::parse($now->toDateString().' '.$end);
        } catch (\Throwable $e) {
            $endTime = $now->copy()->setTime(20, 0, 0);
        }

        return $now->greaterThanOrEqualTo($endTime);
    }
}
