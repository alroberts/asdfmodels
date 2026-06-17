<?php

use App\Services\GeoNamesImporter;
use App\Models\Message;
use App\Services\MailConfigService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('geonames:import {--path=} {--truncate} {--limit=}', function (GeoNamesImporter $importer) {
    $path = $this->option('path') ?? storage_path('app/geonames/cities500.txt');
    $truncate = (bool) $this->option('truncate');
    $limit = $this->option('limit');
    $limit = $limit !== null ? (int) $limit : null;

    try {
        $count = $importer->import($path, $truncate, $limit, $this->output);
        $this->info("Imported {$count} GeoNames records.");
    } catch (\Throwable $e) {
        $this->error($e->getMessage());
        return 1;
    }
})->purpose('Import GeoNames city data from the downloaded dataset');

Artisan::command('messages:send-unread-email-notifications', function () {
    $messages = Message::query()
        ->with(['thread.user1.modelProfile', 'thread.user1.photographerProfile', 'thread.user2.modelProfile', 'thread.user2.photographerProfile', 'sender'])
        ->where('is_read', false)
        ->whereNull('read_at')
        ->whereNull('email_notification_sent_at')
        ->whereNull('unsent_at')
        ->where('created_at', '<=', now()->subMinutes(10))
        ->orderBy('created_at')
        ->limit(100)
        ->get();

    if ($messages->isEmpty()) {
        $this->info('No unread message email notifications are due.');

        return 0;
    }

    MailConfigService::configure();

    $sent = 0;

    foreach ($messages as $message) {
        $thread = $message->thread;
        $recipient = (int) $thread->user1_id === (int) $message->sender_id ? $thread->user2 : $thread->user1;

        if (!$recipient) {
            continue;
        }

        $recipientEmail = $recipient->modelProfile?->public_email
            ?? $recipient->photographerProfile?->public_email
            ?? $recipient->email;

        if (!$recipientEmail) {
            $message->forceFill(['email_notification_sent_at' => now()])->save();
            continue;
        }

        try {
            $senderName = $message->sender?->display_name ?: $message->sender?->name ?: 'A member';
            $subject = 'Unread message on ASDF Models';
            $preview = trim(mb_substr($message->body, 0, 300));
            $messagesUrl = route('messages.show', $thread->id);

            Mail::raw(
                "You have an unread message on ASDF Models from {$senderName}.\n\n"
                . ($preview !== '' ? "Message preview:\n{$preview}\n\n" : '')
                . "View and reply here: {$messagesUrl}",
                function ($mail) use ($recipientEmail, $subject) {
                    $mail->to($recipientEmail)->subject($subject);
                }
            );

            $message->forceFill(['email_notification_sent_at' => now()])->save();
            $sent++;
        } catch (\Throwable $e) {
            Log::warning('Unable to send delayed message notification email: ' . $e->getMessage());
        }
    }

    $this->info("Sent {$sent} delayed message notification email(s).");

    return 0;
})->purpose('Send email notifications for messages that remain unread after 10 minutes');

Schedule::command('messages:send-unread-email-notifications')->everyMinute();
