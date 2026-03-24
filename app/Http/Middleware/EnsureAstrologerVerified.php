<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAstrologerVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user()?->load('astrologer');

        if ($user->hasRole('Astrologer')) {
            switch ($user->astrologer->status) {
                case 'pending_verification':
                    abort(460, json_encode([
                        'title' => 'Verification Pending',
                        'message' => 'Your astrologer account is under verification. Once approved, you’ll be able to access chats and calls.'
                    ]));

                case 'verified':
                    abort(460, json_encode([
                        'title' => 'Documents Verified',
                        'message' => 'Your documents have been successfully verified. Our team will activate your dashboard access shortly. You’ll receive a notification once your account is active.'
                    ]));
                case 'rejected':
                    abort(460, json_encode([
                        'title' => 'Verification Rejected',
                        'message' => 'Unfortunately, your astrologer account has been rejected. Please contact support for more details.'
                    ]));
                case 'active':
                    return $next($request);
            }
        }

        return $next($request);
    }
}
