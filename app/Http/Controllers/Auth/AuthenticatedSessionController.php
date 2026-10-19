<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\WhatsAppOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function sendOtp(Request $request, WhatsAppOtpService $otpService): RedirectResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'digits:10'],
        ]);

        try {
            $otpService->send($validated['phone']);
        } catch (\RuntimeException $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'phone' => 'Unable to send an OTP right now. Please try again later.',
            ]);
        }

        return back()->with('status', 'OTP sent to your WhatsApp number.');
    }

    public function store(Request $request, WhatsAppOtpService $otpService): RedirectResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'digits:10'],
            'otp' => ['required', 'digits:6'],
            'remember' => ['sometimes', 'boolean'],
        ]);

        if (! $otpService->consume($validated['phone'], $validated['otp'])) {
            throw ValidationException::withMessages([
                'otp' => 'The OTP is invalid or has expired.',
            ]);
        }

        $user = User::where('phone', $validated['phone'])->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'phone' => 'No account is registered with this phone number.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if ($user->hasRole('Admin')) {
            return redirect()->intended(route('dashboard', absolute: false));
        } elseif ($user->hasRole('Astrologer')) {
            return redirect()->intended(route('astrologer.dashboard', absolute: false));
        }

        return redirect()->intended('/');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
