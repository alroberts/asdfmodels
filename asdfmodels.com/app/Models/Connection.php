<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Connection extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_DECLINED = 'declined';
    public const STATUS_BLOCKED = 'blocked';

    protected $fillable = [
        'requester_id',
        'recipient_id',
        'user_low_id',
        'user_high_id',
        'status',
        'message',
        'blocked_by_user_id',
        'blocked_at',
        'responded_at',
    ];

    protected $casts = [
        'blocked_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Connection $connection) {
            $ids = [(int) $connection->requester_id, (int) $connection->recipient_id];
            sort($ids);

            $connection->user_low_id = $ids[0];
            $connection->user_high_id = $ids[1];
        });
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function scopeBetween(Builder $query, User|int $firstUser, User|int $secondUser): Builder
    {
        $firstId = $firstUser instanceof User ? $firstUser->id : $firstUser;
        $secondId = $secondUser instanceof User ? $secondUser->id : $secondUser;
        $ids = [(int) $firstId, (int) $secondId];
        sort($ids);

        return $query->where('user_low_id', $ids[0])->where('user_high_id', $ids[1]);
    }

    public function scopeAcceptedFor(Builder $query, User|int $user): Builder
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $query
            ->where('status', self::STATUS_ACCEPTED)
            ->where(function (Builder $connectionQuery) use ($userId) {
                $connectionQuery
                    ->where('requester_id', $userId)
                    ->orWhere('recipient_id', $userId);
            });
    }

    public function otherUser(User|int $user): ?User
    {
        $userId = $user instanceof User ? $user->id : $user;

        return (int) $this->requester_id === (int) $userId ? $this->recipient : $this->requester;
    }

    public function accept(): void
    {
        $this->forceFill([
            'status' => self::STATUS_ACCEPTED,
            'responded_at' => now(),
        ])->save();
    }

    public function decline(): void
    {
        $this->forceFill([
            'status' => self::STATUS_DECLINED,
            'responded_at' => now(),
        ])->save();
    }

    public function block(User|int $blockedBy): void
    {
        $blockedById = $blockedBy instanceof User ? $blockedBy->id : $blockedBy;

        $this->forceFill([
            'status' => self::STATUS_BLOCKED,
            'blocked_by_user_id' => $blockedById,
            'blocked_at' => now(),
            'responded_at' => now(),
        ])->save();
    }
}
