<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PhotographerGallery extends Model
{
    protected $fillable = [
        'photographer_id',
        'title',
        'description',
        'cover_image_path',
        'display_order',
        'is_featured',
        'is_public',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_public' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Get the photographer that owns this gallery.
     */
    public function photographer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'photographer_id');
    }

    /**
     * Get the images in this gallery.
     */
    public function images(): BelongsToMany
    {
        return $this->belongsToMany(PhotographerPortfolioImage::class, 'gallery_image', 'gallery_id', 'image_id')
            ->withPivot('display_order')
            ->withTimestamps()
            ->orderBy('gallery_image.display_order')
            ->orderBy('gallery_image.created_at');
    }

    /**
     * Get the image count for this gallery.
     */
    public function getImageCountAttribute(): int
    {
        return $this->images()->count();
    }
}

