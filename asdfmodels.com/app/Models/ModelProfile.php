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
        'display_name_format',
        'location_city',
        'location_country',
        'location_geoname_id',
        'location_country_code',
        'date_of_birth',
        'gender',
        'measurement_system',
        'height_cm',
        'weight_kg',
        'chest_cm',
        'waist_cm',
        'inseam_cm',
        'bust_cm',
        'hips_cm',
        'shoe_size_region',
        'shoe_size_value',
        'dress_size_region',
        'dress_size_value',
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
        'experience_start_year',
        'specialties',
        'verified_at',
        'verified_by',
        // Contact
        'public_email',
        'instagram',
        'portfolio_website',
        'social_links',
        // Media
        'profile_photo_path',
        'cover_photo_path',
        // Settings
        'is_public',
        'contains_nudity',
    ];

    protected $casts = [
        'specialties' => 'array',
        'social_links' => 'array',
        'verified_at' => 'datetime',
        'is_public' => 'boolean',
        'contains_nudity' => 'boolean',
        'date_of_birth' => 'date',
        'height_cm' => 'integer',
        'weight_kg' => 'decimal:2',
        'chest_cm' => 'decimal:2',
        'waist_cm' => 'decimal:2',
        'inseam_cm' => 'decimal:2',
        'bust_cm' => 'decimal:2',
        'hips_cm' => 'decimal:2',
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

    public function isComplete(): bool
    {
        return filled($this->bio)
            && filled($this->location_country_code)
            && filled($this->location_city)
            && filled($this->gender)
            && filled($this->experience_level)
            && filled($this->public_email ?: $this->user?->email);
    }

    public function getDisplayNameAttribute(): string
    {
        $name = trim((string) $this->user?->name);

        if ($name === '') {
            return '';
        }

        $format = $this->display_name_format ?: 'first_name_last_initial';

        if ($format === 'full_name' && !$this->isVerified()) {
            $format = 'first_name_last_initial';
        }

        $parts = preg_split('/\s+/', $name) ?: [];
        $firstName = $parts[0] ?? $name;
        $lastName = count($parts) > 1 ? $parts[count($parts) - 1] : '';

        return match ($format) {
            'full_name' => $name,
            'first_name' => $firstName,
            'initials' => $this->formatInitials($parts),
            'first_name_last_initial' => $lastName !== ''
                ? trim($firstName . ' ' . mb_substr($lastName, 0, 1) . '.')
                : $firstName,
            default => $firstName,
        };
    }

    public function getHeightDisplayAttribute(): ?string
    {
        return $this->height ?: $this->formatHeight($this->height_cm);
    }

    public function getWeightDisplayAttribute(): ?string
    {
        return $this->weight ?: $this->formatWeight($this->weight_kg);
    }

    public function getChestDisplayAttribute(): ?string
    {
        return $this->chest ?: $this->formatLength($this->chest_cm);
    }

    public function getWaistDisplayAttribute(): ?string
    {
        return $this->waist ?: $this->formatLength($this->waist_cm);
    }

    public function getInseamDisplayAttribute(): ?string
    {
        return $this->inseam ?: $this->formatLength($this->inseam_cm);
    }

    public function getBustDisplayAttribute(): ?string
    {
        return $this->bust ?: $this->formatLength($this->bust_cm);
    }

    public function getHipsDisplayAttribute(): ?string
    {
        return $this->hips ?: $this->formatLength($this->hips_cm);
    }

    private function formatHeight(?int $cm): ?string
    {
        if (!$cm) {
            return null;
        }

        $totalInches = (int) round($cm / 2.54);
        $feet = intdiv($totalInches, 12);
        $inches = $totalInches % 12;

        return "{$cm} cm / {$feet}'{$inches}\"";
    }

    private function formatWeight(?float $kg): ?string
    {
        if (!$kg) {
            return null;
        }

        $lbs = round($kg * 2.20462);

        return rtrim(rtrim(number_format($kg, 2, '.', ''), '0'), '.') . " kg / {$lbs} lbs";
    }

    private function formatLength(?float $cm): ?string
    {
        if (!$cm) {
            return null;
        }

        $inches = round($cm / 2.54, 1);
        $cmText = rtrim(rtrim(number_format($cm, 2, '.', ''), '0'), '.');
        $inchesText = rtrim(rtrim(number_format($inches, 1, '.', ''), '0'), '.');

        return "{$cmText} cm / {$inchesText} in";
    }

    private function formatInitials(array $parts): string
    {
        if ($parts === []) {
            return '';
        }

        $initials = mb_substr($parts[0], 0, 1);

        if (count($parts) > 1) {
            $initials .= ' ' . mb_substr($parts[count($parts) - 1], 0, 1) . '.';
        }

        return trim($initials);
    }
}
