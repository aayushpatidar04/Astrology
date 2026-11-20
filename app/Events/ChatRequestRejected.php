<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatRequestRejected implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chatId;
    public $astrologerName;

    /**
     * Create a new event instance.
     */
    public function __construct(int $chatId, string $astrologerName)
    {
        $this->chatId = $chatId;
        $this->astrologerName = $astrologerName;

        $this->sendPushNotification();
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

    protected function sendPushNotification()
    {
        try {
            $chat = \App\Models\Chat::with('participants.user')->find($this->chatId);
            if (!$chat) return;

            foreach ($chat->participants as $participant) {
                if ($participant->user && !$participant->user->hasRole('Astrologer') && !empty($participant->user->device_token)) {
                    app(\App\Services\FirebaseNotificationService::class)->sendNotification(
                        $participant->user->device_token,
                        'Chat Request Declined',
                        "{$this->astrologerName} is currently unavailable and has declined your chat request.",
                        [
                            'type' => 'chat_rejected',
                            'chat_id' => (string) $this->chatId,
                        ]
                    );
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('ChatRequestRejected Push Notification Failed: ' . $e->getMessage());
        }
    }
}
