<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhotographerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'bio',
        'gender',
        'professional_name',
        'location_city',
        'location_country',
        'location_geoname_id',
        'location_country_code',
        'experience_level',
        'experience_start_year',
        'specialties',
        'equipment',
        'services_offered',
        'studio_location',
        'available_for_travel',
        'pricing_info',
        'verified_at',
        'verified_by',
        'public_email',
        'phone',
        'instagram',
        'portfolio_website',
        'facebook',
        'twitter',
        'profile_photo_path',
        'cover_photo_path',
        'is_public',
        'contains_nudity',
    ];

    protected $casts = [
        'specialties' => 'array',
        'equipment' => 'array',
        'services_offered' => 'array',
        'available_for_travel' => 'boolean',
        'verified_at' => 'datetime',
        'is_public' => 'boolean',
        'contains_nudity' => 'boolean',
    ];

    /**
     * Get the user that owns the photographer profile.
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
     * Get all portfolio images for this photographer.
     */
    public function portfolioImages(): HasMany
    {
        return $this->hasMany(PhotographerPortfolioImage::class, 'photographer_id', 'user_id');
    }

    /**
     * Check if the profile is verified.
     */
    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }
}

