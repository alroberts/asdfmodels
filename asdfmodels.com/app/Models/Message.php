<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'thread_id',
        'sender_id',
        'body',
        'portfolio_image_id',
        'is_read',
        'read_at',
        'email_notification_sent_at',
        'unsent_at',
        'unsent_by_user_id',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'email_notification_sent_at' => 'datetime',
        'unsent_at' => 'datetime',
    ];

    /**
     * Get the thread this message belongs to.
     */
    public function thread(): BelongsTo
    {
        return $this->belongsTo(MessageThread::class, 'thread_id');
    }

    /**
     * Get the sender.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the attached portfolio image.
     */
    public function portfolioImage(): BelongsTo
    {
        return $this->belongsTo(\App\Models\PortfolioImage::class, 'portfolio_image_id');
    }

    public function unsentBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'unsent_by_user_id');
    }

    public function canBeUnsentBy(User|int $user): bool
    {
        $userId = $user instanceof User ? $user->id : $user;

        return (int) $this->sender_id === (int) $userId
            && $this->unsent_at === null
            && $this->created_at
            && $this->created_at->greaterThanOrEqualTo(now()->subMinutes(5));
    }
}
