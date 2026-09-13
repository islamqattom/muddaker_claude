<?php

namespace App\Console\Commands;

use App\Services\MissedRecitationService;
use Illuminate\Console\Command;

class RegisterMissedRecitationSessions extends Command
{
    protected $signature = 'recitation:register-missed';

    protected $description = 'Auto-register "لم يسمع" sessions for present students who were not recited within the grace period';

    public function handle(MissedRecitationService $service): int
    {
        $this->info('Checking for missed recitation sessions...');

        $result = $service->run();

        $this->info(sprintf(
            'Done. Created: %d, Skipped: %d, Cleaned: %d.',
            $result['created'],
            $result['skipped'],
            $result['cleaned']
        ));

        return self::SUCCESS;
    }
}
