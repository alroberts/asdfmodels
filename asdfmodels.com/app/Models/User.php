<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (!filled($user->username)) {
                $user->username = $user->generateUniqueUsername();
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'name',
        'email',
        'password',
        'is_admin',
        'is_photographer',
        'two_factor_enabled',
        'two_factor_method',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'two_factor_email_code',
        'two_factor_email_code_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
            'two_factor_recovery_codes' => 'array',
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_email_code_expires_at' => 'datetime',
        ];
    }

    /**
     * Get the user's full legal name.
     */
    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->last_name,
        ])));
    }

    /**
     * Keep the legacy name field as a redacted public alias.
     */
    public function getNameAttribute($value): string
    {
        return $value ?: $this->formatPublicName($this->first_name, $this->last_name);
    }

    /**
     * Split an assigned name into first and last name parts.
     */
    public function setNameAttribute($value): void
    {
        $value = trim((string) $value);
        $parts = preg_split('/\s+/', $value) ?: [];
        $firstName = trim((string) array_shift($parts));
        $lastName = trim(implode(' ', $parts));

        $this->attributes['first_name'] = $firstName !== '' ? $firstName : null;
        $this->attributes['last_name'] = $lastName !== '' ? $lastName : null;
        $this->attributes['name'] = $this->formatPublicName($firstName, $lastName);
    }

    /**
     * Get a compact public-facing version of the name.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->formatPublicName($this->first_name, $this->last_name);
    }

    private function formatPublicName(?string $firstName, ?string $lastName): string
    {
        $firstName = trim((string) $firstName);
        $lastName = trim((string) $lastName);

        if ($firstName === '') {
            return $lastName;
        }

        if ($lastName === '') {
            return $firstName;
        }

        return $firstName . ' ' . mb_substr($lastName, 0, 1) . '.';
    }

    /**
     * Check if user has 2FA enabled.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_enabled && $this->two_factor_confirmed_at !== null;
    }

    /**
     * Get the encrypted two factor secret.
     */
    public function getTwoFactorSecretAttribute($value)
    {
        return $value ? decrypt($value) : null;
    }

    /**
     * Set the encrypted two factor secret.
     */
    public function setTwoFactorSecretAttribute($value)
    {
        $this->attributes['two_factor_secret'] = $value ? encrypt($value) : null;
    }

    /**
     * Get the model profile for this user.
     */
    public function modelProfile()
    {
        return $this->hasOne(ModelProfile::class);
    }

    /**
     * Get portfolio images where this user is the model.
     */
    public function portfolioImages()
    {
        return $this->hasMany(PortfolioImage::class, 'model_id');
    }

    /**
     * Get portfolio images where this user is the photographer.
     */
    public function photographerImages()
    {
        return $this->hasMany(PortfolioImage::class, 'photographer_id');
    }

    /**
     * Get the photographer profile for this user.
     */
    public function photographerProfile()
    {
        return $this->hasOne(PhotographerProfile::class);
    }

    public function usernameHistories(): HasMany
    {
        return $this->hasMany(UsernameHistory::class);
    }

    public function profileRouteIdentifier(): string
    {
        return (string) $this->username;
    }

    public function canEditUsername(): bool
    {
        return (bool) ($this->is_photographer
            ? $this->photographerProfile?->isVerified()
            : $this->modelProfile?->isVerified());
    }

    public function hasChangedUsernameBefore(): bool
    {
        return $this->usernameHistories()
            ->where('type', 'original')
            ->where('username', '!=', $this->username)
            ->exists();
    }

    public function changeUsername(string $username): bool
    {
        $username = Str::slug(Str::lower(trim($username)));

        if ($username === '' || $username === $this->username) {
            return false;
        }

        $previousUsername = (string) $this->username;
        $hasCustomHistory = $this->hasChangedUsernameBefore();

        $this->usernameHistories()->firstOrCreate(
            ['username' => $previousUsername],
            [
                'type' => 'original',
                'redirects_to_username' => $username,
                'is_active' => true,
            ]
        );

        $this->usernameHistories()
            ->where('type', 'original')
            ->where('is_active', true)
            ->update(['redirects_to_username' => $username]);

        if ($hasCustomHistory) {
            $this->usernameHistories()->updateOrCreate(
                ['username' => $previousUsername],
                [
                    'type' => 'custom',
                    'redirects_to_username' => null,
                    'is_active' => false,
                    'released_at' => now(),
                ]
            );
        }

        $this->username = $username;
        $this->save();

        return true;
    }

    public function ensureUsername(): void
    {
        if (filled($this->username)) {
            return;
        }

        $this->username = $this->generateUniqueUsername();
        $this->save();
    }

    public function generateUniqueUsername(): string
    {
        $firstName = trim((string) $this->first_name);
        $lastName = trim((string) $this->last_name);

        if ($firstName === '' && $this->email) {
            $firstName = Str::before($this->email, '@');
        }

        $lastInitial = $lastName !== '' ? mb_substr($lastName, 0, 1) : '';
        $base = Str::slug(trim($firstName . ' ' . $lastInitial)) ?: 'member';
        $base = Str::limit($base, 66, '');

        do {
            $username = $base . '-' . random_int(1000, 9999);
            $query = static::where('username', $username);

            if ($this->exists) {
                $query->where('id', '!=', $this->id);
            }
        } while ($query->exists());

        return $username;
    }

    public function hasCompletedModelProfile(): bool
    {
        return (bool) $this->modelProfile?->isComplete();
    }

    /**
     * Get photographer portfolio images (own portfolio).
     */
    public function photographerPortfolioImages()
    {
        return $this->hasMany(PhotographerPortfolioImage::class, 'photographer_id');
    }

    public function portfolioCredits()
    {
        return $this->hasMany(PortfolioCredit::class, 'credited_user_id');
    }

    public function createdPortfolioCredits()
    {
        return $this->hasMany(PortfolioCredit::class, 'created_by_user_id');
    }

    public function siteNotifications()
    {
        return $this->hasMany(SiteNotification::class);
    }

    /**
     * Send the email verification notification.
     * Mail configuration is handled centrally in AppServiceProvider.
     */
    public function sendEmailVerificationNotification()
    {
        // Don't send verification emails to admins - they're auto-verified
        if ($this->is_admin) {
            return;
        }

        // Mail configuration is already set in AppServiceProvider boot()
        // All emails will use the configured mailer (SMTP, sendmail, etc.)
        try {
            $this->notify(new \App\Notifications\VerifyEmail);
        } catch (\Exception $e) {
            \Log::error('Failed to send email verification notification: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Send the password reset notification.
     * Uses unified mail configuration from database settings.
     */
    public function sendPasswordResetNotification($token)
    {
        // Mail configuration is already set in AppServiceProvider boot()
        // All emails will use the configured mailer (SMTP, sendmail, etc.)
        $this->notify(new \App\Notifications\ResetPassword($token));
    }

    /**
     * Determine if the user has verified their email address.
     * Admins are considered verified automatically.
     */
    public function hasVerifiedEmail(): bool
    {
        // Admins are always considered verified
        if ($this->is_admin) {
            return true;
        }

        return ! is_null($this->email_verified_at);
    }

    /**
     * Mark the email address as verified.
     */
    public function markEmailAsVerified(): bool
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
        ])->save();
    }
}
