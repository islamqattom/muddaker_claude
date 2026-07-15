<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Private per-user channel: receives new messages, typing events, read receipts
Broadcast::channel('chat.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Presence channel for a conversation: tracks who is currently in the chat
Broadcast::channel('presence-chat.{conversationId}', function ($user, $conversationId) {
    $conversation = \App\Models\Conversation::find($conversationId);
    if (!$conversation) {
        return false;
    }
    if (!$conversation->participants()->where('user_id', $user->id)->exists()) {
        return false;
    }
    return [
        'id' => $user->id,
        'name' => $user->name,
        'avatar' => $user->avatar_url ?? null,
    ];
});
