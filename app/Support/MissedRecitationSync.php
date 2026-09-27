<?php

namespace App\Support;

use App\Services\MissedRecitationService;
use Illuminate\Support\Facades\Log;

/**
 * Runs the missed-recitation scan synchronously (before the page renders) so
 * it works on shared hosting where post-response hooks and cron are not
 * available. It runs at most once per calendar day (tracked in the settings
 * table) and never lets a failure break the page.
 */
class MissedRecitationSync
{
    public static function run(): void
    {
        try {
            app(MissedRecitationService::class)->runOncePerDay();
        } catch (\Throwable $e) {
            Log::error('Missed-recitation synchronous scan failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
