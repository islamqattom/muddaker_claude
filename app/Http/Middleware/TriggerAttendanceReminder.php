<?php

namespace App\Http\Middleware;

use App\Services\AttendanceReminderService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sends the end-of-day attendance reminder at most once per day, triggered by
 * ordinary site visits, so it works on hosts where no cron runs
 * "schedule:run".
 */
class TriggerAttendanceReminder
{
    public function __construct(
        private AttendanceReminderService $service,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Run after the response has been prepared so it does not slow the page.
     */
    public function terminate(Request $request, Response $response): void
    {
        if (! config('attendance.reminder_on_visits', true)) {
            return;
        }

        try {
            $this->service->sendEndOfDayReminders();
        } catch (\Throwable $e) {
            Log::error('Auto attendance reminder failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
