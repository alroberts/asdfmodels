<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PortfolioAlbum extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'cover_image_id',
        'contains_nudity',
        'is_public',
        'display_order',
    ];

    protected $casts = [
        'contains_nudity' => 'boolean',
        'is_public' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Get the user that owns this album.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the cover image for this album.
     */
    public function coverImage(): BelongsTo
    {
        return $this->belongsTo(PortfolioImage::class, 'cover_image_id');
    }

    /**
     * Get all images in this album.
     */
    public function images(): HasMany
    {
        return $this->hasMany(PortfolioImage::class)->orderBy('display_order');
    }
}

