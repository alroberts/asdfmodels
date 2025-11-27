<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            // Already verified, check if profile is complete
            return $this->redirectToProfileCompletion($request->user());
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        // After verification, redirect to profile completion
        return $this->redirectToProfileCompletion($request->user());
    }

    /**
     * Redirect user to profile completion if needed.
     */
    protected function redirectToProfileCompletion($user): RedirectResponse
    {
        // Check if user needs to complete profile
        if ($user->is_photographer) {
            // Photographer - check if profile exists
            if (!$user->photographerProfile) {
                return redirect()->route('photographers.profile.edit')
                    ->with('status', 'Please complete your photographer profile to continue.');
            }
        } else {
            // Model - check if profile exists
            if (!$user->modelProfile) {
                return redirect()->route('profile.model.edit')
                    ->with('status', 'Please complete your model profile to continue.');
            }
        }

        // Profile is complete, go to dashboard
        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }
}
