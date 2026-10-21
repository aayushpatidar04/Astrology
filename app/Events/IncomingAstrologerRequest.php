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
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('astrologer-inbox.' . $this->astrologerId),
        ];
    }
}
