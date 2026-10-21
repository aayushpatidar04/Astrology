<?php

use App\Models\Astrologer;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

test('chat and call directories include both online and offline astrologers', function () {
    $customer = User::factory()->create();
    $customer->assignRole(Role::findOrCreate('User', 'web'));

    foreach ([true, false] as $online) {
        $astrologerUser = User::factory()->create();
        $astrologerUser->assignRole(Role::findOrCreate('Astrologer', 'web'));

        Astrologer::create([
            'user_id' => $astrologerUser->id,
            'expertise' => ['Vedic'],
            'experience_years' => 2,
            'profile_image' => 'astrologer.jpg',
            'asked_call_price' => 10,
            'charged_call_price' => 10,
            'asked_text_price' => 10,
            'charged_text_price' => 10,
            'online' => $online,
            'status' => 'verified',
        ]);
    }

    $this->actingAs($customer)
        ->get(route('user.chat-with-astrologers'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Astrologers/Index')
            ->has('astrologers.data', 2)
            ->where('astrologers.data.0.online', true)
            ->where('astrologers.data.1.online', false)
        );

    $this->get(route('user.talk-to-astrologers'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Astrologers/Index2')
            ->has('astrologers.data', 2)
            ->where('astrologers.data.0.online', true)
            ->where('astrologers.data.1.online', false)
        );
});
