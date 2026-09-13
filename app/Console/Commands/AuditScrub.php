<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Observers\AuditObserver;
use Illuminate\Console\Command;

class AuditScrub extends Command
{
    protected $signature = 'audit:scrub {--dry-run : Show what would be scrubbed without saving}';

    protected $description = 'Scrub sensitive fields (passwords, tokens) from existing audit logs';

    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');
        $sensitiveFields = AuditObserver::SENSITIVE_FIELDS;

        $total = AuditLog::count();
        $scrubbed = 0;

        AuditLog::query()->chunkById(500, function ($chunk) use ($sensitiveFields, &$scrubbed, $isDryRun) {
            foreach ($chunk as $log) {
                $oldValues = $log->old_values;
                $newValues = $log->new_values;
                $changed = false;

                foreach ($sensitiveFields as $field) {
                    if (is_array($oldValues) && array_key_exists($field, $oldValues)) {
                        $oldValues[$field] = '[REDACTED]';
                        $changed = true;
                    }
                    if (is_array($newValues) && array_key_exists($field, $newValues)) {
                        $newValues[$field] = '[REDACTED]';
                        $changed = true;
                    }
                }

                if (! $changed) {
                    continue;
                }

                $scrubbed++;

                if ($isDryRun) {
                    continue;
                }

                $log->old_values = $oldValues;
                $log->new_values = $newValues;
                $log->save();
            }
        });

        $verb = $isDryRun ? 'Would scrub' : 'Scrubbed';
        $this->info("{$verb} {$scrubbed} of {$total} audit log record(s).");

        return Command::SUCCESS;
    }
}
