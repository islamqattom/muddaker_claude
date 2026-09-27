<?php

namespace App\Http\Controllers\Cron;

use App\Http\Controllers\Controller;
use App\Services\MissedRecitationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Secret fallback endpoint for an external scheduler (e.g. cron-job.org) on
 * hosts without a real cron. The key is read from config('recitation.cron_key')
 * which is populated from the RECITATION_CRON_KEY env value.
 */
class MissedRecitationController extends Controller
{
    public function run(Request $request, MissedRecitationService $service)
    {
        $expected = (string) config('recitation.cron_key');

        // Fail closed: no configured key means the endpoint is disabled.
        if ($expected === '') {
            abort(404);
        }

        if (! hash_equals($expected, (string) $request->query('key'))) {
            abort(403);
        }

        try {
            $result = $service->run();

            return response()->json([
                'success' => true,
                'created' => $result['created'],
                'skipped' => $result['skipped'],
                'cleaned' => $result['cleaned'],
            ]);
        } catch (\Throwable $e) {
            Log::error('Missed-recitation cron run failed', ['error' => $e->getMessage()]);

            return response()->json(['success' => false], 500);
        }
    }
}
