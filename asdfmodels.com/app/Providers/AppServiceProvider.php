<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register custom PHP mail transport
        $this->app->resolving(\Illuminate\Mail\MailManager::class, function ($mailManager) {
            $mailManager->extend('php-mail', function ($config) {
                return new \App\Mail\Transports\PhpMailTransport();
            });
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configure mail dynamically from database settings
        // This is the SINGLE POINT of configuration for ALL emails in the application
        // When SMTP is selected, ALL emails (verification, 2FA, test, etc.) will use SMTP
        // When sendmail is selected, ALL emails will use sendmail
        // The From address is set globally here and used by all emails
        try {
            \App\Services\MailConfigService::configure();
        } catch (\Exception $e) {
            // If settings table doesn't exist yet, use defaults
            \Log::warning('Mail configuration failed in AppServiceProvider: ' . $e->getMessage());
        }
    }
}
