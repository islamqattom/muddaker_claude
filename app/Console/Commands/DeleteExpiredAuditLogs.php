<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;

class DeleteExpiredAuditLogs extends Command
{
    protected $signature = 'auditlogs:clean {--dry-run : Show what would be deleted without deleting}';

    protected $description = 'Delete audit logs older than 1 year';

    public function handle(): int
    {
        $cutoffDate = now()->subYear();

        if ($this->option('dry-run')) {
            $count = AuditLog::where('created_at', '<', $cutoffDate)->count();
            $this->info("Dry run: Would delete {$count} audit log(s) older than {$cutoffDate->format('Y-m-d')}.");
            return Command::SUCCESS;
        }

        $deleted = 0;
        AuditLog::where('created_at', '<', $cutoffDate)
            ->chunkById(1000, function ($chunk) use (&$deleted) {
                $deleted += $chunk->delete();
            });

        $this->info("Deleted {$deleted} audit log(s) older than 1 year.");

        return Command::SUCCESS;
    }
}
