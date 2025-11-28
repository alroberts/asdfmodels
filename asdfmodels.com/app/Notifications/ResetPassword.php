<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use App\Services\MailConfigService;
use App\Models\Setting;

class ResetPassword extends BaseResetPassword
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
        
        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $this->token);
        }

        // Build mail message and explicitly set From address (same pattern as test email)
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject(__('Reset Password Notification'))
            ->line(__('You are receiving this email because we received a password reset request for your account.'))
            ->action(__('Reset Password'), $url)
            ->line(__('This password reset link will expire in :count minutes.', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]))
            ->line(__('If you did not request a password reset, no further action is required.'))
            ->from($fromAddress, $fromName); // Explicitly set From address from settings
    }
}

