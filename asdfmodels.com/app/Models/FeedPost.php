<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FeedPost extends Model
{
    public const TYPE_POST = 'post';
    public const TYPE_GALLERY = 'gallery';

    protected $fillable = [
        'user_id',
        'type',
        'body',
        'display_body',
        'link_url',
        'link_title',
        'link_description',
        'link_image',
        'related_type',
        'related_id',
        'visibility',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(FeedPostImage::class)->orderBy('display_order');
    }

    public function mentions(): HasMany
    {
        return $this->hasMany(FeedPostMention::class);
    }

    public function related(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'related_type', 'related_id');
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        $connectionIds = Connection::acceptedFor($user)
            ->get()
            ->map(fn (Connection $connection) => $connection->otherUser($user)?->id)
            ->filter()
            ->push($user->id)
            ->unique()
            ->values();

        return $query->where(function (Builder $visibleQuery) use ($user, $connectionIds) {
            $visibleQuery
                ->whereIn('user_id', $connectionIds)
                ->orWhereHas('mentions', function (Builder $mentionQuery) use ($user) {
                    $mentionQuery
                        ->where('mentioned_user_id', $user->id)
                        ->where('status', FeedPostMention::STATUS_ACCEPTED_VISIBLE);
                });
        });
    }

    public function scopeForProfile(Builder $query, User $profileUser): Builder
    {
        return $query->where(function (Builder $profileQuery) use ($profileUser) {
            $profileQuery
                ->where('user_id', $profileUser->id)
                ->orWhereHas('mentions', function (Builder $mentionQuery) use ($profileUser) {
                    $mentionQuery
                        ->where('mentioned_user_id', $profileUser->id)
                        ->where('status', FeedPostMention::STATUS_ACCEPTED_VISIBLE);
                });
        });
    }
}
