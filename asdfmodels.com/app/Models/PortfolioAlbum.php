<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PortfolioAlbum extends Model
{
    protected $fillable = [
        'user_id',
        'owner_role',
        'name',
        'description',
        'cover_image_id',
        'cover_image_path',
        'contains_nudity',
        'is_public',
        'visibility',
        'status',
        'custom_visibility_users',
        'display_order',
    ];

    protected $casts = [
        'contains_nudity' => 'boolean',
        'is_public' => 'boolean',
        'custom_visibility_users' => 'array',
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
     * Get the photographer cover image for this gallery when used by photographer portfolios.
     */
    public function coverPhotographerImage(): BelongsTo
    {
        return $this->belongsTo(PhotographerPortfolioImage::class, 'cover_image_id');
    }

    /**
     * Get all images in this album.
     */
    public function images(): HasMany
    {
        return $this->hasMany(PortfolioImage::class, 'album_id')->orderBy('display_order');
    }

    public function credits(): MorphMany
    {
        return $this->morphMany(PortfolioCredit::class, 'creditable');
    }

    public function getTitleAttribute(): string
    {
        return $this->name;
    }
}
