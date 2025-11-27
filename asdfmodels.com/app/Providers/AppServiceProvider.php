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
        // Configure mail dynamically from settings
        if (!app()->runningInConsole() || app()->runningUnitTests()) {
            try {
                \App\Services\MailConfigService::configure();
            } catch (\Exception $e) {
                // If settings table doesn't exist yet, use defaults
            }
        }
    }
}
