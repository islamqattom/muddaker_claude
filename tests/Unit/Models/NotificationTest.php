<?php

namespace Tests\Unit\Models;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_has_required_fillable_attributes(): void
    {
        $notification = new Notification();
        $fillable = $notification->getFillable();

        $this->assertContains('user_id', $fillable);
        $this->assertContains('title', $fillable);
        $this->assertContains('message', $fillable);
        $this->assertContains('type', $fillable);
        $this->assertContains('link', $fillable);
        $this->assertContains('is_read', $fillable);
    }

    public function test_notification_has_correct_casts(): void
    {
        $notification = new Notification();
        $casts = $notification->getCasts();

        $this->assertArrayHasKey('is_read', $casts);
        $this->assertEquals('boolean', $casts['is_read']);
    }

    public function test_notification_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->create(['user_id' => $user->id]);

        $this->assertEquals($user->id, $notification->user->id);
    }
}
