<?php

namespace App\Console\Commands;

use App\Models\BillingReminder;
use App\Models\MonthlyFee;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BillingSendReminders extends Command
{
    protected $signature = 'billing:send-reminders';
    protected $description = 'Send payment reminder notifications for unpaid fees';

    public function handle(): int
    {
        $reminderEnabled = config('app.' . MonthlyFee::CONFIG_REMINDER_ENABLED, true);
        if (!$reminderEnabled) {
            $this->info('Reminders are disabled. Exiting.');
            return Command::SUCCESS;
        }

        $frequencyDays = config('app.' . MonthlyFee::CONFIG_REMINDER_FREQUENCY, 7);

        $unpaidFees = MonthlyFee::with(['student.user', 'parent.user'])
            ->where('status', MonthlyFee::STATUS_UNPAID)
            ->get();

        $this->info("Found {$unpaidFees->count()} unpaid fees. Checking for reminders to send...");

        $notificationService = app(NotificationService::class);

        $sent = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($unpaidFees as $fee) {
            if (!$fee->parent?->user) {
                Log::warning('Skipping fee with missing parent/user relationship', [
                    'fee_id' => $fee->id,
                    'parent_id' => $fee->parent_id,
                ]);
                $skipped++;
                continue;
            }

            $parentUser = $fee->parent->user;

            // Check if reminder was already sent recently
            $lastReminder = BillingReminder::where('monthly_fee_id', $fee->id)
                ->where('sent_at', '>=', Carbon::now()->subDays($frequencyDays))
                ->first();

            if ($lastReminder) {
                $skipped++;
                continue;
            }

            // Send notification
            $studentName = $fee->student->user->name ?? 'الطالب';
            $monthName = MonthlyFee::getMonthName($fee->month);

            try {
                $notificationService->sendToUser(
                    $parentUser,
                    'payment_reminder',
                    'تنبيه: رسوم شهرية غير مدفوعة',
                    "تنبيه: يوجد رسوم شهرية غير مدفوعة لـ {$studentName} - {$monthName} / {$fee->year}",
                    '/parent/billing'
                );

                // Record the reminder after successful send
                BillingReminder::create([
                    'monthly_fee_id' => $fee->id,
                    'sent_at' => now(),
                ]);

                $sent++;
                $this->line("Sent reminder for {$studentName} ({$fee->month}/{$fee->year}) to {$parentUser->name}");
            } catch (\Throwable $e) {
                Log::error('Failed to send billing reminder', [
                    'fee_id' => $fee->id,
                    'parent_user_id' => $parentUser->id,
                    'error' => $e->getMessage(),
                ]);
                $this->error("Failed to send reminder for fee {$fee->id}: " . $e->getMessage());
                $failed++;
            }
        }

        $this->info("Done! Sent: {$sent}, Skipped (recently sent): {$skipped}, Failed: {$failed}");

        return Command::SUCCESS;
    }
}
