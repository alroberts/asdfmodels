<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteNotification extends Model
{
    protected $fillable = [
        'user_id',
        'actor_user_id',
        'type',
        'group_key',
        'title',
        'body',
        'action_url',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function markRead(): void
    {
        if (!$this->read_at) {
            $this->forceFill(['read_at' => now()])->save();
        }
    }

    public static function notifyCredit(PortfolioCredit $credit): self
    {
        $creditable = $credit->creditable;
        $isGallery = $creditable instanceof PortfolioAlbum;
        $gallery = $isGallery ? $creditable : $creditable?->album;
        $actorName = $credit->owner?->display_name ?? $credit->owner?->name ?? 'A member';
        $targetName = $isGallery ? ($gallery?->name ?? 'a gallery') : 'an image';

        $groupKey = 'credit:' . ($gallery?->id ? 'gallery:' . $gallery->id : 'single:' . $credit->id);
        $notification = static::where('user_id', $credit->credited_user_id)
            ->where('type', 'credit_pending')
            ->where('group_key', $groupKey)
            ->where('data->credit_id', $credit->id)
            ->first();

        $payload = [
            'actor_user_id' => $credit->owner_user_id,
            'title' => 'New credit request',
            'body' => "{$actorName} credited you in {$targetName}.",
            'action_url' => route('notifications.index'),
            'data' => [
                'credit_id' => $credit->id,
                'gallery_id' => $gallery?->id,
                'creditable_type' => $credit->creditable_type,
                'creditable_id' => $credit->creditable_id,
            ],
            'read_at' => null,
        ];

        if ($notification) {
            $notification->update($payload);

            return $notification;
        }

        return static::create($payload + [
            'user_id' => $credit->credited_user_id,
            'type' => 'credit_pending',
            'group_key' => $groupKey,
        ]);
    }

    public static function notifyMessage(User $recipient, User $sender, MessageThread $thread): self
    {
        return static::create([
            'user_id' => $recipient->id,
            'actor_user_id' => $sender->id,
            'type' => 'message',
            'group_key' => 'message:thread:' . $thread->id,
            'title' => 'New message',
            'body' => ($sender->display_name ?: $sender->name) . ' sent you a message.',
            'action_url' => route('messages.show', $thread->id),
            'data' => [
                'thread_id' => $thread->id,
            ],
        ]);
    }

    public static function notifyConnectionRequest(Connection $connection): self
    {
        $requester = $connection->requester;
        $recipient = $connection->recipient;
        $actorName = $requester?->display_name ?: $requester?->name ?: 'A member';

        return static::updateOrCreate(
            [
                'user_id' => $connection->recipient_id,
                'type' => 'connection_request',
                'group_key' => 'connection:' . $connection->id,
            ],
            [
                'actor_user_id' => $connection->requester_id,
                'title' => 'New connection request',
                'body' => "{$actorName} wants to connect with you.",
                'action_url' => route('dashboard'),
                'data' => [
                    'connection_id' => $connection->id,
                    'message' => $connection->message,
                    'requester_username' => $requester?->username,
                    'recipient_username' => $recipient?->username,
                ],
                'read_at' => null,
            ]
        );
    }

    public static function notifyFeedMention(FeedPostMention $mention): self
    {
        $actor = $mention->mentionedBy;
        $actorName = $actor?->display_name ?: $actor?->name ?: 'A member';

        return static::updateOrCreate(
            [
                'user_id' => $mention->mentioned_user_id,
                'type' => 'feed_mention',
                'group_key' => 'feed:mention:' . $mention->id,
            ],
            [
                'actor_user_id' => $mention->mentioned_by_user_id,
                'title' => 'New feed mention',
                'body' => "{$actorName} mentioned you in a feed post.",
                'action_url' => route('feed.posts.show', $mention->feed_post_id),
                'data' => [
                    'feed_post_id' => $mention->feed_post_id,
                    'feed_post_mention_id' => $mention->id,
                ],
                'read_at' => null,
            ]
        );
    }
}
