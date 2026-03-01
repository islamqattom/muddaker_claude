<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\RecitationSession;
use App\Models\Warning;
use Illuminate\Console\Command;

class PopulateDayColumns extends Command
{
    protected $signature = 'populate:day-columns';

    protected $description = 'Populate day columns for existing records in attendances, recitation_sessions, and warnings tables';

    public function handle()
    {
        $this->info('Populating day columns...');

        // Populate attendances
        $attendancesCount = Attendance::whereNull('day')->count();
        Attendance::whereNull('day')->chunk(100, function ($attendances) {
            foreach ($attendances as $attendance) {
                if ($attendance->date) {
                    $attendance->day = Attendance::getArabicDay($attendance->date);
                    $attendance->save();
                }
            }
        });
        $this->info("Updated {$attendancesCount} attendance records.");

        // Populate recitation_sessions
        $sessionsCount = RecitationSession::whereNull('day')->count();
        RecitationSession::whereNull('day')->chunk(100, function ($sessions) {
            foreach ($sessions as $session) {
                if ($session->session_date) {
                    $session->day = RecitationSession::getArabicDay($session->session_date);
                    $session->save();
                }
            }
        });
        $this->info("Updated {$sessionsCount} recitation session records.");

        // Populate warnings
        $warningsCount = Warning::whereNull('issue_day')->count();
        Warning::whereNull('issue_day')->chunk(100, function ($warnings) {
            foreach ($warnings as $warning) {
                if ($warning->issue_date) {
                    $warning->issue_day = Warning::getArabicDay($warning->issue_date);
                }
                if ($warning->from_date) {
                    $warning->from_day = Warning::getArabicDay($warning->from_date);
                }
                if ($warning->to_date) {
                    $warning->to_day = Warning::getArabicDay($warning->to_date);
                }
                $warning->save();
            }
        });
        $this->info("Updated {$warningsCount} warning records.");

        $this->info('Day columns populated successfully!');
        return 0;
    }
}
