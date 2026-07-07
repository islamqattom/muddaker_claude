<?php

namespace App\Notifications;

use App\Notifications\Channels\WebPushChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PaymentReminder extends Notification
{
    use Queueable;

    public function __construct(
        public string $studentName,
        public int $year,
        public int $month
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', WebPushChannel::class];
    }

    public function toArray(object $notifiable): array
    {
        $months = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
        ];

        $monthName = $months[$this->month] ?? $this->month;

        return [
            'type' => 'payment_reminder',
            'link' => '/parent/billing',
            'message' => "تنبيه: يوجد رسوم شهرية غير مدفوعة لـ {$this->studentName} - {$monthName} / {$this->year}",
            'student_name' => $this->studentName,
            'year' => $this->year,
            'month' => $this->month,
        ];
    }

    public function toWebPush(object $notifiable): array
    {
        $months = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
        ];

        $monthName = $months[$this->month] ?? $this->month;

        return [
            'title' => 'تذكير بدفع الرسوم',
            'body'  => "يوجد رسوم شهرية غير مدفوعة لـ {$this->studentName} - {$monthName} / {$this->year}",
            'link'  => '/parent/billing',
            'type'  => 'payment_reminder',
            'extra' => [
                'student_name' => $this->studentName,
                'year'         => $this->year,
                'month'        => $this->month,
            ],
        ];
    }
}
