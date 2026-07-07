<?php

namespace App\Notifications\Channels;

use App\Models\Notification as NotificationModel;
use App\Services\NotificationService;
use Illuminate\Notifications\Notification;

class WebPushChannel
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    /**
     * Send the given notification as a web push to the notifiable entity.
     *
     * The notification is expected to expose a `toWebPush($notifiable)` method
     * that returns an array with the keys:
     *   - title   (required)
     *   - body    (required)
     *   - link    (optional)
     *   - type    (optional, default 'general')
     *   - extra   (optional array passed through to the payload)
     */
    public function send(object $notifiable, Notification $notification): void
    {
        if (!method_exists($notification, 'toWebPush')) {
            return;
        }

        $payload = $notification->toWebPush($notifiable);
        if (!is_array($payload) || empty($payload['title']) || empty($payload['body'])) {
            return;
        }

        $this->notificationService->sendToUser(
            $notifiable,
            $payload['type'] ?? 'general',
            $payload['title'],
            $payload['body'],
            $payload['link'] ?? null,
            null,
            null,
            $payload['extra'] ?? []
        );
    }
}
