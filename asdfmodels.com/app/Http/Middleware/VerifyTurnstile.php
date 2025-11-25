<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class VerifyTurnstile
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $siteKey = \App\Models\Setting::getValue('cloudflare_turnstile_site_key', '');
        $secretKey = \App\Models\Setting::getValue('cloudflare_turnstile_secret_key', '');

        // If Turnstile is not configured, skip verification
        if (empty($siteKey) || empty($secretKey)) {
            return $next($request);
        }

        // Skip verification for GET requests
        if ($request->isMethod('GET')) {
            return $next($request);
        }

        $token = $request->input('cf-turnstile-response');

        if (empty($token)) {
            return back()->withErrors(['cf-turnstile-response' => 'Please complete the security verification.'])->withInput();
        }

        // Verify the token with Cloudflare
        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => $secretKey,
            'response' => $token,
            'remoteip' => $request->ip(),
        ]);

        $result = $response->json();

        if (!isset($result['success']) || $result['success'] !== true) {
            return back()->withErrors(['cf-turnstile-response' => 'Security verification failed. Please try again.'])->withInput();
        }

        return $next($request);
    }
}

