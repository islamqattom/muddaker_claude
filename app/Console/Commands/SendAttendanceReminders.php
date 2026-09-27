<?php

namespace App\Console\Commands;

use App\Services\AttendanceReminderService;
use Illuminate\Console\Command;

class SendAttendanceReminders extends Command
{
    protected $signature = 'attendance:send-reminders';

    protected $description = 'Remind supervisors who have not recorded the day\'s attendance';

    public function handle(AttendanceReminderService $service): int
    {
        $sent = $service->sendEndOfDayReminders();

        $this->info("Attendance reminders sent: {$sent}");

        return self::SUCCESS;
    }
}
