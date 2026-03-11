<?php

use App\Models\Chat;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    return Chat::where('id', $chatId)
        ->whereHas('participants', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->exists();
});

Broadcast::channel('astrologer.{userId}', function ($user, $userId) {
    // Anyone who has a chat with this astrologer can listen
    return Chat::whereHas('participants', fn($q) => $q->where('user_id', $userId))
        ->whereHas('participants', fn($q) => $q->where('user_id', $user->id))
        ->exists();
});


Broadcast::channel('call.{roomId}', function ($user, $roomId) {
    return $user !== null;
});
