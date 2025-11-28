<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'user_type' => ['required', 'in:model,photographer'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_photographer' => $request->user_type === 'photographer',
        ]);

        // Auto-verify admin emails
        if ($user->is_admin) {
            $user->markEmailAsVerified();
        } else {
            // Mail configuration is already set in AppServiceProvider boot()
            // Fire the Registered event - this will trigger the verification email
            // All emails use the centralized mail configuration (SMTP, sendmail, etc.)
            event(new Registered($user));
        }

        // Log user in so they can access verification page
        Auth::login($user);

        // If admin, skip verification and go to dashboard
        if ($user->is_admin) {
            return redirect()->route('dashboard');
        }

        // Redirect to email verification notice
        // They'll be redirected to profile completion after verification
        return redirect()->route('verification.notice');
    }
}
