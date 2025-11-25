<?php

namespace App\Helpers;

class CookieHelper
{
    /**
     * Check if user has consented to analytics cookies.
     */
    public static function hasAnalyticsConsent(): bool
    {
        if (!isset($_COOKIE['cookie_consent'])) {
            return false;
        }

        $consent = json_decode($_COOKIE['cookie_consent'], true);
        return isset($consent['analytics']) && $consent['analytics'] === true;
    }

    /**
     * Check if user has consented to marketing cookies.
     */
    public static function hasMarketingConsent(): bool
    {
        if (!isset($_COOKIE['cookie_consent'])) {
            return false;
        }

        $consent = json_decode($_COOKIE['cookie_consent'], true);
        return isset($consent['marketing']) && $consent['marketing'] === true;
    }

    /**
     * Get all cookie preferences.
     */
    public static function getPreferences(): array
    {
        if (!isset($_COOKIE['cookie_consent'])) {
            return [
                'essential' => true,
                'analytics' => false,
                'marketing' => false,
            ];
        }

        $consent = json_decode($_COOKIE['cookie_consent'], true);
        return [
            'essential' => $consent['essential'] ?? true,
            'analytics' => $consent['analytics'] ?? false,
            'marketing' => $consent['marketing'] ?? false,
        ];
    }
}

