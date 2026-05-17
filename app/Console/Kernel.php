<?php
namespace App\Console;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
class Kernel extends ConsoleKernel {
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('warning:evaluate-rules')->daily();
        $schedule->command('auditlogs:clean')->daily();

        $schedule->command('billing:generate')
            ->monthlyOn(25, '00:00');

        $schedule->command('billing:send-reminders')
            ->dailyAt('09:00');

        $schedule->command('notifications:daily-reminder')
            ->dailyAt('08:00');
    }
    protected function commands(): void { $this->load(__DIR__.'/Commands'); require base_path('routes/console.php'); }
}
