<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsernameHistory extends Model
{
    protected $fillable = [
        'user_id',
        'username',
        'type',
        'redirects_to_username',
        'is_active',
        'released_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'released_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
