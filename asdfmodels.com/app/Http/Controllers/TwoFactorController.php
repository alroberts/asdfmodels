<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    /**
     * Show the 2FA setup page.
     */
    public function show(): View
    {
        $user = Auth::user();
        
        return view('profile.two-factor', [
            'user' => $user,
        ]);
    }

    /**
     * Enable 2FA with authenticator app.
     */
    public function enableAuthenticator(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $google2fa = new Google2FA();
        
        // Generate secret if not exists
        if (!$user->two_factor_secret) {
            $secret = $google2fa->generateSecretKey();
            $user->two_factor_secret = $secret;
        } else {
            $secret = $user->two_factor_secret;
        }
        
        // Generate QR code URL
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name', 'ASDF Models'),
            $user->email,
            $secret
        );
        
        // Store temporary state in session
        $request->session()->put('two_factor_setup', [
            'method' => 'authenticator',
            'secret' => $secret,
            'qr_code_url' => $qrCodeUrl,
        ]);
        
        return redirect()->route('two-factor.confirm');
    }

    /**
     * Enable 2FA with email.
     */
    public function enableEmail(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        // Generate 6-digit code
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store code temporarily
        $user->two_factor_email_code = Crypt::encryptString($code);
        $user->two_factor_email_code_expires_at = now()->addMinutes(10);
        $user->save();
        
        // Send email
        $user->notify(new \App\Notifications\TwoFactorEmailCode($code));
        
        // Store temporary state in session
        $request->session()->put('two_factor_setup', [
            'method' => 'email',
        ]);
        
        return redirect()->route('two-factor.confirm');
    }

    /**
     * Show 2FA confirmation page.
     */
    public function confirm(Request $request): View
    {
        $setup = $request->session()->get('two_factor_setup');
        
        if (!$setup) {
            return redirect()->route('two-factor.show');
        }
        
        return view('profile.two-factor-confirm', [
            'setup' => $setup,
        ]);
    }

    /**
     * Verify and enable 2FA.
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);
        
        $user = Auth::user();
        $setup = $request->session()->get('two_factor_setup');
        
        if (!$setup) {
            return redirect()->route('two-factor.show')
                ->withErrors(['code' => 'Session expired. Please try again.']);
        }
        
        $code = $request->input('code');
        $valid = false;
        
        if ($setup['method'] === 'authenticator') {
            $google2fa = new Google2FA();
            $valid = $google2fa->verifyKey($setup['secret'], $code);
        } elseif ($setup['method'] === 'email') {
            if ($user->two_factor_email_code && $user->two_factor_email_code_expires_at && $user->two_factor_email_code_expires_at->isFuture()) {
                $storedCode = Crypt::decryptString($user->two_factor_email_code);
                $valid = hash_equals($storedCode, $code);
            }
        }
        
        if (!$valid) {
            return back()->withErrors(['code' => 'Invalid verification code.']);
        }
        
        // Enable 2FA
        $user->two_factor_enabled = true;
        $user->two_factor_method = $setup['method'];
        if ($setup['method'] === 'authenticator') {
            $user->two_factor_secret = $setup['secret'];
        }
        $user->two_factor_confirmed_at = now();
        
        // Generate recovery codes
        $recoveryCodes = [];
        for ($i = 0; $i < 8; $i++) {
            $recoveryCodes[] = strtoupper(bin2hex(random_bytes(4)));
        }
        $user->two_factor_recovery_codes = $recoveryCodes;
        
        // Clear temporary data
        $user->two_factor_email_code = null;
        $user->two_factor_email_code_expires_at = null;
        $user->save();
        
        $request->session()->forget('two_factor_setup');
        
        return redirect()->route('two-factor.show')
            ->with('status', 'Two-factor authentication has been enabled.')
            ->with('recovery_codes', $recoveryCodes);
    }

    /**
     * Disable 2FA.
     */
    public function disable(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        $user->two_factor_enabled = false;
        $user->two_factor_method = null;
        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_confirmed_at = null;
        $user->two_factor_email_code = null;
        $user->two_factor_email_code_expires_at = null;
        $user->save();
        
        return redirect()->route('two-factor.show')
            ->with('status', 'Two-factor authentication has been disabled.');
    }
}

