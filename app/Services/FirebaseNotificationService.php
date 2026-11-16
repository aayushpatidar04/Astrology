<?php

namespace App\Services;

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationService
{
    protected $messaging;

    public function __construct(?Messaging $messaging = null)
    {
        if ($messaging) {
            $this->messaging = $messaging;
            return;
        }

        try {
            $firebaseCredentialsPath = storage_path('firebase_credentials.json');

            $factory = (new Factory)->withServiceAccount($firebaseCredentialsPath);
            $this->messaging = $factory->createMessaging();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function sendNotification($token, $title, $body, $data = [])
    {
        try {
            $data = collect($data)->map(function ($value): string {
                if (is_array($value)) {
                    return json_encode($value, JSON_THROW_ON_ERROR);
                }

                if ($value instanceof \DateTimeInterface) {
                    return $value->format('Y-m-d H:i:s');
                }

                return (string) $value;
            })->all();

            $message = CloudMessage::new()
                ->toToken($token)
                ->withNotification(Notification::create($title, $body))
                ->withData($data)
                ->withHighestPossiblePriority();

            $result = $this->messaging->send($message);
            return $result;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function sendMultipleNotifications($tokens, $title, $body, $data = [])
    {
        try {
            $results = [];
            foreach ($tokens as $token) {
                $results[] = $this->sendNotification($token, $title, $body, $data);
            }

            return $results;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
