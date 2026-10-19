<?php

use App\Services\FirebaseNotificationService;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;

test('firebase notifications are sent to the device token with string data', function () {
    $messaging = Mockery::mock(Messaging::class);
    $messaging->shouldReceive('send')
        ->once()
        ->with(Mockery::on(function (CloudMessage $message) {
            $serialized = $message->jsonSerialize();

            expect($serialized['token'])->toBe('device-token');
            expect($serialized['data'])->toBe([
                'title' => 'New message',
                'body' => 'Hello',
                'sound' => 'custom_sound',
                'chat_id' => '12',
                'details' => '{"kind":"chat"}',
            ]);

            return true;
        }))
        ->andReturn(['name' => 'message-id']);

    $service = new FirebaseNotificationService($messaging);

    expect($service->sendNotification('device-token', 'New message', 'Hello', [
        'chat_id' => 12,
        'details' => ['kind' => 'chat'],
    ]))->toBe(['name' => 'message-id']);
});
