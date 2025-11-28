<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        // Mail configuration is already set in AppServiceProvider boot()
        // All emails use the centralized mail configuration (SMTP, sendmail, etc.)
        try {
            $request->user()->sendEmailVerificationNotification();
            return back()->with('status', 'verification-link-sent');
        } catch (\Exception $e) {
            \Log::error('Failed to send verification email: ' . $e->getMessage());
            return back()->with('error', 'Failed to send verification email. Please check your email configuration in admin settings.');
        }
    }
}
