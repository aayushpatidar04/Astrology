<?php

namespace App\Services;

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;

class FirebaseNotificationService
{
    public function __construct(private Messaging $messaging) {}

    public function sendNotification(string $token, string $title, string $body, array $data = []): array
    {
        $data = collect($data)->map(function ($value): string {
            if (is_array($value)) {
                return json_encode($value, JSON_THROW_ON_ERROR);
            }

            if ($value instanceof \DateTimeInterface) {
                return $value->format('Y-m-d H:i:s');
            }

            return (string) $value;
        })->all();

        $data = array_merge([
            'title' => $title,
            'body' => $body,
            'sound' => 'custom_sound',
        ], $data);

        return $this->messaging->send(
            CloudMessage::new()
                ->withToken($token)
                ->withData($data)
                ->withHighestPossiblePriority()
        );
    }

    public function sendMultipleNotifications(array $tokens, string $title, string $body, array $data = []): array
    {
        $results = [];

        foreach ($tokens as $token) {
            $results[] = $this->sendNotification((string) $token, $title, $body, $data);
        }

        return $results;
    }
}
