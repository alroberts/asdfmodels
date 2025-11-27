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
        
        // Email settings
        $mailDriver = Setting::getValue('mail_driver', 'sendmail');
        $mailFromAddress = Setting::getValue('mail_from_address', 'noreply@asdfmodels.com');
        $mailFromName = Setting::getValue('mail_from_name', 'ASDF Models');
        $mailSendmailPath = Setting::getValue('mail_sendmail_path', '/usr/sbin/sendmail -bs -i');
        
        // SMTP settings
        $mailSmtpHost = Setting::getValue('mail_smtp_host', '');
        $mailSmtpPort = Setting::getValue('mail_smtp_port', 587);
        $mailSmtpUsername = Setting::getValue('mail_smtp_username', '');
        $mailSmtpPassword = Setting::getValue('mail_smtp_password', '');
        $mailSmtpEncryption = Setting::getValue('mail_smtp_encryption', 'tls');
        $mailSmtpTimeout = Setting::getValue('mail_smtp_timeout', 30);
        
        // SES settings
        $mailSesKey = Setting::getValue('mail_ses_key', '');
        $mailSesSecret = Setting::getValue('mail_ses_secret', '');
        $mailSesRegion = Setting::getValue('mail_ses_region', 'us-east-1');
        
        // Dev mode setting
        $devMode = Setting::getValue('dev_mode', false);
        
        return view('admin.settings', [
            'maxImageSize' => $maxImageSize,
            'cloudflareSiteKey' => $cloudflareSiteKey,
            'cloudflareSecretKey' => $cloudflareSecretKey,
            'googleAnalyticsId' => $googleAnalyticsId,
            'mailDriver' => $mailDriver,
            'mailFromAddress' => $mailFromAddress,
            'mailFromName' => $mailFromName,
            'mailSendmailPath' => $mailSendmailPath,
            'mailSmtpHost' => $mailSmtpHost,
            'mailSmtpPort' => $mailSmtpPort,
            'mailSmtpUsername' => $mailSmtpUsername,
            'mailSmtpPassword' => $mailSmtpPassword,
            'mailSmtpEncryption' => $mailSmtpEncryption,
            'mailSmtpTimeout' => $mailSmtpTimeout,
            'mailSesKey' => $mailSesKey,
            'mailSesSecret' => $mailSesSecret,
            'mailSesRegion' => $mailSesRegion,
            'devMode' => $devMode,
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
            
            // Email settings
            'mail_driver' => ['required', 'in:sendmail,smtp,ses'],
            'mail_from_address' => ['required', 'email', 'max:255'],
            'mail_from_name' => ['required', 'string', 'max:255'],
            'mail_sendmail_path' => ['nullable', 'string', 'max:255'],
            
            // SMTP settings
            'mail_smtp_host' => ['nullable', 'string', 'max:255'],
            'mail_smtp_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'mail_smtp_username' => ['nullable', 'string', 'max:255'],
            'mail_smtp_password' => ['nullable', 'string', 'max:255'],
            'mail_smtp_encryption' => ['nullable', 'in:tls,ssl,none'],
            'mail_smtp_timeout' => ['nullable', 'integer', 'min:1', 'max:300'],
            
            // SES settings
            'mail_ses_key' => ['nullable', 'string', 'max:255'],
            'mail_ses_secret' => ['nullable', 'string', 'max:255'],
            'mail_ses_region' => ['nullable', 'string', 'max:50'],
            
            // Dev mode
            'dev_mode' => ['boolean'],
        ]);

        Setting::setValue('max_image_size', $validated['max_image_size'], 'integer', 'Maximum image size in pixels on longest edge');
        Setting::setValue('cloudflare_turnstile_site_key', $validated['cloudflare_turnstile_site_key'] ?? '', 'string', 'Cloudflare Turnstile site key');
        
        // Only update secret key if a new value is provided (password fields are empty when unchanged)
        if (!empty($validated['cloudflare_turnstile_secret_key'])) {
            Setting::setValue('cloudflare_turnstile_secret_key', $validated['cloudflare_turnstile_secret_key'], 'string', 'Cloudflare Turnstile secret key');
        }
        
        Setting::setValue('google_analytics_id', $validated['google_analytics_id'] ?? '', 'string', 'Google Analytics tracking ID');

        // Email settings
        Setting::setValue('mail_driver', $validated['mail_driver'], 'string', 'Email driver (sendmail, smtp, ses)');
        Setting::setValue('mail_from_address', $validated['mail_from_address'], 'string', 'Default from email address');
        Setting::setValue('mail_from_name', $validated['mail_from_name'], 'string', 'Default from name');
        Setting::setValue('mail_sendmail_path', $validated['mail_sendmail_path'] ?? '/usr/sbin/sendmail -bs -i', 'string', 'Sendmail path');
        
        // SMTP settings
        Setting::setValue('mail_smtp_host', $validated['mail_smtp_host'] ?? '', 'string', 'SMTP host');
        Setting::setValue('mail_smtp_port', $validated['mail_smtp_port'] ?? 587, 'integer', 'SMTP port');
        Setting::setValue('mail_smtp_username', $validated['mail_smtp_username'] ?? '', 'string', 'SMTP username');
        if (!empty($validated['mail_smtp_password'])) {
            Setting::setValue('mail_smtp_password', $validated['mail_smtp_password'], 'string', 'SMTP password');
        }
        Setting::setValue('mail_smtp_encryption', $validated['mail_smtp_encryption'] ?? 'tls', 'string', 'SMTP encryption');
        Setting::setValue('mail_smtp_timeout', $validated['mail_smtp_timeout'] ?? 30, 'integer', 'SMTP timeout');
        
        // SES settings
        Setting::setValue('mail_ses_key', $validated['mail_ses_key'] ?? '', 'string', 'AWS SES access key');
        if (!empty($validated['mail_ses_secret'])) {
            Setting::setValue('mail_ses_secret', $validated['mail_ses_secret'], 'string', 'AWS SES secret key');
        }
        Setting::setValue('mail_ses_region', $validated['mail_ses_region'] ?? 'us-east-1', 'string', 'AWS SES region');

        // Dev mode
        Setting::setValue('dev_mode', $request->boolean('dev_mode', false), 'boolean', 'Developer mode - shows detailed error messages');

        return redirect()->route('admin.settings')
            ->with('status', 'Settings updated successfully.');
    }

    /**
     * Send a test email.
     */
    public function testEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'test_email' => ['required', 'email'],
        ]);

        try {
            $success = \App\Services\MailConfigService::testEmail($request->test_email);
            
            if ($success) {
                return redirect()->route('admin.settings')
                    ->with('status', 'Test email sent successfully! Check your inbox.');
            } else {
                return redirect()->route('admin.settings')
                    ->with('error', 'Failed to send test email. Check your email configuration and server logs.');
            }
        } catch (\Exception $e) {
            \Log::error('Test email error: ' . $e->getMessage());
            \Log::error('Test email trace: ' . $e->getTraceAsString());
            
            $devMode = Setting::getValue('dev_mode', false);
            
            if ($devMode) {
                // Show full error details in dev mode
                $errorMessage = 'Error sending test email: ' . $e->getMessage();
                $errorDetails = [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ];
                
                return redirect()->route('admin.settings')
                    ->with('error', $errorMessage)
                    ->with('error_details', $errorDetails);
            } else {
                $errorMessage = 'Error sending test email: ' . $e->getMessage();
                if (strlen($errorMessage) > 200) {
                    $errorMessage = 'Error sending test email. Enable dev mode in settings to see full error details.';
                }
                
                return redirect()->route('admin.settings')
                    ->with('error', $errorMessage);
            }
        }
    }
}

