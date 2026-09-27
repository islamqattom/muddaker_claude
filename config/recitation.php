<?php

use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Recitation automation rules
|--------------------------------------------------------------------------
|
| Configuration for the automated "لم يسمع" (missed recitation) rule:
| when a student is marked present on a day that requires a recitation
| session and the supervisor does not record one before that day ends, the
| system registers a "not recited" session automatically at the start of the
| following day.
|
| Day numbers use Carbon constants (0 = Sunday ... 6 = Saturday), so the
| rule can be extended later without changing code.
|
*/

$requiredDays = env('RECITATION_REQUIRED_DAYS');
$excludedDays = env('RECITATION_EXCLUDED_DAYS');

return [
    // Days on which a present student must have a recitation session.
    'required_days' => $requiredDays !== null && $requiredDays !== ''
        ? array_map('intval', explode(',', (string) $requiredDays))
        : [Carbon::SATURDAY, Carbon::TUESDAY],

    // Days fully excluded from the rule (never auto-registered).
    'excluded_days' => $excludedDays !== null && $excludedDays !== ''
        ? array_map('intval', explode(',', (string) $excludedDays))
        : [Carbon::FRIDAY],

    // How many recent days of attendance the scheduled job scans.
    'lookback_days' => (int) env('RECITATION_LOOKBACK_DAYS', 30),

    // Window used by the automatic scan (synchronous page triggers and the
    // secret cron URL). Kept short so the first successful run after a long
    // outage does not backfill a large number of old sessions at once.
    // Older periods are only created explicitly from the diagnostics page.
    'auto_lookback_days' => (int) env('RECITATION_AUTO_LOOKBACK_DAYS', 7),

    // Run the scan on ordinary web visits (at most once per calendar day) so
    // the rule works even when no cron runs "schedule:run".
    'scan_on_visits' => (bool) env('RECITATION_SCAN_ON_VISITS', true),

    // Shared secret for the /cron/missed-recitation?key=... fallback URL,
    // meant to be called by an external scheduler (e.g. cron-job.org).
    'cron_key' => env('RECITATION_CRON_KEY'),

    // Attendance statuses that count as the student having attended.
    'attendance_statuses' => ['present', 'late'],

    // Type/status used for the automatically created session.
    'type' => 'not_recitate',
    'status' => 'not_recitate',
];
