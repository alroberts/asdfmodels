<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PhotographerPortfolioImage extends Model
{
    protected $fillable = [
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
        'model_id',
        'is_featured',
        'contains_nudity',
        'is_public',
        'display_order',
        'shot_date',
        'uploaded_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'is_featured' => 'boolean',
        'contains_nudity' => 'boolean',
        'is_public' => 'boolean',
        'display_order' => 'integer',
        'shot_date' => 'date',
        'uploaded_at' => 'datetime',
    ];

    /**
     * Get the photographer (user) that owns this image.
     */
    public function photographer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'photographer_id');
    }

    /**
     * Get the model (user) in this image, if applicable.
     */
    public function model(): BelongsTo
    {
        return $this->belongsTo(User::class, 'model_id');
    }

    /**
     * Get the galleries this image belongs to.
     */
    public function galleries(): BelongsToMany
    {
        return $this->belongsToMany(PhotographerGallery::class, 'gallery_image', 'image_id', 'gallery_id')
            ->withPivot('display_order')
            ->withTimestamps()
            ->orderBy('gallery_image.display_order')
            ->orderBy('gallery_image.created_at');
    }

    /**
     * Get the URL for the thumbnail image.
     */
    public function getThumbnailUrlAttribute(): string
    {
        return asset($this->thumbnail_path);
    }

    /**
     * Get the URL for the medium image.
     */
    public function getMediumUrlAttribute(): string
    {
        return asset($this->medium_path);
    }

    /**
     * Get the URL for the full image.
     */
    public function getFullUrlAttribute(): string
    {
        return asset($this->full_path);
    }
}

