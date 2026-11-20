<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IncomingAstrologerRequest implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $astrologerId;
    public string $requestType;
    public int $chatId;
    public int $userId;
    public string $userName;

    public function __construct(int $astrologerId, string $requestType, int $chatId, int $userId, string $userName)
    {
        $this->astrologerId = $astrologerId;
        $this->requestType = $requestType;
        $this->chatId = $chatId;
        $this->userId = $userId;
        $this->userName = $userName;

        $this->sendPushNotification();
    }

    protected function sendPushNotification()
    {
        try {
            $astrologerUser = \App\Models\User::find($this->astrologerId);
            if ($astrologerUser && !empty($astrologerUser->device_token)) {
                $title = $this->requestType === 'call' ? 'Incoming Call Request' : 'Incoming Chat Request';
                $body = "{$this->userName} has requested a {$this->requestType} consultation.";
                app(\App\Services\FirebaseNotificationService::class)->sendNotification(
                    $astrologerUser->device_token,
                    $title,
                    $body,
                    [
                        'type' => $this->requestType . '_request',
                        'chat_id' => (string) $this->chatId,
                        'user_id' => (string) $this->userId,
                    ]
                );
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('IncomingAstrologerRequest Push Notification Failed: ' . $e->getMessage());
        }
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('astrologer-inbox.' . $this->astrologerId),
        ];
    }
}
