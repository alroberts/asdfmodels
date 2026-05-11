<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationSession extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'liveness_code',
        'id_document_path',
        'liveness_video_path',
        'completed_at',
        'expires_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isComplete(): bool
    {
        return filled($this->id_document_path)
            && filled($this->liveness_video_path)
            && $this->completed_at !== null;
    }
}
