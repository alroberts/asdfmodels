<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedPostMention extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED_HIDDEN = 'accepted_hidden';
    public const STATUS_ACCEPTED_VISIBLE = 'accepted_visible';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'feed_post_id',
        'mentioned_user_id',
        'mentioned_by_user_id',
        'status',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(FeedPost::class, 'feed_post_id');
    }

    public function mentionedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentioned_user_id');
    }

    public function mentionedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentioned_by_user_id');
    }

    public function respond(string $status): void
    {
        $this->forceFill([
            'status' => $status,
            'responded_at' => now(),
        ])->save();
    }
}
