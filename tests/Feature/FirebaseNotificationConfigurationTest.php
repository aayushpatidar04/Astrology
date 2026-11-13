<?php

use App\Models\User;
use App\Services\FirebaseNotificationService;

test('firebase credentials return an error when not configured', function () {
    config(['firebase.projects.app.credentials' => null]);

    expect(FirebaseNotificationService::credentialsConfigurationError())
        ->toBe('Firebase credentials are not configured. Set FIREBASE_CREDENTIALS to a service-account key path.');
});

test('firebase credentials reject client configuration JSON', function () {
    $path = tempnam(sys_get_temp_dir(), 'firebase-config-');
    file_put_contents($path, json_encode([
        'project_info' => ['project_id' => 'example'],
        'client' => [],
        'configuration_version' => '1',
    ], JSON_THROW_ON_ERROR));
    config(['firebase.projects.app.credentials' => $path]);

    try {
        expect(FirebaseNotificationService::credentialsConfigurationError())
            ->toContain('not a service-account key');
    } finally {
        unlink($path);
    }
});

test('firebase credentials accept service-account JSON structure', function () {
    $path = tempnam(sys_get_temp_dir(), 'firebase-service-account-');
    file_put_contents($path, json_encode([
        'type' => 'service_account',
        'project_id' => 'example',
        'client_email' => 'firebase@example.iam.gserviceaccount.com',
        'private_key' => 'test-key',
    ], JSON_THROW_ON_ERROR));
    config(['firebase.projects.app.credentials' => $path]);

    try {
        expect(FirebaseNotificationService::credentialsConfigurationError())->toBeNull();
    } finally {
        unlink($path);
    }
});

test('test notification returns a useful status for client configuration JSON', function () {
    $user = User::factory()->create(['device_token' => 'fcm-test-token']);
    $path = tempnam(sys_get_temp_dir(), 'firebase-config-');
    file_put_contents($path, json_encode([
        'project_info' => ['project_id' => 'example'],
        'client' => [],
        'configuration_version' => '1',
    ], JSON_THROW_ON_ERROR));
    config(['firebase.projects.app.credentials' => $path]);

    try {
        $this->actingAs($user, 'sanctum')
            ->postJson('/api/test-notification', [
                'title' => 'Test',
                'body' => 'Notification',
            ])
            ->assertStatus(503)
            ->assertJsonPath(
                'message',
                'Firebase notification service is unavailable or misconfigured.'
            );
    } finally {
        unlink($path);
    }
});
