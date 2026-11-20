<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $chatId;

    /**
     * Create a new event instance.
     */
    public function __construct(ChatMessage $message)
    {
        $this->message = $message;
        $this->chatId = $message->chat_id;

        $this->sendPushNotification($message);
    }

    protected function sendPushNotification(ChatMessage $message)
    {
        try {
            $chat = \App\Models\Chat::with('participants.user')->find($message->chat_id);
            if (!$chat) return;

            $senderId = $message->user_id;
            foreach ($chat->participants as $participant) {
                if ($participant->user_id !== $senderId && $participant->user && !empty($participant->user->device_token)) {
                    $senderName = $message->user?->name ?? 'Someone';
                    app(\App\Services\FirebaseNotificationService::class)->sendNotification(
                        $participant->user->device_token,
                        "New Message from {$senderName}",
                        \Illuminate\Support\Str::limit($message->message, 80),
                        [
                            'type' => 'chat_message',
                            'chat_id' => (string) $message->chat_id,
                            'sender_id' => (string) $senderId,
                        ]
                    );
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('MessageSent Push Notification Failed: ' . $e->getMessage());
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('chat.' . $this->chatId),
        ];
    }
}
