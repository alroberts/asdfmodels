<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Services\MailConfigService;
use App\Models\Setting;

class TwoFactorEmailCode extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $code
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     * Uses the EXACT same approach as the test email that works 100% of the time.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Use the same approach as testEmail() - ensure configuration is loaded
        MailConfigService::configure();
        
        // Get From address from settings (same as test email approach)
        $fromAddress = Setting::getValue('mail_from_address', 'noreply@asdfmodels.com');
        $fromName = Setting::getValue('mail_from_name', 'ASDF Models');
        
        // Build mail message and explicitly set From address (same pattern as test email)
        return (new MailMessage)
            ->subject('Your Two-Factor Authentication Code')
            ->line('Your two-factor authentication code is:')
            ->line('**' . $this->code . '**')
            ->line('This code will expire in 10 minutes.')
            ->line('If you did not request this code, please ignore this email.')
            ->from($fromAddress, $fromName); // Explicitly set From address from settings
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'code' => $this->code,
        ];
    }
}

