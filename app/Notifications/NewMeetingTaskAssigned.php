<?php

namespace App\Notifications;

use App\Notifications\Channels\WebPushChannel;
use App\Models\MeetingTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewMeetingTaskAssigned extends Notification
{
    use Queueable;

    public function __construct(
        public MeetingTask $task
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', WebPushChannel::class];
    }

    public function toArray(object $notifiable): array
    {
        $link = match (true) {
            $notifiable->isAdmin() => route('admin.tasks.index'),
            $notifiable->isSupervisor() => route('supervisor.tasks.index'),
            $notifiable->isStudent() => route('student.tasks.calendar'),
            default => route('notifications.index'),
        };

        return [
            'type' => 'meeting_task',
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'meeting_minute_id' => $this->task->meeting_minute_id,
            'message' => 'تم إسناد تكليف جديد: ' . $this->task->title,
            'assigned_by' => $this->task->assigner?->name ?? 'غير معروف',
            'link' => $link,
        ];
    }

    public function toWebPush(object $notifiable): array
    {
        $link = match (true) {
            $notifiable->isAdmin() => route('admin.tasks.index'),
            $notifiable->isSupervisor() => route('supervisor.tasks.index'),
            $notifiable->isStudent() => route('student.tasks.calendar'),
            default => route('notifications.index'),
        };

        return [
            'title' => 'تكليف جديد',
            'body'  => 'تم إسناد تكليف جديد: ' . $this->task->title,
            'link'  => $link,
            'type'  => 'meeting_task',
            'extra' => [
                'task_id'          => $this->task->id,
                'meeting_minute_id' => $this->task->meeting_minute_id,
                'assigned_by'      => $this->task->assigner?->name,
            ],
        ];
    }
}
