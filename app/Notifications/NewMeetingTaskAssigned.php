<?php

namespace App\Notifications;

use App\Models\MeetingTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewMeetingTaskAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public MeetingTask $task
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'meeting_task',
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'meeting_minute_id' => $this->task->meeting_minute_id,
            'message' => 'تم إسناد تكليف جديد: ' . $this->task->title,
            'assigned_by' => $this->task->assigner?->name ?? 'غير معروف',
        ];
    }
}