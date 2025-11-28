<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();
        
        // Check if 2FA is enabled
        if ($user->hasTwoFactorEnabled()) {
            // Store user ID in session for 2FA verification
            $request->session()->put('login.id', $user->id);
            $request->session()->put('login.remember', $request->boolean('remember'));
            
            // Logout temporarily
            Auth::logout();
            
            // Send email code if using email method
            if ($user->two_factor_method === 'email') {
                $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $user->two_factor_email_code = \Illuminate\Support\Facades\Crypt::encryptString($code);
                $user->two_factor_email_code_expires_at = now()->addMinutes(10);
                $user->save();
                $user->notify(new \App\Notifications\TwoFactorEmailCode($code));
            }
            
            return redirect()->route('two-factor.login');
        }

        $request->session()->regenerate();

        // After login, check if profile is complete
        return $this->redirectToProfileCompletion($user);
    }

    /**
     * Redirect user to profile completion if needed.
     */
    protected function redirectToProfileCompletion($user): RedirectResponse
    {
        // Admins skip profile completion and email verification
        if ($user->is_admin) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        // Check if email is verified
        if (!$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

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
        return redirect()->intended(route('dashboard', absolute: false));
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
