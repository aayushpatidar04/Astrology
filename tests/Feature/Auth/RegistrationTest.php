<?php

use App\Models\Astrologer;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
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

    $this->post(route('register.otp.send'), ['phone' => '9876543210'])
        ->assertRedirect();

    $response = $this->post('/register', [
        'account_type' => 'user',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '9876543210',
        'otp' => $otp,
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect('/');

    $user = User::where('email', 'test@example.com')->firstOrFail();
    expect($user->phone)->toBe('9876543210');
    $this->assertDatabaseHas('roles', ['name' => 'User', 'guard_name' => 'web']);
    $this->assertDatabaseHas('user_wallets', ['user_id' => $user->id]);
    $this->assertDatabaseMissing('phone_otps', ['phone' => $user->phone]);
    $this->assertDatabaseHas('user_details', [
        'user_id' => $user->id,
        'preferred_language' => 'Hindi',
        'profile_image' => null,
    ]);
});

test('astrologers can register with a profile and supporting documents', function () {
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

    $this->post(route('register.otp.send'), ['phone' => '9876543210'])
        ->assertRedirect();

    $this->post('/register', [
        'account_type' => 'astrologer',
        'name' => 'Test Astrologer',
        'email' => 'astrologer@example.com',
        'phone' => '9876543210',
        'otp' => $otp,
        'password' => 'password',
        'password_confirmation' => 'password',
        'bio' => 'Experienced astrologer.',
        'expertise' => 'Vedic, Tarot, Vedic',
        'experience_years' => 8,
        'astrologer_profile_image' => UploadedFile::fake()->image('profile.png'),
        'documents' => [
            ['name' => 'Certificate', 'file' => UploadedFile::fake()->create('certificate.pdf', 100, 'application/pdf')],
        ],
    ])->assertRedirect('/');

    $user = User::where('email', 'astrologer@example.com')->firstOrFail();
    $astrologer = Astrologer::where('user_id', $user->id)->firstOrFail();

    expect($user->hasRole('Astrologer'))->toBeTrue();
    expect($astrologer->expertise)->toBe(['Vedic', 'Tarot']);
    expect($astrologer->experience_years)->toBe(8);
    expect($astrologer->status)->toBe('pending_verification');
    expect($astrologer->profile_image)->toStartWith('uploads/astrologer_profile/');
    expect($astrologer->documents)->toHaveCount(1);
    expect($astrologer->documents[0]['name'])->toBe('Certificate');
    expect($astrologer->documents[0]['document_path'])->toStartWith('uploads/documents/');
    $this->assertDatabaseMissing('user_wallets', ['user_id' => $user->id]);

    File::delete(
        public_path($astrologer->profile_image),
        public_path($astrologer->documents[0]['document_path'])
    );
});

test('registration rejects an invalid OTP', function () {
    $this->post('/register', [
        'account_type' => 'user',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '9876543210',
        'otp' => '000000',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors('otp');

    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
});
