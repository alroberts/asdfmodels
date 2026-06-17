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
        'date_of_birth',
        'professional_name',
        'display_name_format',
        'show_company_on_profile',
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
        'verified_at',
        'verified_by',
        'public_email',
        'phone',
        'instagram',
        'portfolio_website',
        'social_links',
        'facebook',
        'twitter',
        'profile_photo_path',
        'cover_photo_path',
        'logo_path',
        'is_public',
        'contains_nudity',
    ];

    protected $casts = [
        'specialties' => 'array',
        'equipment' => 'array',
        'social_links' => 'array',
        'services_offered' => 'array',
        'available_for_travel' => 'boolean',
        'show_company_on_profile' => 'boolean',
        'verified_at' => 'datetime',
        'is_public' => 'boolean',
        'contains_nudity' => 'boolean',
        'date_of_birth' => 'date',
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

    public function getDisplayNameAttribute(): string
    {
        $format = $this->display_name_format ?: 'first_name_last_initial';
        $professionalName = trim((string) $this->professional_name);
        $firstName = trim((string) $this->user?->first_name);
        $lastName = trim((string) $this->user?->last_name);

        if ($firstName === '' && $this->user?->name) {
            $parts = preg_split('/\s+/', trim($this->user->name)) ?: [];
            $firstName = $parts[0] ?? '';
            $lastName = count($parts) > 1 ? $parts[count($parts) - 1] : $lastName;
        }

        $fullName = trim($firstName . ' ' . $lastName);
        $lastInitial = $lastName !== '' ? mb_substr($lastName, 0, 1) . '.' : '';
        $initials = trim(
            ($firstName !== '' ? mb_substr($firstName, 0, 1) . '.' : '') .
            ($lastInitial !== '' ? $lastInitial : '')
        );

        if (in_array($format, ['full_name', 'professional_name'], true) && !$this->isVerified()) {
            $format = 'first_name_last_initial';
        }

        $displayName = match ($format) {
            'professional_name' => $professionalName,
            'full_name' => $fullName,
            'first_name' => $firstName,
            'initials' => $initials,
            'first_name_last_initial' => trim($firstName . ' ' . $lastInitial),
            default => $professionalName ?: trim($firstName . ' ' . $lastInitial),
        };

        return $displayName ?: $fullName ?: $professionalName ?: $this->user?->email ?: 'Photographer';
    }

    public function shouldShowCompanyName(): bool
    {
        return $this->isVerified()
            && $this->show_company_on_profile
            && filled($this->professional_name)
            && $this->display_name !== trim((string) $this->professional_name);
    }
}
