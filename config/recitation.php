<?php

use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Recitation automation rules
|--------------------------------------------------------------------------
|
| Configuration for the automated "لم يسمع" (missed recitation) rule:
| when a student is marked present on a day that requires a recitation
| session and the supervisor does not record one within the grace period,
| the system registers a "not recited" session automatically.
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

    // Hours that must pass after the attendance was registered.
    'grace_hours' => (int) env('RECITATION_GRACE_HOURS', 24),

    // How many recent days of attendance the scheduled job scans.
    'lookback_days' => (int) env('RECITATION_LOOKBACK_DAYS', 30),

    // Attendance statuses that count as the student having attended.
    'attendance_statuses' => ['present', 'late'],

    // Type/status used for the automatically created session.
    'type' => 'not_recitate',
    'status' => 'not_recitate',
];
