<?php

namespace App\Http\Middleware;

use App\Services\MissedRecitationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Runs the missed-recitation scan at most once per calendar day, triggered by
 * ordinary site visits. This keeps the "لم يسمع" rule working on hosts where
 * no cron entry runs "schedule:run".
 */
class TriggerMissedRecitationScan
{
    public function __construct(
        private MissedRecitationService $service,
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
        if (! config('recitation.scan_on_visits', true)) {
            return;
        }

        try {
            $this->service->runOncePerDay();
        } catch (\Throwable $e) {
            Log::error('Auto missed-recitation scan failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
