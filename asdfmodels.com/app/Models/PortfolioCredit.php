<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PortfolioCredit extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED_VISIBLE = 'accepted_visible';
    public const STATUS_ACCEPTED_HIDDEN = 'accepted_hidden';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'creditable_type',
        'creditable_id',
        'owner_user_id',
        'credited_user_id',
        'created_by_user_id',
        'credited_role',
        'status',
        'source',
        'note',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function creditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function creditedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'credited_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function scopeVisibleOnProfile(Builder $query, User|int $user, ?string $role = null): Builder
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $query
            ->where('credited_user_id', $userId)
            ->whereColumn('owner_user_id', '!=', 'credited_user_id')
            ->when($role, fn (Builder $roleQuery) => $roleQuery->where('credited_role', $role))
            ->where('status', self::STATUS_ACCEPTED_VISIBLE);
    }

    public function scopeAwaitingResponse(Builder $query, User|int $user, ?string $role = null): Builder
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $query
            ->where('credited_user_id', $userId)
            ->whereColumn('owner_user_id', '!=', 'credited_user_id')
            ->when($role, fn (Builder $roleQuery) => $roleQuery->where('credited_role', $role))
            ->where('status', self::STATUS_PENDING);
    }

    public function acceptVisible(): void
    {
        $this->forceFill([
            'status' => self::STATUS_ACCEPTED_VISIBLE,
            'responded_at' => now(),
        ])->save();
    }

    public function acceptHidden(): void
    {
        $this->forceFill([
            'status' => self::STATUS_ACCEPTED_HIDDEN,
            'responded_at' => now(),
        ])->save();
    }

    public function reject(): void
    {
        $this->forceFill([
            'status' => self::STATUS_REJECTED,
            'responded_at' => now(),
        ])->save();
    }
}
