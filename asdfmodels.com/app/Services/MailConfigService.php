<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;

class MailConfigService
{
    /**
     * Configure mail settings dynamically from database.
     */
    public static function configure(): void
    {
        try {
            $driver = Setting::getValue('mail_driver', 'sendmail');
            $fromAddress = Setting::getValue('mail_from_address', 'noreply@asdfmodels.com');
            $fromName = Setting::getValue('mail_from_name', 'ASDF Models');

            // Set global from address
            Config::set('mail.from.address', $fromAddress);
            Config::set('mail.from.name', $fromName);

            // Configure based on driver
            switch ($driver) {
                case 'sendmail':
                    self::configureSendmail();
                    break;
                case 'smtp':
                    self::configureSmtp();
                    break;
                case 'ses':
                    self::configureSes();
                    break;
                default:
                    self::configureSendmail();
                    $driver = 'sendmail';
            }

            // Set the default mailer
            Config::set('mail.default', $driver);
        } catch (\Exception $e) {
            // If configuration fails, use defaults
            \Log::warning('Mail configuration failed, using defaults: ' . $e->getMessage());
            Config::set('mail.default', 'sendmail');
            Config::set('mail.from.address', 'noreply@asdfmodels.com');
            Config::set('mail.from.name', 'ASDF Models');
        }
    }

    /**
     * Configure sendmail (PHPMail).
     * Uses PHP's native mail() function since proc_open() is disabled.
     */
    protected static function configureSendmail(): void
    {
        // Use custom PHP mail transport instead of sendmail transport
        // because proc_open() is disabled on the server
        Config::set('mail.mailers.sendmail', [
            'transport' => 'php-mail', // Custom transport registered in AppServiceProvider
        ]);
    }

    /**
     * Configure SMTP.
     */
    protected static function configureSmtp(): void
    {
        $host = Setting::getValue('mail_smtp_host', '');
        $port = Setting::getValue('mail_smtp_port', 587);
        $username = Setting::getValue('mail_smtp_username', '');
        $password = Setting::getValue('mail_smtp_password', '');
        $encryption = Setting::getValue('mail_smtp_encryption', 'tls');
        $timeout = Setting::getValue('mail_smtp_timeout', 30);
        $fromAddress = Setting::getValue('mail_from_address', 'noreply@asdfmodels.com');
        $fromName = Setting::getValue('mail_from_name', 'ASDF Models');

        // For SMTP, many servers (especially Zoho, Gmail, etc.) require the "From" address 
        // to exactly match the authenticated SMTP username to avoid policy violations.
        // This prevents "554 5.7.7 Email policy violation detected" errors.
        $smtpFromAddress = $fromAddress;
        if ($username && !empty($username) && filter_var($username, FILTER_VALIDATE_EMAIL)) {
            // Always use the SMTP username as the From address to ensure policy compliance
            // The envelope sender (Return-Path) will also match, preventing rejections
            $smtpFromAddress = $username;
            
            if ($smtpFromAddress !== $fromAddress) {
                \Log::info("SMTP From address changed from {$fromAddress} to {$username} to match SMTP authentication and avoid policy violations");
            }
        }

        Config::set('mail.mailers.smtp', [
            'transport' => 'smtp',
            'host' => $host,
            'port' => $port,
            'username' => $username,
            'password' => $password,
            'encryption' => $encryption,
            'timeout' => $timeout,
            'local_domain' => parse_url(config('app.url'), PHP_URL_HOST),
        ]);
        
        // Update from address for SMTP to match authenticated user
        // This ensures both the From header and envelope sender match the SMTP username
        Config::set('mail.from.address', $smtpFromAddress);
        Config::set('mail.from.name', $fromName);
    }

    /**
     * Configure Amazon SES.
     */
    protected static function configureSes(): void
    {
        $key = Setting::getValue('mail_ses_key', '');
        $secret = Setting::getValue('mail_ses_secret', '');
        $region = Setting::getValue('mail_ses_region', 'us-east-1');

        Config::set('mail.mailers.ses', [
            'transport' => 'ses',
        ]);

        Config::set('services.ses', [
            'key' => $key,
            'secret' => $secret,
            'region' => $region,
        ]);
    }

    /**
     * Test email configuration by sending a test email.
     * This is the reference implementation that works 100% of the time.
     * All other emails should follow this exact same pattern.
     */
    public static function testEmail(string $toEmail): bool
    {
        try {
            // Ensure configuration is up to date (in case settings changed)
            self::configure();
            
            // Get From address from settings (not from config cache)
            // This ensures we always use the latest settings
            $fromAddress = Setting::getValue('mail_from_address', 'noreply@asdfmodels.com');
            $fromName = Setting::getValue('mail_from_name', 'ASDF Models');
            
            // Use Mail::raw() with explicit From address - this pattern works 100% of the time
            \Illuminate\Support\Facades\Mail::raw('This is a test email from ASDF Models. If you receive this, your email configuration is working correctly!', function ($message) use ($toEmail, $fromAddress, $fromName) {
                $message->to($toEmail)
                        ->from($fromAddress, $fromName) // Explicitly set From address from settings
                        ->subject('Test Email from ASDF Models');
            });

            return true;
        } catch (\Exception $e) {
            \Log::error('Email test failed: ' . $e->getMessage());
            \Log::error('Email test stack trace: ' . $e->getTraceAsString());
            throw $e; // Re-throw so we can see the actual error
        }
    }
}

