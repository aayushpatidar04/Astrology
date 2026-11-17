<?php

use App\Models\Astrologer;
use App\Models\User;
use Spatie\Permission\Models\Role;

test('users can submit reviews for astrologers and filter astrologers', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('User', 'web'));

    $astrologerUser = User::factory()->create(['name' => 'Astro Guru']);
    $astrologerUser->assignRole(Role::findOrCreate('Astrologer', 'web'));

    $astrologer = Astrologer::create([
        'user_id' => $astrologerUser->id,
        'expertise' => ['Vedic', 'Tarot'],
        'experience_years' => 5,
        'profile_image' => 'guru.jpg',
        'charged_text_price' => 10,
        'charged_call_price' => 20,
        'online' => true,
        'status' => 'verified',
    ]);

    // Test review submission
    $this->actingAs($user)
        ->postJson("/api/astrologers/{$astrologer->id}/reviews", [
            'rating' => 5,
            'review' => 'Amazing consultation!',
        ])
        ->assertOk()
        ->assertJson(['status' => 'success']);

    expect($astrologer->fresh()->average_rating)->toBe(5.0);
    expect($astrologer->fresh()->reviews_count)->toBe(1);

    // Test API filtering astrologers
    $this->actingAs($user, 'sanctum')
        ->getJson('/api/astrologers?search=Guru&specialization=Vedic')
        ->assertOk()
        ->assertJsonPath('astrologers.total', 1);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/astrologers?specialization=Astrology')
        ->assertOk()
        ->assertJsonPath('astrologers.total', 0);
});
