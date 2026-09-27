<?php

namespace App\Services\Concerns;

use Carbon\CarbonInterface;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * Guards a task so it runs at most once per calendar day.
 *
 * The "last run" marker is stored in the settings table rather than the cache
 * so it works regardless of the configured cache store.
 */
trait ReservesDailyRun
{
    /**
     * Atomically reserve today's run for the given marker key. Returns false
     * when the task already ran today (or another request reserved it).
     */
    protected function reserveDailyRun(string $key, CarbonInterface $now): bool
    {
        $today = $now->toDateString();

        if (DB::table('settings')->where('key', $key)->value('value') === $today) {
            return false;
        }

        $reserved = DB::table('settings')
            ->where('key', $key)
            ->where(fn ($query) => $query->whereNull('value')->orWhere('value', '!=', $today))
            ->update(['value' => $today, 'updated_at' => $now]);

        if ($reserved === 0) {
            try {
                DB::table('settings')->insert([
                    'key'        => $key,
                    'value'      => $today,
                    'group'      => 'automation',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } catch (UniqueConstraintViolationException $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * Release today's reservation so the task can retry on the next run.
     */
    protected function releaseDailyRun(string $key, CarbonInterface $now): void
    {
        DB::table('settings')->where('key', $key)->update([
            'value'      => null,
            'updated_at' => $now,
        ]);
    }
}
