<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index(): View
    {
        $maxImageSize = Setting::getValue('max_image_size', 2100);
        $cloudflareSiteKey = Setting::getValue('cloudflare_turnstile_site_key', '');
        $cloudflareSecretKey = Setting::getValue('cloudflare_turnstile_secret_key', '');
        $googleAnalyticsId = Setting::getValue('google_analytics_id', '');
        
        return view('admin.settings', [
            'maxImageSize' => $maxImageSize,
            'cloudflareSiteKey' => $cloudflareSiteKey,
            'cloudflareSecretKey' => $cloudflareSecretKey,
            'googleAnalyticsId' => $googleAnalyticsId,
        ]);
    }

    /**
     * Update settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'max_image_size' => ['required', 'integer', 'min:500', 'max:5000'],
            'cloudflare_turnstile_site_key' => ['nullable', 'string', 'max:255'],
            'cloudflare_turnstile_secret_key' => ['nullable', 'string', 'max:255'],
            'google_analytics_id' => ['nullable', 'string', 'max:255'],
        ]);

        Setting::setValue('max_image_size', $validated['max_image_size'], 'integer', 'Maximum image size in pixels on longest edge');
        Setting::setValue('cloudflare_turnstile_site_key', $validated['cloudflare_turnstile_site_key'] ?? '', 'string', 'Cloudflare Turnstile site key');
        
        // Only update secret key if a new value is provided (password fields are empty when unchanged)
        if (!empty($validated['cloudflare_turnstile_secret_key'])) {
            Setting::setValue('cloudflare_turnstile_secret_key', $validated['cloudflare_turnstile_secret_key'], 'string', 'Cloudflare Turnstile secret key');
        }
        
        Setting::setValue('google_analytics_id', $validated['google_analytics_id'] ?? '', 'string', 'Google Analytics tracking ID');

        return redirect()->route('admin.settings')
            ->with('status', 'Settings updated successfully.');
    }
}

