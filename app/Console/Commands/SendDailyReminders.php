<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class SendDailyReminders extends Command
{
    protected $signature = 'notifications:daily-reminder';

    protected $description = 'Send daily reminder notifications to students and parents';

    public function handle(NotificationService $notificationService): int
    {
        $this->info('Starting daily reminder notifications...');

        $users = User::whereIn('role', ['student', 'parent'])
            ->where('is_active', true)
            ->get();

        $sent = 0;
        $failed = 0;

        foreach ($users as $user) {
            try {
                $notificationService->sendToUser(
                    $user,
                    'daily_reminder',
                    'تذكير بالحفظ اليومي',
                    'لا تنسَ حفظك اليومي - جعله الله حجة لك وليس عليك',
                    '/student/dashboard'
                );
                $sent++;
            } catch (\Exception $e) {
                Log::error('Failed to send daily reminder', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
                $failed++;
            }
        }

        $this->info("Daily reminders sent: {$sent} successful, {$failed} failed.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}