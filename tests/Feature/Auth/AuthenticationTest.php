<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users of every role can authenticate using a WhatsApp OTP', function (string $role) {
    config([
        'services.meta_whatsapp.access_token' => 'test-token',
        'services.meta_whatsapp.phone_number_id' => '12345',
        'services.meta_whatsapp.api_version' => 'v23.0',
        'services.meta_whatsapp.template_name' => 'login_otp',
    ]);

    $otp = null;
    $sentUrl = null;
    $sentRecipient = null;
    $sentTemplate = null;
    Http::fake(function ($request) use (&$otp, &$sentUrl, &$sentRecipient, &$sentTemplate) {
        $otp = $request['template']['components'][0]['parameters'][0]['text'];
        $sentUrl = $request->url();
        $sentRecipient = $request['to'];
        $sentTemplate = $request['template']['name'];

        return Http::response(['messages' => [['id' => 'message-id']]], 200);
    });

    $user = User::factory()->create(['phone' => '9876543210']);
    Role::findOrCreate($role, 'web');
    $user->assignRole($role);

    $this->post(route('login.otp.send'), ['phone' => $user->phone])
        ->assertRedirect();

    expect($sentUrl)->toBe('https://graph.facebook.com/v23.0/12345/messages');
    expect($sentRecipient)->toBe('919876543210');
    expect($sentTemplate)->toBe('login_otp');

    $otpRecord = \App\Models\PhoneOtp::where('phone', $user->phone)->firstOrFail();
    expect(Hash::check($otp, $otpRecord->otp))->toBeTrue();

    $response = $this->post('/login', [
        'phone' => $user->phone,
        'otp' => $otp,
    ]);

    $this->assertAuthenticated();
    $redirect = match ($role) {
        'Admin' => route('dashboard', absolute: false),
        'Astrologer' => route('astrologer.dashboard', absolute: false),
        default => '/',
    };
    $response->assertRedirect($redirect);
})->with(['Admin', 'Astrologer', 'User']);

test('users cannot authenticate with an invalid OTP', function () {
    $user = User::factory()->create(['phone' => '9876543210']);
    \App\Models\PhoneOtp::create([
        'phone' => $user->phone,
        'otp' => Hash::make('123456'),
        'expires_at' => now()->addMinutes(5),
    ]);

    $this->post('/login', [
        'phone' => $user->phone,
        'otp' => '000000',
    ])->assertSessionHasErrors('otp');

    $this->assertGuest();
    $this->assertDatabaseHas('phone_otps', ['phone' => $user->phone]);
});

test('API OTP login works for every role', function (string $role) {
    config([
        'services.meta_whatsapp.access_token' => 'test-token',
        'services.meta_whatsapp.phone_number_id' => '12345',
        'services.meta_whatsapp.template_name' => 'login_otp',
    ]);

    $otp = null;
    Http::fake(function ($request) use (&$otp) {
        $otp = $request['template']['components'][0]['parameters'][0]['text'];

        return Http::response(['messages' => [['id' => 'message-id']]], 200);
    });

    $user = User::factory()->create(['phone' => '9876543210']);
    Role::findOrCreate($role, 'web');
    $user->assignRole($role);

    $this->postJson('/api/sendOtp', ['phone' => $user->phone])
        ->assertOk()
        ->assertJsonPath('status', 'success');

    $this->postJson('/api/verifyOtp', [
        'phone' => $user->phone,
        'otp' => $otp,
    ])->assertOk()
        ->assertJsonPath('status', 'success')
        ->assertJsonStructure(['user', 'token']);
})->with(['Admin', 'Astrologer', 'User']);

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
