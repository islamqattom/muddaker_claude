<?php

namespace App\Notifications;

use App\Models\WarningDraft;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class WarningDraftCreated extends Notification
{
    use Queueable;

    public function __construct(public WarningDraft $draft)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $studentName = $this->draft->student?->name ?? 'الطالب';
        $ruleName = $this->draft->warningRule?->name ?? 'قاعدة الإنذار';
        $link = match (true) {
            $notifiable->isAdmin() => route('admin.warning-drafts.index'),
            $notifiable->isSupervisor() => route('supervisor.warning-drafts.index'),
            default => route('notifications.index'),
        };

        return [
            'type' => 'warning_draft',
            'warning_draft_id' => $this->draft->id,
            'student_id' => $this->draft->student_id,
            'message' => "تنبيه جديد: تم إنشاء مسودة إنذار لـ {$studentName} بناءً على قاعدة \"{$ruleName}\"",
            'link' => $link,
        ];
    }
}
