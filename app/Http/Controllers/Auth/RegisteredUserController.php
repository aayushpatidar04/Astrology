<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Astrologer;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\UserWallet;
use App\Services\WhatsAppOtpService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function sendOtp(Request $request, WhatsAppOtpService $otpService): RedirectResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'digits:10', 'unique:users,phone'],
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

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, WhatsAppOtpService $otpService): RedirectResponse
    {
        $validated = $request->validate([
            'account_type' => ['required', 'in:user,astrologer'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'digits:10', 'unique:users,phone'],
            'otp' => ['required', 'digits:6'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'dob' => ['nullable', 'date', 'before_or_equal:today'],
            'birth_time' => ['nullable', 'date_format:H:i'],
            'gender' => ['nullable', 'in:male,female,other'],
            'location' => ['nullable', 'string', 'max:255'],
            'preferred_language' => ['nullable', 'string', 'max:100'],
            'user_profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'bio' => ['required_if:account_type,astrologer', 'nullable', 'string', 'max:5000'],
            'expertise' => ['required_if:account_type,astrologer', 'nullable', 'string', 'max:1000'],
            'experience_years' => ['required_if:account_type,astrologer', 'nullable', 'integer', 'min:0', 'max:80'],
            'astrologer_profile_image' => ['required_if:account_type,astrologer', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'documents' => ['nullable', 'array', 'max:10'],
            'documents.*.name' => ['required', 'string', 'max:255'],
            'documents.*.file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ]);

        $expertise = collect(explode(',', $validated['expertise'] ?? ''))
            ->map(fn (string $item): string => trim($item))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($validated['account_type'] === 'astrologer' && $expertise === []) {
            throw ValidationException::withMessages([
                'expertise' => 'Add at least one area of expertise.',
            ]);
        }

        if (! $otpService->consume($validated['phone'], $validated['otp'])) {
            throw ValidationException::withMessages([
                'otp' => 'The OTP is invalid or has expired.',
            ]);
        }

        $uploadedPaths = [];

        try {
            $user = DB::transaction(function () use ($request, $validated, $expertise, &$uploadedPaths): User {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'password' => Hash::make($validated['password']),
                ]);

                if ($validated['account_type'] === 'astrologer') {
                    $user->assignRole(Role::findOrCreate('Astrologer', 'web'));
                    $user->ensureWallet();

                    $profileImage = $this->storeRegistrationFile(
                        $request->file('astrologer_profile_image'),
                        'uploads/astrologer_profile',
                        $uploadedPaths
                    );

                    $documents = [];
                    foreach ($validated['documents'] ?? [] as $document) {
                        $documents[] = [
                            'name' => $document['name'],
                            'document_path' => $this->storeRegistrationFile(
                                $document['file'],
                                'uploads/documents',
                                $uploadedPaths
                            ),
                        ];
                    }

                    Astrologer::create([
                        'user_id' => $user->id,
                        'bio' => $validated['bio'],
                        'expertise' => $expertise,
                        'experience_years' => $validated['experience_years'],
                        'profile_image' => $profileImage,
                        'documents' => $documents ?: null,
                        'status' => 'pending_verification',
                    ]);
                } else {
                    $user->assignRole(Role::findOrCreate('User', 'web'));
                    UserWallet::firstOrCreate(
                        ['user_id' => $user->id],
                        ['balance' => 0]
                    );

                    $profileImage = $request->hasFile('user_profile_image')
                        ? $this->storeRegistrationFile(
                            $request->file('user_profile_image'),
                            'uploads/user_profile',
                            $uploadedPaths
                        )
                        : null;

                    UserDetail::create([
                        'user_id' => $user->id,
                        'dob' => $validated['dob'] ?? null,
                        'birth_time' => $validated['birth_time'] ?? null,
                        'gender' => $validated['gender'] ?? null,
                        'location' => $validated['location'] ?? null,
                        'preferred_language' => $validated['preferred_language'] ?? 'Hindi',
                        'profile_image' => $profileImage,
                    ]);
                }

                return $user;
            });
        } catch (\Throwable $exception) {
            foreach ($uploadedPaths as $path) {
                if (File::exists(public_path($path))) {
                    File::delete(public_path($path));
                }
            }

            throw $exception;
        }

        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    /**
     * Store a registration upload under public/uploads and track it for rollback cleanup.
     *
     * @param  array<int, string>  $uploadedPaths
     */
    private function storeRegistrationFile(UploadedFile $file, string $directory, array &$uploadedPaths): string
    {
        File::ensureDirectoryExists(public_path($directory));

        $filename = Str::uuid().'.'.$file->extension();
        $file->move(public_path($directory), $filename);
        $path = $directory.'/'.$filename;
        $uploadedPaths[] = $path;

        return $path;
    }
}
