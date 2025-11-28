<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use App\Services\MailConfigService;
use App\Models\Setting;

class VerifyEmail extends BaseVerifyEmail
{
    /**
     * Build the mail representation of the notification.
     * Uses the EXACT same approach as the test email that works 100% of the time.
     * Ensures mail configuration is loaded and From address is set from settings.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        // Use the same approach as testEmail() - ensure configuration is loaded
        MailConfigService::configure();
        
        // Get From address from settings (same as test email approach)
        $fromAddress = Setting::getValue('mail_from_address', 'noreply@asdfmodels.com');
        $fromName = Setting::getValue('mail_from_name', 'ASDF Models');
        
        $verificationUrl = $this->verificationUrl($notifiable);

        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $verificationUrl);
        }

        // Build mail message and explicitly set From address (same pattern as test email)
        return (new MailMessage)
            ->subject(__('Verify Email Address'))
            ->line(__('Please click the button below to verify your email address.'))
            ->action(__('Verify Email Address'), $verificationUrl)
            ->line(__('If you did not create an account, no further action is required.'))
            ->from($fromAddress, $fromName); // Explicitly set From address from settings
    }
}

