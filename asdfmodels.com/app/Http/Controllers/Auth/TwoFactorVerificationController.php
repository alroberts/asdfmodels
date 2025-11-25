<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorVerificationController extends Controller
{
    /**
     * Show the 2FA verification page.
     */
    public function create(): View
    {
        return view('auth.two-factor-challenge');
    }

    /**
     * Handle the 2FA verification.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);
        
        $userId = $request->session()->get('login.id');
        $user = User::findOrFail($userId);
        
        $code = $request->input('code');
        $valid = false;
        
        // Check if it's a recovery code
        $recoveryCodes = $user->two_factor_recovery_codes ?? [];
        $recoveryCodeIndex = array_search(strtoupper($code), array_map('strtoupper', $recoveryCodes));
        
        if ($recoveryCodeIndex !== false) {
            // Valid recovery code - remove it
            unset($recoveryCodes[$recoveryCodeIndex]);
            $user->two_factor_recovery_codes = array_values($recoveryCodes);
            $user->save();
            $valid = true;
        } elseif ($user->two_factor_method === 'authenticator') {
            // Verify TOTP code
            $google2fa = new Google2FA();
            $valid = $google2fa->verifyKey($user->two_factor_secret, $code);
        } elseif ($user->two_factor_method === 'email') {
            // Verify email code
            if ($user->two_factor_email_code && $user->two_factor_email_code_expires_at && $user->two_factor_email_code_expires_at->isFuture()) {
                $storedCode = Crypt::decryptString($user->two_factor_email_code);
                $valid = hash_equals($storedCode, $code);
            }
        }
        
        if (!$valid) {
            return back()->withErrors(['code' => 'Invalid verification code.']);
        }
        
        // Complete login
        Auth::login($user, $request->session()->get('login.remember', false));
        
        $request->session()->regenerate();
        $request->session()->forget('login.id');
        $request->session()->forget('login.remember');
        
        return redirect()->intended(route('dashboard', absolute: false));
    }
}

