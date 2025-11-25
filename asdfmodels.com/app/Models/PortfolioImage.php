<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioImage extends Model
{
    protected $fillable = [
        'model_id',
        'photographer_id',
        'album_id',
        'original_path',
        'thumbnail_path',
        'medium_path',
        'full_path',
        'title',
        'description',
        'category',
        'tags',
        'is_featured',
        'is_polaroid',
        'contains_nudity',
        'is_public',
        'display_order',
        'shot_date',
        'uploaded_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'is_featured' => 'boolean',
        'is_polaroid' => 'boolean',
        'contains_nudity' => 'boolean',
        'is_public' => 'boolean',
        'display_order' => 'integer',
        'shot_date' => 'date',
        'uploaded_at' => 'datetime',
    ];

    /**
     * Get the model (user) that owns this image.
     */
    public function model(): BelongsTo
    {
        return $this->belongsTo(User::class, 'model_id');
    }

    /**
     * Get the photographer who took this image.
     */
    public function photographer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'photographer_id');
    }

    /**
     * Get the album this image belongs to.
     */
    public function album(): BelongsTo
    {
        return $this->belongsTo(PortfolioAlbum::class);
    }

    /**
     * Get photographer tags for this image.
     */
    public function photographerTags()
    {
        return $this->hasMany(\App\Models\PhotographerImageTag::class, 'portfolio_image_id');
    }

    /**
     * Get the URL for the thumbnail image.
     */
    public function getThumbnailUrlAttribute(): string
    {
        return asset('uploads/models/' . $this->model_id . '/portfolio/thumbnails/' . basename($this->thumbnail_path));
    }

    /**
     * Get the URL for the medium image.
     */
    public function getMediumUrlAttribute(): string
    {
        return asset('uploads/models/' . $this->model_id . '/portfolio/medium/' . basename($this->medium_path));
    }

    /**
     * Get the URL for the full image.
     */
    public function getFullUrlAttribute(): string
    {
        return asset('uploads/models/' . $this->model_id . '/portfolio/full/' . basename($this->full_path));
    }
}

