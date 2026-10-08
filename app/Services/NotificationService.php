<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

/**
 * Central service for in-app + Web Push notifications.
 *
 * Responsibilities:
 *  - Insert one row per recipient in the `notifications` table
 *  - De-duplicate (skip if a notification for the same user/message/type
 *    already exists — protects against queue retries)
 *  - Send Web Push to ALL active devices the recipient has subscribed
 *  - Honour per-user, per-type preferences (push_enabled)
 *  - Mark subscriptions inactive when the push provider returns 404/410
 *    (device uninstalled or endpoint expired) so they get cleaned up
 *    naturally on the next attempt
 */
class NotificationService
{
    public function __construct(
        private readonly ?WebPush $webPush = null,
    ) {}

    /**
     * Send an in-app + push notification to a single user.
     *
     * @param array<string,mixed> $extra  Extra payload (sender_name, message_preview, ...)
     */
    public function sendToUser(
        User $user,
        string $type,
        string $title,
        string $body,
        ?string $link = null,
        ?int $messageId = null,
        ?User $sender = null,
        array $extra = []
    ): ?Notification {
        if (! $user->is_active) {
            return null;
        }

        $notification = $this->insertNotification(
            [$user->id],
            $type,
            $title,
            $body,
            $link,
            $messageId,
            $sender?->id,
            $extra
        );

        if ($notification && $user->isPushEnabledFor($type)) {
            $this->sendWebPushToUser($user, $title, $body, $link, $type, $sender, $extra);
            $this->flushWebPush();
        }

        return $notification;
    }

    /**
     * Send an in-app + push notification to many users.
     *
     * @param int[] $userIds
     * @param array<string,mixed> $extra
     */
    public function sendToUsers(
        array $userIds,
        string $type,
        string $title,
        string $body,
        ?string $link = null,
        ?int $messageId = null,
        ?User $sender = null,
        array $extra = []
    ): void {
        if (empty($userIds)) {
            return;
        }

        $userIds = User::whereIn('id', $userIds)
            ->where('is_active', true)
            ->pluck('id')
            ->all();

        if (empty($userIds)) {
            return;
        }

        $this->insertNotification($userIds, $type, $title, $body, $link, $messageId, $sender?->id, $extra);

        $users = User::whereIn('id', $userIds)->get()->keyBy('id');
        foreach ($users as $user) {
            if (!$user->isPushEnabledFor($type)) {
                continue;
            }
            $this->sendWebPushToUser($user, $title, $body, $link, $type, $sender, $extra);
        }

        $this->flushWebPush();
    }

    /**
     * Insert notifications in bulk, skipping duplicates.
     *
     * @param int[] $userIds
     * @param array<string,mixed> $extra
     */
    private function insertNotification(
        array $userIds,
        string $type,
        string $title,
        string $body,
        ?string $link,
        ?int $messageId,
        ?int $senderId,
        array $extra
    ): ?Notification {
        $now = now();
        $rows = [];

        foreach ($userIds as $userId) {
            $row = [
                'user_id' => $userId,
                'sender_id' => $senderId,
                'type' => $type,
                'title' => $title,
                'message' => $body,
                'link' => $link,
                'message_id' => $messageId,
                'data' => $extra ? json_encode($extra) : null,
                'is_read' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // Skip rows that would violate the (user_id, message_id, type) unique
            // constraint. This is what protects against duplicate notifications
            // when the job is retried.
            if ($messageId) {
                $exists = Notification::where('user_id', $userId)
                    ->where('message_id', $messageId)
                    ->where('type', $type)
                    ->exists();
                if ($exists) {
                    continue;
                }
            }

            $rows[] = $row;
        }

        if (empty($rows)) {
            return null;
        }

        // Use insert() and ignore duplicate-key errors so a race condition
        // between two concurrent sends doesn't blow up the whole batch.
        try {
            Notification::insert($rows);
        } catch (\Illuminate\Database\QueryException $e) {
            // If still a duplicate, fall back to one-by-one inserts so the
            // non-conflicting rows still get saved.
            if (str_contains($e->getMessage(), 'Duplicate') || str_contains($e->getMessage(), '23000')) {
                foreach ($rows as $row) {
                    try {
                        Notification::insert($row);
                    } catch (\Illuminate\Database\QueryException $ignored) {
                        // Duplicate — skip silently.
                    }
                }
            } else {
                throw $e;
            }
        }

        return Notification::where('user_id', $rows[0]['user_id'])
            ->where('type', $type)
            ->latest('id')
            ->first();
    }

    /**
     * Queue a Web Push for every active subscription the user owns.
     *
     * @param array<string,mixed> $extra
     */
    private function sendWebPushToUser(
        User $user,
        string $title,
        string $body,
        ?string $link,
        string $type,
        ?User $sender = null,
        array $extra = []
    ): void {
        if (!$this->webPush) {
            return;
        }

        $subscriptions = PushSubscription::where('user_id', $user->id)
            ->where('is_active', true)
            ->get();

        foreach ($subscriptions as $subscription) {
            if ($subscription->isExpired()) {
                $subscription->update(['is_active' => false]);
                continue;
            }

            $payload = [
                'title' => $title,
                'body' => $body,
                'link' => $link,
                'icon' => '/images/muddaker-logo-main-nobg.png',
                'badge' => '/images/muddaker-badge.png',
                'tag' => $this->buildTag($type, $extra, $user->id),
                'type' => $type,
                'sender_name' => $sender?->name,
                'sender_id' => $sender?->id,
                'message_preview' => $extra['message_preview'] ?? null,
                'conversation_id' => $extra['conversation_id'] ?? null,
                'notification_id' => $extra['notification_id'] ?? null,
                'requireInteraction' => in_array($type, ['message', 'warning'], true),
            ];

            try {
                $this->webPush->queueNotification(
                    Subscription::create([
                        'endpoint' => $subscription->endpoint,
                        'publicKey' => $subscription->key_p256dh,
                        'authToken' => $subscription->key_auth,
                    ]),
                    json_encode($payload)
                );

                $subscription->update(['last_used_at' => now()]);
            } catch (\Exception $e) {
                Log::error('Web push queue failed', [
                    'user_id' => $user->id,
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Build a stable notification tag so the same message doesn't stack
     * multiple times in the device's notification tray.
     *
     * @param array<string,mixed> $extra
     */
    private function buildTag(string $type, array $extra, int $userId): string
    {
        if (isset($extra['notification_id'])) {
            return "notif-{$extra['notification_id']}";
        }
        if (isset($extra['conversation_id']) && $type === 'message') {
            return "chat-{$userId}-{$extra['conversation_id']}";
        }
        return "{$type}-{$userId}";
    }

    public function flushWebPush(): void
    {
        if (!$this->webPush) {
            return;
        }

        try {
            $reports = $this->webPush->flush();
            foreach ($reports as $report) {
                if (!$report->isSuccess()) {
                    $statusCode = $report->getResponse()?->getStatusCode();
                    Log::warning('Web push delivery failed', [
                        'endpoint' => $report->getEndpoint(),
                        'status' => $statusCode,
                        'reason' => $report->getReason(),
                    ]);

                    // 404/410 = endpoint gone forever (user uninstalled PWA,
                    // revoked permission, or endpoint expired). Mark inactive
                    // and remove so we stop trying.
                    if (in_array($statusCode, [404, 410], true)) {
                        PushSubscription::where('endpoint', $report->getEndpoint())->delete();
                    } else {
                        PushSubscription::where('endpoint', $report->getEndpoint())
                            ->update(['is_active' => false]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Web push flush failed', ['error' => $e->getMessage()]);
        }
    }
}
