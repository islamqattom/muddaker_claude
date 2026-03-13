<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RecitationSession;

class UpdateNotRecitateStatus extends Command
{
    protected $signature = 'recitations:update-not-recitate-status';

    protected $description = 'Update not_heard status to not_recitate in recitation_sessions table';

    public function handle()
    {
        $count = RecitationSession::where('status', 'not_heard')->update(['status' => 'not_recitate']);

        $this->info("Updated {$count} records from 'not_heard' to 'not_recitate'.");

        return 0;
    }
}
