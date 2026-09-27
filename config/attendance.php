<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Attendance reminders
    |--------------------------------------------------------------------------
    |
    | At the end of each work day, supervisors who have not recorded
    | attendance/absence for their students are reminded to do so.
    |
    */

    // Run the end-of-day reminder on ordinary web visits (at most once per
    // calendar day) so it works even when no cron runs "schedule:run".
    'reminder_on_visits' => (bool) env('ATTENDANCE_REMINDER_ON_VISITS', true),
];
