<?php

use App\Events\IncomingAstrologerRequest;
use App\Models\Astrologer;
use App\Models\Chat;
use App\Models\ChatSession;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\Models\Role;

function createApiChatParticipants(bool $online = true, float $balance = 100): array
{
    $customer = User::factory()->create();
    $customer->assignRole(Role::findOrCreate('User', 'web'));
    $customer->ensureWallet()->update(['balance' => $balance]);

    $astrologerUser = User::factory()->create();
    $astrologerUser->assignRole(Role::findOrCreate('Astrologer', 'web'));
    $astrologer = Astrologer::create([
        'user_id' => $astrologerUser->id,
        'expertise' => ['Vedic'],
        'experience_years' => 2,
        'profile_image' => 'astrologer.jpg',
        'asked_call_price' => 10,
        'charged_call_price' => 10,
        'asked_text_price' => 5,
        'charged_text_price' => 10,
        'online' => $online,
        'status' => 'active',
    ]);

    return [$customer, $astrologerUser, $astrologer];
}

test('creating a chat by astrologer profile ID is idempotent', function () {
    [$customer, , $astrologer] = createApiChatParticipants();

    $this->actingAs($customer, 'sanctum')
        ->postJson('/api/chats', ['astrologer_id' => $astrologer->id])
        ->assertCreated()
        ->assertJsonPath('chat.astrologer.id', $astrologer->id);

    $chatId = Chat::query()->value('id');

    $this->postJson('/api/chats', ['astrologer_id' => $astrologer->id])
        ->assertOk()
        ->assertJsonPath('chat.id', $chatId);

    expect(Chat::count())->toBe(1);
    expect(Chat::firstOrFail()->participants()->count())->toBe(2);
});

test('starting a chat checks availability and balance before opening a session', function () {
    [$customer, $astrologerUser, $astrologer] = createApiChatParticipants(online: false);
    $chat = Chat::create(['name' => 'api-chat-'.$customer->id]);
    $chat->participants()->createMany([
        ['user_id' => $customer->id],
        ['user_id' => $astrologerUser->id],
    ]);

    $this->actingAs($customer, 'sanctum')
        ->postJson("/api/chats/{$chat->id}/start")
        ->assertStatus(409);
    expect(ChatSession::count())->toBe(0);

    $astrologer->update(['online' => true]);
    $customer->wallet->update(['balance' => 10]);

    $this->postJson("/api/chats/{$chat->id}/start")
        ->assertStatus(422);
    expect(ChatSession::count())->toBe(0);

    $customer->wallet->update(['balance' => 40]);
    Event::fake([IncomingAstrologerRequest::class]);

    $response = $this->postJson("/api/chats/{$chat->id}/start")
        ->assertOk()
        ->assertJsonPath('chat.id', $chat->id);

    expect($response->json('session_reference'))->not->toBeEmpty();
    Event::assertDispatched(IncomingAstrologerRequest::class, fn ($event) => (
        $event->astrologerId === $astrologerUser->id
        && $event->requestType === 'chat'
        && $event->chatId === $chat->id
        && $event->userId === $customer->id
    ));
});

test('astrologer APIs cannot read or update another astrologer account', function () {
    [, , $ownerProfile] = createApiChatParticipants();
    [, , $otherProfile] = createApiChatParticipants();

    $this->actingAs($ownerProfile->user, 'sanctum')
        ->getJson("/api/astrologers/{$otherProfile->id}/stats")
        ->assertNotFound();

    $this->postJson("/api/astrologers/{$otherProfile->id}/bank-details", [
        'account_holder' => 'Example',
        'account_number' => '1234567890',
        'ifsc_code' => 'ABCD0123456',
        'bank_name' => 'Example Bank',
    ])->assertNotFound();
});
