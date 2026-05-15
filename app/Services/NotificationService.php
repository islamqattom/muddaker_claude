<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\PushSubscription;
use Minishlink\WebPush\WebPush;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function __construct(
        private readonly ?WebPush $webPush = null,
    ) {}

    public function sendToUser(User $user, string $type, string $title, string $body, ?string $link = null): Notification
    {
        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $body,
            'link' => $link,
            'is_read' => false,
        ]);

        $this->sendWebPush($user, $title, $body, $link);

        return $notification;
    }

    public function sendToUsers(array $userIds, string $type, string $title, string $body, ?string $link = null): void
    {
        $notifications = [];
        $now = now();

        foreach ($userIds as $userId) {
            $notifications[] = [
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'message' => $body,
                'link' => $link,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        Notification::insert($notifications);

        $users = User::whereIn('id', $userIds)->get()->keyBy('id');
        foreach ($userIds as $userId) {
            $user = $users->get($userId);
            if ($user) {
                $this->sendWebPush($user, $title, $body, $link);
            }
        }
    }

    private function sendWebPush(User $user, string $title, string $body, ?string $link): void
    {
        if (!$this->webPush) {
            return;
        }

        $subscription = PushSubscription::where('user_id', $user->id)->first();
        if (!$subscription || $subscription->isExpired()) {
            return;
        }

        try {
            $this->webPush->send([
                'endpoint' => $subscription->endpoint,
                'key' => $subscription->key_p256dh,
                'auth' => $subscription->key_auth,
            ], json_encode([
                'title' => $title,
                'body' => $body,
                'link' => $link,
            ]));
        } catch (\Exception $e) {
            Log::error('Web push failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
        }
    }
}
