<?php

use App\Events\IncomingAstrologerRequest;
use App\Models\Astrologer;
use App\Models\CallHistory;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\Models\Role;

function createRealtimeChatParticipants(): array
{
    $customer = User::factory()->create();
    $customer->assignRole(Role::findOrCreate('User', 'web'));
    $customer->ensureWallet()->update(['balance' => 100]);

    $astrologerUser = User::factory()->create();
    $astrologerUser->assignRole(Role::findOrCreate('Astrologer', 'web'));

    $astrologer = Astrologer::create([
        'user_id' => $astrologerUser->id,
        'expertise' => ['Vedic'],
        'experience_years' => 2,
        'profile_image' => 'astrologer.jpg',
        'asked_call_price' => 10,
        'charged_call_price' => 10,
        'asked_text_price' => 10,
        'charged_text_price' => 10,
        'online' => true,
        'status' => 'verified',
    ]);

    $chat = Chat::create(['name' => 'realtime-chat-' . uniqid()]);
    $chat->participants()->createMany([
        ['user_id' => $customer->id],
        ['user_id' => $astrologerUser->id],
    ]);

    return [$customer, $astrologerUser, $astrologer, $chat];
}

test('starting a chat sends an incoming chat request without creating a call', function () {
    [$customer, $astrologerUser, , $chat] = createRealtimeChatParticipants();
    Event::fake([IncomingAstrologerRequest::class]);

    $this->actingAs($customer)
        ->postJson(route('user.chat.start-notification', ['chatId' => $chat->id]))
        ->assertOk()
        ->assertJsonPath('status', 'requested');

    Event::assertDispatched(IncomingAstrologerRequest::class, function ($event) use ($customer, $astrologerUser, $chat) {
        return $event->astrologerId === $astrologerUser->id
            && $event->requestType === 'chat'
            && $event->chatId === $chat->id
            && $event->userId === $customer->id;
    });

    expect(CallHistory::count())->toBe(0);
});

test('starting a call creates a ringing call and notifies the astrologer', function () {
    [$customer, $astrologerUser, , $chat] = createRealtimeChatParticipants();
    config([
        'services.pusher_beams.instance_id' => null,
        'services.pusher_beams.secret_key' => null,
    ]);
    Event::fake([IncomingAstrologerRequest::class]);

    $this->actingAs($customer)
        ->postJson(route('call'), ['roomId' => $chat->id])
        ->assertOk()
        ->assertJsonPath('status', 'ok')
        ->assertJsonPath('call_history.status', 'ringing');

    Event::assertDispatched(IncomingAstrologerRequest::class, function ($event) use ($customer, $astrologerUser, $chat) {
        return $event->astrologerId === $astrologerUser->id
            && $event->requestType === 'call'
            && $event->chatId === $chat->id
            && $event->userId === $customer->id;
    });

    expect(CallHistory::where('user_id', $customer->id)
        ->where('astrologer_id', $astrologerUser->id)
        ->where('status', 'ringing')
        ->count())->toBe(1);
});
