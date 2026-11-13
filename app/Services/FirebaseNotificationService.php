<?php

namespace App\Services;

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;

class FirebaseNotificationService
{
    public function __construct(private Messaging $messaging) {}

    public static function credentialsConfigurationError(): ?string
    {
        $credentials = config('firebase.projects.app.credentials');

        if (blank($credentials)) {
            return 'Firebase credentials are not configured. Set FIREBASE_CREDENTIALS to a service-account key path.';
        }

        if (is_array($credentials) && isset($credentials['file']) && is_string($credentials['file'])) {
            $credentials = $credentials['file'];
        }

        if (is_array($credentials)) {
            $serviceAccount = $credentials;
        } elseif (is_string($credentials)) {
            $path = is_file($credentials) ? $credentials : base_path($credentials);
            if (! is_file($path) || ! is_readable($path)) {
                return 'The configured Firebase credentials file is missing or unreadable.';
            }

            try {
                $contents = file_get_contents($path);
                if (! is_string($contents)) {
                    return 'The configured Firebase credentials file could not be read.';
                }

                $serviceAccount = json_decode(
                    $contents,
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                );
            } catch (\JsonException) {
                return 'The configured Firebase credentials file is not valid JSON.';
            }

            if (! is_array($serviceAccount)) {
                return 'The configured Firebase credentials file is not a service-account key.';
            }
        } else {
            return 'Firebase credentials must be a service-account key or a path to one.';
        }

        if (
            ($serviceAccount['type'] ?? null) !== 'service_account'
            || blank($serviceAccount['project_id'] ?? null)
            || blank($serviceAccount['client_email'] ?? null)
            || blank($serviceAccount['private_key'] ?? null)
        ) {
            return 'The configured Firebase JSON is not a service-account key. Configure a service-account JSON with type, project_id, client_email, and private_key fields.';
        }

        return null;
    }

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
