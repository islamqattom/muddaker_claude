<?php

namespace Tests\Unit\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Notification;
use App\Models\User;
use App\Services\ChatService;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ChatServiceTest extends TestCase
{
    use RefreshDatabase;

    private ChatService $chatService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->chatService = app(ChatService::class);
    }

    public function test_send_message_creates_message_and_updates_conversation(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $conversation = Conversation::factory()->create(['created_by' => $sender->id]);
        $conversation->participants()->attach([$sender->id, $recipient->id]);

        $message = $this->chatService->sendMessage($conversation, $sender, 'Hello there');

        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'conversation_id' => $conversation->id,
            'user_id' => $sender->id,
            'content' => 'Hello there',
        ]);
        $this->assertTrue($conversation->fresh()->updated_at >= $message->created_at);
    }

    public function test_send_message_creates_notification_for_recipient(): void
    {
        $sender = User::factory()->create(['name' => 'Ali']);
        $recipient = User::factory()->create();
        $conversation = Conversation::factory()->create(['created_by' => $sender->id]);
        $conversation->participants()->attach([$sender->id, $recipient->id]);

        $message = $this->chatService->sendMessage($conversation, $sender, 'Direct message');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $recipient->id,
            'type' => 'message',
            'message_id' => $message->id,
            'is_read' => false,
        ]);

        $notification = Notification::where('message_id', $message->id)->first();
        $this->assertStringContainsString('Ali', $notification->title);
        $this->assertEquals('Direct message', $notification->message);
        $this->assertEquals(route('chat.show', $conversation), $notification->link);
    }

    public function test_send_message_does_not_notify_sender(): void
    {
        $sender = User::factory()->create();
        $conversation = Conversation::factory()->create(['created_by' => $sender->id]);
        $conversation->participants()->attach([$sender->id]);

        $message = $this->chatService->sendMessage($conversation, $sender, 'Alone message');

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $sender->id,
            'message_id' => $message->id,
        ]);
    }

    public function test_send_message_notifies_all_group_members_except_sender(): void
    {
        $sender = User::factory()->create();
        $member1 = User::factory()->create();
        $member2 = User::factory()->create();
        $conversation = Conversation::factory()->group('Study Group')->create(['created_by' => $sender->id]);
        $conversation->participants()->attach([$sender->id, $member1->id, $member2->id]);

        $message = $this->chatService->sendMessage($conversation, $sender, 'Group message');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $member1->id,
            'message_id' => $message->id,
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $member2->id,
            'message_id' => $message->id,
        ]);
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $sender->id,
            'message_id' => $message->id,
        ]);

        $notification = Notification::where('user_id', $member1->id)->where('message_id', $message->id)->first();
        $this->assertStringContainsString('Study Group', $notification->title);
    }

    public function test_mark_conversation_as_read_marks_related_notifications_read(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $conversation = Conversation::factory()->create(['created_by' => $sender->id]);
        $conversation->participants()->attach([$sender->id, $recipient->id]);

        $message = $this->chatService->sendMessage($conversation, $sender, 'Unread message');
        $notification = Notification::where('message_id', $message->id)->first();
        $this->assertFalse($notification->fresh()->is_read);

        $this->chatService->markConversationAsRead($conversation, $recipient->id);

        $this->assertTrue($notification->fresh()->is_read);
        $this->assertNotNull($message->fresh()->read_at);
    }
}
