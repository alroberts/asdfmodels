<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModelProfile extends Model
{
    protected $fillable = [
        'user_id',
        'bio',
        'location_city',
        'location_country',
        'date_of_birth',
        'gender',
        // Physical Stats - Male
        'height',
        'weight',
        'chest',
        'waist',
        'inseam',
        'shoe_size',
        'suit_size',
        'hair_color',
        'eye_color',
        // Physical Stats - Female
        'bust',
        'hips',
        'dress_size',
        // Professional
        'experience_level',
        'specialties',
        'verified_at',
        'verified_by',
        // Contact
        'public_email',
        'instagram',
        'portfolio_website',
        // Media
        'profile_photo_path',
        'cover_photo_path',
        // Settings
        'is_public',
        'contains_nudity',
    ];

    protected $casts = [
        'specialties' => 'array',
        'verified_at' => 'datetime',
        'is_public' => 'boolean',
        'contains_nudity' => 'boolean',
        'date_of_birth' => 'date',
    ];

    /**
     * Get the user that owns the model profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who verified this profile.
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get all portfolio images for this model.
     */
    public function portfolioImages(): HasMany
    {
        return $this->hasMany(PortfolioImage::class, 'model_id', 'user_id');
    }

    /**
     * Get polaroid images for this model.
     */
    public function polaroids(): HasMany
    {
        return $this->hasMany(PortfolioImage::class, 'model_id', 'user_id')
                    ->where('is_polaroid', true);
    }

    /**
     * Check if the profile is verified.
     */
    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    /**
     * Get age from date of birth.
     */
    public function getAgeAttribute(): ?int
    {
        if (!$this->date_of_birth) {
            return null;
        }
        
        return $this->date_of_birth->age;
    }
}

