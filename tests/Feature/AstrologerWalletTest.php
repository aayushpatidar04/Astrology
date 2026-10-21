<?php

use App\Models\Astrologer;
use App\Models\CallHistory;
use App\Models\Chat;
use App\Models\ChatSession;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserWalletTransaction;
use App\Services\AstrologerWalletService;
use Spatie\Permission\Models\Role;

test('chat earnings are credited once to the astrologer wallet and payouts deduct from it', function () {
    $customer = User::factory()->create();
    $customer->assignRole(Role::findOrCreate('User', 'web'));
    $customerWallet = $customer->ensureWallet();
    $customerWallet->update(['balance' => 100]);

    $astrologerUser = User::factory()->create();
    $astrologerUser->assignRole(Role::findOrCreate('Astrologer', 'web'));

    $astrologer = Astrologer::create([
        'user_id' => $astrologerUser->id,
        'expertise' => ['Vedic'],
        'experience_years' => 2,
        'profile_image' => 'astrologer.jpg',
        'asked_text_price' => 5,
        'charged_text_price' => 10,
        'status' => 'verified',
    ]);

    $chat = Chat::create(['name' => 'wallet-test-chat']);
    $chat->participants()->createMany([
        ['user_id' => $customer->id],
        ['user_id' => $astrologerUser->id],
    ]);
    $sessionReference = ChatSession::startFor($chat, $customer->id, $astrologerUser->id)
        ->session_reference;

    $request = [
        'seconds' => 60,
        'session_reference' => $sessionReference,
    ];

    $this->actingAs($customer, 'sanctum')
        ->postJson("/api/end-chat/{$chat->id}", $request)
        ->assertOk()
        ->assertJsonPath('deduction', 10);

    $this->postJson("/api/end-chat/{$chat->id}", $request)
        ->assertOk()
        ->assertJsonPath('deduction', 10);

    expect((float) $customerWallet->fresh()->balance)->toBe(90.0);
    expect((float) $astrologerUser->wallet()->firstOrFail()->balance)->toBe(5.0);
    expect(ChatSession::where('session_reference', $sessionReference)->count())->toBe(1);
    expect(UserWalletTransaction::where('type', 'earning')->count())->toBe(1);

    $secondSessionReference = ChatSession::startFor($chat, $customer->id, $astrologerUser->id)
        ->session_reference;

    $this->actingAs($astrologerUser)
        ->postJson("/user/chats/{$chat->id}/end", [
            'elapsed_seconds' => 60,
            'session_reference' => $secondSessionReference,
        ])
        ->assertOk()
        ->assertJsonPath('deduction', 10);

    $this->actingAs($customer)
        ->postJson("/user/chats/{$chat->id}/end", [
            'elapsed_seconds' => 60,
            'session_reference' => $secondSessionReference,
        ])
        ->assertOk()
        ->assertJsonPath('deduction', 10);

    $astrologer->update([
        'asked_call_price' => 8,
        'charged_call_price' => 20,
    ]);
    CallHistory::create([
        'user_id' => $customer->id,
        'astrologer_id' => $astrologerUser->id,
        'status' => 'ringing',
        'started_at' => now(),
    ]);

    $this->postJson("/user/calls/{$chat->id}/end", [
        'elapsed_seconds' => 60,
        'status' => 'answered',
    ])->assertOk()->assertJsonPath('deduction', 20);

    expect((float) $customerWallet->fresh()->balance)->toBe(60.0);
    expect((float) $astrologerUser->wallet()->firstOrFail()->balance)->toBe(18.0);
    expect((float) $astrologer->wallet()->firstOrFail()->balance)->toBe(18.0);
    expect(UserWalletTransaction::where('type', 'earning')->count())->toBe(3);

    $payout = Transaction::create([
        'astrologer_id' => $astrologer->id,
        'amount' => 3,
        'mode' => 'Bank Transfer',
    ]);
    app(AstrologerWalletService::class)->recordPayout($astrologer, $payout);
    app(AstrologerWalletService::class)->recordPayout($astrologer, $payout);

    expect((float) $astrologerUser->wallet()->firstOrFail()->fresh()->balance)->toBe(15.0);
    expect(UserWalletTransaction::where('type', 'payout')->count())->toBe(1);

    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('Admin', 'web'));
    $this->actingAs($admin)
        ->from('/admin/transactions')
        ->post(route('admin.transactions.store'), [
            'astrologer_id' => $astrologer->id,
            'amount' => 16,
            'mode' => 'Bank Transfer',
        ])
        ->assertSessionHasErrors('amount');

    expect(Transaction::count())->toBe(1);

    $this->actingAs($astrologerUser, 'sanctum')
        ->getJson('/api/astrologer/wallet')
        ->assertOk()
        ->assertJsonPath('wallet.balance', '15.00')
        ->assertJsonFragment(['type' => 'payout']);
});
